<?php 
$page_title = "Daily Attendance";
$page_subtitle = "Your check-in, check-out, and monthly activity in one place.";

function parseClockToMinutes($t) {
    $t = trim((string) $t);
    if ($t === '' || $t === '--:--') return null;
    if (!preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $t, $m)) return null;
    $h = (int) $m[1];
    $min = (int) $m[2];
    $ap = strtoupper($m[3]);
    if ($ap === 'PM' && $h !== 12) $h += 12;
    if ($ap === 'AM' && $h === 12) $h = 0;
    return $h * 60 + $min;
}

function formatWorkingHours($in, $out) {
    $a = parseClockToMinutes($in);
    $b = parseClockToMinutes($out);
    if ($a === null || $b === null) return '—';
    if ($b <= $a) $b += 24 * 60;
    $diff = $b - $a;
    $h = intdiv($diff, 60);
    $m = $diff % 60;
    return $h . 'h ' . str_pad((string) $m, 2, '0', STR_PAD_LEFT) . 'm';
}

include 'includes/header.php'; 
require_once '../includes/payroll_config.php';
?>
<?php include 'includes/sidebar.php'; ?>

<?php
$current_month = $_GET['month'] ?? date('Y-m');
$range = getPayrollRange($current_month);
// The table and calendar are loaded via JS, so we just need the title and range info for PHP
$month_display = date('F Y', strtotime($current_month . '-01'));

// Get current check-in status
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$current_session = $stmt->fetch();
?>

    <div class="attendance-log-header pb-10 mt-neg-10">
        <div class="header-actions flex-center justify-end gap-16 flex-wrap w-full">
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
                <input type="month" id="monthFilter" class="form-control" value="<?= $current_month ?>">
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
                        <span class="font-13 text-light" id="tableSummary">Showing 0 to 0 of 0 entries</span>
                    </div>
                </div>

                <div class="card p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>DATE</th>
                                    <th>CHECK IN</th>
                                    <th>CHECK OUT</th>
                                    <th>WORKING HOURS</th>
                                    <th>STATUS</th>
                                    <th>MESSAGE</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceTableBody">
                                <!-- Data injected by JS -->
                                <tr>
                                    <td colspan="7" class="text-center py-40">
                                        <div class="empty-state-wrapper">
                                            <i data-lucide="loader-2" size="48" class="text-light mb-16 spin"></i>
                                            <p class="text-light font-14">Loading attendance records...</p>
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
                            <div id="pageNumbers" class="flex-center gap-8"></div>
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
                            <h3 class="font-18 font-700 m-0" id="calendarMonthTitle">Payroll: <?= $month_display ?></h3>
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
                                <div class="flex-center gap-8">
                                    <span class="w-12 h-12 rounded-full status-v2-leave"></span>
                                    <span class="font-12 text-light">Leave</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="calendar-grid-v2" id="calendarGrid">
                        <!-- Calendar injected by JS -->
                    </div>
                </div>
            </div>
        </div>

    <!-- Attendance Detail Modal -->
    <div id="attendanceModal" class="modal-overlay attendance-detail-modal-wrap">
        <div class="modal-content premium attendance-detail-modal">
            <div class="modal-header attendance-detail-modal__header">
                <div class="attendance-detail-modal__head-inner">
                    <div class="attendance-detail-modal__icon" aria-hidden="true">
                        <i data-lucide="clock" size="22"></i>
                    </div>
                    <div>
                        <h3 class="attendance-detail-modal__title m-0">Attendance Details</h3>
                        <p class="attendance-detail-modal__subtitle m-0">View record &amp; update your message</p>
                    </div>
                </div>
                <button type="button" class="icon-btn attendance-detail-modal__close" onclick="closeAttendanceModal()" aria-label="Close"><i data-lucide="x" size="20"></i></button>
            </div>
            <div class="modal-body attendance-detail-modal__body">
                <div class="attendance-modal-details-list attendance-detail-modal__details">
                    <div class="attendance-detail-row">
                        <span class="label">Date</span>
                        <span class="value" id="modalDetailDate">—</span>
                    </div>
                    <div class="attendance-detail-row">
                        <span class="label">Check in</span>
                        <span class="value" id="modalDetailIn">—</span>
                    </div>
                    <div class="attendance-detail-row">
                        <span class="label">Check out</span>
                        <span class="value" id="modalDetailOut">—</span>
                    </div>
                    <div class="attendance-detail-row">
                        <span class="label">Working hours</span>
                        <span class="value" id="modalHours">—</span>
                    </div>
                    <div class="attendance-detail-row attendance-detail-row-status">
                        <span class="label">Status</span>
                        <div class="value">
                            <span class="status-badge-v2 status-badge-modal attendance-detail-modal__status-badge" id="modalStatus">ON TIME</span>
                        </div>
                    </div>
                </div>

                <div class="attendance-detail-modal__message">
                    <div class="attendance-detail-modal__msg-header">
                        <label class="attendance-detail-modal__msg-label" for="modalMsgInput">Message</label>
                        <span class="attendance-detail-modal__msg-time hidden" id="modalMsgMeta"></span>
                    </div>
                    <textarea id="modalMsgInput" class="form-control attendance-modal-msg-textarea attendance-detail-modal__textarea" rows="4" placeholder="Type a message for this day (optional)…"></textarea>
                </div>
            </div>
            <div class="modal-footer attendance-detail-modal__footer modal-footer-p-30">
                <button type="button" class="btn btn-primary attendance-detail-modal__save btn-premium-lg" onclick="saveAttendanceDetails()">
                    <i data-lucide="check" size="16"></i>
                    <span>Save message</span>
                </button>
            </div>
        </div>
    </div>

<script src="assets/js/attendance-log.js"></script>
<?php include 'includes/footer.php'; ?>
