<?php
require_once '../db_connect.php';
require_once '../auth_helper.php';
require_once '../payroll_config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? 'get_overview';
$today = date('Y-m-d');

try {
    switch ($action) {
        case 'get_overview':
            // 1. Total Active Employees
            $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status NOT IN ('Terminated', 'Exit') AND deleted_at IS NULL");
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

        case 'get_announcements_notifications':
            $user_id = $_SESSION['user_id'];
            $dept_id = $_SESSION['user_dept_id'] ?? 0;

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
            $current_month = date('Y-m');
            $range = getPayrollRange($current_month);
            $start_date = $range['start'];
            $end_date = $range['end'];
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE employee_id = ? AND date BETWEEN ? AND ? AND status IN ('ON TIME', 'LATE IN', 'HALF DAY')");
            $stmt->execute([$user_id, $start_date, $end_date]);
            $attendance_count = $stmt->fetchColumn();

            // 3. My Department Count
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM employees 
                WHERE department_id = (SELECT department_id FROM employees WHERE id = ?)
                AND status NOT IN ('Terminated', 'Exit')
            ");
            $stmt->execute([$user_id]);
            $dept_count = $stmt->fetchColumn();

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'leave_balance' => (int)($total_allowed - $approved_days),
                    'monthly_attendance' => (int)$attendance_count,
                    'dept_employees' => (int)$dept_count
                ]
            ]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
