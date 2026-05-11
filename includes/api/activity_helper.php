<?php
/**
 * Activity Log Helper - Centralized logging for Admin and User actions.
 */

if (!function_exists('logActivity')) {
    function logActivity($user_id, $action, $module, $details = '') {
        global $pdo;
        
        try {
            // Get User IP
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            
            // Combine Module and Details for the 'description' column since 'module' is missing in DB
            $full_description = "[" . $module . "] " . $details;
            
            $stmt = $pdo->prepare("
                INSERT INTO activity_logs (employee_id, action, description, ip_address, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $user_id,
                $action,
                $full_description,
                $ip_address
            ]);
        } catch (Exception $e) {
            error_log("Activity Log Error: " . $e->getMessage());
            return false;
        }
    }
}
?>
