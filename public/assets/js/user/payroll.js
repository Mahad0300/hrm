// --- Pagination (payroll table) ---
let payrollCurrentPage = 1;
let payrollRowsPerPage = 10;

function initPayrollPagination() {
    const tableBody = document.getElementById('payrollTableBody');
    const perPageSelect = document.getElementById('perPageSelect');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    if (!tableBody || !perPageSelect) return;

    perPageSelect.addEventListener('change', function () {
        payrollRowsPerPage = this.value === 'all' ? tableBody.rows.length : parseInt(this.value, 10);
        payrollCurrentPage = 1;
        updatePayrollTable();
    });

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

    updatePayrollTable();
}

function updatePayrollTable() {
    const tableBody = document.getElementById('payrollTableBody');
    if (!tableBody) return;

    const rows = Array.from(tableBody.rows);
    const totalRows = rows.length;

    const start = (payrollCurrentPage - 1) * payrollRowsPerPage;
    const end = payrollRowsPerPage === totalRows ? totalRows : start + payrollRowsPerPage;

    rows.forEach((row, index) => {
        row.style.display = index >= start && index < end ? '' : 'none';
    });

    const summaryText = `Showing ${totalRows === 0 ? 0 : start + 1} to ${Math.min(end, totalRows)} of ${totalRows} entries`;
    const summaryElement = document.getElementById('tableSummary');
    const paginationInfo = document.getElementById('paginationInfo');
    if (summaryElement) summaryElement.textContent = summaryText;
    if (paginationInfo) paginationInfo.textContent = summaryText;

    updatePayrollPaginationControls(totalRows);
}

function updatePayrollPaginationControls(totalRows) {
    const pageNumbersContainer = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const totalPages = Math.ceil(totalRows / payrollRowsPerPage) || 1;

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

    if (prevBtn) prevBtn.disabled = payrollCurrentPage === 1;
    if (nextBtn) nextBtn.disabled = payrollCurrentPage === totalPages || totalPages === 0;
}

document.addEventListener('DOMContentLoaded', initPayrollPagination);
