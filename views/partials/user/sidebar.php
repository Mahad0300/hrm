<?php
use App\Core\View;
use App\Helpers\HRPermissions;

$currentPageKey = HRPermissions::resolvePageKey() ?? 'index';
$prefix = View::basePath();

// Always use latest profile picture & biometric_id from DB
$profile_pic = null;
$user_biometric_id = null;
if (!empty($_SESSION['user_id'])) {
	$stmt_pic = $pdo->prepare("SELECT profile_pic, biometric_id FROM employees WHERE id = ? AND deleted_at IS NULL LIMIT 1");
	$stmt_pic->execute([$_SESSION['user_id']]);
	$user_row = $stmt_pic->fetch(PDO::FETCH_ASSOC);
	if ($user_row) {
		$profile_pic = $user_row['profile_pic'] ?: null;
		$user_biometric_id = !empty($user_row['biometric_id']) ? trim((string)$user_row['biometric_id']) : null;
	}
	$_SESSION['user_profile_pic'] = $profile_pic;
}

// Check global attendance self check-in setting
$show_self_checkin = true;
try {
	$stmt_checkin_set = $pdo->query("SELECT meta_value FROM settings WHERE meta_key = 'enable_self_checkin' LIMIT 1");
	if ($stmt_checkin_set && ($cval = $stmt_checkin_set->fetchColumn()) !== false) {
		$show_self_checkin = ($cval !== '0' && $cval !== 0);
	}
} catch (\Throwable $t) {}

// Fetch attendance status for topbar (respecting logical date and shift)
$stmt_shift = $pdo->prepare("SELECT s.* FROM employees e JOIN shifts s ON e.shift_id = s.id WHERE e.id = ?");
$stmt_shift->execute([$_SESSION['user_id']]);
$shift_info = $stmt_shift->fetch();

$can_check_in = false;
$can_check_out = false;

if ($shift_info) {
	// Determine Logical Date
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
?>
<aside class="sidebar" id="sidebar">
	<div class="sidebar-logo">
		<a href="<?= View::url('dashboard') ?>" class="sidebar-logo-mark" aria-label="HRM Employee dashboard">
			<img src="<?= View::image('loginimage/logo.png') ?>" alt="Richmond Tech Group" class="sidebar-logo-img" width="44" height="44">
		</a>
		<div class="sidebar-brand-text">
			<span class="sidebar-brand-name">HRM</span>
			<span class="sidebar-brand-tag">Employee portal</span>
		</div>
	</div>
    
	<div class="sidebar-menu custom-scrollbar">
		<div class="menu-label">Main Menu</div>
		<div class="menu-item">
			<a href="<?= View::url('dashboard') ?>" class="menu-link <?= ($currentPageKey === 'index') ? 'active' : '' ?>">
				<i data-lucide="layout-dashboard" size="18"></i>
				<span>Dashboard</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::url('sheets') ?>" class="menu-link <?= ($currentPageKey === 'sheets') ? 'active' : '' ?>">
				<i data-lucide="table-properties" size="18"></i>
				<span>Sheets</span>
			</a>
		</div>
        
		<div class="menu-label">Organization</div>
		<?php if (!empty($user_biometric_id)): ?>
		<div class="menu-item">
			<a href="<?= View::url('daily-attendance') ?>" class="menu-link <?= ($currentPageKey === 'daily-attendance') ? 'active' : '' ?>">
				<i data-lucide="calendar-check" size="18"></i>
				<span>Daily Attendance</span>
			</a>
		</div>
		<?php endif; ?>
		<div class="menu-item">
			<a href="<?= View::url('leave') ?>" class="menu-link <?= ($currentPageKey === 'leave-management') ? 'active' : '' ?>">
				<i data-lucide="clock" size="18"></i>
				<span>Leave History</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::url('hierarchy') ?>" class="menu-link <?= ($currentPageKey === 'hierarchy') ? 'active' : '' ?>">
				<i data-lucide="network" size="18"></i>
				<span>Company Hierarchy</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::url('events') ?>" class="menu-link <?= ($currentPageKey === 'event-calendar') ? 'active' : '' ?>">
				<i data-lucide="calendar" size="18"></i>
				<span>Event Calendar</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::url('kpi') ?>" class="menu-link <?= ($currentPageKey === 'kpi' || $currentPageKey === 'kpi-management') ? 'active' : '' ?>">
				<i data-lucide="award" size="18"></i>
				<span>My KPI Result</span>
			</a>
		</div>

		<div class="menu-label">Administration</div>
		<div class="menu-item">
			<a href="<?= View::url('payroll') ?>" class="menu-link <?= ($currentPageKey === 'payroll') ? 'active' : '' ?>">
				<i data-lucide="banknote" size="18"></i>
				<span>Payroll</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::url('policies') ?>" class="menu-link <?= (in_array($currentPageKey, ['policies', 'policy-detail'], true)) ? 'active' : '' ?>">
				<i data-lucide="scroll-text" size="18"></i>
				<span>Company Policies</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::url('announcements') ?>" class="menu-link <?= ($currentPageKey === 'announcements') ? 'active' : '' ?>">
				<i data-lucide="megaphone" size="18"></i>
				<span>Announcements</span>
			</a>
		</div>
		<div class="menu-item">
			<a href="<?= View::url('notifications') ?>" class="menu-link <?= ($currentPageKey === 'notifications') ? 'active' : '' ?>">
				<i data-lucide="bell" size="18"></i>
				<span>Notifications</span>
				<span class="badge badge-primary badge-pill ml-auto hidden" id="notiSidebarBadge">0</span>
			</a>
		</div>

		<div class="menu-label">Support & Help</div>
		<div class="menu-item">
			<a href="<?= View::url('it-support') ?>" class="menu-link <?= ($currentPageKey === 'it-support') ? 'active' : '' ?>">
				<i data-lucide="headset" size="18"></i>
				<span>IT Helpdesk</span>
			</a>
		</div>
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
			<?php else: ?>
			<div class="biometric-notice-pill" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(108, 76, 241, 0.08); border: 1px solid rgba(108, 76, 241, 0.2); color: var(--primary-color, #6c4cf1); padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; margin-right: 12px; max-width: 320px; overflow: hidden; text-overflow: ellipsis;" title="Your clock-in/clock-out is now managed by machine, so just see your progress here!">
				<i data-lucide="fingerprint" size="16" style="flex-shrink: 0;"></i>
				<span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Clock-in/out is managed by machine</span>
			</div>
			<?php endif; ?>
            
			<div class="user-profile-dropdown" id="userProfileDropdown">
				<button type="button" class="user-profile user-profile-toggle" id="userProfileToggle" aria-haspopup="true" aria-expanded="false">
					<?php
					$avatar_path = (!empty($profile_pic) && file_exists(\App\Helpers\StorageHelper::diskPath($profile_pic))) ? View::upload($profile_pic) : View::image('profile-image/default-avatar.svg');
					?>
					<img src="<?= $avatar_path ?>" alt="Employee" class="user-avatar" 
						 onerror="this.src='<?= View::image('profile-image/default-avatar.svg') ?>'">
					<div class="user-info">
						<span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Employee', ENT_QUOTES, 'UTF-8') ?></span>
						<span class="user-role"><?= htmlspecialchars($_SESSION['user_role'] ?? 'Staff', ENT_QUOTES, 'UTF-8') ?></span>
					</div>
					<i data-lucide="chevron-down" size="16" class="user-dropdown-chevron"></i>
				</button>
				<div class="dropdown-menu user-profile-menu">
					<a href="<?= View::url('profile') ?>" class="dropdown-item">
						<div class="item-icon primary">
							<i data-lucide="user" size="16"></i>
						</div>
						<div class="item-text">
							<span class="title">Profile</span>
							<span class="desc">View your profile</span>
						</div>
					</a>
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
					if (res.status === 'success' && res.count > 0) {
						badge.textContent = res.count;
						badge.classList.remove('hidden');
					} else {
						badge.classList.add('hidden');
					}
				});
		}
		refreshNotiBadge();
		// Live updates handled via WebSocket (websocket.js)

		// Lucide init (ensure it runs after potential dynamic content)
		lucide.createIcons();
	</script>
	<div class="content-body">

