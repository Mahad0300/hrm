/**
 * KPI Report Logic - Dedicated Page for Employee Scores
 */

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const empId = urlParams.get('id');

    if (!empId) {
        Swal.fire('Error', 'No employee ID provided.', 'error').then(() => {
            window.location.href = 'kpi-management.php';
        });
        return;
    }

    fetchReportData(empId);
    initFormLogic();
});

function fetchReportData(id) {
    fetch(`assets/api/kpi_handler.php?action=fetch_report_data&id=${id}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                renderProfile(res.employee);
                renderLatestScorecard(res.history[0]);
                renderHistory(res.history);
                renderTrendChart(res.history);
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
}

function renderProfile(emp) {
    document.getElementById('detailName').textContent = `${emp.first_name} ${emp.middle_name ? emp.middle_name + ' ' : ''}${emp.last_name}`;
    document.getElementById('detailDept').textContent = `${emp.dept_name || 'No Department'}`;
    document.getElementById('detailAvatar').src = emp.profile_pic ? '../' + emp.profile_pic : '../images/profile-image/default-avatar.svg';
}

function renderLatestScorecard(lastReview) {
    const container = document.getElementById('detailGoalsContainer');
    container.innerHTML = '';

    if (!lastReview || !lastReview.goals || lastReview.goals.length === 0) {
        container.innerHTML = '<p class="text-light italic font-13">No recent ratings found for this employee.</p>';
        document.getElementById('detailScore').textContent = '0.0';
        document.getElementById('detailStatus').textContent = 'N/A';
        return;
    }

    // Update Header Stats
    document.getElementById('detailScore').textContent = parseFloat(lastReview.overall_rating).toFixed(1);
    const statusBadge = document.getElementById('detailStatus');
    statusBadge.textContent = lastReview.status;
    statusBadge.className = `badge font-11 uppercase ls-05 badge-${getStatusClass(lastReview.status)}`;

    lastReview.goals.forEach(goal => {
        const percent = goal.achieved_score;
        const barClass = percent >= 80 ? 'success' : percent >= 60 ? 'primary' : percent >= 40 ? 'warning' : 'danger';

        const html = `
            <div class="goal-item mb-10">
                <div class="flex-between mb-4">
                    <span class="font-12 font-600 uppercase">${goal.goal_name}</span>
                    <span class="font-13 text-primary-color font-600">${percent}%</span>
                </div>
                <div class="progress-bar-container h-8 mb-12">
                    <div class="progress-bar ${barClass}" style="width: ${percent}%;"></div>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    });
}

function renderHistory(history) {
    const container = document.getElementById('feedbackTimeline');
    container.innerHTML = '';

    if (history.length === 0) {
        container.innerHTML = '<p class="text-light italic">No feedback history available.</p>';
        return;
    }

    history.forEach(item => {
        const dotClass = getStatusClass(item.status);
        const dateStr = new Date(item.review_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

        let goalSummary = '';
        if (item.goals) {
            goalSummary = `<div class="history-score-grid mt-20 pt-16 border-t-dashed flex-column gap-12">`;
            item.goals.forEach(g => {
                const gPercent = g.achieved_score;
                const gClass = gPercent >= 80 ? 'success' : gPercent >= 60 ? 'primary' : gPercent >= 40 ? 'warning' : 'danger';
                goalSummary += `
                    <div>
                        <div class="flex-between mb-4">
                            <span class="font-9 text-light uppercase ls-05 mb-4">${g.goal_name}</span>
                            <span class="font-9 font-700 text-dark">${gPercent}%</span>
                        </div>
                        <div class="progress-bar-container h-4 mb-4">
                            <div class="progress-bar ${gClass}" style="width: ${gPercent}%;"></div>
                        </div>
                        ${g.reviewer_comment ? `<div class="font-11 text-light italic pl-8 mt-4">"${g.reviewer_comment}"</div>` : ''}
                    </div>`;
            });
            goalSummary += `</div>`;
        }

        const html = `
            <div class="timeline-item-lite">
                <div class="timeline-dot-lite ${dotClass}"></div>
                <div class="timeline-content-lite cursor-pointer" onclick="openViewDetail(${item.id})">
                    <div class="flex-between">
                        <span class="font-12 font-700">${item.reviewer_first} ${item.reviewer_middle ? item.reviewer_middle + ' ' : ''}${item.reviewer_last} (${item.period})</span>
                        <span class="font-12 text-light">${dateStr}</span>
                    </div>
                    <p class="font-13 text-secondary mt-8 italic">"${item.feedback || 'No comments provided.'}"</p>
                    ${goalSummary}
                    <div class="flex-between mt-20">
                        <div class="rating-tag">
                            <i data-lucide="star" class="text-warning fill-warning" size="12"></i>
                            <span class="font-11 font-700">${parseFloat(item.overall_rating).toFixed(1)} / 5.0</span>
                        </div>
                        <div class="kpi-history-actions">
                            <button class="action-btn action-btn-edit" title="Edit Review" onclick="event.stopPropagation(); openEditReview(${item.id})">
                                <i data-lucide="edit-2" size="14"></i>
                            </button>
                            <button class="action-btn action-btn-delete" title="Delete Review" onclick="event.stopPropagation(); deleteReview(${item.id})">
                                <i data-lucide="trash-2" size="14"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function renderTrendChart(history) {
    const svg = document.getElementById('trendSvg');
    const area = document.getElementById('chartArea');
    const line = document.getElementById('chartLine');
    const dotsG = document.getElementById('chartDots');
    const labels = document.getElementById('chartMonths');

    if (!history || history.length < 2) {
        // Not enough data for a trend, just show static placeholder or clear
        return;
    }

    // Sort history by date for chart (oldest first)
    const chartData = [...history].reverse().slice(-6); // Last 6 reviews

    const width = 500;
    const height = 200;
    const stepX = width / (chartData.length - 1);

    let pathD = "";
    let areaD = `M0,${height} `;
    dotsG.innerHTML = '';
    labels.innerHTML = '';

    chartData.forEach((item, i) => {
        const x = i * stepX;
        // Map 0-5 score to 200-0 height (y=0 is top)
        const score = parseFloat(item.overall_rating);
        const y = height - (score / 5.0 * height);

        pathD += (i === 0 ? "M" : " L") + `${x},${y}`;
        areaD += `L${x},${y} `;

        // Dot
        const dot = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        dot.setAttribute("cx", x);
        dot.setAttribute("cy", y);
        dot.setAttribute("r", i === chartData.length - 1 ? "6" : "4");
        dot.setAttribute("fill", i === chartData.length - 1 ? "var(--primary-color)" : "#fff");
        dot.setAttribute("stroke", "var(--primary-color)");
        dot.setAttribute("stroke-width", "2");
        dot.classList.add("chart-dot");
        if (i === chartData.length - 1) dot.classList.add("active");
        dotsG.appendChild(dot);

        // Label
        const date = new Date(item.review_date);
        const label = document.createElement('span');
        label.textContent = date.toLocaleDateString('en-US', { month: 'short' });
        if (i === chartData.length - 1) label.classList.add('font-700', 'text-dark');
        labels.appendChild(label);
    });

    areaD += `L${width},${height} L0,${height} Z`;

    line.setAttribute("d", pathD);
    area.setAttribute("d", areaD);
}

function getStatusClass(status) {
    if (status === 'Excelling') return 'success';
    if (status === 'Good') return 'primary';
    if (status === 'On Track') return 'warning';
    if (status === 'Not Rated') return 'secondary';
    return 'danger';
}

// --- Modal & Review Logic ---

const urlParams = new URLSearchParams(window.location.search);
const currentEmpId = urlParams.get('id');

window.openReviewModal = function () {
    const modal = document.getElementById('addReviewModal');
    modal.classList.add('active');

    // Reset Modal Content
    document.getElementById('modalTitleText').textContent = 'Add Performance Review';
    document.getElementById('modalReviewId').value = '';
    document.getElementById('addReviewForm').reset();
    document.getElementById('modalEmployeeId').value = currentEmpId;
    document.getElementById('modalEmpNameDisplay').textContent = document.getElementById('detailName').textContent;
    window.setSentiment(3); // Default

    // Load latest goals for persistence
    fetch(`assets/api/kpi_handler.php?action=fetch_latest_goals&employee_id=${currentEmpId}`)
        .then(res => res.json())
        .then(res => {
            const container = document.getElementById('dynamicKpiContainer');
            container.innerHTML = '';
            if (res.status === 'success' && res.data.length > 0) {
                res.data.forEach(goalTitle => {
                    window.createGoalRow(goalTitle, 80);
                });
            } else {
                container.innerHTML = '<p class="text-light italic col-span-2 text-center py-20">No previous goals found. Add new goals below.</p>';
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
};

window.openViewDetail = function (id) {
    fetch(`assets/api/kpi_handler.php?action=fetch_review_details&id=${id}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const data = res.data;
                const modal = document.getElementById('viewReviewDetailModal');

                // Set Header Info
                document.getElementById('viewDetailEmpName').textContent = document.getElementById('detailName').textContent;
                document.getElementById('viewDetailPeriod').textContent = data.period.toUpperCase();
                document.getElementById('viewDetailRating').textContent = `${parseFloat(data.overall_rating).toFixed(1)} / 5.0`;
                document.getElementById('viewDetailFeedback').textContent = data.feedback || 'No general comments provided for this review.';

                // Render Goals
                const container = document.getElementById('viewDetailKpiContainer');
                container.innerHTML = '';
                if (data.goals && data.goals.length > 0) {
                    data.goals.forEach(g => {
                        const gPercent = g.achieved_score;
                        const gClass = gPercent >= 80 ? 'success' : gPercent >= 60 ? 'primary' : gPercent >= 40 ? 'warning' : 'danger';

                        const html = `
                            <div class="review-detail-goal">
                                <div class="review-detail-goal__head">
                                    <span class="review-detail-goal__title">${g.goal_name}</span>
                                    <span class="badge badge-${gClass}-light review-detail-goal__score">${gPercent}%</span>
                                </div>
                                <div class="progress-bar-container h-6 mb-12">
                                    <div class="progress-bar ${gClass}" style="width: ${gPercent}%;"></div>
                                </div>
                                ${g.reviewer_comment ? `
                                <div class="review-detail-goal__comment">
                                    <p>"${g.reviewer_comment}"</p>
                                </div>` : ''}
                            </div>`;
                        container.insertAdjacentHTML('beforeend', html);
                    });
                } else {
                    container.innerHTML = '<p class="text-light italic col-span-2 py-20">No specific goals were rated in this period.</p>';
                }

                // Attach actions to footer buttons
                document.getElementById('viewDetailEditBtn').onclick = () => {
                    window.closeModal('viewReviewDetailModal');
                    window.openEditReview(id);
                };
                document.getElementById('viewDetailDeleteBtn').onclick = () => {
                    window.closeModal('viewReviewDetailModal');
                    window.deleteReview(id);
                };

                modal.classList.add('active');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
};

window.openEditReview = function (id) {
    fetch(`assets/api/kpi_handler.php?action=fetch_review_details&id=${id}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const data = res.data;
                const modal = document.getElementById('addReviewModal');
                modal.classList.add('active');

                // Set Edit Mode
                document.getElementById('modalTitleText').textContent = 'Edit Performance Review';
                document.getElementById('modalReviewId').value = data.id;
                document.getElementById('modalEmployeeId').value = data.employee_id;
                document.querySelector('textarea[name="feedback"]').value = data.feedback;

                // Select Period
                const period = data.period.charAt(0).toUpperCase() + data.period.slice(1).toLowerCase();
                document.querySelectorAll('.period-card').forEach(card => {
                    if (card.textContent.trim().toUpperCase() === period.toUpperCase()) {
                        window.selectPeriod(card, period);
                    }
                });

                // Set Rating
                window.setSentiment(data.overall_rating);

                // Render Goals
                const container = document.getElementById('dynamicKpiContainer');
                container.innerHTML = '';
                if (data.goals && data.goals.length > 0) {
                    data.goals.forEach(g => {
                        window.createGoalRow(g.goal_name, g.achieved_score, g.reviewer_comment);
                    });
                }

                if (typeof lucide !== 'undefined') lucide.createIcons();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
};

window.deleteReview = function (id) {
    if (!id) return;

    Swal.fire({
        title: 'Are you sure?',
        text: "This review and its scores will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed === true) {
            const formData = new FormData();
            formData.append('action', 'delete_review');
            formData.append('id', id);

            fetch('assets/api/kpi_handler.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire({
                            title: 'Deleted!',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        fetchReportData(currentEmpId);
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                })
                .catch(err => {
                    console.error('Delete error:', err);
                    Swal.fire('Error', 'Connection failed', 'error');
                });
        }
    });
};

window.closeModal = function (id) {
    document.getElementById(id).classList.remove('active');
};

window.setSentiment = function (val) {
    document.getElementById('reviewRatingInput').value = val;
    const text = document.getElementById('sentimentText');
    const stars = document.querySelectorAll('#starRatingSelect i, #starRatingSelect svg');

    stars.forEach((star, i) => {
        if (i < val) {
            star.classList.remove('empty');
            star.classList.add('filled');
        } else {
            star.classList.add('empty');
            star.classList.remove('filled');
        }
    });

    const labels = { 1: 'Poor / Critical', 2: 'Below Target', 3: 'On Track', 4: 'Good / Above Target', 5: 'Exceptional Performance' };
    text.textContent = labels[val];
};

window.selectPeriod = function (el, label) {
    document.querySelectorAll('.period-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
};

window.createGoalRow = function (title, val, comment = '') {
    const container = document.getElementById('dynamicKpiContainer');
    if (container.querySelector('.italic')) container.innerHTML = '';

    const id = 'kpi_' + Math.random().toString(36).substr(2, 9);
    const html = `
        <div class="kpi-goal-row p-16 border rounded-12 bg-white animate-fade-in w-full mb-10 shadow-sm">
            <div class="flex-between">
                <label class="admin-form-label goal-title">${title}</label>
                <span class="badge badge-primary-light font-12 font-700" id="val_${id}">${val}%</span>
            </div>
            <input type="range" class="kpi-range-input mb-10 w-full" min="0" max="100" value="${val}"
                oninput="document.getElementById('val_${id}').innerText = this.value + '%'">
            <div class="mt-8">
                <input type="text" class="form-control font-12 py-8 px-12 goal-comment" 
                    placeholder="Write answer/comment for this goal..." value="${comment}">
            </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
};

window.addCustomGoal = function () {
    const input = document.getElementById('customGoalInput');
    const title = input.value.trim();
    if (!title) return;
    window.createGoalRow(title, 80);
    input.value = '';
};

function initFormLogic() {
    const form = document.getElementById('addReviewForm');
    if (!form) return;
    
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        formData.append('action', 'add_review');
        formData.append('period', document.querySelector('.period-card.active').textContent.toLowerCase().trim());
        formData.append('overall_rating', document.getElementById('reviewRatingInput').value);

        // Collect goals
        const goals = [];
        document.querySelectorAll('.kpi-goal-row').forEach(row => {
            goals.push({
                name: row.querySelector('.goal-title').textContent,
                score: row.querySelector('.kpi-range-input').value,
                comment: row.querySelector('.goal-comment').value
            });
        });
        formData.append('goals', JSON.stringify(goals));

        fetch('assets/api/kpi_handler.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: res.message.includes('updated') ? 'Updated!' : 'Submitted!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    window.closeModal('addReviewModal');
                    if (typeof fetchReportData === 'function') {
                        fetchReportData(currentEmpId);
                    }
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            });
    });
}
