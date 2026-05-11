document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('attendanceTableBody');
    const perPageSelect = document.getElementById('perPageSelect');
    const paginationInfo = document.getElementById('paginationInfo');
    const tableSummary = document.getElementById('tableSummary');
    const pageNumbersContainer = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    if (!tableBody || !perPageSelect || !paginationInfo) return;

    let allRows = Array.from(tableBody.querySelectorAll('tr'));
    let currentPage = 1;
    let rowsPerPage = parseInt(perPageSelect.value) || 10;

    function updateTable() {
        const totalRows = allRows.length;
        const totalPages = rowsPerPage === -1 ? 1 : Math.ceil(totalRows / rowsPerPage);
        
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const start = (currentPage - 1) * rowsPerPage;
        const end = rowsPerPage === -1 ? totalRows : start + rowsPerPage;

        allRows.forEach((row, index) => {
            if (rowsPerPage === -1) {
                row.style.display = '';
            } else {
                row.style.display = (index >= start && index < end) ? '' : 'none';
            }
        });

        // Update Info Text
        const showingStart = totalRows === 0 ? 0 : start + 1;
        const showingEnd = Math.min(end, totalRows);
        const infoText = `Showing ${showingStart} to ${showingEnd} of ${totalRows} entries`;
        paginationInfo.textContent = infoText;
        if (tableSummary) tableSummary.textContent = infoText;

        updatePaginationControls(totalPages);
    }

    function updatePaginationControls(totalPages) {
        pageNumbersContainer.innerHTML = '';
        
        // Only show pagination if more than one page
        if (totalPages <= 1) {
            prevBtn.classList.add('hidden');
            nextBtn.classList.add('hidden');
            return;
        } else {
            prevBtn.classList.remove('hidden');
            nextBtn.classList.remove('hidden');
        }

        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
        prevBtn.style.opacity = currentPage === 1 ? '0.5' : '1';
        nextBtn.style.opacity = currentPage === totalPages ? '0.5' : '1';

        // simple page numbers
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.className = `action-btn ${i === currentPage ? 'btn-active' : ''}`;
            btn.textContent = i;
            btn.onclick = () => {
                currentPage = i;
                updateTable();
            };
            pageNumbersContainer.appendChild(btn);
        }
    }

    perPageSelect.addEventListener('change', () => {
        const val = perPageSelect.value;
        rowsPerPage = val === 'all' ? -1 : parseInt(val);
        currentPage = 1;
        updateTable();
    });

    prevBtn.onclick = () => {
        if (currentPage > 1) {
            currentPage--;
            updateTable();
        }
    };

    nextBtn.onclick = () => {
        const totalPages = rowsPerPage === -1 ? 1 : Math.ceil(allRows.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            updateTable();
        }
    };

    // Initial load
    updateTable();
});
