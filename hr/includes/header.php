<?php
require_once dirname(__DIR__, 2) . '/includes/middleware.php';
require_once dirname(__DIR__, 2) . '/includes/payroll_config.php';
require_once dirname(__DIR__, 2) . '/includes/db_connect.php';
require_once dirname(__DIR__, 2) . '/includes/access_control_helper.php';
protectModule(['Admin', 'HR']);

hrSeedPermissionsIfEmpty($pdo);
$hr_current_page_key = hrResolvePageKey(basename($_SERVER['PHP_SELF'] ?? ''));
hrEnforcePageAccess($pdo, $hr_current_page_key);
$hr_user_permissions = isHrPortalUser() ? hrFetchAllPermissions($pdo) : [];
$hr_permissions_revision = hrPermissionsRevision($pdo);
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
            current_payroll_month: '<?= getCurrentPayrollMonth() ?>',
            user_role: <?= json_encode($_SESSION['user_role'] ?? '') ?>,
            page_key: <?= json_encode($hr_current_page_key) ?>,
            permissions_revision: <?= (int) $hr_permissions_revision ?>,
            permissions: <?= json_encode($hr_user_permissions) ?>,
            hr_no_portal_access: <?= !empty($GLOBALS['hr_access_denied']) ? 'true' : 'false' ?>
        };
    </script>
</head>

<body>
    <div class="admin-container">