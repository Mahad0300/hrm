<?php
require_once __DIR__ . '/bootstrap.php';
// admin/assets/api/attendance_handler.php
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? '';
$userRole = $_SESSION['user_role'] ?? '';

// Employee-only actions (self-data access)
$employeeActions = ['fetch_logs', 'save_message', 'check_in', 'check_out', 'get_status'];
// Admin/HR-only actions
$adminHrActions = ['fetch_daily', 'fetch_log', 'update_attendance', 'fetch_bulk_init', 'fetch_bulk_employees', 'process_bulk_attendance'];

if (in_array($action, $adminHrActions) && !in_array($userRole, ['Admin', 'HR'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if (in_array($action, $adminHrActions)) {
    hrGuardApiRequest($pdo, $action, basename(__FILE__));
}

switch ($action) {
    case 'fetch_daily':
        handleFetchDaily($pdo);
        break;
    case 'fetch_log':
        handleFetchLog($pdo);
        break;
    case 'fetch_logs':
        handleFetchLogs($pdo);
        break;
    case 'save_message':
        handleSaveMessage($pdo);
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
    case 'check_in':
        handleCheckIn($pdo);
        break;
    case 'check_out':
        handleCheckOut($pdo);
        break;
    case 'get_status':
        handleGetStatus($pdo);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}

function handleFetchDaily($pdo) {
    // Logical Date Logic: If it's before 9 AM, default to yesterday's date
    // because the night shift of yesterday is still active or just ended.
    $default_date = date('H') < 9 ? date('Y-m-d', strtotime('-1 day')) : date('Y-m-d');
    $date = $_GET['date'] ?? $default_date;
    
    // Fetch all active employees and their attendance for the chosen date
    $query = "
        SELECT 
            e.id as emp_id, e.first_name, e.middle_name, e.last_name, e.profile_pic,
            s.name as shift_name, s.start_time as shift_start, s.end_time as shift_end,
            a.id as attendance_id, a.clock_in, a.clock_out, a.working_hours, a.status, a.message
        FROM employees e
        LEFT JOIN shifts s ON e.shift_id = s.id
        LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = ?
        WHERE (e.role != 'Admin' OR (e.role = 'Admin' AND e.biometric_id IS NOT NULL AND e.biometric_id != '')) AND e.deleted_at IS NULL AND e.status = 'Active' AND (e.joining_date IS NULL OR e.joining_date <= ?)
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
        if (empty($r['shift_start']) && empty($r['shift_end'])) {
            if ($r['attendance_id']) {
                if (($r['status'] ?? '') !== 'LEAVE' && ($r['status'] ?? '') !== 'HOLIDAY') {
                    $r['status'] = '-';
                }
            } else {
                $r['status'] = '-';
            }
        } else if (!$r['attendance_id']) {
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
    $month = $_GET['month'] ?? getCurrentPayrollMonth();
    
    if (!$emp_id) {
        echo json_encode(['status' => 'error', 'message' => 'Employee ID required.']);
        return;
    }

    $range = getPayrollRange($month);
    $start_date = $range['start'];
    $end_date = $range['end'];

    \App\Helpers\LeaveAttendanceSync::cleanupWeekendLeaveInRange($pdo, (int) $emp_id, $start_date, $end_date);

    // Fetch employee info
    $stmt = $pdo->prepare("SELECT e.*, s.name as shift_name, s.start_time, s.end_time FROM employees e LEFT JOIN shifts s ON e.shift_id = s.id WHERE e.id = ?");
    $stmt->execute([$emp_id]);
    $employee = $stmt->fetch();

    if (!$employee) {
        echo json_encode(['status' => 'error', 'message' => 'Employee not found.']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT a.*,
               COALESCE(s.name, es.name) as shift_name,
               COALESCE(s.start_time, es.start_time) as shift_start,
               COALESCE(s.end_time, es.end_time) as shift_end
        FROM attendance a
        LEFT JOIN shifts s  ON a.shift_id = s.id
        LEFT JOIN employees emp ON a.employee_id = emp.id
        LEFT JOIN shifts es ON emp.shift_id = es.id
        WHERE a.employee_id = ? AND a.date BETWEEN ? AND ?
        ORDER BY a.date DESC
    ");
    $stmt->execute([$emp_id, $start_date, $end_date]);
    $logs = $stmt->fetchAll();

    foreach ($logs as &$log) {
        if (empty($log['shift_start']) && empty($log['shift_end'])) {
            if (($log['status'] ?? '') !== 'LEAVE' && ($log['status'] ?? '') !== 'HOLIDAY') {
                $log['status'] = '-';
            }
        }
    }

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
    $stmt = $pdo->prepare("SELECT s.* FROM employees e LEFT JOIN shifts s ON e.shift_id = s.id WHERE e.id = ?");
    $stmt->execute([$emp_id]);
    $shift = $stmt->fetch();

    $clock_in_db = null;
    $clock_out_db = null;
    $status = 'ON TIME';
    $working_hours_str = '';

    if ($clock_in && $clock_in !== '--:--') {
        $in_ts = strtotime("$date $clock_in");
        $clock_in_db = date('Y-m-d H:i:s', $in_ts);
        
        // Status Logic only if shift is assigned
        if ($shift && !empty($shift['start_time'])) {
            $shift_start_str = $shift['start_time'];
            $shift_start_ts = strtotime("$date $shift_start_str");
            $grace_minutes = (int)($shift['grace_time'] ?? 15);
            $grace_ts = $shift_start_ts + ($grace_minutes * 60) + 59;

            if ($in_ts > $grace_ts) {
                $status = 'LATE IN';
            }
        }

        if ($clock_out && $clock_out !== '--:--') {
            $out_date = $date;
            if ($shift && !empty($shift['start_time']) && !empty($shift['end_time'])) {
                if (strtotime($shift['start_time']) > strtotime($shift['end_time'])) {
                    if (strtotime($clock_out) < strtotime($clock_in)) {
                        $out_date = date('Y-m-d', strtotime($date . ' +1 day'));
                    }
                }
            } else if (strtotime($clock_out) < strtotime($clock_in)) {
                $out_date = date('Y-m-d', strtotime($date . ' +1 day'));
            }
            $out_ts = strtotime("$out_date $clock_out");
            $clock_out_db = date('Y-m-d H:i:s', $out_ts);

            // Duration
            $diff = $out_ts - $in_ts;
            if ($diff < 0) $diff += 86400; // Safety for 24h+
            
            $h = floor($diff / 3600);
            $m = floor(($diff % 3600) / 60);
            $working_hours_str = "{$h}h " . str_pad($m, 2, '0', STR_PAD_LEFT) . "m";

            // Half Day logic only if shift has halfday_hours
            if ($shift && !empty($shift['halfday_hours'])) {
                $halfday_minutes = (float)$shift['halfday_hours'] * 60;
                if (($diff / 60) < $halfday_minutes) {
                    $status = 'HALF DAY';
                }
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
        $stmt = $pdo->prepare("UPDATE attendance SET clock_in = ?, clock_out = ?, working_hours = ?, status = ?, message = ?, shift_id = ?, is_manual = 1 WHERE id = ?");
        $stmt->execute([$clock_in_db, $clock_out_db, $working_hours_str, $status, $message, $shift['id'], $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, clock_in, clock_out, working_hours, status, message, shift_id, is_manual) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$emp_id, $date, $clock_in_db, $clock_out_db, $working_hours_str, $status, $message, $shift['id']]);
    }

    // [TRIGGER] Notify Employee via In-App Alert and WebSocket
    $actor_name = $_SESSION['user_name'] ?? 'Admin/HR';
    $actor_id = $_SESSION['user_id'] ?? 0;
    $notif_msg = "Your attendance for date $date has been updated by $actor_name to status: $status.";
    addNotification([(int)$emp_id], "Attendance Updated", $notif_msg, "daily-attendance.php", "Attendance", $actor_id);
    
    // Broadcast WebSocket updates to dynamic lists
    \App\Helpers\WebSocketHelper::sendToUser((int)$emp_id, 'attendance_updated');
    \App\Helpers\WebSocketHelper::broadcast('attendance_updated');

    // [LOG ACTIVITY]
    $emp_stmt = $pdo->prepare("SELECT first_name, last_name FROM employees WHERE id = ?");
    $emp_stmt->execute([$emp_id]);
    $emp_row = $emp_stmt->fetch();
    $emp_name = $emp_row ? ($emp_row['first_name'] . ' ' . $emp_row['last_name']) : "Employee #$emp_id";
    logActivity($actor_id, "Update Attendance", "Attendance", "Updated attendance for $emp_name for date: $date to status: $status.");

    echo json_encode(['status' => 'success', 'message' => 'Attendance updated.']);
}

function handleFetchBulkInit($pdo) {
    $stmt = $pdo->query("SELECT id, name FROM departments ORDER BY name ASC");
    $depts = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'departments' => $depts]);
}

function handleFetchBulkEmployees($pdo) {
    $dept_id = $_GET['dept_id'] ?? '';
    $default_date = date('H') < 9 ? date('Y-m-d', strtotime('-1 day')) : date('Y-m-d');
    $date = $_GET['date'] ?? $default_date;
    $search = $_GET['search'] ?? '';

    $params = [$date, $date];
    $where = "WHERE (e.role != 'Admin' OR (e.role = 'Admin' AND e.biometric_id IS NOT NULL AND e.biometric_id != '')) AND e.deleted_at IS NULL AND (e.joining_date IS NULL OR e.joining_date <= ?)";
    
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
            e.id as emp_id, e.first_name, e.middle_name, e.last_name, 
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
        $middle = !empty($r['middle_name']) ? ' ' . $r['middle_name'] : '';
        $r['full_name'] = trim($r['first_name'] . $middle . ' ' . $r['last_name']);
    }

    echo json_encode(['status' => 'success', 'data' => $results]);
}

function handleProcessBulkAttendance($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $emp_ids = $data['emp_ids'] ?? [];
    $is_range = !empty($data['is_range']);
    $date = $data['date'] ?? '';
    $start_date = $data['start_date'] ?? $date;
    $end_date = $data['end_date'] ?? $date;
    $status_type = $data['status_type'] ?? ''; // 'AUTO' or 'HOLIDAY'

    if (empty($emp_ids) || !$status_type) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required data.']);
        return;
    }

    // Determine target dates list
    $dates = [];
    if ($is_range && $start_date && $end_date) {
        $dates = \App\Helpers\LeaveAttendanceSync::eachDateInRange($start_date, $end_date);
    } else {
        if (!$date) {
            echo json_encode(['status' => 'error', 'message' => 'Please specify a valid date.']);
            return;
        }
        $dates = [$date];
    }

    if (empty($dates)) {
        echo json_encode(['status' => 'error', 'message' => 'No valid dates selected.']);
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

            foreach ($dates as $target_date) {
                // Skip weekend days (Saturday & Sunday)
                if (\App\Helpers\LeaveAttendanceSync::isWeekendDate($target_date)) {
                    continue;
                }

                $status = 'ON TIME';
                $clock_in = null;
                $clock_out = null;
                $working_hours = '';

                if ($status_type === 'AUTO') {
                    $status = 'ON TIME';
                    $clock_in = $target_date . ' ' . $shift['start_time'];
                    $clock_out = $target_date . ' ' . $shift['end_time'];
                    
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

                // Check existing record
                $stmt = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND date = ?");
                $stmt->execute([$emp_id, $target_date]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $stmt = $pdo->prepare("UPDATE attendance SET clock_in = ?, clock_out = ?, working_hours = ?, status = ?, shift_id = ?, is_manual = 1 WHERE id = ?");
                    $stmt->execute([$clock_in, $clock_out, $working_hours, $status, $shift['id'], $existing['id']]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, clock_in, clock_out, working_hours, status, shift_id, is_manual) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
                    $stmt->execute([$emp_id, $target_date, $clock_in, $clock_out, $working_hours, $status, $shift['id']]);
                }
            }
        }

        $pdo->commit();
        \App\Helpers\WebSocketHelper::broadcast('attendance_updated');

        $msg = count($dates) > 1 
            ? "Bulk attendance processed for " . count($dates) . " days successfully." 
            : "Bulk attendance processed successfully.";

        echo json_encode(['status' => 'success', 'message' => $msg]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
    }
}

/**
 * handleFetchLogs — Employee self-service: returns the logged-in employee's own attendance.
 * Uses session user_id; employees cannot view other employees' data.
 */
function handleFetchLogs($pdo) {
    $emp_id = (int) ($_SESSION['user_id'] ?? 0);
    if (!$emp_id) {
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
        return;
    }

    $month = $_GET['month'] ?? getCurrentPayrollMonth();
    $range = getPayrollRange($month);
    $start_date = $range['start'];
    $end_date   = $range['end'];

    \App\Helpers\LeaveAttendanceSync::cleanupWeekendLeaveInRange($pdo, $emp_id, $start_date, $end_date);

    $stmt = $pdo->prepare("
        SELECT a.*,
               COALESCE(s.name, es.name) as shift_name,
               COALESCE(s.start_time, es.start_time) as shift_start,
               COALESCE(s.end_time, es.end_time) as shift_end
        FROM attendance a
        LEFT JOIN shifts s  ON a.shift_id = s.id
        LEFT JOIN employees emp ON a.employee_id = emp.id
        LEFT JOIN shifts es ON emp.shift_id = es.id
        WHERE a.employee_id = ? AND a.date BETWEEN ? AND ?
        ORDER BY a.date DESC
    ");
    $stmt->execute([$emp_id, $start_date, $end_date]);
    $logs = $stmt->fetchAll();

    foreach ($logs as &$log) {
        if (empty($log['shift_start']) && empty($log['shift_end'])) {
            if (($log['status'] ?? '') !== 'LEAVE' && ($log['status'] ?? '') !== 'HOLIDAY') {
                $log['status'] = '-';
            }
        }
    }

    echo json_encode(['status' => 'success', 'data' => $logs]);
}

/**
 * handleSaveMessage — Employee can add/update a message on a specific attendance record.
 */
function handleSaveMessage($pdo) {
    $emp_id = (int) ($_SESSION['user_id'] ?? 0);
    if (!$emp_id) {
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
        return;
    }

    $data       = json_decode(file_get_contents('php://input'), true);
    $date       = trim($data['date'] ?? '');
    $message    = trim($data['message'] ?? '');

    if (!$date) {
        echo json_encode(['status' => 'error', 'message' => 'Date is required.']);
        return;
    }

    // Convert formatted date string (e.g. "24 Jul, 2026") to YYYY-MM-DD if needed
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $ts = strtotime($date);
        if ($ts) {
            $date = date('Y-m-d', $ts);
        }
    }

    // Only allow editing own records
    $stmt = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND date = ?");
    $stmt->execute([$emp_id, $date]);
    $record = $stmt->fetch();

    if (!$record) {
        echo json_encode(['status' => 'error', 'message' => 'No attendance record found for this date.']);
        return;
    }

    $stmt = $pdo->prepare("UPDATE attendance SET message = ? WHERE id = ? AND employee_id = ?");
    $stmt->execute([$message, $record['id'], $emp_id]);

    echo json_encode(['status' => 'success', 'message' => 'Message saved successfully.']);
}

/**
 * handleCheckIn — Employee self check-in.
 */
function handleCheckIn($pdo) {
    $emp_id = (int) ($_SESSION['user_id'] ?? 0);
    if (!$emp_id) {
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
        return;
    }

    // Fetch employee shift
    $stmt = $pdo->prepare("SELECT s.* FROM employees e LEFT JOIN shifts s ON e.shift_id = s.id WHERE e.id = ?");
    $stmt->execute([$emp_id]);
    $shift = $stmt->fetch();

    // Determine logical date based on shift (overnight shift support)
    $logical_date = date('Y-m-d');
    if ($shift && !empty($shift['start_time']) && !empty($shift['end_time'])) {
        if (strtotime($shift['start_time']) > strtotime($shift['end_time'])) {
            // Overnight shift: before 12:00 PM (noon) belongs to yesterday
            if ((int)date('H') < 12) {
                $logical_date = date('Y-m-d', strtotime('-1 day'));
            }
        }
    }

    // Already checked in today?
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
    $stmt->execute([$emp_id, $logical_date]);
    $existing = $stmt->fetch();

    if ($existing && $existing['clock_in']) {
        echo json_encode(['status' => 'error', 'message' => 'Already checked in for today.']);
        return;
    }

    // Mark past missing weekdays as ABSENT or HOLIDAY before logging today's check-in
    \App\Helpers\LeaveAttendanceSync::fillMissingAttendance($pdo, $emp_id);

    // Re-fetch existing in case fillMissingAttendance has created a record for $logical_date (e.g. yesterday)
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
    $stmt->execute([$emp_id, $logical_date]);
    $existing = $stmt->fetch();

    $now = date('Y-m-d H:i:s');

    // Determine status: ON TIME or LATE IN (or - if no shift timing)
    $status = '-';
    if ($shift && !empty($shift['start_time'])) {
        $shift_start_ts = strtotime("$logical_date " . $shift['start_time']);
        $grace_ts       = $shift_start_ts + (((int)($shift['grace_time'] ?? 15)) * 60) + 59;
        $status         = (time() > $grace_ts) ? 'LATE IN' : 'ON TIME';
    }

    $shift_id = $shift['id'] ?? null;

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE attendance SET clock_in = ?, status = ?, shift_id = ? WHERE id = ?");
        $stmt->execute([$now, $status, $shift_id, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, clock_in, status, shift_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$emp_id, $logical_date, $now, $status, $shift_id]);
    }

    // WebSocket push to update active dashboards
    \App\Helpers\WebSocketHelper::broadcast('attendance_updated');

    // [LOG ACTIVITY]
    logActivity($emp_id, "Clock In", "Attendance", "Employee checked in for date: $logical_date at " . date('h:i A', strtotime($now)) . ".");

    echo json_encode(['status' => 'success', 'message' => 'Checked in successfully!']);
}

/**
 * handleCheckOut — Employee self check-out.
 */
function handleCheckOut($pdo) {
    $emp_id = (int) ($_SESSION['user_id'] ?? 0);
    if (!$emp_id) {
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
        return;
    }

    // Fetch employee shift to determine logical date
    $stmt = $pdo->prepare("SELECT s.* FROM employees e LEFT JOIN shifts s ON e.shift_id = s.id WHERE e.id = ?");
    $stmt->execute([$emp_id]);
    $shift = $stmt->fetch();

    $logical_date = date('Y-m-d');
    if ($shift && !empty($shift['start_time']) && !empty($shift['end_time'])) {
        if (strtotime($shift['start_time']) > strtotime($shift['end_time'])) {
            // Overnight shift: before 12:00 PM (noon) belongs to yesterday
            if ((int)date('H') < 12) {
                $logical_date = date('Y-m-d', strtotime('-1 day'));
            }
        }
    }

    $stmt = $pdo->prepare("SELECT a.*, s.start_time, s.end_time, s.halfday_hours FROM attendance a LEFT JOIN shifts s ON a.shift_id = s.id WHERE a.employee_id = ? AND a.date = ?");
    $stmt->execute([$emp_id, $logical_date]);
    $record = $stmt->fetch();

    if (!$record || !$record['clock_in']) {
        echo json_encode(['status' => 'error', 'message' => 'No check-in found for today.']);
        return;
    }

    if ($record['clock_out']) {
        echo json_encode(['status' => 'error', 'message' => 'Already checked out for today.']);
        return;
    }

    $now    = date('Y-m-d H:i:s');
    $in_ts  = strtotime($record['clock_in']);
    $out_ts = time();
    $diff   = $out_ts - $in_ts;
    if ($diff < 0) $diff = 0;

    $h = floor($diff / 3600);
    $m = floor(($diff % 3600) / 60);
    $working_hours = "{$h}h " . str_pad($m, 2, '0', STR_PAD_LEFT) . 'm';

    // Preserve status; only apply HALF DAY if halfday_hours is set
    $status = $record['status'] ?: '-';
    if (!empty($record['halfday_hours'])) {
        $halfday_minutes = (float)$record['halfday_hours'] * 60;
        if (($diff / 60) < $halfday_minutes) {
            $status = 'HALF DAY';
        }
    }

    $stmt = $pdo->prepare("UPDATE attendance SET clock_out = ?, working_hours = ?, status = ? WHERE id = ?");
    $stmt->execute([$now, $working_hours, $status, $record['id']]);

    // WebSocket push to update active dashboards
    \App\Helpers\WebSocketHelper::broadcast('attendance_updated');

    // [LOG ACTIVITY]
    logActivity($emp_id, "Clock Out", "Attendance", "Employee checked out for date: $logical_date working for $working_hours.");

    echo json_encode(['status' => 'success', 'message' => 'Checked out successfully!']);
}

/**
 * handleGetStatus — Returns current attendance state for the logged-in employee.
 */
function handleGetStatus($pdo) {
    $emp_id = (int) ($_SESSION['user_id'] ?? 0);
    if (!$emp_id) {
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
        return;
    }

    // Fetch employee shift to determine logical date
    $stmt = $pdo->prepare("SELECT s.* FROM employees e JOIN shifts s ON e.shift_id = s.id WHERE e.id = ?");
    $stmt->execute([$emp_id]);
    $shift = $stmt->fetch();

    $logical_date = date('Y-m-d');
    if ($shift && !empty($shift['start_time']) && !empty($shift['end_time'])) {
        if (strtotime($shift['start_time']) > strtotime($shift['end_time'])) {
            // Overnight shift: before 12:00 PM (noon) belongs to yesterday
            if ((int)date('H') < 12) {
                $logical_date = date('Y-m-d', strtotime('-1 day'));
            }
        }
    }

    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
    $stmt->execute([$emp_id, $logical_date]);
    $record = $stmt->fetch();

    $can_check_in  = !$record || !$record['clock_in'];
    $can_check_out = $record && $record['clock_in'] && !$record['clock_out'];
    $check_in_time = ($record && $record['clock_in']) ? date('h:i A', strtotime($record['clock_in'])) : null;

    echo json_encode([
        'status'        => 'success',
        'can_check_in'  => $can_check_in,
        'can_check_out' => $can_check_out,
        'check_in_time' => $check_in_time,
    ]);
}
