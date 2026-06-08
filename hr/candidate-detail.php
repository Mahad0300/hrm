<?php
$page_title = "Candidate Profile";
$page_subtitle = "Application profile, answers, and documents.";
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="cand-v2-container">
    <!-- Header Action Bar (Top) -->
    <div class="flex-between mb-24 px-8">
        <div class="flex-center gap-16">

            <a href="job-candidates.php" class="action-btn no-bg border" title="Back to Attendance">
                <i data-lucide="arrow-left" size="18"></i>
            </a>


            <h2 class="font-24 font-700 m-0" style="color: #1e1b4b;">Candidate Profile</h2>
        </div>
        <div class="flex-center gap-12">
            <button class="btn-light gap-8 hidden" id="rejectCandidateBtn"
                data-hr-perm-action="reject_ban"
                style="background: rgba(245, 158, 11, 0.1); color: #b45309;">
                <i data-lucide="user-x"></i> <span>Reject Candidate</span>
            </button>
            <button class="btn-light gap-8" id="banCandidateBtn"
                data-hr-perm-action="reject_ban"
                style="background: rgba(239, 68, 68, 0.08); color: var(--danger);">
                <i data-lucide="ban"></i> <span>Ban Candidate</span>
            </button>
        </div>
    </div>

    <!-- Header Action Bar (Top) -->
    <div class="cand-v2-header-card">
        <div class="cand-v2-avatar" id="candAvatar">—</div>
        <div class="cand-v2-header-info">
            <div class="cand-v2-name-row">
                <h1 class="font-24 font-700 m-0" id="candName">Loading...</h1>
                <span class="cand-v2-status-badge" id="candStatus">PENDING</span>
            </div>
            <div class="cand-v2-contact-row">
                <div class="cand-v2-contact-item">
                    <i data-lucide="mail" size="14"></i>
                    <span id="candEmail">—</span>
                </div>
                <div class="cand-v2-contact-item">
                    <i data-lucide="phone" size="14"></i>
                    <span id="candPhone">—</span>
                </div>
                <div class="cand-v2-contact-item">
                    <i data-lucide="briefcase" size="14"></i>
                    <span id="candJobTitle">—</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Two-Column Main Layout -->
    <div class="cand-v2-main-layout">
        <!-- Main Content -->
        <div class="cand-v2-content-area">
            <!-- Application Details Grid -->
            <div class="cand-v2-card">
                <h3 class="cand-v2-card-title">Application Details</h3>
                <div class="cand-v2-details-grid" id="candDetailsGrid">
                    <!-- Questions & Answers will be injected here -->
                    <p class="text-light italic font-13">Loading details...</p>
                </div>
            </div>

            <!-- Journey History -->
            <div class="cand-v2-card">
                <h3 class="cand-v2-card-title">Journey History</h3>
                <div class="cand-v2-timeline" id="candJourney">
                    <!-- Timeline items will be dynamically injected here -->
                    <p class="text-light italic font-13 ml-16">Loading history...</p>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="cand-v2-sidebar">
            <!-- Duplicate Warning integration -->
            <div id="duplicateWarning" class="card p-16 hidden mb-24"
                style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 16px;">
                <div class="flex-start gap-12 text-danger">
                    <i data-lucide="alert-circle" size="20" class="mt-2"></i>
                    <div>
                        <div class="font-13 font-700 mb-4">Potential Duplicate</div>
                        <div class="font-11 op-08" id="duplicateText">Matches another candidate’s information.</div>
                        <a href="#" id="duplicateOriginalLink"
                            class="font-11 font-800 text-danger hover-underline mt-8 block">View Original Candidate</a>
                    </div>
                </div>
            </div>

            <div class="cand-v2-card mb-24">
                <h3 class="cand-v2-card-title font-13 text-light uppercase mb-16">Document Vault</h3>
                <div id="candDocs">
                    <a href="#" target="_blank" class="cand-v2-doc-card hidden" id="resumeCard">
                        <div class="cand-v2-doc-icon">
                            <i data-lucide="file-text" size="20"></i>
                        </div>
                        <div class="cand-v2-doc-info">
                            <div class="cand-v2-doc-name">Main Resume / CV</div>
                            <div class="cand-v2-doc-meta" id="resumeFileName">Resume_File.pdf</div>
                        </div>
                        <i data-lucide="external-link" size="14" class="text-light"></i>
                    </a>
                    <p id="noDocText" class="text-light italic font-13">No documents found.</p>
                </div>
            </div>

            <div class="flex-column gap-12">
                <button class="cand-v2-btn-approve" id="primaryPipelineBtn">Approve to Interview</button>
                <button class="btn-light w-100 hidden" id="rescheduleBtn" data-hr-perm-action="schedule_interview">
                    <i data-lucide="calendar-clock"></i> 
                    <span>Reschedule Interview</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Schedule interview modal -->
<div class="modal-overlay" id="scheduleInterviewModal">
    <div class="modal-content premium wide-sm">
        <div class="modal-header">
            <div>
                <h3>Schedule Interview</h3>
                <p class="font-12 text-light mt-1 m-0">Set date and time for this candidate</p>
            </div>
            <button type="button" class="icon-btn" onclick="closeModal('scheduleInterviewModal')"><i data-lucide="x"
                    size="20"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="scheduleInterviewForm">
                <div class="form-group mb-20">
                    <label class="admin-form-label">Interview Date *</label>
                    <input type="date" id="scheduleInterviewDate" class="form-control bg-white-input" required>
                </div>
                <div class="form-group mb-0">
                    <label class="admin-form-label">Interview Time *</label>
                    <input type="time" id="scheduleInterviewTime" class="form-control bg-white-input" required>
                </div>
                <div class="form-group mb-0">
                    <label class="admin-form-label">Interview Notes / Feedback</label>
                    <textarea id="scheduleInterviewFeedback" class="form-control bg-white-input" rows="3"
                        placeholder="Add any specific notes or initial feedback..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer flex-end gap-12 p-30 border-top-0">
            <button type="submit" form="scheduleInterviewForm" class="btn-primary px-30">Schedule & Notify</button>
        </div>
    </div>
</div>

<!-- Status transition modal (Offer, Hire, Reject etc) -->
<div class="modal-overlay" id="statusTransitionModal">
    <div class="modal-content premium wide-sm">
        <div class="modal-header">
            <div>
                <h3 id="statusModalTitle">Update Status</h3>
                <p class="font-12 text-light mt-1 m-0" id="statusModalSubtitle">Move candidate to the next stage</p>
            </div>
            <button type="button" class="icon-btn" onclick="closeModal('statusTransitionModal')"><i data-lucide="x"
                    size="20"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="statusTransitionForm">
                <input type="hidden" id="targetStatus">
                <div class="form-group mb-0">
                    <label class="admin-form-label">Recruiter Feedback / Evaluation *</label>
                    <textarea id="statusFeedback" class="form-control bg-white-input" rows="5"
                        placeholder="Please provide a detailed evaluation or reason for this status change..."
                        required></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer flex-end gap-12 p-30 border-top-0">
            <button type="button" class="btn-light px-24" onclick="closeModal('statusTransitionModal')">Cancel</button>
            <button type="submit" form="statusTransitionForm" class="btn-primary px-30"
                id="statusModalSubmitBtn">Confirm & Update</button>
        </div>
    </div>
</div>

<script src="assets/js/candidate-detail.js?v=2"></script>
<?php include 'includes/footer.php'; ?>