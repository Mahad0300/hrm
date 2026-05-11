<?php
$page_title = "Candidate Detail";
$page_subtitle = "Application profile, answers, and documents.";
include 'includes/header.php';
include 'includes/sidebar.php';

$dummy_pdf = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
?>

<div class="page-action-area page-action-area--align-start">
    <div class="header-actions flex-center gap-12">
        <a href="job-candidates.php" class="btn-primary no-bg border candidate-detail-back-btn">
            <i data-lucide="arrow-left" size="18"></i>
            <span>Back to Candidate Pool</span>
        </a>
    </div>
</div>

<div class="candidate-detail-page">
    <div class="candidate-detail-layout">
        <!-- Left: profile + documents -->
        <div class="candidate-detail-col candidate-detail-col--left">
            <div class="card p-24 mb-24 candidate-detail-summary-card">
                <div class="candidate-detail-summary-head">
                    <h2 class="font-22 font-700 m-0">Syed Shahir Ali</h2>
                    <p class="font-13 text-light mt-8 m-0">Applied for: Operations Assistant</p>
                    <div class="flex-center gap-10 mt-12 flex-wrap">
                        <span class="badge-select interview" role="status">Interview</span>
                        <span class="font-12 text-light">Applied: Feb 10, 2026</span>
                    </div>
                </div>
                <div class="candidate-detail-summary-contact">
                    <h3 class="font-12 font-700 text-light uppercase ls-05 mb-16 m-0">Contact</h3>
                    <div class="candidate-detail-stack">
                        <div class="candidate-detail-field mb-16">
                            <span class="candidate-detail-label">Email</span>
                            <span class="candidate-detail-value allow-wrap">syedshahirali16@gmail.com</span>
                        </div>
                        <div class="candidate-detail-field mb-16">
                            <span class="candidate-detail-label">Phone</span>
                            <span class="candidate-detail-value">+92 333 4455667</span>
                        </div>
                        <div class="candidate-detail-field mb-0">
                            <span class="candidate-detail-label">Location</span>
                            <span class="candidate-detail-value allow-wrap">Gulistan-e-Jauhar, Karachi, Sindh</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-24 mb-0">
                <h3 class="font-14 font-700 mb-20 text-dark">Resume &amp; CNIC</h3>
                <div class="candidate-detail-docs candidate-detail-docs--stack">
                    <div class="candidate-doc-card">
                        <div class="candidate-doc-inner">
                            <div class="font-13 font-600 text-dark mb-8">Resume / CV</div>
                            <p class="font-11 text-light m-0 mb-10">Resume_Shahir.pdf</p>
                            <div class="mt-12">
                                <a class="btn-outline-primary font-12 px-16 py-8" href="<?= htmlspecialchars($dummy_pdf) ?>" target="_blank" rel="noopener">View / Download</a>
                            </div>
                        </div>
                    </div>
                    <div class="candidate-doc-card">
                        <div class="candidate-doc-inner">
                            <div class="font-13 font-600 text-dark mb-8">CNIC (front)</div>
                            <p class="font-11 text-light m-0 mb-10">CNIC_front.pdf</p>
                            <div class="mt-12">
                                <a class="btn-outline-primary font-12 px-16 py-8" href="<?= htmlspecialchars($dummy_pdf) ?>" target="_blank" rel="noopener">View / Download</a>
                            </div>
                        </div>
                    </div>
                    <div class="candidate-doc-card">
                        <div class="candidate-doc-inner">
                            <div class="font-13 font-600 text-dark mb-8">CNIC (back)</div>
                            <p class="font-11 text-light m-0 mb-10">CNIC_back.pdf</p>
                            <div class="mt-12">
                                <a class="btn-outline-primary font-12 px-16 py-8" href="<?= htmlspecialchars($dummy_pdf) ?>" target="_blank" rel="noopener">View / Download</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Q&A + actions -->
        <div class="candidate-detail-col candidate-detail-col--right">
            <div class="card p-24 mb-24 candidate-detail-qa-card">
                <h3 class="font-14 font-700 mb-20 text-dark">Application questions &amp; answers</h3>
                <div class="candidate-detail-answers">
                    <div class="candidate-answer-row">
                        <div class="font-11 text-light uppercase font-600 mb-6">What is your current salary?</div>
                        <div class="font-14 text-dark font-500 allow-wrap">PKR 38,000</div>
                    </div>
                    <div class="candidate-answer-row">
                        <div class="font-11 text-light uppercase font-600 mb-6">What is your expected salary?</div>
                        <div class="font-14 text-dark font-500 allow-wrap">PKR 50,000</div>
                    </div>
                    <div class="candidate-answer-row">
                        <div class="font-11 text-light uppercase font-600 mb-6">Do you have your own laptop?</div>
                        <div class="font-14 text-dark font-500 allow-wrap">Yes</div>
                    </div>
                    <div class="candidate-answer-row">
                        <div class="font-11 text-light uppercase font-600 mb-6">What is your notice period?</div>
                        <div class="font-14 text-dark font-500 allow-wrap">30 days</div>
                    </div>
                    <div class="candidate-answer-row">
                        <div class="font-11 text-light uppercase font-600 mb-6">Total years of experience in a similar role?</div>
                        <div class="font-14 text-dark font-500 allow-wrap">3 years</div>
                    </div>
                    <div class="candidate-answer-row">
                        <div class="font-11 text-light uppercase font-600 mb-6">Are you willing to work on-site in Karachi?</div>
                        <div class="font-14 text-dark font-500 allow-wrap">Yes</div>
                    </div>
                    <div class="candidate-answer-row">
                        <div class="font-11 text-light uppercase font-600 mb-6">What is your current salary?</div>
                        <div class="font-14 text-dark font-500 allow-wrap">PKR 38,000</div>
                    </div>
                    <div class="candidate-answer-row">
                        <div class="font-11 text-light uppercase font-600 mb-6">What is your expected salary?</div>
                        <div class="font-14 text-dark font-500 allow-wrap">PKR 50,000</div>
                    </div>
                    <div class="candidate-answer-row">
                        <div class="font-11 text-light uppercase font-600 mb-6">Do you have your own laptop?</div>
                        <div class="font-14 text-dark font-500 allow-wrap">Yes</div>
                    </div>
                    <div class="candidate-answer-row">
                        <div class="font-11 text-light uppercase font-600 mb-6">What is your notice period?</div>
                        <div class="font-14 text-dark font-500 allow-wrap">30 days</div>
                    </div>
                    <div class="candidate-answer-row">
                        <div class="font-11 text-light uppercase font-600 mb-6">Total years of experience in a similar role?</div>
                        <div class="font-14 text-dark font-500 allow-wrap">3 years</div>
                    </div>
                    <div class="candidate-answer-row">
                        <div class="font-11 text-light uppercase font-600 mb-6">Are you willing to work on-site in Karachi?</div>
                        <div class="font-14 text-dark font-500 allow-wrap">Yes</div>
                    </div>
                </div>
            </div>

            <div class="candidate-detail-actions card p-24">
                <div class="flex-between flex-wrap gap-16 align-center w-full candidate-detail-actions-row">
                    <div class="candidate-detail-status-wrap">
                        <select id="candidateStatusSelect" class="form-control font-13 candidate-detail-status-select bg-white-input" aria-label="Candidate status">
                            <option value="Shortlisted">Shortlisted</option>
                            <option value="Interview" selected>Interview</option>
                            <option value="Hired">Hired</option>
                            <option value="Resource Pool">Resource Pool</option>
                            <option value="Reject">Reject</option>
                        </select>
                    </div>
                    <button type="button" class="btn-primary px-24 font-13" id="scheduleInterviewBtn">
                        <i data-lucide="calendar" size="16"></i>
                        <span>Schedule interview</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule interview modal -->
<div class="modal-overlay" id="scheduleInterviewModal">
    <div class="modal-content premium wide-sm">
        <div class="modal-header">
            <div>
                <h3>Schedule interview</h3>
                <p class="font-12 text-light mt-1 m-0" id="scheduleInterviewSubtitle">Set date and time for this candidate</p>
            </div>
            <button type="button" class="icon-btn" onclick="closeModal('scheduleInterviewModal')" aria-label="Close"><i data-lucide="x" size="20"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="scheduleInterviewForm">
                <div class="form-group mb-20">
                    <label class="admin-form-label">Interview date *</label>
                    <input type="date" id="scheduleInterviewDate" class="form-control bg-white-input" required>
                </div>
                <div class="form-group mb-0">
                    <label class="admin-form-label">Interview time *</label>
                    <input type="time" id="scheduleInterviewTime" class="form-control bg-white-input" required>
                </div>
            </form>
        </div>
        <div class="modal-footer flex-end gap-12 p-30 border-top-0">
            <button type="submit" form="scheduleInterviewForm" class="btn-primary px-30">Save schedule</button>
        </div>
    </div>
</div>

<script src="assets/js/candidate-detail.js"></script>
<?php include 'includes/footer.php'; ?>
