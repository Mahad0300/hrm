<?php
$page_title = "Create New Job";
$page_subtitle = "Post a new job opening and configure candidate assessment questions.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<div class="job-creation-container max-w-1000 mx-auto">
    <div class="card premium mb-30 overflow-hidden">
        <div class="card-header-gradient p-30 text-white">
            <h3 class="m-0 font-600">Job Posting Details</h3>
            <p class="font-13 opacity-8 mt-1">Fill in the basic information for the new position</p>
        </div>
        <div class="card-body p-30">
            <form id="createJobForm">
                <div class="form-group mb-24">
                    <label class="admin-form-label" for="jobTitle">Job Title *</label>
                    <input type="text" id="jobTitle" class="form-control bg-white-input font-15"
                        placeholder="e.g. Senior Frontend Developer" required>
                </div>

                <div class="form-grid-2 mb-24">
                    <div class="form-group">
                        <label class="admin-form-label" for="jobDept">Department *</label>
                        <select id="jobDept" class="form-control bg-white-input" required>
                            <option value="">Select Department</option>
                            <?php
                            try {
                                require_once dirname(__DIR__) . '/includes/db_connect.php';
                                if (isset($pdo)) {
                                    $stmt = $pdo->query("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name ASC");
                                    while ($dept = $stmt->fetch()) {
                                        echo "<option value='{$dept['id']}'>{$dept['name']}</option>";
                                    }
                                } else {
                                    echo "<option disabled>Database connection failed</option>";
                                }
                            } catch (Exception $e) {
                                echo "<option disabled>Error loading departments</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label" for="jobLocation">Location *</label>
                        <input type="text" id="jobLocation" class="form-control bg-white-input"
                            placeholder="e.g. Remote, Lahore, Karachi" required>
                    </div>
                </div>

                <div class="form-group mb-30">
                    <label class="admin-form-label" for="jobDesc">Job Description *</label>
                    <textarea id="jobDesc" class="form-control bg-white-input" style="min-height: 120px;"
                        placeholder="Describe the responsibilities, requirements, and benefits..." required></textarea>
                </div>

                <!-- Form Builder Section -->
                <div class="form-builder-section pt-30 border-top mt-30">
                    <div class="mb-20">
                        <h4 class="m-0 font-600 text-dark">Application Form Builder</h4>
                        <p class="font-12 text-light mt-1">Configure the questions candidates must answer during
                            application</p>
                    </div>

                    <!-- Quick Add Section -->
                    <div class="flex align-center gap-20 mb-30">
                        <span class="create-job-quick-add-label flex align-center gap-8">
                            <i data-lucide="zap" size="16" class="text-primary-color"></i> QUICK ADD:
                        </span>
                        <div class="quick-add-chips">
                            <button type="button" class="chip-btn"
                                onclick="quickAddQuestion('What is your current salary?')">+ Salary</button>
                            <button type="button" class="chip-btn"
                                onclick="quickAddQuestion('How many years of experience do you have?')">+
                                Experience</button>
                            <button type="button" class="chip-btn" onclick="quickAddQuestion('Portfolio Link')">+
                                Portfolio</button>
                            <button type="button" class="chip-btn" onclick="quickAddQuestion('LinkedIn Profile')">+
                                LinkedIn</button>
                            <button type="button" class="chip-btn" onclick="quickAddQuestion('When can you start?')">+
                                Start Date</button>
                        </div>
                    </div>

                    <!-- Fixed/Locked Fields -->
                    <div class="form-builder-list mb-12">
                        <div class="form-builder-card locked">
                            <i data-lucide="lock" class="card-lock-icon" size="18"></i>
                            <div class="question-card-content flex-between">
                                <div>
                                    <div class="font-14 font-600 text-dark">Full Name</div>
                                    <div class="font-12 text-danger mt-4">* Required field</div>
                                </div>
                                <div class="text-light font-12 font-500 uppercase">Text input</div>
                            </div>
                        </div>
                        <div class="form-builder-card locked">
                            <i data-lucide="lock" class="card-lock-icon" size="18"></i>
                            <div class="question-card-content flex-between">
                                <div>
                                    <div class="font-14 font-600 text-dark">Email Address</div>
                                    <div class="font-12 text-danger mt-4">* Required field</div>
                                </div>
                                <div class="text-light font-12 font-500 uppercase">Email input</div>
                            </div>
                        </div>
                        <div class="form-builder-card locked">
                            <i data-lucide="lock" class="card-lock-icon" size="18"></i>
                            <div class="question-card-content flex-between">
                                <div>
                                    <div class="font-14 font-600 text-dark">Phone Number</div>
                                    <div class="font-12 text-danger mt-4">* Required field</div>
                                </div>
                                <div class="text-light font-12 font-500 uppercase">Phone input</div>
                            </div>
                        </div>
                        <div class="form-builder-card locked">
                            <i data-lucide="lock" class="card-lock-icon" size="18"></i>
                            <div class="question-card-content flex-between">
                                <div>
                                    <div class="font-14 font-600 text-dark">CNIC Number</div>
                                    <div class="font-12 text-danger mt-4">* Required field</div>
                                </div>
                                <div class="text-light font-12 font-500 uppercase">Number input</div>
                            </div>
                        </div>
                        <div class="form-builder-card locked">
                            <i data-lucide="lock" class="card-lock-icon" size="18"></i>
                            <div class="question-card-content flex-between">
                                <div>
                                    <div class="font-14 font-600 text-dark">Residential Address</div>
                                    <div class="font-12 text-danger mt-4">* Required field</div>
                                </div>
                                <div class="text-light font-12 font-500 uppercase">Text area</div>
                            </div>
                        </div>
                        <div class="form-builder-card locked">
                            <i data-lucide="lock" class="card-lock-icon" size="18"></i>
                            <div class="question-card-content flex-between">
                                <div>
                                    <div class="font-14 font-600 text-dark">Resume / CV</div>
                                    <div class="font-12 text-danger mt-4">* Required field</div>
                                </div>
                                <div class="text-light font-12 font-500 uppercase">File upload</div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Question List -->
                    <div id="questionList">
                        <!-- Custom questions injected here -->
                    </div>

                    <!-- Dotted Add Button -->
                    <button type="button" class="btn-add-dotted mt-10" id="addQuestionBtn">
                        <i data-lucide="plus" size="18"></i>
                        <span>Add Custom Question</span>
                    </button>
                </div>

                <div class="flex-between gap-12 mt-20">
                    <button type="button" class="btn-cancel-custom px-30" onclick="window.history.back()">
                        <i data-lucide="x" size="18"></i>
                        <span>Cancel</span>
                    </button>
                    <button type="submit" class="btn-primary px-40">
                        <i data-lucide="send" size="18"></i>
                        <span>Finalize & Post Job</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/job-management.js"></script>
<?php include 'includes/footer.php'; ?>