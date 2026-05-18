<?php
$current_page = basename($_SERVER['PHP_SELF']);

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
    } elseif ($att_record['clock_in'] && !$att_record['clock_out']) {
        $can_check_out = true;
    }
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <i data-lucide="shield-check" size="28"></i>
        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name">HRM</span>
            <span class="sidebar-brand-tag">Employee portal</span>
        </div>
    </div>
    
    <div class="sidebar-menu custom-scrollbar">
        <div class="menu-label">Main Menu</div>
        <div class="menu-item">
            <a href="index.php" class="menu-link <?= ($current_page == 'index.php') ? 'active' : '' ?>">
                <i data-lucide="layout-dashboard" size="18"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <div class="menu-label">Organization</div>
        <div class="menu-item">
            <a href="daily-attendance.php" class="menu-link <?= ($current_page == 'daily-attendance.php') ? 'active' : '' ?>">
                <i data-lucide="calendar-check" size="18"></i>
                <span>Daily Attendance</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="leave-management.php" class="menu-link <?= ($current_page == 'leave-management.php') ? 'active' : '' ?>">
                <i data-lucide="clock" size="18"></i>
                <span>Leave History</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="hierarchy.php" class="menu-link <?= ($current_page == 'hierarchy.php') ? 'active' : '' ?>">
                <i data-lucide="network" size="18"></i>
                <span>Company Hierarchy</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="event-calendar.php" class="menu-link <?= ($current_page == 'event-calendar.php') ? 'active' : '' ?>">
                <i data-lucide="calendar" size="18"></i>
                <span>Event Calendar</span>
            </a>
        </div>

        <div class="menu-label">Administration</div>
        <div class="menu-item">
            <a href="payroll.php" class="menu-link <?= ($current_page == 'payroll.php') ? 'active' : '' ?>">
                <i data-lucide="banknote" size="18"></i>
                <span>Payroll</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="policies.php" class="menu-link <?= (in_array($current_page, ['policies.php', 'policy-detail.php'], true)) ? 'active' : '' ?>">
                <i data-lucide="scroll-text" size="18"></i>
                <span>Company Policies</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="announcements.php" class="menu-link <?= ($current_page == 'announcements.php') ? 'active' : '' ?>">
                <i data-lucide="megaphone" size="18"></i>
                <span>Announcements</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="notifications.php" class="menu-link <?= ($current_page == 'notifications.php') ? 'active' : '' ?>">
                <i data-lucide="bell" size="18"></i>
                <span>Notifications</span>
                <span class="badge badge-primary badge-pill ml-auto hidden" id="notiSidebarBadge">0</span>
            </a>
        </div>

        <div class="menu-label">Support & Help</div>
        <div class="menu-item">
            <a href="it-support.php" class="menu-link <?= ($current_page == 'it-support.php') ? 'active' : '' ?>">
                <i data-lucide="headset" size="18"></i>
                <span>IT Helpdesk</span>
            </a>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="menu-item m-0">
            <a href="../logout.php" class="menu-link danger menu-link--logout-stack">
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
                <h2><?= isset($page_title) ? $page_title : ucwords(str_replace(['.php', '-'], ['', ' '], $current_page)) ?></h2>
                <?php if(isset($page_subtitle)): ?>
                    <p class="font-13 text-light mt-2"><?= $page_subtitle ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="top-actions">
           
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
            
            <div class="user-profile-dropdown" id="userProfileDropdown">
                <button type="button" class="user-profile user-profile-toggle" id="userProfileToggle" aria-haspopup="true" aria-expanded="false">
                    <?php 
                    $profile_pic = $_SESSION['user_profile_pic'] ?? null;
                    $avatar_path = $profile_pic ? '../' . $profile_pic : '../images/profile-image/default-avatar.svg';
                    ?>
                    <img src="<?= $avatar_path ?>" alt="Employee" class="user-avatar" 
                         onerror="this.src='../images/profile-image/default-avatar.svg'">
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Employee', ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="user-role"><?= htmlspecialchars($_SESSION['user_role'] ?? 'Staff', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <i data-lucide="chevron-down" size="16" class="user-dropdown-chevron"></i>
                </button>
                <div class="dropdown-menu user-profile-menu">
                    <a href="profile.php" class="dropdown-item">
                        <div class="item-icon primary">
                            <i data-lucide="user" size="16"></i>
                        </div>
                        <div class="item-text">
                            <span class="title">Profile</span>
                            <span class="desc">View your profile</span>
                        </div>
                    </a>
                    <a href="../logout.php" class="dropdown-item">
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
            fetch('../includes/api/notification_handler.php?action=unread_count')
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
        // Keep the badge fresh while employees stay on any user page.
        setInterval(refreshNotiBadge, 5000);

        // Lucide init (ensure it runs after potential dynamic content)
        lucide.createIcons();
    </script>
    <div class="content-body">
