<?php
$page_title = "Job Postings";
$page_subtitle = "Management of all active and closed job openings.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<div class="job-list-page">
    <div class="job-list-toolbar mb-24">
        <div class="job-list-toolbar__actions">
            <div class="job-list-toolbar__search-wrap">
                <div class="search-box job-list-toolbar__search">
                    <i data-lucide="search" size="16"></i>
                    <input type="text" id="jobSearch" class="form-control"
                        placeholder="Search by job title or department...">
                </div>
            </div>
            <a href="create-job.php" class="btn-primary job-list-toolbar__create">
                <i data-lucide="plus"></i>
                <span>Create New Job</span>
            </a>
        </div>

        <div class="job-list-toolbar__filters">
            <div class="job-list-tabs" id="jobStatusTabs" role="tablist" aria-label="Filter by job status">
                <button type="button" class="job-list-tab active" data-status="">All Jobs</button>
                <button type="button" class="job-list-tab" data-status="Active">Active</button>
                <button type="button" class="job-list-tab" data-status="Close">Closed</button>
            </div>
            <div class="filter-item job-list-toolbar__dept">
                <label class="admin-form-label font-12">Department</label>
                <select id="filterDept" class="form-control font-13">
                    <option value="">All Departments</option>
                    <?php
                    try {
                        require_once dirname(__DIR__) . '/includes/db_connect.php';
                        if (isset($pdo)) {
                            $stmt = $pdo->query("SELECT name FROM departments WHERE deleted_at IS NULL ORDER BY name ASC");
                            while ($dept = $stmt->fetch()) {
                                echo "<option value='" . htmlspecialchars($dept['name'], ENT_QUOTES) . "'>" . htmlspecialchars($dept['name']) . "</option>";
                            }
                        }
                    } catch (Exception $e) {
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <div class="job-postings-grid" id="jobGridBody">
        <p class="text-light italic font-13 p-40 text-center w-full" style="grid-column: 1 / -1;">Loading job postings...</p>
    </div>
</div>

<!-- Job Details Modal -->
<div class="modal-overlay" id="jobDetailModal">
    <div class="modal-content premium wide-md job-detail-modal">
        <div class="modal-header">
            <div>
                <h3 id="detailJobTitle">Job Title</h3>
                <p class="font-12 text-light mt-1">Review posting details, requirements, and apply link.</p>
                <div class="job-detail-modal__pills mt-8" id="detailJobAppCount"></div>
            </div>
            <button type="button" class="icon-btn js-modal-close" aria-label="Close"><i data-lucide="x"></i></button>
        </div>

        <div class="modal-body p-30">
            <div class="job-detail-stats">
                <div class="job-detail-stat">
                    <div class="job-detail-stat__icon"><i data-lucide="building-2" size="18"></i></div>
                    <div class="job-detail-stat__text">
                        <span class="job-detail-stat__label">Department</span>
                        <span class="job-detail-stat__value" id="detailDept">—</span>
                    </div>
                </div>
                <div class="job-detail-stat">
                    <div class="job-detail-stat__icon"><i data-lucide="calendar" size="18"></i></div>
                    <div class="job-detail-stat__text">
                        <span class="job-detail-stat__label">Posted Date</span>
                        <span class="job-detail-stat__value" id="detailPostedDate">—</span>
                    </div>
                </div>
                <div class="job-detail-stat job-detail-stat--full">
                    <div class="job-detail-stat__icon"><i data-lucide="map-pin" size="18"></i></div>
                    <div class="job-detail-stat__text">
                        <span class="job-detail-stat__label">Location</span>
                        <span class="job-detail-stat__value allow-wrap" id="detailLocation">—</span>
                    </div>
                </div>
            </div>

            <div class="job-detail-stat job-detail-stat--full job-detail-stat--desc">
                <div class="job-detail-stat__icon"><i data-lucide="file-text" size="18"></i></div>
                <div class="job-detail-stat__text">
                    <span class="job-detail-stat__label">Job Description</span>
                    <span class="job-detail-stat__value allow-wrap" id="detailDesc">—</span>
                </div>
            </div>

            <section class="job-detail-section job-detail-section--bordered">
                <h4 class="job-detail-section__title">Requirements &amp; Questions</h4>
                <div class="job-detail-questions-wrap">
                    <div class="job-detail-q-list job-detail-q-list--standard">
                        <div class="job-detail-q-item job-detail-q-item--standard">
                            <div class="job-detail-q-item__q">Q1. Full Name</div>
                            <div class="job-detail-q-item__meta">
                                <span><i data-lucide="user" size="16"></i> Text input</span>
                                <span class="text-danger font-500"><i data-lucide="asterisk" size="16"></i> Required</span>
                                <span class="job-detail-q-standard-tag">Standard</span>
                            </div>
                        </div>
                        <div class="job-detail-q-item job-detail-q-item--standard">
                            <div class="job-detail-q-item__q">Q2. Email Address</div>
                            <div class="job-detail-q-item__meta">
                                <span><i data-lucide="mail" size="16"></i> Email input</span>
                                <span class="text-danger font-500"><i data-lucide="asterisk" size="16"></i> Required</span>
                                <span class="job-detail-q-standard-tag">Standard</span>
                            </div>
                        </div>
                        <div class="job-detail-q-item job-detail-q-item--standard">
                            <div class="job-detail-q-item__q">Q3. CNIC Number</div>
                            <div class="job-detail-q-item__meta">
                                <span><i data-lucide="credit-card" size="16"></i> Number input</span>
                                <span class="text-danger font-500"><i data-lucide="asterisk" size="16"></i> Required</span>
                                <span class="job-detail-q-standard-tag">Standard</span>
                            </div>
                        </div>
                        <div class="job-detail-q-item job-detail-q-item--standard">
                            <div class="job-detail-q-item__q">Q4. Resume / CV</div>
                            <div class="job-detail-q-item__meta">
                                <span><i data-lucide="file-text" size="16"></i> File upload</span>
                                <span class="text-danger font-500"><i data-lucide="asterisk" size="16"></i> Required</span>
                                <span class="job-detail-q-standard-tag">Standard</span>
                            </div>
                        </div>
                    </div>
                    <div id="detailQuestionsList"></div>
                </div>
            </section>
        </div>

        <div class="modal-footer flex-between p-30 border-top-0">
            <button type="button" class="btn-primary no-bg border text-light px-24 font-13" id="detailCopyLinkBtn">
                <i data-lucide="link" size="16"></i> <span>Copy Apply Link</span>
            </button>
            <button type="button" class="btn-primary px-30 font-13" id="detailEditBtn">
                <i data-lucide="edit-2" size="16"></i> <span>Edit Job Post</span>
            </button>
        </div>
    </div>
</div>

<script src="assets/js/job-management.js"></script>
<?php include 'includes/footer.php'; ?>
