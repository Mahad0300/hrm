/**
 * KPI Report Logic - Employee Scorecard Page (v6 - Category Cards & Sleek Timeline)
 */

let currentEmpId = null;

document.addEventListener('DOMContentLoaded', function () {
    const empId = document.getElementById('currentEmpId')?.value || new URLSearchParams(window.location.search).get('id');
    currentEmpId = empId;

    if (!empId) {
        Swal.fire('Error', 'No employee ID provided.', 'error').then(() => {
            window.location.href = typeof window.HRM !== 'undefined' && window.HRM.url ? window.HRM.url('kpi') : '/kpi';
        });
        return;
    }

    fetchReportData(empId);
});

function fetchReportData(id) {
    fetch(`/assets/api/kpi_handler.php?action=fetch_report_data&id=${id}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                renderProfile(res.employee);
                renderLatestScorecard(res.history[0]);
                renderHistory(res.history);
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
}

function renderProfile(emp) {
    const name = [emp.first_name, emp.middle_name, emp.last_name].filter(Boolean).join(' ');
    document.getElementById('detailName').textContent = name || 'Employee';
    document.getElementById('detailDept').textContent = (emp.job_title ? `${emp.job_title} — ` : '') + (emp.dept_name || 'Unassigned');

    const addBtn = document.getElementById('addNewEvalBtn');
    if (addBtn) {
        addBtn.href = (window.HRM && window.HRM.url ? window.HRM.url('kpi/evaluate') : '/kpi/evaluate') + `?employee_id=${emp.id}`;
    }

    const avatarEl = document.getElementById('detailAvatar');
    const defaultAvatar = (window.HRM && window.HRM.url ? window.HRM.url('assets/images/profile-image/default-avatar.svg') : '/assets/images/profile-image/default-avatar.svg');
    if (emp.profile_pic) {
        avatarEl.src = emp.profile_pic.startsWith('http') ? emp.profile_pic : '/' + emp.profile_pic;
    } else {
        avatarEl.src = defaultAvatar;
    }
    avatarEl.onerror = () => { avatarEl.onerror = null; avatarEl.src = defaultAvatar; };

    const jdBox  = document.getElementById('jobDescriptionBox');
    const jdText = document.getElementById('jobDescriptionText');
    if (jdBox && jdText && emp.job_description) {
        jdText.innerText = emp.job_description;
        jdBox.style.display = 'block';
    }
}

function renderLatestScorecard(lastReview) {
    const container = document.getElementById('detailGoalsContainer');
    container.innerHTML = '';

    const items = (lastReview && lastReview.items) ? lastReview.items : (lastReview ? lastReview.goals : []);

    if (!lastReview || !items || items.length === 0) {
        container.innerHTML = '<div class="card p-35 text-center text-light" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; text-align:center; padding:35px; color:#64748b;">No performance evaluations recorded for this employee yet. Click "Add New Evaluation" above to evaluate.</div>';
        document.getElementById('detailScore').textContent = '0%';
        const statusBadge = document.getElementById('detailStatus');
        statusBadge.textContent = 'Not Rated';
        statusBadge.style.background = '#f1f5f9';
        statusBadge.style.color = '#64748b';
        document.getElementById('detailLastPeriod').textContent = 'Never';
        return;
    }

    const overallPct = lastReview.overall_pct || Math.round(parseFloat(lastReview.overall_rating || 0) * 20);
    document.getElementById('detailScore').textContent = overallPct + '%';
    document.getElementById('detailLastPeriod').textContent = lastReview.period_month || lastReview.period || '--';

    const statusBadge = document.getElementById('detailStatus');
    const grade = lastReview.grade || lastReview.status || '';
    statusBadge.textContent = grade;
    if (overallPct >= 95) {
        statusBadge.style.background = '#ecfdf5'; statusBadge.style.color = '#059669'; statusBadge.style.border = '1px solid #a7f3d0';
    } else if (overallPct >= 85) {
        statusBadge.style.background = '#f5f3ff'; statusBadge.style.color = '#6c4cf1'; statusBadge.style.border = '1px solid #ddd6fe';
    } else {
        statusBadge.style.background = '#fffbe8'; statusBadge.style.color = '#d97706'; statusBadge.style.border = '1px solid #fef3c7';
    }

    // Group items by category
    const categories = {};
    items.forEach(it => {
        const cat = it.category || 'Performance';
        if (!categories[cat]) categories[cat] = [];
        categories[cat].push(it);
    });

    Object.keys(categories).forEach(cat => {
        const catItems = categories[cat];
        const catTotalAchieved = catItems.reduce((sum, i) => sum + parseFloat(i.achieved_score || 0), 0);
        const catTotalWeight   = catItems.reduce((sum, i) => sum + parseFloat(i.target_weight || i.weight || 0), 0);
        const catPct = catTotalWeight > 0 ? Math.round((catTotalAchieved / catTotalWeight) * 100) : 0;

        const card = document.createElement('div');
        card.className = 'card mb-20 tpl-category-section';

        let rowsHtml = '';
        catItems.forEach(item => {
            const name     = item.item_name || item.goal_name || 'Criteria Question';
            const desc     = item.evaluation_criteria || '--';
            const weight   = parseFloat(item.target_weight || item.weight || 0);
            const achieved = parseFloat(item.achieved_score || 0);
            const itemPct  = weight > 0 ? Math.round((achieved / weight) * 100) : 0;

            rowsHtml += `
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:12px 14px; vertical-align:middle;">
                        <span style="font-size:13px; font-weight:700; color:#0f172a;">${escapeHtml(name)}</span>
                    </td>
                    <td style="padding:12px 14px; vertical-align:middle;">
                        <span style="font-size:12px; color:#64748b;">${escapeHtml(desc)}</span>
                    </td>
                    <td style="padding:12px 14px; vertical-align:middle; text-align:center;">
                        <span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; font-size:12px; font-weight:700; padding:4px 10px; border-radius:12px;">${weight}%</span>
                    </td>
                    <td style="padding:12px 14px; vertical-align:middle; text-align:center;">
                        <span style="font-size:14px; font-weight:800; color:#6c4cf1;">${achieved.toFixed(1)} <span style="font-size:12px; font-weight:600; color:#64748b;">/ ${weight}</span></span>
                    </td>
                    <td style="padding:12px 14px; vertical-align:middle;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div class="progress-bar-container" style="flex:1; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden;">
                                <div class="progress-bar" style="width:${itemPct}%; height:100%; background:#6c4cf1;"></div>
                            </div>
                            <span style="font-size:11px; font-weight:700; color:#64748b; width:32px; text-align:right;">${itemPct}%</span>
                        </div>
                    </td>
                </tr>
                ${item.comment ? `
                    <tr style="background:#f8fafc; border-bottom:1px solid #f1f5f9;">
                        <td colspan="5" style="padding:8px 14px; font-size:12px; color:#475569; font-style:italic;">
                            💬 Notes: ${escapeHtml(item.comment)}
                        </td>
                    </tr>
                ` : ''}
            `;
        });

        card.innerHTML = `
            <!-- Header -->
            <div class="tpl-category-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="font-15 font-700 text-dark" style="font-size:15px; font-weight:700; color:#0f172a;">${escapeHtml(cat)}</span>
                    <span class="badge category-subtotal-badge" style="background:#f1f5f9; color:#6c4cf1; border:1px solid #e2e8f0; font-size:12px; font-weight:700; padding:4px 12px; border-radius:20px;">Max Target: ${catTotalWeight.toFixed(2)}%</span>
                </div>
                <div class="font-13 font-700 text-primary-color">Score: ${catTotalAchieved.toFixed(1)} / ${catTotalWeight.toFixed(0)} pts (${catPct}%)</div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="data-table font-13" style="width:100%; border-collapse:collapse; table-layout:fixed;">
                    <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                        <tr>
                            <th style="width:24%; padding:12px 14px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">CRITERIA QUESTION</th>
                            <th style="width:30%; padding:12px 14px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">EVALUATION GUIDELINES</th>
                            <th style="width:12%; padding:12px 14px; text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">TARGET WEIGHT</th>
                            <th style="width:16%; padding:12px 14px; text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">ACHIEVED SCORE</th>
                            <th style="width:18%; padding:12px 14px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; vertical-align:middle;">PROGRESS</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>
            </div>
        `;
        container.appendChild(card);
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function renderHistory(history) {
    const timeline = document.getElementById('feedbackTimeline');
    if (!timeline) return;

    if (!history || history.length === 0) {
        timeline.innerHTML = '<p class="text-light italic font-13 text-center" style="font-size:13px; color:#94a3b8; font-style:italic;">No historical evaluations found.</p>';
        return;
    }

    timeline.innerHTML = history.map(item => {
        const pct = item.overall_pct || Math.round(parseFloat(item.overall_rating || 0) * 20);
        const editUrl = (window.HRM && window.HRM.url ? window.HRM.url('kpi/evaluate') : '/kpi/evaluate') + `?review_id=${item.id}`;

        return `
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:13px; font-weight:700; color:#0f172a;">${escapeHtml(item.period_month || item.period || 'Evaluation')}</span>
                    <span class="badge" style="background:#f5f3ff; color:#6c4cf1; border:1px solid #ddd6fe; font-size:11px; font-weight:700; padding:2px 8px; border-radius:10px;">${pct}%</span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:11px; color:#64748b; font-weight:600;">${escapeHtml(item.grade || 'Completed')}</span>
                    <a href="${editUrl}" class="btn-ghost font-11 text-primary py-2 px-6" style="font-size:11px; font-weight:700; color:#6c4cf1; display:inline-flex; align-items:center; gap:3px;">
                        <i data-lucide="edit-2" size="11"></i> Edit
                    </a>
                </div>
            </div>
        `;
    }).join('');

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
