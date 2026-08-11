<?php
use App\Core\View;
// admin/employee-profile.php

$page_title = 'Employee Profile - HRM';

function getEmployeeProfileFullName(array $employee)
{
    return trim(
        ($employee['first_name'] ?? '') . ' ' .
        (!empty($employee['middle_name']) ? $employee['middle_name'] . ' ' : '') .
        ($employee['last_name'] ?? '')
    );
}

function createEmployeeProfileSlug($firstName, $middleName = '', $lastName = '')
{
    $fullName = trim($firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName);
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $fullName), '-'));

    return $slug ?: '';
}

// Fetch Employee Data
$id_param = trim($_GET['id'] ?? '');
$employee_param = trim($_GET['employee'] ?? ($_GET['name'] ?? ''));
$employee = null;

$employeeSelectSql = "
            SELECT e.*, d.name as dept_name, s.name as shift_name, s.start_time, s.end_time,
                   b.bank_name, b.account_type, b.account_title, b.account_number, b.branch_info,
                   ex.qualification, ex.degree_cert as degree_certification, ex.university as college_university, 
                   ex.expertise as professional_expertise, ex.last_employer, ex.last_job_title as last_designation, 
                   ex.exp_from as experience_from, ex.exp_to as experience_to, e.other_docs
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN shifts s ON e.shift_id = s.id
            LEFT JOIN banking_info b ON e.id = b.employee_id
            LEFT JOIN education_experience ex ON e.id = ex.employee_id
";

try {
    if ($employee_param !== '') {
        $targetSlug = createEmployeeProfileSlug($employee_param);
        $stmt = $pdo->query($employeeSelectSql . " ORDER BY e.created_at DESC, e.id DESC");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($employees as $row) {
            $rowSlug = createEmployeeProfileSlug($row['first_name'], $row['middle_name'] ?? '', $row['last_name']);
            if ($rowSlug === $targetSlug) {
                if ($id_param !== '' && (string) $row['id'] !== $id_param) {
                    continue;
                }
                $employee = $row;
                break;
            }
        }
    } elseif ($id_param !== '') {
        $stmt = $pdo->prepare($employeeSelectSql . " WHERE e.id = ?");
        $stmt->execute([$id_param]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "Error fetching employee details.";
}

if (!$employee) {
    header('Location: ' . View::url('employees'));
    exit;
}

// Canonical URL: name slug only (no id in address bar)
if ($employee_param === '' && $id_param !== '') {
    $canonicalSlug = createEmployeeProfileSlug(
        $employee['first_name'],
        $employee['middle_name'] ?? '',
        $employee['last_name']
    );
    if ($canonicalSlug !== '') {
        header('Location: ' . View::url('employees.profile') . '?name=' . rawurlencode($canonicalSlug));
        exit;
    }
}

// Format Name
$emp_id = (int) $employee['id'];
$full_name = htmlspecialchars(getEmployeeProfileFullName($employee), ENT_QUOTES, 'UTF-8');
$page_title = $full_name . ' - Employee Profile - HRM';

include __DIR__ . '/../partials/admin/header.php';
include __DIR__ . '/../partials/admin/sidebar.php';

// Default Avatar URL
$avatar_url = $employee['profile_pic'] && file_exists(STORAGE_DIR . '/' . $employee['profile_pic']) ? View::upload($employee['profile_pic']) : View::image('profile-image/default-avatar.svg');

// Helper for display
function displayValue($val, $fallback = '-')
{
    return !empty($val) ? htmlspecialchars($val) : $fallback;
}

// Time Format Helper
function formatShiftTime($start, $end)
{
    if (!$start || !$end)
        return '-';
    return date("g:i A", strtotime($start)) . " - " . date("g:i A", strtotime($end));
}

// Fetch all departments & shifts for dropdown options in edit mode
$all_departments = [];
$all_shifts = [];
try {
    $all_departments = $pdo->query("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $all_shifts = $pdo->query("SELECT id, name, start_time, end_time FROM shifts WHERE deleted_at IS NULL ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback
}

// Fetch Leave Summary
$leave_summary = [];
try {
    $stmt = $pdo->query("SELECT id, name, days_per_year FROM leave_types");
    $leave_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($leave_types as $lt) {
        $used_days = \App\Helpers\LeaveAttendanceSync::sumApprovedLeaveWorkingDays($pdo, (int) $emp_id, (int) $lt['id']);
        
        $leave_summary[] = [
            'name' => $lt['name'],
            'total' => $lt['days_per_year'],
            'used' => $used_days,
            'remaining' => max(0, $lt['days_per_year'] - $used_days)
        ];
    }
} catch (PDOException $e) {
    // Silent fail for summary
}
?>

<main class="content-wrapper">
    <header class="content-header p-30 pb-10">
        <div class="flex-between align-start w-full">
            <div class="greeting-area">
                <div class="flex-center gap-12 mb-8">
                    <a href="<?= view::to('employees') ?>" class="action-btn no-bg border" title="Back to Directory">
                        <i data-lucide="arrow-left" size="18"></i>
                    </a>
                    <h1 class="font-24 font-700 ls-05">Employee Profile</h1>
                </div>
                <p class="text-light font-14">Detailed profile information for
                    <strong><?php echo $full_name; ?></strong></p>
            </div>
            <div class="flex-center gap-12" id="profileHeaderActions">
                <button type="button" class="btn-primary px-20 flex-center gap-8" id="toggleInlineEditBtn" onclick="toggleProfileInlineEdit(true)">
                    <i data-lucide="edit-3" size="18"></i> <span>Edit Profile</span>
                </button>
                <div id="inlineEditActionBtns" class="flex-center gap-10 hidden">
                    <button type="button" class="btn-cancel-modal px-20" onclick="toggleProfileInlineEdit(false)">Cancel</button>
                    <button type="button" class="btn-primary px-24 flex-center gap-8" id="saveInlineProfileBtn" onclick="saveProfileInlineEdit()">
                        <i data-lucide="check-circle-2" size="18"></i> <span>Save Changes</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div>
        <form id="inlineProfileForm" autocomplete="off">
        <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">
        <div class="profile-grid">
            <!-- Left Column: Primary Identity -->
            <div class="profile-aside">
                <div class="premium-card profile-identity-card flex-column flex-center text-center mb-24">
                    <div class="profile-avatar-wrapper mb-20">
                        <img src="<?php echo $avatar_url; ?>" class="profile-avatar-xl shadow-lg"
                            alt="<?php echo $full_name; ?>" 
                            onerror="this.src='<?= \App\Core\View::image('profile-image/default-avatar.svg') ?>'">
                        <span
                            class="status-indicator-lg <?php echo strtolower($employee['status']) === 'active' ? 'active' : 'inactive'; ?> border-4"></span>
                    </div>
                    <h2 class="font-22 font-700 mb-4"><?php echo $full_name; ?></h2>
                    <p class="text-primary-color font-600 mb-12">
                        <?php echo displayValue($employee['job_title'], '-'); ?></p>
                    <div
                        class="badge <?php echo strtolower($employee['status']) === 'active' ? 'badge-success' : 'badge-light'; ?> px-15 py-6">
                        <?php echo displayValue($employee['status']); ?> Employee
                    </div>
                </div>

                <div class="premium-card p-24 mb-24">
                    <h3 class="font-15 font-700 mb-20 flex-center gap-10">
                        <i data-lucide="contact" size="18" class="text-primary-color"></i>
                        Primary Contact
                    </h3>
                    <div class="mb-20">
                        <label class="admin-form-label">Email</label>
                        <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['email']); ?></span>
                        <input type="email" name="email" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['email'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-20">
                        <label class="admin-form-label">Phone</label>
                        <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['phone']); ?></span>
                        <input type="text" name="phone" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['phone'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="premium-card p-24 mb-24">
                    <h3 class="font-15 font-700 mb-20 flex-center gap-10">
                        <i data-lucide="shield-alert" size="18" class="text-primary-color"></i>
                        Emergency Contact
                    </h3>
                    <div class="mb-20">
                        <label class="admin-form-label">Emergency Contact</label>
                        <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['emergency_contact']); ?></span>
                        <input type="text" name="emergency_contact" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['emergency_contact'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="admin-form-label">Emergency Contact Relation</label>
                        <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['emergency_relation']); ?></span>
                        <input type="text" name="emergency_relation" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['emergency_relation'] ?? ''); ?>">
                    </div>
                </div>

                <div class="premium-card p-24 mb-24" id="employeeLeaveSummaryCard">
                    <h3 class="font-15 font-700 mb-20 flex-center gap-10">
                        <i data-lucide="calendar-check" size="18" class="text-primary-color"></i>
                        Leave Summary
                    </h3>
                    <div class="leave-summary-list">
                        <?php if (!empty($leave_summary)): ?>
                            <?php foreach ($leave_summary as $ls): ?>
                                <div class="leave-summary-row">
                                    <span class="leave-summary-name"><?php echo htmlspecialchars($ls['name']); ?></span>
                                    <span class="leave-summary-meta"><span><?php echo $ls['used']; ?></span> used · <span><?php echo $ls['remaining']; ?></span> remaining</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-10 text-center text-light font-12 italic">
                                No leave data found.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Salary History (Moved to Left Sidebar) -->
                <div class="premium-card p-24">
                    <div class="flex-between align-center flex-wrap gap-10 mb-20">
                        <h3 class="font-14 font-700 m-0 flex-center gap-8">
                            <i data-lucide="trending-up" size="18" class="text-primary-color"></i>
                            <span>Salary History</span>
                        </h3>
                        <button type="button" class="btn-primary font-11 py-6 px-12 flex-center gap-6" onclick="openQuickSalaryModal('<?= $employee['id'] ?>', '<?= $employee['salary'] ?>', '<?= htmlspecialchars(addslashes($full_name)) ?>')">
                            <i data-lucide="plus" size="14"></i> <span>Add Increment</span>
                        </button>
                    </div>
                    <div class="timeline-list">
                        <?php
                        $h_stmt = $pdo->prepare("SELECT * FROM salary_history WHERE employee_id = ? ORDER BY change_date DESC, id DESC");
                        $h_stmt->execute([$employee['id']]);
                        $history = $h_stmt->fetchAll();

                        if (!empty($history)):
                            foreach ($history as $index => $row):
                                $is_inc = $row['type'] === 'Increment';
                                ?>
                                <div class="timeline-item <?= $is_inc ? 'inc' : 'dec' ?>">
                                    <div class="timeline-info">
                                        <span class="font-15 <?= $index === 0 ? 'font-700' : 'font-600' ?> text-dark block">
                                            <?= number_format($row['new_salary']) ?>
                                        </span>
                                        <span class="font-11 <?= $is_inc ? 'text-success' : 'text-danger' ?> font-600" style="margin-top: -4px;">
                                            <?= ($is_inc ? '+' : '-') . number_format($row['amount_change']) ?>
                                        </span>
                                        <span class="font-12 text-light font-500">
                                            <?= date("M d, Y", strtotime($row['change_date'])) ?>
                                        </span>
                                        <?php if ($index === 0 && $is_inc): ?>
                                            <div class="mt-8">
                                                <span class="badge badge-success px-10 py-4 font-10">Recent Increment</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php
                            endforeach;
                        else:
                            ?>
                            <div class="text-center py-20 opacity-50">
                                <i data-lucide="clock-rewind" size="24" class="mb-10 opacity-30"></i>
                                <p class="font-12 italic">No salary changes recorded.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <p class="font-11 text-light m-0 mt-15">
                        Latest salary: <span class="font-600 text-dark"><?= number_format($employee['salary']) ?></span> (synced with payroll data).
                    </p>
                </div>
            </div>

            <!-- Right Column: Details -->
            <div class="profile-main">
                <!-- Personal Details -->
                <div class="premium-card mb-24">
                    <div class="card-header p-24 border-bottom">
                        <h3 class="font-16 font-700 flex-center gap-10">
                            <i data-lucide="user" size="20" class="text-primary-color"></i>
                            Personal Details
                        </h3>
                    </div>
                    <div class="card-body p-24">
                        <div class="form-grid-3 mb-30">
                            <div>
                                <label class="admin-form-label">First Name *</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['first_name']); ?></span>
                                <input type="text" name="first_name" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['first_name'] ?? ''); ?>" required>
                            </div>
                            <div>
                                <label class="admin-form-label">Middle Name</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['middle_name']); ?></span>
                                <input type="text" name="middle_name" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['middle_name'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="admin-form-label">Last Name *</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['last_name']); ?></span>
                                <input type="text" name="last_name" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-grid-3 mb-30">
                            <div>
                                <label class="admin-form-label">Gender</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['gender']); ?></span>
                                <select name="gender" class="form-control profile-edit-input hidden">
                                    <option value="Male" <?php echo ($employee['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($employee['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            <div>
                                <label class="admin-form-label">Date of Birth</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo !empty($employee['dob']) ? date("F j, Y", strtotime($employee['dob'])) : '-'; ?></span>
                                <input type="date" name="dob" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['dob'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="admin-form-label">ID Card Number</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['cnic_number']); ?></span>
                                <input type="text" name="cnic_number" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['cnic_number'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="admin-form-label">Address</label>
                            <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['address']); ?></span>
                            <input type="text" name="address" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['address'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Job & Banking -->
                <div class="premium-card mb-24">
                    <div class="card-header p-24 border-bottom">
                        <h3 class="font-16 font-700 flex-center gap-10">
                            <i data-lucide="briefcase" size="20" class="text-primary-color"></i>
                            Job & Banking
                        </h3>
                    </div>
                    <div class="card-body p-24">
                        <div class="form-grid-3 mb-30">
                            <div>
                                <div class="flex-between align-center mb-4">
                                    <label class="admin-form-label mb-0">Shift Timing</label>
                                    <button type="button" class="action-btn no-bg py-0 px-4 profile-view-val" title="Change Shift" onclick="openQuickShiftModal('<?= $employee['id'] ?>', '<?= $employee['shift_id'] ?>', '<?= htmlspecialchars(addslashes($full_name)) ?>')">
                                        <i data-lucide="edit-3" size="14"></i>
                                    </button>
                                </div>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['shift_name']); ?>
                                    <?php echo ($employee['start_time']) ? '(' . formatShiftTime($employee['start_time'], $employee['end_time']) . ')' : ''; ?></span>
                                <select name="shift_id" class="form-control profile-edit-input hidden">
                                    <option value="">Select Shift</option>
                                    <?php foreach ($all_shifts as $s): ?>
                                        <option value="<?php echo $s['id']; ?>" <?php echo ($employee['shift_id'] == $s['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['name']); ?> (<?php echo formatShiftTime($s['start_time'], $s['end_time']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="admin-form-label">Job Title</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['job_title']); ?></span>
                                <input type="text" name="job_title" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['job_title'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="admin-form-label">Department</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['dept_name']); ?></span>
                                <select name="department_id" class="form-control profile-edit-input hidden">
                                    <option value="">Select Department</option>
                                    <?php foreach ($all_departments as $d): ?>
                                        <option value="<?php echo $d['id']; ?>" <?php echo ($employee['department_id'] == $d['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($d['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-grid-3 mb-24">
                            <div>
                                <label class="admin-form-label">Job Type</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['job_type']); ?></span>
                                <select name="job_type" class="form-control profile-edit-input hidden">
                                    <option value="Full-Time" <?php echo ($employee['job_type'] === 'Full-Time' || $employee['job_type'] === 'Permanent') ? 'selected' : ''; ?>>Full-Time</option>
                                    <option value="Part-Time" <?php echo ($employee['job_type'] === 'Part-Time') ? 'selected' : ''; ?>>Part-Time</option>
                                    <option value="Probation" <?php echo ($employee['job_type'] === 'Probation') ? 'selected' : ''; ?>>Probation</option>
                                    <option value="Contract" <?php echo ($employee['job_type'] === 'Contract') ? 'selected' : ''; ?>>Contract</option>
                                    <option value="Internship" <?php echo ($employee['job_type'] === 'Internship') ? 'selected' : ''; ?>>Internship</option>
                                </select>
                            </div>
                            <div>
                                <label class="admin-form-label">Salary (PKR)</label>
                                <span class="font-14 font-600 block profile-view-val"><?php echo $employee['salary'] ? number_format($employee['salary']) : '-'; ?></span>
                                <input type="number" name="salary" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['salary'] ?? ''); ?>" step="any">
                            </div>
                            <div>
                                <label class="admin-form-label">Joining Date</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo !empty($employee['joining_date']) ? date("M d, Y", strtotime($employee['joining_date'])) : '-'; ?></span>
                                <input type="date" name="joining_date" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['joining_date'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-10">
                            <label class="admin-form-label">Biometric Device ID</label>
                            <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['biometric_id']); ?></span>
                            <input type="text" name="biometric_id" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['biometric_id'] ?? ''); ?>" placeholder="e.g. 13">
                        </div>

                        <div class="border-top pt-30 mt-30">
                            <h3 class="font-15 font-700 flex-center gap-10 mb-24">
                                <i data-lucide="building" size="18" class="text-primary-color"></i>
                                Bank Information
                            </h3>
                            <div class="form-grid-3 mb-24">
                                <div>
                                    <label class="admin-form-label">Bank Name</label>
                                    <span class="font-13 font-600 block profile-view-val"><?php echo displayValue($employee['bank_name']); ?></span>
                                    <input type="text" name="bank_name" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['bank_name'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label class="admin-form-label">Account Type</label>
                                    <span class="font-13 font-500 block profile-view-val"><?php echo displayValue($employee['account_type']); ?></span>
                                    <input type="text" name="account_type" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['account_type'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label class="admin-form-label">Account Title</label>
                                    <span class="font-13 font-500 block profile-view-val"><?php echo displayValue($employee['account_title']); ?></span>
                                    <input type="text" name="account_title" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['account_title'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div>
                                    <label class="admin-form-label">Account Number / IBAN</label>
                                    <span class="font-13 font-600 block ls-05 profile-view-val"><?php echo displayValue($employee['account_number']); ?></span>
                                    <input type="text" name="account_number" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['account_number'] ?? ''); ?>">
                                </div>
                                <div>
                                    <label class="admin-form-label">Bank Branch</label>
                                    <span class="font-13 font-500 block profile-view-val"><?php echo displayValue($employee['branch_info']); ?></span>
                                    <input type="text" name="branch_info" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['branch_info'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Education & Experience -->
                <div class="premium-card">
                    <div class="card-header p-24 border-bottom">
                        <h3 class="font-16 font-700 flex-center gap-10">
                            <i data-lucide="graduation-cap" size="20" class="text-primary-color"></i>
                            Education & Experience
                        </h3>
                    </div>
                    <div class="card-body p-24">
                        <div class="form-grid-2 mb-30">
                            <div>
                                <label class="admin-form-label">Qualification</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['qualification']); ?></span>
                                <input type="text" name="qualification" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['qualification'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="admin-form-label">Degree / Certification</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['degree_certification']); ?></span>
                                <input type="text" name="degree_certification" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['degree_certification'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-grid-2 mb-30">
                            <div>
                                <label class="admin-form-label">College / University</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['college_university']); ?></span>
                                <input type="text" name="college_university" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['college_university'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="admin-form-label">Professional Expertise</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['professional_expertise']); ?></span>
                                <input type="text" name="professional_expertise" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['professional_expertise'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-grid-2 mb-30 border-top pt-30 mt-10">
                            <div>
                                <label class="admin-form-label">Last Employer</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['last_employer']); ?></span>
                                <input type="text" name="last_employer" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['last_employer'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="admin-form-label">Last Job Title</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['last_designation']); ?></span>
                                <input type="text" name="last_designation" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['last_designation'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-grid-2 mb-40">
                            <div>
                                <label class="admin-form-label">Experience From Date</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['experience_from']); ?></span>
                                <input type="date" name="experience_from" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['experience_from'] ?? ''); ?>">
                            </div>
                            <div>
                                <label class="admin-form-label">Experience To Date</label>
                                <span class="font-14 font-500 block profile-view-val"><?php echo displayValue($employee['experience_to']); ?></span>
                                <input type="date" name="experience_to" class="form-control profile-edit-input hidden" value="<?php echo htmlspecialchars($employee['experience_to'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Documents Section -->
                        <div class="border-top pt-30 mt-30">
                            <h3 class="font-15 font-700 flex-center gap-10 mb-20 ">
                                <i data-lucide="paperclip" size="18"></i>
                                Document Attachments
                            </h3>

                            <div class="form-grid-3 gap-24">
                                <!-- Resume Card -->
                                <?php if ($employee['resume_path']): ?>
                                    <a href="../<?php echo $employee['resume_path']; ?>" target="_blank"
                                        class="doc-card border rounded-16 p-20 hover-bg-light transition block no-underline text-dark">
                                        <label class="admin-form-label">Resume Attachment</label>
                                        <div class="flex-center gap-12 mt-4">
                                            <div class="icon-square-40 bg-primary-soft text-primary-color flex-shrink-0">
                                                <i data-lucide="file-text" size="20"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <span class="font-13 font-600 block truncate"><?php echo htmlspecialchars(basename($employee['resume_path'])); ?></span>
                                            </div>
                                        </div>
                                    </a>
                                <?php endif; ?>

                                <!-- ID Card Card -->
                                <?php if ($employee['id_card_path']): ?>
                                    <a href="../<?php echo $employee['id_card_path']; ?>" target="_blank"
                                        class="doc-card border rounded-16 p-20 hover-bg-light transition block no-underline text-dark">
                                        <label class="admin-form-label">ID Card Attachment</label>
                                        <div class="flex-center gap-12 mt-4">
                                            <div class="icon-square-40 bg-success-soft text-success-color flex-shrink-0">
                                                <i data-lucide="image" size="20"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <span class="font-13 font-600 block truncate"><?php echo htmlspecialchars(basename($employee['id_card_path'])); ?></span>
                                            </div>
                                        </div>
                                    </a>
                                <?php endif; ?>

                                <!-- Other Documents -->
                                <?php
                                $other_docs = !empty($employee['other_docs']) ? json_decode($employee['other_docs'], true) : [];
                                if (!empty($other_docs)):
                                    foreach ($other_docs as $doc_path):
                                        ?>
                                        <a href="../<?php echo $doc_path; ?>" target="_blank"
                                            class="doc-card border rounded-16 p-20 hover-bg-light transition block no-underline text-dark">
                                            <label class="admin-form-label">Other Documents</label>
                                            <div class="flex-center gap-12 mt-4">
                                                <div class="icon-square-40 bg-warning-soft text-warning-color flex-shrink-0">
                                                    <i data-lucide="files" size="20"></i>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <span class="font-13 font-600 block truncate"><?php echo htmlspecialchars(basename($doc_path)); ?></span>
                                                </div>
                                            </div>
                                        </a>
                                    <?php
                                    endforeach;
                                endif;

                                if (!$employee['resume_path'] && !$employee['id_card_path'] && empty($other_docs)):
                                    ?>
                                    <div class="form-grid-3" style="grid-column: span 3;">
                                        <div class="p-20 border border-dashed rounded-16 flex-center gap-12 text-light font-13 italic bg-light w-full">
                                            <i data-lucide="info" size="20"></i>
                                            No documents attached for this employee.
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
</main>

<!-- Quick Change Shift Modal -->
<div class="modal-overlay" id="quickShiftModal">
    <div class="modal-content premium">
        <div class="modal-header">
            <div>
                <h3>Change Employee Shift</h3>
                <p class="font-12 text-light mt-1">Assign a new work shift timing to employee</p>
            </div>
            <button type="button" class="icon-btn js-modal-close" onclick="closeQuickShiftModal()"><i data-lucide="x"></i></button>
        </div>
        <form id="quickShiftForm" autocomplete="off">
            <input type="hidden" id="qs_employee_id" name="employee_id">
            <div class="modal-body p-24">
                <div class="form-group mb-16">
                    <label class="admin-form-label font-12">Employee Name</label>
                    <input type="text" id="qs_employee_name" class="form-control bg-white-input" readonly>
                </div>
                <div class="form-group">
                    <label class="admin-form-label font-12">Select New Shift *</label>
                    <select id="qs_shift_id" name="shift_id" class="form-control" required>
                        <option value="">Loading shifts...</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer flex-between p-20 border-top">
                <button type="button" class="btn-cancel-modal" onclick="closeQuickShiftModal()">Cancel</button>
                <button type="submit" class="btn-primary px-24">Save Shift</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Salary Increment Modal -->
<div class="modal-overlay" id="quickSalaryModal">
    <div class="modal-content premium">
        <div class="modal-header">
            <div>
                <h3>Apply Salary Increment</h3>
                <p class="font-12 text-light mt-1">Quickly revise and record employee base salary</p>
            </div>
            <button type="button" class="icon-btn js-modal-close" onclick="closeQuickSalaryModal()"><i data-lucide="x"></i></button>
        </div>
        <form id="quickSalaryForm" autocomplete="off">
            <input type="hidden" id="qi_employee_id" name="employee_id">
            <div class="modal-body p-24">
                <div class="form-group mb-16">
                    <label class="admin-form-label font-12">Employee Name</label>
                    <input type="text" id="qi_employee_name" class="form-control bg-white-input" readonly>
                </div>
                <div class="form-grid-2 mb-16">
                    <div class="form-group">
                        <label class="admin-form-label font-12">Current Salary (PKR)</label>
                        <input type="text" id="qi_current_salary_display" class="form-control bg-white-input font-600" readonly value="0">
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label font-12">Increment Amount (PKR) *</label>
                        <input type="number" id="qi_increment_amount" class="form-control bg-white-input" placeholder="e.g. 5000" min="1" step="any" required>
                    </div>
                </div>
                <div class="form-grid-2 mb-16">
                    <div class="form-group">
                        <label class="admin-form-label font-12">New Gross Salary (PKR)</label>
                        <input type="number" id="qi_new_salary" name="new_salary" class="form-control bg-white-input font-700 text-success" placeholder="Calculated automatically" step="any" required>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label font-12">Effective Date</label>
                        <input type="date" id="qi_effective_date" name="effective_date" class="form-control bg-white-input" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="p-12 rounded-8 bg-light flex-between font-13 text-dark">
                    <span>Increment Percentage:</span>
                    <strong id="qi_percentage_label" class="text-primary-color">+0%</strong>
                </div>
            </div>
            <div class="modal-footer flex-between p-20 border-top">
                <button type="button" class="btn-cancel-modal" onclick="closeQuickSalaryModal()">Cancel</button>
                <button type="submit" class="btn-primary px-24">Apply Increment</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= \App\Core\View::asset('js/shared/employees.js?v=2') ?>"></script>
<?php include __DIR__ . '/../partials/admin/footer.php'; ?>
