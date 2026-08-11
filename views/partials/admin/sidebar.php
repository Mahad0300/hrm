<?php
use App\Core\View;
use App\Helpers\HRPermissions;

$currentPageKey = HRPermissions::resolvePageKey() ?? 'index';
$prefix = View::basePath();
if ($prefix === '/') {
	$prefix = '';
}

$user_biometric_id = null;
if (!empty($_SESSION['user_id'])) {
	$stmt_bio = $pdo->prepare("SELECT biometric_id FROM employees WHERE id = ? AND deleted_at IS NULL LIMIT 1");
	$stmt_bio->execute([$_SESSION['user_id']]);
	$user_bio = $stmt_bio->fetchColumn();
	$user_biometric_id = !empty($user_bio) ? trim((string)$user_bio) : null;
}
?>
<aside class="sidebar" id="sidebar">
	<div class="sidebar-logo">
		<a href="<?= View::to('dashboard') ?>" class="sidebar-logo-mark" aria-label="HRM Admin dashboard">
			<img src="<?= View::image('loginimage/logo.png') ?>" alt="Richmond Tech Group" class="sidebar-logo-img" width="44" height="44">
		</a>
		<div class="sidebar-brand-text">
			<span class="sidebar-brand-name">HRM</span>
			<span class="sidebar-brand-tag">Admin portal</span>
		</div>
	</div>
    
	<div class="sidebar-menu custom-scrollbar">
		<div class="menu-label">Main Menu</div>
		<div class="menu-item">
			<a href="<?= View::to('dashboard') ?>" class="menu-link <?= ($currentPageKey === 'index') ? 'active' : '' ?>">
				<i data-lucide="layout-dashboard" size="18"></i>
				<span>Dashboard</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::to('sheets') ?>" class="menu-link <?= ($currentPageKey === 'sheets') ? 'active' : '' ?>">
				<i data-lucide="table-properties" size="18"></i>
				<span>Sheets</span>
			</a>
		</div>
        
		<div class="menu-label">Organization</div>
		<div class="menu-item">
			<a href="<?= View::to('employees') ?>" class="menu-link <?= ($currentPageKey === 'employees') ? 'active' : '' ?>">
				<i data-lucide="users" size="18"></i>
				<span>Employees</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::to('attendance') ?>" class="menu-link <?= ($currentPageKey === 'attendance') ? 'active' : '' ?>">
				<i data-lucide="calendar-check" size="18"></i>
				<span>Attendance</span>
			</a>
		</div>
		<?php if (!empty($user_biometric_id)): ?>
		<div class="menu-item">
			<a href="<?= View::url('daily-attendance') ?>" class="menu-link <?= ($currentPageKey === 'daily-attendance') ? 'active' : '' ?>">
				<i data-lucide="clock" size="18"></i>
				<span>Daily Attendance</span>
			</a>
		</div>
		<?php endif; ?>
		<div class="menu-item">
			<a href="<?= View::to('leave') ?>" class="menu-link <?= ($currentPageKey === 'leave-management') ? 'active' : '' ?>">
				<i data-lucide="clock" size="18"></i>
				<span>Leave Management</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::to('new-joining') ?>" class="menu-link <?= ($currentPageKey === 'new-joining') ? 'active' : '' ?>">
				<i data-lucide="user-plus" size="18"></i>
				<span>New Joining</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::to('hierarchy') ?>" class="menu-link <?= ($currentPageKey === 'hierarchy') ? 'active' : '' ?>">
				<i data-lucide="network" size="18"></i>
				<span>Hierarchy</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::to('kpi') ?>" class="menu-link <?= ($currentPageKey === 'kpi-management') ? 'active' : '' ?>">
				<i data-lucide="line-chart" size="18"></i>
				<span>KPI Management</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::to('events') ?>" class="menu-link <?= ($currentPageKey === 'event-calendar') ? 'active' : '' ?>">
				<i data-lucide="calendar" size="18"></i>
				<span>Event Calendar</span>
			</a>
		</div>
        
		<!-- 
		<div class="menu-label">Job Management</div>
		<div class="menu-item has-submenu <?= in_array($currentPageKey, ['job-list', 'create-job', 'job-candidates', 'walk-in-candidates', 'candidate-detail', 'interviews'], true) ? 'active open' : '' ?>">
			<a href="javascript:void(0)" class="menu-link submenu-toggle">
				<i data-lucide="briefcase" size="18"></i>
				<span>Job Management</span>
				<i data-lucide="chevron-down" size="14" class="chevron"></i>
			</a>
			<div class="submenu">
				<a href="<?= View::to('jobs') ?>" class="submenu-link <?= ($currentPageKey === 'job-list') ? 'active' : '' ?>">
					<i data-lucide="list" size="14"></i>
					<span>Job Postings</span>
				</a>
				<a href="<?= View::to('jobs.create') ?>" class="submenu-link <?= ($currentPageKey === 'create-job') ? 'active' : '' ?>">
					<i data-lucide="plus-circle" size="14"></i>
					<span>Create New Job</span>
				</a>
				<a href="<?= View::to('recruitment') ?>" class="submenu-link <?= ($currentPageKey === 'job-candidates') ? 'active' : '' ?>">
					<i data-lucide="users" size="14"></i>
					<span>Candidate Pool</span>
				</a>
				<a href="<?= View::to('recruitment.walk-in') ?>" class="submenu-link <?= ($currentPageKey === 'walk-in-candidates') ? 'active' : '' ?>">
					<i data-lucide="user-check" size="14"></i>
					<span>Walk-In Candidates</span>
				</a>
				<a href="<?= View::to('recruitment.interviews') ?>" class="submenu-link <?= ($currentPageKey === 'interviews') ? 'active' : '' ?>">
					<i data-lucide="calendar" size="14"></i>
					<span>Interviews</span>
				</a>
			</div>
		</div>
		-->

		<div class="menu-label">Administration</div>
		<div class="menu-item">
			<a href="<?= View::to('payroll') ?>" class="menu-link <?= ($currentPageKey === 'payroll') ? 'active' : '' ?>">
				<i data-lucide="banknote" size="18"></i>
				<span>Payroll</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::to('activity-logs') ?>" class="menu-link <?= ($currentPageKey === 'activity-logs') ? 'active' : '' ?>">
				<i data-lucide="history" size="18"></i>
				<span>Activity Logs</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::to('announcements') ?>" class="menu-link <?= ($currentPageKey === 'announcements') ? 'active' : '' ?>">
				<i data-lucide="megaphone" size="18"></i>
				<span>Announcements</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::to('notifications') ?>" class="menu-link <?= ($currentPageKey === 'notifications') ? 'active' : '' ?>">
				<i data-lucide="bell" size="18"></i>
				<span>Notifications</span>
				<span class="badge badge-primary badge-pill ml-auto hidden" id="notiSidebarBadge">0</span>
			</a>
		</div>
        
		<div class="menu-item">
			<a href="<?= View::to('it-support') ?>" class="menu-link <?= ($currentPageKey === 'it-support') ? 'active' : '' ?>">
				<i data-lucide="headset" size="18"></i>
				<span>IT Helpdesk</span>
			</a>
		</div>
		<div class="menu-label">System</div>
		<?php
		$stmtChatrox = $pdo->query("SELECT meta_value FROM settings WHERE meta_key = 'chatrox_url' LIMIT 1");
		$chatroxUrlVal = ($stmtChatrox && ($v = $stmtChatrox->fetchColumn()) !== false && !empty($v)) ? rtrim((string)$v, '/') : 'http://localhost/chatrox';
		?>
		<div class="menu-item">
			<a href="<?= htmlspecialchars($chatroxUrlVal) ?>" target="_blank" class="menu-link" style="position:relative;">
				<img src="<?= htmlspecialchars($chatroxUrlVal) ?>/assets/images/logo.png" alt="Chatrox" style="width:18px;height:18px;object-fit:contain;flex-shrink:0;" onerror="this.style.display='none'">
				<span>Chatrox</span>
				<span style="width:7px;height:7px;background:#22c55e;border-radius:50%;display:inline-block;flex-shrink:0;margin-left:auto;"></span>
			</a>
		</div>

		<div class="menu-item has-submenu <?= in_array($currentPageKey, ['shifts', 'department-management', 'role-management', 'policy-management', 'payroll-settings', 'hierarchy-settings'], true) ? 'active open' : '' ?>">
			<a href="javascript:void(0)" class="menu-link submenu-toggle">
				<i data-lucide="settings" size="18"></i>
				<span>Settings</span>
				<i data-lucide="chevron-down" size="14" class="chevron"></i>
			</a>
			<div class="submenu">
				<a href="<?= View::to('shifts') ?>" class="submenu-link <?= ($currentPageKey === 'shifts') ? 'active' : '' ?>">
					<i data-lucide="plus-circle" size="14"></i>
					<span>Add Shift</span>
				</a> 
				<a href="<?= View::to('departments') ?>" class="submenu-link <?= ($currentPageKey === 'department-management') ? 'active' : '' ?>">
					<i data-lucide="building-2" size="14"></i>
					<span>Dept Management</span>
				</a>
				<a href="<?= View::to('roles') ?>" class="submenu-link <?= ($currentPageKey === 'role-management') ? 'active' : '' ?>">
					<i data-lucide="shield-check" size="14"></i>
					<span>Access Control</span>
				</a>
				<a href="<?= View::to('policy-management') ?>" class="submenu-link <?= ($currentPageKey === 'policy-management') ? 'active' : '' ?>">
					<i data-lucide="file-text" size="14"></i>
					<span>Policy Management</span>
				</a>
				<a href="<?= View::to('payroll.settings') ?>" class="submenu-link <?= ($currentPageKey === 'payroll-settings') ? 'active' : '' ?>">
					<i data-lucide="calculator" size="14"></i>
					<span>Payroll Cycle</span>
				</a>
				<a href="<?= View::to('hierarchy.settings') ?>" class="submenu-link <?= ($currentPageKey === 'hierarchy-settings') ? 'active' : '' ?>">
					<i data-lucide="network" size="14"></i>
					<span>Hierarchy Settings</span>
				</a>
                <a href="<?= View::to('connected-apps') ?>" class="submenu-link <?= ($currentPageKey === 'connected-apps') ? 'active' : '' ?>">
                    <i data-lucide="plug-zap" size="14"></i>
                    <span>Connected Apps</span>
                </a>
			</div>
		</div>
	</div>

	<div class="sidebar-footer">
		<div class="menu-item m-0">
			<a href="<?= View::to('logout') ?>" class="menu-link danger menu-link--logout-stack">
				<i data-lucide="log-out" size="18"></i>
				<span class="menu-link__logout-stack">
					<span class="menu-link__logout-title">Logout</span>
					<span class="menu-link__logout-sub">Sign out of your account</span>
				</span>
			</a>
		</div>
	</div>
</aside>
<main class="main-wrapper">
	<header class="top-bar">
		<div class="page-title">
			<button class="icon-btn mobile-menu-toggle hidden" id="menuToggle">
				<i data-lucide="menu"></i>
			</button>
			<div class="title-meta">
				<h2><?= isset($page_title) ? $page_title : ucwords(str_replace('-', ' ', $currentPageKey)) ?></h2>
				<?php if(isset($page_subtitle)): ?>
					<p class="font-13 text-light mt-2"><?= htmlspecialchars($page_subtitle, ENT_QUOTES, 'UTF-8') ?></p>
				<?php endif; ?>
			</div>
		</div>
        
		<div class="top-actions">

			<a href="<?= View::to('notifications') ?>" class="topbar-noti" title="Notifications" style="margin-right: 12px;">
				<i data-lucide="bell" size="20"></i>
				<span class="topbar-noti-badge hidden" id="topbarNotiBadge">0</span>
			</a>
			<div class="user-profile-dropdown" id="userProfileDropdown">
				<button type="button" class="user-profile user-profile-toggle" id="userProfileToggle" aria-haspopup="true" aria-expanded="false">
					<?php 
					$admin_pic = $_SESSION['user_profile_pic'] ?? '';
					$admin_avatar = (!empty($admin_pic) && file_exists(\App\Helpers\StorageHelper::diskPath($admin_pic))) 
						? View::upload($admin_pic) 
						: View::image('profile-image/default-avatar.svg');
					?>
					<img src="<?= $admin_avatar ?>" alt="Admin" class="user-avatar" onerror="this.src='<?= View::image('profile-image/default-avatar.svg') ?>'">
					<div class="user-info">
						<span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></span>
						<span class="user-role"><?= htmlspecialchars($_SESSION['user_role'] ?? 'Super Admin', ENT_QUOTES, 'UTF-8') ?></span>
					</div>
					<i data-lucide="chevron-down" size="16" class="user-dropdown-chevron"></i>
				</button>
				<div class="dropdown-menu user-profile-menu">
					<a href="<?= View::to('logout') ?>" class="dropdown-item">
						<div class="item-icon danger">
							<i data-lucide="log-out" size="16"></i>
						</div>
						<div class="item-text">
							<span class="title">Logout</span>
							<span class="desc">Sign out of your account</span>
						</div>
					</a>
				</div>
			</div>
		</div>
	</header>
	<script>
		// Attendance Dropdown Toggle
		const attToggle = document.getElementById('attendanceToggle');
		const attDropdown = document.getElementById('attendanceDropdown');
        
		if(attToggle) {
			attToggle.addEventListener('click', (e) => {
				e.stopPropagation();
				attDropdown.classList.toggle('active');
				userProfileDropdown && userProfileDropdown.classList.remove('active');
			});
            
			document.addEventListener('click', () => {
				attDropdown.classList.remove('active');
			});
            
			attDropdown.querySelector('.dropdown-menu').addEventListener('click', (e) => {
				e.stopPropagation();
			});
		}

		// User Profile Dropdown Toggle
		const userProfileToggle = document.getElementById('userProfileToggle');
		const userProfileDropdown = document.getElementById('userProfileDropdown');
		if(userProfileToggle && userProfileDropdown) {
			userProfileToggle.addEventListener('click', (e) => {
				e.stopPropagation();
				userProfileDropdown.classList.toggle('active');
				userProfileToggle.setAttribute('aria-expanded', userProfileDropdown.classList.contains('active'));
				attDropdown && attDropdown.classList.remove('active');
			});
			document.addEventListener('click', () => {
				userProfileDropdown.classList.remove('active');
				userProfileToggle.setAttribute('aria-expanded', 'false');
			});
			userProfileDropdown.querySelector('.dropdown-menu').addEventListener('click', (e) => {
				e.stopPropagation();
			});
		}
        
		// Sidebar Submenu Toggle
		const submenuToggles = document.querySelectorAll('.submenu-toggle');
		submenuToggles.forEach(toggle => {
			toggle.addEventListener('click', (e) => {
				e.preventDefault();
				const parent = toggle.closest('.has-submenu');
				parent.classList.toggle('open');
			});
		});

		// Update Notification Badge
		function refreshNotiBadge() {
			fetch('<?= View::api('notification_handler.php?action=unread_count') ?>')
				.then(res => res.json())
				.then(res => {
					const badge = document.getElementById('notiSidebarBadge');
					const topbarBadge = document.getElementById('topbarNotiBadge');
					if (res.status === 'success' && res.count > 0) {
						if (badge) {
							badge.textContent = res.count;
							badge.classList.remove('hidden');
						}
						if (topbarBadge) {
							topbarBadge.textContent = res.count;
							topbarBadge.classList.remove('hidden');
						}
					} else {
						if (badge) badge.classList.add('hidden');
						if (topbarBadge) topbarBadge.classList.add('hidden');
					}
				});
		}
		refreshNotiBadge();
		// Live updates handled via WebSocket (websocket.js)
	</script>
	<div class="content-body">

