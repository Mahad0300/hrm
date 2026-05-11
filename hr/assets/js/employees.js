// Password Visibility Toggle
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        input.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }
    
    lucide.createIcons({
        attrs: { size: 18 },
        nameAttr: 'data-lucide'
    });
}

// File selection feedback
function handleFileSelect(input, wrapperId, filenameId) {
    const wrapper = document.getElementById(wrapperId);
    const filenameLabel = document.getElementById(filenameId);
    
    if (input.files && input.files.length > 0) {
        wrapper.classList.add('has-file');
        if (input.files.length === 1) {
            filenameLabel.textContent = input.files[0].name;
            filenameLabel.classList.add('text-success');
        } else {
            filenameLabel.textContent = input.files.length + " files selected";
            filenameLabel.classList.add('text-success');
        }
    } else {
        wrapper.classList.remove('has-file');
        filenameLabel.classList.remove('text-success');
        // Reset to default info based on ID
        if (wrapperId === 'resume_wrapper') filenameLabel.textContent = "PDF, DOCX up to 5MB";
        else if (wrapperId === 'id_wrapper') filenameLabel.textContent = "PNG, JPG or PDF";
        else filenameLabel.textContent = "Certificates, etc.";
    }
}

document.addEventListener('DOMContentLoaded', () => {
    let currentStep = 1;
    const totalSteps = 3;
    const form = document.getElementById('addEmployeeForm');
    const modal = document.getElementById('addEmployeeModal');
    
    function updateStepUI() {
        // Update Indicators
        document.querySelectorAll('.step-indicator').forEach(ind => {
            const step = parseInt(ind.dataset.step);
            ind.classList.toggle('active', step === currentStep);
            ind.classList.toggle('completed', step < currentStep);
        });

        // Update Panes
        document.querySelectorAll('.step-pane').forEach((pane, idx) => {
            pane.classList.toggle('active', idx + 1 === currentStep);
        });

        // Update Buttons
        const backBtn = document.getElementById('navBackBtn');
        const backText = document.getElementById('backBtnText');
        const backIcon = document.getElementById('backIcon');
        
        if (currentStep === 1) {
            backText.textContent = "Cancel Account";
            backBtn.classList.remove('text-primary-color');
            backBtn.classList.add('text-light');
            backIcon.setAttribute('data-lucide', 'x');
        } else {
            backText.textContent = "Back to Previous Step";
            backBtn.classList.remove('text-light');
            backBtn.classList.add('text-primary-color');
            backIcon.setAttribute('data-lucide', 'arrow-left');
        }
        
        // Re-initialize icons for the back button
        lucide.createIcons({
            attrs: {
                size: 18
            },
            nameAttr: 'data-lucide'
        });

        document.getElementById('nextStepBtn').classList.toggle('hidden', currentStep === totalSteps);
        document.getElementById('submitEmployeeBtn').classList.toggle('hidden', currentStep !== totalSteps);
        
        // Update Title Subtitle
        const titles = ["Personal Details", "Job & Banking", "Education & Experience"];
        const subs = ["Identity and contact information", "Employment setup and payroll details", "Academic background and documentation"];
        document.querySelector('#addEmployeeModal h3').textContent = "Step " + currentStep + ": " + titles[currentStep-1];
        document.querySelector('#addEmployeeModal p').textContent = subs[currentStep-1];
    }

    const nextStepBtn = document.getElementById('nextStepBtn');
    if (nextStepBtn) {
        nextStepBtn.addEventListener('click', () => {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepUI();
            }
        });
    }

    const navBackBtn = document.getElementById('navBackBtn');
    if (navBackBtn) {
        navBackBtn.addEventListener('click', () => {
            if (currentStep === 1) {
                closeModal('addEmployeeModal');
            } else {
                currentStep--;
                updateStepUI();
            }
        });
    }

    // Reset wizard when modal closes or opens
    window.openAddEmployeeModal = () => {
        currentStep = 1;
        if (form) form.reset();
        updateStepUI();
        openModal('addEmployeeModal');
    };
});

// Edit Employee Wizard Logic
document.addEventListener('DOMContentLoaded', () => {
    let editCurrentStep = 1;
    const editTotalSteps = 3;
    const editForm = document.getElementById('editEmployeeForm');
    
    function updateEditStepUI() {
        // Update Indicators
        const indicators = document.querySelectorAll('#editEmployeeModal .step-indicator');
        indicators.forEach(ind => {
            const step = parseInt(ind.dataset.step);
            ind.classList.toggle('active', step === editCurrentStep);
            ind.classList.toggle('completed', step < editCurrentStep);
            
            // Make indicators clickable for editing efficiency
            ind.style.cursor = 'pointer';
            ind.onclick = () => {
                editCurrentStep = step;
                updateEditStepUI();
            };
        });

        // Update Panes
        const panes = document.querySelectorAll('#editEmployeeModal .step-pane');
        panes.forEach((pane, idx) => {
            pane.classList.toggle('active', idx + 1 === editCurrentStep);
        });

        // Update Buttons
        const backBtn = document.getElementById('editNavBackBtn');
        const backText = document.getElementById('editBackBtnText');
        const backIcon = document.getElementById('editBackIcon');
        
        if (backBtn && backText && backIcon) {
            if (editCurrentStep === 1) {
                backText.textContent = "Cancel Changes";
                backBtn.classList.remove('text-primary-color');
                backBtn.classList.add('text-light');
                backIcon.setAttribute('data-lucide', 'x');
            } else {
                backText.textContent = "Back to Previous Step";
                backBtn.classList.remove('text-light');
                backBtn.classList.add('text-primary-color');
                backIcon.setAttribute('data-lucide', 'arrow-left');
            }
        }
        
        lucide.createIcons({
            attrs: { size: 18 },
            nameAttr: 'data-lucide'
        });

        const editNextStepBtn = document.getElementById('editNextStepBtn');
        const editSubmitBtn = document.getElementById('editSubmitBtn');
        if (editNextStepBtn) editNextStepBtn.classList.toggle('hidden', editCurrentStep === editTotalSteps);
        if (editSubmitBtn) editSubmitBtn.classList.toggle('hidden', editCurrentStep !== editTotalSteps);
        
        const titles = ["Edit Personal Details", "Edit Job & Banking", "Edit Education & Experience"];
        const subs = ["Update identity and contact information", "Update employment and payroll setup", "Update academic and professional details"];
        
        const modalHeaderH3 = document.querySelector('#editEmployeeModal h3');
        const modalHeaderP = document.querySelector('#editEmployeeModal p');
        if (modalHeaderH3) modalHeaderH3.textContent = titles[editCurrentStep-1];
        if (modalHeaderP) modalHeaderP.textContent = subs[editCurrentStep-1];
    }

    const editNextStepBtn = document.getElementById('editNextStepBtn');
    if (editNextStepBtn) {
        editNextStepBtn.addEventListener('click', () => {
            if (editCurrentStep < editTotalSteps) {
                editCurrentStep++;
                updateEditStepUI();
            }
        });
    }

    const editNavBackBtn = document.getElementById('editNavBackBtn');
    if (editNavBackBtn) {
        editNavBackBtn.addEventListener('click', () => {
            if (editCurrentStep === 1) {
                closeModal('editEmployeeModal');
            } else {
                editCurrentStep--;
                updateEditStepUI();
            }
        });
    }

    window.openEditEmployeeModal = (empId) => {
        editCurrentStep = 1;
        // Mock data loading
        console.log("Loading data for employee: " + empId);
        updateEditStepUI();
        openModal('editEmployeeModal');
    };
});

// --- Pagination & Table Logic (optional Exit-only filter) ---
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('employeeTableBody');
    const perPageSelect = document.getElementById('perPageSelect');
    const paginationInfo = document.getElementById('paginationInfo');
    const tableSummary = document.getElementById('tableSummary');
    const pageNumbersContainer = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const btnExitEmployees = document.getElementById('btnExitEmployees');

    if (!tableBody || !perPageSelect || !paginationInfo) return;

    const allRows = Array.from(tableBody.querySelectorAll('tr'));
    let currentPage = 1;
    let rowsPerPage = parseInt(perPageSelect.value, 10) || 10;
    let exitEmployeesFilterActive = false;

    function getFilteredRows() {
        if (!exitEmployeesFilterActive) return allRows;
        return allRows.filter((row) => row.getAttribute('data-emp-status') === 'exit');
    }

    function updateTable() {
        const filteredRows = getFilteredRows();
        const totalRows = filteredRows.length;
        const totalPages =
            totalRows === 0 ? 1 : rowsPerPage === -1 ? 1 : Math.ceil(totalRows / rowsPerPage);

        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const start = (currentPage - 1) * rowsPerPage;
        const end = rowsPerPage === -1 ? totalRows : start + rowsPerPage;

        allRows.forEach((row) => {
            row.style.display = 'none';
        });
        filteredRows.forEach((row, index) => {
            const show = rowsPerPage === -1 || (index >= start && index < end);
            row.style.display = show ? '' : 'none';
        });

        const showingStart = totalRows === 0 ? 0 : start + 1;
        const showingEnd = rowsPerPage === -1 ? totalRows : Math.min(end, totalRows);
        const infoText = `Showing ${showingStart} to ${showingEnd} of ${totalRows} entries`;
        paginationInfo.textContent = infoText;
        if (tableSummary) tableSummary.textContent = infoText;

        updatePaginationControls(totalPages);
    }

    function updatePaginationControls(totalPages) {
        if (!pageNumbersContainer || !prevBtn || !nextBtn) return;

        pageNumbersContainer.innerHTML = '';

        if (totalPages <= 1) {
            prevBtn.classList.add('hidden');
            nextBtn.classList.add('hidden');
            return;
        }
        prevBtn.classList.remove('hidden');
        nextBtn.classList.remove('hidden');

        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
        prevBtn.style.opacity = currentPage === 1 ? '0.5' : '1';
        nextBtn.style.opacity = currentPage === totalPages ? '0.5' : '1';

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
        rowsPerPage = val === 'all' ? -1 : parseInt(val, 10);
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
        const filteredRows = getFilteredRows();
        const totalRows = filteredRows.length;
        const totalPages =
            totalRows === 0 ? 1 : rowsPerPage === -1 ? 1 : Math.ceil(totalRows / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            updateTable();
        }
    };

    function syncExitEmployeesButtonUI() {
        if (!btnExitEmployees) return;
        const labelSpan = btnExitEmployees.querySelector('span');
        const iconEl = btnExitEmployees.querySelector('[data-lucide]');
        if (exitEmployeesFilterActive) {
            if (labelSpan) labelSpan.textContent = 'Back to All';
            if (iconEl) iconEl.setAttribute('data-lucide', 'arrow-left');
            btnExitEmployees.title = 'Show all employees';
            btnExitEmployees.setAttribute('aria-label', 'Back to all employees');
        } else {
            if (labelSpan) labelSpan.textContent = 'Exit Employees';
            if (iconEl) iconEl.setAttribute('data-lucide', 'log-out');
            btnExitEmployees.title = 'Show only employees with Exit status';
            btnExitEmployees.setAttribute('aria-label', 'Show only exit employees');
        }
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons({ nameAttr: 'data-lucide' });
        }
    }

    if (btnExitEmployees) {
        btnExitEmployees.addEventListener('click', () => {
            exitEmployeesFilterActive = !exitEmployeesFilterActive;
            currentPage = 1;
            btnExitEmployees.setAttribute('aria-pressed', exitEmployeesFilterActive ? 'true' : 'false');
            btnExitEmployees.classList.toggle('is-exit-filter-active', exitEmployeesFilterActive);
            syncExitEmployeesButtonUI();
            updateTable();
        });
    }

    updateTable();
});

// Employee directory: eye (view) → employee profile
document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('employeeTableBody');
    if (!tbody) return;

    tbody.addEventListener('click', (e) => {
        const viewBtn = e.target.closest('.action-btn-view');
        if (!viewBtn) return;

        const tr = viewBtn.closest('tr');
        if (!tr) return;

        const profileLink = tr.querySelector('a[href*="employee-profile.php"]');
        if (profileLink) {
            e.preventDefault();
            window.location.href = profileLink.href;
            return;
        }

        const idSpan = tr.querySelector('.emp-info .email');
        if (idSpan && idSpan.textContent.trim()) {
            e.preventDefault();
            window.location.href =
                'employee-profile.php?id=' + encodeURIComponent(idSpan.textContent.trim());
        }
    });
});
