(function () {
    const cfg = window.HRM_CONFIG || {};
    if (cfg.user_role !== 'HR' || !cfg.permissions) return;

    const perms = cfg.permissions;
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

    const PAGE_HREF_MAP = {
        'index.php': 'index',
        'employees.php': 'employees',
        'attendance.php': 'attendance',
        'leave-management.php': 'leave-management',
        'new-joining.php': 'new-joining',
        'hierarchy.php': 'hierarchy',
        'kpi-management.php': 'kpi-management',
        'kpi-report.php': 'kpi-report',
        'event-calendar.php': 'event-calendar',
        'job-list.php': 'job-list',
        'create-job.php': 'create-job',
        'edit-job.php': 'edit-job',
        'job-candidates.php': 'job-candidates',
        'candidate-detail.php': 'candidate-detail',
        'interviews.php': 'interviews',
        'payroll.php': 'payroll',
        'payslip-print.php': 'payslip-print',
        'activity-logs.php': 'activity-logs',
        'announcements.php': 'announcements',
        'notifications.php': 'notifications',
        'it-support.php': 'it-support',
        'shifts.php': 'shifts',
        'department-management.php': 'department-management',
        'policy-management.php': 'policy-management',
        'payroll-settings.php': 'payroll-settings',
        'employee-profile.php': 'employee-profile',
        'attendance-log.php': 'attendance-log',
        'role-management.php': 'role-management',
    };

    /** Detail pages inherit Create/Edit/Delete/Export from parent module */
    const PAGE_PERM_SOURCE = {
        'attendance-log': 'attendance',
        'employee-profile': 'employees',
        'create-job': 'job-list',
        'edit-job': 'job-list',
        'candidate-detail': 'job-candidates',
        'interviews': 'job-candidates',
        'kpi-report': 'kpi-management',
        'payslip-print': 'payroll',
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
        const clean = href.split('?')[0].split('#')[0];
        const file = clean.replace(/^\.\//, '').split('/').pop();
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
            const p = perms[pageKey];
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

    function boot() {
        document.addEventListener('click', handleRestrictedClick, true);
        wrapFetchForPermissionErrors();
        HR_PERMS.refresh();

        const observer = new MutationObserver(() => {
            window.requestAnimationFrame(() => HR_PERMS.refresh());
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
