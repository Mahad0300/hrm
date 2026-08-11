<?php
$page_title = "Interview Calendar";
$page_subtitle = "Scheduled interviews by date — click a slot to open candidate profile.";
include __DIR__ . '/../partials/hr/header.php';
?>

<!-- Add Calendar Styles -->
<link rel="stylesheet" href="<?= \App\Core\View::asset('css/hr/calendar.css') ?>">

<?php
include __DIR__ . '/../partials/hr/sidebar.php';
?>

<div class="card p-24 interview-calendar-card">
    <div class="interview-cal-toolbar flex-between flex-wrap gap-16 mb-24">
        <h3 class="font-18 font-700 m-0" id="interviewCalMonthTitle">March 2026</h3>
        <div class="flex-center gap-8">
            <button type="button" class="action-btn" id="interviewCalPrev" title="Previous month"
                aria-label="Previous month">
                <i data-lucide="chevron-left" size="20"></i>
            </button>
            <button type="button" class="btn-primary no-bg border font-13 px-16" id="interviewCalToday">Today</button>
            <button type="button" class="action-btn" id="interviewCalNext" title="Next month" aria-label="Next month">
                <i data-lucide="chevron-right" size="20"></i>
            </button>
        </div>
    </div>

    <div class="interview-cal-weekdays">
        <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
    </div>
    <div class="interview-cal-grid" id="interviewCalGrid"></div>
</div>

<!-- Modal for showing all interviews of a day -->
<div class="modal-overlay" id="dayInterviewsModal">
    <div class="modal-content premium" style="max-width: 450px;">
        <div class="modal-header">
            <div class="flex-center gap-12">
                <div class="type-icon-box primary">
                    <i data-lucide="calendar" size="20"></i>
                </div>
                <div>
                    <h3 id="dayModalTitle">Interviews on March 25</h3>
                    <p class="font-12 text-light m-0">Daily Schedule</p>
                </div>
            </div>
            <button class="icon-btn" onclick="closeModal('dayInterviewsModal')"><i data-lucide="x"
                    size="20"></i></button>
        </div>
        <div class="modal-body p-20">
            <div id="dayInterviewsList" class="flex-column gap-12">
                <!-- Interviews will be populated here -->
            </div>
        </div>
    </div>
</div>

<!-- Reschedule Interview Modal -->
<div class="modal-overlay" id="rescheduleInterviewModal">
    <div class="modal-content premium wide-sm">
        <div class="modal-header">
            <div>
                <h3>Reschedule Interview</h3>
                <p class="font-12 text-light mt-1 m-0">Update date and time for this interview</p>
            </div>
            <button type="button" class="icon-btn" onclick="closeModal('rescheduleInterviewModal')"><i data-lucide="x"
                    size="20"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="rescheduleForm">
                <input type="hidden" id="reschedId">
                <input type="hidden" id="reschedCandId">

                <div class="form-group mb-20">
                    <label class="admin-form-label">Interview Type *</label>
                    <select id="reschedType" class="form-control bg-white-input">
                        <option value="Onsite">Onsite Interview</option>
                        <option value="Online">Online Interview</option>
                    </select>
                </div>
                <div class="form-group mb-20">
                    <label class="admin-form-label">Interview Date *</label>
                    <input type="date" id="reschedDate" class="form-control bg-white-input" required>
                </div>
                <div class="form-group mb-20">
                    <label class="admin-form-label">Interview Time *</label>
                    <input type="time" id="reschedTime" class="form-control bg-white-input" required>
                </div>
                <div class="form-group mb-20">
                    <label class="admin-form-label">Interview Notes / Feedback</label>
                    <textarea id="reschedFeedback" class="form-control bg-white-input" rows="3"
                        placeholder="Add any specific notes or update evaluation..."></textarea>
                </div>
                <div class="form-group mb-0 flex-center gap-8 justify-content-start">
                    <input type="checkbox" id="reschedSendEmail" class="form-checkbox" checked style="width: 16px; height: 16px; accent-color: var(--primary);">
                    <label for="reschedSendEmail" class="font-13 cursor-pointer m-0">Send email notification to candidate</label>
                </div>
            </form>
        </div>
        <div class="modal-footer flex-end gap-12 p-30 border-top-0">
            <button type="button" class="btn-primary no-bg border text-light px-24"
                onclick="closeModal('rescheduleInterviewModal')">Cancel</button>
            <button type="submit" form="rescheduleForm" class="btn-primary px-30">Update Schedule</button>
        </div>
    </div>
</div>

<script src="<?= \App\Core\View::asset('js/shared/interviews-calendar.js') ?>"></script>
<?php include __DIR__ . '/../partials/hr/footer.php'; ?>
