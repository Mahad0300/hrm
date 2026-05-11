<?php
$page_title = "Interview Calendar";
$page_subtitle = "Scheduled interviews by date — click a slot to open candidate profile.";
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="card p-24 interview-calendar-card">
    <div class="interview-cal-toolbar flex-between flex-wrap gap-16 mb-24">
        <h3 class="font-18 font-700 m-0" id="interviewCalMonthTitle">March 2026</h3>
        <div class="flex-center gap-8">
            <button type="button" class="action-btn" id="interviewCalPrev" title="Previous month" aria-label="Previous month">
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

<script src="assets/js/interviews-calendar.js"></script>
<?php include 'includes/footer.php'; ?>
