document.addEventListener('DOMContentLoaded', () => {
    const saveBtn = document.getElementById('savePermissions');
    const table = document.getElementById('permissionsTable');
    const API = '../includes/api/access_control_handler.php';
    const CAP_TO_DB = {
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

    if (!table) return;

    const viewToggles = document.querySelectorAll('.view-toggle');

    viewToggles.forEach(toggle => {
        toggle.addEventListener('change', function () {
            const row = this.closest('tr');
            if (!row) return;
            const rowActions = row.querySelectorAll('.action-check');
            if (!this.checked) {
                rowActions.forEach(check => {
                    check.checked = false;
                    check.disabled = true;
                });
            } else {
                rowActions.forEach(check => {
                    check.disabled = false;
                });
            }
        });
    });

    function applyPermissionRow(pageKey, perm) {
        const row = table.querySelector(`tr[data-page="${pageKey}"]`);
        if (!row || !perm) return;

        const inheritsView = row.hasAttribute('data-inherits-view');
        const viewToggle = row.querySelector('.view-toggle');
        if (viewToggle && !inheritsView) {
            viewToggle.checked = !!perm.can_view;
        }

        row.querySelectorAll('.action-check').forEach((check) => {
            const cap = check.getAttribute('data-cap');
            const dbCol = CAP_TO_DB[cap];
            if (!dbCol) return;
            check.disabled = !perm.can_view;
            check.checked = !!perm[dbCol];
        });
    }

    function collectPermissions() {
        const rows = table.querySelectorAll('tbody tr[data-page]');
        const permissions = [];
        rows.forEach(row => {
            const pageKey = row.getAttribute('data-page');
            if (!pageKey || pageKey === 'role-management') return;
            const inheritsView = row.hasAttribute('data-inherits-view');
            const viewToggle = row.querySelector('.view-toggle');
            const payload = {
                page_key: pageKey,
                can_view: (!inheritsView && viewToggle && viewToggle.checked) ? 1 : 0,
                can_create: 0,
                can_edit: 0,
                can_delete: 0,
                can_export: 0,
            };
            row.querySelectorAll('.action-check').forEach((check) => {
                const cap = check.getAttribute('data-cap');
                const dbCol = CAP_TO_DB[cap];
                if (dbCol && check.checked) {
                    payload[dbCol] = 1;
                }
            });
            permissions.push(payload);
        });
        return permissions;
    }

    async function loadPermissions() {
        try {
            const res = await fetch(`${API}?action=fetch_permissions`);
            const json = await res.json();
            if (json.status !== 'success') {
                showToast(json.message || 'Failed to load permissions.', 'error');
                return;
            }
            (json.data || []).forEach(perm => applyPermissionRow(perm.page_key, perm));
        } catch (err) {
            showToast('Could not load access control settings.', 'error');
        }
    }

    saveBtn?.addEventListener('click', async () => {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i data-lucide="loader-2" class="spin" size="16"></i> <span>Saving...</span>';
        if (typeof lucide !== 'undefined') lucide.createIcons();

        try {
            const body = new FormData();
            body.append('action', 'save_permissions');
            body.append('permissions', JSON.stringify(collectPermissions()));

            const res = await fetch(API, { method: 'POST', body });
            const json = await res.json();

            if (json.status === 'success') {
                showToast(json.message || 'Access control settings updated successfully!', 'success');
                loadPermissions();
            } else {
                showToast(json.message || 'Failed to save permissions.', 'error');
            }
        } catch (err) {
            showToast('Could not save access control settings.', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i data-lucide="save" size="16"></i> <span>Save Changes</span>';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    });

    function showToast(message, type = 'success') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
            return;
        }
        const toast = document.createElement('div');
        toast.className = `custom-toast ${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i data-lucide="${type === 'success' ? 'check-circle' : 'info'}" size="18"></i>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(toast);
        if (typeof lucide !== 'undefined') lucide.createIcons();
        setTimeout(() => toast.classList.add('active'), 10);
        setTimeout(() => {
            toast.classList.remove('active');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    loadPermissions();
});
