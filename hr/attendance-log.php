<?php 
$page_title = "Attendance History";
$page_subtitle = "Viewing detailed attendance records for Emma Williams";
include 'includes/header.php'; 
?>
<?php include 'includes/sidebar.php'; ?>

    <div class="attendance-log-header px-30 pb-10 mt-neg-10">
        <div class="flex-between align-start w-full">
            <div class="greeting-area">
                <div class="flex-center gap-12 mb-8">
                    <a href="attendance.php" class="action-btn no-bg border" title="Back to Attendance">
                        <i data-lucide="arrow-left" size="18"></i>
                    </a>
                    <h1 class="font-24 font-700 ls-05">Attendance Log</h1>
                </div>
                <div class="flex-center gap-12 mb-20">
                    <div class="emp-profile">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                        <div class="emp-info">
                            <span class="name font-16 font-600">Emma Williams</span>
                            <span class="email font-12 text-light">Product Manager • EM-4820</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header-actions flex-center gap-16">
                <div class="log-tabs segmented-tabs">
                    <button class="log-tab-btn active" onclick="switchLogTab('activity', this)">
                        <i data-lucide="list-ordered" size="16"></i>
                        <span>Activity</span>
                    </button>
                    <button class="log-tab-btn" onclick="switchLogTab('calendar', this)">
                        <i data-lucide="calendar" size="16"></i>
                        <span>Calendar</span>
                    </button>
                </div>
                <div class="filter-item">
                    <input type="month" class="form-control" value="2026-09">
                </div>
            </div>
        </div>
    </div>

    <div class="attendance-log-container p-30 pt-0">
            <!-- Tab Content: Activity Log -->
            <div id="activityLog" class="log-tab-content active">
                
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

                <div class="card p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>DATE</th>
                                    <th>SHIFT</th>
                                    <th>CHECK IN</th>
                                    <th>CHECK OUT</th>
                                    <th>STATUS</th>
                                    <th>MESSAGE</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceTableBody">
                                <?php 
                                $mockData = [
                                    ['15 Sep, 2026', 'A (09:00 AM - 06:00 PM)', '09:05 AM', '06:12 PM', 'ON TIME', 'Working from home...', 'status-v2-ontime'],
                                    ['14 Sep, 2026', 'A (09:00 AM - 06:00 PM)', '09:45 AM', '06:15 PM', 'LATE IN', 'Traffic jam at main road', 'status-v2-late'],
                                    ['13 Sep, 2026', 'A (09:00 AM - 06:00 PM)', '09:00 AM', '01:00 PM', 'HALF DAY', 'Family event in evening', 'status-v2-halfday'],
                                    ['12 Sep, 2026', 'A (09:00 AM - 06:00 PM)', '--:--', '--:--', 'ABSENT', 'Medical leave requested', 'status-v2-absent'],
                                    ['11 Sep, 2026', 'A (09:00 AM - 06:00 PM)', '08:55 AM', '06:05 PM', 'ON TIME', '-', 'status-v2-ontime'],
                                    ['10 Sep, 2026', 'A (09:00 AM - 06:00 PM)', '09:02 AM', '06:10 PM', 'ON TIME', 'Morning team meeting', 'status-v2-ontime'],
                                    ['09 Sep, 2026', 'A (09:00 AM - 06:00 PM)', '09:15 AM', '06:00 PM', 'LATE IN', 'Slightly late today', 'status-v2-late'],
                                    ['08 Sep, 2026', 'A (09:00 AM - 06:00 PM)', '08:50 AM', '06:15 PM', 'ON TIME', 'Arrived early', 'status-v2-ontime'],
                                    ['07 Sep, 2026', 'A (09:00 AM - 06:00 PM)', '09:00 AM', '06:00 PM', 'ON TIME', '-', 'status-v2-ontime'],
                                    ['06 Sep, 2026', 'A (09:00 AM - 06:00 PM)', '--:--', '--:--', 'WEEKEND', 'Sunday', 'status-v2-holiday'],
                                ];
                                foreach($mockData as $row): ?>
                                <tr>
                                    <td><?= $row[0] ?></td>
                                    <td><?= $row[1] ?></td>
                                    <td><?= $row[2] ?></td>
                                    <td><?= $row[3] ?></td>
                                    <td><span class="status-badge-v2 <?= $row[6] ?>"><?= $row[4] ?></span></td>
                                    <td><span class="status-msg-v2" title="<?= $row[5] ?>"><?= $row[5] ?></span></td>
                                    <td><button class="action-btn p-6" onclick="openAttendanceModal({
                                        date: '<?= $row[0] ?>',
                                        shift: '<?= $row[1] ?>',
                                        in: '<?= $row[2] ?>',
                                        out: '<?= $row[3] ?>',
                                        status: '<?= $row[4] ?>',
                                        msg: '<?= $row[5] ?>',
                                        msgTime: '09:10 AM',
                                        hours: '9h 07m'
                                    })"><i data-lucide="info" size="14"></i></button></td>
                                </tr>
                                <?php endforeach; ?>
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
            </div>

            <!-- Tab Content: Calendar View -->
            <div id="attendanceCalendar" class="log-tab-content">
                <div class="calendar-card">
                    <div class="calendar-header-v2 border-bottom">
                        <div class="flex-between">
                            <h3 class="font-18 font-700 m-0">September 2026</h3>
                            <div class="flex-center gap-12">
                                <div class="flex-center gap-8">
                                    <span class="w-12 h-12 rounded-full status-v2-ontime"></span>
                                    <span class="font-12 text-light">On-time</span>
                                </div>
                                <div class="flex-center gap-8">
                                    <span class="w-12 h-12 rounded-full status-v2-late"></span>
                                    <span class="font-12 text-light">Late</span>
                                </div>
                                <div class="flex-center gap-8">
                                    <span class="w-12 h-12 rounded-full status-v2-absent"></span>
                                    <span class="font-12 text-light">Absent</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="calendar-grid-v2">
                        <!-- Day Headers -->
                        <div class="calendar-day-head-v2">SUN</div>
                        <div class="calendar-day-head-v2">MON</div>
                        <div class="calendar-day-head-v2">TUE</div>
                        <div class="calendar-day-head-v2">WED</div>
                        <div class="calendar-day-head-v2">THU</div>
                        <div class="calendar-day-head-v2">FRI</div>
                        <div class="calendar-day-head-v2">SAT</div>
                        
                        <!-- Empty cells for start of month alignment if needed -->
                        <?php for($e=0; $e<2; $e++): ?>
                            <div class="calendar-day-cell-v2 bg-light-soft"></div>
                        <?php endfor; ?>

                        <!-- Actual Days -->
                        <?php for($d=1; $d<=30; $d++): ?>
                            <div class="calendar-day-cell-v2 pointer" onclick="openAttendanceModal({
                                date: '<?= $d ?> Sep, 2026',
                                shift: 'A (09:00 AM - 06:00 PM)',
                                in: '09:00 AM',
                                out: '06:00 PM',
                                status: 'ON TIME',
                                msg: 'Regular work day',
                                msgTime: '09:05 AM',
                                hours: '9h 00m'
                            })">
                                <span class="day-num-v2"><?= $d ?></span>
                                <div class="day-content-v2">
                                    <?php if($d == 15 || $d == 11 || $d == 10 || $d == 9): ?>
                                        <span class="status-badge-v2 status-v2-ontime">ON TIME</span>
                                        <span class="time-info-v2">09:00 AM - 06:00 PM</span>
                                    <?php elseif($d == 14): ?>
                                        <span class="status-badge-v2 status-v2-late">LATE</span>
                                        <span class="time-info-v2">09:45 AM - 06:15 PM</span>
                                    <?php elseif($d == 13): ?>
                                        <span class="status-badge-v2 status-v2-halfday">HALF DAY</span>
                                        <span class="time-info-v2">09:00 AM - 01:00 PM</span>
                                    <?php elseif($d == 12): ?>
                                        <span class="status-badge-v2 status-v2-absent">ABSENT</span>
                                        <span class="time-info-v2">NO DATA</span>
                                    <?php elseif($d == 6 || $d == 7): ?>
                                        <span class="font-10 text-light italic">No Record</span>
                                    <?php else: ?>
                                        <span class="font-10 text-light italic">No Record</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Attendance Detail Modal -->
    <div id="attendanceModal" class="modal-overlay"> <!-- Changed to modal-overlay to match style.js active class usage -->
        <div class="modal-content premium">
            <div class="modal-header">
                <div class="flex-center gap-12">
                    <div class="type-icon-box primary">
                        <i data-lucide="clock" size="20"></i>
                    </div>
                    <div>
                        <h3 class="font-18 font-700 m-0">Attendance Details</h3>
                        <p class="font-12 text-light m-0" id="modalDateDisplay">15 Sep, 2026</p>
                    </div>
                </div>
                <button type="button" class="icon-btn" onclick="closeAttendanceModal()" aria-label="Close"><i data-lucide="x" size="20"></i></button>
            </div>
            <div class="modal-body">
                <div class="attendance-details-grid mb-24">
                    <div class="detail-item">
                        <span class="label">Shift</span>
                        <span class="value" id="modalShift">A (09:00 AM - 06:00 PM)</span>
                    </div>
                    <div class="detail-item text-right">
                        <span class="label">Working Hours</span>
                        <span class="value" id="modalHours">9h 07m</span>
                    </div>
                </div>

                <div class="form-grid-2 mb-24 time-picker-grid">
                    <div class="form-group time-picker-group">
                        <label class="admin-form-label">Clock In</label>
                        <div class="time-picker-trigger" id="triggerTimeIn" onclick="toggleTimePicker('in')">
                            <i data-lucide="clock" size="18"></i>
                            <span id="modalInDisplay">09:05 AM</span>
                        </div>
                        <input type="hidden" id="modalIn" value="09:05 AM">
                        <div id="timePickerIn" class="time-picker-dropdown">
                            <div class="time-picker-column" id="hourIn"></div>
                            <div class="time-picker-column" id="minuteIn"></div>
                            <div class="time-picker-column" id="ampmIn"></div>
                        </div>
                    </div>
                    <div class="form-group time-picker-group">
                        <label class="admin-form-label">Clock Out</label>
                        <div class="time-picker-trigger" id="triggerTimeOut" onclick="toggleTimePicker('out')">
                            <i data-lucide="clock" size="18"></i>
                            <span id="modalOutDisplay">06:12 PM</span>
                        </div>
                        <input type="hidden" id="modalOut" value="06:12 PM">
                        <div id="timePickerOut" class="time-picker-dropdown">
                            <div class="time-picker-column" id="hourOut"></div>
                            <div class="time-picker-column" id="minuteOut"></div>
                            <div class="time-picker-column" id="ampmOut"></div>
                        </div>
                    </div>
                </div>

                <div class="attendance-modal-status-section mb-24">
                    <div class="status-card">
                        <span class="status-card-label">Current Status</span>
                        <span class="status-badge-v2 status-badge-modal" id="modalStatus">ON TIME</span>
                    </div>
                    <div class="message-card">
                        <div class="message-card-header">
                            <span class="message-card-label">Message</span>
                            <span class="message-time" id="modalMsgTime">at 09:10 AM</span>
                        </div>
                        <p class="message-text" id="modalMsg">Working from home today due to some personal work.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-end gap-12 modal-footer-p-30">
                <button class="btn btn-primary px-30 btn-premium-lg" onclick="saveAttendanceDetails()">
                    <i data-lucide="check" size="16"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </div>
    </div>

<script src="assets/js/attendance-log.js"></script>
<?php include 'includes/footer.php'; ?>
