<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $safePageTitle ?></title>
    <meta name="title" content="<?= $safePageTitle ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= \App\Core\View::asset('css/admin/style.css') ?>">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        html, body.walk-in-body {
            min-height: 100%;
            background: #f8fafc;
            margin: 0;
            color: #1e293b;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .walk-in-container {
            max-width: 760px;
            margin: clamp(24px, 4vw, 48px) auto;
            padding: 0 16px;
        }

        .walk-in-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.03);
            padding: clamp(28px, 4vw, 44px);
        }

        .walk-in-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid #f1f5f9;
        }

        .walk-in-logo {
            max-width: 140px;
            height: auto;
        }

        .walk-in-title {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .walk-in-subtitle {
            font-size: 14px;
            color: #64748b;
            margin: 4px 0 0 0;
        }

        .form-section-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #6366f1;
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
    </style>
</head>

<body class="walk-in-body">

    <div class="walk-in-container">
        <div class="walk-in-card">
            
            <header class="walk-in-header">
                <img src="/images/loginimage/logo.png" alt="Richmond Tech Group" class="walk-in-logo">
                <div>
                    <h1 class="walk-in-title">Walk-In Interview Registration</h1>
                    <p class="walk-in-subtitle">Please fill in your information to register for your walk-in interview session.</p>
                </div>
            </header>

            <form id="walkInForm">
                
                <!-- SECTION 1: INTERESTED OPPORTUNITY -->
                <div class="form-section-title">Interested Opportunity</div>
                
                <div class="form-group-full">
                    <label class="field-label" for="walkInJobId">Select Position You're Applying For *</label>
                    <select id="walkInJobId" class="form-control-custom" required>
                        <option value="" disabled selected hidden>-- Choose Position --</option>
                    </select>
                </div>

                <!-- SECTION 2: PERSONAL INFORMATION -->
                <div class="form-section-title">Personal Information</div>

                <div class="form-grid-2">
                    <div>
                        <label class="field-label" for="walkInName">Full Name *</label>
                        <input type="text" id="walkInName" class="form-control-custom" placeholder="Enter your full name" required autocomplete="name">
                    </div>
                    <div>
                        <label class="field-label" for="walkInEmail">Email Address *</label>
                        <input type="email" id="walkInEmail" class="form-control-custom" placeholder="Enter your email address" required autocomplete="email">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div>
                        <label class="field-label" for="walkInPhone">Phone Number *</label>
                        <input type="tel" id="walkInPhone" class="form-control-custom" placeholder="0300-0000000" maxlength="12" inputmode="numeric" required autocomplete="tel">
                    </div>
                    <div>
                        <label class="field-label" for="walkInCnic">CNIC / ID Number *</label>
                        <input type="text" id="walkInCnic" class="form-control-custom" placeholder="42101-0000000-0" maxlength="15" inputmode="numeric" required autocomplete="off">
                    </div>
                </div>

                <div class="form-group-full">
                    <label class="field-label" for="walkInAddress">Current Address *</label>
                    <textarea id="walkInAddress" class="form-control-custom" placeholder="Enter your current residential address" rows="3" required style="resize: vertical;"></textarea>
                </div>

                <!-- SECTION 3: UPLOAD RESUME -->
                <div class="form-section-title">Upload Resume</div>

                <div class="form-group-full">
                    <div class="resume-upload-box" onclick="document.getElementById('walkInResume').click()">
                        <div class="upload-icon-wrap">
                            <i data-lucide="file-text" size="26"></i>
                        </div>
                        <div class="upload-title">Upload your Resume/CV</div>
                        <div class="upload-sub">Standard PDF or DOC format required (Max 5MB)</div>
                        <div id="fileSelectedName" style="margin-top: 10px; font-weight: 700; color: #4f46e5;"></div>
                    </div>
                    <input type="file" id="walkInResume" class="hidden" accept=".pdf,.doc,.docx,application/pdf" required onchange="updateFileName(this)">
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
        lucide.createIcons();

        function updateFileName(input) {
            const file = input.files[0];
            const nameEl = document.getElementById('fileSelectedName');
            if (file) {
                nameEl.textContent = 'Selected: ' + file.name;
            } else {
                nameEl.textContent = '';
            }
        }

        // CNIC Auto-Formatting (12345-1234567-1)
        const cnicInput = document.getElementById('walkInCnic');
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
        const phoneInput = document.getElementById('walkInPhone');
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

        // Fetch jobs for dropdown
        document.addEventListener('DOMContentLoaded', function() {
            fetch('<?= \App\Core\View::asset('api/job_handler.php') ?>?action=fetch_jobs')
                .then(res => res.json())
                .then(data => {
                    const select = document.getElementById('walkInJobId');
                    const jobsList = data.data || data.jobs || [];
                    if (data.status === 'success' && Array.isArray(jobsList)) {
                        jobsList.forEach(job => {
                            const opt = document.createElement('option');
                            opt.value = job.id;
                            opt.textContent = job.title;
                            select.appendChild(opt);
                        });
                    }
                })
                .catch(err => console.error('Error loading jobs:', err));
        });

        // Form Submit
        document.getElementById('walkInForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Submitting...';

            const formData = new FormData();
            formData.append('action', 'submit_walk_in');
            formData.append('job_id', document.getElementById('walkInJobId').value);
            formData.append('name', document.getElementById('walkInName').value);
            formData.append('email', document.getElementById('walkInEmail').value);
            formData.append('phone', document.getElementById('walkInPhone').value);
            formData.append('cnic_number', document.getElementById('walkInCnic').value);
            formData.append('address', document.getElementById('walkInAddress').value);
            
            const fileInput = document.getElementById('walkInResume');
            if (fileInput.files[0]) {
                formData.append('resume', fileInput.files[0]);
            }

            fetch('<?= \App\Core\View::asset('api/job_handler.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Submit Application</span><i data-lucide="arrow-right" size="18"></i>';
                lucide.createIcons();

                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Application Submitted!',
                        text: 'Your walk-in application has been registered successfully.',
                        confirmButtonColor: '#4f46e5'
                    }).then(() => {
                        document.getElementById('walkInForm').reset();
                        document.getElementById('fileSelectedName').textContent = '';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Submission Failed',
                        text: data.message || 'An error occurred while submitting your application.',
                        confirmButtonColor: '#4f46e5'
                    });
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Submit Application</span><i data-lucide="arrow-right" size="18"></i>';
                lucide.createIcons();
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Unable to connect to server. Please try again.',
                    confirmButtonColor: '#4f46e5'
                });
            });
        });
    </script>
</body>

</html>
