<?php
namespace App\Controllers\Public;

use App\Core\BaseController;
use App\Core\Auth;
use App\Core\Database;
use PDO;

class PublicController extends BaseController
{
    public function login(): void
    {
        if (Auth::isLoggedIn()) {
            Auth::redirectByRole();
        }

        require_once ROOT_DIR . '/app/Helpers/ActivityHelper.php';
        require_once ROOT_DIR . '/app/Helpers/RateLimiter.php';

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
            } elseif (\App\Helpers\RateLimiter::isLimited('login', 10, 900)) {
                $error = 'Too many failed login attempts. Please try again in 15 minutes.';
            } else {
                try {
                    $pdo = Database::connection();
                    // Find user in database
                    $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ? AND deleted_at IS NULL LIMIT 1");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user && password_verify($password, $user['password'])) {
                        // Successful login
                        Auth::setLoginSession($user);

                        // Check if remember me checkbox is checked
                        if (isset($_POST['remember'])) {
                            Auth::createRememberMeToken($user['id']);
                        }
                        
                        // [LOG ACTIVITY]
                        \App\Helpers\ActivityHelper::log($user['id'], "User Login", "Authentication", "User authenticated successfully and accessed the system dashboard.");

                        // Redirect based on role
                        Auth::redirectByRole();
                    } else {
                        \App\Helpers\RateLimiter::recordHit('login', 10, 900);
                        $error = 'Invalid email or password. Please try again.';
                    }
                } catch (\PDOException $e) {
                    $error = 'A database error occurred. Please try again later.';
                }
            }
        }

        $this->render('index', [
            'error' => $error,
            'success' => $success,
            'email' => $email
        ]);
    }

    public function jobApply(): void
    {
        $jobTitleForMeta = '';

        if (!function_exists('jobApplySlug')) {
            function jobApplySlug($title) {
                $slug = strtolower((string) $title);
                $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
                return trim($slug, '-');
            }
        }

        try {
            $jobId = $_GET['jobId'] ?? ($_GET['jobid'] ?? '');
            $jobSlug = jobApplySlug(trim($_GET['job'] ?? ''));

            if ($jobId !== '' || $jobSlug !== '') {
                $pdo = Database::connection();

                if ($jobId !== '') {
                    $stmt = $pdo->prepare("SELECT title FROM jobs WHERE id = ? AND deleted_at IS NULL LIMIT 1");
                    $stmt->execute([$jobId]);
                    $jobTitleForMeta = (string) ($stmt->fetchColumn() ?: '');
                } elseif ($jobSlug !== '') {
                    $stmt = $pdo->query("SELECT title FROM jobs WHERE deleted_at IS NULL ORDER BY created_at DESC");
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $jobRow) {
                        if (jobApplySlug($jobRow['title']) === $jobSlug) {
                            $jobTitleForMeta = (string) $jobRow['title'];
                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            $jobTitleForMeta = '';
        }

        $pageTitle = $jobTitleForMeta !== '' ? $jobTitleForMeta . ' | Job Application' : 'Job Application';
        $safePageTitle = htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8');

        $this->render('job-apply', [
            'safePageTitle' => $safePageTitle
        ]);
    }

    public static function logout(): void
    {
        Auth::logout('You have been successfully logged out.');
    }

    public function joiningForm(): void
    {
        $this->render('joining-form');
    }

    public function walkIn(): void
    {
        $this->render('walk-in', [
            'safePageTitle' => 'Walk-In Interview Application'
        ]);
    }
}
