// Job Management Logic
document.addEventListener('DOMContentLoaded', function() {
    // --- Initial Jobs Data (Mock) ---
    let jobs = JSON.parse(localStorage.getItem('hrm_jobs')) || [
        { id: 'job_1', title: 'Operations Assistant', department: 'Operations Assistant', location: 'North Nazimabad Block D, Near Ship Owner College at Hill View Apartment karachi.', type: 'Full-time', status: 'Active', postedDate: 'Jan 28, 2026', applicants: 2 },
        { id: 'job_2', title: 'SEO Executive', department: 'Marketing', location: 'North Nazimabad Block D, Near Ship Owner College at Hill View Apartment karachi.', type: 'Full-time', status: 'Active', postedDate: 'Jan 28, 2026', applicants: 9 },
        { id: 'job_3', title: 'IT', department: 'IT', location: 'North Nazimabad Block D, Near Ship Owner College at Hill View Apartment karachi.', type: 'Full-time', status: 'Active', postedDate: 'Jan 7, 2026', applicants: 3 },
        { id: 'job_4', title: 'CSR / Chat Support', department: 'Customer Service Representative', location: 'North Nazimabad Block D, Near Ship Owner College at Hill View Apartment karachi.', type: 'Full-time', status: 'Active', postedDate: 'Jan 6, 2026', applicants: 5 },
        { id: 'job_5', title: 'Backend Developer', department: 'Production', location: 'North Nazimabad Block D, Near Ship Owner College at Hill View Apartment karachi.', type: 'Full-time', status: 'Active', postedDate: 'Jan 6, 2026', applicants: 7 },
        { id: 'job_6', title: 'Final Expense Sales Executive', department: 'Sales Executive', location: 'North Nazimabad Block D, Near Ship Owner College at Hill View Apartment karachi.', type: 'Full-time', status: 'Active', postedDate: 'Jan 6, 2026', applicants: 18 }
    ];

    function saveJobs() {
        localStorage.setItem('hrm_jobs', JSON.stringify(jobs));
    }

    // --- Candidate Management Logic ---
    let candidates = JSON.parse(localStorage.getItem('hrm_candidates')) || [
        { id: 'cand_1', name: 'M Sadiq Ahmed', email: 'sadiqahmed@gmail.com', phone: '+92 301 1122334', location: 'North Nazimabad, Karachi', jobId: 'job_6', jobTitle: 'Final Expense Sales Executive', appliedDate: 'Feb 12, 2026', status: 'Interview', cnic: false, matchScore: 16, answers: { 'What is your current salary?': 'PKR 45,000', 'What is your expected salary?': 'PKR 60,000', 'Do you have your own laptop?': 'Yes' }, resumeUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', resumeFileName: 'CV_Sadiq_Ahmed.pdf', cnicFrontUrl: 'assets/images/default-avatar.svg', cnicBackUrl: 'assets/images/default-avatar.svg', cnicFrontFileName: 'cnic_front.jpg', cnicBackFileName: 'cnic_back.jpg' },
        { id: 'cand_2', name: 'Syed Shahir Ali', email: 'syedshahirali16@gmail.com', phone: '+92 333 4455667', location: 'Gulistan-e-Jauhar, Karachi', jobId: 'job_1', jobTitle: 'Operations Assistant', appliedDate: 'Feb 10, 2026', status: 'Interview', cnic: true, matchScore: 10, answers: { 'What is your current salary?': 'PKR 38,000', 'What is your expected salary?': 'PKR 50,000' }, resumeUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', resumeFileName: 'Resume_Shahir.pdf', cnicFrontUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', cnicBackUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', cnicFrontFileName: 'CNIC_front.pdf', cnicBackFileName: 'CNIC_back.pdf' },
        { id: 'cand_3', name: 'Harmain Masood', email: 'harmainmasood@gmail.com', phone: '+92 300 9988776', location: 'Lahore, Punjab', jobId: 'job_2', jobTitle: 'SEO Executive', appliedDate: 'Feb 9, 2026', status: 'Interview', cnic: false, matchScore: 23, answers: { 'What is your current salary?': 'PKR 70,000', 'What is your expected salary?': 'PKR 85,000', 'Do you have your own laptop?': 'Yes' }, resumeUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', resumeFileName: 'harmain_cv.pdf', cnicFrontUrl: 'assets/images/default-avatar.svg', cnicBackUrl: 'assets/images/default-avatar.svg', cnicFrontFileName: '', cnicBackFileName: '' },
        { id: 'cand_4', name: 'Ibad Uddin', email: 'ibaduddin222@gmail.com', phone: '+92 321 5544332', location: 'Islamabad', jobId: 'job_2', jobTitle: 'SEO Executive', appliedDate: 'Feb 7, 2026', status: 'Interview', cnic: false, matchScore: 2, answers: { 'What is your current salary?': 'PKR 25,000', 'What is your expected salary?': 'PKR 40,000' }, resumeUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', resumeFileName: 'ibad_resume.pdf', cnicFrontUrl: '', cnicBackUrl: '', cnicFrontFileName: '', cnicBackFileName: '' },
        { id: 'cand_5', name: 'Suffiyan', email: 'suffiyank898@gmail.com', phone: '—', location: 'Karachi', jobId: 'job_2', jobTitle: 'SEO Executive', appliedDate: 'Feb 5, 2026', status: 'New', cnic: false, matchScore: 0, answers: {}, resumeUrl: '', cnicFrontUrl: '', cnicBackUrl: '', resumeFileName: '', cnicFrontFileName: '', cnicBackFileName: '' },
        { id: 'cand_6', name: 'Muhammad Wasim', email: 'muhammadwasim@gmail.com', phone: '+92 345 6677889', location: 'Hyderabad', jobId: 'job_2', jobTitle: 'SEO Executive', appliedDate: 'Feb 4, 2026', status: 'New', cnic: true, matchScore: 2, answers: { 'What is your current salary?': 'Not employed' }, resumeUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', resumeFileName: 'wasim.pdf', cnicFrontUrl: 'assets/images/default-avatar.svg', cnicBackUrl: 'assets/images/default-avatar.svg', cnicFrontFileName: 'cnic_f.png', cnicBackFileName: 'cnic_b.png' },
        { id: 'cand_7', name: 'Gohar Iqbal Khan', email: 'gohariqbal@gmail.com', phone: '+92 302 2233445', location: 'Karachi', jobId: 'job_6', jobTitle: 'Final Expense Sales Executive', appliedDate: 'Feb 4, 2026', status: 'Interview', cnic: false, matchScore: 0, answers: { 'What is your expected salary?': 'Commission based' }, resumeUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', resumeFileName: 'gohar_cv.pdf', cnicFrontUrl: '', cnicBackUrl: '' },
        { id: 'cand_8', name: 'Muhammad Anas', email: 'muhammadanas@gmail.com', phone: '+92 311 0099887', location: 'Karachi', jobId: 'job_2', jobTitle: 'SEO Executive', appliedDate: 'Feb 4, 2026', status: 'New', cnic: false, matchScore: 0, answers: {}, resumeUrl: '', cnicFrontUrl: '', cnicBackUrl: '' }
    ];

    function saveCandidates() {
        localStorage.setItem('hrm_candidates', JSON.stringify(candidates));
    }

    if (!localStorage.getItem('hrm_candidates')) {
        saveCandidates();
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
                                <label class="admin-form-label admin-form-label--inner">Question Text</label>
                                <input type="text" class="form-control bg-white-input question-input font-14 font-600" value="${text}" placeholder="e.g. What is your notice period?">
                            </div>
                            <div class="form-group">
                                <label class="admin-form-label admin-form-label--inner">Answer Type</label>
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

            // Add default questions
            addQuestion('What is your current salary?');
            addQuestion('What is your expected salary?');
        }

        createJobForm.onsubmit = function(e) {
            e.preventDefault();
            const questionCards = document.querySelectorAll('.form-builder-card:not(.locked)');
            const questions = Array.from(questionCards).map(card => ({
                text: card.querySelector('.question-input').value,
                type: card.querySelector('.question-type').value,
                required: card.querySelector('.question-required').checked
            })).filter(q => q.text.trim() !== '');

            const newJob = {
                id: 'job_' + Date.now(),
                title: document.getElementById('jobTitle').value,
                department: document.getElementById('jobDept').value,
                location: document.getElementById('jobLocation').value,
                type: document.getElementById('jobType').value,
                description: document.getElementById('jobDesc').value,
                status: 'Active',
                postedDate: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
                applicants: 0,
                questions: questions
            };

            jobs.unshift(newJob);
            saveJobs();
            alert('Job created successfully!');
            window.location.href = 'job-list.php';
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
                const matchesSearch = job.title.toLowerCase().includes(query) || job.department.toLowerCase().includes(query) || job.location.toLowerCase().includes(query);
                const matchesDept = dept === '' || job.department === dept || job.department.includes(dept);
                const matchesStatus = status === '' || job.status === status;
                return matchesSearch && matchesDept && matchesStatus;
            });

            if (sortBy === 'newest') {
                filteredJobs.sort((a, b) => new Date(b.postedDate) - new Date(a.postedDate));
            } else if (sortBy === 'oldest') {
                filteredJobs.sort((a, b) => new Date(a.postedDate) - new Date(b.postedDate));
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
                const appCount = candidates.filter(c => c.jobId === job.id).length || job.applicants || 0;
                return `
                <tr>
                    <td>
                        <div class="text-dark font-14 font-600">${job.title}</div>
                        <div class="font-11 text-light mt-1">${job.type}</div>
                    </td>
                    <td class="font-14">${job.department}</td>
                    <td class="font-14 allow-wrap">${job.location}</td>
                    <td class="font-14">${job.postedDate}</td>
                    <td class="font-14">${appCount} Applicants</td>
                    <td><span class="badge ${job.status === 'Active' ? 'badge-success' : job.status === 'Draft' ? 'badge-info' : 'badge-warning'}">${job.status}</span></td>
                    <td class="text-right px-30">
                        <div class="flex-center gap-8 justify-end">
                            <button class="action-btn" title="View Details" onclick="viewJobDetails('${job.id}')"><i data-lucide="eye" size="14"></i></button>
                            <button class="action-btn" title="Edit Job"><i data-lucide="edit-2" size="14"></i></button>
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

        window.viewJobDetails = function(jobId) {
            const job = jobs.find(j => j.id === jobId);
            if (!job) return;

            const appCount = candidates.filter(c => c.jobId === job.id).length || job.applicants || 0;

            document.getElementById('detailJobTitle').textContent = job.title;
            const statusClass = job.status === 'Active' ? 'job-detail-pill--success' : job.status === 'Draft' ? 'job-detail-pill--info' : 'job-detail-pill--neutral';
            document.getElementById('detailJobAppCount').innerHTML = `
                <span class="job-detail-pill"><i data-lucide="users" size="14"></i> ${appCount} Applicants</span>
                <span class="job-detail-pill ${statusClass}">${job.status}</span>`;
            
            document.getElementById('detailDept').textContent = job.department;
            document.getElementById('detailLocation').textContent = job.location;
            document.getElementById('detailType').textContent = job.type || 'Full-time';
            document.getElementById('detailPostedDate').textContent = job.postedDate;

            // Description
            const detailDesc = document.getElementById('detailDesc');
            detailDesc.textContent = job.description || 'No description provided for this job posting.';

            // Requirements & Questions
            const detailQuestionsList = document.getElementById('detailQuestionsList');
            detailQuestionsList.innerHTML = '';
            
            const questions = job.questions || [
                { text: 'What is your current salary?', type: 'Text Answer', required: true },
                { text: 'What is your expected salary?', type: 'Number', required: true },
                { text: 'Do you have your own laptop?', type: 'Dropdown', required: false }
            ]; // Fallback for old mock data

            const standardOffset = 3;
            let questionsHtml = '';
            if (questions.length) {
                questionsHtml = '<div class="job-detail-q-list job-detail-q-list--custom">';
                questions.forEach((q, index) => {
                    const reqClass = q.required ? 'text-danger font-500' : '';
                    const qNum = index + 1 + standardOffset;
                    questionsHtml += `
                    <div class="job-detail-q-item">
                        <div class="job-detail-q-item__q">Q${qNum}. ${q.text}</div>
                        <div class="job-detail-q-item__meta">
                            <span><i data-lucide="help-circle" size="16"></i> ${q.type}</span>
                            <span class="${reqClass}"><i data-lucide="${q.required ? 'asterisk' : 'check'}" size="16"></i> ${q.required ? 'Required' : 'Optional'}</span>
                        </div>
                    </div>`;
                });
                questionsHtml += '</div>';
            }
            detailQuestionsList.innerHTML = questionsHtml;

            // Footer Actions
            const detailCopyLinkBtn = document.getElementById('detailCopyLinkBtn');
            const detailEditBtn = document.getElementById('detailEditBtn');
            detailCopyLinkBtn.onclick = () => copyJobLink(job.id);
            detailEditBtn.onclick = () => { /* Placeholder for Edit */ alert('Edit Job modal/page to be implemented.'); };

            openModal('jobDetailModal');
            if (typeof lucide !== 'undefined') lucide.createIcons();
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

        renderJobs();
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
    const candPerPage = document.getElementById('perPageSelect'); // Same ID since on different page
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
                const avatarBg = avatarColors[cand.name.length % avatarColors.length];
                
                return `
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
                        <span class="badge-select ${cand.status.toLowerCase().replace(' ', '-')}">${cand.status}</span>
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
        renderCandidates();
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
            if (typeof q === 'string') {
                return { text: q, type: 'Text Answer', required: true };
            }
            if (q && typeof q === 'object') {
                return {
                    text: typeof q.text === 'string' ? q.text : '',
                    type: q.type || 'Text Answer',
                    required: q.required !== false
                };
            }
            return { text: '', type: 'Text Answer', required: true };
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
            var dept = job.department || '—';
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
            var html = '<p class="apply-section-label" style="margin-top:8px;">Role-specific questions</p>';
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
        var job = jobId ? jobs.find(function (j) { return j.id === jobId; }) : null;

        populatePublicJobPosting(job, jobId);
        renderPublicDynamicQuestions(job);

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

        applyForm.onsubmit = function (e) {
            e.preventDefault();
            var answers = {};
            document.querySelectorAll('.app-question').forEach(function (input) {
                answers[input.dataset.question] = input.value;
            });
            document.querySelectorAll('.app-question-select').forEach(function (sel) {
                answers[sel.dataset.question] = sel.value;
            });
            document.querySelectorAll('.app-question-file').forEach(function (input) {
                var f = input.files && input.files[0];
                answers[input.dataset.question] = f ? f.name : '';
            });

            var resumeEl = document.getElementById('appResume');
            var cnicFEl = document.getElementById('appCnicFront');
            var cnicBEl = document.getElementById('appCnicBack');
            var phoneEl = document.getElementById('appPhone');
            var locEl = document.getElementById('appLocation');

            var MAX = 1200000;

            Promise.all([
                readFileDataUrl(resumeEl, MAX),
                readFileDataUrl(cnicFEl, 800000),
                readFileDataUrl(cnicBEl, 800000)
            ]).then(function (results) {
                var resumeDataUrl = results[0];
                var cnicFrontDataUrl = results[1];
                var cnicBackDataUrl = results[2];

                var titleFromPage = (function () {
                    var el = document.getElementById('applyJobTitle');
                    return el && el.textContent ? el.textContent.trim() : 'Application';
                })();
                var fullName = (function () {
                    var el = document.getElementById('appFullName');
                    return el && el.value ? el.value.trim() : '';
                })();
                var newCandidate = {
                    id: 'cand_' + Date.now(),
                    name: fullName,
                    email: document.getElementById('appEmail').value,
                    phone: phoneEl ? phoneEl.value : '',
                    location: locEl ? locEl.value : '',
                    jobId: jobId || 'unknown',
                    jobTitle: job ? job.title : titleFromPage,
                    appliedDate: new Date().toISOString().split('T')[0],
                    status: 'New',
                    cnic: !!(cnicFrontDataUrl && cnicBackDataUrl),
                    matchScore: Math.floor(Math.random() * 40) + 60,
                    answers: answers,
                    resumeDataUrl: resumeDataUrl || '',
                    resumeUrl: '',
                    resumeFileName: resumeEl && resumeEl.files[0] ? resumeEl.files[0].name : '',
                    cnicFrontDataUrl: cnicFrontDataUrl || '',
                    cnicBackDataUrl: cnicBackDataUrl || '',
                    cnicFrontUrl: '',
                    cnicBackUrl: '',
                    cnicFrontFileName: cnicFEl && cnicFEl.files[0] ? cnicFEl.files[0].name : '',
                    cnicBackFileName: cnicBEl && cnicBEl.files[0] ? cnicBEl.files[0].name : ''
                };

                candidates.push(newCandidate);
                saveCandidates();
                var next = 'job-apply.php?submitted=1';
                if (jobId) next += '&jobId=' + encodeURIComponent(jobId);
                window.location.href = next;
            });
        };
    }
});
