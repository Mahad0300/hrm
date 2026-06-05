<?php
require_once dirname(__DIR__, 2) . '/includes/middleware.php';
require_once dirname(__DIR__, 2) . '/includes/payroll_config.php';
protectModule(['Admin', 'HR']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRM Dashboard | Rtg Corp</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (!empty($load_apexcharts)): ?>
        <!-- ApexCharts (admin dashboard — set $load_apexcharts before including this file) -->
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <?php endif; ?>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Toastify-js CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Global HRM Config for JS -->
    <script>
        window.HRM_CONFIG = {
            payroll_start_day: <?= defined('PAYROLL_START_DAY') ? PAYROLL_START_DAY : 21 ?>,
            payroll_end_day: <?= defined('PAYROLL_END_DAY') ? PAYROLL_END_DAY : 20 ?>,
            current_payroll_month: '<?= getCurrentPayrollMonth() ?>'
        };
    </script>
</head>

<body>
    <div class="admin-container">