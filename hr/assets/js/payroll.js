/**
 * Open the Edit Payroll Modal
 * @param {string} empId - Employee ID
 * @param {string} empName - Employee Name
 */
function openEditPayrollModal(empId, empName) {
    const modal = document.getElementById('editPayrollModal');
    const subtitle = document.getElementById('editPayrollSubtitle');
    
    if (modal && subtitle) {
        subtitle.textContent = `Updating payroll for ${empName} (${empId})`;
        
        // Show modal using utility from modals.js
        if (typeof openModal === 'function') {
            openModal('editPayrollModal');
        } else {
            modal.classList.add('active');
        }
    }
}

// Custom Month Picker Logic (Inline)
document.addEventListener('DOMContentLoaded', function() {
    const monthItems = document.querySelectorAll('.month-item');
    const yearDisplay = document.getElementById('currentYearDisplay');
    const monthInput = document.getElementById('monthInput');
    
    let currentYear = 2026;
    let selectedMonth = 7; // Aug

    // Year Navigation
    document.getElementById('prevYear')?.addEventListener('click', () => {
        currentYear--;
        yearDisplay.textContent = currentYear;
        updateInput();
    });

    document.getElementById('nextYear')?.addEventListener('click', () => {
        currentYear++;
        yearDisplay.textContent = currentYear;
        updateInput();
    });

    // Month Selection
    monthItems.forEach(item => {
        item.addEventListener('click', function() {
            monthItems.forEach(m => m.classList.remove('active'));
            this.classList.add('active');
            selectedMonth = parseInt(this.dataset.month);
            updateInput();
        });
    });

    function updateInput() {
        const monthNum = (selectedMonth + 1).toString().padStart(2, '0');
        monthInput.value = `${currentYear}-${monthNum}`;
    }
});

// Generate Payroll Modal Tab Switching
function switchGenerateTab(tabId) {
    // Buttons
    document.querySelectorAll('.modal-tabs .tab-btn').forEach(btn => {
        btn.classList.remove('active');
        if(btn.textContent.toLowerCase().includes(tabId)) btn.classList.add('active');
    });

    // Panes
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    document.getElementById('tab-' + tabId).classList.add('active');
    
    // Toggle Footer Selection Count Visibility
    const footerCount = document.getElementById('footerSelectionCount');
    if(footerCount) {
        footerCount.style.visibility = (tabId === 'specific') ? 'visible' : 'hidden';
    }

    // Refresh icons if needed
    if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
        lucide.createIcons();
    }
}

// Toggle All Specific Employees
function toggleAllSpecific(checked) {
    const checkboxes = document.querySelectorAll('#specificEmployeeList .emp-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checked;
    });
    updateSelectedCount();
}

// Update Selected Count
function updateSelectedCount() {
    const count = document.querySelectorAll('#specificEmployeeList .emp-checkbox:checked').length;
    const countLabel = document.getElementById('selectedCountText');
    if(countLabel) {
        countLabel.textContent = `${count} Employees selected`;
    }
}

// Filter Specific Employees (Search + Dept + Designation)
function filterSpecificEmployees() {
    const query = document.querySelector('.modal-search input').value.toLowerCase();
    const dept = document.getElementById('filterDept').value.toLowerCase();
    const desig = document.getElementById('filterDesig').value.toLowerCase();
    const rows = document.querySelectorAll('#specificEmployeeList tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const rowDept = row.cells[3].textContent.toLowerCase();
        const rowDesig = row.cells[2].textContent.toLowerCase();
        
        const matchesSearch = text.includes(query);
        const matchesDept = dept === "" || rowDept.includes(dept);
        const matchesDesig = desig === "" || rowDesig.includes(desig);
        
        row.style.display = (matchesSearch && matchesDept && matchesDesig) ? '' : 'none';
    });
}

// Add listeners for individual checkboxes
document.addEventListener('change', function(e) {
    if(e.target.classList.contains('emp-checkbox')) {
        updateSelectedCount();
        
        // Update header checkbox
        const all = document.querySelectorAll('#specificEmployeeList .emp-checkbox').length;
        const checked = document.querySelectorAll('#specificEmployeeList .emp-checkbox:checked').length;
        const headerCB = document.getElementById('selectAllEmployees');
        if(headerCB) headerCB.checked = (all === checked);
    }
});

// Handle form submission
document.getElementById('editPayrollForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Payroll record updated successfully');
    if (typeof closeModal === 'function') {
        closeModal('editPayrollModal');
    } else {
        document.getElementById('editPayrollModal').classList.remove('active');
    }
});

// --- Pagination Logic ---
let payrollCurrentPage = 1;
let payrollRowsPerPage = 10;

function initPayrollPagination() {
    const tableBody = document.getElementById('payrollTableBody');
    const perPageSelect = document.getElementById('perPageSelect');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    if (!tableBody || !perPageSelect) return;

    // Per Page Change
    perPageSelect.addEventListener('change', function() {
        payrollRowsPerPage = this.value === 'all' ? tableBody.rows.length : parseInt(this.value);
        payrollCurrentPage = 1;
        updatePayrollTable();
    });

    // Navigation
    prevBtn?.addEventListener('click', () => {
        if (payrollCurrentPage > 1) {
            payrollCurrentPage--;
            updatePayrollTable();
        }
    });

    nextBtn?.addEventListener('click', () => {
        const totalRows = tableBody.rows.length;
        const totalPages = Math.ceil(totalRows / payrollRowsPerPage);
        if (payrollCurrentPage < totalPages) {
            payrollCurrentPage++;
            updatePayrollTable();
        }
    });

    // Initial Load
    updatePayrollTable();
}

function updatePayrollTable() {
    const tableBody = document.getElementById('payrollTableBody');
    const rows = Array.from(tableBody.rows);
    const totalRows = rows.length;
    
    // Calculate range
    const start = (payrollCurrentPage - 1) * payrollRowsPerPage;
    const end = payrollRowsPerPage === totalRows ? totalRows : start + payrollRowsPerPage;
    
    // Show/Hide rows
    rows.forEach((row, index) => {
        row.style.display = (index >= start && index < end) ? '' : 'none';
    });

    // Update Summary
    const summaryText = `Showing ${totalRows === 0 ? 0 : start + 1} to ${Math.min(end, totalRows)} of ${totalRows} entries`;
    const summaryElement = document.getElementById('tableSummary');
    const paginationInfo = document.getElementById('paginationInfo');
    if (summaryElement) summaryElement.textContent = summaryText;
    if (paginationInfo) paginationInfo.textContent = summaryText;

    // Update Controls
    updatePayrollPaginationControls(totalRows);
}

function updatePayrollPaginationControls(totalRows) {
    const pageNumbersContainer = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const totalPages = Math.ceil(totalRows / payrollRowsPerPage);

    if (!pageNumbersContainer) return;

    pageNumbersContainer.innerHTML = '';
    
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.className = `action-btn ${i === payrollCurrentPage ? 'btn-active' : ''}`;
        btn.textContent = i;
        btn.onclick = () => {
            payrollCurrentPage = i;
            updatePayrollTable();
        };
        pageNumbersContainer.appendChild(btn);
    }

    // Disable/Enable Nav
    if (prevBtn) prevBtn.disabled = (payrollCurrentPage === 1);
    if (nextBtn) nextBtn.disabled = (payrollCurrentPage === totalPages || totalPages === 0);
}

// Initialize on load
document.addEventListener('DOMContentLoaded', initPayrollPagination);
