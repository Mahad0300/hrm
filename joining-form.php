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

        body.nj-body {
            margin: 0;
            min-height: 100%;
            background: transparent;
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
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(236, 236, 243, 0.95);
            box-shadow:
                0 4px 6px -1px rgba(15, 23, 42, 0.05),
                0 24px 48px -12px rgba(15, 23, 42, 0.1);
        }

        .nj-title-banner {
            text-align: center;
            padding: 16px 20px;
            margin-bottom: 26px;
            background: rgba(var(--primary-rgb, 108, 76, 241), 0.06);
            border: 1px solid rgba(var(--primary-rgb, 108, 76, 241), 0.18);
            border-radius: 10px;
        }

        .nj-title-banner h1 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-dark, #5839d6);
            letter-spacing: -0.02em;
        }

        .nj-intro {
            margin: 0 0 28px;
            font-size: 15px;
            color: var(--text-dark, #2b2b2b);
            line-height: 1.75;
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
            display: none !important;
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

        <div class="nj-title-banner">
            <h1>Joining Form</h1>
        </div>

        <p class="nj-intro">
            Please complete all sections below. Fields marked with <span class="req">*</span> are required.
            Attachments should be clear PDF or image files unless your HR team specifies otherwise.
        </p>

        <form id="njHireForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="source" value="joining_form">

            <div class="nj-section-head">
                <div class="nj-section-icon" aria-hidden="true">
                    <i data-lucide="user-round" width="22" height="22"></i>
                </div>
                <h2 id="nj-sec-personal">Personal information</h2>
            </div>
            <div class="nj-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label" for="nj_first_name">First name <span class="req">*</span></label>
                    <input type="text" id="nj_first_name" name="first_name" class="form-control"
                        placeholder="First name" required autocomplete="given-name">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_middle_name">Middle name</label>
                    <input type="text" id="nj_middle_name" name="middle_name" class="form-control"
                        placeholder="Middle name" autocomplete="additional-name">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_last_name">Last name <span class="req">*</span></label>
                    <input type="text" id="nj_last_name" name="last_name" class="form-control" placeholder="Last name"
                        required autocomplete="family-name">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_dob">Date of birth</label>
                    <input type="date" id="nj_dob" name="dob" class="form-control">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_cnic">ID Card Number <span class="req">*</span></label>
                    <input type="text" id="nj_cnic" name="cnic_number" class="form-control"
                        placeholder="00000-0000000-0" maxlength="15" required
                        title="Please enter a valid 13-digit CNIC (12345-1234567-1)" inputmode="numeric">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_gender">Gender <span class="req">*</span></label>
                    <select id="nj_gender" name="gender" class="form-control" required>
                        <option value="">Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_phone">Phone <span class="req">*</span></label>
                    <input type="tel" id="nj_phone" name="phone" class="form-control" placeholder="03XXXXXXXXX"
                        autocomplete="tel" inputmode="numeric" maxlength="12" required
                        title="Please enter a valid 11-digit phone number">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_email">Email <span class="req">*</span></label>
                    <input type="email" id="nj_email" name="email" class="form-control" placeholder="you@email.com"
                        required autocomplete="email">
                    <div id="email_feedback" class="nj-file-sub" style="margin-top: 6px; min-height: 18px; font-weight: 500;"></div>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_address">Address</label>
                    <input type="text" id="nj_address" name="address" class="form-control" placeholder="Your address"
                        autocomplete="street-address">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_job_title">Job title</label>
                    <input type="text" id="nj_job_title" name="job_title" class="form-control"
                        placeholder="Your job title">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_emergency_phone">Emergency contact <span
                            class="req">*</span></label>
                    <input type="tel" id="nj_emergency_phone" name="emergency_contact" class="form-control"
                        placeholder="03XXXXXXXXX" autocomplete="tel" inputmode="numeric" maxlength="12" required
                        title="Please enter a valid 11-digit phone number">
                </div>
                <div class="form-group nj-span-2">
                    <label class="admin-form-label" for="nj_emergency_relation">Emergency contact relation</label>
                    <input type="text" id="nj_emergency_relation" name="emergency_relation" class="form-control"
                        placeholder="Father, mother, brother, sister, spouse, etc.">
                </div>
            </div>

            <div class="nj-section-head">
                <div class="nj-section-icon" aria-hidden="true">
                    <i data-lucide="landmark" width="22" height="22"></i>
                </div>
                <h2 id="nj-sec-bank">Bank details</h2>
            </div>
            <div class="nj-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label" for="nj_bank_name">Bank name</label>
                    <select id="nj_bank_name" name="bank_name" class="form-control">
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
                    <label class="admin-form-label" for="nj_account_type">Account type</label>
                    <select id="nj_account_type" name="account_type" class="form-control">
                        <option value="">Select account type</option>
                        <option value="IBN">IBN</option>
                        <option value="IBFT">IBFT</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_account_title">Account title</label>
                    <input type="text" id="nj_account_title" name="account_title" class="form-control"
                        placeholder="Account title">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_account_number">Account number</label>
                    <input type="text" id="nj_account_number" name="account_number"
                        class="form-control form-control--muted" placeholder="Account number" inputmode="numeric"
                        autocomplete="off">
                </div>
                <div class="form-group nj-span-2">
                    <label class="admin-form-label" for="nj_bank_branch">Bank branch</label>
                    <input type="text" id="nj_bank_branch" name="branch_info" class="form-control"
                        placeholder="Bank branch">
                </div>
            </div>

            <div class="nj-section-head">
                <div class="nj-section-icon" aria-hidden="true">
                    <i data-lucide="graduation-cap" width="22" height="22"></i>
                </div>
                <h2 id="nj-sec-education">Education</h2>
            </div>
            <div class="nj-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label" for="nj_qualification">Qualification</label>
                    <input type="text" id="nj_qualification" name="qualification" class="form-control"
                        placeholder="Qualification">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_degree">Degree / certification</label>
                    <input type="text" id="nj_degree" name="degree_certification" class="form-control"
                        placeholder="Degree or certification">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_expertise">Professional expertise</label>
                    <input type="text" id="nj_expertise" name="professional_expertise" class="form-control"
                        placeholder="Professional expertise">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_college">College / university</label>
                    <input type="text" id="nj_college" name="college_university" class="form-control"
                        placeholder="College / university">
                </div>
            </div>

            <div class="nj-section-head">
                <div class="nj-section-icon" aria-hidden="true">
                    <i data-lucide="briefcase" width="22" height="22"></i>
                </div>
                <h2 id="nj-sec-experience">Experience</h2>
            </div>
            <div class="nj-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label" for="nj_last_employer">Last employer <span class="req">*</span></label>
                    <input type="text" id="nj_last_employer" name="last_employer" class="form-control"
                        placeholder="Last employer" required>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_last_designation">Last designation <span class="req">*</span></label>
                    <input type="text" id="nj_last_designation" name="last_designation" class="form-control"
                        placeholder="Last designation" required>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_exp_from">Experience from date</label>
                    <input type="date" id="nj_exp_from" name="experience_from" class="form-control">
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_exp_to">Experience to date</label>
                    <input type="date" id="nj_exp_to" name="experience_to" class="form-control">
                </div>
            </div>

            <div class="nj-section-head">
                <div class="nj-section-icon" aria-hidden="true">
                    <i data-lucide="paperclip" width="22" height="22"></i>
                </div>
                <h2 id="nj-sec-docs">Document attachments</h2>
            </div>
            <div class="nj-form-grid-2">
                <div class="form-group">
                    <label class="admin-form-label" for="nj_resume">Resume attachment <span class="req">*</span></label>
                    <div class="nj-file-zone flex-center flex-column gap-12"
                        onclick="document.getElementById('nj_resume').click()">
                        <i data-lucide="upload-cloud" width="30" height="30" class="nj-upload-icon"></i>
                        <div class="text-center">
                            <div class="nj-file-hint">Click to upload resume</div>
                            <div class="nj-file-sub">PDF or DOCX, up to 10MB</div>
                        </div>
                        <input type="file" id="nj_resume" name="resume" class="hidden"
                            accept=".pdf,.doc,.docx,application/pdf" required>
                    </div>
                    <div id="nj_resume_label" class="nj-file-name" aria-live="polite"></div>
                </div>
                <div class="form-group">
                    <label class="admin-form-label" for="nj_id_card">ID card attachment <span
                            class="req">*</span></label>
                    <div class="nj-file-zone flex-center flex-column gap-12"
                        onclick="document.getElementById('nj_id_card').click()">
                        <i data-lucide="id-card" width="30" height="30" class="nj-upload-icon"></i>
                        <div class="text-center">
                            <div class="nj-file-hint">National ID / CNIC</div>
                            <div class="nj-file-sub">PDF, PNG, JPG (Mandatory)</div>
                        </div>
                        <input type="file" id="nj_id_card" name="id_card" class="hidden"
                            accept=".pdf,.png,.jpg,.jpeg,.webp,image/*,application/pdf">
                    </div>
                    <div id="nj_id_card_label" class="nj-file-name" aria-live="polite"></div>
                </div>
                <div class="form-group nj-span-2">
                    <label class="admin-form-label" for="nj_other_docs">Other documents</label>
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
        });
        document.getElementById('nj_id_card').addEventListener('change', function () {
            njFileLabel(this, 'nj_id_card_label');
        });
        document.getElementById('nj_other_docs').addEventListener('change', function () {
            njFileLabel(this, 'nj_other_docs_label');
        });

        document.getElementById('njHireForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            // Manual validation for hidden file inputs
            const idCardInput = document.getElementById('nj_id_card');
            if (!idCardInput.files || idCardInput.files.length === 0) {
                Swal.fire({
                    title: 'Attachment Required',
                    text: 'Please upload your ID Card / CNIC attachment to proceed.',
                    icon: 'warning',
                    confirmButtonColor: '#6c4cf1',
                    borderRadius: '16px'
                });
                return;
            }

            if (!this.checkValidity()) {
                this.reportValidity();
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