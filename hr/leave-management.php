<?php
$page_title = "Leave Management";
$page_subtitle = "Review and manage employee time-off requests.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Page Action Area -->
<div class="page-action-area">
    <button type="button" class="btn-primary" id="openLeaveSettingsBtn">
        <i data-lucide="settings"></i>
        <span>Leave Settings</span>
    </button>
    <div id="allRequestsBtn">
        <button class="btn-primary" onclick="toggleLeaveView('pending')">
            <i data-lucide="clipboard-list"></i>
            <span>Request Leaves</span>
        </button>
    </div>
    <div id="backToAllBtn" class="hidden">
        <button class="btn-primary no-bg border text-light" onclick="toggleLeaveView('all')">
            <i data-lucide="arrow-left"></i>
            <span>Back to All</span>
        </button>
    </div>
</div>

<!-- Filters Card -->
<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="search-box">
            <i data-lucide="search" size="18"></i>
            <input type="text" placeholder="Search by employee name...">
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">Leave Type (All)</option>
                <option value="sick">Sick Leave</option>
                <option value="casual">Casual Leave</option>
                <option value="annual">Annual Leave</option>
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">Status (All)</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="filter-item">
            <input type="month" class="form-control" value="<?= date('Y-m') ?>">
        </div>
    </div>
</div>

<div class="card p-20 mb-24 leave-quota-summary-card" id="leaveQuotaSummaryCard">
    <p class="font-13 m-0 text-dark">
        <strong>Organization leave (per year — all employees):</strong>
        <span id="leaveQuotaSummaryText" class="text-light">—</span>
    </p>
</div>

<!-- Main Table View -->
<div id="allRequestsView">
    
    <!-- Table Tools: Per Page & Summary -->
    <div class="flex-between mb-24 px-4">
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
            <span class="font-13 text-light" id="tableSummary">Showing 1 to 10 of 10 entries</span>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>EMPLOYEE</th>
                        <th>LEAVE TYPE</th>
                        <th>DURATION</th>
                        <th class="allow-wrap">REASON</th>
                        <th>DOCUMENT</th>
                        <th>STATUS</th>
                        <th class="text-right px-30">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="leaveTableBody">
                    <!-- Table content remains same as existing -->
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Emma Williams</span>
                                    <span class="email">EM-4820</span>
                                </div>
                            </div>
                        </td>
                        <td>Sick Leave</td>
                        <td>Sep 12 - Sep 14 (3 days)</td>
                        <td class="allow-wrap">High fever and flu</td>
                        <td>
                            <button class="doc-pill doc-pill-pdf" title="View Document"
                                onclick="openLeaveDocument('assets/sample/medical-certificate-emma.pdf','pdf')">
                                <i data-lucide="file-text" size="14"></i>
                                <span>PDF</span>
                            </button>
                        </td>
                        <td><span class="badge badge-success">Approved</span></td>
                        <td class="text-right px-30">
                            <div class="btn-group justify-end">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Emma Williams','Sick Leave',3,'12 Sep 2026','14 Sep 2026','Approved as per policy. Get well soon.','assets/sample/medical-certificate-emma.pdf','pdf')"><i data-lucide="eye" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Oliver Mitchell</span>
                                    <span class="email">EM-4821</span>
                                </div>
                            </div>
                        </td>
                        <td>Casual Leave</td>
                        <td>Sep 20 - Sep 21 (2 days)</td>
                        <td class="allow-wrap">Family event</td>
                        <td>
                            <button class="doc-pill doc-pill-image" title="View Document"
                                onclick="openLeaveDocument('assets/sample/family-event-oliver.jpg','image')">
                                <i data-lucide="image" size="14"></i>
                                <span>Image</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td class="text-right px-30">
                            <div class="btn-group justify-end">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Oliver Mitchell','Casual Leave',2,'20 Sep 2026','21 Sep 2026','','assets/sample/family-event-oliver.jpg','image')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Ethan Hunt</span>
                                    <span class="email">EM-4825</span>
                                </div>
                            </div>
                        </td>
                        <td>Annual Leave</td>
                        <td>Oct 05 - Oct 10 (6 days)</td>
                        <td class="allow-wrap">Personal travel</td>
                        <td>
                            <button class="doc-pill doc-pill-pdf" title="View Document"
                                onclick="openLeaveDocument('assets/sample/travel-tickets-ethan.pdf','pdf')">
                                <i data-lucide="file-text" size="14"></i>
                                <span>PDF</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td class="text-right px-30">
                            <div class="btn-group justify-end">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Ethan Hunt','Annual Leave',6,'05 Oct 2026','10 Oct 2026','','assets/sample/travel-tickets-ethan.pdf','pdf')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Noah Smith</span>
                                    <span class="email">EM-4826</span>
                                </div>
                            </div>
                        </td>
                        <td>Sick Leave</td>
                        <td>Oct 12 - Oct 13 (2 days)</td>
                        <td class="allow-wrap">Medical checkup</td>
                        <td>
                            <button class="doc-pill doc-pill-image" title="View Document"
                                onclick="openLeaveDocument('assets/sample/medical-report-noah.jpg','image')">
                                <i data-lucide="image" size="14"></i>
                                <span>Image</span>
                            </button>
                        </td>
                        <td><span class="badge badge-danger">Rejected</span></td>
                        <td class="text-right px-30">
                            <div class="btn-group justify-end">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Noah Smith','Sick Leave',2,'12 Oct 2026','13 Oct 2026','Rejected: Please submit medical certificate.','assets/sample/medical-report-noah.jpg','image')"><i data-lucide="eye" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Sophia Chen</span>
                                    <span class="email">EM-4827</span>
                                </div>
                            </div>
                        </td>
                        <td>Casual Leave</td>
                        <td>Oct 15 - Oct 16 (2 days)</td>
                        <td class="allow-wrap">Home maintenance</td>
                        <td>
                            <button class="doc-pill doc-pill-pdf" title="View Document"
                                onclick="openLeaveDocument('assets/sample/home-repair-sophia.pdf','pdf')">
                                <i data-lucide="file-text" size="14"></i>
                                <span>PDF</span>
                            </button>
                        </td>
                        <td><span class="badge badge-success">Approved</span></td>
                        <td class="text-right px-30">
                            <div class="btn-group justify-end">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Sophia Chen','Casual Leave',2,'15 Oct 2026','16 Oct 2026','Approved.','assets/sample/home-repair-sophia.pdf','pdf')"><i data-lucide="eye" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Lucas Gray</span>
                                    <span class="email">EM-4828</span>
                                </div>
                            </div>
                        </td>
                        <td>Annual Leave</td>
                        <td>Oct 20 - Oct 25 (6 days)</td>
                        <td class="allow-wrap">Wedding attendance</td>
                        <td>
                            <button class="doc-pill doc-pill-image" title="View Document"
                                onclick="openLeaveDocument('assets/sample/wedding-card-lucas.jpg','image')">
                                <i data-lucide="image" size="14"></i>
                                <span>Image</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td class="text-right px-30">
                            <div class="btn-group justify-end">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Lucas Gray','Annual Leave',6,'20 Oct 2026','25 Oct 2026','','assets/sample/wedding-card-lucas.jpg','image')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Mia Wong</span>
                                    <span class="email">EM-4829</span>
                                </div>
                            </div>
                        </td>
                        <td>Sick Leave</td>
                        <td>Oct 28 - Oct 29 (2 days)</td>
                        <td class="allow-wrap">Fever</td>
                        <td>
                            <button class="doc-pill doc-pill-image" title="View Document"
                                onclick="openLeaveDocument('assets/sample/clinic-slip-mia.jpg','image')">
                                <i data-lucide="image" size="14"></i>
                                <span>Image</span>
                            </button>
                        </td>
                        <td><span class="badge badge-success">Approved</span></td>
                        <td class="text-right px-30">
                            <div class="btn-group justify-end">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Mia Wong','Sick Leave',2,'28 Oct 2026','29 Oct 2026','Approved. Take rest.','assets/sample/clinic-slip-mia.jpg','image')"><i data-lucide="eye" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Isabella Silva</span>
                                    <span class="email">EM-4830</span>
                                </div>
                            </div>
                        </td>
                        <td>Casual Leave</td>
                        <td>Nov 02 - Nov 03 (2 days)</td>
                        <td class="allow-wrap">Personal work</td>
                        <td>
                            <button class="doc-pill doc-pill-pdf" title="View Document"
                                onclick="openLeaveDocument('assets/sample/personal-doc-isabella.pdf','pdf')">
                                <i data-lucide="file-text" size="14"></i>
                                <span>PDF</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td class="text-right px-30">
                            <div class="btn-group justify-end">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Isabella Silva','Casual Leave',2,'02 Nov 2026','03 Nov 2026','','assets/sample/personal-doc-isabella.pdf','pdf')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1552058544-f2b08422138a?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">William John</span>
                                    <span class="email">EM-4831</span>
                                </div>
                            </div>
                        </td>
                        <td>Annual Leave</td>
                        <td>Nov 10 - Nov 15 (6 days)</td>
                        <td class="allow-wrap">Vacation</td>
                        <td>
                            <button class="doc-pill doc-pill-pdf" title="View Document"
                                onclick="openLeaveDocument('assets/sample/vacation-plan-william.pdf','pdf')">
                                <i data-lucide="file-text" size="14"></i>
                                <span>PDF</span>
                            </button>
                        </td>
                        <td><span class="badge badge-danger">Rejected</span></td>
                        <td class="text-right px-30">
                            <div class="btn-group justify-end">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('William John','Annual Leave',6,'10 Nov 2026','15 Nov 2026','Rejected: Peak period.','assets/sample/vacation-plan-william.pdf','pdf')"><i data-lucide="eye" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1552058544-f2b08422138a?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">William John</span>
                                    <span class="email">EM-4831</span>
                                </div>
                            </div>
                        </td>
                        <td>Annual Leave</td>
                        <td>Nov 10 - Nov 15 (6 days)</td>
                        <td class="allow-wrap">Vacation</td>
                        <td>
                            <button class="doc-pill doc-pill-pdf" title="View Document"
                                onclick="openLeaveDocument('assets/sample/vacation-plan-william.pdf','pdf')">
                                <i data-lucide="file-text" size="14"></i>
                                <span>PDF</span>
                            </button>
                        </td>
                        <td><span class="badge badge-danger">Rejected</span></td>
                        <td class="text-right px-30">
                            <div class="btn-group justify-end">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('William John','Annual Leave',6,'10 Nov 2026','15 Nov 2026','Rejected: Peak period. Please choose alternate dates.','assets/sample/vacation-plan-william.pdf','pdf')"><i data-lucide="eye" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="p-24 flex-between border-top">
            <span class="font-13 text-light" id="paginationInfo">Showing 1 to 10 of 10 entries</span>
            <div class="flex-center gap-8" id="paginationControls">
                <button class="action-btn" id="prevPage"><i data-lucide="chevron-left" size="16"></i></button>
                <div id="pageNumbers" class="flex-center gap-8">
                    <button class="action-btn btn-active">1</button>
                </div>
                <button class="action-btn" id="nextPage"><i data-lucide="chevron-right" size="16"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Pending Requests View (In-Page) -->
<div id="pendingRequestsView" class="hidden">
    
    <!-- Table Tools: Per Page & Summary -->
    <div class="flex-between mb-24 px-4">
        <div class="flex-center gap-10">
            <span class="font-13 text-light">Show</span>
            <select class="form-control font-13 font-600 per-page-select" id="pendingPerPageSelect">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="all">All</option>
            </select>
            <span class="font-13 text-light">entries</span>
        </div>
        <div class="text-right">
            <span class="font-13 text-light" id="pendingTableSummary">Showing 1 to 10 of 10 entries</span>
        </div>
    </div>

    <div class="flex-between mb-20">
        <div class="section-title">
            <h3 class="font-18 font-700">Pending Requests</h3>
            <p class="font-12 text-light">Manage only pending leave applications</p>
        </div>
        <span class="badge badge-warning">10 Requests Found</span>
    </div>
    
    <div class="card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>EMPLOYEE</th>
                        <th>LEAVE TYPE</th>
                        <th>DURATION</th>
                        <th>REASON</th>
                        <th>DOCUMENT</th>
                        <th>STATUS</th>
                        <th class="text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="pendingTableBody">
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Oliver Mitchell</span>
                                    <span class="email">EM-4821</span>
                                </div>
                            </div>
                        </td>
                        <td>Casual Leave</td>
                        <td>Sep 20 - Sep 21 (2 days)</td>
                        <td>Family event</td>
                        <td>
                            <button class="doc-pill doc-pill-image" title="View Document" onclick="openLeaveDocument('assets/sample/family-event-oliver.jpg','image')">
                                <i data-lucide="image" size="14"></i>
                                <span>Image</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <div class="btn-group justify-end px-20">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Oliver Mitchell','Casual Leave',2,'20 Sep 2026','21 Sep 2026','','assets/sample/family-event-oliver.jpg','image')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Ethan Hunt</span>
                                    <span class="email">EM-4825</span>
                                </div>
                            </div>
                        </td>
                        <td>Annual Leave</td>
                        <td>Oct 05 - Oct 10 (6 days)</td>
                        <td>Personal travel</td>
                        <td>
                            <button class="doc-pill doc-pill-pdf" title="View Document" onclick="openLeaveDocument('assets/sample/travel-tickets-ethan.pdf','pdf')">
                                <i data-lucide="file-text" size="14"></i>
                                <span>PDF</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <div class="btn-group justify-end px-20">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Ethan Hunt','Annual Leave',6,'05 Oct 2026','10 Oct 2026','','assets/sample/travel-tickets-ethan.pdf','pdf')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Lucas Gray</span>
                                    <span class="email">EM-4828</span>
                                </div>
                            </div>
                        </td>
                        <td>Annual Leave</td>
                        <td>Oct 20 - Oct 25 (6 days)</td>
                        <td>Wedding attendance</td>
                        <td>
                            <button class="doc-pill doc-pill-image" title="View Document" onclick="openLeaveDocument('assets/sample/wedding-card-lucas.jpg','image')">
                                <i data-lucide="image" size="14"></i>
                                <span>Image</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <div class="btn-group justify-end px-20">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Lucas Gray','Annual Leave',6,'20 Oct 2026','25 Oct 2026','','assets/sample/wedding-card-lucas.jpg','image')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Isabella Silva</span>
                                    <span class="email">EM-4830</span>
                                </div>
                            </div>
                        </td>
                        <td>Casual Leave</td>
                        <td>Nov 02 - Nov 03 (2 days)</td>
                        <td>Personal work</td>
                        <td>
                            <button class="doc-pill doc-pill-pdf" title="View Document" onclick="openLeaveDocument('assets/sample/personal-doc-isabella.pdf','pdf')">
                                <i data-lucide="file-text" size="14"></i>
                                <span>PDF</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <div class="btn-group justify-end px-20">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Isabella Silva','Casual Leave',2,'02 Nov 2026','03 Nov 2026','','assets/sample/personal-doc-isabella.pdf','pdf')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">James Bond</span>
                                    <span class="email">EM-007</span>
                                </div>
                            </div>
                        </td>
                        <td>Casual Leave</td>
                        <td>Nov 20 - Nov 21 (2 days)</td>
                        <td>Top Secret</td>
                        <td>
                            <button class="doc-pill doc-pill-pdf" title="View Document" onclick="openLeaveDocument('assets/sample/top-secret-bond.pdf','pdf')">
                                <i data-lucide="file-text" size="14"></i>
                                <span>PDF</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <div class="btn-group justify-end px-20">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('James Bond','Casual Leave',2,'20 Nov 2026','21 Nov 2026','','assets/sample/top-secret-bond.pdf','pdf')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <!-- Add more to reach 10 -->
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1599566150163-29194dcaad36?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Alex Smith</span>
                                    <span class="email">EM-4832</span>
                                </div>
                            </div>
                        </td>
                        <td>Sick Leave</td>
                        <td>Nov 22 - Nov 23 (2 days)</td>
                        <td>Fever</td>
                        <td>
                            <button class="doc-pill doc-pill-image" title="View Document" onclick="openLeaveDocument('assets/sample/clinic-slip-alex.jpg','image')">
                                <i data-lucide="image" size="14"></i>
                                <span>Image</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <div class="btn-group justify-end px-20">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Alex Smith','Sick Leave',2,'22 Nov 2026','23 Nov 2026','','assets/sample/clinic-slip-alex.jpg','image')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">John Doe</span>
                                    <span class="email">EM-4833</span>
                                </div>
                            </div>
                        </td>
                        <td>Annual Leave</td>
                        <td>Dec 01 - Dec 05 (5 days)</td>
                        <td>Winter Break</td>
                        <td>
                            <button class="doc-pill doc-pill-pdf" title="View Document" onclick="openLeaveDocument('assets/sample/winter-break-john.pdf','pdf')">
                                <i data-lucide="file-text" size="14"></i>
                                <span>PDF</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <div class="btn-group justify-end px-20">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('John Doe','Annual Leave',5,'01 Dec 2026','05 Dec 2026','','assets/sample/winter-break-john.pdf','pdf')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Sarah Connor</span>
                                    <span class="email">EM-4834</span>
                                </div>
                            </div>
                        </td>
                        <td>Casual Leave</td>
                        <td>Dec 10 - Dec 11 (2 days)</td>
                        <td>Family emergency</td>
                        <td>
                            <button class="doc-pill doc-pill-image" title="View Document" onclick="openLeaveDocument('assets/sample/family-emergency-sarah.jpg','image')">
                                <i data-lucide="image" size="14"></i>
                                <span>Image</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <div class="btn-group justify-end px-20">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Sarah Connor','Casual Leave',2,'10 Dec 2026','11 Dec 2026','','assets/sample/family-emergency-sarah.jpg','image')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1527980965255-d3b416303d12?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Bruce Wayne</span>
                                    <span class="email">EM-Bat</span>
                                </div>
                            </div>
                        </td>
                        <td>Sick Leave</td>
                        <td>Dec 15 - Dec 16 (2 days)</td>
                        <td>Night shift fatigue</td>
                        <td>
                            <button class="doc-pill doc-pill-image" title="View Document" onclick="openLeaveDocument('assets/sample/medical-bruce.jpg','image')">
                                <i data-lucide="image" size="14"></i>
                                <span>Image</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <div class="btn-group justify-end px-20">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Bruce Wayne','Sick Leave',2,'15 Dec 2026','16 Dec 2026','','assets/sample/medical-bruce.jpg','image')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?auto=format&fit=crop&q=80&w=150" class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Diana Prince</span>
                                    <span class="email">EM-Wonder</span>
                                </div>
                            </div>
                        </td>
                        <td>Annual Leave</td>
                        <td>Dec 20 - Dec 31 (12 days)</td>
                        <td>Vacation to Themyscira</td>
                        <td>
                            <button class="doc-pill doc-pill-pdf" title="View Document" onclick="openLeaveDocument('assets/sample/vacation-diana.pdf','pdf')">
                                <i data-lucide="file-text" size="14"></i>
                                <span>PDF</span>
                            </button>
                        </td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <div class="btn-group justify-end px-20">
                                <button class="action-btn action-btn-view" title="View Details" onclick="openLeaveDetailModal('Diana Prince','Annual Leave',12,'20 Dec 2026','31 Dec 2026','','assets/sample/vacation-diana.pdf','pdf')"><i data-lucide="eye" size="14"></i></button>
                                <button class="action-btn action-btn-edit" title="Approve"><i data-lucide="check" size="14"></i></button>
                                <button class="action-btn action-btn-delete" title="Reject"><i data-lucide="x" size="14"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="p-24 flex-between border-top">
            <span class="font-13 text-light" id="pendingPaginationInfo">Showing 1 to 10 of 10 entries</span>
            <div class="flex-center gap-8" id="pendingPaginationControls">
                <button class="action-btn" id="pendingPrevPage"><i data-lucide="chevron-left" size="16"></i></button>
                <div id="pendingPageNumbers" class="flex-center gap-8">
                    <button class="action-btn btn-active">1</button>
                </div>
                <button class="action-btn" id="pendingNextPage"><i data-lucide="chevron-right" size="16"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Premium Leave Management Modal -->
<div class="modal-overlay" id="applyLeaveModal">
    <div class="modal-content premium">
        <div class="modal-header">
            <div>
                <h3>Leave Management</h3>
                <p class="font-12 text-light mt-1">Set company-wide days per year (Sick, Casual, Annual). Everyone gets the same entitlement until you connect HR rules per employee.</p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body">
            <div class="leave-quota-company mb-0">
                <label class="admin-form-label admin-form-label--compact">Company leave days (per year)</label>
                <p class="font-12 text-light mb-20">These counts apply to <strong>all employees</strong> for annual entitlement.</p>
                <div class="leave-quota-grid mb-16">
                    <div class="form-group mb-0">
                        <label class="admin-form-label admin-form-label--inner" for="leaveQuotaSick">Sick Leave</label>
                        <input type="number" id="leaveQuotaSick" class="form-control bg-white-input" min="0" max="365" step="1" value="10">
                        <span class="font-11 text-light">days / year</span>
                    </div>
                    <div class="form-group mb-0">
                        <label class="admin-form-label admin-form-label--inner" for="leaveQuotaCasual">Casual Leave</label>
                        <input type="number" id="leaveQuotaCasual" class="form-control bg-white-input" min="0" max="365" step="1" value="8">
                        <span class="font-11 text-light">days / year</span>
                    </div>
                    <div class="form-group mb-0">
                        <label class="admin-form-label admin-form-label--inner" for="leaveQuotaAnnual">Annual Leave</label>
                        <input type="number" id="leaveQuotaAnnual" class="form-control bg-white-input" min="0" max="365" step="1" value="20">
                        <span class="font-11 text-light">days / year</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" id="leaveQuotaSaveBtn">
                <i data-lucide="save"></i>
                <span>Save leave counts</span>
            </button>
        </div>
    </div>
</div>
<!-- Leave Detail Modal -->
<div class="modal-overlay" id="leaveDetailModal">
    <div class="modal-content premium leave-detail-modal">
        <div class="modal-header">
            <div class="flex-center gap-12">
                <div class="type-icon-box primary">
                    <i data-lucide="clipboard-list" size="20"></i>
                </div>
                <div>
                    <h3 class="font-18 font-700 m-0">Leave Details</h3>
                    <p class="font-12 text-light m-0">Leave request information</p>
                </div>
            </div>
            <button type="button" class="icon-btn js-modal-close" aria-label="Close"><i data-lucide="x" size="20"></i></button>
        </div>
        <div class="modal-body">
            <div class="leave-detail-summary">
                <div class="leave-detail-summary-main">
                    <span class="leave-detail-emp-name" id="leaveDetailEmpName">—</span>
                    <span class="leave-detail-type-badge" id="leaveDetailLeaveType">—</span>
                </div>
                <div class="leave-detail-days" id="leaveDetailDays">—</div>
            </div>

            <div class="leave-detail-section">
                <h4 class="leave-detail-section-title">Duration</h4>
                <div class="leave-detail-grid leave-detail-dates">
                    <div class="detail-row">
                        <span class="detail-label">From Date</span>
                        <span class="detail-value" id="leaveDetailFromDate">—</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">To Date</span>
                        <span class="detail-value" id="leaveDetailToDate">—</span>
                    </div>
                </div>
            </div>

            <div class="leave-detail-section leave-detail-comment-section">
                <h4 class="leave-detail-section-title">Admin Comment</h4>
                <textarea id="leaveDetailAdminCommentInput" class="leave-detail-comment-input form-control" rows="3" placeholder="Add or edit your comment (e.g. Approved, Rejected, or any note)..."></textarea>
            </div>
        </div>
        <div class="modal-footer leave-detail-footer">
            <div class="leave-detail-footer-left">
                <button type="button" class="btn-primary leave-btn-approve" title="Approve">
                    <i data-lucide="check" size="16"></i>
                    <span>Approve</span>
                </button>
                <button type="button" class="btn-primary leave-btn-reject" title="Reject">
                    <i data-lucide="x" size="16"></i>
                    <span>Reject</span>
                </button>
            </div>
            <button type="button" class="btn-primary" id="leaveDetailSaveBtn" onclick="leaveDetailSaveComment()">
                <i data-lucide="check" size="16"></i>
                <span>Save</span>
            </button>
        </div>
    </div>
</div>

<!-- Pending Requests Modal -->
<!-- (REMOVED: Modal removed in favor of in-page table view) -->

<!-- Leave Document Preview Modal -->
<div class="modal-overlay" id="leaveDocumentModal">
    <div class="modal-content wide-lg">
        <div class="modal-header">
            <div>
                <h3 class="font-16 font-700">Leave Document</h3>
                <p class="font-12 text-light m-0">Preview of the attachment uploaded with this leave request.</p>
            </div>
            <button type="button" class="icon-btn js-modal-close" aria-label="Close">
                <i data-lucide="x" size="18"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="document-preview-container">
                <img id="leaveDocImage" src="" alt="Leave Document" class="leave-doc-media hidden">
                <iframe id="leaveDocPdf" src="" class="leave-doc-media hidden" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/leave-management.js"></script>
<?php include 'includes/footer.php'; ?>
