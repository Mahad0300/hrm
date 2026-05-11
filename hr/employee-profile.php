<?php
$page_title = 'Employee Profile - HRM';
include 'includes/header.php';
include 'includes/sidebar.php';
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
                <p class="text-light font-14">Detailed profile information synchronized with registration records</p>
            </div>
        </div>
    </header>

    <div>
        <div class="profile-grid">
            <!-- Left Column: Primary Identity -->
            <div class="profile-aside">
                <div class="premium-card profile-identity-card flex-column flex-center text-center mb-24">
                    <div class="profile-avatar-wrapper mb-20">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=200"
                            class="profile-avatar-xl shadow-lg" alt="Emma Williams">
                        <span class="status-indicator-lg active border-4"></span>
                    </div>
                    <h2 class="font-22 font-700 mb-4">Emma Williams</h2>
                    <p class="text-primary-color font-600 mb-12">Product Manager</p>
                    <div class="badge badge-success px-15 py-6">Active Employee</div>
                </div>

                <div class="premium-card p-24 mb-24">
                    <h3 class="font-15 font-700 mb-20 flex-center gap-10">
                        <i data-lucide="contact" size="18" class="text-primary-color"></i>
                        Primary Contact
                    </h3>
                    <div class="mb-20">
                        <label class="admin-form-label">Email *</label>
                        <span class="font-14 font-500 block">emma.w@rtg.com</span>
                    </div>
                    <div class="mb-20">
                        <label class="admin-form-label">Phone</label>
                        <span class="font-14 font-500 block">+1 (555) 482-0192</span>
                    </div>
                </div>

                <div class="premium-card p-24 mb-24">
                    <h3 class="font-15 font-700 mb-20 flex-center gap-10">
                        <i data-lucide="shield-alert" size="18" class="text-primary-color"></i>
                        Emergency Contact
                    </h3>
                    <div class="mb-20">
                        <label class="admin-form-label">Emergency Contact</label>
                        <span class="font-14 font-500 block">+1 (555) 902-1122</span>
                    </div>
                    <div>
                        <label class="admin-form-label">Emergency Contact
                            Relation</label>
                        <span class="font-14 font-500 block">Spouse</span>
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

                    <div class="timeline-list">
                        <div class="timeline-item">
                            <div class="timeline-info">
                                <span class="font-15 font-700 text-dark block">$85,000</span>
                                <span class="font-12 text-light font-500">Jan 01, 2026</span>
                                <div class="mt-8">
                                    <span class="badge badge-success px-10 py-4 font-10">Recent Increment</span>
                                </div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-info">
                                <span class="font-15 font-600 text-dark block">$78,000</span>
                                <span class="font-12 text-light font-500">Jan 01, 2025</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-info">
                                <span class="font-15 font-600 text-dark block">$72,000</span>
                                <span class="font-12 text-light font-500">Jul 01, 2024</span>
                            </div>
                        </div>
                    </div>

                    <p class="font-11 text-light m-0">
                        Latest salary: <span class="font-600 text-dark">$85,000</span> (synced with payroll mock data).
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
                                <label class="admin-form-label">First Name *</label>
                                <span class="font-14 font-500">Emma</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Middle Name</label>
                                <span class="font-14 font-500">-</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Last Name *</label>
                                <span class="font-14 font-500">Williams</span>
                            </div>
                        </div>
                        <div class="form-grid-3 mb-30">
                            <div>
                                <label class="admin-form-label">Gender *</label>
                                <span class="font-14 font-500">Female</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Date of Birth</label>
                                <span class="font-14 font-500">June 15, 1995</span>
                            </div>
                            <div>
                                <label class="admin-form-label">ID card Number</label>
                                <span class="font-14 font-500">42101-5829102-1</span>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="admin-form-label">Address</label>
                            <span class="font-14 font-500">422 Maple Drive, Downtown Austin, TX 78701, United
                                States</span>
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
                                <span class="font-14 font-500">Shift A (09:00 AM - 06:00 PM)</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Job Title</label>
                                <span class="font-14 font-500">Product Manager</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Department</label>
                                <span class="font-14 font-500">Engineering</span>
                            </div>
                        </div>
                        <div class="form-grid-3 mb-10">
                            <div>
                                <label class="admin-form-label">Job Type</label>
                                <span class="font-14 font-500">Full Time</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Salary</label>
                                <span class="font-14 font-500 ls-05">$85,000</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Joining Date</label>
                                <span class="font-14 font-500">Jan 12, 2023</span>
                            </div>
                        </div>

                        <div class="border-top pt-30 mt-30">
                            <h3 class="font-16 font-700 flex-center gap-10 mb-24">
                                <i data-lucide="building" size="20" class="text-primary-color"></i>
                                Banking Information
                            </h3>

                            <div class="form-grid-3 mb-24">
                                <div>
                                    <label class="admin-form-label">Bank Name</label>
                                    <span class="font-13 font-600 block">Standard Chartered Bank</span>
                                </div>
                                <div>
                                    <label class="admin-form-label">Account Type</label>
                                    <span class="font-13 font-500 block">Savings</span>
                                </div>
                                <div>
                                    <label class="admin-form-label">Account
                                        Title</label>
                                    <span class="font-13 font-500 block">Emma Williams</span>
                                </div>
                            </div>
                            <div class="form-grid-2">
                                <div>
                                    <label class="admin-form-label">Account
                                        Number</label>
                                    <span class="font-13 font-500 block ls-05">SCB-5829-1029-4821</span>
                                </div>
                                <div>
                                    <label class="admin-form-label">Bank Branch</label>
                                    <span class="font-13 font-500 block">Downtown Austin (091)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Education & Docs -->
                <div class="premium-card mb-24">
                    <div class="card-header p-24 border-bottom">
                        <h3 class="font-16 font-700 flex-center gap-10">
                            <i data-lucide="graduation-cap" size="20" class="text-primary-color"></i>
                            Education & Docs
                        </h3>
                    </div>
                    <div class="card-body p-24">
                        <div class="form-grid-2 mb-30">
                            <div>
                                <label class="admin-form-label">Qualification</label>
                                <span class="font-14 font-500">Master's in CS</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Degree /
                                    Certification</label>
                                <span class="font-14 font-500">PMP, AWS Solutions Architect</span>
                            </div>
                        </div>
                        <div class="form-grid-2 mb-30">
                            <div>
                                <label class="admin-form-label">College /
                                    University</label>
                                <span class="font-14 font-500">Texas State University</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Professional
                                    Expertise</label>
                                <span class="font-14 font-500">React, Node.js, UI/UX</span>
                            </div>
                        </div>
                        <div class="form-grid-2 mb-30 border-top pt-30 mt-10">
                            <div>
                                <label class="admin-form-label">Last Employer</label>
                                <span class="font-14 font-500">Creative Solutions Inc.</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Last Job Title</label>
                                <span class="font-14 font-500">Senior Developer</span>
                            </div>
                        </div>
                        <div class="form-grid-2 mb-40">
                            <div>
                                <label class="admin-form-label">Experience From
                                    Date</label>
                                <span class="font-14 font-500">Jan 2020</span>
                            </div>
                            <div>
                                <label class="admin-form-label">Experience To
                                    Date</label>
                                <span class="font-14 font-500">Feb 2022</span>
                            </div>
                        </div>

                        <div class="form-grid-3 gap-24">
                            <div class="doc-card border rounded-16 p-20 hover-bg-light transition">
                                <label class="admin-form-label">Resume
                                    Attachment</label>
                                <div class="flex-center gap-12">
                                    <div class="icon-square-40 bg-primary-soft text-primary-color">
                                        <i data-lucide="file-text" size="20"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <span class="font-13 font-600 truncate block">Resume_Emma.pdf</span>
                                        <span class="font-11 text-light">PDF • 1.2MB</span>
                                    </div>
                                </div>
                            </div>
                            <div class="doc-card border rounded-16 p-20 hover-bg-light transition">
                                <label class="admin-form-label">ID Card Attachment
                                    *</label>
                                <div class="flex-center gap-12">
                                    <div class="icon-square-40 bg-success-soft text-success-color">
                                        <i data-lucide="image" size="20"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <span class="font-13 font-600 truncate block">ID_Card_Front.jpg</span>
                                        <span class="font-11 text-light">JPG • 800KB</span>
                                    </div>
                                </div>
                            </div>
                            <div class="doc-card border rounded-16 p-20 hover-bg-light transition">
                                <label class="admin-form-label">Other Documents</label>
                                <div class="flex-center gap-12">
                                    <div class="icon-square-40 bg-light-soft text-light">
                                        <i data-lucide="files" size="20"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <span class="font-13 font-600 truncate block">Certificates.zip</span>
                                        <span class="font-11 text-light">ZIP • 4.5MB</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="assets/js/employee-profile.js"></script>
<?php include 'includes/footer.php'; ?>