// admin/assets/js/attendance.js

document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('attendanceTableBody');
    const dateFilter = document.getElementById('dateFilter');
    const employeeSearch = document.getElementById('employeeSearch');
    const statusFilter = document.getElementById('statusFilter');
    const perPageSelect = document.getElementById('perPageSelect');
    
    let allData = [];
    let filteredData = [];
    let currentPage = 1;
    let rowsPerPage = 10;
    let selectedEmpId = null;
    let selectedBulkIds = new Set(); // To persist selection in bulk modal

    // --- Data Fetching ---
    function fetchAttendance() {
        const date = dateFilter.value;
        fetch(`assets/api/attendance_handler.php?action=fetch_daily&date=${date}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    allData = res.data;
                    applyFilters();
                    updateStats();
                } else {
                    showToast(res.message, 'error');
                }
            });
    }

    function applyFilters() {
        const searchTerm = employeeSearch.value.toLowerCase();
        const statusTerm = statusFilter.value;

        filteredData = allData.filter(r => {
            const empCode = 'EMP-' + String(r.emp_id).padStart(3, '0');
            const matchesSearch = r.full_name.toLowerCase().includes(searchTerm) || empCode.toLowerCase().includes(searchTerm);
            const matchesStatus = !statusTerm || r.status === statusTerm;
            return matchesSearch && matchesStatus;
        });

        currentPage = 1;
        renderTable();
    }

    function updateStats() {
        const stats = {
            present: allData.filter(r => r.status === 'ON TIME' || r.status === 'LATE IN' || r.status === 'HALF DAY').length,
            absent: allData.filter(r => r.status === 'ABSENT').length,
            halfDay: allData.filter(r => r.status === 'HALF DAY').length,
            late: allData.filter(r => r.status === 'LATE IN').length
        };

        document.getElementById('statPresent').textContent = stats.present;
        document.getElementById('statAbsent').textContent = stats.absent;
        document.getElementById('statHalfDay').textContent = stats.halfDay;
        document.getElementById('statLate').textContent = stats.late;
    }

    function renderTable() {
        tableBody.innerHTML = '';
        const total = filteredData.length;
        const start = (currentPage - 1) * rowsPerPage;
        const end = rowsPerPage === -1 ? total : Math.min(start + rowsPerPage, total);
        const paginated = rowsPerPage === -1 ? filteredData : filteredData.slice(start, end);

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
            const avatar = r.profile_pic ? `../${r.profile_pic}` : '../images/profile-image/default-avatar.svg';
            const empCode = 'EMP-' + String(r.emp_id).padStart(3, '0');
            const displayDate = formatDateLong(dateFilter.value);

            row.innerHTML = `
                <td class="font-600">${displayDate}</td>
                <td>
                    <div class="emp-profile">
                        <img src="${avatar}" class="emp-avatar" alt="Avatar" onerror="this.src='../images/profile-image/default-avatar.svg'">
                        <div class="emp-info">
                            <span class="name">${r.full_name}</span>
                            <span class="email">${empCode}</span>
                        </div>
                    </div>
                </td>
                <td>${inTime}</td>
                <td>${outTime}</td>
                <td>${r.working_hours || '--'}</td>
                <td><span class="status-badge-v2 ${statusClass}">${r.status || 'NO RECORD'}</span></td>
                <td class="text-right px-30">
                    <div class="btn-group justify-end gap-8">
                        <a href="attendance-log.php?emp_id=${r.emp_id}" class="action-btn action-btn-view" title="View Logs">
                            <i data-lucide="history" size="14"></i>
                        </a>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        });

        updatePagination(start + 1, end, total);
        lucide.createIcons();
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

    function getStatusClass(status) {
        switch (status) {
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

    // --- Event Listeners ---
    dateFilter.addEventListener('change', fetchAttendance);
    employeeSearch.addEventListener('input', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    perPageSelect.addEventListener('change', () => {
        rowsPerPage = perPageSelect.value === 'all' ? -1 : parseInt(perPageSelect.value);
        currentPage = 1;
        renderTable();
    });

    document.getElementById('prevPage').onclick = () => { if (currentPage > 1) { currentPage--; renderTable(); } };
    document.getElementById('nextPage').onclick = () => { 
        const totalPages = Math.ceil(filteredData.length / rowsPerPage);
        if (currentPage < totalPages) { currentPage++; renderTable(); } 
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

    // Initial Load
    fetchAttendance();

    // --- Bulk Attendance Logic ---
    window.openBulkModal = function(e) {
        if(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        selectedBulkIds.clear(); // Reset on fresh open
        updateSelectedCount();
        
        const modal = document.getElementById('bulkAttendanceModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Fetch Init Data (Departments)
        fetch('assets/api/attendance_handler.php?action=fetch_bulk_init')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const deptSelect = document.getElementById('bulkDeptFilter');
                    const currentVal = deptSelect.value;
                    deptSelect.innerHTML = '<option value="">All Departments</option>';
                    res.departments.forEach(d => {
                        deptSelect.innerHTML += `<option value="${d.id}">${d.name}</option>`;
                    });
                    deptSelect.value = currentVal;
                }
            });

        fetchBulkEmployees();
    };

    window.closeBulkModal = function(e) {
        if(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        document.getElementById('bulkAttendanceModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    };

    function fetchBulkEmployees() {
        const dept = document.getElementById('bulkDeptFilter').value;
        const search = document.getElementById('bulkEmpSearch').value;
        const date = document.getElementById('bulkDateInput').value;

        const container = document.getElementById('bulkTableContainer');
        const tableBody = document.getElementById('bulkEmpTableBody');
        
        // Prevent loader blink on very fast connections (like localhost)
        // Only show the overlay if the request takes more than 150ms
        const loaderTimeout = setTimeout(() => {
            container.classList.add('is-loading');
        }, 150);
        
        if (window.lucide) lucide.createIcons();

        fetch(`assets/api/attendance_handler.php?action=fetch_bulk_employees&dept_id=${dept}&search=${search}&date=${date}`)
            .then(res => res.json())
            .then(res => {
                clearTimeout(loaderTimeout);
                container.classList.remove('is-loading');
                if (res.status === 'success') {
                    renderBulkEmployees(res.data);
                } else {
                    showToast(res.message, 'error');
                }
            })
            .catch(err => {
                clearTimeout(loaderTimeout);
                container.classList.remove('is-loading');
                showToast('Failed to fetch employees', 'error');
            });
    }

    function renderBulkEmployees(data) {
        const tableBody = document.getElementById('bulkEmpTableBody');
        tableBody.innerHTML = '';

        if (data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-40 text-light">No employees found matching criteria.</td></tr>';
            return;
        }

        data.forEach(r => {
            const row = document.createElement('tr');
            const empCode = 'EMP-' + String(r.emp_id).padStart(3, '0');

            row.innerHTML = `
                <td>
                    <label class="custom-checkbox m-0">
                        <input type="checkbox" class="emp-checkbox" value="${r.emp_id}" ${selectedBulkIds.has(String(r.emp_id)) ? 'checked' : ''}>
                        <span class="checkmark"></span>
                    </label>
                </td>
                <td>
                    <div class="emp-info">
                        <span class="name font-600">${r.full_name}</span>
                        <span class="email font-12">${empCode}</span>
                    </div>
                </td>
                <td><span class="font-13">${r.department_name || '—'}</span></td>
                <td style="text-align: left !important;"><span class="font-12 text-light">${r.shift_name || '—'}<br>${formatSimpleTime(r.start_time)} - ${formatSimpleTime(r.end_time)}</span></td>
            `;
            tableBody.appendChild(row);
        });

        // Re-bind individual checkbox listeners to persist state
        document.querySelectorAll('.emp-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.checked) {
                    selectedBulkIds.add(this.value);
                } else {
                    selectedBulkIds.delete(this.value);
                    document.getElementById('selectAllEmployees').checked = false;
                }
                updateSelectedCount();
            });
        });

        if (window.lucide) lucide.createIcons();
    }

    function updateSelectedCount() {
        const count = selectedBulkIds.size;
        document.getElementById('selectedCount').textContent = `${count} employees selected`;
    }

    // --- Bulk Selection ---
    document.getElementById('selectAllEmployees').addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.emp-checkbox').forEach(cb => {
            cb.checked = isChecked;
            if (isChecked) selectedBulkIds.add(cb.value);
            else selectedBulkIds.delete(cb.value);
        });
        updateSelectedCount();
    });

    function formatSimpleTime(timeStr) {
        if (!timeStr) return '--:--';
        const parts = timeStr.split(':');
        let h = parseInt(parts[0]);
        const m = parts[1];
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return `${String(h).padStart(2, '0')}:${m} ${ampm}`;
    }

    // Modal Events
    document.getElementById('bulkDeptFilter').addEventListener('change', (e) => {
        if(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        fetchBulkEmployees();
    });
    document.getElementById('bulkEmpSearch').addEventListener('input', (e) => {
        if(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        fetchBulkEmployees();
    });
    document.getElementById('bulkDateInput').addEventListener('change', (e) => {
        if(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        fetchBulkEmployees();
    });

    document.getElementById('saveBulkBtn').onclick = function(e) {
        if(e) e.preventDefault();
        const selectedIds = Array.from(selectedBulkIds);
        const statusType = document.getElementById('bulkStatus').value;
        const date = document.getElementById('bulkDateInput').value;

        if (selectedIds.length === 0) {
            showToast('Please select at least one employee.', 'error');
            return;
        }
        if (!statusType) {
            showToast('Please select a bulk status.', 'error');
            return;
        }

        const btn = this;
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" class="spin" size="16"></i><span>Processing...</span>';
        if (window.lucide) lucide.createIcons();

        fetch('assets/api/attendance_handler.php?action=process_bulk_attendance', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                emp_ids: selectedIds,
                status_type: statusType,
                date: date
            })
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = originalContent;
            if (window.lucide) lucide.createIcons();

            if (res.status === 'success') {
                showToast(res.message);
                closeBulkModal();
                fetchAttendance(); // Refresh main table
            } else {
                showToast(res.message, 'error');
            }
        });
    };
});
