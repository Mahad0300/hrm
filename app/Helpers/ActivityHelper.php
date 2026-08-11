<?php
/**
 * Activity Log Helper
 * From: includes/api/activity_helper.php
 */

namespace App\Helpers;

use PDO;

class ActivityHelper
{
    /**
     * Log user activity
     */
    public static function log(int $userId, string $action, string $module, string $details = ''): bool
    {
        try {
            $db = \App\Core\Database::connection();
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $fullDescription = "[$module] $details";

            $stmt = $db->prepare("
                INSERT INTO activity_logs (employee_id, action, description, ip_address, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");

            $success = $stmt->execute([
                $userId,
                $action,
                $fullDescription,
                $ipAddress
            ]);

            if ($success) {
                \App\Helpers\WebSocketHelper::broadcast('activity_logged');
            }

            return $success;
        } catch (\Exception $e) {
            error_log("Activity Log Error: " . $e->getMessage());
            return false;
        }
    }
}
?>
