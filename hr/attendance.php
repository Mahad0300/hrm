<?php 
$page_title = "Attendance Tracking";
$page_subtitle = "Monitor daily presence and work hours.";
include 'includes/header.php'; 
?>
<?php include 'includes/sidebar.php'; ?>


<!-- Filters Card -->
<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="search-box">
            <i data-lucide="search" size="18"></i>
            <input type="text" placeholder="Search employee...">
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">Department (All)</option>
                <option value="eng">Engineering</option>
                <option value="hr">HR</option>
                <option value="sales">Sales</option>
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">Status (All)</option>
                <option value="present">Present</option>
                <option value="late">Late</option>
                <option value="absent">Absent</option>
            </select>
        </div>
        <div class="filter-item">
            <input type="date" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
    </div>
</div>

<!-- Attendance Stats -->
<div class="stat-grid">
    <div class="card stat-card">
        <div class="stat-header">
            <div class="stat-icon success">
                <i data-lucide="user-check"></i>
            </div>
            <div class="trend-up">
                <i data-lucide="trending-up" size="14"></i>
                <span>92%</span>
            </div>
        </div>
        <div class="stat-content">
            <h3 class="stat-value success">456</h3>
            <p>Present Today</p>
        </div>
        <div class="stat-footer">
            <span>Across all departments</span>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-header">
            <div class="stat-icon danger">
                <i data-lucide="user-x"></i>
            </div>
            <div class="trend-down">
                <i data-lucide="trending-down" size="14"></i>
                <span>-3%</span>
            </div>
        </div>
        <div class="stat-content">
            <h3 class="stat-value danger">12</h3>
            <p>Absent Today</p>
        </div>
        <div class="stat-footer">
            <span>26 on approved leave</span>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-header">
            <div class="stat-icon info">
                <i data-lucide="clock-3"></i>
            </div>
            <div class="trend-up">
                <i data-lucide="trending-up" size="14"></i>
                <span>+5</span>
            </div>
        </div>
        <div class="stat-content">
            <h3 class="stat-value info">08</h3>
            <p>Half Day</p>
        </div>
        <div class="stat-footer">
            <span>Early departures today</span>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-header">
            <div class="stat-icon warning">
                <i data-lucide="alert-circle"></i>
            </div>
            <div class="trend-up">
                <i data-lucide="trending-up" size="14"></i>
                <span>+2</span>
            </div>
        </div>
        <div class="stat-content">
            <h3 class="stat-value warning">14</h3>
            <p>Late In</p>
        </div>
        <div class="stat-footer">
            <span>Grace period utilized</span>
        </div>
    </div>
</div>

<!-- Table Tools: Per Page & Summary -->
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

<!-- Attendance Table -->
<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>EMPLOYEE</th>
                    <th>CHECK IN</th>
                    <th>CHECK OUT</th>
                    <th>WORK HOURS</th>
                    <th>STATUS</th>
                    <th class="text-right px-30">LOGS</th>
                </tr>
            </thead>
            <tbody id="attendanceTableBody">
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email">EM-4820</span>
                            </div>
                        </div>
                    </td>
                    <td>09:05 AM</td>
                    <td>06:12 PM</td>
                    <td>9h 07m</td>
                    <td><span class="badge badge-success">Present</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <a href="attendance-log.php" class="action-btn action-btn-view" title="View Logs"><i data-lucide="history" size="14"></i></a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Oliver Mitchell</span>
                                <span class="email">EM-4821</span>
                            </div>
                        </div>
                    </td>
                    <td>09:45 AM</td>
                    <td>--:--</td>
                    <td>--</td>
                    <td><span class="badge badge-warning">Late</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <a href="attendance-log.php" class="action-btn action-btn-view" title="View Logs"><i data-lucide="history" size="14"></i></a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Sophia Reynolds</span>
                                <span class="email">EM-4822</span>
                            </div>
                        </div>
                    </td>
                    <td>--:--</td>
                    <td>--:--</td>
                    <td>--</td>
                    <td><span class="badge badge-danger">Absent</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <a href="attendance-log.php" class="action-btn action-btn-view" title="View Logs"><i data-lucide="history" size="14"></i></a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">David Chen</span>
                                <span class="email">EM-4825</span>
                            </div>
                        </div>
                    </td>
                    <td>09:00 AM</td>
                    <td>01:00 PM</td>
                    <td>4h 00m</td>
                    <td><span class="badge badge-info">Half Day</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <a href="attendance-log.php" class="action-btn action-btn-view" title="View Logs"><i data-lucide="history" size="14"></i></a>
                        </div>
                    </td>
                </tr>
                 <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email">EM-4820</span>
                            </div>
                        </div>
                    </td>
                    <td>09:05 AM</td>
                    <td>06:12 PM</td>
                    <td>9h 07m</td>
                    <td><span class="badge badge-success">Present</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <a href="attendance-log.php" class="action-btn action-btn-view" title="View Logs"><i data-lucide="history" size="14"></i></a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Oliver Mitchell</span>
                                <span class="email">EM-4821</span>
                            </div>
                        </div>
                    </td>
                    <td>09:45 AM</td>
                    <td>--:--</td>
                    <td>--</td>
                    <td><span class="badge badge-warning">Late</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <a href="attendance-log.php" class="action-btn action-btn-view" title="View Logs"><i data-lucide="history" size="14"></i></a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Sophia Reynolds</span>
                                <span class="email">EM-4822</span>
                            </div>
                        </div>
                    </td>
                    <td>--:--</td>
                    <td>--:--</td>
                    <td>--</td>
                    <td><span class="badge badge-danger">Absent</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <a href="attendance-log.php" class="action-btn action-btn-view" title="View Logs"><i data-lucide="history" size="14"></i></a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">David Chen</span>
                                <span class="email">EM-4825</span>
                            </div>
                        </div>
                    </td>
                    <td>09:00 AM</td>
                    <td>01:00 PM</td>
                    <td>4h 00m</td>
                    <td><span class="badge badge-info">Half Day</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <a href="attendance-log.php" class="action-btn action-btn-view" title="View Logs"><i data-lucide="history" size="14"></i></a>
                        </div>
                    </td>
                </tr>
                 <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email">EM-4820</span>
                            </div>
                        </div>
                    </td>
                    <td>09:05 AM</td>
                    <td>06:12 PM</td>
                    <td>9h 07m</td>
                    <td><span class="badge badge-success">Present</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <a href="attendance-log.php" class="action-btn action-btn-view" title="View Logs"><i data-lucide="history" size="14"></i></a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Oliver Mitchell</span>
                                <span class="email">EM-4821</span>
                            </div>
                        </div>
                    </td>
                    <td>09:45 AM</td>
                    <td>--:--</td>
                    <td>--</td>
                    <td><span class="badge badge-warning">Late</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <a href="attendance-log.php" class="action-btn action-btn-view" title="View Logs"><i data-lucide="history" size="14"></i></a>
                        </div>
                    </td>
                </tr>
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

<script src="assets/js/attendance.js"></script>
<?php include 'includes/footer.php'; ?>
