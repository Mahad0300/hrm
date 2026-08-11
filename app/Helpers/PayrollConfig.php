<?php
/**
 * Payroll Configuration Helper
 * From: includes/payroll_config.php
 */

namespace App\Helpers;

use PDO;

class PayrollConfig
{
    const DEFAULT_START_DAY = 21;
    const DEFAULT_END_DAY = 20;

    /**
     * Get setting from database
     */
    public static function getSetting(PDO $pdo, string $key, $default = '')
    {
        try {
            $stmt = $pdo->prepare("SELECT meta_value FROM settings WHERE meta_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return $val !== false ? $val : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Get payroll start day
     */
    public static function getStartDay(PDO $pdo): int
    {
        return (int)self::getSetting($pdo, 'payroll_start_day', self::DEFAULT_START_DAY);
    }

    /**
     * Get payroll end day
     */
    public static function getEndDay(PDO $pdo): int
    {
        return (int)self::getSetting($pdo, 'payroll_end_day', self::DEFAULT_END_DAY);
    }

    /**
     * Get payroll range for month
     * Returns array with 'start' and 'end' dates
     */
    public static function getPayrollRange(PDO $pdo, string $monthStr): array
    {
        // monthStr format: YYYY-MM
        $startDay = self::getStartDay($pdo);
        $endDay = self::getEndDay($pdo);

        $year = date('Y', strtotime($monthStr . '-01'));
        $month = date('m', strtotime($monthStr . '-01'));

        $daysInCurrentMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
        $actualEndDay = min($endDay, $daysInCurrentMonth);
        $endDate = "$year-$month-" . str_pad($actualEndDay, 2, '0', STR_PAD_LEFT);

        if ($startDay > $endDay) {
            // Cross-month cycle (e.g. 21st of prev month to 20th of current month)
            $prevMonthTime = strtotime("$year-$month-01 -1 month");
            $prevYear = date('Y', $prevMonthTime);
            $prevMonth = date('m', $prevMonthTime);

            $daysInPrevMonth = cal_days_in_month(CAL_GREGORIAN, (int)$prevMonth, (int)$prevYear);
            $actualStartDay = min($startDay, $daysInPrevMonth);

            $startDate = "$prevYear-$prevMonth-" . str_pad($actualStartDay, 2, '0', STR_PAD_LEFT);
        } else {
            // Same-month cycle (e.g. 1st to 30th of current month)
            $actualStartDay = min($startDay, $daysInCurrentMonth);
            $startDate = "$year-$month-" . str_pad($actualStartDay, 2, '0', STR_PAD_LEFT);
        }

        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }

    public static function getPayrollRolloverCutoffTimestamp(PDO $pdo, string $cycleEndDateYmd): int
    {
        $bufferHours = 4;
        $defaultCutoff = strtotime($cycleEndDateYmd . ' +1 day 12:00:00');

        try {
            $stmt = $pdo->query('SELECT MAX(end_time) FROM shifts WHERE deleted_at IS NULL');
            $latestEnd = $stmt ? $stmt->fetchColumn() : false;
            if ($latestEnd) {
                $dayAfter = date('Y-m-d', strtotime($cycleEndDateYmd . ' +1 day'));
                $cutoff = strtotime($dayAfter . ' ' . $latestEnd . " +{$bufferHours} hours");
                if ($cutoff !== false) {
                    return $cutoff;
                }
            }
        } catch (\Exception $e) {
            // ignore and fallback
        }

        return $defaultCutoff;
    }

    public static function getCurrentPayrollMonth(?string $date = null, ?PDO $pdo = null): string
    {
        $pdo = $pdo ?? \App\Core\Database::connection();

        $ts = $date !== null && $date !== '' ? strtotime($date) : time();
        if ($ts === false) {
            $ts = time();
        }

        $year = (int)date('Y', $ts);
        $month = (int)date('n', $ts);
        $day = (int)date('j', $ts);

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $endDay = min(self::getEndDay($pdo), $daysInMonth);
        $cycleEndDate = sprintf('%04d-%02d-%02d', $year, $month, $endDay);

        if ($day <= $endDay) {
            return sprintf('%04d-%02d', $year, $month);
        }

        if ($ts < self::getPayrollRolloverCutoffTimestamp($pdo, $cycleEndDate)) {
            return sprintf('%04d-%02d', $year, $month);
        }

        $month++;
        if ($month > 12) {
            $month = 1;
            $year++;
        }

        return sprintf('%04d-%02d', $year, $month);
    }

    public static function getPayrollAttendanceStats(PDO $pdo, int $employeeId, string $month, ?array $employeeInfo = null): array
    {
        $range = self::getPayrollRange($pdo, $month);
        $cStart = $range['start'];
        $cEnd = $range['end'];

        if (!$employeeInfo) {
            $stmt = $pdo->prepare("SELECT joining_date, status, deleted_at, updated_at FROM employees WHERE id = ?");
            $stmt->execute([$employeeId]);
            $employeeInfo = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        $joiningDate = !empty($employeeInfo['joining_date']) ? $employeeInfo['joining_date'] : null;
        $status = $employeeInfo['status'] ?? '';
        $deletedAt = !empty($employeeInfo['deleted_at']) ? date('Y-m-d', strtotime($employeeInfo['deleted_at'])) : null;

        // Effective Start Date (handle mid-cycle joining)
        $effectiveStart = $cStart;
        if ($joiningDate && $joiningDate > $cStart) {
            $effectiveStart = $joiningDate;
        }

        // Effective End Date (handle mid-cycle exit/termination)
        $effectiveEnd = $cEnd;
        if (in_array($status, ['Exit', 'Terminated']) || $deletedAt) {
            $exitDate = $deletedAt ?: (!empty($employeeInfo['updated_at']) ? date('Y-m-d', strtotime($employeeInfo['updated_at'])) : null);
            if ($exitDate && $exitDate < $cEnd) {
                $effectiveEnd = $exitDate;
            }
        }

        // Ongoing Cycle Cutoff: If cycle end date is in the future compared to today,
        // limit effective end date to yesterday (last completed day) for open shift safety (Option A)
        $today = date('Y-m-d');
        if ($today < $cEnd) {
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $effectiveEnd = min($effectiveEnd, $yesterday);
        }

        // Total calendar base days for month = 30
        $totalBaseDays = 30;
        $activeDays = 0;
        if ($effectiveStart <= $effectiveEnd) {
            $daysDiff = (int)floor((strtotime($effectiveEnd) - strtotime($effectiveStart)) / 86400) + 1;
            $activeDays = min($totalBaseDays, max(0, $daysDiff));
        }

        $unworkedDays = max(0, $totalBaseDays - $activeDays);

        $stmt = $pdo->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN status = 'ABSENT' THEN 1 ELSE 0 END), 0) AS absents,
                COALESCE(SUM(CASE WHEN status = 'LEAVE' THEN 1 ELSE 0 END), 0) AS leaves,
                COALESCE(SUM(CASE WHEN status = 'LATE IN' THEN 1 ELSE 0 END), 0) AS lates,
                COALESCE(SUM(CASE WHEN status = 'HALF DAY' THEN 1 ELSE 0 END), 0) AS halfdays
            FROM attendance
            WHERE employee_id = ?
              AND date BETWEEN ? AND ?
        ");
        $stmt->execute([$employeeId, $cStart, $cEnd]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $attAbsents = (int)($row['absents'] ?? 0);

        return [
            'start' => $cStart,
            'end' => $cEnd,
            'effective_start' => $effectiveStart,
            'effective_end' => $effectiveEnd,
            'active_days' => $activeDays,
            'unworked_days' => $unworkedDays,
            'absents' => $attAbsents + $unworkedDays,
            'attendance_absents' => $attAbsents,
            'leaves' => (int)($row['leaves'] ?? 0),
            'lates' => (int)($row['lates'] ?? 0),
            'halfdays' => (int)($row['halfdays'] ?? 0),
        ];
    }

    public static function buildPayrollSalaryBreakdown(float $grossSalary): array
    {
        $basic = round($grossSalary * 0.50, 2);
        $house_rent = round($grossSalary * 0.20, 2);
        $utility = round($grossSalary * 0.10, 2);
        $fuel = round($grossSalary * 0.05, 2);
        $mobile = round($grossSalary * 0.05, 2);
        // Absorb floating point rounding drift on final component
        $medical = round($grossSalary - ($basic + $house_rent + $utility + $fuel + $mobile), 2);

        return [
            'basic' => $basic,
            'house_rent' => $house_rent,
            'utility' => $utility,
            'fuel' => $fuel,
            'mobile' => $mobile,
            'medical' => $medical,
        ];
    }

    public static function calculatePayrollAttendanceDeduction(float $grossSalary, array $stats): array
    {
        $daysInMonth = (int)($stats['days_in_month'] ?? 30);
        if ($daysInMonth <= 0) {
            $daysInMonth = 30;
        }

        $oneDaySalary = round($grossSalary / $daysInMonth, 4);
        $absents = (float)($stats['absents'] ?? 0);
        $lates = (int)($stats['lates'] ?? 0);
        $halfdays = (float)($stats['halfdays'] ?? 0);

        $lateDeductionDays = (int)floor($lates / 3);
        $totalDeductionDays = $absents + $lateDeductionDays + ($halfdays * 0.5);
        $attendanceDeduction = round($totalDeductionDays * $oneDaySalary, 2);

        return [
            'one_day_salary' => $oneDaySalary,
            'late_deduction_days' => $lateDeductionDays,
            'total_deduction_days' => $totalDeductionDays,
            'attendance_deduction' => $attendanceDeduction,
        ];
    }
}
?>
