<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joining Form</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin/assets/css/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        html,
        body.nj-body {
            min-height: 100%;
            background: #fff;
        }

        body.nj-body {
            margin: 0;
            color: var(--text-dark, #2b2b2b);
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .nj-page {
            max-width: min(1120px, 96vw);
            margin: clamp(16px, 3vw, 32px) auto clamp(32px, 5vw, 56px);
            padding: clamp(28px, 4vw, 48px) clamp(20px, 4vw, 44px) clamp(40px, 5vw, 64px);
            background: #fff;
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid rgba(236, 236, 243, 0.95);
            box-shadow:
                0 4px 6px -1px rgba(15, 23, 42, 0.05),
                0 24px 48px -12px rgba(15, 23, 42, 0.1);
        }

        .nj-page-header {
            margin-bottom: 28px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border-color, #ececf3);
        }

        .nj-page-header__inner {
            display: flex;
            align-items: center;
            gap: clamp(18px, 3vw, 32px);
        }

        .nj-page-header__brand {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            line-height: 0;
        }

        .nj-page-header__logo {
            display: block;
            width: auto;
            max-width: 148px;
            height: auto;
            max-height: 80px;
            object-fit: contain;
        }

        .nj-page-header__content {
            flex: 1;
            min-width: 0;
        }

        @media (max-width: 560px) {
            .nj-page-header__inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .nj-page-header__logo {
                max-width: 128px;
                max-height: 44px;
            }
        }

        .nj-page-header__eyebrow {
            margin: 0px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-light, #64748b);
        }

        .nj-page-header__title {
            margin: 0px;
            font-size: clamp(1.5rem, 3vw, 1.85rem);
            font-weight: 700;
            color: var(--text-dark, #0f172a);
            letter-spacing: -0.03em;
            line-height: 1.2;
        }

        .nj-intro {
            margin: 0;
            font-size: 14px;
            color: var(--text-light, #64748b);
            line-height: 1.65;
        }

        .nj-section-head {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 36px 0 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-color, #ececf3);
        }

        .nj-section-head:first-of-type {
            margin-top: 0;
        }

        .nj-section-icon {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(var(--primary-rgb, 108, 76, 241), 0.1);
            color: var(--primary-color, #6c4cf1);
            border: 1px solid rgba(var(--primary-rgb, 108, 76, 241), 0.22);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .nj-section-icon svg {
            flex-shrink: 0;
        }

        .nj-section-head h2 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark, #0f172a);
            letter-spacing: -0.02em;
            line-height: 1.3;
        }

        @media (max-width: 480px) {
            .nj-section-head {
                gap: 12px;
            }

            .nj-section-icon {
                width: 44px;
                height: 44px;
            }

            .nj-section-head h2 {
                font-size: 1rem;
            }
        }

        .nj-form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 24px;
            margin-bottom: 20px;
        }

        @media (max-width: 560px) {
            .nj-form-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .nj-span-2 {
            grid-column: 1 / -1;
        }

        .nj-page .form-group {
            margin-bottom: 0;
        }

        /* Using global .admin-form-label from style.css */

        .nj-page .form-control,
        .nj-page input.form-control,
        .nj-page select.form-control,
        .nj-page textarea.form-control {
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

        .nj-page textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .nj-page .form-control--muted {
            background: #f1f5f9 !important;
        }

        .nj-page .form-control::placeholder {
            color: #94a3b8;
        }

        .nj-page .form-control:focus {
            outline: none;
            border-color: var(--primary-color, #6c4cf1) !important;
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 108, 76, 241), 0.2) !important;
            background: #fff !important;
        }

        .nj-page .form-control--muted:focus {
            background: #f8fafc !important;
        }

        .nj-file-zone {
            border: 2px dashed #d8d4f0 !important;
            border-radius: 10px !important;
            background: rgba(var(--primary-rgb, 108, 76, 241), 0.04) !important;
            padding: 20px !important;
            transition: border-color 0.2s, background 0.2s;
            cursor: pointer;
        }

        .nj-file-zone:hover {
            border-color: var(--primary-light, #8a6fff) !important;
            background: rgba(var(--primary-rgb, 108, 76, 241), 0.08) !important;
        }

        .nj-file-zone .nj-file-hint {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark, #2b2b2b);
        }

        .nj-file-zone .nj-file-sub {
            font-size: 13px;
            color: var(--text-light, #64748b);
            margin-top: 6px;
        }

        .nj-file-name {
            font-size: 13px;
            margin-top: 8px;
            color: var(--primary-color, #6c4cf1);
            font-weight: 600;
        }

        .nj-upload-icon {
            color: var(--primary-color, #6c4cf1);
            opacity: 0.55;
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

        .hidden {
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

        .nj-file-zone--error {
            border-color: #ef4444 !important;
            background: rgba(239, 68, 68, 0.06);
        }

        .nj-success-banner {
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

        .nj-success-banner.is-visible {
            display: flex;
        }

        .nj-success-banner strong {
            color: var(--primary-dark, #5839d6);
        }
    </style>
</head>

<body class="nj-body">

    <div class="nj-page">
        <div id="njSuccessBanner" class="nj-success-banner" role="status">
            <i data-lucide="check-circle" width="22" height="22" style="flex-shrink:0;margin-top:2px;"></i>
            <div>
                <strong>Joining form submitted.</strong> Thank you for providing your details. Our HR team will review
                your information soon.
            </div>
        </div>

        <header class="nj-page-header">
            <div class="nj-page-header__inner">
                <div class="nj-page-header__brand">
                    <img src="images/loginimage/logo.png" alt="Richmond Tech Group" class="nj-page-header__logo"
                        width="160" height="48">
                </div>
                <div class="nj-page-header__content">
                    <p class="nj-page-header__eyebrow">Onboarding</p>
                    <h1 class="nj-page-header__title">Joining Form</h1>
                    <p class="nj-intro">
                        Please complete all sections below. Fields marked with * are required.
                        Attachments should be clear PDF or image files unless your HR team specifies otherwise.
                    </p>
                </div>
            </div>
        </header>

        <form id="njHireForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="source" value="joining_form">

            <div class="nj-section-head">
                <div class="nj-section-icon" aria-hidden="true">
                    <i data-lucide="user-round" width="22" height="22"></i>
                </div>
                <h2 id="nj-sec-personal">Personal Details</h2>
            </div>
            <div class="nj-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label" for="nj_first_name">First Name *</label>
                    <input type="text" id="nj_first_name" name="first_name" class="form-control"
                        placeholder="First name" required autocomplete="given-name">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_middle_name">Middle Name</label>
                    <input type="text" id="nj_middle_name" name="middle_name" class="form-control"
                        placeholder="Middle Name" autocomplete="additional-name">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_last_name">Last Name *</label>
                    <input type="text" id="nj_last_name" name="last_name" class="form-control" placeholder="Last name"
                        required autocomplete="family-name">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_dob">Date of Birth *</label>
                    <input type="date" id="nj_dob" name="dob" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_cnic">ID Card Number *</label>
                    <input type="text" id="nj_cnic" name="cnic_number" class="form-control"
                        placeholder="00000-0000000-0" maxlength="15" required
                        title="Please enter a valid 13-digit CNIC (12345-1234567-1)" inputmode="numeric">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_gender">Gender *</label>
                    <select id="nj_gender" name="gender" class="form-control" required>
                        <option value="">Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_phone">Phone *</label>
                    <input type="tel" id="nj_phone" name="phone" class="form-control" placeholder="03XXXXXXXXX"
                        autocomplete="tel" inputmode="numeric" maxlength="12" required
                        title="Please enter a valid 11-digit phone number">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_email">Email *</label>
                    <input type="email" id="nj_email" name="email" class="form-control" placeholder="you@email.com"
                        required autocomplete="email">
                    <div id="email_feedback" class="nj-file-sub" style="margin-top: 6px; min-height: 18px; font-weight: 500;"></div>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_address">Address *</label>
                    <input type="text" id="nj_address" name="address" class="form-control" placeholder="Your address"
                        autocomplete="street-address" required>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_job_title">Job Title *</label>
                    <input type="text" id="nj_job_title" name="job_title" class="form-control"
                        placeholder="Your job title" required>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_emergency_phone">Emergency Contact *</label>
                    <input type="tel" id="nj_emergency_phone" name="emergency_contact" class="form-control"
                        placeholder="03XXXXXXXXX" autocomplete="tel" inputmode="numeric" maxlength="12" required
                        title="Please enter a valid 11-digit phone number">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_emergency_relation">Emergency Contact Relation *</label>
                    <input type="text" id="nj_emergency_relation" name="emergency_relation" class="form-control"
                        placeholder="Father, mother, brother, sister, spouse, etc." required>
                </div>
            </div>

            <div class="nj-section-head">
                <div class="nj-section-icon" aria-hidden="true">
                    <i data-lucide="landmark" width="22" height="22"></i>
                </div>
                <h2 id="nj-sec-bank">Job & Banking</h2>
            </div>
            <div class="nj-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label" for="nj_bank_name">Bank Name *</label>
                    <select id="nj_bank_name" name="bank_name" class="form-control" required>
                        <option value="">Select Bank</option>
                        <option value="HBL">HBL</option>
                        <option value="ALHabib">AL Habib</option>
                        <option value="MCB">MCB</option>
                        <option value="UBL">UBL</option>
                        <option value="Meezan">Meezan</option>
                        <option value="Allied">Allied</option>
                        <option value="Bank Alfalah">Bank Alfalah</option>
                        <option value="Askari">Askari</option>
                        <option value="Faysal">Faysal</option>
                        <option value="Habib Metro">Habib Metro</option>
                        <option value="Soneri">Soneri</option>
                        <option value="JS Bank">JS Bank</option>
                        <option value="Bank Islami">Bank Islami</option>
                        <option value="Standard Chartered">Standard Chartered</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_account_type">Account Type *</label>
                    <select id="nj_account_type" name="account_type" class="form-control" required>
                        <option value="">Select account type</option>
                        <option value="IBN">IBN</option>
                        <option value="IBFT">IBFT</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_account_title">Account Title *</label>
                    <input type="text" id="nj_account_title" name="account_title" class="form-control"
                        placeholder="Account title" required>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_account_number">Account Number *</label>
                    <input type="text" id="nj_account_number" name="account_number"
                        class="form-control form-control--muted" placeholder="Account number" inputmode="numeric"
                        autocomplete="off" required>
                </div>
                <div class="form-group nj-span-2">
                    <label class="admin-form-label" for="nj_bank_branch">Bank Branch *</label>
                    <input type="text" id="nj_bank_branch" name="branch_info" class="form-control"
                        placeholder="Bank branch" required>
                </div>
            </div>

            <div class="nj-section-head">
                <div class="nj-section-icon" aria-hidden="true">
                    <i data-lucide="graduation-cap" width="22" height="22"></i>
                </div>
                <h2 id="nj-sec-education">Education & Experience</h2>
            </div>
            <div class="nj-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label" for="nj_qualification">Qualification *</label>
                    <input type="text" id="nj_qualification" name="qualification" class="form-control"
                        placeholder="Qualification" required>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_degree">Degree / Certification *</label>
                    <input type="text" id="nj_degree" name="degree_certification" class="form-control"
                        placeholder="Degree or certification" required>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_expertise">Professional Expertise *</label>
                    <input type="text" id="nj_expertise" name="professional_expertise" class="form-control"
                        placeholder="Professional expertise" required>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_college">College / University *</label>
                    <input type="text" id="nj_college" name="college_university" class="form-control"
                        placeholder="College / university" required>
                </div>
            </div>

            <div class="nj-section-head">
                <div class="nj-section-icon" aria-hidden="true">
                    <i data-lucide="briefcase" width="22" height="22"></i>
                </div>
            </div>
            <div class="nj-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label" for="nj_last_employer">Last Employer *</label>
                    <input type="text" id="nj_last_employer" name="last_employer" class="form-control"
                        placeholder="Last employer" required>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_last_designation">Last Job Title *</label>
                    <input type="text" id="nj_last_designation" name="last_designation" class="form-control"
                        placeholder="Last job title" required>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_exp_from">Experience From Date</label>
                    <input type="date" id="nj_exp_from" name="experience_from" class="form-control">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_exp_to">Experience To Date</label>
                    <input type="date" id="nj_exp_to" name="experience_to" class="form-control">
                </div>
            </div>

            <div class="nj-section-head">
                <div class="nj-section-icon" aria-hidden="true">
                    <i data-lucide="paperclip" width="22" height="22"></i>
                </div>
                <h2 id="nj-sec-docs">Document Attachments</h2>
            </div>
            <div class="nj-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label" for="nj_resume">Resume Attachment *</label>
                    <div class="nj-file-zone flex-center flex-column gap-12"
                        onclick="document.getElementById('nj_resume').click()">
                        <i data-lucide="upload-cloud" width="30" height="30" class="nj-upload-icon"></i>
                        <div class="text-center">
                            <div class="nj-file-hint">Click to upload resume</div>
                            <div class="nj-file-sub">PDF or DOCX, up to 10MB</div>
                        </div>
                        <input type="file" id="nj_resume" name="resume" class="hidden"
                            accept=".pdf,.doc,.docx,application/pdf" aria-required="true">
                    </div>
                    <div id="nj_resume_label" class="nj-file-name" aria-live="polite"></div>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_id_card">ID Card Attachment *</label>
                    <div class="nj-file-zone flex-center flex-column gap-12"
                        onclick="document.getElementById('nj_id_card').click()">
                        <i data-lucide="id-card" width="30" height="30" class="nj-upload-icon"></i>
                        <div class="text-center">
                            <div class="nj-file-hint">National ID / CNIC</div>
                            <div class="nj-file-sub">PDF, PNG, JPG (Mandatory)</div>
                        </div>
                        <input type="file" id="nj_id_card" name="id_card" class="hidden"
                            accept=".pdf,.png,.jpg,.jpeg,.webp,image/*,application/pdf" aria-required="true">
                    </div>
                    <div id="nj_id_card_label" class="nj-file-name" aria-live="polite"></div>
                </div>
                <div class="form-group nj-span-2">
                    <label class="admin-form-label" for="nj_other_docs">Other Documents</label>
                    <div class="nj-file-zone flex-center flex-column gap-12"
                        onclick="document.getElementById('nj_other_docs').click()">
                        <i data-lucide="files" width="30" height="30" class="nj-upload-icon"></i>
                        <div class="text-center">
                            <div class="nj-file-hint">Additional documents (optional)</div>
                            <div class="nj-file-sub">PDF or images, multiple files if your backend supports it</div>
                        </div>
                        <input type="file" id="nj_other_docs" name="other_documents[]" class="hidden"
                            accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,image/*" multiple>
                    </div>
                    <div id="nj_other_docs_label" class="nj-file-name" aria-live="polite"></div>
                </div>
            </div>

            <div class="ja-submit-wrap">
                <button type="submit" class="ja-btn-submit">
                    <span>Submit</span>
                    <i data-lucide="arrow-right" width="18" height="18"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // --- User-Friendly Input Formatting ---

        // CNIC Formatting (12345-1234567-1)
        document.getElementById('nj_cnic').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
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

        // Phone Formatting (03XX-XXXXXXX)
        const setupPhoneMask = (id) => {
            document.getElementById(id).addEventListener('input', function (e) {
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
        };
        setupPhoneMask('nj_phone');
        setupPhoneMask('nj_emergency_phone');

        // --- Real-time Email Verification ---
        const emailInput = document.getElementById('nj_email');
        const emailFeedback = document.getElementById('email_feedback');

        emailInput.addEventListener('blur', async function() {
            const email = this.value.trim();
            if (email === '') {
                emailFeedback.textContent = '';
                return;
            }

            // Basic email regex for quick client validation
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(email)) {
                emailFeedback.textContent = 'Please enter a valid email format.';
                emailFeedback.style.color = '#ef4444';
                return;
            }

            emailFeedback.textContent = 'Checking availability...';
            emailFeedback.style.color = '#64748b';

            try {
                const response = await fetch(`admin/assets/api/employee_handler.php?action=check_email&email=${encodeURIComponent(email)}`);
                const result = await response.json();

                if (result.status === 'success') {
                    emailFeedback.textContent = '✅ ' + result.message;
                    emailFeedback.style.color = '#10b981';
                    emailInput.style.borderColor = '#10b981';
                } else if (result.status === 'exited') {
                    emailFeedback.textContent = '⚠️ ' + result.message;
                    emailFeedback.style.color = '#d97706';
                    emailInput.style.borderColor = '#f59e0b';
                } else {
                    emailFeedback.textContent = '⚠️ ' + result.message;
                    emailFeedback.style.color = '#ef4444';
                    emailInput.style.borderColor = '#ef4444';
                }
            } catch (error) {
                emailFeedback.textContent = '';
            }
        });

        function njFileLabel(input, labelId) {
            var el = document.getElementById(labelId);
            if (!el) return;
            if (input.multiple && input.files && input.files.length) {
                var names = Array.prototype.map.call(input.files, function (f) { return f.name; });
                el.textContent = names.join(', ');
            } else {
                el.textContent = input.files && input.files[0] ? input.files[0].name : '';
            }
        }

        document.getElementById('nj_resume').addEventListener('change', function () {
            njFileLabel(this, 'nj_resume_label');
            var zone = this.closest('.form-group') && this.closest('.form-group').querySelector('.nj-file-zone');
            if (zone) zone.classList.remove('nj-file-zone--error');
        });
        document.getElementById('nj_id_card').addEventListener('change', function () {
            njFileLabel(this, 'nj_id_card_label');
            var zone = this.closest('.form-group') && this.closest('.form-group').querySelector('.nj-file-zone');
            if (zone) zone.classList.remove('nj-file-zone--error');
        });
        document.getElementById('nj_other_docs').addEventListener('change', function () {
            njFileLabel(this, 'nj_other_docs_label');
        });

        function njClearFileErrors() {
            document.querySelectorAll('.nj-file-zone--error').forEach(function (el) {
                el.classList.remove('nj-file-zone--error');
            });
        }

        function njValidateVisibleFields(form) {
            var skip = { file: 1, hidden: 1, button: 1, submit: 1, reset: 1 };
            var fields = form.querySelectorAll('input, select, textarea');
            for (var i = 0; i < fields.length; i++) {
                var field = fields[i];
                if (skip[field.type]) continue;
                if (!field.checkValidity()) {
                    field.reportValidity();
                    field.focus();
                    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
            }
            return true;
        }

        function njValidateFileAttachment(inputId, message) {
            var input = document.getElementById(inputId);
            var zone = input && input.closest('.form-group')
                ? input.closest('.form-group').querySelector('.nj-file-zone')
                : null;
            if (input && input.files && input.files.length > 0) {
                if (zone) zone.classList.remove('nj-file-zone--error');
                return true;
            }
            njClearFileErrors();
            if (zone) {
                zone.classList.add('nj-file-zone--error');
                zone.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            Swal.fire({
                title: 'Required Field',
                text: message,
                icon: 'warning',
                confirmButtonColor: '#6c4cf1'
            });
            return false;
        }

        document.getElementById('njHireForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            njClearFileErrors();

            if (!njValidateVisibleFields(this)) {
                return;
            }

            if (!njValidateFileAttachment('nj_resume', 'Please upload your Resume Attachment to continue.')) {
                return;
            }
            if (!njValidateFileAttachment('nj_id_card', 'Please upload your ID Card Attachment to continue.')) {
                return;
            }

            const submitBtn = this.querySelector('.ja-btn-submit');
            const originalText = submitBtn.innerHTML;

            // UI state: Processing
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Uploading Data...</span><i data-lucide="loader-2" class="spin"></i>';
            lucide.createIcons();

            const formData = new FormData(this);

            try {
                const response = await fetch('admin/assets/api/employee_handler.php?action=onboard', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const result = await response.json();

                if (result.status === 'success') {
                    Swal.fire({
                        title: 'Form Submitted!',
                        text: 'Your joining details have been received. Our HR team will reach out to you shortly.',
                        icon: 'success',
                        confirmButtonColor: '#6c4cf1',
                        borderRadius: '16px'
                    }).then(() => {
                        this.reset();
                        // Clear file labels
                        document.getElementById('nj_resume_label').textContent = '';
                        document.getElementById('nj_id_card_label').textContent = '';
                        document.getElementById('nj_other_docs_label').textContent = '';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                } else {
                    Swal.fire({
                        title: 'Submission Failed',
                        text: result.message || 'Something went wrong while processing your form.',
                        icon: 'error',
                        confirmButtonColor: '#6c4cf1',
                        borderRadius: '16px'
                    });
                }
            } catch (error) {
                console.error('Submission error:', error);
                Swal.fire({
                    title: 'Connection Error',
                    text: 'We could not connect to the server. Please check your internet and try again.',
                    icon: 'error',
                    confirmButtonColor: '#6c4cf1',
                    borderRadius: '16px'
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                lucide.createIcons();
            }
        });
    </script>
</body>

</html>