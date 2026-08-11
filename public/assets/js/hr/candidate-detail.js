/**
 * Candidate Detail Redesign Logic - Pipeline Edition
 */
(function () {
    function getTodayISO() {
        const d = new Date();
        const pad = (n) => n < 10 ? '0' + n : n;
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    }

    const API_HANDLER = '/assets/api/job_handler.php';
    function getActiveCandidateId() {
        return document.getElementById('currentCandidateId')?.value || new URLSearchParams(window.location.search).get('id');
    }
    let currentInterview = null;
    let currentCandidateStatus = '';
    let hireShifts = [];
    let hireShiftsLoaded = false;

    function formatShiftTime(timeStr) {
        if (!timeStr) return '';
        const parts = String(timeStr).split(':');
        let h = parseInt(parts[0], 10);
        const m = parts[1] || '00';
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return `${h}:${m} ${ampm}`;
    }

    function formatShiftOptionLabel(shift) {
        if (shift.start_time && shift.end_time) {
            return `${shift.name} (${formatShiftTime(shift.start_time)} - ${formatShiftTime(shift.end_time)})`;
        }
        return shift.name || 'Shift';
    }

    async function loadHireShifts() {
        if (hireShiftsLoaded) return hireShifts;
        try {
            const response = await fetch('/assets/api/employee_handler.php?action=fetch_requirements');
            const result = await response.json();
            if (result.status === 'success' && Array.isArray(result.shifts)) {
                hireShifts = result.shifts;
                hireShiftsLoaded = true;
            }
        } catch (e) {
            console.error('Could not load shifts:', e);
        }
        return hireShifts;
    }

    function populateHireShiftSelect() {
        const select = document.getElementById('statusShiftId');
        if (!select) return;
        const selected = select.value;
        select.innerHTML = '<option value="">Select shift</option>' +
            hireShifts.map(shift => `<option value="${shift.id}">${formatShiftOptionLabel(shift)}</option>`).join('');
        if (selected) {
            select.value = selected;
        }
    }

    function setStatusFieldsVisibility(status) {
        const shortlistFields = document.getElementById('statusShortlistFields');
        const hiredFields = document.getElementById('statusHiredFields');
        const interviewDate = document.getElementById('statusInterviewDate');
        const interviewTime = document.getElementById('statusInterviewTime');
        const joiningDate = document.getElementById('statusJoiningDate');
        const shiftSelect = document.getElementById('statusShiftId');

        const isShortlist = status === 'Shortlisted';
        const isHired = status === 'Hired';

        if (shortlistFields) {
            shortlistFields.classList.toggle('hidden', !isShortlist);
            shortlistFields.style.display = isShortlist ? '' : 'none';
        }
        if (hiredFields) {
            hiredFields.classList.toggle('hidden', !isHired);
            hiredFields.style.display = isHired ? '' : 'none';
        }

        if (interviewDate) {
            interviewDate.required = isShortlist;
            if (isShortlist) {
                interviewDate.min = getTodayISO();
            }
        }
        if (interviewTime) {
            interviewTime.required = isShortlist;
        }
        if (joiningDate) {
            joiningDate.required = isHired;
            if (isHired) {
                joiningDate.min = getTodayISO();
                if (!joiningDate.value) joiningDate.value = getTodayISO();
            }
        }
        if (shiftSelect) {
            shiftSelect.required = isHired;
        }
    }

    /** Reject not allowed once hired or in other closed states */
    const REJECT_HIDDEN_STATUSES = ['hired', 'rejected', 'banned', 'duplicated'];

    function normalizeStatusKey(status) {
        return String(status || 'new').toLowerCase().trim().replace(/\s+/g, '-');
    }

    function syncRejectCandidateButton(statusKey) {
        const rejectBtn = document.getElementById('rejectCandidateBtn');
        if (!rejectBtn) return;
        const hide = REJECT_HIDDEN_STATUSES.includes(normalizeStatusKey(statusKey));
        rejectBtn.classList.toggle('hidden', hide);
        rejectBtn.style.display = hide ? 'none' : '';
        rejectBtn.disabled = hide;
        rejectBtn.setAttribute('aria-hidden', hide ? 'true' : 'false');
    }

    function syncBanCandidateButton(statusKey) {
        const banBtn = document.getElementById('banCandidateBtn');
        if (!banBtn) return;
        const isBanned = statusKey === 'banned';
        banBtn.style.display = isBanned ? 'none' : '';
        banBtn.disabled = isBanned;
        banBtn.setAttribute('aria-hidden', isBanned ? 'true' : 'false');
    }

    function openScheduleInterviewModal() {
        var modal = document.getElementById('scheduleInterviewModal');
        var dateEl = document.getElementById('scheduleInterviewDate');
        var timeEl = document.getElementById('scheduleInterviewTime');
        var feedbackEl = document.getElementById('scheduleInterviewFeedback');
        
        if (!modal || !dateEl || !timeEl) return;

        // Dynamic Title & Labels
        var titleEl = modal.querySelector('h3');
        var subtitleEl = modal.querySelector('p');
        var submitBtn = modal.querySelector('button[type="submit"]');

        if (currentInterview) {
            if (titleEl) titleEl.textContent = 'Reschedule Interview';
            if (subtitleEl) subtitleEl.textContent = 'Update date and time for this interview';
            if (submitBtn) submitBtn.textContent = 'Update Schedule';
            
            dateEl.value = currentInterview.date;
            dateEl.min = getTodayISO();
            timeEl.value = currentInterview.time;
            if (feedbackEl) feedbackEl.value = currentInterview.feedback || '';
        } else {
            if (titleEl) titleEl.textContent = 'Schedule Interview';
            if (subtitleEl) subtitleEl.textContent = 'Set date and time for this candidate';
            if (submitBtn) submitBtn.textContent = 'Schedule & Notify';

            dateEl.value = '';
            dateEl.min = getTodayISO();
            timeEl.value = '';
            if (feedbackEl) feedbackEl.value = '';
        }

        if (typeof openModal === 'function') {
            openModal('scheduleInterviewModal');
        } else {
            modal.classList.add('active');
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    async function loadCandidateDetail() {
        const activeCandId = getActiveCandidateId();
        if (!activeCandId) return;

        try {
            const response = await fetch(`${API_HANDLER}?action=fetch_candidate_detail&id=${activeCandId}&_=${Date.now()}`);
            const result = await response.json();
            if (result.status === 'success' && result.data) {
                const cand = result.data;
                currentInterview = cand.current_interview || null;
                
                // --- Header Info ---
                document.getElementById('candName').textContent = cand.name;
                document.getElementById('candJobTitle').textContent = cand.job_title || '—';
                document.getElementById('candEmail').textContent = cand.email;
                document.getElementById('candPhone').textContent = cand.phone || '—';
                
                // Applied Date in Timeline
                const appliedDateStr = new Date(cand.applied_date).toLocaleDateString('en-US', { 
                    month: 'long', 
                    day: 'numeric', 
                    year: 'numeric' 
                });
                const appliedEl = document.getElementById('candAppliedDate');
                if (appliedEl) appliedEl.textContent = appliedDateStr.toUpperCase() + ' — 09:00 AM';

                if (document.getElementById('candJobTitleSpan')) {
                    document.getElementById('candJobTitleSpan').textContent = cand.job_title || 'Designation';
                }

                // Avatar Initials
                const initials = cand.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                document.getElementById('candAvatar').textContent = initials;

                // --- Status Badge (Now Static) ---
                const statusBadge = document.getElementById('candStatus');
                let rawStatus = (cand.status && cand.status.trim()) ? cand.status.trim() : 'New';
                
                statusBadge.textContent = rawStatus.toUpperCase();
                
                // Use standardized lowercase status as class
                const s = normalizeStatusKey(rawStatus);
                statusBadge.className = `cand-v2-status-badge ${s}`;
                currentCandidateStatus = s;
                syncRejectCandidateButton(s);
                syncBanCandidateButton(s);

                // --- Pipeline Action Button ---
                const pipelineBtn = document.getElementById('primaryPipelineBtn');
                const rescheduleBtn = document.getElementById('rescheduleBtn');
                
                if (pipelineBtn) {
                    if (rescheduleBtn) rescheduleBtn.classList.add('hidden'); // Hide by default
                    
                    if (s === 'new' || s === 'applied' || s === 'pending') {
                        pipelineBtn.textContent = 'Approve to Interview';
                        pipelineBtn.disabled = false;
                        pipelineBtn.style.display = 'block';
                        pipelineBtn.style.opacity = '1';
                        pipelineBtn.style.cursor = 'pointer';
                        pipelineBtn.onclick = () => openScheduleInterviewModal();
                        setPipelinePermAction('schedule_interview');
                    } else if (s === 'interview') {
                        pipelineBtn.textContent = 'Move to Shortlisted';
                        pipelineBtn.disabled = false;
                        pipelineBtn.style.display = 'block';
                        pipelineBtn.style.opacity = '1';
                        pipelineBtn.style.cursor = 'pointer';
                        pipelineBtn.onclick = () => openStatusModal('Shortlisted');
                        setPipelinePermAction('update_pipeline');

                        if (rescheduleBtn) {
                            rescheduleBtn.classList.remove('hidden');
                            rescheduleBtn.style.display = 'flex';
                            rescheduleBtn.style.alignItems = 'center';
                            rescheduleBtn.style.justifyContent = 'center';
                            rescheduleBtn.style.gap = '8px';
                            rescheduleBtn.style.opacity = '1';
                            rescheduleBtn.onclick = (e) => {
                                e.preventDefault();
                                openScheduleInterviewModal();
                            };
                        }
                    } else if (s === 'shortlisted') {
                        pipelineBtn.textContent = 'Move to Offer';
                        pipelineBtn.disabled = false;
                        pipelineBtn.style.display = 'block';
                        pipelineBtn.style.opacity = '1';
                        pipelineBtn.style.cursor = 'pointer';
                        pipelineBtn.onclick = () => openStatusModal('Offer');
                        setPipelinePermAction('update_pipeline');

                        if (rescheduleBtn) {
                            rescheduleBtn.classList.remove('hidden');
                            rescheduleBtn.style.display = 'flex';
                            rescheduleBtn.style.alignItems = 'center';
                            rescheduleBtn.style.justifyContent = 'center';
                            rescheduleBtn.style.gap = '8px';
                            rescheduleBtn.style.opacity = '1';
                            rescheduleBtn.onclick = (e) => {
                                e.preventDefault();
                                openScheduleInterviewModal();
                            };
                        }
                    } else if (s === 'offer') {
                        pipelineBtn.textContent = 'Confirm Hiring';
                        pipelineBtn.disabled = false;
                        pipelineBtn.style.display = 'block';
                        pipelineBtn.style.opacity = '1';
                        pipelineBtn.style.cursor = 'pointer';
                        pipelineBtn.onclick = () => openStatusModal('Hired');
                        setPipelinePermAction('update_pipeline');
                    } else if (s === 'hired') {
                        pipelineBtn.textContent = 'Hired & Active';
                        pipelineBtn.disabled = true;
                        pipelineBtn.style.opacity = '0.6';
                        pipelineBtn.style.cursor = 'not-allowed';
                        setPipelinePermAction(null);
                        syncRejectCandidateButton('hired');
                    } else if (s === 'rejected' || s === 'banned' || s === 'duplicated') {
                        pipelineBtn.textContent = s.toUpperCase();
                        pipelineBtn.disabled = true;
                        pipelineBtn.style.opacity = '0.6';
                        pipelineBtn.style.cursor = 'not-allowed';
                        setPipelinePermAction(null);
                    } else {
                        pipelineBtn.style.display = 'none';
                        setPipelinePermAction(null);
                    }
                }

                if (window.HR_PERMS && typeof HR_PERMS.refresh === 'function') {
                    HR_PERMS.refresh();
                }

                // --- Application Details Grid ---
                const detailsGrid = document.getElementById('candDetailsGrid');
                let detailsHtml = '';

                detailsHtml += `
                    <div class="cand-v2-detail-item">
                        <div class="cand-v2-detail-label">CNIC NUMBER</div>
                        <div class="cand-v2-detail-value">${cand.cnic_number || '—'}</div>
                    </div>
                    <div class="cand-v2-detail-item">
                        <div class="cand-v2-detail-label">RESIDENCY / LOCATION</div>
                        <div class="cand-v2-detail-value">${cand.address || cand.location || '—'}</div>
                    </div>
                `;

                if (cand.answers && cand.answers.length > 0) {
                    cand.answers.forEach(ans => {
                        detailsHtml += `
                            <div class="cand-v2-detail-item">
                                <div class="cand-v2-detail-label">${ans.question_text.toUpperCase()}</div>
                                <div class="cand-v2-detail-value">${ans.answer || '—'}</div>
                            </div>
                        `;
                    });
                }
                detailsGrid.innerHTML = detailsHtml;

                // --- Journey History (Timeline) ---
                const journeyTimeline = document.getElementById('candJourney');
                if (journeyTimeline) {
                    let timelineHtml = '';
                    
                    // Format Application Date and Time dynamically from created_at
                    const submissionDate = new Date(cand.created_at || cand.applied_date);
                    const appliedDateStr = submissionDate.toLocaleDateString('en-US', { 
                        month: 'short', day: 'numeric', year: 'numeric'
                    });
                    const appliedTimeStr = submissionDate.toLocaleTimeString('en-US', { 
                        hour: '2-digit', minute: '2-digit', hour12: true 
                    });

                    // 1. Add History Items from Database
                    if (result.data.history && result.data.history.length > 0) {
                        result.data.history.forEach(item => {
                            const dateObj = new Date(item.created_at);
                            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                            
                            const eventDate = monthNames[dateObj.getMonth()] + ' ' + dateObj.getDate() + '-' + dateObj.getFullYear();
                            const eventTime = dateObj.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });

                            const userName = (item.first_name || item.last_name) 
                                ? `${item.first_name} ${item.last_name}`.trim() 
                                : 'System';

                            timelineHtml += `
                                <div class="cand-v2-timeline-item">
                                    <div class="cand-v2-timeline-dot"></div>
                                    <div class="font-11 text-light uppercase font-700 ls-05 mb-4">${eventDate.toUpperCase()}, ${eventTime}</div>
                                    <div class="font-14 font-700 text-dark">
                                        Candidate moved to <span style="color: var(--primary-dark);">${item.status_to}</span>
                                    </div>
                                    <div class="font-11 text-primary-color font-700 mt-4">Changed by: ${userName}</div>
                                    
                                    ${item.feedback ? `
                                        <div class="mt-16 p-16" style="display: flex; gap: 12px; align-items: flex-start;">
                                            <i data-lucide="message-square" size="14" class="text-light mt-2"></i>
                                            <div>
                                                <div class="font-10 text-light uppercase font-700 ls-05 mb-4">Recruiter Feedback</div>
                                                <div class="font-12 text-dark" style="white-space: pre-line; font-weight: 500;">${item.feedback}</div>
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            `;
                        });
                    }

                    // 2. Always show the initial "Application Submitted" event at the end
                    timelineHtml += `
                        <div class="cand-v2-timeline-item">
                            <div class="cand-v2-timeline-dot"></div>
                            <div class="font-10 text-light uppercase font-700 ls-05 mb-4">${appliedDateStr.toUpperCase()}, ${appliedTimeStr}</div>
                            <div class="font-14 font-700 text-dark">Application submitted for <span style="color: var(--primary-dark); font-weight: 800;">${cand.job_title}</span></div>
                            <div class="font-11 text-success font-700 mt-4">System Status: New</div>
                            <div class="mt-16 p-12" >
                                <div class="font-11 text-dark italic">Potential Duplicate Check and Document Verification complete. Candidate profile initialised.</div>
                            </div>
                        </div>
                    `;

                    journeyTimeline.innerHTML = timelineHtml;
                }

                // --- Duplicate Warning ---
                const dupWarning = document.getElementById('duplicateWarning');
                if ((cand.duplicate_of || (cand.status && cand.status.toLowerCase() === 'duplicated')) && dupWarning) {
                    dupWarning.classList.remove('hidden');
                    const reasonText = cand.duplicate_reason ? `${cand.duplicate_reason}.` : 'This application was flagged as duplicate because matching information (Email, Phone, CNIC, or Address) exists in the database.';
                    const textEl = document.getElementById('duplicateText');
                    if (textEl) textEl.textContent = reasonText;
                    
                    const dupLink = document.getElementById('duplicateOriginalLink');
                    if (dupLink) {
                        if (cand.duplicate_of) {
                            const detailUrl = (window.HRM && typeof window.HRM.url === 'function') 
                                ? window.HRM.url('recruitment/detail') + '?id=' + cand.duplicate_of 
                                : 'recruitment/detail?id=' + cand.duplicate_of;
                            dupLink.href = detailUrl;
                            dupLink.textContent = `View Original Profile (${cand.duplicate_of_name || 'Candidate #' + cand.duplicate_of}) \u2192`;
                            dupLink.style.display = 'inline-block';
                        } else {
                            dupLink.style.display = 'none';
                        }
                    }
                } else if (dupWarning) {
                    dupWarning.classList.add('hidden');
                }

                // --- Resume ---
                const resumeCard = document.getElementById('resumeCard');
                const resumeFileName = document.getElementById('resumeFileName');
                const noDocText = document.getElementById('noDocText');
                
                if (cand.resume_path) {
                    const fileName = cand.resume_path.split('/').pop();
                    resumeFileName.textContent = fileName;
                    resumeCard.href = '../' + cand.resume_path;
                    resumeCard.classList.remove('hidden');
                    if (noDocText) noDocText.classList.add('hidden');
                } else {
                    resumeCard.classList.add('hidden');
                    if (noDocText) noDocText.classList.remove('hidden');
                }

                if (typeof lucide !== 'undefined') lucide.createIcons();
            } else {
                console.error('Error fetching candidate:', result.message);
                alert('Candidate not found.');
            }
        } catch (e) {
            console.error('Error loading candidate detail:', e);
        }
    }

    function setButtonLoading(btn, loading, loadingLabel) {
        if (!btn) return;
        if (loading) {
            if (!btn.dataset.originalHtml) {
                btn.dataset.originalHtml = btn.innerHTML;
            }
            btn.disabled = true;
            btn.innerHTML = loadingLabel || 'Saving...';
        } else {
            btn.disabled = false;
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function notifySuccess(message) {
        if (typeof showToast === 'function') {
            showToast(message, 'success');
            return;
        }
        Swal.fire({ icon: 'success', title: 'Success', text: message, timer: 2200, showConfirmButton: false });
    }

    function notifyError(message) {
        Swal.fire('Error', message || 'Something went wrong.', 'error');
    }

    function candidatePermTypeForStatus(status) {
        const key = normalizeStatusKey(status);
        return ['rejected', 'banned'].includes(key) ? 'reject_ban' : 'update_pipeline';
    }

    function setPipelinePermAction(actionType) {
        const pipelineBtn = document.getElementById('primaryPipelineBtn');
        if (!pipelineBtn) return;
        if (actionType) {
            pipelineBtn.setAttribute('data-hr-perm-action', actionType);
        } else {
            pipelineBtn.removeAttribute('data-hr-perm-action');
        }
        if (window.HR_PERMS && typeof HR_PERMS.refresh === 'function') {
            HR_PERMS.refresh();
        }
    }

    function openStatusModal(status) {
        document.getElementById('targetStatus').value = status;
        const titleEl = document.getElementById('statusModalTitle');
        const subtitleEl = document.getElementById('statusModalSubtitle');
        const submitBtn = document.getElementById('statusModalSubmitBtn');
        const feedbackEl = document.getElementById('statusFeedback');

        setStatusFieldsVisibility(status);

        const emailGroup = document.getElementById('statusSendEmailGroup');
        const emailCheckbox = document.getElementById('statusSendEmail');
        if (status === 'Offer') {
            if (emailGroup) {
                emailGroup.classList.add('hidden');
                emailGroup.style.setProperty('display', 'none', 'important');
            }
            if (emailCheckbox) emailCheckbox.checked = false;
        } else {
            if (emailGroup) {
                emailGroup.classList.remove('hidden');
                emailGroup.style.removeProperty('display');
            }
            if (emailCheckbox) emailCheckbox.checked = true;
        }

        if (status === 'Rejected') {
            if (titleEl) titleEl.textContent = 'Reject Candidate';
            if (subtitleEl) subtitleEl.textContent = 'Please provide a reason for rejecting this application.';
            if (submitBtn) submitBtn.textContent = 'Confirm Rejection';
            if (feedbackEl) {
                feedbackEl.placeholder = 'Reason for rejection (required)...';
            }
        } else if (status === 'Shortlisted') {
            const interviewDate = document.getElementById('statusInterviewDate');
            const interviewTime = document.getElementById('statusInterviewTime');
            if (interviewDate) interviewDate.value = '';
            if (interviewTime) interviewTime.value = '';
            if (titleEl) titleEl.textContent = 'Move to Shortlisted';
            if (subtitleEl) subtitleEl.textContent = 'Set the second-round interview date and time — these details will be emailed to the candidate.';
            if (submitBtn) submitBtn.textContent = 'Confirm & Send Invite';
            if (feedbackEl) {
                feedbackEl.placeholder = 'Shortlist evaluation ya interview notes...';
            }
        } else if (status === 'Hired') {
            loadHireShifts().then(() => populateHireShiftSelect());
            if (titleEl) titleEl.textContent = 'Confirm Hiring';
            if (subtitleEl) subtitleEl.textContent = 'Select joining date and shift — reporting time is taken from the shift start time.';
            if (submitBtn) submitBtn.textContent = 'Confirm Hiring & Notify';
            if (feedbackEl) {
                feedbackEl.placeholder = 'Offer confirmation ya onboarding notes...';
            }
        } else {
            if (titleEl) titleEl.textContent = `Move to ${status}`;
            if (subtitleEl) subtitleEl.textContent = `Please provide feedback for moving candidate to ${status} stage.`;
            if (submitBtn) submitBtn.textContent = 'Confirm & Update';
            if (feedbackEl) {
                feedbackEl.placeholder = 'Please provide a detailed evaluation or reason for this status change...';
            }
        }

        document.getElementById('statusFeedback').value = '';

        if (typeof openModal === 'function') {
            openModal('statusTransitionModal');
        } else {
            const modal = document.getElementById('statusTransitionModal');
            if (modal) modal.classList.add('active');
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    async function updateStatus(newStatus, feedback = '', scheduleData = {}) {
        const permType = candidatePermTypeForStatus(newStatus);

        try {
            const formData = new FormData();
            formData.append('action', 'update_candidate_status');
            formData.append('id', getActiveCandidateId());
            formData.append('status', newStatus);
            formData.append('feedback', feedback);
            formData.append('send_email', scheduleData.send_email !== undefined ? scheduleData.send_email : 1);

            if (newStatus === 'Shortlisted') {
                formData.append('date', scheduleData.date || '');
                formData.append('time', scheduleData.time || '');
                formData.append('interview_type', scheduleData.interview_type || 'Onsite');
                formData.append('location', scheduleData.location || '');
            } else if (newStatus === 'Hired') {
                formData.append('date', scheduleData.date || '');
                formData.append('shift_id', scheduleData.shift_id || '');
            }

            const response = await fetch(API_HANDLER, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.status === 'success') {
                await loadCandidateDetail();
                notifySuccess('Candidate status updated to ' + newStatus);
                return true;
            }
            notifyError(result.message);
            return false;
        } catch (e) {
            console.error('Status update failed:', e);
            notifyError('Could not update status. Please try again.');
            return false;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadCandidateDetail();
        
        const interviewForm = document.getElementById('scheduleInterviewForm');
        const transitionForm = document.getElementById('statusTransitionForm');
        const banBtn = document.getElementById('banCandidateBtn');
        const rejectBtn = document.getElementById('rejectCandidateBtn');

        // Dynamic interview type toggles
        const schedTypeEl = document.getElementById('scheduleInterviewType');
        const schedLocGroup = document.getElementById('scheduleInterviewLocationGroup');
        const schedLocInput = document.getElementById('scheduleInterviewLocation');
        if (schedTypeEl && schedLocGroup) {
            schedTypeEl.onchange = function() {
                if (this.value === 'Online') {
                    schedLocGroup.classList.remove('hidden');
                    if (schedLocInput) schedLocInput.required = true;
                } else {
                    schedLocGroup.classList.add('hidden');
                    if (schedLocInput) {
                        schedLocInput.required = false;
                        schedLocInput.value = '';
                    }
                }
            };
        }

        const statTypeEl = document.getElementById('statusInterviewType');
        const statLocGroup = document.getElementById('statusInterviewLocationGroup');
        const statLocInput = document.getElementById('statusInterviewLocation');
        if (statTypeEl && statLocGroup) {
            statTypeEl.onchange = function() {
                if (this.value === 'Online') {
                    statLocGroup.classList.remove('hidden');
                    if (statLocInput) statLocInput.required = true;
                } else {
                    statLocGroup.classList.add('hidden');
                    if (statLocInput) {
                        statLocInput.required = false;
                        statLocInput.value = '';
                    }
                }
            };
        }

        if (transitionForm) {
            transitionForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const status = document.getElementById('targetStatus').value;
                const feedback = document.getElementById('statusFeedback').value;
                const submitBtn = document.getElementById('statusModalSubmitBtn');
                const sendEmail = document.getElementById('statusSendEmail')?.checked ? 1 : 0;
                const scheduleData = { send_email: sendEmail };

                if (status === 'Shortlisted') {
                    const date = document.getElementById('statusInterviewDate')?.value || '';
                    const time = document.getElementById('statusInterviewTime')?.value || '';
                    const interviewType = document.getElementById('statusInterviewType')?.value || 'Onsite';
                    const location = document.getElementById('statusInterviewLocation')?.value || '';

                    if (!date || !time) {
                        notifyError('Second interview date and time are required.');
                        return;
                    }
                    scheduleData.date = date;
                    scheduleData.time = time;
                    scheduleData.interview_type = interviewType;
                    scheduleData.location = location;
                } else if (status === 'Hired') {
                    const date = document.getElementById('statusJoiningDate')?.value || '';
                    const shiftId = document.getElementById('statusShiftId')?.value || '';
                    if (!date || !shiftId) {
                        notifyError('Joining date and shift selection are required.');
                        return;
                    }
                    scheduleData.date = date;
                    scheduleData.shift_id = shiftId;
                }

                closeModal('statusTransitionModal');
                setButtonLoading(submitBtn, true, 'Updating...');
                await updateStatus(status, feedback, scheduleData);
                setButtonLoading(submitBtn, false);
            });
        }

        if (rejectBtn) {
            rejectBtn.addEventListener('click', () => {
                if (REJECT_HIDDEN_STATUSES.includes(normalizeStatusKey(currentCandidateStatus))) {
                    return;
                }
                openStatusModal('Rejected');
            });
        }

        if (banBtn) {
            banBtn.addEventListener('click', () => {
                if (currentCandidateStatus === 'banned') {
                    return;
                }
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to ban this candidate?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, Ban'
                }).then((result) => {
                    if (result.isConfirmed) {
                        updateStatus('Banned');
                    }
                });
            });
        }

        // Interview Form Submission (Moves status to 'Interview')
        if (interviewForm) {
            interviewForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                var date = document.getElementById('scheduleInterviewDate').value;
                var time = document.getElementById('scheduleInterviewTime').value;
                var interviewType = document.getElementById('scheduleInterviewType')?.value || 'Onsite';
                var location = document.getElementById('scheduleInterviewLocation')?.value || '';
                var feedback = document.getElementById('scheduleInterviewFeedback').value;
                var sendEmail = document.getElementById('scheduleInterviewSendEmail')?.checked ? 1 : 0;
                const submitBtn = document.querySelector('button[form="scheduleInterviewForm"]');
                const wasReschedule = !!currentInterview;

                closeModal('scheduleInterviewModal');
                setButtonLoading(submitBtn, true, wasReschedule ? 'Rescheduling...' : 'Scheduling...');

                try {
                    const formData = new FormData();
                    if (wasReschedule) {
                        formData.append('action', 'reschedule_interview');
                        formData.append('interview_id', currentInterview.id);
                    } else {
                        formData.append('action', 'schedule_interview');
                    }

                    formData.append('candidate_id', getActiveCandidateId());
                    formData.append('date', date);
                    formData.append('time', time);
                    formData.append('interview_type', interviewType);
                    formData.append('location', location);
                    formData.append('feedback', feedback);
                    formData.append('send_email', sendEmail);

                    const response = await fetch(API_HANDLER, {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        await loadCandidateDetail();
                        notifySuccess(result.message || (wasReschedule ? 'Interview rescheduled.' : 'Interview scheduled.'));
                    } else {
                        notifyError(result.message);
                    }
                } catch (err) {
                    console.error('Operation failed:', err);
                    notifyError('Could not save interview. Please try again.');
                } finally {
                    setButtonLoading(submitBtn, false);
                }
            });
        }
        // Expose globally for WebSocket updates
        window.refreshCandidateDetail = loadCandidateDetail;
    });

    // Expose globally for WebSocket updates
    window.refreshCandidateDetail = loadCandidateDetail;
})();
