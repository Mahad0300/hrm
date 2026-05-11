function setLeaveFileHint(inputId, hintId, emptyText, wrapperId) {
    var input = document.getElementById(inputId);
    var hint = document.getElementById(hintId);
    var wrapper = wrapperId ? document.getElementById(wrapperId) : null;
    if (!hint) return;
    if (input && input.files && input.files.length) {
        hint.textContent = input.files[0].name;
        if (wrapper) wrapper.classList.add('has-file');
    } else {
        hint.textContent = emptyText;
        if (wrapper) wrapper.classList.remove('has-file');
    }
}

function resetApplyLeaveForm() {
    var form = document.getElementById('applyLeaveForm');
    if (!form) return;
    form.reset();
    setLeaveFileHint('applyLeaveDocument', 'applyLeaveFileHint', 'No file chosen', 'applyLeaveFileWrapper');
}

function openApplyLeaveModal() {
    resetApplyLeaveForm();
    if (typeof openModal === 'function') openModal('applyLeaveModal');
    else {
        var m = document.getElementById('applyLeaveModal');
        if (m) m.classList.add('active');
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function openEditLeaveModal(btn) {
    var tr = btn && btn.closest && btn.closest('tr');
    if (!tr || !tr.dataset) return;
    var id = tr.dataset.id || '';
    var type = tr.dataset.leaveType || '';
    var from = tr.dataset.dateFrom || '';
    var to = tr.dataset.dateTo || '';
    var reason = tr.dataset.reason || '';
    var docPath = tr.dataset.documentPath || '';

    var idEl = document.getElementById('editLeaveId');
    var typeEl = document.getElementById('editLeaveType');
    var fromEl = document.getElementById('editLeaveFrom');
    var toEl = document.getElementById('editLeaveTo');
    var reasonEl = document.getElementById('editLeaveReason');
    var docEl = document.getElementById('editLeaveDocument');
    var fileLabel = document.getElementById('editLeaveFileLabel');
    var fileInfo = document.getElementById('editLeaveFileInfo');
    var fileIcon = document.getElementById('editLeaveFileIcon');
    var fileWrapper = document.getElementById('editLeaveFileWrapper');
    
    if (idEl) idEl.value = id;
    if (typeEl) typeEl.value = type;
    if (fromEl) fromEl.value = from;
    if (toEl) toEl.value = to;
    if (reasonEl) reasonEl.value = reason;
    if (docEl) docEl.value = '';

    if (docPath && fileLabel && fileInfo && fileWrapper) {
        var filename = docPath.split('/').pop();
        fileLabel.textContent = filename;
        fileInfo.innerHTML = '<a href="../' + docPath + '" target="_blank" class="text-primary hover-underline">View current file</a> (or click to replace)';
        fileWrapper.classList.add('has-file');
        if (fileIcon) fileIcon.setAttribute('data-lucide', 'file-text');
    } else if (fileLabel && fileInfo && fileWrapper) {
        fileLabel.textContent = 'Choose file';
        fileInfo.textContent = 'Replace or keep existing';
        fileWrapper.classList.remove('has-file');
        if (fileIcon) fileIcon.setAttribute('data-lucide', 'upload-cloud');
    }

    var editModal = document.getElementById('editLeaveModal');
    if (editModal) editModal.dataset.editRowIndex = String(tr.rowIndex);

    setLeaveFileHint('editLeaveDocument', 'editLeaveFileHint', 'Replace attachment, or leave empty to keep the current file', 'editLeaveFileWrapper');

    if (typeof openModal === 'function') openModal('editLeaveModal');
    else if (editModal) editModal.classList.add('active');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

/**
 * Centralized function to handle Leave Form submissions (Apply & Edit)
 * Reduces duplicate code and ensures consistent behavior.
 */
function setupLeaveFormSubmission(formId, fromId, toId) {
    var form = document.getElementById(formId);
    if (!form || form.dataset.boundSubmit) return;
    
    form.dataset.boundSubmit = '1';
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        
        var fromEl = document.getElementById(fromId);
        var toEl = document.getElementById(toId);
        
        if (fromEl && toEl && fromEl.value && toEl.value) {
            if (new Date(fromEl.value) > new Date(toEl.value)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Dates',
                    text: 'To date must be on or after from date.',
                    heightAuto: false
                });
                return;
            }
        }

        var submitBtn = form.querySelector('button[type="submit"]');
        var originalBtnHtml = submitBtn ? submitBtn.innerHTML : 'Submit';
        
        try {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i data-lucide="loader" class="spin"></i> <span>Processing...</span>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }

            var formData = new FormData(form);
            const response = await fetch('assets/api/leave_handler.php', {
                method: 'POST',
                body: formData
            });

            // Robust JSON parsing to catch server errors
            const rawText = await response.text();
            let result;
            try {
                result = JSON.parse(rawText);
            } catch(parseErr) {
                console.error("API Error. Server output:", rawText);
                throw new Error("Invalid server response");
            }

            if (result.status === 'success') {
                // HIDE MODAL IMMEDIATELY
                const modalId = (formId === 'editLeaveForm') ? 'editLeaveModal' : 'applyLeaveModal';
                const modalEl = document.getElementById(modalId);
                
                if (modalEl) {
                    modalEl.classList.remove('active');
                    // Also reset overflow in case closeModal isn't finished
                    document.body.style.overflow = 'auto';
                }

                if (typeof closeModal === 'function') {
                    closeModal(modalId);
                }

                Swal.fire({
                    icon: 'success',
                    title: result.message.includes('updated') ? 'Updated!' : 'Submitted!',
                    text: result.message,
                    showConfirmButton: false,
                    timer: 2000,
                    heightAuto: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message,
                    heightAuto: false
                });
            }
        } catch (err) {
            console.error("Form Submission Error:", err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Action failed. Please try again or check console.',
                heightAuto: false
            });
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }
    });
}

function bindLeaveApplyEditForms() {
    var applyDoc = document.getElementById('applyLeaveDocument');
    if (applyDoc && !applyDoc.dataset.boundHint) {
        applyDoc.dataset.boundHint = '1';
        applyDoc.addEventListener('change', function () {
            setLeaveFileHint('applyLeaveDocument', 'applyLeaveFileHint', 'No file chosen', 'applyLeaveFileWrapper');
        });
    }
    var editDoc = document.getElementById('editLeaveDocument');
    if (editDoc && !editDoc.dataset.boundHint) {
        editDoc.dataset.boundHint = '1';
        editDoc.addEventListener('change', function () {
            setLeaveFileHint('editLeaveDocument', 'editLeaveFileHint', 'Replace attachment, or leave empty to keep the current file', 'editLeaveFileWrapper');
        });
    }

    // Initialize unified form submission logic
    setupLeaveFormSubmission('applyLeaveForm', 'applyLeaveFrom', 'applyLeaveTo');
    setupLeaveFormSubmission('editLeaveForm', 'editLeaveFrom', 'editLeaveTo');
}

function openLeaveDetailModal(empName, leaveType, days, fromDate, toDate, reason, remarks, docUrl, docType) {
    var modal = document.getElementById('leaveDetailModal');
    document.getElementById('leaveDetailEmpName').textContent = empName || '—';
    document.getElementById('leaveDetailLeaveType').textContent = leaveType || '—';
    document.getElementById('leaveDetailDays').textContent = days != null && days !== '' ? days : '—';
    document.getElementById('leaveDetailFromDate').textContent = fromDate || '—';
    document.getElementById('leaveDetailToDate').textContent = toDate || '—';
    var reasonEl = document.getElementById('leaveDetailReason');
    if (reasonEl) {
        reasonEl.textContent = reason && String(reason).trim() ? String(reason).trim() : '—';
    }
    var remarksEl = document.getElementById('leaveDetailRemarks');
    if (remarksEl) {
        remarksEl.textContent = remarks && remarks.trim() ? remarks.trim() : 'No remarks yet.';
    }
    if (modal) {
        modal.dataset.docUrl = docUrl || '';
        modal.dataset.docType = docType || 'pdf';
    }
    if (typeof openModal === 'function') openModal('leaveDetailModal');
    else if (modal) modal.classList.add('active');
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
    bindLeaveApplyEditForms();

    initPagination({
        tableBodyId: 'leaveTableBody',
        perPageSelectId: 'perPageSelect',
        paginationInfoId: 'paginationInfo',
        tableSummaryId: 'tableSummary',
        pageNumbersId: 'pageNumbers',
        prevBtnId: 'prevPage',
        nextBtnId: 'nextPage'
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
                if (prevBtn) prevBtn.classList.add('hidden');
                if (nextBtn) nextBtn.classList.add('hidden');
                return;
            }
            if (prevBtn) prevBtn.classList.remove('hidden');
            if (nextBtn) nextBtn.classList.remove('hidden');

            if (prevBtn) {
                prevBtn.disabled = currentPage === 1;
                prevBtn.style.opacity = currentPage === 1 ? '0.5' : '1';
                prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; updateTable(); } };
            }
            if (nextBtn) {
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
