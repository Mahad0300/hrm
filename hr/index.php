<?php 
$page_title = "Admin Dashboard";
$page_subtitle = "Welcome back, James! Here's what's happening today.";
$dashboard_as_of = date('j M Y, H:i');
$load_apexcharts = true;
$load_charts_js = true;
include 'includes/header.php'; 
?>
<?php include 'includes/sidebar.php'; ?>

<p class="dashboard-data-meta font-12 text-light mb-15" role="note">Sample / demo figures for UI preview — connect your API for live data. <span class="dashboard-as-of-label">As of <?= htmlspecialchars($dashboard_as_of, ENT_QUOTES, 'UTF-8') ?></span></p>

<!-- Stats -->
<div class="stat-grid">
    <a href="employees.php" class="card stat-card stat-card--link" aria-label="Total Employees — open Employees">
        <div class="stat-header">
            <div class="stat-icon primary">
                <i data-lucide="users"></i>
            </div>
            <div class="trend-up">
                <i data-lucide="trending-up" size="14"></i>
                <span>+12%</span>
            </div>
        </div>
        <div class="stat-content">
            <h3>482</h3>
            <p>Total Employees</p>
        </div>
        <div class="stat-footer">
            <span>Last month: 430</span>
        </div>
    </a>

    <a href="attendance.php" class="card stat-card stat-card--link" aria-label="Present Today — open Attendance">
        <div class="stat-header">
            <div class="stat-icon success">
                <i data-lucide="calendar-check"></i>
            </div>
            <div class="trend-up">
                <i data-lucide="trending-up" size="14"></i>
                <span>94%</span>
            </div>
        </div>
        <div class="stat-content">
            <h3>456</h3>
            <p>Present Today</p>
        </div>
        <div class="stat-footer">
            <span>26 on leave</span>
        </div>
    </a>

    <a href="leave-management.php" class="card stat-card stat-card--link" aria-label="Pending Leaves — open Leave Management">
        <div class="stat-header">
            <div class="stat-icon warning">
                <i data-lucide="clock"></i>
            </div>
            <div class="trend-down">
                <i data-lucide="trending-down" size="14"></i>
                <span>-2%</span>
            </div>
        </div>
        <div class="stat-content">
            <h3>14</h3>
            <p>Pending Leaves</p>
        </div>
        <div class="stat-footer">
            <span>Requires approval</span>
        </div>
    </a>

    <a href="job-candidates.php" class="card stat-card stat-card--link" aria-label="New Applicants — open Candidate Pool">
        <div class="stat-header">
            <div class="stat-icon info">
                <i data-lucide="briefcase"></i>
            </div>
            <div class="trend-up">
                <i data-lucide="trending-up" size="14"></i>
                <span>+8</span>
            </div>
        </div>
        <div class="stat-content">
            <h3>24</h3>
            <p>New Applicants</p>
        </div>
        <div class="stat-footer">
            <span>8 open roles</span>
        </div>
    </a>

    <a href="event-calendar.php" class="card stat-card stat-card--link" aria-label="Upcoming Events — open Event Calendar">
        <div class="stat-header">
            <div class="stat-icon danger">
                <i data-lucide="megaphone"></i>
            </div>
        </div>
        <div class="stat-content">
            <h3>3</h3>
            <p>Upcoming Events</p>
        </div>
        <div class="stat-footer">
            <span>Next: Town Hall (10 AM)</span>
        </div>
    </a>
</div>

<!-- Organization snapshot -->
<section class="dashboard-section" aria-label="Organization snapshot">
    <div class="dashboard-section-head">
        <h4 class="font-600 dashboard-section-title">Organization snapshot</h4>
        <span class="dashboard-section-hint">KPIs, structure, jobs, and alerts</span>
    </div>
    <div class="dashboard-snapshot-grid">
        <a href="kpi-management.php" class="snapshot-tile snapshot-tile--primary">
            <span class="snapshot-tile-icon"><i data-lucide="line-chart" size="18"></i></span>
            <span class="snapshot-tile-value">78%</span>
            <span class="snapshot-tile-label">KPIs on track</span>
        </a>
        <a href="notifications.php" class="snapshot-tile snapshot-tile--warning">
            <span class="snapshot-tile-icon"><i data-lucide="bell" size="18"></i></span>
            <span class="snapshot-tile-value">7</span>
            <span class="snapshot-tile-label">Unread notifications</span>
        </a>
        <a href="shifts.php" class="snapshot-tile snapshot-tile--neutral">
            <span class="snapshot-tile-icon"><i data-lucide="clock" size="18"></i></span>
            <span class="snapshot-tile-value">4</span>
            <span class="snapshot-tile-label">Active shift patterns</span>
        </a>
        <a href="department-management.php" class="snapshot-tile snapshot-tile--info">
            <span class="snapshot-tile-icon"><i data-lucide="building-2" size="18"></i></span>
            <span class="snapshot-tile-value">12</span>
            <span class="snapshot-tile-label">Departments</span>
        </a>
        <a href="role-management.php" class="snapshot-tile snapshot-tile--info">
            <span class="snapshot-tile-icon"><i data-lucide="shield" size="18"></i></span>
            <span class="snapshot-tile-value">6</span>
            <span class="snapshot-tile-label">Roles configured</span>
        </a>
        <a href="job-list.php" class="snapshot-tile snapshot-tile--primary">
            <span class="snapshot-tile-icon"><i data-lucide="briefcase" size="18"></i></span>
            <span class="snapshot-tile-value">8</span>
            <span class="snapshot-tile-label">Open job postings</span>
        </a>
    </div>
</section>

<!-- Needs attention -->
<section class="dashboard-section" aria-label="Needs attention">
    <div class="dashboard-section-head">
        <h4 class="font-600 dashboard-section-title">Needs attention</h4>
        <span class="dashboard-section-hint">Items that need a decision or follow-up</span>
    </div>
    <div class="attention-grid">
        <a href="leave-management.php" class="attention-card attention-card-warning">
            <div class="attention-card-icon"><i data-lucide="clipboard-list" size="20"></i></div>
            <div class="attention-card-body">
                <span class="attention-card-count">14</span>
                <span class="attention-card-label">Pending leave approvals</span>
            </div>
            <i data-lucide="chevron-right" class="attention-card-chevron" size="18"></i>
        </a>
        <a href="job-candidates.php" class="attention-card attention-card-primary">
            <div class="attention-card-icon"><i data-lucide="user-plus" size="20"></i></div>
            <div class="attention-card-body">
                <span class="attention-card-count">24</span>
                <span class="attention-card-label">Candidates in pipeline</span>
            </div>
            <i data-lucide="chevron-right" class="attention-card-chevron" size="18"></i>
        </a>
        <a href="interviews.php" class="attention-card attention-card-info">
            <div class="attention-card-icon"><i data-lucide="calendar" size="20"></i></div>
            <div class="attention-card-body">
                <span class="attention-card-count">5</span>
                <span class="attention-card-label">Interviews scheduled this week</span>
            </div>
            <i data-lucide="chevron-right" class="attention-card-chevron" size="18"></i>
        </a>
        <a href="payroll.php" class="attention-card attention-card-neutral">
            <div class="attention-card-icon"><i data-lucide="banknote" size="20"></i></div>
            <div class="attention-card-body">
                <span class="attention-card-count">Mar</span>
                <span class="attention-card-label">Payroll cycle — review before run</span>
            </div>
            <i data-lucide="chevron-right" class="attention-card-chevron" size="18"></i>
        </a>
    </div>
</section>

<div class="layout-dashboard-main">
    <div class="charts-col">
        <div class="dashboard-charts-row">
            <div class="card dashboard-chart-card">
                <div class="card-header-v2">
                    <div>
                        <h4 class="font-600">Attendance trend</h4>
                        <p class="dashboard-chart-sub">Weekly presence rate (%)</p>
                    </div>
                    <select class="form-control select-sm" aria-label="Attendance period">
                        <option>Weekly</option>
                        <option>Monthly</option>
                    </select>
                </div>
                <div class="chart-canvas-wrap">
                    <div id="adminAttendanceChart" class="admin-apex-chart" style="min-height:240px;"></div>
                </div>
            </div>
            <div class="card dashboard-chart-card dashboard-chart-card--compact">
                <div class="card-header-v2 card-header-v2--compact">
                    <div>
                        <h4 class="font-600">Today’s mix</h4>
                        <p class="dashboard-chart-sub">Present, absent &amp; leave</p>
                    </div>
                </div>
                <div class="chart-canvas-wrap chart-canvas-wrap--donut chart-canvas-wrap--today-mix">
                    <div id="adminMixChart" class="admin-apex-chart"></div>
                </div>
            </div>
        </div>

        <div class="card dashboard-chart-card">
            <div class="card-header-v2">
                <div>
                    <h4 class="font-600">Salary by department</h4>
                    <p class="dashboard-chart-sub">Estimated monthly payroll cost per department (sample data)</p>
                </div>
            </div>
            <div class="chart-canvas-wrap chart-canvas-wrap--salary-vibrant" id="salaryByDeptVibrantWrap">
                <div id="adminSalaryChart" class="admin-apex-chart" style="min-height:320px;" aria-label="Salary by department chart"></div>
            </div>
        </div>
    </div>
</div>

<!-- Workforce & hiring -->
<section class="dashboard-section" aria-label="Workforce and hiring">
    <div class="dashboard-section-head">
        <h4 class="font-600 dashboard-section-title">Workforce &amp; hiring</h4>
        <span class="dashboard-section-hint">Headcount, leave mix, and recruitment funnel</span>
    </div>
    <div class="dashboard-widgets-3">
        <div class="card dashboard-chart-card">
            <div class="card-header-v2 card-header-v2--compact">
                <div>
                    <h4 class="font-600">Headcount by department</h4>
                    <p class="dashboard-chart-sub">Active employees per department</p>
                </div>
            </div>
            <div class="chart-canvas-wrap chart-canvas-wrap--donut-dash">
                <div id="adminHeadcountChart" class="admin-apex-chart" style="min-height:260px;"></div>
            </div>
        </div>
        <div class="card dashboard-chart-card dashboard-chart-card--leave-bars">
            <div class="card-header-v2 card-header-v2--compact">
                <div>
                    <h4 class="font-600">Leave by type (MTD)</h4>
                    <p class="dashboard-chart-sub">Approved days this month — bar view</p>
                </div>
            </div>
            <div class="leave-type-vibrant" id="leaveTypeVibrantWrap" aria-label="Leave days by type"></div>
        </div>
        <div class="card dashboard-chart-card">
            <div class="card-header-v2 card-header-v2--compact">
                <div>
                    <h4 class="font-600">Hiring funnel</h4>
                    <p class="dashboard-chart-sub">Open roles pipeline (sample)</p>
                </div>
            </div>
            <div class="chart-canvas-wrap chart-canvas-wrap--funnel">
                <div id="adminFunnelChart" class="admin-apex-chart" style="min-height:280px;"></div>
            </div>
        </div>
    </div>
</section>

<!-- Payroll, retention & punctuality -->
<section class="dashboard-section" aria-label="Payroll and attendance quality">
    <div class="dashboard-section-head">
        <h4 class="font-600 dashboard-section-title">Payroll, retention &amp; punctuality</h4>
        <span class="dashboard-section-hint">Budget use, turnover trend, and today’s time patterns</span>
    </div>
    <div class="dashboard-widgets-3">
        <div class="card dashboard-chart-card">
            <div class="card-header-v2 card-header-v2--compact">
                <div>
                    <h4 class="font-600">Turnover trend</h4>
                    <p class="dashboard-chart-sub">Monthly voluntary exit rate (%)</p>
                </div>
            </div>
            <div class="chart-canvas-wrap">
                <div id="adminTurnoverChart" class="admin-apex-chart" style="min-height:240px;"></div>
            </div>
        </div>
        <div class="card dashboard-chart-card dash-payroll-card">
            <div class="card-header-v2 card-header-v2--compact">
                <div>
                    <h4 class="font-600">Payroll vs budget</h4>
                    <p class="dashboard-chart-sub">March cycle (sample)</p>
                </div>
            </div>
            <div class="dash-payroll-body">
                <div class="dash-budget-row">
                    <span class="dash-budget-label">Budget used</span>
                    <span class="dash-budget-pct font-600">82%</span>
                </div>
                <div class="dash-progress-track" role="progressbar" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100">
                    <span class="dash-progress-fill" style="width:82%"></span>
                </div>
                <dl class="dash-payroll-dl">
                    <div><dt>Allocated</dt><dd>PKR 42.0M</dd></div>
                    <div><dt>Committed</dt><dd>PKR 34.4M</dd></div>
                    <div><dt>Variance</dt><dd class="text-success">−PKR 1.2M</dd></div>
                </dl>
                <p class="font-12 text-light mt-10 mb-0">Next run · 28 Mar — <a href="payroll.php" class="dash-inline-link">Open payroll</a></p>
            </div>
        </div>
        <div class="card dashboard-chart-card">
            <div class="card-header-v2 card-header-v2--compact">
                <div>
                    <h4 class="font-600">Today’s punctuality</h4>
                    <p class="dashboard-chart-sub">Share of headcount — on time, late, half day &amp; absent (sample)</p>
                </div>
            </div>
            <div class="dash-punctuality-radial-wrap" id="punctualityRadialWrap">
                <div id="adminPunctualityChart" class="admin-apex-chart" style="min-height:260px;" role="img" aria-label="Today punctuality breakdown"></div>
            </div>
        </div>
    </div>
</section>

<!-- People & communications -->
<section class="dashboard-section" aria-label="People and communications">
    <div class="dashboard-section-head">
        <h4 class="font-600 dashboard-section-title">People &amp; communications</h4>
        <span class="dashboard-section-hint">Joinings, announcements, and notification feed</span>
    </div>
    <div class="dashboard-widgets-3">
        <div class="card dashboard-chart-card dash-feed-card">
            <div class="card-header-v2 card-header-v2--compact mb-0">
                <div>
                    <h4 class="font-600">New joinings</h4>
                    <p class="dashboard-chart-sub">Onboarding queue</p>
                </div>
                <a href="candidates.php" class="dash-feed-link">View all</a>
            </div>
            <ul class="dash-feed-list">
                <li>
                    <a href="candidates.php" class="dash-feed-item">
                        <span class="dash-feed-dot dash-feed-dot--primary"></span>
                        <span class="dash-feed-main">
                            <span class="dash-feed-title">Ayesha Khan</span>
                            <span class="dash-feed-meta">Engineering · Start 1 Apr</span>
                        </span>
                        <i data-lucide="chevron-right" size="16" class="dash-feed-chevron"></i>
                    </a>
                </li>
                <li>
                    <a href="candidates.php" class="dash-feed-item">
                        <span class="dash-feed-dot dash-feed-dot--primary"></span>
                        <span class="dash-feed-main">
                            <span class="dash-feed-title">Omar Siddiqui</span>
                            <span class="dash-feed-meta">Sales · Docs pending</span>
                        </span>
                        <i data-lucide="chevron-right" size="16" class="dash-feed-chevron"></i>
                    </a>
                </li>
                <li>
                    <a href="candidates.php" class="dash-feed-item">
                        <span class="dash-feed-dot dash-feed-dot--success"></span>
                        <span class="dash-feed-main">
                            <span class="dash-feed-title">Sara Malik</span>
                            <span class="dash-feed-meta">HR · Cleared</span>
                        </span>
                        <i data-lucide="chevron-right" size="16" class="dash-feed-chevron"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="card dashboard-chart-card dash-feed-card">
            <div class="card-header-v2 card-header-v2--compact mb-0">
                <div>
                    <h4 class="font-600">Announcements</h4>
                    <p class="dashboard-chart-sub">Latest broadcasts</p>
                </div>
                <a href="announcements.php" class="dash-feed-link">Manage</a>
            </div>
            <ul class="dash-feed-list">
                <li>
                    <a href="announcements.php" class="dash-feed-item">
                        <span class="dash-feed-main">
                            <span class="dash-feed-title">Town Hall — Q1 results</span>
                            <span class="dash-feed-meta">Posted 2 days ago</span>
                        </span>
                        <i data-lucide="chevron-right" size="16" class="dash-feed-chevron"></i>
                    </a>
                </li>
                <li>
                    <a href="announcements.php" class="dash-feed-item">
                        <span class="dash-feed-main">
                            <span class="dash-feed-title">Ramadan timings</span>
                            <span class="dash-feed-meta">Posted 5 days ago</span>
                        </span>
                        <i data-lucide="chevron-right" size="16" class="dash-feed-chevron"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="card dashboard-chart-card dash-feed-card">
            <div class="card-header-v2 card-header-v2--compact mb-0">
                <div>
                    <h4 class="font-600">Notifications</h4>
                    <p class="dashboard-chart-sub">Recent alerts</p>
                </div>
                <a href="notifications.php" class="dash-feed-link">Inbox</a>
            </div>
            <ul class="dash-feed-list">
                <li>
                    <a href="notifications.php" class="dash-feed-item">
                        <span class="dash-feed-main">
                            <span class="dash-feed-title">Leave approval needed</span>
                            <span class="dash-feed-meta">3 requests · HR queue</span>
                        </span>
                        <i data-lucide="chevron-right" size="16" class="dash-feed-chevron"></i>
                    </a>
                </li>
                <li>
                    <a href="notifications.php" class="dash-feed-item">
                        <span class="dash-feed-main">
                            <span class="dash-feed-title">Interview reminder</span>
                            <span class="dash-feed-meta">Today 3:00 PM</span>
                        </span>
                        <i data-lucide="chevron-right" size="16" class="dash-feed-chevron"></i>
                    </a>
                </li>
                <li>
                    <a href="notifications.php" class="dash-feed-item">
                        <span class="dash-feed-main">
                            <span class="dash-feed-title">Payroll draft ready</span>
                            <span class="dash-feed-meta">Finance</span>
                        </span>
                        <i data-lucide="chevron-right" size="16" class="dash-feed-chevron"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</section>

<!-- KPIs & org structure -->
<section class="dashboard-section dashboard-section--last" aria-label="KPIs and organization structure">
    <div class="dashboard-section-head">
        <h4 class="font-600 dashboard-section-title">KPIs &amp; organization structure</h4>
        <span class="dashboard-section-hint">Goal progress and reporting health</span>
    </div>
    <div class="dashboard-widgets-2">
        <div class="card dashboard-chart-card">
            <div class="card-header-v2 card-header-v2--compact">
                <div>
                    <h4 class="font-600">KPI snapshot</h4>
                    <p class="dashboard-chart-sub">Company goals this quarter (sample)</p>
                </div>
                <a href="kpi-management.php" class="dash-feed-link">KPI hub</a>
            </div>
            <div class="dash-kpi-list">
                <div class="dash-kpi-row">
                    <div class="dash-kpi-head">
                        <span class="dash-kpi-name">Sales revenue target</span>
                        <span class="dash-kpi-val">88%</span>
                    </div>
                    <div class="dash-progress-track dash-progress-track--sm"><span class="dash-progress-fill" style="width:88%"></span></div>
                </div>
                <div class="dash-kpi-row">
                    <div class="dash-kpi-head">
                        <span class="dash-kpi-name">Hiring plan (Q1)</span>
                        <span class="dash-kpi-val">62%</span>
                    </div>
                    <div class="dash-progress-track dash-progress-track--sm"><span class="dash-progress-fill dash-progress-fill--warning" style="width:62%"></span></div>
                </div>
                <div class="dash-kpi-row">
                    <div class="dash-kpi-head">
                        <span class="dash-kpi-name">Training completion</span>
                        <span class="dash-kpi-val">91%</span>
                    </div>
                    <div class="dash-progress-track dash-progress-track--sm"><span class="dash-progress-fill dash-progress-fill--success" style="width:91%"></span></div>
                </div>
                <div class="dash-kpi-row">
                    <div class="dash-kpi-head">
                        <span class="dash-kpi-name">eNPS / satisfaction</span>
                        <span class="dash-kpi-val">+34</span>
                    </div>
                    <div class="dash-progress-track dash-progress-track--sm"><span class="dash-progress-fill dash-progress-fill--info" style="width:72%"></span></div>
                </div>
            </div>
        </div>
        <div class="card dashboard-chart-card dash-hierarchy-card">
            <div class="card-header-v2 card-header-v2--compact">
                <div>
                    <h4 class="font-600">Org structure</h4>
                    <p class="dashboard-chart-sub">Reporting lines &amp; coverage</p>
                </div>
            </div>
            <div class="dash-hierarchy-body">
                <div class="dash-hierarchy-stat">
                    <i data-lucide="network" size="22" class="dash-hierarchy-icon"></i>
                    <div>
                        <p class="font-20 font-600 mb-5">7</p>
                        <p class="font-13 text-light mb-0">Department heads without documented backup</p>
                    </div>
                </div>
                <p class="font-13 text-light mb-15">Use hierarchy view to validate managers, dotted lines, and open headcount under each node.</p>
                <a href="hierarchy.php" class="btn-primary w-full">Open hierarchy</a>
                <a href="event-calendar.php" class="btn-primary w-full btn-muted mt-10">Event calendar</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
