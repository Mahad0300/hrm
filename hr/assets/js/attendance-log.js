function switchLogTab(tabId, btn) {
    document.querySelectorAll('.log-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.log-tab-content').forEach(c => c.classList.remove('active'));
    if(tabId === 'activity') {
        document.getElementById('activityLog').classList.add('active');
    } else {
        document.getElementById('attendanceCalendar').classList.add('active');
    }
}

// --- Time Picker Helpers ---
function parseTime(str) {
    if (!str || str === '--:--') return { hour: 9, minute: 0, ampm: 'AM' };
    const match = String(str).trim().match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    if (!match) return { hour: 9, minute: 0, ampm: 'AM' };
    let h = parseInt(match[1], 10);
    const m = parseInt(match[2], 10);
    const ampm = (match[3] || 'AM').toUpperCase();
    if (ampm === 'PM' && h !== 12) h += 12;
    if (ampm === 'AM' && h === 12) h = 0;
    const hour12 = h === 0 ? 12 : (h > 12 ? h - 12 : h);
    return { hour: hour12, minute: m, ampm };
}

function formatTime(hour, minute, ampm) {
    const h = String(hour).padStart(2, '0');
    const m = String(minute).padStart(2, '0');
    return h + ':' + m + ' ' + ampm;
}

function buildTimePickerColumns() {
    const buildColumn = (containerId, type) => {
        const el = document.getElementById(containerId);
        if (!el || el.dataset.built === 'yes') return;
        el.dataset.built = 'yes';
        if (type === 'hour') {
            for (let i = 1; i <= 12; i++) {
                const opt = document.createElement('div');
                opt.className = 'time-picker-option';
                opt.dataset.val = i;
                opt.textContent = String(i).padStart(2, '0');
                el.appendChild(opt);
            }
        } else if (type === 'minute') {
            for (let i = 0; i < 60; i++) {
                const opt = document.createElement('div');
                opt.className = 'time-picker-option';
                opt.dataset.val = i;
                opt.textContent = String(i).padStart(2, '0');
                el.appendChild(opt);
            }
        } else if (type === 'ampm') {
            ['AM', 'PM'].forEach(v => {
                const opt = document.createElement('div');
                opt.className = 'time-picker-option';
                opt.dataset.val = v;
                opt.textContent = v;
                el.appendChild(opt);
            });
        }
    };
    buildColumn('hourIn', 'hour');
    buildColumn('minuteIn', 'minute');
    buildColumn('ampmIn', 'ampm');
    buildColumn('hourOut', 'hour');
    buildColumn('minuteOut', 'minute');
    buildColumn('ampmOut', 'ampm');
}

function setTimePickerSelection(which, timeStr) {
    const parsed = parseTime(timeStr);
    const prefix = which === 'in' ? '' : 'Out';
    ['hour', 'minute', 'ampm'].forEach(part => {
        const col = document.getElementById(part + (which === 'in' ? 'In' : 'Out'));
        if (!col) return;
        col.querySelectorAll('.time-picker-option').forEach(opt => {
            const val = opt.dataset.val;
            const match = (part === 'hour' && parseInt(val, 10) === parsed.hour) ||
                (part === 'minute' && parseInt(val, 10) === parsed.minute) ||
                (part === 'ampm' && val === parsed.ampm);
            opt.classList.toggle('selected', !!match);
        });
    });
}

function getTimePickerValue(which) {
    const suffix = which === 'in' ? 'In' : 'Out';
    let hour = 9, minute = 0, ampm = 'AM';
    ['hour', 'minute', 'ampm'].forEach(part => {
        const col = document.getElementById(part + suffix);
        if (!col) return;
        const selected = col.querySelector('.time-picker-option.selected');
        if (selected) {
            const v = selected.dataset.val;
            if (part === 'hour') hour = parseInt(v, 10);
            else if (part === 'minute') minute = parseInt(v, 10);
            else ampm = v;
        }
    });
    return formatTime(hour, minute, ampm);
}

function bindTimePickerOptions(which) {
    const dropdown = document.getElementById('timePicker' + (which === 'in' ? 'In' : 'Out'));
    const displayEl = document.getElementById(which === 'in' ? 'modalInDisplay' : 'modalOutDisplay');
    const hiddenEl = document.getElementById(which === 'in' ? 'modalIn' : 'modalOut');
    if (!dropdown || !displayEl || !hiddenEl) return;

    dropdown.querySelectorAll('.time-picker-option').forEach(opt => {
        opt.addEventListener('click', function () {
            const col = this.closest('.time-picker-column');
            col.querySelectorAll('.time-picker-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            const value = getTimePickerValue(which);
            displayEl.textContent = value;
            hiddenEl.value = value;
            if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
        });
    });
}

function toggleTimePicker(which) {
    const id = 'timePicker' + (which === 'in' ? 'In' : 'Out');
    const otherId = which === 'in' ? 'timePickerOut' : 'timePickerIn';
    const dropdown = document.getElementById(id);
    const other = document.getElementById(otherId);
    if (other) other.classList.remove('active');
    if (dropdown) dropdown.classList.toggle('active');
}

function closeAllTimePickers() {
    ['timePickerIn', 'timePickerOut'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('active');
    });
}

function openAttendanceModal(data) {
    const modal = document.getElementById('attendanceModal');
    if(!modal) return;

    buildTimePickerColumns();

    document.getElementById('modalDateDisplay').innerText = data.date;
    document.getElementById('modalShift').innerText = data.shift;
    document.getElementById('modalHours').innerText = data.hours;
    document.getElementById('modalIn').value = data.in;
    document.getElementById('modalOut').value = data.out;
    document.getElementById('modalInDisplay').innerText = data.in;
    document.getElementById('modalOutDisplay').innerText = data.out;
    setTimePickerSelection('in', data.in);
    setTimePickerSelection('out', data.out);

    document.getElementById('modalStatus').innerText = data.status;
    document.getElementById('modalMsg').innerText = data.msg;
    document.getElementById('modalMsgTime').innerText = 'at ' + data.msgTime;

    const statusBadge = document.getElementById('modalStatus');
    statusBadge.className = 'status-badge-v2 status-badge-modal';
    if(data.status === 'ON TIME') statusBadge.classList.add('status-v2-ontime');
    else if(data.status === 'LATE IN') statusBadge.classList.add('status-v2-late');
    else if(data.status === 'ABSENT') statusBadge.classList.add('status-v2-absent');
    else if(data.status === 'HALF DAY') statusBadge.classList.add('status-v2-halfday');
    else if(data.status === 'WEEKEND') statusBadge.classList.add('status-v2-holiday');

    bindTimePickerOptions('in');
    bindTimePickerOptions('out');
    closeAllTimePickers();

    modal.classList.add('active');
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
}

function closeAttendanceModal() {
    closeAllTimePickers();
    if (typeof closeModal === 'function') {
        closeModal('attendanceModal');
    } else {
        document.getElementById('attendanceModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

function saveAttendanceDetails() {
    const clockIn = document.getElementById('modalIn').value;
    const clockOut = document.getElementById('modalOut').value;

    console.log('Saving attendance:', { clockIn, clockOut });

    const saveBtn = document.querySelector('#attendanceModal .btn-primary');
    const originalContent = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i data-lucide="loader-2" class="spin" size="16"></i><span>Saving...</span>';
    lucide.createIcons();

    setTimeout(() => {
        saveBtn.innerHTML = originalContent;
        lucide.createIcons();
        closeAttendanceModal();
        alert('Attendance updated successfully!');
    }, 800);
}

document.addEventListener('DOMContentLoaded', function () {
    buildTimePickerColumns();
    bindTimePickerOptions('in');
    bindTimePickerOptions('out');

    // --- Pagination & Table Logic ---
    const tableBody = document.getElementById('attendanceTableBody');
    const perPageSelect = document.getElementById('perPageSelect');
    const paginationInfo = document.getElementById('paginationInfo');
    const tableSummary = document.getElementById('tableSummary');
    const pageNumbersContainer = document.getElementById('pageNumbers');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');

    if (!tableBody || !perPageSelect || !paginationInfo) return;

    let allRows = Array.from(tableBody.querySelectorAll('tr'));
    let currentPage = 1;
    let rowsPerPage = parseInt(perPageSelect.value) || 10;

    function updateTable() {
        const totalRows = allRows.length;
        const totalPages = rowsPerPage === -1 ? 1 : Math.ceil(totalRows / rowsPerPage);
        
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const start = (currentPage - 1) * rowsPerPage;
        const end = rowsPerPage === -1 ? totalRows : start + rowsPerPage;

        allRows.forEach((row, index) => {
            if (rowsPerPage === -1) {
                row.style.display = '';
            } else {
                row.style.display = (index >= start && index < end) ? '' : 'none';
            }
        });

        // Update Info Text
        const showingStart = totalRows === 0 ? 0 : start + 1;
        const showingEnd = Math.min(end, totalRows);
        const infoText = `Showing ${showingStart} to ${showingEnd} of ${totalRows} entries`;
        paginationInfo.textContent = infoText;
        if (tableSummary) tableSummary.textContent = infoText;

        updatePaginationControls(totalPages);
    }

    function updatePaginationControls(totalPages) {
        pageNumbersContainer.innerHTML = '';
        
        // Only show pagination if more than one page
        if (totalPages <= 1) {
            prevBtn.classList.add('hidden');
            nextBtn.classList.add('hidden');
            return;
        } else {
            prevBtn.classList.remove('hidden');
            nextBtn.classList.remove('hidden');
        }

        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
        prevBtn.style.opacity = currentPage === 1 ? '0.5' : '1';
        nextBtn.style.opacity = currentPage === totalPages ? '0.5' : '1';

        // simple page numbers
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.className = `action-btn ${i === currentPage ? 'btn-active' : ''}`;
            btn.textContent = i;
            btn.onclick = () => {
                currentPage = i;
                updateTable();
            };
            pageNumbersContainer.appendChild(btn);
        }
    }

    perPageSelect.addEventListener('change', () => {
        const val = perPageSelect.value;
        rowsPerPage = val === 'all' ? -1 : parseInt(val);
        currentPage = 1;
        updateTable();
    });

    prevBtn.onclick = () => {
        if (currentPage > 1) {
            currentPage--;
            updateTable();
        }
    };

    nextBtn.onclick = () => {
        const totalPages = rowsPerPage === -1 ? 1 : Math.ceil(allRows.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            updateTable();
        }
    };

    // Initial load
    updateTable();
});

window.addEventListener('click', function(event) {
    if (!event.target.closest('.time-picker-trigger') && !event.target.closest('.time-picker-dropdown')) {
        closeAllTimePickers();
    }
});
