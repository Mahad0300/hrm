// user/assets/js/attendance-log.js

function getAttendanceStatusClass(status) {
    const s = (status || '').trim().toUpperCase();
    switch (s) {
        case 'ON TIME': return 'status-v2-ontime';
        case 'LATE IN': return 'status-v2-late';
        case 'HALF DAY': return 'status-v2-halfday';
        case 'ABSENT': return 'status-v2-absent';
        case 'LEAVE': return 'status-v2-leave';
        case 'WEEKEND':
        case 'HOLIDAY': return 'status-v2-holiday';
        default: return 'status-v2-none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const monthFilter = document.getElementById('monthFilter');
    if (monthFilter && window.HRM_CONFIG?.current_payroll_month && !new URLSearchParams(window.location.search).has('month')) {
        monthFilter.value = window.HRM_CONFIG.current_payroll_month;
    }
    const tableBody = document.getElementById('attendanceTableBody');
    const perPageSelect = document.getElementById('perPageSelect');
    
    let allLogs = [];
    let currentPage = 1;
    let rowsPerPage = 10;

    window.fetchLogs = function() {
        const month = monthFilter.value;
        fetch(`assets/api/attendance_handler.php?action=fetch_logs&month=${month}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    // Only re-render if data has changed (simple length + last modified check)
                    const newDataStr = JSON.stringify(res.data);
                    if (window.lastDataStr !== newDataStr) {
                        allLogs = res.data;
                        renderTable();
                        renderCalendar();
                        window.lastDataStr = newDataStr;
                    }
                }
            })
            .catch(err => console.error('Polling error:', err));
    }

    function renderTable() {
        tableBody.innerHTML = '';
        const total = allLogs.length;
        
        if (total === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-40"><div class="empty-state-wrapper"><i data-lucide="calendar-x" size="48" class="text-light mb-16"></i><p class="text-light font-14">No attendance records found for this month.</p></div></td></tr>';
            updatePaginationInfo(0, 0, 0);
            if (window.lucide) lucide.createIcons();
            return;
        }

        const start = (currentPage - 1) * rowsPerPage;
        const end = rowsPerPage === -1 ? total : Math.min(start + rowsPerPage, total);
        const paginated = rowsPerPage === -1 ? allLogs : allLogs.slice(start, end);

        paginated.forEach(r => {
            const row = document.createElement('tr');
            const inTime = r.clock_in ? formatTimeStr(r.clock_in) : '--:--';
            const outTime = r.clock_out ? formatTimeStr(r.clock_out) : '--:--';
            const statusLabel = (r.status || '').trim();
            const statusClass = r.status_class || getAttendanceStatusClass(statusLabel);

            row.innerHTML = `
                <td class="font-600">${formatDateLong(r.date)}</td>
                <td>${inTime}</td>
                <td>${outTime}</td>
                <td>${r.working_hours || '—'}</td>
                <td><span class="status-badge-v2 ${statusClass}">${statusLabel || '—'}</span></td>
                <td><span class="status-msg-v2" title="${r.message || ''}">${r.message || '-'}</span></td>
                <td>
                    <button class="action-btn p-6" onclick="openAttendanceModal(${JSON.stringify({
                        date: formatDateLong(r.date),
                        in: inTime,
                        out: outTime,
                        status: r.status,
                        msg: r.message || '',
                        hours: r.working_hours || '—'
                    }).replace(/"/g, '&quot;')})">
                        <i data-lucide="info" size="14"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });

        updatePaginationInfo(start + 1, end, total);
        if (window.lucide) lucide.createIcons();
    }

    function renderCalendar() {
        const grid = document.getElementById('calendarGrid');
        if (!grid) return;
        grid.innerHTML = '';

        // Headers
        ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'].forEach(day => {
            const h = document.createElement('div');
            h.className = 'calendar-day-head-v2';
            h.textContent = day;
            grid.appendChild(h);
        });

        const [year, month] = monthFilter.value.split('-').map(Number);
        const startDay = window.HRM_CONFIG ? window.HRM_CONFIG.payroll_start_day : 21;
        const endDay = window.HRM_CONFIG ? window.HRM_CONFIG.payroll_end_day : 20;
        
        // Payroll Range: startDay of (month-1) to endDay of (month)
        const startDate = new Date(year, month - 2, startDay);
        const endDate = new Date(year, month - 1, endDay);
        
        const firstDay = startDate.getDay();

        // Month Title
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        const titleEl = document.getElementById('calendarMonthTitle');
        const rangeLabel = `${startDate.getDate()} ${monthNames[startDate.getMonth()]} – ${endDate.getDate()} ${monthNames[endDate.getMonth()]} ${endDate.getFullYear()}`;
        if (titleEl) titleEl.textContent = `Payroll: ${monthNames[month - 1]} ${year} (${rangeLabel})`;

        // Empty cells
        for (let i = 0; i < firstDay; i++) {
            const empty = document.createElement('div');
            empty.className = 'calendar-day-cell-v2 bg-light-soft';
            grid.appendChild(empty);
        }

        // Days in Range
        const logMap = {};
        allLogs.forEach(l => { 
            const d = new Date(l.date);
            const key = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
            logMap[key] = l; 
        });

        let current = new Date(startDate);
        while (current <= endDate) {
            const cell = document.createElement('div');
            cell.className = 'calendar-day-cell-v2';
            
            const key = current.getFullYear() + '-' + (current.getMonth() + 1) + '-' + current.getDate();
            const log = logMap[key];
            const dw = current.getDay();
            const isWeekend = (dw === 0 || dw === 6);

            let status = log ? (log.status || '').trim() : (isWeekend ? 'WEEKEND' : '');
            if (isWeekend && status === 'LEAVE') {
                status = 'WEEKEND';
            }
            let statusClass = getAttendanceStatusClass(status);

            let contentHtml = '';
            if (status === 'WEEKEND') {
                contentHtml = '<span class="time-info-v2">Week End</span>';
            } else if (status) {
                contentHtml = `<span class="status-badge-v2 ${statusClass}">${status}</span>`;
                if (log) {
                    if (log.status === 'ABSENT') {
                        contentHtml += '<div class="mt-4"><span class="time-info-v2">No Data</span></div>';
                    } else if (log.clock_in) {
                        contentHtml += `<div class="mt-4"><span class="time-info-v2">${formatTimeStr(log.clock_in)} - ${log.clock_out ? formatTimeStr(log.clock_out) : '--:--'}</span></div>`;
                    }
                }
            } else {
                contentHtml = '<span class="time-info-v2">No Record</span>';
            }

            const dayNum = current.getDate();
            const monthShort = current.toLocaleDateString('en-GB', { month: 'short' });

            cell.innerHTML = `
                <span class="day-num-v2">${dayNum} <small style="font-size: 10px; opacity: 0.6;">${monthShort}</small></span>
                <div class="day-content-v2">
                    ${contentHtml}
                </div>
            `;

            if (log) {
                cell.classList.add('pointer');
                cell.onclick = () => {
                    openAttendanceModal({
                        date: formatDateLong(log.date),
                        in: log.clock_in ? formatTimeStr(log.clock_in) : '--:--',
                        out: log.clock_out ? formatTimeStr(log.clock_out) : '--:--',
                        status: log.status,
                        msg: log.message || '',
                        hours: log.working_hours || '—'
                    });
                };
            }

            grid.appendChild(cell);
            current.setDate(current.getDate() + 1);
        }
    }

    function updatePaginationInfo(start, end, total) {
        const info = `Showing ${start} to ${end} of ${total} entries`;
        const pInfo = document.getElementById('paginationInfo');
        const tSum = document.getElementById('tableSummary');
        if (pInfo) pInfo.textContent = info;
        if (tSum) tSum.textContent = info;

        const totalPages = rowsPerPage === -1 ? 1 : Math.ceil(total / rowsPerPage);
        const pageNumbers = document.getElementById('pageNumbers');
        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = `action-btn ${i === currentPage ? 'btn-active' : ''}`;
                btn.textContent = i;
                btn.onclick = () => { currentPage = i; renderTable(); };
                pageNumbers.appendChild(btn);
            }
        }
        
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage === totalPages || totalPages === 0;
    }

    // --- Helpers ---
    function formatTimeStr(datetime) {
        if (!datetime) return '--:--';
        const date = new Date(datetime);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function formatDateLong(dateStr) {
        if (!dateStr) return '--';
        let date;
        const raw = String(dateStr).trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
            const [y, m, d] = raw.split('-').map(Number);
            date = new Date(y, m - 1, d);
        } else {
            date = new Date(dateStr);
        }
        if (isNaN(date.getTime())) return '--';
        const month = date.toLocaleDateString('en-GB', { month: 'short' });
        return date.getDate() + ' ' + month + ', ' + date.getFullYear();
    }

    // --- Events ---
    if (monthFilter) {
        monthFilter.onchange = () => { fetchLogs(); };
    }
    if (perPageSelect) {
        perPageSelect.onchange = () => {
            rowsPerPage = perPageSelect.value === 'all' ? -1 : parseInt(perPageSelect.value);
            currentPage = 1;
            renderTable();
        };
    }

    document.getElementById('prevPage').onclick = () => { if (currentPage > 1) { currentPage--; renderTable(); } };
    document.getElementById('nextPage').onclick = () => { 
        const totalPages = Math.ceil(allLogs.length / rowsPerPage);
        if (currentPage < totalPages) { currentPage++; renderTable(); } 
    };

    // Initial Fetch
    fetchLogs();

    // Start Polling (every 5 seconds)
    setInterval(fetchLogs, 5000);
});

// Global functions (needed because they are called from inline onclicks or other parts)
window.switchLogTab = function(tabId, btn) {
    document.querySelectorAll('.log-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.log-tab-content').forEach(c => c.classList.remove('active'));
    if (tabId === 'activity') {
        document.getElementById('activityLog').classList.add('active');
    } else {
        document.getElementById('attendanceCalendar').classList.add('active');
    }
};

window.openAttendanceModal = function(data) {
    const modal = document.getElementById('attendanceModal');
    if (!modal) return;

    document.getElementById('modalDetailDate').textContent = data.date || '—';
    document.getElementById('modalDetailIn').textContent = data.in || '—';
    document.getElementById('modalDetailOut').textContent = data.out || '—';
    document.getElementById('modalHours').textContent = data.hours || '—';
    
    const statusBadge = document.getElementById('modalStatus');
    statusBadge.textContent = data.status || '—';
    statusBadge.className = 'status-badge-v2 status-badge-modal';
    const st = (data.status || '').trim();
    const modalStatusClass = getAttendanceStatusClass(st);
    if (modalStatusClass && modalStatusClass !== 'status-v2-none') {
        statusBadge.classList.add(modalStatusClass);
    }

    document.getElementById('modalMsgInput').value = data.msg || '';

    document.body.style.overflow = 'hidden';
    modal.classList.add('active');
    
    if (window.lucide) lucide.createIcons();
};

window.closeAttendanceModal = function() {
    const m = document.getElementById('attendanceModal');
    if (m) m.classList.remove('active');
    document.body.style.overflow = 'auto';
};

window.saveAttendanceDetails = function() {
    const messageInput = document.getElementById('modalMsgInput');
    const dateEl = document.getElementById('modalDetailDate');
    
    if (!messageInput || !dateEl) return;

    const payload = {
        date: dateEl.textContent.trim(),
        message: messageInput.value.trim()
    };

    const saveBtn = document.querySelector('#attendanceModal .btn-primary');
    const originalContent = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i data-lucide="loader-2" class="spin" size="16"></i><span>Saving...</span>';
    if (window.lucide) lucide.createIcons();

    fetch('assets/api/attendance_handler.php?action=save_message', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalContent;
        if (window.lucide) lucide.createIcons();

        if (data.status === 'success') {
            showToast(data.message);
            closeAttendanceModal();
            // No reload needed anymore! Fetch logs will pick it up on next poll or we call it manually
            fetchLogs(); 
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalContent;
        console.error(err);
        showToast('Connection error.', 'error');
    });
};

window.showToast = function(msg, type = 'success') {
    Toastify({
        text: msg,
        duration: 3000,
        gravity: "top",
        position: "right",
        style: { background: type === 'success' ? "#10b981" : "#ef4444" }
    }).showToast();
};
