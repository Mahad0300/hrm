<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $safePageTitle ?></title>
    <meta name="title" content="<?= $safePageTitle ?>">
    <meta property="og:title" content="<?= $safePageTitle ?>">
    <meta name="twitter:title" content="<?= $safePageTitle ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= \App\Core\View::asset('css/admin/style.css') ?>">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        html, body.ja-body {
            min-height: 100%;
            background: #f8fafc;
            margin: 0;
            color: #1e293b;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .ja-container {
            max-width: 760px;
            margin: clamp(24px, 4vw, 48px) auto;
            padding: 0 16px;
        }

        .ja-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.03);
            padding: clamp(28px, 4vw, 44px);
        }

        .ja-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .ja-logo {
            max-width: 140px;
            height: auto;
        }

        .ja-title {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .ja-subtitle {
            font-size: 14px;
            color: #64748b;
            margin: 4px 0 0 0;
        }

        .apply-success-banner {
            display: none;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 20px;
            border-radius: 12px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            font-size: 14px;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .apply-success-banner.is-visible {
            display: flex;
            align-items: center;
        }

        .ja-role-block {
            margin: 20px 0 20px;
        }

        .ja-role-title {
            margin: 0;
            font-size: clamp(1.35rem, 2.5vw, 1.65rem);
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.03em;
            line-height: 1.25;
        }

        .ja-posting-panel {
            margin-bottom: 28px;
        }

        .ja-details-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .ja-stat {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            background: #fafafa;
            border: 1px solid #f1f5f9;
            border-radius: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .ja-stat:hover {
            border-color: rgba(99, 102, 241, 0.2);
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        }

        .ja-stat--full {
            grid-column: 1 / -1;
        }

        .ja-stat__icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #e0e7ff;
            color: #4f46e5;
        }

        .ja-stat__body {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .ja-stat__label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
        }

        .ja-stat__value {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.35;
        }

        .ja-stat__value--desc {
            font-weight: 500;
            line-height: 1.65;
            white-space: pre-wrap;
            word-break: break-word;
            color: #334155;
        }

        @media (max-width: 560px) {
            .ja-details-stats {
                grid-template-columns: 1fr;
            }
        }

        .form-section-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #6366f1;
            margin-top: 28px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
            margin-left: 8px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 600px) {
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .form-group-full {
            margin-bottom: 24px;
        }

        .form-control-custom {
            width: 100%;
            min-height: 48px;
            padding: 12px 16px;
            font-family: inherit;
            font-size: 14px;
            color: #0f172a;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        label.field-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .resume-upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            background: #f8fafc;
            padding: 32px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .resume-upload-box:hover {
            border-color: #6366f1;
            background: #f5f3ff;
        }

        .upload-icon-wrap {
            width: 52px;
            height: 52px;
            background: #e0e7ff;
            color: #4f46e5;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }

        .upload-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e1b4b;
        }

        .upload-sub {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
        }

        .btn-submit-app {
            width: 100%;
            min-height: 52px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit-app:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 24px -5px rgba(79, 70, 229, 0.5);
        }

        .btn-submit-app:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .allow-wrap {
            white-space: pre-wrap;
            word-break: break-word;
        }
    </style>
</head>

<body class="ja-body">

    <div class="ja-container">
        <div class="ja-card">
            
            <div id="applySuccessBanner" class="apply-success-banner" role="status">
                <i data-lucide="check-circle" size="22" style="flex-shrink:0;"></i>
                <div>
                    <strong>Application received!</strong> Thank you — our hiring team will review your submission and contact you if there is a fit.
                </div>
            </div>

            <header class="ja-header">
                <img src="/images/loginimage/logo.png" alt="Richmond Tech Group" class="ja-logo">
                <div>
                    <h1 class="ja-title">Job Application</h1>
                    <p class="ja-subtitle" id="jaIntro">Complete the form below to apply for this role. Our hiring team reviews every submission.</p>
                </div>
            </header>

            <!-- JOB TITLE BLOCK -->
            <div class="ja-role-block">
                <h2 class="ja-role-title" id="applyJobTitle">—</h2>
            </div>

            <!-- JOB POSTING DETAILS SECTION -->
            <div class="form-section-title" style="margin-top: 0;">Job Posting Details</div>

            <section class="ja-posting-panel" aria-label="Job posting summary">
                <div class="ja-details-stats">
                    <div class="ja-stat">
                        <div class="ja-stat__icon"><i data-lucide="building-2" size="18"></i></div>
                        <div class="ja-stat__body">
                            <span class="ja-stat__label">Department</span>
                            <span class="ja-stat__value" id="applyJobDept">—</span>
                        </div>
                    </div>
                    <div class="ja-stat">
                        <div class="ja-stat__icon"><i data-lucide="calendar" size="18"></i></div>
                        <div class="ja-stat__body">
                            <span class="ja-stat__label">Posted date</span>
                            <span class="ja-stat__value" id="applyJobPostedDate">—</span>
                        </div>
                    </div>
                    <div class="ja-stat ja-stat--full">
                        <div class="ja-stat__icon"><i data-lucide="map-pin" size="18"></i></div>
                        <div class="ja-stat__body">
                            <span class="ja-stat__label">Location</span>
                            <span class="ja-stat__value allow-wrap" id="applyJobLocation">—</span>
                        </div>
                    </div>
                    <div class="ja-stat ja-stat--full ja-stat--desc">
                        <div class="ja-stat__icon"><i data-lucide="file-text" size="18"></i></div>
                        <div class="ja-stat__body">
                            <span class="ja-stat__label">Job description</span>
                            <span class="ja-stat__value ja-stat__value--desc allow-wrap" id="applyJobDesc">—</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 2: PERSONAL INFORMATION -->
            <div class="form-section-title">Personal Information</div>

            <form id="jobApplyForm">
                <div class="form-grid-2">
                    <div>
                        <label class="field-label" for="appFullName">Full Name *</label>
                        <input type="text" id="appFullName" class="form-control-custom" placeholder="Enter your full name" required autocomplete="name">
                    </div>
                    <div>
                        <label class="field-label" for="appEmail">Email Address *</label>
                        <input type="email" id="appEmail" class="form-control-custom" placeholder="Enter your email address" required autocomplete="email">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div>
                        <label class="field-label" for="appPhone">Phone Number *</label>
                        <input type="tel" id="appPhone" class="form-control-custom" placeholder="0300-0000000" maxlength="12" inputmode="numeric" required autocomplete="tel">
                    </div>
                    <div>
                        <label class="field-label" for="appCnicNumber">CNIC / ID Number *</label>
                        <input type="text" id="appCnicNumber" class="form-control-custom" placeholder="42101-0000000-0" maxlength="15" inputmode="numeric" required autocomplete="off">
                    </div>
                </div>

                <div class="form-group-full">
                    <label class="field-label" for="appAddress">Current Address *</label>
                    <textarea id="appAddress" class="form-control-custom" placeholder="Enter your current residential address" rows="3" required style="resize: vertical;"></textarea>
                </div>

                <!-- DYNAMIC QUESTIONS CONTAINER -->
                <div id="dynamicQuestions" class="form-group-full"></div>

                <!-- SECTION 3: UPLOAD RESUME -->
                <div class="form-section-title">Upload Resume</div>

                <div class="form-group-full">
                    <div class="resume-upload-box" onclick="document.getElementById('appResume').click()">
                        <div class="upload-icon-wrap">
                            <i data-lucide="file-text" size="26"></i>
                        </div>
                        <div class="upload-title">Upload your Resume/CV</div>
                        <div class="upload-sub">Standard PDF or DOCX format required (Max 5MB)</div>
                        <div id="fileName" style="margin-top: 10px; font-weight: 700; color: #4f46e5;"></div>
                    </div>
                    <input type="file" id="appResume" class="hidden" accept=".pdf,.doc,.docx,application/pdf" required onchange="handleFileSelect(this)">
                </div>

                <!-- SUBMIT BUTTON -->
                <div style="margin-top: 32px;">
                    <button type="submit" id="submitBtn" class="btn-submit-app">
                        <span>Submit Application</span>
                        <i data-lucide="arrow-right" size="18"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        function handleFileSelect(input) {
            var name = input.files[0] ? input.files[0].name : '';
            var el = document.getElementById('fileName');
            if (el) el.textContent = name ? ('Selected: ' + name) : '';
        }

        // CNIC Auto-Formatting (12345-1234567-1)
        const cnicInput = document.getElementById('appCnicNumber');
        if (cnicInput) {
            cnicInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 13) value = value.substring(0, 13);
                let formatted = '';
                if (value.length > 0) {
                    formatted = value.substring(0, 5);
                    if (value.length > 5) {
                        formatted += '-' + value.substring(5, 12);
                        if (value.length > 12) {
                            formatted += '-' + value.substring(12, 13);
                        }
                    }
                }
                e.target.value = formatted;
            });
        }

        // Phone Auto-Formatting (03XX-XXXXXXX)
        const phoneInput = document.getElementById('appPhone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.substring(0, 11);
                let formatted = '';
                if (value.length > 0) {
                    formatted = value.substring(0, 4);
                    if (value.length > 4) {
                        formatted += '-' + value.substring(4);
                    }
                }
                e.target.value = formatted;
            });
        }

        (function () {
            var params = new URLSearchParams(window.location.search);
            if (params.get('submitted') === '1') {
                var b = document.getElementById('applySuccessBanner');
                if (b) b.classList.add('is-visible');
                try {
                    var cleanQuery = '';
                    if (params.get('job')) {
                        cleanQuery = '?job=' + encodeURIComponent(params.get('job'));
                    } else if (params.get('jobId') || params.get('jobid')) {
                        cleanQuery = '?jobId=' + encodeURIComponent(params.get('jobId') || params.get('jobid'));
                    }
                    history.replaceState({}, '', window.location.pathname + cleanQuery);
                } catch (e) { }
            }
        })();
    </script>

    <script src="<?= \App\Core\View::asset('js/hr/job-management.js') ?>"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>
