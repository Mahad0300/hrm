<?php
require_once __DIR__ . '/bootstrap.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!in_array($_SESSION['user_role'] ?? '', ['Admin', 'HR']) && $action !== 'fetch_my_kpi') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if (isHrPortalUser() && $action !== '') {
    hrGuardApiRequest($pdo, $action, basename(__FILE__));
}

// ─── Helper: Calculate attendance score for an employee over a month ──────────
function calculateAttendanceScore(PDO $pdo, int $empId, string $yearMonth, float $targetWeight = 15.00): array {
    $range = \App\Helpers\PayrollConfig::getPayrollRange($pdo, $yearMonth);
    $startDate = $range['start'];
    $endDate   = $range['end'];

    // Count workdays (Mon–Fri) within the configured Payroll Cycle range
    $startTs       = strtotime($startDate);
    $endTs         = strtotime($endDate);
    $totalWorkdays = 0;

    for ($curr = $startTs; $curr <= $endTs; $curr = strtotime('+1 day', $curr)) {
        $dow = (int)date('N', $curr);
        if ($dow < 6) { // Mon–Fri
            $totalWorkdays++;
        }
    }

    // Count days employee was present (status ON TIME, LATE IN, or HALF DAY) within the payroll cycle date range
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM attendance
        WHERE employee_id = ?
          AND date BETWEEN ? AND ?
          AND (
            (clock_in IS NOT NULL AND status IN ('ON TIME', 'LATE IN', 'HALF DAY'))
            OR status IN ('ON TIME', 'LATE IN', 'HALF DAY')
          )
    ");
    $stmt->execute([$empId, $startDate, $endDate]);
    $presentDays = (int)$stmt->fetchColumn();

    $attRatio = $totalWorkdays > 0 ? round($presentDays / $totalWorkdays, 4) : 0;
    $attScore = round($attRatio * $targetWeight, 2);

    return [
        'present_days'   => $presentDays,
        'total_workdays' => $totalWorkdays,
        'att_pct'        => round($attRatio * 100, 2),
        'att_ratio'      => $attRatio,
        'att_score'      => $attScore,
        'target_weight'  => $targetWeight,
        'start_date'     => $startDate,
        'end_date'       => $endDate,
    ];
}

// ─── Helper: Convert percentage to grade label ────────────────────────────────
function pctToGrade(float $pct): string {
    if ($pct >= 95) return 'Grade A excellent';
    if ($pct >= 85) return 'Grade B good';
    return 'Grade C needs improvement';
}

// ─── Helper: Convert percentage to status ────────────────────────────────────
function pctToStatus(float $pct): string {
    if ($pct >= 95) return 'Excellent';
    if ($pct >= 85) return 'Good';
    return 'Needs Improvement';
}

// ─── Helper: Fetch items for a review ───────────────────────────────────────
function fetchReviewItems(PDO $pdo, int $reviewId): array {
    $stmt = $pdo->prepare("SELECT * FROM kpi_review_items WHERE review_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$reviewId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        // Fallback for legacy reviews in kpi_goals
        $gStmt = $pdo->prepare("SELECT goal_name as item_name, weight as target_weight, target_score, achieved_score, reviewer_comment as comment FROM kpi_goals WHERE review_id = ? ORDER BY id ASC");
        $gStmt->execute([$reviewId]);
        $goals = $gStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($goals as $g) {
            $category = 'Performance';
            if (stripos($g['item_name'], 'attendance') !== false || stripos($g['item_name'], 'dependability') !== false) {
                $category = 'Attendance & Discipline';
            } elseif (stripos($g['item_name'], 'manager') !== false || stripos($g['item_name'], 'feedback') !== false) {
                $category = "Manager's Feedback";
            }
            $targetWeight = (float)($g['target_weight'] > 0 ? $g['target_weight'] : $g['target_score']);
            $achievedScore = (float)$g['achieved_score'];
            $ratio = $targetWeight > 0 ? round($achievedScore / $targetWeight, 4) : 1.0;
            $items[] = [
                'id' => 0,
                'review_id' => $reviewId,
                'category' => $category,
                'item_name' => $g['item_name'],
                'target_weight' => $targetWeight,
                'evaluation_criteria' => $g['item_name'],
                'achieved_ratio' => $ratio,
                'achieved_score' => $achievedScore,
                'comment' => $g['comment'],
                'sort_order' => 0
            ];
        }
    }
    return $items;
}

switch ($action) {

    // ── Employee personal KPI result ─────────────────────────────────────────
    case 'fetch_my_kpi':
        try {
            $empId = (int)($_SESSION['user_id'] ?? 0);
            if (!$empId) {
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
                break;
            }

            $selectedMonth = trim($_GET['month'] ?? $_POST['month'] ?? '');

            // Fetch all reviews for this employee
            $historyStmt = $pdo->prepare("
                SELECT id, review_date, period, period_month, overall_rating, status, feedback
                FROM kpi_reviews
                WHERE employee_id = ?
                ORDER BY period_month DESC, id DESC
            ");
            $historyStmt->execute([$empId]);
            $allReviews = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($allReviews)) {
                echo json_encode([
                    'status' => 'success',
                    'has_kpi' => false,
                    'message' => 'No KPI performance evaluations recorded yet.'
                ]);
                break;
            }

            // Find review for selectedMonth or take the latest
            $targetReview = null;
            if ($selectedMonth !== '') {
                foreach ($allReviews as $rev) {
                    if ($rev['period_month'] === $selectedMonth) {
                        $targetReview = $rev;
                        break;
                    }
                }
            }
            if (!$targetReview) {
                $targetReview = $allReviews[0];
            }

            $items = fetchReviewItems($pdo, (int)$targetReview['id']);
            $overallPct = round((float)$targetReview['overall_rating'] * 20, 1);

            echo json_encode([
                'status' => 'success',
                'has_kpi' => true,
                'data' => [
                    'review_id'      => $targetReview['id'],
                    'period_month'   => $targetReview['period_month'],
                    'review_date'    => $targetReview['review_date'],
                    'overall_rating' => (float)$targetReview['overall_rating'],
                    'overall_pct'    => $overallPct,
                    'grade'          => pctToGrade($overallPct),
                    'kpi_status'     => pctToStatus($overallPct),
                    'feedback'       => $targetReview['feedback'],
                    'items'          => $items,
                    'history'        => array_map(function($r) {
                        $pct = round((float)$r['overall_rating'] * 20, 1);
                        return [
                            'id' => $r['id'],
                            'period_month' => $r['period_month'],
                            'overall_pct' => $pct,
                            'grade' => pctToGrade($pct),
                            'kpi_status' => pctToStatus($pct),
                        ];
                    }, $allReviews)
                ]
            ]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred: ' . $e->getMessage()]);
        }
        break;

    // ── Summary Metrics ───────────────────────────────────────────────────────
    case 'fetch_summary':
        try {
            $avgStmt = $pdo->query("
                SELECT AVG(r.overall_rating) as avg_score
                FROM kpi_reviews r
                INNER JOIN employees e ON e.id = r.employee_id
                WHERE e.deleted_at IS NULL
            ");
            $avg = $avgStmt->fetch();

            $ratedStmt = $pdo->query("
                SELECT COUNT(DISTINCT r.employee_id) as rated_count
                FROM kpi_reviews r
                INNER JOIN employees e ON e.id = r.employee_id
                WHERE e.deleted_at IS NULL
            ");
            $rated = $ratedStmt->fetch();

            $totalStmt = $pdo->query("SELECT COUNT(*) as total_count FROM employees WHERE deleted_at IS NULL AND status = 'Active'");
            $total = $totalStmt->fetch();

            $topDeptStmt = $pdo->query("
                SELECT d.name as dept_name
                FROM kpi_reviews r
                JOIN employees e ON r.employee_id = e.id
                JOIN departments d ON e.department_id = d.id
                WHERE e.deleted_at IS NULL
                GROUP BY d.id
                ORDER BY AVG(r.overall_rating) DESC
                LIMIT 1
            ");
            $topDept = $topDeptStmt->fetch();

            echo json_encode([
                'status' => 'success',
                'data'   => [
                    'avg_score'   => number_format(($avg['avg_score'] ?? 0) * 20, 1),
                    'rated_count' => $rated['rated_count'] ?? 0,
                    'total_count' => $total['total_count'] ?? 0,
                    'top_dept' => $topDept['dept_name'] ?? 'N/A',
                ]
            ]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred.']);
        }
        break;

    // ── Employee list for management table ───────────────────────────────────
    case 'fetch_list':
        try {
            $filterMonth = trim($_GET['period_month'] ?? $_POST['period_month'] ?? '');

            if ($filterMonth !== '') {
                $sql = "
                    SELECT e.id as employee_id, e.first_name, e.middle_name, e.last_name, e.profile_pic, e.job_title,
                           d.name as department_name, e.department_id,
                           r.id as review_id, r.review_date, r.period_month, r.overall_rating,
                           COALESCE(r.status, 'Not Rated') as status, r.feedback
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN kpi_reviews r
                           ON r.employee_id = e.id
                          AND r.period_month = :month
                          AND r.id = (
                              SELECT id FROM kpi_reviews
                              WHERE employee_id = e.id AND period_month = :month2
                              ORDER BY id DESC LIMIT 1
                          )
                    WHERE e.deleted_at IS NULL AND e.status = 'Active'
                    ORDER BY e.first_name ASC
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':month' => $filterMonth, ':month2' => $filterMonth]);
            } else {
                $sql = "
                    SELECT e.id as employee_id, e.first_name, e.middle_name, e.last_name, e.profile_pic, e.job_title,
                           d.name as department_name, e.department_id,
                           r.id as review_id, r.review_date, r.period_month, r.overall_rating,
                           COALESCE(r.status, 'Not Rated') as status, r.feedback
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN (
                        SELECT r1.*
                        FROM kpi_reviews r1
                        JOIN (SELECT employee_id, MAX(id) as max_id FROM kpi_reviews GROUP BY employee_id) r2
                          ON r1.id = r2.max_id
                    ) r ON e.id = r.employee_id
                    WHERE e.deleted_at IS NULL AND e.status = 'Active'
                    ORDER BY e.first_name ASC
                ";
                $stmt = $pdo->query($sql);
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$row) {
                $overall_pct = $row['overall_rating'] ? round((float)$row['overall_rating'] * 20, 2) : 0;
                $row['overall_pct']  = $overall_pct;
                $row['grade']        = $row['review_id'] ? pctToGrade($overall_pct) : '';

                if ($row['review_id']) {
                    $row['items'] = fetchReviewItems($pdo, (int)$row['review_id']);
                } else {
                    $row['items'] = [];
                }
            }
            unset($row);

            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred: ' . $e->getMessage()]);
        }
        break;

    // ── Active Employees & Job Titles Dropdown ──────────────────────────────
    case 'fetch_employees':
        try {
            $stmt = $pdo->query("SELECT id, first_name, middle_name, last_name, profile_pic, job_title, job_description FROM employees WHERE deleted_at IS NULL AND status = 'Active' ORDER BY first_name ASC");
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $jtStmt = $pdo->query("
                SELECT DISTINCT job_title FROM (
                    SELECT job_title FROM employees WHERE deleted_at IS NULL AND job_title IS NOT NULL AND job_title != ''
                    UNION
                    SELECT title AS job_title FROM jobs WHERE status = 'Active'
                    UNION
                    SELECT job_title FROM kpi_template_items WHERE job_title IS NOT NULL AND job_title != ''
                ) t ORDER BY job_title ASC
            ");
            $jobTitles = $jtStmt->fetchAll(PDO::FETCH_COLUMN);

            echo json_encode(['status' => 'success', 'data' => $employees, 'job_titles' => $jobTitles]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred.']);
        }
        break;

    // ── Auto-calculate attendance score ───────────────────────────────────────
    case 'calc_attendance':
        try {
            $empId     = (int)($_GET['employee_id'] ?? 0);
            $yearMonth = $_GET['month'] ?? date('Y-m');
            $weight    = (float)($_GET['target_weight'] ?? 15.00);

            if (!$empId) {
                echo json_encode(['status' => 'error', 'message' => 'employee_id required.']);
                break;
            }

            $result = calculateAttendanceScore($pdo, $empId, $yearMonth, $weight);
            echo json_encode(['status' => 'success', 'data' => $result]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred.']);
        }
        break;

    // ── Fetch Criteria Template for Job Title or Employee ────────────────────
    case 'fetch_template':
        try {
            $jobTitle   = trim($_GET['job_title'] ?? $_POST['job_title'] ?? '');
            $employeeId = (int)($_GET['employee_id'] ?? $_POST['employee_id'] ?? 0);

            if ($employeeId > 0 && empty($jobTitle)) {
                $eStmt = $pdo->prepare("SELECT job_title FROM employees WHERE id = ?");
                $eStmt->execute([$employeeId]);
                $jobTitle = (string)$eStmt->fetchColumn();
            }

            // Check if specific employee template exists first
            $items = [];
            if ($employeeId > 0) {
                $stmt = $pdo->prepare("SELECT * FROM kpi_template_items WHERE employee_id = ? ORDER BY sort_order ASC, id ASC");
                $stmt->execute([$employeeId]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // If no employee-specific template, search by job_title case-insensitively
            if (empty($items) && !empty($jobTitle)) {
                $stmt = $pdo->prepare("SELECT * FROM kpi_template_items WHERE UPPER(job_title) = UPPER(?) AND employee_id IS NULL ORDER BY sort_order ASC, id ASC");
                $stmt->execute([$jobTitle]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode(['status' => 'success', 'job_title' => $jobTitle, 'data' => $items]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred: ' . $e->getMessage()]);
        }
        break;

    // ── Save Criteria Template for Job Title or Employee ─────────────────────
    case 'save_template':
        try {
            $pdo->beginTransaction();

            $jobTitle   = trim($_POST['job_title'] ?? '');
            $employeeId = !empty($_POST['employee_id']) ? (int)$_POST['employee_id'] : null;
            $rawItems   = $_POST['items'] ?? [];

            if (is_string($rawItems)) {
                $rawItems = json_decode($rawItems, true) ?: [];
            }

            if (empty($jobTitle) && empty($employeeId)) {
                echo json_encode(['status' => 'error', 'message' => 'Job title or Employee ID is required.']);
                exit;
            }

            // Clear previous template items for this scope
            if ($employeeId) {
                $del = $pdo->prepare("DELETE FROM kpi_template_items WHERE employee_id = ?");
                $del->execute([$employeeId]);
            } else {
                $del = $pdo->prepare("DELETE FROM kpi_template_items WHERE job_title = ? AND employee_id IS NULL");
                $del->execute([$jobTitle]);
            }

            $ins = $pdo->prepare("INSERT INTO kpi_template_items (job_title, employee_id, category, item_name, target_weight, evaluation_criteria, is_auto_attendance, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            $sortOrder = 1;
            foreach ($rawItems as $item) {
                $cat        = trim($item['category'] ?? 'Performance');
                $name       = trim($item['item_name'] ?? '');
                $weight     = max(0, (float)($item['target_weight'] ?? 0));
                $criteria   = trim($item['evaluation_criteria'] ?? '');
                $isAutoAtt  = !empty($item['is_auto_attendance']) ? 1 : 0;

                if (!empty($name)) {
                    $ins->execute([$jobTitle ?: null, $employeeId, $cat, $name, $weight, $criteria, $isAutoAtt, $sortOrder++]);
                }
            }

            $pdo->commit();
            logActivity($_SESSION['user_id'], 'KPI Template Saved', 'KPI Management', "Saved KPI template items for " . ($jobTitle ?: "Employee #$employeeId"));
            echo json_encode(['status' => 'success', 'message' => 'Template saved successfully!']);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Error saving template: ' . $e->getMessage()]);
        }
        break;

    // ── Full scorecard data for report page ──────────────────────────────────
    case 'fetch_report_data':
        try {
            $empId = $_GET['id'] ?? 0;

            $empStmt = $pdo->prepare("
                SELECT e.id, e.first_name, e.middle_name, e.last_name, e.profile_pic,
                       e.job_title, e.job_description, d.name as dept_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE e.id = ? AND e.deleted_at IS NULL
            ");
            $empStmt->execute([$empId]);
            $employee = $empStmt->fetch(PDO::FETCH_ASSOC);

            if (!$employee) {
                echo json_encode(['status' => 'error', 'message' => 'Employee not found.']);
                exit;
            }

            $historyStmt = $pdo->prepare("
                SELECT r.id, r.review_date, r.period, r.period_month, r.overall_rating, r.status, r.feedback,
                       rv.first_name as reviewer_first, rv.middle_name as reviewer_middle, rv.last_name as reviewer_last
                FROM kpi_reviews r
                JOIN employees rv ON r.reviewer_id = rv.id
                WHERE r.employee_id = ?
                ORDER BY r.period_month DESC, r.review_date DESC
            ");
            $historyStmt->execute([$empId]);
            $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($history as &$review) {
                $overall_pct = $review['overall_rating'] ? round((float)$review['overall_rating'] * 20, 2) : 0;
                $review['overall_pct'] = $overall_pct;
                $review['grade']       = pctToGrade($overall_pct);
                $review['items']       = fetchReviewItems($pdo, (int)$review['id']);
            }
            unset($review);

            echo json_encode([
                'status'   => 'success',
                'employee' => $employee,
                'history'  => $history,
            ]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred: ' . $e->getMessage()]);
        }
        break;

    // ── Add or update a review ────────────────────────────────────────────────
    case 'add_review':
        try {
            $pdo->beginTransaction();

            $employee_id  = (int)$_POST['employee_id'];
            $review_id    = !empty($_POST['review_id']) ? (int)$_POST['review_id'] : null;
            $period       = $_POST['period'] ?? 'Monthly';
            $period_month = $_POST['period_month'] ?? date('Y-m');
            $feedback     = trim($_POST['feedback'] ?? '');
            $reviewer_id  = $_SESSION['user_id'];
            $review_date  = date('Y-m-d');
            $rawItems     = $_POST['items'] ?? [];

            if (is_string($rawItems)) {
                $rawItems = json_decode($rawItems, true) ?: [];
            }

            $empCheck = $pdo->prepare("SELECT id, job_title FROM employees WHERE id = ? AND deleted_at IS NULL AND status = 'Active' LIMIT 1");
            $empCheck->execute([$employee_id]);
            $empInfo = $empCheck->fetch(PDO::FETCH_ASSOC);

            if (!$empInfo) {
                echo json_encode(['status' => 'error', 'message' => 'Only active employees can receive KPI reviews.']);
                exit;
            }

            // Calculate total achieved score from items
            $totalScore = 0.0;
            foreach ($rawItems as $item) {
                $totalScore += (float)($item['achieved_score'] ?? 0);
            }

            $overall_pct    = round(min(100, max(0, $totalScore)), 2);
            $overall_rating = round($overall_pct / 20, 2);
            $status         = pctToStatus($overall_pct);

            if ($review_id) {
                $pdo->prepare("UPDATE kpi_reviews SET period = ?, period_month = ?, overall_rating = ?, status = ?, feedback = ?, updated_at = NOW() WHERE id = ?")
                    ->execute([$period, $period_month, $overall_rating, $status, $feedback, $review_id]);
                $pdo->prepare("DELETE FROM kpi_review_items WHERE review_id = ?")->execute([$review_id]);
            } else {
                $pdo->prepare("INSERT INTO kpi_reviews (employee_id, reviewer_id, period, period_month, review_date, overall_rating, status, feedback, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())")
                    ->execute([$employee_id, $reviewer_id, $period, $period_month, $review_date, $overall_rating, $status, $feedback]);
                $review_id = (int)$pdo->lastInsertId();
            }

            // Save review item snapshots
            $insSnapshot = $pdo->prepare("INSERT INTO kpi_review_items (review_id, category, item_name, target_weight, evaluation_criteria, achieved_ratio, achieved_score, comment, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $sortOrder = 1;

            foreach ($rawItems as $item) {
                $cat         = trim($item['category'] ?? 'Performance');
                $name        = trim($item['item_name'] ?? 'Criteria');
                $weight      = (float)($item['target_weight'] ?? 10);
                $criteria    = trim($item['evaluation_criteria'] ?? '');
                $achScore    = min($weight, max(0, (float)($item['achieved_score'] ?? 0)));
                $ratio       = $weight > 0 ? round($achScore / $weight, 4) : 1.0;
                $comment     = trim($item['comment'] ?? '');

                $insSnapshot->execute([$review_id, $cat, $name, $weight, $criteria, $ratio, $achScore, $comment, $sortOrder++]);
            }

            $pdo->commit();

            // Activity log
            $e_name = trim(($empInfo['first_name'] ?? '') . ' ' . ($empInfo['last_name'] ?? ''));
            $logMsg = "Submitted KPI review for $e_name — Period: $period ($period_month) | Score: {$overall_pct}% ({$status})";
            logActivity($_SESSION['user_id'], 'KPI Review Submitted', 'KPI Management', $logMsg);

            \App\Helpers\WebSocketHelper::broadcast('kpi_updated');
            echo json_encode(['status' => 'success', 'message' => 'Review saved successfully!', 'review_id' => $review_id]);

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred: ' . $e->getMessage()]);
        }
        break;

    // ── Delete a review ───────────────────────────────────────────────────────
    case 'delete_review':
        try {
            $id = (int)$_POST['id'];
            $e_stmt = $pdo->prepare("SELECT e.first_name, e.last_name FROM kpi_reviews r JOIN employees e ON r.employee_id = e.id WHERE r.id = ?");
            $e_stmt->execute([$id]);
            $e_data = $e_stmt->fetch(PDO::FETCH_ASSOC);
            $e_name = ($e_data['first_name'] ?? 'Unknown') . ' ' . ($e_data['last_name'] ?? '');

            $pdo->prepare("DELETE FROM kpi_reviews WHERE id = ?")->execute([$id]);
            logActivity($_SESSION['user_id'], 'Deleted KPI Review', 'KPI Management', "Removed performance review for $e_name");
            \App\Helpers\WebSocketHelper::broadcast('kpi_updated');
            echo json_encode(['status' => 'success', 'message' => 'Review deleted successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred.']);
        }
        break;

    // ── Get a single review for editing ──────────────────────────────────────
    case 'fetch_review_details':
        try {
            $id = (int)($_GET['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM kpi_reviews WHERE id = ?");
            $stmt->execute([$id]);
            $review = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($review) {
                $review['items']       = fetchReviewItems($pdo, $id);
                $review['overall_pct'] = round((float)$review['overall_rating'] * 20, 2);
                $review['grade']       = pctToGrade($review['overall_pct']);
                echo json_encode(['status' => 'success', 'data' => $review]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Review not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
}
