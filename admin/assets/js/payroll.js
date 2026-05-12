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

    // Handle Edit Payroll Form Submission
    const editPayrollForm = document.getElementById('editPayrollForm');
    if (editPayrollForm) {
        editPayrollForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'save_payroll');

            try {
                const response = await fetch('assets/api/payroll_handler.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Payroll record updated successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    closeModal('editPayrollModal');
                    fetchPayroll(); // Refresh table
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An error occurred while saving.'
                });
            }
        });
    }

    // Handle Bulk Payroll Generation Form
    const payrollForm = document.getElementById('payrollForm');
    if (payrollForm) {
        payrollForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            
            const activeTab = document.querySelector('.modal-tabs .tab-btn.active');
            const isSpecific = activeTab && activeTab.getAttribute('onclick').includes('specific');
            const month = this.querySelector('input[type="month"]').value;
            
            let selectedIds = [];
            if (isSpecific) {
                const checked = document.querySelectorAll('#specificEmployeeList .emp-checkbox:checked');
                selectedIds = Array.from(checked).map(cb => cb.value);
            } else {
                const all = document.querySelectorAll('#specificEmployeeList .emp-checkbox');
                selectedIds = Array.from(all).map(cb => cb.value);
            }

            if (selectedIds.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Selection Error', text: 'Please select at least one employee.' });
                return;
            }

            Swal.fire({
                title: 'Generating Payroll...',
                text: 'Please wait while we process the disbursements.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const response = await fetch('assets/api/payroll_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=generate_bulk_payroll&ids=${JSON.stringify(selectedIds)}&month=${month}`
                });
                const result = await response.json();

                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Disbursement Successful!',
                        text: `${result.count} payroll records have been processed.`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    closeModal('generatePayrollModal');
                    fetchPayroll(); // Refresh the main table
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: result.message });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Failed to process disbursement.' });
            }
        });
    }

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
                Swal.fire({
                    icon: 'error',
                    title: 'Fetch Error',
                    text: result.message
                });
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
                            <button class="action-btn action-btn-edit" title="Edit" onclick="openEditPayrollModal(${p.employee_id}, '${fullName.replace(/'/g, "\\'")}', '${monthVal}')"><i data-lucide="edit-2" size="16"></i></button>
                            <button class="action-btn action-btn-view" title="View Payslip" onclick="viewPayslip(${p.employee_id}, '${monthVal}', '${fullName.replace(/'/g, "\\'")}')"><i data-lucide="eye" size="16"></i></button>
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
    fetchEligibleEmployees();

    // Generate Modal Filter Listeners
    const genSearchId = document.getElementById('genSearchId');
    const genSearchName = document.getElementById('genSearchName');
    const genDept = document.getElementById('genDept');
    
    const triggerGenSearch = () => {
        clearTimeout(generateSearchTimer);
        generateSearchTimer = setTimeout(fetchEligibleEmployees, 500);
    };

    if (genSearchId) genSearchId.addEventListener('input', triggerGenSearch);
    if (genSearchName) genSearchName.addEventListener('input', triggerGenSearch);
    if (genDept) genDept.addEventListener('change', fetchEligibleEmployees);

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

// Redundant function removed (Merged into fetchEligibleEmployees)

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
window.openEditPayrollModal = async function(empId, empName, month) {
    const modal = document.getElementById('editPayrollModal');
    const subtitle = document.getElementById('editPayrollSubtitle');
    const form = document.getElementById('editPayrollForm');
    
    if (!modal) return;

    // Show loading or clear previous
    subtitle.textContent = `Loading payroll details for ${empName}...`;
    modal.classList.add('active');

    try {
        const response = await fetch(`assets/api/payroll_handler.php?action=get_payroll_details&employee_id=${empId}&month=${month}`);
        const result = await response.json();

        if (result.status === 'success') {
            const data = result.data;
            subtitle.textContent = `Updating payroll for ${empName} (EMP-0${empId})`;
            
            // Populate Hidden Fields
            form.querySelector('#editEmpId').value = empId;
            form.querySelector('#editMonth').value = month;

            // Populate form fields
            form.querySelector('[name="leaves"]').value = Math.round(data.leaves_count || 0);
            form.querySelector('[name="late"]').value = Math.round(data.lates_count || 0);
            form.querySelector('[name="halfday"]').value = data.halfdays_count || 0;
            form.querySelector('[name="loan"]').value = Math.round(data.loan_deduction || 0);
            form.querySelector('[name="pfund"]').value = Math.round(data.provident_fund || 0);
            form.querySelector('[name="ptax"]').value = Math.round(data.professional_tax || 0);
            
            // Update Month Picker Hidden Input
            document.getElementById('monthInput').value = month;
            const [year, m] = month.split('-');
            document.getElementById('currentYearDisplay').textContent = year;
            
            // Highlight month in picker
            const monthIdx = parseInt(m) - 1;
            document.querySelectorAll('.month-item').forEach(item => {
                item.classList.toggle('active', parseInt(item.getAttribute('data-month')) === monthIdx);
            });

            // Update Salary Info Panel
            updateSalaryInfoPanel(data, result.employee);

            // Add Live Calculation Event Listeners
            addLiveCalculationListeners(result.employee.salary);
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error opening edit modal:', error);
    }
    
    lucide.createIcons();
};

window.viewPayslip = function(empId, month, empName) {
    const urlName = encodeURIComponent(empName.replace(/ /g, '-'));
    window.open(`payslip-print.php?id=${empId}&month=${month}&name=${urlName}`, '_blank');
};

function updateSalaryInfoPanel(data, employee) {
    const panel = document.querySelector('.salary-info-panel');
    if (!panel) return;

    const currencyFormat = (val) => {
        return 'PKR ' + Math.round(val).toLocaleString('en-US');
    };
    
    // Calculate total earnings
    const totalEarnings = parseFloat(data.basic_salary) + parseFloat(data.house_rent) + 
                          parseFloat(data.utility) + parseFloat(data.fuel) + 
                          parseFloat(data.mobile) + parseFloat(data.medical);

    panel.innerHTML = `
        <h4 class="font-14 font-600 mb-20 text-dark flex-center gap-8 justify-start">
            <i data-lucide="info" size="18"></i> Salary Info (30 Days Base)
        </h4>

        <div class="space-y-4">
            <div class="info-group">
                <span class="info-label">Basic Salary (50%)</span>
                <span class="info-value">${currencyFormat(data.basic_salary)}</span>
            </div>
            <div class="info-group">
                <span class="info-label">House Rent (20%)</span>
                <span class="info-value">${currencyFormat(data.house_rent)}</span>
            </div>
            <div class="info-group">
                <span class="info-label">Utility Allowance (10%)</span>
                <span class="info-value">${currencyFormat(data.utility)}</span>
            </div>
            <div class="info-group">
                <span class="info-label">Medical Allowance (10%)</span>
                <span class="info-value">${currencyFormat(data.medical)}</span>
            </div>
            <div class="info-group">
                <span class="info-label">Fuel Allowance (5%)</span>
                <span class="info-value">${currencyFormat(data.fuel)}</span>
            </div>
            <div class="info-group">
                <span class="info-label">Mobile Allowance (5%)</span>
                <span class="info-value">${currencyFormat(data.mobile)}</span>
            </div>

            <div class="my-16 border-b-light"></div>

            <div class="info-group">
                <span class="info-label">Attendance Deduction</span>
                <span class="info-value text-danger" id="summary-attendance-deduction">-${currencyFormat(data.deductions)}</span>
            </div>
            <div class="info-group">
                <span class="info-label">Loan Deduction</span>
                <span class="info-value text-danger" id="summary-loan-deduction">-${currencyFormat(data.loan_deduction || 0)}</span>
            </div>
            <div class="info-group">
                <span class="info-label">Provident Fund</span>
                <span class="info-value text-danger" id="summary-pfund-deduction">-${currencyFormat(data.provident_fund || 0)}</span>
            </div>
            <div class="info-group">
                <span class="info-label">Professional Tax</span>
                <span class="info-value text-danger" id="summary-ptax-deduction">-${currencyFormat(data.professional_tax || 0)}</span>
            </div>

            <div class="mt-20"></div>

            <div class="info-group highlight">
                <span class="info-label font-600">Gross Earnings</span>
                <span class="info-value font-700 text-primary">${currencyFormat(totalEarnings)}</span>
            </div>

            <div class="info-group highlight mt-8">
                <span class="info-label font-600">Net Payable</span>
                <span class="info-value font-700 text-success" id="summary-net-payable">${currencyFormat(data.net_payable)}</span>
            </div>
        </div>
    `;
    lucide.createIcons();
}

function addLiveCalculationListeners(grossSalary) {
    const form = document.getElementById('editPayrollForm');
    const inputs = form.querySelectorAll('input[type="number"]');
    
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            calculateLive(grossSalary);
        });
    });
}

function calculateLive(grossSalary) {
    const form = document.getElementById('editPayrollForm');
    const leaves = parseFloat(form.querySelector('[name="leaves"]').value) || 0;
    const lates = parseFloat(form.querySelector('[name="late"]').value) || 0;
    const halfdays = parseFloat(form.querySelector('[name="halfday"]').value) || 0;
    const loan = parseFloat(form.querySelector('[name="loan"]').value) || 0;
    const pfund = parseFloat(form.querySelector('[name="pfund"]').value) || 0;
    const ptax = parseFloat(form.querySelector('[name="ptax"]').value) || 0;

    const oneDaySalary = grossSalary / 30;
    const lateDeductionDays = Math.floor(lates / 3);
    const attendanceDeduction = (leaves + lateDeductionDays + (halfdays * 0.5)) * oneDaySalary;
    
    const totalDeductions = attendanceDeduction + loan + pfund + ptax;
    const netPayable = grossSalary - totalDeductions;

    const currencyFormat = (val) => {
        return 'PKR ' + Math.round(val).toLocaleString('en-US');
    };

    // Update Summary Labels
    document.getElementById('summary-attendance-deduction').textContent = `-${currencyFormat(attendanceDeduction)}`;
    document.getElementById('summary-loan-deduction').textContent = `-${currencyFormat(loan)}`;
    document.getElementById('summary-pfund-deduction').textContent = `-${currencyFormat(pfund)}`;
    document.getElementById('summary-ptax-deduction').textContent = `-${currencyFormat(ptax)}`;
    document.getElementById('summary-net-payable').textContent = currencyFormat(netPayable);
}

let generateSearchTimer;

// Open Generate Modal and refresh count
window.openGenerateModal = function() {
    const modal = document.getElementById('generatePayrollModal');
    if (modal) {
        modal.classList.add('active');
        // Reset to first tab
        switchGenerateTab('all');
        
        // Refresh Employee List
        fetchEligibleEmployees();
        
        lucide.createIcons();
    }
};

async function fetchEligibleEmployees() {
    const list = document.getElementById('specificEmployeeList');
    if (!list) return;

    const searchId = document.getElementById('genSearchId')?.value || '';
    const searchName = document.getElementById('genSearchName')?.value || '';
    const dept = document.getElementById('genDept')?.value || '';

    list.innerHTML = `<tr><td colspan="5" class="text-center py-20"><i data-lucide="loader-2" class="spin text-primary" size="20"></i></td></tr>`;
    lucide.createIcons();

    try {
        const url = `assets/api/payroll_handler.php?action=fetch_eligible_employees&searchId=${encodeURIComponent(searchId)}&searchName=${encodeURIComponent(searchName)}&department=${dept}`;
        const response = await fetch(url);
        const result = await response.json();

        if (result.status === 'success') {
            renderEligibleEmployees(result.data);
        } else {
            list.innerHTML = `<tr><td colspan="5" class="text-center py-20 text-danger">${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error(error);
        list.innerHTML = `<tr><td colspan="5" class="text-center py-20 text-danger">Error loading employees</td></tr>`;
    }
}

function renderEligibleEmployees(employees) {
    const list = document.getElementById('specificEmployeeList');
    list.innerHTML = '';

    const totalProcess = document.getElementById('totalEmployeesToProcess');
    if (totalProcess) {
        totalProcess.textContent = employees.length;
    }

    employees.forEach(emp => {
        const fullName = `${emp.first_name} ${emp.middle_name ? emp.middle_name + ' ' : ''}${emp.last_name}`;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="checkbox" class="emp-checkbox custom-checkbox" value="${emp.id}" onchange="updateSelectedCount()">
            </td>
            <td>
                <div class="employee-info-min">
                    <span class="font-14 font-600 block">${fullName}</span>
                    <span class="font-11 text-light block">EMP-0${emp.id}</span>
                </div>
            </td>
            <td><span class="font-13 text-main"">${emp.job_title}</span></td>
            <td><span class="font-13 text-main">${emp.dept_name || 'N/A'}</span></td>
            <td><span class="font-14 font-600">PKR ${Math.round(emp.salary).toLocaleString()}</span></td>
        `;
        list.appendChild(row);
    });

    // Handle Select All
    const selectAll = document.getElementById('selectAllEmployees');
    if (selectAll) {
        selectAll.checked = false;
        selectAll.onchange = function() {
            const checkboxes = list.querySelectorAll('.emp-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateSelectedCount();
        };
    }

    updateSelectedCount();
}

function updateSelectedCount() {
    const countText = document.getElementById('selectedCountText');
    if (!countText) return;

    const specificTabActive = document.getElementById('tab-specific')?.classList.contains('active');
    
    if (specificTabActive) {
        const checkboxes = document.querySelectorAll('.emp-checkbox:checked');
        countText.textContent = `${checkboxes.length} employees selected`;
    } else {
        const allCheckboxes = document.querySelectorAll('.emp-checkbox');
        countText.textContent = `All ${allCheckboxes.length} employees selected`;
    }
}

// Close logic for modals
document.querySelectorAll('.js-modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
        const modal = btn.closest('.modal-overlay');
        if (modal) modal.classList.remove('active');
    });
});
