<?php
/**
 * Notification Helper
 * From: includes/notification_helper.php
 */

namespace App\Helpers;

use PDO;

class NotificationHelper
{
    /**
     * Add notification for recipients
     */
    public static function add(array $recipientIds, string $title, string $message, ?string $targetUrl = null, string $type = 'System', ?int $senderId = null): bool
    {
        try {
            $db = \App\Core\Database::connection();
            $db->beginTransaction();

            $stmt = $db->prepare('INSERT INTO notifications (title, message, target_url, type, sender_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$title, $message, $targetUrl, $type, $senderId]);
            $notificationId = $db->lastInsertId();

            $recipientStmt = $db->prepare('INSERT INTO notification_recipients (notification_id, employee_id) VALUES (?, ?)');
            foreach ($recipientIds as $employeeId) {
                $recipientStmt->execute([$notificationId, $employeeId]);
            }

            $db->commit();

            // Trigger WebSocket pushes for all notification recipients in a single connection
            \App\Helpers\WebSocketHelper::sendToUsers($recipientIds, 'notification', [
                'title' => $title,
                'message' => $message,
                'target_url' => $targetUrl,
                'notification_type' => $type
            ]);

            return true;
        } catch (\Exception $e) {
            error_log('AddNotification Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse target department audience
     */
    public static function parseTargetDeptAudience(string $target): array
    {
        $target = trim($target);
        if ($target === '') {
            return ['everyone' => false, 'depts' => []];
        }
        if (in_array(strtolower($target), ['everyone', 'all'], true)) {
            return ['everyone' => true, 'depts' => []];
        }

        $depts = array_values(array_unique(array_filter(array_map('trim', explode(',', $target)))));
        return ['everyone' => false, 'depts' => $depts];
    }

    /**
     * Get employee IDs for target departments
     */
    public static function getTargetDeptEmployeeIds(PDO $pdo, string $target): array
    {
        $parsed = self::parseTargetDeptAudience($target);

        if ($parsed['everyone']) {
            $stmt = $pdo->query("SELECT id FROM employees WHERE status = 'Active' AND deleted_at IS NULL");
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        }

        if (empty($parsed['depts'])) {
            return [];
        }

        $placeholders = str_repeat('?,', count($parsed['depts']) - 1) . '?';
        $stmt = $pdo->prepare("
            SELECT e.id
            FROM employees e
            JOIN departments d ON e.department_id = d.id
            WHERE d.name IN ($placeholders) AND e.status = 'Active' AND e.deleted_at IS NULL
        ");
        $stmt->execute($parsed['depts']);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Get newly added recipient IDs
     */
    public static function getAddedTargetDeptRecipients(PDO $pdo, string $oldTarget, string $newTarget, int $senderId): array
    {
        $oldIds = self::getTargetDeptEmployeeIds($pdo, $oldTarget);
        $newIds = self::getTargetDeptEmployeeIds($pdo, $newTarget);
        $added = array_diff($newIds, $oldIds);

        return array_values(array_filter($added, static function ($id) use ($senderId) {
            return $id !== $senderId;
        }));
    }
}
?>
