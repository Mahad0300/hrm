<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';

use App\Core\Database;

try {
    $pdo = Database::connection();
    echo "--- Server Attendance Table (Last 15 rows) ---\n";
    $stmt = $pdo->query("
        SELECT a.id, a.employee_id, e.first_name, e.last_name, e.biometric_id, a.date, a.clock_in, a.clock_out, a.status 
        FROM attendance a
        LEFT JOIN employees e ON a.employee_id = e.id 
        ORDER BY a.id DESC 
        LIMIT 15
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "ID={$row['id']} | EmpID={$row['employee_id']} | Name={$row['first_name']} {$row['last_name']} (BioID={$row['biometric_id']}) | Date={$row['date']} | In={$row['clock_in']} | Out={$row['clock_out']} | Status={$row['status']}\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
