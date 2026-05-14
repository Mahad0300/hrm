<?php
$page_title = "Dashboard";
$page_subtitle = "Welcome back, " . ($_SESSION['user_name'] ?? 'User') . ". Here is your personal HR overview for today.";
$load_apexcharts = true;
$load_charts_js = true;
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<section class="udash-stat-grid">
    <a href="daily-attendance.php" class="card-link">
        <article class="card stat-card">
            <div class="stat-header">
                <div class="stat-icon success"><i data-lucide="calendar-check"></i></div>
                <div class="trend-up"><i data-lucide="trending-up" size="14"></i><span>On time</span></div>
            </div>
            <div class="stat-content">
                <h3 id="stat-present-status">...</h3>
                <p>Today Attendance</p>
            </div>
            <div class="stat-footer"><span id="stat-attendance-time">Not checked in yet</span></div>
        </article>
    </a>

    <a href="daily-attendance.php" class="card-link">
        <article class="card stat-card">
            <div class="stat-header">
                <div class="stat-icon info"><i data-lucide="timer"></i></div>
                <div id="stat-work-trend-container" class="trend-up" style="display: none;">
                    <i id="stat-work-trend-icon" data-lucide="trending-up" size="14"></i>
                    <span id="stat-work-trend-text">+0 min</span>
                </div>
            </div>
            <div class="stat-content">
                <h3 id="stat-work-hours">...</h3>
                <p>Working Hours</p>
            </div>
            <div class="stat-footer"><span id="stat-work-target">Target: 08h 00m</span></div>
        </article>
    </a>

    <a href="leave-management.php" class="card-link">
        <article class="card stat-card">
            <div class="stat-header">
                <div class="stat-icon warning"><i data-lucide="clock-3"></i></div>
                <div class="trend-down"><i data-lucide="trending-down" size="14"></i><span id="stat-leave-trend">-0 days</span></div>
            </div>
            <div class="stat-content">
                <h3 id="stat-leave-balance">...</h3>
                <p>Leave Balance</p>
            </div>
            <div class="stat-footer"><span>Annual + Casual combined</span></div>
        </article>
    </a>

    <a href="hierarchy.php" class="card-link">
        <article class="card stat-card">
            <div class="stat-header">
                <div class="stat-icon info"><i data-lucide="building-2"></i></div>
                <div class="trend-up"><i data-lucide="users" size="14"></i><span id="stat-dept-count-badge">...</span></div>
            </div>
            <div class="stat-content">
                <h3 id="user-job-title"><?= htmlspecialchars($_SESSION['user_role'] ?? 'Employee') ?></h3>
                <p id="user-dept-name">My Department</p>
            </div>
            <div class="stat-footer"><span id="stat-dept-employees-count">... employees in your department</span></div>
        </article>
    </a>

    <a href="notifications.php" class="card-link">
        <article class="card stat-card">
            <div class="stat-header">
                <div class="stat-icon primary"><i data-lucide="bell-ring"></i></div>
                <div class="trend-up"><i data-lucide="bell" size="14"></i><span id="stat-unread-count-badge">...</span></div>
            </div>
            <div class="stat-content">
                <h3 id="stat-unread-notifications">...</h3>
                <p>Unread Notifications</p>
            </div>
            <div class="stat-footer"><span>2 high-priority alerts</span></div>
        </article>
    </a>
</section>

<section class="udash-grid">
    <article class="card udash-panel">
        <div class="udash-head">
            <div>
                <h3 class="font-600">Weekly Attendance Trend</h3>
                <p class="udash-sub">Attendance percentage for current week</p>
            </div>
            <a href="daily-attendance.php" class="font-13 text-light">Open details</a>
        </div>
        <div id="attendanceApexChart" class="udash-chart"></div>
    </article>

    <article class="card udash-panel">
        <div class="udash-head">
            <div>
                <h3 class="font-600">Monthly Attendance</h3>
                <p class="udash-sub">On time, late, half day, absent</p>
            </div>
        </div>
        <div id="attendanceMixApexChart" class="udash-chart udash-chart--donut"></div>
    </article>
</section>

<section class="udash-grid udash-grid--secondary">
    <article class="card udash-panel h-full">
        <div class="udash-head align-start">
            <div>
                <h3 class="font-600">Leave Usage Analytics</h3>
                <p class="udash-sub mb-15">Monthly leave usage by type</p>
                
                <div class="flex-center gap-10 mt-10">
                    <div class="flex-center gap-5">
                        <span class="font-12 text-light font-500">From:</span>
                        <input type="month" id="leaveFromFilter" class="per-page-select font-12 m-0" value="2026-01" 
                            style="width: auto; height: 32px; padding: 0 12px; border: 1px solid #e2e8f0; border-radius: 8px; color: #1e293b; background: #fff; cursor: pointer;">
                    </div>
                    <div class="flex-center gap-5">
                        <span class="font-12 text-light font-500">To:</span>
                        <input type="month" id="leaveToFilter" class="per-page-select font-12 m-0" value="2026-05" 
                            style="width: auto; height: 32px; padding: 0 12px; border: 1px solid #e2e8f0; border-radius: 8px; color: #1e293b; background: #fff; cursor: pointer;">
                    </div>
                </div>
            </div>
            <a href="leave-history.php" class="font-13 text-light hover-underline">Open leave history</a>
        </div>
        <div id="leaveUsageApexChart" class="udash-chart"></div>
    </article>

    <article class="card udash-panel">
        <div class="udash-head">
            <div>
                <h3 class="font-600">Work Hours Analysis</h3>
                <p class="udash-sub">Last 7 days worked vs target hours</p>
            </div>
            <a href="daily-attendance.php" class="font-13 text-light">Open timesheet</a>
        </div>
        <div id="workHoursApexChart" class="udash-chart"></div>
    </article>
</section>

<section class="udash-grid udash-grid--secondary">
    <article class="card udash-panel">
        <div class="udash-head">
            <div>
                <h3 class="font-600">Latest Announcements</h3>
                <p class="udash-sub">Recent company updates</p>
            </div>
            <a href="announcements.php" class="font-13 text-light">View all</a>
        </div>
        <div class="udash-feed" id="announcementsFeed">
            <div class="flex-center py-20"><div class="loader-ripple"><div></div><div></div></div></div>
        </div>
    </article>

    <article class="card udash-panel">
        <div class="udash-head">
            <div>
                <h3 class="font-600">Recent Notifications</h3>
                <p class="udash-sub">Latest alerts from your inbox</p>
            </div>
            <a href="notifications.php" class="font-13 text-light">Open inbox</a>
        </div>
        <div class="udash-feed" id="notificationsFeed">
            <div class="flex-center py-20"><div class="loader-ripple"><div></div><div></div></div></div>
        </div>
    </article>
</section>


<script src="assets/js/dashboard.js"></script>
<?php include 'includes/footer.php'; ?>