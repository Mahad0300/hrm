<?php 
$page_title = "Company Announcements";
$page_subtitle = "Stay updated with the latest news and broadcasts.";
include 'includes/header.php'; 
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Page Action Area -->
<div class="page-action-area">
    <button class="btn-primary" onclick="openModal('createAnnouncementModal')">
        <i data-lucide="plus"></i>
        <span>Push New Announcement</span>
    </button>
</div>

<!-- Filters Card -->
<div class="card p-24 mb-24">
    <div class="filter-grid-3">
        <div class="modal-search">
            <i data-lucide="search" class="input-icon"></i>
            <input type="text" class="form-control bg-white-input" placeholder="Search announcements...">
        </div>
        <div class="filter-item">
            <select class="form-control bg-white-input">
                <option value="">Category (All)</option>
                <option>Corporate</option>
                <option>IT Department</option>
                <option>HR News</option>
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control bg-white-input">
                <option value="">Visibility (All)</option>
                <option>Everyone</option>
                <option>Specific Dept</option>
            </select>
        </div>
    </div>
</div>

<!-- Announcements List -->
<div class="grid-cards">
    <!-- Announcement 1 -->
    <div class="announcement-card">
        <div class="card-shape shape-1"></div>
        <div class="card-shape shape-2"></div>
        <div class="announcement-content">
            <div class="flex-between mb-15">
                <span class="card-category cat-corporate">Corporate</span>
                <span class="font-12 text-light">2 hours ago</span>
            </div>
            <h3 class="mb-12">Q3 Town Hall Meeting</h3>
            <p class="font-14 text-light mb-20">Join us for the quarterly town hall meeting where we'll discuss our achievements and future strategy. Attendance is mandatory for all departments.</p>
        </div>
        <div class="announcement-footer">
            <div class="flex-center gap-10">
                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150" class="icon-box-sm">
                <span class="font-13 font-500">James Wilson</span>
            </div>
            <div class="flex-center gap-10">
                <button class="action-btn info" title="View Details" onclick="viewAnnouncementDetail('ANN-1')"><i data-lucide="eye" size="14"></i></button>
                <button class="action-btn primary" title="Edit Announcement" onclick="openEditAnnouncementModal('ANN-1')"><i data-lucide="edit-2" size="14"></i></button>
                <button class="action-btn danger" title="Delete Announcement" onclick="deleteAnnouncement('ANN-1')"><i data-lucide="trash-2" size="14"></i></button>
            </div>
        </div>
    </div>
    
    <!-- Announcement 2 -->
    <div class="announcement-card it-dept">
        <div class="card-shape shape-1"></div>
        <div class="card-shape shape-2"></div>
        <div class="announcement-content">
            <div class="flex-between mb-15">
                <span class="card-category cat-it">IT Department</span>
                <span class="font-12 text-light">Yesterday</span>
            </div>
            <h3 class="mb-12">Scheduled System Maintenance</h3>
            <p class="font-14 text-light mb-20">The internal servers will be under maintenance this Friday from 10 PM to 2 AM. Please save your work and log out of all systems before the scheduled time.</p>
        </div>
        <div class="announcement-footer">
            <div class="flex-center gap-10">
                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150" class="icon-box-sm">
                <span class="font-13 font-500">Michael Scott</span>
            </div>
            <div class="flex-center gap-10">
                <button class="action-btn info" title="View Details" onclick="viewAnnouncementDetail('ANN-2')"><i data-lucide="eye" size="14"></i></button>
                <button class="action-btn primary" title="Edit Announcement" onclick="openEditAnnouncementModal('ANN-2')"><i data-lucide="edit-2" size="14"></i></button>
                <button class="action-btn danger" title="Delete Announcement" onclick="deleteAnnouncement('ANN-2')"><i data-lucide="trash-2" size="14"></i></button>
            </div>
        </div>
    </div>

      <!-- Announcement 1 -->
    <div class="announcement-card">
        <div class="card-shape shape-1"></div>
        <div class="card-shape shape-2"></div>
        <div class="announcement-content">
            <div class="flex-between mb-15">
                <span class="card-category cat-corporate">Corporate</span>
                <span class="font-12 text-light">2 hours ago</span>
            </div>
            <h3 class="mb-12">Q3 Town Hall Meeting</h3>
            <p class="font-14 text-light mb-20">Join us for the quarterly town hall meeting where we'll discuss our achievements and future strategy. Attendance is mandatory for all departments.</p>
        </div>
        <div class="announcement-footer">
            <div class="flex-center gap-10">
                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150" class="icon-box-sm">
                <span class="font-13 font-500">James Wilson</span>
            </div>
            <div class="flex-center gap-10">
                <button class="action-btn info" title="View Details" onclick="viewAnnouncementDetail('ANN-1')"><i data-lucide="eye" size="14"></i></button>
                <button class="action-btn primary" title="Edit Announcement" onclick="openEditAnnouncementModal('ANN-1')"><i data-lucide="edit-2" size="14"></i></button>
                <button class="action-btn danger" title="Delete Announcement" onclick="deleteAnnouncement('ANN-1')"><i data-lucide="trash-2" size="14"></i></button>
            </div>
        </div>
    </div>
    
    <!-- Announcement 2 -->
    <div class="announcement-card it-dept">
        <div class="card-shape shape-1"></div>
        <div class="card-shape shape-2"></div>
        <div class="announcement-content">
            <div class="flex-between mb-15">
                <span class="card-category cat-it">IT Department</span>
                <span class="font-12 text-light">Yesterday</span>
            </div>
            <h3 class="mb-12">Scheduled System Maintenance</h3>
            <p class="font-14 text-light mb-20">The internal servers will be under maintenance this Friday from 10 PM to 2 AM. Please save your work and log out of all systems before the scheduled time.</p>
        </div>
        <div class="announcement-footer">
            <div class="flex-center gap-10">
                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150" class="icon-box-sm">
                <span class="font-13 font-500">Michael Scott</span>
            </div>
            <div class="flex-center gap-10">
                <button class="action-btn info" title="View Details" onclick="viewAnnouncementDetail('ANN-2')"><i data-lucide="eye" size="14"></i></button>
                <button class="action-btn primary" title="Edit Announcement" onclick="openEditAnnouncementModal('ANN-2')"><i data-lucide="edit-2" size="14"></i></button>
                <button class="action-btn danger" title="Delete Announcement" onclick="deleteAnnouncement('ANN-2')"><i data-lucide="trash-2" size="14"></i></button>
            </div>
        </div>
    </div>

      <!-- Announcement 1 -->
    <div class="announcement-card">
        <div class="card-shape shape-1"></div>
        <div class="card-shape shape-2"></div>
        <div class="announcement-content">
            <div class="flex-between mb-15">
                <span class="card-category cat-corporate">Corporate</span>
                <span class="font-12 text-light">2 hours ago</span>
            </div>
            <h3 class="mb-12">Q3 Town Hall Meeting</h3>
            <p class="font-14 text-light mb-20">Join us for the quarterly town hall meeting where we'll discuss our achievements and future strategy. Attendance is mandatory for all departments.</p>
        </div>
        <div class="announcement-footer">
            <div class="flex-center gap-10">
                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150" class="icon-box-sm">
                <span class="font-13 font-500">James Wilson</span>
            </div>
            <div class="flex-center gap-10">
                <button class="action-btn info" title="View Details" onclick="viewAnnouncementDetail('ANN-1')"><i data-lucide="eye" size="14"></i></button>
                <button class="action-btn primary" title="Edit Announcement" onclick="openEditAnnouncementModal('ANN-1')"><i data-lucide="edit-2" size="14"></i></button>
                <button class="action-btn danger" title="Delete Announcement" onclick="deleteAnnouncement('ANN-1')"><i data-lucide="trash-2" size="14"></i></button>
            </div>
        </div>
    </div>
    
    <!-- Announcement 2 -->
    <div class="announcement-card it-dept">
        <div class="card-shape shape-1"></div>
        <div class="card-shape shape-2"></div>
        <div class="announcement-content">
            <div class="flex-between mb-15">
                <span class="card-category cat-it">IT Department</span>
                <span class="font-12 text-light">Yesterday</span>
            </div>
            <h3 class="mb-12">Scheduled System Maintenance</h3>
            <p class="font-14 text-light mb-20">The internal servers will be under maintenance this Friday from 10 PM to 2 AM. Please save your work and log out of all systems before the scheduled time.</p>
        </div>
        <div class="announcement-footer">
            <div class="flex-center gap-10">
                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150" class="icon-box-sm">
                <span class="font-13 font-500">Michael Scott</span>
            </div>
            <div class="flex-center gap-10">
                <button class="action-btn info" title="View Details" onclick="viewAnnouncementDetail('ANN-2')"><i data-lucide="eye" size="14"></i></button>
                <button class="action-btn primary" title="Edit Announcement" onclick="openEditAnnouncementModal('ANN-2')"><i data-lucide="edit-2" size="14"></i></button>
                <button class="action-btn danger" title="Delete Announcement" onclick="deleteAnnouncement('ANN-2')"><i data-lucide="trash-2" size="14"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Create Announcement Modal -->
<div class="modal-overlay" id="createAnnouncementModal">
    <div class="modal-content wide-md">
        <div class="modal-header">
            <div>
                <h3 class="mb-4">Create New Announcement</h3>
                <p class="font-12 text-light">Broadcast important updates or celebrations to your team.</p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="announcementForm">
                <div class="mb-24">
                    <label class="admin-form-label">Announcement Type</label>
                    <div class="ann-type-grid" id="annTypeSelection">
                        <div class="ann-type-card active" data-type="IMPORTANT">
                            <span class="ann-type-emoji">🚨</span>
                            <span>Important</span>
                        </div>
                        <div class="ann-type-card" data-type="CELEBRATION">
                            <span class="ann-type-emoji">🎂</span>
                            <span>Celebration</span>
                        </div>
                        <div class="ann-type-card" data-type="UPDATE">
                            <span class="ann-type-emoji">📢</span>
                            <span>Update</span>
                        </div>
                        <div class="ann-type-card" data-type="HOLIDAY">
                            <span class="ann-type-emoji">📅</span>
                            <span>Holiday</span>
                        </div>
                    </div>
                    <input type="hidden" id="selectedAnnType" value="IMPORTANT">
                </div>

                <div class="form-group mb-24">
                    <label class="admin-form-label">Announcement Title</label>
                    <input type="text" id="add_ann_title" class="form-control bg-white-input" placeholder="e.g. Q3 Strategic Planning Session">
                </div>

                <div class="mb-24">
                    <label class="admin-form-label">Target Audience / Department</label>
                    <div class="category-selection-grid" id="deptSelection">
                        <div class="category-pill active" data-dept="Everyone">Everyone</div>
                        <div class="category-pill" data-dept="Engineering">Engineering</div>
                        <div class="category-pill" data-dept="HR">HR</div>
                        <div class="category-pill" data-dept="Sales">Sales</div>
                        <div class="category-pill" data-dept="Marketing">Marketing</div>
                        <div class="category-pill" data-dept="Finance">Finance</div>
                    </div>
                    <input type="hidden" id="selectedAnnDept" value="Everyone">
                </div>

                <div class="mb-24">
                    <label class="admin-form-label">Description</label>
                    <div class="rich-text-container">
                        <div class="rich-text-toolbar">
                            <button type="button" class="toolbar-btn" title="Bold"><i data-lucide="bold"></i></button>
                            <button type="button" class="toolbar-btn" title="Italic"><i data-lucide="italic"></i></button>
                            <button type="button" class="toolbar-btn" title="Underline"><i data-lucide="underline"></i></button>
                            <div class="toolbar-divider"></div>
                            <button type="button" class="toolbar-btn" title="InsertUnorderedList"><i data-lucide="list"></i></button>
                            <button type="button" class="toolbar-btn" title="InsertOrderedList"><i data-lucide="list-ordered"></i></button>
                            <div class="toolbar-divider"></div>
                            <button type="button" class="toolbar-btn" title="CreateLink"><i data-lucide="link"></i></button>
                        </div>
                        <div class="rich-text-editor" contenteditable="true" data-placeholder="Write your announcement details here..."></div>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="admin-form-label">Start Date</label>
                        <div class="input-with-icon">
                            <i data-lucide="calendar" class="input-icon"></i>
                            <input type="date" id="add_ann_start" class="form-control bg-white-input pl-45" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">End Date</label>
                        <div class="input-with-icon">
                            <i data-lucide="calendar-check" class="input-icon"></i>
                            <input type="date" id="add_ann_end" class="form-control bg-white-input pl-45" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <div class="footer-actions">
                <button type="submit" form="announcementForm" class="btn-primary">
                    <i data-lucide="megaphone" size="16"></i>
                    Broadcast Announcement
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div class="modal-overlay" id="editAnnouncementModal">
    <div class="modal-content wide-md">
        <div class="modal-header">
            <div>
                <h3 class="mb-4">Edit Announcement</h3>
                <p class="font-12 text-light">Modify the details of your broadcasted announcement.</p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="editAnnouncementForm">
                <div class="mb-24">
                    <label class="admin-form-label">Announcement Type</label>
                    <div class="ann-type-grid" id="editAnnTypeSelection">
                        <div class="ann-type-card" data-type="IMPORTANT">
                            <span class="ann-type-emoji">🚨</span>
                            <span>Important</span>
                        </div>
                        <div class="ann-type-card" data-type="CELEBRATION">
                            <span class="ann-type-emoji">🎂</span>
                            <span>Celebration</span>
                        </div>
                        <div class="ann-type-card" data-type="UPDATE">
                            <span class="ann-type-emoji">📢</span>
                            <span>Update</span>
                        </div>
                        <div class="ann-type-card" data-type="HOLIDAY">
                            <span class="ann-type-emoji">📅</span>
                            <span>Holiday</span>
                        </div>
                    </div>
                    <input type="hidden" id="edit_selectedAnnType" value="">
                </div>

                <div class="form-group mb-24">
                    <label class="admin-form-label">Announcement Title</label>
                    <input type="text" id="edit_ann_title" class="form-control bg-white-input" placeholder="e.g. Q3 Strategic Planning Session">
                </div>

                <div class="mb-24">
                    <label class="admin-form-label">Target Audience / Department</label>
                    <div class="category-selection-grid" id="editDeptSelection">
                        <div class="category-pill" data-dept="Everyone">Everyone</div>
                        <div class="category-pill" data-dept="Engineering">Engineering</div>
                        <div class="category-pill" data-dept="HR">HR</div>
                        <div class="category-pill" data-dept="Sales">Sales</div>
                        <div class="category-pill" data-dept="Marketing">Marketing</div>
                        <div class="category-pill" data-dept="Finance">Finance</div>
                    </div>
                    <input type="hidden" id="edit_selectedAnnDept" value="">
                </div>

                <div class="mb-24">
                    <label class="admin-form-label">Description</label>
                    <div class="rich-text-container">
                        <div class="rich-text-toolbar">
                            <button type="button" class="toolbar-btn" title="Bold"><i data-lucide="bold"></i></button>
                            <button type="button" class="toolbar-btn" title="Italic"><i data-lucide="italic"></i></button>
                            <button type="button" class="toolbar-btn" title="Underline"><i data-lucide="underline"></i></button>
                            <div class="toolbar-divider"></div>
                            <button type="button" class="toolbar-btn" title="InsertUnorderedList"><i data-lucide="list"></i></button>
                            <button type="button" class="toolbar-btn" title="InsertOrderedList"><i data-lucide="list-ordered"></i></button>
                            <div class="toolbar-divider"></div>
                            <button type="button" class="toolbar-btn" title="CreateLink"><i data-lucide="link"></i></button>
                        </div>
                        <div class="rich-text-editor" id="edit_ann_rich_desc" contenteditable="true" data-placeholder="Write your announcement details here..."></div>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="admin-form-label">Start Date</label>
                        <div class="input-with-icon">
                            <i data-lucide="calendar" class="input-icon"></i>
                            <input type="date" id="edit_ann_start" class="form-control bg-white-input pl-45">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">End Date</label>
                        <div class="input-with-icon">
                            <i data-lucide="calendar-check" class="input-icon"></i>
                            <input type="date" id="edit_ann_end" class="form-control bg-white-input pl-45">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <div class="footer-actions">
                <button type="submit" form="editAnnouncementForm" class="btn-primary">
                    <i data-lucide="check" size="16"></i>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Announcement Detail Modal -->
<div class="modal-overlay" id="viewAnnouncementModal">
    <div class="modal-content wide-md">
        <div class="modal-header">
            <div>
                <h3 id="view_ann_title" class="mb-4">Announcement Detail</h3>
                <div class="flex-center gap-10">
                    <span id="view_ann_type_badge" class="badge"></span>
                    <span class="font-12 text-light" id="view_ann_date_range"></span>
                </div>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body p-30">
            <div class="detail-view-container">
                <div class="mb-30">
                    <label class="admin-form-label">Target Audience</label>
                    <div class="category-selection-grid" id="view_ann_depts">
                        <!-- Filled by JS -->
                    </div>
                </div>

                <div class="announcement-detail-content">
                    <label class="admin-form-label">Announcement Description</label>
                    <div id="view_ann_desc" class="rich-content-view">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="closeModal('viewAnnouncementModal')">Close View</button>
        </div>
    </div>
</div>

<!-- Announcement Logic -->
<script src="assets/js/announcements.js"></script>

<?php include 'includes/footer.php'; ?>
