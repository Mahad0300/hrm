<?php
// admin/assets/api/job_handler.php
require_once dirname(__DIR__, 3) . '/includes/db_connect.php';
require_once dirname(__DIR__, 3) . '/includes/api/notification_handler.php';
require_once dirname(__DIR__, 3) . '/includes/api/activity_helper.php';
require_once dirname(__DIR__, 3) . '/includes/api/rate_limiter.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Rate limit public-facing endpoints
if ($action === 'submit_application' && !checkRateLimit('submit_application', 5, 60)) { rateLimitExceeded(); }

// Public actions that don't require authentication (used by job-apply.php)
$public_actions = ['submit_application', 'fetch_jobs', 'fetch_job_detail', 'fetch_interviews'];

// Auth Check — Block all non-public actions for unauthenticated users
if (!in_array($action, $public_actions)) {
    if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['Admin', 'HR'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
        exit;
    }
}

$user_id = $_SESSION['user_id'] ?? 0;

function createJobSlug($title) {
    $slug = strtolower((string) $title);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}


try {
    switch ($action) {
        case 'save_job':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Invalid request method.");

            $pdo->beginTransaction();

            $id = $_POST['id'] ?? null;
            $title = $_POST['title'] ?? '';
            $dept_id = $_POST['department_id'] ?? null;
            $location = $_POST['location'] ?? '';
            $desc = $_POST['description'] ?? '';
            $type = $_POST['type'] ?? 'Full-time';
            $status = $_POST['status'] ?? 'Active';
            
            // Decode questions from JSON string
            $questions = isset($_POST['questions']) ? json_decode($_POST['questions'], true) : [];
            // Handle both FormData and potential JSON input
            if (empty($questions) && isset($_POST['questions_json'])) {
                $questions = json_decode($_POST['questions_json'], true);
            }

            if ($id) {
                // Update existing job
                $stmt = $pdo->prepare("UPDATE jobs SET title = ?, department_id = ?, location = ?, description = ?, type = ?, status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$title, $dept_id, $location, $desc, $type, $status, $id]);
                
                // Remove old questions first
                $pdo->prepare("DELETE FROM job_questions WHERE job_id = ?")->execute([$id]);
            } else {
                // Insert new job
                $stmt = $pdo->prepare("INSERT INTO jobs (title, department_id, location, description, type, status, posted_date, created_at) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), NOW())");
                $stmt->execute([$title, $dept_id, $location, $desc, $type, $status]);
                $id = $pdo->lastInsertId();
            }

            // Insert questions
            if (!empty($questions)) {
                $qStmt = $pdo->prepare("INSERT INTO job_questions (job_id, question_text, answer_type, is_required) VALUES (?, ?, ?, ?)");
                foreach ($questions as $q) {
                    $qStmt->execute([
                        $id,
                        $q['text'] ?? $q['question_text'],
                        $q['type'] ?? $q['answer_type'] ?? 'TEXT INPUT',
                        isset($q['required']) ? ($q['required'] ? 1 : 0) : 1
                    ]);
                }
            }

            $pdo->commit();

            // [LOG]
            $logAction = $id ? "Updated Job Opening" : "Created Job Opening";
            $logMsg = $id ? "Modified the requirements and details for position: '$title'" : "Published a new career opportunity: '$title'";
            logActivity($user_id, $logAction, "Job Management", $logMsg);

            echo json_encode(['status' => 'success', 'message' => 'Job saved successfully', 'job_id' => $id]);
            break;

        case 'fetch_jobs':
            $stmt = $pdo->query("
                SELECT j.*, d.name as department_name, 
                (SELECT COUNT(*) FROM candidates WHERE job_id = j.id AND deleted_at IS NULL) as applicant_count
                FROM jobs j
                LEFT JOIN departments d ON j.department_id = d.id
                WHERE j.deleted_at IS NULL
                ORDER BY j.created_at DESC
            ");
            $jobs = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $jobs]);
            break;

        case 'fetch_interviews':
            $stmt = $pdo->query("
                SELECT i.id as id, DATE(i.interview_date) as date, TIME_FORMAT(i.interview_date, '%H:%i') as time, 
                       c.name as name, 
                       j.title as job, c.id as candidate_id, i.feedback
                FROM interviews i
                JOIN candidates c ON i.candidate_id = c.id
                JOIN jobs j ON c.job_id = j.id
                ORDER BY i.interview_date ASC
            ");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'fetch_job_detail':
            $id = $_GET['id'] ?? '';
            $slug = createJobSlug(trim($_GET['slug'] ?? ($_GET['job'] ?? '')));
            $job = false;

            if ($id !== '') {
                $stmt = $pdo->prepare("SELECT j.*, d.name as department_name, (SELECT COUNT(*) FROM candidates WHERE job_id = j.id AND deleted_at IS NULL) as applicant_count FROM jobs j LEFT JOIN departments d ON j.department_id = d.id WHERE j.id = ? AND j.deleted_at IS NULL");
                $stmt->execute([$id]);
                $job = $stmt->fetch();
            } elseif ($slug !== '') {
                $stmt = $pdo->query("SELECT j.*, d.name as department_name, (SELECT COUNT(*) FROM candidates WHERE job_id = j.id AND deleted_at IS NULL) as applicant_count FROM jobs j LEFT JOIN departments d ON j.department_id = d.id WHERE j.deleted_at IS NULL ORDER BY j.created_at DESC");
                foreach ($stmt->fetchAll() as $candidateJob) {
                    if (createJobSlug($candidateJob['title']) === $slug) {
                        $job = $candidateJob;
                        $id = $candidateJob['id'];
                        break;
                    }
                }
            }

            if ($job) {
                $qStmt = $pdo->prepare("SELECT * FROM job_questions WHERE job_id = ?");
                $qStmt->execute([$id]);
                $job['questions'] = $qStmt->fetchAll();
                echo json_encode(['status' => 'success', 'data' => $job]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Job not found']);
            }
            break;

        case 'delete_job':
            $jobId = $_POST['id'] ?? $_GET['id'] ?? null;
            if (!$jobId) throw new Exception("Job ID missing.");

            // Fetch title for logging before delete
            $t_stmt = $pdo->prepare("SELECT title FROM jobs WHERE id = ?");
            $t_stmt->execute([$jobId]);
            $j_title = $t_stmt->fetchColumn() ?: "Unknown Job";

            $stmt = $pdo->prepare("UPDATE jobs SET deleted_at = NOW(), status = 'Archived' WHERE id = ?");
            $stmt->execute([$jobId]);

            // [LOG]
            logActivity($user_id, "Deleted Job Listing", "Job Management", "Closed and removed the recruitment listing for: '$j_title'");

            echo json_encode(['status' => 'success', 'message' => 'Job deleted successfully']);
            break;

        case 'fetch_candidate_detail':
            $cand_id = $_GET['id'] ?? '';
            $stmt = $pdo->prepare("
                SELECT c.id, c.name, c.email, c.phone, c.cnic_number, c.address, c.applied_date, c.status, 
                       c.resume_path, c.duplicate_of, c.duplicate_reason, c.created_at,
                       j.title as job_title,
                       orig.name as duplicate_of_name
                FROM candidates c
                LEFT JOIN jobs j ON c.job_id = j.id
                LEFT JOIN candidates orig ON c.duplicate_of = orig.id
                WHERE c.id = ? AND c.deleted_at IS NULL
            ");
            $stmt->execute([$cand_id]);
            $candidate = $stmt->fetch();

            if ($candidate) {
                // Fetch Answers
                $aStmt = $pdo->prepare("SELECT question_text, answer FROM candidate_answers WHERE candidate_id = ?");
                $aStmt->execute([$cand_id]);
                $candidate['answers'] = $aStmt->fetchAll();

                // Fetch History (Including Interviews)
                // We combine candidate_history and interviews for the timeline
                $hStmt = $pdo->prepare("
                    SELECT 'status_change' as type, status_from, status_to, feedback, e.first_name, e.last_name, ch.created_at 
                    FROM candidate_history ch 
                    LEFT JOIN employees e ON ch.created_by = e.id 
                    WHERE ch.candidate_id = ?
                    ORDER BY ch.created_at DESC
                ");
                $hStmt->execute([$cand_id]);
                $candidate['history'] = $hStmt->fetchAll();

                // Fetch current interview if any
                $iStmt = $pdo->prepare("SELECT id, DATE(interview_date) as date, TIME_FORMAT(interview_date, '%H:%i') as time, feedback FROM interviews WHERE candidate_id = ? ORDER BY created_at DESC LIMIT 1");
                $iStmt->execute([$cand_id]);
                $candidate['current_interview'] = $iStmt->fetch();

                echo json_encode(['status' => 'success', 'data' => $candidate]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Candidate not found']);
            }
            break;

        case 'fetch_candidates':
            $stmt = $pdo->query("
                SELECT c.*, j.title as job_title 
                FROM candidates c
                LEFT JOIN jobs j ON c.job_id = j.id
                WHERE c.deleted_at IS NULL
                ORDER BY c.created_at DESC
            ");
            $candidates = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $candidates]);
            break;

        case 'submit_application':
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $cnic = $_POST['cnic_number'] ?? '';
            $job_id = $_POST['job_id'] ?? '';
            $answers = isset($_POST['answers']) ? json_decode($_POST['answers'], true) : [];

            try {
                $pdo->beginTransaction();

                // Duplicate Check
                $duplicate_of = null;
                $duplicate_reason = null;
                $status = 'New';

                $checkStmt = $pdo->prepare("
                    SELECT id, name, phone, email, cnic_number, address FROM candidates 
                    WHERE ((phone = ? AND ? != '') 
                       OR (email = ? AND ? != '')
                       OR (cnic_number = ? AND ? != '')
                       OR (address = ? AND ? != ''))
                    AND deleted_at IS NULL 
                    LIMIT 1
                ");
                $checkStmt->execute([$phone, $phone, $email, $email, $cnic, $cnic, $address, $address]);
                $orig = $checkStmt->fetch();

                if ($orig) {
                    $status = 'Duplicated';
                    $duplicate_of = $orig['id'];
                    
                    $matched_fields = [];
                    if ($phone && $phone === $orig['phone']) $matched_fields[] = 'Phone';
                    if ($email && $email === $orig['email']) $matched_fields[] = 'Email';
                    if ($cnic && $cnic === $orig['cnic_number']) $matched_fields[] = 'CNIC';
                    
                    $reason_str = !empty($matched_fields) ? implode(', ', $matched_fields) : 'Information';
                    $duplicate_reason = "Matched " . $reason_str . " with " . $orig['name'];
                }

                // Handle Resume Upload
                $resume_path = null;
                if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../../../uploads/candidates/resumes/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    
                    $file_ext = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
                    $file_name = 'RES_' . uniqid() . '.' . $file_ext;
                    $target_file = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_file)) {
                        $resume_path = 'uploads/candidates/resumes/' . $file_name;
                    }
                }

                // Insert Candidate
                $stmt = $pdo->prepare("INSERT INTO candidates (name, email, phone, cnic_number, address, job_id, resume_path, applied_date, status, duplicate_of, duplicate_reason, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, NOW())");
                $stmt->execute([$name, $email, $phone, $cnic, $address, $job_id, $resume_path, $status, $duplicate_of, $duplicate_reason]);
                $candidate_id = $pdo->lastInsertId();

                // Insert Answers
                if (!empty($answers)) {
                    $qStmt = $pdo->prepare("INSERT INTO candidate_answers (candidate_id, question_text, answer) VALUES (?, ?, ?)");
                    foreach ($answers as $qText => $ans) {
                        $qStmt->execute([$candidate_id, $qText, $ans]);
                    }
                }

                $pdo->commit();

                // [TRIGGER] Notify Admin/HR
                $admin_stmt = $pdo->query("SELECT id FROM employees WHERE role IN ('Admin', 'HR') AND deleted_at IS NULL");
                $admin_ids = $admin_stmt->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($admin_ids)) {
                    $job_title_stmt = $pdo->prepare("SELECT title FROM jobs WHERE id = ?");
                    $job_title_stmt->execute([$job_id]);
                    $job_title = $job_title_stmt->fetchColumn() ?: "a job";
                    
                    $msg = "New application received from $name for position: $job_title.";
                    addNotification($admin_ids, "New Job Application", $msg, "job-candidates.php", "Recruitment");
                }

                echo json_encode(['status' => 'success', 'message' => 'Application submitted successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
            }
            break;

        case 'update_candidate_status':
            $id = $_POST['id'] ?? null;
            $status = $_POST['status'] ?? null;
            $feedback = $_POST['feedback'] ?? '';

            
            if (!$id || !$status) throw new Exception("ID and Status are required.");

            $pdo->beginTransaction();

            // Get current status for history
            $currStmt = $pdo->prepare("SELECT status FROM candidates WHERE id = ?");
            $currStmt->execute([$id]);
            $oldStatus = $currStmt->fetchColumn() ?: 'New';

            // Update candidate
            $stmt = $pdo->prepare("UPDATE candidates SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $id]);

            // Record in history if it's a real change or has feedback
            $hStmt = $pdo->prepare("INSERT INTO candidate_history (candidate_id, status_from, status_to, feedback, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $hStmt->execute([$id, $oldStatus, $status, $feedback, $user_id]);

            // [LOG]
            $c_stmt = $pdo->prepare("SELECT name FROM candidates WHERE id = ?");
            $c_stmt->execute([$id]);
            $c_name = $c_stmt->fetchColumn();
            logActivity($user_id, "Updated Candidate Status", "Job Management", "Updated status for candidate '$c_name' from '$oldStatus' to '$status'.");

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Status updated successfully']);
            break;

        case 'schedule_interview':
            $candidate_id = $_POST['candidate_id'] ?? null;
            $date = $_POST['date'] ?? null;
            $time = $_POST['time'] ?? null;
            $feedback = $_POST['feedback'] ?? '';


            if (!$candidate_id || !$date || !$time) throw new Exception("All fields are required.");

            $pdo->beginTransaction();
            
            // Combine date and time for DATETIME column
            $interview_datetime = $date . ' ' . $time;
            $stmt = $pdo->prepare("INSERT INTO interviews (candidate_id, interview_date, feedback, status, created_at) VALUES (?, ?, ?, 'Scheduled', NOW())");
            $stmt->execute([$candidate_id, $interview_datetime, $feedback]);
            
            // Update candidate status & Record History
            $upStmt = $pdo->prepare("UPDATE candidates SET status = 'Interview', updated_at = NOW() WHERE id = ?");
            $upStmt->execute([$candidate_id]);

            // Format for human readable history
            $dt = new DateTime($interview_datetime);
            $formattedTime = $dt->format('F j-Y, g:i A');

            $hStmt = $pdo->prepare("INSERT INTO candidate_history (candidate_id, status_from, status_to, feedback, created_by, created_at) VALUES (?, 'New', 'Interview', ?, ?, NOW())");
            $hStmt->execute([$candidate_id, "Interview scheduled for $formattedTime. $feedback", $user_id]);

            // [LOG]
            $c_stmt = $pdo->prepare("SELECT name FROM candidates WHERE id = ?");
            $c_stmt->execute([$candidate_id]);
            $c_name = $c_stmt->fetchColumn();
            logActivity($user_id, "Scheduled Interview", "Job Management", "Scheduled an interview session for candidate '$c_name' on " . date('M d, Y', strtotime($date)) . " at $time.");

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Interview scheduled successfully']);
            break;

        case 'reschedule_interview':
            $interview_id = $_POST['interview_id'] ?? null;
            $candidate_id = $_POST['candidate_id'] ?? null;
            $date = $_POST['date'] ?? null;
            $time = $_POST['time'] ?? null;
            $feedback = $_POST['feedback'] ?? '';


            if (!$interview_id || !$date || !$time) throw new Exception("Interview ID, Date and Time are required.");

            $pdo->beginTransaction();
            
            // Get original time for history log
            $origStmt = $pdo->prepare("SELECT interview_date, candidate_id FROM interviews WHERE id = ?");
            $origStmt->execute([$interview_id]);
            $origInfo = $origStmt->fetch();
            $origTimeStr = $origInfo ? $origInfo['interview_date'] : 'Unknown';
            $target_candidate_id = $candidate_id ?: ($origInfo ? $origInfo['candidate_id'] : null);

            // Update Interview
            $interview_datetime = $date . ' ' . $time;
            $stmt = $pdo->prepare("UPDATE interviews SET interview_date = ?, feedback = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$interview_datetime, $feedback, $interview_id]);
            
            // Record History
            if ($target_candidate_id) {
                // Formatting for human readable history
                $prevDT = new DateTime($origTimeStr);
                $newDT = new DateTime($interview_datetime);
                
                $prevFormatted = $prevDT->format('F j-Y, g:i A');
                $newFormatted = $newDT->format('F j-Y, g:i A');

                $hStmt = $pdo->prepare("INSERT INTO candidate_history (candidate_id, status_from, status_to, feedback, created_by, created_at) VALUES (?, 'Interview', 'Interview', ?, ?, NOW())");
                $history_msg = "Interview rescheduled. Previous: $prevFormatted. New: $newFormatted. Notes: $feedback";
                $hStmt->execute([$target_candidate_id, $history_msg, $user_id]);

                // [LOG]
                $c_stmt = $pdo->prepare("SELECT name FROM candidates WHERE id = ?");
                $c_stmt->execute([$target_candidate_id]);
                $c_name = $c_stmt->fetchColumn();
                logActivity($user_id, "Rescheduled Interview", "Job Management", "Rescheduled the interview session for '$c_name' to " . date('M d, Y', strtotime($date)) . " at $time.");
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Interview rescheduled successfully']);
            break;

        case 'toggle_job_status':
            $id = $_POST['id'] ?? null;
            $status = $_POST['status'] ?? null;

            if (!$id || !$status) throw new Exception("ID and Status are required.");

            $stmt = $pdo->prepare("UPDATE jobs SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $id]);

            // [LOG]
            $t_stmt = $pdo->prepare("SELECT title FROM jobs WHERE id = ?");
            $t_stmt->execute([$id]);
            $j_title = $t_stmt->fetchColumn() ?: "Job";
            logActivity($user_id, "Updated Job Status", "Job Management", "Changed status for '$j_title' to '$status'.");

            echo json_encode(['status' => 'success', 'message' => 'Job status updated to ' . $status]);
            break;

        default:
            throw new Exception("Invalid action.");
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
}
