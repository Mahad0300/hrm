<?php
// user/assets/api/attendance_handler.php
header('Content-Type: application/json');

require_once dirname(__DIR__, 3) . '/includes/db_connect.php';
require_once dirname(__DIR__, 3) . '/includes/auth_helper.php';
require_once dirname(__DIR__, 3) . '/includes/payroll_config.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_status':
        handleGetStatus($pdo, $user_id);
        break;
    case 'check_in':
        handleCheckIn($pdo, $user_id);
        break;
    case 'check_out':
        handleCheckOut($pdo, $user_id);
        break;
    case 'fetch_logs':
        handleFetchLogs($pdo, $user_id);
        break;
    case 'save_message':
        handleSaveMessage($pdo, $user_id);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}

function handleGetStatus($pdo, $user_id) {
    // 1. Fetch Shift Info
    $stmt = $pdo->prepare("SELECT s.* FROM employees e JOIN shifts s ON e.shift_id = s.id WHERE e.id = ?");
    $stmt->execute([$user_id]);
    $shift = $stmt->fetch();

    if (!$shift) {
        echo json_encode(['status' => 'error', 'message' => 'No shift assigned.']);
        return;
    }

    $logical_date = determineLogicalDate($shift);

    // 2. Check for attendance record on this logical date
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ? LIMIT 1");
    $stmt->execute([$user_id, $logical_date]);
    $record = $stmt->fetch();

    $can_check_in = false;
    $can_check_out = false;
    $is_checked_in = false;
    $check_in_time = null;

    if (!$record || $record['status'] === 'ABSENT') {
        // If no record or it's an ABSENT placeholder, user can check in
        $can_check_in = true;
    } else {
        // Record exists and is NOT ABSENT
        if ($record['clock_in'] && !$record['clock_out']) {
            $is_checked_in = true;
            $can_check_out = true;
            $check_in_time = date('h:i A', strtotime($record['clock_in']));
        } elseif ($record['clock_in'] && $record['clock_out']) {
            // Already finished this shift
            $can_check_in = false;
            $can_check_out = false;
        }
    }

    echo json_encode([
        'status' => 'success',
        'is_checked_in' => $is_checked_in,
        'can_check_in' => $can_check_in,
        'can_check_out' => $can_check_out,
        'check_in_time' => $check_in_time,
        'logical_date' => $logical_date
    ]);
}

function handleCheckIn($pdo, $user_id) {
    // 1. Fetch Shift Info
    $stmt = $pdo->prepare("SELECT s.* FROM employees e JOIN shifts s ON e.shift_id = s.id WHERE e.id = ?");
    $stmt->execute([$user_id]);
    $shift = $stmt->fetch();

    if (!$shift) {
        echo json_encode(['status' => 'error', 'message' => 'No shift assigned to you.']);
        return;
    }

    $logical_date = determineLogicalDate($shift);

    // 2. Prevent Duplicate Check-in for this logical shift
    $stmt = $pdo->prepare("SELECT id, status FROM attendance WHERE employee_id = ? AND date = ? LIMIT 1");
    $stmt->execute([$user_id, $logical_date]);
    $existing = $stmt->fetch();
    
    if ($existing && $existing['status'] !== 'ABSENT') {
        echo json_encode(['status' => 'error', 'message' => 'Attendance already recorded for shift starting on ' . date('d M', strtotime($logical_date))]);
        return;
    }

    // If there's an ABSENT record, we'll delete it or overwrite it
    if ($existing && $existing['status'] === 'ABSENT') {
        $pdo->prepare("DELETE FROM attendance WHERE id = ?")->execute([$existing['id']]);
    }

    $now = new DateTime();

    // 3. Mark Absent for missed days (Backdate logic)
    markMissedDaysAbsent($pdo, $user_id, $logical_date, $shift['id']);

    // 4. Determine Status (ON TIME / LATE IN)
    // We compare current time with shift start time on the logical date
    $shift_start_dt = new DateTime($logical_date . ' ' . $shift['start_time']);
    $grace_minutes = (int)$shift['grace_time'];
    $grace_dt = clone $shift_start_dt;
    $grace_dt->modify("+$grace_minutes minutes");

    $status = 'ON TIME';
    if ($now > $grace_dt) {
        $status = 'LATE IN';
    }

    // 5. Insert Record
    $now_db = $now->format('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, shift_id, clock_in, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $logical_date, $shift['id'], $now_db, $status]);

    echo json_encode(['status' => 'success', 'message' => 'Checked in successfully for shift ' . date('d M', strtotime($logical_date)) . ' as ' . $status]);
}

function handleCheckOut($pdo, $user_id) {
    // Find latest open check-in (MUST have clock_in and MUST NOT be ABSENT)
    $stmt = $pdo->prepare("SELECT a.*, s.halfday_hours FROM attendance a JOIN shifts s ON a.shift_id = s.id WHERE a.employee_id = ? AND a.clock_in IS NOT NULL AND a.clock_out IS NULL AND a.status != 'ABSENT' ORDER BY a.clock_in DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $record = $stmt->fetch();

    if (!$record) {
        echo json_encode(['status' => 'error', 'message' => 'You are not checked in.']);
        return;
    }

    $clock_in = new DateTime($record['clock_in']);
    $clock_out = new DateTime(); // Now
    
    // Calculate Duration
    $interval = $clock_in->diff($clock_out);
    $total_minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
    $hours = floor($total_minutes / 60);
    $mins = $total_minutes % 60;
    $working_hours_str = "{$hours}h " . str_pad($mins, 2, '0', STR_PAD_LEFT) . "m";

    // Determine if Half Day
    $status = $record['status'];
    $halfday_threshold = (float)$record['halfday_hours'] * 60; // Convert hours to minutes
    if ($total_minutes < $halfday_threshold) {
        $status = 'HALF DAY';
    }

    // Update Record
    $clock_out_db = $clock_out->format('Y-m-d H:i:s');
    $stmt = $pdo->prepare("UPDATE attendance SET clock_out = ?, working_hours = ?, status = ? WHERE id = ?");
    $stmt->execute([$clock_out_db, $working_hours_str, $status, $record['id']]);

    echo json_encode(['status' => 'success', 'message' => 'Checked out successfully. Work duration: ' . $working_hours_str]);
}

function handleFetchLogs($pdo, $user_id) {
    $month = $_GET['month'] ?? date('Y-m');
    $range = getPayrollRange($month);
    $start_date = $range['start'];
    $end_date = $range['end'];

    $stmt = $pdo->prepare("
        SELECT a.*, s.name as shift_name, s.start_time as shift_start, s.end_time as shift_end 
        FROM attendance a 
        LEFT JOIN shifts s ON a.shift_id = s.id 
        WHERE a.employee_id = ? AND a.date BETWEEN ? AND ? 
        ORDER BY a.date DESC
    ");
    $stmt->execute([$user_id, $start_date, $end_date]);
    $logs = $stmt->fetchAll();

    // Format for frontend
    foreach ($logs as &$log) {
        $log['formatted_date'] = date('d M, Y', strtotime($log['date']));
        $log['date_iso'] = $log['date'];
        $log['in_time'] = $log['clock_in'] ? date('h:i A', strtotime($log['clock_in'])) : '--:--';
        $log['out_time'] = $log['clock_out'] ? date('h:i A', strtotime($log['clock_out'])) : '--:--';
        $log['status_class'] = getStatusClass($log['status']);
    }

    echo json_encode(['status' => 'success', 'data' => $logs]);
}

function handleSaveMessage($pdo, $user_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $date = $data['date'] ?? '';
    $message = $data['message'] ?? '';

    if (!$date) {
        echo json_encode(['status' => 'error', 'message' => 'Date is required.']);
        return;
    }

    // Convert "15 Sep, 2026" back to Y-m-d
    $formatted_date = date('Y-m-d', strtotime($date));

    $stmt = $pdo->prepare("UPDATE attendance SET message = ? WHERE employee_id = ? AND date = ?");
    $stmt->execute([$message, $user_id, $formatted_date]);

    echo json_encode(['status' => 'success', 'message' => 'Message updated.']);
}

function markMissedDaysAbsent($pdo, $user_id, $today, $shift_id) {
    // Find last attendance date
    $stmt = $pdo->prepare("SELECT date FROM attendance WHERE employee_id = ? ORDER BY date DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $last = $stmt->fetch();

    if (!$last) return; // First time user?

    $last_date = new DateTime($last['date']);
    $current_date = new DateTime($today);
    
    $last_date->modify('+1 day');
    
    while ($last_date < $current_date) {
        $day_of_week = $last_date->format('N'); // 1 (Mon) to 7 (Sun)
        
        // Skip Sat (6) and Sun (7)
        if ($day_of_week < 6) {
            $check_date = $last_date->format('Y-m-d');
            
            // Check if already exists (shouldn't, but safety first)
            $stmt = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND date = ?");
            $stmt->execute([$user_id, $check_date]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, shift_id, status, message) VALUES (?, ?, ?, 'ABSENT', NULL)");
                $stmt->execute([$user_id, $check_date, $shift_id]);
            }
        }
        $last_date->modify('+1 day');
    }
}

function getStatusClass($status) {
    switch ($status) {
        case 'ON TIME': return 'status-v2-ontime';
        case 'LATE IN': return 'status-v2-late';
        case 'HALF DAY': return 'status-v2-halfday';
        case 'ABSENT': return 'status-v2-absent';
        case 'WEEKEND':
        case 'HOLIDAY': return 'status-v2-holiday';
        default: return '';
    }
}

function determineLogicalDate($shift) {
    $now = new DateTime();
    $current_time_str = $now->format('H:i:s');
    $shift_start_str = $shift['start_time'];
    $shift_end_str = $shift['end_time'];
    
    $logical_date = date('Y-m-d');
    $is_overnight = strtotime($shift_start_str) > strtotime($shift_end_str);
    
    if ($is_overnight) {
        // If current time is between 00:00:00 and (Shift End + 4 hours buffer), map to yesterday
        $buffer_end = date('H:i:s', strtotime($shift_end_str . ' +4 hours'));
        if ($current_time_str >= '00:00:00' && $current_time_str <= $buffer_end) {
            $logical_date = date('Y-m-d', strtotime('-1 day'));
        }
    }
    return $logical_date;
}
