<?php
require_once '../db_connect.php';
require_once '../auth_helper.php';
require_once '../payroll_config.php';
require_once __DIR__ . '/admin_dashboard_helpers.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'get_overview';

// Logical Date Logic: If before 7 AM, count today as yesterday
$today = date('H') < 7 ? date('Y-m-d', strtotime('-1 day')) : date('Y-m-d');

try {
    switch ($action) {
        case 'get_overview':
            // 1. Total Active Employees (Employee role only — excludes Admin & HR)
            $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE role = 'Employee' AND status IN ('Active', 'On Leave') AND deleted_at IS NULL");
            $total_employees = $stmt->fetchColumn();

            // 2. Present Today
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND status IN ('ON TIME', 'LATE IN', 'HALF DAY')");
            $stmt->execute([$today]);
            $present_today = $stmt->fetchColumn();

            // 3. Pending Leaves
            $stmt = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'Pending'");
            $pending_leaves = $stmt->fetchColumn();

            // 4. Active Job Openings
            $stmt = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'Active' AND deleted_at IS NULL");
            $active_jobs = $stmt->fetchColumn();

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'total_employees' => (int)$total_employees,
                    'present_today' => (int)$present_today,
                    'pending_leaves' => (int)$pending_leaves,
                    'active_jobs' => (int)$active_jobs
                ]
            ]);
            break;

        case 'get_admin_dashboard':
            if (!in_array($_SESSION['user_role'] ?? '', ['Admin', 'HR'], true)) {
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
                break;
            }
            $payload = buildAdminDashboardPayload($pdo, $user_id);
            echo json_encode(['status' => 'success', 'data' => $payload]);
            break;

        case 'get_announcements_notifications':
            $user_id = $_SESSION['user_id'];
            $dept_stmt = $pdo->prepare("SELECT department_id FROM employees WHERE id = ?");
            $dept_stmt->execute([$user_id]);
            $dept_id = (int) ($dept_stmt->fetchColumn() ?: 0);

            // 1. Latest Announcements (Events with show_in_announcement = 1)
            // Show for their dept or 'All'
            $stmt = $pdo->prepare("
                SELECT e.*, d.name as dept_name 
                FROM events e 
                LEFT JOIN departments d ON e.target_dept = d.name
                WHERE e.show_in_announcement = 1 
                AND (e.target_dept = 'All' OR e.target_dept = 'Everyone' OR d.id = ?)
                ORDER BY e.created_at DESC 
                LIMIT 5
            ");
            $stmt->execute([$dept_id]);
            $announcements = $stmt->fetchAll();

            // 2. Latest Unread Notifications
            $nStmt = $pdo->prepare("
                SELECT n.* 
                FROM notifications n 
                JOIN notification_recipients nr ON n.id = nr.notification_id 
                WHERE nr.employee_id = ? AND nr.is_read = 0 
                ORDER BY n.created_at DESC 
                LIMIT 5
            ");
            $nStmt->execute([$user_id]);
            $notifications = $nStmt->fetchAll();

            // 3. Unread Count
            $cStmt = $pdo->prepare("SELECT COUNT(*) FROM notification_recipients WHERE employee_id = ? AND is_read = 0");
            $cStmt->execute([$user_id]);
            $unread_count = $cStmt->fetchColumn();

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'announcements' => $announcements,
                    'notifications' => $notifications,
                    'unread_count' => (int)$unread_count
                ]
            ]);
            break;

        case 'get_personal_overview':
            $user_id = $_SESSION['user_id'];
            
            // 1. My Leave Balance (simplified: total - approved)
            // Note: In a real app, this would be per leave type. For now, general.
            $stmt = $pdo->prepare("SELECT SUM(days_per_year) FROM leave_types");
            $stmt->execute();
            $total_allowed = $stmt->fetchColumn() ?: 0;
            
            $stmt = $pdo->prepare("
                SELECT SUM(DATEDIFF(end_date, start_date) + 1) 
                FROM leave_requests 
                WHERE employee_id = ? AND status = 'Approved'
            ");
            $stmt->execute([$user_id]);
            $approved_days = $stmt->fetchColumn() ?: 0;
            
            // 2. My Attendance this payroll month
            $current_month = getCurrentPayrollMonth();
            $range = getPayrollRange($current_month);
            $start_date = $range['start'];
            $end_date = $range['end'];
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE employee_id = ? AND date BETWEEN ? AND ? AND status IN ('ON TIME', 'LATE IN', 'HALF DAY')");
            $stmt->execute([$user_id, $start_date, $end_date]);
            $attendance_count = $stmt->fetchColumn();

            // 3. My Department Info
            $stmt = $pdo->prepare("
                SELECT d.name, e.job_title, (SELECT COUNT(*) FROM employees WHERE department_id = d.id AND status NOT IN ('Terminated', 'Exit') AND deleted_at IS NULL) as dept_count
                FROM departments d
                JOIN employees e ON d.id = e.department_id
                WHERE e.id = ?
            ");
            $stmt->execute([$user_id]);
            $dept_info = $stmt->fetch();
            $dept_name = $dept_info['name'] ?? 'General';
            $job_title = trim($dept_info['job_title'] ?? '') ?: 'Employee';
            $dept_count = $dept_info['dept_count'] ?? 0;

            // 4. Today's or Active Attendance Details
            // We look for an open session first, then for a session on the logical today
            $stmt = $pdo->prepare("
                SELECT a.*, s.start_time, s.end_time 
                FROM attendance a 
                JOIN shifts s ON a.shift_id = s.id
                WHERE a.employee_id = ? 
                AND (a.date = ? OR a.clock_out IS NULL)
                ORDER BY a.clock_in DESC LIMIT 1
            ");
            $stmt->execute([$user_id, $today]);
            $today_attendance = $stmt->fetch();

            // 5. Fetch Shift for Target Hours (even if no attendance yet)
            $stmt = $pdo->prepare("SELECT start_time, end_time FROM shifts WHERE id = (SELECT shift_id FROM employees WHERE id = ?)");
            $stmt->execute([$user_id]);
            $shift = $stmt->fetch();
            $target_hours_str = "08h 00m"; // Default

            if ($shift) {
                $start = new DateTime($shift['start_time']);
                $end = new DateTime($shift['end_time']);
                if ($start > $end) $end->modify('+1 day'); // Overnight
                $diff = $start->diff($end);
                $target_hours_str = $diff->format('%hh %Im');
            }

            // 5. Unread Notifications Count
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications n JOIN notification_recipients nr ON n.id = nr.notification_id WHERE nr.employee_id = ? AND nr.is_read = 0");
            $stmt->execute([$user_id]);
            $unread_count = $stmt->fetchColumn();

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'leave_balance' => (int)($total_allowed - $approved_days),
                    'approved_leaves' => (int)$approved_days,
                    'monthly_attendance' => (int)$attendance_count,
                    'dept_employees' => (int)$dept_count,
                    'dept_name' => $dept_name,
                    'job_title' => $job_title,
                    'unread_notifications' => (int)$unread_count,
                    'today_attendance' => $today_attendance ? array_merge($today_attendance, [
                        'shift_end_raw' => $today_attendance['end_time'] // We'll handle date in JS or here
                    ]) : null,
                    'target_hours' => $target_hours_str,
                    'server_time' => date('Y-m-d H:i:s')
                ]
            ]);
            break;
        case 'get_latest_announcements':
            $today = date('Y-m-d');
            // Get user department
            $dept_stmt = $pdo->prepare("SELECT d.name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.id = ?");
            $dept_stmt->execute([$user_id]);
            $user_dept = $dept_stmt->fetchColumn() ?: 'unknown';

            $sql = "SELECT t.*, CONCAT(e.first_name, ' ', e.last_name) as author_name, e.profile_pic
                    FROM (
                        SELECT id, title, content, type, target_depts, start_date, end_date, created_by, created_at, 'announcement' as source
                        FROM announcements WHERE deleted_at IS NULL AND (target_depts = 'everyone' OR FIND_IN_SET(?, target_depts) > 0)
                        AND start_date <= ? AND end_date >= ?
                        UNION ALL
                        SELECT id, title, description as content, UPPER(category) as type, target_dept as target_depts, event_date as start_date, event_date as end_date, NULL as created_by, created_at, 'event' as source
                        FROM events WHERE show_in_announcement = 1 AND (LOWER(target_dept) IN ('everyone', 'all') OR FIND_IN_SET(?, REPLACE(target_dept, ', ', ',')) > 0)
                        AND event_date >= ?
                    ) t
                    LEFT JOIN employees e ON t.created_by = e.id
                    ORDER BY t.created_at DESC LIMIT 3";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_dept, $today, $today, $user_dept, $today]);
            $results = $stmt->fetchAll();

            foreach ($results as &$row) {
                $row['time_ago'] = 'Recently'; // Simpler for dashboard
                if ($row['source'] === 'event' && empty($row['author_name'])) {
                    $row['author_name'] = 'System Admin';
                }
            }

            echo json_encode(['status' => 'success', 'data' => $results]);
            break;

        case 'get_recent_notifications':
            $stmt = $pdo->prepare("
                SELECT n.*, nr.is_read 
                FROM notifications n 
                JOIN notification_recipients nr ON n.id = nr.notification_id 
                WHERE nr.employee_id = ? 
                ORDER BY n.created_at DESC LIMIT 3
            ");
            $stmt->execute([$user_id]);
            $results = $stmt->fetchAll();

            foreach ($results as &$row) {
                $row['time_ago'] = 'Recently'; // Simpler for dashboard
            }

            echo json_encode(['status' => 'success', 'data' => $results]);
            break;

        case 'get_chart_data':
            
            // 1. Weekly Attendance (Mon - Fri of Current Week)
            $monday = date('Y-m-d', strtotime('monday this week'));
            $weekly_data = [];
            $weekly_statuses = [];
            for ($i = 0; $i < 5; $i++) {
                $date = date('Y-m-d', strtotime("$monday +$i days"));
                $stmt = $pdo->prepare("SELECT status FROM attendance WHERE employee_id = ? AND date = ?");
                $stmt->execute([$user_id, $date]);
                $status = $stmt->fetchColumn();
                
                if (!$status) {
                    $weekly_data[] = 0;
                    $weekly_statuses[] = 'NOT RECORDED';
                } else if ($status === 'ABSENT') {
                    $weekly_data[] = 0;
                    $weekly_statuses[] = 'ABSENT';
                } else {
                    $weekly_data[] = 100;
                    $weekly_statuses[] = $status;
                }
            }

            // 2. Monthly Mix (Current Payroll Month - Accurate)
            $current_month = getCurrentPayrollMonth();
            $range = getPayrollRange($current_month);
            $start = new DateTime($range['start']);
            $end = new DateTime($range['end']);
            $today_dt = new DateTime(date('Y-m-d'));
            // Only check for Absents up to yesterday. Today will be counted only if there's a record (Present/Late etc.)
            $check_until = clone $today_dt;
            $check_until->modify('-1 day');
            if ($end > $check_until) $end = $check_until;

            $mix_counts = ['ON TIME' => 0, 'LATE IN' => 0, 'HALF DAY' => 0, 'ABSENT' => 0];
            
            // Fetch all records up to Today
            $stmt = $pdo->prepare("SELECT date, status FROM attendance WHERE employee_id = ? AND date BETWEEN ? AND ?");
            $stmt->execute([$user_id, $range['start'], $today_dt->format('Y-m-d')]);
            $records = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $interval = new DateInterval('P1D');
            // Loop from start to today
            $end_of_loop = clone $today_dt;
            $period = new DatePeriod($start, $interval, $end_of_loop->modify('+1 day'));

            foreach ($period as $dt) {
                $d_str = $dt->format('Y-m-d');
                $day_of_week = $dt->format('N');
                
                if ($day_of_week <= 5) { // Mon to Fri
                    if (isset($records[$d_str])) {
                        $status = $records[$d_str];
                        if (isset($mix_counts[$status])) $mix_counts[$status]++;
                    } else {
                        // Only mark as ABSENT if the date is strictly in the past
                        if ($d_str < $today_dt->format('Y-m-d')) {
                            $mix_counts['ABSENT']++;
                        }
                    }
                }
            }
            
            $mix_data = [
                'On Time' => $mix_counts['ON TIME'],
                'Late' => $mix_counts['LATE IN'],
                'Half Day' => $mix_counts['HALF DAY'],
                'Absent' => $mix_counts['ABSENT']
            ];

            // 3. Work Hours (Mon - Fri of Current Week)
            $stmt = $pdo->prepare("SELECT start_time, end_time FROM shifts WHERE id = (SELECT shift_id FROM employees WHERE id = ?)");
            $stmt->execute([$user_id]);
            $shift = $stmt->fetch();
            $shift_duration = 8.0; // Default
            if ($shift) {
                $s = new DateTime($shift['start_time']);
                $e = new DateTime($shift['end_time']);
                if ($s > $e) $e->modify('+1 day');
                $diff = $s->diff($e);
                $shift_duration = round($diff->h + ($diff->i / 60), 1);
            }

            $monday = date('Y-m-d', strtotime('monday this week'));
            $work_hours_data = [];
            $target_hours_data = [];
            $work_hours_labels = [];
            
            for ($i = 0; $i < 5; $i++) {
                $date = date('Y-m-d', strtotime("$monday +$i days"));
                $work_hours_labels[] = date('D', strtotime($date));
                
                // Fetch worked hours
                $stmt = $pdo->prepare("SELECT working_hours FROM attendance WHERE employee_id = ? AND date = ?");
                $stmt->execute([$user_id, $date]);
                $wh_str = $stmt->fetchColumn();
                
                $hours = 0;
                if ($wh_str && preg_match('/(\d+)h\s+(\d+)m/', $wh_str, $matches)) {
                    $hours = (float)$matches[1] + ((float)$matches[2] / 60);
                }
                $work_hours_data[] = round($hours, 1);
                $target_hours_data[] = $shift_duration;
            }

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'weekly_attendance' => $weekly_data,
                    'weekly_statuses' => $weekly_statuses,
                    'monthly_mix' => array_values($mix_data),
                    'work_hours' => [
                        'labels' => $work_hours_labels,
                        'worked' => $work_hours_data,
                        'target' => $target_hours_data
                    ]
                ]
            ]);
            break;

        case 'get_leave_analytics':
            $user_id = $_SESSION['user_id'];
            $from = $_GET['from'] ?? date('Y-01');
            $to = $_GET['to'] ?? date('Y-m');

            // Fetch all leave types
            $stmt = $pdo->prepare("SELECT id, name FROM leave_types");
            $stmt->execute();
            $leave_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $startDate = new DateTime($from . '-01');
            $endDate = new DateTime($to . '-01');
            $endDate->modify('last day of this month');

            $series = [];
            $categories = [];

            // Build categories (Month Names)
            $tempDate = clone $startDate;
            while ($tempDate <= $endDate) {
                $categories[] = $tempDate->format('M Y');
                $tempDate->modify('+1 month');
            }

            foreach ($leave_types as $type) {
                $type_data = [];
                $tempDate = clone $startDate;
                while ($tempDate <= $endDate) {
                    $mStart = $tempDate->format('Y-m-01');
                    $mEnd = $tempDate->format('Y-m-t');

                    $stmt = $pdo->prepare("
                        SELECT SUM(DATEDIFF(LEAST(end_date, ?), GREATEST(start_date, ?)) + 1)
                        FROM leave_requests
                        WHERE employee_id = ? AND leave_type_id = ? AND status = 'Approved'
                        AND start_date <= ? AND end_date >= ?
                    ");
                    $stmt->execute([$mEnd, $mStart, $user_id, $type['id'], $mEnd, $mStart]);
                    $count = $stmt->fetchColumn() ?: 0;
                    $type_data[] = (int)$count;

                    $tempDate->modify('+1 month');
                }
                $series[] = ['name' => $type['name'], 'data' => $type_data];
            }

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'series' => $series,
                    'categories' => $categories
                ]
            ]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
}
?>
