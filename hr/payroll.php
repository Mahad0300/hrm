<?php 
$page_title = "Payroll Management";
$page_subtitle = "Manage employee salaries and deductions.";
include 'includes/header.php'; 
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Page Action Area -->
<div class="page-action-area">
    <button class="btn-primary" onclick="openModal('generatePayrollModal')">
        <i data-lucide="calculator"></i>
        <span>Run Payroll</span>
    </button>
</div>

<!-- Filters Card -->
<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="search-box">
            <i data-lucide="search" size="18"></i>
            <input type="text" placeholder="Search by employee...">
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">Month (Current)</option>
                <option>August 2026</option>
                <option>July 2026</option>
                <option>June 2026</option>
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">Status (All)</option>
                <option>Paid</option>
                <option>Pending</option>
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">Department (All)</option>
                <option>Engineering</option>
                <option>HR</option>
                <option>Marketing</option>
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
                    <th>DEDUCTIONS</th>
                    <th>NET PAYABLE</th>
                    <th>STATUS</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody id="payrollTableBody">
                <!-- Row 1 -->
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
                    <td>August 2026</td>
                    <td>$4,500.00</td>
                    <td>$150.00</td>
                    <td class="font-bold">$4,550.00</td>
                    <td><span class="badge badge-success">Paid</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="action-btn action-btn-edit" title="Edit" onclick="openEditPayrollModal('EM-4820', 'Emma Williams')"><i data-lucide="edit-2" size="16"></i></button>
                            <button class="action-btn action-btn-view" title="View Payslip"><i data-lucide="eye" size="16"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2 -->
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
                    <td>August 2026</td>
                    <td>$5,200.00</td>
                    <td>$200.00</td>
                    <td class="font-bold">$5,500.00</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="action-btn action-btn-edit" title="Edit" onclick="openEditPayrollModal('EM-4821', 'Oliver Mitchell')"><i data-lucide="edit-2" size="16"></i></button>
                            <button class="action-btn action-btn-view" title="View Payslip"><i data-lucide="eye" size="16"></i></button>
                        </div>
                    </td>
                </tr>
                  <!-- Row 1 -->
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
                    <td>August 2026</td>
                    <td>$4,500.00</td>
                    <td>$150.00</td>
                    <td class="font-bold">$4,550.00</td>
                    <td><span class="badge badge-success">Paid</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="action-btn action-btn-edit" title="Edit" onclick="openEditPayrollModal('EM-4820', 'Emma Williams')"><i data-lucide="edit-2" size="16"></i></button>
                            <button class="action-btn action-btn-view" title="View Payslip"><i data-lucide="eye" size="16"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2 -->
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
                    <td>August 2026</td>
                    <td>$5,200.00</td>
                    <td>$200.00</td>
                    <td class="font-bold">$5,500.00</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="action-btn action-btn-edit" title="Edit" onclick="openEditPayrollModal('EM-4821', 'Oliver Mitchell')"><i data-lucide="edit-2" size="16"></i></button>
                            <button class="action-btn action-btn-view" title="View Payslip"><i data-lucide="eye" size="16"></i></button>
                        </div>
                    </td>
                </tr>
                  <!-- Row 1 -->
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
                    <td>August 2026</td>
                    <td>$4,500.00</td>
                    <td>$150.00</td>
                    <td class="font-bold">$4,550.00</td>
                    <td><span class="badge badge-success">Paid</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="action-btn action-btn-edit" title="Edit" onclick="openEditPayrollModal('EM-4820', 'Emma Williams')"><i data-lucide="edit-2" size="16"></i></button>
                            <button class="action-btn action-btn-view" title="View Payslip"><i data-lucide="eye" size="16"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2 -->
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
                    <td>August 2026</td>
                    <td>$5,200.00</td>
                    <td>$200.00</td>
                    <td class="font-bold">$5,500.00</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="action-btn action-btn-edit" title="Edit" onclick="openEditPayrollModal('EM-4821', 'Oliver Mitchell')"><i data-lucide="edit-2" size="16"></i></button>
                            <button class="action-btn action-btn-view" title="View Payslip"><i data-lucide="eye" size="16"></i></button>
                        </div>
                    </td>
                </tr>
                  <!-- Row 1 -->
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
                    <td>August 2026</td>
                    <td>$4,500.00</td>
                    <td>$150.00</td>
                    <td class="font-bold">$4,550.00</td>
                    <td><span class="badge badge-success">Paid</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="action-btn action-btn-edit" title="Edit" onclick="openEditPayrollModal('EM-4820', 'Emma Williams')"><i data-lucide="edit-2" size="16"></i></button>
                            <button class="action-btn action-btn-view" title="View Payslip"><i data-lucide="eye" size="16"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2 -->
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
                    <td>August 2026</td>
                    <td>$5,200.00</td>
                    <td>$200.00</td>
                    <td class="font-bold">$5,500.00</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="action-btn action-btn-edit" title="Edit" onclick="openEditPayrollModal('EM-4821', 'Oliver Mitchell')"><i data-lucide="edit-2" size="16"></i></button>
                            <button class="action-btn action-btn-view" title="View Payslip"><i data-lucide="eye" size="16"></i></button>
                        </div>
                    </td>
                </tr>
                  <!-- Row 1 -->
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
                    <td>August 2026</td>
                    <td>$4,500.00</td>
                    <td>$150.00</td>
                    <td class="font-bold">$4,550.00</td>
                    <td><span class="badge badge-success">Paid</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="action-btn action-btn-edit" title="Edit" onclick="openEditPayrollModal('EM-4820', 'Emma Williams')"><i data-lucide="edit-2" size="16"></i></button>
                            <button class="action-btn action-btn-view" title="View Payslip"><i data-lucide="eye" size="16"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2 -->
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
                    <td>August 2026</td>
                    <td>$5,200.00</td>
                    <td>$200.00</td>
                    <td class="font-bold">$5,500.00</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="action-btn action-btn-edit" title="Edit" onclick="openEditPayrollModal('EM-4821', 'Oliver Mitchell')"><i data-lucide="edit-2" size="16"></i></button>
                            <button class="action-btn action-btn-view" title="View Payslip"><i data-lucide="eye" size="16"></i></button>
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

<!-- Edit Payroll Modal -->
<div class="modal-overlay" id="editPayrollModal">
    <div class="modal-content premium wide-md">
        <div class="modal-header">
            <div>
                <h3>Edit Payroll Record</h3>
                <p class="font-12 text-light mt-1" id="editPayrollSubtitle">Updating payroll for Emma Williams (EM-4820)</p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        
        <form id="editPayrollForm">
            <div class="modal-body p-30">
                <div class="grid-2-1 gap-24">
                    <!-- Left Column: Editable Fields -->
                    <div class="form-section">
                        <div class="form-grid-2">
                            <div class="form-group col-span-2">
                                <label class="admin-form-label">Payroll Month</label>
                                <div class="month-picker-container" id="payrollMonthPicker">
                                    <div class="month-picker-inline">
                                        <div class="month-picker-header">
                                            <button type="button" class="icon-btn" id="prevYear"><i data-lucide="chevron-left" size="18"></i></button>
                                            <h4 id="currentYearDisplay">2026</h4>
                                            <button type="button" class="icon-btn" id="nextYear"><i data-lucide="chevron-right" size="18"></i></button>
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
                            
                            <div class="form-group">
                                <label class="admin-form-label">Total Leaves</label>
                                <input type="number" class="form-control bg-white-input" name="leaves" value="2" placeholder="0">
                            </div>
                            
                            <div class="form-group">
                                <label class="admin-form-label">Total Late</label>
                                <input type="number" class="form-control bg-white-input" name="late" value="1" placeholder="0">
                            </div>
                            
                            <div class="form-group">
                                <label class="admin-form-label">Total Half-day</label>
                                <input type="number" class="form-control bg-white-input" name="halfday" value="0" placeholder="0">
                            </div>
                            
                            <div class="form-group">
                                <label class="admin-form-label">Loan Deduction</label>
                                <input type="number" class="form-control bg-white-input" name="loan" value="0" placeholder="0.00">
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
            <div>
                <h3>Generate Payroll</h3>
                <p class="font-12 text-light mt-1">Process salary disbursement for various departments or individuals.</p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body">
            <!-- Modal Tabs -->
            <div class="modal-tabs">
                <button type="button" class="tab-btn active" onclick="switchGenerateTab('all')">Select All Employees</button>
                <button type="button" class="tab-btn" onclick="switchGenerateTab('specific')">Select Specific</button>
            </div>

            <!-- Tab: Select All (Summary) -->
            <div id="tab-all" class="tab-pane active">
                <div class="highlight-box bg-primary-light border-primary-light">
                    <div class="flex-between">
                        <div>
                            <p class="font-600 text-primary-color font-16">Selected Period: August 2026</p>
                            <p class="font-13 text-light mt-4">Total Employees to process: 482</p>
                        </div>
                        <i data-lucide="info" class="text-primary-color opacity-50" size="24"></i>
                    </div>
                </div>
            </div>

            <!-- Tab: Select Specific (Enhanced Table) -->
            <div id="tab-specific" class="tab-pane">
                <div class="modal-filters">
                    <div class="modal-search">
                        <i data-lucide="search" class="input-icon" size="16"></i>
                        <input type="text" class="form-control bg-white-input" placeholder="Search by name or ID..." onkeyup="filterSpecificEmployees()">
                    </div>
                    <div class="form-group">
                        <select class="form-control bg-white-input font-13" id="filterDept" onchange="filterSpecificEmployees()">
                            <option value="">All Departments</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Human Resources">Human Resources</option>
                            <option value="Finance">Finance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <select class="form-control bg-white-input font-13" id="filterDesig" onchange="filterSpecificEmployees()">
                            <option value="">All Designations</option>
                            <option value="Senior Developer">Senior Developer</option>
                            <option value="UI/UX Designer">UI/UX Designer</option>
                            <option value="HR Manager">HR Manager</option>
                            <option value="Finance Lead">Finance Lead</option>
                        </select>
                    </div>
                </div>
                
                <div class="table-scroll-y custom-scrollbar">
                    <table class="table-minimal">
                        <thead>
                            <tr>
                                <th width="40" class="text-center">
                                    <label class="custom-checkbox">
                                        <input type="checkbox" id="selectAllEmployees" class="emp-checkbox" onclick="toggleAllSpecific(this.checked)">
                                        <span class="checkmark"></span>
                                    </label>
                                </th>
                                <th width="200">Employee Details</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th class="text-right">Basic Salary</th>
                            </tr>
                        </thead>
                        <tbody id="specificEmployeeList">
                            <?php 
                            $employees = [
                                ['Emma Williams', 'EM-4820', 'Senior Developer', 'Engineering', '$4,500.00'],
                                ['Oliver Mitchell', 'EM-4821', 'UI/UX Designer', 'Marketing', '$5,200.00'],
                                ['Sophia Wright', 'EM-4822', 'HR Manager', 'Human Resources', '$4,800.00'],
                                ['James Wilson', 'EM-4823', 'Project Manager', 'Engineering', '$6,000.00'],
                                ['Isabella Hall', 'EM-4824', 'Finance Lead', 'Finance', '$5,500.00'],
                                ['Lucas Gray', 'EM-4825', 'Product Designer', 'Marketing', '$4,900.00'],
                                ['Ava Bennett', 'EM-4826', 'Backend Engineer', 'Engineering', '$4,700.00'],
                                ['Mason Reed', 'EM-4827', 'Marketing Exec', 'Marketing', '$3,800.00']
                            ];
                            foreach($employees as $e): ?>
                            <tr>
                                <td class="text-center">
                                    <label class="custom-checkbox">
                                        <input type="checkbox" class="emp-checkbox">
                                        <span class="checkmark"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="emp-info">
                                        <span class="name font-600"><?= $e[0] ?></span>
                                        <span class="email font-11 text-light uppercase"><?= $e[1] ?></span>
                                    </div>
                                </td>
                                <td class="font-13 text-secondary"><?= $e[2] ?></td>
                                <td class="font-13 text-secondary"><?= $e[3] ?></td>
                                <td class="font-13 font-600 text-primary-color text-right"><?= $e[4] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border-divider"></div>

            <form id="payrollForm">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="admin-form-label">Payment Method</label>
                        <select class="form-control bg-white-input">
                            <option>Direct Deposit</option>
                            <option>Bank Transfer</option>
                            <option>Check</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Expected Payout Month</label>
                        <div class="input-with-icon">
                            <i data-lucide="calendar" class="input-icon"></i>
                            <input type="month" class="form-control bg-white-input pl-45" value="<?= date('Y-m') ?>">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <div id="footerSelectionCount" class="footer-selection-info" style="visibility: hidden;">
                <i data-lucide="check-circle-2" size="16"></i>
                <span class="font-13 font-600" id="selectedCountText">0 Employees selected</span>
            </div>
            <div class="footer-actions">
                <button type="button" class="btn-ghost" onclick="closeModal('generatePayrollModal')">
                    <i data-lucide="x" size="16"></i>
                    Cancel
                </button>
                <button type="submit" form="payrollForm" class="btn-primary">
                    <i data-lucide="dollar-sign" size="16"></i>
                    Start Disbursement
                </button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/payroll.js"></script>
<?php include 'includes/footer.php'; ?>
