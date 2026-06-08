(function () {
    const cfg = window.HRM_CONFIG || {};
    if (cfg.user_role !== 'HR') return;

    const API = '../includes/api/access_control_handler.php';
    let lastRevision = cfg.permissions_revision || 0;
    const pageKey = cfg.page_key || null;
    const noPortalAccess = !!cfg.hr_no_portal_access;
    let revokeAlertOpen = false;

    const pageToSelector = {
        index: '[data-hr-page="index"]',
        employees: '[data-hr-page="employees"]',
        attendance: '[data-hr-page="attendance"]',
        'leave-management': '[data-hr-page="leave-management"]',
        'new-joining': '[data-hr-page="new-joining"]',
        hierarchy: '[data-hr-page="hierarchy"]',
        'kpi-management': '[data-hr-page="kpi-management"]',
        'event-calendar': '[data-hr-page="event-calendar"]',
        'job-list': '[data-hr-page="job-list"]',
        'create-job': '[data-hr-page="create-job"]',
        'job-candidates': '[data-hr-page="job-candidates"]',
        interviews: '[data-hr-page="interviews"]',
        payroll: '[data-hr-page="payroll"]',
        'activity-logs': '[data-hr-page="activity-logs"]',
        announcements: '[data-hr-page="announcements"]',
        notifications: '[data-hr-page="notifications"]',
        'it-support': '[data-hr-page="it-support"]',
        shifts: '[data-hr-page="shifts"]',
        'department-management': '[data-hr-page="department-management"]',
        'policy-management': '[data-hr-page="policy-management"]',
        'payroll-settings': '[data-hr-page="payroll-settings"]',
    };

    const pageToHref = {
        index: 'index.php',
        employees: 'employees.php',
        attendance: 'attendance.php',
        'leave-management': 'leave-management.php',
        'new-joining': 'new-joining.php',
        hierarchy: 'hierarchy.php',
        'kpi-management': 'kpi-management.php',
        'event-calendar': 'event-calendar.php',
        'job-list': 'job-list.php',
        'create-job': 'create-job.php',
        'job-candidates': 'job-candidates.php',
        interviews: 'interviews.php',
        payroll: 'payroll.php',
        'activity-logs': 'activity-logs.php',
        announcements: 'announcements.php',
        notifications: 'notifications.php',
        'it-support': 'it-support.php',
        shifts: 'shifts.php',
        'department-management': 'department-management.php',
        'policy-management': 'policy-management.php',
        'payroll-settings': 'payroll-settings.php',
    };

    const fallbackPageOrder = [
        'index', 'employees', 'attendance', 'leave-management', 'new-joining', 'hierarchy',
        'kpi-management', 'event-calendar',
        'job-list', 'create-job', 'job-candidates', 'interviews',
        'payroll', 'activity-logs', 'announcements', 'notifications', 'it-support',
        'shifts', 'department-management', 'policy-management', 'payroll-settings',
    ];

    function isOnIndexPage() {
        return pageKey === 'index' || /\/index\.php$/i.test(window.location.pathname);
    }

    function cleanRevokeQueryParams() {
        const url = new URL(window.location.href);
        let changed = false;
        ['access_revoked', 'access_denied'].forEach((key) => {
            if (url.searchParams.has(key)) {
                url.searchParams.delete(key);
                changed = true;
            }
        });
        if (changed) {
            const qs = url.searchParams.toString();
            window.history.replaceState({}, '', url.pathname + (qs ? '?' + qs : ''));
        }
    }

    function revokeNoticeKey() {
        return `hr_revoke_notice_${pageKey}_${lastRevision}`;
    }

    function wasRevokeNoticeShown() {
        try {
            return sessionStorage.getItem(revokeNoticeKey()) === '1';
        } catch (e) {
            return false;
        }
    }

    function markRevokeNoticeShown() {
        try {
            sessionStorage.setItem(revokeNoticeKey(), '1');
        } catch (e) {
            /* ignore */
        }
    }

    const PAGE_VIEW_SOURCE = {
        'create-job': 'job-list',
        'edit-job': 'job-list',
        'attendance-log': 'attendance',
        'employee-profile': 'employees',
        'candidate-detail': 'job-candidates',
        'interviews': 'job-candidates',
        'kpi-report': 'kpi-management',
        'payslip-print': 'payroll',
    };

    function effectiveViewAllowed(permissions, pageKey) {
        if (!permissions) return false;
        if (pageKey === 'interviews') {
            const pool = permissions['job-candidates'];
            return !!(pool && Number(pool.can_view) === 1 && Number(pool.can_create) === 1);
        }
        const src = PAGE_VIEW_SOURCE[pageKey] || pageKey;
        return !!(permissions[src] && Number(permissions[src].can_view) === 1);
    }

    function effectiveSidebarAllowed(permissions, pageKey) {
        if (pageKey === 'interviews') {
            return effectiveViewAllowed(permissions, pageKey);
        }
        if (!effectiveViewAllowed(permissions, pageKey)) return false;
        if (pageKey === 'create-job') {
            return !!(permissions['job-list'] && Number(permissions['job-list'].can_create) === 1);
        }
        return true;
    }

    function syncSidebar(permissions) {
        Object.keys(pageToSelector).forEach(key => {
            const el = document.querySelector(pageToSelector[key]);
            if (!el) return;
            const allowed = effectiveSidebarAllowed(permissions, key);
            el.style.display = allowed ? '' : 'none';
        });

        document.querySelectorAll('.menu-label').forEach(label => {
            let node = label.nextElementSibling;
            let visible = false;
            while (node && !node.classList.contains('menu-label')) {
                if (node.matches('[data-hr-page], .has-submenu') && node.style.display !== 'none') {
                    const childVisible = node.querySelector('[data-hr-page]')
                        ? Array.from(node.querySelectorAll('[data-hr-page]')).some(c => c.style.display !== 'none')
                        : true;
                    if (childVisible) visible = true;
                }
                node = node.nextElementSibling;
            }
            label.style.display = visible ? '' : 'none';
        });
    }

    function findFallbackPage(permissions) {
        if (!permissions) return null;

        for (const key of fallbackPageOrder) {
            if (key === pageKey) continue;
            if (effectiveViewAllowed(permissions, key) && pageToHref[key]) {
                return key;
            }
        }

        for (const key of Object.keys(permissions)) {
            if (key === pageKey) continue;
            if (effectiveViewAllowed(permissions, key) && pageToHref[key]) {
                return key;
            }
        }

        return null;
    }

    function resolveDashboardRevokeRedirect(permissions) {
        const fallbackKey = findFallbackPage(permissions);
        if (fallbackKey) {
            return {
                url: pageToHref[fallbackKey],
                message: 'Your access to the dashboard has been revoked by Admin. You will be redirected to another page you can access.',
            };
        }

        return {
            url: 'index.php',
            message: 'Your access to the dashboard has been revoked by Admin. Please contact your Admin or use Logout.',
        };
    }

    function resolveRevokeRedirect(permissions, indexAccess) {
        const hasDashboard = indexAccess && indexAccess.can_view;
        if (hasDashboard) {
            return {
                url: 'index.php?access_denied=1',
                message: 'Your access to this page has been revoked by Admin. You will be redirected to the dashboard.',
            };
        }

        const fallbackKey = findFallbackPage(permissions);
        if (fallbackKey) {
            return {
                url: pageToHref[fallbackKey],
                message: 'Your access to this page has been revoked by Admin. You will be redirected to another page you can access.',
            };
        }

        return {
            url: 'index.php',
            message: 'Your access to this page has been revoked and you do not have dashboard access. Please contact your Admin or use Logout from the sidebar.',
        };
    }

    function handleRevoked(permissions, indexAccess) {
        if (noPortalAccess) {
            cleanRevokeQueryParams();
            return;
        }

        if (revokeAlertOpen || wasRevokeNoticeShown()) {
            return;
        }

        revokeAlertOpen = true;
        markRevokeNoticeShown();

        const redirect = isOnIndexPage()
            ? resolveDashboardRevokeRedirect(permissions)
            : resolveRevokeRedirect(permissions, indexAccess);

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Access Revoked',
                text: redirect.message,
                confirmButtonText: 'OK',
                confirmButtonColor: '#6c4cf1',
                allowOutsideClick: true,
                allowEscapeKey: true,
            }).then(() => {
                revokeAlertOpen = false;
                window.location.href = redirect.url;
            });
            return;
        }

        revokeAlertOpen = false;
        window.location.href = redirect.url;
    }

    function applyPermissionPayload(data, revision) {
        if (!data) return;
        if (revision && revision !== lastRevision) {
            lastRevision = revision;
            if (cfg.permissions) {
                Object.keys(cfg.permissions).forEach(k => { delete cfg.permissions[k]; });
                Object.assign(cfg.permissions, data);
            }
            syncSidebar(data);
            if (window.HR_PERMS && typeof window.HR_PERMS.refresh === 'function') {
                window.HR_PERMS.refresh();
            }
        }
    }

    async function pollPermissions() {
        try {
            const tasks = [fetch(`${API}?action=my_permissions`).then(r => r.json())];

            const shouldCheckCurrentPage = pageKey && !noPortalAccess;
            if (shouldCheckCurrentPage) {
                tasks.push(fetch(`${API}?action=check_access&page=${encodeURIComponent(pageKey)}`).then(r => r.json()));
                if (pageKey !== 'index') {
                    tasks.push(fetch(`${API}?action=check_access&page=index`).then(r => r.json()));
                }
            }

            const results = await Promise.all(tasks);
            const mine = results[0];
            let check = null;
            let indexAccess = null;
            if (shouldCheckCurrentPage) {
                check = results[1];
                indexAccess = pageKey === 'index' ? check : results[2];
            }

            if (mine.status === 'success') {
                applyPermissionPayload(mine.data || {}, mine.revision);

                if (shouldCheckCurrentPage && check && check.status === 'success' && !check.can_view) {
                    handleRevoked(mine.data || cfg.permissions || {}, indexAccess);
                }
            }
        } catch (e) {
            /* silent — guard should not break the portal */
        }
    }

    if (cfg.permissions) {
        syncSidebar(cfg.permissions);
    }

    if (isOnIndexPage()) {
        cleanRevokeQueryParams();
    }

    pollPermissions();
    setInterval(pollPermissions, 5000);
})();
