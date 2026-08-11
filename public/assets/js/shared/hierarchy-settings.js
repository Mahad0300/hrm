document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('hierarchySettingsForm');
    const managerRows = document.getElementById('managerRows');
    const noManagersMsg = document.getElementById('noManagersMsg');
    const ceoEmployeeBlock = document.getElementById('ceoEmployeeBlock');
    const ceoManualBlock = document.getElementById('ceoManualBlock');
    const ceoEmployeeId = document.getElementById('ceoEmployeeId');
    const ctoEmployeeId = document.getElementById('ctoEmployeeId');

    let employees = [];
    let allRawEmployees = [];
    let departments = [];
    let managerAssignments = [];

    function employeeLabel(emp) {
        const name = [emp.first_name, emp.middle_name, emp.last_name].filter(Boolean).join(' ').trim();
        const title = emp.job_title ? ` — ${emp.job_title}` : '';
        const dept = emp.department_name ? ` (${emp.department_name})` : '';
        return `${name}${title}${dept}`;
    }

    function employeeOptions(selectedId = '') {
        return '<option value="">Select employee...</option>' +
            employees.map(emp => `<option value="${emp.id}" ${String(selectedId) === String(emp.id) ? 'selected' : ''}>${employeeLabel(emp)}</option>`).join('');
    }

    function toggleCeoMode(mode) {
        const isEmployee = mode === 'employee';
        ceoEmployeeBlock.classList.toggle('hidden', !isEmployee);
        ceoManualBlock.classList.toggle('hidden', isEmployee);
    }

    function updateManagersEmptyState() {
        const hasRows = managerRows.children.length > 0;
        noManagersMsg.classList.toggle('hidden', hasRows);
    }

    function createManagerRow(data = {}) {
        const row = document.createElement('div');
        row.className = 'manager-row';
        row.innerHTML = `
            <div class="manager-row__header">
                <div class="form-group mb-0">
                    <label class="admin-form-label">Manager Employee</label>
                    <select class="form-control bg-white-input manager-employee">${employeeOptions(data.employee_id || '')}</select>
                </div>
                <button type="button" class="btn btn-outline btn-sm danger-text remove-manager-row">
                    <i data-lucide="trash-2" size="16"></i>
                    <span>Remove</span>
                </button>
            </div>
            <div class="manager-row__depts">
                <label class="admin-form-label">Departments Managed</label>
                <div class="dept-checkboxes"></div>
            </div>
        `;

        const checkboxWrap = row.querySelector('.dept-checkboxes');
        const selected = new Set((data.department_ids || []).map(String));
        departments.forEach(dept => {
            checkboxWrap.insertAdjacentHTML('beforeend', `
                <label class="dept-checkbox-item">
                    <input type="checkbox" class="manager-dept" value="${dept.id}" ${selected.has(String(dept.id)) ? 'checked' : ''}>
                    <span>${dept.name}</span>
                </label>
            `);
        });

        row.querySelector('.remove-manager-row').addEventListener('click', () => {
            row.remove();
            updateManagersEmptyState();
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        managerRows.appendChild(row);
        updateManagersEmptyState();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function collectManagers() {
        return Array.from(managerRows.querySelectorAll('.manager-row')).map(row => {
            const employee_id = row.querySelector('.manager-employee')?.value || '';
            const department_ids = Array.from(row.querySelectorAll('.manager-dept:checked')).map(cb => parseInt(cb.value, 10));
            return { employee_id, department_ids };
        }).filter(item => item.employee_id);
    }

    function applyDepartmentFilter(selectedDeptId) {
        if (selectedDeptId) {
            employees = allRawEmployees.filter(emp => String(emp.department_id) === String(selectedDeptId) || emp.role === 'Admin' || emp.role === 'HR');
        } else {
            employees = allRawEmployees;
        }
    }

    async function loadSettings() {
        const response = await fetch('/assets/api/hierarchy_settings_handler.php?action=fetch');
        const result = await response.json();
        if (result.status !== 'success') {
            throw new Error(result.message || 'Failed to load hierarchy settings.');
        }

        allRawEmployees = result.employees || [];
        departments = result.departments || [];
        managerAssignments = result.managers || [];

        const settings = result.settings || {};

        // Populate Management Department dropdown
        const deptSelect = document.getElementById('managementDeptId');
        if (deptSelect) {
            deptSelect.innerHTML = '<option value="">Select Department...</option>' +
                departments.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
            deptSelect.value = settings.management_dept_id || '';
        }

        applyDepartmentFilter(settings.management_dept_id || '');

        ceoEmployeeId.innerHTML = '<option value="">Select CEO...</option>' +
            employees.map(emp => `<option value="${emp.id}">${employeeLabel(emp)}</option>`).join('');
        ctoEmployeeId.innerHTML = '<option value="">Select CIO (optional)...</option>' +
            employees.map(emp => `<option value="${emp.id}">${employeeLabel(emp)}</option>`).join('');

        const ceoMode = settings.ceo_mode === 'employee' ? 'employee' : 'manual';
        document.querySelector(`input[name="ceo_mode"][value="${ceoMode}"]`).checked = true;
        toggleCeoMode(ceoMode);

        ceoEmployeeId.value = settings.ceo_employee_id || '';
        document.getElementById('ceoManualName').value = settings.ceo_manual_name || '';
        document.getElementById('ceoManualTitle').value = settings.ceo_manual_title || 'CEO';
        ctoEmployeeId.value = settings.cto_employee_id || '';

        managerRows.innerHTML = '';
        if (managerAssignments.length) {
            managerAssignments.forEach(row => createManagerRow(row));
        } else {
            updateManagersEmptyState();
        }
    }

    // Dynamic Filter Change Listener
    document.getElementById('managementDeptId')?.addEventListener('change', (e) => {
        const val = e.target.value;
        const currentCeo = ceoEmployeeId.value;
        const currentCto = ctoEmployeeId.value;

        // Remember existing manager selections for active rows
        const activeRows = Array.from(managerRows.querySelectorAll('.manager-row')).map(row => {
            const selectEl = row.querySelector('.manager-employee');
            const deptsChecked = Array.from(row.querySelectorAll('.manager-dept:checked')).map(cb => cb.value);
            return {
                selectEl: selectEl,
                selectedValue: selectEl?.value || '',
                deptsChecked: deptsChecked
            };
        });

        applyDepartmentFilter(val);

        // Re-populate CEO & CTO select boxes
        ceoEmployeeId.innerHTML = '<option value="">Select CEO...</option>' +
            employees.map(emp => `<option value="${emp.id}">${employeeLabel(emp)}</option>`).join('');
        ceoEmployeeId.value = employees.some(emp => String(emp.id) === String(currentCeo)) ? currentCeo : '';

        ctoEmployeeId.innerHTML = '<option value="">Select CIO (optional)...</option>' +
            employees.map(emp => `<option value="${emp.id}">${employeeLabel(emp)}</option>`).join('');
        ctoEmployeeId.value = employees.some(emp => String(emp.id) === String(currentCto)) ? currentCto : '';

        // Re-populate Manager selects for each row
        activeRows.forEach(row => {
            if (row.selectEl) {
                row.selectEl.innerHTML = employeeOptions(row.selectedValue);
                // If previous manager is still in the new filtered list, keep them. Otherwise set to empty.
                if (!employees.some(emp => String(emp.id) === String(row.selectedValue))) {
                    row.selectEl.value = '';
                }
            }
        });
    });

    document.querySelectorAll('input[name="ceo_mode"]').forEach(radio => {
        radio.addEventListener('change', (e) => toggleCeoMode(e.target.value));
    });

    document.getElementById('addManagerRow')?.addEventListener('click', () => createManagerRow());

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const ceoMode = document.querySelector('input[name="ceo_mode"]:checked')?.value || 'manual';
        if (ceoMode === 'employee' && ceoEmployeeId.value && ctoEmployeeId.value
            && ceoEmployeeId.value === ctoEmployeeId.value) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'CEO and CIO cannot be the same person.',
                confirmButtonColor: '#6c4cf1',
            });
            return;
        }
        const payload = {
            ceo_mode: ceoMode,
            ceo_employee_id: ceoEmployeeId.value,
            ceo_manual_name: document.getElementById('ceoManualName').value.trim(),
            ceo_manual_title: document.getElementById('ceoManualTitle').value.trim() || 'CEO',
            cto_employee_id: ctoEmployeeId.value,
            management_dept_id: document.getElementById('managementDeptId')?.value || '',
            managers: collectManagers(),
        };

        const submitBtn = form.querySelector('button[type="submit"]');
        const original = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader-2" class="spin" size="18"></i><span>Saving...</span>';
        if (typeof lucide !== 'undefined') lucide.createIcons();

        try {
            const response = await fetch('/assets/api/hierarchy_settings_handler.php?action=save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const result = await response.json();
            if (result.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Saved', text: result.message, confirmButtonColor: '#6c4cf1' });
                await loadSettings();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message || 'Save failed.', confirmButtonColor: '#6c4cf1' });
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Unable to save hierarchy settings.', confirmButtonColor: '#6c4cf1' });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = original;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    });

    loadSettings().catch(err => {
        Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#6c4cf1' });
    });
});
