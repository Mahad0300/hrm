<?php
use App\Core\View;
use App\Core\Database;

$cand_id = $_GET['id'] ?? '';
$name_param = $_GET['name'] ?? $_GET['candidate'] ?? '';

$pdo = $pdo ?? Database::connection();

// 1. If name is provided and ID is NOT provided, resolve ID from slug
if ($cand_id === '' && $name_param !== '') {
    $targetSlug = strtolower(trim($name_param));
    try {
        $stmt = $pdo->query("SELECT id, name FROM candidates WHERE deleted_at IS NULL ORDER BY id DESC");
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($candidates as $row) {
            $rowSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $row['name']), '-'));
            if ($rowSlug === $targetSlug) {
                $cand_id = $row['id'];
                break;
            }
        }
    } catch (\Throwable $e) {
        // Fallback silently
    }
}

$page_title = "Candidate Profile";
$page_subtitle = "Application profile, answers, and documents.";
include __DIR__ . '/../partials/hr/header.php';
include __DIR__ . '/../partials/hr/sidebar.php';
?>

<input type="hidden" id="currentCandidateId" value="<?= htmlspecialchars($cand_id) ?>">

<div class="cand-v2-container">
    <!-- Header Action Bar (Top) -->
    <div class="flex-between mb-24 px-8">
        <div class="flex-center gap-16">

            <a href="<?= View::url('recruitment') ?>" class="action-btn no-bg border" title="Back to Candidate Pool">
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
            <!-- Duplicate Warning Integration -->
            <div id="duplicateWarning" class="cand-v2-card hidden mb-24" style="background: #fff8f6; border: 1px solid #fecdd3; padding: 20px;">
                <div class="flex-center justify-start gap-10 mb-8">
                    <div style="background: #ffe4e6; color: #e11d48; width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="alert-triangle" size="18"></i>
                    </div>
                    <div class="font-14 font-700 m-0" style="color: #9f1239;">Potential Duplicate</div>
                </div>
                <div class="font-12 leading-snug mb-8" id="duplicateText" style="color: #be123c; opacity: 0.9;">Matches another candidate's information.</div>
                <a href="#" id="duplicateOriginalLink" class="font-12 font-700 text-danger hover-underline inline-block" style="color: #e11d48; text-decoration: underline;">
                    View Original Candidate &rarr;
                </a>
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
                    <label class="admin-form-label">Interview Type *</label>
                    <select id="scheduleInterviewType" class="form-control bg-white-input">
                        <option value="Onsite">Onsite Interview</option>
                        <option value="Online">Online Interview</option>
                    </select>
                </div>
                <div class="form-group mb-20">
                    <label class="admin-form-label">Interview Date *</label>
                    <input type="date" id="scheduleInterviewDate" class="form-control bg-white-input" required>
                </div>
                <div class="form-group mb-20">
                    <label class="admin-form-label">Interview Time *</label>
                    <input type="time" id="scheduleInterviewTime" class="form-control bg-white-input" required>
                </div>
                <div class="form-group mb-20">
                    <label class="admin-form-label">Interview Notes / Feedback</label>
                    <textarea id="scheduleInterviewFeedback" class="form-control bg-white-input" rows="3"
                        placeholder="Add any specific notes or initial feedback..."></textarea>
                </div>
                <div class="form-group mb-0 flex-center gap-8 justify-content-start">
                    <input type="checkbox" id="scheduleInterviewSendEmail" class="form-checkbox" checked style="width: 16px; height: 16px; accent-color: var(--primary);">
                    <label for="scheduleInterviewSendEmail" class="font-13 cursor-pointer m-0">Send email notification to candidate</label>
                </div>
            </form>
        </div>
        <div class="modal-footer flex-end gap-12 p-30 border-top-0">
            <button type="submit" form="scheduleInterviewForm" class="btn-primary px-30">Save Schedule</button>
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

                <div id="statusShortlistFields" class="hidden mb-20">
                    <p class="font-12 text-light mb-12">Schedule the second-round interview — this date and time will be sent in the email.</p>
                    <div class="form-group mb-16">
                        <label class="admin-form-label">Interview Type *</label>
                        <select id="statusInterviewType" class="form-control bg-white-input">
                            <option value="Onsite">Onsite Interview</option>
                            <option value="Online">Online Interview</option>
                        </select>
                    </div>
                    <div class="form-group mb-16">
                        <label class="admin-form-label">Interview Date *</label>
                        <input type="date" id="statusInterviewDate" class="form-control bg-white-input">
                    </div>
                    <div class="form-group mb-0">
                        <label class="admin-form-label">Interview Time *</label>
                        <input type="time" id="statusInterviewTime" class="form-control bg-white-input">
                    </div>
                </div>

                <div id="statusHiredFields" class="hidden mb-20">
                    <p class="font-12 text-light mb-12">Set joining date and shift — reporting time comes from the selected shift, not the interview.</p>
                    <div class="form-group mb-16">
                        <label class="admin-form-label">Joining Date *</label>
                        <input type="date" id="statusJoiningDate" class="form-control bg-white-input">
                    </div>
                    <div class="form-group mb-0">
                        <label class="admin-form-label">Assigned Shift / Reporting Time *</label>
                        <select id="statusShiftId" class="form-control bg-white-input">
                            <option value="">Select shift</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-20">
                    <label class="admin-form-label">Recruiter Feedback / Evaluation *</label>
                    <textarea id="statusFeedback" class="form-control bg-white-input" rows="4"
                        placeholder="Please provide a detailed evaluation or reason for this status change..."
                        required></textarea>
                </div>
                <div class="form-group mb-0 flex-center gap-8 justify-content-start" id="statusSendEmailGroup">
                    <input type="checkbox" id="statusSendEmail" class="form-checkbox" checked style="width: 16px; height: 16px; accent-color: var(--primary);">
                    <label for="statusSendEmail" class="font-13 cursor-pointer m-0">Send email notification to candidate</label>
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

<script src="<?= \App\Core\View::asset('js/hr/candidate-detail.js?v=6') ?>"></script>
<?php include __DIR__ . '/../partials/hr/footer.php'; ?>
