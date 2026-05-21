// user/assets/js/profile.js

document.addEventListener('DOMContentLoaded', () => {
    const profileAvatarImg = document.getElementById('profileAvatarImg');
    const profileAvatarInput = document.getElementById('profileAvatarInput');
    const editProfileForm = document.getElementById('editProfileForm');
    const editProfileModal = document.getElementById('editProfileModal');
    const openEditProfileModalBtn = document.getElementById('openEditProfileModalBtn');

    let userData = null;

    const defaultAvatarPath = '../images/profile-image/default-avatar.svg';

    function updateTopbarAvatar(profilePic) {
        const topbarImg = document.querySelector('.user-profile-dropdown .user-avatar');
        if (!topbarImg) return;
        topbarImg.src = profilePic ? '../' + profilePic : defaultAvatarPath;
    }

    // --- Input Masking Helper ---
    const applyMask = (id, type) => {
        const input = document.getElementById(id);
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

    // --- 1. Fetch & Load Profile Data ---
    async function loadProfile() {
        try {
            const response = await fetch('assets/api/profile_handler.php?action=fetch');
            const result = await response.json();

            if (result.status === 'success') {
                userData = result.data;
                renderProfileData(userData);
            } else {
                console.error('Error fetching profile:', result.message);
                showToast('Error loading profile data.', 'error');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            showToast('Connection error. Please try again.', 'error');
        }
    }

    function renderProfileData(user) {
        const safeSetText = (id, text, fallback = '-') => {
            const el = document.getElementById(id);
            if (el) el.textContent = text || fallback;
        };

        // Primary Identity
        const fullName = [user.first_name, user.middle_name, user.last_name].filter(v => v && v.trim() !== '').join(' ');
        safeSetText('profileFullName', fullName);
        safeSetText('profileJobTitle', user.job_title, 'N/A');
        
        // Profile Picture
        if (profileAvatarImg) {
            const defaultAvatar = profileAvatarImg.getAttribute('data-default-avatar') || defaultAvatarPath;
            const picSrc = user.profile_pic ? '../' + user.profile_pic : defaultAvatar;
            profileAvatarImg.src = picSrc;
            updateTopbarAvatar(user.profile_pic);
        }

        // Contacts
        safeSetText('pf_email', user.email);
        safeSetText('pf_phone', user.phone, 'Not provided');
        safeSetText('pf_emergencyPhone', user.emergency_contact, 'None');
        safeSetText('pf_emergencyRelation', user.emergency_relation, 'N/A');

        // Personal Details
        safeSetText('pf_firstName', user.first_name);
        safeSetText('pf_middleName', user.middle_name, '-');
        safeSetText('pf_lastName', user.last_name);
        safeSetText('pf_gender', user.gender, '-');
        safeSetText('pf_address', user.address, 'No address provided');
        
        // Formatting Helpers
        const formatTime = (timeStr) => {
            if (!timeStr) return '';
            const [hours, minutes] = timeStr.split(':');
            let h = parseInt(hours);
            const m = minutes || '00';
            const ampm = h >= 12 ? ' PM' : ' AM';
            h = h % 12;
            h = h ? h : 12;
            return `${h}:${m}${ampm}`;
        };

        const formatDate = (dateStr) => {
            if (!dateStr || dateStr === '0000-00-00') return 'N/A';
            const options = { month: 'short', day: 'numeric', year: 'numeric' };
            try {
                // Replacing '-' with '/' forces browser to parse as local time instead of UTC
                const localDate = new Date(dateStr.replace(/-/g, '/'));
                return localDate.toLocaleDateString('en-US', options);
            } catch (e) {
                return dateStr;
            }
        };

        safeSetText('pf_dob', formatDate(user.dob));
        safeSetText('pf_idCard', user.cnic_number, 'N/A');

        // Job & Banking Summary
        const shiftText = user.shift_name ? `${user.shift_name} (${formatTime(user.start_time)} - ${formatTime(user.end_time)})` : 'Not Set';
        safeSetText('pf_shift', shiftText);
        safeSetText('pf_jobTitleDisplay', user.job_title, 'N/A');
        safeSetText('pf_dept', user.dept_name, 'N/A');
        safeSetText('pf_jobType', user.job_type, 'N/A');
        
        const salaryText = user.salary ? parseFloat(user.salary).toLocaleString() : '0';
        safeSetText('pf_salary', salaryText);
        safeSetText('pf_joiningDate', formatDate(user.joining_date));

        // Leave Summary Rendering
        if (user.leave_summary && Array.isArray(user.leave_summary)) {
            user.leave_summary.forEach(leave => {
                // Normalize name: "Sick Leave" -> "Sick", "CASUAL" -> "Casual"
                const typeName = leave.name.trim().toLowerCase();
                let prefix = '';
                if (typeName.includes('sick')) prefix = 'Sick';
                else if (typeName.includes('casual')) prefix = 'Casual';
                else if (typeName.includes('annual')) prefix = 'Annual';

                if (prefix) {
                    const used = parseInt(leave.used) || 0;
                    const total = parseInt(leave.days_per_year) || 0;
                    safeSetText(`empLeave${prefix}Used`, used, '0');
                    safeSetText(`empLeave${prefix}Remaining`, (total - used), '0');
                }
            });
        } else {
            ['Sick', 'Casual', 'Annual'].forEach(prefix => {
                safeSetText(`empLeave${prefix}Used`, '0');
                safeSetText(`empLeave${prefix}Remaining`, '0');
            });
        }

        // Banking
        safeSetText('pf_bankName', user.bank_name, 'Not Added');
        safeSetText('pf_accountType', user.account_type, '-');
        safeSetText('pf_accountTitle', user.account_title, '-');
        safeSetText('pf_accountNumber', user.account_number, '-');
        safeSetText('pf_bankBranch', user.branch_info, '-');

        // Education
        safeSetText('pf_qualification', user.qualification, 'N/A');
        safeSetText('pf_degreeCert', user.degree_cert, 'N/A');
        safeSetText('pf_college', user.university, 'N/A');
        safeSetText('pf_expertise', user.expertise, 'N/A');
        safeSetText('pf_lastEmployer', user.last_employer, 'N/A');
        safeSetText('pf_prevJobTitle', user.last_job_title, 'N/A');
        safeSetText('pf_expFrom', user.exp_from, '-');
        safeSetText('pf_expTo', user.exp_to, '-');

        // Documentation Labels & Links
        const resumeLink = document.getElementById('pf_resumeLink');
        const idDocLink = document.getElementById('pf_idDocLink');

        if (user.resume_path) {
            const fileName = user.resume_path.split('/').pop();
            safeSetText('pf_resumeLabel', fileName);
            if (resumeLink) {
                resumeLink.href = '../' + user.resume_path;
                resumeLink.style.display = 'block';
            }
        } else if (resumeLink) {
            resumeLink.style.display = 'none';
        }

        if (user.id_card_path) {
            const fileName = user.id_card_path.split('/').pop();
            safeSetText('pf_idDocLabel', fileName);
            if (idDocLink) {
                idDocLink.href = '../' + user.id_card_path;
                idDocLink.style.display = 'block';
            }
        } else if (idDocLink) {
            idDocLink.style.display = 'none';
        }
        
        // Other Documents Dynamic Rendering
        const otherDocsContainer = document.getElementById('pf_otherDocsContainer');
        if (otherDocsContainer) {
            otherDocsContainer.innerHTML = '';
            let otherDocs = [];
            try {
                if (user.other_docs) {
                    otherDocs = typeof user.other_docs === 'string' ? JSON.parse(user.other_docs) : user.other_docs;
                }
            } catch (e) {
                console.error("Error parsing other_docs", e);
            }

            if (otherDocs && Array.isArray(otherDocs)) {
                otherDocs.forEach(docPath => {
                    const fileName = docPath.split('/').pop();
                    const card = document.createElement('a');
                    card.href = '../' + docPath;
                    card.target = '_blank';
                    card.className = 'doc-card border rounded-16 p-20 hover-bg-light transition block no-underline';
                    card.innerHTML = `
                        <label class="admin-form-label cursor-pointer">Other Documents</label>
                        <div class="flex-center gap-12">
                            <div class="icon-square-40 bg-light-soft text-light">
                                <i data-lucide="files" size="20"></i>
                            </div>
                            <div class="overflow-hidden">
                                <span class="font-13 font-600 truncate block">${fileName}</span>
                            </div>
                        </div>
                    `;
                    otherDocsContainer.appendChild(card);
                });
                lucide.createIcons();
            }
        } 

        // Salary History Timeline
        const timelineContainer = document.getElementById('salaryTimeline');
        const latestSalaryEl = document.getElementById('pf_latestSalary');
        
        if (timelineContainer) {
            timelineContainer.innerHTML = '';
            const history = user.salary_history || [];
            
            if (history.length === 0) {
                timelineContainer.innerHTML = '<p class="font-12 text-light italic">No salary history records found.</p>';
            }

            history.forEach((record, index) => {
                const item = document.createElement('div');
                const isIncrement = record.type === 'Increment';
                item.className = `timeline-item ${isIncrement ? 'inc' : 'dec'}`;
                
                const changeDateStr = record.change_date ? record.change_date.replace(/-/g, '/') : null;
                const formattedDate = changeDateStr ? new Date(changeDateStr).toLocaleDateString('en-US', {
                    month: 'short', day: '2-digit', year: 'numeric'
                }) : 'N/A';

                const amountText = parseFloat(record.new_salary).toLocaleString();
                const changeAmount = parseFloat(record.amount_change);
                const changeText = (isIncrement ? '+' : '-') + Math.abs(changeAmount).toLocaleString();
                const isRecent = index === 0 && isIncrement;

                item.innerHTML = `
                    <div class="timeline-info">
                        <span class="font-15 ${index === 0 ? 'font-700' : 'font-600'} text-dark block">${amountText}</span>
                        <span class="font-11 ${isIncrement ? 'text-success' : 'text-danger'} font-600" style="margin-top: -4px;">${changeText}</span>
                        <span class="font-12 text-light font-500">${formattedDate}</span>
                        ${isRecent ? `
                        <div class="mt-8">
                            <span class="badge badge-success px-10 py-4 font-10">Recent Increment</span>
                        </div>` : ''}
                    </div>
                `;
                timelineContainer.appendChild(item);
            });

            // Update latest salary label
            if (latestSalaryEl) {
                const currentSal = user.salary || 0;
                latestSalaryEl.textContent = parseFloat(currentSal).toLocaleString();
            }
        }
    }

    // --- 2. Populate Modal Fields ---
    function populateModal(user) {
        document.getElementById('ep_firstName').value = user.first_name || '';
        document.getElementById('ep_middleName').value = user.middle_name || '';
        document.getElementById('ep_lastName').value = user.last_name || '';
        document.getElementById('ep_gender').value = user.gender || 'Male';
        document.getElementById('ep_dob').value = user.dob || '';
        document.getElementById('ep_phone').value = user.phone || '';
        document.getElementById('ep_idCard').value = user.cnic_number || '';
        document.getElementById('ep_address').value = user.address || '';
        document.getElementById('ep_email').value = user.email || '';
        document.getElementById('ep_jobTitle').value = user.job_title || '';
        
        document.getElementById('ep_emergencyPhone').value = user.emergency_contact || '';
        document.getElementById('ep_emergencyRelation').value = user.emergency_relation || '';

        document.getElementById('ep_bankName').value = user.bank_name || '';
        document.getElementById('ep_accountType').value = user.account_type || '';
        document.getElementById('ep_accountTitle').value = user.account_title || '';
        document.getElementById('ep_accountNumber').value = user.account_number || '';
        document.getElementById('ep_bankBranch').value = user.branch_info || '';

        document.getElementById('ep_qualification').value = user.qualification || '';
        document.getElementById('ep_degreeCert').value = user.degree_cert || '';
        document.getElementById('ep_college').value = user.university || '';
        document.getElementById('ep_expertise').value = user.expertise || '';
        document.getElementById('ep_lastEmployer').value = user.last_employer || '';
        document.getElementById('ep_prevJobTitle').value = user.last_job_title || '';
        document.getElementById('ep_expFrom').value = user.exp_from || '';
        document.getElementById('ep_expTo').value = user.exp_to || '';

        // Reset & Set File Status Labels
        const fileSections = [
            { type: 'resume', path: user.resume_path, default: "PDF, DOCX up to 5MB" },
            { type: 'id', path: user.id_card_path, default: "PNG, JPG or PDF" },
            { type: 'other', path: user.other_docs, default: "Certificates, etc." }
        ];

        fileSections.forEach(section => {
            const wrapper = document.getElementById(`ep_${section.type}_wrapper`);
            const label = document.getElementById(`ep_${section.type}_filename`);
            if (!wrapper || !label) return;

            if (section.path) {
                wrapper.classList.add('has-file');
                label.classList.add('text-success');
                if (section.type === 'other') {
                    const docs = typeof section.path === 'string' ? JSON.parse(section.path) : section.path;
                    label.textContent = (docs.length || 0) + " existing files";
                } else {
                    label.textContent = "File already uploaded";
                }
            } else {
                wrapper.classList.remove('has-file');
                label.classList.remove('text-success');
                label.textContent = section.default;
            }
        });
    }

    // --- 3. Profile Picture Interaction ---
    if (profileAvatarInput) {
        profileAvatarInput.addEventListener('change', async function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Client-side preview
                const reader = new FileReader();
                reader.onload = (e) => profileAvatarImg.src = e.target.result;
                reader.readAsDataURL(file);

                // Auto-upload: must send ALL current data to prevent other fields being blanked out
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('profile_avatar', file);

                // Personal Info
                formData.append('firstName', userData.first_name || '');
                formData.append('middleName', userData.middle_name || '');
                formData.append('lastName', userData.last_name || '');
                formData.append('gender', userData.gender || '');
                formData.append('dob', userData.dob || '');
                formData.append('phone', userData.phone || '');
                formData.append('idCard', userData.cnic_number || '');
                formData.append('address', userData.address || '');
                formData.append('emergencyPhone', userData.emergency_contact || '');
                formData.append('emergencyRelation', userData.emergency_relation || '');

                // Banking Info
                formData.append('bankName', userData.bank_name || '');
                formData.append('accountType', userData.account_type || '');
                formData.append('accountTitle', userData.account_title || '');
                formData.append('accountNumber', userData.account_number || '');
                formData.append('bankBranch', userData.branch_info || '');

                // Education & Experience
                formData.append('qualification', userData.qualification || '');
                formData.append('degreeCert', userData.degree_cert || '');
                formData.append('college', userData.university || '');
                formData.append('expertise', userData.expertise || '');
                formData.append('lastEmployer', userData.last_employer || '');
                formData.append('prevJobTitle', userData.last_job_title || '');
                formData.append('expFrom', userData.exp_from || '');
                formData.append('expTo', userData.exp_to || '');

                try {
                    const response = await fetch('assets/api/profile_handler.php', {
                        method: 'POST',
                        body: formData
                    });
                    const res = await response.json();
                    if (res.status === 'success') {
                        userData.profile_pic = res.profile_pic; // Update local state
                        Swal.fire({
                            icon: 'success',
                            title: 'Photo Updated',
                            text: 'Your profile picture has been updated successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Failed to upload image.', 'error');
                }
            }
        });
    }

    // --- 4. Submit Profile Update ---
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const saveBtn = document.getElementById('editProfileSaveBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span>Saving...</span>';

            const formData = new FormData(editProfileForm);
            formData.append('action', 'update');

            try {
                const response = await fetch('assets/api/profile_handler.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
 
                 if (result.status === 'success') {
                    if (typeof closeModal === 'function') closeModal('editProfileModal');
                    const newPic = result.profile_pic || result.data?.profile_pic || null;
                    if (newPic) updateTopbarAvatar(newPic);
                    loadProfile(); // Refresh data
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Profile updated successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                 } else {
                    Swal.fire('Update Failed', result.message, 'error');
                 }
             } catch (error) {
                Swal.fire('Error', 'An error occurred during update.', 'error');
             } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<span>Save changes</span> <i data-lucide="check" width="16" height="16"></i>';
                lucide.createIcons();
            }
        });
    }

    // --- Modal Bootstrap ---
    if (openEditProfileModalBtn) {
        openEditProfileModalBtn.onclick = () => {
            if (userData) populateModal(userData);
            if (typeof openModal === 'function') openModal('editProfileModal');
        };
    }

    // --- 5. File Input Listeners (Removed in favor of onchange) ---

    // --- 6. Apply Input Masking ---
    ['ep_phone', 'ep_emergencyPhone'].forEach(id => applyMask(id, 'phone'));
    applyMask('ep_idCard', 'cnic');

    // Initial Load
    loadProfile();
});

// File selection feedback (Global for onchange)
function handleFileSelect(input, wrapperId, filenameId) {
    const wrapper = document.getElementById(wrapperId);
    const filenameLabel = document.getElementById(filenameId);
    if (!wrapper || !filenameLabel) return;
    
    if (input.files && input.files.length > 0) {
        wrapper.classList.add('has-file');
        if (input.files.length === 1) {
            filenameLabel.textContent = input.files[0].name;
        } else {
            filenameLabel.textContent = input.files.length + " files selected";
        }
        filenameLabel.classList.add('text-success');
    } else {
        wrapper.classList.remove('has-file');
        filenameLabel.classList.remove('text-success');
        // Reset to default info based on wrapper ID
        if (wrapperId.includes('resume')) filenameLabel.textContent = "PDF, DOCX up to 5MB";
        else if (wrapperId.includes('id')) filenameLabel.textContent = "PNG, JPG or PDF";
        else filenameLabel.textContent = "Certificates, etc.";
    }
}

// Helper for notifications (assuming toast exists in global space)
function showToast(message, type = 'success') {
    if (typeof Toastify !== 'undefined') {
        Toastify({
            text: message,
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: type === 'success' ? "#22c55e" : "#ef4444",
        }).showToast();
    } else {
        alert(message);
    }
}
