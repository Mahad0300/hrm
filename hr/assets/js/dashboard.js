// admin/assets/js/dashboard.js

document.addEventListener('DOMContentLoaded', function () {
    fetchAdminDashboard();
    startLiveClock();
    setInterval(fetchAdminDashboard, 300000);
});

function fetchAdminDashboard() {
    fetch('../includes/api/stats_handler.php?action=get_admin_dashboard')
        .then((res) => res.json())
        .then((res) => {
            if (res.status !== 'success' || !res.data) {
                console.error('Admin dashboard:', res.message || 'Unknown error');
                if (typeof window.initAdminChartsFromData === 'function') {
                    window.initAdminChartsFromData(null);
                }
                return;
            }
            bindAdminDashboard(res.data);
            if (typeof window.initAdminChartsFromData === 'function') {
                window.initAdminChartsFromData(res.data.charts);
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        })
        .catch((err) => {
            console.error('Dashboard Stats Error:', err);
            if (typeof window.initAdminChartsFromData === 'function') {
                window.initAdminChartsFromData(null);
            }
        });
}

function bindAdminDashboard(data) {
    const s = data.stats || {};
    const snap = data.snapshot || {};
    const attn = data.attention || {};
    const foot = data.footers || {};

    updateStat('stat-total-employees', s.total_employees);
    updateStat('stat-present-today', s.present_today);
    updateStat('stat-pending-leaves', s.pending_leaves);
    updateStat('stat-active-jobs', s.active_jobs);
    updateStat('stat-upcoming-events', s.upcoming_events);

    setText('snap-active-jobs', snap.active_jobs);
    setText('snap-kpi', snap.kpi_on_track ? snap.kpi_on_track + '%' : '—');
    setText('snap-notifications', snap.unread_notifications);
    setText('snap-shifts', snap.active_shifts);
    setText('snap-departments', snap.departments);
    setText('snap-roles', snap.roles);

    setText('attn-pending-leaves', attn.pending_leaves);
    setText('attn-candidates', attn.pipeline_candidates);
    setText('attn-interviews', attn.interviews_this_week);
    setText('attn-payroll-month', attn.payroll_label);
    setText(
        'attn-payroll-label',
        attn.payroll_pending > 0
            ? attn.payroll_pending + ' pending payroll record(s)'
            : 'Payroll cycle — review before run'
    );

    setText('stat-emp-footer', 'Active today: ' + (s.active_count ?? '—') + ' staff');
    setText('stat-present-footer', (s.on_leave_today ?? 0) + ' on leave today');
    setText('stat-present-pct', (s.present_pct ?? 0) + '%');
    setText('stat-jobs-footer', (s.active_jobs ?? 0) + ' open role(s)');

    if (foot.employees_compare !== undefined) {
        setText('stat-emp-footer', s.total_employees + ' employees · was ' + foot.employees_compare + ' at month start');
    }

    const trendEl = document.getElementById('stat-emp-trend');
    const trendWrap = document.querySelector('.stat-card .trend-up');
    if (trendEl && s.emp_trend !== null && s.emp_trend !== undefined) {
        const sign = s.emp_trend >= 0 ? '+' : '';
        trendEl.textContent = sign + s.emp_trend + '%';
        if (trendWrap) trendWrap.classList.remove('hidden');
    }

    if (foot.next_event && foot.next_event.title) {
        const t = foot.next_event.event_time
            ? formatTimeShort(foot.next_event.event_time)
            : '';
        setText(
            'stat-events-footer',
            'Next: ' + foot.next_event.title + (t ? ' (' + t + ')' : '')
        );
    } else {
        setText('stat-events-footer', (s.upcoming_events || 0) + ' upcoming event(s)');
    }

    bindPayrollBlock(data.payroll || {});
    bindRetentionBlock(data.retention || {});
    bindFeeds(data.feeds || {});
    bindKpiList(data.kpi_goals || []);
    setText('dash-org-gaps', data.org_gaps ?? '0');

    const trendSub = data.charts?.attendance_trend?.subtitle;
    if (trendSub) setText('dash-attendance-trend-sub', trendSub);

    const funnelTotal = data.charts?.funnel_total;
    if (funnelTotal !== undefined) {
        setText('dash-funnel-sub', funnelTotal + ' active candidate(s) in pipeline (excl. rejected/banned)');
    }

    const leavePeriod = data.charts?.leave_period_label;
    if (leavePeriod) setText('dash-leave-period-sub', leavePeriod + ' · approved days by type');
}

function bindPayrollBlock(p) {
    const label = p.label || 'Current';
    setText('dash-payroll-sub', label + ' · ' + (p.cycle_label || 'payroll cycle'));

    setText('dash-payroll-pool', formatPkr(p.salary_pool ?? p.allocated ?? 0));
    setText('dash-payroll-paid', formatPkr(p.paid_total ?? 0));
    setText('dash-payroll-pending-amt', formatPkr(p.pending_amount ?? 0));

    const paidCount = Number(p.paid_count) || 0;
    const pendingCount = Number(p.pending_count) || 0;
    const eligible = Number(p.eligible_staff) || 0;
    const completion = Math.min(100, Math.max(0, Number(p.completion_pct) || 0));

    setText('dash-payroll-paid-hint', paidCount + ' staff paid this cycle');
    setText(
        'dash-payroll-pending-hint',
        pendingCount > 0
            ? pendingCount + ' staff · payslip not paid yet'
            : 'All staff paid this cycle'
    );

    setText('dash-payroll-completion-pct', completion + '%');
    setText(
        'dash-payroll-completion-meta',
        paidCount + ' paid · ' + pendingCount + ' pending · ' + eligible + ' active staff'
    );

    const bar = document.getElementById('dash-payroll-completion-bar');
    const track = document.getElementById('dash-payroll-completion-track');
    if (bar) {
        bar.style.width = completion + '%';
        bar.classList.remove('dash-progress-fill--success', 'dash-progress-fill--warning', 'dash-progress-fill--info');
        if (completion >= 80) bar.classList.add('dash-progress-fill--success');
        else if (completion >= 35) bar.classList.add('dash-progress-fill--warning');
        else bar.classList.add('dash-progress-fill--info');
    }
    if (track) track.setAttribute('aria-valuenow', String(Math.round(completion)));

    const badge = document.getElementById('dash-payroll-pending-badge');
    if (badge) {
        if (pendingCount > 0) {
            badge.textContent = pendingCount + ' pending';
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    setText('dash-payroll-next', 'Cycle ends ' + formatDateShort(p.range_end));
}

function bindRetentionBlock(r) {
    const label = r.label || '';
    setText('dash-retention-sub', label ? label + ' · team movement' : 'Team movement this period');

    setText('dash-retention-active', r.active_staff ?? '—');
    setText('dash-retention-hires', r.new_hires_month ?? 0);
    setText('dash-retention-exits', r.exits_this_month ?? 0);

    const container = document.getElementById('dash-retention-exit-bars');
    if (!container) return;

    const labels = r.month_labels || [];
    const counts = r.exit_counts || [];
    if (!labels.length) {
        container.innerHTML = '<p class="dash-ent-exit-bars__empty">No exit data</p>';
        return;
    }

    const max = Math.max(...counts, 1);
    container.innerHTML = labels
        .map(function (lbl, i) {
            const n = counts[i] || 0;
            const h = Math.max(12, Math.round((n / max) * 100));
            return (
                '<div class="dash-ent-exit-bar" title="' +
                lbl +
                ': ' +
                n +
                ' exit' +
                (n === 1 ? '' : 's') +
                '">' +
                '<span class="dash-ent-exit-bar__col" style="height:' +
                h +
                '%"></span>' +
                '<span class="dash-ent-exit-bar__val">' +
                n +
                '</span>' +
                '<span class="dash-ent-exit-bar__lbl">' +
                lbl +
                '</span>' +
                '</div>'
            );
        })
        .join('');
}

function bindFeeds(feeds) {
    renderFeedList(
        'dash-joinings-feed',
        feeds.joinings,
        (j) => {
            const name = [j.first_name, j.middle_name, j.last_name].filter(Boolean).join(' ');
            const meta = [j.dept_name || 'General', j.joining_date ? 'Start ' + formatDateShort(j.joining_date) : 'Pending onboarding'].join(' · ');
            return feedItemHtml('new-joining.php', name, meta, 'dash-feed-dot--primary');
        },
        'No pending onboardings.',
        'new-joining.php'
    );

    renderFeedList(
        'dash-announcements-feed',
        feeds.announcements,
        (a) => feedItemHtml('announcements.php', a.title, 'Posted ' + timeAgoClient(a.created_at), null, false),
        'No active announcements.',
        'announcements.php'
    );

    renderFeedList(
        'dash-notifications-feed',
        feeds.notifications,
        (n) => {
            const msg = (n.message || '').substring(0, 50);
            return feedItemHtml('notifications.php', n.title, msg + (n.message && n.message.length > 50 ? '…' : '') + ' · ' + timeAgoClient(n.created_at), null, false);
        },
        'No recent notifications.',
        'notifications.php'
    );
}

function renderFeedList(containerId, items, mapFn, emptyText) {
    const el = document.getElementById(containerId);
    if (!el) return;
    if (!items || items.length === 0) {
        el.innerHTML = '<li class="py-20 text-center font-13 text-light">' + escapeHtml(emptyText) + '</li>';
        return;
    }
    el.innerHTML = items.map((item) => '<li>' + mapFn(item) + '</li>').join('');
}

function feedItemHtml(href, title, meta, dotClass, showDot = true) {
    return (
        '<a href="' +
        escapeHtml(href) +
        '" class="dash-feed-item">' +
        (showDot ? '<span class="dash-feed-dot ' + (dotClass || 'dash-feed-dot--primary') + '"></span>' : '') +
        '<span class="dash-feed-main">' +
        '<span class="dash-feed-title">' +
        escapeHtml(title) +
        '</span>' +
        '<span class="dash-feed-meta">' +
        escapeHtml(meta) +
        '</span></span>' +
        '<i data-lucide="chevron-right" size="16" class="dash-feed-chevron"></i></a>'
    );
}

function bindKpiList(goals) {
    const el = document.getElementById('dash-kpi-list');
    if (!el) return;
    if (!goals.length) {
        el.innerHTML = '<p class="font-13 text-light py-20 mb-0">No KPI goals recorded yet. <a href="kpi-management.php">Add reviews</a></p>';
        return;
    }
    const gradients = ['', ' dash-progress-fill--warning', ' dash-progress-fill--success', ' dash-progress-fill--info'];
    el.innerHTML = goals
        .map((g, i) => {
            const target = Math.max(1, parseInt(g.target_score, 10) || 100);
            const achieved = parseInt(g.achieved_score, 10) || 0;
            const pct = Math.min(100, Math.round((achieved / target) * 100));
            const fillClass = gradients[i % gradients.length] || '';
            return (
                '<div class="dash-kpi-row">' +
                '<div class="dash-kpi-head"><span class="dash-kpi-name">' + escapeHtml(g.goal_name) +
                '</span><span class="dash-kpi-val">' + pct + '%</span></motion>' +
                '<div class="dash-progress-track dash-progress-track--sm">' +
                '<span class="dash-progress-fill' + fillClass + '" style="width:' + pct + '%"></span></motion>' +
                '</motion>'
            );
        })
        .join('')
        .replace(/<\/motion>/g, '</div>')
        .replace(/<motion/g, '<div');
}

function updateStat(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    const current = parseInt(el.textContent.replace(/\D/g, ''), 10) || 0;
    const end = parseInt(value, 10) || 0;
    if (el.textContent === '...' || el.textContent === '—') {
        el.textContent = end;
        return;
    }
    animateValue(el, current, end, 800);
}

function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text ?? '—';
}

function animateValue(obj, start, end, duration) {
    if (start === end) {
        obj.textContent = end;
        return;
    }
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        obj.textContent = Math.floor(progress * (end - start) + start);
        if (progress < 1) window.requestAnimationFrame(step);
    };
    window.requestAnimationFrame(step);
}

function formatPkr(amount) {
    const n = parseFloat(amount) || 0;
    try {
        return new Intl.NumberFormat('en-PK', {
            style: 'currency',
            currency: 'PKR',
            maximumFractionDigits: 0,
        }).format(n);
    } catch {
        return 'PKR ' + Math.round(n).toLocaleString('en-PK');
    }
}

function formatDateShort(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr + (dateStr.length === 10 ? 'T12:00:00' : ''));
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
}

function formatTimeShort(t) {
    if (!t) return '';
    const parts = String(t).split(':');
    if (parts.length < 2) return t;
    let h = parseInt(parts[0], 10);
    const m = parts[1];
    const ap = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return h + ':' + m + ' ' + ap;
}

function timeAgoClient(datetime) {
    if (!datetime) return 'Recently';
    const ts = new Date(datetime.replace(' ', 'T')).getTime();
    const diff = Math.floor((Date.now() - ts) / 1000);
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
    if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
    return formatDateShort(datetime.substring(0, 10));
}

function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function startLiveClock() {
    const clockEl = document.getElementById('dashLiveClock');
    if (!clockEl) return;
    const updateClock = () => {
        clockEl.textContent = new Date().toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });
    };
    updateClock();
    setInterval(updateClock, 1000);
}
