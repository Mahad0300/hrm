<?php
$page_title = "Job Postings";
$page_subtitle = "Management of all active and closed job openings.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-action-area">
    <div class="header-actions">
        <a href="create-job.php" class="btn-primary">
            <i data-lucide="plus"></i>
            <span>Create New Job</span>
        </a>
    </div>
</div>

<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="search-bar-wrap">
            <i data-lucide="search" size="18"></i>
            <input type="text" class="search-input" id="jobSearch" placeholder="Search postings...">
        </div>
        <div class="filter-item">
            <select id="filterDept" class="form-control font-13">
                <option value="">All Departments</option>
                <?php
                try {
                    require_once dirname(__DIR__) . '/includes/db_connect.php';
                    if (isset($pdo)) {
                        $stmt = $pdo->query("SELECT name FROM departments WHERE deleted_at IS NULL ORDER BY name ASC");
                        while ($dept = $stmt->fetch()) {
                            echo "<option value='{$dept['name']}'>{$dept['name']}</option>";
                        }
                    }
                } catch (Exception $e) {
                    // Fail silently for filters
                }
                ?>
            </select>
        </div>
        <div class="filter-item">
            <select id="filterStatus" class="form-control font-13">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Close">Closed</option>
            </select>
        </div>
        <div class="filter-item">
            <select id="sortBy" class="form-control font-13">
                <option value="newest">Sort by: Newest</option>
                <option value="oldest">Sort by: Oldest</option>
            </select>
        </div>
    </div>
</div>

<!-- Table Tools: Per Page & Summary -->
<div class="flex-between mb-24 px-4 mt-24">
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
        <span class="font-13 text-light" id="tableSummary">Showing 0 entries</span>
    </div>
</div>

<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 20%;">JOB TITLE</th>
                    <th style="width: 15%;">DEPARTMENT</th>
                    <th style="width: 25%;">LOCATION</th>
                    <th style="width: 12%;">POSTED DATE</th>
                    <th style="width: 10%;">APPLICANTS</th>
                    <th style="width: 10%;">STATUS</th>
                    <th class="text-right px-30" style="width: 8%;">ACTION</th>
                </tr>
            </thead>
            <tbody id="jobTableBody">
                <!-- Job rows will be injected by JS -->
            </tbody>
        </table>
    </div>
    <!-- Pagination Footer INSIDE Card -->
    <div class="p-24 flex-between border-top">
        <span class="font-13 text-light" id="paginationInfo">Showing 0 entries</span>
        <div class="flex-center gap-8" id="paginationControls">
            <button class="action-btn" id="prevPage" title="Previous"><i data-lucide="chevron-left"
                    size="16"></i></button>
            <div id="pageNumbers" class="flex-center gap-8">
                <!-- Page numbers injected by JS -->
            </div>
            <button class="action-btn" id="nextPage" title="Next"><i data-lucide="chevron-right" size="16"></i></button>
        </div>
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
                    <div class="job-detail-stat__icon"><i data-lucide="map-pin" size="18"></i></div>
                    <div class="job-detail-stat__text">
                        <span class="job-detail-stat__label">Location</span>
                        <span class="job-detail-stat__value allow-wrap" id="detailLocation">—</span>
                    </div>
                </div>
                <div class="job-detail-stat">
                    <div class="job-detail-stat__icon"><i data-lucide="clock" size="18"></i></div>
                    <div class="job-detail-stat__text">
                        <span class="job-detail-stat__label">Employment Type</span>
                        <span class="job-detail-stat__value" id="detailType">—</span>
                    </div>
                </div>
                <div class="job-detail-stat">
                    <div class="job-detail-stat__icon"><i data-lucide="calendar" size="18"></i></div>
                    <div class="job-detail-stat__text">
                        <span class="job-detail-stat__label">Posted Date</span>
                        <span class="job-detail-stat__value" id="detailPostedDate">—</span>
                    </div>
                </div>
            </div>

            <section class="job-detail-section">
                <h4 class="job-detail-section__title">Job Description</h4>
                <div class="job-detail-desc" id="detailDesc"></div>
            </section>

            <section class="job-detail-section job-detail-section--bordered">
                <h4 class="job-detail-section__title">Requirements &amp; Questions</h4>
                <div class="job-detail-questions-wrap">
                    <div class="job-detail-q-list job-detail-q-list--standard">
                        <div class="job-detail-q-item job-detail-q-item--standard">
                            <div class="job-detail-q-item__q">Q1. Full Name</div>
                            <div class="job-detail-q-item__meta">
                                <span><i data-lucide="user" size="16"></i> Text input</span>
                                <span class="text-danger font-500"><i data-lucide="asterisk" size="16"></i>
                                    Required</span>
                                <span class="job-detail-q-standard-tag">Standard</span>
                            </div>
                        </div>
                        <div class="job-detail-q-item job-detail-q-item--standard">
                            <div class="job-detail-q-item__q">Q2. Email Address</div>
                            <div class="job-detail-q-item__meta">
                                <span><i data-lucide="mail" size="16"></i> Email input</span>
                                <span class="text-danger font-500"><i data-lucide="asterisk" size="16"></i>
                                    Required</span>
                                <span class="job-detail-q-standard-tag">Standard</span>
                            </div>
                        </div>
                        <div class="job-detail-q-item job-detail-q-item--standard">
                            <div class="job-detail-q-item__q">Q3. CNIC Number</div>
                            <div class="job-detail-q-item__meta">
                                <span><i data-lucide="credit-card" size="16"></i> Number input</span>
                                <span class="text-danger font-500"><i data-lucide="asterisk" size="16"></i>
                                    Required</span>
                                <span class="job-detail-q-standard-tag">Standard</span>
                            </div>
                        </div>
                        <div class="job-detail-q-item job-detail-q-item--standard">
                            <div class="job-detail-q-item__q">Q4. Resume / CV</div>
                            <div class="job-detail-q-item__meta">
                                <span><i data-lucide="file-text" size="16"></i> File upload</span>
                                <span class="text-danger font-500"><i data-lucide="asterisk" size="16"></i>
                                    Required</span>
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