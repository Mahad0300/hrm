// admin/assets/js/new-joining.js

document.addEventListener('DOMContentLoaded', () => {
    // 1. Load Requirements (Departments & Shifts)
    loadRequirementData();
    
    // 2. Fetch Pending Cards
    fetchPendingOnboarding();

    const refreshBtn = document.getElementById('njRefreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => refreshPendingList());
    }

    // 2. Handle Form Submission
    const hireForm = document.getElementById('hireCandidateForm');
    const submitBtn = document.getElementById('hireSubmitBtn');

    if (hireForm && submitBtn) {
        hireForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Prevent multiple submissions
            if (submitBtn.disabled) return;

            const hasResume = document.getElementById('cand_resume_wrapper')?.classList.contains('has-file')
                || (document.getElementById('cand_resume_upload')?.files?.length > 0);
            const hasIdCard = document.getElementById('cand_id_wrapper')?.classList.contains('has-file')
                || (document.getElementById('cand_id_upload')?.files?.length > 0);
            if (!hasResume) {
                Swal.fire('Attachment Required', 'Resume attachment is required.', 'warning');
                return;
            }
            if (!hasIdCard) {
                Swal.fire('Attachment Required', 'ID Card Attachment is required.', 'warning');
                return;
            }

            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }

            // Enterprise Validation: Phone Number (11 digits already handled by HTML5 pattern)
            
            const originalBtnContent = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-lucide="loader-2" class="spin" size="18"></i> <span>Activating Account...</span>';
            lucide.createIcons();

            const formData = new FormData(this);
            formData.append('status', 'Active'); // Finalizing the hire makes them Active

            try {
                // We use the same update logic because we're essentially finalizing the Pending record
                const response = await fetch('assets/api/employee_handler.php?action=update', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    // Force close modal immediately
                    const modal = document.getElementById('candidateEmployeeModal');
                    if (modal) modal.classList.remove('active');

                    Swal.fire({
                        title: 'Hired Successfully!',
                        text: 'Employee is now active and moved to the directory.',
                        icon: 'success',
                        confirmButtonColor: '#6c4cf1'
                    }).then(() => {
                        window.location.reload(); 
                    });
                } else {
                    Swal.fire('Error', result.message || 'Failed to activate employee.', 'error');
                }
            } catch (error) {
                console.error('Hiring Error:', error);
                Swal.fire('Error', 'Connection failed. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
                lucide.createIcons();
            }
        });
    }

    // Modal Close Logic (ensure it cleans up if needed)
    document.querySelectorAll('.js-modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById('candidateEmployeeModal');
            if (modal) modal.classList.remove('active');
            // Clean URL if we used openCandidate
            const url = new URL(window.location);
            url.searchParams.delete('openCandidate');
            window.history.replaceState({}, '', url);
        });
    });

    // --- Input Formatting & Masks ---

    // CNIC Formatting
    const candCnic = document.getElementById('cand_cnic');
    if (candCnic) {
        candCnic.addEventListener('input', function(e) {
            let val = e.target.value.replace(/\D/g, '').substring(0, 13);
            let formatted = '';
            if (val.length > 0) {
                formatted = val.substring(0, 5);
                if (val.length > 5) {
                    formatted += '-' + val.substring(5, 12);
                    if (val.length > 12) formatted += '-' + val.substring(12, 13);
                }
            }
            e.target.value = formatted;
        });
    }

    // Phone Masks
    const setupMask = (id) => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function(e) {
                let val = e.target.value.replace(/\D/g, '').substring(0, 11);
                let formatted = val.substring(0, 4);
                if (val.length > 4) formatted += '-' + val.substring(4);
                e.target.value = formatted;
            });
        }
    };
    setupMask('cand_phone');
    setupMask('cand_emergency_phone');

    // --- Real-time Email Verification ---
    const candEmail = document.getElementById('cand_email');
    const candEmailFeedback = document.getElementById('cand_email_feedback');

    if (candEmail && candEmailFeedback) {
        candEmail.addEventListener('blur', async function() {
            const email = this.value.trim();
            const hiddenId = document.getElementById('cand_id_hidden').value;

            if (email === '') {
                candEmailFeedback.textContent = '';
                return;
            }

            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(email)) {
                candEmailFeedback.textContent = '❌ Invalid email format.';
                candEmailFeedback.style.color = '#ef4444';
                return;
            }

            candEmailFeedback.textContent = 'Verifying...';
            candEmailFeedback.style.color = '#64748b';

            try {
                // When checking in modal, we might want to ignore the CURRENT record if we are updating, 
                // but usually onboarding is for NEW records, so simple check is fine.
                const response = await fetch(`assets/api/employee_handler.php?action=check_email&email=${encodeURIComponent(email)}`);
                const result = await response.json();

                if (result.status === 'success') {
                    candEmailFeedback.textContent = '✅ ' + result.message;
                    candEmailFeedback.style.color = '#10b981';
                    candEmail.style.borderColor = '#10b981';
                } else if (result.status === 'exited') {
                    candEmailFeedback.textContent = '⚠️ ' + result.message;
                    candEmailFeedback.style.color = '#d97706';
                    candEmail.style.borderColor = '#f59e0b';
                } else {
                    candEmailFeedback.textContent = '⚠️ ' + result.message;
                    candEmailFeedback.style.color = '#ef4444';
                    candEmail.style.borderColor = '#ef4444';
                }
            } catch (error) {
                candEmailFeedback.textContent = '';
            }
        });
    }

    // --- Password Visibility Toggle ---
    const togglePassBtn = document.getElementById('togglePasswordBtn');
    const passInput = document.getElementById('candidate_admin_password');
    if (togglePassBtn && passInput) {
        togglePassBtn.addEventListener('click', function() {
            const isPass = passInput.type === 'password';
            passInput.type = isPass ? 'text' : 'password';
            
            // Toggle Icon
            this.innerHTML = isPass 
                ? '<i data-lucide="eye-off" size="18"></i>' 
                : '<i data-lucide="eye" size="18"></i>';
            lucide.createIcons();
        });
    }
});

function formatJoiningCardDate(value) {
    if (!value) return '—';

    const datePart = String(value).split(' ')[0];
    const parts = datePart.split('-').map(Number);
    const date = parts.length === 3 && parts.every(Boolean)
        ? new Date(parts[0], parts[1] - 1, parts[2])
        : new Date(value);

    if (Number.isNaN(date.getTime())) return value;

    return date.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });
}

function showPendingListLoading() {
    const container = document.getElementById('pendingOnboardingContainer');
    if (!container) return;

    container.innerHTML = `
        <div class="col-span-full py-50 text-center">
            <i data-lucide="loader-2" class="spin text-primary-color mb-15" size="40"></i>
            <p class="text-light">Scanning for new joinings...</p>
        </div>
    `;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

async function refreshPendingList() {
    const refreshBtn = document.getElementById('njRefreshBtn');
    const originalHtml = refreshBtn ? refreshBtn.innerHTML : '';

    if (refreshBtn) {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i data-lucide="loader-2" class="spin" size="18"></i> <span>Refreshing...</span>';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    showPendingListLoading();
    await fetchPendingOnboarding();

    if (refreshBtn) {
        refreshBtn.disabled = false;
        refreshBtn.innerHTML = originalHtml;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

async function fetchPendingOnboarding() {
    const container = document.getElementById('pendingOnboardingContainer');
    if (!container) return;

    try {
        const response = await fetch('assets/api/employee_handler.php?action=fetch_pending');
        const result = await response.json();

        if (result.status === 'success' && result.data.length > 0) {
            container.innerHTML = result.data.map(emp => `
                <div class="announcement-card it-dept new-joining-card">
                    <div class="card-shape shape-1"></div>
                    <div class="card-shape shape-2"></div>
                    <div class="announcement-content">
                        <div class="flex-between mb-15">
                            <span class="card-category cat-it">New Joining</span>
                            <span class="badge badge-warning">Pending</span>
                        </div>
                        <h3 class="mb-2">${emp.first_name} ${emp.middle_name ? emp.middle_name + ' ' : ''}${emp.last_name}</h3>
                        <p class="font-12 text-primary font-600 mb-15">${emp.job_title || 'Employee Onboarding'}</p>
                        
                        <div class="candidate-info-rows new-joining-info">
                            <div class="new-joining-info__row">
                                <i data-lucide="mail-check" class="new-joining-info__icon"></i>
                                <span>${emp.email}</span>
                            </div>
                            <div class="new-joining-info__row">
                                <i data-lucide="phone-call" class="new-joining-info__icon"></i>
                                <span>${emp.phone || 'No phone provided'}</span>
                            </div>
                        </div>
                    </div>
                    <div class="announcement-footer">
                        <div class="new-joining-date">
                            <i data-lucide="calendar-days" class="new-joining-info__icon"></i>
                            <span>${formatJoiningCardDate(emp.created_at)}</span>
                        </div>
                        <div class="new-joining-actions">
                            <button class="action-btn action-btn-view" onclick="openHiringModal(${emp.id})" title="Finalize Hire">
                                <i data-lucide="user-check"></i>
                            </button>
                            <button class="action-btn action-btn-delete" type="button" title="Reject candidate">
                                <i data-lucide="trash-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
            lucide.createIcons();
        } else {
            container.innerHTML = `
                <div style="grid-column: 1 / -1; width: 100%; margin-top: 10px;">
                    <div class="empty-state-container">
                        <div class="empty-state-icon">
                            <i data-lucide="clipboard-check" size="32"></i>
                        </div>
                        <h4 class="empty-state-title">No pending onboardings found</h4>
                        <p class="empty-state-desc">When new candidates submit the joining form, they will appear here for administrative review.</p>
                    </div>
                </div>
            `;
            lucide.createIcons();
        }
    } catch (error) {
        console.error('Fetch Pending Error:', error);
        container.innerHTML = '<p class="text-danger p-20">Error loading pending records.</p>';
    }
}

async function openHiringModal(empId) {
    try {
        const response = await fetch(`assets/api/employee_handler.php?action=get_employee&id=${empId}`);
        const result = await response.json();

        if (result.status === 'success') {
            const data = result.data;
            
            // Populate Modal Fields (targets new-joining.php IDs)
            document.getElementById('cand_id_hidden').value = data.id;
            document.getElementById('cand_first_name').value = data.first_name || '';
            document.getElementById('cand_middle_name').value = data.middle_name || '';
            document.getElementById('cand_last_name').value = data.last_name || '';
            document.getElementById('cand_gender').value = data.gender || '';
            document.getElementById('cand_dob').value = data.dob || '';
            document.getElementById('cand_phone').value = data.phone || '';
            document.getElementById('cand_cnic').value = data.cnic_number || '';
            document.getElementById('cand_address').value = data.address || '';
            document.getElementById('cand_emergency_phone').value = data.emergency_contact || '';
            document.getElementById('cand_emergency_relation').value = data.emergency_relation || '';
            document.getElementById('cand_email').value = data.email || '';
            
            document.getElementById('cand_job_title').value = data.job_title || '';
            document.getElementById('cand_bank_name').value = data.bank_name || '';
            document.getElementById('cand_account_type').value = data.account_type || 'IBN';
            document.getElementById('cand_account_title').value = data.account_title || '';
            document.getElementById('cand_account_number').value = data.account_number || '';
            document.getElementById('cand_branch_info').value = data.branch_info || '';

            // Education & Experience Populate
            document.getElementById('cand_qualification').value = data.qualification || '';
            document.getElementById('cand_degree').value = data.degree_certification || '';
            document.getElementById('cand_college').value = data.college_university || '';
            document.getElementById('cand_expertise').value = data.professional_expertise || '';
            document.getElementById('cand_last_employer').value = data.last_employer || '';
            document.getElementById('cand_last_designation').value = data.last_designation || '';
            document.getElementById('cand_exp_from').value = data.experience_from || '';
            document.getElementById('cand_exp_to').value = data.experience_to || '';

            // --- Pre-fill File Status ---
            const updateFileStatus = (path, wrapperId, labelId, defaultText) => {
                const wrapper = document.getElementById(wrapperId);
                const label = document.getElementById(labelId);
                if (path && label && wrapper) {
                    const filename = path.split('/').pop();
                    label.textContent = filename || "File Uploaded";
                    label.classList.add('text-success');
                    wrapper.classList.add('has-file');
                } else if (label && wrapper) {
                    label.textContent = defaultText;
                    label.classList.remove('text-success');
                    wrapper.classList.remove('has-file');
                }
            };

            updateFileStatus(data.resume_path, 'cand_resume_wrapper', 'cand_resume_filename', 'Upload Resume');
            updateFileStatus(data.id_card_path, 'cand_id_wrapper', 'cand_id_filename', 'Upload ID Card');

            // Open Modal
            const modal = document.getElementById('candidateEmployeeModal');
            if (modal) modal.classList.add('active');
            
            lucide.createIcons();
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    } catch (error) {
        console.error('Fetch Candidate Details Error:', error);
        Swal.fire('Error', 'Failed to fetch onboarding details.', 'error');
    }
}

async function loadRequirementData() {
    try {
        const response = await fetch('assets/api/employee_handler.php?action=fetch_requirements');
        const result = await response.json();
        
        if (result.status === 'success') {
            const deptSelect = document.getElementById('cand_dept');
            const shiftSelect = document.getElementById('cand_shift');
            
            if (deptSelect) {
                deptSelect.innerHTML = '<option value="">Select Department</option>' + 
                    result.departments.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
            }
            
            if (shiftSelect) {
                const formatTime = (timeStr) => {
                    if (!timeStr) return '';
                    let [h, m] = timeStr.split(':');
                    h = parseInt(h);
                    let ampm = h >= 12 ? 'PM' : 'AM';
                    h = h % 12 || 12;
                    return `${h}:${m} ${ampm}`;
                };

                shiftSelect.innerHTML = '<option value="" selected disabled>Select Shift Timing</option>' + 
                    result.shifts.map(s => {
                        const timing = (s.start_time && s.end_time) ? ` (${formatTime(s.start_time)} - ${formatTime(s.end_time)})` : '';
                        return `<option value="${s.id}">${s.name}${timing}</option>`;
                    }).join('');
            }
        }
    } catch (error) {
        console.error('Error loading requirements:', error);
    }
}

// File selection feedback (Global or local)
function handleFileSelect(input, wrapperId, filenameId) {
    const wrapper = document.getElementById(wrapperId);
    const filenameLabel = document.getElementById(filenameId);
    
    if (input.files && input.files.length > 0) {
        wrapper.classList.add('has-file');
        filenameLabel.textContent = input.files[0].name;
        filenameLabel.classList.add('text-success');
    } else {
        // Only remove if it wasn't already a pre-filled file
        // For simplicity in this UI, we just reset
        wrapper.classList.remove('has-file');
        filenameLabel.classList.remove('text-success');
        
        if (wrapperId.includes('resume')) filenameLabel.textContent = "Upload Resume";
        else if (wrapperId.includes('id')) filenameLabel.textContent = "Upload ID Card";
        else filenameLabel.textContent = "Upload Other Docs";
    }
}
