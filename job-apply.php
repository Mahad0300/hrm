<?php
$jobTitleForMeta = '';

function jobApplySlug($title) {
    $slug = strtolower((string) $title);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

try {
    $jobId = $_GET['jobId'] ?? ($_GET['jobid'] ?? '');
    $jobSlug = jobApplySlug(trim($_GET['job'] ?? ''));

    if ($jobId !== '' || $jobSlug !== '') {
        require_once __DIR__ . '/includes/db_connect.php';

        if ($jobId !== '') {
            $stmt = $pdo->prepare("SELECT title FROM jobs WHERE id = ? AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([$jobId]);
            $jobTitleForMeta = (string) ($stmt->fetchColumn() ?: '');
        } elseif ($jobSlug !== '') {
            $stmt = $pdo->query("SELECT title FROM jobs WHERE deleted_at IS NULL ORDER BY created_at DESC");
            foreach ($stmt->fetchAll() as $jobRow) {
                if (jobApplySlug($jobRow['title']) === $jobSlug) {
                    $jobTitleForMeta = (string) $jobRow['title'];
                    break;
                }
            }
        }
    }
} catch (Throwable $e) {
    $jobTitleForMeta = '';
}

$pageTitle = $jobTitleForMeta !== '' ? $jobTitleForMeta . ' | Job Application' : 'Job Application';
$safePageTitle = htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8');
?>
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Public apply — plain page background */
        html,
        body.ja-body {
            min-height: 100%;
            background: #fff;
        }

        body.ja-body {
            margin: 0;
            color: var(--text-dark, #2b2b2b);
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .ja-page {
            max-width: min(1120px, 96vw);
            margin: clamp(16px, 3vw, 32px) auto clamp(32px, 5vw, 56px);
            padding: clamp(28px, 4vw, 48px) clamp(20px, 4vw, 44px) clamp(40px, 5vw, 64px);
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid rgba(236, 236, 243, 0.95);
            box-shadow:
                0 4px 6px -1px rgba(15, 23, 42, 0.05),
                0 24px 48px -12px rgba(15, 23, 42, 0.1);
        }

        .apply-success-banner {
            display: none;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 10px;
            background: rgba(var(--primary-rgb, 108, 76, 241), 0.08);
            border: 1px solid rgba(var(--primary-rgb, 108, 76, 241), 0.22);
            color: var(--text-dark, #2b2b2b);
            font-size: 14px;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .apply-success-banner.is-visible {
            display: flex;
            align-items: center;
        }

        .apply-success-banner strong {
            color: var(--primary-dark, #5839d6);
        }

        .ja-page-header {
            margin-bottom: 28px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border-color, #ececf3);
        }

        .ja-page-header__inner {
            display: flex;
            align-items: flex-start;
            gap: clamp(18px, 3vw, 32px);
        }

        .ja-page-header__brand {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            line-height: 0;
        }

        .ja-page-header__logo {
            display: block;
            width: auto;
            max-width: 148px;
            height: auto;
            max-height: 80px;
            object-fit: contain;
        }

        .ja-page-header__content {
            flex: 1;
            min-width: 0;
        }

        @media (max-width: 560px) {
            .ja-page-header__inner {
                flex-direction: column;
                align-items: center;
            }

            .ja-page-header__logo {
                max-width: 128px;
                max-height: 44px;
            }
        }

        .ja-page-header__eyebrow {
            margin: 0px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-light, #64748b);
        }

        .ja-page-header__title {
            margin: 0px;
            font-size: clamp(1.5rem, 3vw, 1.85rem);
            font-weight: 700;
            color: var(--text-dark, #0f172a);
            letter-spacing: -0.03em;
            line-height: 1.2;
        }

        .ja-intro {
            margin: 0;
            font-size: 14px;
            color: var(--text-light, #64748b);
            line-height: 1.65;
        }

        .ja-role-block {
            margin: 28px 0 24px;
        }

        .ja-role-title {
            margin: 0;
            font-size: clamp(1.35rem, 2.5vw, 1.65rem);
            font-weight: 700;
            color: var(--text-dark, #0f172a);
            letter-spacing: -0.03em;
            line-height: 1.25;
        }

        .ja-section-head {
            margin-bottom: 16px;
        }

        .ja-section-head--form {
            margin-top: 36px;
            padding-top: 28px;
            border-top: 1px solid var(--border-color, #ececf3);
        }

        .ja-section-head--form .ja-section-head__title {
            color: #475569;
        }

        .ja-section-head--form .ja-section-head__sub {
            color: #334155;
        }

        .ja-section-head__title {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-light, #64748b);
        }

        .ja-section-head__sub {
            margin: 8px 0 0;
            font-size: 14px;
            color: var(--text-light, #64748b);
            line-height: 1.5;
        }

        .ja-posting-panel {
            margin-bottom: 8px;
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
            padding: 14px 14px;
            background: #fafafa;
            border: 1px solid #f1f5f9;
            border-radius: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .ja-stat:hover {
            border-color: rgba(108, 76, 241, 0.15);
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        }

        .ja-stat--full {
            grid-column: 1 / -1;
        }

        .ja-stat--desc {
            margin-bottom: 4px;
        }

        .ja-stat__icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(108, 76, 241, 0.08);
            color: var(--primary-color, #6c4cf1);
        }

        .ja-stat__icon svg {
            width: 18px;
            height: 18px;
        }

        .ja-stat__body {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .ja-stat__label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-light, #64748b);
        }

        .ja-stat__value {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark, #0f172a);
            line-height: 1.35;
        }

        .ja-stat__value--desc {
            font-weight: 500;
            line-height: 1.65;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .ja-closed-notice {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 0 0 24px;
            padding: 14px 18px;
            border-radius: 12px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            line-height: 1.5;
        }

        .ja-closed-notice svg {
            flex-shrink: 0;
        }

        @media (max-width: 560px) {
            .ja-details-stats {
                grid-template-columns: 1fr;
            }

            .ja-stat--full {
                grid-column: 1;
            }
        }

        .ja-form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 24px;
            margin-bottom: 20px;
        }

        @media (max-width: 560px) {
            .ja-form-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .ja-form-full {
            margin-bottom: 20px;
        }

        .ja-page .form-group {
            margin-bottom: 0;
        }

        /* Using global .admin-form-label from style.css */

        .ja-page .form-control,
        .ja-page input.form-control,
        .ja-page select.form-control {
            width: 100%;
            box-sizing: border-box;
            min-height: 52px;
            padding: 14px 16px;
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 15px;
            color: var(--text-dark, #2b2b2b);
            background: #fff;
            border: 1px solid var(--border-color, #ececf3);
            border-radius: 10px;
            height: auto;
            transition: var(--transition);
        }

        .ja-page .form-control::placeholder {
            color: #94a3b8;
        }

        .ja-page .form-control:focus {
            outline: none;
            border-color: var(--primary-color, #6c4cf1) !important;
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 108, 76, 241), 0.2) !important;
            background: #fff !important;
        }

        .ja-file-zone {
            border: 2px dashed #d8d4f0 !important;
            border-radius: 10px !important;
            background: rgba(var(--primary-rgb, 108, 76, 241), 0.04) !important;
            padding: 24px !important;
            transition: border-color 0.2s, background 0.2s;
        }

        .ja-file-zone:hover {
            border-color: var(--primary-light, #8a6fff) !important;
            background: rgba(var(--primary-rgb, 108, 76, 241), 0.08) !important;
        }

        .ja-page .hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .ja-file-zone--error {
            border-color: #ef4444 !important;
            background: rgba(239, 68, 68, 0.06) !important;
        }

        .ja-file-zone .ja-file-hint {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark, #2b2b2b);
        }

        .ja-file-zone .ja-file-sub {
            font-size: 13px;
            color: var(--text-light, #64748b);
            margin-top: 6px;
        }

        .ja-file-name {
            font-size: 13px;
            margin-top: 8px;
            color: var(--primary-color, #6c4cf1);
            font-weight: 600;
        }

        .ja-upload-icon {
            color: var(--primary-color, #6c4cf1);
            opacity: 0.55;
        }

        /* Compact upload (CNIC etc.) — no default file button */
        .ja-file-zone--compact {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 12px !important;
            padding: 16px 18px !important;
            min-height: 72px;
            text-align: left;
        }

        .ja-file-zone--compact .ja-upload-icon-sm {
            flex-shrink: 0;
            color: var(--primary-color, #6c4cf1);
            opacity: 0.65;
        }

        .ja-file-zone--compact .ja-file-zone-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .ja-file-zone--compact .ja-file-hint {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark, #2b2b2b);
        }

        .ja-file-zone--compact .ja-file-sub {
            font-size: 12px;
            margin-top: 0;
        }

        .ja-file-name--sm {
            font-size: 12px;
            word-break: break-all;
        }

        .ja-submit-wrap {
            margin-top: 36px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color, #ececf3);
            text-align: center;
        }

        .ja-btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 40px;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            background: var(--primary-color, #6c4cf1);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(var(--primary-rgb, 108, 76, 241), 0.35);
            transition: background 0.2s, transform 0.15s;
        }

        .ja-btn-submit:hover {
            background: var(--primary-dark, #5839d6);
        }

        .ja-btn-submit:active {
            transform: translateY(1px);
        }

        #dynamicQuestions .apply-section-label {
            color: var(--text-light, #64748b);
        }
    </style>
</head>

<body class="ja-body">

    <div class="ja-page">
        <div id="applySuccessBanner" class="apply-success-banner" role="status">
            <i data-lucide="check-circle" size="22" style="flex-shrink:0;margin-top:2px;"></i>
            <div>
                <strong>Application received.</strong> Thank you — our hiring team will review your submission and
                contact you if there is a fit.
            </div>
        </div>

        <header class="ja-page-header">
            <div class="ja-page-header__inner">
                <div class="ja-page-header__brand">
                    <img src="images/loginimage/logo.png" alt="Richmond Tech Group" class="ja-page-header__logo"
                        width="160" height="48">
                </div>
                <div class="ja-page-header__content">
                    <p class="ja-page-header__eyebrow">Careers</p>
                    <h1 class="ja-page-header__title">Job Application</h1>
                    <p class="ja-intro" id="jaIntro">
                        Complete the form below to apply for this role. Our hiring team reviews every submission and will
                        contact shortlisted candidates.
                    </p>
                </div>
            </div>
        </header>

        <div class="ja-role-block">
            <h2 class="ja-role-title" id="applyJobTitle">—</h2>
        </div>

        <div class="ja-section-head">
            <h3 class="ja-section-head__title">Job posting details</h3>
        </div>
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

        <div class="ja-section-head ja-section-head--form" id="jaFormSection">
            <h3 class="ja-section-head__title">Your application</h3>
            <p class="ja-section-head__sub">Required fields are marked with *. Please provide accurate information.</p>
        </div>

            <form id="jobApplyForm">
            <div class="ja-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label" for="appFullName">Full name *</label>
                    <input type="text" id="appFullName" class="form-control" placeholder="Full name" required
                        autocomplete="name">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="appEmail">Email address *</label>
                    <input type="email" id="appEmail" class="form-control" placeholder="Email address" required
                        autocomplete="email">
                </div>
            </div>

            <div class="ja-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label" for="appPhone">Phone number *</label>
                    <input type="tel" id="appPhone" class="form-control" placeholder="0000-0000000" required
                        autocomplete="tel">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="appCnicNumber">CNIC Number *</label>
                    <input type="text" id="appCnicNumber" class="form-control" placeholder="e.g. 00000-0000000-0" required
                        autocomplete="off">
                </div>
            </div>

            <div class="form-group ja-form-full mb-20">
                <label class="admin-form-label" for="appAddress">Residential Address *</label>
                <textarea id="appAddress" class="form-control" placeholder="Complete address" required style="min-height: 100px;"></textarea>
            </div>

            <div id="dynamicQuestions" class="mb-8"></div>

            <div class="form-group ja-form-full mb-20">
                <label class="admin-form-label">Resume / CV *</label>
                <div class="file-upload-wrapper ja-file-zone flex-center flex-column gap-12 cursor-pointer"
                    onclick="document.getElementById('appResume').click()">
                    <i data-lucide="upload-cloud" size="30" class="ja-upload-icon"></i>
                    <div class="text-center">
                        <div class="ja-file-hint">Click to upload or drag and drop</div>
                        <div class="ja-file-sub">PDF or DOCX, up to 5MB</div>
                    </div>
                    <input type="file" id="appResume" class="hidden" accept=".pdf,.doc,.docx,application/pdf"
                        aria-required="true" onchange="handleFileSelect(this)">
                </div>
                <div id="fileName" class="ja-file-name"></div>
                </div>

            <div class="ja-submit-wrap">
                <button type="submit" class="ja-btn-submit">
                    <span>Submit application</span>
                        <i data-lucide="arrow-right" size="18"></i>
                    </button>
                </div>
            </form>
         </div>

<script>
    function handleFileSelect(input) {
            var name = input.files[0] ? input.files[0].name : '';
            var el = document.getElementById('fileName');
            if (el) el.textContent = name ? ('Selected: ' + name) : '';
            var zone = input.closest('.ja-file-zone');
            if (zone) zone.classList.remove('ja-file-zone--error');
        }

        function handleJaFilePick(input, labelId) {
            var name = input.files[0] ? input.files[0].name : '';
            var el = document.getElementById(labelId);
            if (el) {
                el.textContent = name ? ('✓ ' + name) : '';
            }
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

<script src="admin/assets/js/job-management.js"></script>
<script>
    lucide.createIcons();
</script>
</body>

</html>