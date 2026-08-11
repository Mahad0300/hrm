<?php
// admin/attendance.php
$page_title = "Attendance Tracking";
$page_subtitle = "Monitor daily presence and work hours.";
include __DIR__ . '/../partials/hr/header.php';
// Logical Date: If before 9 AM, default to yesterday
$logical_date = date('H') < 9 ? date('Y-m-d', strtotime('-1 day')) : date('Y-m-d');
?>
<?php include __DIR__ . '/../partials/hr/sidebar.php'; ?>

<!-- Filters Card -->
<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="filter-item">
            <label class="admin-form-label font-12">Search Employee</label>
            <div class="search-box w-full">
                <i data-lucide="search" size="16"></i>
                <input type="text" id="employeeSearch" class="form-control" placeholder="Search employee...">
            </div>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Status</label>
            <select class="form-control" id="statusFilter">
                <option value="">Status (All)</option>
                <option value="PRESENT">Present Today (All)</option>
                <option value="ON TIME">On Time</option>
                <option value="LATE IN">Late In</option>
                <option value="ABSENT">Absent</option>
                <option value="HALF DAY">Half Day</option>
            </select>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Date</label>
            <input type="date" id="dateFilter" class="form-control" value="<?= $logical_date ?>">
        </div>
        <div class="filter-item text-right">
            <button type="button" class="btn btn-primary w-full h-full" onclick="openBulkModal(event)">
                <i data-lucide="layers" size="18"></i>
                <span>Bulk Attendance</span>
            </button>
        </div>
    </div>
</div>

<!-- Flatpickr Date Picker CDN & Custom Range Theme -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
/* Custom Flatpickr Theme - Soft Rounded Pills Matching Theme Palette (#6C4CF1) */
.flatpickr-calendar {
    border-radius: 16px !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 14px 30px rgba(108, 76, 241, 0.15), 0 4px 10px rgba(0, 0, 0, 0.05) !important;
    font-family: 'Inter', system-ui, sans-serif !important;
    padding: 12px 14px !important;
    background: #ffffff !important;
    z-index: 99999 !important;
    width: 325px !important;
    box-sizing: border-box !important;
}
.flatpickr-innerContainer {
    width: 100% !important;
}
.flatpickr-rContainer, .flatpickr-days, .dayContainer {
    width: 295px !important;
    min-width: 295px !important;
    max-width: 295px !important;
}
.flatpickr-months {
    align-items: center !important;
    padding-bottom: 8px !important;
}
.flatpickr-months .flatpickr-month {
    color: #0f172a !important;
    font-weight: 700 !important;
    font-size: 15px !important;
}
.flatpickr-current-month .numInputWrapper {
    font-weight: 700 !important;
}
.flatpickr-weekdays {
    margin-bottom: 6px !important;
    width: 295px !important;
}
span.flatpickr-weekday {
    color: #64748b !important;
    font-weight: 700 !important;
    font-size: 11px !important;
    width: 42px !important;
}
.flatpickr-day {
    border-radius: 50% !important;
    font-weight: 600 !important;
    font-size: 12px !important;
    color: #1e293b !important;
    border: none !important;
    height: 38px !important;
    line-height: 38px !important;
    max-width: 38px !important;
    margin: 2px 2px !important;
}
.flatpickr-day:hover {
    background: #f1f5f9 !important;
}
.flatpickr-day.selected, 
.flatpickr-day.startRange, 
.flatpickr-day.endRange {
    background: linear-gradient(135deg, #6C4CF1 0%, #5839D6 100%) !important;
    color: #ffffff !important;
    border-radius: 50% !important;
    box-shadow: 0 4px 12px rgba(108, 76, 241, 0.4) !important;
}
.flatpickr-day.inRange {
    background: #ede9fe !important;
    color: #5839d6 !important;
    border-radius: 20px !important;
    box-shadow: none !important;
}

/* Bulk Modal Calendar Mode Switcher */
.date-mode-switcher {
    display: inline-flex;
    background: #f1f5f9;
    padding: 2px;
    border-radius: 8px;
    gap: 2px;
}
.date-mode-btn {
    border: none;
    background: transparent;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.date-mode-btn.active {
    background: #6C4CF1;
    color: #ffffff;
    font-weight: 700;
    box-shadow: 0 2px 6px rgba(108, 76, 241, 0.25);
}
</style>

<!-- Bulk Attendance Modal -->
<div id="bulkAttendanceModal" class="modal-overlay">
    <div class="modal-content premium wide">
        <div class="modal-header">
            <div class="flex-center gap-12">
                <div class="type-icon-box primary">
                    <i data-lucide="layers" size="20"></i>
                </div>
                <div>
                    <h3 class="font-18 font-700 m-0">Bulk Attendance Management</h3>
                    <p class="font-12 text-light m-0">Manage multiple attendance records at once.</p>
                </div>
            </div>
            <button type="button" class="icon-btn" onclick="closeBulkModal(event)"><i data-lucide="x" size="20"></i></button>
        </div>
        <div class="modal-body p-0">
            <!-- Modal Filters -->
            <div class="p-24 bg-light-soft border-bottom">
                <div class="grid-4 gap-16">
                    <div class="form-group mb-0">
                        <label class="admin-form-label font-12">Department</label>
                        <select class="form-control" id="bulkDeptFilter">
                            <option value="">All Departments</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="admin-form-label font-12">Search Employee</label>
                        <input type="text" class="form-control" id="bulkEmpSearch" placeholder="Name or ID...">
                    </div>
                    <div class="form-group mb-0">
                        <div class="flex-between align-center mb-6">
                            <label class="admin-form-label font-12 m-0">Date</label>
                            <div class="date-mode-switcher">
                                <button type="button" class="date-mode-btn active" id="modeSingleBtn">Single</button>
                                <button type="button" class="date-mode-btn" id="modeRangeBtn">Range</button>
                            </div>
                        </div>
                        <div class="position-relative">
                            <input type="text" class="form-control bg-white cursor-pointer" id="bulkDatePickerInput" placeholder="Select date..." readonly value="<?= $logical_date ?>" style="padding-right: 28px; padding-left: 10px; font-size: 12px; font-weight: 600;">
                            <i data-lucide="calendar" size="16" class="text-light" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="admin-form-label font-12">Bulk Status</label>
                        <select class="form-control" id="bulkStatus">
                            <option value="">Select Status</option>
                            <option value="AUTO">Auto Attendance</option>
                            <option value="HOLIDAY">Holiday</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Employee Selection Table -->
            <div class="bulk-table-container" id="bulkTableContainer">
                <div class="bulk-loading-overlay">
                    <i data-lucide="loader-2" class="spin text-primary mb-12" size="32"></i>
                    <p class="font-600">Updating List...</p>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="40">
                                <label class="custom-checkbox m-0">
                                    <input type="checkbox" id="selectAllEmployees">
                                    <span class="checkmark"></span>
                                </label>
                            </th>
                            <th style="width: 33.33%;">EMPLOYEE</th>
                            <th style="width: 33.33%;">DEPARTMENT</th>
                            <th style="width: 33.33%; text-align: left !important;">SHIFT</th>
                        </tr>
                    </thead>
                    <tbody id="bulkEmpTableBody">
                        <!-- Fetched employees will go here -->
                        <tr>
                            <td colspan="4" class="text-center py-40">
                                <i data-lucide="loader-2" class="spin text-light mb-12" size="32"></i>
                                <p class="text-light">Loading employees...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer flex-between gap-12 bg-light-soft">
            <span class="font-13 text-light" id="selectedCount">0 employees selected</span>
            <div class="flex-center gap-12">
                <button type="button" class="btn btn-light px-24" onclick="closeBulkModal(event)">Cancel</button>
                <button type="button" class="btn btn-primary px-30" id="saveBulkBtn">
                    <i data-lucide="check" size="16"></i>
                    <span>Apply Bulk Update</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Stats -->
<div class="stat-grid" id="statsGrid">
    <div class="card stat-card">
        <svg class="attendance-card-shape" viewBox="0 0 200 100" preserveAspectRatio="none">
            <defs>
                <linearGradient id="grad-present" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#10b981" />
                    <stop offset="100%" stop-color="#059669" />
                </linearGradient>
            </defs>
            <path d="M0,0 L0,55 C0,75 30,75 45,55 C65,30 80,0 105,0 Z" fill="url(#grad-present)" opacity="0.08" />
            <path d="M0,100 L35,100 C75,100 95,25 115,25 C130,25 165,100 200,100 Z" fill="url(#grad-present)" opacity="0.12" />
        </svg>
        <div class="stat-header">
            <div class="stat-icon success">
                <i data-lucide="user-check"></i>
            </div>
        </div>
        <div class="stat-content">
            <h3 class="stat-value success" id="statPresent">0</h3>
            <p>Present Today</p>
        </div>
    </div>
    <div class="card stat-card">
        <svg class="attendance-card-shape" viewBox="0 0 200 100" preserveAspectRatio="none">
            <defs>
                <linearGradient id="grad-absent" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#ef4444" />
                    <stop offset="100%" stop-color="#dc2626" />
                </linearGradient>
            </defs>
            <path d="M0,0 L0,55 C0,75 30,75 45,55 C65,30 80,0 105,0 Z" fill="url(#grad-absent)" opacity="0.08" />
            <path d="M0,100 L35,100 C75,100 95,25 115,25 C130,25 165,100 200,100 Z" fill="url(#grad-absent)" opacity="0.12" />
        </svg>
        <div class="stat-header">
            <div class="stat-icon danger">
                <i data-lucide="user-x"></i>
            </div>
        </div>
        <div class="stat-content">
            <h3 class="stat-value danger" id="statAbsent">0</h3>
            <p>Absent Today</p>
        </div>
    </div>
    <div class="card stat-card">
        <svg class="attendance-card-shape" viewBox="0 0 200 100" preserveAspectRatio="none">
            <defs>
                <linearGradient id="grad-halfday" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#3b82f6" />
                    <stop offset="100%" stop-color="#2563eb" />
                </linearGradient>
            </defs>
            <path d="M0,0 L0,55 C0,75 30,75 45,55 C65,30 80,0 105,0 Z" fill="url(#grad-halfday)" opacity="0.08" />
            <path d="M0,100 L35,100 C75,100 95,25 115,25 C130,25 165,100 200,100 Z" fill="url(#grad-halfday)" opacity="0.12" />
        </svg>
        <div class="stat-header">
            <div class="stat-icon info">
                <i data-lucide="clock-3"></i>
            </div>
        </div>
        <div class="stat-content">
            <h3 class="stat-value info" id="statHalfDay">0</h3>
            <p>Half Day</p>
        </div>
    </div>
    <div class="card stat-card">
        <svg class="attendance-card-shape" viewBox="0 0 200 100" preserveAspectRatio="none">
            <defs>
                <linearGradient id="grad-latein" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f59e0b" />
                    <stop offset="100%" stop-color="#d97706" />
                </linearGradient>
            </defs>
            <path d="M0,0 L0,55 C0,75 30,75 45,55 C65,30 80,0 105,0 Z" fill="url(#grad-latein)" opacity="0.08" />
            <path d="M0,100 L35,100 C75,100 95,25 115,25 C130,25 165,100 200,100 Z" fill="url(#grad-latein)" opacity="0.12" />
        </svg>
        <div class="stat-header">
            <div class="stat-icon warning">
                <i data-lucide="alert-circle"></i>
            </div>
        </div>
        <div class="stat-content">
            <h3 class="stat-value warning" id="statLate">0</h3>
            <p>Late In</p>
        </div>
    </div>
</div>

<!-- Table Tools -->
<div class="flex-between mb-24 px-4 mt-24">
    <div class="flex-center gap-10">
        <span class="font-13 text-light">Show</span>
        <select class="form-control font-13 font-600 per-page-select" id="perPageSelect">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="all">All</option>
        </select>
        <span class="font-13 text-light">entries</span>
    </div>
    <div class="text-right">
        <span class="font-13 text-light" id="tableSummary">Showing 0 to 0 of 0 entries</span>
    </div>
</div>

<!-- Attendance Table -->
<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>DATE</th>
                    <th>EMPLOYEE</th>
                    <th>CHECK IN</th>
                    <th>CHECK OUT</th>
                    <th>WORK HOURS</th>
                    <th>STATUS</th>
                    <th class="text-right px-30">VIEW LOGS</th>
                </tr>
            </thead>
            <tbody id="attendanceTableBody">
                <!-- Data injected by JS -->
            </tbody>
        </table>
    </div>
    <div class="p-24 flex-between border-top">
        <span class="font-13 text-light" id="paginationInfo">Showing 0 to 0 of 0 entries</span>
        <div class="flex-center gap-8" id="paginationControls">
            <button class="action-btn" id="prevPage"><i data-lucide="chevron-left" size="16"></i></button>
            <div id="pageNumbers" class="flex-center gap-8"></div>
            <button class="action-btn" id="nextPage"><i data-lucide="chevron-right" size="16"></i></button>
        </div>
    </div>
</div>

</div>

<script src="<?= \App\Core\View::asset('js/shared/attendance.js') ?>?v=<?= time() ?>"></script>
<?php include __DIR__ . '/../partials/hr/footer.php'; ?>
