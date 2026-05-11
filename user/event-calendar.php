<?php 
$page_title = "Event Calendar";
$page_subtitle = "Browse company events, meetings, and deadlines in one calendar.";
include 'includes/header.php'; 
?>

<!-- Add Calendar Styles -->
<link rel="stylesheet" href="assets/css/calendar.css">

<?php include 'includes/sidebar.php'; ?>

<!-- Page Action Area -->
<!-- (Add New Event modal removed) -->

<div class="calendar-wrapper">
    <!-- Main Calendar Area -->
    <div class="calendar-main">
        <div class="calendar-header">
            <div class="calendar-nav">
                <h3 class="font-18 font-700 m-0" id="monthYear">March 2026</h3>
                <div class="btn-group">
                    <button type="button" class="action-btn" id="prevMonth" aria-label="Previous month"><i data-lucide="chevron-left" size="20"></i></button>
                    <button type="button" class="btn-primary no-bg border" id="todayBtn">Today</button>
                    <button type="button" class="action-btn" id="nextMonth" aria-label="Next month"><i data-lucide="chevron-right" size="20"></i></button>
                </div>
            </div>
        </div>

        <div class="calendar-grid-header">
            <div class="calendar-grid" style="grid-template-rows: auto;">
                <div class="calendar-day-label">Sun</div>
                <div class="calendar-day-label">Mon</div>
                <div class="calendar-day-label">Tue</div>
                <div class="calendar-day-label">Wed</div>
                <div class="calendar-day-label">Thu</div>
                <div class="calendar-day-label">Fri</div>
                <div class="calendar-day-label">Sat</div>
            </div>
        </div>
        
        <div class="calendar-grid custom-scrollbar" id="calendarGrid">
            <!-- Days will be injected by JS -->
        </div>
    </div>

    <!-- Filter Sidebar -->
    <div class="calendar-sidebar">
        <div class="filter-card">
            <h4 class="filter-title"><i data-lucide="search" size="18"></i> Search Events</h4>
            <div class="search-box">
                <input type="text" id="eventSearch" placeholder="Search by title..." class="form-control">
            </div>
        </div>

        <div class="filter-card">
            <h4 class="filter-title"><i data-lucide="layers" size="18"></i> Categories</h4>
            <div class="filter-group" id="categoryFilters">
                <div class="filter-option" data-category="Meeting">
                    <span class="color-dot" style="background: #3b82f6;"></span>
                    <span>Meetings</span>
                </div>
                <div class="filter-option" data-category="Holiday">
                    <span class="color-dot" style="background: #ef4444;"></span>
                    <span>Holidays</span>
                </div>
                <div class="filter-option" data-category="Celebration">
                    <span class="color-dot" style="background: #10b981;"></span>
                    <span>Celebration</span>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <h4 class="filter-title"><i data-lucide="history" size="18"></i> Recent Activity</h4>
            <div class="activity-list" id="recentActivity">
                <p class="text-light font-12">No recent changes tracked.</p>
            </div>
        </div>
    </div>
</div>

<!-- Event Detail Modal -->
<div class="modal-overlay" id="eventDetailModal">
    <div class="modal-content premium">
        <div class="modal-header">
            <div class="flex-center gap-12">
                <div class="type-icon-box primary">
                    <i data-lucide="calendar-days" size="20"></i>
                </div>
                <div>
                    <h3 id="detailTitle">Event Title</h3>
                    <p class="font-12 text-light m-0" id="detailCategoryText">Meeting Details</p>
                </div>
            </div>
            <div class="flex-center gap-16">
                <span class="badge" id="detailCategory">Meeting</span>
                <button class="icon-btn" onclick="closeModal('eventDetailModal')"><i data-lucide="x"
                        size="20"></i></button>
            </div>
        </div>

        <div class="modal-body p-30">
            <div class="attendance-details-grid mb-24" style="grid-template-columns: 1fr 1fr;">
                <div class="detail-item">
                    <span class="label">Department</span>
                    <span class="value font-13" id="detailDept">Engineering</span>
                </div>
                <div class="detail-item text-right">
                    <span class="label">Date & Time</span>
                    <span class="value font-13" id="detailDateTime">March 25, 2026 at 10:00 AM</span>
                </div>
                <div class="detail-item pt-12">
                    <span class="label">Created By</span>
                    <span class="value font-13" id="detailCreatedBy">Admin User</span>
                </div>
                <div class="detail-item text-right pt-12">
                    <span class="label">Last Updated</span>
                    <span class="value font-13" id="detailUpdatedAt">2 hours ago</span>
                </div>
                <div class="detail-item pt-12">
                    <span class="label">Visibility</span>
                    <span class="value font-13" id="detailVisibility">Visible in Account</span>
                </div>
            </div>

            <div class="detail-section mb-0">
                <label class="admin-form-label">Description</label>
                <div class="p-16 rounded-12 border font-14 text-dark leading-relaxed" id="detailDesc">
                    No description provided.
                </div>
            </div>
        </div>

        <div class="modal-footer flex-end p-30 border-top-0">
            <button type="button" class="btn-primary no-bg border text-light"
                onclick="closeModal('eventDetailModal')">Close</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="dayEventsModal">
    <div class="modal-content premium" style="max-width: 450px;">
        <div class="modal-header">
            <div class="flex-center gap-12">
                <div class="type-icon-box primary">
                    <i data-lucide="calendar" size="20"></i>
                </div>
                <div>
                    <h3 id="dayModalTitle">Events on March 25</h3>
                    <p class="font-12 text-light m-0">Daily Schedule</p>
                </div>
            </div>
            <button class="icon-btn" onclick="closeModal('dayEventsModal')"><i data-lucide="x" size="20"></i></button>
        </div>
        <div class="modal-body p-20">
            <div id="dayEventsList" class="flex-column gap-12">
                <!-- Events will be populated here -->
            </div>
        </div>
    </div>
</div>

<script src="assets/js/calendar.js"></script>
<?php include 'includes/footer.php'; ?>
