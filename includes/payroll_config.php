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

        // Start date is PAYROLL_START_DAY of the PREVIOUS month
        $start_date = date('Y-m-d', strtotime("-1 month", strtotime("$year-$month-" . PAYROLL_START_DAY)));
        
        // End date is PAYROLL_END_DAY of the CURRENT month
        $end_date = "$year-$month-" . str_pad(PAYROLL_END_DAY, 2, '0', STR_PAD_LEFT);

        return [
            'start' => $start_date,
            'end' => $end_date
        ];
    }
}
