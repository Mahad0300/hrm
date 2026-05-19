<?php
$page_title = "Payroll Management";
$page_subtitle = "Manage employee salaries and deductions.";
include 'includes/header.php';

// Fetch departments for filters
$deptStmt = $pdo->query("SELECT id, name FROM departments ORDER BY name ASC");
$departments = $deptStmt->fetchAll();
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Page Action Area -->
<div class="page-action-area">
    <button class="btn-primary" onclick="openGenerateModal()">
        <i data-lucide="calculator"></i>
        <span>Run Payroll</span>
    </button>
</div>

<!-- Filters Card -->
<div class="card p-24 mb-24">
    <div class="filter-grid grid-4">
        <div class="filter-item">
            <label class="admin-form-label font-12">Search Employee</label>
            <div class="search-box w-full">
                <i data-lucide="search" size="16"></i>
                <input type="text" id="searchPayroll" class="form-control" placeholder="ID or Name...">
            </div>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Select Month</label>
            <div class="search-box w-full">
                <i data-lucide="calendar" size="16"></i>
                <input type="month" id="filterMonth" class="form-control" value="<?= date('Y-m') ?>">
            </div>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Status</label>
            <select class="form-control" id="filterStatus">
                <option value="">All Status</option>
                <option value="Paid">Paid</option>
                <option value="Pending">Pending</option>
            </select>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Department</label>
            <select class="form-control" id="filterDept">
                <option value="">All Departments</option>
            </select>
        </div>
    </div>
</div>

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
        <span class="font-13 text-light" id="tableSummary">Showing 1 to 10 of 10 entries</span>
    </div>
</div>

<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>EMPLOYEE</th>
                    <th>MONTH</th>
                    <th>BASIC SALARY</th>
                    <th>TOTAL DEDUCTION</th>
                    <th>NET PAYABLE</th>
                    <th>STATUS</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody id="payrollTableBody">
                <!-- Data will be fetched dynamically via payroll.js -->
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

<!-- Edit Payroll Modal -->
<div class="modal-overlay" id="editPayrollModal">
    <div class="modal-content premium wide-lg">
        <div class="modal-header">
            <div class="flex-center gap-12">
                <div class="type-icon-box primary">
                    <i data-lucide="pencil" size="20"></i>
                </div>
                <div>
                    <h3 class="font-18 font-700 m-0">Edit Payroll Record</h3>
                    <p class="font-12 text-light m-0" id="editPayrollSubtitle">Updating payroll for Emma Williams (EM-4820)</p>
                </div>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>

        <form id="editPayrollForm">
            <input type="hidden" name="employee_id" id="editEmpId">
            <input type="hidden" name="month" id="editMonth">
            <div class="modal-body p-30">
                <div class="grid-2-1 gap-8">
                    <!-- Left Column: Editable Fields -->
                    <div class="form-section">
                        <div class="form-grid-2">
                            <div class="form-group col-span-2">
                                <label class="admin-form-label">Payroll Month</label>
                                <div class="month-picker-container" id="payrollMonthPicker">
                                    <div class="month-picker-inline">
                                        <div class="month-picker-header">
                                            <button type="button" class="icon-btn" id="prevYear"><i
                                                    data-lucide="chevron-left" size="18"></i></button>
                                            <h4 id="currentYearDisplay">2026</h4>
                                            <button type="button" class="icon-btn" id="nextYear"><i
                                                    data-lucide="chevron-right" size="18"></i></button>
                                        </div>
                                        <div class="month-grid">
                                            <button type="button" class="month-item" data-month="0">Jan</button>
                                            <button type="button" class="month-item" data-month="1">Feb</button>
                                            <button type="button" class="month-item" data-month="2">Mar</button>
                                            <button type="button" class="month-item" data-month="3">Apr</button>
                                            <button type="button" class="month-item" data-month="4">May</button>
                                            <button type="button" class="month-item" data-month="5">Jun</button>
                                            <button type="button" class="month-item" data-month="6">Jul</button>
                                            <button type="button" class="month-item active" data-month="7">Aug</button>
                                            <button type="button" class="month-item" data-month="8">Sep</button>
                                            <button type="button" class="month-item" data-month="9">Oct</button>
                                            <button type="button" class="month-item" data-month="10">Nov</button>
                                            <button type="button" class="month-item" data-month="11">Dec</button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="month" id="monthInput" value="2026-08">
                                </div>
                            </div>

                            <div class="form-grid-3 col-span-2">
                                <div class="form-group">
                                    <label class="admin-form-label">Total Leaves</label>
                                    <input type="number" class="form-control bg-white-input" name="leaves" value="0"
                                        placeholder="0" min="0">
                                </div>
                                <div class="form-group">
                                    <label class="admin-form-label">Total Late</label>
                                    <input type="number" class="form-control bg-white-input" name="late" value="0"
                                        placeholder="0" min="0">
                                </div>
                                <div class="form-group">
                                    <label class="admin-form-label">Total Half-day</label>
                                    <input type="number" class="form-control bg-white-input" name="halfday" value="0"
                                        placeholder="0" min="0">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="admin-form-label">Loan Deduction</label>
                                <input type="number" class="form-control bg-white-input" name="loan" value="0"
                                    placeholder="0.00">
                            </div>

                            <div class="form-group">
                                <label class="admin-form-label">Provident Fund</label>
                                <input type="number" class="form-control bg-white-input" name="pfund" value="0"
                                    placeholder="0.00">
                            </div>

                            <div class="form-group">
                                <label class="admin-form-label">Professional Tax</label>
                                <input type="number" class="form-control bg-white-input" name="ptax" value="0"
                                    placeholder="0.00">
                            </div>

                            <div class="form-group">
                                <label class="admin-form-label">Other Deduction</label>
                                <input type="number" class="form-control bg-white-input" name="other" value="0"
                                    min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Info Details (Read-only) -->
                    <div class="form-section salary-info-panel rounded-16 p-24">
                        <h4 class="font-14 font-600 mb-20 text-dark flex-center gap-8 justify-start">
                            <i data-lucide="info" size="18"></i> Salary Info
                        </h4>

                        <div class="space-y-4">
                            <div class="info-group">
                                <span class="info-label">Basic Salary</span>
                                <span class="info-value">$4,500.00</span>
                            </div>

                            <div class="info-group">
                                <span class="info-label">Fuel Allowance</span>
                                <span class="info-value">$200.00</span>
                            </div>

                            <div class="info-group">
                                <span class="info-label">House Rent</span>
                                <span class="info-value">$500.00</span>
                            </div>

                            <div class="info-group">
                                <span class="info-label">Utility Allowance</span>
                                <span class="info-value">$150.00</span>
                            </div>

                            <div class="info-group">
                                <span class="info-label">Mobile Allowance</span>
                                <span class="info-value">$50.00</span>
                            </div>

                            <div class="my-16 border-b-light"></div>

                            <div class="info-group">
                                <span class="info-label">Provident Fund</span>
                                <span class="info-value text-danger">-$120.00</span>
                            </div>

                            <div class="info-group">
                                <span class="info-label">Professional Tax</span>
                                <span class="info-value text-danger">-$30.00</span>
                            </div>

                            <div class="mt-20"></div>

                            <div class="info-group highlight">
                                <span class="info-label font-600">Total Earnings</span>
                                <span class="info-value font-700 text-primary">$5,400.00</span>
                            </div>

                            <div class="info-group highlight mt-8">
                                <span class="info-label font-600">Net Salary</span>
                                <span class="info-value font-700 text-success">$5,250.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-primary modal-btn-cancel js-modal-close">
                    <i data-lucide="x" size="16"></i>
                    Discard
                </button>
                <button type="submit" class="btn-primary">
                    <i data-lucide="check-circle-2" size="16"></i>
                    Update Payroll
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Generate Payroll Modal -->
<div class="modal-overlay" id="generatePayrollModal">
    <div class="modal-content premium wide-lg">
        <div class="modal-header">
            <div class="flex-center gap-12">
                <div class="type-icon-box primary">
                    <i data-lucide="wallet" size="20"></i>
                </div>
                <div>
                    <h3 class="font-18 font-700 m-0">Generate Payroll</h3>
                    <p class="font-12 text-light m-0">Process salary disbursement for various departments or individuals.</p>
                </div>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body p-0">
            <div class="p-24 pb-0">
                <!-- Modal Tabs -->
                <div class="modal-tabs">
                    <button type="button" class="tab-btn active" onclick="switchGenerateTab('all')">Select All
                        Employees</button>
                    <button type="button" class="tab-btn" onclick="switchGenerateTab('specific')">Select Specific</button>
                </div>

                <!-- Tab: Select All (Summary) -->
                <div id="tab-all" class="tab-pane active">
                    <div class="highlight-box bg-primary-light border-primary-light">
                        <div class="flex-between">
                            <div>
                                <p class="font-600 text-primary-color font-16">Selected Period: <span id="selectedMonthText"><?= date('F Y') ?></span></p>
                                <p class="font-13 text-light mt-4">Total Employees to process: <span id="totalEmployeesToProcess">0</span></p>
                            </div>
                            <i data-lucide="info" class="text-primary-color opacity-50" size="24"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Select Specific (Enhanced Table) -->
            <div id="tab-specific" class="tab-pane">
                <div class="p-24 bg-light-soft border-bottom">
                    <div class="grid-3 gap-16">
                        <div class="form-group mb-0">
                            <label class="admin-form-label font-12">Search by ID</label>
                            <div class="modal-search" style="margin-bottom: 0;">
                                <i data-lucide="hash" class="input-icon" size="16"></i>
                                <input type="text" id="genSearchId" class="form-control bg-white-input" placeholder="e.g. 033">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="admin-form-label font-12">Search by Name</label>
                            <div class="modal-search" style="margin-bottom: 0;">
                                <i data-lucide="search" class="input-icon" size="16"></i>
                                <input type="text" id="genSearchName" class="form-control bg-white-input" placeholder="e.g. Ahmed">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="admin-form-label font-12">Department</label>
                            <select class="form-control bg-white-input" id="genDept">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= $dept['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bulk-table-container border-bottom">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAllEmployees" class="custom-checkbox">
                                </th>
                                <th style="width: 30%;">EMPLOYEE DETAILS</th>
                                <th style="width: 25%;">DESIGNATION</th>
                                <th style="width: 25%;">DEPARTMENT</th>
                                <th style="width: 20%;">BASIC SALARY</th>
                            </tr>
                        </thead>
                        <tbody id="specificEmployeeList">
                            <!-- JS will populate this with real employees -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-24 pt-0">
                <form id="payrollForm">
                    <div class="form-grid-2">
                        <div class="form-group mb-0">
                            <label class="admin-form-label">Payment Method</label>
                            <select class="form-control bg-white-input">
                                <option>Direct Deposit</option>
                                <option>Bank Transfer</option>
                                <option>Cheque</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="admin-form-label">Expected Payout Month</label>
                            <div class="input-with-icon">
                                <i data-lucide="calendar" class="input-icon"></i>
                                <input type="month" class="form-control bg-white-input pl-45" value="<?= date('Y-m') ?>">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer flex-between gap-12 bg-light-soft">
            <span class="font-13 text-light" id="selectedCountText">0 employees selected</span>
            <div class="flex-center gap-12">
                <button type="button" class="btn btn-light px-24" onclick="closeModal('generatePayrollModal')">Cancel</button>
                <button type="submit" form="payrollForm" class="btn btn-primary px-30">
                    <i data-lucide="dollar-sign" size="16"></i>
                    <span>Start Disbursement</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/payroll.js"></script>
<?php include 'includes/footer.php'; ?>