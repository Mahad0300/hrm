(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var openBtn = document.getElementById('openLeaveSettingsBtn');
        if (openBtn) {
            openBtn.addEventListener('click', function () {
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
            saveBtn.addEventListener('click', async function () {
                var s = document.getElementById('leaveQuotaSick');
                var c = document.getElementById('leaveQuotaCasual');
                var a = document.getElementById('leaveQuotaAnnual');
                
                var sickVal = Math.min(365, Math.max(0, parseInt(s && s.value, 10) || 0));
                var casualVal = Math.min(365, Math.max(0, parseInt(c && c.value, 10) || 0));
                var annualVal = Math.min(365, Math.max(0, parseInt(a && a.value, 10) || 0));

                try {
                    // Update UI Button State
                    var originalText = saveBtn.innerHTML;
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<i data-lucide="loader" class="spin"></i> <span>Saving...</span>';
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    const formData = new FormData();
                    formData.append('sick', sickVal);
                    formData.append('casual', casualVal);
                    formData.append('annual', annualVal);

                    const response = await fetch('assets/api/leave_type_handler.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        // Update the UI text dynamically without reload
                        var summaryEl = document.getElementById('leaveQuotaSummaryText');
                        if (summaryEl) {
                            summaryEl.innerHTML = `Sick ${sickVal} &middot; Casual ${casualVal} &middot; Annual ${annualVal} (days each)`;
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Settings Saved',
                            text: result.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        if (typeof closeModal === 'function') {
                            closeModal('applyLeaveModal');
                        }
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Failed to save leave settings.', 'error');
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i data-lucide="save"></i> <span>Save leave counts</span>';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            });
        }
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

function openLeaveDetailModal(leaveId, empName, leaveType, days, fromDate, toDate, adminComment, docUrl, docType, status) {
    var modal = document.getElementById('leaveDetailModal');
    document.getElementById('leaveDetailEmpName').textContent = empName || '—';
    document.getElementById('leaveDetailLeaveType').textContent = leaveType || '—';
    document.getElementById('leaveDetailDays').textContent = days != null && days !== '' ? days : '—';
    document.getElementById('leaveDetailFromDate').textContent = fromDate || '—';
    document.getElementById('leaveDetailToDate').textContent = toDate || '—';
    var commentInput = document.getElementById('leaveDetailAdminCommentInput');
    if (commentInput) commentInput.value = adminComment || '';
    
    var approveBtn = modal.querySelector('.leave-btn-approve');
    var rejectBtn = modal.querySelector('.leave-btn-reject');
    var updateBtn = modal.querySelector('.leave-btn-update');

    if (commentInput) commentInput.readOnly = false;

    // Reset visibility
    if (approveBtn) approveBtn.style.display = 'none';
    if (rejectBtn) rejectBtn.style.display = 'none';
    if (updateBtn) updateBtn.style.display = 'none';

    // Show according to current status
    if (status === 'Pending') {
        if (approveBtn) {
            approveBtn.style.display = 'flex';
            approveBtn.onclick = function() { 
                if (typeof closeModal === 'function') closeModal('leaveDetailModal');
                else document.getElementById('leaveDetailModal').classList.remove('active');
                confirmLeaveAction(leaveId, 'Approve', commentInput ? commentInput.value : ''); 
            };
        }
        if (rejectBtn) {
            rejectBtn.style.display = 'flex';
            rejectBtn.onclick = function() { 
                if (typeof closeModal === 'function') closeModal('leaveDetailModal');
                else document.getElementById('leaveDetailModal').classList.remove('active');
                confirmLeaveAction(leaveId, 'Reject', commentInput ? commentInput.value : ''); 
            };
        }
    } else if (status === 'Approved') {
        if (rejectBtn) {
            rejectBtn.style.display = 'flex';
            rejectBtn.onclick = function() { 
                if (typeof closeModal === 'function') closeModal('leaveDetailModal');
                else document.getElementById('leaveDetailModal').classList.remove('active');
                confirmLeaveAction(leaveId, 'Reject', commentInput ? commentInput.value : ''); 
            };
        }
        if (updateBtn) {
            updateBtn.style.display = 'flex';
            updateBtn.onclick = function() {
                if (typeof closeModal === 'function') closeModal('leaveDetailModal');
                else document.getElementById('leaveDetailModal').classList.remove('active');
                confirmLeaveAction(leaveId, 'Update', commentInput ? commentInput.value : ''); 
            };
        }
    } else if (status === 'Rejected') {
        if (approveBtn) {
            approveBtn.style.display = 'flex';
            approveBtn.onclick = function() { 
                if (typeof closeModal === 'function') closeModal('leaveDetailModal');
                else document.getElementById('leaveDetailModal').classList.remove('active');
                confirmLeaveAction(leaveId, 'Approve', commentInput ? commentInput.value : ''); 
            };
        }
        if (updateBtn) {
            updateBtn.style.display = 'flex';
            updateBtn.onclick = function() {
                if (typeof closeModal === 'function') closeModal('leaveDetailModal');
                else document.getElementById('leaveDetailModal').classList.remove('active');
                confirmLeaveAction(leaveId, 'Update', commentInput ? commentInput.value : ''); 
            };
        }
    }

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

async function confirmLeaveAction(leaveId, action, prefilledNote = null) {
    if (!leaveId || !action) return;

    let adminNote = prefilledNote;

    if (prefilledNote === null) {
        const { value: noteText } = await Swal.fire({
            title: `${action} Leave Request`,
            input: 'textarea',
            inputLabel: 'Admin Remarks (Optional)',
            inputPlaceholder: 'Enter any remarks...',
            showCancelButton: true,
            confirmButtonColor: action === 'Approve' ? '#22c55e' : '#ef4444',
            confirmButtonText: `Yes, ${action}`,
            cancelButtonText: 'Cancel'
        });
        if (noteText === undefined) return; // User clicked Cancel
        adminNote = noteText;
    }

    try {
        const formData = new FormData();
        formData.append('leave_id', leaveId);
        formData.append('action', action);
        formData.append('admin_note', adminNote);

        const res = await fetch('assets/api/leave_status_handler.php', {
            method: 'POST',
            body: formData
        });

        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("Raw response:", text);
            throw new Error("Invalid server response format.");
        }

        if (data.status === 'success') {
            await Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            });
            window.location.reload();
        } else {
            Swal.fire('Error', data.message || 'Action failed', 'error');
        }
    } catch (error) {
        Swal.fire('Error', error.message || 'An unexpected error occurred.', 'error');
    }
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
