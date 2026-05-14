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
        /* Public apply — full-width friendly: tinted edges + wide content sheet */
        html {
            min-height: 100%;
            background: #e2e8f0;
            background-image:
                radial-gradient(ellipse 100% 70% at 50% -15%, rgba(var(--primary-rgb, 108, 76, 241), 0.18), transparent 50%),
                radial-gradient(ellipse 55% 45% at 0% 40%, rgba(var(--primary-rgb, 108, 76, 241), 0.08), transparent 50%),
                radial-gradient(ellipse 55% 45% at 100% 60%, rgba(var(--primary-rgb, 108, 76, 241), 0.08), transparent 50%),
                linear-gradient(165deg, #eef2ff 0%, #f5f6fa 42%, #f1f5f9 100%);
            background-attachment: fixed;
        }

        body.ja-body {
            margin: 0;
            min-height: 100%;
            background: transparent;
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
            border-radius: 16px;
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
        }

        .apply-success-banner strong {
            color: var(--primary-dark, #5839d6);
        }

        .ja-title-banner {
            text-align: center;
            padding: 16px 20px;
            margin-bottom: 26px;
            background: rgba(var(--primary-rgb, 108, 76, 241), 0.06);
            border: 1px solid rgba(var(--primary-rgb, 108, 76, 241), 0.18);
            border-radius: 10px;
        }

        .ja-title-banner h1 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-dark, #5839d6);
            letter-spacing: -0.02em;
        }

        .ja-intro {
            margin: 0 0 28px;
            font-size: 15px;
            color: var(--text-dark, #2b2b2b);
            line-height: 1.75;
        }

        .ja-role-head {
            margin-bottom: 10px;
            border-bottom: 1px solid var(--border-color, #ececf3);
        }

        .ja-role-head h2 {
            margin: 0 0 10px;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-dark, #2b2b2b);
            letter-spacing: -0.02em;
        }

        .ja-section-title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-light, #64748b);
            margin: 0 0 14px;
        }

        .job-posting-card {
            background: var(--card-bg, #fff);
            border: 1px solid var(--border-color, #ececf3);
            border-radius: 12px;
            padding: 22px 24px;
            margin-bottom: 32px;
            box-shadow: var(--shadow-soft, 0 4px 15px rgba(0, 0, 0, 0.05));
        }

        .job-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px 28px;
            margin-bottom: 18px;
        }

        @media (max-width: 560px) {
            .job-meta-grid {
                grid-template-columns: 1fr;
            }
        }

        .job-meta-item dt {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-light, #64748b);
            margin-bottom: 6px;
        }

        .job-meta-item dd {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-dark, #2b2b2b);
        }

        .job-desc-block .job-desc-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-light, #64748b);
            display: block;
            margin-bottom: 8px;
        }

        .job-desc-body {
            font-size: 14px;
            line-height: 1.65;
            color: var(--text-dark);
            margin: 0;
            white-space: pre-wrap;
            padding: 16px 18px;
            background: #f8fafc;
            border: 1px solid var(--border-color, #ececf3);
            border-radius: 10px;
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

        .ja-help {
            font-size: 14px;
            color: var(--text-dark);
            margin: 0 0 22px;
            line-height: 1.6;
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

        <div class="ja-title-banner">
            <h1>Job Application</h1>
        </div>

        <p class="ja-intro">
            Are you someone who stays organized under pressure, enjoys working with people across teams, and takes pride
            in getting the details right?
            Do you want to grow with a company that values clarity, respect, and steady improvement?
            If so, you should consider applying for the role below.
        </p>

        <div class="ja-role-head">
            <h2 id="applyJobTitle">Operations Executive</h2>
        </div>

        <p class="ja-section-title">Job posting details</p>
        <section class="job-posting-card" aria-label="Job posting summary">
            <div class="job-meta-grid">
                <div class="job-meta-item">
                    <dt>Job title *</dt>
                    <dd id="applyJobTitleMeta">Operations Executive</dd>
                </div>
                <div class="job-meta-item">
                    <dt>Department *</dt>
                    <dd id="applyJobDept">Operations</dd>
                </div>
                <div class="job-meta-item">
                    <dt>Location *</dt>
                    <dd id="applyJobLocation">North Nazimabad, Karachi (on-site)</dd>
                </div>
                <div class="job-meta-item">
                    <dt>Employment type *</dt>
                    <dd id="applyJobType">Full-time</dd>
                </div>
            </div>
            <div class="job-desc-block">
                <span class="job-desc-label">Job description *</span>
                <p id="applyJobDesc" class="job-desc-body">We are looking for an Operations Executive to keep our
                    day-to-day work organized and efficient. You will track tasks, liaise with internal teams, prepare
                    simple reports, and follow up on action items from leadership.

                    Key responsibilities:
                    • Support scheduling, documentation, and operational follow-ups.
                    • Coordinate with HR, IT, and department heads on routine requests.
                    • Maintain accurate records and help improve our internal checklists.
                    • Communicate clearly in English and Urdu with staff and vendors.

                    What we expect:
                    • Bachelor’s degree or equivalent experience in operations / admin / business.
                    • 1–3 years in a similar coordination or operations role (fresh grads with strong organization
                    skills may apply).
                    • Strong attention to detail, MS Office comfort, and a professional attitude.

                    We are an equal-opportunity employer. We review every application carefully.</p>
            </div>
        </section>

        <p class="ja-section-title">Your application</p>
        <p class="ja-help">Required fields are marked with *. Please answer honestly; the hiring team may use this
            information to follow up with you.</p>

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
                    <input type="file" id="appResume" class="hidden" accept=".pdf,.doc,.docx,application/pdf" required
                        onchange="handleFileSelect(this)">
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