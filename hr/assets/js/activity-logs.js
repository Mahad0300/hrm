/**
 * Activity Logs Pagination Logic
 */

let activityCurrentPage = 1;
let activityRowsPerPage = 10;

function initActivityPagination() {
    const tableBody = document.getElementById('activityTableBody');
    const perPageSelect = document.getElementById('perPageSelect');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    if (!tableBody || !perPageSelect) return;

    // Per Page Change
    perPageSelect.addEventListener('change', function() {
        activityRowsPerPage = this.value === 'all' ? tableBody.rows.length : parseInt(this.value);
        activityCurrentPage = 1;
        updateActivityTable();
    });

    // Navigation
    prevBtn?.addEventListener('click', () => {
        if (activityCurrentPage > 1) {
            activityCurrentPage--;
            updateActivityTable();
        }
    });

    nextBtn?.addEventListener('click', () => {
        const totalRows = tableBody.rows.length;
        const totalPages = Math.ceil(totalRows / activityRowsPerPage);
        if (activityCurrentPage < totalPages) {
            activityCurrentPage++;
            updateActivityTable();
        }
    });

    // Initial Load
    updateActivityTable();
}

function updateActivityTable() {
    const tableBody = document.getElementById('activityTableBody');
    const rows = Array.from(tableBody.rows);
    const totalRows = rows.length;
    
    // Calculate range
    const start = (activityCurrentPage - 1) * activityRowsPerPage;
    const end = activityRowsPerPage === totalRows ? totalRows : start + activityRowsPerPage;
    
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
    updateActivityPaginationControls(totalRows);
}

function updateActivityPaginationControls(totalRows) {
    const pageNumbersContainer = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const totalPages = Math.ceil(totalRows / activityRowsPerPage);

    if (!pageNumbersContainer) return;

    pageNumbersContainer.innerHTML = '';
    
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.className = `action-btn ${i === activityCurrentPage ? 'btn-active' : ''}`;
        btn.textContent = i;
        btn.onclick = () => {
            activityCurrentPage = i;
            updateActivityTable();
        };
        pageNumbersContainer.appendChild(btn);
    }

    // Disable/Enable Nav
    if (prevBtn) prevBtn.disabled = (activityCurrentPage === 1);
    if (nextBtn) nextBtn.disabled = (activityCurrentPage === totalPages || totalPages === 0);
}

// Initialize on load
document.addEventListener('DOMContentLoaded', initActivityPagination);
