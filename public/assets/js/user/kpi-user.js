/**
 * User Side KPI Result Logic (v5 - Total Points & Grade Summary Only)
 */

document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('myKpiRoot');
    const monthSelect = document.getElementById('myKpiMonthSelect');

    if (!root) return;

    function formatMonthName(periodStr) {
        if (!periodStr) return '';
        const parts = periodStr.split('-');
        if (parts.length === 2) {
            const year = parts[0];
            const monthIdx = parseInt(parts[1], 10) - 1;
            const date = new Date(year, monthIdx, 1);
            if (!isNaN(date.getTime())) {
                return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            }
        }
        return periodStr;
    }

    function loadKpiResult(month = '') {
        const url = month ? `/assets/api/kpi_handler.php?action=fetch_my_kpi&month=${encodeURIComponent(month)}` : '/assets/api/kpi_handler.php?action=fetch_my_kpi';
        
        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.status !== 'success') {
                    renderError(res.message || 'Failed to load KPI result.');
                    return;
                }

                if (!res.has_kpi) {
                    renderEmptyState(res.message || 'No KPI performance evaluations recorded yet.');
                    return;
                }

                renderKpiResult(res.data);
            })
            .catch(err => {
                console.error('KPI Fetch Error:', err);
                renderError('Connection error loading KPI performance result.');
            });
    }

    function renderEmptyState(msg) {
        root.innerHTML = `
            <div class="card p-40 text-center" style="border-radius: 16px; background: #fff; max-width: 540px; margin: 30px auto;">
                <div class="empty-state-icon mb-15" style="width: 56px; height: 56px; margin: 0 auto; display: flex; align-items: center; justify-content: center; background: rgba(108, 76, 241, 0.1); color: var(--primary-color, #6c4cf1); border-radius: 50%;">
                    <i data-lucide="award" size="28"></i>
                </div>
                <h3 class="font-18 font-700 m-0">No KPI Evaluation Found</h3>
                <p class="font-13 text-light mt-8 mb-0">${escapeHtml(msg)}</p>
            </div>
        `;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function renderError(msg) {
        root.innerHTML = `
            <div class="card p-30 text-center" style="border-radius: 16px; background: #fff; max-width: 540px; margin: 30px auto;">
                <p class="font-14 text-danger m-0">${escapeHtml(msg)}</p>
            </div>
        `;
    }

    function renderKpiResult(data) {
        if (monthSelect && data.history && data.history.length > 0) {
            let options = '';
            data.history.forEach(h => {
                const isSelected = h.period_month === data.period_month ? 'selected' : '';
                options += `<option value="${h.period_month}" ${isSelected}>${formatMonthName(h.period_month)}</option>`;
            });
            monthSelect.innerHTML = options;
            if (monthSelect.parentElement) monthSelect.parentElement.style.display = 'flex';
        }

        const score = data.overall_pct;
        const grade = data.grade || 'Grade C needs improvement';
        const formattedCurrentMonth = formatMonthName(data.period_month);

        let badgeBg = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
        let glowColor = 'rgba(16, 185, 129, 0.4)';
        let badgeIcon = 'award';

        if (grade.includes('Grade B') || (score < 95 && score >= 85)) {
            badgeBg = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';
            glowColor = 'rgba(59, 130, 246, 0.4)';
            badgeIcon = 'thumbs-up';
        } else if (grade.includes('Grade C') || score < 85) {
            badgeBg = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
            glowColor = 'rgba(245, 158, 11, 0.4)';
            badgeIcon = 'alert-circle';
        }

        // Render Monthly History Cards
        let historyCardsHtml = '';
        if (data.history && data.history.length > 0) {
            historyCardsHtml = data.history.map(item => {
                const isCurrent = item.period_month === data.period_month;
                const mName = formatMonthName(item.period_month);
                const itemGrade = item.grade || 'Grade C needs improvement';
                const itemScore = item.overall_pct || 0;

                let itemPillBg = '#10b981';
                if (itemGrade.includes('Grade B')) itemPillBg = '#3b82f6';
                if (itemGrade.includes('Grade C')) itemPillBg = '#f59e0b';

                return `
                    <div class="kpi-history-card ${isCurrent ? 'active' : ''}" data-month="${item.period_month}" style="
                        background: #fff;
                        border: ${isCurrent ? '2px solid #6c4cf1' : '1px solid #e2e8f0'};
                        border-radius: 16px;
                        padding: 20px;
                        cursor: pointer;
                        transition: all 0.25s ease;
                        box-shadow: ${isCurrent ? '0 10px 25px -5px rgba(108, 76, 241, 0.15)' : '0 2px 6px rgba(0,0,0,0.02)'};
                    ">
                        <div class="flex-between mb-12" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span class="font-13 font-700 text-dark" style="display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="calendar" size="15" class="text-primary" style="color:#6c4cf1;"></i>
                                <span>${escapeHtml(mName)}</span>
                            </span>
                            <span class="font-14 font-800" style="color:#6c4cf1;">${itemScore}%</span>
                        </div>
                        <div>
                            <span style="
                                display: inline-block;
                                background: ${itemPillBg};
                                color: #ffffff;
                                padding: 6px 14px;
                                border-radius: 20px;
                                font-size: 12px;
                                font-weight: 700;
                            ">
                                ${escapeHtml(itemGrade)}
                            </span>
                        </div>
                    </div>
                `;
            }).join('');
        }

        root.innerHTML = `
            <!-- Top Hero Summary Banner Card -->
            <div class="card p-35 mb-24" style="
                background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%);
                color: #ffffff;
                border-radius: 24px;
                box-shadow: 0 20px 40px -10px rgba(49, 46, 129, 0.35);
                position: relative;
                overflow: hidden;
            ">
                <div style="
                    position: absolute;
                    top: -40px;
                    right: -40px;
                    width: 200px;
                    height: 200px;
                    background: radial-gradient(circle, rgba(108, 76, 241, 0.3) 0%, rgba(255,255,255,0) 70%);
                    border-radius: 50%;
                    pointer-events: none;
                "></div>

                <div class="flex-between flex-wrap gap-24 align-center" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 24px;">
                    <div>
                        <div class="inline-flex align-center gap-8 px-14 py-6 rounded-full font-12 font-700 mb-12" style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 30px; background: rgba(255,255,255,0.12); color: #c7d2fe; border: 1px solid rgba(255,255,255,0.18); margin-bottom: 12px;">
                            <i data-lucide="sparkles" size="14"></i>
                            <span>Performance Scorecard Summary</span>
                        </div>
                        <h2 class="font-32 font-800 m-0 text-white" style="letter-spacing: -0.02em; font-size: 32px; font-weight: 800; margin: 0; color: #fff;">${escapeHtml(formattedCurrentMonth)} Score: ${score}%</h2>
                        <p class="font-14 mt-6 m-0" style="color: #a5b4fc; margin-top: 6px; margin-bottom: 0;">Verified overall employee performance rating.</p>
                    </div>

                    <!-- Highlighted Big Grade Pill -->
                    <div class="text-right flex-center gap-16 flex-wrap" style="display: flex; align-items: center; justify-content: flex-end; gap: 16px;">
                        <div style="
                            display: inline-flex;
                            align-items: center;
                            gap: 12px;
                            background: ${badgeBg};
                            color: #ffffff;
                            padding: 16px 32px;
                            border-radius: 50px;
                            font-size: 24px;
                            font-weight: 800;
                            box-shadow: 0 12px 30px ${glowColor};
                            border: 2px solid rgba(255,255,255,0.25);
                        ">
                            <i data-lucide="${badgeIcon}" size="28" style="flex-shrink: 0;"></i>
                            <span>${escapeHtml(grade)}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evaluator Feedback / Remarks Summary (if available) -->
            ${data.feedback ? `
                <div class="card p-24 mb-24" style="border-radius: 20px; border: 1px solid #e2e8f0; background: #fff;">
                    <h4 class="font-15 font-700 m-0 text-dark flex-center gap-8 mb-12" style="display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; margin-bottom: 12px; color: #0f172a;">
                        <i data-lucide="message-square" size="18" style="color: #6c4cf1;"></i>
                        <span>Evaluator Feedback & Remarks</span>
                    </h4>
                    <p class="font-13 text-dark m-0" style="font-size: 13px; color: #334155; line-height: 1.6; white-space: pre-line;">${escapeHtml(data.feedback)}</p>
                </div>
            ` : ''}

            <!-- Month-wise Evaluation History Section -->
            <div class="card p-24" style="border-radius: 20px; border: 1px solid #e2e8f0; background: #fff;">
                <div class="flex-between flex-wrap gap-15 mb-20" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
                    <div>
                        <h4 class="font-16 font-700 m-0 text-dark flex-center gap-8" style="display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 700; margin: 0; color: #0f172a;">
                            <i data-lucide="history" size="18" style="color: #6c4cf1;"></i>
                            <span>Performance History</span>
                        </h4>
                        <p class="font-12 text-light m-0 mt-3" style="font-size: 12px; color: #64748b; margin-top: 3px; margin-bottom: 0;">Click on any period card to view that month's overall score and grade.</p>
                    </div>
                </div>

                <div style="
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                    gap: 16px;
                ">
                    ${historyCardsHtml}
                </div>
            </div>
        `;

        if (typeof lucide !== 'undefined') lucide.createIcons();

        document.querySelectorAll('.kpi-history-card').forEach(card => {
            card.addEventListener('click', function () {
                const selectedM = this.getAttribute('data-month');
                if (selectedM && selectedM !== data.period_month) {
                    loadKpiResult(selectedM);
                }
            });
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    monthSelect?.addEventListener('change', function () {
        loadKpiResult(this.value);
    });

    loadKpiResult();
});
