<?php
// admin/assets/api/payroll_handler.php
require_once '../../../includes/db_connect.php';
require_once '../../../includes/auth_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['Admin', 'HR'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

require_once '../../../includes/access_control_helper.php';
hrGuardApiRequest($pdo, $action);

switch ($action) {
    case 'fetch_payroll':
        try {
            // Fetch Payroll Cycle Settings
            $setStmt = $pdo->query("SELECT meta_key, meta_value FROM settings WHERE meta_key IN ('payroll_start_day', 'payroll_end_day')");
            $settings = $setStmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $startDay = $settings['payroll_start_day'] ?? '1';
            $endDay = $settings['payroll_end_day'] ?? '30';

            $search = trim($_GET['search'] ?? '');
            $month = $_GET['month'] ?? date('Y-m'); 
            $status = $_GET['status'] ?? '';
            $dept = $_GET['department'] ?? '';
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 10);
            $offset = ($page - 1) * $limit;

            $where = ["e.role = 'Employee'", "e.deleted_at IS NULL", "e.status = 'Active'"];
            $params = [];

            if (!empty($search)) {
                $where[] = "(e.first_name LIKE ? OR e.last_name LIKE ? OR e.id LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if (!empty($dept)) {
                $where[] = "e.department_id = ?";
                $params[] = $dept;
            }

            $whereSql = implode(" AND ", $where);

            // Status filter via HAVING (because payroll status uses COALESCE on LEFT JOIN)
            $havingSql = '';
            $havingParams = [];
            if (!empty($status)) {
                $havingSql = "HAVING status = ?";
                $havingParams[] = $status;
            }

            // Fetch Total for Pagination (with status filter)
            $countSql = "SELECT COUNT(*) FROM (
                SELECT COALESCE(p.status, 'Pending') as status
                FROM employees e
                LEFT JOIN payroll p ON e.id = p.employee_id AND p.month_year = ?
                WHERE $whereSql
                $havingSql
            ) as filtered";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute(array_merge([$month], $params, $havingParams));
            $totalEntries = $countStmt->fetchColumn();

            // Fetch Employees with their Payroll Status for the specific month
            $sql = "SELECT e.id as employee_id, e.first_name, e.middle_name, e.last_name, e.profile_pic, e.salary as basic_salary,
                           p.id as payroll_id, p.month_year, p.deductions, p.loan_deduction, p.provident_fund,
                           p.professional_tax, p.other_deduction, p.net_payable,
                           (
                               COALESCE(p.deductions, 0) + COALESCE(p.loan_deduction, 0) + COALESCE(p.provident_fund, 0)
                               + COALESCE(p.professional_tax, 0) + COALESCE(p.other_deduction, 0)
                           ) AS total_deduction,
                           COALESCE(p.status, 'Pending') as status,
                           d.name as dept_name
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN payroll p ON e.id = p.employee_id AND p.month_year = ?
                    WHERE $whereSql
                    $havingSql
                    ORDER BY e.first_name ASC
                    LIMIT $limit OFFSET $offset";
            
            $stmt = $pdo->prepare($sql);
            $execParams = array_merge([$month], $params, $havingParams);
            $stmt->execute($execParams);
            $payrolls = $stmt->fetchAll();

            echo json_encode([
                'status' => 'success',
                'data' => $payrolls,
                'total' => $totalEntries,
                'page' => $page,
                'limit' => $limit
            ]);
        } catch (PDOException $e) {
            error_log("Payroll Error: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'fetch_eligible_employees':
        try {
            $searchId = trim($_GET['searchId'] ?? '');
            $searchName = trim($_GET['searchName'] ?? '');
            $dept = $_GET['department'] ?? '';
            
            $where = ["e.role = 'Employee'", "e.status = 'Active'", "e.deleted_at IS NULL"];
            $params = [];

            if (!empty($searchId)) {
                $where[] = "e.id = ?";
                // If the user typed "033" or "EMP-033", we should try to extract the number
                $cleanId = preg_replace('/[^0-9]/', '', $searchId);
                $params[] = $cleanId ?: $searchId; 
            }

            if (!empty($searchName)) {
                $where[] = "(e.first_name LIKE ? OR e.last_name LIKE ? OR e.middle_name LIKE ?)";
                $params[] = "%$searchName%";
                $params[] = "%$searchName%";
                $params[] = "%$searchName%";
            }

            if (!empty($dept)) {
                $where[] = "e.department_id = ?";
                $params[] = $dept;
            }

            $whereSql = implode(" AND ", $where);
            $sql = "SELECT e.id, e.first_name, e.middle_name, e.last_name, e.job_title, e.salary, d.name as dept_name 
                    FROM employees e 
                    LEFT JOIN departments d ON e.department_id = d.id 
                    WHERE $whereSql 
                    ORDER BY e.first_name ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $employees = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $employees]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'get_payroll_details':
        try {
            $employee_id = $_GET['employee_id'] ?? '';
            $month = $_GET['month'] ?? date('Y-m');

            if (empty($employee_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Employee ID is required.']);
                exit;
            }

            // Fetch Payroll Cycle Settings
            $setStmt = $pdo->query("SELECT meta_key, meta_value FROM settings WHERE meta_key IN ('payroll_start_day', 'payroll_end_day')");
            $settings = $setStmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $startDay = (int)($settings['payroll_start_day'] ?? 1);
            $endDay = (int)($settings['payroll_end_day'] ?? 30);

            // Calculate Start and End Dates based on the selected month
            $currentMonthObj = new DateTime($month . "-01");
            $prevMonthObj = clone $currentMonthObj;
            $prevMonthObj->modify("-1 month");

            $startDate = $prevMonthObj->format('Y-m') . "-" . str_pad($startDay, 2, '0', STR_PAD_LEFT);
            $endDate = $currentMonthObj->format('Y-m') . "-" . str_pad($endDay, 2, '0', STR_PAD_LEFT);

            // 1. Check if payroll record already exists
            $stmt = $pdo->prepare("SELECT * FROM payroll WHERE employee_id = ? AND month_year = ?");
            $stmt->execute([$employee_id, $month]);
            $payroll = $stmt->fetch();

            // 2. Fetch Employee Info (Gross Salary)
            $empStmt = $pdo->prepare("SELECT first_name, last_name, salary, joining_date, job_title, department_id FROM employees WHERE id = ?");
            $empStmt->execute([$employee_id]);
            $employee = $empStmt->fetch();

            if (!$employee) {
                echo json_encode(['status' => 'error', 'message' => 'Employee not found.']);
                exit;
            }

            $gross_salary = (float)$employee['salary'];
            
            // 3. If payroll exists, use those values, otherwise calculate
            if ($payroll) {
                echo json_encode(['status' => 'success', 'data' => $payroll, 'employee' => $employee, 'cycle' => ['start' => $startDate, 'end' => $endDate]]);
            } else {
                // Fetch attendance for deductions within the calculated range
                $attStmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM attendance WHERE employee_id = ? AND date BETWEEN ? AND ? GROUP BY status");
                $attStmt->execute([$employee_id, $startDate, $endDate]);
                $attendance = $attStmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $absents = (int)($attendance['ABSENT'] ?? 0);
                $lates = (int)($attendance['LATE IN'] ?? 0);
                $halfdays = (int)($attendance['HALF DAY'] ?? 0);

                // Calculations
                $oneDaySalary = $gross_salary / 30;
                
                // Earnings Breakdown
                $breakdown = [
                    'basic' => $gross_salary * 0.50,
                    'house_rent' => $gross_salary * 0.20,
                    'utility' => $gross_salary * 0.10,
                    'fuel' => $gross_salary * 0.05,
                    'mobile' => $gross_salary * 0.05,
                    'medical' => $gross_salary * 0.10
                ];

                // Deductions
                $lateDeductionDays = floor($lates / 3);
                $totalDeductionDays = $absents + $lateDeductionDays + ($halfdays * 0.5);
                $attendanceDeduction = $totalDeductionDays * $oneDaySalary;

                $data = [
                    'employee_id' => $employee_id,
                    'month_year' => $month,
                    'basic_salary' => $breakdown['basic'],
                    'house_rent' => $breakdown['house_rent'],
                    'utility' => $breakdown['utility'],
                    'fuel' => $breakdown['fuel'],
                    'mobile' => $breakdown['mobile'],
                    'medical' => $breakdown['medical'],
                    'leaves_count' => $absents,
                    'lates_count' => $lates,
                    'halfdays_count' => $halfdays,
                    'deductions' => round($attendanceDeduction, 2),
                    'loan_deduction' => 0,
                    'provident_fund' => 0,
                    'professional_tax' => 0,
                    'other_deduction' => 0,
                    'net_payable' => round($gross_salary - $attendanceDeduction, 2),
                    'gross_salary' => $gross_salary,
                    'status' => 'Pending'
                ];

                echo json_encode(['status' => 'success', 'data' => $data, 'employee' => $employee, 'cycle' => ['start' => $startDate, 'end' => $endDate]]);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'generate_bulk_payroll':
        try {
            $ids = json_decode($_POST['ids'] ?? '[]');
            $month = $_POST['month'] ?? date('Y-m');

            if (empty($ids)) {
                echo json_encode(['status' => 'error', 'message' => 'No employees selected.']);
                exit;
            }

            // Fetch Settings
            $setStmt = $pdo->query("SELECT meta_key, meta_value FROM settings WHERE meta_key IN ('payroll_start_day', 'payroll_end_day')");
            $settings = $setStmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $startDay = (int)($settings['payroll_start_day'] ?? 1);
            $endDay = (int)($settings['payroll_end_day'] ?? 30);

            $currentMonthObj = new DateTime($month . "-01");
            $prevMonthObj = clone $currentMonthObj;
            $prevMonthObj->modify("-1 month");
            $startDate = $prevMonthObj->format('Y-m') . "-" . str_pad($startDay, 2, '0', STR_PAD_LEFT);
            $endDate = $currentMonthObj->format('Y-m') . "-" . str_pad($endDay, 2, '0', STR_PAD_LEFT);

            $count = 0;
            foreach ($ids as $employee_id) {
                // Fetch Employee
                $empStmt = $pdo->prepare("SELECT salary FROM employees WHERE id = ?");
                $empStmt->execute([$employee_id]);
                $employee = $empStmt->fetch();
                if (!$employee) continue;

                $gross_salary = (float)$employee['salary'];
                $oneDaySalary = $gross_salary / 30;

                // Fetch Attendance
                $attStmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM attendance WHERE employee_id = ? AND date BETWEEN ? AND ? GROUP BY status");
                $attStmt->execute([$employee_id, $startDate, $endDate]);
                $attendance = $attStmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $absents = (int)($attendance['ABSENT'] ?? 0);
                $lates = (int)($attendance['LATE IN'] ?? 0);
                $halfdays = (int)($attendance['HALF DAY'] ?? 0);

                // Calculate Deductions
                $lateDeductionDays = floor($lates / 3);
                $totalDeductionDays = $absents + $lateDeductionDays + ($halfdays * 0.5);
                $attendanceDeduction = $totalDeductionDays * $oneDaySalary;

                // Earnings Breakdown
                $basic = $gross_salary * 0.50;
                $hrent = $gross_salary * 0.20;
                $util = $gross_salary * 0.10;
                $fuel = $gross_salary * 0.05;
                $mob = $gross_salary * 0.05;
                $med = $gross_salary * 0.10;

                // Check if payroll already exists
                $checkStmt = $pdo->prepare("SELECT id, loan_deduction, provident_fund, professional_tax, other_deduction FROM payroll WHERE employee_id = ? AND month_year = ?");
                $checkStmt->execute([$employee_id, $month]);
                $exists = $checkStmt->fetch();

                // Preserve existing loan/PF/tax/other if record exists, otherwise default to 0
                $loan = (float)($exists['loan_deduction'] ?? 0);
                $pfund = (float)($exists['provident_fund'] ?? 0);
                $ptax = (float)($exists['professional_tax'] ?? 0);
                $other = (float)($exists['other_deduction'] ?? 0);
                $totalDeductions = $attendanceDeduction + $loan + $pfund + $ptax + $other;
                $net_payable = $gross_salary - $totalDeductions;

                if ($exists) {
                    $sql = "UPDATE payroll SET 
                            basic_salary = ?, house_rent = ?, utility = ?, fuel = ?, mobile = ?, medical = ?,
                            leaves_count = ?, lates_count = ?, halfdays_count = ?, deductions = ?, 
                            net_payable = ?, status = 'Paid'
                            WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$basic, $hrent, $util, $fuel, $mob, $med, $absents, $lates, $halfdays, $attendanceDeduction, $net_payable, $exists['id']]);
                } else {
                    $sql = "INSERT INTO payroll (employee_id, month_year, basic_salary, house_rent, utility, fuel, mobile, medical, leaves_count, lates_count, halfdays_count, deductions, net_payable, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid')";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$employee_id, $month, $basic, $hrent, $util, $fuel, $mob, $med, $absents, $lates, $halfdays, $attendanceDeduction, $net_payable]);
                }
                $count++;
            }

            echo json_encode(['status' => 'success', 'message' => 'Bulk payroll generated.', 'count' => $count]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'save_payroll':
        try {
            $employee_id = $_POST['employee_id'] ?? '';
            $month = $_POST['month'] ?? '';
            
            if (empty($employee_id) || empty($month)) {
                echo json_encode(['status' => 'error', 'message' => 'Employee ID and Month are required.']);
                exit;
            }

            // Fetch Employee and Settings to recalculate
            $empStmt = $pdo->prepare("SELECT salary FROM employees WHERE id = ?");
            $empStmt->execute([$employee_id]);
            $employee = $empStmt->fetch();
            $gross_salary = (float)($employee['salary'] ?? 0);

            $setStmt = $pdo->query("SELECT meta_key, meta_value FROM settings WHERE meta_key IN ('payroll_start_day', 'payroll_end_day')");
            $settings = $setStmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $startDay = (int)($settings['payroll_start_day'] ?? 1);
            $endDay = (int)($settings['payroll_end_day'] ?? 30);

            // Re-calculate Attendance Deduction
            $leaves = (float)($_POST['leaves'] ?? 0);
            $lates = (float)($_POST['late'] ?? 0);
            $halfdays = (float)($_POST['halfday'] ?? 0);
            
            $oneDaySalary = $gross_salary / 30;
            $lateDeductionDays = floor($lates / 3);
            $attendanceDeduction = ($leaves + $lateDeductionDays + ($halfdays * 0.5)) * $oneDaySalary;

            // Earnings Breakdown
            $basic = $gross_salary * 0.50;
            $hrent = $gross_salary * 0.20;
            $util = $gross_salary * 0.10;
            $fuel = $gross_salary * 0.05;
            $mob = $gross_salary * 0.05;
            $med = $gross_salary * 0.10;
            
            $loan = (float)($_POST['loan'] ?? 0);
            $pfund = (float)($_POST['pfund'] ?? 0);
            $ptax = (float)($_POST['ptax'] ?? 0);
            $other = (float)($_POST['other'] ?? 0);
            
            $totalDeductions = $attendanceDeduction + $loan + $pfund + $ptax + $other;
            $net_payable = $gross_salary - $totalDeductions;
            $status = 'Paid'; // Automatically set to Paid when saved/updated

            // Check if exists
            $stmt = $pdo->prepare("SELECT id FROM payroll WHERE employee_id = ? AND month_year = ?");
            $stmt->execute([$employee_id, $month]);
            $exists = $stmt->fetch();

            if ($exists) {
                $sql = "UPDATE payroll SET 
                        basic_salary = ?, house_rent = ?, utility = ?, fuel = ?, mobile = ?, medical = ?,
                        leaves_count = ?, lates_count = ?, halfdays_count = ?, 
                        loan_deduction = ?, provident_fund = ?, professional_tax = ?, other_deduction = ?,
                        deductions = ?, net_payable = ?, status = ?
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$basic, $hrent, $util, $fuel, $mob, $med, $leaves, $lates, $halfdays, $loan, $pfund, $ptax, $other, $attendanceDeduction, $net_payable, $status, $exists['id']]);
            } else {
                $sql = "INSERT INTO payroll (employee_id, month_year, basic_salary, house_rent, utility, fuel, mobile, medical, leaves_count, lates_count, halfdays_count, loan_deduction, provident_fund, professional_tax, other_deduction, deductions, net_payable, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$employee_id, $month, $basic, $hrent, $util, $fuel, $mob, $med, $leaves, $lates, $halfdays, $loan, $pfund, $ptax, $other, $attendanceDeduction, $net_payable, $status]);
            }

            echo json_encode(['status' => 'success', 'message' => 'Payroll record saved successfully.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}
?>
