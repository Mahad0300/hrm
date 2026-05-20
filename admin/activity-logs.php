<?php
$page_title = "Activity Logs";
$page_subtitle = "Monitor system activities and employee actions in real-time.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Filters Card -->
<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="filter-item">
            <label class="admin-form-label font-12">Search</label>
            <div class="search-box w-full">
                <i data-lucide="search" size="16"></i>
                <input type="text" id="activityFilterSearch" class="form-control" placeholder="Search by employee name or ID...">
            </div>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Module</label>
            <select class="form-control">
                <option value="">All Modules</option>
                <option value="employees">Employees</option>
                <option value="attendance">Attendance</option>
                <option value="payroll">Payroll</option>
                <option value="settings">Settings</option>
                <option value="auth">Authentication</option>
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">All Actions</option>
                <option value="create">Create</option>
                <option value="update">Update</option>
                <option value="delete">Delete</option>
                <option value="login">Login</option>
            </select>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Date</label>
            <input type="date" class="form-control">
        </div>
    </div>
</div>

<!-- Activity Logs Table -->
<div class="flex-between mb-24 px-4">
    <div class="flex-center gap-10">
        <span class="font-13 text-light">Show</span>
        <select class="form-control font-13 font-600 per-page-select" id="perPageSelect">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="all">All</option>
        </select>
        <span class="font-13 text-light">entries</span>
    </div>
    <div class="text-right">
        <span class="font-13 text-light" id="tableSummary">Showing 1 to 10 of 10 entries</span>
    </div>
</div>

<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>EMPLOYEE</th>
                    <th>DATE & TIME</th>
                    <th>MODULE</th>
                    <th>ACTION</th>
                    <th>DESCRIPTION</th>
                    <th>IP ADDRESS</th>
                </tr>
            </thead>
            <tbody id="activityTableBody">
                <tr>
                    <td colspan="6" class="text-center p-40">
                        <div class="loading-spinner"></div>
                        <p class="mt-10 text-light">Loading activities...</p>
                    </td>
                </tr>
            </tbody>
        </table>
        </tbody>
        </table>
    </div>
    <div class="p-24 flex-between border-top">
        <span class="font-13 text-light" id="paginationInfo">Showing 1 to 10 of 10 entries</span>
        <div class="flex-center gap-8" id="paginationControls">
            <button class="action-btn" id="prevPage"><i data-lucide="chevron-left" size="16"></i></button>
            <div id="pageNumbers" class="flex-center gap-8">
                <button class="action-btn btn-active">1</button>
            </div>
            <button class="action-btn" id="nextPage"><i data-lucide="chevron-right" size="16"></i></button>
        </div>
    </div>
</div>



<script src="assets/js/activity-logs.js"></script>
<?php include 'includes/footer.php'; ?>