<?php 
$page_title = "Event Calendar";
$page_subtitle = "View and manage company events, meetings, and deadlines.";
include 'includes/header.php'; 
?>

<!-- Add Calendar Styles -->
<link rel="stylesheet" href="assets/css/calendar.css">

<?php include 'includes/sidebar.php'; ?>

<!-- Page Action Area -->
<div class="page-action-area">
    <button class="btn-primary" onclick="openEventModal()">
        <i data-lucide="plus"></i>
        <span>Add New Event</span>
    </button>
</div>

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
            <div class="calendar-actions">
                <div class="btn-group">
                    <button type="button" class="btn-active view-tab" data-view="month">Month</button>
                    <button type="button" class="view-tab" data-view="week">Week</button>
                    <button type="button" class="view-tab" data-view="day">Day</button>
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
                <div class="filter-option" data-category="Workshop">
                    <span class="color-dot" style="background: #10b981;"></span>
                    <span>Workshops</span>
                </div>
                <div class="filter-option" data-category="Others">
                    <span class="color-dot" style="background: #6b7280;"></span>
                    <span>Others</span>
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

<!-- Event Modal (Create/Edit) -->
<div class="modal-overlay" id="eventModal">
    <div class="modal-content premium">
        <div class="modal-header">
            <div class="flex-center gap-12">
                <div class="type-icon-box primary">
                    <i data-lucide="calendar" size="20"></i>
                </div>
                <div>
                    <h3 id="modalTitle">Create New Event</h3>
                    <p class="font-12 text-light m-0" id="modalSubtitle">Schedule and manage company events</p>
                </div>
            </div>
            <button class="icon-btn" onclick="closeModal('eventModal')" aria-label="Close"><i data-lucide="x" size="20"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="eventForm">
                <input type="hidden" id="eventId">
                
                <div class="form-group mb-20">
                    <label class="admin-form-label">Event Title *</label>
                    <input type="text" class="form-control bg-white-input" id="eventTitle" placeholder="e.g. Quarterly Review" required>
                </div>
                
                <div class="form-grid-2">
                    <div class="form-group mb-20">
                        <label class="admin-form-label">Category *</label>
                        <select class="form-control bg-white-input" id="eventCategory" required>
                            <option value="Meeting">Meeting</option>
                            <option value="Holiday">Holiday</option>
                            <option value="Workshop">Workshop</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="form-group mb-20">
                        <label class="admin-form-label">Department *</label>
                        <select class="form-control bg-white-input" id="eventDept" required>
                            <option value="All">All Departments</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Design">Design</option>
                            <option value="HR">Human Resources</option>
                            <option value="Sales">Sales & Marketing</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid-2">
                    <div class="form-group mb-20">
                        <label class="admin-form-label">Date *</label>
                        <input type="date" class="form-control bg-white-input" id="eventDate" required>
                    </div>
                    <div class="form-group mb-20">
                        <label class="admin-form-label">Time *</label>
                        <input type="time" class="form-control bg-white-input" id="eventTime" required>
                    </div>
                </div>
                
                <div class="form-group mb-20">
                    <label class="admin-form-label">Description / Notes</label>
                    <textarea class="form-control bg-white-input" id="eventDesc" rows="3" style="height: auto;" placeholder="Add any additional details or notes here..."></textarea>
                </div>

                <div class="form-group mb-0">
                    <label class="flex-center gap-10 cursor-pointer">
                        <input type="checkbox" id="eventShowInAccount" style="width: 18px; height: 18px; cursor: pointer;">
                        <span class="font-13 text-dark font-600">Show this event in Announcement</span>
                    </label>
                </div>
            </form>
        </div>
        <div class="modal-footer flex-between p-30 border-top-0">
            <div>
                <!-- Deleted button from here -->
            </div>
            <div class="flex-center gap-12">
                <button type="button" class="btn-primary no-bg border text-light" onclick="closeModal('eventModal')">Cancel</button>
                <button type="submit" form="eventForm" class="btn-primary px-30">Save Event</button>
            </div>
        </div>
    </div>
</div>

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
                <button class="icon-btn" onclick="closeModal('eventDetailModal')"><i data-lucide="x" size="20"></i></button>
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
        
        <div class="modal-footer flex-between p-30 border-top-0">
            <button type="button" class="btn-primary danger no-bg border" id="deleteEventBtnDetail">
                <i data-lucide="trash-2" size="18"></i>
                <span>Delete Event</span>
            </button>
            <button type="button" class="btn-primary px-30" id="editEventBtn">
                <i data-lucide="edit-2" size="16"></i>
                <span>Edit Event Details</span>
            </button>
        </div>
    </div>
</div>

<script src="assets/js/calendar.js"></script>
<?php include 'includes/footer.php'; ?>
