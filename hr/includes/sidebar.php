<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <i data-lucide="shield-check" size="28"></i>
        <span>HRM Admin</span>
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
            <a href="employees.php" class="menu-link <?= ($current_page == 'employees.php') ? 'active' : '' ?>">
                <i data-lucide="users" size="18"></i>
                <span>Employees</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="attendance.php" class="menu-link <?= ($current_page == 'attendance.php') ? 'active' : '' ?>">
                <i data-lucide="calendar-check" size="18"></i>
                <span>Attendance</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="leave-management.php" class="menu-link <?= ($current_page == 'leave-management.php') ? 'active' : '' ?>">
                <i data-lucide="clock" size="18"></i>
                <span>Leave Management</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="candidates.php" class="menu-link <?= ($current_page == 'candidates.php') ? 'active' : '' ?>">
                <i data-lucide="user-plus" size="18"></i>
                <span>New Joining</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="hierarchy.php" class="menu-link <?= ($current_page == 'hierarchy.php') ? 'active' : '' ?>">
                <i data-lucide="network" size="18"></i>
                <span>Hierarchy</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="kpi-management.php" class="menu-link <?= ($current_page == 'kpi-management.php') ? 'active' : '' ?>">
                <i data-lucide="line-chart" size="18"></i>
                <span>KPI Management</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="event-calendar.php" class="menu-link <?= ($current_page == 'event-calendar.php') ? 'active' : '' ?>">
                <i data-lucide="calendar" size="18"></i>
                <span>Event Calendar</span>
            </a>
        </div>
        
        <div class="menu-label">Job Management</div>
        <div class="menu-item has-submenu <?= in_array($current_page, ['job-list.php', 'create-job.php', 'job-candidates.php', 'candidate-detail.php', 'interviews.php']) ? 'active open' : '' ?>">
            <a href="javascript:void(0)" class="menu-link submenu-toggle">
                <i data-lucide="briefcase" size="18"></i>
                <span>Job Management</span>
                <i data-lucide="chevron-down" size="14" class="chevron"></i>
            </a>
            <div class="submenu">
                <a href="job-list.php" class="submenu-link <?= ($current_page == 'job-list.php') ? 'active' : '' ?>">
                    <i data-lucide="list" size="14"></i>
                    <span>Job Postings</span>
                </a>
                <a href="create-job.php" class="submenu-link <?= ($current_page == 'create-job.php') ? 'active' : '' ?>">
                    <i data-lucide="plus-circle" size="14"></i>
                    <span>Create New Job</span>
                </a>
                <a href="job-candidates.php" class="submenu-link <?= ($current_page == 'job-candidates.php') ? 'active' : '' ?>">
                    <i data-lucide="users" size="14"></i>
                    <span>Candidate Pool</span>
                </a>
                <a href="interviews.php" class="submenu-link <?= ($current_page == 'interviews.php') ? 'active' : '' ?>">
                    <i data-lucide="calendar" size="14"></i>
                    <span>Interviews</span>
                </a>
            </div>
        </div>

        <div class="menu-label">Administration</div>
        <div class="menu-item">
            <a href="payroll.php" class="menu-link <?= ($current_page == 'payroll.php') ? 'active' : '' ?>">
                <i data-lucide="banknote" size="18"></i>
                <span>Payroll</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="activity-logs.php" class="menu-link <?= ($current_page == 'activity-logs.php') ? 'active' : '' ?>">
                <i data-lucide="history" size="18"></i>
                <span>Activity Logs</span>
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
            </a>
        </div>
        
        <div class="menu-label">System</div>
        <div class="menu-item has-submenu <?= in_array($current_page, ['shifts.php', 'department-management.php', 'role-management.php', 'policy-management.php']) ? 'active open' : '' ?>">
            <a href="javascript:void(0)" class="menu-link submenu-toggle">
                <i data-lucide="settings" size="18"></i>
                <span>Settings</span>
                <i data-lucide="chevron-down" size="14" class="chevron"></i>
            </a>
            <div class="submenu">
                <a href="shifts.php" class="submenu-link <?= ($current_page == 'shifts.php') ? 'active' : '' ?>">
                    <i data-lucide="plus-circle" size="14"></i>
                    <span>Add Shift</span>
                </a>
                <a href="department-management.php" class="submenu-link <?= ($current_page == 'department-management.php') ? 'active' : '' ?>">
                    <i data-lucide="building-2" size="14"></i>
                    <span>Dept Management</span>
                </a>
                <a href="role-management.php" class="submenu-link <?= ($current_page == 'role-management.php') ? 'active' : '' ?>">
                    <i data-lucide="shield-check" size="14"></i>
                    <span>Role Management</span>
                </a>
                <a href="policy-management.php" class="submenu-link <?= ($current_page == 'policy-management.php') ? 'active' : '' ?>">
                    <i data-lucide="file-text" size="14"></i>
                    <span>Policy Management</span>
                </a>
            </div>
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
<!--             
            <button class="icon-btn" title="Notifications">
                <i data-lucide="bell"></i>
                <span class="badge-dot"></span>
            </button> -->
            
            <!-- <div class="attendance-dropdown" id="attendanceDropdown">
                <button class="attendance-toggle" id="attendanceToggle">
                    <i data-lucide="clock" size="18"></i>
                    <span>Attendance</span>
                    <i data-lucide="chevron-down" size="14"></i>
                </button>
                <div class="dropdown-menu">
                    <div class="dropdown-header">Daily Tracking</div>
                    <button class="dropdown-item">
                        <div class="item-icon success">
                            <i data-lucide="log-in" size="16"></i>
                        </div>
                        <div class="item-text">
                            <span class="title">Check In</span>
                            <span class="desc">Mark your arrival</span>
                        </div>
                    </button>
                    <button class="dropdown-item">
                        <div class="item-icon danger">
                            <i data-lucide="log-out" size="16"></i>
                        </div>
                        <div class="item-text">
                            <span class="title">Check Out</span>
                            <span class="desc">Mark your departure</span>
                        </div>
                    </button>
                </div>
            </div> -->
            
            <div class="user-profile-dropdown" id="userProfileDropdown">
                <button type="button" class="user-profile user-profile-toggle" id="userProfileToggle" aria-haspopup="true" aria-expanded="false">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150" alt="Admin" class="user-avatar">
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'HR Manager', ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="user-role"><?= htmlspecialchars($_SESSION['user_role'] ?? 'HR', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <i data-lucide="chevron-down" size="16" class="user-dropdown-chevron"></i>
                </button>
                <div class="dropdown-menu user-profile-menu">
                    <a href="employee-profile.php" class="dropdown-item">
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
    </script>
    <div class="content-body">
