<?php
$page_title = "Dashboard";
$page_subtitle = "Welcome back, " . ($_SESSION['user_name'] ?? 'User') . ". Here is your personal HR overview for today.";
$load_apexcharts = true;
$load_charts_js = true;
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<section class="udash-stat-grid">
    <article class="card stat-card">
        <div class="stat-header">
            <div class="stat-icon success"><i data-lucide="calendar-check"></i></div>
            <div class="trend-up"><i data-lucide="trending-up" size="14"></i><span>On time</span></div>
        </div>
        <div class="stat-content">
            <h3 id="stat-present-status">...</h3>
            <p>Today Attendance</p>
        </div>
        <div class="stat-footer"><span>Checked in at 08:57 AM</span></div>
    </article>

    <article class="card stat-card">
        <div class="stat-header">
            <div class="stat-icon info"><i data-lucide="timer"></i></div>
            <div class="trend-up"><i data-lucide="trending-up" size="14"></i><span>+18 min</span></div>
        </div>
        <div class="stat-content">
            <h3 id="stat-work-hours">...</h3>
            <p>Working Hours</p>
        </div>
        <div class="stat-footer"><span>Target: 08h 00m</span></div>
    </article>

    <article class="card stat-card">
        <div class="stat-header">
            <div class="stat-icon warning"><i data-lucide="clock-3"></i></div>
            <div class="trend-down"><i data-lucide="trending-down" size="14"></i><span>-2 days</span></div>
        </div>
        <div class="stat-content">
            <h3 id="stat-leave-balance">...</h3>
            <p>Leave Balance</p>
        </div>
        <div class="stat-footer"><span>Annual + Casual combined</span></div>
    </article>

    <article class="card stat-card">
        <div class="stat-header">
            <div class="stat-icon info"><i data-lucide="building-2"></i></div>
            <div class="trend-up"><i data-lucide="users" size="14"></i><span>24</span></div>
        </div>
        <div class="stat-content">
            <h3 id="user-job-title"><?= htmlspecialchars($_SESSION['user_role'] ?? 'Employee') ?></h3>
            <p id="user-dept-name">My Department</p>
        </div>
        <div class="stat-footer"><span id="stat-dept-employees-count">... employees in your department</span></div>
    </article>

    <article class="card stat-card">
        <div class="stat-header">
            <div class="stat-icon primary"><i data-lucide="bell-ring"></i></div>
            <div class="trend-up"><i data-lucide="trending-up" size="14"></i><span>+2</span></div>
        </div>
        <div class="stat-content">
            <h3 id="stat-unread-notifications">...</h3>
            <p>Unread Notifications</p>
        </div>
        <div class="stat-footer"><span>2 high-priority alerts</span></div>
    </article>
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
    <article class="card udash-panel">
        <div class="udash-head">
            <div>
                <h3 class="font-600">Leave Usage Analytics</h3>
                <p class="udash-sub">Monthly leave usage by type</p>
            </div>
            <a href="leave-management.php" class="font-13 text-light">Open leave history</a>
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
        <div class="udash-feed">
            <a href="announcements.php" class="udash-feed__item">
                <span class="udash-feed__body">
                    <span class="udash-feed__title"><span class="udash-feed__emoji" aria-hidden="true">🚨</span>Security policy update scheduled for this week.</span>
                    <span class="udash-feed__meta"><span class="udash-feed__badge udash-feed__badge--important">🚨 IMPORTANT</span> IT Desk · 2 hours ago</span>
                </span>
            </a>
            <a href="announcements.php" class="udash-feed__item">
                <span class="udash-feed__body">
                    <span class="udash-feed__title"><span class="udash-feed__emoji" aria-hidden="true">📢</span>Town hall timing updated to Friday 10:00 AM.</span>
                    <span class="udash-feed__meta"><span class="udash-feed__badge udash-feed__badge--update">📢 UPDATE</span> HR Team · Yesterday</span>
                </span>
            </a>
            <a href="announcements.php" class="udash-feed__item">
                <span class="udash-feed__body">
                    <span class="udash-feed__title"><span class="udash-feed__emoji" aria-hidden="true">🎉</span>Payroll cycle closing date is 28 Mar.</span>
                    <span class="udash-feed__meta"><span class="udash-feed__badge udash-feed__badge--holiday">🎉 NOTICE</span> Finance · 2 days ago</span>
                </span>
            </a>
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
        <div class="udash-feed">
            <a href="notifications.php" class="udash-feed__item">
                <span class="udash-feed__body">
                    <span class="udash-feed__title"><span class="udash-feed__emoji" aria-hidden="true">✅</span>Your leave request has been approved.</span>
                    <span class="udash-feed__meta">Manager Approval · Just now</span>
                </span>
            </a>
            <a href="notifications.php" class="udash-feed__item">
                <span class="udash-feed__body">
                    <span class="udash-feed__title"><span class="udash-feed__emoji" aria-hidden="true">📝</span>Reminder: Submit monthly self-review before Friday.</span>
                    <span class="udash-feed__meta">Performance Cycle · Today</span>
                </span>
            </a>
            <a href="notifications.php" class="udash-feed__item">
                <span class="udash-feed__body">
                    <span class="udash-feed__title"><span class="udash-feed__emoji" aria-hidden="true">🕒</span>Interview panel meeting starts at 3:00 PM today.</span>
                    <span class="udash-feed__meta">Calendar Alert · Today</span>
                </span>
            </a>
        </div>
    </article>
</section>


<script src="assets/js/dashboard.js"></script>
<?php include 'includes/footer.php'; ?>

