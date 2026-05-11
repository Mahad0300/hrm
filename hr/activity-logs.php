<?php
$page_title = "Activity Logs";
$page_subtitle = "Monitor system activities and employee actions in real-time.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Filters Card -->
<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="search-box">
            <i data-lucide="search" size="18"></i>
            <input type="text" placeholder="Search by employee name or ID...">
        </div>
        <div class="filter-item">
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
                <!-- Row 1: Employee Update -->
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email font-12 text-light">EM-4820</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex-column">
                            <span class="font-14 font-600">Oct 24, 2023</span>
                            <span class="font-12 text-light">10:45 AM</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light">Employees</span>
                    </td>
                    <td>
                        <span class="badge badge-info">Update</span>
                    </td>
                    <td class="allow-wrap">Updated banking information for employee EM-5021</td>
                    <td><code class="font-12">192.168.1.45</code></td>
                </tr>

                <!-- Row 2: Attendance Create -->
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Oliver Mitchell</span>
                                <span class="email font-12 text-light">EM-4821</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex-column">
                            <span class="font-14 font-600">Oct 24, 2023</span>
                            <span class="font-12 text-light">09:12 AM</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light">Attendance</span>
                    </td>
                    <td>
                        <span class="badge badge-success">Create</span>
                    </td>
                    <td class="allow-wrap">Manually added clock-in for Oct 23 (EM-4821)</td>
                    <td><code class="font-12">182.164.3.21</code></td>
                </tr>

                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email font-12 text-light">EM-4820</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex-column">
                            <span class="font-14 font-600">Oct 24, 2023</span>
                            <span class="font-12 text-light">10:45 AM</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light">Employees</span>
                    </td>
                    <td>
                        <span class="badge badge-info">Update</span>
                    </td>
                    <td class="allow-wrap">Updated banking information for employee EM-5021</td>
                    <td><code class="font-12">192.168.1.45</code></td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email font-12 text-light">EM-4820</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex-column">
                            <span class="font-14 font-600">Oct 24, 2023</span>
                            <span class="font-12 text-light">10:45 AM</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light">Employees</span>
                    </td>
                    <td>
                        <span class="badge badge-info">Update</span>
                    </td>
                    <td class="allow-wrap">Updated banking information for employee EM-5021</td>
                    <td><code class="font-12">192.168.1.45</code></td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email font-12 text-light">EM-4820</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex-column">
                            <span class="font-14 font-600">Oct 24, 2023</span>
                            <span class="font-12 text-light">10:45 AM</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light">Employees</span>
                    </td>
                    <td>
                        <span class="badge badge-info">Update</span>
                    </td>
                    <td class="allow-wrap">Updated banking information for employee EM-5021</td>
                    <td><code class="font-12">192.168.1.45</code></td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email font-12 text-light">EM-4820</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex-column">
                            <span class="font-14 font-600">Oct 24, 2023</span>
                            <span class="font-12 text-light">10:45 AM</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light">Employees</span>
                    </td>
                    <td>
                        <span class="badge badge-info">Update</span>
                    </td>
                    <td class="allow-wrap">Updated banking information for employee EM-5021</td>
                    <td><code class="font-12">192.168.1.45</code></td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email font-12 text-light">EM-4820</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex-column">
                            <span class="font-14 font-600">Oct 24, 2023</span>
                            <span class="font-12 text-light">10:45 AM</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light">Employees</span>
                    </td>
                    <td>
                        <span class="badge badge-info">Update</span>
                    </td>
                    <td class="allow-wrap">Updated banking information for employee EM-5021</td>
                    <td><code class="font-12">192.168.1.45</code></td>
                </tr>

                <!-- Row 3: Login Action -->
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Sophia Reynolds</span>
                                <span class="email font-12 text-light">EM-4822</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex-column">
                            <span class="font-14 font-600">Oct 24, 2023</span>
                            <span class="font-12 text-light">08:00 AM</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light">Auth</span>
                    </td>
                    <td>
                        <span class="badge badge-primary">Login</span>
                    </td>
                    <td class="allow-wrap">System login successful from Chrome v118</td>
                    <td><code class="font-12">110.23.54.120</code></td>
                </tr>

                <!-- Row 4: Delete Action -->
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">James Wilson</span>
                                <span class="email font-12 text-light">EM-4819</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex-column">
                            <span class="font-14 font-600">Oct 23, 2023</span>
                            <span class="font-12 text-light">04:30 PM</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light">Settings</span>
                    </td>
                    <td>
                        <span class="badge badge-danger">Delete</span>
                    </td>
                    <td class="allow-wrap">Deleted old shift schedule: "Summer Shift 2022"</td>
                    <td><code class="font-12">45.210.12.8</code></td>
                </tr>

                <!-- Row 5: Payroll Action -->
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email font-12 text-light">EM-4820</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex-column">
                            <span class="font-14 font-600">Oct 23, 2023</span>
                            <span class="font-12 text-light">11:20 AM</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light">Payroll</span>
                    </td>
                    <td>
                        <span class="badge badge-warning">Export</span>
                    </td>
                    <td class="allow-wrap">Exported September 2023 payroll report (PDF)</td>
                    <td><code class="font-12">192.168.1.45</code></td>
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