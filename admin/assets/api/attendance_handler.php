<?php
// admin/assets/api/attendance_handler.php
header('Content-Type: application/json');

require_once dirname(__DIR__, 3) . '/includes/db_connect.php';
require_once dirname(__DIR__, 3) . '/includes/auth_helper.php';
require_once dirname(__DIR__, 3) . '/includes/payroll_config.php';

if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['Admin', 'HR'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'fetch_daily':
        handleFetchDaily($pdo);
        break;
    case 'fetch_log':
        handleFetchLog($pdo);
        break;
    case 'update_attendance':
        handleUpdateAttendance($pdo);
        break;
    case 'fetch_bulk_init':
        handleFetchBulkInit($pdo);
        break;
    case 'fetch_bulk_employees':
        handleFetchBulkEmployees($pdo);
        break;
    case 'process_bulk_attendance':
        handleProcessBulkAttendance($pdo);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}

function handleFetchDaily($pdo) {
    $date = $_GET['date'] ?? date('Y-m-d');
    
    // Fetch all active employees and their attendance for the chosen date
    $query = "
        SELECT 
            e.id as emp_id, e.first_name, e.middle_name, e.last_name, e.profile_pic,
            s.name as shift_name, s.start_time as shift_start, s.end_time as shift_end,
            a.id as attendance_id, a.clock_in, a.clock_out, a.working_hours, a.status, a.message
        FROM employees e
        LEFT JOIN shifts s ON e.shift_id = s.id
        LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = ?
        WHERE e.role NOT IN ('Admin', 'HR') AND (e.joining_date IS NULL OR e.joining_date <= ?)
        ORDER BY e.id ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$date, $date]);
    $results = $stmt->fetchAll();

    // Identify if it's a weekend
    $dw = date('w', strtotime($date));
    $is_weekend = ($dw == 0 || $dw == 6);

    foreach ($results as &$r) {
        $r['full_name'] = trim($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'] . ' ' : '') . $r['last_name']);
        if (!$r['attendance_id']) {
            if ($is_weekend) {
                $r['status'] = 'WEEKEND';
            } else {
                // If past date and no record, it's ABSENT (for UI)
                // But we only show it if the date is in the past
                if ($date < date('Y-m-d')) {
                    $r['status'] = 'ABSENT';
                } else {
                    $r['status'] = 'NO RECORD';
                }
            }
        }
    }

    echo json_encode(['status' => 'success', 'data' => $results]);
}

function handleFetchLog($pdo) {
    $emp_id = $_GET['emp_id'] ?? '';
    $month = $_GET['month'] ?? date('Y-m');
    
    if (!$emp_id) {
        echo json_encode(['status' => 'error', 'message' => 'Employee ID required.']);
        return;
    }

    $range = getPayrollRange($month);
    $start_date = $range['start'];
    $end_date = $range['end'];

    // Fetch employee info
    $stmt = $pdo->prepare("SELECT e.*, s.name as shift_name, s.start_time, s.end_time FROM employees e LEFT JOIN shifts s ON e.shift_id = s.id WHERE e.id = ?");
    $stmt->execute([$emp_id]);
    $employee = $stmt->fetch();

    if (!$employee) {
        echo json_encode(['status' => 'error', 'message' => 'Employee not found.']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT a.*, s.name as shift_name, s.start_time as shift_start, s.end_time as shift_end 
        FROM attendance a 
        LEFT JOIN shifts s ON a.shift_id = s.id 
        WHERE a.employee_id = ? AND a.date BETWEEN ? AND ? 
        ORDER BY a.date DESC
    ");
    $stmt->execute([$emp_id, $start_date, $end_date]);
    $logs = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success', 
        'employee' => [
            'name' => trim($employee['first_name'] . ' ' . ($employee['middle_name'] ? $employee['middle_name'] . ' ' : '') . $employee['last_name']),
            'id' => $employee['id'],
            'email' => $employee['email'],
            'role' => $employee['role'] ?? 'Employee',
            'profile_pic' => $employee['profile_pic'],
            'shift_name' => $employee['shift_name'],
            'start_time' => $employee['start_time'],
            'end_time' => $employee['end_time']
        ],
        'data' => $logs
    ]);
}

function handleUpdateAttendance($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $emp_id = $data['emp_id'] ?? '';
    $date = $data['date'] ?? ''; // Logical date
    $clock_in = $data['clock_in'] ?? null;
    $clock_out = $data['clock_out'] ?? null;
    $message = $data['message'] ?? '';

    if (!$emp_id || !$date) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        return;
    }

    // Fetch Shift Info
    $stmt = $pdo->prepare("SELECT s.* FROM employees e JOIN shifts s ON e.shift_id = s.id WHERE e.id = ?");
    $stmt->execute([$emp_id]);
    $shift = $stmt->fetch();

    if (!$shift) {
        echo json_encode(['status' => 'error', 'message' => 'No shift found for employee.']);
        return;
    }

    // Prepare Dates
    // If clock_in/out are provided as "HH:MM AM/PM", convert to "YYYY-MM-DD HH:MM:SS"
    // Clock In is always on the logical date (or the night before, but for admin edit we simplify)
    // Actually, for overnight shifts, if Clock In is 8 PM and Out is 5 AM:
    // In date = logical date, Out date = logical date + 1 day.
    
    $clock_in_db = null;
    $clock_out_db = null;
    $status = 'ON TIME';
    $working_hours_str = '—';

    if ($clock_in && $clock_in !== '--:--') {
        $in_ts = strtotime("$date $clock_in");
        $clock_in_db = date('Y-m-d H:i:s', $in_ts);
        
        // Status Logic
        $shift_start_str = $shift['start_time'];
        $shift_start_ts = strtotime("$date $shift_start_str");
        $grace_minutes = (int)$shift['grace_time'];
        $grace_ts = $shift_start_ts + ($grace_minutes * 60);

        if ($in_ts > $grace_ts) {
            $status = 'LATE IN';
        }

        if ($clock_out && $clock_out !== '--:--') {
            $out_date = $date;
            // Overnight check
            if (strtotime($shift['start_time']) > strtotime($shift['end_time'])) {
                // It's overnight. If out_time is small (e.g. AM) and in_time is large (e.g. PM), it crossed midnight.
                if (strtotime($clock_out) < strtotime($clock_in)) {
                    $out_date = date('Y-m-d', strtotime($date . ' +1 day'));
                }
            }
            $out_ts = strtotime("$out_date $clock_out");
            $clock_out_db = date('Y-m-d H:i:s', $out_ts);

            // Duration
            $diff = $out_ts - $in_ts;
            if ($diff < 0) $diff += 86400; // Safety for 24h+
            
            $h = floor($diff / 3600);
            $m = floor(($diff % 3600) / 60);
            $working_hours_str = "{$h}h " . str_pad($m, 2, '0', STR_PAD_LEFT) . "m";

            // Half Day logic
            $halfday_minutes = (float)$shift['halfday_hours'] * 60;
            if (($diff / 60) < $halfday_minutes) {
                $status = 'HALF DAY';
            }
        }
    } else {
        $status = 'ABSENT';
    }

    // Update or Insert
    $stmt = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND date = ?");
    $stmt->execute([$emp_id, $date]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE attendance SET clock_in = ?, clock_out = ?, working_hours = ?, status = ?, message = ?, shift_id = ? WHERE id = ?");
        $stmt->execute([$clock_in_db, $clock_out_db, $working_hours_str, $status, $message, $shift['id'], $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, clock_in, clock_out, working_hours, status, message, shift_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$emp_id, $date, $clock_in_db, $clock_out_db, $working_hours_str, $status, $message, $shift['id']]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Attendance updated.']);
}

function handleFetchBulkInit($pdo) {
    $stmt = $pdo->query("SELECT id, name FROM departments ORDER BY name ASC");
    $depts = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'departments' => $depts]);
}

function handleFetchBulkEmployees($pdo) {
    $dept_id = $_GET['dept_id'] ?? '';
    $date = $_GET['date'] ?? date('Y-m-d');
    $search = $_GET['search'] ?? '';

    $params = [$date, $date];
    $where = "WHERE e.role NOT IN ('Admin', 'HR') AND e.deleted_at IS NULL AND (e.joining_date IS NULL OR e.joining_date <= ?)";
    
    if ($dept_id) {
        $where .= " AND e.department_id = ?";
        $params[] = $dept_id;
    }
    
    if ($search) {
        $where .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.id LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $query = "
        SELECT 
            e.id as emp_id, e.first_name, e.last_name, 
            d.name as department_name,
            s.name as shift_name, s.start_time, s.end_time,
            a.status as today_status
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN shifts s ON e.shift_id = s.id
        LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = ?
        $where
        ORDER BY e.first_name ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    foreach($results as &$r) {
        $r['full_name'] = trim($r['first_name'] . ' ' . $r['last_name']);
    }

    echo json_encode(['status' => 'success', 'data' => $results]);
}

function handleProcessBulkAttendance($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $emp_ids = $data['emp_ids'] ?? [];
    $date = $data['date'] ?? '';
    $status_type = $data['status_type'] ?? ''; // 'AUTO' or 'HOLIDAY'

    if (empty($emp_ids) || !$date || !$status_type) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required data.']);
        return;
    }

    try {
        $pdo->beginTransaction();

        foreach ($emp_ids as $emp_id) {
            // Fetch shift info for this employee
            $stmt = $pdo->prepare("SELECT s.* FROM employees e JOIN shifts s ON e.shift_id = s.id WHERE e.id = ?");
            $stmt->execute([$emp_id]);
            $shift = $stmt->fetch();

            if (!$shift) continue;

            $status = 'ON TIME';
            $clock_in = null;
            $clock_out = null;
            $working_hours = '—';

            if ($status_type === 'AUTO') {
                $status = 'ON TIME';
                $clock_in = $date . ' ' . $shift['start_time'];
                $clock_out = $date . ' ' . $shift['end_time'];
                
                // If overnight, adjust out date
                if (strtotime($shift['start_time']) > strtotime($shift['end_time'])) {
                    $clock_out = date('Y-m-d H:i:s', strtotime($clock_out . ' +1 day'));
                }

                // Duration
                $diff = strtotime($clock_out) - strtotime($clock_in);
                $h = floor($diff / 3600);
                $m = floor(($diff % 3600) / 60);
                $working_hours = "{$h}h " . str_pad($m, 2, '0', STR_PAD_LEFT) . "m";

            } elseif ($status_type === 'HOLIDAY') {
                $status = 'HOLIDAY';
            }

            // Check existing
            $stmt = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND date = ?");
            $stmt->execute([$emp_id, $date]);
            $existing = $stmt->fetch();

            if ($existing) {
                $stmt = $pdo->prepare("UPDATE attendance SET clock_in = ?, clock_out = ?, working_hours = ?, status = ?, shift_id = ? WHERE id = ?");
                $stmt->execute([$clock_in, $clock_out, $working_hours, $status, $shift['id'], $existing['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, clock_in, clock_out, working_hours, status, shift_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$emp_id, $date, $clock_in, $clock_out, $working_hours, $status, $shift['id']]);
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Bulk attendance processed successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}
