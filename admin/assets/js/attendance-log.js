// admin/assets/js/attendance-log.js

document.addEventListener('DOMContentLoaded', function () {
    const empId = document.getElementById('currentEmpId').value;
    const monthFilter = document.getElementById('monthFilter');
    const tableBody = document.getElementById('attendanceTableBody');
    const perPageSelect = document.getElementById('perPageSelect');
    
    let allLogs = [];
    let currentPage = 1;
    let rowsPerPage = 10;
    let employeeInfo = null;
    let selectedDate = null;
    let currentRecordMessage = '';
    let currentShiftStart = '';
    let currentShiftEnd = '';

    function fetchLog() {
        const month = monthFilter.value;
        fetch(`assets/api/attendance_handler.php?action=fetch_log&emp_id=${empId}&month=${month}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const newDataStr = JSON.stringify(res.data) + JSON.stringify(res.employee);
                    if (window.lastDataStr !== newDataStr) {
                        allLogs = res.data;
                        employeeInfo = res.employee;
                        if (employeeInfo) {
                            updateHeader();
                            renderTable();
                            renderCalendar();
                        }
                        window.lastDataStr = newDataStr;
                    }
                }
            })
            .catch(err => console.error('Polling error:', err));
    }

    function updateHeader() {
        const empCode = 'EMP-' + String(employeeInfo.id).padStart(3, '0');
        document.getElementById('headerEmpName').textContent = employeeInfo.name;
        document.getElementById('headerEmpDetail').textContent = `${employeeInfo.role} • ${empCode}`;
        document.getElementById('headerEmpAvatar').src = employeeInfo.profile_pic ? `../${employeeInfo.profile_pic}` : '../images/profile-image/default-avatar.svg';
    }

    function renderTable() {
        tableBody.innerHTML = '';
        const total = allLogs.length;
        const start = (currentPage - 1) * rowsPerPage;
        const end = rowsPerPage === -1 ? total : Math.min(start + rowsPerPage, total);
        const paginated = rowsPerPage === -1 ? allLogs : allLogs.slice(start, end);

        if (paginated.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-40">No records found.</td></tr>';
            updatePagination(0, 0, 0);
            return;
        }

        paginated.forEach(r => {
            const row = document.createElement('tr');
            const inTime = r.clock_in ? formatTimeStr(r.clock_in) : '--:--';
            const outTime = r.clock_out ? formatTimeStr(r.clock_out) : '--:--';
            const statusClass = getStatusClass(r.status);

            row.innerHTML = `
                <td>${formatDateLong(r.date)}</td>
                <td>${r.shift_name} (${formatSimpleTime(r.shift_start)} - ${formatSimpleTime(r.shift_end)})</td>
                <td>${inTime}</td>
                <td>${outTime}</td>
                <td>${r.working_hours || '—'}</td>
                <td><span class="status-badge-v2 ${statusClass}">${r.status}</span></td>
                <td><span class="status-msg-v2" title="${r.message || ''}">${r.message || '-'}</span></td>
                <td>
                    <button class="action-btn p-6" onclick="openEditModal(${JSON.stringify(r).replace(/"/g, '&quot;')})">
                        <i data-lucide="edit-2" size="14"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });

        updatePagination(start + 1, end, total);
        lucide.createIcons();
    }

    function renderCalendar() {
        const grid = document.getElementById('calendarGrid');
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
        document.getElementById('calendarMonthTitle').textContent = `Payroll: ${monthNames[month - 1]} ${year}`;

        // Empty cells for the first row to align weekdays
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
            cell.className = 'calendar-day-cell-v2 pointer';
            
            const key = current.getFullYear() + '-' + (current.getMonth() + 1) + '-' + current.getDate();
            const log = logMap[key];
            const dw = current.getDay();
            const isWeekend = (dw === 0 || dw === 6);

            let status = log ? log.status : (isWeekend ? 'WEEKEND' : '');
            let statusClass = getStatusClass(status);

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

            // For display, we might want to show the month too if it changes
            const dayNum = current.getDate();
            const monthShort = current.toLocaleDateString('en-GB', { month: 'short' });
            
            cell.innerHTML = `
                <span class="day-num-v2">${dayNum} <small style="font-size: 10px; opacity: 0.6;">${monthShort}</small></span>
                <div class="day-content-v2">
                    ${contentHtml}
                </div>
            `;

            const currentData = log ? {...log} : {
                date: current.toISOString().split('T')[0],
                emp_id: empId,
                shift_name: employeeInfo.shift_name,
                shift_start: employeeInfo.start_time,
                shift_end: employeeInfo.end_time,
                status: isWeekend ? 'WEEKEND' : 'NO RECORD',
                clock_in: null,
                clock_out: null,
                message: '',
                working_hours: '--'
            };

            cell.onclick = () => {
                openEditModal(currentData);
            };

            grid.appendChild(cell);
            current.setDate(current.getDate() + 1);
        }
    }

    function updatePagination(start, end, total) {
        const info = `Showing ${start} to ${end} of ${total} entries`;
        document.getElementById('paginationInfo').textContent = info;
        document.getElementById('tableSummary').textContent = info;
        const totalPages = rowsPerPage === -1 ? 1 : Math.ceil(total / rowsPerPage);
        const pageNumbers = document.getElementById('pageNumbers');
        pageNumbers.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.className = `action-btn ${i === currentPage ? 'btn-active' : ''}`;
            btn.textContent = i;
            btn.onclick = () => { currentPage = i; renderTable(); };
            pageNumbers.appendChild(btn);
        }
        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage === totalPages;
    }

    // --- Modal Shared Logic ---
    window.openEditModal = function(data) {
        selectedDate = data.date;
        currentRecordMessage = data.message || '';
        currentShiftStart = data.shift_start || '';
        currentShiftEnd = data.shift_end || '';
        const modal = document.getElementById('attendanceModal');
        
        document.getElementById('modalDateDisplay').textContent = formatDateLong(data.date);
        document.getElementById('modalShift').textContent = (data.shift_name || 'No Shift') + (data.shift_start ? ` (${formatSimpleTime(data.shift_start)} - ${formatSimpleTime(data.shift_end)})` : '');
        document.getElementById('modalHours').textContent = data.working_hours || '--';
        document.getElementById('modalStatus').textContent = data.status;
        document.getElementById('modalStatus').className = 'status-badge-v2 status-badge-modal ' + getStatusClass(data.status);
        document.getElementById('modalMsg').textContent = data.message || '-';

        const inTime = data.clock_in ? formatTimeOnly(data.clock_in) : '09:00 AM';
        const outTime = data.clock_out ? formatTimeOnly(data.clock_out) : '06:00 PM';
        
        document.getElementById('modalIn').value = inTime;
        document.getElementById('modalInDisplay').textContent = inTime;
        document.getElementById('modalOut').value = outTime;
        document.getElementById('modalOutDisplay').textContent = outTime;

        buildTimePickerColumns();
        setTimePickerSelection('in', inTime);
        setTimePickerSelection('out', outTime);
        bindTimePickerOptions('in');
        bindTimePickerOptions('out');

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        calculateWorkingHours();
        lucide.createIcons();
    };

    window.closeAttendanceModal = function() {
        document.getElementById('attendanceModal').classList.remove('active');
        document.body.style.overflow = 'auto';
        closeAllTimePickers();
    };

    document.getElementById('saveAttendanceBtn').onclick = function() {
        const payload = {
            emp_id: empId,
            date: selectedDate,
            clock_in: document.getElementById('modalIn').value,
            clock_out: document.getElementById('modalOut').value,
            message: currentRecordMessage
        };

        const btn = this;
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" class="spin" size="16"></i><span>Saving...</span>';
        lucide.createIcons();

        fetch('assets/api/attendance_handler.php?action=update_attendance', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = originalContent;
            lucide.createIcons();
            if (res.status === 'success') {
                showToast(res.message);
                closeAttendanceModal();
                fetchLog();
            } else {
                showToast(res.message, 'error');
            }
        });
    };

    // --- Helpers ---
    function formatSimpleTime(timeStr) {
        if (!timeStr) return '--:--';
        // Handle HH:MM:SS or HH:MM
        const parts = timeStr.split(':');
        let h = parseInt(parts[0]);
        const m = parts[1];
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return `${String(h).padStart(2, '0')}:${m} ${ampm}`;
    }
    function formatTimeStr(datetime) {
        if (!datetime) return '--:--';
        const date = new Date(datetime);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    function formatTimeOnly(datetime) {
        const date = new Date(datetime);
        let h = date.getHours();
        const m = date.getMinutes();
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')} ${ampm}`;
    }
    function formatDateLong(dateStr) {
        return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    function getStatusClass(status) {
        switch (status) {
            case 'ON TIME': return 'status-v2-ontime';
            case 'LATE IN': return 'status-v2-late';
            case 'HALF DAY': return 'status-v2-halfday';
            case 'ABSENT': return 'status-v2-absent';
            case 'WEEKEND':
            case 'HOLIDAY': return 'status-v2-holiday';
            default: return 'status-v2-none';
        }
    }

    // --- Time Picker (Copy-paste shared) ---
    function buildTimePickerColumns() {
        const buildColumn = (containerId, type) => {
            const el = document.getElementById(containerId);
            if (!el || el.dataset.built === 'yes') return;
            el.dataset.built = 'yes';
            if (type === 'hour') { for (let i = 1; i <= 12; i++) { const opt = document.createElement('div'); opt.className = 'time-picker-option'; opt.dataset.val = i; opt.textContent = String(i).padStart(2, '0'); el.appendChild(opt); } }
            else if (type === 'minute') { for (let i = 0; i < 60; i++) { const opt = document.createElement('div'); opt.className = 'time-picker-option'; opt.dataset.val = i; opt.textContent = String(i).padStart(2, '0'); el.appendChild(opt); } }
            else if (type === 'ampm') { ['AM', 'PM'].forEach(v => { const opt = document.createElement('div'); opt.className = 'time-picker-option'; opt.dataset.val = v; opt.textContent = v; el.appendChild(opt); }); }
        };
        buildColumn('hourIn', 'hour'); buildColumn('minuteIn', 'minute'); buildColumn('ampmIn', 'ampm');
        buildColumn('hourOut', 'hour'); buildColumn('minuteOut', 'minute'); buildColumn('ampmOut', 'ampm');
    }
    function setTimePickerSelection(which, timeStr) {
        const match = timeStr.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
        if (!match) return;
        const h = parseInt(match[1]); const m = parseInt(match[2]); const ap = match[3].toUpperCase();
        const suffix = which === 'in' ? 'In' : 'Out';
        selectOption('hour' + suffix, h);
        selectOption('minute' + suffix, m);
        selectOption('ampm' + suffix, ap);
    }
    function selectOption(colId, val) {
        const col = document.getElementById(colId); if (!col) return;
        col.querySelectorAll('.time-picker-option').forEach(opt => { opt.classList.toggle('selected', String(opt.dataset.val) == String(val)); });
    }
    function bindTimePickerOptions(which) {
        const suffix = which === 'in' ? 'In' : 'Out';
        ['hour', 'minute', 'ampm'].forEach(part => {
            const col = document.getElementById(part + suffix);
            col.querySelectorAll('.time-picker-option').forEach(opt => {
                opt.onclick = function() {
                    col.querySelectorAll('.time-picker-option').forEach(o => o.classList.remove('selected'));
                    this.classList.add('selected');
                    updatePickerDisplay(which);
                };
            });
        });
    }
    function updatePickerDisplay(which) {
        const suffix = which === 'in' ? 'In' : 'Out';
        const h = document.querySelector(`#hour${suffix} .selected`)?.dataset.val || '12';
        const m = document.querySelector(`#minute${suffix} .selected`)?.dataset.val || '00';
        const ap = document.querySelector(`#ampm${suffix} .selected`)?.dataset.val || 'AM';
        const formatted = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')} ${ap}`;
        document.getElementById(`modal${which === 'in' ? 'In' : 'Out'}Display`).textContent = formatted;
        document.getElementById(`modal${which === 'in' ? 'In' : 'Out'}`).value = formatted;
        calculateWorkingHours();
    }

    function calculateWorkingHours() {
        const inStr = document.getElementById('modalIn').value;
        const outStr = document.getElementById('modalOut').value;
        
        if (!inStr || !outStr || inStr === '--:--' || outStr === '--:--') {
            document.getElementById('modalHours').textContent = '--';
            return;
        }

        const parseTime = (str) => {
            const match = str.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
            if (!match) return { h: 0, m: 0 };
            let h = parseInt(match[1]);
            let m = parseInt(match[2]);
            const ap = match[3].toUpperCase();
            if (ap === 'PM' && h < 12) h += 12;
            if (ap === 'AM' && h === 12) h = 0;
            return { h, m };
        };

        const tIn = parseTime(inStr);
        const tOut = parseTime(outStr);

        let dateIn = new Date(2000, 0, 1, tIn.h, tIn.m);
        let dateOut = new Date(2000, 0, 1, tOut.h, tOut.m);

        // Shift times for overnight logic
        // currentShiftStart/End are in HH:MM:SS format
        const parseShift = (str) => {
            if (!str) return { h: 0, m: 0 };
            const [h, m] = str.split(':').map(Number);
            return { h, m };
        };
        const sStart = parseShift(currentShiftStart);
        const sEnd = parseShift(currentShiftEnd);
        
        const shiftOvernight = (sStart.h > sEnd.h || (sStart.h === sEnd.h && sStart.m > sEnd.m));
        
        if (shiftOvernight && (dateOut < dateIn)) {
            dateOut.setDate(dateOut.getDate() + 1);
        }

        let diff = (dateOut - dateIn) / 1000;
        if (diff < 0) diff += 86400;

        const hrs = Math.floor(diff / 3600);
        const mins = Math.floor((diff % 3600) / 60);
        
        document.getElementById('modalHours').textContent = `${hrs}h ${String(mins).padStart(2, '0')}m`;
    }
    window.toggleTimePicker = function(which) {
        const dropdown = document.getElementById('timePicker' + (which === 'in' ? 'In' : 'Out'));
        const other = document.getElementById('timePicker' + (which === 'in' ? 'Out' : 'In'));
        other.classList.remove('active'); dropdown.classList.toggle('active');
    };
    window.closeAllTimePickers = function() { document.querySelectorAll('.time-picker-dropdown').forEach(d => d.classList.remove('active')); };
    
    // --- Global Helpers ---
    window.switchLogTab = function(tabId, btn) {
        document.querySelectorAll('.log-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.log-tab-content').forEach(c => c.classList.remove('active'));
        if(tabId === 'activity') document.getElementById('activityLog').classList.add('active');
        else document.getElementById('attendanceCalendar').classList.add('active');
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

    // --- Init ---
    monthFilter.addEventListener('change', fetchLog);
    perPageSelect.addEventListener('change', () => {
        rowsPerPage = perPageSelect.value === 'all' ? -1 : parseInt(perPageSelect.value);
        currentPage = 1; renderTable();
    });
    document.getElementById('prevPage').onclick = () => { if (currentPage > 1) { currentPage--; renderTable(); } };
    document.getElementById('nextPage').onclick = () => { 
        const totalPages = Math.ceil(allLogs.length / rowsPerPage);
        if (currentPage < totalPages) { currentPage++; renderTable(); } 
    };

    fetchLog();

    // Start Polling (every 5 seconds)
    setInterval(fetchLog, 5000);
});
