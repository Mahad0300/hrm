<?php
$page_title = "Employee KPI Scorecard";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<div id="kpiReportPage">
    <div class="flex-between align-start mb-24">
        <div class="greeting-area">
            <div class="flex-center gap-12 mb-8">
                <a href="kpi-management.php" class="action-btn no-bg border" title="Back to Management">
                    <i data-lucide="arrow-left" size="18"></i>
                </a>
                <h1 class="font-24 font-700 ls-05 m-0">Employee KPI Scorecard</h1>
            </div>
            <p class="text-light font-14 m-0">Detailed performance indicators and historical feedback.</p>
        </div>
        <button type="button" class="btn-primary py-8 px-16 flex-center gap-8 font-13" data-hr-perm-action="create" onclick="openReviewModal()">
            <i data-lucide="plus-circle" size="16"></i>
            <span>Add New Review</span>
        </button>
    </div>

    <div class="grid-2-1 gap-24">
        <!-- Left: Detailed Scorecard -->
        <div class="flex-column gap-24">
            <div class="card p-30">
                <div class="flex-center justify-start gap-10 mb-30">
                    <img id="detailAvatar" src="../images/profile-image/default-avatar.svg"
                        class="w-80 h-80 rounded-full border-4-white" alt="Avatar">
                    <div>
                        <h3 class="font-24 font-700" id="detailName">Loading...</h3>
                        <p class="font-14 text-light mt-4" id="detailDept">---</p>
                        <div class="flex-center justify-start gap-8 mt-12">
                            <span class="badge badge-success font-11 uppercase ls-05" id="detailStatus">---</span>
                            <span class="text-light opacity-50">|</span>
                            <div class="flex-center gap-8">
                                <i data-lucide="star" class="text-warning fill-warning" size="14"></i>
                                <span class="font-14 font-700" id="detailScore">0.0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-divider my-30"></div>

                <div class="kpi-goals-section">
                    <div class="mb-24">
                        <h4 class="font-14 font-800 text-dark uppercase ls-05">Latest Performance Scorecard</h4>
                    </div>

                    <div class="space-y-20" id="detailGoalsContainer">
                        <!-- Dynamic Goals will load here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Feedback History -->
        <div class="card p-30 h-fit sticky-top-120">
            <h4 class="font-14 font-600 text-dark uppercase mb-24 ls-05">Feedback History</h4>
            <div class="timeline-compact" id="feedbackTimeline">
                <!-- Dynamic History loads here -->
            </div>
        </div>
    </div>
</div>

<!-- Add Review Modal (Specific for this employee) -->
<div class="modal-overlay" id="addReviewModal">
    <div class="modal-content premium wide-md">
        <div class="modal-header">
            <div>
                <h3 id="modalTitleText">Add Performance Review</h3>
                <p class="font-12 text-light mt-1" id="modalSubtitleText">Submit feedback and update KPI scores for
                    <span id="modalEmpNameDisplay" class="font-600 text-dark">...</span>
                </p>
            </div>
            <button class="icon-btn js-modal-close" onclick="closeModal('addReviewModal')"><i
                    data-lucide="x"></i></button>
        </div>
        <form id="addReviewForm">
            <input type="hidden" name="employee_id" id="modalEmployeeId">
            <input type="hidden" name="review_id" id="modalReviewId">
            <div class="modal-body p-40 custom-scrollbar max-h-70vh">
                <div class="mb-32">
                    <label class="admin-form-label mb-10">Review Period</label>
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
                    <label class="admin-form-label mb-10">Overall Performance Sentiment</label>
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
                    <label class="admin-form-label mb-10">General Feedback / Comments</label>
                    <textarea name="feedback" class="form-control p-16 font-14" rows="3"
                        placeholder="Write overall performance feedback here..."></textarea>
                </div>

                <div class="mb-32">
                    <div class="mb-10">
                        <h4 class="font-14 font-800 text-dark uppercase ls-1 mb-4">Specific KPI Goals</h4>
                        <p class="font-12 text-light">Update individual performance metrics.</p>
                    </div>

                    <div class="flex-center gap-12 bg-white p-8 rounded-12 border">
                        <input type="text" id="customGoalInput"
                            class="form-control font-14 border-none shadow-none px-16 h-48 flex-1"
                            placeholder="e.g. Code Stability, Client Satisfaction..." style="background: transparent;">
                        <button type="button" class="btn-primary font-14 font-700 h-48 px-32 rounded-10 shadow-primary"
                            onclick="window.addCustomGoal()">
                            <i data-lucide="plus" size="18" class="mr-8"></i> Add
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
                <button type="submit" class="btn-primary px-32 font-700">Submit Review</button>
            </div>
        </form>
    </div>
</div>

<!-- View Detail Modal (Read-only) -->
<div class="modal-overlay" id="viewReviewDetailModal">
    <div class="modal-content premium wide-md house-shadow review-detail-modal">
        <div class="modal-header bb-none review-detail-modal__header">
            <div>
                <h3>Review Detail</h3>
                <p>Full breakdown of performance feedback for <span
                        id="viewDetailEmpName">...</span></p>
            </div>
            <button class="icon-btn js-modal-close" onclick="closeModal('viewReviewDetailModal')"><i
                    data-lucide="x"></i></button>
        </div>
        <div class="modal-body review-detail-modal__body custom-scrollbar">
            <div class="review-detail-summary">
                <div class="review-detail-summary__item">
                    <div class="review-detail-summary__icon">
                        <i data-lucide="calendar" class="text-primary-color" size="24"></i>
                    </div>
                    <div>
                        <span class="review-detail-label">Review Period</span>
                        <h4 class="review-detail-period" id="viewDetailPeriod">MONTHLY
                        </h4>
                    </div>
                </div>
                <div class="review-detail-score-block">
                    <span class="review-detail-label">Performance Score</span>
                    <div class="rating-tag review-detail-rating">
                        <i data-lucide="star" class="text-warning fill-warning" size="18"></i>
                        <span id="viewDetailRating">4.0 / 5.0</span>
                    </div>
                </div>
            </div>

            <div class="review-detail-section">
                <div class="review-detail-section__head">
                    <label class="admin-form-label mb-0">General Feedback / Comments</label>
                </div>
                <div class="review-detail-feedback"
                    id="viewDetailFeedback">
                    ...
                </div>
            </div>

            <div class="review-detail-section__head review-detail-section__head--goals">
                <h4>Goal-Specific Breakdown</h4>
                <span>detailed scores & context</span>
            </div>

            <div id="viewDetailKpiContainer" class="review-detail-goals">
                <!-- Goals injected here -->
            </div>
        </div>
        <div class="modal-footer review-detail-modal__footer">
            <button class="btn-danger-light" id="viewDetailDeleteBtn" data-hr-perm-action="delete">Delete</button>
            <button class="btn-primary" id="viewDetailEditBtn" data-hr-perm-action="edit">Edit Record</button>
        </div>
    </div>
</div>


<!-- Special JS for Reporting -->
<script src="assets/js/kpi-report.js"></script>

<?php include 'includes/footer.php'; ?>