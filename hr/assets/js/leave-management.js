(function () {
    'use strict';

    var LEAVE_QUOTA_STORAGE_KEY = 'hrm_admin_leave_quotas_v1';

    var defaultLeaveQuotas = {
        sick: 10,
        casual: 8,
        annual: 20
    };

    function getLeaveQuotas() {
        try {
            var raw = localStorage.getItem(LEAVE_QUOTA_STORAGE_KEY);
            if (!raw) {
                return Object.assign({}, defaultLeaveQuotas);
            }
            var o = JSON.parse(raw);
            return {
                sick: Math.max(0, parseInt(o.sick, 10) || defaultLeaveQuotas.sick),
                casual: Math.max(0, parseInt(o.casual, 10) || defaultLeaveQuotas.casual),
                annual: Math.max(0, parseInt(o.annual, 10) || defaultLeaveQuotas.annual)
            };
        } catch (e) {
            return Object.assign({}, defaultLeaveQuotas);
        }
    }

    function saveLeaveQuotas(q) {
        try {
            localStorage.setItem(LEAVE_QUOTA_STORAGE_KEY, JSON.stringify(q));
        } catch (e) {}
    }

    function loadLeaveQuotasIntoForm() {
        var q = getLeaveQuotas();
        var s = document.getElementById('leaveQuotaSick');
        var c = document.getElementById('leaveQuotaCasual');
        var a = document.getElementById('leaveQuotaAnnual');
        if (s) s.value = String(q.sick);
        if (c) c.value = String(q.casual);
        if (a) a.value = String(q.annual);
    }

    function updateLeaveQuotaSummary() {
        var q = getLeaveQuotas();
        var el = document.getElementById('leaveQuotaSummaryText');
        if (el) {
            el.textContent =
                'Sick ' +
                q.sick +
                ' · Casual ' +
                q.casual +
                ' · Annual ' +
                q.annual +
                ' (days each)';
        }
    }

    window.hrmGetLeaveQuotas = getLeaveQuotas;

    document.addEventListener('DOMContentLoaded', function () {
        var openBtn = document.getElementById('openLeaveSettingsBtn');
        if (openBtn) {
            openBtn.addEventListener('click', function () {
                loadLeaveQuotasIntoForm();
                if (typeof openModal === 'function') {
                    openModal('applyLeaveModal');
                }
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        }

        var saveBtn = document.getElementById('leaveQuotaSaveBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                var s = document.getElementById('leaveQuotaSick');
                var c = document.getElementById('leaveQuotaCasual');
                var a = document.getElementById('leaveQuotaAnnual');
                var q = {
                    sick: Math.min(365, Math.max(0, parseInt(s && s.value, 10) || 0)),
                    casual: Math.min(365, Math.max(0, parseInt(c && c.value, 10) || 0)),
                    annual: Math.min(365, Math.max(0, parseInt(a && a.value, 10) || 0))
                };
                saveLeaveQuotas(q);
                updateLeaveQuotaSummary();
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        }

        loadLeaveQuotasIntoForm();
        updateLeaveQuotaSummary();
    });
})();

function toggleLeaveView(view) {
    const allView = document.getElementById('allRequestsView');
    const pendingView = document.getElementById('pendingRequestsView');
    const allBtn = document.getElementById('allRequestsBtn');
    const backBtn = document.getElementById('backToAllBtn');
    
    if (view === 'pending') {
        allView.classList.add('hidden');
        pendingView.classList.remove('hidden');
        allBtn.classList.add('hidden');
        backBtn.classList.remove('hidden');
    } else {
        allView.classList.remove('hidden');
        pendingView.classList.add('hidden');
        allBtn.classList.remove('hidden');
        backBtn.classList.add('hidden');
    }
    if(typeof lucide !== 'undefined') lucide.createIcons();
}

function openLeaveDetailModal(empName, leaveType, days, fromDate, toDate, adminComment, docUrl, docType) {
    var modal = document.getElementById('leaveDetailModal');
    document.getElementById('leaveDetailEmpName').textContent = empName || '—';
    document.getElementById('leaveDetailLeaveType').textContent = leaveType || '—';
    document.getElementById('leaveDetailDays').textContent = days != null && days !== '' ? days : '—';
    document.getElementById('leaveDetailFromDate').textContent = fromDate || '—';
    document.getElementById('leaveDetailToDate').textContent = toDate || '—';
    var commentInput = document.getElementById('leaveDetailAdminCommentInput');
    if (commentInput) commentInput.value = adminComment || '';
    if (modal) {
        modal.dataset.docUrl = docUrl || '';
        modal.dataset.docType = docType || 'pdf';
    }
    if (typeof openModal === 'function') openModal('leaveDetailModal');
    else document.getElementById('leaveDetailModal').classList.add('active');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function leaveDetailViewDocument() {
    var modal = document.getElementById('leaveDetailModal');
    var url = modal && modal.dataset.docUrl;
    var type = (modal && modal.dataset.docType) || 'pdf';
    if (url && typeof openLeaveDocument === 'function') {
        openLeaveDocument(url, type);
    } else {
        alert('No document attached to this request.');
    }
}

function leaveDetailSaveComment() {
    var commentInput = document.getElementById('leaveDetailAdminCommentInput');
    var saveBtn = document.getElementById('leaveDetailSaveBtn');
    if (!commentInput || !saveBtn) return;
    var comment = commentInput.value.trim();
    var span = saveBtn.querySelector('span');
    var originalText = span ? span.textContent : 'Save';
    saveBtn.disabled = true;
    if (span) span.textContent = 'Saving...';
    if (typeof lucide !== 'undefined') lucide.createIcons();
    setTimeout(function() {
        saveBtn.disabled = false;
        if (span) span.textContent = 'Saved';
        if (typeof lucide !== 'undefined') lucide.createIcons();
        setTimeout(function() { if (span) span.textContent = originalText; if (typeof lucide !== 'undefined') lucide.createIcons(); }, 1500);
    }, 600);
}

function openLeaveDocument(url, type) {
    const imgEl = document.getElementById('leaveDocImage');
    const pdfEl = document.getElementById('leaveDocPdf');

    if (!imgEl || !pdfEl) return;

    imgEl.classList.add('hidden');
    pdfEl.classList.add('hidden');
    imgEl.src = '';
    pdfEl.src = '';

    if (type === 'image') {
        imgEl.src = url;
        imgEl.classList.remove('hidden');
    } else {
        pdfEl.src = url;
        pdfEl.classList.remove('hidden');
    }

    if (typeof openModal === 'function') {
        openModal('leaveDocumentModal');
    } else {
        document.getElementById('leaveDocumentModal').classList.add('active');
    }

    if (window.lucide && typeof lucide.createIcons === 'function') {
        lucide.createIcons();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // --- Pagination Logic for Main Table ---
    initPagination({
        tableBodyId: 'leaveTableBody',
        perPageSelectId: 'perPageSelect',
        paginationInfoId: 'paginationInfo',
        tableSummaryId: 'tableSummary',
        pageNumbersId: 'pageNumbers',
        prevBtnId: 'prevPage',
        nextBtnId: 'nextPage'
    });

    // --- Pagination Logic for Pending Table ---
    initPagination({
        tableBodyId: 'pendingTableBody',
        perPageSelectId: 'pendingPerPageSelect',
        paginationInfoId: 'pendingPaginationInfo',
        tableSummaryId: 'pendingTableSummary',
        pageNumbersId: 'pendingPageNumbers',
        prevBtnId: 'pendingPrevPage',
        nextBtnId: 'pendingNextPage'
    });

    function initPagination(config) {
        const tableBody = document.getElementById(config.tableBodyId);
        const perPageSelect = document.getElementById(config.perPageSelectId);
        const paginationInfo = document.getElementById(config.paginationInfoId);
        const tableSummary = document.getElementById(config.tableSummaryId);
        const pageNumbersContainer = document.getElementById(config.pageNumbersId);
        const prevBtn = document.getElementById(config.prevBtnId);
        const nextBtn = document.getElementById(config.nextBtnId);

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

            const showingStart = totalRows === 0 ? 0 : start + 1;
            const showingEnd = Math.min(end, totalRows);
            const infoText = `Showing ${showingStart} to ${showingEnd} of ${totalRows} entries`;
            paginationInfo.textContent = infoText;
            if (tableSummary) tableSummary.textContent = infoText;

            updatePaginationControls(totalPages);
        }

        function updatePaginationControls(totalPages) {
            if (!pageNumbersContainer) return;
            pageNumbersContainer.innerHTML = '';
            
            if (totalPages <= 1) {
                if(prevBtn) prevBtn.classList.add('hidden');
                if(nextBtn) nextBtn.classList.add('hidden');
                return;
            } else {
                if(prevBtn) prevBtn.classList.remove('hidden');
                if(nextBtn) nextBtn.classList.remove('hidden');
            }

            if(prevBtn) {
                prevBtn.disabled = currentPage === 1;
                prevBtn.style.opacity = currentPage === 1 ? '0.5' : '1';
                prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; updateTable(); } };
            }
            if(nextBtn) {
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.style.opacity = currentPage === totalPages ? '0.5' : '1';
                nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; updateTable(); } };
            }

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

        updateTable();
    }
});
