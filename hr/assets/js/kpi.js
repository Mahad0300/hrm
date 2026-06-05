/**
 * KPI Management Hub Logic
 */

document.addEventListener('DOMContentLoaded', function () {
    initSummary();
    initKpiTable();
    initFilters();
    initModalData();
    initFormLogic();
});

// --- Initialization ---

function initSummary() {
    fetch('assets/api/kpi_handler.php?action=fetch_summary')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const d = res.data;
                document.getElementById('statAvgScore').textContent = `${d.avg_score} / 5.0`;
                document.getElementById('statRatedCount').textContent = `${d.rated_count} / ${d.total_count}`;
                document.getElementById('statTopDept').textContent = d.top_dept;
                // Currently top month is static or derived from chart, settings "---" for now.
            }
        });
}

function initKpiTable() {
    fetch('assets/api/kpi_handler.php?action=fetch_list')
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

function setupPaginationControls() {
    const perPageSelect = document.getElementById('perPageSelect');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    if (perPageSelect && !perPageSelect.dataset.bound) {
        perPageSelect.dataset.bound = '1';
        perPageSelect.addEventListener('change', () => {
            const val = perPageSelect.value;
            kpiRowsPerPage = val === 'all' ? -1 : parseInt(val);
            kpiCurrentPage = 1;
            renderKpiTable(_lastFilteredData);
        });
    }
    if (prevBtn && !prevBtn.dataset.bound) {
        prevBtn.dataset.bound = '1';
        prevBtn.addEventListener('click', () => {
            if (kpiCurrentPage > 1) { kpiCurrentPage--; renderKpiTable(_lastFilteredData); }
        });
    }
    if (nextBtn && !nextBtn.dataset.bound) {
        nextBtn.dataset.bound = '1';
        nextBtn.addEventListener('click', () => {
            const totalPages = kpiRowsPerPage === -1 ? 1 : Math.ceil(_lastFilteredData.length / kpiRowsPerPage);
            if (kpiCurrentPage < totalPages) { kpiCurrentPage++; renderKpiTable(_lastFilteredData); }
        });
    }
}

let allKpiData = [];
let kpiCurrentPage = 1;
let kpiRowsPerPage = 10;

function renderKpiTable(data) {
    const tbody = document.getElementById('kpiTableBody');
    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-40 text-light italic">No performance reviews found.</td></tr>';
        updatePaginationUI(0);
        return;
    }

    // Pagination slice
    const totalRows = data.length;
    const totalPages = kpiRowsPerPage === -1 ? 1 : Math.ceil(totalRows / kpiRowsPerPage);
    if (kpiCurrentPage > totalPages) kpiCurrentPage = totalPages;
    if (kpiCurrentPage < 1) kpiCurrentPage = 1;

    const start = kpiRowsPerPage === -1 ? 0 : (kpiCurrentPage - 1) * kpiRowsPerPage;
    const end = kpiRowsPerPage === -1 ? totalRows : Math.min(start + kpiRowsPerPage, totalRows);
    const pageData = data.slice(start, end);

    pageData.forEach(item => {
        const score = item.overall_rating ? parseFloat(item.overall_rating).toFixed(1) : '0.0';
        const statusClass = getStatusClass(item.status);
        const progress = item.progress_percent || 0;
        const progressClass = progress >= 80 ? 'success' : progress >= 60 ? 'primary' : progress >= 40 ? 'warning' : 'danger';

        const row = `
            <tr>
                <td>
                    <div class="emp-profile">
                        <img src="${item.profile_pic ? '../' + item.profile_pic : '../images/profile-image/default-avatar.svg'}" class="emp-avatar" alt="Avatar" onerror="this.src='../images/profile-image/default-avatar.svg'">
                        <div class="emp-info">
                            <span class="name">${item.first_name} ${item.middle_name ? item.middle_name + ' ' : ''}${item.last_name}</span>
                            <span class="email font-12 text-light">EMP-0${item.employee_id}</span>
                        </div>
                    </div>
                </td>
                <td>${item.department_name || 'N/A'}</td>
                <td class="font-13">${item.kpi_goal || '<span class="text-light italic">No Review yet</span>'}</td>
                <td width="200">
                    <div class="kpi-progress-wrapper">
                        <span class="font-11 text-light mb-4 block">${item.target_vs_achieved || '0 / 0'}</span>
                        <div class="progress-bar-container">
                            <div class="progress-bar ${progressClass}" style="width: ${progress}%;"></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="flex-center justify-start gap-8">
                        <i data-lucide="star" class="text-warning ${item.overall_rating ? 'fill-warning' : ''}" size="14"></i>
                        <span class="font-14 font-600">${score}</span>
                    </div>
                </td>
                <td><span class="badge badge-${statusClass}">${item.status}</span></td>
                <td class="text-right px-30">
                    <a href="kpi-report.php?id=${item.employee_id}" class="btn-primary">
                        <i data-lucide="eye"></i> View Scorecard
                    </a>
                </td>
            </tr>`;
        tbody.insertAdjacentHTML('beforeend', row);
    });

    updatePaginationUI(totalRows);
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function updatePaginationUI(totalRows) {
    const totalPages = kpiRowsPerPage === -1 ? 1 : Math.ceil(totalRows / kpiRowsPerPage);
    const start = kpiRowsPerPage === -1 ? 1 : (kpiCurrentPage - 1) * kpiRowsPerPage + 1;
    const end = kpiRowsPerPage === -1 ? totalRows : Math.min(kpiCurrentPage * kpiRowsPerPage, totalRows);

    const infoText = totalRows === 0 ? 'No entries found' : `Showing ${start} to ${end} of ${totalRows} entries`;
    const infoEl = document.getElementById('paginationInfo');
    const summaryEl = document.getElementById('tableSummary');
    if (infoEl) infoEl.textContent = infoText;
    if (summaryEl) summaryEl.textContent = infoText;

    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const pageNumbersEl = document.getElementById('pageNumbers');

    if (prevBtn) {
        prevBtn.disabled = kpiCurrentPage <= 1;
        prevBtn.style.opacity = kpiCurrentPage <= 1 ? '0.5' : '1';
    }
    if (nextBtn) {
        nextBtn.disabled = kpiCurrentPage >= totalPages;
        nextBtn.style.opacity = kpiCurrentPage >= totalPages ? '0.5' : '1';
    }

    if (pageNumbersEl) {
        pageNumbersEl.innerHTML = '';
        const maxVisible = 5;
        let startPage = Math.max(1, kpiCurrentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.className = `action-btn ${i === kpiCurrentPage ? 'btn-active' : ''}`;
            btn.textContent = i;
            btn.onclick = () => { kpiCurrentPage = i; renderKpiTable(getCurrentFilteredData()); };
            pageNumbersEl.appendChild(btn);
        }
    }
}

let _lastFilteredData = [];
function getCurrentFilteredData() { return _lastFilteredData; }

function getStatusClass(status) {
    if (status === 'Excelling') return 'success';
    if (status === 'Good') return 'primary';
    if (status === 'On Track') return 'warning';
    if (status === 'Not Rated') return 'secondary';
    return 'danger';
}

// --- Filters ---

function initFilters() {
    const search = document.getElementById('searchEmployee');
    const dept = document.getElementById('filterDept');
    const month = document.getElementById('filterMonth');
    const status = document.getElementById('filterStatus');

    // Fetch Departments for filter
    fetch('assets/api/employee_handler.php?action=fetch_requirements')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                res.departments.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.name;
                    dept.appendChild(opt);
                });
            }
        });

    const applyFilters = () => {
        const query = search.value.toLowerCase();
        const dVal = dept.value;
        const mVal = month.value;
        const sVal = status.value;

        const filtered = allKpiData.filter(item => {
            const matchesSearch = item.first_name.toLowerCase().includes(query) || 
                                (item.middle_name && item.middle_name.toLowerCase().includes(query)) || 
                                item.last_name.toLowerCase().includes(query);
            const matchesDept = dVal === '' || item.department_id == dVal;
            const matchesStatus = sVal === '' || item.status === sVal;
            const matchesMonth = mVal === '' || (item.review_date && item.review_date.startsWith(mVal));
            return matchesSearch && matchesDept && matchesStatus && matchesMonth;
        });
        _lastFilteredData = filtered;
        kpiCurrentPage = 1; // reset to page 1 on filter change
        renderKpiTable(_lastFilteredData);
    };

    search.addEventListener('input', applyFilters);
    dept.addEventListener('change', applyFilters);
    month.addEventListener('change', applyFilters);
    status.addEventListener('change', applyFilters);
}

// --- Modal & Form Logic ---

function initModalData() {
    const empSelect = document.getElementById('modalEmployeeSelect');

    // Fetch all employees for the selection
    fetch('assets/api/kpi_handler.php?action=fetch_employees')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                res.data.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.id;
                    opt.textContent = `${e.first_name} ${e.middle_name ? e.middle_name + ' ' : ''}${e.last_name}`;
                    empSelect.appendChild(opt);
                });
            }
        });

    // Handle Employee Selection -> Load Latest Goals (Persistence)
    empSelect.addEventListener('change', function () {
        const id = this.value;
        if (!id) return;

        fetch(`assets/api/kpi_handler.php?action=fetch_latest_goals&employee_id=${id}`)
            .then(res => res.json())
            .then(res => {
                const container = document.getElementById('dynamicKpiContainer');
                container.innerHTML = '';
                if (res.status === 'success' && res.data.length > 0) {
                    res.data.forEach(goalTitle => {
                        createGoalRow(goalTitle, 80);
                    });
                } else {
                    container.innerHTML = '<p class="text-light italic col-span-2 text-center py-20">No previous goals found. Add new goals below.</p>';
                }
            });
    });
}

function initFormLogic() {
    const form = document.getElementById('addReviewForm');
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        formData.append('action', 'add_review');
        formData.append('employee_id', document.getElementById('modalEmployeeSelect').value);
        formData.append('period', getActivePeriod());
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
                    Swal.fire('Success', res.message, 'success');
                    closeModal('addReviewModal');
                    initKpiTable(); // Refresh table
                    initSummary(); // Refresh summary
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            });
    });
}

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

window.closeModal = function (id) {
    document.getElementById(id).classList.remove('active');
};
