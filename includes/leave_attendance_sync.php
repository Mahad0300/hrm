<?php
/**
 * Sync approved leave requests with attendance (status = LEAVE).
 */

function employeeHasApprovedLeaveOnDate(PDO $pdo, int $employeeId, string $date, ?int $excludeLeaveId = null): bool
{
    $sql = "
        SELECT 1 FROM leave_requests
        WHERE employee_id = ? AND status = 'Approved'
          AND ? BETWEEN start_date AND end_date
    ";
    $params = [$employeeId, $date];
    if ($excludeLeaveId !== null) {
        $sql .= " AND id != ?";
        $params[] = $excludeLeaveId;
    }
    $sql .= " LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (bool) $stmt->fetchColumn();
}

function eachDateInRange(string $startDate, string $endDate): array
{
    $dates = [];
    $current = new DateTime($startDate);
    $end = new DateTime($endDate);
    while ($current <= $end) {
        $dates[] = $current->format('Y-m-d');
        $current->modify('+1 day');
    }
    return $dates;
}

/**
 * After leave is Approved: create/update attendance rows as LEAVE for each day in range.
 */
function syncApprovedLeaveToAttendance(PDO $pdo, int $leaveRequestId): void
{
    $stmt = $pdo->prepare("
        SELECT lr.id, lr.employee_id, lr.start_date, lr.end_date, lr.status,
               lt.name AS leave_type_name, e.shift_id
        FROM leave_requests lr
        JOIN leave_types lt ON lr.leave_type_id = lt.id
        JOIN employees e ON lr.employee_id = e.id
        WHERE lr.id = ?
    ");
    $stmt->execute([$leaveRequestId]);
    $leave = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$leave || $leave['status'] !== 'Approved') {
        return;
    }

    $message = 'Approved ' . ($leave['leave_type_name'] ?? 'Leave');
    $shiftId = $leave['shift_id'] ?: null;

    foreach (eachDateInRange($leave['start_date'], $leave['end_date']) as $date) {
        upsertLeaveAttendanceDay($pdo, (int) $leave['employee_id'], $date, $shiftId, $message);
    }
}

/**
 * After a previously Approved leave is Rejected: remove LEAVE rows not covered by other approved leave.
 */
function revertRejectedLeaveFromAttendance(PDO $pdo, int $leaveRequestId): void
{
    $stmt = $pdo->prepare("
        SELECT employee_id, start_date, end_date
        FROM leave_requests
        WHERE id = ?
    ");
    $stmt->execute([$leaveRequestId]);
    $leave = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$leave) {
        return;
    }

    $employeeId = (int) $leave['employee_id'];

    foreach (eachDateInRange($leave['start_date'], $leave['end_date']) as $date) {
        if (employeeHasApprovedLeaveOnDate($pdo, $employeeId, $date, $leaveRequestId)) {
            continue;
        }

        $stmt = $pdo->prepare("
            SELECT id, status FROM attendance
            WHERE employee_id = ? AND date = ? AND status = 'LEAVE'
            LIMIT 1
        ");
        $stmt->execute([$employeeId, $date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            continue;
        }

        $del = $pdo->prepare("DELETE FROM attendance WHERE id = ?");
        $del->execute([$row['id']]);
    }
}

function upsertLeaveAttendanceDay(PDO $pdo, int $employeeId, string $date, ?int $shiftId, string $message): void
{
    $stmt = $pdo->prepare("SELECT id, status FROM attendance WHERE employee_id = ? AND date = ? LIMIT 1");
    $stmt->execute([$employeeId, $date]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $upd = $pdo->prepare("
            UPDATE attendance
            SET status = 'LEAVE', clock_in = NULL, clock_out = NULL, working_hours = '—',
                message = ?, shift_id = COALESCE(?, shift_id)
            WHERE id = ?
        ");
        $upd->execute([$message, $shiftId, $existing['id']]);
        return;
    }

    $ins = $pdo->prepare("
        INSERT INTO attendance (employee_id, date, shift_id, clock_in, clock_out, working_hours, status, message)
        VALUES (?, ?, ?, NULL, NULL, '—', 'LEAVE', ?)
    ");
    $ins->execute([$employeeId, $date, $shiftId, $message]);
}
