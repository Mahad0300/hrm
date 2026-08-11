<?php
/**
 * Database Migration Script
 * Migrates data from the old HRM schema (source) to the new HRM schema (destination)
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
\App\Helpers\EnvHelper::load();
require_once dirname(__DIR__) . '/config/config.php';

use App\Core\Database;

if ($argc < 2) {
    echo "Usage: php bin/migrate-database.php <source_database_name> [source_db_user] [source_db_pass]\n";
    echo "Example: php bin/migrate-database.php hrm_db hrm_user my_secret_pass\n";
    exit(1);
}

$sourceDb = $argv[1];
$destDb = DB_NAME;

echo "=== HRM Schema Migration Engine ===\n";
echo "Source Database:      $sourceDb\n";
echo "Destination Database: $destDb\n\n";

try {
    $destPdo = Database::connection();
    
    // Test connection to source database
    $host = DB_HOST;
    $user = $argc > 2 ? $argv[2] : DB_USER;
    $pass = $argc > 3 ? $argv[3] : DB_PASS;
    $charset = 'utf8mb4';
    $sourceDsn = "mysql:host=$host;dbname=$sourceDb;charset=$charset";
    $sourcePdo = new PDO($sourceDsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "✅ Connected to both databases successfully.\n";
    
    // Disable foreign key checks
    $destPdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    echo "⚙️ Disabled foreign key checks on destination.\n\n";
    
    // ---------------------------------------------------------
    // 1. Migrate Departments
    // ---------------------------------------------------------
    echo "Migrating departments...\n";
    $destPdo->exec("TRUNCATE TABLE departments;");
    $stmt = $sourcePdo->query("SELECT * FROM departments");
    $departments = $stmt->fetchAll();
    
    $ins = $destPdo->prepare("
        INSERT INTO departments (id, name, manager, head, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $deptCount = 0;
    foreach ($departments as $dept) {
        $ins->execute([
            $dept['dept_id'],
            $dept['dept_name'],
            $dept['manager'] ?: null,
            $dept['dep_head'] ?: null,
            $dept['created_at'] ?? date('Y-m-d H:i:s'),
            $dept['updated_at'] ?? date('Y-m-d H:i:s')
        ]);
        $deptCount++;
    }
    echo "✅ Migrated $deptCount departments.\n\n";
    
    // ---------------------------------------------------------
    // 2. Migrate Shifts
    // ---------------------------------------------------------
    echo "Migrating shifts...\n";
    $destPdo->exec("TRUNCATE TABLE shifts;");
    $stmt = $sourcePdo->query("SELECT * FROM shifts");
    $shifts = $stmt->fetchAll();
    
    $ins = $destPdo->prepare("
        INSERT INTO shifts (id, name, start_time, end_time, grace_time, halfday_hours, timing, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $shiftCount = 0;
    foreach ($shifts as $shift) {
        // Construct human-readable timing string
        $timing = date('h:i A', strtotime($shift['start_time'])) . ' - ' . date('h:i A', strtotime($shift['end_time']));
        
        $ins->execute([
            $shift['id'],
            $shift['shift_name'],
            $shift['start_time'],
            $shift['end_time'],
            $shift['grace_time'] ?? 0,
            $shift['halfday_hours'] ?? 0,
            $timing
        ]);
        $shiftCount++;
    }
    echo "✅ Migrated $shiftCount shifts.\n\n";
    
    // ---------------------------------------------------------
    // 3. Migrate Employees
    // ---------------------------------------------------------
    echo "Migrating employees...\n";
    $destPdo->exec("TRUNCATE TABLE employees;");
    $stmt = $sourcePdo->query("SELECT * FROM employees");
    $employees = $stmt->fetchAll();
    
    $ins = $destPdo->prepare("
        INSERT INTO employees (
            id, biometric_id, first_name, middle_name, last_name, email, password, role, gender, dob, phone,
            cnic_number, address, emergency_contact, emergency_relation, department_id, shift_id,
            job_title, job_type, salary, joining_date, status, id_card_path, other_docs, resume_path,
            profile_pic, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?
        )
    ");
    
    $empCount = 0;
    $managersMap = []; // Save line_manager name -> employee ID mapping for second pass
    
    foreach ($employees as $emp) {
        // Map role
        $role = 'Employee';
        $oldRole = strtolower($emp['role'] ?? 'user');
        if ($oldRole === 'admin') {
            $role = 'Admin';
        } elseif ($oldRole === 'hr') {
            $role = 'HR';
        }
        
        // Map status
        $status = 'Active';
        $oldStatus = strtolower($emp['status'] ?? 'active');
        if ($oldStatus === 'inactive') {
            $status = 'Exit';
        }
        
        // Map job_type
        $jobType = 'Permanent';
        $oldJobType = $emp['job_type'] ?? 'Permanent';
        if (in_array($oldJobType, ['Permanent', 'Probation', 'Internship'])) {
            $jobType = $oldJobType;
        }
        
        $ins->execute([
            $emp['emp_id'],
            $emp['biometric_id'] ?? null,
            $emp['first_name'],
            $emp['middle_name'] ?: null,
            $emp['last_name'] ?: '',
            $emp['email'],
            $emp['password'],
            $role,
            $emp['gender'] ?: null,
            $emp['date_of_birth'] ?: null,
            $emp['phone'] ?: null,
            $emp['cnic'] ?: null,
            $emp['address'] ?: null,
            $emp['emergency_contact'] ?: null,
            $emp['emergency_relation'] ?: null,
            $emp['department_id'] ?: null,
            $emp['shift_id'] ?: null,
            $emp['designation'] ?: $emp['position'] ?: null,
            $jobType,
            $emp['salary'] ?: 0.00,
            $emp['joining_date'] ?: null,
            $status,
            $emp['id_card_attachment'] ?: null,
            $emp['other_documents'] ?: null,
            $emp['cv_attachment'] ?: null,
            $emp['profile_img'] ?: null,
            $emp['created_at'] ?? date('Y-m-d H:i:s'),
            $emp['updated_at'] ?? date('Y-m-d H:i:s')
        ]);
        
        // Track line manager names to map reports_to in the next step
        if (!empty($emp['line_manager'])) {
            $managersMap[$emp['emp_id']] = trim($emp['line_manager']);
        }
        
        $empCount++;
    }
    echo "✅ Migrated $empCount employees.\n";
    
    // Resolve reports_to (line_manager) relationship
    echo "Resolving manager relationships...\n";
    $updManager = $destPdo->prepare("UPDATE employees SET reports_to = ? WHERE id = ?");
    $resolvedCount = 0;
    
    foreach ($managersMap as $empId => $managerName) {
        // Search employee by name to find their ID
        $findStmt = $destPdo->prepare("
            SELECT id FROM employees 
            WHERE CONCAT(first_name, ' ', last_name) LIKE ? 
               OR CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) LIKE ?
               OR first_name LIKE ?
            LIMIT 1
        ");
        $findStmt->execute(["%$managerName%", "%$managerName%", "%$managerName%"]);
        $managerId = $findStmt->fetchColumn();
        
        if ($managerId) {
            $updManager->execute([$managerId, $empId]);
            $resolvedCount++;
        }
    }
    echo "✅ Resolved $resolvedCount manager relationships.\n\n";
    
    // ---------------------------------------------------------
    // 4. Migrate Attendance
    // ---------------------------------------------------------
    echo "Migrating attendance...\n";
    $destPdo->exec("TRUNCATE TABLE attendance;");
    $stmt = $sourcePdo->query("SELECT * FROM attendance");
    
    $ins = $destPdo->prepare("
        INSERT INTO attendance (id, employee_id, date, shift_id, clock_in, clock_out, working_hours, status, message, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $attCount = 0;
    while ($att = $stmt->fetch()) {
        // Map status
        $status = 'ON TIME';
        $oldStatus = strtolower($att['status'] ?? 'present');
        if ($oldStatus === 'late') {
            $status = 'LATE IN';
        } elseif ($oldStatus === 'halfday' || $oldStatus === 'half-day') {
            $status = 'HALF DAY';
        } elseif ($oldStatus === 'absent') {
            $status = 'ABSENT';
        } elseif ($oldStatus === 'leave') {
            $status = 'LEAVE';
        } elseif ($oldStatus === 'weekend') {
            $status = 'WEEKEND';
        } elseif ($oldStatus === 'holiday') {
            $status = 'HOLIDAY';
        }
        
        // Calculate date from check_in, created_at, or updated_at with a safe default
        $dateStr = null;
        if (!empty($att['check_in']) && strtotime($att['check_in']) !== false) {
            $dateStr = date('Y-m-d', strtotime($att['check_in']));
        } elseif (!empty($att['created_at']) && strtotime($att['created_at']) !== false) {
            $dateStr = date('Y-m-d', strtotime($att['created_at']));
        } elseif (!empty($att['updated_at']) && strtotime($att['updated_at']) !== false) {
            $dateStr = date('Y-m-d', strtotime($att['updated_at']));
        }
        
        if (empty($dateStr) || $dateStr === '1970-01-01' || $dateStr === '0000-00-00') {
            $dateStr = date('Y-m-d');
        }
        
        $ins->execute([
            $att['attendance_id'],
            $att['emp_id'],
            $dateStr,
            $att['shift_id'] ?: null,
            $att['check_in'] ?: null,
            $att['check_out'] ?: null,
            $att['working_hrs'] ?: null,
            $status,
            $att['reason'] ?: null,
            $att['created_at'] ?? date('Y-m-d H:i:s'),
            $att['updated_at'] ?? date('Y-m-d H:i:s')
        ]);
        $attCount++;
    }
    echo "✅ Migrated $attCount attendance records.\n\n";
    
    // ---------------------------------------------------------
    // 5. Migrate Leave Types
    // ---------------------------------------------------------
    echo "Migrating leave types...\n";
    $destPdo->exec("TRUNCATE TABLE leave_types;");
    $stmt = $sourcePdo->query("SELECT * FROM leave_types");
    $leaveTypes = $stmt->fetchAll();
    
    $ins = $destPdo->prepare("
        INSERT INTO leave_types (id, name, days_per_year)
        VALUES (?, ?, ?)
    ");
    
    $ltCount = 0;
    foreach ($leaveTypes as $lt) {
        $days = 0;
        $name = strtolower($lt['type_name']);
        if (str_contains($name, 'annual')) {
            $days = 12;
        } elseif (str_contains($name, 'sick') || str_contains($name, 'casual')) {
            $days = 8;
        }
        
        $ins->execute([
            $lt['leave_type_id'],
            $lt['type_name'],
            $days
        ]);
        $ltCount++;
    }
    echo "✅ Migrated $ltCount leave types.\n\n";
    
    // ---------------------------------------------------------
    // 6. Migrate Leave Requests
    // ---------------------------------------------------------
    echo "Migrating leave requests...\n";
    $destPdo->exec("TRUNCATE TABLE leave_requests;");
    $stmt = $sourcePdo->query("SELECT * FROM leave_requests");
    
    $ins = $destPdo->prepare("
        INSERT INTO leave_requests (id, employee_id, leave_type_id, start_date, end_date, reason, document_path, status, admin_note, applied_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $lrCount = 0;
    while ($lr = $stmt->fetch()) {
        $status = ucfirst(strtolower($lr['status'] ?? 'pending'));
        if (!in_array($status, ['Pending', 'Approved', 'Rejected'])) {
            $status = 'Pending';
        }
        
        $ins->execute([
            $lr['leave_id'],
            $lr['emp_id'],
            $lr['leave_type_id'] ?: 1,
            $lr['start_date'],
            $lr['end_date'],
            $lr['reason'] ?: null,
            $lr['document_path'] ?: null,
            $status,
            $lr['admin_comment'] ?: null,
            $lr['created_at'] ?? date('Y-m-d H:i:s'),
            $lr['updated_at'] ?? date('Y-m-d H:i:s')
        ]);
        $lrCount++;
    }
    echo "✅ Migrated $lrCount leave requests.\n\n";
    
    // ---------------------------------------------------------
    // 7. Migrate Announcements
    // ---------------------------------------------------------
    echo "Migrating announcements...\n";
    $destPdo->exec("TRUNCATE TABLE announcements;");
    $stmt = $sourcePdo->query("SELECT * FROM announcements");
    
    $ins = $destPdo->prepare("
        INSERT INTO announcements (id, title, content, type, target_depts, start_date, end_date, created_by, is_notified, created_at, updated_at)
        VALUES (?, ?, ?, 'UPDATE', NULL, ?, ?, NULL, 0, ?, ?)
    ");
    
    $annCount = 0;
    while ($ann = $stmt->fetch()) {
        $ins->execute([
            $ann['announcement_id'],
            $ann['title'],
            $ann['content'] ?: '',
            $ann['start_date'] ?: null,
            $ann['end_date'] ?: null,
            $ann['created_at'] ?? date('Y-m-d H:i:s'),
            $ann['updated_at'] ?? date('Y-m-d H:i:s')
        ]);
        $annCount++;
    }
    echo "✅ Migrated $annCount announcements.\n\n";
    
    // ---------------------------------------------------------
    // 8. Migrate Events
    // ---------------------------------------------------------
    echo "Migrating events...\n";
    $destPdo->exec("TRUNCATE TABLE events;");
    $stmt = $sourcePdo->query("SELECT * FROM events");
    
    $ins = $destPdo->prepare("
        INSERT INTO events (id, title, description, event_date, event_time, category, target_dept, show_in_announcement, created_by, is_notified, created_at, updated_at)
        VALUES (?, ?, ?, ?, NULL, ?, NULL, 0, NULL, 0, ?, ?)
    ");
    
    $evCount = 0;
    while ($ev = $stmt->fetch()) {
        $ins->execute([
            $ev['event_id'],
            $ev['title'],
            $ev['description'] ?: null,
            $ev['start_date'],
            $ev['event_type'] ?: 'Holiday',
            $ev['created_at'] ?? date('Y-m-d H:i:s'),
            $ev['updated_at'] ?? date('Y-m-d H:i:s')
        ]);
        $evCount++;
    }
    echo "✅ Migrated $evCount events.\n\n";
    
    // ---------------------------------------------------------
    // 9. Migrate Payroll
    // ---------------------------------------------------------
    echo "Migrating payroll...\n";
    $destPdo->exec("TRUNCATE TABLE payroll;");
    $stmt = $sourcePdo->query("SELECT * FROM payroll");
    
    $ins = $destPdo->prepare("
        INSERT INTO payroll (
            id, employee_id, month_year, basic_salary, allowances, deductions, net_payable,
            payment_method, status, paid_date, created_at, updated_at, house_rent, utility,
            fuel, mobile, medical, leaves_count, lates_count, halfdays_count, loan_deduction,
            provident_fund, professional_tax, other_deduction
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?
        )
    ");
    
    $prCount = 0;
    while ($pr = $stmt->fetch()) {
        $monthYear = sprintf('%02d-%04d', $pr['month'], $pr['year']);
        
        $allowances = (float)($pr['fuel_allowance'] ?? 0) + 
                      (float)($pr['house_rent_allowance'] ?? 0) + 
                      (float)($pr['utility_allowance'] ?? 0) + 
                      (float)($pr['mobile_allowance'] ?? 0);
                      
        $status = !empty($pr['payment_date']) ? 'Paid' : 'Pending';
        
        $ins->execute([
            $pr['payroll_id'],
            $pr['emp_id'],
            $monthYear,
            $pr['basic_salary'] ?: 0.00,
            $allowances,
            $pr['total_deductions'] ?: 0.00,
            $pr['net_salary'] ?: 0.00,
            !empty($pr['bank']) ? $pr['bank'] : 'Bank Transfer',
            $status,
            $pr['payment_date'] ?: null,
            $pr['updated_at'] ?? date('Y-m-d H:i:s'),
            $pr['updated_at'] ?? date('Y-m-d H:i:s'),
            $pr['house_rent_allowance'] ?: 0.00,
            $pr['utility_allowance'] ?: 0.00,
            $pr['fuel_allowance'] ?: 0.00,
            $pr['mobile_allowance'] ?: 0.00,
            0.00, // medical
            $pr['leave_days'] ?? 0,
            $pr['late_days'] ?? 0,
            $pr['half_day_days'] ?? 0,
            $pr['loan'] ?: 0.00,
            $pr['provident_fund'] ?: 0.00,
            $pr['professional_tax'] ?: 0.00,
            0.00 // other_deduction
        ]);
        $prCount++;
    }
    echo "✅ Migrated $prCount payroll records.\n\n";
    
    // Re-enable foreign key checks
    $destPdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "⚙️ Re-enabled foreign key checks on destination.\n\n";
    
    echo "🎉 Database migration completed successfully!\n";
    
} catch (\Throwable $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
