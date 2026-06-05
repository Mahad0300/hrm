<?php
$page_title = "Access Control";
$page_subtitle = "Control which pages and actions are allowed in the application.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<div class="role-management-container">
    <div class="card mb-24">
        <div class="flex-between flex-wrap gap-15">
            <div class="flex-center gap-15">
                <div class="stat-icon primary">
                    <i data-lucide="shield-check"></i>
                </div>
                <div>
                    <h4 class="font-18 font-700 m-0">Permissions Matrix</h4>
                    <p class="font-12 text-light m-0">Toggle view and action rights per module or page</p>
                </div>
            </div>
            <div class="flex-center gap-12">
                <button class="btn-primary px-30" id="savePermissions">
                    <i data-lucide="save" size="16"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="data-table mb-0" id="permissionsTable">
                <thead>
                    <tr>
                        <th style="width: 250px;">Module / Page</th>
                        <th class="text-center">View Access</th>
                        <th class="text-center">Create</th>
                        <th class="text-center">Edit / Update</th>
                        <th class="text-center">Delete</th>
                        <th class="text-center">Export / PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Main Menu (sidebar) -->
                    <tr class="perm-matrix-section">
                        <td colspan="6" class="perm-matrix-section__cell">Main Menu</td>
                    </tr>
                    <tr data-page="index">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="layout-dashboard" size="16"></i>
                                <span>Dashboard</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>

                    <!-- Organization -->
                    <tr class="perm-matrix-section">
                        <td colspan="6" class="perm-matrix-section__cell">Organization</td>
                    </tr>
                    <tr data-page="employees">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="users" size="16"></i>
                                <span>Employees</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="attendance">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="calendar-check" size="16"></i>
                                <span>Attendance</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="leave-management">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="clock" size="16"></i>
                                <span>Leave Management</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="new-joining">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="user-plus" size="16"></i>
                                <span>New Joining</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="hierarchy">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="network" size="16"></i>
                                <span>Hierarchy</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="kpi-management">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="line-chart" size="16"></i>
                                <span>KPI Management</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="event-calendar">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="calendar" size="16"></i>
                                <span>Event Calendar</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                    </tr>

                    <!-- Job Management (submenu) -->
                    <tr class="perm-matrix-section">
                        <td colspan="6" class="perm-matrix-section__cell">Job Management</td>
                    </tr>
                    <tr data-page="job-list">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="list" size="16"></i>
                                <span>Job Postings</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="create-job">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="plus-circle" size="16"></i>
                                <span>Create New Job</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                    </tr>
                    <tr data-page="job-candidates">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="users" size="16"></i>
                                <span>Candidate Pool</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="interviews">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="calendar" size="16"></i>
                                <span>Interviews</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>

                    <!-- Administration -->
                    <tr class="perm-matrix-section">
                        <td colspan="6" class="perm-matrix-section__cell">Administration</td>
                    </tr>
                    <tr data-page="payroll">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="banknote" size="16"></i>
                                <span>Payroll</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="activity-logs">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="history" size="16"></i>
                                <span>Activity Logs</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="announcements">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="megaphone" size="16"></i>
                                <span>Announcements</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                    </tr>
                    <tr data-page="notifications">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="bell" size="16"></i>
                                <span>Notifications</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                    </tr>
                    <tr data-page="it-support">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="headset" size="16"></i>
                                <span>IT Helpdesk</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                    </tr>

                    <!-- System (Settings submenu) -->
                    <tr class="perm-matrix-section">
                        <td colspan="6" class="perm-matrix-section__cell">System</td>
                    </tr>
                    <tr data-page="shifts">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="plus-circle" size="16"></i>
                                <span>Add Shift</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                    </tr>
                    <tr data-page="department-management">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="building-2" size="16"></i>
                                <span>Dept Management</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="role-management">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="shield-check" size="16"></i>
                                <span>Access Control</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                    </tr>
                    <tr data-page="policy-management">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="file-text" size="16"></i>
                                <span>Policy Management</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="payroll-settings">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="calculator" size="16"></i>
                                <span>Payroll Cycle</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                    </tr>

                    <!-- Detail & linked pages (not in sidebar) -->
                    <tr class="perm-matrix-section">
                        <td colspan="6" class="perm-matrix-section__cell">Detail &amp; Linked Pages</td>
                    </tr>
                    <tr data-page="employee-profile">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="user" size="16"></i>
                                <span>Employee Profile</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="attendance-log">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="clipboard-list" size="16"></i>
                                <span>Attendance History</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="edit-job">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="pencil" size="16"></i>
                                <span>Edit Job</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                    </tr>
                    <tr data-page="candidate-detail">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="user-search" size="16"></i>
                                <span>Candidate Detail</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="kpi-report">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="bar-chart-2" size="16"></i>
                                <span>KPI Scorecard</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="payslip-print">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="file-output" size="16"></i>
                                <span>Payslip Print</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script src="assets/js/access-control.js"></script>
<?php include 'includes/footer.php'; ?>