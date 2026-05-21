<?php
/**
 * Admin dashboard aggregates — read-only queries, no side effects.
 */

if (!function_exists('adminDashboardLogicalDate')) {
    function adminDashboardLogicalDate() {
        return date('H') < 7 ? date('Y-m-d', strtotime('-1 day')) : date('Y-m-d');
    }
}

if (!function_exists('adminDashboardTimeAgo')) {
    function adminDashboardTimeAgo($datetime) {
        if (!$datetime) return 'Recently';
        $ts = strtotime($datetime);
        $diff = time() - $ts;
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        return date('d M Y', $ts);
    }
}

if (!function_exists('buildTodayAttendanceMix')) {
    /**
     * Today's status breakdown for active staff (excl. Admin/HR), leave excluded from absent.
     */
    function buildTodayAttendanceMix(PDO $pdo, string $today): array {
        $mix = ['ON TIME' => 0, 'LATE IN' => 0, 'HALF DAY' => 0, 'ABSENT' => 0];

        $stmt = $pdo->prepare("
            SELECT a.status, COUNT(DISTINCT a.employee_id) AS cnt
            FROM attendance a
            INNER JOIN employees e ON e.id = a.employee_id
            WHERE a.date = ?
            AND a.status IN ('ON TIME', 'LATE IN', 'HALF DAY', 'ABSENT')
            AND e.role NOT IN ('Admin', 'HR')
            AND e.deleted_at IS NULL
            AND e.status = 'Active'
            AND (e.joining_date IS NULL OR e.joining_date <= ?)
            GROUP BY a.status
        ");
        $stmt->execute([$today, $today]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (isset($mix[$row['status']])) {
                $mix[$row['status']] = (int) $row['cnt'];
            }
        }

        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM employees e
            WHERE e.role NOT IN ('Admin', 'HR')
            AND e.deleted_at IS NULL
            AND e.status = 'Active'
            AND (e.joining_date IS NULL OR e.joining_date <= ?)
            AND NOT EXISTS (
                SELECT 1 FROM leave_requests lr
                WHERE lr.employee_id = e.id
                AND lr.status = 'Approved'
                AND ? BETWEEN lr.start_date AND lr.end_date
            )
            AND NOT EXISTS (
                SELECT 1 FROM attendance a
                WHERE a.employee_id = e.id AND a.date = ?
            )
        ");
        $stmt->execute([$today, $today, $today]);
        $mix['ABSENT'] += (int) $stmt->fetchColumn();

        return $mix;
    }
}

if (!function_exists('buildPayrollWeeklyAttendanceTrend')) {
    /**
     * Current calendar week Mon–Fri only; days outside payroll range or in the future are null.
     */
    function buildPayrollWeeklyAttendanceTrend(PDO $pdo, string $today, string $activeSql): array {
        $payroll_month = date('Y-m', strtotime($today));
        $range = getPayrollRange($payroll_month);
        $payroll_start = $range['start'];
        $payroll_end = $range['end'];

        $monday = date('Y-m-d', strtotime('monday this week', strtotime($today)));
        $labels = [];
        $values = [];

        $presentSql = "
            SELECT COUNT(DISTINCT a.employee_id)
            FROM attendance a
            INNER JOIN employees e ON e.id = a.employee_id
            WHERE a.date = ?
            AND a.status IN ('ON TIME', 'LATE IN', 'HALF DAY')
            AND e.role NOT IN ('Admin', 'HR')
            AND e.deleted_at IS NULL
            AND e.status = 'Active'
            AND (e.joining_date IS NULL OR e.joining_date <= ?)
        ";

        for ($i = 0; $i < 5; $i++) {
            $d = date('Y-m-d', strtotime("$monday +$i days"));
            $labels[] = date('D', strtotime($d));

            if ($d < $payroll_start || $d > $payroll_end || $d > $today) {
                $values[] = null;
                continue;
            }

            $stmt = $pdo->prepare($activeSql);
            $stmt->execute([$d]);
            $day_active = (int) $stmt->fetchColumn();

            if ($day_active === 0) {
                $values[] = 0;
                continue;
            }

            $stmt = $pdo->prepare($presentSql);
            $stmt->execute([$d, $d]);
            $day_present = (int) $stmt->fetchColumn();
            $values[] = $day_present;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'subtitle' => sprintf(
                'Mon–Fri present count · payroll %s – %s',
                date('j M', strtotime($payroll_start)),
                date('j M', strtotime($payroll_end))
            ),
        ];
    }
}

if (!function_exists('buildAdminDashboardPayload')) {
    function buildAdminDashboardPayload(PDO $pdo, $user_id) {
        $today = adminDashboardLogicalDate();
        $calendar_today = date('Y-m-d');
        $now = new DateTime();

        // —— Active workforce (matches admin attendance scope) ——
        $activeSql = "
            SELECT COUNT(*) FROM employees e
            WHERE e.role NOT IN ('Admin', 'HR')
            AND e.deleted_at IS NULL
            AND e.status = 'Active'
            AND (e.joining_date IS NULL OR e.joining_date <= ?)
        ";
        $stmt = $pdo->prepare($activeSql);
        $stmt->execute([$today]);
        $active_count = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT a.employee_id)
            FROM attendance a
            INNER JOIN employees e ON e.id = a.employee_id
            WHERE a.date = ?
            AND a.status IN ('ON TIME', 'LATE IN', 'HALF DAY')
            AND e.role NOT IN ('Admin', 'HR')
            AND e.deleted_at IS NULL
            AND e.status = 'Active'
            AND (e.joining_date IS NULL OR e.joining_date <= ?)
        ");
        $stmt->execute([$today, $today]);
        $present_today = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT lr.employee_id)
            FROM leave_requests lr
            INNER JOIN employees e ON e.id = lr.employee_id
            WHERE lr.status = 'Approved'
            AND ? BETWEEN lr.start_date AND lr.end_date
            AND e.role NOT IN ('Admin', 'HR')
            AND e.deleted_at IS NULL
            AND e.status = 'Active'
        ");
        $stmt->execute([$today]);
        $on_leave_today = (int) $stmt->fetchColumn();

        $absent_today = max(0, $active_count - $present_today - $on_leave_today);

        $present_pct = $active_count > 0 ? round(($present_today / $active_count) * 100) : 0;

        // Total employees — Employee role only (excludes Admin & HR)
        $total_employees = (int) $pdo->query(
            "SELECT COUNT(*) FROM employees WHERE role = 'Employee' AND status NOT IN ('Terminated', 'Exit') AND deleted_at IS NULL"
        )->fetchColumn();

        $pending_leaves = (int) $pdo->query(
            "SELECT COUNT(*) FROM leave_requests WHERE status = 'Pending'"
        )->fetchColumn();

        $active_jobs = (int) $pdo->query(
            "SELECT COUNT(*) FROM jobs WHERE status = 'Active' AND deleted_at IS NULL"
        )->fetchColumn();

        // Employee trend vs last calendar month
        $first_this_month = date('Y-m-01');
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM employees
            WHERE role = 'Employee'
            AND status NOT IN ('Terminated', 'Exit')
            AND deleted_at IS NULL
            AND created_at < ?
        ");
        $stmt->execute([$first_this_month]);
        $employees_last_month = (int) $stmt->fetchColumn();
        $emp_trend = $employees_last_month > 0
            ? round((($total_employees - $employees_last_month) / $employees_last_month) * 100)
            : null;

        // Upcoming events
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE event_date >= ?");
        $stmt->execute([$calendar_today]);
        $upcoming_events = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT title, event_date, event_time
            FROM events WHERE event_date >= ?
            ORDER BY event_date ASC, event_time ASC LIMIT 1
        ");
        $stmt->execute([$calendar_today]);
        $next_event = $stmt->fetch(PDO::FETCH_ASSOC);

        // Snapshot tiles
        $kpi_on_track = 0;
        $stmt = $pdo->query("
            SELECT ROUND(AVG(CASE WHEN kg.target_score > 0 THEN (kg.achieved_score / kg.target_score) * 100 ELSE 0 END))
            FROM kpi_goals kg
        ");
        $kpi_avg = $stmt->fetchColumn();
        if ($kpi_avg !== false && $kpi_avg !== null) {
            $kpi_on_track = (int) $kpi_avg;
        }

        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM notification_recipients WHERE employee_id = ? AND is_read = 0
        ");
        $stmt->execute([$user_id]);
        $unread_notifications = (int) $stmt->fetchColumn();

        $active_shifts = (int) $pdo->query(
            "SELECT COUNT(*) FROM shifts WHERE deleted_at IS NULL"
        )->fetchColumn();

        $departments_count = (int) $pdo->query(
            "SELECT COUNT(*) FROM departments WHERE deleted_at IS NULL"
        )->fetchColumn();

        $roles_count = (int) $pdo->query(
            "SELECT COUNT(DISTINCT role) FROM employees WHERE deleted_at IS NULL"
        )->fetchColumn();

        // Attention
        $pipeline_candidates = (int) $pdo->query("
            SELECT COUNT(*) FROM candidates
            WHERE deleted_at IS NULL
            AND status IN ('New', 'Interview', 'Shortlisted', 'Offer')
        ")->fetchColumn();

        $week_start = date('Y-m-d', strtotime('monday this week'));
        $week_end = date('Y-m-d', strtotime('sunday this week'));
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM interviews
            WHERE status = 'Scheduled'
            AND DATE(interview_date) BETWEEN ? AND ?
        ");
        $stmt->execute([$week_start, $week_end]);
        $interviews_this_week = (int) $stmt->fetchColumn();

        $payroll_month = date('Y-m', strtotime($today));
        $range = getPayrollRange($payroll_month);
        $payroll_label = date('M Y', strtotime($payroll_month . '-01'));

        $attendance_trend = buildPayrollWeeklyAttendanceTrend($pdo, $today, $activeSql);
        $today_mix = buildTodayAttendanceMix($pdo, $today);

        // Headcount & salary by department
        $stmt = $pdo->query("
            SELECT d.name,
                COUNT(e.id) AS headcount,
                COALESCE(SUM(e.salary), 0) AS salary_total
            FROM departments d
            LEFT JOIN employees e ON e.department_id = d.id
                AND e.role NOT IN ('Admin', 'HR')
                AND e.deleted_at IS NULL
                AND e.status = 'Active'
            WHERE d.deleted_at IS NULL
            GROUP BY d.id, d.name
            HAVING headcount > 0 OR salary_total > 0
            ORDER BY headcount DESC, d.name ASC
        ");
        $dept_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $dept_labels = array_column($dept_rows, 'name');
        $dept_headcount = array_map('intval', array_column($dept_rows, 'headcount'));
        $dept_salary = array_map('floatval', array_column($dept_rows, 'salary_total'));

        // Leave by type (current payroll period)
        $payroll_period_start = $range['start'];
        $payroll_period_end = $range['end'];
        $stmt = $pdo->prepare("
            SELECT lt.name, COALESCE(SUM(
                DATEDIFF(
                    LEAST(lr.end_date, ?),
                    GREATEST(lr.start_date, ?)
                ) + 1
            ), 0) AS days
            FROM leave_types lt
            LEFT JOIN leave_requests lr ON lr.leave_type_id = lt.id
                AND lr.status = 'Approved'
                AND lr.start_date <= ?
                AND lr.end_date >= ?
            GROUP BY lt.id, lt.name
            ORDER BY days DESC, lt.name ASC
        ");
        $stmt->execute([
            $payroll_period_end,
            $payroll_period_start,
            $payroll_period_end,
            $payroll_period_start,
        ]);
        $leave_types_mtd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $leave_period_label = sprintf(
            'Payroll %s – %s',
            date('j M', strtotime($payroll_period_start)),
            date('j M Y', strtotime($payroll_period_end))
        );

        // Hiring funnel
        $stmt = $pdo->query("
            SELECT status, COUNT(*) AS cnt FROM candidates
            WHERE deleted_at IS NULL
            AND status NOT IN ('Rejected', 'Banned', 'Duplicated')
            GROUP BY status
        ");
        $funnel_map = ['New' => 0, 'Interview' => 0, 'Shortlisted' => 0, 'Offer' => 0, 'Hired' => 0];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (isset($funnel_map[$row['status']])) {
                $funnel_map[$row['status']] = (int) $row['cnt'];
            }
        }
        $funnel_labels = ['New', 'Interview', 'Shortlisted', 'Offer', 'Hired'];
        $funnel_data = [
            $funnel_map['New'],
            $funnel_map['Interview'],
            $funnel_map['Shortlisted'],
            $funnel_map['Offer'],
            $funnel_map['Hired'],
        ];
        $funnel_total = array_sum($funnel_data);

        // Turnover — exits last 6 months
        $turnover_labels = [];
        $turnover_values = [];
        for ($m = 5; $m >= 0; $m--) {
            $month_dt = (clone $now)->modify("-$m months");
            $ym = $month_dt->format('Y-m');
            $turnover_labels[] = $month_dt->format('M');

            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM employees
                WHERE status IN ('Terminated', 'Exit')
                AND DATE_FORMAT(COALESCE(deleted_at, updated_at), '%Y-%m') = ?
            ");
            $stmt->execute([$ym]);
            $exits = (int) $stmt->fetchColumn();
            $rate = $total_employees > 0 ? round(($exits / $total_employees) * 100, 1) : 0;
            $turnover_values[] = $rate;
        }

        // Payroll summary (enterprise dashboard)
        $stmt = $pdo->prepare("
            SELECT
                COALESCE(SUM(net_payable), 0) AS total_all,
                COALESCE(SUM(CASE WHEN status = 'Paid' THEN net_payable ELSE 0 END), 0) AS paid_total,
                COALESCE(SUM(CASE WHEN status = 'Pending' THEN net_payable ELSE 0 END), 0) AS pending_amount,
                COUNT(*) AS records_total,
                SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) AS paid_count
            FROM payroll
            WHERE month_year = ?
        ");
        $stmt->execute([$payroll_month]);
        $payroll_row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $payroll_committed = (float) ($payroll_row['total_all'] ?? 0);
        $payroll_paid_total = (float) ($payroll_row['paid_total'] ?? 0);
        $payroll_paid_count = (int) ($payroll_row['paid_count'] ?? 0);
        $payroll_records_total = (int) ($payroll_row['records_total'] ?? 0);

        $stmt = $pdo->query("
            SELECT COALESCE(SUM(salary), 0) AS salary_pool, COUNT(*) AS eligible_staff
            FROM employees
            WHERE role NOT IN ('Admin', 'HR')
            AND deleted_at IS NULL
            AND status = 'Active'
        ");
        $salary_row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $payroll_budget = (float) ($salary_row['salary_pool'] ?? 0);
        $payroll_eligible_staff = (int) ($salary_row['eligible_staff'] ?? 0);

        // Pending = payroll list jaisa: Paid nahi (no row OR status Pending)
        $stmt = $pdo->prepare("
            SELECT
                COALESCE(SUM(
                    CASE
                        WHEN p.id IS NULL THEN e.salary
                        WHEN p.status = 'Pending' THEN p.net_payable
                        ELSE 0
                    END
                ), 0) AS pending_amount,
                SUM(CASE WHEN p.id IS NULL OR p.status != 'Paid' THEN 1 ELSE 0 END) AS pending_count
            FROM employees e
            LEFT JOIN payroll p ON p.employee_id = e.id AND p.month_year = ?
            WHERE e.role NOT IN ('Admin', 'HR')
            AND e.deleted_at IS NULL
            AND e.status = 'Active'
        ");
        $stmt->execute([$payroll_month]);
        $pending_row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $payroll_pending_amount = (float) ($pending_row['pending_amount'] ?? 0);
        $payroll_pending_count = (int) ($pending_row['pending_count'] ?? 0);
        if ($payroll_budget <= 0) {
            $payroll_budget = max($payroll_committed, 1);
        }
        $budget_pct = min(100, round(($payroll_committed / $payroll_budget) * 100));
        $variance = $payroll_budget - $payroll_committed;
        $payroll_completion_pct = $payroll_eligible_staff > 0
            ? min(100, round(($payroll_paid_count / $payroll_eligible_staff) * 100))
            : 0;

        $payroll_cycle_label = sprintf(
            '%s – %s',
            date('j M', strtotime($range['start'])),
            date('j M Y', strtotime($range['end']))
        );

        // Workforce & retention snapshot
        $ym_current = date('Y-m', strtotime($today));
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM employees
            WHERE status IN ('Terminated', 'Exit')
            AND DATE_FORMAT(COALESCE(deleted_at, updated_at), '%Y-%m') = ?
        ");
        $stmt->execute([$ym_current]);
        $exits_this_month = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM employees
            WHERE deleted_at IS NULL
            AND joining_date IS NOT NULL
            AND DATE_FORMAT(joining_date, '%Y-%m') = ?
            AND status = 'Active'
        ");
        $stmt->execute([$ym_current]);
        $new_hires_month = (int) $stmt->fetchColumn();

        $retention_month_labels = [];
        $retention_exit_counts = [];
        for ($m = 5; $m >= 0; $m--) {
            $month_dt = (clone $now)->modify("-$m months");
            $ym = $month_dt->format('Y-m');
            $retention_month_labels[] = $month_dt->format('M');
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM employees
                WHERE status IN ('Terminated', 'Exit')
                AND DATE_FORMAT(COALESCE(deleted_at, updated_at), '%Y-%m') = ?
            ");
            $stmt->execute([$ym]);
            $retention_exit_counts[] = (int) $stmt->fetchColumn();
        }

        // KPI snapshot (latest goals)
        $stmt = $pdo->query("
            SELECT kg.goal_name, kg.target_score, kg.achieved_score
            FROM kpi_goals kg
            INNER JOIN kpi_reviews kr ON kr.id = kg.review_id
            ORDER BY kr.review_date DESC, kg.id ASC
            LIMIT 4
        ");
        $kpi_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Org gaps
        $org_gaps = (int) $pdo->query("
            SELECT COUNT(*) FROM departments
            WHERE deleted_at IS NULL AND (head IS NULL OR manager IS NULL)
        ")->fetchColumn();

        // Feeds
        $stmt = $pdo->query("
            SELECT e.id, e.first_name, e.middle_name, e.last_name, e.job_title, e.joining_date, d.name AS dept_name
            FROM employees e
            LEFT JOIN departments d ON d.id = e.department_id
            WHERE e.status = 'Pending' AND e.deleted_at IS NULL
            ORDER BY e.created_at DESC LIMIT 3
        ");
        $joinings_feed = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT title, created_at FROM announcements
            WHERE deleted_at IS NULL
            AND start_date <= ? AND end_date >= ?
            ORDER BY created_at DESC LIMIT 3
        ");
        $stmt->execute([$calendar_today, $calendar_today]);
        $announcements_feed = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT n.title, n.message, n.created_at
            FROM notifications n
            JOIN notification_recipients nr ON n.id = nr.notification_id
            WHERE nr.employee_id = ?
            ORDER BY n.created_at DESC LIMIT 3
        ");
        $stmt->execute([$user_id]);
        $notifications_feed = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'logical_date' => $today,
            'stats' => [
                'total_employees' => $total_employees,
                'present_today' => $present_today,
                'pending_leaves' => $pending_leaves,
                'active_jobs' => $active_jobs,
                'upcoming_events' => $upcoming_events,
                'on_leave_today' => $on_leave_today,
                'absent_today' => $absent_today,
                'active_count' => $active_count,
                'present_pct' => $present_pct,
                'emp_trend' => $emp_trend,
            ],
            'footers' => [
                'employees_compare' => $employees_last_month,
                'on_leave' => $on_leave_today,
                'next_event' => $next_event,
            ],
            'snapshot' => [
                'kpi_on_track' => $kpi_on_track,
                'unread_notifications' => $unread_notifications,
                'active_shifts' => $active_shifts,
                'departments' => $departments_count,
                'roles' => $roles_count,
                'active_jobs' => $active_jobs,
            ],
            'attention' => [
                'pending_leaves' => $pending_leaves,
                'pipeline_candidates' => $pipeline_candidates,
                'interviews_this_week' => $interviews_this_week,
                'payroll_label' => $payroll_label,
                'payroll_pending' => $payroll_pending_count,
            ],
            'charts' => [
                'attendance_trend' => $attendance_trend,
                'today_mix' => $today_mix,
                'dept_labels' => $dept_labels,
                'dept_headcount' => $dept_headcount,
                'dept_salary' => $dept_salary,
                'leave_types_mtd' => $leave_types_mtd,
                'leave_period_label' => $leave_period_label,
                'funnel_labels' => $funnel_labels,
                'funnel_data' => $funnel_data,
                'funnel_total' => $funnel_total,
                'turnover_labels' => $turnover_labels,
                'turnover_values' => $turnover_values,
            ],
            'payroll' => [
                'label' => $payroll_label,
                'cycle_label' => $payroll_cycle_label,
                'budget_pct' => $budget_pct,
                'allocated' => $payroll_budget,
                'salary_pool' => $payroll_budget,
                'committed' => $payroll_committed,
                'paid_total' => $payroll_paid_total,
                'pending_amount' => $payroll_pending_amount,
                'variance' => $variance,
                'range_start' => $range['start'],
                'range_end' => $range['end'],
                'pending_count' => $payroll_pending_count,
                'eligible_staff' => $payroll_eligible_staff,
                'paid_count' => $payroll_paid_count,
                'records_total' => $payroll_records_total,
                'completion_pct' => $payroll_completion_pct,
            ],
            'retention' => [
                'label' => $payroll_label,
                'active_staff' => $payroll_eligible_staff,
                'exits_this_month' => $exits_this_month,
                'new_hires_month' => $new_hires_month,
                'month_labels' => $retention_month_labels,
                'exit_counts' => $retention_exit_counts,
            ],
            'kpi_goals' => $kpi_rows,
            'org_gaps' => $org_gaps,
            'feeds' => [
                'joinings' => $joinings_feed,
                'announcements' => $announcements_feed,
                'notifications' => $notifications_feed,
            ],
        ];
    }
}
