<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';

$job_id = $_GET['id'] ?? null;
if (!$job_id) {
    header('Location: job-list.php');
    exit;
}

// Fetch Job Details
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: job-list.php');
    exit;
}

// Fetch Job Questions
$qStmt = $pdo->prepare("SELECT * FROM job_questions WHERE job_id = ?");
$qStmt->execute([$job_id]);
$questions = $qStmt->fetchAll();

$page_title = "Edit Job";
$page_subtitle = "Update job details and candidate assessment questions.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<div class="job-creation-container max-w-1000 mx-auto">
    <div class="card premium mb-30 overflow-hidden">
        <div class="card-header-gradient p-30 text-white mb-24">
            <h3 class="m-0 font-600">Edit Job Posting</h3>
            <p class="font-13 opacity-8 mt-1">Update the information for this position</p>
        </div>
        <div class="card-body p-30">
            <form id="createJobForm"> <!-- Using same ID for JS consistency -->
                <input type="hidden" id="jobId" value="<?= htmlspecialchars($job['id']) ?>">

                <div class="form-group mb-24">
                    <label class="admin-form-label" for="jobTitle">Job Title *</label>
                    <input type="text" id="jobTitle" class="form-control bg-white-input font-15"
                        value="<?= htmlspecialchars($job['title']) ?>" placeholder="e.g. Senior Frontend Developer"
                        required>
                </div>

                <div class="form-grid-2 mb-24">
                    <div class="form-group">
                        <label class="admin-form-label" for="jobDept">Department *</label>
                        <select id="jobDept" class="form-control bg-white-input" required>
                            <option value="">Select Department</option>
                            <?php
                            try {
                                $dStmt = $pdo->query("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name ASC");
                                while ($dept = $dStmt->fetch()) {
                                    $selected = ($dept['id'] == $job['department_id']) ? 'selected' : '';
                                    echo "<option value='{$dept['id']}' $selected>{$dept['name']}</option>";
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
                            value="<?= htmlspecialchars($job['location']) ?>" placeholder="e.g. Remote, Lahore, Karachi"
                            required>
                    </div>
                </div>

                <div class="form-group mb-24">
                    <label class="admin-form-label" for="jobDesc">Job Description *</label>
                    <textarea id="jobDesc" class="form-control bg-white-input" style="min-height: 120px;"
                        placeholder="Describe the responsibilities, requirements, and benefits..."
                        required><?= htmlspecialchars($job['description']) ?></textarea>
                </div>

                <div class="form-group mb-24">
                    <label class="admin-form-label" for="jobStatus">Posting Status *</label>
                    <select id="jobStatus" class="form-control bg-white-input" required>
                        <option value="Active" <?= $job['status'] === 'Active' ? 'selected' : '' ?>>Active (Accepting Applications)</option>
                        <option value="Close" <?= $job['status'] === 'Close' ? 'selected' : '' ?>>Close (No longer accepting)</option>
                    </select>
                </div>

                <!-- Form Builder Section -->
                <div class="form-builder-section pt-30 border-top mt-30">
                    <div class="mb-24">
                        <h4 class="m-0 font-600 text-dark">Application Form Builder</h4>
                        <p class="font-12 text-light mt-1">Configure the questions candidates must answer during
                            application</p>
                    </div>

                    <!-- Quick Add Section -->
                    <div class="flex-center gap-10 mb-24">
                        <span class="create-job-quick-add-label">
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
                        <!-- Custom questions injected here via JS snippet below -->
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
                        <span>Finalize & Update Job</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/job-management.js"></script>
<script>
    // Pre-populate questions
    document.addEventListener('DOMContentLoaded', function () {
        <?php foreach ($questions as $q): ?>
            if (typeof addQuestion === 'function') {
                addQuestion(
                    "<?= addslashes($q['question_text']) ?>",
                    "<?= addslashes($q['answer_type']) ?>",
                    <?= $q['is_required'] ? 'true' : 'false' ?>
                );
            }
        <?php endforeach; ?>
    });
</script>
<?php include 'includes/footer.php'; ?>