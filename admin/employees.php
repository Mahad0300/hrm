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
            <input type="text" id="searchEmployee" placeholder="Search by name, email or ID...">
        </div>
        <div class="filter-item">
            <select class="form-control" id="filterDept">
                <option value="">All Departments</option>
                <option value="eng">Engineering</option>
                <option value="design">Design</option>
                <option value="hr">Human Resources</option>
                <option value="sales">Sales & Marketing</option>
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control" id="filterRole">
                <option value="">All Roles</option>
                <option value="Admin">Admin</option>
                <option value="HR">HR</option>
                <option value="Employee">Employee</option>
                <option value="Manager">Manager</option>
                <option value="Team Lead">Team Lead</option>
                <option value="Senior Associate">Senior Associate</option>
                <option value="Junior Associate">Junior Associate</option>
                <option value="Intern">Intern</option>
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control" id="filterStatus">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="On Leave">On Leave</option>
                <option value="Terminated">Terminated</option>
                <option value="Exit">Exit</option>
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
                    <th>JOB TITLE</th>
                    <th>STATUS</th>
                    <th class="text-right px-30">ACTIONS</th>
                </tr>
            </thead>
            <tbody id="employeeTableBody">
                <!-- Data will be loaded via AJAX from fetch_directory -->
            </tbody>
            </tbody>
        </table>
    </div>
    <div class="p-24 flex-between border-top">
        <span class="font-13 text-light" id="paginationInfo">Showing 1 to 10 of 482 entries</span>
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
                            <input type="text" id="add_first_name" name="first_name" class="form-control bg-white-input"
                                placeholder="Enter first name" required>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Middle Name</label>
                            <input type="text" id="add_middle_name" name="middle_name"
                                class="form-control bg-white-input" placeholder="Enter middle name">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Last Name *</label>
                            <input type="text" id="add_last_name" name="last_name" class="form-control bg-white-input"
                                placeholder="Enter last name" required>
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Gender *</label>
                            <select id="add_gender" name="gender" class="form-control bg-white-input" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control bg-white-input">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Phone *</label>
                            <input type="tel" id="add_phone" name="phone" class="form-control bg-white-input"
                                placeholder="03XXXXXXXXX" maxlength="12" required
                                title="Please enter exactly 11 digits">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">ID card Number</label>
                            <input type="text" id="add_cnic" name="cnic_number" class="form-control bg-white-input"
                                placeholder="00000-0000000-0" maxlength="15">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Address</label>
                        <textarea id="add_address" name="address" class="form-control bg-white-input" rows="2"
                            placeholder="Enter full address"></textarea>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Emergency Contact *</label>
                            <input type="tel" id="add_emergency_phone" name="emergency_contact"
                                class="form-control bg-white-input" placeholder="03XXXXXXXXX" maxlength="12" required
                                title="Please enter exactly 11 digits">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Emergency Contact Relation</label>
                            <input type="text" id="add_emergency_relation" name="emergency_relation"
                                class="form-control bg-white-input" placeholder="e.g. Father, Spouse">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Email *</label>
                            <input type="email" id="add_email" name="email" class="form-control bg-white-input"
                                placeholder="email@company.com" required>
                            <span id="email_verify_msg" class="font-11 mt-4 block"></span>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Password *</label>
                            <div class="password-input-container">
                                <input type="password" name="password" class="form-control bg-white-input"
                                    id="add_password" placeholder="••••••••" required>
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
                            <label class="admin-form-label">Shift Timing *</label>
                            <select id="add_shift" name="shift_id" class="form-control bg-white-input" required>
                                <option value="">Loading Shifts...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Job Title</label>
                            <input type="text" id="add_job_title" name="job_title" class="form-control bg-white-input"
                                placeholder="e.g. Software Engineer">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Department *</label>
                            <select id="add_dept" name="department_id" class="form-control bg-white-input" required>
                                <option value="">Loading Departments...</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="admin-form-label">Job Type</label>
                            <select id="add_job_type" name="job_type" class="form-control bg-white-input">
                                <option value="">Select Job Type</option>
                                <option value="Permanent">Permanent</option>
                                <option value="Probation">Probation</option>
                                <option value="Internship">Internship</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Salary</label>
                            <input type="number" id="add_salary" name="salary" class="form-control bg-white-input"
                                placeholder="0">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Joining Date</label>
                            <input type="date" id="add_joining_date" name="joining_date"
                                class="form-control bg-white-input">
                        </div>
                    </div>
                    <div class="p-15 rounded-12 mb-20 border">
                        <h4 class="font-13 font-600 mb-15 text-primary-color flex-center gap-8">
                            <i data-lucide="building" size="16"></i> Banking Information
                        </h4>
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Bank Name</label>
                                <select id="add_bank_name" name="bank_name" class="form-control bg-white-input">
                                    <option value="">Select Bank</option>
                                    <option value="HBL">HBL</option>
                                    <option value="ALHabib">AL Habib</option>
                                    <option value="MCB">MCB</option>
                                    <option value="UBL">UBL</option>
                                    <option value="Meezan">Meezan</option>
                                    <option value="Allied">Allied</option>
                                    <option value="Bank Alfalah">Bank Alfalah</option>
                                    <option value="Askari">Askari</option>
                                    <option value="Faysal">Faysal</option>
                                    <option value="Habib Metro">Habib Metro</option>
                                    <option value="Soneri">Soneri</option>
                                    <option value="JS Bank">JS Bank</option>
                                    <option value="Bank Islami">Bank Islami</option>
                                    <option value="Standard Chartered">Standard Chartered</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Account Type</label>
                                <select id="add_account_type" name="account_type" class="form-control bg-white-input">
                                    <option value="IBN">IBN</option>
                                    <option value="IBFT">IBFT</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Account Title</label>
                                <input type="text" id="add_account_title" name="account_title"
                                    class="form-control bg-white-input" placeholder="Name on account">
                            </div>
                        </div>
                        <div class="form-grid-2 mt-10">
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Account Number</label>
                                <input type="text" id="add_account_number" name="account_number"
                                    class="form-control bg-white-input" placeholder="0000 0000 0000 0000">
                            </div>
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Bank Branch</label>
                                <input type="text" id="add_branch_info" name="branch_info"
                                    class="form-control bg-white-input" placeholder="Branch Code / City">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Education & Docs -->
                <div class="step-pane" id="step3">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Qualification</label>
                            <input type="text" id="add_qualification" name="qualification"
                                class="form-control bg-white-input" placeholder="e.g. Master's in CS">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Degree / Certification</label>
                            <input type="text" id="add_degree" name="degree_certification"
                                class="form-control bg-white-input" placeholder="e.g. PMP, AWS Solutions Architect">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">College / University</label>
                            <input type="text" id="add_college" name="college_university"
                                class="form-control bg-white-input" placeholder="Name of institution">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Professional Expertise</label>
                            <input type="text" id="add_expertise" name="professional_expertise"
                                class="form-control bg-white-input" placeholder="e.g. React, Node.js, UI/UX">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Last Employer</label>
                            <input type="text" id="add_last_employer" name="last_employer"
                                class="form-control bg-white-input" placeholder="Previous Company Name">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Last Job Title</label>
                            <input type="text" id="add_last_designation" name="last_designation"
                                class="form-control bg-white-input" placeholder="e.g. Senior Developer">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Experience From Date</label>
                            <input type="date" name="experience_from" class="form-control bg-white-input">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Experience To Date</label>
                            <input type="date" name="experience_to" class="form-control bg-white-input">
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
                                <input type="file" id="resume_upload" name="resume" class="hidden-file-input"
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
                                <input type="file" id="id_upload" name="id_card" class="hidden-file-input" required
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
                                <input type="file" id="other_upload" name="other_documents[]" class="hidden-file-input"
                                    multiple onchange="handleFileSelect(this, 'other_wrapper', 'other_filename')">
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
                <input type="hidden" id="edit_id_hidden" name="employee_id_hidden">
                <!-- Step 1: Personal Details -->
                <div class="step-pane active">
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="admin-form-label">First Name *</label>
                            <input type="text" id="edit_first_name" name="first_name"
                                class="form-control bg-white-input" value="Sophia" required>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Middle Name</label>
                            <input type="text" id="edit_middle_name" name="middle_name"
                                class="form-control bg-white-input">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Last Name *</label>
                            <input type="text" id="edit_last_name" name="last_name" class="form-control bg-white-input"
                                value="Reynolds" required>
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Gender *</label>
                            <select id="edit_gender" name="gender" class="form-control bg-white-input" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Date of Birth</label>
                            <input type="date" id="edit_dob" name="dob" class="form-control bg-white-input"
                                value="1995-06-15">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Phone *</label>
                            <input type="tel" id="edit_phone" name="phone" class="form-control bg-white-input"
                                placeholder="03XXXXXXXXX" maxlength="12" required
                                title="Please enter exactly 11 digits">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">ID card Number</label>
                            <input type="text" id="edit_cnic" name="cnic_number" class="form-control bg-white-input"
                                placeholder="00000-0000000-0" maxlength="15">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Address</label>
                        <textarea id="edit_address" name="address" class="form-control bg-white-input"
                            rows="2">422 Maple Drive, Austin, TX</textarea>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Emergency Contact *</label>
                            <input type="tel" id="edit_emergency_phone" name="emergency_contact"
                                class="form-control bg-white-input" placeholder="03XXXXXXXXX" maxlength="12" required
                                title="Please enter exactly 11 digits">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Emergency Contact Relation</label>
                            <input type="text" id="edit_emergency_relation" name="emergency_relation"
                                class="form-control bg-white-input" value="Spouse">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Email *</label>
                            <input type="email" id="edit_email" name="email" class="form-control bg-white-input"
                                value="sophia.r@rtg.com" required>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Password *</label>
                            <div class="password-input-container">
                                <input type="password" name="password" class="form-control bg-white-input"
                                    id="edit_password" value="" autocomplete="new-password"
                                    placeholder="Leave blank to keep current">
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
                            <label class="admin-form-label">Shift Timing *</label>
                            <select id="edit_shift" name="shift_id" class="form-control bg-white-input" required>
                                <option value="">Loading Shifts...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Job Title</label>
                            <input type="text" id="edit_job_title" name="job_title" class="form-control bg-white-input"
                                value="UX Designer">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Department *</label>
                            <select id="edit_dept" name="department_id" class="form-control bg-white-input" required>
                                <option value="">Loading Departments...</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="admin-form-label">Job Type</label>
                            <select id="edit_job_type" name="job_type" class="form-control bg-white-input">
                                <option value="">Select Job Type</option>
                                <option value="Permanent">Permanent</option>
                                <option value="Probation">Probation</option>
                                <option value="Internship">Internship</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Salary</label>
                            <input type="number" id="edit_salary" name="salary" class="form-control bg-white-input"
                                value="85000">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Joining Date</label>
                            <input type="date" id="edit_joining_date" name="joining_date"
                                class="form-control bg-white-input" value="2022-03-01">
                        </div>
                    </div>
                    <div class="p-15 rounded-12 mb-20 border">
                        <h4 class="font-13 font-600 mb-15 text-primary-color flex-center gap-8"><i
                                data-lucide="building" size="16"></i> Banking Information</h4>
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Bank Name</label>
                                <select id="edit_bank_name" name="bank_name" class="form-control bg-white-input">
                                    <option value="">Select Bank</option>
                                    <option value="HBL">HBL</option>
                                    <option value="ALHabib">AL Habib</option>
                                    <option value="MCB">MCB</option>
                                    <option value="UBL">UBL</option>
                                    <option value="Meezan">Meezan</option>
                                    <option value="Allied">Allied</option>
                                    <option value="Bank Alfalah">Bank Alfalah</option>
                                    <option value="Askari">Askari</option>
                                    <option value="Faysal">Faysal</option>
                                    <option value="Habib Metro">Habib Metro</option>
                                    <option value="Soneri">Soneri</option>
                                    <option value="JS Bank">JS Bank</option>
                                    <option value="Bank Islami">Bank Islami</option>
                                    <option value="Standard Chartered">Standard Chartered</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Account Type</label>
                                <select id="edit_account_type" name="account_type" class="form-control bg-white-input">
                                    <option value="IBN">IBN</option>
                                    <option value="IBFT">IBFT</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Account Title</label>
                                <input type="text" id="edit_account_title" name="account_title"
                                    class="form-control bg-white-input" value="Sophia Reynolds">
                            </div>
                        </div>
                        <div class="form-grid-2 mt-10">
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Account Number</label>
                                <input type="text" id="edit_account_number" name="account_number"
                                    class="form-control bg-white-input" value="SCB-4810293021">
                            </div>
                            <div class="form-group">
                                <label class="font-11 font-600 text-light mb-1 block uppercase">Bank Branch</label>
                                <input type="text" id="edit_branch_info" name="branch_info"
                                    class="form-control bg-white-input" value="Main Street Branch">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Education & Docs -->
                <div class="step-pane">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Qualification</label>
                            <input type="text" id="edit_qualification" name="qualification"
                                class="form-control bg-white-input" value="Master's in Graphic Design">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Degree / Certification</label>
                            <input type="text" id="edit_degree" name="degree_certification"
                                class="form-control bg-white-input" value="Google UX Design Professional Cert">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">College / University</label>
                            <input type="text" id="edit_college" name="college_university"
                                class="form-control bg-white-input" value="Texas State University">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Professional Expertise</label>
                            <input type="text" id="edit_expertise" name="professional_expertise"
                                class="form-control bg-white-input" value="Figma, Adobe XD, User Research">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Last Employer</label>
                            <input type="text" id="edit_last_employer" name="last_employer"
                                class="form-control bg-white-input" value="Creative Solutions Inc.">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Last Job Title</label>
                            <input type="text" id="edit_last_designation" name="last_designation"
                                class="form-control bg-white-input" value="Junior Designer">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label">Experience From Date</label>
                            <input type="date" id="edit_experience_from" name="experience_from"
                                class="form-control bg-white-input" value="2020-01-01">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Experience To Date</label>
                            <input type="date" id="edit_experience_to" name="experience_to"
                                class="form-control bg-white-input" value="2022-02-15">
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
                                <input type="file" id="edit_resume_upload" name="resume" class="hidden-file-input"
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
                                <input type="file" id="edit_id_upload" name="id_card" class="hidden-file-input"
                                    onchange="handleFileSelect(this, 'edit_id_wrapper', 'edit_id_filename')">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-11 font-600 text-light mb-1 block uppercase">Other Documents</label>
                            <div class="custom-file-upload">
                                <label for="edit_other_upload" class="file-upload-wrapper" id="edit_other_wrapper">
                                    <i data-lucide="files" size="20" id="edit_other_icon"></i>
                                    <span class="file-upload-label" id="edit_other_filename">Choose Files</span>
                                    <span class="file-upload-info" id="edit_other_info">Certificates, etc.</span>
                                </label>
                                <input type="file" id="edit_other_upload" name="other_documents[]"
                                    class="hidden-file-input" multiple
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