<?php
// Includes
require_once 'includes/db_connect.php';
require_once 'includes/auth_helper.php';
require_once 'includes/api/activity_helper.php';

// If already logged in, redirect to respective dashboard
if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    switch ($role) {
        case 'Admin': header('Location: admin/index.php'); break;
        case 'HR': header('Location: hr/index.php'); break;
        case 'Employee': header('Location: user/index.php'); break;
        default: header('Location: index.php'); break;
    }
    exit;
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        try {
            // Find user in database
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ? AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                if (password_verify($password, $user['password'])) {
                    // Successful login
                    setLoginSession($user);
                    
                    // [LOG ACTIVITY]
                    logActivity($user['id'], "User Login", "Authentication", "User authenticated successfully and accessed the system dashboard.");
                    
                    // Remember Me: Cookie removed for security (stored plain user ID).
                    // TODO: Implement secure token-based remember me if needed in future.

                    // Redirect based on role
                    redirectByRole($user['role']);
                } else {
                    $error = 'Invalid password. Please try again.';
                }
            } else {
                $error = 'Invalid email. This account does not exist.';
            }
        } catch (PDOException $e) {
            $error = 'A database error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | rtg HRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap"
        rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* App theme (same as admin/assets/css/style.css) */
        :root {
            --primary-color: #6C4CF1;
            --primary-rgb: 108, 76, 241;
            --primary-light: #8A6FFF;
            --primary-dark: #5839D6;
            --login-text: #1a1a1a;
            --login-muted: #6b7280;
            --login-border: #e5e7eb;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body.auth-v2 {
            margin: 0;
            min-height: 100vh;
            font-family: 'DM Sans', system-ui, sans-serif;
            font-size: 15px;
            color: var(--login-text);
            background: #0f172a;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        .auth-v2-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.95fr);
            min-height: 100vh;
            width: 100%;
            max-width: 100%;
        }

        /* Left: office image — full bleed, only this column */
        .auth-v2-hero {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            min-height: 100dvh;
            background-color: #0f172a;
            background-image: url('images/loginimage/loginleft.png');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
            padding: clamp(28px, 5vw, 48px) clamp(24px, 4vw, 48px);
        }

        .auth-v2-hero__overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(115deg,
                    rgba(15, 23, 42, 0.88) 0%,
                    rgba(15, 23, 42, 0.55) 45%,
                    rgba(15, 23, 42, 0.35) 78%,
                    rgba(15, 23, 42, 0.5) 100%);
            pointer-events: none;
        }

        /* Logo top-left; headline block vertically centered below */
        .auth-v2-hero__layout {
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            width: 100%;
        }

        .auth-v2-hero__logo {
            align-self: flex-start;
            display: block;
            line-height: 0;
            margin-bottom: clamp(16px, 3vh, 28px);
        }

        .auth-v2-hero__logo img {
            height: clamp(36px, 5vw, 48px);
            width: auto;
            max-width: min(200px, 55vw);
            object-fit: contain;
            object-position: left center;
            display: block;
            filter: drop-shadow(0 2px 12px rgba(0, 0, 0, 0.35));
        }

        .auth-v2-hero__content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            max-width: 520px;
        }

        .auth-v2-eyebrow {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.72);
            margin: 0 0 10px;
        }

        .auth-v2-headline {
            font-size: clamp(1.75rem, 3.5vw, 2.35rem);
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #fff;
            margin: 0 0 14px;
        }

        .auth-v2-desc {
            font-size: 14px;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.78);
            margin: 0 0 22px;
            max-width: 440px;
        }

        .auth-v2-features {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 11px;
            max-width: 440px;
        }

        .auth-v2-features li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 13px;
            line-height: 1.45;
            color: rgba(255, 255, 255, 0.85);
        }

        .auth-v2-features__check {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            margin-top: 1px;
            border-radius: 6px;
            background: rgba(var(--primary-rgb), 0.25);
            border: 1px solid rgba(var(--primary-rgb), 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e9d5ff;
        }

        .auth-v2-features__check svg {
            width: 12px;
            height: 12px;
            display: block;
        }

        .auth-v2-hero__note {
            margin-top: clamp(20px, 3vh, 28px);
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            font-size: 12px;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.5);
            max-width: 440px;
        }

        /* Right: full column = loginright.png (topo pattern) + centered card */
        .auth-v2-aside {
            position: relative;
            z-index: 2;
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(24px, 4vw, 48px) clamp(20px, 3vw, 40px);
            padding-left: clamp(32px, 5vw, 72px);
            margin-left: min(-7vw, -52px);
            /* Full right column: topo pattern (loginright.png) + light overlay for readability */
            background-color: #dfdfdf;
            background-image:
                linear-gradient(165deg, rgba(255, 255, 255, 0.5) 0%, rgba(255, 255, 255, 0.15) 50%, rgba(241, 245, 249, 0.4) 100%),
                url('images/loginimage/loginright.png');
            background-size: cover, cover;
            background-position: center, center;
            background-repeat: no-repeat, no-repeat;
            /* Diagonal seam overlapping the photo (matches reference) */
            clip-path: polygon(10% 0, 100% 0, 100% 100%, 0 100%);
        }

        .auth-v2-card {
            width: 100%;
            max-width: 400px;
            padding: clamp(28px, 4vw, 40px) clamp(22px, 3vw, 32px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #fff;
            border-radius: 14px;
            box-shadow:
                0 4px 6px -1px rgba(15, 23, 42, 0.06),
                0 20px 40px -12px rgba(15, 23, 42, 0.12);
            border: 1px solid rgba(226, 232, 240, 0.9);
        }

        .auth-v2-card__title {
            font-size: 1.35rem;
            font-weight: 700;
            text-align: center;
            margin: 0 0 28px;
            color: #111827;
        }

        .field-v2 {
            margin-bottom: 18px;
        }

        .field-v2 label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--login-muted);
            margin-bottom: 8px;
        }

        .auth-input-v2 {
            width: 100%;
            height: 48px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0 14px;
            font-size: 14px;
            font-family: inherit;
            color: var(--login-text);
            background: #f0f4f8;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .auth-input-v2::placeholder {
            color: #9ca3af;
        }

        .auth-input-v2:focus {
            outline: none;
            border-color: rgba(var(--primary-rgb), 0.45);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
        }

        /* Password Toggle Styling */
        .password-field-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-toggle-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            padding: 4px;
            cursor: pointer;
            color: #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            z-index: 5;
        }

        .password-toggle-btn:hover {
            color: var(--primary-color);
        }

        .auth-input-v2#password {
            padding-right: 45px;
        }

        .meta-row-v2 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 4px 0 22px;
            font-size: 13px;
        }

        .remember-v2 {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--login-muted);
            cursor: pointer;
        }

        .remember-v2 input {
            width: 16px;
            height: 16px;
            accent-color: var(--primary-color);
        }

        .meta-link-v2 {
            color: var(--login-muted);
            text-decoration: none;
            font-size: 13px;
        }

        .meta-link-v2:hover {
            color: var(--primary-color);
        }

        .btn-login-v2 {
            width: 100%;
            min-height: 52px;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            box-shadow: 0 10px 28px rgba(var(--primary-rgb), 0.35);
            transition: filter 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
        }

        .btn-login-v2:hover {
            filter: brightness(1.05);
            box-shadow: 0 12px 32px rgba(var(--primary-rgb), 0.42);
            transform: translateY(-1px);
        }

        .btn-login-v2:active {
            transform: translateY(0);
            filter: brightness(0.98);
        }

        .error-box-v2 {
            margin-bottom: 16px;
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.28);
            color: #b91c1c;
            font-size: 13px;
            border-radius: 8px;
            padding: 10px 12px;
        }

        @media (max-width: 1100px) {
            .auth-v2-aside {
                margin-left: 0;
                clip-path: none;
                padding-left: clamp(20px, 4vw, 40px);
            }
        }

        @media (max-width: 820px) {
            .auth-v2-shell {
                grid-template-columns: 1fr;
            }

            .auth-v2-hero {
                min-height: 42vh;
                min-height: 42dvh;
            }

            .auth-v2-hero__content {
                justify-content: flex-end;
                padding-bottom: 8px;
            }

            .auth-v2-features {
                gap: 9px;
            }

            .auth-v2-features li {
                font-size: 12px;
            }

            .auth-v2-hero__note {
                margin-top: 16px;
                padding-top: 14px;
            }

            .auth-v2-aside {
                margin-left: 0;
                clip-path: none;
                min-height: auto;
                padding: clamp(28px, 5vw, 40px) 20px;
            }

            .auth-v2-card {
                box-shadow:
                    0 4px 6px -1px rgba(15, 23, 42, 0.06),
                    0 16px 32px -8px rgba(15, 23, 42, 0.1);
            }
        }
    </style>
</head>

<body class="auth-v2">
    <div class="auth-v2-shell">
        <section class="auth-v2-hero" aria-label="Welcome">
            <div class="auth-v2-hero__overlay"></div>
            <div class="auth-v2-hero__layout">
                <a class="auth-v2-hero__logo" href="login.php" aria-label="Company home">
                    <img src="images/loginimage/logo.png" width="200" height="48" alt="Company logo">
                </a>
                <div class="auth-v2-hero__content">
                    <p class="auth-v2-eyebrow">HRM Portal</p>
                    <h1 class="auth-v2-headline">Your workforce, one dashboard</h1>
                    <p class="auth-v2-desc">
                        Sign in to manage employees, time, payroll, and hiring from a single secure hub — built for HR
                        teams, managers, and staff.
                    </p>
                    <ul class="auth-v2-features" aria-label="Platform features">
                        <li>
                            <span class="auth-v2-features__check" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </span>
                            <span>Attendance, shifts &amp; leave — approvals and policies in flow</span>
                        </li>
                        <li>
                            <span class="auth-v2-features__check" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </span>
                            <span>Payroll cycles, payslips &amp; compensation insights</span>
                        </li>
                        <li>
                            <span class="auth-v2-features__check" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </span>
                            <span>Recruitment — jobs, candidates, interviews &amp; onboarding</span>
                        </li>
                        <li>
                            <span class="auth-v2-features__check" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            </span>
                            <span>Org hierarchy, KPIs, announcements &amp; HR analytics</span>
                        </li>
                    </ul>
                    <p class="auth-v2-hero__note">Encrypted session · Role-based access · Activity suited for admin, HR,
                        and employee portals</p>
                </div>
            </div>
        </section>

        <div class="auth-v2-aside">
            <div class="auth-v2-card" id="account-login">
                <h2 class="auth-v2-card__title">Account Login</h2>

                <?php if ($error !== ''): ?>
                    <div class="error-box-v2"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                
                <?php if ($success !== ''): ?>
                    <div class="success-box-v2" style="background: rgba(34, 197, 94, 0.08); border: 1px solid rgba(34, 197, 94, 0.28); color: #15803d; font-size: 13px; border-radius: 8px; padding: 10px 12px; margin-bottom: 16px;"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="index.php" autocomplete="on" novalidate>
                    <div class="field-v2">
                        <label for="email">Username or Email</label>
                        <input id="email" name="email" type="text" class="auth-input-v2" placeholder="Username or Email"
                            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required inputmode="email"
                            autocomplete="username">
                    </div>

                    <div class="field-v2">
                        <label for="password">Password</label>
                        <div class="password-field-wrapper">
                            <input id="password" name="password" type="password" class="auth-input-v2"
                                placeholder="Password" required autocomplete="current-password">
                            <button type="button" class="password-toggle-btn" id="togglePassword" aria-label="Toggle password visibility">
                                <i data-lucide="eye" size="18"></i>
                            </button>
                        </div>
                    </div>

                    <div class="meta-row-v2">
                        <label class="remember-v2">
                            <input type="checkbox" name="remember_me" value="1">
                            <span>Remember Me</span>
                        </label>
                        <a class="meta-link-v2" href="#">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-login-v2">Login to your Account!</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Lucide icons initialization
        lucide.createIcons();

        // Password Toggle Script
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // toggle the eye icon
            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                icon.setAttribute('data-lucide', 'eye');
            }
            
            // Re-initialize lucide icons for the new icon
            lucide.createIcons();
        });
    </script>
</body>

</html>