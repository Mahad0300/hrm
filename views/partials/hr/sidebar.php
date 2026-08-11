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

$hrShowJobsMenu = \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'job-list') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'create-job') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'job-candidates') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'walk-in-candidates') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'interviews');
$hrShowSettingsMenu = \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'shifts') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'department-management') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'policy-management') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'payroll-settings') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'hierarchy-settings');
$hrShowOrgSection = \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'employees') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'attendance') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'leave-management') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'new-joining') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'hierarchy') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'kpi-management') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'event-calendar');
$hrShowAdminSection = \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'payroll') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'activity-logs') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'announcements') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'notifications') || \App\Helpers\HRPermissions::canViewSidebarPage($pdo, 'it-support');

// Check global attendance self check-in setting
$show_self_checkin = true;
$can_check_in = false;
$can_check_out = false;
try {
	$stmt_checkin_set = $pdo->query("SELECT meta_value FROM settings WHERE meta_key = 'enable_self_checkin' LIMIT 1");
	if ($stmt_checkin_set && ($cval = $stmt_checkin_set->fetchColumn()) !== false) {
		$show_self_checkin = ($cval !== '0' && $cval !== 0);
	}
	if ($show_self_checkin && !empty($_SESSION['user_id'])) {
		$stmt_shift = $pdo->prepare("SELECT s.* FROM employees e JOIN shifts s ON e.shift_id = s.id WHERE e.id = ?");
		$stmt_shift->execute([$_SESSION['user_id']]);
		$shift_info = $stmt_shift->fetch();
		if ($shift_info) {
			$now_top = new DateTime();
			$current_time_str = $now_top->format('H:i:s');
			$shift_start_str = $shift_info['start_time'];
			$shift_end_str = $shift_info['end_time'];
			$logical_date_top = date('Y-m-d');
			$is_overnight_top = strtotime($shift_start_str) > strtotime($shift_end_str);
			if ($is_overnight_top) {
				$buffer_end_top = date('H:i:s', strtotime($shift_end_str . ' +4 hours'));
				if ($current_time_str >= '00:00:00' && $current_time_str <= $buffer_end_top) {
					$logical_date_top = date('Y-m-d', strtotime('-1 day'));
				}
			}
			$stmt_att = $pdo->prepare("SELECT id, status, clock_in, clock_out FROM attendance WHERE employee_id = ? AND date = ? LIMIT 1");
			$stmt_att->execute([$_SESSION['user_id'], $logical_date_top]);
			$att_record = $stmt_att->fetch();
			if (!$att_record || $att_record['status'] === 'ABSENT') {
				$can_check_in = true;
			} elseif ($att_record && $att_record['status'] === 'LEAVE') {
				$can_check_in = false;
				$can_check_out = false;
			} elseif ($att_record['clock_in'] && !$att_record['clock_out']) {
				$can_check_out = true;
			}
		}
	}
} catch (\Throwable $t) {}
?>
<aside class="sidebar" id="sidebar">
	<div class="sidebar-logo">
		<a href="<?= View::to('dashboard') ?>" class="sidebar-logo-mark" aria-label="HRM Admin dashboard">
			<img src="<?= View::image('loginimage/logo.png') ?>" alt="Richmond Tech Group" class="sidebar-logo-img" width="44" height="44">
		</a>
		<div class="sidebar-brand-text">
			<span class="sidebar-brand-name">HRM</span>
			<span class="sidebar-brand-tag">HR portal</span>
		</div>
	</div>
    
	<div class="sidebar-menu custom-scrollbar">
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'index')): ?>
		<div class="menu-label">Main Menu</div>
		<div class="menu-item" data-hr-page="index">
			<a href="<?= View::to('dashboard') ?>" class="menu-link <?= ($currentPageKey === 'index') ? 'active' : '' ?>">
				<i data-lucide="layout-dashboard" size="18"></i>
				<span>Dashboard</span>
			</a>
		</div>
		<div class="menu-item" data-hr-page="sheets">
			<a href="<?= View::to('sheets') ?>" class="menu-link <?= ($currentPageKey === 'sheets') ? 'active' : '' ?>">
				<i data-lucide="table-properties" size="18"></i>
				<span>Sheets</span>
			</a>
		</div>
		<?php endif; ?>
        
		<?php if ($hrShowOrgSection): ?>
		<div class="menu-label">Organization</div>
		<?php endif; ?>
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'employees')): ?>
		<div class="menu-item" data-hr-page="employees">
			<a href="<?= View::to('employees') ?>" class="menu-link <?= ($currentPageKey === 'employees') ? 'active' : '' ?>">
				<i data-lucide="users" size="18"></i>
				<span>Employees</span>
			</a>
		</div>
		<?php endif; ?>
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'attendance')): ?>
		<div class="menu-item" data-hr-page="attendance">
			<a href="<?= View::to('attendance') ?>" class="menu-link <?= ($currentPageKey === 'attendance') ? 'active' : '' ?>">
				<i data-lucide="calendar-check" size="18"></i>
				<span>Attendance</span>
			</a>
		</div>
		<?php endif; ?>
		<?php if (!empty($user_biometric_id)): ?>
		<div class="menu-item" data-hr-page="daily-attendance">
			<a href="<?= View::url('daily-attendance') ?>" class="menu-link <?= ($currentPageKey === 'daily-attendance') ? 'active' : '' ?>">
				<i data-lucide="clock" size="18"></i>
				<span>Daily Attendance</span>
			</a>
		</div>
		<?php endif; ?>
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'leave-management')): ?>
		<div class="menu-item" data-hr-page="leave-management">
			<a href="<?= View::to('leave') ?>" class="menu-link <?= ($currentPageKey === 'leave-management') ? 'active' : '' ?>">
				<i data-lucide="clock" size="18"></i>
				<span>Leave Management</span>
			</a>
		</div>
		<?php endif; ?>
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'new-joining')): ?>
		<div class="menu-item" data-hr-page="new-joining">
			<a href="<?= View::to('new-joining') ?>" class="menu-link <?= ($currentPageKey === 'new-joining') ? 'active' : '' ?>">
				<i data-lucide="user-plus" size="18"></i>
				<span>New Joining</span>
			</a>
		</div>
		<?php endif; ?>
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'hierarchy')): ?>
		<div class="menu-item" data-hr-page="hierarchy">
			<a href="<?= View::to('hierarchy') ?>" class="menu-link <?= ($currentPageKey === 'hierarchy') ? 'active' : '' ?>">
				<i data-lucide="network" size="18"></i>
				<span>Hierarchy</span>
			</a>
		</div>
		<?php endif; ?>
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'kpi-management')): ?>
		<div class="menu-item" data-hr-page="kpi-management">
			<a href="<?= View::to('kpi') ?>" class="menu-link <?= ($currentPageKey === 'kpi-management') ? 'active' : '' ?>">
				<i data-lucide="line-chart" size="18"></i>
				<span>KPI Management</span>
			</a>
		</div>
		<?php endif; ?>
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'event-calendar')): ?>
		<div class="menu-item" data-hr-page="event-calendar">
			<a href="<?= View::to('events') ?>" class="menu-link <?= ($currentPageKey === 'event-calendar') ? 'active' : '' ?>">
				<i data-lucide="calendar" size="18"></i>
				<span>Event Calendar</span>
			</a>
		</div>
		<?php endif; ?>
        
		<?php if ($hrShowJobsMenu): ?>
		<div class="menu-label">Job Management</div>
		<div class="menu-item has-submenu <?= in_array($currentPageKey, ['job-list', 'create-job', 'job-candidates', 'walk-in-candidates', 'candidate-detail', 'interviews'], true) ? 'active open' : '' ?>">
			<a href="javascript:void(0)" class="menu-link submenu-toggle">
				<i data-lucide="briefcase" size="18"></i>
				<span>Job Management</span>
				<i data-lucide="chevron-down" size="14" class="chevron"></i>
			</a>
			<div class="submenu">
				<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'job-list')): ?>
				<a href="<?= View::to('jobs') ?>" class="submenu-link <?= ($currentPageKey === 'job-list') ? 'active' : '' ?>" data-hr-page="job-list">
					<i data-lucide="list" size="14"></i>
					<span>Job Postings</span>
				</a>
				<?php endif; ?>
				<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'create-job')): ?>
				<a href="<?= View::url('jobs.create') ?>" class="submenu-link <?= ($currentPageKey === 'create-job') ? 'active' : '' ?>" data-hr-page="create-job">
					<i data-lucide="plus-circle" size="14"></i>
					<span>Create New Job</span>
				</a>
				<?php endif; ?>
				<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'job-candidates')): ?>
				<a href="<?= View::url('recruitment') ?>" class="submenu-link <?= ($currentPageKey === 'job-candidates') ? 'active' : '' ?>" data-hr-page="job-candidates">
					<i data-lucide="users" size="14"></i>
					<span>Candidate Pool</span>
				</a>
				<a href="<?= View::url('recruitment.walk-in') ?>" class="submenu-link <?= ($currentPageKey === 'walk-in-candidates') ? 'active' : '' ?>" data-hr-page="walk-in-candidates">
					<i data-lucide="user-check" size="14"></i>
					<span>Walk-In Candidates</span>
				</a>
				<?php endif; ?>
				<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'interviews')): ?>
				<a href="<?= View::url('recruitment.interviews') ?>" class="submenu-link <?= ($currentPageKey === 'interviews') ? 'active' : '' ?>" data-hr-page="interviews">
					<i data-lucide="calendar" size="14"></i>
					<span>Interviews</span>
				</a>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ($hrShowAdminSection): ?>
		<div class="menu-label">Administration</div>
		<?php endif; ?>
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'payroll')): ?>
		<div class="menu-item" data-hr-page="payroll">
			<a href="<?= View::url('payroll') ?>" class="menu-link <?= ($currentPageKey === 'payroll') ? 'active' : '' ?>">
				<i data-lucide="banknote" size="18"></i>
				<span>Payroll</span>
			</a>
		</div>
		<?php endif; ?>
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'activity-logs')): ?>
		<div class="menu-item" data-hr-page="activity-logs">
			<a href="<?= View::url('activity-logs') ?>" class="menu-link <?= ($currentPageKey === 'activity-logs') ? 'active' : '' ?>">
				<i data-lucide="history" size="18"></i>
				<span>Activity Logs</span>
			</a>
		</div>
		<?php endif; ?>
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'announcements')): ?>
		<div class="menu-item" data-hr-page="announcements">
			<a href="<?= View::url('announcements') ?>" class="menu-link <?= ($currentPageKey === 'announcements') ? 'active' : '' ?>">
				<i data-lucide="megaphone" size="18"></i>
				<span>Announcements</span>
			</a>
		</div>
		<?php endif; ?>
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'notifications')): ?>
		<div class="menu-item" data-hr-page="notifications">
			<a href="<?= View::url('notifications') ?>" class="menu-link <?= ($currentPageKey === 'notifications') ? 'active' : '' ?>">
				<i data-lucide="bell" size="18"></i>
				<span>Notifications</span>
				<span class="badge badge-primary badge-pill ml-auto hidden" id="notiSidebarBadge">0</span>
			</a>
		</div>
		<?php endif; ?>
		<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'it-support')): ?>
		<div class="menu-item" data-hr-page="it-support">
			<a href="<?= View::url('it-support') ?>" class="menu-link <?= ($currentPageKey === 'it-support') ? 'active' : '' ?>">
				<i data-lucide="headset" size="18"></i>
				<span>IT Helpdesk</span>
			</a>
		</div>
		<?php endif; ?>

		<?php if ($hrShowSettingsMenu): ?>
		<div class="menu-label">System</div>
		<div class="menu-item has-submenu <?= in_array($currentPageKey, ['shifts', 'department-management', 'policy-management', 'payroll-settings', 'hierarchy-settings'], true) ? 'active open' : '' ?>">
			<a href="javascript:void(0)" class="menu-link submenu-toggle">
				<i data-lucide="settings" size="18"></i>
				<span>Settings</span>
				<i data-lucide="chevron-down" size="14" class="chevron"></i>
			</a>
			<div class="submenu">
				<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'shifts')): ?>
				<a href="<?= View::url('shifts') ?>" class="submenu-link <?= ($currentPageKey === 'shifts') ? 'active' : '' ?>" data-hr-page="shifts">
					<i data-lucide="plus-circle" size="14"></i>
					<span>Add Shift</span>
				</a>
				<?php endif; ?>
				<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'department-management')): ?>
				<a href="<?= View::url('departments') ?>" class="submenu-link <?= ($currentPageKey === 'department-management') ? 'active' : '' ?>" data-hr-page="department-management">
					<i data-lucide="building-2" size="14"></i>
					<span>Dept Management</span>
				</a>
				<?php endif; ?>
				<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'policy-management')): ?>
				<a href="<?= View::url('policy-management') ?>" class="submenu-link <?= ($currentPageKey === 'policy-management') ? 'active' : '' ?>" data-hr-page="policy-management">
					<i data-lucide="file-text" size="14"></i>
					<span>Policy Management</span>
				</a>
				<?php endif; ?>
				<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'payroll-settings')): ?>
				<a href="<?= View::url('payroll.settings') ?>" class="submenu-link <?= ($currentPageKey === 'payroll-settings') ? 'active' : '' ?>" data-hr-page="payroll-settings">
					<i data-lucide="calculator" size="14"></i>
					<span>Payroll Cycle</span>
				</a>
				<?php endif; ?>
				<?php if (\App\Helpers\HRPermissions::canViewSidebarPage($pdo,'hierarchy-settings')): ?>
				<a href="<?= View::url('hierarchy.settings') ?>" class="submenu-link <?= ($currentPageKey === 'hierarchy-settings') ? 'active' : '' ?>" data-hr-page="hierarchy-settings">
					<i data-lucide="network" size="14"></i>
					<span>Hierarchy Settings</span>
				</a>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>
		<?php
		$stmtChatroxSb = $pdo->query("SELECT meta_value FROM settings WHERE meta_key = 'chatrox_url' LIMIT 1");
		$chatroxUrlSb = ($stmtChatroxSb && ($vsb = $stmtChatroxSb->fetchColumn()) !== false && !empty($vsb)) ? rtrim((string)$vsb, '/') : 'http://localhost/chatrox';
		?>
		<div class="menu-label">Connected Apps</div>
		<div class="menu-item">
			<a href="<?= htmlspecialchars($chatroxUrlSb) ?>" target="_blank" class="menu-link" style="position:relative;">
				<img src="<?= htmlspecialchars($chatroxUrlSb) ?>/assets/images/logo.png" alt="Chatrox" style="width:18px;height:18px;object-fit:contain;flex-shrink:0;" onerror="this.style.display='none'">
				<span>Chatrox</span>
				<span style="width:7px;height:7px;background:#22c55e;border-radius:50%;display:inline-block;flex-shrink:0;margin-left:auto;"></span>
			</a>
		</div>
	</div>

	<div class="sidebar-footer">
		<div class="menu-item m-0">
			<a href="<?= View::url('logout') ?>" class="menu-link danger menu-link--logout-stack">
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

            
			<?php if (!empty($show_self_checkin)): ?>
			<div class="attendance-dropdown" id="attendanceDropdown">
				<button class="attendance-toggle" id="attendanceToggle">
					<i data-lucide="clock" size="18"></i>
					<span>Attendance</span>
					<i data-lucide="chevron-down" size="14"></i>
				</button>
				<div class="dropdown-menu">
					<div class="dropdown-header">Daily Tracking</div>
					<button class="dropdown-item <?= $can_check_in ? '' : 'hidden' ?>" id="topbarCheckIn" onclick="handleCheckIn()">
						<div class="item-icon success">
							<i data-lucide="log-in" size="16"></i>
						</div>
						<div class="item-text">
							<span class="title">Check In</span>
							<span class="desc">Mark your arrival</span>
						</div>
					</button>
					<button class="dropdown-item <?= $can_check_out ? '' : 'hidden' ?>" id="topbarCheckOut" onclick="handleCheckOut()">
						<div class="item-icon danger">
							<i data-lucide="log-out" size="16"></i>
						</div>
						<div class="item-text">
							<span class="title">Check Out</span>
							<span class="desc">Mark your departure</span>
						</div>
					</button>
				</div>
			</div>
			<?php endif; ?>
            
			<a href="<?= View::url('notifications') ?>" class="topbar-noti" title="Notifications" style="margin-right: 12px;">
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
					<a href="<?= View::url('logout') ?>" class="dropdown-item">
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
				})
				.catch(() => { /* silent — badge is non-critical */ });
		}
		refreshNotiBadge();
		// Live updates handled via WebSocket (websocket.js)
	</script>
	<div class="content-body">

