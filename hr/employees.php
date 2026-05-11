<?php
$page_title = "Employee Directory";
$page_subtitle = "Manage and view all members of your organization.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Page Action Area -->
<div class="page-action-area">
    <button type="button" class="btn-primary" id="btnExitEmployees" aria-pressed="false"
        aria-label="Show only exit employees" title="Show only employees with Exit status">
        <i data-lucide="log-out"></i>
        <span>Exit Employees</span>
    </button>
    <button class="btn-primary" onclick="openAddEmployeeModal()">
        <i data-lucide="plus"></i>
        <span>Add New Employee</span>
    </button>
</div>

<!-- Filters Card -->
<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="search-box">
            <i data-lucide="search" size="18"></i>
            <input type="text" placeholder="Search by name, email or ID...">
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">All Departments</option>
                <option value="eng">Engineering</option>
                <option value="design">Design</option>
                <option value="hr">Human Resources</option>
                <option value="sales">Sales & Marketing</option>
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">All Roles</option>
                <option value="lead">Team Lead</option>
                <option value="senior">Senior Associate</option>
                <option value="junior">Junior Associate</option>
                <option value="intern">Intern</option>
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="onleave">On Leave</option>
                <option value="terminated">Terminated</option>
                <option value="exit">Exit</option>
            </select>
        </div>
    </div>
</div>

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

<!-- Employees Table -->
<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>EMPLOYEE</th>
                    <th>EMAIL</th>
                    <th>DEPARTMENT</th>
                    <th>ROLE</th>
                    <th>STATUS</th>
                    <th class="text-right px-30">ACTIONS</th>
                </tr>
            </thead>
            <tbody id="employeeTableBody">
                <!-- Row 1 -->
                <tr data-emp-status="active">
                    <td>
                        <a href="employee-profile.php?id=EM-4820" class="emp-profile no-decoration hover-opacity">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email">EM-4820</span>
                            </div>
                        </a>
                    </td>
                    <td class="allow-wrap">emma.w@rtg.com</td>
                    <td class="allow-wrap">Engineering</td>
                    <td>Product Manager</td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <button class="action-btn action-btn-view" title="View Details"><i data-lucide="eye"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-edit" title="Edit"
                                onclick="openEditEmployeeModal('EM-4820')"><i data-lucide="edit-2"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete"><i data-lucide="trash-2"
                                    size="14"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2 -->
                <tr data-emp-status="active">
                    <td>
                        <a href="employee-profile.php?id=EM-4821" class="emp-profile no-decoration hover-opacity">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Oliver Mitchell</span>
                                <span class="email">EM-4821</span>
                            </div>
                        </a>
                    </td>
                    <td>oliver.m@rtg.com</td>
                    <td>Engineering</td>
                    <td>Lead Developer</td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <button class="action-btn action-btn-view" title="View Details"><i data-lucide="eye"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-edit" title="Edit"
                                onclick="openEditEmployeeModal('EM-4821')"><i data-lucide="edit-2"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete"><i data-lucide="trash-2"
                                    size="14"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 3 -->
                <tr data-emp-status="onleave">
                    <td>
                        <a href="employee-profile.php?id=EM-4822" class="emp-profile no-decoration hover-opacity">
                            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Sophia Reynolds</span>
                                <span class="email">EM-4822</span>
                            </div>
                        </a>
                    </td>
                    <td>sophia.r@rtg.com</td>
                    <td>Design</td>
                    <td>UX Designer</td>
                    <td><span class="badge badge-warning">On Leave</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <button class="action-btn action-btn-view" title="View Details"><i data-lucide="eye"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-edit" title="Edit"
                                onclick="openEditEmployeeModal('EM-4822')"><i data-lucide="edit-2"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete"><i data-lucide="trash-2"
                                    size="14"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 1 -->
                <tr data-emp-status="exit">
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email">EM-4820</span>
                            </div>
                        </div>
                    </td>
                    <td class="allow-wrap">emma.w@rtg.com</td>
                    <td class="allow-wrap">Engineering</td>
                    <td>Product Manager</td>
                    <td><span class="badge badge-danger">Exit</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <button class="action-btn action-btn-view" title="View Details"><i data-lucide="eye"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-edit" title="Edit"
                                onclick="openEditEmployeeModal('EM-4820')"><i data-lucide="edit-2"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete"><i data-lucide="trash-2"
                                    size="14"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2 -->
                <tr data-emp-status="active">
                    <td>
                        <a href="employee-profile.php?id=EM-4821" class="emp-profile no-decoration hover-opacity">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Oliver Mitchell</span>
                                <span class="email">EM-4821</span>
                            </div>
                        </a>
                    </td>
                    <td>oliver.m@rtg.com</td>
                    <td>Engineering</td>
                    <td>Lead Developer</td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <button class="action-btn action-btn-view" title="View Details"><i data-lucide="eye"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-edit" title="Edit"
                                onclick="openEditEmployeeModal('EM-4821')"><i data-lucide="edit-2"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete"><i data-lucide="trash-2"
                                    size="14"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 3 -->
                <tr data-emp-status="onleave">
                    <td>
                        <a href="employee-profile.php?id=EM-4822" class="emp-profile no-decoration hover-opacity">
                            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Sophia Reynolds</span>
                                <span class="email">EM-4822</span>
                            </div>
                        </a>
                    </td>
                    <td>sophia.r@rtg.com</td>
                    <td>Design</td>
                    <td>UX Designer</td>
                    <td><span class="badge badge-warning">On Leave</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <button class="action-btn action-btn-view" title="View Details"><i data-lucide="eye"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-edit" title="Edit"
                                onclick="openEditEmployeeModal('EM-4822')"><i data-lucide="edit-2"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete"><i data-lucide="trash-2"
                                    size="14"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 1 -->
                <tr data-emp-status="exit">
                    <td>
                        <div class="emp-profile">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Emma Williams</span>
                                <span class="email">EM-4820</span>
                            </div>
                        </div>
                    </td>
                    <td class="allow-wrap">emma.w@rtg.com</td>
                    <td class="allow-wrap">Engineering</td>
                    <td>Product Manager</td>
                    <td><span class="badge badge-danger">Exit</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <button class="action-btn action-btn-view" title="View Details"><i data-lucide="eye"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-edit" title="Edit"
                                onclick="openEditEmployeeModal('EM-4820')"><i data-lucide="edit-2"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete"><i data-lucide="trash-2"
                                    size="14"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 2 -->
                <tr data-emp-status="active">
                    <td>
                        <a href="employee-profile.php?id=EM-4821" class="emp-profile no-decoration hover-opacity">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Oliver Mitchell</span>
                                <span class="email">EM-4821</span>
                            </div>
                        </a>
                    </td>
                    <td>oliver.m@rtg.com</td>
                    <td>Engineering</td>
                    <td>Lead Developer</td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <button class="action-btn action-btn-view" title="View Details"><i data-lucide="eye"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-edit" title="Edit"
                                onclick="openEditEmployeeModal('EM-4821')"><i data-lucide="edit-2"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete"><i data-lucide="trash-2"
                                    size="14"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 3 -->
                <tr data-emp-status="onleave">
                    <td>
                        <a href="employee-profile.php?id=EM-4822" class="emp-profile no-decoration hover-opacity">
                            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Sophia Reynolds</span>
                                <span class="email">EM-4822</span>
                            </div>
                        </a>
                    </td>
                    <td>sophia.r@rtg.com</td>
                    <td>Design</td>
                    <td>UX Designer</td>
                    <td><span class="badge badge-warning">On Leave</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <button class="action-btn action-btn-view" title="View Details"><i data-lucide="eye"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-edit" title="Edit"
                                onclick="openEditEmployeeModal('EM-4822')"><i data-lucide="edit-2"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete"><i data-lucide="trash-2"
                                    size="14"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Row 1 -->
                <tr data-emp-status="exit">
                    <td>
                        <a href="employee-profile.php?id=EM-4821" class="emp-profile no-decoration hover-opacity">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150"
                                class="emp-avatar" alt="Avatar">
                            <div class="emp-info">
                                <span class="name">Oliver Mitchell</span>
                                <span class="email">EM-4821</span>
                            </div>
                        </a>
                    </td>
                    <td>oliver.m@rtg.com</td>
                    <td>Engineering</td>
                    <td>Lead Developer</td>
                    <td><span class="badge badge-danger">Exit</span></td>
                    <td class="text-right px-30">
                        <div class="btn-group justify-end">
                            <button class="action-btn action-btn-view" title="View Details"><i data-lucide="eye"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-edit" title="Edit"
                                onclick="openEditEmployeeModal('EM-4821')"><i data-lucide="edit-2"
                                    size="14"></i></button>
                            <button class="action-btn action-btn-delete" title="Delete"><i data-lucide="trash-2"
                                    size="14"></i></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="p-24 flex-between border-top">
        <span class="font-13 text-light" id="paginationInfo">Showing 0 to 0 of 0 entries</span>
        <div class="flex-center gap-8" id="paginationControls">
            <button class="action-btn" id="prevPage"><i data-lucide="chevron-left" size="16"></i></button>
            <div id="pageNumbers" class="flex-center gap-8">
                <button class="action-btn btn-active">1</button>
                <button class="action-btn">2</button>
                <button class="action-btn">3</button>
            </div>
            <button class="action-btn" id="nextPage"><i data-lucide="chevron-right" size="16"></i></button>
        </div>
    </div>
</div>

<!-- Add Employee Modal (3-Step Wizard) -->
<div class="modal-overlay" id="addEmployeeModal">
    <div class="modal-content premium wide-md">
        <div class="modal-header">
            <div>
                <h3 id="modalTitle">Add New Employee</h3>
                <p class="font-12 text-light mt-1">Fill in all details to create a new employee profile</p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>

        <div class="modal-body p-30">
            <!-- Step Indicators -->
            <div class="step-indicators">
                <div class="step-indicator active" data-step="1">
                    1
                    <span class="step-label">Personal</span>
                </div>
                <div class="step-indicator" data-step="2">
                    2
                    <span class="step-label">Job & Bank</span>
                </div>
                <div class="step-indicator" data-step="3">
                    3
                    <span class="step-label">Education</span>
                </div>
            </div>

            <form id="addEmployeeForm">
                <!-- Step 1: Personal Details -->
                <div class="step-pane active" id="step1">
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="admin-form-label">First Name *</label>
                            <input type="text" class="form-control bg-white-input" placeholder="Enter first name"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Middle Name</label>
                            <input type="text" class="form-control bg-white-input" placeholder="Enter middle name">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Last Name *</label>
                            <input type="text" class="form-control bg-white-input" placeholder="Enter last name"
                                required>
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Gender *</label>
                            <select class="form-control bg-white-input" required>
                                <option value="">Select Gender</option>
                                <option>Male</option>
                                <option>Female</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Date of Birth</label>
                            <input type="date" class="form-control bg-white-input">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Phone</label>
                            <input type="text" class="form-control bg-white-input" placeholder="+1 (555) 000-0000">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">ID card Number</label>
                            <input type="text" class="form-control bg-white-input" placeholder="00000-0000000-0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Address</label>
                        <textarea class="form-control bg-white-input" rows="2"
                            placeholder="Enter full address"></textarea>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Emergency Contact</label>
                            <input type="text" class="form-control bg-white-input" placeholder="+1 (555) 000-0000">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Emergency Contact Relation</label>
                            <input type="text" class="form-control bg-white-input" placeholder="e.g. Father, Spouse">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Email *</label>
                            <input type="email" class="form-control bg-white-input" placeholder="email@company.com"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Password *</label>
                            <div class="password-input-container">
                                <input type="password" class="form-control bg-white-input" id="add_password"
                                    placeholder="••••••••" required>
                                <button type="button" class="password-toggle"
                                    onclick="togglePassword('add_password', this)">
                                    <i data-lucide="eye" size="18"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Job & Banking -->
                <div class="step-pane" id="step2">
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="admin-form-label">Shift Timing</label>
                            <select class="form-control bg-white-input">
                                <option value="">Select Shift</option>
                                <option>Shift A (09:00 AM - 06:00 PM)</option>
                                <option>Shift B (02:00 PM - 11:00 PM)</option>
                                <option>Shift C (10:00 PM - 07:00 AM)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Job Title</label>
                            <input type="text" class="form-control bg-white-input" placeholder="e.g. Software Engineer">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Department</label>
                            <select class="form-control bg-white-input">
                                <option value="">Select Department</option>
                                <option>Engineering</option>
                                <option>Design</option>
                                <option>HR</option>
                                <option>Sales & Marketing</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="admin-form-label">Job Type</label>
                            <select class="form-control bg-white-input">
                                <option>Full Time</option>
                                <option>Part Time</option>
                                <option>Contract</option>
                                <option>Internship</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Salary</label>
                            <input type="number" class="form-control bg-white-input" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Joining Date</label>
                            <input type="date" class="form-control bg-white-input">
                        </div>
                    </div>
                    <div class="p-15 rounded-12 mb-20 border">
                        <h4 class="font-13 font-600 mb-15 text-primary-color flex-center gap-8">
                            <i data-lucide="building" size="16"></i> Banking Information
                        </h4>
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Bank Name</label>
                                <select class="form-control bg-white-input">
                                    <option>Select Bank</option>
                                    <option>Standard Chartered</option>
                                    <option>HBL</option>
                                    <option>Bank Alfalah</option>
                                    <option>UBL</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Account Type</label>
                                <select class="form-control bg-white-input">
                                    <option>Current</option>
                                    <option>Savings</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Account Title</label>
                                <input type="text" class="form-control bg-white-input" placeholder="Name on account">
                            </div>
                        </div>
                        <div class="form-grid-2 mt-10">
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Account Number</label>
                                <input type="text" class="form-control bg-white-input"
                                    placeholder="0000 0000 0000 0000">
                            </div>
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Bank Branch</label>
                                <input type="text" class="form-control bg-white-input" placeholder="Branch Code / City">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Education & Docs -->
                <div class="step-pane" id="step3">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Qualification</label>
                            <input type="text" class="form-control bg-white-input" placeholder="e.g. Master's in CS">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Degree / Certification</label>
                            <input type="text" class="form-control bg-white-input"
                                placeholder="e.g. PMP, AWS Solutions Architect">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">College / University</label>
                            <input type="text" class="form-control bg-white-input" placeholder="Name of institution">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Professional Expertise</label>
                            <input type="text" class="form-control bg-white-input"
                                placeholder="e.g. React, Node.js, UI/UX">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Last Employer</label>
                            <input type="text" class="form-control bg-white-input" placeholder="Previous Company Name">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Last Job Title</label>
                            <input type="text" class="form-control bg-white-input" placeholder="e.g. Senior Developer">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Experience From Date</label>
                            <input type="date" class="form-control bg-white-input">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Experience To Date</label>
                            <input type="date" class="form-control bg-white-input">
                        </div>
                    </div>
                    <div class="form-grid-3 mt-10">
                        <div class="form-group">
                            <label class="font-11 font-600 text-light mb-1 block uppercase">Resume Attachment</label>
                            <div class="custom-file-upload">
                                <label for="resume_upload" class="file-upload-wrapper" id="resume_wrapper">
                                    <i data-lucide="file-text" size="20"></i>
                                    <span class="file-upload-label">Choose Resume</span>
                                    <span class="file-upload-info" id="resume_filename">PDF, DOCX up to 5MB</span>
                                </label>
                                <input type="file" id="resume_upload" class="hidden-file-input"
                                    onchange="handleFileSelect(this, 'resume_wrapper', 'resume_filename')">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-11 font-600 text-light mb-1 block uppercase">ID Card Attachment *</label>
                            <div class="custom-file-upload">
                                <label for="id_upload" class="file-upload-wrapper" id="id_wrapper">
                                    <i data-lucide="image" size="20"></i>
                                    <span class="file-upload-label">Upload ID Card</span>
                                    <span class="file-upload-info" id="id_filename">PNG, JPG or PDF</span>
                                </label>
                                <input type="file" id="id_upload" class="hidden-file-input" required
                                    onchange="handleFileSelect(this, 'id_wrapper', 'id_filename')">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-11 font-600 text-light mb-1 block uppercase">Other Documents</label>
                            <div class="custom-file-upload">
                                <label for="other_upload" class="file-upload-wrapper" id="other_wrapper">
                                    <i data-lucide="files" size="20"></i>
                                    <span class="file-upload-label">Choose Files</span>
                                    <span class="file-upload-info" id="other_filename">Certificates, etc.</span>
                                </label>
                                <input type="file" id="other_upload" class="hidden-file-input" multiple
                                    onchange="handleFileSelect(this, 'other_wrapper', 'other_filename')">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer flex-between p-30 border-top-0">
            <button type="button" class="btn-primary no-bg border text-light" id="navBackBtn">
                <i data-lucide="x" size="18" id="backIcon"></i> <span id="backBtnText">Cancel Account</span>
            </button>
            <div class="flex-center gap-12">
                <button type="button" class="btn-primary px-30" id="nextStepBtn">
                    Next Step <i data-lucide="arrow-right" size="18"></i>
                </button>
                <button type="submit" form="addEmployeeForm" class="btn-primary px-30 hidden" id="submitEmployeeBtn">
                    Finalize & Create Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal (3-Step Wizard) -->
<div class="modal-overlay" id="editEmployeeModal">
    <div class="modal-content premium wide-md">
        <div class="modal-header">
            <div>
                <h3>Edit Employee Details</h3>
                <p class="font-12 text-light mt-1">Review and update employee profile information</p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>

        <div class="modal-body p-30">
            <div class="step-indicators">
                <div class="step-indicator active" data-step="1">1<span class="step-label">Personal</span></div>
                <div class="step-indicator" data-step="2">2<span class="step-label">Job & Bank</span></div>
                <div class="step-indicator" data-step="3">3<span class="step-label">Education</span></div>
            </div>

            <form id="editEmployeeForm">
                <!-- Step 1: Personal Details -->
                <div class="step-pane active">
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="admin-form-label">First Name *</label>
                            <input type="text" class="form-control bg-white-input" value="Sophia" required>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Middle Name</label>
                            <input type="text" class="form-control bg-white-input">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Last Name *</label>
                            <input type="text" class="form-control bg-white-input" value="Reynolds" required>
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Gender *</label>
                            <select class="form-control bg-white-input" required>
                                <option>Female</option>
                                <option>Male</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Date of Birth</label>
                            <input type="date" class="form-control bg-white-input" value="1995-06-15">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Phone</label>
                            <input type="text" class="form-control bg-white-input" value="+1 (555) 482-0192">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">ID card Number</label>
                            <input type="text" class="form-control bg-white-input" value="42101-5829102-1">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Address</label>
                        <textarea class="form-control bg-white-input" rows="2">422 Maple Drive, Austin, TX</textarea>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Emergency Contact</label>
                            <input type="text" class="form-control bg-white-input" value="+1 (555) 902-1122">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Emergency Contact Relation</label>
                            <input type="text" class="form-control bg-white-input" value="Spouse">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Email *</label>
                            <input type="email" class="form-control bg-white-input" value="sophia.r@rtg.com" required>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Password *</label>
                            <div class="password-input-container">
                                <input type="password" class="form-control bg-white-input" id="edit_password"
                                    value="sophia@123" required>
                                <button type="button" class="password-toggle"
                                    onclick="togglePassword('edit_password', this)">
                                    <i data-lucide="eye" size="18"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Job & Banking -->
                <div class="step-pane">
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="admin-form-label">Shift Timing</label>
                            <select class="form-control bg-white-input">
                                <option selected>Shift A (09:00 AM - 06:00 PM)</option>
                                <option>Shift B</option>
                                <option>Shift C</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Job Title</label>
                            <input type="text" class="form-control bg-white-input" value="UX Designer">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Department</label>
                            <select class="form-control bg-white-input">
                                <option selected>Design</option>
                                <option>Engineering</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="admin-form-label">Job Type</label>
                            <select class="form-control bg-white-input">
                                <option selected>Full Time</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Salary</label>
                            <input type="number" class="form-control bg-white-input" value="85000">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Joining Date</label>
                            <input type="date" class="form-control bg-white-input" value="2022-03-01">
                        </div>
                    </div>
                    <div class="p-15 rounded-12 mb-20 border">
                        <h4 class="font-13 font-600 mb-15 text-primary-color flex-center gap-8"><i
                                data-lucide="building" size="16"></i> Banking Information</h4>
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Bank Name</label>
                                <select class="form-control bg-white-input">
                                    <option selected>Standard Chartered</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Account Type</label>
                                <select class="form-control bg-white-input">
                                    <option selected>Savings</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Account Title</label>
                                <input type="text" class="form-control bg-white-input" value="Sophia Reynolds">
                            </div>
                        </div>
                        <div class="form-grid-2 mt-10">
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Account Number</label>
                                <input type="text" class="form-control bg-white-input" value="SCB-5829-1029-4821">
                            </div>
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Bank Branch</label>
                                <input type="text" class="form-control bg-white-input" value="Downtown Austin">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Education & Docs -->
                <div class="step-pane">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Qualification</label>
                            <input type="text" class="form-control bg-white-input" value="Master's in Graphic Design">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Degree / Certification</label>
                            <input type="text" class="form-control bg-white-input"
                                value="Google UX Design Professional Cert">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">College / University</label>
                            <input type="text" class="form-control bg-white-input" value="Texas State University">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Professional Expertise</label>
                            <input type="text" class="form-control bg-white-input"
                                value="Figma, Adobe XD, User Research">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Last Employer</label>
                            <input type="text" class="form-control bg-white-input" value="Creative Solutions Inc.">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Last Job Title</label>
                            <input type="text" class="form-control bg-white-input" value="Junior Designer">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Experience From Date</label>
                            <input type="date" class="form-control bg-white-input" value="2020-01-01">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Experience To Date</label>
                            <input type="date" class="form-control bg-white-input" value="2022-02-15">
                        </div>
                    </div>
                    <div class="form-grid-3 mt-10">
                        <div class="form-group">
                            <label class="font-11 font-600 text-light mb-1 block uppercase">Resume Attachment</label>
                            <div class="custom-file-upload">
                                <label for="edit_resume_upload" class="file-upload-wrapper has-file"
                                    id="edit_resume_wrapper">
                                    <i data-lucide="file-check" size="20" class="text-success"></i>
                                    <span class="file-upload-label" id="edit_resume_filename">Resume_Sophia_R.pdf</span>
                                    <span class="file-upload-info text-success">File already uploaded</span>
                                </label>
                                <input type="file" id="edit_resume_upload" class="hidden-file-input"
                                    onchange="handleFileSelect(this, 'edit_resume_wrapper', 'edit_resume_filename')">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-11 font-600 text-light mb-1 block uppercase">ID Card Attachment *</label>
                            <div class="custom-file-upload">
                                <label for="edit_id_upload" class="file-upload-wrapper has-file" id="edit_id_wrapper">
                                    <i data-lucide="image" size="20" class="text-success"></i>
                                    <span class="file-upload-label" id="edit_id_filename">ID_Card_Front.jpg</span>
                                    <span class="file-upload-info text-success">File already uploaded</span>
                                </label>
                                <input type="file" id="edit_id_upload" class="hidden-file-input" required
                                    onchange="handleFileSelect(this, 'edit_id_wrapper', 'edit_id_filename')">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-11 font-600 text-light mb-1 block uppercase">Other Documents</label>
                            <div class="custom-file-upload">
                                <label for="edit_other_upload" class="file-upload-wrapper" id="edit_other_wrapper">
                                    <i data-lucide="files" size="20"></i>
                                    <span class="file-upload-label" id="edit_other_filename">Choose Files</span>
                                    <span class="file-upload-info">Certificates, etc.</span>
                                </label>
                                <input type="file" id="edit_other_upload" class="hidden-file-input" multiple
                                    onchange="handleFileSelect(this, 'edit_other_wrapper', 'edit_other_filename')">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer flex-between p-30 border-top-0">
            <button type="button" class="btn-primary no-bg border text-light" id="editNavBackBtn">
                <i data-lucide="x" size="18" id="editBackIcon"></i> <span id="editBackBtnText">Cancel Changes</span>
            </button>
            <div class="flex-center gap-12">
                <button type="button" class="btn-primary px-30" id="editNextStepBtn">
                    Next Step <i data-lucide="arrow-right" size="18"></i>
                </button>
                <button type="submit" form="editEmployeeForm" class="btn-primary px-30 hidden" id="editSubmitBtn">
                    Save Profile Changes
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Employee Module Logic -->
<script src="assets/js/employees.js"></script>
<?php include 'includes/footer.php'; ?>