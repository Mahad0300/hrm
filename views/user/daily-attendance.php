<?php 
$page_title = "Daily Attendance";
$page_subtitle = "Your check-in, check-out, and monthly activity in one place.";

// Restrict access: Only users with a configured Biometric ID can view Daily Attendance
$stmt_bio = $pdo->prepare("SELECT biometric_id FROM employees WHERE id = ? AND deleted_at IS NULL LIMIT 1");
$stmt_bio->execute([$_SESSION['user_id']]);
$user_bio_id = trim((string)$stmt_bio->fetchColumn());

if (empty($user_bio_id)) {
    header("Location: " . \App\Core\View::url('dashboard'));
    exit;
}

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

$userRole = strtolower($_SESSION['user_role'] ?? 'user');
if (!in_array($userRole, ['admin', 'hr', 'user'], true)) {
    $userRole = 'user';
}

include __DIR__ . "/../partials/{$userRole}/header.php"; 
include __DIR__ . "/../partials/{$userRole}/sidebar.php";
?>

<input type="hidden" id="currentEmpId" value="<?= htmlspecialchars($_SESSION['user_id']) ?>">

<?php
$current_month = $_GET['month'] ?? \App\Helpers\PayrollConfig::getCurrentPayrollMonth(null, $pdo);
$range = \App\Helpers\PayrollConfig::getPayrollRange($pdo, $current_month);
// The table and calendar are loaded via JS, so we just need the title and range info for PHP
$month_display = date('F Y', strtotime($current_month . '-01'));

// Get current check-in status
$yesterday = date('Y-m-d', strtotime('-1 day'));
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE employee_id = ? AND clock_out IS NULL AND date >= ? ORDER BY clock_in DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id'], $yesterday]);
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
        <!-- Stats Cards Row -->
        <div class="attendance-stats-grid">
            <div class="attendance-stat-card" style="--accent-color: #10b981; --accent-bg: rgba(16, 185, 129, 0.1); --accent-hover-border: rgba(16, 185, 129, 0.3);">
                <svg class="attendance-card-shape" viewBox="0 0 200 100" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="grad-ontime" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#10b981" />
                            <stop offset="100%" stop-color="#059669" />
                        </linearGradient>
                    </defs>
                    <path d="M0,0 L0,55 C0,75 30,75 45,55 C65,30 80,0 105,0 Z" fill="url(#grad-ontime)" opacity="0.08" />
                    <path d="M0,100 L35,100 C75,100 95,25 115,25 C130,25 165,100 200,100 Z" fill="url(#grad-ontime)" opacity="0.12" />
                </svg>
                <div class="attendance-stat-icon">
                    <i data-lucide="check-circle" size="20"></i>
                </div>
                <div class="attendance-stat-info">
                    <h3 id="stat-count-ontime" class="attendance-stat-count">0</h3>
                    <p class="attendance-stat-label">On Time</p>
                </div>
            </div>
            <div class="attendance-stat-card" style="--accent-color: #f59e0b; --accent-bg: rgba(245, 158, 11, 0.1); --accent-hover-border: rgba(245, 158, 11, 0.3);">
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
                <div class="attendance-stat-icon">
                    <i data-lucide="clock" size="20"></i>
                </div>
                <div class="attendance-stat-info">
                    <h3 id="stat-count-latein" class="attendance-stat-count">0</h3>
                    <p class="attendance-stat-label">Late In</p>
                </div>
            </div>
            <div class="attendance-stat-card" style="--accent-color: #ef4444; --accent-bg: rgba(239, 68, 68, 0.1); --accent-hover-border: rgba(239, 68, 68, 0.3);">
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
                <div class="attendance-stat-icon">
                    <i data-lucide="x-circle" size="20"></i>
                </div>
                <div class="attendance-stat-info">
                    <h3 id="stat-count-absent" class="attendance-stat-count">0</h3>
                    <p class="attendance-stat-label">Absent</p>
                </div>
            </div>
            <div class="attendance-stat-card" style="--accent-color: #3b82f6; --accent-bg: rgba(59, 130, 246, 0.1); --accent-hover-border: rgba(59, 130, 246, 0.3);">
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
                <div class="attendance-stat-icon">
                    <i data-lucide="hourglass" size="20"></i>
                </div>
                <div class="attendance-stat-info">
                    <h3 id="stat-count-halfday" class="attendance-stat-count">0</h3>
                    <p class="attendance-stat-label">Half Day</p>
                </div>
            </div>
            <div class="attendance-stat-card" style="--accent-color: #a855f7; --accent-bg: rgba(168, 85, 247, 0.1); --accent-hover-border: rgba(168, 85, 247, 0.3);">
                <svg class="attendance-card-shape" viewBox="0 0 200 100" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="grad-leave" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#a855f7" />
                            <stop offset="100%" stop-color="#7e22ce" />
                        </linearGradient>
                    </defs>
                    <path d="M0,0 L0,55 C0,75 30,75 45,55 C65,30 80,0 105,0 Z" fill="url(#grad-leave)" opacity="0.08" />
                    <path d="M0,100 L35,100 C75,100 95,25 115,25 C130,25 165,100 200,100 Z" fill="url(#grad-leave)" opacity="0.12" />
                </svg>
                <div class="attendance-stat-icon">
                    <i data-lucide="plane" size="20"></i>
                </div>
                <div class="attendance-stat-info">
                    <h3 id="stat-count-leave" class="attendance-stat-count">0</h3>
                    <p class="attendance-stat-label">Leave</p>
                </div>
            </div>
            <div class="attendance-stat-card" style="--accent-color: #06b6d4; --accent-bg: rgba(6, 182, 212, 0.1); --accent-hover-border: rgba(6, 182, 212, 0.3);">
                <svg class="attendance-card-shape" viewBox="0 0 200 100" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="grad-holiday" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#06b6d4" />
                            <stop offset="100%" stop-color="#0891b2" />
                        </linearGradient>
                    </defs>
                    <path d="M0,0 L0,55 C0,75 30,75 45,55 C65,30 80,0 105,0 Z" fill="url(#grad-holiday)" opacity="0.08" />
                    <path d="M0,100 L35,100 C75,100 95,25 115,25 C130,25 165,100 200,100 Z" fill="url(#grad-holiday)" opacity="0.12" />
                </svg>
                <div class="attendance-stat-icon">
                    <i data-lucide="palmtree" size="20"></i>
                </div>
                <div class="attendance-stat-info">
                    <h3 id="stat-count-holiday" class="attendance-stat-count">0</h3>
                    <p class="attendance-stat-label">Holiday</p>
                </div>
            </div>
        </div>

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

<script src="<?= \App\Core\View::asset('js/user/attendance-log.js') ?>?v=<?= time() ?>"></script>
<?php include __DIR__ . '/../partials/user/footer.php'; ?>

