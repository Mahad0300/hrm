<?php
$page_title = "KPI Management";
$page_subtitle = "Track and manage employee performance indicators and feedback.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Master Dashboard View -->
<div id="kpiMasterView">
    <!-- Summary Cards -->
    <div class="grid-3 gap-24 mb-24">
        <div class="card p-20 flex-center justify-start gap-16">
            <div class="icon-box-md success-light">
                <i data-lucide="award" class="text-success" size="24"></i>
            </div>
            <div>
                <h4 class="font-13 text-light font-500">Average Score</h4>
                <p class="font-20 font-700 mt-4" id="statAvgScore">0.0 / 5.0</p>
            </div>
        </div>
        <div class="card p-20 flex-center justify-start gap-16">
            <div class="icon-box-md primary-light">
                <i data-lucide="users" class="text-primary" size="24"></i>
            </div>
            <div>
                <h4 class="font-13 text-light font-500">Employees Rated</h4>
                <p class="font-20 font-700 mt-4" id="statRatedCount">0 / 0</p>
            </div>
        </div>
        <div class="card p-20 flex-center justify-start gap-16">
            <div class="icon-box-md info-light">
                <i data-lucide="trending-up" class="text-info" size="24"></i>
            </div>
            <div>
                <h4 class="font-13 text-light font-500">Top Department</h4>
                <p class="font-20 font-700 mt-4" id="statTopDept">---</p>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card p-24 mb-24">
        <div class="filter-grid" id="kpiFilters">
            <div class="filter-item">
                <label class="admin-form-label font-12">Search Employee</label>
                <div class="search-box w-full">
                    <i data-lucide="search" size="16"></i>
                    <input type="text" id="searchEmployee" class="form-control" placeholder="Search by employee name or ID...">
                </div>
            </div>
            <div class="filter-item">
                <label class="admin-form-label font-12">Department</label>
                <select class="form-control" id="filterDept">
                    <option value="">All Departments</option>
                    <!-- Departments load dynamically -->
                </select>
            </div>
            <div class="filter-item">
                <label class="admin-form-label font-12">Period</label>
                <select class="form-control" id="filterMonth">
                    <option value="">Any Time</option>
                    <option value="<?= date('Y-m') ?>">This Month</option>
                    <option value="<?= date('Y-m', strtotime('-1 month')) ?>">Last Month</option>
                </select>
            </div>
            <div class="filter-item">
                <label class="admin-form-label font-12">Performance</label>
                <select class="form-control" id="filterStatus">
                    <option value="">Performance (All)</option>
                    <option value="Excelling">Excelling</option>
                    <option value="Good">Good</option>
                    <option value="On Track">On Track</option>
                    <option value="Below Target">Below Target</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Header Info (Pagination placeholder) -->
    <div class="flex-between mb-24 px-4">
        <div class="flex-center gap-10">
            <span class="font-13 text-light">Show</span>
            <select class="form-control font-13 font-600 per-page-select" id="perPageSelect">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="all">All</option>
            </select>
            <span class="font-13 text-light">entries</span>
        </div>
        <div class="text-right">
            <span class="font-13 text-light" id="tableSummary">Showing 1 to 5 of 5 entries</span>
        </div>
    </div>

    <!-- KPI Master Table -->
    <div class="card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>EMPLOYEE</th>
                        <th>DEPARTMENT</th>
                        <th>KPI GOAL</th>
                        <th>TARGET VS ACHIEVED</th>
                        <th>SCORE</th>
                        <th>STATUS</th>
                        <th class="text-right px-30">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="kpiTableBody">
                    <!-- Dynamic Data Injected via kpi.js -->
                </tbody>
            </table>
        </div>
        <div class="p-24 flex-between border-top">
            <span class="font-13 text-light" id="paginationInfo">Showing 1 to 5 of 5 entries</span>
            <div class="flex-center gap-8" id="paginationControls">
                <button class="action-btn" id="prevPage" disabled><i data-lucide="chevron-left" size="16"></i></button>
                <div id="pageNumbers" class="flex-center gap-8">
                    <button class="action-btn btn-active">1</button>
                </div>
                <button class="action-btn" id="nextPage" disabled><i data-lucide="chevron-right" size="16"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Add Review Modal -->
<div class="modal-overlay" id="addReviewModal">
    <div class="modal-content premium wide-md">
        <div class="modal-header">
            <div>
                <h3>Add Performance Review</h3>
                <p class="font-12 text-light mt-1">Submit feedback and update KPI scores.</p>
            </div>
            <button class="icon-btn js-modal-close" onclick="closeModal('addReviewModal')"><i
                    data-lucide="x"></i></button>
        </div>
        <form id="addReviewForm">
            <div class="modal-body p-40 custom-scrollbar max-h-70vh">
                <div class="mb-32">
                    <label class="admin-form-label mt-4">Select Employee</label>
                    <select class="form-control mt-8" id="modalEmployeeSelect" required>
                        <option value="">Choose Employee...</option>
                        <!-- Employees load dynamically -->
                    </select>
                </div>
                <div class="mb-32">
                    <label class="admin-form-label mb-20">Review Period</label>
                    <div class="period-header">
                        <div class="period-card active" onclick="selectPeriod(this, 'Monthly')">
                            <i data-lucide="calendar" size="18" class="mb-4"></i>
                            <span class="font-14 font-700">MONTHLY</span>
                        </div>
                        <div class="period-card" onclick="selectPeriod(this, 'Quarterly')">
                            <i data-lucide="layers" size="18" class="mb-4"></i>
                            <span class="font-14 font-700">QUARTERLY</span>
                        </div>
                        <div class="period-card" onclick="selectPeriod(this, 'Annual')">
                            <i data-lucide="award" size="18" class="mb-4"></i>
                            <span class="font-14 font-700">ANNUAL</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="admin-form-label mb-20">Overall Performance Sentiment</label>
                    <div class="flex-column items-start">
                        <div class="sentiment-stars mb-8" id="starRatingSelect">
                            <i data-lucide="star" class="empty cursor-pointer" onclick="setSentiment(1)" data-value="1"
                                size="28"></i>
                            <i data-lucide="star" class="empty cursor-pointer" onclick="setSentiment(2)" data-value="2"
                                size="28"></i>
                            <i data-lucide="star" class="empty cursor-pointer" onclick="setSentiment(3)" data-value="3"
                                size="28"></i>
                            <i data-lucide="star" class="empty cursor-pointer" onclick="setSentiment(4)" data-value="4"
                                size="28"></i>
                            <i data-lucide="star" class="empty cursor-pointer" onclick="setSentiment(5)" data-value="5"
                                size="28"></i>
                        </div>
                        <div id="sentimentText" class="font-14 font-700 text-primary-color mt-4">Average Performance
                        </div>
                        <input type="hidden" name="rating" id="reviewRatingInput" value="3">
                    </div>
                </div>

                <div class="border-divider mb-24 mt-40"></div>

                <div class="mb-32">
                    <label class="admin-form-label mb-12">General Feedback / Comments</label>
                    <textarea name="feedback" class="form-control p-16 font-14" rows="3"
                        placeholder="Write overall performance feedback here..."></textarea>
                </div>

                <div class="mb-32 mt-40">
                    <div class="mb-20">
                        <h4 class="font-14 font-800 text-dark uppercase ls-1 mb-4">Specific KPI Goals</h4>
                        <p class="font-12 text-light">Define and rate individual performance metrics for this review
                            period.</p>
                    </div>

                    <div class="flex-center gap-12 bg-white p-8 rounded-12 border">
                        <input type="text" id="customGoalInput"
                            class="form-control font-14 border-none shadow-none px-16 h-48 flex-1"
                            placeholder="e.g. Code Stability, Client Satisfaction, Team Lead..."
                            style="background: transparent;">
                        <button type="button" class="btn-primary"
                            onclick="window.addCustomGoal()">
                            <i data-lucide="plus"></i> Add
                        </button>
                    </div>
                </div>

                <div class="flex-column gap-16 mt-20" id="dynamicKpiContainer">
                    <!-- Goals will be injected here -->
                </div>
            </div>
            <div class="modal-footer p-20 px-40 border-t flex-between bg-light rounded-b-24">
                <button type="button" class="btn-ghost js-modal-close"
                    onclick="closeModal('addReviewModal')">Cancel</button>
                <button type="submit" class="btn-primary">Submit Review</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/kpi.js"></script>
<?php include 'includes/footer.php'; ?>