<?php
$page_title = "My Payroll";
$page_subtitle = "Your salary records and net pay by month in one place.";
include 'includes/header.php';

$payroll_user_name = 'James Wilson';
$payroll_user_id = 'EM-4820';
$payroll_user_role = 'Developer';
$payroll_user_avatar = 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150';

/**
 * Demo: logged-in employee only — pay period, attendance summary, amounts, payslip.
 * status pending = payslip disabled until processed.
 */
$payroll_records = [
    ['month' => 'March 2026', 'basic' => '$6,000.00', 'leave' => 2, 'late' => 1, 'half_day' => 0, 'deductions' => '$195.00', 'net' => '$5,805.00', 'status' => 'pending'],
    ['month' => 'February 2026', 'basic' => '$6,000.00', 'leave' => 1, 'late' => 0, 'half_day' => 1, 'deductions' => '$188.00', 'net' => '$5,812.00', 'status' => 'paid'],
    ['month' => 'January 2026', 'basic' => '$6,000.00', 'leave' => 0, 'late' => 2, 'half_day' => 0, 'deductions' => '$190.00', 'net' => '$5,810.00', 'status' => 'paid'],
    ['month' => 'December 2025', 'basic' => '$5,800.00', 'leave' => 1, 'late' => 0, 'half_day' => 0, 'deductions' => '$175.00', 'net' => '$5,625.00', 'status' => 'paid'],
    ['month' => 'November 2025', 'basic' => '$5,800.00', 'leave' => 3, 'late' => 1, 'half_day' => 0, 'deductions' => '$172.00', 'net' => '$5,628.00', 'status' => 'paid'],
    ['month' => 'October 2025', 'basic' => '$5,800.00', 'leave' => 0, 'late' => 0, 'half_day' => 2, 'deductions' => '$180.00', 'net' => '$5,620.00', 'status' => 'paid'],
    ['month' => 'September 2025', 'basic' => '$5,800.00', 'leave' => 1, 'late' => 3, 'half_day' => 0, 'deductions' => '$168.00', 'net' => '$5,632.00', 'status' => 'paid'],
    ['month' => 'August 2025', 'basic' => '$5,600.00', 'leave' => 0, 'late' => 1, 'half_day' => 0, 'deductions' => '$165.00', 'net' => '$5,435.00', 'status' => 'paid'],
    ['month' => 'July 2025', 'basic' => '$5,600.00', 'leave' => 2, 'late' => 0, 'half_day' => 1, 'deductions' => '$160.00', 'net' => '$5,440.00', 'status' => 'paid'],
    ['month' => 'June 2025', 'basic' => '$5,600.00', 'leave' => 0, 'late' => 0, 'half_day' => 0, 'deductions' => '$158.00', 'net' => '$5,442.00', 'status' => 'paid'],
];

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
                <?php foreach ($payroll_records as $row):
                    $payslipDisabled = ($row['status'] ?? '') === 'pending';
                    ?>
                <tr data-payment-status="<?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>">
                    <td><?= htmlspecialchars($row['month'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['basic'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int) $row['leave'] ?></td>
                    <td><?= (int) $row['late'] ?></td>
                    <td><?= (int) $row['half_day'] ?></td>
                    <td><?= htmlspecialchars($row['deductions'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="font-bold text-primary-color"><?= htmlspecialchars($row['net'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-center">
                        <div class="btn-group justify-center">
                            <button type="button"
                                class="action-btn action-btn-view"
                                title="<?= $payslipDisabled ? 'Payslip available after payment' : 'View payslip' ?>"
                                <?= $payslipDisabled ? 'disabled' : '' ?>><i data-lucide="eye" size="16"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
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
