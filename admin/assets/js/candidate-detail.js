/**
 * Candidate Detail Redesign Logic - Pipeline Edition
 */
(function () {
    function getTodayISO() {
        const d = new Date();
        const pad = (n) => n < 10 ? '0' + n : n;
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    }

    const API_HANDLER = 'assets/api/job_handler.php';
    const params = new URLSearchParams(window.location.search);
    const candidateId = params.get('id');
    let currentInterview = null;
    let currentCandidateStatus = '';

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

            dateEl.value = getTodayISO();
            dateEl.min = getTodayISO();
            timeEl.value = '10:00';
            if (feedbackEl) feedbackEl.value = '';
        }

        if (typeof openModal === 'function') {
            openModal('scheduleInterviewModal');
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    async function loadCandidateDetail() {
        if (!candidateId) return;

        try {
            const response = await fetch(`${API_HANDLER}?action=fetch_candidate_detail&id=${candidateId}&_=${Date.now()}`);
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
                    } else if (s === 'interview') {
                        pipelineBtn.textContent = 'Move to Shortlisted';
                        pipelineBtn.disabled = false;
                        pipelineBtn.style.display = 'block';
                        pipelineBtn.style.opacity = '1';
                        pipelineBtn.style.cursor = 'pointer';
                        pipelineBtn.onclick = () => openStatusModal('Shortlisted');

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
                    } else if (s === 'offer') {
                        pipelineBtn.textContent = 'Confirm Hiring';
                        pipelineBtn.disabled = false;
                        pipelineBtn.style.display = 'block';
                        pipelineBtn.style.opacity = '1';
                        pipelineBtn.style.cursor = 'pointer';
                        pipelineBtn.onclick = () => openStatusModal('Hired');
                    } else if (s === 'hired') {
                        pipelineBtn.textContent = 'Hired & Active';
                        pipelineBtn.disabled = true;
                        pipelineBtn.style.opacity = '0.6';
                        pipelineBtn.style.cursor = 'not-allowed';
                        syncRejectCandidateButton('hired');
                    } else if (s === 'rejected' || s === 'banned' || s === 'duplicated') {
                        pipelineBtn.textContent = s.toUpperCase();
                        pipelineBtn.disabled = true;
                        pipelineBtn.style.opacity = '0.6';
                        pipelineBtn.style.cursor = 'not-allowed';
                    } else {
                        pipelineBtn.style.display = 'none';
                    }
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
                if (cand.duplicate_of && dupWarning) {
                    dupWarning.classList.remove('hidden');
                    document.getElementById('duplicateText').textContent = `Matches existing candidate: ${cand.duplicate_reason || 'Information match'}.`;
                    const dupLink = document.getElementById('duplicateOriginalLink');
                    if (dupLink) {
                        dupLink.href = `candidate-detail.php?id=${cand.duplicate_of}`;
                        dupLink.textContent = `View ${cand.duplicate_of_name || 'Original'}`;
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

    function openStatusModal(status) {
        document.getElementById('targetStatus').value = status;
        const titleEl = document.getElementById('statusModalTitle');
        const subtitleEl = document.getElementById('statusModalSubtitle');
        const submitBtn = document.getElementById('statusModalSubmitBtn');
        const feedbackEl = document.getElementById('statusFeedback');

        if (status === 'Rejected') {
            if (titleEl) titleEl.textContent = 'Reject Candidate';
            if (subtitleEl) subtitleEl.textContent = 'Please provide a reason for rejecting this application.';
            if (submitBtn) submitBtn.textContent = 'Confirm Rejection';
            if (feedbackEl) {
                feedbackEl.placeholder = 'Reason for rejection (required)...';
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

        const modal = document.getElementById('statusTransitionModal');
        if (modal) modal.classList.add('active');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    async function updateStatus(newStatus, feedback = '') {
        try {
            const formData = new FormData();
            formData.append('action', 'update_candidate_status');
            formData.append('id', candidateId);
            formData.append('status', newStatus);
            formData.append('feedback', feedback);

            const response = await fetch(API_HANDLER, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.status === 'success') {
                loadCandidateDetail();
                Swal.fire('Success', 'Candidate status updated to ' + newStatus, 'success');
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        } catch (e) {
            console.error('Status update failed:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadCandidateDetail();
        
        const interviewForm = document.getElementById('scheduleInterviewForm');
        const transitionForm = document.getElementById('statusTransitionForm');
        const banBtn = document.getElementById('banCandidateBtn');
        const rejectBtn = document.getElementById('rejectCandidateBtn');

        if (transitionForm) {
            transitionForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const status = document.getElementById('targetStatus').value;
                const feedback = document.getElementById('statusFeedback').value;
                
                closeModal('statusTransitionModal');
                await updateStatus(status, feedback);
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
                var feedback = document.getElementById('scheduleInterviewFeedback').value;
                
                try {
                    const formData = new FormData();
                    // Decision logic: if already has an interview, it's a reschedule
                    if (currentInterview) {
                        formData.append('action', 'reschedule_interview');
                        formData.append('interview_id', currentInterview.id);
                    } else {
                        formData.append('action', 'schedule_interview');
                    }
                    
                    formData.append('candidate_id', candidateId);
                    formData.append('date', date);
                    formData.append('time', time);
                    formData.append('feedback', feedback);

                    const response = await fetch(API_HANDLER, {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        closeModal('scheduleInterviewModal');
                        loadCandidateDetail(); 
                        Swal.fire(currentInterview ? 'Rescheduled' : 'Scheduled', result.message, 'success');
                    } else {
                        Swal.fire('Error', result.message, 'error');
                    }
                } catch (err) {
                    console.error('Operation failed:', err);
                }
            });
        }
    });
})();
