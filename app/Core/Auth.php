<?php
/**
 * Authentication & Authorization Handler
 * From: includes/auth_helper.php + includes/access_control_helper.php
 */

namespace App\Core;

use App\Core\View;
use PDO;

class Auth
{
    private static ?PDO $db = null;

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
        self::$db = Database::connection();

        // Check remember me auto-login
        if (!self::isLoggedIn() && isset($_COOKIE['remember_me'])) {
            self::checkRememberMe();
        }
    }

    /**
     * Create a remember me token for persistent login
     */
    public static function createRememberMeToken(int $employeeId): void
    {
        try {
            $selector = bin2hex(random_bytes(16));
            $validator = bin2hex(random_bytes(32));
            $hashedValidator = hash('sha256', $validator);
            
            // Expiry in 30 days
            $expiry = date('Y-m-d H:i:s', time() + 30 * 86400);

            $stmt = self::$db->prepare("
                INSERT INTO user_tokens (employee_id, selector, hashed_validator, expiry)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$employeeId, $selector, $hashedValidator, $expiry]);

            $cookieValue = $selector . ':' . $validator;
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            setcookie('remember_me', $cookieValue, time() + 30 * 86400, '/', '', $isSecure, true);
        } catch (\Throwable $e) {
            // Fallback silently
        }
    }

    /**
     * Validate the remember me cookie and automatically log the user in
     */
    public static function checkRememberMe(): bool
    {
        if (empty($_COOKIE['remember_me'])) {
            return false;
        }

        $parts = explode(':', $_COOKIE['remember_me']);
        if (count($parts) !== 2) {
            self::clearRememberMeCookie();
            return false;
        }

        list($selector, $validator) = $parts;

        try {
            $stmt = self::$db->prepare("
                SELECT * FROM user_tokens 
                WHERE selector = ? LIMIT 1
            ");
            $stmt->execute([$selector]);
            $token = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$token) {
                self::clearRememberMeCookie();
                return false;
            }

            // Check expiry
            if (strtotime($token['expiry']) < time()) {
                $stmtDel = self::$db->prepare("DELETE FROM user_tokens WHERE id = ?");
                $stmtDel->execute([$token['id']]);
                self::clearRememberMeCookie();
                return false;
            }

            // Verify validator hash
            $hashedInput = hash('sha256', $validator);
            if (hash_equals($token['hashed_validator'], $hashedInput)) {
                // Log the user in
                $stmtUser = self::$db->prepare("SELECT * FROM employees WHERE id = ? AND deleted_at IS NULL LIMIT 1");
                $stmtUser->execute([$token['employee_id']]);
                $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    self::setLoginSession($user);

                    // Token Rotation: Generate new validator and expiry
                    $newValidator = bin2hex(random_bytes(32));
                    $newHashedValidator = hash('sha256', $newValidator);
                    $newExpiry = date('Y-m-d H:i:s', time() + 30 * 86400);

                    $stmtUpd = self::$db->prepare("
                        UPDATE user_tokens 
                        SET hashed_validator = ?, expiry = ? 
                        WHERE id = ?
                    ");
                    $stmtUpd->execute([$newHashedValidator, $newExpiry, $token['id']]);

                    $cookieValue = $selector . ':' . $newValidator;
                    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

                    setcookie('remember_me', $cookieValue, time() + 30 * 86400, '/', '', $isSecure, true);

                    return true;
                }
            }

            // If verification failed, delete token for security
            $stmtDel = self::$db->prepare("DELETE FROM user_tokens WHERE id = ?");
            $stmtDel->execute([$token['id']]);
            self::clearRememberMeCookie();
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Clear the remember me cookie
     */
    public static function clearRememberMeCookie(): void
    {
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        setcookie('remember_me', '', time() - 3600, '/', '', $isSecure, true);
    }

    /**
     * Set session data after successful login
     */
    public static function setLoginSession(array $user): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $middle_name = !empty($user['middle_name']) ? $user['middle_name'] . ' ' : '';
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $middle_name . $user['last_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_profile_pic'] = $user['profile_pic'] ?? null;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current user role
     */
    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Get current user ID
     */
    public static function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole(string $role): bool
    {
        return self::role() === $role;
    }

    /**
     * Check if user is Admin
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('Admin');
    }

    /**
     * Check if user is HR
     */
    public static function isHR(): bool
    {
        return self::hasRole('HR');
    }

    /**
     * Check if user is Employee
     */
    public static function isEmployee(): bool
    {
        return self::hasRole('Employee');
    }

    /**
     * Require Admin role (throws error if not)
     */
    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            http_response_code(403);
            die('Unauthorized: Admin access required.');
        }
    }

    /**
     * Require HR role
     */
    public static function requireHR(): void
    {
        if (!self::isHR()) {
            http_response_code(403);
            die('Unauthorized: HR access required.');
        }
    }

    /**
     * Require Employee role
     */
    public static function requireEmployee(): void
    {
        if (!self::isEmployee()) {
            http_response_code(403);
            die('Unauthorized: Employee access required.');
        }
    }

    /**
     * Require Admin or HR
     */
    public static function requireAdminOrHR(): void
    {
        if (!self::isAdmin() && !self::isHR()) {
            http_response_code(403);
            die('Unauthorized: Admin or HR access required.');
        }
    }

    /**
     * Get application base path for redirects
     */
    public static function getBasePath(): string
    {
        if (defined('BASE_PATH')) {
            return BASE_PATH;
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($scriptDir !== '' && preg_match('#/public$#', $scriptDir)) {
            $basePath = substr($scriptDir, 0, -7);
            if ($basePath === '') {
                return '';
            }
            return $basePath;
        }

        return $scriptDir ?: '';
    }

    /**
     * Redirect user based on their role
     */
    public static function redirectByRole(): void
    {
        if (self::isLoggedIn()) {
            if (self::isHR()) {
                $pdo = \App\Core\Database::connection();
                $targetSlug = \App\Helpers\HRPermissions::resolveDeniedRedirectSlug($pdo, null) ?? 'dashboard';
                header('Location: ' . View::url($targetSlug));
                exit;
            }
            header('Location: ' . View::url('dashboard'));
            exit;
        }

        header('Location: ' . View::url('login'));
        exit;
    }

    /**
     * Logout user
     */
    public static function logout(?string $message = null): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $flash = [];
        if ($message !== null) {
            $flash['success'] = $message;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_regenerate_id(true);

        if (!empty($flash)) {
            foreach ($flash as $key => $value) {
                $_SESSION[$key] = $value;
            }
        }

        if (isset($_COOKIE['remember_me'])) {
            $parts = explode(':', $_COOKIE['remember_me']);
            if (count($parts) === 2) {
                try {
                    $stmt = self::$db->prepare("DELETE FROM user_tokens WHERE selector = ?");
                    $stmt->execute([$parts[0]]);
                } catch (\Throwable $e) {}
            }
            self::clearRememberMeCookie();
        }

        header('Location: ' . View::url('login'));
        exit;
    }

    // ===== HR PERMISSION MATRIX (from access_control_helper.php) =====

    private static array $hrPageRegistry = [
        // Main
        'dashboard' => 'Dashboard',
        'activity-logs' => 'Activity Logs',
        
        // Organization
        'employees' => 'Employees',
        'employee-profile' => 'Employee Profile',
        'new-joining' => 'New Joining',
        'departments' => 'Departments',
        'hierarchy' => 'Hierarchy',
        'hierarchy-settings' => 'Hierarchy Settings',
        
        // Job Management
        'job-list' => 'Job List',
        'create-job' => 'Create Job',
        'edit-job' => 'Edit Job',
        'job-candidates' => 'Job Candidates',
        'candidate-detail' => 'Candidate Detail',
        'interviews' => 'Interviews',
        
        // Administration
        'shifts' => 'Shifts',
        'announcements' => 'Announcements',
        'notifications' => 'Notifications',
        'event-calendar' => 'Event Calendar',
        'it-support' => 'IT Support',
        'policy-management' => 'Policies',
        
        // System
        'attendance' => 'Attendance',
        'attendance-log' => 'Attendance Log',
        'leave-management' => 'Leave Management',
        'payroll' => 'Payroll',
        'payroll-settings' => 'Payroll Settings',
        'kpi-management' => 'KPI Management',
        'kpi-report' => 'KPI Report',
    ];

    /**
     * Get all HR pages
     */
    public static function getHRPageRegistry(): array
    {
        return self::$hrPageRegistry;
    }

    /**
     * Check if HR has permission to access page
     */
    public static function hrCanAccess(string $pageKey): bool
    {
        if (!self::isHR()) {
            return false;
        }

        try {
            $stmt = self::$db->prepare("
                SELECT can_view FROM hr_page_permissions 
                WHERE page_key = ? LIMIT 1
            ");
            $stmt->execute([$pageKey]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result && (bool)$result['can_view'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if HR can perform action on page
     */
    public static function hrCanPerform(string $pageKey, string $action): bool
    {
        if (!self::isHR()) {
            return false;
        }

        $allowedActions = ['view', 'create', 'edit', 'delete', 'export'];
        if (!in_array($action, $allowedActions)) {
            return false;
        }

        try {
            $column = "can_$action";
            $stmt = self::$db->prepare("
                SELECT $column FROM hr_page_permissions 
                WHERE page_key = ? LIMIT 1
            ");
            $stmt->execute([$pageKey]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result && (bool)$result[$column];
        } catch (\Exception $e) {
            return false;
        }
    }
}

// Initialize Auth on load
Auth::init();
?>
