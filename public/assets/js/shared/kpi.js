/**
 * KPI Management Hub Logic (v4 - Dynamic Job Criteria & Template Builder)
 */

document.addEventListener('DOMContentLoaded', function () {
    initSummary();
    initKpiTable();
    initFilters();
    initModalData();
    initKpiFormLogic();
    initTemplateModalLogic();
});

// ─── State ────────────────────────────────────────────────────────────────────
let allKpiData          = [];
let _lastFilteredData    = [];
let kpiCurrentPage      = 1;
let kpiRowsPerPage      = 10;
let employeeData         = [];
let currentLoadedTemplate = [];

// ─── Summary Cards ────────────────────────────────────────────────────────────
function initSummary() {
    fetch('/assets/api/kpi_handler.php?action=fetch_summary')
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') return;
            const d = res.data;
            document.getElementById('statAvgScore').textContent = `${d.avg_score} / 100`;
            document.getElementById('statRatedCount').textContent = `${d.rated_count} / ${d.total_count}`;
            document.getElementById('statTopDept').textContent = d.top_dept;
        });
}

// ─── Table Data ───────────────────────────────────────────────────────────────
function initKpiTable() {
    fetch('/assets/api/kpi_handler.php?action=fetch_list')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                allKpiData = res.data;
                _lastFilteredData = allKpiData;
                kpiCurrentPage = 1;
                renderKpiTable(_lastFilteredData);
                setupPaginationControls();
            }
        });
}

// ─── Table Rendering ──────────────────────────────────────────────────────────
function renderKpiTable(data) {
    const tbody = document.getElementById('kpiTableBody');
    tbody.innerHTML = '';

    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-40 text-light italic">No performance reviews found.</td></tr>';
        updatePaginationUI(0);
        return;
    }

    const totalRows  = data.length;
    const totalPages = kpiRowsPerPage === -1 ? 1 : Math.ceil(totalRows / kpiRowsPerPage);
    if (kpiCurrentPage > totalPages) kpiCurrentPage = totalPages;
    if (kpiCurrentPage < 1) kpiCurrentPage = 1;

    const start    = kpiRowsPerPage === -1 ? 0 : (kpiCurrentPage - 1) * kpiRowsPerPage;
    const end      = kpiRowsPerPage === -1 ? totalRows : Math.min(start + kpiRowsPerPage, totalRows);
    const pageData = data.slice(start, end);

    pageData.forEach(item => {
        const overallPct    = item.overall_pct || 0;
        const statusClass   = getStatusClass(item.status);
        const defaultAvatar = '../assets/images/profile-image/default-avatar.svg';
        const avatarUrl     = item.profile_pic ? '../' + item.profile_pic : defaultAvatar;
        const empName       = [item.first_name, item.middle_name, item.last_name].filter(p => p && p.trim()).join(' ');

        // Summary of items criteria
        let itemsSummaryHtml = '<span class="text-light italic font-12">Not Rated Yet</span>';
        if (item.items && item.items.length > 0) {
            const categories = {};
            item.items.forEach(it => {
                categories[it.category] = (categories[it.category] || 0) + parseFloat(it.achieved_score || 0);
            });

            itemsSummaryHtml = Object.keys(categories).map(cat => {
                const catScore = Math.round(categories[cat] * 10) / 10;
                return `<span class="badge badge-secondary-light font-11 mr-4 mb-4"><strong>${escapeHtml(cat)}:</strong> ${catScore} pts</span>`;
            }).join('');
        }

        const grade = item.grade || '';
        const gradeClass = grade.includes('Grade A') ? 'success' : grade.includes('Grade B') ? 'primary' : (grade ? 'warning' : 'secondary');

        const row = `
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding:14px 16px; vertical-align:middle; overflow:hidden;">
                    <div class="emp-profile" style="display:flex; align-items:center; gap:12px; min-width:0;">
                        <img src="${avatarUrl}" class="emp-avatar" alt="Avatar" style="width:38px; height:38px; border-radius:50%; object-fit:cover; flex-shrink:0;"
                             onerror="this.onerror=null; this.src='${defaultAvatar}';">
                        <div class="emp-info" style="min-width:0; flex:1; overflow:hidden;">
                            <div class="name font-700" title="${escapeHtml(empName)}" style="font-size:13px; font-weight:700; color:#0f172a; word-break:break-word; line-height:1.3; margin-bottom:2px;">${escapeHtml(empName)}</div>
                            <div class="email font-11 text-light" style="font-size:11px; color:#64748b;">EMP-${String(item.employee_id).padStart(3,'0')}</div>
                        </div>
                    </div>
                </td>
                <td style="padding:14px 16px; vertical-align:middle; overflow:hidden;">
                    <div class="font-600 text-dark" style="font-size:13px; font-weight:600; color:#1e293b; word-break:break-word; line-height:1.3;">${escapeHtml(item.job_title || 'N/A')}</div>
                    <div class="font-11 text-light" style="font-size:11px; color:#64748b; word-break:break-word;">${escapeHtml(item.department_name || 'Unassigned')}</div>
                </td>
                <td style="padding:14px 16px; text-align:center; vertical-align:middle;">
                    ${item.review_id
                        ? `<div style="display:inline-flex; flex-direction:column; align-items:center; gap:4px;">
                               <span class="font-14 font-800 text-primary-color" style="font-size:14px; font-weight:800; color:#6c4cf1;">${overallPct}%</span>
                               <div class="progress-bar-container" style="width:70px; height:5px; background:#e2e8f0; border-radius:3px; overflow:hidden;">
                                   <div class="progress-bar ${statusClass}" style="width:${overallPct}%; height:100%; background:#6c4cf1;"></div>
                               </div>
                           </div>`
                        : '<span class="text-light italic font-12" style="font-size:12px; color:#94a3b8; font-style:italic;">Not Rated</span>'
                    }
                </td>
                <td style="padding:14px 16px; text-align:center; vertical-align:middle;">
                    ${item.review_id ? `<span class="badge badge-${gradeClass}" style="font-size:11px; font-weight:700; padding:4px 10px; border-radius:12px;">${escapeHtml(grade || item.status)}</span>` : '<span class="badge badge-secondary" style="font-size:11px; font-weight:700; padding:4px 10px; border-radius:12px; background:#f1f5f9; color:#64748b;">Not Rated</span>'}
                </td>
                <td style="padding:14px 16px; text-align:center; vertical-align:middle;">
                    <span class="font-12 font-600 text-dark" style="font-size:12px; font-weight:600; color:#334155;">${item.period_month || item.period || 'Never'}</span>
                </td>
                <td style="padding:14px 12px; text-align:center; vertical-align:middle;">
                    <div style="display:inline-flex; align-items:center; justify-content:center; gap:6px; flex-wrap:nowrap;">
                        ${item.review_id ? `
                            <a href="${window.HRM && window.HRM.url ? window.HRM.url('kpi/evaluate') : '/kpi/evaluate'}?review_id=${item.review_id}" class="btn-ghost font-11" style="color:#6c4cf1; background:#f5f3ff; border:1px solid #ddd6fe; border-radius:6px; font-weight:600; padding:5px 8px; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;" title="Edit Evaluation">
                                <i data-lucide="edit-2" size="12"></i> <span>Edit</span>
                            </a>
                        ` : `
                            <a href="${window.HRM && window.HRM.url ? window.HRM.url('kpi/evaluate') : '/kpi/evaluate'}?employee_id=${item.employee_id}" class="btn-ghost font-11" style="color:#6c4cf1; background:#f5f3ff; border:1px solid #ddd6fe; border-radius:6px; font-weight:600; padding:5px 8px; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;" title="Evaluate Employee">
                                <i data-lucide="plus-circle" size="12"></i> <span>Evaluate</span>
                            </a>
                        `}
                        <a href="${window.HRM && window.HRM.url ? window.HRM.url('kpi/report') : '/kpi/report'}?id=${item.employee_id}"
                           class="btn-primary font-11" style="border-radius:6px; font-weight:600; padding:5px 10px; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;" title="View Scorecard Report">
                            <i data-lucide="eye" size="12"></i> <span>Scorecard</span>
                        </a>
                    </div>
                </td>
            </tr>`;
        tbody.insertAdjacentHTML('beforeend', row);
    });

    updatePaginationUI(totalRows);
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// ─── Pagination ───────────────────────────────────────────────────────────────
function setupPaginationControls() {
    const perPageSelect = document.getElementById('perPageSelect');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    if (perPageSelect && !perPageSelect.dataset.bound) {
        perPageSelect.dataset.bound = '1';
        perPageSelect.addEventListener('change', () => {
            kpiRowsPerPage = perPageSelect.value === 'all' ? -1 : parseInt(perPageSelect.value);
            kpiCurrentPage = 1;
            renderKpiTable(_lastFilteredData);
        });
    }
    if (prevBtn && !prevBtn.dataset.bound) {
        prevBtn.dataset.bound = '1';
        prevBtn.addEventListener('click', () => { if (kpiCurrentPage > 1) { kpiCurrentPage--; renderKpiTable(_lastFilteredData); } });
    }
    if (nextBtn && !nextBtn.dataset.bound) {
        nextBtn.dataset.bound = '1';
        nextBtn.addEventListener('click', () => {
            const totalPages = kpiRowsPerPage === -1 ? 1 : Math.ceil(_lastFilteredData.length / kpiRowsPerPage);
            if (kpiCurrentPage < totalPages) { kpiCurrentPage++; renderKpiTable(_lastFilteredData); }
        });
    }
}

function updatePaginationUI(totalRows) {
    const totalPages = kpiRowsPerPage === -1 ? 1 : Math.ceil(totalRows / kpiRowsPerPage);
    const start  = kpiRowsPerPage === -1 ? 1 : (kpiCurrentPage - 1) * kpiRowsPerPage + 1;
    const end    = kpiRowsPerPage === -1 ? totalRows : Math.min(kpiCurrentPage * kpiRowsPerPage, totalRows);
    const infoText = totalRows === 0 ? 'No entries found' : `Showing ${start} to ${end} of ${totalRows} entries`;

    ['paginationInfo', 'tableSummary'].forEach(id => { const el = document.getElementById(id); if (el) el.textContent = infoText; });

    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    if (prevBtn) { prevBtn.disabled = kpiCurrentPage <= 1; prevBtn.style.opacity = kpiCurrentPage <= 1 ? '0.5' : '1'; }
    if (nextBtn) { nextBtn.disabled = kpiCurrentPage >= totalPages; nextBtn.style.opacity = kpiCurrentPage >= totalPages ? '0.5' : '1'; }

    const pageNumbersEl = document.getElementById('pageNumbers');
    if (pageNumbersEl) {
        pageNumbersEl.innerHTML = '';
        const maxVisible = 5;
        let startPage = Math.max(1, kpiCurrentPage - Math.floor(maxVisible / 2));
        let endPage   = Math.min(totalPages, startPage + maxVisible - 1);
        if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);
        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.className = `action-btn ${i === kpiCurrentPage ? 'btn-active' : ''}`;
            btn.textContent = i;
            btn.onclick = () => { kpiCurrentPage = i; renderKpiTable(_lastFilteredData); };
            pageNumbersEl.appendChild(btn);
        }
    }
}

// ─── Filters ──────────────────────────────────────────────────────────────────
function initFilters() {
    const search = document.getElementById('searchEmployee');
    const dept   = document.getElementById('filterDept');
    const month  = document.getElementById('filterMonth');
    const status = document.getElementById('filterStatus');

    fetch('/assets/api/employee_handler.php?action=fetch_requirements')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                res.departments.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id; opt.textContent = d.name;
                    dept?.appendChild(opt);
                });
            }
        });

    const applyLocalFilters = () => {
        const query = (search?.value || '').toLowerCase();
        const dVal  = dept?.value  || '';
        const sVal  = status?.value || '';

        _lastFilteredData = allKpiData.filter(item => {
            const name = [item.first_name, item.middle_name, item.last_name, item.job_title].filter(Boolean).join(' ').toLowerCase();
            const matchesSearch = name.includes(query);
            const matchesDept   = dVal === '' || item.department_id == dVal;
            const matchesStatus = sVal === '' || (item.grade && item.grade.includes(sVal)) || item.status === sVal;
            return matchesSearch && matchesDept && matchesStatus;
        });
        kpiCurrentPage = 1;
        renderKpiTable(_lastFilteredData);
    };

    const fetchForMonth = () => {
        const mVal = month?.value || '';
        const url = mVal
            ? `/assets/api/kpi_handler.php?action=fetch_list&period_month=${encodeURIComponent(mVal)}`
            : `/assets/api/kpi_handler.php?action=fetch_list`;

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    allKpiData = res.data;
                    applyLocalFilters();
                    setupPaginationControls();
                }
            });
    };

    month?.addEventListener('change', fetchForMonth);
    month?.addEventListener('input',  fetchForMonth);

    [search, dept, status].forEach(el => {
        el?.addEventListener('input',  applyLocalFilters);
        el?.addEventListener('change', applyLocalFilters);
    });
}

function getStatusClass(status) {
    if (status === 'Excelling' || (status && status.includes('Grade A'))) return 'success';
    if (status === 'Good' || (status && status.includes('Grade B')))      return 'primary';
    if (status === 'On Track')                                           return 'warning';
    if (status === 'Below Target' || status === 'Poor')                   return 'danger';
    return 'secondary';
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// ─── Modal Data (Employee selection & dynamic template loading) ───────────────
function initModalData() {
    const empSelect = document.getElementById('modalEmployeeSelect');
    if (!empSelect) return;

    fetch('/assets/api/kpi_handler.php?action=fetch_employees')
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') return;
            employeeData = res.data;

            // Populate Employee select in review modal
            empSelect.innerHTML = '<option value="">Choose Employee...</option>';
            res.data.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.id;
                opt.textContent = [e.first_name, e.middle_name, e.last_name].filter(Boolean).join(' ') + (e.job_title ? ` (${e.job_title})` : '');
                empSelect.appendChild(opt);
            });

            // Populate Job Title dropdown in Template modal
            const jobTitleSelect = document.getElementById('templateJobTitleSelect');
            const empTemplateSelect = document.getElementById('templateEmployeeSelect');

            if (jobTitleSelect) {
                const uniqueTitles = [...new Set(res.data.map(e => e.job_title).filter(Boolean))];
                jobTitleSelect.innerHTML = '<option value="">Select Job Role Template...</option>';
                uniqueTitles.forEach(title => {
                    const opt = document.createElement('option');
                    opt.value = title;
                    opt.textContent = title;
                    jobTitleSelect.appendChild(opt);
                });
            }

            if (empTemplateSelect) {
                empTemplateSelect.innerHTML = '<option value="">Use Job Role Template (Default)</option>';
                res.data.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.id;
                    opt.textContent = [e.first_name, e.middle_name, e.last_name].filter(Boolean).join(' ') + (e.job_title ? ` (${e.job_title})` : '');
                    empTemplateSelect.appendChild(opt);
                });
            }
        });

    empSelect.addEventListener('change', function () {
        const id = this.value;
        if (!id) {
            hideJobDesc();
            document.getElementById('dynamicCriteriaContainer').innerHTML = '<div class="p-30 text-center text-light italic border rounded-12 bg-light">Please select an employee above to load their job evaluation criteria.</div>';
            return;
        }
        const emp = employeeData.find(e => String(e.id) === String(id));
        if (emp) {
            document.getElementById('jobDescRoleLabel').textContent = `JOB DESCRIPTION (${(emp.job_title || 'EMPLOYEE').toUpperCase()})`;
            if (emp.job_description) {
                document.getElementById('jobDescText').textContent = emp.job_description;
                document.getElementById('jobDescBox').style.display = 'block';
            } else {
                hideJobDesc();
            }
        }
        // Load criteria template for employee
        loadTemplateForReviewModal(id, emp?.job_title);
    });
}

function hideJobDesc() {
    const box = document.getElementById('jobDescBox');
    if (box) box.style.display = 'none';
}

// ─── Dynamic Evaluation Form Builder in Add/Edit Review Modal ────────────────
function loadTemplateForReviewModal(empId, jobTitle, existingItems = null) {
    const container = document.getElementById('dynamicCriteriaContainer');
    container.innerHTML = '<div class="p-20 text-center text-light"><i data-lucide="loader-2" size="20" class="spin"></i> Loading criteria...</div>';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    if (existingItems && existingItems.length > 0) {
        renderDynamicCriteriaForm(existingItems);
        return;
    }

    const month = document.getElementById('reviewPeriodMonth')?.value || new Date().toISOString().slice(0, 7);

    fetch(`/assets/api/kpi_handler.php?action=fetch_template&employee_id=${empId}&job_title=${encodeURIComponent(jobTitle || '')}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data && res.data.length > 0) {
                currentLoadedTemplate = res.data;
                renderDynamicCriteriaForm(res.data);
            } else {
                container.innerHTML = '<div class="p-20 text-center text-warning border rounded-12 bg-light">No evaluation template found for this role. Click "Configure KPI Templates" to set up questions.</div>';
            }
        });
}

function renderDynamicCriteriaForm(items) {
    const container = document.getElementById('dynamicCriteriaContainer');
    container.innerHTML = '';

    // Group items by category
    const categories = {};
    items.forEach((item, idx) => {
        const cat = item.category || 'Performance';
        if (!categories[cat]) categories[cat] = [];
        categories[cat].push({ ...item, orig_index: idx });
    });

    let html = '';
    const month = document.getElementById('reviewPeriodMonth')?.value || new Date().toISOString().slice(0, 7);

    Object.keys(categories).forEach(cat => {
        const catItems = categories[cat];
        const catTotalWeight = catItems.reduce((sum, i) => sum + parseFloat(i.target_weight || 0), 0);

        html += `
            <div class="kpi-category-card mb-24 p-20 rounded-16 border bg-white">
                <div class="flex-between align-center mb-16 pb-12 border-b">
                    <div class="flex-center gap-8">
                        <span class="font-15 font-800 text-dark">${escapeHtml(cat)}</span>
                        <span class="badge badge-primary font-11 font-700">${catTotalWeight} pts</span>
                    </div>
                </div>
                <div class="kpi-items-list flex-column gap-16">
        `;

        catItems.forEach((item, idx) => {
            const itemId     = `item_score_${item.orig_index}`;
            const commentId  = `item_comment_${item.orig_index}`;
            const targetWeight = parseFloat(item.target_weight || 0);
            const achievedVal  = item.achieved_score !== undefined ? parseFloat(item.achieved_score) : targetWeight;

            html += `
                <div class="kpi-item-row p-16 rounded-12 bg-light border">
                    <div class="flex-between align-start mb-8 flex-wrap gap-8">
                        <div style="flex:1;">
                            <div class="flex-center justify-start gap-8 flex-wrap">
                                <span class="font-14 font-700 text-dark">${escapeHtml(item.item_name)}</span>
                                <span class="badge badge-secondary-light font-10 font-700">${targetWeight} pts max</span>
                                ${item.is_auto_attendance ? `<span class="badge badge-success-light font-10"><i data-lucide="zap" size="10"></i> Auto-Attendance</span>` : ''}
                            </div>
                            ${item.evaluation_criteria ? `<p class="font-12 text-light m-0 mt-4">${escapeHtml(item.evaluation_criteria)}</p>` : ''}
                        </div>
                        <div class="flex-center gap-8">
                            ${item.is_auto_attendance ? `
                                <button type="button" class="btn-ghost font-11 px-12 py-4 border" onclick="autoCalcAttendanceItem('${itemId}', ${targetWeight})">
                                    <i data-lucide="refresh-cw" size="12" class="mr-4"></i> Auto-calc
                                </button>
                            ` : ''}
                            <div class="flex-center gap-6">
                                <input type="number" class="form-control font-14 font-700 kpi-score-input"
                                       id="${itemId}" data-target-weight="${targetWeight}"
                                       data-category="${escapeHtml(cat)}" data-name="${escapeHtml(item.item_name)}"
                                       data-criteria="${escapeHtml(item.evaluation_criteria || '')}"
                                       min="0" max="${targetWeight}" step="0.01" value="${achievedVal}"
                                       style="width:90px; text-align:right;" oninput="recalcOverall()">
                                <span class="font-13 text-light">/ ${targetWeight}</span>
                            </div>
                        </div>
                    </div>
                    <textarea id="${commentId}" class="form-control font-12 mt-8 kpi-comment-input" rows="1" placeholder="Optional item notes / feedback...">${escapeHtml(item.comment || '')}</textarea>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    if (typeof lucide !== 'undefined') lucide.createIcons();
    recalcOverall();
}

// ─── Auto-Calculate Attendance for specific Item ──────────────────────────────
window.autoCalcAttendanceItem = function (inputId, targetWeight) {
    const empId = document.getElementById('modalEmployeeSelect')?.value;
    const month = document.getElementById('reviewPeriodMonth')?.value || new Date().toISOString().slice(0, 7);
    if (!empId) { Swal.fire('Notice', 'Please select an employee first.', 'info'); return; }

    fetch(`/assets/api/kpi_handler.php?action=calc_attendance&employee_id=${empId}&month=${month}&target_weight=${targetWeight}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const input = document.getElementById(inputId);
                if (input) input.value = res.data.att_score;
                recalcOverall();
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: `Attendance Auto-Calculated: ${res.data.present_days}/${res.data.total_workdays} days (${res.data.att_pct}%) = ${res.data.att_score} pts`,
                    showConfirmButton: false, timer: 3000
                });
            }
        });
};

// ─── Real-time Overall Score Calculation ─────────────────────────────────────
window.recalcOverall = function () {
    const scoreInputs = document.querySelectorAll('.kpi-score-input');
    let totalScore = 0;

    scoreInputs.forEach(inp => {
        const val = parseFloat(inp.value) || 0;
        const max = parseFloat(inp.dataset.targetWeight) || 0;
        totalScore += Math.min(max, Math.max(0, val));
    });

    totalScore = Math.round(totalScore * 100) / 100;

    const display = document.getElementById('overallScoreDisplay');
    const grade   = document.getElementById('overallGradeDisplay');
    const bar     = document.getElementById('overallProgressBar');

    if (display) display.innerHTML = `${totalScore} <span class="font-16 font-400 text-light">/ 100</span>`;

    let gradeText = 'Grade C (Needs Improvement)', gradeClass = 'danger';
    if (totalScore >= 95)      { gradeText = 'Grade A (Excelling)'; gradeClass = 'success'; }
    else if (totalScore >= 85) { gradeText = 'Grade B (Good)';      gradeClass = 'primary'; }
    else if (totalScore >= 70) { gradeText = 'Grade C (On Track)';  gradeClass = 'warning'; }

    if (grade) { grade.textContent = gradeText; grade.className = `badge font-14 font-800 px-20 py-8 badge-${gradeClass}`; }
    if (bar)   { bar.style.width = `${Math.min(100, totalScore)}%`; bar.className = `progress-bar ${gradeClass}`; }
};

// ─── Review Form Submission ──────────────────────────────────────────────────
function initKpiFormLogic() {
    const form = document.getElementById('addReviewForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const empId = document.getElementById('modalEmployeeSelect')?.value;
        if (!empId) { Swal.fire('Error', 'Please select an employee.', 'error'); return; }

        const items = [];
        const scoreInputs = document.querySelectorAll('.kpi-score-input');

        scoreInputs.forEach((inp, i) => {
            const commentInput = document.querySelectorAll('.kpi-comment-input')[i];
            items.push({
                category: inp.dataset.category,
                item_name: inp.dataset.name,
                target_weight: parseFloat(inp.dataset.targetWeight) || 0,
                evaluation_criteria: inp.dataset.criteria || '',
                achieved_score: parseFloat(inp.value) || 0,
                comment: commentInput ? commentInput.value : ''
            });
        });

        const fd = new FormData(form);
        fd.append('action', 'add_review');
        fd.append('period', document.querySelector('.period-card.active span')?.textContent?.trim() || 'Monthly');
        fd.append('items', JSON.stringify(items));

        const btn  = document.getElementById('submitReviewBtn');
        const orig = btn?.innerHTML;
        if (btn) { btn.disabled = true; btn.innerHTML = '<i data-lucide="loader-2" size="16" class="spin"></i> Saving Review...'; if (typeof lucide !== 'undefined') lucide.createIcons(); }

        fetch('/assets/api/kpi_handler.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(res => {
                if (btn) { btn.disabled = false; btn.innerHTML = orig; }
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Saved!', text: res.message, timer: 2000, showConfirmButton: false });
                    closeModal('addReviewModal');
                    initKpiTable();
                    initSummary();
                } else { Swal.fire('Error', res.message, 'error'); }
            });
    });
}

// ─── Edit Review Handler ──────────────────────────────────────────────────────
window.editReview = function(reviewId) {
    fetch(`/assets/api/kpi_handler.php?action=fetch_review_details&id=${reviewId}`)
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') { Swal.fire('Error', res.message, 'error'); return; }
            const d = res.data;
            document.getElementById('reviewIdInput').value = d.id;
            document.getElementById('modalEmployeeSelect').value = d.employee_id;
            document.getElementById('reviewPeriodMonth').value = d.period_month;
            document.getElementById('generalFeedback').value = d.feedback || '';

            // Load template/snapshot items for review
            const emp = employeeData.find(e => String(e.id) === String(d.employee_id));
            loadTemplateForReviewModal(d.employee_id, emp?.job_title, d.items);
            openModal('addReviewModal');
        });
};

// ─── Configure KPI Templates Builder Logic ───────────────────────────────────
function initTemplateModalLogic() {
    const jobSelect = document.getElementById('templateJobTitleSelect');
    const empSelect = document.getElementById('templateEmployeeSelect');
    const form      = document.getElementById('kpiTemplateForm');

    if (!jobSelect || !form) return;

    jobSelect.addEventListener('change', () => {
        if (empSelect) empSelect.value = '';
        loadTemplateItemsForBuilder();
    });

    empSelect?.addEventListener('change', () => {
        loadTemplateItemsForBuilder();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const jobTitle   = jobSelect.value;
        const employeeId = empSelect?.value || '';

        if (!jobTitle && !employeeId) {
            Swal.fire('Error', 'Please select a Job Title or Employee to save template.', 'error');
            return;
        }

        const items = [];
        document.querySelectorAll('#templateItemsBody tr').forEach(tr => {
            const cat     = tr.querySelector('.tpl-category')?.value || 'Performance';
            const name    = tr.querySelector('.tpl-name')?.value || '';
            const weight  = parseFloat(tr.querySelector('.tpl-weight')?.value) || 0;
            const desc    = tr.querySelector('.tpl-desc')?.value || '';
            const isAuto  = tr.querySelector('.tpl-auto-att')?.checked ? 1 : 0;

            if (name.trim() !== '') {
                items.push({ category: cat, item_name: name, target_weight: weight, evaluation_criteria: desc, is_auto_attendance: isAuto });
            }
        });

        const fd = new FormData();
        fd.append('action', 'save_template');
        fd.append('job_title', jobTitle);
        fd.append('employee_id', employeeId);
        fd.append('items', JSON.stringify(items));

        const btn = document.getElementById('saveTemplateBtn');
        const orig = btn?.innerHTML;
        if (btn) { btn.disabled = true; btn.innerHTML = '<i data-lucide="loader-2" size="16" class="spin"></i> Saving...'; if (typeof lucide !== 'undefined') lucide.createIcons(); }

        fetch('/assets/api/kpi_handler.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(res => {
                if (btn) { btn.disabled = false; btn.innerHTML = orig; }
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Saved!', text: res.message, timer: 2000, showConfirmButton: false });
                    closeModal('kpiTemplateModal');
                } else { Swal.fire('Error', res.message, 'error'); }
            });
    });
}

function loadTemplateItemsForBuilder() {
    const jobTitle   = document.getElementById('templateJobTitleSelect')?.value || '';
    const employeeId = document.getElementById('templateEmployeeSelect')?.value || '';
    const tbody      = document.getElementById('templateItemsBody');

    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-20 text-light"><i data-lucide="loader-2" size="18" class="spin"></i> Loading template items...</td></tr>';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    fetch(`/assets/api/kpi_handler.php?action=fetch_template&job_title=${encodeURIComponent(jobTitle)}&employee_id=${employeeId}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                renderTemplateItemsEditor(res.data || []);
            }
        });
}

function renderTemplateItemsEditor(items) {
    const tbody = document.getElementById('templateItemsBody');
    tbody.innerHTML = '';

    if (!items || items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-20 text-light italic">No items in template. Click "Add Sub-Criteria Item" below to add.</td></tr>';
        updateTemplateTotalWeight();
        return;
    }

    items.forEach((item, idx) => {
        addTemplateItemRow(item);
    });

    updateTemplateTotalWeight();
}

window.addTemplateItemRow = function (item = null) {
    const tbody = document.getElementById('templateItemsBody');
    if (tbody.querySelector('.italic')) tbody.innerHTML = '';

    const cat        = item ? item.category : 'Performance';
    const name       = item ? item.item_name : '';
    const weight     = item ? parseFloat(item.target_weight || 0) : 10.00;
    const desc       = item ? (item.evaluation_criteria || '') : '';
    const isAutoAtt  = item && item.is_auto_attendance ? 'checked' : '';

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <select class="form-control font-13 tpl-category">
                <option value="Attendance & Discipline" ${cat === 'Attendance & Discipline' ? 'selected' : ''}>Attendance & Discipline</option>
                <option value="Performance" ${cat === 'Performance' ? 'selected' : ''}>Performance</option>
                <option value="Manager's Feedback" ${cat === "Manager's Feedback" ? 'selected' : ''}>Manager's Feedback</option>
                <option value="Soft Skills & Behavior" ${cat === 'Soft Skills & Behavior' ? 'selected' : ''}>Soft Skills & Behavior</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control font-13 tpl-name" placeholder="Item Name (e.g. Punctuality)" value="${escapeHtml(name)}">
        </td>
        <td>
            <div class="flex-center gap-4">
                <input type="number" class="form-control font-13 font-700 tpl-weight" min="0" max="100" step="0.5" value="${weight}" oninput="updateTemplateTotalWeight()" style="text-align:right;">
                <span class="font-12 text-light">%</span>
            </div>
        </td>
        <td>
            <input type="text" class="form-control font-13 tpl-desc" placeholder="Evaluation Description / Criteria" value="${escapeHtml(desc)}">
        </td>
        <td class="text-center">
            <input type="checkbox" class="tpl-auto-att" ${isAutoAtt} title="Check if this item is auto-calculated from attendance logs">
        </td>
        <td class="text-right">
            <button type="button" class="action-btn text-danger no-bg" onclick="this.closest('tr').remove(); updateTemplateTotalWeight();">
                <i data-lucide="trash-2" size="16"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    if (typeof lucide !== 'undefined') lucide.createIcons();
    updateTemplateTotalWeight();
};

window.updateTemplateTotalWeight = function () {
    let total = 0;
    document.querySelectorAll('.tpl-weight').forEach(inp => {
        total += parseFloat(inp.value) || 0;
    });

    total = Math.round(total * 100) / 100;
    const disp  = document.getElementById('templateTotalWeightDisplay');
    const badge = document.getElementById('templateWeightBadge');

    if (disp) disp.textContent = `${total.toFixed(2)}%`;

    if (badge) {
        if (Math.abs(total - 100) < 0.01) {
            badge.innerHTML = '<span class="badge badge-success font-12 px-12 py-6"><i data-lucide="check-circle" size="12" class="mr-4"></i> Complete (100%)</span>';
        } else {
            badge.innerHTML = `<span class="badge badge-warning font-12 px-12 py-6"><i data-lucide="alert-triangle" size="12" class="mr-4"></i> Total: ${total.toFixed(2)}% (Target: 100%)</span>`;
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
};

// ─── Modal Triggers ───────────────────────────────────────────────────────────
window.openAddReviewModal = function () {
    document.getElementById('addReviewForm')?.reset();
    document.getElementById('reviewIdInput').value = '';
    document.getElementById('modalEmployeeSelect').value = '';
    document.getElementById('dynamicCriteriaContainer').innerHTML = '<div class="p-30 text-center text-light italic border rounded-12 bg-light">Please select an employee above to load their job evaluation criteria.</div>';
    hideJobDesc();
    openModal('addReviewModal');
};

window.openTemplateModal = function () {
    openModal('kpiTemplateModal');
    const jobSelect = document.getElementById('templateJobTitleSelect');
    if (jobSelect && jobSelect.options.length > 1) {
        jobSelect.selectedIndex = 1;
        loadTemplateItemsForBuilder();
    }
};

window.openTemplateModalForSelectedEmp = function() {
    closeModal('addReviewModal');
    const empId = document.getElementById('modalEmployeeSelect')?.value;
    openModal('kpiTemplateModal');
    const empSelect = document.getElementById('templateEmployeeSelect');
    if (empSelect && empId) {
        empSelect.value = empId;
        loadTemplateItemsForBuilder();
    }
};

window.openModal = function (id) {
    document.getElementById(id)?.classList.add('active');
};

window.closeModal = function (id) {
    document.getElementById(id)?.classList.remove('active');
};

window.selectPeriod = function (el, label) {
    document.querySelectorAll('.period-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    const inp = document.getElementById('reviewPeriodInput');
    if (inp) inp.value = label;
};
