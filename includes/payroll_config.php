<?php
// includes/payroll_config.php

// Function to get a setting from the database
if (!function_exists('getHRMSetting')) {
    function getHRMSetting($pdo, $key, $default = '') {
        try {
            $stmt = $pdo->prepare("SELECT meta_value FROM settings WHERE meta_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return $val !== false ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

// Global constants - Ab yeh dynamic hain
// Note: We ensure $pdo is available
if (!isset($pdo)) {
    require_once __DIR__ . '/db_connect.php';
}

if (isset($pdo)) {
    if (!defined('PAYROLL_START_DAY')) {
        define('PAYROLL_START_DAY', (int)getHRMSetting($pdo, 'payroll_start_day', 21));
    }
    if (!defined('PAYROLL_END_DAY')) {
        define('PAYROLL_END_DAY', (int)getHRMSetting($pdo, 'payroll_end_day', 20));
    }
} else {
    // Fallback if PDO is not initialized
    if (!defined('PAYROLL_START_DAY')) define('PAYROLL_START_DAY', 21);
    if (!defined('PAYROLL_END_DAY')) define('PAYROLL_END_DAY', 20);
}

/**
 * Calculates the start and end dates for a payroll month
 * Example: for '2026-03', returns ['start' => '2026-02-21', 'end' => '2026-03-20']
 */
if (!function_exists('getPayrollRange')) {
    function getPayrollRange($month_str) {
        // month_str format: YYYY-MM
        $year = date('Y', strtotime($month_str . '-01'));
        $month = date('m', strtotime($month_str . '-01'));

        // Safe way to get previous month without date overflow
        $prev_month_time = strtotime("$year-$month-01 -1 month");
        $prev_year = date('Y', $prev_month_time);
        $prev_month = date('m', $prev_month_time);
        
        // Find the max days in the previous month (e.g. Feb has 28 or 29)
        $days_in_prev_month = cal_days_in_month(CAL_GREGORIAN, $prev_month, $prev_year);
        
        // Make sure we don't pick a day that doesn't exist (e.g. if start day is 31, but Feb has 28)
        $start_day = min(PAYROLL_START_DAY, $days_in_prev_month);
        
        $start_date = "$prev_year-$prev_month-" . str_pad($start_day, 2, '0', STR_PAD_LEFT);
        
        // End date is PAYROLL_END_DAY of the CURRENT month
        // We also enforce max days logic for end date to be extremely safe
        $days_in_curr_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $end_day = min(PAYROLL_END_DAY, $days_in_curr_month);
        
        $end_date = "$year-$month-" . str_pad($end_day, 2, '0', STR_PAD_LEFT);

        return [
            'start' => $start_date,
            'end' => $end_date
        ];
    }
}

/**
 * When payroll month advances after cycle end (overnight shifts).
 * Same buffer as attendance logical date: latest shift end + N hours on the day after payroll end.
 */
if (!function_exists('getPayrollRolloverCutoffTimestamp')) {
    function getPayrollRolloverCutoffTimestamp(string $cycleEndDateYmd): int
    {
        $bufferHours = 4;
        $defaultCutoff = strtotime($cycleEndDateYmd . ' +1 day 12:00:00');

        global $pdo;
        if (!isset($pdo)) {
            return $defaultCutoff;
        }

        try {
            $stmt = $pdo->query("SELECT MAX(end_time) FROM shifts WHERE deleted_at IS NULL");
            $latestEnd = $stmt ? $stmt->fetchColumn() : false;
            if ($latestEnd) {
                $dayAfter = date('Y-m-d', strtotime($cycleEndDateYmd . ' +1 day'));
                $cutoff = strtotime($dayAfter . ' ' . $latestEnd . " +{$bufferHours} hours");
                if ($cutoff !== false) {
                    return $cutoff;
                }
            }
        } catch (Exception $e) {
            // fall through
        }

        return $defaultCutoff;
    }
}

/**
 * Active payroll month key (YYYY-MM) = month in which the cycle ENDS.
 * Example (start 21, end 20): on 2026-05-22 → "2026-06" (21 May – 20 Jun).
 * On 2026-05-15 → "2026-05" (21 Apr – 20 May).
 *
 * After payroll end date, calendar stays on that cycle until overnight work finishes
 * (day after end, until max shift end + 4h — e.g. end 20 May, shift ends 21 May morning → still "2026-05").
 */
if (!function_exists('getCurrentPayrollMonth')) {
    function getCurrentPayrollMonth(?string $date = null): string
    {
        $ts = $date !== null && $date !== '' ? strtotime($date) : time();
        if ($ts === false) {
            $ts = time();
        }

        $year = (int) date('Y', $ts);
        $month = (int) date('n', $ts);
        $day = (int) date('j', $ts);

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $endDay = min(PAYROLL_END_DAY, $daysInMonth);
        $cycleEndDate = sprintf('%04d-%02d-%02d', $year, $month, $endDay);

        // Still inside the cycle (on or before payroll end day)
        if ($day <= $endDay) {
            return sprintf('%04d-%02d', $year, $month);
        }

        // After end day: keep same payroll month until overnight rollover cutoff
        if ($ts < getPayrollRolloverCutoffTimestamp($cycleEndDate)) {
            return sprintf('%04d-%02d', $year, $month);
        }

        $month++;
        if ($month > 12) {
            $month = 1;
            $year++;
        }

        return sprintf('%04d-%02d', $year, $month);
    }
}
