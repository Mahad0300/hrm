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

switch ($action) {
    case 'fetch_payroll':
        try {
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

            // Fetch Total for Pagination
            $countSql = "SELECT COUNT(*) FROM employees e WHERE $whereSql";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $totalEntries = $countStmt->fetchColumn();

            // Fetch Employees with their Payroll Status for the specific month
            $sql = "SELECT e.id as employee_id, e.first_name, e.middle_name, e.last_name, e.profile_pic, e.salary as basic_salary,
                           p.id as payroll_id, p.month_year, p.deductions, p.net_payable, 
                           COALESCE(p.status, 'Pending') as status,
                           d.name as dept_name
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN payroll p ON e.id = p.employee_id AND p.month_year = ?
                    WHERE $whereSql
                    ORDER BY e.first_name ASC
                    LIMIT $limit OFFSET $offset";
            
            $stmt = $pdo->prepare($sql);
            $execParams = array_merge([$month], $params);
            $stmt->execute($execParams);
            $payrolls = $stmt->fetchAll();

            // Filter by status on the final results if status is set
            if (!empty($status)) {
                $payrolls = array_filter($payrolls, function($p) use ($status) {
                    return $p['status'] === $status;
                });
                $payrolls = array_values($payrolls);
                $totalEntries = count($payrolls); // Simplified for now
            }

            echo json_encode([
                'status' => 'success',
                'data' => $payrolls,
                'total' => $totalEntries,
                'page' => $page,
                'limit' => $limit
            ]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_eligible_employees':
        try {
            $stmt = $pdo->query("SELECT e.id, e.first_name, e.middle_name, e.last_name, e.job_title, e.salary, d.name as dept_name 
                                FROM employees e 
                                LEFT JOIN departments d ON e.department_id = d.id 
                                WHERE e.role = 'Employee' AND e.status = 'Active' AND e.deleted_at IS NULL 
                                ORDER BY e.first_name ASC");
            $employees = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $employees]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}
?>
