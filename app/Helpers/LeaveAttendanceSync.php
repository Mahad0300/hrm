<?php
/**
 * Leave & Attendance Sync Helper
 * From: includes/leave_attendance_sync.php
 */

namespace App\Helpers;

use PDO;
use DateTime;

class LeaveAttendanceSync
{
    /**
     * Check if employee has approved leave on date
     */
    public static function hasApprovedLeaveOnDate(PDO $pdo, int $employeeId, string $date, ?int $excludeLeaveId = null): bool
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
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Get each date in range
     */
    public static function eachDateInRange(string $startDate, string $endDate): array
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
     * Check if date is weekend (Saturday/Sunday)
     */
    public static function isWeekendDate(string $date): bool
    {
        $dw = (int)date('w', strtotime($date)); // 0 = Sun, 6 = Sat
        return $dw === 0 || $dw === 6;
    }

    /**
     * Count working days in leave range
     */
    public static function countWorkingLeaveDays(string $startDate, string $endDate): int
    {
        $count = 0;
        foreach (self::eachDateInRange($startDate, $endDate) as $date) {
            if (!self::isWeekendDate($date)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Sync approved leaves to attendance
     */
    public static function syncLeaveToAttendance(PDO $pdo, int $employeeId, string $startDate, string $endDate): void
    {
        foreach (self::eachDateInRange($startDate, $endDate) as $date) {
            if (self::isWeekendDate($date)) {
                continue;
            }

            // Check if attendance record exists
            $stmt = $pdo->prepare("
                SELECT id FROM attendance 
                WHERE employee_id = ? AND date = ?
            ");
            $stmt->execute([$employeeId, $date]);
            $attendanceId = $stmt->fetchColumn();

            if ($attendanceId) {
                // Update existing
                $updateStmt = $pdo->prepare("
                    UPDATE attendance 
                    SET status = 'LEAVE', clock_in = NULL, clock_out = NULL, working_hours = ''
                    WHERE id = ?
                ");
                $updateStmt->execute([$attendanceId]);
            } else {
                // Insert new
                $insertStmt = $pdo->prepare("
                    INSERT INTO attendance (employee_id, date, status, clock_in, clock_out, working_hours)
                    VALUES (?, ?, 'LEAVE', NULL, NULL, '')
                ");
                $insertStmt->execute([$employeeId, $date]);
            }
        }
    }

    public static function sumApprovedLeaveWorkingDays(
        PDO $pdo,
        ?int $employeeId = null,
        ?int $leaveTypeId = null,
        ?string $rangeStart = null,
        ?string $rangeEnd = null
    ): int {
        $sql = "SELECT start_date, end_date FROM leave_requests WHERE status = 'Approved'";
        $params = [];

        if ($employeeId !== null) {
            $sql .= ' AND employee_id = ?';
            $params[] = $employeeId;
        }
        if ($leaveTypeId !== null) {
            $sql .= ' AND leave_type_id = ?';
            $params[] = $leaveTypeId;
        }
        if ($rangeStart !== null && $rangeEnd !== null) {
            $sql .= ' AND start_date <= ? AND end_date >= ?';
            $params[] = $rangeEnd;
            $params[] = $rangeStart;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $total = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $from = $row['start_date'];
            $to = $row['end_date'];
            if ($rangeStart !== null && $from < $rangeStart) {
                $from = $rangeStart;
            }
            if ($rangeEnd !== null && $to > $rangeEnd) {
                $to = $rangeEnd;
            }
            if ($from <= $to) {
                $total += self::countWorkingLeaveDays($from, $to);
            }
        }

        return $total;
    }

    public static function employeeHasApprovedLeaveOnDate(PDO $pdo, int $employeeId, string $date, ?int $excludeLeaveId = null): bool
    {
        $sql = "
            SELECT 1 FROM leave_requests
            WHERE employee_id = ? AND status = 'Approved'
              AND ? BETWEEN start_date AND end_date
        ";
        $params = [$employeeId, $date];
        if ($excludeLeaveId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeLeaveId;
        }
        $sql .= ' LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public static function clearWeekendLeaveAttendance(PDO $pdo, int $employeeId, string $date): void
    {
        if (!self::isWeekendDate($date)) {
            return;
        }

        $stmt = $pdo->prepare("\n            DELETE FROM attendance\n            WHERE employee_id = ? AND date = ? AND status = 'LEAVE' AND clock_in IS NULL\n        ");
        $stmt->execute([$employeeId, $date]);
    }

    public static function cleanupWeekendLeaveInRange(PDO $pdo, int $employeeId, string $startDate, string $endDate): void
    {
        $stmt = $pdo->prepare("\n            DELETE FROM attendance\n            WHERE employee_id = ? AND status = 'LEAVE' AND clock_in IS NULL\n              AND date BETWEEN ? AND ?\n              AND DAYOFWEEK(date) IN (1, 7)\n        ");
        $stmt->execute([$employeeId, $startDate, $endDate]);
    }

    public static function syncApprovedLeaveToAttendance(PDO $pdo, int $leaveRequestId): void
    {
        $stmt = $pdo->prepare("\n            SELECT lr.id, lr.employee_id, lr.start_date, lr.end_date, lr.status,\n                   lt.name AS leave_type_name, e.shift_id\n            FROM leave_requests lr\n            JOIN leave_types lt ON lr.leave_type_id = lt.id\n            JOIN employees e ON lr.employee_id = e.id\n            WHERE lr.id = ?\n        ");
        $stmt->execute([$leaveRequestId]);
        $leave = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$leave || $leave['status'] !== 'Approved') {
            return;
        }

        self::syncLeaveToAttendance($pdo, (int)$leave['employee_id'], $leave['start_date'], $leave['end_date']);
    }

    public static function revertRejectedLeaveFromAttendance(PDO $pdo, int $leaveRequestId): void
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

        $deleteStmt = $pdo->prepare("
            DELETE FROM attendance
            WHERE employee_id = ? 
              AND status = 'LEAVE'
              AND clock_in IS NULL
              AND date BETWEEN ? AND ?
        ");
        $deleteStmt->execute([
            (int)$leave['employee_id'],
            $leave['start_date'],
            $leave['end_date']
        ]);
    }

    /**
     * Fill missing attendance records as ABSENT or HOLIDAY for past weekdays
     * from the last attendance record (or joining date) up to the day before today.
     */
    public static function fillMissingAttendance(PDO $pdo, int $employeeId): void
    {
        // 1. Fetch employee shift, joining date, and creation time
        $stmt = $pdo->prepare("SELECT role, shift_id, joining_date, created_at FROM employees WHERE id = ?");
        $stmt->execute([$employeeId]);
        $emp = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$emp) {
            return;
        }
        $shiftId = $emp['shift_id'] ? (int)$emp['shift_id'] : null;

        // 2. Determine start date
        if (!empty($emp['joining_date'])) {
            $startDate = $emp['joining_date'];
        } else {
            $startDate = date('Y-m-d', strtotime('-30 days'));
        }

        // Limit the back-fill range to not go further back than 30 days ago (for performance and safety)
        $limitDate = date('Y-m-d', strtotime('-30 days'));
        if ($startDate < $limitDate) {
            $startDate = $limitDate;
        }

        $yesterdayStr = date('Y-m-d', strtotime('-1 day'));

        // If start date is after yesterday, nothing to fill
        if ($startDate > $yesterdayStr) {
            return;
        }

        // Fetch all existing attendance dates for this employee in the range
        $stmt = $pdo->prepare("
            SELECT date FROM attendance 
            WHERE employee_id = ? AND date BETWEEN ? AND ?
        ");
        $stmt->execute([$employeeId, $startDate, $yesterdayStr]);
        $existingDates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $existingSet = array_flip($existingDates);

        $dates = self::eachDateInRange($startDate, $yesterdayStr);

        foreach ($dates as $date) {
            // Skip weekends
            if (self::isWeekendDate($date)) {
                continue;
            }

            // If a record already exists, skip
            if (isset($existingSet[$date])) {
                continue;
            }

            // Check if there is a holiday event in events table
            $stmt = $pdo->prepare("SELECT 1 FROM events WHERE event_date = ? AND category = 'Holiday' LIMIT 1");
            $stmt->execute([$date]);
            $isHoliday = (bool)$stmt->fetchColumn();

            if ($isHoliday) {
                // Insert HOLIDAY record
                $ins = $pdo->prepare("INSERT INTO attendance (employee_id, date, status, clock_in, clock_out, working_hours, shift_id) VALUES (?, ?, 'HOLIDAY', NULL, NULL, '', ?)");
                $ins->execute([$employeeId, $date, $shiftId]);
                continue;
            }

            // Check if they have an approved leave
            if (self::hasApprovedLeaveOnDate($pdo, $employeeId, $date)) {
                // Insert LEAVE record
                $ins = $pdo->prepare("INSERT INTO attendance (employee_id, date, status, clock_in, clock_out, working_hours, shift_id) VALUES (?, ?, 'LEAVE', NULL, NULL, '', ?)");
                $ins->execute([$employeeId, $date, $shiftId]);
                continue;
            }

            // Insert ABSENT record (Skip for Admin accounts or employees without assigned shift)
            if (($emp['role'] ?? '') === 'Admin' || $shiftId === null) {
                continue;
            }

            $ins = $pdo->prepare("INSERT INTO attendance (employee_id, date, status, clock_in, clock_out, working_hours, shift_id) VALUES (?, ?, 'ABSENT', NULL, NULL, '', ?)");
            $ins->execute([$employeeId, $date, $shiftId]);
        }
    }
}
?>
