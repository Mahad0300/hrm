<?php
$page_title = "KPI Management";
$page_subtitle = "Track and manage employee performance indicators and feedback.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Master Dashboard View -->
<div id="kpiMasterView">
    <!-- Summary Cards -->
    <div class="grid-4 gap-24 mb-24">
        <div class="card p-20 flex-center justify-start gap-16">
            <div class="icon-box-md success-light">
                <i data-lucide="award" class="text-success" size="24"></i>
            </div>
            <div>
                <h4 class="font-13 text-light font-500">Average Score</h4>
                <p class="font-20 font-700 mt-4">4.2 / 5.0</p>
            </div>
        </div>
        <div class="card p-20 flex-center justify-start gap-16">
            <div class="icon-box-md primary-light">
                <i data-lucide="users" class="text-primary" size="24"></i>
            </div>
            <div>
                <h4 class="font-13 text-light font-500">Employees Rated</h4>
                <p class="font-20 font-700 mt-4">384 / 450</p>
            </div>
        </div>
        <div class="card p-20 flex-center justify-start gap-16">
            <div class="icon-box-md warning-light">
                <i data-lucide="clock" class="text-warning" size="24"></i>
            </div>
            <div>
                <h4 class="font-13 text-light font-500">Pending Reviews</h4>
                <p class="font-20 font-700 mt-4">66</p>
            </div>
        </div>
        <div class="card p-20 flex-center justify-start gap-16">
            <div class="icon-box-md info-light">
                <i data-lucide="trending-up" class="text-info" size="24"></i>
            </div>
            <div>
                <h4 class="font-13 text-light font-500">Top Department</h4>
                <p class="font-20 font-700 mt-4">Engineering</p>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card p-24 mb-24">
        <div class="filter-grid">
            <div class="search-box">
                <i data-lucide="search" size="18"></i>
                <input type="text" placeholder="Search by employee name or ID...">
            </div>
            <div class="filter-item">
                <select class="form-control">
                    <option value="">All Departments</option>
                    <option>Engineering</option>
                    <option>Marketing</option>
                    <option>HR</option>
                </select>
            </div>
            <div class="filter-item">
                <select class="form-control">
                    <option value="">Month (Current)</option>
                    <option>October 2026</option>
                    <option>September 2026</option>
                    <option>August 2026</option>
                </select>
            </div>
            <div class="filter-item">
                <select class="form-control">
                    <option value="">Performance (All)</option>
                    <option>Above Target</option>
                    <option>On Track</option>
                    <option>Below Target</option>
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
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                                    class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Emma Williams</span>
                                    <span class="email font-12 text-light">EM-4820</span>
                                </div>
                            </div>
                        </td>
                        <td>Engineering</td>
                        <td class="font-13">Q4 Code Quality</td>
                        <td width="200">
                            <div class="kpi-progress-wrapper">
                                <span class="font-11 text-light mb-4 block">85 / 100</span>
                                <div class="progress-bar-container">
                                    <div class="progress-bar success" style="width: 85%;"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex-center justify-start gap-4">
                                <i data-lucide="star" class="text-warning fill-warning" size="14"></i>
                                <span class="font-14 font-600">4.5</span>
                            </div>
                        </td>
                        <td><span class="badge badge-success">Excelling</span></td>
                        <td class="text-right px-30">
                            <button class="btn-primary font-11 py-8 px-16 rounded-8" onclick="showKpiDetail('Emma Williams', 'Engineering', '4.5')">
                                <i data-lucide="eye" size="14" class="mr-6"></i> View Scorecard
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150"
                                    class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Oliver Mitchell</span>
                                    <span class="email font-12 text-light">EM-4821</span>
                                </div>
                            </div>
                        </td>
                        <td>Marketing</td>
                        <td class="font-13">Social Engagement</td>
                        <td width="200">
                            <div class="kpi-progress-wrapper">
                                <span class="font-11 text-light mb-4 block">60 / 100</span>
                                <div class="progress-bar-container">
                                    <div class="progress-bar warning" style="width: 60%;"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex-center justify-start gap-4">
                                <i data-lucide="star" class="text-warning fill-warning" size="14"></i>
                                <span class="font-14 font-600">3.2</span>
                            </div>
                        </td>
                        <td><span class="badge badge-warning">On Track</span></td>
                        <td class="text-right px-30">
                            <button class="btn-primary font-11 py-8 px-16 rounded-8" onclick="showKpiDetail('Oliver Mitchell', 'Marketing', '3.2')">
                                <i data-lucide="eye" size="14" class="mr-6"></i> View Scorecard
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150"
                                    class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Sophia Reynolds</span>
                                    <span class="email font-12 text-light">EM-4822</span>
                                </div>
                            </div>
                        </td>
                        <td>HR</td>
                        <td class="font-13">Recruitment Time</td>
                        <td width="200">
                            <div class="kpi-progress-wrapper">
                                <span class="font-11 text-light mb-4 block">95 / 100</span>
                                <div class="progress-bar-container">
                                    <div class="progress-bar success" style="width: 95%;"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex-center justify-start gap-4">
                                <i data-lucide="star" class="text-warning fill-warning" size="14"></i>
                                <span class="font-14 font-600">4.9</span>
                            </div>
                        </td>
                        <td><span class="badge badge-success">Excelling</span></td>
                        <td class="text-right px-30">
                            <button class="btn-primary font-11 py-8 px-16 rounded-8" onclick="showKpiDetail('Sophia Reynolds', 'HR', '4.9')">
                                <i data-lucide="eye" size="14" class="mr-6"></i> View Scorecard
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150"
                                    class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">James Wilson</span>
                                    <span class="email font-12 text-light">EM-4819</span>
                                </div>
                            </div>
                        </td>
                        <td>Management</td>
                        <td class="font-13">Project Delivery</td>
                        <td width="200">
                            <div class="kpi-progress-wrapper">
                                <span class="font-11 text-light mb-4 block">40 / 100</span>
                                <div class="progress-bar-container">
                                    <div class="progress-bar danger" style="width: 40%;"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex-center justify-start gap-4">
                                <i data-lucide="star" class="text-warning fill-warning" size="14"></i>
                                <span class="font-14 font-600">2.1</span>
                            </div>
                        </td>
                        <td><span class="badge badge-danger">Below Target</span></td>
                        <td class="text-right px-30">
                            <button class="btn-primary font-11 py-8 px-16 rounded-8" onclick="showKpiDetail('James Wilson', 'Management', '2.1')">
                                <i data-lucide="eye" size="14" class="mr-6"></i> View Scorecard
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="emp-profile">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150"
                                    class="emp-avatar" alt="Avatar">
                                <div class="emp-info">
                                    <span class="name">Ethan Hunt</span>
                                    <span class="email font-12 text-light">EM-4825</span>
                                </div>
                            </div>
                        </td>
                        <td>Engineering</td>
                        <td class="font-13">Sprint Efficiency</td>
                        <td width="200">
                            <div class="kpi-progress-wrapper">
                                <span class="font-11 text-light mb-4 block">75 / 100</span>
                                <div class="progress-bar-container">
                                    <div class="progress-bar primary" style="width: 75%;"></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex-center justify-start gap-4">
                                <i data-lucide="star" class="text-warning fill-warning" size="14"></i>
                                <span class="font-14 font-600">3.8</span>
                            </div>
                        </td>
                        <td><span class="badge badge-primary">Good</span></td>
                        <td class="text-right px-30">
                            <button class="btn-primary font-11 py-8 px-16 rounded-8" onclick="showKpiDetail('Ethan Hunt', 'Engineering', '3.8')">
                                <i data-lucide="eye" size="14" class="mr-6"></i> View Scorecard
                            </button>
                        </td>
                    </tr>
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

<!-- Employee Detail KPI View (Hidden by default) -->
<div id="kpiDetailView" class="hidden">
    <div class="flex-between mb-24">
        <button class="btn-outline-primary py-8 px-16 flex-center gap-8 font-13" onclick="hideKpiDetail()">
            <i data-lucide="arrow-left" size="16"></i>
            <span>Back to Dashboard</span>
        </button>
        <button class="btn-primary py-8 px-16 flex-center gap-8 font-13" onclick="openModal('addReviewModal')">
            <i data-lucide="plus-circle" size="16"></i>
            <span>Add New Review</span>
        </button>
    </div>

    <div class="grid-2-1 gap-24">
        <!-- Left: Detailed Scorecard -->
        <div class="flex-column gap-24">
            <div class="card p-30">
                <div class="flex-center justify-start gap-20 mb-30">
                    <img id="detailAvatar"
                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150"
                        class="w-80 h-80 rounded-full border-4-white shadow-sm" alt="Avatar">
                    <div>
                        <h3 class="font-24 font-700" id="detailName">Emma Williams</h3>
                        <p class="font-14 text-light mt-4" id="detailDept">Engineering Department</p>
                        <div class="flex-center justify-start gap-8 mt-12">
                            <span class="badge badge-success font-11 uppercase ls-05" id="detailStatus">Excelling</span>
                            <span class="text-light opacity-50">|</span>
                            <div class="flex-center gap-4">
                                <i data-lucide="star" class="text-warning fill-warning" size="14"></i>
                                <span class="font-14 font-700" id="detailScore">4.5</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-divider my-30"></div>

                <div class="kpi-goals-section">
                    <div class="mb-24">
                        <h4 class="font-14 font-800 text-dark uppercase ls-05">Performance Scorecard</h4>
                    </div>


                    <div class="space-y-20" id="detailGoalsContainer">
                        <!-- Goal 1 -->
                        <div class="goal-item">
                            <div class="flex-between mb-8">
                                <span class="font-14 font-600">Technical Proficiency</span>
                                <span class="font-13 text-primary-color font-600">92%</span>
                            </div>
                            <div class="progress-bar-container h-8">
                                <div class="progress-bar success" style="width: 92%;"></div>
                            </div>
                        </div>
                        <!-- Goal 2 -->
                        <div class="goal-item">
                            <div class="flex-between mb-8">
                                <span class="font-14 font-600">Task Completion Rate</span>
                                <span class="font-13 text-primary-color font-600">88%</span>
                            </div>
                            <div class="progress-bar-container h-8">
                                <div class="progress-bar success" style="width: 88%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Trend Chart Placeholder -->
            <div class="card p-30">
                <div class="flex-between mb-24">
                    <h4 class="font-14 font-600 text-dark uppercase ls-05">Monthly Progress Trend</h4>
                    <select class="form-control w-120 py-4 px-8 font-12">
                        <option>Last 6 Months</option>
                        <option>Last 12 Months</option>
                    </select>
                </div>

                <div class="trend-chart-wrapper h-220 mt-20 pr-10">
                    <div class="chart-y-axis">
                        <span>5.0</span>
                        <span>4.0</span>
                        <span>3.0</span>
                        <span>2.0</span>
                        <span>1.0</span>
                        <span>0</span>
                    </div>
                    <div class="chart-main">
                        <div class="chart-grid-lines">
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                            <div class="grid-line"></div>
                        </div>
                        <svg viewBox="0 0 500 200" class="performance-svg" preserveAspectRatio="none">
                            <!-- Area Gradient Fill -->
                            <defs>
                                <linearGradient id="chartGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:rgba(108, 76, 241, 0.2); stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:rgba(108, 76, 241, 0); stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <path d="M0,160 Q50,140 100,120 T200,140 T300,80 T400,60 T500,40 V200 H0 Z" fill="url(#chartGradient)"></path>
                            <!-- Main Trend Line -->
                            <path d="M0,160 Q50,140 100,120 T200,140 T300,80 T400,60 T500,40" fill="none" stroke="var(--primary-color)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="chart-line-anim"></path>
                            <!-- Data Points -->
                            <circle cx="0" cy="160" r="4" fill="#fff" stroke="var(--primary-color)" stroke-width="2" class="chart-dot"></circle>
                            <circle cx="100" cy="120" r="4" fill="#fff" stroke="var(--primary-color)" stroke-width="2" class="chart-dot"></circle>
                            <circle cx="200" cy="140" r="4" fill="#fff" stroke="var(--primary-color)" stroke-width="2" class="chart-dot"></circle>
                            <circle cx="300" cy="80" r="4" fill="#fff" stroke="var(--primary-color)" stroke-width="2" class="chart-dot"></circle>
                            <circle cx="400" cy="60" r="4" fill="#fff" stroke="var(--primary-color)" stroke-width="2" class="chart-dot"></circle>
                            <circle cx="500" cy="40" r="6" fill="var(--primary-color)" stroke="#fff" stroke-width="2" class="chart-dot active"></circle>
                        </svg>
                        <div class="chart-x-axis mt-12">
                            <span>May</span>
                            <span>Jun</span>
                            <span>Jul</span>
                            <span>Aug</span>
                            <span>Sep</span>
                            <span class="font-700 text-dark">Oct</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Feedback History -->
        <div class="card p-30 h-fit sticky-top-120">
            <h4 class="font-14 font-600 text-dark uppercase mb-24 ls-05">Feedback History</h4>

            <div class="timeline-compact">
                <!-- Log 1 -->
                <div class="timeline-item-lite">
                    <div class="timeline-dot-lite success"></div>
                    <div class="timeline-content-lite">
                        <div class="flex-between">
                            <span class="font-12 font-700">James Wilson (Admin)</span>
                            <span class="font-12 text-light">Oct 12, 2026</span>
                        </div>
                        <p class="font-13 text-secondary mt-8">Excellent delivery on the Q3 project. Code quality has
                            improved significantly. Keep it up!</p>
                        
                        <!-- Mini Scorecard in History -->
                        <div class="history-score-grid mt-20 pt-16 border-t-dashed grid-2 gap-x-20 gap-y-12">
                            <div>
                                <div class="flex-between mb-4">
                                    <span class="font-9 text-light uppercase ls-05">Technical</span>
                                    <span class="font-9 font-700 text-dark">92%</span>
                                </div>
                                <div class="progress-bar-container h-4">
                                    <div class="progress-bar success" style="width: 92%;"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex-between mb-4">
                                    <span class="font-9 text-light uppercase ls-05">Tasks</span>
                                    <span class="font-9 font-700 text-dark">88%</span>
                                </div>
                                <div class="progress-bar-container h-4">
                                    <div class="progress-bar success" style="width: 88%;"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex-between mb-4">
                                    <span class="font-9 text-light uppercase ls-05">Comm.</span>
                                    <span class="font-9 font-700 text-dark">75%</span>
                                </div>
                                <div class="progress-bar-container h-4">
                                    <div class="progress-bar primary" style="width: 75%;"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex-between mb-4">
                                    <span class="font-9 text-light uppercase ls-05">Collab.</span>
                                    <span class="font-9 font-700 text-dark">80%</span>
                                </div>
                                <div class="progress-bar-container h-4">
                                    <div class="progress-bar primary" style="width: 80%;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="rating-tag mt-20">
                            <i data-lucide="star" class="text-warning fill-warning" size="12"></i>
                            <span class="font-11 font-700">4.8 / 5.0</span>
                        </div>
                    </div>
                </div>
                <!-- Log 2 -->
                <div class="timeline-item-lite">
                    <div class="timeline-dot-lite info"></div>
                    <div class="timeline-content-lite">
                        <div class="flex-between">
                            <span class="font-12 font-700">James Wilson (Admin)</span>
                            <span class="font-12 text-light">Aug 10, 2026</span>
                        </div>
                        <p class="font-13 text-secondary mt-8">Good progress on technical skills, but needs to focus
                            more on internal team communications.</p>
                        
                        <!-- Mini Scorecard in History -->
                        <div class="history-score-grid mt-20 pt-16 border-t-dashed grid-2 gap-x-20 gap-y-12">
                            <div>
                                <div class="flex-between mb-4">
                                    <span class="font-9 text-light uppercase ls-05">Technical</span>
                                    <span class="font-9 font-700 text-dark">85%</span>
                                </div>
                                <div class="progress-bar-container h-4">
                                    <div class="progress-bar warning" style="width: 85%;"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex-between mb-4">
                                    <span class="font-9 text-light uppercase ls-05">Tasks</span>
                                    <span class="font-9 font-700 text-dark">80%</span>
                                </div>
                                <div class="progress-bar-container h-4">
                                    <div class="progress-bar warning" style="width: 80%;"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex-between mb-4">
                                    <span class="font-9 text-light uppercase ls-05">Comm.</span>
                                    <span class="font-9 font-700 text-dark">60%</span>
                                </div>
                                <div class="progress-bar-container h-4">
                                    <div class="progress-bar danger" style="width: 60%;"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex-between mb-4">
                                    <span class="font-9 text-light uppercase ls-05">Collab.</span>
                                    <span class="font-9 font-700 text-dark">70%</span>
                                </div>
                                <div class="progress-bar-container h-4">
                                    <div class="progress-bar primary" style="width: 70%;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="rating-tag mt-20">
                            <i data-lucide="star" class="text-warning fill-warning" size="12"></i>
                            <span class="font-11 font-700">4.0 / 5.0</span>
                        </div>
                    </div>
                </div>
                <!-- Log 3 -->
                <div class="timeline-item-lite">
                    <div class="timeline-dot-lite warning"></div>
                    <div class="timeline-content-lite">
                        <div class="flex-between">
                            <span class="font-12 font-700">James Wilson (Admin)</span>
                            <span class="font-12 text-light">Jun 05, 2026</span>
                        </div>
                        <p class="font-13 text-secondary mt-8">Initial review. Great potential in backend development.</p>
                        
                        <!-- Mini Scorecard in History -->
                        <div class="history-score-grid mt-20 pt-16 border-t-dashed grid-2 gap-x-20 gap-y-12">
                            <div>
                                <div class="flex-between mb-4">
                                    <span class="font-9 text-light uppercase ls-05">Technical</span>
                                    <span class="font-9 font-700 text-dark">75%</span>
                                </div>
                                <div class="progress-bar-container h-4">
                                    <div class="progress-bar primary" style="width: 75%;"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex-between mb-4">
                                    <span class="font-9 text-light uppercase ls-05">Tasks</span>
                                    <span class="font-9 font-700 text-dark">70%</span>
                                </div>
                                <div class="progress-bar-container h-4">
                                    <div class="progress-bar primary" style="width: 70%;"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex-between mb-4">
                                    <span class="font-9 text-light uppercase ls-05">Comm.</span>
                                    <span class="font-9 font-700 text-dark">80%</span>
                                </div>
                                <div class="progress-bar-container h-4">
                                    <div class="progress-bar primary" style="width: 80%;"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex-between mb-4">
                                    <span class="font-9 text-light uppercase ls-05">Collab.</span>
                                    <span class="font-9 font-700 text-dark">65%</span>
                                </div>
                                <div class="progress-bar-container h-4">
                                    <div class="progress-bar warning" style="width: 65%;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="rating-tag mt-20">
                            <i data-lucide="star" class="text-warning fill-warning" size="12"></i>
                            <span class="font-11 font-700">3.5 / 5.0</span>
                        </div>
                    </div>
                </div>
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
                <p class="font-12 text-light mt-1">Submit feedback and update KPI scores for <span id="modalEmpName"
                        class="font-600 text-dark">Emma Williams</span></p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <form id="addReviewForm">
            <div class="modal-body p-40 custom-scrollbar max-h-70vh">
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
                            <i data-lucide="star" class="empty cursor-pointer" onclick="setSentiment(1)" data-value="1" size="28"></i>
                            <i data-lucide="star" class="empty cursor-pointer" onclick="setSentiment(2)" data-value="2" size="28"></i>
                            <i data-lucide="star" class="empty cursor-pointer" onclick="setSentiment(3)" data-value="3" size="28"></i>
                            <i data-lucide="star" class="empty cursor-pointer" onclick="setSentiment(4)" data-value="4" size="28"></i>
                            <i data-lucide="star" class="empty cursor-pointer" onclick="setSentiment(5)" data-value="5" size="28"></i>
                        </div>
                        <div id="sentimentText" class="font-14 font-700 text-primary-color mt-4">Average Performance</div>
                        <input type="hidden" name="rating" id="reviewRatingInput" value="0">
                    </div>
                </div>

                <div class="border-divider mb-24"></div>

                <div class="mb-32 mt-40">
                    <div class="mb-20">
                        <h4 class="font-14 font-800 text-dark uppercase ls-1 mb-4">Specific KPI Goals</h4>
                        <p class="font-12 text-light">Define and rate individual performance metrics for this review period.</p>
                    </div>
                    
                    <div class="flex-center gap-12 bg-white p-8 rounded-12 border shadow-sm">
                        <input type="text" id="customGoalInput" 
                            class="form-control font-14 border-none shadow-none px-16 h-48 flex-1" 
                            placeholder="e.g. Code Stability, Client Satisfaction, Team Lead..."
                            style="background: transparent;">
                        <button type="button" class="btn-primary font-14 font-700 h-48 px-32 rounded-10 shadow-primary" 
                            onclick="window.addCustomGoal()">
                            <i data-lucide="plus" size="18" class="mr-8"></i> Add
                        </button>
                    </div>
                </div>
                
                <div class="grid-2 gap-x-32 gap-y-32 mt-20" id="dynamicKpiContainer">
                    <!-- Goals will be injected here by kpi.js -->
                </div>



            </div>
            <div class="modal-footer">
                <button type="button" class="btn-ghost js-modal-close">Cancel</button>
                <button type="submit" class="btn-primary">Submit Review</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Dashboard Summary Icons */
    .icon-box-md.success-light {
        background: #ecfdf5;
    }

    .icon-box-md.primary-light {
        background: #eef2ff;
    }

    .icon-box-md.warning-light {
        background: #fffbeb;
    }

    .icon-box-md.info-light {
        background: #eff6ff;
    }

    /* KPI Progress Bars */
    .kpi-progress-wrapper {
        width: 100%;
    }

    .progress-bar-container {
        background: #f1f5f9;
        border-radius: 99px;
        height: 6px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        transition: width 0.3s ease;
    }

    .progress-bar.success {
        background: var(--success);
    }

    .progress-bar.warning {
        background: var(--warning);
    }

    .progress-bar.danger {
        background: var(--danger);
    }

    .progress-bar.primary {
        background: var(--primary-color);
    }

    /* Timeline Compact (For detail view) */
    .timeline-compact {
        padding-left: 24px;
        border-left: 2px solid #f1f5f9;
        position: relative;
    }

    .timeline-item-lite {
        position: relative;
        padding-bottom: 24px;
    }

    .timeline-dot-lite {
        position: absolute;
        left: -31px;
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #f1f5f9;
        z-index: 2;
    }

    .timeline-dot-lite.success {
        background: #22c55e;
    }

    .timeline-dot-lite.info {
        background: #3b82f6;
    }

    .timeline-dot-lite.warning {
        background: #f59e0b;
    }

    .timeline-content-lite {
        background: #f8fafc;
        padding: 16px;
        border-radius: 12px;
        transition: var(--transition);
        border: 1px solid transparent;
    }

    .timeline-content-lite:hover {
        background: #fff;
        border-color: #f1f5f9;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        transform: translateX(4px);
    }

    .rating-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: white;
        padding: 2px 8px;
        border-radius: 99px;
        border: 1px solid #e2e8f0;
    }

    /* Professional SVG Chart Styles */
    .trend-chart-wrapper {
        display: flex;
        gap: 16px;
    }

    .chart-y-axis {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding-bottom: 32px;
        font-size: 10px;
        color: #94a3b8;
        font-weight: 600;
        text-align: right;
        width: 24px;
    }

    .chart-main {
        flex: 1;
        position: relative;
    }

    .chart-grid-lines {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 32px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        pointer-events: none;
    }

    .grid-line {
        border-top: 1px dashed #e2e8f0;
        width: 100%;
        height: 1px;
    }

    .performance-svg {
        width: 100%;
        height: 168px;
        overflow: visible;
        margin-top: 0;
    }

    .chart-line-anim {
        stroke-dasharray: 1000;
        stroke-dashoffset: 1000;
        animation: drawLine 2s forwards ease-in-out;
    }

    @keyframes drawLine {
        to {
            stroke-dashoffset: 0;
        }
    }

    .chart-dot {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .chart-dot:hover {
        r: 8px;
        filter: drop-shadow(0 0 8px rgba(108, 76, 241, 0.4));
    }

    .chart-dot.active {
        animation: pulseDot 2s infinite;
    }

    @keyframes pulseDot {
        0% {
            r: 6;
            opacity: 1;
        }

        50% {
            r: 10;
            opacity: 0.6;
        }

        100% {
            r: 6;
            opacity: 1;
        }
    }

    .chart-x-axis {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: #94a3b8;
        padding: 0 5px;
    }

    /* Modal Custom Styles */
    .kpi-range-input {
        width: 100%;
        height: 6px;
        background: #eef2ff;
        border-radius: 5px;
        outline: none;
        cursor: pointer;
        -webkit-appearance: none;
    }

    .kpi-range-input::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 16px;
        height: 16px;
        background: var(--primary-color);
        border-radius: 50%;
        cursor: pointer;
    }

    .star-rating-icon.active {
        color: #f59e0b !important;
        fill: #f59e0b !important;
    }

    .star-rating-icon:hover {
        color: #f59e0b !important;
        opacity: 0.8;
    }

    .rating-tag i.fill-warning {
        fill: #f59e0b !important;
        color: #f59e0b !important;
    }

    .border-t-dashed {
        border-top: 1px dashed #e2e8f0;
    }

    .h-4 {
        height: 4px !important;
    }

    .history-score-grid {
        opacity: 0.9;
        transition: opacity 0.3s ease;
    }

    .gap-x-20 { gap: 0 20px; }
    .gap-y-12 { row-gap: 12px; }

    .timeline-content-lite {
        padding: 20px 24px !important;
    }

    .history-score-grid:hover {
        opacity: 1;
    }

    .h-fit {
        height: fit-content;
    }

    .sticky-top-120 {
        position: sticky;
        top: 120px;
    }

    /* Advanced Modal Components */
    .period-card {
        padding: 16px;
        border: 1.5px solid #f1f5f9;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .period-card i {
        width: 20px;
        height: 20px;
        color: #94a3b8;
    }

    .period-card span {
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
    }

    .period-card:hover {
        border-color: var(--primary-color);
        background: #f8fafc;
    }

    .period-card.active {
 background: linear-gradient(135deg, #6C4CF1 0%, #8A6FFF 100%);
    color: #fff;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
    box-shadow: 0 8px 16px -4px rgba(108, 76, 241, 0.3);
    }

    .period-card.active i {
        color: var(--primary-color);
    }

    .period-card.active span {
        color: var(--primary-color);
    }


    .bg-primary-soft { background: #f8fafc; border: 1px solid #f1f5f9; }
    .rounded-20 { border-radius: 20px; }
    .blur-20 { filter: blur(20px); }
    .z-10 { z-index: 10; }
    .ls-1 { letter-spacing: 1px; }
</style>

<script src="assets/js/kpi.js"></script>
<?php include 'includes/footer.php'; ?>