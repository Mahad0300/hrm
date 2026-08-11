/**
 * Dedicated Employee Performance Evaluation Page Logic (v10 - Compact Auto Calc Pill Button)
 */

let employeeData = [];
let currentLoadedTemplate = [];

document.addEventListener('DOMContentLoaded', function () {
    initEvaluatePage();
});

function initEvaluatePage() {
    const form = document.getElementById('addReviewForm');
    if (!form) return;

    fetch('/assets/api/kpi_handler.php?action=fetch_employees')
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') return;
            employeeData = res.data;

            setupEmployeeAutocomplete();

            // Check if editing an existing review or pre-selecting employee
            const paramReviewId = document.getElementById('paramReviewId')?.value;
            const paramEmpId    = document.getElementById('paramEmpId')?.value;

            if (paramReviewId) {
                loadExistingReview(paramReviewId);
            } else if (paramEmpId) {
                const emp = employeeData.find(e => String(e.id) === String(paramEmpId));
                if (emp) {
                    const name = [emp.first_name, emp.middle_name, emp.last_name].filter(Boolean).join(' ');
                    document.getElementById('employeeSearchInput').value = name;
                    document.getElementById('modalEmployeeSelect').value = emp.id;
                    triggerEmployeeSelected(emp.id);
                }
            }
        });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const empId = document.getElementById('modalEmployeeSelect')?.value;
        if (!empId) { Swal.fire('Error', 'Please search and select an employee first.', 'error'); return; }

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
        fd.append('period', document.querySelector('.scope-toggle-btn.active span')?.textContent?.trim() || 'Monthly');
        fd.append('items', JSON.stringify(items));

        const btn  = document.getElementById('submitReviewBtn');
        const orig = btn?.innerHTML;
        if (btn) { btn.disabled = true; btn.innerHTML = '<i data-lucide="loader-2" size="18" class="spin"></i> Submitting Review...'; if (typeof lucide !== 'undefined') lucide.createIcons(); }

        fetch('/assets/api/kpi_handler.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(res => {
                if (btn) { btn.disabled = false; btn.innerHTML = orig; }
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Performance Review Submitted!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = typeof window.HRM !== 'undefined' && window.HRM.url ? window.HRM.url('kpi') : '/kpi';
                    });
                } else { Swal.fire('Error', res.message, 'error'); }
            });
    });
}

// ─── Searchable Autocomplete for Employee ───────────────────────────────────
function setupEmployeeAutocomplete() {
    const input    = document.getElementById('employeeSearchInput');
    const dropdown = document.getElementById('employeeSuggestions');
    if (!input || !dropdown) return;

    function renderSuggestions(query = '') {
        const q = query.toLowerCase().trim();
        const filtered = employeeData.filter(e => {
            const name = [e.first_name, e.middle_name, e.last_name].filter(Boolean).join(' ').toLowerCase();
            const job  = (e.job_title || '').toLowerCase();
            return name.includes(q) || job.includes(q) || String(e.id).includes(q);
        });

        if (filtered.length === 0) {
            dropdown.innerHTML = '<div class="p-16 text-center text-light font-13">No matching employees found</div>';
            dropdown.style.display = 'block';
            return;
        }

        dropdown.innerHTML = filtered.map(e => {
            const name = [e.first_name, e.middle_name, e.last_name].filter(Boolean).join(' ');
            const label = name + (e.job_title ? ` (${e.job_title})` : '');
            return `
                <div class="search-suggestion-item" data-value="${e.id}" data-label="${escapeHtml(label)}">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <i data-lucide="user" size="14" style="color:#64748b;"></i>
                        <span style="font-size:13px; font-weight:600; color:#1e293b;">${escapeHtml(label)}</span>
                    </div>
                    <i data-lucide="chevron-right" size="14" style="color:#cbd5e1;"></i>
                </div>
            `;
        }).join('');

        dropdown.style.display = 'block';
        if (typeof lucide !== 'undefined') lucide.createIcons();

        dropdown.querySelectorAll('.search-suggestion-item').forEach(el => {
            el.addEventListener('click', function () {
                const val = this.dataset.value;
                const label = this.dataset.label;
                input.value = label;
                document.getElementById('modalEmployeeSelect').value = val;
                dropdown.style.display = 'none';

                triggerEmployeeSelected(val);
            });
        });
    }

    input.addEventListener('focus', function () {
        renderSuggestions(this.value);
    });

    input.addEventListener('input', function () {
        renderSuggestions(this.value);
    });

    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

function triggerEmployeeSelected(empId) {
    const emp = employeeData.find(e => String(e.id) === String(empId));
    if (emp) {
        document.getElementById('jobDescRoleLabel').textContent = `JOB DESCRIPTION (${(emp.job_title || 'EMPLOYEE').toUpperCase()})`;
        if (emp.job_description) {
            document.getElementById('jobDescText').textContent = emp.job_description;
            document.getElementById('jobDescBox').style.display = 'block';
        } else {
            hideJobDesc();
        }

        const editLink = document.getElementById('editRoleTemplateLink');
        if (editLink) {
            editLink.href = (window.HRM && window.HRM.url ? window.HRM.url('kpi/templates') : '/kpi/templates') + `?job_title=${encodeURIComponent(emp.job_title || '')}&employee_id=${emp.id}`;
        }
    }
    loadTemplateForReviewModal(empId, emp?.job_title);
}

function loadExistingReview(reviewId) {
    fetch(`/assets/api/kpi_handler.php?action=fetch_review_details&id=${reviewId}`)
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') { Swal.fire('Error', res.message, 'error'); return; }
            const d = res.data;
            document.getElementById('reviewIdInput').value = d.id;
            document.getElementById('modalEmployeeSelect').value = d.employee_id;
            document.getElementById('reviewPeriodMonth').value = d.period_month;
            document.getElementById('evaluatePageTitle').textContent = 'Edit Performance Review';

            const emp = employeeData.find(e => String(e.id) === String(d.employee_id));
            if (emp) {
                const name = [emp.first_name, emp.middle_name, emp.last_name].filter(Boolean).join(' ');
                document.getElementById('employeeSearchInput').value = name + (emp.job_title ? ` (${emp.job_title})` : '');
            }

            loadTemplateForReviewModal(d.employee_id, emp?.job_title, d.items);
        });
}

function hideJobDesc() {
    const box = document.getElementById('jobDescBox');
    if (box) box.style.display = 'none';
}

function loadTemplateForReviewModal(empId, jobTitle, existingItems = null) {
    const container = document.getElementById('dynamicCriteriaContainer');
    container.innerHTML = '<div class="card p-30 text-center text-light" style="background:#fff; border-radius:14px; border:1px solid #e2e8f0;"><i data-lucide="loader-2" size="20" class="spin"></i> Loading evaluation questions...</div>';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    if (existingItems && existingItems.length > 0) {
        renderDynamicCriteriaForm(existingItems);
        return;
    }

    fetch(`/assets/api/kpi_handler.php?action=fetch_template&employee_id=${empId}&job_title=${encodeURIComponent(jobTitle || '')}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data && res.data.length > 0) {
                currentLoadedTemplate = res.data;
                renderDynamicCriteriaForm(res.data);
            } else {
                container.innerHTML = '<div class="card p-30 text-center text-warning border rounded-12 bg-light" style="padding:30px; text-align:center; color:#d97706; background:#fffbe8; border:1px solid #fef3c7;">No evaluation criteria template found for this role. Click "Edit Role Template Questions" above to set up questions.</div>';
            }
        });
}

function renderDynamicCriteriaForm(items) {
    const container = document.getElementById('dynamicCriteriaContainer');
    container.innerHTML = '';

    const categories = {};
    items.forEach((item, idx) => {
        const cat = item.category || 'Performance';
        if (!categories[cat]) categories[cat] = [];
        categories[cat].push({ ...item, orig_index: idx });
    });

    Object.keys(categories).forEach(cat => {
        const catItems = categories[cat];
        const catTotalMax = catItems.reduce((sum, i) => sum + parseFloat(i.target_weight || 0), 0);

        const card = document.createElement('div');
        card.className = 'card mb-20 tpl-category-section';
        card.dataset.category = cat;

        let rowsHtml = '';
        catItems.forEach(item => {
            const itemId       = `item_score_${item.orig_index}`;
            const commentId    = `item_comment_${item.orig_index}`;
            const targetWeight = parseFloat(item.target_weight || 0);
            const achievedVal  = item.achieved_score !== undefined ? parseFloat(item.achieved_score) : targetWeight;

            rowsHtml += `
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:12px 14px; vertical-align:middle;">
                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                            <span style="font-size:13px; font-weight:700; color:#0f172a;">${escapeHtml(item.item_name)}</span>
                            ${item.is_auto_attendance ? `
                                <button type="button" class="btn-ghost font-10" style="color:#059669; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; font-size:10px; font-weight:700; padding:2px 8px; height:24px; display:inline-flex; align-items:center; gap:3px; cursor:pointer;" onclick="autoCalcAttendanceItem('${itemId}', ${targetWeight})" title="Click to auto-calculate score from attendance logs">
                                    <i data-lucide="zap" size="10"></i> <span>Auto Calc</span>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                    <td style="padding:12px 14px; vertical-align:middle; text-align:center;">
                        <span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; font-size:12px; font-weight:700; padding:4px 10px; border-radius:12px;">${targetWeight}%</span>
                    </td>
                    <td style="padding:12px 14px; vertical-align:middle;">
                        <span style="font-size:12px; color:#64748b;">${escapeHtml(item.evaluation_criteria || '--')}</span>
                    </td>
                    <td style="padding:12px 14px; vertical-align:middle; text-align:center;">
                        <div style="display:inline-flex; align-items:center; justify-content:center; gap:6px;">
                            <input type="number" class="form-control font-14 font-700 kpi-score-input"
                                   id="${itemId}" data-target-weight="${targetWeight}"
                                   data-category="${escapeHtml(cat)}" data-name="${escapeHtml(item.item_name)}"
                                   data-criteria="${escapeHtml(item.evaluation_criteria || '')}"
                                   min="0" max="${targetWeight}" step="0.01" value="${achievedVal}"
                                   style="width:75px; height:36px; text-align:center; border-radius:8px; font-size:14px; font-weight:700; padding:0 4px;" oninput="recalcOverall()">
                            <span class="font-12 font-700" style="font-size:12px; font-weight:700; color:#64748b; width:35px; text-align:left;">/ ${targetWeight}</span>
                        </div>
                    </td>
                    <td style="padding:12px 14px; vertical-align:middle;">
                        <input type="text" id="${commentId}" class="form-control font-12 kpi-comment-input" placeholder="Evaluation notes / comments..." value="${escapeHtml(item.comment || '')}" style="width:100%; height:36px; border-radius:6px; padding:0 12px; font-size:12px;">
                    </td>
                </tr>
            `;
        });

        card.innerHTML = `
            <!-- HRM Theme Slate Header Banner -->
            <div class="tpl-category-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="font-15 font-700 text-dark" style="font-size:15px; font-weight:700; color:#0f172a;">${escapeHtml(cat)}</span>
                    <span class="badge category-subtotal-badge" style="background:#f1f5f9; color:#6c4cf1; border:1px solid #e2e8f0; font-size:12px; font-weight:700; padding:4px 12px; border-radius:20px;">Max Target: ${catTotalMax.toFixed(2)}%</span>
                </div>
                <div class="font-13 font-700 text-primary-color" id="cat_achieved_${escapeHtml(cat).replace(/\s+/g, '_')}">Subtotal: ${catTotalMax.toFixed(2)} pts</div>
            </div>

            <!-- Table of Items for this Category -->
            <div class="table-responsive">
                <table class="data-table font-13" style="width:100%; border-collapse:collapse;">
                    <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                        <tr>
                            <th style="width:24%; padding:12px 14px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">ITEM / QUESTION NAME</th>
                            <th style="width:10%; padding:12px 14px; text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">TARGET WEIGHT</th>
                            <th style="width:26%; padding:12px 14px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">EVALUATION GUIDELINES</th>
                            <th style="width:16%; padding:12px 14px; text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">ACHIEVED SCORE</th>
                            <th style="width:24%; padding:12px 14px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">NOTES / COMMENTS</th>
                        </tr>
                    </thead>
                    <tbody class="category-items-tbody">
                        ${rowsHtml}
                    </tbody>
                    <!-- HRM Theme Category Subtotal Footer Row -->
                    <tfoot style="background:#f8fafc; border-top:1px solid #e2e8f0;">
                        <tr>
                            <td colspan="3" style="padding:12px 14px; font-weight:700; color:#334155; font-size:13px; vertical-align:middle;">Total ${escapeHtml(cat)} Score</td>
                            <td style="padding:12px 14px; text-align:center; font-weight:800; color:#6c4cf1; font-size:14px; vertical-align:middle;" class="category-total-cell" id="cat_foot_${escapeHtml(cat).replace(/\s+/g, '_')}">0.00 / ${catTotalMax.toFixed(2)}</td>
                            <td style="padding:12px 14px; text-align:right; font-size:12px; color:#64748b; font-style:italic; vertical-align:middle;">Module score sum</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
        container.appendChild(card);
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
    recalcOverall();
}

window.autoCalcAttendanceItem = function (inputId, targetWeight) {
    const empId = document.getElementById('modalEmployeeSelect')?.value;
    const month = document.getElementById('reviewPeriodMonth')?.value || new Date().toISOString().slice(0, 7);
    if (!empId) { Swal.fire('Notice', 'Please search and select an employee first.', 'info'); return; }

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

window.recalcOverall = function () {
    const scoreInputs = document.querySelectorAll('.kpi-score-input');
    let totalScore = 0;

    const catTotals = {};

    scoreInputs.forEach(inp => {
        const val = parseFloat(inp.value) || 0;
        const max = parseFloat(inp.dataset.targetWeight) || 0;
        const cat = inp.dataset.category || 'Performance';
        const cleanVal = Math.min(max, Math.max(0, val));
        
        totalScore += cleanVal;
        if (!catTotals[cat]) catTotals[cat] = { achieved: 0, max: 0 };
        catTotals[cat].achieved += cleanVal;
        catTotals[cat].max += max;
    });

    // Update Category Footers & Header badges
    Object.keys(catTotals).forEach(cat => {
        const key = cat.replace(/\s+/g, '_');
        const foot = document.getElementById(`cat_foot_${key}`);
        const head = document.getElementById(`cat_achieved_${key}`);

        const achieved = Math.round(catTotals[cat].achieved * 100) / 100;
        const max      = Math.round(catTotals[cat].max * 100) / 100;

        if (foot) foot.textContent = `${achieved.toFixed(2)} / ${max.toFixed(2)}`;
        if (head) head.textContent = `Score: ${achieved.toFixed(2)} / ${max.toFixed(2)} pts`;
    });

    totalScore = Math.round(totalScore * 100) / 100;

    const display = document.getElementById('overallScoreDisplay');
    const grade   = document.getElementById('overallGradeDisplay');

    if (display) display.innerHTML = `${totalScore} <span class="font-16 font-400 text-light" style="font-size:16px; font-weight:400; color:#64748b;">/ 100</span>`;

    let gradeText = 'Grade C (Needs Improvement)', gradeClass = 'warning';
    if (totalScore >= 95)      { gradeText = 'Grade A (Excelling)'; gradeClass = 'success'; }
    else if (totalScore >= 85) { gradeText = 'Grade B (Good)';      gradeClass = 'primary'; }
    else if (totalScore >= 70) { gradeText = 'Grade C (On Track)';  gradeClass = 'warning'; }
    else                       { gradeText = 'Grade D (Needs Action)'; gradeClass = 'danger'; }

    if (grade) { 
        grade.textContent = gradeText; 
        if (gradeClass === 'success') {
            grade.style.background = '#10b981'; grade.style.color = '#fff';
        } else if (gradeClass === 'primary') {
            grade.style.background = '#6c4cf1'; grade.style.color = '#fff';
        } else if (gradeClass === 'warning') {
            grade.style.background = '#f59e0b'; grade.style.color = '#fff';
        } else {
            grade.style.background = '#ef4444'; grade.style.color = '#fff';
        }
    }
};

window.selectPeriod = function (el, label) {
    document.querySelectorAll('.scope-toggle-btn').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    const inp = document.getElementById('reviewPeriodInput');
    if (inp) inp.value = label;
};

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
