/**
 * Dedicated KPI Templates Page Logic (v12 - Refined Dropdown Icons & Pill Switch)
 */

let employeeData = [];
let jobTitleList = [];
let activeScopeMode = 'job_role';

document.addEventListener('DOMContentLoaded', function () {
    initTemplatePage();
});

function initTemplatePage() {
    const form = document.getElementById('kpiTemplateForm');

    fetch('/assets/api/kpi_handler.php?action=fetch_employees')
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') return;
            employeeData = res.data;

            jobTitleList = (res.job_titles && res.job_titles.length > 0) ? res.job_titles : [...new Set(res.data.map(e => e.job_title).filter(Boolean))];

            setupTargetAutocomplete();

            // Check URL parameters for pre-selected job_title or employee_id
            const urlParams = new URLSearchParams(window.location.search);
            const preJob    = urlParams.get('job_title');
            const preEmp    = urlParams.get('employee_id');

            if (preEmp) {
                switchScopeMode('employee');
                const emp = employeeData.find(e => String(e.id) === String(preEmp));
                if (emp) {
                    const name = [emp.first_name, emp.middle_name, emp.last_name].filter(Boolean).join(' ');
                    document.getElementById('targetSearchInput').value = name;
                    document.getElementById('templateEmployeeSelect').value = emp.id;
                    loadTemplateItemsForBuilder();
                } else {
                    renderTemplateItemsEditor([]);
                }
            } else if (preJob) {
                switchScopeMode('job_role');
                const match = jobTitleList.find(t => t.toLowerCase() === preJob.toLowerCase());
                if (match) {
                    document.getElementById('targetSearchInput').value = match;
                    document.getElementById('templateJobTitleSelect').value = match;
                    loadTemplateItemsForBuilder();
                } else {
                    renderTemplateItemsEditor([]);
                }
            } else {
                switchScopeMode('job_role');
                renderTemplateItemsEditor([]);
            }
        });

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const jobTitle   = activeScopeMode === 'job_role' ? document.getElementById('templateJobTitleSelect').value : '';
            const employeeId = activeScopeMode === 'employee' ? document.getElementById('templateEmployeeSelect').value : '';

            if (!jobTitle && !employeeId) {
                Swal.fire('Error', 'Please search and select a Job Role or Employee target.', 'error');
                return;
            }

            const items = collectTemplateItemsFromUI();

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
                        Swal.fire({
                            icon: 'success',
                            title: 'Template Saved!',
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
}

// ─── Single Unified Target Autocomplete Engine ──────────────────────────────
function setupTargetAutocomplete() {
    const input    = document.getElementById('targetSearchInput');
    const dropdown = document.getElementById('targetSuggestions');
    if (!input || !dropdown) return;

    function renderSuggestions(query = '') {
        const q = query.toLowerCase().trim();

        let rawItems = [];
        if (activeScopeMode === 'job_role') {
            rawItems = jobTitleList.map(t => ({ value: t, label: t, icon: 'briefcase' }));
        } else {
            rawItems = employeeData.map(e => {
                const name = [e.first_name, e.middle_name, e.last_name].filter(Boolean).join(' ');
                return { value: e.id, label: name + (e.job_title ? ` (${e.job_title})` : ''), icon: 'user' };
            });
        }

        const filtered = rawItems.filter(i => String(i.label).toLowerCase().includes(q));

        if (filtered.length === 0) {
            dropdown.innerHTML = '<div class="p-16 text-center text-light font-13">No matching results found</div>';
            dropdown.style.display = 'block';
            return;
        }

        dropdown.innerHTML = filtered.map(i => `
            <div class="search-suggestion-item" data-value="${escapeHtml(String(i.value))}" data-label="${escapeHtml(String(i.label))}">
                <div style="display:flex; align-items:center; gap:10px;">
                    <i data-lucide="${i.icon || 'circle'}" size="14" style="color:#64748b;"></i>
                    <span style="font-size:13px; font-weight:600; color:#1e293b;">${escapeHtml(String(i.label))}</span>
                </div>
                <i data-lucide="chevron-right" size="14" style="color:#cbd5e1;"></i>
            </div>
        `).join('');

        dropdown.style.display = 'block';
        if (typeof lucide !== 'undefined') lucide.createIcons();

        dropdown.querySelectorAll('.search-suggestion-item').forEach(el => {
            el.addEventListener('click', function () {
                const val = this.dataset.value;
                const label = this.dataset.label;
                input.value = label;
                dropdown.style.display = 'none';

                if (activeScopeMode === 'job_role') {
                    document.getElementById('templateJobTitleSelect').value = val;
                    document.getElementById('templateEmployeeSelect').value = '';
                } else {
                    document.getElementById('templateEmployeeSelect').value = val;
                    document.getElementById('templateJobTitleSelect').value = '';
                }

                loadTemplateItemsForBuilder();
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

// ─── Scope Mode Tabs Switch ──────────────────────────────────────────────────
window.switchScopeMode = function(mode) {
    activeScopeMode = mode;
    const tabJob = document.getElementById('tabJobRole');
    const tabEmp = document.getElementById('tabEmployee');
    const input  = document.getElementById('targetSearchInput');

    if (mode === 'job_role') {
        tabJob.className = 'scope-toggle-btn active';
        tabEmp.className = 'scope-toggle-btn';
        if (input) input.placeholder = 'Search Job Role (e.g. HR Executive, Web Developer)...';
    } else {
        tabEmp.className = 'scope-toggle-btn active';
        tabJob.className = 'scope-toggle-btn';
        if (input) input.placeholder = 'Search Employee Name or ID (e.g. Syeda Bisma, Zain)...';
    }

    if (input) input.value = '';
    document.getElementById('templateJobTitleSelect').value = '';
    document.getElementById('templateEmployeeSelect').value = '';

    if (typeof lucide !== 'undefined') lucide.createIcons();
    renderTemplateItemsEditor([]);
};

function loadTemplateItemsForBuilder() {
    const jobTitle   = activeScopeMode === 'job_role' ? document.getElementById('templateJobTitleSelect')?.value || '' : '';
    const employeeId = activeScopeMode === 'employee' ? document.getElementById('templateEmployeeSelect')?.value || '' : '';
    const container  = document.getElementById('categoriesContainer');

    if (!jobTitle && !employeeId) {
        renderTemplateItemsEditor([]);
        return;
    }

    container.innerHTML = '<div class="card p-30 text-center text-light" style="background:#fff; border-radius:14px; border:1px solid #e2e8f0;"><i data-lucide="loader-2" size="20" class="spin"></i> Loading criteria questions...</div>';
    if (typeof lucide !== 'undefined') lucide.createIcons();

    fetch(`/assets/api/kpi_handler.php?action=fetch_template&job_title=${encodeURIComponent(jobTitle)}&employee_id=${employeeId}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data && res.data.length > 0) {
                renderTemplateItemsEditor(res.data);
            } else {
                renderTemplateItemsEditor([]);
            }
        });
}

function renderTemplateItemsEditor(items) {
    const container = document.getElementById('categoriesContainer');
    container.innerHTML = '';

    const categories = {};
    if (items && items.length > 0) {
        items.forEach(item => {
            const cat = item.category || 'Performance';
            if (!categories[cat]) categories[cat] = [];
            categories[cat].push(item);
        });
    }

    // Always ensure standard categories exist so HR sees clean category cards
    const defaultCategories = ['Attendance & Discipline', 'Performance', "Manager's Feedback"];
    defaultCategories.forEach(cat => {
        if (!categories[cat]) categories[cat] = [];
    });

    Object.keys(categories).forEach(cat => {
        const catItems = categories[cat];
        const isAttendanceCat = cat.toLowerCase().includes('attendance');

        const card = document.createElement('div');
        card.className = 'card mb-20 tpl-category-section';
        card.dataset.category = cat;

        let rowsHtml = '';
        catItems.forEach(item => {
            rowsHtml += createQuestionRowHtml(item, isAttendanceCat);
        });

        const tableHeaderHtml = isAttendanceCat ? `
            <tr>
                <th style="width:32%; padding:12px 14px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">ITEM / QUESTION NAME</th>
                <th style="width:15%; padding:12px 14px; text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">TARGET WEIGHT (%)</th>
                <th style="width:38%; padding:12px 14px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">EVALUATION GUIDELINES / DESCRIPTION</th>
                <th style="width:8%;  padding:12px 8px;  text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">AUTO ATT.</th>
                <th style="width:7%;  padding:12px 8px;  text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">ACTION</th>
            </tr>
        ` : `
            <tr>
                <th style="width:35%; padding:12px 14px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">ITEM / QUESTION NAME</th>
                <th style="width:15%; padding:12px 14px; text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">TARGET WEIGHT (%)</th>
                <th style="width:43%; padding:12px 14px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">EVALUATION GUIDELINES / DESCRIPTION</th>
                <th style="width:7%;  padding:12px 8px;  text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">ACTION</th>
            </tr>
        `;

        const totalCols = isAttendanceCat ? 5 : 4;
        const rightColspan = isAttendanceCat ? 3 : 2;

        card.innerHTML = `
            <!-- HRM Theme Slate Header Banner -->
            <div class="tpl-category-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="font-15 font-700 text-dark" style="font-size:15px; font-weight:700; color:#0f172a;">${escapeHtml(cat)}</span>
                    <span class="badge category-subtotal-badge" style="background:#f1f5f9; color:#6c4cf1; border:1px solid #e2e8f0; font-size:12px; font-weight:700; padding:4px 12px; border-radius:20px;">0.00%</span>
                </div>
                <button type="button" class="btn-ghost font-12 font-600 px-14 py-6" style="color:#6c4cf1; background:#ffffff; border-radius:8px; display:inline-flex; align-items:center; gap:6px; border:1px solid #6c4cf1;" onclick="addQuestionToCategory(this)">
                    <i data-lucide="plus" size="14"></i> Add Question
                </button>
            </div>

            <!-- Table of Items for this Category -->
            <div class="table-responsive">
                <table class="data-table font-13" style="width:100%; border-collapse:collapse;">
                    <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                        ${tableHeaderHtml}
                    </thead>
                    <tbody class="category-items-tbody">
                        ${rowsHtml || `<tr><td colspan="${totalCols}" class="text-center py-20 text-light italic" style="padding:20px; text-align:center; color:#64748b; font-style:italic;">No questions in this section yet. Click "+ Add Question" above to add items.</td></tr>`}
                    </tbody>
                    <!-- HRM Theme Category Subtotal Row -->
                    <tfoot style="background:#f8fafc; border-top:1px solid #e2e8f0;">
                        <tr>
                            <td style="padding:12px 14px; font-weight:700; color:#334155; font-size:13px; vertical-align:middle;">Total ${escapeHtml(cat)} Target Weight</td>
                            <td style="padding:12px 14px; text-align:center; font-weight:800; color:#6c4cf1; font-size:14px; vertical-align:middle;" class="category-total-cell">0.00%</td>
                            <td colspan="${rightColspan}" style="padding:12px 14px; text-align:right; font-size:12px; color:#64748b; font-style:italic; vertical-align:middle;">Subtotal module target weight sum</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
        container.appendChild(card);
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
    updateTemplateTotalWeight();
}

function createQuestionRowHtml(item = null, isAttendanceCat = false) {
    const name       = item ? item.item_name : '';
    const weight     = item ? parseFloat(item.target_weight || 0) : 10.00;
    const desc       = item ? (item.evaluation_criteria || '') : '';
    const isAutoAtt  = item && item.is_auto_attendance ? 'checked' : '';

    const autoAttTd = isAttendanceCat ? `
        <td style="padding:12px 8px; vertical-align:middle; text-align:center;">
            <input type="checkbox" class="tpl-auto-att" ${isAutoAtt} style="width:18px; height:18px; margin:0 auto; display:block; cursor:pointer; accent-color:#6c4cf1;" title="Check if this item is auto-calculated from attendance logs">
        </td>
    ` : '';

    return `
        <tr style="border-bottom:1px solid #f1f5f9;" class="tpl-item-row">
            <td style="padding:12px 14px; vertical-align:middle;">
                <input type="text" class="form-control font-13 tpl-name font-600" placeholder="Question / Item Name (e.g. Punctuality)" value="${escapeHtml(name)}" style="width:100%; height:40px; border-radius:8px;">
            </td>
            <td style="padding:12px 14px; vertical-align:middle; text-align:center;">
                <div style="display:inline-flex; align-items:center; justify-content:center; gap:4px;">
                    <input type="number" class="form-control font-13 font-700 tpl-weight" min="0" max="100" step="0.5" value="${weight}" oninput="updateTemplateTotalWeight()" style="width:80px; height:40px; text-align:right; border-radius:8px;">
                    <span class="font-12 text-light font-600">%</span>
                </div>
            </td>
            <td style="padding:12px 14px; vertical-align:middle;">
                <input type="text" class="form-control font-13 tpl-desc" placeholder="Evaluation Criteria / Guidelines for Evaluator" value="${escapeHtml(desc)}" style="width:100%; height:40px; border-radius:8px;">
            </td>
            ${autoAttTd}
            <td style="padding:12px 8px; vertical-align:middle; text-align:center;">
                <button type="button" class="action-btn text-danger no-bg" title="Delete Question" onclick="this.closest('tr').remove(); updateTemplateTotalWeight();" style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:8px; border:1px solid #fee2e2; background:#fef2f2; color:#ef4444; cursor:pointer;">
                    <i data-lucide="trash-2" size="16"></i>
                </button>
            </td>
        </tr>
    `;
}

window.addQuestionToCategory = function(btn, categoryName) {
    const card  = btn.closest('.tpl-category-section');
    if (!card) return;
    const catName = categoryName || card.dataset.category || '';
    const tbody = card.querySelector('.category-items-tbody');
    if (!tbody) return;
    if (tbody.querySelector('.italic')) tbody.innerHTML = '';

    const isAttendanceCat = catName.toLowerCase().includes('attendance');
    const newRowHtml = createQuestionRowHtml({ category: catName, item_name: '', target_weight: 10.00, evaluation_criteria: '' }, isAttendanceCat);
    tbody.insertAdjacentHTML('beforeend', newRowHtml);
    if (typeof lucide !== 'undefined') lucide.createIcons();
    updateTemplateTotalWeight();
};

window.addCustomCategorySection = function() {
    Swal.fire({
        title: 'Add New Category Section',
        input: 'text',
        inputPlaceholder: 'Enter Category Name (e.g. Leadership & Initiative)',
        showCancelButton: true,
        confirmButtonText: 'Add Category'
    }).then(res => {
        if (res.isConfirmed && res.value.trim() !== '') {
            const catName = res.value.trim();
            const items = collectTemplateItemsFromUI();
            items.push({ category: catName, item_name: '', target_weight: 10.00, evaluation_criteria: '' });
            renderTemplateItemsEditor(items);
        }
    });
};

window.loadDefaultHrPreset = function() {
    const hrPresetItems = [
        { category: 'Attendance & Discipline', item_name: 'Attendance Record', target_weight: 15.00, evaluation_criteria: 'Monthly attendance percentage', is_auto_attendance: 1 },
        { category: 'Attendance & Discipline', item_name: 'Punctuality', target_weight: 10.00, evaluation_criteria: 'No late arrivals / shift adherence', is_auto_attendance: 0 },
        { category: 'Attendance & Discipline', item_name: 'Leave Management', target_weight: 5.00, evaluation_criteria: 'Leaves approved as per policy', is_auto_attendance: 0 },
        { category: 'Attendance & Discipline', item_name: 'Policy Compliance', target_weight: 5.00, evaluation_criteria: 'No policy violations or warnings', is_auto_attendance: 0 },
        { category: 'Attendance & Discipline', item_name: 'Time Management', target_weight: 5.00, evaluation_criteria: 'Break timings, availability & adherence', is_auto_attendance: 0 },

        { category: 'Performance', item_name: 'Hiring Achievement', target_weight: 10.00, evaluation_criteria: 'Assigned hiring targets achieved within timeline.', is_auto_attendance: 0 },
        { category: 'Performance', item_name: 'Documentation Accuracy', target_weight: 10.00, evaluation_criteria: 'Accurate and timely HR documentation.', is_auto_attendance: 0 },
        { category: 'Performance', item_name: 'Onboarding & Employee Coordination', target_weight: 10.00, evaluation_criteria: 'Smooth onboarding and effective employee coordination.', is_auto_attendance: 0 },

        { category: "Manager's Feedback", item_name: 'Teamwork & Collaboration', target_weight: 10.00, evaluation_criteria: 'Works effectively with team and departments.', is_auto_attendance: 0 },
        { category: "Manager's Feedback", item_name: 'Learning Attitude & Adaptability', target_weight: 10.00, evaluation_criteria: 'Accepts feedback and adapts to changes.', is_auto_attendance: 0 },
        { category: "Manager's Feedback", item_name: 'Employee Engagement', target_weight: 10.00, evaluation_criteria: 'Maintains positive employee relations and addresses concerns effectively.', is_auto_attendance: 0 },
    ];

    if (activeScopeMode === 'job_role' && !document.getElementById('templateJobTitleSelect').value) {
        document.getElementById('targetSearchInput').value = 'HR Executive';
        document.getElementById('templateJobTitleSelect').value = 'HR Executive';
    }

    renderTemplateItemsEditor(hrPresetItems);
};

function collectTemplateItemsFromUI() {
    const items = [];
    document.querySelectorAll('.tpl-category-section').forEach(card => {
        const cat = card.dataset.category || 'Performance';
        card.querySelectorAll('.tpl-item-row').forEach(tr => {
            const name   = tr.querySelector('.tpl-name')?.value || '';
            const weight = parseFloat(tr.querySelector('.tpl-weight')?.value) || 0;
            const desc   = tr.querySelector('.tpl-desc')?.value || '';
            const isAuto = tr.querySelector('.tpl-auto-att')?.checked ? 1 : 0;

            if (name.trim() !== '') {
                items.push({ category: cat, item_name: name, target_weight: weight, evaluation_criteria: desc, is_auto_attendance: isAuto });
            }
        });
    });
    return items;
}

window.updateTemplateTotalWeight = function () {
    let grandTotal = 0;

    document.querySelectorAll('.tpl-category-section').forEach(card => {
        let catTotal = 0;
        card.querySelectorAll('.tpl-weight').forEach(inp => {
            catTotal += parseFloat(inp.value) || 0;
        });
        catTotal = Math.round(catTotal * 100) / 100;
        grandTotal += catTotal;

        const catBadge = card.querySelector('.category-subtotal-badge');
        const catCell  = card.querySelector('.category-total-cell');

        if (catBadge) catBadge.textContent = `${catTotal.toFixed(2)}%`;
        if (catCell)  catCell.textContent  = `${catTotal.toFixed(2)}%`;
    });

    grandTotal = Math.round(grandTotal * 100) / 100;
    const disp  = document.getElementById('templateTotalWeightDisplay');
    const badge = document.getElementById('templateWeightBadge');

    if (disp) disp.textContent = `${grandTotal.toFixed(2)}%`;

    if (badge) {
        if (Math.abs(grandTotal - 100) < 0.01) {
            badge.innerHTML = '<span class="badge badge-success font-12 px-14 py-6" style="background:#10b981; color:#fff; border-radius:20px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:6px;"><i data-lucide="check-circle" size="14"></i> Complete (100%)</span>';
        } else {
            badge.innerHTML = `<span class="badge badge-warning font-12 px-14 py-6" style="background:#f59e0b; color:#fff; border-radius:20px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:6px;"><i data-lucide="alert-triangle" size="14"></i> Total: ${grandTotal.toFixed(2)}% (Needs 100%)</span>`;
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
};

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
