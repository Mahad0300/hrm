<?php
// admin/employee-profile.php
require_once '../includes/db_connect.php';
require_once '../includes/auth_helper.php';

$page_title = 'Employee Profile - HRM';
include 'includes/header.php';
include 'includes/sidebar.php';

// Fetch Employee Data
$emp_id = $_GET['id'] ?? '';
$employee = null;

if ($emp_id) {
    try {
        $stmt = $pdo->prepare("
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
            WHERE e.id = ?
        ");
        $stmt->execute([$emp_id]);
        $employee = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error fetching employee details.";
    }
}

if (!$employee) {
    echo "<script>window.location.href='employees.php';</script>";
    exit;
}

// Format Name
$full_name = trim($employee['first_name'] . ' ' . ($employee['middle_name'] ? $employee['middle_name'] . ' ' : '') . $employee['last_name']);

// Default Avatar URL
$avatar_url = $employee['profile_pic'] ? '../' . $employee['profile_pic'] : '../images/profile-image/default-avatar.svg';

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

// Fetch Leave Summary
$leave_summary = [];
try {
    // Get base quotas
    $stmt = $pdo->query("SELECT id, name, days_per_year FROM leave_types");
    $leave_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($leave_types as $lt) {
        // Calculate used days for this type
        $used_stmt = $pdo->prepare("SELECT SUM(DATEDIFF(end_date, start_date) + 1) as used_days 
                                    FROM leave_requests 
                                    WHERE employee_id = ? AND leave_type_id = ? AND status = 'Approved'");
        $used_stmt->execute([$emp_id, $lt['id']]);
        $used_result = $used_stmt->fetch();
        $used_days = $used_result['used_days'] ? (int)$used_result['used_days'] : 0;
        
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
                    <a href="employees.php" class="action-btn no-bg border" title="Back to Directory">
                        <i data-lucide="arrow-left" size="18"></i>
                    </a>
                    <h1 class="font-24 font-700 ls-05">Employee Profile</h1>
                </div>
                <p class="text-light font-14">Detailed profile information for
                    <strong><?php echo $full_name; ?></strong></p>
            </div>
        </div>
    </header>

    <div>
        <div class="profile-grid">
            <!-- Left Column: Primary Identity -->
            <div class="profile-aside">
                <div class="premium-card profile-identity-card flex-column flex-center text-center mb-24">
                    <div class="profile-avatar-wrapper mb-20">
                        <img src="<?php echo $avatar_url; ?>" class="profile-avatar-xl shadow-lg"
                            alt="<?php echo $full_name; ?>" 
                            onerror="this.src='../images/profile-image/default-avatar.svg'">
                        <span
                            class="status-indicator-lg <?php echo strtolower($employee['status']) === 'active' ? 'active' : 'inactive'; ?> border-4"></span>
                    </div>
                    <h2 class="font-22 font-700 mb-4"><?php echo $full_name; ?></h2>
                    <p class="text-primary-color font-600 mb-12">
                        <?php echo displayValue($employee['job_title'], 'Employee'); ?></p>
                    <div
                        class="badge <?php echo strtolower($employee['status']) === 'active' ? 'badge-success' : 'badge-light'; ?> px-15 py-6">
                        <?php echo $employee['status']; ?> Employee
                    </div>
                </div>

                <div class="premium-card p-24 mb-24">
                    <h3 class="font-15 font-700 mb-20 flex-center gap-10">
                        <i data-lucide="contact" size="18" class="text-primary-color"></i>
                        Primary Contact
                    </h3>
                    <div class="mb-20">
                        <label class="admin-form-label">Email</label>
                        <span class="font-14 font-500 block"><?php echo $employee['email']; ?></span>
                    </div>
                    <div class="mb-20">
                        <label class="admin-form-label">Phone Number</label>
                        <span class="font-14 font-500 block"><?php echo displayValue($employee['phone']); ?></span>
                    </div>
                </div>

                <div class="premium-card p-24 mb-24">
                    <h3 class="font-15 font-700 mb-20 flex-center gap-10">
                        <i data-lucide="shield-alert" size="18" class="text-primary-color"></i>
                        Emergency Contact
                    </h3>
                    <div class="mb-20">
                        <label class="admin-form-label">Emergency Contact</label>
                        <span
                            class="font-14 font-500 block"><?php echo displayValue($employee['emergency_contact']); ?></span>
                    </div>
                    <div>
                        <label class="admin-form-label">Emergency Contact Relation</label>
                        <span
                            class="font-14 font-500 block"><?php echo displayValue($employee['emergency_relation']); ?></span>
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
                    <h3 class="font-15 font-700 mb-20 flex-center gap-10">
                        <i data-lucide="trending-up" size="18" class="text-primary-color"></i>
                        Salary Increment History
                    </h3>
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
                                <label class="admin-form-label">First Name</label>
                                <span class="font-14 font-500 block"><?php echo $employee['first_name']; ?></span>
                            </div>
                            <div>
                                <label class="admin-form-label">Middle Name</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['middle_name']); ?></span>
                            </div>
                            <div>
                                <label class="admin-form-label">Last Name</label>
                                <span class="font-14 font-500 block"><?php echo $employee['last_name']; ?></span>
                            </div>
                        </div>
                        <div class="form-grid-3 mb-30">
                            <div>
                                <label class="admin-form-label">Gender</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['gender']); ?></span>
                            </div>
                            <div>
                                <label class="admin-form-label">Date of Birth</label>
                                <span
                                    class="font-14 font-500 block"><?php echo !empty($employee['dob']) ? date("F j, Y", strtotime($employee['dob'])) : '-'; ?></span>
                            </div>
                            <div>
                                <label class="admin-form-label">ID card Number</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['cnic_number']); ?></span>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="admin-form-label">Residential Address</label>
                            <span
                                class="font-14 font-500 block"><?php echo displayValue($employee['address']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Job & Banking -->
                <div class="premium-card mb-24">
                    <div class="card-header p-24 border-bottom">
                        <h3 class="font-16 font-700 flex-center gap-10">
                            <i data-lucide="briefcase" size="20" class="text-primary-color"></i>
                            Job & Banking Profile
                        </h3>
                    </div>
                    <div class="card-body p-24">
                        <div class="form-grid-3 mb-30">
                            <div>
                                <label class="admin-form-label">Shift Timing</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['shift_name']); ?>
                                    <?php echo ($employee['start_time']) ? '(' . formatShiftTime($employee['start_time'], $employee['end_time']) . ')' : ''; ?></span>
                            </div>
                            <div>
                                <label class="admin-form-label">Job Title</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['job_title']); ?></span>
                            </div>
                            <div>
                                <label class="admin-form-label">Department</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['dept_name']); ?></span>
                            </div>
                        </div>
                        <div class="form-grid-3 mb-10">
                            <div>
                                <label class="admin-form-label">Employment Type</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['job_type']); ?></span>
                            </div>
                            <div>
                                <label class="admin-form-label">Monthly Salary</label>
                                <span
                                    class="font-14 font-600 block"><?php echo $employee['salary'] ? number_format($employee['salary']) : '-'; ?></span>
                            </div>
                            <div>
                                <label class="admin-form-label">Joining Date</label>
                                <span
                                    class="font-14 font-500 block"><?php echo !empty($employee['joining_date']) ? date("M d, Y", strtotime($employee['joining_date'])) : '-'; ?></span>
                            </div>
                        </div>

                        <div class="border-top pt-30 mt-30">
                            <h3 class="font-15 font-700 flex-center gap-10 mb-24">
                                <i data-lucide="building" size="18" class="text-primary-color"></i>
                                Bank Information
                            </h3>
                            <div class="form-grid-3 mb-24">
                                <div>
                                    <label class="admin-form-label">Bank Name</label>
                                    <span
                                        class="font-13 font-600 block"><?php echo displayValue($employee['bank_name']); ?></span>
                                </div>
                                <div>
                                    <label class="admin-form-label">Account Type</label>
                                    <span
                                        class="font-13 font-500 block"><?php echo displayValue($employee['account_type']); ?></span>
                                </div>
                                <div>
                                    <label class="admin-form-label">Account Title</label>
                                    <span
                                        class="font-13 font-500 block"><?php echo displayValue($employee['account_title']); ?></span>
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div>
                                    <label class="admin-form-label">Account number</label>
                                    <span
                                        class="font-13 font-600 block ls-05"><?php echo displayValue($employee['account_number']); ?></span>
                                </div>
                                <div>
                                    <label class="admin-form-label">Bank Branch</label>
                                    <span
                                        class="font-13 font-500 block"><?php echo displayValue($employee['branch_info']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Education & Docs -->
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
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['qualification']); ?></span>
                            </div>
                            <div>
                                <label class="admin-form-label">Degree / Certification</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['degree_certification']); ?></span>
                            </div>
                        </div>
                        <div class="form-grid-2 mb-30">
                            <div>
                                <label class="admin-form-label">University / Institute</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['college_university']); ?></span>
                            </div>
                            <div>
                                <label class="admin-form-label">Professional
                                    Expertise</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['professional_expertise']); ?></span>
                            </div>
                        </div>
                        <div class="form-grid-2 mb-30 border-top pt-30 mt-10">
                            <div>
                                <label class="admin-form-label">Last Employer</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['last_employer']); ?></span>
                            </div>
                            <div>
                                <label class="admin-form-label">Last Designation</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['last_designation']); ?></span>
                            </div>
                        </div>

                        <div class="form-grid-2 mb-40">
                            <div>
                                <label class="admin-form-label">Experience From Date</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['experience_from']); ?></span>
                            </div>
                            <div>
                                <label class="admin-form-label">Experience To Date</label>
                                <span
                                    class="font-14 font-500 block"><?php echo displayValue($employee['experience_to']); ?></span>
                            </div>
                        </div>

                        <!-- Documents Section -->
                        <div class="border-top pt-30 mt-30">
                            <h3 class="font-15 font-700 flex-center gap-10 mb-20 text-primary-color">
                                <i data-lucide="paperclip" size="18"></i>
                                Document Attachments
                            </h3>

                            <div class="form-grid-3 gap-24">
                                <!-- Resume Card -->
                                <?php if ($employee['resume_path']): ?>
                                    <a href="../<?php echo $employee['resume_path']; ?>" target="_blank"
                                        class="doc-card border rounded-16 p-20 hover-bg-light transition block no-underline text-dark">
                                        <label class="admin-form-label cursor-pointer uppercase font-11">Resume Attachment</label>
                                        <div class="flex-center gap-12 mt-4">
                                            <div class="icon-square-40 bg-primary-soft text-primary-color flex-shrink-0">
                                                <i data-lucide="file-text" size="20"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <span class="font-13 font-600 block truncate"><?php echo basename($employee['resume_path']); ?></span>
                                            </div>
                                        </div>
                                    </a>
                                <?php endif; ?>

                                <!-- ID Card Card -->
                                <?php if ($employee['id_card_path']): ?>
                                    <a href="../<?php echo $employee['id_card_path']; ?>" target="_blank"
                                        class="doc-card border rounded-16 p-20 hover-bg-light transition block no-underline text-dark">
                                        <label class="admin-form-label cursor-pointer uppercase font-11">ID Card Attachment</label>
                                        <div class="flex-center gap-12 mt-4">
                                            <div class="icon-square-40 bg-success-soft text-success-color flex-shrink-0">
                                                <i data-lucide="image" size="20"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <span class="font-13 font-600 block truncate"><?php echo basename($employee['id_card_path']); ?></span>
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
                                            <label class="admin-form-label cursor-pointer uppercase font-11">Other Document</label>
                                            <div class="flex-center gap-12 mt-4">
                                                <div class="icon-square-40 bg-warning-soft text-warning-color flex-shrink-0">
                                                    <i data-lucide="files" size="20"></i>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <span class="font-13 font-600 block truncate"><?php echo basename($doc_path); ?></span>
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
    </div> <!-- End Right Column -->
    </div> <!-- End Profile Grid -->
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>

<?php include 'includes/footer.php'; ?>