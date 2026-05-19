<?php
$page_title = "Admin Dashboard";
$page_subtitle = "Organization overview for today.";
$dashboard_as_of = date('j M Y, H:i');
$load_apexcharts = true;
$load_charts_js = true;
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<header class="dashboard-greeting mb-25">
    <div class="greeting-content">
        <h2 class="font-700 mb-5">Welcome back, <span class="text-primary"><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></span></h2>
        <p class="text-light font-14">Here is your organization overview for today.</p>
    </div>
    <div class="greeting-meta text-right">
        <div class="live-clock font-600 text-dark" id="dashLiveClock">00:00:00 AM</div>
        <div class="dash-date font-13 text-light mt-5" id="dashDate"><?php echo date('d M, Y'); ?></div>
    </div>
</header>

<!-- Stats -->
<div class="stat-grid mb-20">
    <a href="employees.php" class="card stat-card stat-card--link" aria-label="Total Employees — open Employees">
        <div class="stat-header">
            <div class="stat-icon primary">
                <i data-lucide="users"></i>
            </div>
            <div class="trend-up">
                <i data-lucide="trending-up" size="14"></i>
                <span id="stat-emp-trend">—</span>
            </div>
        </div>
        <div class="stat-content">
            <h3 id="stat-total-employees">...</h3>
            <p>Total Employees</p>
        </div>
        <div class="stat-footer">
            <span id="stat-emp-footer">Loading…</span>
        </div>
    </a>

    <a href="attendance.php" class="card stat-card stat-card--link" aria-label="Present Today — open Attendance">
        <div class="stat-header">
            <div class="stat-icon success">
                <i data-lucide="calendar-check"></i>
            </div>
            <div class="trend-up">
                <i data-lucide="trending-up" size="14"></i>
                <span id="stat-present-pct">—</span>
            </div>
        </div>
        <div class="stat-content">
            <h3 id="stat-present-today">...</h3>
            <p>Present Today</p>
        </div>
        <div class="stat-footer">
            <span id="stat-present-footer">Loading…</span>
        </div>
    </a>

    <a href="leave-management.php" class="card stat-card stat-card--link"
        aria-label="Pending Leaves — open Leave Management">
        <div class="stat-header">
            <div class="stat-icon warning">
                <i data-lucide="clock"></i>
            </div>
            <div class="trend-down">
                <i data-lucide="trending-down" size="14"></i>
                <span id="stat-leave-trend" class="hidden">—</span>
            </div>
        </div>
        <div class="stat-content">
            <h3 id="stat-pending-leaves">...</h3>
            <p>Pending Leaves</p>
        </div>
        <div class="stat-footer">
            <span>Requires approval</span>
        </div>
    </a>

    <a href="job-candidates.php" class="card stat-card stat-card--link"
        aria-label="New Applicants — open Candidate Pool">
        <div class="stat-header">
            <div class="stat-icon info">
                <i data-lucide="briefcase"></i>
            </div>
            <div class="trend-up">
                <i data-lucide="trending-up" size="14"></i>
                <span id="stat-jobs-trend" class="hidden">—</span>
            </div>
        </div>
        <div class="stat-content">
            <h3 id="stat-active-jobs">...</h3>
            <p>Active Jobs</p>
        </div>
        <div class="stat-footer">
            <span id="stat-jobs-footer">Loading…</span>
        </div>
    </a>

    <a href="event-calendar.php" class="card stat-card stat-card--link"
        aria-label="Upcoming Events — open Event Calendar">
        <div class="stat-header">
            <div class="stat-icon danger">
                <i data-lucide="megaphone"></i>
            </div>
        </div>
        <div class="stat-content">
            <h3 id="stat-upcoming-events">...</h3>
            <p>Upcoming Events</p>
        </div>
        <div class="stat-footer">
            <span id="stat-events-footer">Loading…</span>
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
            <span class="snapshot-tile-value" id="snap-kpi">—</span>
            <span class="snapshot-tile-label">KPIs on track</span>
        </a>
        <a href="notifications.php" class="snapshot-tile snapshot-tile--warning">
            <span class="snapshot-tile-icon"><i data-lucide="bell" size="18"></i></span>
            <span class="snapshot-tile-value" id="snap-notifications">—</span>
            <span class="snapshot-tile-label">Unread notifications</span>
        </a>
        <a href="shifts.php" class="snapshot-tile snapshot-tile--neutral">
            <span class="snapshot-tile-icon"><i data-lucide="clock" size="18"></i></span>
            <span class="snapshot-tile-value" id="snap-shifts">—</span>
            <span class="snapshot-tile-label">Active shift patterns</span>
        </a>
        <a href="department-management.php" class="snapshot-tile snapshot-tile--info">
            <span class="snapshot-tile-icon"><i data-lucide="building-2" size="18"></i></span>
            <span class="snapshot-tile-value" id="snap-departments">—</span>
            <span class="snapshot-tile-label">Departments</span>
        </a>
        <a href="role-management.php" class="snapshot-tile snapshot-tile--info">
            <span class="snapshot-tile-icon"><i data-lucide="shield" size="18"></i></span>
            <span class="snapshot-tile-value" id="snap-roles">—</span>
            <span class="snapshot-tile-label">Roles configured</span>
        </a>
        <a href="job-list.php" class="snapshot-tile snapshot-tile--primary">
            <span class="snapshot-tile-icon"><i data-lucide="briefcase" size="18"></i></span>
            <span class="snapshot-tile-value" id="snap-active-jobs">...</span>
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
                <span class="attention-card-count" id="attn-pending-leaves">—</span>
                <span class="attention-card-label">Pending leave approvals</span>
            </div>
            <i data-lucide="chevron-right" class="attention-card-chevron" size="18"></i>
        </a>
        <a href="job-candidates.php" class="attention-card attention-card-primary">
            <div class="attention-card-icon"><i data-lucide="user-plus" size="20"></i></div>
            <div class="attention-card-body">
                <span class="attention-card-count" id="attn-candidates">—</span>
                <span class="attention-card-label">Candidates in pipeline</span>
            </div>
            <i data-lucide="chevron-right" class="attention-card-chevron" size="18"></i>
        </a>
        <a href="interviews.php" class="attention-card attention-card-info">
            <div class="attention-card-icon"><i data-lucide="calendar" size="20"></i></div>
            <div class="attention-card-body">
                <span class="attention-card-count" id="attn-interviews">—</span>
                <span class="attention-card-label">Interviews scheduled this week</span>
            </div>
            <i data-lucide="chevron-right" class="attention-card-chevron" size="18"></i>
        </a>
        <a href="payroll.php" class="attention-card attention-card-neutral">
            <div class="attention-card-icon"><i data-lucide="banknote" size="20"></i></div>
            <div class="attention-card-body">
                <span class="attention-card-count" id="attn-payroll-month">—</span>
                <span class="attention-card-label" id="attn-payroll-label">Payroll cycle — review before run</span>
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
                        <p class="dashboard-chart-sub" id="dash-attendance-trend-sub">Mon–Fri present count</p>
                    </div>
                </div>
                <div class="chart-canvas-wrap">
                    <div id="adminAttendanceChart" class="admin-apex-chart" style="min-height:240px;"></div>
                </div>
            </div>
            <div class="card dashboard-chart-card dashboard-chart-card--compact">
                <div class="card-header-v2 card-header-v2--compact">
                    <div>
                        <h4 class="font-600">Today’s mix</h4>
                        <p class="dashboard-chart-sub">On time, late, half day &amp; absent</p>
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
                    <p class="dashboard-chart-sub">Monthly salary total per department (active staff)</p>
                </div>
            </div>
            <div class="chart-canvas-wrap chart-canvas-wrap--salary-vibrant" id="salaryByDeptVibrantWrap">
                <div id="adminSalaryChart" class="admin-apex-chart" style="min-height:320px;"
                    aria-label="Salary by department chart"></div>
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
                    <h4 class="font-600">Leave by type</h4>
                    <p class="dashboard-chart-sub" id="dash-leave-period-sub">Payroll period — approved days</p>
                </div>
            </div>
            <div class="leave-type-vibrant" id="leaveTypeVibrantWrap" aria-label="Leave days by type"></div>
        </div>
        <div class="card dashboard-chart-card">
            <div class="card-header-v2 card-header-v2--compact">
                <div>
                    <h4 class="font-600">Hiring funnel</h4>
                    <p class="dashboard-chart-sub" id="dash-funnel-sub">New → Interview → Offer → Hired (active pipeline)</p>
                </div>
            </div>
            <div class="chart-canvas-wrap chart-canvas-wrap--funnel">
                <div id="adminFunnelChart" class="hiring-funnel-vibrant" aria-label="Hiring funnel by stage"></div>
            </div>
        </div>
    </div>
</section>

<!-- Payroll & workforce -->
<section class="dashboard-section dashboard-section--enterprise" aria-label="Payroll and workforce">
    <div class="dashboard-section-head">
        <h4 class="font-600 dashboard-section-title">Payroll &amp; workforce</h4>
        <span class="dashboard-section-hint">Cycle status, payouts, and team movement</span>
    </div>
    <div class="dashboard-widgets-2 dash-ent-widgets">
        <article class="card dash-ent-card dash-ent-card--payroll">
            <header class="dash-ent-card__head">
                <div class="dash-ent-card__title-block">
                    <span class="dash-ent-card__icon dash-ent-card__icon--primary" aria-hidden="true">
                        <i data-lucide="wallet"></i>
                    </span>
                    <div>
                        <h4 class="dash-ent-card__title">Payroll overview</h4>
                        <p class="dash-ent-card__sub" id="dash-payroll-sub">—</p>
                    </div>
                </div>
                <span class="dash-ent-pill dash-ent-pill--warn hidden" id="dash-payroll-pending-badge" role="status">—</span>
            </header>
            <div class="dash-ent-kpi-grid dash-ent-kpi-grid--3">
                <div class="dash-ent-kpi">
                    <span class="dash-ent-kpi__label">Salary pool</span>
                    <span class="dash-ent-kpi__value" id="dash-payroll-pool">—</span>
                    <span class="dash-ent-kpi__hint">Active staff monthly salaries</span>
                </div>
                <div class="dash-ent-kpi">
                    <span class="dash-ent-kpi__label">Paid this cycle</span>
                    <span class="dash-ent-kpi__value dash-ent-kpi__value--success" id="dash-payroll-paid">—</span>
                    <span class="dash-ent-kpi__hint" id="dash-payroll-paid-hint">Processed payouts</span>
                </div>
                <div class="dash-ent-kpi">
                    <span class="dash-ent-kpi__label">Not paid yet</span>
                    <span class="dash-ent-kpi__value dash-ent-kpi__value--warn" id="dash-payroll-pending-amt">—</span>
                    <span class="dash-ent-kpi__hint" id="dash-payroll-pending-hint">Staff without paid payslip</span>
                </div>
            </div>
            <div class="dash-ent-meter">
                <div class="dash-ent-meter__head">
                    <span class="dash-ent-meter__title">Payroll completion</span>
                    <span class="dash-ent-meter__pct font-600" id="dash-payroll-completion-pct">0%</span>
                </div>
                <div class="dash-progress-track dash-ent-meter__track" role="progressbar" aria-valuemin="0"
                    aria-valuemax="100" aria-valuenow="0" id="dash-payroll-completion-track">
                    <span class="dash-progress-fill dash-progress-fill--success" id="dash-payroll-completion-bar"
                        style="width:0%"></span>
                </div>
                <p class="dash-ent-meter__meta" id="dash-payroll-completion-meta">—</p>
            </div>
            <footer class="dash-ent-card__foot">
                <p class="dash-ent-card__meta" id="dash-payroll-next">—</p>
                <a href="payroll.php" class="dash-ent-btn">
                    <span>Manage payroll</span>
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </a>
            </footer>
        </article>
        <article class="card dash-ent-card dash-ent-card--workforce">
            <header class="dash-ent-card__head">
                <div class="dash-ent-card__title-block">
                    <span class="dash-ent-card__icon dash-ent-card__icon--slate" aria-hidden="true">
                        <i data-lucide="users"></i>
                    </span>
                    <div>
                        <h4 class="dash-ent-card__title">Workforce snapshot</h4>
                        <p class="dash-ent-card__sub" id="dash-retention-sub">—</p>
                    </div>
                </div>
            </header>
            <div class="dash-ent-kpi-grid dash-ent-kpi-grid--3">
                <div class="dash-ent-kpi dash-ent-kpi--compact">
                    <span class="dash-ent-kpi__label">Active staff</span>
                    <span class="dash-ent-kpi__value" id="dash-retention-active">—</span>
                </div>
                <div class="dash-ent-kpi dash-ent-kpi--compact">
                    <span class="dash-ent-kpi__label">New hires</span>
                    <span class="dash-ent-kpi__value dash-ent-kpi__value--success" id="dash-retention-hires">—</span>
                    <span class="dash-ent-kpi__hint">This month</span>
                </div>
                <div class="dash-ent-kpi dash-ent-kpi--compact">
                    <span class="dash-ent-kpi__label">Exits</span>
                    <span class="dash-ent-kpi__value dash-ent-kpi__value--danger" id="dash-retention-exits">—</span>
                    <span class="dash-ent-kpi__hint">This month</span>
                </div>
            </div>
            <div class="dash-ent-exit-trend">
                <div class="dash-ent-exit-trend__head">
                    <span class="dash-ent-meter__title">Exit trend</span>
                    <span class="dash-ent-exit-trend__caption">Last 6 months</span>
                </div>
                <div class="dash-ent-exit-bars" id="dash-retention-exit-bars" role="img" aria-label="Monthly exits"></div>
            </div>
            <footer class="dash-ent-card__foot">
                <p class="dash-ent-card__meta">Track joinings, exits, and headcount changes</p>
                <a href="employees.php" class="dash-ent-btn dash-ent-btn--ghost">
                    <span>View employees</span>
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </a>
            </footer>
        </article>
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
                <a href="new-joining.php" class="dash-feed-link">View all</a>
            </div>
            <ul class="dash-feed-list" id="dash-joinings-feed">
                <li class="py-20 text-center font-13 text-light">Loading…</li>
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
            <ul class="dash-feed-list" id="dash-announcements-feed">
                <li class="py-20 text-center font-13 text-light">Loading…</li>
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
            <ul class="dash-feed-list" id="dash-notifications-feed">
                <li class="py-20 text-center font-13 text-light">Loading…</li>
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
                    <p class="dashboard-chart-sub">Latest KPI goals from reviews</p>
                </div>
                <a href="kpi-management.php" class="dash-feed-link">KPI hub</a>
            </div>
            <div class="dash-kpi-list" id="dash-kpi-list">
                <div class="dash-kpi-row">
                    <div class="dash-kpi-head">
                        <span class="dash-kpi-name">Sales revenue target</span>
                        <span class="dash-kpi-val">88%</span>
                    </div>
                    <div class="dash-progress-track dash-progress-track--sm"><span class="dash-progress-fill"
                            style="width:88%"></span></div>
                </div>
                <div class="dash-kpi-row">
                    <div class="dash-kpi-head">
                        <span class="dash-kpi-name">Hiring plan (Q1)</span>
                        <span class="dash-kpi-val">62%</span>
                    </div>
                    <div class="dash-progress-track dash-progress-track--sm"><span
                            class="dash-progress-fill dash-progress-fill--warning" style="width:62%"></span></div>
                </div>
                <div class="dash-kpi-row">
                    <div class="dash-kpi-head">
                        <span class="dash-kpi-name">Training completion</span>
                        <span class="dash-kpi-val">91%</span>
                    </div>
                    <div class="dash-progress-track dash-progress-track--sm"><span
                            class="dash-progress-fill dash-progress-fill--success" style="width:91%"></span></div>
                </div>
                <div class="dash-kpi-row">
                    <div class="dash-kpi-head">
                        <span class="dash-kpi-name">eNPS / satisfaction</span>
                        <span class="dash-kpi-val">+34</span>
                    </div>
                    <div class="dash-progress-track dash-progress-track--sm"><span
                            class="dash-progress-fill dash-progress-fill--info" style="width:72%"></span></div>
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
                        <p class="font-20 font-600 mb-5" id="dash-org-gaps">—</p>
                        <p class="font-13 text-light mb-0">Department heads without documented backup</p>
                    </div>
                </div>
                <p class="font-13 text-light mb-15">Use hierarchy view to validate managers, dotted lines, and open
                    headcount under each node.</p>
                <div class="dash-org-actions">
                    <a href="hierarchy.php" class="dash-org-btn dash-org-btn--primary">
                        <span class="dash-org-btn__lead" aria-hidden="true"><i data-lucide="network"></i></span>
                        <span class="dash-org-btn__label">Open hierarchy</span>
                        <i data-lucide="arrow-right" class="dash-org-btn__arrow" aria-hidden="true"></i>
                    </a>
                    <a href="department-management.php" class="dash-org-btn dash-org-btn--secondary">
                        <span class="dash-org-btn__lead" aria-hidden="true"><i data-lucide="building-2"></i></span>
                        <span class="dash-org-btn__label">Manage departments</span>
                        <i data-lucide="arrow-right" class="dash-org-btn__arrow" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


<script src="assets/js/dashboard.js"></script>
<?php include 'includes/footer.php'; ?>