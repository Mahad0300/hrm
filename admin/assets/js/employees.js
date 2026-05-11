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
        
        // Reset to default info based on wrapper ID
        if (wrapperId.includes('resume')) filenameLabel.textContent = "PDF, DOCX up to 5MB";
        else if (wrapperId.includes('id')) filenameLabel.textContent = "PNG, JPG or PDF";
        else filenameLabel.textContent = "Certificates, etc.";
    }
}
async function loadRequirementData() {
    try {
        const response = await fetch('assets/api/employee_handler.php?action=fetch_requirements');
        const result = await response.json();
        
        if (result.status === 'success') {
            const addDept = document.getElementById('add_dept');
            const addShift = document.getElementById('add_shift');
            const editDept = document.getElementById('edit_dept');
            const editShift = document.getElementById('edit_shift');
            const filterDept = document.getElementById('filterDept');
            
            const deptOptions = '<option value="">Select Department</option>' + 
                result.departments.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
                
            const filterDeptOptions = '<option value="">All Departments</option>' + 
                result.departments.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
                
            const formatTime = (timeStr) => {
                if (!timeStr) return '';
                let [h, m] = timeStr.split(':');
                h = parseInt(h);
                let ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12 || 12;
                return `${h}:${m} ${ampm}`;
            };

            const shiftOptions = '<option value="">Select Shift</option>' + 
                result.shifts.map(s => {
                    const timing = (s.start_time && s.end_time) ? ` (${formatTime(s.start_time)} - ${formatTime(s.end_time)})` : '';
                    return `<option value="${s.id}">${s.name}${timing}</option>`;
                }).join('');

            if (addDept) addDept.innerHTML = deptOptions;
            if (editDept) editDept.innerHTML = deptOptions;
            if (filterDept) filterDept.innerHTML = filterDeptOptions;
            
            if (addShift) addShift.innerHTML = shiftOptions;
            if (editShift) editShift.innerHTML = shiftOptions;
        }
    } catch (error) {
        console.error('Error loading requirements:', error);
    }
}

// --- Pagination & Table Logic (Dynamic Server-side) ---
document.addEventListener('DOMContentLoaded', () => {
    // Selectors
    const tableBody = document.getElementById('employeeTableBody');
    const perPageSelect = document.getElementById('perPageSelect');
    const filterID = document.getElementById('filterID');
    const filterName = document.getElementById('filterName');
    const filterDept = document.getElementById('filterDept');
    const filterRole = document.getElementById('filterRole');
    const paginationInfo = document.getElementById('paginationInfo');
    const tableSummary = document.getElementById('tableSummary');
    const pageNumbersContainer = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const btnExitEmployees = document.getElementById('btnExitEmployees');

    if (!tableBody) return;

    let currentPage = 1;
    let entriesLimit = parseInt(perPageSelect.value) || 10;
    let isExitOnly = false;
    let searchTimer;

    async function fetchEmployees() {
        // Show loading state
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-50">
                    <div class="flex-column flex-center text-center">
                        <i data-lucide="loader-2" class="spin text-primary-color mb-10" size="32"></i>
                        <p class="text-light">Fetching directory entries...</p>
                    </div>
                </td>
            </tr>
        `;
        lucide.createIcons();

        try {
            const id_search = filterID ? filterID.value : '';
            const name_search = filterName ? filterName.value : '';
            const dept = filterDept ? filterDept.value : '';
            const role = filterRole ? filterRole.value : '';
            // Default to empty status unless it's Exit toggle
            const status = isExitOnly ? 'Exit' : '';

            const url = `assets/api/employee_handler.php?action=fetch_directory&page=${currentPage}&limit=${entriesLimit}&id_search=${encodeURIComponent(id_search)}&name_search=${encodeURIComponent(name_search)}&department=${dept}&role=${role}&status=${status}`;
            
            const response = await fetch(url);
            const result = await response.json();

            if (result.status === 'success') {
                renderEmployees(result.data);
                updatePaginationControls(result.total, result.page, result.limit);
            } else {
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-30 text-danger">${result.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Fetch Directory Error:', error);
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-30 text-danger">Unable to load directory entries.</td></tr>`;
        }
    }

    function renderEmployees(employees) {
        if (!employees || employees.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-50">
                        <div class="noti-empty" style="display: flex;">
                            <div class="noti-empty-icon text-light opacity-30">
                                <i data-lucide="user-minus" size="48"></i>
                            </div>
                            <h3 class="noti-empty-title">No employees found</h3>
                            <p class="noti-empty-text">Adjust your filters or try a different search term.</p>
                        </div>
                    </td>
                </tr>
            `;
            lucide.createIcons();
            return;
        }

        tableBody.innerHTML = employees.map(emp => {
            const statusClass = (emp.status || 'Active').toLowerCase().replace(' ', '');
            const badgeClass = statusClass === 'active' ? 'badge-success' : 
                               (statusClass === 'onleave' ? 'badge-warning' : 
                               (statusClass === 'terminated' || statusClass === 'exit' ? 'badge-danger' : 'badge-light'));

            return `
                <tr data-emp-id="${emp.id}">
                    <td>
                        <a href="employee-profile.php?id=${emp.id}" class="emp-profile no-decoration hover-opacity">
                            <img src="${emp.profile_pic ? '../' + emp.profile_pic : '../images/profile-image/default-avatar.svg'}"
                                class="emp-avatar" alt="Avatar"
                                onerror="this.src='../images/profile-image/default-avatar.svg'">
                            <div class="emp-info">
                                <span class="name">${emp.first_name} ${emp.middle_name ? emp.middle_name + ' ' : ''}${emp.last_name}</span>
                                <span class="email">EMP-0${emp.id}</span>
                            </div>
                        </a>
                    </td>
                    <td class="allow-wrap">${emp.email}</td>
                    <td class="allow-wrap">${emp.dept_name || 'Unassigned'}</td>
                    <td>${emp.job_title || 'Employee'}</td>
                    <td><span class="badge ${badgeClass}">${emp.status}</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <button class="action-btn action-btn-view" title="View Details" onclick="window.location.href='employee-profile.php?id=${emp.id}'"><i data-lucide="eye"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-edit" title="Edit"
                                onclick="openEditEmployeeModal('${emp.id}')"><i data-lucide="user-pen"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete" onclick="deleteEmployee('${emp.id}')"><i data-lucide="trash-2"
                                    size="14"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
        
        lucide.createIcons();
    }

    function updatePaginationControls(total, page, limit) {
        if (!paginationInfo) return;
        
        const totalPages = Math.ceil(total / limit) || 1;
        const start = total === 0 ? 0 : (page - 1) * limit + 1;
        const end = Math.min(page * limit, total);
        
        const infoText = `Showing ${start} to ${end} of ${total} entries`;
        paginationInfo.textContent = infoText;
        if (tableSummary) tableSummary.textContent = infoText;

        if (pageNumbersContainer) {
            pageNumbersContainer.innerHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                if (totalPages > 5 && i > 3 && i < totalPages) {
                    if (i === 4) {
                        const dots = document.createElement('span');
                        dots.textContent = '...';
                        dots.className = 'px-10 text-light';
                        pageNumbersContainer.appendChild(dots);
                    }
                    continue;
                }
                const btn = document.createElement('button');
                btn.className = `action-btn ${i === page ? 'btn-active' : ''}`;
                btn.textContent = i;
                btn.onclick = () => { currentPage = i; fetchEmployees(); };
                pageNumbersContainer.appendChild(btn);
            }
        }

        if (prevBtn) {
            prevBtn.disabled = page === 1;
            prevBtn.style.opacity = page === 1 ? '0.5' : '1';
            prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; fetchEmployees(); } };
        }
        if (nextBtn) {
            nextBtn.disabled = page === totalPages;
            nextBtn.style.opacity = page === totalPages ? '0.5' : '1';
            nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; fetchEmployees(); } };
        }
    }

    // Event Listeners
    if (perPageSelect) perPageSelect.onchange = () => { 
        const val = perPageSelect.value;
        entriesLimit = val === 'all' ? 1000 : parseInt(val);
        currentPage = 1; 
        fetchEmployees(); 
    };
    if (filterDept) filterDept.onchange = () => { currentPage = 1; fetchEmployees(); };
    if (filterRole) filterRole.onchange = () => { currentPage = 1; fetchEmployees(); };
    
    const handleSearchInput = () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            currentPage = 1;
            fetchEmployees();
        }, 400);
    };

    if (filterID) filterID.oninput = handleSearchInput;
    if (filterName) filterName.oninput = handleSearchInput;

    if (btnExitEmployees) {
        btnExitEmployees.onclick = () => {
            isExitOnly = !isExitOnly;
            btnExitEmployees.classList.toggle('btn-active', isExitOnly);
            const label = btnExitEmployees.querySelector('span');
            const icon = btnExitEmployees.querySelector('[data-lucide]');
            
            if (isExitOnly) {
                if (label) label.textContent = "Back to All";
                if (icon) icon.setAttribute('data-lucide', 'arrow-left');
            } else {
                if (label) label.textContent = "Exit Employees";
                if (icon) icon.setAttribute('data-lucide', 'log-out');
            }
            lucide.createIcons();
            currentPage = 1;
            fetchEmployees();
        };
    }

    // Modal close hooks to refresh data
    const modalCloses = document.querySelectorAll('.js-modal-close');
    modalCloses.forEach(btn => {
        btn.addEventListener('click', () => {
            // Optional: refresh if something changed?
        });
    });

    // --- Wizard Navigation Logic (Add & Edit) ---
    let addCurrentStep = 1;
    let editCurrentStep = 1;

    function updateWizardUI(modalId, step) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Update Indicators
        const indicators = modal.querySelectorAll('.step-indicator');
        indicators.forEach(ind => {
            const indStep = parseInt(ind.getAttribute('data-step'));
            ind.classList.toggle('active', indStep === step);
            ind.classList.toggle('completed', indStep < step);
        });

        // Update Panes
        const panes = modal.querySelectorAll('.step-pane');
        panes.forEach((pane, idx) => {
            pane.classList.toggle('active', (idx + 1) === step);
        });

        // Update Buttons
        const backBtn = modal.querySelector(modalId === 'addEmployeeModal' ? '#navBackBtn' : '#editNavBackBtn');
        const nextBtn = modal.querySelector(modalId === 'addEmployeeModal' ? '#nextStepBtn' : '#editNextStepBtn');
        const submitBtn = modal.querySelector(modalId === 'addEmployeeModal' ? '#submitEmployeeBtn' : '#editSubmitBtn');
        const backText = modal.querySelector(modalId === 'addEmployeeModal' ? '#backBtnText' : '#editBackBtnText');
        const backIcon = modal.querySelector(modalId === 'addEmployeeModal' ? '#backIcon' : '#editBackIcon');

        if (step === 1) {
            backText.textContent = modalId === 'addEmployeeModal' ? "Cancel Account" : "Cancel Changes";
            backIcon.setAttribute('data-lucide', 'x');
        } else {
            backText.textContent = "Back Step";
            backIcon.setAttribute('data-lucide', 'arrow-left');
        }

        if (step === 3) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        }

        lucide.createIcons();
    }

    // Global Add Opener
    window.openAddEmployeeModal = () => {
        addCurrentStep = 1;
        updateWizardUI('addEmployeeModal', 1);
        document.getElementById('addEmployeeModal').classList.add('active');
        // Reset form
        document.getElementById('addEmployeeForm')?.reset();
        // Reset file status
        ['resume', 'id', 'other'].forEach(type => {
            const wrapper = document.getElementById(`${type}_wrapper`);
            if (wrapper) wrapper.classList.remove('has-file');
        });
    };

    // Add Modal Nav
    document.getElementById('nextStepBtn')?.addEventListener('click', () => {
        if (addCurrentStep < 3) {
            addCurrentStep++;
            updateWizardUI('addEmployeeModal', addCurrentStep);
        }
    });
    document.getElementById('navBackBtn')?.addEventListener('click', () => {
        if (addCurrentStep > 1) {
            addCurrentStep--;
            updateWizardUI('addEmployeeModal', addCurrentStep);
        } else {
            document.getElementById('addEmployeeModal').classList.remove('active');
        }
    });

    // Edit Modal Nav
    document.getElementById('editNextStepBtn')?.addEventListener('click', () => {
        if (editCurrentStep < 3) {
            editCurrentStep++;
            updateWizardUI('editEmployeeModal', editCurrentStep);
        }
    });
    document.getElementById('editNavBackBtn')?.addEventListener('click', () => {
        if (editCurrentStep > 1) {
            editCurrentStep--;
            updateWizardUI('editEmployeeModal', editCurrentStep);
        } else {
            document.getElementById('editEmployeeModal').classList.remove('active');
        }
    });

    // Global Edit Opener
    window.openEditEmployeeModal = async (empId) => {
        editCurrentStep = 1;
        updateWizardUI('editEmployeeModal', 1);
        
        try {
            const response = await fetch(`assets/api/employee_handler.php?action=get_employee&id=${empId}`);
            const result = await response.json();
            
            if (result.status === 'success') {
                const data = result.data;
                const modal = document.getElementById('editEmployeeModal');
                
                // Bind Data to Fields
                document.getElementById('edit_id_hidden').value = data.id;
                document.getElementById('edit_first_name').value = data.first_name || '';
                document.getElementById('edit_middle_name').value = data.middle_name || '';
                document.getElementById('edit_last_name').value = data.last_name || '';
                document.getElementById('edit_gender').value = data.gender || 'Male';
                document.getElementById('edit_dob').value = data.dob || '';
                document.getElementById('edit_phone').value = data.phone || '';
                document.getElementById('edit_cnic').value = data.cnic_number || '';
                document.getElementById('edit_address').value = data.address || '';
                document.getElementById('edit_emergency_phone').value = data.emergency_contact || '';
                document.getElementById('edit_emergency_relation').value = data.emergency_relation || '';
                document.getElementById('edit_email').value = data.email || '';
                
                // Job & Bank
                document.getElementById('edit_shift').value = data.shift_id || '';
                document.getElementById('edit_dept').value = data.department_id || '';
                document.getElementById('edit_job_title').value = data.job_title || '';
                document.getElementById('edit_job_type').value = data.job_type || 'Permanent';
                document.getElementById('edit_salary').value = data.salary ? Math.round(data.salary) : '';
                document.getElementById('edit_joining_date').value = data.joining_date || '';
                
                document.getElementById('edit_bank_name').value = data.bank_name || '';
                document.getElementById('edit_account_type').value = data.account_type || 'IBN';
                document.getElementById('edit_account_title').value = data.account_title || '';
                document.getElementById('edit_account_number').value = data.account_number || '';
                document.getElementById('edit_branch_info').value = data.branch_info || '';

                // Education
                document.getElementById('edit_qualification').value = data.qualification || '';
                document.getElementById('edit_degree').value = data.degree_certification || '';
                document.getElementById('edit_college').value = data.college_university || '';
                document.getElementById('edit_expertise').value = data.professional_expertise || '';
                document.getElementById('edit_last_employer').value = data.last_employer || '';
                document.getElementById('edit_last_designation').value = data.last_designation || '';
                document.getElementById('edit_experience_from').value = data.experience_from || '';
                document.getElementById('edit_experience_to').value = data.experience_to || '';

                // File Status Update
                const updateFileStatus = (type, path, isOther = false) => {
                    const wrapper = document.getElementById(`edit_${type}_wrapper`);
                    const filenameLabel = document.getElementById(`edit_${type}_filename`);
                    const infoLabel = wrapper?.querySelector('.file-upload-info');
                    const icon = wrapper?.querySelector('i');

                    if (path && ((!isOther) || (isOther && JSON.parse(path || '[]').length > 0))) {
                        wrapper.classList.add('has-file');
                        if (icon) icon.className = 'text-success';
                        
                        if (isOther) {
                            const docs = JSON.parse(path || '[]');
                            const filenames = docs.map(p => p.split('/').pop()).join(', ');
                            filenameLabel.textContent = filenames;
                            if (infoLabel) {
                                infoLabel.textContent = "Click to add more";
                                infoLabel.classList.add('text-success');
                            }
                        } else {
                            filenameLabel.textContent = path.split('/').pop(); // Get filename
                            if (infoLabel) {
                                infoLabel.textContent = "File already uploaded";
                                infoLabel.classList.add('text-success');
                            }
                        }
                    } else {
                        wrapper.classList.remove('has-file');
                        if (icon) icon.className = '';
                        filenameLabel.textContent = type === 'resume' ? "Resume_Sophia_R.pdf" : (type === 'id' ? "ID_Card_Front.jpg" : "Choose Files");
                        if (infoLabel) {
                            infoLabel.textContent = type === 'resume' ? "PDF, DOCX up to 5MB" : (type === 'id' ? "PNG, JPG or PDF" : "Certificates, etc.");
                            infoLabel.classList.remove('text-success');
                        }
                    }
                };

                updateFileStatus('resume', data.resume_path);
                updateFileStatus('id', data.id_card_path);
                updateFileStatus('other', data.other_docs, true);

                modal.classList.add('active');
            } else {
                Swal.fire('Error', 'Unable to fetch employee data', 'error');
            }
        } catch (error) {
            console.error('Error opening edit modal:', error);
            Swal.fire('Error', 'An unexpected error occurred', 'error');
        }
    };

    // Edit Form Submission
    document.getElementById('editEmployeeForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById('editSubmitBtn');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader-2" class="spin" size="18"></i> Saving...';
        lucide.createIcons();

        try {
            const formData = new FormData(e.target);
            formData.append('action', 'update');
            
            const response = await fetch('assets/api/employee_handler.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'success') {
                document.getElementById('editEmployeeModal').classList.remove('active');
                fetchEmployees();
                Swal.fire('Success', 'Employee updated successfully', 'success');
            } else {
                Swal.fire('Error', result.message || 'Update failed', 'error');
            }
        } catch (error) {
            console.error('Update Error:', error);
            Swal.fire('Error', 'Network error or server deviation', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            lucide.createIcons();
        }
    });
    
    // Add Form Submission
    document.getElementById('addEmployeeForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById('submitEmployeeBtn');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader-2" class="spin" size="18"></i> Creating...';
        lucide.createIcons();

        try {
            const formData = new FormData(e.target);
            formData.append('action', 'add');
            
            const response = await fetch('assets/api/employee_handler.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'success') {
                document.getElementById('addEmployeeModal').classList.remove('active');
                e.target.reset(); // Clear form
                fetchEmployees();
                Swal.fire('Success', 'Employee account created successfully', 'success');
            } else {
                Swal.fire('Error', result.message || 'Creation failed', 'error');
            }
        } catch (error) {
            console.error('Creation Error:', error);
            Swal.fire('Error', 'Network error or server deviation', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            lucide.createIcons();
        }
    });

    // --- Input Masking for Phone & CNIC ---
    const applyMask = (selector, type) => {
        const input = document.getElementById(selector);
        if (!input) return;

        input.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            if (type === 'phone') {
                if (value.length > 4) value = value.slice(0, 4) + '-' + value.slice(4, 11);
                else value = value.slice(0, 11);
            } else if (type === 'cnic') {
                if (value.length > 5 && value.length <= 12) value = value.slice(0, 5) + '-' + value.slice(5);
                else if (value.length > 12) value = value.slice(0, 5) + '-' + value.slice(5, 12) + '-' + value.slice(12, 13);
            }
            e.target.value = value;
        });
    };

    ['add_phone', 'add_emergency_phone', 'edit_phone', 'edit_emergency_phone'].forEach(id => applyMask(id, 'phone'));
    ['add_cnic', 'edit_cnic'].forEach(id => applyMask(id, 'cnic'));

    // Initial Fetch
    fetchEmployees();
    loadRequirementData();

    // --- Real-time Email Verification ---
    const addEmail = document.getElementById('add_email');
    const emailMsg = document.getElementById('email_verify_msg');
    let emailTimer;

    addEmail?.addEventListener('input', (e) => {
        const email = e.target.value.trim();
        clearTimeout(emailTimer);
        
        if (email.length < 5) {
            emailMsg.textContent = '';
            return;
        }

        emailTimer = setTimeout(async () => {
            try {
                const response = await fetch(`assets/api/employee_handler.php?action=check_email&email=${encodeURIComponent(email)}`);
                const result = await response.json();
                
                if (result.status === 'error') {
                    emailMsg.textContent = '⚠️ ' + result.message;
                    emailMsg.className = 'font-11 mt-4 block text-danger';
                    addEmail.style.borderColor = '#ef4444';
                } else {
                    emailMsg.textContent = '✅ ' + result.message;
                    emailMsg.className = 'font-11 mt-4 block text-success';
                    addEmail.style.borderColor = '#10b981';
                }
            } catch (error) {
                console.error('Email Verification Error:', error);
                emailMsg.textContent = '';
                addEmail.style.borderColor = '';
            }
        }, 500);
    });
});

// Finalize delete function
window.deleteEmployee = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this employee record? This action can be undone by admin.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6c4cf1',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        try {
            const formData = new FormData();
            formData.append('id', id);
            const response = await fetch('assets/api/employee_handler.php?action=delete', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                Swal.fire('Deleted!', 'Employee record has been deleted.', 'success')
                .then(() => {
                    // Small delay to ensure DB sync if needed
                    setTimeout(() => window.location.reload(), 500);
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Unable to delete record.', 'error');
        }
    }
}
;
