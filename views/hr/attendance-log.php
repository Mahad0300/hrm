<?php
use App\Core\View;
// admin/attendance-log.php
$page_title = "Attendance History";
$page_subtitle = "Viewing detailed attendance records";
include __DIR__ . '/../partials/hr/header.php';
?>
<?php include __DIR__ . '/../partials/hr/sidebar.php'; ?>

<?php 
use App\Core\Database;

$emp_id = $_GET['emp_id'] ?? $_GET['id'] ?? '';
$name_param = $_GET['name'] ?? $_GET['employee'] ?? '';

$pdo = $pdo ?? Database::connection();

// 1. If emp_id is provided but name is not, redirect to canonical slug URL
if ($emp_id !== '' && $name_param === '') {
    try {
        $stmt = $pdo->prepare("SELECT first_name, middle_name, last_name FROM employees WHERE id = ? LIMIT 1");
        $stmt->execute([$emp_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $fullName = trim($row['first_name'] . ' ' . (!empty($row['middle_name']) ? $row['middle_name'] . ' ' : '') . $row['last_name']);
            $canonicalSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $fullName), '-'));
            if ($canonicalSlug !== '') {
                header('Location: ' . View::url('attendance.log') . '?name=' . rawurlencode($canonicalSlug));
                exit;
            }
        }
    } catch (\Throwable $e) {
        // Fallback silently
    }
}

// 2. If name is provided, resolve it to numeric emp_id
if ($name_param !== '') {
    $targetSlug = strtolower(trim($name_param));
    try {
        $stmt = $pdo->query("SELECT id, first_name, middle_name, last_name FROM employees");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($employees as $row) {
            $fullName = trim($row['first_name'] . ' ' . (!empty($row['middle_name']) ? $row['middle_name'] . ' ' : '') . $row['last_name']);
            $rowSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $fullName), '-'));
            if ($rowSlug === $targetSlug) {
                $emp_id = $row['id'];
                break;
            }
        }
    } catch (\Throwable $e) {
        // Fallback silently
    }
}

if (!$emp_id) {
    echo "<div class='p-30'><div class='alert alert-danger'>Employee ID or Name is missing or invalid. <a href='" . View::url('attendance') . "'>Go back</a></div></div>";
    include __DIR__ . '/../partials/hr/footer.php';
    exit;
}
?>

<input type="hidden" id="currentEmpId" value="<?= htmlspecialchars($emp_id) ?>">

<div class="attendance-log-header pb-10 mt-neg-10">
    <div class="flex-between align-start w-full">
        <div class="greeting-area">
            <div class="flex-center gap-12 mb-8">
                <a href="<?= View::to('attendance') ?>" class="action-btn no-bg border" title="Back to Attendance">
                    <i data-lucide="arrow-left" size="18"></i>
                </a>
                <h1 class="font-24 font-700 ls-05">Attendance Log</h1>
            </div>
            <div class="flex-center gap-12 mb-20">
                <div class="emp-profile">
                    <img src="<?= \App\Core\View::image('profile-image/default-avatar.svg') ?>" id="headerEmpAvatar" class="emp-avatar" alt="Avatar" onerror="this.src='<?= \App\Core\View::image('profile-image/default-avatar.svg') ?>'">
                    <div class="emp-info">
                        <span class="name font-16 font-600" id="headerEmpName">Loading...</span>
                        <span class="email font-12 text-light" id="headerEmpDetail">...</span>
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
                <input type="month" id="monthFilter" class="form-control" value="<?= htmlspecialchars(\App\Helpers\PayrollConfig::getCurrentPayrollMonth(null, $pdo), ENT_QUOTES, 'UTF-8') ?>">
            </div>
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
        <div class="flex-between mb-24 px-4">
            <div class="flex-center gap-10">
                <span class="font-13 text-light">Show</span>
                <select class="form-control font-13 font-600 per-page-select" id="perPageSelect">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
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
                            <th>SHIFT</th>
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

    <!-- Tab Content: Calendar View -->
    <div id="attendanceCalendar" class="log-tab-content">
        <div class="calendar-card">
            <div class="calendar-header-v2 border-bottom">
                <div class="flex-between">
                    <h3 class="font-18 font-700 m-0" id="calendarMonthTitle">Month Year</h3>
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
            <div class="calendar-grid-v2" id="calendarGrid">
                <!-- Grid injected by JS -->
            </div>
        </div>
    </div>
</div>

<!-- Attendance Edit Modal (Shared Structure) -->
<div id="attendanceModal" class="modal-overlay">
    <div class="modal-content premium">
        <div class="modal-header">
            <div class="flex-center gap-12">
                <div class="type-icon-box primary">
                    <i data-lucide="clock" size="20"></i>
                </div>
                <div>
                    <h3 class="font-18 font-700 m-0">Edit Attendance</h3>
                    <p class="font-12 text-light m-0" id="modalDateDisplay">--</p>
                </div>
            </div>
            <button type="button" class="icon-btn" onclick="closeAttendanceModal()"><i data-lucide="x" size="20"></i></button>
        </div>
        <div class="modal-body">
            <div class="attendance-details-grid mb-24">
                <div class="detail-item">
                    <span class="label">Shift</span>
                    <span class="value" id="modalShift">--</span>
                </div>
                <div class="detail-item text-right">
                    <span class="label">Working Hours</span>
                    <span class="value" id="modalHours">--</span>
                </div>
            </div>

            <div class="form-grid-2 mb-24 time-picker-grid">
                <div class="form-group time-picker-group">
                    <label class="admin-form-label">Clock In</label>
                    <div class="time-picker-trigger" id="triggerTimeIn" onclick="toggleTimePicker('in')">
                        <i data-lucide="clock" size="18"></i>
                        <span id="modalInDisplay">--:-- --</span>
                    </div>
                    <input type="hidden" id="modalIn" value="">
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
                        <span id="modalOutDisplay">--:-- --</span>
                    </div>
                    <input type="hidden" id="modalOut" value="">
                    <div id="timePickerOut" class="time-picker-dropdown">
                        <div class="time-picker-column" id="hourOut"></div>
                        <div class="time-picker-column" id="minuteOut"></div>
                        <div class="time-picker-column" id="ampmOut"></div>
                    </div>
                </div>
            </div>

            <div class="attendance-modal-status-section mb-24">
                <div class="status-card">
                    <span class="status-card-label">Status</span>
                    <span class="status-badge-v2 status-badge-modal" id="modalStatus">--</span>
                </div>
                <div class="message-card">
                    <div class="message-card-header">
                        <span class="message-card-label">Message</span>
                    </div>
                    <p class="message-text" id="modalMsg">-</p>
                </div>
            </div>
        </div>
        <div class="modal-footer flex-end gap-12 modal-footer-p-30">
            <button class="btn btn-primary px-30 btn-premium-lg" id="saveAttendanceBtn">
                <i data-lucide="check" size="16"></i>
                <span>Save Changes</span>
            </button>
        </div>
    </div>
</div>

<script src="<?= \App\Core\View::asset('js/shared/attendance-log.js?v=' . time()) ?>"></script>
<?php include __DIR__ . '/../partials/hr/footer.php'; ?>
