<?php
$page_title = "New Joining / Onboarding";
$page_subtitle = "Review and manage upcoming team members and new joiners.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<?php
// Onboarding Controller Logic
?>

<!-- Page Action Area -->
<div class="page-action-area">
    <div class="filter-item w-full-mobile" style="flex:1;max-width:420px;margin-right:auto;">
        <label class="admin-form-label font-12">Search</label>
        <div class="search-box w-full">
            <i data-lucide="search" size="16"></i>
            <input type="text" id="njSearch" class="form-control" placeholder="Search new joinings...">
        </div>
    </div>
    <div class="header-actions">
        <button class="btn-primary" type="button" id="njRefreshBtn">
            <i data-lucide="refresh-cw"></i>
            <span>Refresh List</span>
        </button>
    </div>
</div>

<div class="candidates-grid" id="pendingOnboardingContainer">
    <!-- Loading State -->
    <div class="col-span-full py-50 text-center">
        <i data-lucide="loader-2" class="spin text-primary-color mb-15" size="40"></i>
        <p class="text-light">Scanning for new joinings...</p>
    </div>
</div>


<!-- Candidate Modal (Dynamic API Populate) -->
<div class="modal-overlay" id="candidateEmployeeModal">
    <div class="modal-content premium wide-md">
        <div class="modal-header">
            <div>
                <h3>Administrative Onboarding</h3>
                <p class="font-12 text-light mt-1">New joining details and administrative access</p>
            </div>
            <button type="button" class="icon-btn js-modal-close" aria-label="Close"><i data-lucide="x"></i></button>
        </div>

        <form id="hireCandidateForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="cand_id_hidden" name="employee_id_hidden" value="">
            <div class="modal-body p-30">
                <div class="nj-section-head">
                    <div class="nj-section-icon" aria-hidden="true">
                        <i data-lucide="user-round" width="22" height="22"></i>
                    </div>
                    <h2>Personal Details</h2>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="admin-form-label">First Name *</label>
                        <input type="text" id="cand_first_name" name="first_name" class="form-control bg-white-input"
                            value="" placeholder="Enter first name" required />
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Middle Name</label>
                        <input type="text" id="cand_middle_name" name="middle_name" class="form-control bg-white-input"
                            value="" placeholder="Enter middle name" />
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Last Name *</label>
                        <input type="text" id="cand_last_name" name="last_name" class="form-control bg-white-input"
                            value="" placeholder="Enter last name" required />
                    </div>
                </div>

                <div class="form-grid-2 mt-10">
                    <div class="form-group">
                        <label class="admin-form-label">Gender *</label>
                        <select id="cand_gender" name="gender" class="form-control bg-white-input" required>
                            <option value="" selected disabled>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Date of Birth *</label>
                        <input type="date" id="cand_dob" name="dob" class="form-control bg-white-input" value="" required />
                    </div>
                </div>

                <div class="form-grid-2 mt-10">
                    <div class="form-group">
                        <label class="admin-form-label">Phone *</label>
                        <input type="tel" id="cand_phone" name="phone" class="form-control bg-white-input" value=""
                            placeholder="03XXXXXXXXX" inputmode="numeric" maxlength="12" required
                            title="Please enter exactly 11 digits" />
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">ID Card Number *</label>
                        <input type="text" id="cand_cnic" name="cnic_number" class="form-control bg-white-input"
                            value="" placeholder="00000-0000000-0" maxlength="15" inputmode="numeric" required />
                    </div>
                </div>

                <div class="form-group mt-10">
                    <label class="admin-form-label">Address *</label>
                    <textarea id="cand_address" name="address" class="form-control bg-white-input" rows="2"
                        placeholder="Enter full address" required></textarea>
                </div>

                <div class="form-grid-2 mt-10">
                    <div class="form-group">
                        <label class="admin-form-label">Emergency Contact *</label>
                        <input type="tel" id="cand_emergency_phone" name="emergency_contact"
                            class="form-control bg-white-input" value="" placeholder="03XXXXXXXXX" inputmode="numeric"
                            maxlength="12" required title="Please enter exactly 11 digits" />
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Emergency Contact Relation *</label>
                        <input type="text" id="cand_emergency_relation" name="emergency_relation"
                            class="form-control bg-white-input" value="" placeholder="e.g. Father, Spouse" required />
                    </div>
                </div>

                <div class="form-group mt-10">
                    <label class="admin-form-label">Email *</label>
                    <input type="email" id="cand_email" name="email" class="form-control bg-white-input" value=""
                        placeholder="email@example.com" required />
                    <div id="cand_email_feedback" class="font-11 mt-4" style="min-height: 14px; font-weight: 500;">
                    </div>
                </div>

                <div class="nj-section-head">
                    <div class="nj-section-icon" aria-hidden="true">
                        <i data-lucide="landmark" width="22" height="22"></i>
                    </div>
                    <h2>Job & Banking</h2>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="admin-form-label">Job Title *</label>
                        <input type="text" id="cand_job_title" name="job_title" class="form-control bg-white-input"
                            value="" placeholder="e.g. Software Engineer" required />
                    </div>

                    <div class="form-group">
                        <label class="admin-form-label">Bank Name *</label>
                        <select id="cand_bank_name" name="bank_name" class="form-control bg-white-input" required>
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
                        <label class="admin-form-label">Account Type *</label>
                        <select id="cand_account_type" name="account_type" class="form-control bg-white-input" required>
                            <option value="" selected disabled>Select Type</option>
                            <option value="IBN">IBN</option>
                            <option value="IBFT">IBFT</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2 mt-10">
                    <div class="form-group">
                        <label class="admin-form-label">Account Title *</label>
                        <input type="text" id="cand_account_title" name="account_title"
                            class="form-control bg-white-input" value="" placeholder="Account title" required />
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Account Number *</label>
                        <input type="text" id="cand_account_number" name="account_number"
                            class="form-control bg-white-input" value="" placeholder="0000 0000 0000 0000" required />
                    </div>
                </div>

                <div class="form-group mt-10">
                    <label class="admin-form-label">Bank Branch *</label>
                    <input type="text" id="cand_branch_info" name="branch_info" class="form-control bg-white-input"
                        value="" placeholder="Branch Code / City" required />
                </div>

                <div class="nj-section-head">
                    <div class="nj-section-icon" aria-hidden="true">
                        <i data-lucide="graduation-cap" width="22" height="22"></i>
                    </div>
                    <h2>Education & Experience</h2>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="admin-form-label">Qualification *</label>
                        <input type="text" id="cand_qualification" name="qualification"
                            class="form-control bg-white-input" value="" placeholder="e.g. BS Computer Science" required />
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Degree / Certification *</label>
                        <input type="text" id="cand_degree" name="degree_certification"
                            class="form-control bg-white-input" value="" placeholder="e.g. Bachelor's / Diploma" required />
                    </div>
                </div>

                <div class="form-grid-2 mt-10">
                    <div class="form-group">
                        <label class="admin-form-label">College / University *</label>
                        <input type="text" id="cand_college" name="college_university"
                            class="form-control bg-white-input" value="" placeholder="e.g. University of Karachi" required />
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Professional Expertise *</label>
                        <input type="text" id="cand_expertise" name="professional_expertise"
                            class="form-control bg-white-input" value="" placeholder="e.g. Node.js, React, APIs" required />
                    </div>
                </div>

                <div class="form-grid-2 mt-10">
                    <div class="form-group">
                        <label class="admin-form-label">Last Employer *</label>
                        <input type="text" id="cand_last_employer" name="last_employer"
                            class="form-control bg-white-input" value="" placeholder="Company name" required />
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Last Job Title *</label>
                        <input type="text" id="cand_last_designation" name="last_designation"
                            class="form-control bg-white-input" value="" placeholder="Job title" required />
                    </div>
                </div>

                <div class="form-grid-2 mt-10">
                    <div class="form-group">
                        <label class="admin-form-label">Experience From Date</label>
                        <input type="date" id="cand_exp_from" name="experience_from" class="form-control bg-white-input"
                            value="" />
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Experience To Date</label>
                        <input type="date" id="cand_exp_to" name="experience_to" class="form-control bg-white-input"
                            value="" />
                    </div>
                </div>

                <div class="nj-section-head">
                    <div class="nj-section-icon" aria-hidden="true">
                        <i data-lucide="paperclip" width="22" height="22"></i>
                    </div>
                    <h2>Document Attachments</h2>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="admin-form-label">Resume Attachment *</label>
                        <div class="custom-file-upload">
                            <label for="cand_resume_upload" class="file-upload-wrapper" id="cand_resume_wrapper">
                                <i data-lucide="file-text" size="20"></i>
                                <span class="file-upload-label" id="cand_resume_filename">Upload Resume</span>
                                <span class="file-upload-info">PDF, DOCX up to 10MB</span>
                            </label>
                            <input type="file" id="cand_resume_upload" name="resume" class="hidden-file-input"
                                accept=".pdf,.doc,.docx"
                                onchange="handleFileSelect(this, 'cand_resume_wrapper', 'cand_resume_filename')" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">ID Card Attachment *</label>
                        <div class="custom-file-upload">
                            <label for="cand_id_upload" class="file-upload-wrapper" id="cand_id_wrapper">
                                <i data-lucide="image" size="20"></i>
                                <span class="file-upload-label" id="cand_id_filename">Upload ID Card</span>
                                <span class="file-upload-info">PNG, JPG or PDF</span>
                            </label>
                            <input type="file" id="cand_id_upload" name="id_card" class="hidden-file-input"
                                accept=".pdf,.png,.jpg,.jpeg"
                                onchange="handleFileSelect(this, 'cand_id_wrapper', 'cand_id_filename')" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Other Documents</label>
                        <div class="custom-file-upload">
                            <label for="cand_other_upload" class="file-upload-wrapper" id="cand_other_wrapper">
                                <i data-lucide="files" size="20"></i>
                                <span class="file-upload-label" id="cand_other_filename">Upload Other Docs</span>
                                <span class="file-upload-info">Additional certificates</span>
                            </label>
                            <input type="file" id="cand_other_upload" name="other_documents[]" class="hidden-file-input"
                                multiple
                                onchange="handleFileSelect(this, 'cand_other_wrapper', 'cand_other_filename')" />
                        </div>
                    </div>
                </div>

                <div class="p-15 rounded-12 mt-20 mb-10 border onboard-admin-block">
                    <div class="nj-section-head">
                        <div class="nj-section-icon" aria-hidden="true">
                            <i data-lucide="shield-check" width="22" height="22"></i>
                        </div>
                        <h2>Administrative Access</h2>
                    </div>

                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="admin-form-label">Department *</label>
                            <select id="cand_dept" name="department_id" class="form-control bg-white-input" required>
                                <option value="">Select Department</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Job Type *</label>
                            <select id="cand_job_type" name="job_type" class="form-control bg-white-input" required>
                                <option value="" selected disabled>Select Job Type</option>
                                <option value="Permanent">Permanent</option>
                                <option value="Probation">Probation</option>
                                <option value="Internship">Internship</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Salary *</label>
                            <input type="number" id="cand_salary" name="salary" class="form-control bg-white-input"
                                value="" placeholder="0.00" required />
                        </div>
                    </div>

                    <div class="form-grid-3 mt-10">
                        <div class="form-group">
                            <label class="admin-form-label">Joining Date *</label>
                            <input type="date" id="cand_joining_date" name="joining_date"
                                class="form-control bg-white-input" value="" required />
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Shift Timing *</label>
                            <select id="cand_shift" name="shift_id" class="form-control bg-white-input" required>
                                <option value="" selected disabled>Select Shift Timing</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="admin-form-label">Login Password *</label>
                            <div style="position: relative;">
                                <input type="password" id="candidate_admin_password" name="candidate_admin_password"
                                    class="form-control bg-white-input" placeholder="Create login password" required
                                    style="padding-right: 40px;" />
                                <button type="button" id="togglePasswordBtn"
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; padding: 0; display: flex; align-items: center;">
                                    <i data-lucide="eye" size="18"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer flex-between p-30 border-top-0">
                <button type="button" class="btn-primary no-bg border text-light js-modal-close">
                    <i data-lucide="x" size="18"></i> <span>Close</span>
                </button>
                <div class="flex-center gap-12">
                    <button type="submit" class="btn-primary px-30" id="hireSubmitBtn" data-hr-perm-action="create">
                        <i data-lucide="check" size="18"></i> <span>Submit & Hire</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>



















<script src="assets/js/new-joining.js"></script>
<?php include 'includes/footer.php'; ?>