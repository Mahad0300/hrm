<?php
// admin/attendance.php
$page_title = "Attendance Tracking";
$page_subtitle = "Monitor daily presence and work hours.";
include 'includes/header.php';
// Logical Date: If before 7 AM, default to yesterday
$logical_date = date('H') < 7 ? date('Y-m-d', strtotime('-1 day')) : date('Y-m-d');
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Filters Card -->
<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="search-box">
            <i data-lucide="search" size="18"></i>
            <input type="text" id="employeeSearch" placeholder="Search employee...">
        </div>
        <div class="filter-item">
            <select class="form-control" id="statusFilter">
                <option value="">Status (All)</option>
                <option value="ON TIME">On Time</option>
                <option value="LATE IN">Late In</option>
                <option value="ABSENT">Absent</option>
                <option value="HALF DAY">Half Day</option>
            </select>
        </div>
        <div class="filter-item">
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
                        <label class="admin-form-label font-12">Date</label>
                        <input type="date" class="form-control" id="bulkDateInput" value="<?= $logical_date ?>">
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

<script src="assets/js/attendance.js"></script>
<?php include 'includes/footer.php'; ?>