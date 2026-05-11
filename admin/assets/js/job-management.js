// Job Management Logic
document.addEventListener('DOMContentLoaded', function() {
    const isPublic = !window.location.pathname.includes('/admin/');
    const API_HANDLER = isPublic ? 'admin/assets/api/job_handler.php' : 'assets/api/job_handler.php';
    let jobs = [];

    // --- Loading Jobs from DB ---
    async function loadJobs() {
        try {
            const response = await fetch(`${API_HANDLER}?action=fetch_jobs`);
            const result = await response.json();
            if (result.status === 'success') {
                jobs = result.data;
                if (typeof renderJobs === 'function') renderJobs();
            }
        } catch (e) {
            console.error('Error loading jobs:', e);
        }
    }

    // --- Candidate Management Logic ---
    let candidates = [];
 
    async function loadCandidates() {
        try {
            const response = await fetch(`${API_HANDLER}?action=fetch_candidates&_=${Date.now()}`);
            const result = await response.json();
            if (result.status === 'success') {
                candidates = result.data.map(c => ({
                    id: c.id,
                    name: c.name,
                    email: c.email,
                    jobTitle: c.job_title || 'Unknown',
                    appliedDate: new Date(c.applied_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
                    status: c.status || 'New',
                    matchScore: 0
                }));
                if (typeof applyFilters === 'function') applyFilters();
            }
        } catch (e) {
            console.error('Error loading candidates:', e);
        }
    }



    // --- Create Job Form Logic ---
    const createJobForm = document.getElementById('createJobForm');
    if (createJobForm) {
        const questionList = document.getElementById('questionList');
        const addQuestionBtn = document.getElementById('addQuestionBtn');
        
        if (addQuestionBtn) {
            window.addQuestion = function(text = '', type = 'Text Answer', required = true) {
                const questionItem = document.createElement('div');
                questionItem.className = 'form-builder-card animate-fade-in';
                questionItem.innerHTML = `
                    <div class="drag-handle"><i data-lucide="grip-vertical" size="18"></i></div>
                    <div class="question-card-content">
                        <div class="form-grid-2 mb-12">
                            <div class="form-group">
                                <input type="text" class="form-control bg-white-input question-input font-14 font-600" value="${text}" placeholder="e.g. What is your notice period?">
                            </div>
                            <div class="form-group">
                                <select class="form-control bg-white-input question-type font-14">
                                    <option ${type === 'Text Answer' ? 'selected' : ''}>Text Answer</option>
                                    <option ${type === 'Number' ? 'selected' : ''}>Number</option>
                                    <option ${type === 'Dropdown' ? 'selected' : ''}>Dropdown</option>
                                    <option ${type === 'File Upload' ? 'selected' : ''}>File Upload</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex-between mt-10">
                            <label class="flex-center gap-8 cursor-pointer">
                                <input type="checkbox" class="question-required" ${required ? 'checked' : ''}>
                                <span class="font-13 text-dark font-500">Required Field</span>
                            </label>
                            <span class="remove-link" onclick="this.closest('.form-builder-card').remove()">
                                <i data-lucide="trash-2" size="14"></i> Remove
                            </span>
                        </div>
                    </div>
                `;
                questionList.appendChild(questionItem);
                lucide.createIcons();
            };

            addQuestionBtn.addEventListener('click', () => addQuestion());
            
            window.quickAddQuestion = function(text) {
                addQuestion(text);
            };

        }

        createJobForm.onsubmit = async function(e) {
            e.preventDefault();
            const questionCards = document.querySelectorAll('.form-builder-card:not(.locked)');
            const questions = Array.from(questionCards).map(card => ({
                text: card.querySelector('.question-input').value,
                type: card.querySelector('.question-type').value,
                required: card.querySelector('.question-required').checked
            })).filter(q => q.text.trim() !== '');

            const formData = new FormData();
            formData.append('action', 'save_job');
            
            const idEl = document.getElementById('jobId');
            if (idEl) formData.append('id', idEl.value);

            formData.append('title', document.getElementById('jobTitle').value);
            formData.append('department_id', document.getElementById('jobDept').value);
            formData.append('location', document.getElementById('jobLocation').value);
            formData.append('description', document.getElementById('jobDesc').value);
            formData.append('type', 'Full-time');
            formData.append('status', 'Active');
            formData.append('questions', JSON.stringify(questions));

            try {
                const response = await fetch(API_HANDLER, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    Swal.fire('Success', 'Job posted successfully!', 'success').then(() => {
                        window.location.href = 'job-list.php';
                    });
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (err) {
                console.error('Error saving job:', err);
                Swal.fire('Error', 'Connection failed.', 'error');
            }
        };
    }

    // --- Job List Logic ---
    const jobTableBody = document.getElementById('jobTableBody');
    const jobPerPage = document.getElementById('perPageSelect');
    const jobTableSummary = document.getElementById('tableSummary');
    const jobPaginationInfo = document.getElementById('paginationInfo');
    const jobPageNumbers = document.getElementById('pageNumbers');
    const jobPrevBtn = document.getElementById('prevPage');
    const jobNextBtn = document.getElementById('nextPage');

    let jobPage = 1;
    let jobLimit = jobPerPage ? (jobPerPage.value === 'all' ? -1 : parseInt(jobPerPage.value)) : 10;

    if (jobTableBody) {
        function renderJobs() {
            const query = (document.getElementById('jobSearch')?.value || '').toLowerCase();
            const dept = document.getElementById('filterDept')?.value || '';
            const status = document.getElementById('filterStatus')?.value || '';
            const sortBy = document.getElementById('sortBy')?.value || '';

            let filteredJobs = jobs.filter(job => {
                const searchStr = (job.title + ' ' + (job.department_name || '') + ' ' + job.location).toLowerCase();
                const matchesSearch = searchStr.includes(query);
                const matchesDept = dept === '' || (job.department_name && job.department_name.includes(dept));
                const matchesStatus = status === '' || job.status === status;
                return matchesSearch && matchesDept && matchesStatus;
            });

            if (sortBy === 'newest') {
                filteredJobs.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            } else if (sortBy === 'oldest') {
                filteredJobs.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            }

            const totalRows = filteredJobs.length;
            if (totalRows === 0) {
                jobTableBody.innerHTML = `<tr><td colspan="7" class="text-center p-40 text-light italic">No jobs found matching your criteria.</td></tr>`;
                updateJobPagination(1);
                return;
            }

            const totalPages = jobLimit === -1 ? 1 : Math.ceil(totalRows / jobLimit);
            if (jobPage > totalPages) jobPage = totalPages;
            
            const start = (jobPage - 1) * jobLimit;
            const end = jobLimit === -1 ? totalRows : start + jobLimit;
            const currentJobs = jobLimit === -1 ? filteredJobs : filteredJobs.slice(start, end);

            jobTableBody.innerHTML = currentJobs.map(job => {
                const posted = job.posted_date ? new Date(job.posted_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';
                return `
                <tr>
                    <td>
                        <div class="text-dark font-14 font-600">${job.title}</div>
                        <div class="font-11 text-light mt-1">${job.type}</div>
                    </td>
                    <td class="font-14">${job.department_name || '—'}</td>
                    <td class="font-14 allow-wrap">${job.location}</td>
                    <td class="font-14">${posted}</td>
                    <td class="font-14">${job.applicant_count} Applicants</td>
                    <td><span class="badge ${job.status === 'Active' ? 'badge-success' : job.status === 'Draft' ? 'badge-info' : 'badge-warning'}">${job.status}</span></td>
                    <td class="text-right px-30">
                        <div class="flex-center gap-8 justify-end">
                            <button class="action-btn" title="View Details" onclick="viewJobDetails('${job.id}')"><i data-lucide="eye" size="14"></i></button>
                            <a href="edit-job.php?id=${job.id}" class="action-btn" title="Edit Job"><i data-lucide="edit-2" size="14"></i></a>
                            <button class="action-btn" title="Copy Apply Link" onclick="copyJobLink('${job.id}')"><i data-lucide="link" size="14"></i></button>
                        </div>
                    </td>
                </tr>
            `;}).join('');

            // Update Summaries
            const showingStart = totalRows === 0 ? 0 : start + 1;
            const showingEnd = Math.min(end, totalRows);
            const infoText = `Showing ${showingStart} to ${showingEnd} of ${totalRows} entries`;
            if (jobPaginationInfo) jobPaginationInfo.textContent = infoText;
            if (jobTableSummary) jobTableSummary.textContent = infoText;

            updateJobPagination(totalPages);
            lucide.createIcons();
        }

        window.viewJobDetails = async function(jobId) {
            try {
                const response = await fetch(`${API_HANDLER}?action=fetch_job_detail&id=${jobId}`);
                const result = await response.json();
                if (result.status !== 'success') throw new Exception(result.message);

                const job = result.data;
                const posted = job.posted_date ? new Date(job.posted_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—';

                document.getElementById('detailJobTitle').textContent = job.title;
                const statusClass = job.status === 'Active' ? 'job-detail-pill--success' : job.status === 'Draft' ? 'job-detail-pill--info' : 'job-detail-pill--neutral';
                document.getElementById('detailJobAppCount').innerHTML = `
                    <span class="job-detail-pill"><i data-lucide="users" size="14"></i> ${job.applicant_count || 0} Applicants</span>
                    <span class="job-detail-pill ${statusClass}">${job.status}</span>`;
                
                document.getElementById('detailDept').textContent = job.department_name || '—';
                document.getElementById('detailLocation').textContent = job.location;
                document.getElementById('detailType').textContent = job.type || 'Full-time';
                document.getElementById('detailPostedDate').textContent = posted;

                // Description
                const detailDesc = document.getElementById('detailDesc');
                detailDesc.textContent = job.description || 'No description provided for this job posting.';

                // Requirements & Questions
                const detailQuestionsList = document.getElementById('detailQuestionsList');
                detailQuestionsList.innerHTML = '';
                
                const questions = job.questions || [];

                const standardOffset = 5;
                let questionsHtml = '';
                if (questions.length) {
                    questionsHtml = '<div class="job-detail-q-list job-detail-q-list--custom">';
                    questions.forEach((q, index) => {
                        const isReq = parseInt(q.is_required) === 1;
                        const reqClass = isReq ? 'text-danger font-500' : '';
                        const qNum = index + 1 + standardOffset;
                        questionsHtml += `
                        <div class="job-detail-q-item">
                            <div class="job-detail-q-item__q">Q${qNum}. ${q.question_text}</div>
                            <div class="job-detail-q-item__meta">
                                <span><i data-lucide="help-circle" size="16"></i> ${q.answer_type}</span>
                                <span class="${reqClass}"><i data-lucide="${isReq ? 'asterisk' : 'check'}" size="16"></i> ${isReq ? 'Required' : 'Optional'}</span>
                            </div>
                        </div>`;
                    });
                    questionsHtml += '</div>';
                } else {
                    questionsHtml = '<p class="font-13 text-light italic py-20 text-center">No custom assessment questions defined.</p>';
                }
                detailQuestionsList.innerHTML = questionsHtml;

                // Footer Actions
                document.getElementById('detailCopyLinkBtn').onclick = () => copyJobLink(job.id);
                document.getElementById('detailEditBtn').onclick = () => { window.location.href = `edit-job.php?id=${job.id}`; };

                openModal('jobDetailModal');
                if (typeof lucide !== 'undefined') lucide.createIcons();

            } catch (e) {
                console.error('Error fetching job details:', e);
                Swal.fire('Error', 'Could not load job details.', 'error');
            }
        };

        window.applyJobFilters = function() {
            jobPage = 1;
            renderJobs();
        };

        const searchInput = document.getElementById('jobSearch');
        const deptFilter = document.getElementById('filterDept');
        const statusFilter = document.getElementById('filterStatus');
        const sortFilter = document.getElementById('sortBy');

        if (searchInput) searchInput.oninput = applyJobFilters;
        if (deptFilter) deptFilter.onchange = applyJobFilters;
        if (statusFilter) statusFilter.onchange = applyJobFilters;
        if (sortFilter) sortFilter.onchange = applyJobFilters;



        function updateJobPagination(totalPages) {
            if (!jobPageNumbers) return;
            jobPageNumbers.innerHTML = '';
            
            if (totalPages <= 1) {
                jobPrevBtn.parentElement.classList.add('hidden');
            } else {
                jobPrevBtn.parentElement.classList.remove('hidden');
            }

            jobPrevBtn.disabled = jobPage === 1;
            jobNextBtn.disabled = jobPage === totalPages;
            jobPrevBtn.style.opacity = jobPage === 1 ? '0.5' : '1';
            jobNextBtn.style.opacity = jobPage === totalPages ? '0.5' : '1';

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = `action-btn ${i === jobPage ? 'btn-active' : ''}`;
                btn.textContent = i;
                btn.onclick = () => { jobPage = i; renderJobs(); };
                jobPageNumbers.appendChild(btn);
            }
        }

        if (jobPerPage) {
            jobPerPage.onchange = () => {
                jobLimit = jobPerPage.value === 'all' ? -1 : parseInt(jobPerPage.value);
                jobPage = 1;
                renderJobs();
            };
        }

        if (jobPrevBtn) jobPrevBtn.onclick = () => { if (jobPage > 1) { jobPage--; renderJobs(); } };
        if (jobNextBtn) jobNextBtn.onclick = () => { 
            const totalPages = jobLimit === -1 ? 1 : Math.ceil(jobs.length / jobLimit);
            if (jobPage < totalPages) { jobPage++; renderJobs(); } 
        };

        loadJobs();
    }

    window.copyJobLink = function(id) {
        const baseUrl = window.location.origin + window.location.pathname.replace('admin/job-list.php', 'job-apply.php');
        const link = `${baseUrl}?jobId=${id}`;
        navigator.clipboard.writeText(link).then(() => {
            if (typeof showToast === 'function') {
                showToast('Job application link copied to clipboard!');
            }
        });
    };

    // --- Candidate Pool Logic ---
    const candidateTableBody = document.getElementById('candidateTableBody');
    const candPerPage = document.getElementById('perPageSelect');
    const candTableSummary = document.getElementById('tableSummary');
    const candPaginationInfo = document.getElementById('paginationInfo');
    const candPageNumbers = document.getElementById('pageNumbers');
    const candPrevBtn = document.getElementById('prevPage');
    const candNextBtn = document.getElementById('nextPage');

    let candPage = 1;
    let candLimit = candPerPage ? parseInt(candPerPage.value) : 10;

    if (candidateTableBody) {
        function renderCandidates(filteredData = candidates) {
            const totalRows = filteredData.length;
            if (totalRows === 0) {
                candidateTableBody.innerHTML = `<tr><td colspan="5" class="text-center p-40 text-light italic">No candidates found.</td></tr>`;
                return;
            }

            const totalPages = candLimit === -1 ? 1 : Math.ceil(totalRows / candLimit);
            if (candPage > totalPages) candPage = totalPages;

            const start = (candPage - 1) * candLimit;
            const end = candLimit === -1 ? totalRows : start + candLimit;
            const currentCands = candLimit === -1 ? filteredData : filteredData.slice(start, end);

            candidateTableBody.innerHTML = currentCands.map(cand => {
                const initials = cand.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                const avatarColors = ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
                const avatarBg = avatarColors[cand.name.length % avatarColors.length];                return `
                <tr>
                    <td>
                        <div class="flex-center gap-12">
                            <div class="avatar-initial" style="background: ${avatarBg}20; color: ${avatarBg}; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;">
                                ${initials}
                            </div>
                            <div>
                                <div class="font-14 font-700 text-dark">${cand.name}</div>
                                <div class="font-11 text-light mt-1">${cand.email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="font-14">${cand.jobTitle}</td>
                    <td class="font-14">${cand.appliedDate}</td>
                    <td>
                        <span class="badge-select ${(cand.status || '').toLowerCase().replace(' ', '-')}">${cand.status}</span>
                    </td>
                    <td class="text-right px-30">
                        <a href="candidate-detail.php?id=${encodeURIComponent(cand.id)}" class="action-btn no-bg border-0" title="View details">
                            <i data-lucide="chevron-right" size="18"></i>
                        </a>
                    </td>
                </tr>
            `;}).join('');

            // Update Summaries
            const showingStart = totalRows === 0 ? 0 : start + 1;
            const showingEnd = Math.min(end, totalRows);
            const infoText = `Showing ${showingStart} to ${showingEnd} of ${totalRows} entries`;
            if (candPaginationInfo) candPaginationInfo.textContent = infoText;
            if (candTableSummary) candTableSummary.textContent = infoText;

            updateCandPagination(totalPages, filteredData);
            lucide.createIcons();
        }

        function updateCandPagination(totalPages, filteredData) {
            if (!candPageNumbers) return;
            candPageNumbers.innerHTML = '';
            
            if (totalPages <= 1) {
                candPrevBtn.parentElement.classList.add('hidden');
            } else {
                candPrevBtn.parentElement.classList.remove('hidden');
            }

            candPrevBtn.disabled = candPage === 1;
            candNextBtn.disabled = candPage === totalPages;
            candPrevBtn.style.opacity = candPage === 1 ? '0.5' : '1';
            candNextBtn.style.opacity = candPage === totalPages ? '0.5' : '1';

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = `action-btn ${i === candPage ? 'btn-active' : ''}`;
                btn.textContent = i;
                btn.onclick = () => { candPage = i; renderCandidates(filteredData); };
                candPageNumbers.appendChild(btn);
            }
        }

        const searchInput = document.getElementById('candidateSearch');
        const filterStatus = document.getElementById('filterStatus');
        const filterDept = document.getElementById('filterDept');
        const sortBy = document.getElementById('sortBy');

        function applyFilters() {
            const query = (searchInput?.value || '').toLowerCase();
            const status = filterStatus?.value || '';
            const dept = filterDept?.value || '';
            
            let filtered = candidates.filter(cand => {
                const matchesSearch = cand.name.toLowerCase().includes(query) || cand.jobTitle.toLowerCase().includes(query) || (cand.email && cand.email.toLowerCase().includes(query));
                const matchesStatus = (status === '' || status === 'All Status') ? true : cand.status === status;
                const matchesDept = (dept === '' || dept === 'All Departments') ? true : cand.jobTitle.includes(dept); // Simple mapping
                return matchesSearch && matchesStatus && matchesDept;
            });

            // Sorting Logic
            if (sortBy?.value.includes('Newest')) {
                filtered.sort((a, b) => new Date(b.appliedDate) - new Date(a.appliedDate));
            } else if (sortBy?.value.includes('Oldest')) {
                filtered.sort((a, b) => new Date(a.appliedDate) - new Date(b.appliedDate));
            } else if (sortBy?.value.includes('Match Score')) {
                filtered.sort((a, b) => b.matchScore - a.matchScore);
            }

            candPage = 1;
            renderCandidates(filtered);
        }

        if (candPerPage) {
            candPerPage.onchange = () => {
                candLimit = candPerPage.value === 'all' ? -1 : parseInt(candPerPage.value);
                candPage = 1;
                applyFilters();
            };
        }

        if (searchInput) searchInput.oninput = applyFilters;
        if (filterStatus) filterStatus.onchange = applyFilters;
        if (filterDept) filterDept.onchange = applyFilters;
        if (sortBy) sortBy.onchange = applyFilters;

        window.updateCandStatus = function(id, status) {
            candidates = candidates.map(c => c.id === id ? {...c, status} : c);
            saveCandidates();
            applyFilters();
        };

        window.openInterviewModal = function(id) {
            document.getElementById('interviewCandId').value = id;
            openModal('interviewModal');
        };

        const interviewForm = document.getElementById('interviewForm');
        if (interviewForm) {
            interviewForm.onsubmit = function(e) {
                e.preventDefault();
                const id = document.getElementById('interviewCandId').value;
                const date = document.getElementById('interviewDate').value;
                const time = document.getElementById('interviewTime').value;
                window.updateCandStatus(id, 'Interview');
                closeModal('interviewModal');
                alert(`Interview scheduled for ${date} at ${time}. Notification sent.`);
            };
        }
        loadCandidates();
    }

    // --- Job Application Logic (Public) ---
    const applyForm = document.getElementById('jobApplyForm');
    if (applyForm) {
        function jobQuestionText(q) {
            if (typeof q === 'string') return q;
            if (q && typeof q.text === 'string') return q.text;
            return '';
        }

        function normalizeApplyQuestion(q) {
            // Standardize DB-style questions for the frontend
            let type = q.answer_type || 'Text Answer';
            if (type === 'TEXT INPUT') type = 'Text Answer';
            if (type === 'SELECT') type = 'Dropdown';
            if (type === 'NUMBER') type = 'Number';
            if (type === 'FILE') type = 'File Upload';

            return {
                text: q.question_text || q.text || '',
                type: type,
                required: parseInt(q.is_required) === 1 || q.required === true
            };
        }

        function escapeAttr(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;');
        }

        function setText(id, text) {
            var el = document.getElementById(id);
            if (el) el.textContent = text;
        }

        function populatePublicJobPosting(job, jobId) {
            var hasJob = !!(job && jobId);
            /* No jobId: keep static HTML in job-apply.php (sample posting). */
            if (!hasJob) {
                return;
            }
            var title = job.title || 'Open role';
            var dept = job.department_name || job.department || '—';
            var loc = job.location || '—';
            var type = job.type || 'Full-time';
            var desc;
            if (String(job.description || '').trim()) {
                desc = String(job.description).trim();
            } else {
                desc =
                    'No extended description was added for this posting. Refer to the job title and department, or contact HR for more details.';
            }

            setText('applyJobTitle', title);
            setText('applyJobTitleMeta', title);
            setText('applyJobDept', dept);
            setText('applyJobLocation', loc);
            setText('applyJobType', type);
            setText('applyJobDesc', desc);
        }

        function renderPublicDynamicQuestions(job) {
            var container = document.getElementById('dynamicQuestions');
            if (!container) return;
            var list = (job && job.questions) || [];
            var normalized = list.map(normalizeApplyQuestion).filter(function (q) {
                return q.text.trim() !== '';
            });
            if (!normalized.length) {
                container.innerHTML =
                    '<p class="font-13 text-light mb-0 py-8">No extra screening questions for this role. Submit your details and documents above.</p>';
                return;
            }
            var html = '';
            normalized.forEach(function (q) {
                var safeQ = escapeAttr(q.text);
                var reqStar = q.required ? ' *' : '';
                var reqAttr = q.required ? ' required' : '';
                html += '<div class="form-group mb-20">';
                html += '<label class="admin-form-label">' + escapeAttr(q.text) + reqStar + '</label>';
                if (q.type === 'Number') {
                    html +=
                        '<input type="number" step="any" class="form-control app-question" data-question="' +
                        safeQ +
                        '"' +
                        reqAttr +
                        '>';
                } else if (q.type === 'Dropdown') {
                    html +=
                        '<select class="form-control app-question-select" data-question="' +
                        safeQ +
                        '"' +
                        reqAttr +
                        '>';
                    html += '<option value="">Select an option</option>';
                    ['Yes', 'No', 'Prefer not to say'].forEach(function (opt) {
                        html += '<option value="' + escapeAttr(opt) + '">' + escapeAttr(opt) + '</option>';
                    });
                    html += '</select>';
                } else if (q.type === 'File Upload') {
                    html +=
                        '<input type="file" class="form-control font-12 py-10 app-question-file" data-question="' +
                        safeQ +
                        '"' +
                        reqAttr +
                        '>';
                } else {
                    html +=
                        '<input type="text" class="form-control app-question" data-question="' +
                        safeQ +
                        '"' +
                        reqAttr +
                        '>';
                }
                html += '</div>';
            });
            container.innerHTML = html;
        }

        var params = new URLSearchParams(window.location.search);
        var jobId = params.get('jobId');
        var currentJob = null;

        async function loadPublicJob() {
            if (!jobId) return;
            try {
                const response = await fetch(`${API_HANDLER}?action=fetch_job_detail&id=${jobId}`);
                const result = await response.json();
                if (result.status === 'success') {
                    currentJob = result.data;
                    populatePublicJobPosting(currentJob, jobId);
                    renderPublicDynamicQuestions(currentJob);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            } catch (err) {
                console.error('Error loading public job:', err);
            }
        }

        loadPublicJob();

        // --- CNIC Masking ---
        const cnicInput = document.getElementById('appCnicNumber');
        if (cnicInput) {
            cnicInput.addEventListener('input', function(e) {
                let val = e.target.value.replace(/\D/g, '');
                if (val.length > 13) val = val.slice(0, 13);
                
                let res = '';
                if (val.length > 0) res += val.slice(0, 5);
                if (val.length > 5) res += '-' + val.slice(5, 12);
                if (val.length > 12) res += '-' + val.slice(12, 13);
                
                e.target.value = res;
            });
        }

        // --- Phone Masking ---
        const phoneInput = document.getElementById('appPhone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let val = e.target.value.replace(/\D/g, '');
                if (val.length > 11) val = val.slice(0, 11);
                
                let res = '';
                if (val.length > 0) res += val.slice(0, 4);
                if (val.length > 4) res += '-' + val.slice(4, 11);
                
                e.target.value = res;
            });
        }

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        function readFileDataUrl(fileInput, maxBytes) {
            return new Promise(function (resolve) {
                var file = fileInput && fileInput.files && fileInput.files[0];
                if (!file) {
                    resolve(null);
                    return;
                }
                if (file.size > maxBytes) {
                    resolve(null);
                    return;
                }
                var r = new FileReader();
                r.onload = function () {
                    resolve(r.result);
                };
                r.onerror = function () {
                    resolve(null);
                };
                r.readAsDataURL(file);
            });
        }

        applyForm.onsubmit = async function (e) {
            e.preventDefault();
            
            const submitBtn = applyForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Submitting...</span><i data-lucide="loader-2" class="spin" size="18"></i>';
            if (typeof lucide !== 'undefined') lucide.createIcons();

            var answers = {};
            document.querySelectorAll('.app-question').forEach(function (input) {
                answers[input.dataset.question] = input.value;
            });
            document.querySelectorAll('.app-question-select').forEach(function (sel) {
                answers[sel.dataset.question] = sel.value;
            });
            document.querySelectorAll('.app-question-file').forEach(function (input) {
                // For screening files, we just store the filename for now
                var f = input.files && input.files[0];
                answers[input.dataset.question] = f ? f.name : 'No file';
            });

            var resumeEl = document.getElementById('appResume');
            var cnicNumberEl = document.getElementById('appCnicNumber');
            var fullNameEl = document.getElementById('appFullName');
            var emailEl = document.getElementById('appEmail');
            var phoneEl = document.getElementById('appPhone');
            var addressEl = document.getElementById('appAddress');

            const formData = new FormData();
            formData.append('action', 'submit_application');
            formData.append('name', fullNameEl.value);
            formData.append('email', emailEl.value);
            formData.append('phone', phoneEl ? phoneEl.value : '');
            formData.append('cnic_number', cnicNumberEl.value);
            formData.append('address', addressEl ? addressEl.value : '');
            formData.append('job_id', jobId || '');
            formData.append('answers', JSON.stringify(answers));
            
            if (resumeEl.files[0]) {
                formData.append('resume', resumeEl.files[0]);
            }

            try {
                const response = await fetch(API_HANDLER, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    var next = 'job-apply.php?submitted=1';
                    if (jobId) next += '&jobId=' + encodeURIComponent(jobId);
                    window.location.href = next;
                } else {
                    alert('Error submitting application: ' + result.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            } catch (err) {
                console.error('Submission error:', err);
                alert('An unexpected error occurred. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        };
    }
});
