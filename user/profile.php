<?php
$page_title = 'Employee Profile';
$page_subtitle = 'Your personal, job, and contact details in one place.';
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="content-wrapper">
    <header class="content-header p-30 pb-10">
        <div class="flex-between align-start w-full">
            <div class="greeting-area">
                <div class="flex-center gap-12 mb-8">
                    <a href="index.php" class="action-btn no-bg border" title="Back to Dashboard">
                        <i data-lucide="arrow-left" size="18"></i>
                    </a>
                    <h1 class="font-24 font-700 ls-05">Employee Profile</h1>
                </div>
                <p class="text-light font-14"><?= isset($page_subtitle) ? htmlspecialchars($page_subtitle) : '' ?></p>
            </div>
            <div class="header-actions flex-center gap-12">
                <button type="button" class="btn-primary btn-primary--profile-edit px-20" id="openEditProfileModalBtn">
                    <span>Edit Profile</span>
                    <i data-lucide="edit-2" width="16" height="16" stroke-width="2" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </header>

    <div>
        <div class="profile-grid">
            <!-- Left Column: Primary Identity -->
            <div class="profile-aside">
                <div class="premium-card profile-identity-card flex-column flex-center text-center mb-24">
                    <div class="profile-avatar-wrapper mb-20">
                        <img id="profileAvatarImg" src="../images/profile-image/default-avatar.svg"
                            class="profile-avatar-xl shadow-lg" alt="Profile photo"
                            data-default-avatar="../images/profile-image/default-avatar.svg"
                            onerror="this.src='../images/profile-image/default-avatar.svg'">
                        <span class="status-indicator-lg active border-4 profile-avatar-status" title="Active"></span>
                        <label for="profileAvatarInput" class="profile-avatar-camera" title="Change profile photo">
                            <i data-lucide="camera" width="18" height="18" aria-hidden="true"></i>
                            <span class="sr-only">Change profile photo</span>
                        </label>
                        <input type="file" id="profileAvatarInput" class="hidden-file-input"
                            accept="image/jpeg,image/png,image/webp,image/gif" tabindex="-1">
                    </div>
                    <h2 class="font-22 font-700 mb-4" id="profileFullName">-</h2>
                    <p class="text-primary-color font-600 mb-12" id="profileJobTitle">-</p>
                    <div class="badge badge-success px-15 py-6">Active Employee</div>
                </div>

                <div class="premium-card p-24 mb-24">
                    <h3 class="font-15 font-700 mb-20 flex-center gap-10">
                        <i data-lucide="contact" size="18" class="text-primary-color"></i>
                        Primary Contact
                    </h3>
                    <div class="mb-20">
                        <label class="admin-form-label">Email</label>
                        <span class="font-14 font-500 block" id="pf_email">-</span>
                    </div>
                    <div class="mb-20">
                        <label class="admin-form-label">Phone</label>
                        <span class="font-14 font-500 block" id="pf_phone">-</span>
                    </div>
                </div>

                <div class="premium-card p-24 mb-24">
                    <h3 class="font-15 font-700 mb-20 flex-center gap-10">
                        <i data-lucide="shield-alert" size="18" class="text-primary-color"></i>
                        Emergency Contact
                    </h3>
                    <div class="mb-20">
                        <label class="admin-form-label">Emergency Contact</label>
                        <span class="font-14 font-500 block" id="pf_emergencyPhone">-</span>
                    </div>
                    <div>
                        <label class="admin-form-label">Emergency Contact Relation</label>
                        <span class="font-14 font-500 block" id="pf_emergencyRelation">-</span>
                    </div>
                </div>

                <div class="premium-card p-24 mb-24" id="employeeLeaveSummaryCard">
                    <h3 class="font-15 font-700 mb-20 flex-center gap-10">
                        <i data-lucide="calendar-check" size="18" class="text-primary-color"></i>
                        Leave Summary
                    </h3>
                    <div class="leave-summary-list">
                        <div class="leave-summary-row">
                            <span class="leave-summary-name">Sick Leave</span>
                            <span class="leave-summary-meta"><span id="empLeaveSickUsed">—</span> used · <span
                                    id="empLeaveSickRemaining">—</span> remaining</span>
                        </div>
                        <div class="leave-summary-row">
                            <span class="leave-summary-name">Casual Leave</span>
                            <span class="leave-summary-meta"><span id="empLeaveCasualUsed">—</span> used · <span
                                    id="empLeaveCasualRemaining">—</span> remaining</span>
                        </div>
                        <div class="leave-summary-row">
                            <span class="leave-summary-name">Annual Leave</span>
                            <span class="leave-summary-meta"><span id="empLeaveAnnualUsed">—</span> used · <span
                                    id="empLeaveAnnualRemaining">—</span> remaining</span>
                        </div>
                    </div>
                </div>

                <div class="premium-card p-24 mt-24">
                    <h3 class="font-15 font-700 mb-20 flex-center gap-10">
                        <i data-lucide="trending-up" size="18" class="text-primary-color"></i>
                        Salary Increment History
                    </h3>

                    <div class="timeline-list" id="salaryTimeline">
                        <!-- Dynamic items will be injected here -->
                    </div>

                    <p class="font-11 text-light m-0">
                        Latest salary: <span class="font-600 text-dark" id="pf_latestSalary">$0</span> (synced with
                        payroll data).
                    </p>
                </div>
            </div>

            <!-- Right Column: All Fields from Modal -->
            <div class="profile-main">
                <!-- Step 1: Personal Details -->
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
                                <span class="font-14 font-500" id="pf_firstName">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Middle Name</label>
                                <span class="font-14 font-500" id="pf_middleName">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Last Name</label>
                                <span class="font-14 font-500" id="pf_lastName">-</span>
                            </div>
                        </div>
                        <div class="form-grid-3 mb-30">
                            <div>
                                <label class="admin-form-label">Gender</label>
                                <span class="font-14 font-500" id="pf_gender">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Date of Birth</label>
                                <span class="font-14 font-500" id="pf_dob">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">ID Card Number</label>
                                <span class="font-14 font-500" id="pf_idCard">-</span>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="admin-form-label">Address</label>
                            <span class="font-14 font-500" id="pf_address">-</span>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Job & Banking -->
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
                                <label class="admin-form-label">Shift Timing</label>
                                <span class="font-14 font-500" id="pf_shift">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Job Title</label>
                                <span class="font-14 font-500" id="pf_jobTitleDisplay">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Department</label>
                                <span class="font-14 font-500" id="pf_dept">-</span>
                            </div>
                        </div>
                        <div class="form-grid-3 mb-10">
                            <div>
                                <label class="admin-form-label">Job Type</label>
                                <span class="font-14 font-500" id="pf_jobType">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Salary</label>
                                <span class="font-14 font-500 ls-05" id="pf_salary">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Joining Date</label>
                                <span class="font-14 font-500" id="pf_joiningDate">-</span>
                            </div>
                        </div>

                        <div class="border-top pt-30 mt-30">
                            <h3 class="font-16 font-700 flex-center gap-10 mb-24">
                                <i data-lucide="building" size="20" class="text-primary-color"></i>
                                Bank Information
                            </h3>

                            <div class="form-grid-3 mb-24">
                                <div>
                                    <label class="admin-form-label">Bank Name</label>
                                    <span class="font-13 font-600 block" id="pf_bankName">-</span>
                                </div>
                                <div>
                                    <label class="admin-form-label">Account Type</label>
                                    <span class="font-13 font-500 block" id="pf_accountType">-</span>
                                </div>
                                <div>
                                    <label class="admin-form-label">Account Title</label>
                                    <span class="font-13 font-500 block" id="pf_accountTitle">-</span>
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div>
                                    <label class="admin-form-label">Account Number</label>
                                    <span class="font-13 font-500 block ls-05" id="pf_accountNumber">-</span>
                                </div>
                                <div>
                                    <label class="admin-form-label">Bank Branch</label>
                                    <span class="font-13 font-500 block" id="pf_bankBranch">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Education & Experience -->
                <div class="premium-card mb-24">
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
                                <span class="font-14 font-500" id="pf_qualification">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Degree / Certification</label>
                                <span class="font-14 font-500" id="pf_degreeCert">-</span>
                            </div>
                        </div>
                        <div class="form-grid-2 mb-30">
                            <div>
                                <label class="admin-form-label">College / University</label>
                                <span class="font-14 font-500" id="pf_college">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Professional Expertise</label>
                                <span class="font-14 font-500" id="pf_expertise">-</span>
                            </div>
                        </div>
                        <div class="form-grid-2 mb-30 border-top pt-30 mt-10">
                            <div>
                                <label class="admin-form-label">Last Employer</label>
                                <span class="font-14 font-500" id="pf_lastEmployer">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Last Job Title</label>
                                <span class="font-14 font-500" id="pf_prevJobTitle">-</span>
                            </div>
                        </div>
                        <div class="form-grid-2 mb-40">
                            <div>
                                <label class="admin-form-label">Experience From Date</label>
                                <span class="font-14 font-500" id="pf_expFrom">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Experience To Date</label>
                                <span class="font-14 font-500" id="pf_expTo">-</span>
                            </div>
                        </div>

                        <div class="form-grid-3 gap-24">
                            <a href="#" target="_blank"
                                class="doc-card border rounded-16 p-20 hover-bg-light transition block no-underline"
                                id="pf_resumeLink">
                                <label class="admin-form-label cursor-pointer">Resume Attachment</label>
                                <div class="flex-center gap-12">
                                    <div class="icon-square-40 bg-primary-soft text-primary-color">
                                        <i data-lucide="file-text" size="20"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <span class="font-13 font-600 truncate block" id="pf_resumeLabel">-</span>
                                    </div>
                                </div>
                            </a>
                            <a href="#" target="_blank"
                                class="doc-card border rounded-16 p-20 hover-bg-light transition block no-underline"
                                id="pf_idDocLink">
                                <label class="admin-form-label cursor-pointer">ID Card Attachment</label>
                                <div class="flex-center gap-12">
                                    <div class="icon-square-40 bg-success-soft text-success-color">
                                        <i data-lucide="image" size="20"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <span class="font-13 font-600 truncate block" id="pf_idDocLabel">-</span>
                                    </div>
                                </div>
                            </a>
                            <div id="pf_otherDocsContainer" class="form-grid-3 gap-24"
                                style="grid-column: span 3; display: contents;">
                                <!-- Dynamic cards for Other Documents will be injected here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Edit Profile Modal -->
<div class="modal-overlay" id="editProfileModal">
    <div class="modal-content premium wide-md modal-content--profile-edit">
        <div class="modal-header">
            <div>
                <h3 class="font-18 font-700 m-0">Edit profile</h3>
                <p class="font-12 text-light m-0">Update your personal, emergency, banking, and education details</p>
            </div>
            <button type="button" class="icon-btn js-modal-close" aria-label="Close"><i data-lucide="x"
                    size="20"></i></button>
        </div>
        <form id="editProfileForm" enctype="multipart/form-data">
            <div class="modal-body p-30">
                <!-- Same field order & grid pattern as admin Add Employee (employees.php step 1–3) -->
                <div class="profile-edit-section">
                    <div class="profile-edit-section__head">
                        <span class="icon-box-32 primary" aria-hidden="true"><i data-lucide="user" size="18"></i></span>
                        <h4 class="profile-edit-section__title">Personal Details</h4>
                    </div>
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_firstName">First Name *</label>
                            <input type="text" class="form-control bg-white-input" id="ep_firstName" name="firstName"
                                required maxlength="80">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_middleName">Middle Name</label>
                            <input type="text" class="form-control bg-white-input" id="ep_middleName" name="middleName"
                                maxlength="80" placeholder="-">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_lastName">Last Name *</label>
                            <input type="text" class="form-control bg-white-input" id="ep_lastName" name="lastName"
                                required maxlength="80">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_gender">Gender *</label>
                            <select class="form-control bg-white-input" id="ep_gender" name="gender" required>
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_dob">Date of Birth</label>
                            <input type="date" class="form-control bg-white-input" id="ep_dob" name="dob">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_phone">Phone</label>
                            <input type="text" class="form-control bg-white-input" id="ep_phone" name="phone"
                                maxlength="12" placeholder="0000-0000000">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_idCard">ID Card Number</label>
                            <input type="text" class="form-control bg-white-input" id="ep_idCard" name="idCard"
                                maxlength="15" placeholder="00000-0000000-0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label" for="ep_address">Address</label>
                        <textarea class="form-control bg-white-input" id="ep_address" name="address" rows="2"
                            maxlength="500"></textarea>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_email">Email *</label>
                            <input type="email" class="form-control bg-white-input" id="ep_email" name="email" required
                                maxlength="120" autocomplete="email" readonly>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_jobTitle">Job Title</label>
                            <input type="text" class="form-control bg-white-input" id="ep_jobTitle" name="jobTitle"
                                maxlength="80" readonly>
                        </div>
                    </div>
                </div>

                <div class="profile-edit-section">
                    <div class="profile-edit-section__head">
                        <span class="icon-box-32 primary" aria-hidden="true"><i data-lucide="shield-alert"
                                size="18"></i></span>
                        <h4 class="profile-edit-section__title">Emergency Contact</h4>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group mb-0">
                            <label class="admin-form-label" for="ep_emergencyPhone">Emergency Contact</label>
                            <input type="text" class="form-control bg-white-input" id="ep_emergencyPhone"
                                name="emergencyPhone" maxlength="12" placeholder="0000-0000000">
                        </div>
                        <div class="form-group mb-0">
                            <label class="admin-form-label" for="ep_emergencyRelation">Emergency Contact Relation</label>
                            <input type="text" class="form-control bg-white-input" id="ep_emergencyRelation"
                                name="emergencyRelation" maxlength="60">
                        </div>
                    </div>
                </div>

                <div class="profile-edit-section">
                    <div class="profile-edit-section__head">
                        <span class="icon-box-32 primary" aria-hidden="true"><i data-lucide="building-2"
                                size="18"></i></span>
                        <h4 class="profile-edit-section__title">Bank Information</h4>
                    </div>
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_bankName">Bank Name</label>
                            <select class="form-control bg-white-input" id="ep_bankName" name="bankName">
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
                            <label class="admin-form-label" for="ep_accountType">Account Type</label>
                            <select class="form-control bg-white-input" id="ep_accountType" name="accountType">
                                <option value="">Select Type</option>
                                <option value="IBN">IBN</option>
                                <option value="IBFT">IBFT</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_accountTitle">Account Title</label>
                            <input type="text" class="form-control bg-white-input" id="ep_accountTitle"
                                name="accountTitle" maxlength="120">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group mb-0">
                            <label class="admin-form-label" for="ep_accountNumber">Account Number</label>
                            <input type="text" class="form-control bg-white-input" id="ep_accountNumber"
                                name="accountNumber" maxlength="80">
                        </div>
                        <div class="form-group mb-0">
                            <label class="admin-form-label" for="ep_bankBranch">Bank Branch</label>
                            <input type="text" class="form-control bg-white-input" id="ep_bankBranch" name="bankBranch"
                                maxlength="120">
                        </div>
                    </div>
                </div>

                <div class="profile-edit-section mb-0">
                    <div class="profile-edit-section__head">
                        <span class="icon-box-32 primary" aria-hidden="true"><i data-lucide="graduation-cap"
                                size="18"></i></span>
                        <h4 class="profile-edit-section__title">Education &amp; Experience</h4>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_qualification">Qualification</label>
                            <input type="text" class="form-control bg-white-input" id="ep_qualification"
                                name="qualification" maxlength="120">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_degreeCert">Degree / Certification</label>
                            <input type="text" class="form-control bg-white-input" id="ep_degreeCert" name="degreeCert"
                                maxlength="200">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_college">College / University</label>
                            <input type="text" class="form-control bg-white-input" id="ep_college" name="college"
                                maxlength="120">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_expertise">Professional Expertise</label>
                            <input type="text" class="form-control bg-white-input" id="ep_expertise" name="expertise"
                                maxlength="200">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_lastEmployer">Last Employer</label>
                            <input type="text" class="form-control bg-white-input" id="ep_lastEmployer"
                                name="lastEmployer" maxlength="120">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_prevJobTitle">Last Job Title</label>
                            <input type="text" class="form-control bg-white-input" id="ep_prevJobTitle"
                                name="prevJobTitle" maxlength="120">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_expFrom">Experience From Date</label>
                            <input type="date" class="form-control bg-white-input" id="ep_expFrom" name="expFrom">
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label" for="ep_expTo">Experience To Date</label>
                            <input type="date" class="form-control bg-white-input" id="ep_expTo" name="expTo">
                        </div>
                    </div>
                    <div class="form-grid-3">
                        <div class="form-group mb-0">
                            <label class="admin-form-label">Resume Attachment</label>
                            <div class="custom-file-upload">
                                <label for="ep_resumeFile" class="file-upload-wrapper" id="ep_resume_wrapper">
                                    <i data-lucide="file-text" size="20"></i>
                                    <span class="file-upload-label">Choose resume</span>
                                    <span class="file-upload-info" id="ep_resume_filename">PDF, DOCX up to 5MB</span>
                                </label>
                                <input type="file" id="ep_resumeFile" name="resume_file" class="hidden-file-input"
                                    accept=".pdf,.doc,.docx,application/pdf"
                                    onchange="handleFileSelect(this, 'ep_resume_wrapper', 'ep_resume_filename')" />
                            </div>
                            <input type="hidden" id="ep_resumeLabel" name="resumeLabel" value="">
                        </div>
                        <div class="form-group mb-0">
                            <label class="admin-form-label">ID Card Attachment</label>
                            <div class="custom-file-upload">
                                <label for="ep_idFile" class="file-upload-wrapper" id="ep_id_wrapper">
                                    <i data-lucide="image" size="20"></i>
                                    <span class="file-upload-label">Upload ID Card</span>
                                    <span class="file-upload-info" id="ep_id_filename">PNG, JPG or PDF</span>
                                </label>
                                <input type="file" id="ep_idFile" name="id_file" class="hidden-file-input"
                                    accept=".pdf,.png,.jpg,.jpeg,image/*,application/pdf"
                                    onchange="handleFileSelect(this, 'ep_id_wrapper', 'ep_id_filename')" />
                            </div>
                            <input type="hidden" id="ep_idDocLabel" name="idDocLabel" value="">
                        </div>
                        <div class="form-group mb-0">
                            <label class="admin-form-label">Other Documents</label>
                            <div class="custom-file-upload">
                                <label for="ep_otherFile" class="file-upload-wrapper" id="ep_other_wrapper">
                                    <i data-lucide="files" size="20"></i>
                                    <span class="file-upload-label">Choose files</span>
                                    <span class="file-upload-info" id="ep_other_filename">Certificates, etc.</span>
                                </label>
                                <input type="file" id="ep_otherFile" name="other_files" class="hidden-file-input"
                                    multiple
                                    onchange="handleFileSelect(this, 'ep_other_wrapper', 'ep_other_filename')" />
                            </div>
                            <input type="hidden" id="ep_otherDocLabel" name="otherDocLabel" value="">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-between flex-wrap gap-12">
                <button type="button" class="btn-ghost js-modal-close">Cancel</button>
                <button type="submit" class="btn-primary" id="editProfileSaveBtn">
                    <span>Save changes</span>
                    <i data-lucide="check" width="16" height="16" aria-hidden="true"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/profile.js"></script>
<?php include 'includes/footer.php'; ?>