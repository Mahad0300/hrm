<?php
use App\Core\Database;
use App\Core\Middleware;
use App\Core\View;
use App\Helpers\HRPermissions;
use App\Helpers\PayrollConfig;

$pdo = $pdo ?? Database::connection();
Middleware::protectModule(['HR']);

HRPermissions::seedPermissionsIfEmpty($pdo);
$hr_current_page_key = HRPermissions::resolvePageKey($_SERVER['REQUEST_URI'] ?? '');
HRPermissions::enforcePageAccess($pdo, $hr_current_page_key);
$hr_user_permissions = HRPermissions::isHrPortalUser() ? HRPermissions::fetchAllPermissions($pdo) : [];
$hr_permissions_revision = HRPermissions::permissionsRevision($pdo);
$hrmJs = View::jsConfig();
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="<?= \App\Helpers\CSRFToken::generate() ?>">
	<title>HRM Dashboard | Rtg Corp</title>
	<base href="<?= View::e((defined('BASE_URL') ? BASE_URL : '') . '/hr/') ?>">
	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?= View::asset('css/hr/style.css') ?>?v=<?= time() ?>">
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
		window.HRM = <?= json_encode($hrmJs, JSON_UNESCAPED_SLASHES) ?>;
		window.HRM.url = (path) => window.HRM.baseUrl.replace(/\/$/, '') + '/' + String(path || '').replace(/^\//, '');
		window.HRM.api = (path) => window.HRM.url('/assets/api/' + String(path || '').replace(/^\//, ''));
		window.HRM_CONFIG = {
			payroll_start_day: <?= PayrollConfig::getStartDay($pdo) ?>,
			payroll_end_day: <?= PayrollConfig::getEndDay($pdo) ?>,
			current_payroll_month: '<?= PayrollConfig::getCurrentPayrollMonth(null, $pdo) ?>',
			user_role: <?= json_encode($_SESSION['user_role'] ?? '') ?>,
			page_key: <?= json_encode($hr_current_page_key) ?>,
			permissions_revision: <?= (int) $hr_permissions_revision ?>,
			permissions: <?= json_encode($hr_user_permissions) ?>,
			permissions_meta: <?= json_encode(HRPermissions::permissionsJsConfig()) ?>,
			hr_no_portal_access: <?= !empty($GLOBALS['hr_access_denied']) ? 'true' : 'false' ?>
		};
		window.currentUserId = <?= $_SESSION['user_id'] ?? 0 ?>;
		window.wsPort = <?= defined('WS_PORT') ? WS_PORT : 6001 ?>;
	</script>
	<!-- Global WebSockets Client -->
	<script src="<?= View::asset('js/shared/utils.js') ?>?v=<?= time() ?>"></script>
	<script src="<?= View::asset('js/shared/websocket.js') ?>?v=<?= time() ?>"></script>
</head>

<body>
	<div class="admin-container">

