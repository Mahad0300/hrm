// admin/assets/js/payroll.js

document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('payrollTableBody');
    const searchInput = document.getElementById('searchPayroll');
    const monthInput = document.getElementById('filterMonth');
    const statusSelect = document.getElementById('filterStatus');
    const deptSelect = document.getElementById('filterDept');
    const perPageSelect = document.getElementById('perPageSelect');
    const paginationInfo = document.getElementById('paginationInfo');
    const tableSummary = document.getElementById('tableSummary');
    const pageNumbersContainer = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    let currentPage = 1;
    let entriesLimit = 10;
    let searchTimer;

    async function fetchPayroll() {
        if (!tableBody) return;

        // Show loading state
        tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-50"><i data-lucide="loader-2" class="spin text-primary-color mb-10" size="32"></i><p class="text-light">Fetching records...</p></td></tr>`;
        lucide.createIcons();

        try {
            const search = searchInput ? searchInput.value : '';
            const month = monthInput ? monthInput.value : '';
            const status = statusSelect ? statusSelect.value : '';
            const dept = deptSelect ? deptSelect.value : '';

            const url = `assets/api/payroll_handler.php?action=fetch_payroll&page=${currentPage}&limit=${entriesLimit}&search=${encodeURIComponent(search)}&month=${month}&status=${status}&department=${dept}`;
            
            const response = await fetch(url);
            const result = await response.json();

            if (result.status === 'success') {
                renderPayroll(result.data);
                updatePagination(result.total, result.page, result.limit);
            } else {
                tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-30 text-danger">${result.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Fetch Payroll Error:', error);
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-30 text-danger">Unable to load payroll entries.</td></tr>`;
        }
    }

    function renderPayroll(payrolls) {
        if (!payrolls || payrolls.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-50"><p class="text-light">No payroll records found for the selected criteria.</p></td></tr>`;
            return;
        }

        tableBody.innerHTML = payrolls.map(p => {
            const statusBadge = p.status === 'Paid' ? 'badge-success' : 'badge-warning';
            // Handle month year display if it's null (from LEFT JOIN)
            const monthVal = p.month_year || document.getElementById('filterMonth').value;
            const formattedMonth = new Date(monthVal + '-01').toLocaleString('default', { month: 'long', year: 'numeric' });
            
            const basicSal = parseFloat(p.basic_salary || 0);
            const deductions = parseFloat(p.deductions || 0);
            const netPayable = p.net_payable ? parseFloat(p.net_payable) : basicSal;

            const fullName = `${p.first_name} ${p.middle_name ? p.middle_name + ' ' : ''}${p.last_name}`;

            return `
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="${p.profile_pic ? '../' + p.profile_pic : '../images/profile-image/default-avatar.svg'}" class="emp-avatar" alt="Avatar" onerror="this.src='../images/profile-image/default-avatar.svg'">
                            <div class="emp-info">
                                <span class="name">${fullName}</span>
                                <span class="email">EMP-0${p.employee_id}</span>
                            </div>
                        </div>
                    </td>
                    <td>${formattedMonth}</td>
                    <td>${basicSal.toLocaleString('en-US', { style: 'currency', currency: 'PKR' })}</td>
                    <td>${deductions.toLocaleString('en-US', { style: 'currency', currency: 'PKR' })}</td>
                    <td class="font-bold">${netPayable.toLocaleString('en-US', { style: 'currency', currency: 'PKR' })}</td>
                    <td><span class="badge ${statusBadge}">${p.status}</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="action-btn action-btn-edit" title="Edit" onclick="openEditPayrollModal('EMP-0${p.employee_id}', '${p.first_name} ${p.last_name}')"><i data-lucide="edit-2" size="16"></i></button>
                            <button class="action-btn action-btn-view" title="View Payslip"><i data-lucide="eye" size="16"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
        
        lucide.createIcons();
    }

    function updatePagination(total, page, limit) {
        const totalPages = Math.ceil(total / limit) || 1;
        const start = total === 0 ? 0 : (page - 1) * limit + 1;
        const end = Math.min(page * limit, total);
        
        const infoText = `Showing ${start} to ${end} of ${total} entries`;
        if (paginationInfo) paginationInfo.textContent = infoText;
        if (tableSummary) tableSummary.textContent = infoText;

        if (pageNumbersContainer) {
            pageNumbersContainer.innerHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = `action-btn ${i === page ? 'btn-active' : ''}`;
                btn.textContent = i;
                btn.onclick = () => { currentPage = i; fetchPayroll(); };
                pageNumbersContainer.appendChild(btn);
            }
        }

        if (prevBtn) {
            prevBtn.disabled = page === 1;
            prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; fetchPayroll(); } };
        }
        if (nextBtn) {
            nextBtn.disabled = page === totalPages;
            nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; fetchPayroll(); } };
        }
    }

    // Load Departments
    async function loadDeptData() {
        try {
            const response = await fetch('assets/api/employee_handler.php?action=fetch_requirements');
            const result = await response.json();
            if (result.status === 'success' && deptSelect) {
                const options = '<option value="">All Departments</option>' + 
                    result.departments.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
                deptSelect.innerHTML = options;
            }
        } catch (error) { console.error('Dept Load Error:', error); }
    }

    // Event Listeners
    if (perPageSelect) perPageSelect.onchange = () => { entriesLimit = perPageSelect.value === 'all' ? 1000 : parseInt(perPageSelect.value); currentPage = 1; fetchPayroll(); };
    if (monthInput) monthInput.onchange = () => { currentPage = 1; fetchPayroll(); };
    if (statusSelect) statusSelect.onchange = () => { currentPage = 1; fetchPayroll(); };
    if (deptSelect) deptSelect.onchange = () => { currentPage = 1; fetchPayroll(); };
    
    if (searchInput) {
        searchInput.oninput = () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => { currentPage = 1; fetchPayroll(); }, 400);
        };
    }

    // Initial Calls
    loadDeptData();
    fetchPayroll();
    loadEligibleEmployees();

    // Month Picker Logic for Edit Modal
    const yearDisplay = document.getElementById('currentYearDisplay');
    const monthInputHidden = document.getElementById('monthInput');
    const prevYearBtn = document.getElementById('prevYear');
    const nextYearBtn = document.getElementById('nextYear');
    const monthItems = document.querySelectorAll('.month-item');

    if (yearDisplay && monthInputHidden) {
        let currentSelectedYear = parseInt(yearDisplay.textContent);
        let currentSelectedMonth = 7; // Default index for August

        // Initialize from hidden input if it has a value
        if (monthInputHidden.value) {
            const [y, m] = monthInputHidden.value.split('-');
            currentSelectedYear = parseInt(y);
            currentSelectedMonth = parseInt(m) - 1;
            yearDisplay.textContent = currentSelectedYear;
            
            // Highlight correct month
            monthItems.forEach(item => {
                if (parseInt(item.getAttribute('data-month')) === currentSelectedMonth) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }

        const updateMonthInput = () => {
            const formattedMonth = (currentSelectedMonth + 1).toString().padStart(2, '0');
            monthInputHidden.value = `${currentSelectedYear}-${formattedMonth}`;
        };

        if (prevYearBtn) {
            prevYearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                currentSelectedYear--;
                yearDisplay.textContent = currentSelectedYear;
                updateMonthInput();
            });
        }

        if (nextYearBtn) {
            nextYearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                currentSelectedYear++;
                yearDisplay.textContent = currentSelectedYear;
                updateMonthInput();
            });
        }

        monthItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                monthItems.forEach(m => m.classList.remove('active'));
                item.classList.add('active');
                currentSelectedMonth = parseInt(item.getAttribute('data-month'));
                updateMonthInput();
            });
        });
    }
});

// Tab Switching Logic
window.switchGenerateTab = function(tabId) {
    // Update Button Classes
    const buttons = document.querySelectorAll('.modal-tabs .tab-btn');
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('onclick').includes(`'${tabId}'`)) {
            btn.classList.add('active');
        }
    });

    // Update Pane Classes
    const panes = document.querySelectorAll('.tab-pane');
    panes.forEach(pane => {
        pane.classList.remove('active');
    });
    
    const targetPane = document.getElementById('tab-' + tabId);
    if (targetPane) targetPane.classList.add('active');

    // Update Footer Selection Text
    updateSelectedCount();

    console.log('Switched to tab:', tabId);
};

// Selection Logic for Modal
window.toggleAllSpecific = function(checked) {
    const checkboxes = document.querySelectorAll('#specificEmployeeList .emp-checkbox');
    checkboxes.forEach(cb => cb.checked = checked);
    updateSelectedCount();
};

function updateSelectedCount() {
    const countLabel = document.getElementById('selectedCountText');
    if (!countLabel) return;

    const activeTab = document.querySelector('.modal-tabs .tab-btn.active');
    const isSpecific = activeTab && activeTab.getAttribute('onclick').includes('specific');

    if (isSpecific) {
        const count = document.querySelectorAll('#specificEmployeeList .emp-checkbox:checked').length;
        countLabel.textContent = `${count} employees selected`;
    } else {
        const total = document.getElementById('totalEmployeesToProcess')?.textContent || '0';
        countLabel.textContent = `All ${total} employees selected`;
    }
}

// Add event listener for checkbox changes in modal
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('emp-checkbox')) {
        updateSelectedCount();
        
        // Sync header checkbox
        const headerCB = document.getElementById('selectAllEmployees');
        if (headerCB && e.target.id !== 'selectAllEmployees') {
            const all = document.querySelectorAll('#specificEmployeeList .emp-checkbox').length;
            const checked = document.querySelectorAll('#specificEmployeeList .emp-checkbox:checked').length;
            headerCB.checked = (all === checked && all > 0);
        }
    }
});

async function loadEligibleEmployees() {
    const list = document.getElementById('specificEmployeeList');
    if (!list) return;

    console.log('Fetching eligible employees...');
    try {
        const response = await fetch('assets/api/payroll_handler.php?action=fetch_eligible_employees');
        const result = await response.json();
        console.log('Eligible employees result:', result);
        
        if (result.status === 'success') {
            renderEligibleEmployees(result.data);
            // Update summary count in modal
            const totalCount = document.getElementById('totalEmployeesToProcess');
            if (totalCount) totalCount.textContent = result.data.length;
            
            // Immediately update the footer count
            updateSelectedCount();
        } else {
            console.error('API Error:', result.message);
        }
    } catch (error) {
        console.error('Error loading eligible employees:', error);
    }
}

function renderEligibleEmployees(employees) {
    const list = document.getElementById('specificEmployeeList');
    if (!list) return;

    if (!employees || employees.length === 0) {
        list.innerHTML = '<tr><td colspan="5" class="text-center py-20">No eligible employees found.</td></tr>';
        return;
    }

    list.innerHTML = employees.map(e => {
        const fullName = `${e.first_name} ${e.middle_name ? e.middle_name + ' ' : ''}${e.last_name}`;
        return `
            <tr>
                <td>
                    <label class="custom-checkbox m-0">
                        <input type="checkbox" class="emp-checkbox" value="${e.id}">
                        <span class="checkmark"></span>
                    </label>
                </td>
                <td>
                    <div class="emp-info">
                        <span class="name font-600">${fullName}</span>
                        <span class="email font-12 text-light uppercase">EMP-0${e.id}</span>
                    </div>
                </td>
                <td><span class="font-13">${e.job_title || 'Employee'}</span></td>
                <td><span class="font-13">${e.dept_name || 'Unassigned'}</span></td>
                <td class="text-right"><span class="font-13 font-600 text-primary-color">${parseFloat(e.salary || 0).toLocaleString('en-US', { style: 'currency', currency: 'PKR' })}</span></td>
            </tr>
        `;
    }).join('');
}

// Edit Modal (Global for row clicks)
window.openEditPayrollModal = function(empId, empName) {
    const modal = document.getElementById('editPayrollModal');
    const subtitle = document.getElementById('editPayrollSubtitle');
    if (modal && subtitle) {
        subtitle.textContent = `Updating payroll for ${empName} (${empId})`;
        modal.classList.add('active');
        lucide.createIcons();
    }
};

// Open Generate Modal and refresh count
window.openGenerateModal = function() {
    const modal = document.getElementById('generatePayrollModal');
    if (modal) {
        modal.classList.add('active');
        // Reset to first tab
        switchGenerateTab('all');
        updateSelectedCount();
        lucide.createIcons();
    }
};

// Close logic for modals
document.querySelectorAll('.js-modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
        const modal = btn.closest('.modal-overlay');
        if (modal) modal.classList.remove('active');
    });
});
