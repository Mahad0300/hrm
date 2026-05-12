<?php
$page_title = "My Payroll";
$page_subtitle = "Your salary records and net pay by month in one place.";
include 'includes/header.php';

$payroll_user_name = 'James Wilson';
$payroll_user_id = 'EM-4820';
$payroll_user_role = 'Developer';
$payroll_user_avatar = 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150';

$user_id = $_SESSION['user_id'] ?? 0;

// Fetch Employee Name for URL
$empStmt = $pdo->prepare("SELECT first_name, middle_name, last_name FROM employees WHERE id = ?");
$empStmt->execute([$user_id]);
$empData = $empStmt->fetch();
$fullName = trim(($empData['first_name'] ?? '') . ' ' . ($empData['middle_name'] ?? '') . ' ' . ($empData['last_name'] ?? ''));
$urlName = urlencode(str_replace(' ', '-', $fullName));

// Fetch ONLY 'Paid' payroll records for the logged-in employee
$stmt = $pdo->prepare("SELECT * FROM payroll WHERE employee_id = ? AND status = 'Paid' ORDER BY month_year DESC");
$stmt->execute([$user_id]);
$payroll_records = $stmt->fetchAll();

$payroll_row_count = count($payroll_records);
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Payroll Table -->
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
        <span class="font-13 text-light" id="tableSummary">Showing 1 to <?= min(10, $payroll_row_count) ?> of <?= $payroll_row_count ?> entries</span>
    </div>
</div>

<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="data-table payroll-table">
            <thead>
                <tr>
                    <th>PAY PERIOD</th>
                    <th>BASIC SALARY</th>
                    <th>LEAVE</th>
                    <th>LATE</th>
                    <th>HALF DAY</th>
                    <th>TOTAL DEDUCTIONS</th>
                    <th>NET SALARY</th>
                    <th class="text-center">PAYSLIP</th>
                </tr>
            </thead>
            <tbody id="payrollTableBody">
                <?php if ($payroll_row_count > 0): ?>
                    <?php foreach ($payroll_records as $row):
                        $dateObj = DateTime::createFromFormat('Y-m', $row['month_year']);
                        $formattedMonth = $dateObj ? $dateObj->format('F Y') : $row['month_year'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($formattedMonth, ENT_QUOTES, 'UTF-8') ?></td>
                        <td>PKR <?= number_format($row['basic_salary']) ?></td>
                        <td><?= (int) $row['leaves_count'] ?></td>
                        <td><?= (int) $row['lates_count'] ?></td>
                        <td><?= (int) $row['halfdays_count'] ?></td>
                        <td>PKR <?= number_format($row['deductions']) ?></td>
                        <td class="font-bold">PKR <?= number_format($row['net_payable']) ?></td>
                        <td class="text-center">
                            <div class="btn-group justify-center">
                                <button type="button" onclick="window.open('payslip-print.php?month=<?= htmlspecialchars($row['month_year']) ?>&name=<?= $urlName ?>', '_blank')" class="action-btn action-btn-view" title="View payslip">
                                    <i data-lucide="eye" size="16"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-20 text-light">No payroll records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-24 flex-between border-top">
        <span class="font-13 text-light" id="paginationInfo">Showing 1 to <?= min(10, $payroll_row_count) ?> of <?= $payroll_row_count ?> entries</span>
        <div class="flex-center gap-8" id="paginationControls">
            <button class="action-btn" id="prevPage"><i data-lucide="chevron-left" size="16"></i></button>
            <div id="pageNumbers" class="flex-center gap-8">
                <button class="action-btn btn-active">1</button>
            </div>
            <button class="action-btn" id="nextPage"><i data-lucide="chevron-right" size="16"></i></button>
        </div>
    </div>
</div>

<script src="assets/js/payroll.js"></script>
<?php include 'includes/footer.php'; ?>
