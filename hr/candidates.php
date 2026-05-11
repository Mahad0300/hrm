<?php 
$page_title = "New Joining / Onboarding";
$page_subtitle = "Review and manage upcoming team members and new joiners.";
include 'includes/header.php'; 
?>
<?php include 'includes/sidebar.php'; ?>

<?php
// Static candidate data for the modal (no extra JS, only PHP prefill)
$openCandidate = strtolower(trim($_GET['openCandidate'] ?? ''));
$candidateMap = [
    'alex' => [
        'name' => 'Alex Johnson',
        'role' => 'Senior Backend Developer',
        'department' => 'Engineering',
        'email' => 'alex.j@example.com',
        'phone' => '+92 321 4567890',
    ],
    'sarah' => [
        'name' => 'Sarah Ahmed',
        'role' => 'HR Specialist',
        'department' => 'HR',
        'email' => 'sarah.a@example.com',
        'phone' => '+92 333 1234567',
    ],
    'usman' => [
        'name' => 'Usman Khan',
        'role' => 'Technical Support Lead',
        'department' => 'Engineering',
        'email' => 'usman.k@example.com',
        'phone' => '+92 300 9876543',
    ],
];

$selectedCandidate = $candidateMap[$openCandidate] ?? null;
$modalActiveClass = $selectedCandidate ? 'active' : '';

function split_name($fullName)
{
    $parts = preg_split('/\s+/', trim($fullName));
    $first = $parts[0] ?? '';
    $last = array_pop($parts);
    $middle = count($parts) ? implode(' ', $parts) : '';
    return [$first, $middle, $last];
}

[$cand_first, $cand_middle, $cand_last] = $selectedCandidate ? split_name($selectedCandidate['name']) : ['', '', ''];
$cand_email = $selectedCandidate['email'] ?? '';
$cand_phone = $selectedCandidate['phone'] ?? '';
$cand_role = $selectedCandidate['role'] ?? '';
$cand_dept = $selectedCandidate['department'] ?? '';
?>

<!-- Page Action Area -->
<div class="page-action-area">
    <div class="search-bar-wrap w-full-mobile">
        <i data-lucide="search" size="18"></i>
        <input type="text" class="search-input" placeholder="Search new joinings...">
    </div>
    <div class="header-actions">
        <button class="btn-primary" type="button">
            <i data-lucide="refresh-cw"></i>
            <span>Refresh List</span>
        </button>
    </div>
</div>

<div class="candidates-grid">
    <!-- Candidate Card 1 -->
    <div class="announcement-card corporate">
        <div class="card-shape shape-1"></div>
        <div class="card-shape shape-2"></div>
        <div class="announcement-content">
            <div class="flex-between mb-15">
                <span class="card-category cat-it">Engineering</span>
                <span class="badge badge-warning">Pending</span>
            </div>
            <h3 class="mb-2">Alex Johnson</h3>
            <p class="font-12 text-primary font-600 mb-15">Senior Backend Developer</p>
            
            <div class="candidate-info-rows">
                <div class="flex-center gap-10 mb-6 font-13 text-light">
                    <i data-lucide="mail" size="14"></i>
                    <span>alex.j@example.com</span>
                </div>
                <div class="flex-center gap-10 mb-0 font-13 text-light">
                    <i data-lucide="phone" size="14"></i>
                    <span>+92 321 4567890</span>
                </div>
            </div>
        </div>
        <div class="announcement-footer">
            <div class="flex-center gap-10">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150"
                    class="icon-box-sm">
                <span class="font-13 font-500 text-dark">Alex Johnson</span>
            </div>
            <div class="flex-center gap-10">
                <a class="action-btn action-btn-view" href="candidates.php?openCandidate=alex" title="View Details">
                    <i data-lucide="eye" size="14"></i>
                </a>
                <button class="action-btn danger" type="button" title="Delete New Joining">
                    <i data-lucide="trash-2" size="14"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Candidate Card 2 -->
    <div class="announcement-card hr-dept">
        <div class="card-shape shape-1"></div>
        <div class="card-shape shape-2"></div>
        <div class="announcement-content">
            <div class="flex-between mb-15">
                <span class="card-category cat-it">Human Resources</span>
                <span class="badge badge-warning">Pending</span>
            </div>
            <h3 class="mb-2">Sarah Ahmed</h3>
            <p class="font-12 text-primary font-600 mb-15">HR Specialist</p>
            
            <div class="candidate-info-rows">
                <div class="flex-center gap-10 mb-6 font-13 text-light">
                    <i data-lucide="mail" size="14"></i>
                    <span>sarah.a@example.com</span>
                </div>
                <div class="flex-center gap-10 mb-0 font-13 text-light">
                    <i data-lucide="phone" size="14"></i>
                    <span>+92 333 1234567</span>
                </div>
            </div>
        </div>
        <div class="announcement-footer">
            <div class="flex-center gap-10">
                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                    class="icon-box-sm">
                <span class="font-13 font-500 text-dark">Sarah Ahmed</span>
            </div>
            <div class="flex-center gap-10">
                <a class="action-btn action-btn-view" href="candidates.php?openCandidate=sarah" title="View Details">
                    <i data-lucide="eye" size="14"></i>
                </a>
                <button class="action-btn danger" type="button" title="Delete New Joining">
                    <i data-lucide="trash-2" size="14"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Candidate Card 3 -->
    <div class="announcement-card it-dept">
        <div class="card-shape shape-1"></div>
        <div class="card-shape shape-2"></div>
        <div class="announcement-content">
            <div class="flex-between mb-15">
                <span class="card-category cat-it">IT Support</span>
                <span class="badge badge-warning">Pending</span>
            </div>
            <h3 class="mb-2">Usman Khan</h3>
            <p class="font-12 text-primary font-600 mb-15">Technical Support Lead</p>
            
            <div class="candidate-info-rows">
                <div class="flex-center gap-10 mb-6 font-13 text-light">
                    <i data-lucide="mail" size="14"></i>
                    <span>usman.k@example.com</span>
                </div>
                <div class="flex-center gap-10 mb-0 font-13 text-light">
                    <i data-lucide="phone" size="14"></i>
                    <span>+92 300 9876543</span>
                </div>
            </div>
        </div>
        <div class="announcement-footer">
            <div class="flex-center gap-10">
                <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&q=80&w=150"
                    class="icon-box-sm">
                <span class="font-13 font-500 text-dark">Usman Khan</span>
            </div>
            <div class="flex-center gap-10">
                <a class="action-btn action-btn-view" href="candidates.php?openCandidate=usman" title="View Details">
                    <i data-lucide="eye" size="14"></i>
                </a>
                <button class="action-btn danger" type="button" title="Delete New Joining">
                    <i data-lucide="trash-2" size="14"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Candidate Modal (static, PHP prefill) -->
<div class="modal-overlay<?= $modalActiveClass ? ' active' : '' ?>" id="candidateEmployeeModal">
    <div class="modal-content premium wide-md">
        <div class="modal-header">
            <div>
                <h3>Administrative Onboarding</h3>
                <p class="font-12 text-light mt-1">New joining details and administrative access</p>
            </div>
            <button type="button" class="icon-btn js-modal-close" aria-label="Close"><i data-lucide="x"></i></button>
        </div>

        <div class="modal-body p-30">
            <h4 class="font-13 font-600 mb-15 text-primary-color flex-center gap-8">
                <i data-lucide="clipboard-list" size="16"></i> Personal Details
            </h4>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="admin-form-label">First Name *</label>
                    <input type="text" class="form-control bg-white-input" value="<?= htmlspecialchars($cand_first) ?>"
                        placeholder="Enter first name" />
                </div>
                <div class="form-group">
                    <label class="admin-form-label">Middle Name</label>
                    <input type="text" class="form-control bg-white-input" value="<?= htmlspecialchars($cand_middle) ?>"
                        placeholder="Enter middle name" />
                </div>
                <div class="form-group">
                    <label class="admin-form-label">Last Name *</label>
                    <input type="text" class="form-control bg-white-input" value="<?= htmlspecialchars($cand_last) ?>"
                        placeholder="Enter last name" />
                </div>
            </div>

            <div class="form-grid-2 mt-10">
                <div class="form-group">
                    <label class="admin-form-label">Gender *</label>
                    <select class="form-control bg-white-input">
                        <option value="" selected disabled>Select Gender</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="admin-form-label">Date of Birth</label>
                    <input type="date" class="form-control bg-white-input" value="" />
                </div>
            </div>

            <div class="form-grid-2 mt-10">
                <div class="form-group">
                    <label class="admin-form-label">Phone</label>
                    <input type="text" class="form-control bg-white-input" value="<?= htmlspecialchars($cand_phone) ?>"
                        placeholder="e.g. +92 3xx xxxxxxx" />
                </div>
                <div class="form-group">
                    <label class="admin-form-label">ID card Number</label>
                    <input type="text" class="form-control bg-white-input" value="" placeholder="00000-0000000-0" />
                </div>
            </div>

            <div class="form-group mt-10">
                <label class="admin-form-label">Address</label>
                <textarea class="form-control bg-white-input" rows="2" placeholder="Enter full address"></textarea>
            </div>

            <div class="form-grid-2 mt-10">
                <div class="form-group">
                    <label class="admin-form-label">Emergency Contact</label>
                    <input type="text" class="form-control bg-white-input" value=""
                        placeholder="e.g. +92 3xx xxxxxxx" />
                </div>
                <div class="form-group">
                    <label class="admin-form-label">Emergency Contact Relation</label>
                    <input type="text" class="form-control bg-white-input" value="" placeholder="e.g. Father, Spouse" />
                </div>
            </div>

            <div class="form-group mt-10">
                <label class="admin-form-label">Email *</label>
                <input type="email" class="form-control bg-white-input" value="<?= htmlspecialchars($cand_email) ?>"
                    placeholder="email@example.com" />
            </div>

            <!-- Administrative Access (moved to modal bottom) -->

            <h4 class="font-13 font-600 mb-15 text-primary-color flex-center gap-8">
                <i data-lucide="building" size="16"></i> Job & Banking
            </h4>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="admin-form-label">Job Title</label>
                    <input type="text" class="form-control bg-white-input" value="<?= htmlspecialchars($cand_role) ?>"
                        placeholder="e.g. Software Engineer" />
                </div>

                <div class="form-group">
                    <label class="admin-form-label">Bank Name</label>
                    <select class="form-control bg-white-input">
                        <option selected>Standard Chartered</option>
                        <option>HBL</option>
                        <option>Bank Alfalah</option>
                        <option>UBL</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="admin-form-label">Account Type</label>
                    <select class="form-control bg-white-input">
                        <option selected>Savings</option>
                        <option>Current</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-2 mt-10">
                <div class="form-group">
                    <label class="admin-form-label">Account Title</label>
                    <input type="text" class="form-control bg-white-input"
                        value="<?= htmlspecialchars($selectedCandidate['name'] ?? '') ?>" placeholder="Account title" />
                </div>
                <div class="form-group">
                    <label class="admin-form-label">Account Number</label>
                    <input type="text" class="form-control bg-white-input" value="" placeholder="0000 0000 0000 0000" />
                </div>
            </div>

            <div class="form-group mt-10">
                <label class="admin-form-label">Bank Branch</label>
                <input type="text" class="form-control bg-white-input" value="" placeholder="Branch Code / City" />
            </div>

            <h4 class="font-13 font-600 mb-15 text-primary-color flex-center gap-8 mt-20">
                <i data-lucide="graduation-cap"></i> Education & Docs
            </h4>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label">Qualification</label>
                    <input type="text" class="form-control bg-white-input" value=""
                        placeholder="e.g. BS Computer Science" />
                </div>
                <div class="form-group">
                    <label class="admin-form-label">Degree / Certification</label>
                    <input type="text" class="form-control bg-white-input" value=""
                        placeholder="e.g. Bachelor's / Diploma" />
                </div>
            </div>

            <div class="form-grid-2 mt-10">
                <div class="form-group">
                    <label class="admin-form-label">College / University</label>
                    <input type="text" class="form-control bg-white-input" value=""
                        placeholder="e.g. University of Karachi" />
                </div>
                <div class="form-group">
                    <label class="admin-form-label">Professional Expertise</label>
                    <input type="text" class="form-control bg-white-input" value=""
                        placeholder="e.g. Node.js, React, APIs" />
                </div>
            </div>

            <div class="form-grid-2 mt-10">
                <div class="form-group">
                    <label class="admin-form-label">Last Employer</label>
                    <input type="text" class="form-control bg-white-input" value="" placeholder="Company name" />
                </div>
                <div class="form-group">
                    <label class="admin-form-label">Last Job Title</label>
                    <input type="text" class="form-control bg-white-input" value="" placeholder="Job title" />
                </div>
            </div>

            <div class="form-grid-2 mt-10">
                <div class="form-group">
                    <label class="admin-form-label">Experience From Date</label>
                    <input type="date" class="form-control bg-white-input" value="" />
                </div>
                <div class="form-group">
                    <label class="admin-form-label">Experience To Date</label>
                    <input type="date" class="form-control bg-white-input" value="" />
                </div>
            </div>

            <div class="form-grid-3 mt-10">
                <div class="form-group">
                    <label class="admin-form-label">Resume Attachment</label>
                    <div class="custom-file-upload">
                        <label for="cand_resume_upload" class="file-upload-wrapper has-file" id="cand_resume_wrapper">
                            <i data-lucide="file-text" size="20"></i>
                            <span class="file-upload-label" id="cand_resume_filename">Resume_Uploaded.pdf</span>
                            <span class="file-upload-info">File already uploaded</span>
                        </label>
                        <input type="file" id="cand_resume_upload" class="hidden-file-input" />
                        <div class="file-preview-actions">
                            <a
                                class="file-action-link"
                                href="data:text/plain;charset=utf-8,Placeholder%20for%20Resume_Uploaded.pdf"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                View
                            </a>
                            <a
                                class="file-action-link"
                                href="data:text/plain;charset=utf-8,Placeholder%20for%20Resume_Uploaded.pdf"
                                download="Resume_Uploaded.pdf"
                            >
                                Download
                            </a>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="admin-form-label">ID Card Attachment *</label>
                    <div class="custom-file-upload">
                        <label for="cand_id_upload" class="file-upload-wrapper has-file" id="cand_id_wrapper">
                            <i data-lucide="image" size="20" class="text-success"></i>
                            <span class="file-upload-label" id="cand_id_filename">ID_Card_Uploaded.png</span>
                            <span class="file-upload-info">File already uploaded</span>
                        </label>
                        <input type="file" id="cand_id_upload" class="hidden-file-input" required />
                        <div class="file-preview-actions">
                            <a
                                class="file-action-link"
                                href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+v9G0AAAAASUVORK5CYII="
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                View
                            </a>
                            <a
                                class="file-action-link"
                                href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+v9G0AAAAASUVORK5CYII="
                                download="ID_Card_Uploaded.png"
                            >
                                Download
                            </a>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="admin-form-label">Other Documents</label>
                    <div class="custom-file-upload">
                        <label for="cand_other_upload" class="file-upload-wrapper has-file" id="cand_other_wrapper">
                            <i data-lucide="files" size="20" class="text-success"></i>
                            <span class="file-upload-label" id="cand_other_filename">Certificates_Uploaded.zip</span>
                            <span class="file-upload-info">Files already uploaded</span>
                        </label>
                        <input type="file" id="cand_other_upload" class="hidden-file-input" multiple />
                        <div class="file-preview-actions">
                            <a
                                class="file-action-link"
                                href="data:text/plain;charset=utf-8,Placeholder%20for%20Certificates_Uploaded.zip"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                View
                            </a>
                            <a
                                class="file-action-link"
                                href="data:text/plain;charset=utf-8,Placeholder%20for%20Certificates_Uploaded.zip"
                                download="Certificates_Uploaded.zip"
                            >
                                Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-15 rounded-12 mt-20 mb-20 border">
                <h4 class="font-13 font-600 mb-15 text-primary-color flex-center gap-8">
                    <i data-lucide="shield-check"></i> Administrative Access
                </h4>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label class="admin-form-label">Department *</label>
                        <select class="form-control bg-white-input">
                            <option value="">Select Department</option>
                            <option value="Engineering" <?= $cand_dept === 'Engineering' ? 'selected' : '' ?>>Engineering
                            </option>
                            <option value="Design" <?= $cand_dept === 'Design' ? 'selected' : '' ?>>Design</option>
                            <option value="HR" <?= $cand_dept === 'HR' ? 'selected' : '' ?>>HR</option>
                            <option value="Sales & Marketing" <?= $cand_dept === 'Sales & Marketing' ? 'selected' : '' ?>>
                                Sales & Marketing</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="admin-form-label">Job Type *</label>
                        <select class="form-control bg-white-input">
                            <option value="" selected disabled>Select Job Type</option>
                            <option>Full Time</option>
                            <option>Part Time</option>
                            <option>Contract</option>
                            <option>Internship</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="admin-form-label">Salary *</label>
                        <input type="number" class="form-control bg-white-input" value="" placeholder="0.00" />
                    </div>
                </div>

                <div class="form-grid-3 mt-10">
                    <div class="form-group">
                        <label class="admin-form-label">Joining Date *</label>
                        <input type="date" class="form-control bg-white-input" value="" />
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Shift Timing *</label>
                        <select class="form-control bg-white-input">
                            <option value="" selected disabled>Select Shift Timing</option>
                            <option>Shift A (09:00 AM - 06:00 PM)</option>
                            <option>Shift B (02:00 PM - 11:00 PM)</option>
                            <option>Shift C (10:00 PM - 07:00 AM)</option>
                        </select>
                    </div>
                    <div class="form-group mt-10">
                        <label class="admin-form-label">Password *</label>
                        <div class="password-input-container">
                            <input type="password" class="form-control bg-white-input" id="candidate_admin_password"
                                name="candidate_admin_password" value="" placeholder="Enter password" />
                            <button type="button" class="password-toggle"
                                onclick="togglePassword('candidate_admin_password', this)"
                                aria-label="Toggle password visibility">
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
                <button type="button" class="btn-primary px-30">
                    <i data-lucide="check" size="18"></i> <span>Submit</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/employees.js"></script>
<?php include 'includes/footer.php'; ?>