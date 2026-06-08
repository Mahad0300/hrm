<?php
// admin/assets/api/kpi_handler.php
require_once '../../../includes/db_connect.php';
require_once '../../../includes/auth_helper.php';
require_once '../../../includes/api/activity_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['Admin', 'HR'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

require_once '../../../includes/access_control_helper.php';
hrGuardApiRequest($pdo, $action);

switch ($action) {
    case 'fetch_summary':
        try {
            // Average Score
            $avgStmt = $pdo->query("SELECT AVG(overall_rating) as avg_score FROM kpi_reviews");
            $avg = $avgStmt->fetch();

            // Total Employees Rated (Distinct)
            $ratedStmt = $pdo->query("SELECT COUNT(DISTINCT employee_id) as rated_count FROM kpi_reviews");
            $rated = $ratedStmt->fetch();

            // Total Employees for context
            $totalStmt = $pdo->query("SELECT COUNT(*) as total_count FROM employees WHERE deleted_at IS NULL AND role != 'Admin'");
            $total = $totalStmt->fetch();

            // Top Department (by Avg Score)
            $topDeptStmt = $pdo->query("
                SELECT d.name as dept_name 
                FROM kpi_reviews r 
                JOIN employees e ON r.employee_id = e.id 
                JOIN departments d ON e.department_id = d.id 
                GROUP BY d.id 
                ORDER BY AVG(r.overall_rating) DESC 
                LIMIT 1
            ");
            $topDept = $topDeptStmt->fetch();

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'avg_score' => number_format($avg['avg_score'] ?? 0, 1),
                    'rated_count' => $rated['rated_count'] ?? 0,
                    'total_count' => $total['total_count'] ?? 0,
                    'top_dept' => $topDept['dept_name'] ?? 'N/A'
                ]
            ]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'fetch_list':
        try {
            $stmt = $pdo->query("
                SELECT e.id as employee_id, e.first_name, e.middle_name, e.last_name, e.profile_pic,
                       d.name as department_name, e.department_id,
                       r.id as review_id, r.review_date, r.overall_rating, 
                       COALESCE(r.status, 'Not Rated') as status,
                       (SELECT goal_name FROM kpi_goals WHERE review_id = r.id LIMIT 1) as kpi_goal,
                       (SELECT CONCAT(achieved_score, ' / ', target_score) FROM kpi_goals WHERE review_id = r.id LIMIT 1) as target_vs_achieved,
                       (SELECT achieved_score FROM kpi_goals WHERE review_id = r.id LIMIT 1) as progress_percent
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN (
                    SELECT r1.*
                    FROM kpi_reviews r1
                    JOIN (
                        SELECT employee_id, MAX(id) as max_id
                        FROM kpi_reviews
                        GROUP BY employee_id
                    ) r2 ON r1.id = r2.max_id
                ) r ON e.id = r.employee_id
                WHERE e.deleted_at IS NULL AND e.role != 'Admin'
                ORDER BY e.first_name ASC
            ");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'fetch_employees':
        try {
            $stmt = $pdo->query("SELECT id, first_name, middle_name, last_name, profile_pic FROM employees WHERE deleted_at IS NULL AND role != 'Admin'");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'fetch_latest_goals':
        try {
            $empId = $_GET['employee_id'] ?? 0;
            // Get goals from the absolute latest review of this employee
            $stmt = $pdo->prepare("
                SELECT goal_name 
                FROM kpi_goals 
                WHERE review_id = (SELECT id FROM kpi_reviews WHERE employee_id = ? ORDER BY review_date DESC LIMIT 1)
            ");
            $stmt->execute([$empId]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'fetch_report_data':
        try {
            $empId = $_GET['id'] ?? 0;

            // Employee Profile
            $empStmt = $pdo->prepare("
                SELECT e.first_name, e.middle_name, e.last_name, e.profile_pic, e.job_title, d.name as dept_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE e.id = ?
            ");
            $empStmt->execute([$empId]);
            $employee = $empStmt->fetch();

            if (!$employee) {
                echo json_encode(['status' => 'error', 'message' => 'Employee not found.']);
                exit;
            }

            // History & Scores
            $historyStmt = $pdo->prepare("
                SELECT r.id, r.review_date, r.overall_rating, r.status, r.feedback, r.period,
                       rv.first_name as reviewer_first, rv.middle_name as reviewer_middle, rv.last_name as reviewer_last
                FROM kpi_reviews r
                JOIN employees rv ON r.reviewer_id = rv.id
                WHERE r.employee_id = ?
                ORDER BY r.review_date DESC
            ");
            $historyStmt->execute([$empId]);
            $history = $historyStmt->fetchAll();

            // All goals for each review in the history
            foreach ($history as &$review) {
                $goalsStmt = $pdo->prepare("SELECT goal_name, target_score, achieved_score, reviewer_comment FROM kpi_goals WHERE review_id = ?");
                $goalsStmt->execute([$review['id']]);
                $review['goals'] = $goalsStmt->fetchAll();
            }

            echo json_encode([
                'status' => 'success',
                'employee' => $employee,
                'history' => $history
            ]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'add_review':
        try {
            $pdo->beginTransaction();

            $employee_id = $_POST['employee_id'];
            $review_id = $_POST['review_id'] ?? null;
            $period = $_POST['period'];
            $review_date = date('Y-m-d');
            $overall_rating = $_POST['overall_rating'];
            $feedback = $_POST['feedback'] ?? '';
            $reviewer_id = $_SESSION['user_id'];

            // Map score to status
            $status = 'On Track';
            if ($overall_rating >= 4.5)
                $status = 'Excelling';
            else if ($overall_rating >= 3.5)
                $status = 'Good';
            else if ($overall_rating >= 2.5)
                $status = 'On Track';
            else if ($overall_rating >= 1.5)
                $status = 'Below Target';
            else
                $status = 'Poor';

            if ($review_id) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE kpi_reviews SET period = ?, overall_rating = ?, status = ?, feedback = ? WHERE id = ?");
                $stmt->execute([$period, $overall_rating, $status, $feedback, $review_id]);
                // Clear old goals
                $pdo->prepare("DELETE FROM kpi_goals WHERE review_id = ?")->execute([$review_id]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO kpi_reviews (employee_id, reviewer_id, period, review_date, overall_rating, status, feedback) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$employee_id, $reviewer_id, $period, $review_date, $overall_rating, $status, $feedback]);
                $review_id = $pdo->lastInsertId();
            }

            // Insert/Re-insert Goals
            if (!empty($_POST['goals'])) {
                $goals = json_decode($_POST['goals'], true);
                $goalStmt = $pdo->prepare("INSERT INTO kpi_goals (review_id, goal_name, target_score, achieved_score, reviewer_comment) VALUES (?, ?, ?, ?, ?)");
                foreach ($goals as $goal) {
                    $goalStmt->execute([$review_id, $goal['name'], 100, $goal['score'], $goal['comment'] ?? '']);
                }
            }

            $pdo->commit();

            // [LOG]
            $e_stmt = $pdo->prepare("SELECT first_name, last_name FROM employees WHERE id = ?");
            $e_stmt->execute([$employee_id]);
            $e_data = $e_stmt->fetch();
            $e_name = ($e_data['first_name'] ?? 'Unknown') . ' ' . ($e_data['last_name'] ?? '');

            $logAction = $review_id ? "Updated Performance Review" : "Submitted Performance Review";
            $logMsg = $review_id ? "Modified the performance appraisal details for team member: $e_name (Period: $period)" : "Formally submitted a new performance appraisal for team member: $e_name (Period: $period)";
            logActivity($_SESSION['user_id'], $logAction, "KPI Management", $logMsg);

            echo json_encode(['status' => 'success', 'message' => $review_id ? 'Review updated successfully!' : 'Review submitted successfully!']);
        } catch (Exception $e) {
            if ($pdo->inTransaction())
                $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'delete_review':
        try {
            $id = $_POST['id'];

            // Fetch name for log before delete
            $e_stmt = $pdo->prepare("SELECT e.first_name, e.last_name FROM kpi_reviews r JOIN employees e ON r.employee_id = e.id WHERE r.id = ?");
            $e_stmt->execute([$id]);
            $e_data = $e_stmt->fetch();
            $e_name = ($e_data['first_name'] ?? 'Unknown') . ' ' . ($e_data['last_name'] ?? '');

            $stmt = $pdo->prepare("DELETE FROM kpi_reviews WHERE id = ?");
            $stmt->execute([$id]);

            // [LOG]
            logActivity($_SESSION['user_id'], "Deleted Performance Review", "KPI Management", "Permanently removed the performance appraisal record for team member: $e_name");

            echo json_encode(['status' => 'success', 'message' => 'Review deleted successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'fetch_review_details':
        try {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM kpi_reviews WHERE id = ?");
            $stmt->execute([$id]);
            $review = $stmt->fetch();

            if ($review) {
                $goalsStmt = $pdo->prepare("SELECT * FROM kpi_goals WHERE review_id = ?");
                $goalsStmt->execute([$id]);
                $review['goals'] = $goalsStmt->fetchAll();
                echo json_encode(['status' => 'success', 'data' => $review]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Review not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
}
