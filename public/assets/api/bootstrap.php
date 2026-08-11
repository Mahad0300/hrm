<?php
/**
 * Shared bootstrap for legacy API handler scripts.
 * Provides PDO, session auth, and compatibility shims for app/Includes removal.
 */

declare(strict_types=1);

define('ROOT_DIR', dirname(__DIR__, 3));

require_once ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/app/Helpers/EnvHelper.php';

use App\Core\Auth;
use App\Core\Database;
use App\Helpers\ActivityHelper;
use App\Helpers\CSRFToken;
use App\Helpers\HRPermissions;
use App\Helpers\PayrollConfig;
use App\Helpers\RateLimiter;
use App\Helpers\StorageHelper;

Auth::init();
$pdo = Database::connection();

// Verify CSRF token for state-changing HTTP requests if user is logged in
if (Auth::isLoggedIn() && in_array(strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'), ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
    CSRFToken::verifyRequest();
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn(): bool
    {
        return Auth::isLoggedIn();
    }
}

if (!function_exists('requireAdminRole')) {
    function requireAdminRole(): void
    {
        Auth::requireAdmin();
    }
}

if (!function_exists('requireAdminOrHrRole')) {
    function requireAdminOrHrRole(): void
    {
        Auth::requireAdminOrHR();
    }
}

if (!function_exists('requireEmployeeRole')) {
    function requireEmployeeRole(): void
    {
        Auth::requireEmployee();
    }
}

if (!function_exists('getCurrentPayrollMonth')) {
    function getCurrentPayrollMonth(?string $date = null): string
    {
        global $pdo;
        return PayrollConfig::getCurrentPayrollMonth($date, $pdo);
    }
}

if (!function_exists('getPayrollRange')) {
    function getPayrollRange(string $monthStr): array
    {
        global $pdo;
        return PayrollConfig::getPayrollRange($pdo, $monthStr);
    }
}

if (!function_exists('getHRMSetting')) {
    function getHRMSetting($pdo, string $key, $default = '')
    {
        return PayrollConfig::getSetting($pdo, $key, $default);
    }
}

if (!function_exists('isHrPortalUser')) {
    function isHrPortalUser(): bool
    {
        return HRPermissions::isHrPortalUser();
    }
}

if (!function_exists('hrSeedPermissionsIfEmpty')) {
    function hrSeedPermissionsIfEmpty(PDO $pdo): void
    {
        HRPermissions::seedPermissionsIfEmpty($pdo);
    }
}

if (!function_exists('hrGuardApiRequest')) {
    function hrGuardApiRequest(PDO $pdo, string $action, ?string $handlerFile = null): void
    {
        HRPermissions::guardApiRequest($pdo, $action, $handlerFile);
    }
}

if (!function_exists('hrFetchAllPermissions')) {
    function hrFetchAllPermissions(PDO $pdo): array
    {
        return HRPermissions::fetchAllPermissions($pdo);
    }
}

if (!function_exists('hrPermissionsRevision')) {
    function hrPermissionsRevision(PDO $pdo): int
    {
        return HRPermissions::permissionsRevision($pdo);
    }
}

if (!function_exists('hrAccessPageRegistry')) {
    function hrAccessPageRegistry(): array
    {
        return HRPermissions::accessPageRegistry();
    }
}

if (!function_exists('hrNormalizePermissionRow')) {
    function hrNormalizePermissionRow(string $pageKey, array $perm): array
    {
        return HRPermissions::normalizePermissionRow($pageKey, $perm);
    }
}

if (!function_exists('hrBumpPermissionsRevision')) {
    function hrBumpPermissionsRevision(PDO $pdo): void
    {
        HRPermissions::bumpPermissionsRevision($pdo);
    }
}

if (!function_exists('hrCanViewSidebarPage')) {
    function hrCanViewSidebarPage(PDO $pdo, string $pageKey): bool
    {
        return HRPermissions::canViewSidebarPage($pdo, $pageKey);
    }
}

if (!function_exists('addNotification')) {
    function addNotification($recipient_ids, $title, $message, $target_url = null, $type = 'System', $sender_id = null): bool
    {
        return \App\Helpers\NotificationHelper::add(
            array_map('intval', (array) $recipient_ids),
            (string) $title,
            (string) $message,
            $target_url !== null ? (string) $target_url : null,
            (string) $type,
            $sender_id !== null ? (int) $sender_id : null
        );
    }
}

if (!function_exists('syncApprovedLeaveToAttendance')) {
    function syncApprovedLeaveToAttendance(PDO $pdo, int $leaveRequestId): void
    {
        \App\Helpers\LeaveAttendanceSync::syncApprovedLeaveToAttendance($pdo, $leaveRequestId);
    }
}

if (!function_exists('revertRejectedLeaveFromAttendance')) {
    function revertRejectedLeaveFromAttendance(PDO $pdo, int $leaveRequestId): void
    {
        \App\Helpers\LeaveAttendanceSync::revertRejectedLeaveFromAttendance($pdo, $leaveRequestId);
    }
}

if (!function_exists('logActivity')) {
    function logActivity($user_id, $action, $module, $details = ''): bool
    {
        return ActivityHelper::log((int) $user_id, (string) $action, (string) $module, (string) $details);
    }
}

if (!function_exists('checkRateLimit')) {
    function checkRateLimit($action, $maxHits = 10, $windowSec = 60): bool
    {
        $action = (string) $action;
        $maxHits = (int) $maxHits;
        $windowSec = (int) $windowSec;
        if (RateLimiter::isLimited($action, $maxHits, $windowSec)) {
            return false;
        }
        RateLimiter::recordHit($action, $maxHits, $windowSec);
        return true;
    }
}

if (!function_exists('sendCandidateStatusEmail')) {
    function sendCandidateStatusEmail($candidateId, $status, array $extraData = []): array
    {
        global $pdo;
        return \App\Helpers\EmailHelper::sendCandidateStatusEmail($pdo, (int) $candidateId, (string) $status, $extraData);
    }
}

if (!function_exists('rateLimitExceeded')) {
    function rateLimitExceeded(): void
    {
        http_response_code(429);
        echo json_encode(['status' => 'error', 'message' => 'Too many requests. Please try again later.']);
        exit;
    }
}

if (!function_exists('uploadFile')) {
    /**
     * @param string $targetDir Relative DB dir, e.g. uploads/employees/resumes/
     */
    function uploadFile(?array $file, string $targetDir, ?string $fileName = null): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($fileName === null) {
            $prefix = 'EMP_';
            if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'Employee') {
                $prefix = 'user_' . (int) $_SESSION['user_id'] . '_';
            }
            $fileName = uniqid($prefix) . '.' . $ext;
        }

        return StorageHelper::storeUploadedFile($file, $targetDir, $fileName);
    }
}

if (!function_exists('storageDiskPath')) {
    function storageDiskPath(string $relativePath): string
    {
        return StorageHelper::diskPath($relativePath);
    }
}
