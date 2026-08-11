(function () {
    const cfg = window.HRM_CONFIG || {};
    if (cfg.user_role !== 'HR') return;

    const permMeta = cfg.permissions_meta || {};
    const PAGE_HREF_MAP = permMeta.page_href_map || {};
    const PAGE_PERM_SOURCE = permMeta.page_parents || {};
    const pageToHref = permMeta.page_files || {};
    const fallbackPageOrder = permMeta.landing_order || [];
    const PAGE_VIEW_SOURCE = permMeta.page_parents || {};
    const pageToSelector = {};
    (permMeta.sidebar_page_keys || []).forEach((key) => {
        pageToSelector[key] = `[data-hr-page="${key}"]`;
    });

    const API = '/assets/api/hr_permissions_api.php';
    let lastRevision = cfg.permissions_revision || 0;
    const pageKey = cfg.page_key || null;
    const noPortalAccess = !!cfg.hr_no_portal_access;
    let revokeAlertOpen = false;

    let denyAlertOpen = false;

    const TYPE_LABELS = {
        view: 'View',
        create: 'Create',
        edit: 'Edit / Update',
        mark_read: 'Mark as Read',
        toggle_status: 'Active / Close',
        schedule_interview: 'Schedule Interview',
        update_pipeline: 'Update Pipeline',
        reject_ban: 'Reject / Ban',
        delete: 'Delete',
        export: 'Export / PDF',
        action: 'this action',
    };

    function permPageKey(pageKey) {
        return PAGE_PERM_SOURCE[pageKey] || pageKey;
    }

    function canAccessInterviewsModule() {
        return HR_PERMS.can('job-candidates', 'view') && HR_PERMS.can('job-candidates', 'schedule_interview');
    }

    function canViewPage(pageKey) {
        if (pageKey === 'interviews') {
            return canAccessInterviewsModule();
        }
        const viewKey = PAGE_PERM_SOURCE[pageKey] || pageKey;
        if (!HR_PERMS.can(viewKey, 'view')) return false;
        if (pageKey === 'create-job' && !HR_PERMS.can('job-list', 'create')) return false;
        return true;
    }

    const PAGE_RULES = {
        employees: {
            create: ['button[onclick*="openAddEmployeeModal"]', '#addEmployeeForm button[type="submit"]'],
            edit: ['.action-btn-edit', 'button[onclick*="openEditEmployeeModal"]', '#editEmployeeForm button[type="submit"]', 'button[onclick*="restoreEmployee"]', '.action-btn-restore'],
            delete: ['button[onclick*="deleteEmployee"]', '.action-btn-delete'],
        },
        attendance: {
            edit: ['button[onclick*="openBulkModal"]', '#saveBulkBtn', 'button[onclick*="openEditAttendance"]', '#attendanceEditForm button[type="submit"]'],
        },
        'attendance-log': {
            edit: [
                'button[onclick*="openEditModal"]',
                '#saveAttendanceBtn',
                '.calendar-day-cell-v2.pointer',
            ],
        },
        'leave-management': {
            edit: ['#openLeaveSettingsBtn', '.leave-btn-approve', '.leave-btn-reject', '.leave-btn-update', '#leaveQuotaSaveBtn'],
        },
        'new-joining': {
            create: [
                'button[onclick*="openHiringModal"]',
                '.new-joining-actions .action-btn-view',
                '[data-hr-perm-action="create"]',
                '#hireSubmitBtn',
                '#hireCandidateForm button[type="submit"]',
                '#candidateEmployeeModal input:not([type="hidden"])',
                '#candidateEmployeeModal select',
                '#candidateEmployeeModal textarea',
            ],
            delete: [
                '.new-joining-actions .action-btn-delete',
                'button[title="Reject candidate"]',
            ],
        },
        'event-calendar': {
            create: [
                'button[onclick*="openEventModal"]',
                '[data-hr-perm-action="create"]',
                '#eventModal[data-hr-mode="create"] #eventFormSubmitBtn',
                '#eventModal[data-hr-mode="create"] button[form="eventForm"]',
                '#eventModal[data-hr-mode="create"] input:not([type="hidden"])',
                '#eventModal[data-hr-mode="create"] select',
                '#eventModal[data-hr-mode="create"] textarea',
                '#eventModal[data-hr-mode="create"] .category-pill',
                '#eventModal[data-hr-mode="create"] #eventShowInAccount',
            ],
            edit: [
                '#editEventBtn',
                '[data-hr-perm-action="edit"]',
                '#eventModal[data-hr-mode="edit"] #eventFormSubmitBtn',
                '#eventModal[data-hr-mode="edit"] button[form="eventForm"]',
                '#eventModal[data-hr-mode="edit"] input:not([type="hidden"])',
                '#eventModal[data-hr-mode="edit"] select',
                '#eventModal[data-hr-mode="edit"] textarea',
                '#eventModal[data-hr-mode="edit"] .category-pill',
                '#eventModal[data-hr-mode="edit"] #eventShowInAccount',
            ],
            delete: ['#deleteEventBtnDetail'],
        },
        'job-list': {
            create: ['a[href="create-job.php"]', '.job-list-toolbar__create'],
            edit: ['a[href*="edit-job.php"]', '.action-btn[title="Edit Job"]'],
            toggle_status: ['.js-toggle-job-status', 'button[onclick*="toggleJobStatus"]'],
        },
        'create-job': {
            create: [
                '#createJobForm button[type="submit"]',
                '.js-job-form-submit',
                '#addQuestionBtn',
                '.btn-add-dotted',
                '.chip-btn',
                'button[onclick*="quickAddQuestion"]',
                '#createJobForm input:not([type="hidden"])',
                '#createJobForm textarea',
                '#createJobForm select',
            ],
        },
        'edit-job': {
            edit: [
                '#createJobForm button[type="submit"]',
                '.js-job-form-submit',
                '#addQuestionBtn',
                '.btn-add-dotted',
                '.chip-btn',
                'button[onclick*="quickAddQuestion"]',
                '#createJobForm input:not([type="hidden"])',
                '#createJobForm textarea',
                '#createJobForm select',
            ],
        },
        'job-candidates': {},
        'candidate-detail': {
            schedule_interview: [
                '[data-hr-perm-action="schedule_interview"]',
                '#scheduleInterviewModal button[type="submit"]',
                'button[form="scheduleInterviewForm"]',
            ],
            update_pipeline: [
                '[data-hr-perm-action="update_pipeline"]',
                '#statusTransitionModal button[type="submit"]',
                '#statusModalSubmitBtn',
            ],
            reject_ban: [
                '#rejectCandidateBtn',
                '#banCandidateBtn',
            ],
        },
        interviews: {
            schedule_interview: ['button[onclick*="schedule"]', '#interviewForm button[type="submit"]', 'button[onclick*="reschedule"]'],
        },
        payroll: {
            create: ['button[onclick*="openGenerateModal"]', 'button[onclick*="generateBulk"]', '#generatePayrollForm button[type="submit"]'],
            edit: ['button[onclick*="openEditPayroll"]', '#editPayrollForm button[type="submit"]', '.action-btn-edit'],
            export: ['button[onclick*="viewPayslip"]', 'a[href*="payslip-print"]'],
        },
        announcements: {
            create: [
                'button[onclick*="createAnnouncementModal"]',
                'button[form="announcementForm"]',
                '#createAnnouncementModal button[type="submit"]',
                '#announcementForm button[type="submit"]',
                '#createAnnouncementModal input',
                '#createAnnouncementModal .rich-text-editor',
                '#annTypeSelection .ann-type-card',
                '#deptSelection .category-pill',
            ],
            edit: [
                'button[onclick*="openEditAnnouncementModal"]',
                'button[form="editAnnouncementForm"]',
                '#editAnnouncementModal button[type="submit"]',
                '#editAnnouncementForm button[type="submit"]',
                '#editAnnouncementModal input',
                '#editAnnouncementModal .rich-text-editor',
                '#editAnnTypeSelection .ann-type-card',
                '#editDeptSelection .category-pill',
            ],
            delete: ['button[onclick*="deleteAnnouncement"]', 'button.action-btn.danger[title="Delete"]'],
        },
        notifications: {
            mark_read: ['#markAllReadBtn', '.noti-read-btn', 'button[onclick*="markRead"]'],
            delete: ['#clearAllBtn', 'button[onclick*="removeNotification"]', '.noti-remove-btn'],
        },
        'it-support': {
            edit: [
                '#btn-claim-ticket',
                '#btn-handover-ticket',
                '.btn-change-status',
                '#btn-reopen-ticket',
                '#btn-send-message',
                '#chat-message-input',
                '#is-internal-note',
                '[data-hr-perm-action="edit"]',
            ],
        },
        shifts: {
            create: ['button[onclick*="addShiftModal"]', '#addShiftFormSubmit'],
            edit: ['.action-btn-edit', '#editShiftFormSubmit'],
            delete: ['.action-btn-delete', 'button[onclick*="deleteShift"]'],
        },
        'department-management': {
            create: ['button[onclick*="addDeptModal"]', '#addDeptFormSubmit'],
            edit: ['.action-btn-edit', '#editDeptFormSubmit'],
            delete: ['.action-btn-delete', 'button[onclick*="deleteDept"]'],
        },
        'policy-management': {
            create: [
                '#policyBtnOpenAdd',
                'button[form="policyAddForm"]',
                '#policyAddModal button[type="submit"]',
                '#policyAddModal input:not([type="hidden"])',
                '#policyAddModal select',
                '#policyAddModal .rich-text-editor',
                '#policyAddModal .toolbar-btn',
            ],
            edit: [
                '.policy-btn-edit',
                'button[form="policyEditForm"]',
                '#policyEditModal button[type="submit"]',
                '#policyEditModal input:not([type="hidden"])',
                '#policyEditModal select',
                '#policyEditModal .rich-text-editor',
                '#policyEditModal .toolbar-btn',
            ],
            delete: ['.policy-btn-del'],
        },
        'payroll-settings': {
            edit: ['#payrollSettingsForm button[type="submit"]', 'button[onclick*="savePayroll"]'],
        },
        'hierarchy-settings': {
            edit: [
                '#hierarchySettingsForm button[type="submit"]',
                '#addManagerRow',
                '.remove-manager-row',
                'input[name="ceo_mode"]',
                '#ceoEmployeeId',
                '#ceoManualName',
                '#ceoManualTitle',
                '#ctoEmployeeId',
                '.manager-employee',
                '.manager-dept',
            ],
        },
        'kpi-management': {
            create: [
                'button[onclick*="openReviewModal"]',
                '#addReviewForm button[type="submit"]',
                '#addReviewModal input:not([type="hidden"])',
                '#addReviewModal select',
                '#addReviewModal textarea',
                '#addReviewModal .period-card',
                '#addReviewModal .sentiment-stars i',
                'button[onclick*="addCustomGoal"]',
            ],
        },
        'kpi-report': {
            create: [
                'button[onclick*="openReviewModal"]',
                '[data-hr-perm-action="create"]',
                '#addReviewForm button[type="submit"]',
                '#addReviewModal input:not([type="hidden"])',
                '#addReviewModal select',
                '#addReviewModal textarea',
                '#addReviewModal .period-card',
                '#addReviewModal .sentiment-stars i',
                'button[onclick*="addCustomGoal"]',
            ],
            edit: [
                '.kpi-history-actions .action-btn-edit',
                'button[onclick*="openEditReview"]',
                '#viewDetailEditBtn',
                '[data-hr-perm-action="edit"]',
            ],
            delete: [
                '.kpi-history-actions .action-btn-delete',
                'button[onclick*="deleteReview"]',
                '#viewDetailDeleteBtn',
                '[data-hr-perm-action="delete"]',
            ],
        },
        'payslip-print': {
            export: ['button[onclick*="print"]', '.no-print-btn', '.btn-print', '#printBtn'],
        },
    };

    function showAccessDenied(type, customText) {
        if (denyAlertOpen) return;
        const label = TYPE_LABELS[type] || TYPE_LABELS.action;
        const text = customText || `You do not have "${label}" permission for this page. Please contact your Admin.`;

        if (typeof Swal !== 'undefined') {
            denyAlertOpen = true;
            Swal.fire({
                icon: 'warning',
                title: 'Access Not Allowed',
                text,
                confirmButtonText: 'OK',
                confirmButtonColor: '#6c4cf1',
            }).finally(() => {
                denyAlertOpen = false;
            });
            return;
        }
        window.alert(text);
    }

    function resolveHrefPageKey(href) {
        if (!href) return null;
        
        let clean = href.split('?')[0].split('#')[0];
        
        // Strip domain and protocol if absolute URL
        if (clean.includes('://')) {
            clean = clean.split('://')[1].split('/').slice(1).join('/');
        }
        
        // Strip leading slash
        clean = clean.replace(/^\/+/, '');
        
        // Strip basePath/baseFolder if it starts with it
        const basePath = (cfg.basePath || '').replace(/^\/+|\/+$/g, '');
        if (basePath && clean.startsWith(basePath + '/')) {
            clean = clean.substring(basePath.length + 1);
        }
        
        // Strip public prefix if present
        if (clean.startsWith('public/')) {
            clean = clean.substring(7);
        }
        
        // Strip role prefix if present
        const rolePrefixes = ['hr', 'admin', 'user', 'employee'];
        for (const role of rolePrefixes) {
            if (clean.startsWith(role + '/')) {
                clean = clean.substring(role.length + 1);
                break;
            }
        }
        
        // Strip .php extension
        if (clean.toLowerCase().endsWith('.php')) {
            clean = clean.slice(0, -4);
        }
        
        // Try full cleaned path match
        if (PAGE_HREF_MAP[clean]) {
            return PAGE_HREF_MAP[clean];
        }
        
        // Fallback to last segment match
        const file = clean.split('/').pop();
        return PAGE_HREF_MAP[file] || null;
    }

    function unblockEl(el) {
        if (!el || el.dataset.hrPermBlocked !== '1') return;
        delete el.dataset.hrPermBlocked;
        delete el.dataset.hrPermType;
        el.classList.remove('hr-perm-blocked');
        el.removeAttribute('aria-disabled');
        el.removeAttribute('title');

        if (el.tagName === 'A' && el.dataset.hrPermHref) {
            el.setAttribute('href', el.dataset.hrPermHref);
            delete el.dataset.hrPermHref;
        }

        if (el.tagName === 'BUTTON' || el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') {
            if (el.dataset.hrPermWasDisabled === '0') {
                el.disabled = false;
            }
            delete el.dataset.hrPermWasDisabled;
        }
        if (el.dataset.hrPermWasEditable === '1') {
            el.setAttribute('contenteditable', 'true');
        }
        delete el.dataset.hrPermWasEditable;
    }

    function blockEl(el, type) {
        if (!el) return;
        el.dataset.hrPermBlocked = '1';
        el.dataset.hrPermType = type;
        el.classList.add('hr-perm-blocked');
        el.setAttribute('aria-disabled', 'true');
        el.title = 'Access not allowed';

        if (el.tagName === 'A') {
            const href = el.getAttribute('href');
            if (href && href !== 'javascript:void(0)') {
                el.dataset.hrPermHref = href;
                el.setAttribute('href', 'javascript:void(0)');
            }
        }

        if (el.tagName === 'BUTTON' || el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') {
            if (el.dataset.hrPermWasDisabled === undefined) {
                el.dataset.hrPermWasDisabled = el.disabled ? '1' : '0';
            }
            el.disabled = true;
        }
        if (el.isContentEditable) {
            if (el.dataset.hrPermWasEditable === undefined) {
                el.dataset.hrPermWasEditable = el.isContentEditable ? '1' : '0';
            }
            el.setAttribute('contenteditable', 'false');
        }
    }

    function applySelectors(permKey, type, selectors) {
        (selectors || []).forEach((selector) => {
            document.querySelectorAll(selector).forEach((el) => {
                if (HR_PERMS.can(permKey, type)) unblockEl(el);
                else blockEl(el, type);
            });
        });
    }

    function applyCrossPagePermLinks() {
        document.querySelectorAll('[data-hr-perm-page]').forEach((el) => {
            const page = el.getAttribute('data-hr-perm-page');
            const type = el.getAttribute('data-hr-perm-type') || 'view';
            if (!page) return;
            if (HR_PERMS.can(page, type)) unblockEl(el);
            else blockEl(el, type);
        });
    }

    function applyInterviewsNavLinks() {
        document.querySelectorAll('a[href="interviews.php"], a[href$="/interviews.php"]').forEach((el) => {
            if (canAccessInterviewsModule()) unblockEl(el);
            else blockEl(el, 'schedule_interview');
        });
    }

    function applyOpenNewJoiningModalPermissions() {
        if (cfg.page_key !== 'new-joining') return;

        document.querySelectorAll(
            'button[onclick*="openHiringModal"], .new-joining-actions .action-btn-view, [data-hr-perm-action="create"]'
        ).forEach((el) => {
            if (HR_PERMS.can('new-joining', 'create')) unblockEl(el);
            else blockEl(el, 'create');
        });

        const modal = document.getElementById('candidateEmployeeModal');
        if (!modal || !modal.classList.contains('active')) return;

        const allowed = HR_PERMS.can('new-joining', 'create');
        modal.querySelectorAll(
            '#hireSubmitBtn, #hireCandidateForm button[type="submit"], input:not([type="hidden"]), select, textarea'
        ).forEach((el) => {
            if (allowed) unblockEl(el);
            else blockEl(el, 'create');
        });
    }

    function applyOpenEventModalPermissions() {
        if (cfg.page_key !== 'event-calendar') return;

        const detailModal = document.getElementById('eventDetailModal');
        if (detailModal && detailModal.classList.contains('active')) {
            const editBtn = document.getElementById('editEventBtn');
            const deleteBtn = document.getElementById('deleteEventBtnDetail');
            if (editBtn) {
                if (HR_PERMS.can('event-calendar', 'edit')) unblockEl(editBtn);
                else blockEl(editBtn, 'edit');
            }
            if (deleteBtn) {
                if (HR_PERMS.can('event-calendar', 'delete')) unblockEl(deleteBtn);
                else blockEl(deleteBtn, 'delete');
            }
        }

        const modal = document.getElementById('eventModal');
        if (!modal || !modal.classList.contains('active')) return;

        const mode = modal.getAttribute('data-hr-mode') || 'create';
        const permType = mode === 'edit' ? 'edit' : 'create';
        const allowed = HR_PERMS.can('event-calendar', permType);

        modal.querySelectorAll(
            '#eventFormSubmitBtn, button[form="eventForm"], input:not([type="hidden"]), select, textarea, .category-pill, #eventShowInAccount'
        ).forEach((el) => {
            if (allowed) unblockEl(el);
            else blockEl(el, permType);
        });
    }

    function applyOpenKpiModalPermissions() {
        if (cfg.page_key !== 'kpi-report' && cfg.page_key !== 'kpi-management') return;

        const permKey = 'kpi-management';

        const detailModal = document.getElementById('viewReviewDetailModal');
        if (detailModal && detailModal.classList.contains('active')) {
            const editBtn = document.getElementById('viewDetailEditBtn');
            const deleteBtn = document.getElementById('viewDetailDeleteBtn');
            if (editBtn) {
                if (HR_PERMS.can(permKey, 'edit')) unblockEl(editBtn);
                else blockEl(editBtn, 'edit');
            }
            if (deleteBtn) {
                if (HR_PERMS.can(permKey, 'delete')) unblockEl(deleteBtn);
                else blockEl(deleteBtn, 'delete');
            }
        }

        const modal = document.getElementById('addReviewModal');
        if (!modal || !modal.classList.contains('active')) return;

        const reviewIdEl = document.getElementById('modalReviewId');
        const isEdit = !!(reviewIdEl && reviewIdEl.value);
        const permType = isEdit ? 'edit' : 'create';
        const allowed = HR_PERMS.can(permKey, permType);

        modal.querySelectorAll(
            '#addReviewForm button[type="submit"], input:not([type="hidden"]), select, textarea, .period-card, .sentiment-stars i, button[onclick*="addCustomGoal"], .kpi-range-input, .goal-comment'
        ).forEach((el) => {
            if (allowed) unblockEl(el);
            else blockEl(el, permType);
        });
    }

    function applyPageRules(pageKey) {
        const rules = PAGE_RULES[pageKey];
        if (!rules) return;
        const permKey = permPageKey(pageKey);
        Object.keys(rules).forEach((type) => applySelectors(permKey, type, rules[type]));
    }

    function applyGlobalActionButtons(pageKey) {
        const permKey = permPageKey(pageKey);
        document.querySelectorAll('.action-btn-edit').forEach((el) => {
            if (HR_PERMS.can(permKey, 'edit')) unblockEl(el);
            else blockEl(el, 'edit');
        });
        document.querySelectorAll('.action-btn-delete').forEach((el) => {
            if (HR_PERMS.can(permKey, 'delete')) unblockEl(el);
            else blockEl(el, 'delete');
        });
        document.querySelectorAll('.action-btn-create, .btn-add-dotted').forEach((el) => {
            if (HR_PERMS.can(permKey, 'create')) unblockEl(el);
            else blockEl(el, 'create');
        });
        document.querySelectorAll('.btn-export, [data-export="1"]').forEach((el) => {
            if (HR_PERMS.can(permKey, 'export')) unblockEl(el);
            else blockEl(el, 'export');
        });
    }

    function findDeniedAction(pageKey, target) {
        const rules = PAGE_RULES[pageKey];
        const permKey = permPageKey(pageKey);

        const actionEl = target.closest('[data-hr-perm-action]');
        if (actionEl) {
            const actionType = actionEl.getAttribute('data-hr-perm-action');
            if (actionType && !HR_PERMS.can(permKey, actionType)) {
                return actionType;
            }
        }

        const crossPageEl = target.closest('[data-hr-perm-page]');
        if (crossPageEl) {
            const crossPage = crossPageEl.getAttribute('data-hr-perm-page');
            const crossType = crossPageEl.getAttribute('data-hr-perm-type') || 'view';
            if (crossPage && !HR_PERMS.can(crossPage, crossType)) {
                return crossType;
            }
        }

        if (!rules) return null;

        for (const type of Object.keys(rules)) {
            if (HR_PERMS.can(permKey, type)) continue;
            for (const selector of rules[type]) {
                try {
                    if (target.closest(selector)) return type;
                } catch (_) {
                    /* invalid selector */
                }
            }
        }
        return null;
    }

    function findDeniedGlobalAction(pageKey, target) {
        const permKey = permPageKey(pageKey);
        const checks = [
            ['edit', '.action-btn-edit'],
            ['delete', '.action-btn-delete'],
            ['create', '.action-btn-create, .btn-add-dotted'],
            ['export', '.btn-export, [data-export="1"]'],
        ];
        for (const [type, selector] of checks) {
            if (HR_PERMS.can(permKey, type)) continue;
            try {
                if (target.closest(selector)) return type;
            } catch (_) {
                /* invalid selector */
            }
        }
        return null;
    }

    function handleRestrictedClick(e) {
        const blocked = e.target.closest('[data-hr-perm-blocked="1"]');
        if (blocked) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            showAccessDenied(blocked.dataset.hrPermType || 'action');
            return true;
        }

        const pageKey = cfg.page_key;
        if (pageKey) {
            const deniedType = findDeniedAction(pageKey, e.target) || findDeniedGlobalAction(pageKey, e.target);
            if (deniedType) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                showAccessDenied(deniedType);
                return true;
            }
        }

        const menuEl = e.target.closest('[data-hr-page]');
        const menuLink = e.target.closest('a[href]');
        if (menuEl && menuLink) {
            const menuKey = menuEl.getAttribute('data-hr-page');
            if (menuKey && !canViewPage(menuKey)) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                showAccessDenied(menuKey === 'create-job' && HR_PERMS.can('job-list', 'view') ? 'create' : 'view');
                return true;
            }
        }

        const navLink = e.target.closest('a[href]');
        if (navLink) {
            const hrefKey = resolveHrefPageKey(navLink.getAttribute('href'));
            if (hrefKey && !canViewPage(hrefKey)) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                showAccessDenied('view');
                return true;
            }
        }

        return false;
    }

    function wrapFetchForPermissionErrors() {
        if (window.__hrPermFetchWrapped) return;
        window.__hrPermFetchWrapped = true;
        const nativeFetch = window.fetch.bind(window);

        window.fetch = function (...args) {
            const init = args[1] || {};
            const method = String(init.method || 'GET').toUpperCase();
            const showDeniedOnError = init.hrShowDeniedOnError === true;

            return nativeFetch(...args).then(async (response) => {
                try {
                    const cloned = response.clone();
                    const contentType = cloned.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        const data = await cloned.json();
                        const msg = (data && (data.message || data.error || '')).toString().toLowerCase();
                        // Only alert on explicit user actions (POST) — not background GET polls
                        if (
                            showDeniedOnError
                            || method === 'POST'
                        ) {
                            if (
                                data
                                && (data.success === false || data.status === 'error')
                                && msg.includes('do not have permission')
                            ) {
                                showAccessDenied('action', 'You do not have permission to perform this action.');
                            }
                        }
                    }
                } catch (_) {
                    /* non-json response */
                }
                return response;
            });
        };
    }

    window.HR_PERMS = {
        can(pageKey, type) {
            const p = (cfg.permissions || {})[pageKey];
            if (!p) return false;
            const map = {
                view: 'can_view',
                create: 'can_create',
                edit: 'can_edit',
                mark_read: 'can_edit',
                toggle_status: 'can_delete',
                schedule_interview: 'can_create',
                update_pipeline: 'can_edit',
                reject_ban: 'can_delete',
                delete: 'can_delete',
                export: 'can_export',
            };
            return !!p[map[type]];
        },
        showDenied: showAccessDenied,
        refresh() {
            applyPageRules(cfg.page_key);
            applyGlobalActionButtons(cfg.page_key);
            applyCrossPagePermLinks();
            applyInterviewsNavLinks();
            applyOpenNewJoiningModalPermissions();
            applyOpenEventModalPermissions();
            applyOpenKpiModalPermissions();
        },
    };

    let observer = null;
    let refreshScheduled = false;

    function scheduleRefresh() {
        if (refreshScheduled) return;
        refreshScheduled = true;
        window.requestAnimationFrame(() => {
            if (observer) observer.disconnect();
            HR_PERMS.refresh();
            if (observer) observer.observe(document.body, { childList: true, subtree: true });
            refreshScheduled = false;
        });
    }

    function boot() {
        document.addEventListener('click', handleRestrictedClick, true);
        wrapFetchForPermissionErrors();
        HR_PERMS.refresh();

        observer = new MutationObserver(() => {
            scheduleRefresh();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

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

    function effectiveViewAllowed(permissions, targetPageKey) {
        if (!permissions) return false;
        if (targetPageKey === 'interviews') {
            const pool = permissions['job-candidates'];
            return !!(pool && Number(pool.can_view) === 1 && Number(pool.can_create) === 1);
        }
        const src = PAGE_VIEW_SOURCE[targetPageKey] || targetPageKey;
        return !!(permissions[src] && Number(permissions[src].can_view) === 1);
    }

    function effectiveSidebarAllowed(permissions, targetPageKey) {
        if (targetPageKey === 'interviews') {
            return effectiveViewAllowed(permissions, targetPageKey);
        }
        if (!effectiveViewAllowed(permissions, targetPageKey)) return false;
        if (targetPageKey === 'create-job') {
            return !!(permissions['job-list'] && Number(permissions['job-list'].can_create) === 1);
        }
        return true;
    }

    function syncSidebar(permissions) {
        Object.keys(pageToSelector).forEach((key) => {
            const el = document.querySelector(pageToSelector[key]);
            if (!el) return;
            const allowed = effectiveSidebarAllowed(permissions, key);
            el.style.display = allowed ? '' : 'none';
        });

        document.querySelectorAll('.menu-label').forEach((label) => {
            let node = label.nextElementSibling;
            let visible = false;
            while (node && !node.classList.contains('menu-label')) {
                if (node.matches('[data-hr-page], .has-submenu') && node.style.display !== 'none') {
                    const childVisible = node.querySelector('[data-hr-page]')
                        ? Array.from(node.querySelectorAll('[data-hr-page]')).some((c) => c.style.display !== 'none')
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
            url: '/dashboard',
            message: 'Your access to the dashboard has been revoked by Admin. Please contact your Admin or use Logout.',
        };
    }

    function resolveRevokeRedirect(permissions, indexAccess) {
        const hasDashboard = indexAccess && indexAccess.can_view;
        if (hasDashboard) {
            return {
                url: '/dashboard?access_denied=1',
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
            url: '/dashboard',
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
                Object.keys(cfg.permissions).forEach((k) => { delete cfg.permissions[k]; });
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
            const tasks = [fetch(`${API}?action=my_permissions`).then((r) => r.json())];

            const shouldCheckCurrentPage = pageKey && !noPortalAccess;
            if (shouldCheckCurrentPage) {
                tasks.push(fetch(`${API}?action=check_access&page=${encodeURIComponent(pageKey)}`).then((r) => r.json()));
                if (pageKey !== 'index') {
                    tasks.push(fetch(`${API}?action=check_access&page=index`).then((r) => r.json()));
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
    // Live updates handled via WebSocket (permissions_updated event)

    // Real-Time Instant Sync via WebSocket & Global Listener
    window.refreshPermissions = pollPermissions;
    window.addEventListener('permissions_updated', function() {
        pollPermissions();
    });
})();
