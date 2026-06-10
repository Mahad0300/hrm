/**
 * Activity Logs - Live Fetching and Filtering
 */

let activityCurrentPage = 1;
let activityRowsPerPage = 10;
let activityFilters = {
    search: '',
    module: '',
    action_filter: '',
    date: ''
};

document.addEventListener('DOMContentLoaded', function() {
    initActivityLogs();
});

function initActivityLogs() {
    const searchInput = document.querySelector('.search-box input');
    const moduleSelect = document.querySelectorAll('.filter-item select')[0];
    const actionSelect = document.querySelectorAll('.filter-item select')[1];
    const dateInput = document.querySelector('.filter-item input[type="date"]');
    const perPageSelect = document.getElementById('perPageSelect');

    // Search with Debounce
    let searchTimeout;
    searchInput?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            activityFilters.search = this.value;
            activityCurrentPage = 1;
            fetchActivityLogs();
        }, 500);
    });

    // Module Filter
    moduleSelect?.addEventListener('change', function() {
        activityFilters.module = this.value;
        activityCurrentPage = 1;
        fetchActivityLogs();
    });

    // Action Filter
    actionSelect?.addEventListener('change', function() {
        activityFilters.action_filter = this.value;
        activityCurrentPage = 1;
        fetchActivityLogs();
    });

    // Date Filter
    dateInput?.addEventListener('change', function() {
        activityFilters.date = this.value;
        activityCurrentPage = 1;
        fetchActivityLogs();
    });

    // Per Page
    perPageSelect?.addEventListener('change', function() {
        activityRowsPerPage = this.value === 'all' ? 1000 : parseInt(this.value);
        activityCurrentPage = 1;
        fetchActivityLogs();
    });

    // Pagination Nav
    document.getElementById('prevPage')?.addEventListener('click', () => {
        if (activityCurrentPage > 1) {
            activityCurrentPage--;
            fetchActivityLogs();
        }
    });

    document.getElementById('nextPage')?.addEventListener('click', () => {
        activityCurrentPage++;
        fetchActivityLogs();
    });

    // Initial Fetch
    fetchActivityLogs();
}

function fetchActivityLogs() {
    const tableBody = document.getElementById('activityTableBody');
    if (!tableBody) return;

    // Show Loading
    tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-40"><div class="loading-spinner"></div> Loading activities...</td></tr>`;

    const params = new URLSearchParams({
        action: 'fetch',
        page: activityCurrentPage,
        perPage: activityRowsPerPage,
        search: activityFilters.search,
        module: activityFilters.module,
        action_filter: activityFilters.action_filter,
        date: activityFilters.date
    });

    fetch(`assets/api/activity_handler.php?${params.toString()}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                renderActivityTable(res.data);
                updatePaginationInfo(res.total);
            } else {
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-40 text-danger">${res.message}</td></tr>`;
            }
        })
        .catch(err => {
            console.error('Fetch Error:', err);
            tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-40 text-danger">Failed to load logs.</td></tr>`;
        });
}

function renderActivityTable(logs) {
    const tableBody = document.getElementById('activityTableBody');
    if (logs.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center p-40 italic text-light">No activity logs found matching your criteria.</td></tr>`;
        return;
    }

    tableBody.innerHTML = logs.map(log => {
        const date = new Date(log.created_at);
        const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const formattedTime = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        
        // Path logic consistent with employee-profile.php
        const defaultAvatar = '../images/profile-image/default-avatar.svg';
        const avatar = log.profile_pic ? `../${log.profile_pic}` : defaultAvatar;

        // Format ID as EMP-0 + ID (matching the rest of the HRM system)
        const formattedId = `EMP-0${log.emp_code}`;
        const employeeName = [log.first_name, log.middle_name, log.last_name].filter(v => v && String(v).trim() !== '').join(' ');

        return `
            <tr>
                <td>
                    <div class="emp-profile">
                        <img src="${avatar}" class="emp-avatar" alt="Avatar" onerror="this.src='${defaultAvatar}'">
                        <div class="emp-info">
                            <span class="name">${employeeName}</span>
                            <span class="email font-12 text-light">${formattedId}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="flex-column">
                        <span>${formattedDate}</span>
                        <span class="font-12 text-light">${formattedTime}</span>
                    </div>
                </td>
                <td>
                    <span class="badge badge-light">${log.module}</span>
                </td>
                <td>
                    <span class="badge ${getActionBadgeClass(log.action)}">${log.action}</span>
                </td>
                <td class="allow-wrap font-13">${log.details}</td>
                <td><code class="font-12">${log.ip_address === '::1' ? 'Localhost' : log.ip_address}</code></td>
            </tr>
        `;
    }).join('');
}

function getActionBadgeClass(action) {
    const act = action.toLowerCase();
    if (act.includes('create') || act.includes('add') || act.includes('submit')) return 'badge-success';
    if (act.includes('update') || act.includes('edit')) return 'badge-info';
    if (act.includes('delete') || act.includes('remove') || act.includes('reject')) return 'badge-danger';
    if (act.includes('login')) return 'badge-primary';
    return 'badge-light';
}

function updatePaginationInfo(total) {
    const start = (activityCurrentPage - 1) * activityRowsPerPage + 1;
    const end = Math.min(activityCurrentPage * activityRowsPerPage, total);
    const summaryText = `Showing ${total === 0 ? 0 : start} to ${end} of ${total} entries`;
    
    const summaryElement = document.getElementById('tableSummary');
    const paginationInfo = document.getElementById('paginationInfo');
    if (summaryElement) summaryElement.textContent = summaryText;
    if (paginationInfo) paginationInfo.textContent = summaryText;

    const totalPages = Math.ceil(total / activityRowsPerPage);
    const pageNumbersContainer = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    if (pageNumbersContainer) {
        pageNumbersContainer.innerHTML = '';
        // Simple pagination: show current, prev, and next if they exist
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= activityCurrentPage - 1 && i <= activityCurrentPage + 1)) {
                const btn = document.createElement('button');
                btn.className = `action-btn ${i === activityCurrentPage ? 'btn-active' : ''}`;
                btn.textContent = i;
                btn.onclick = () => {
                    activityCurrentPage = i;
                    fetchActivityLogs();
                };
                pageNumbersContainer.appendChild(btn);
            } else if (i === activityCurrentPage - 2 || i === activityCurrentPage + 2) {
                const dot = document.createElement('span');
                dot.textContent = '...';
                pageNumbersContainer.appendChild(dot);
            }
        }
    }

    if (prevBtn) prevBtn.disabled = (activityCurrentPage === 1);
    if (nextBtn) nextBtn.disabled = (activityCurrentPage >= totalPages || totalPages === 0);
}
