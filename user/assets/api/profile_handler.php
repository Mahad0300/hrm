<?php
// user/assets/api/profile_handler.php
require_once '../../../includes/db_connect.php';
require_once '../../../includes/auth_helper.php';
require_once '../../../includes/api/activity_helper.php';

header('Content-Type: application/json');

// Session & Role Check
if (!isLoggedIn() || $_SESSION['user_role'] !== 'Employee') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

/**
 * Validation Helpers
 */
function uploadFile($file, $targetDir) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = 'user_' . $_SESSION['user_id'] . '_' . uniqid() . '.' . $ext;
    $targetPath = '../../../' . $targetDir . $newName;
    
    if (!is_dir('../../../' . $targetDir)) mkdir('../../../' . $targetDir, 0777, true);
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetDir . $newName;
    }
    return null;
}

switch ($action) {
    case 'fetch':
        try {
            $stmt = $pdo->prepare("
                SELECT e.*, d.name as dept_name, s.name as shift_name, s.start_time, s.end_time,
                       b.bank_name, b.account_type, b.account_title, b.account_number, b.branch_info,
                       ex.qualification, ex.degree_cert, ex.university, ex.expertise, 
                       ex.last_employer, ex.last_job_title, ex.exp_from, ex.exp_to
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN shifts s ON e.shift_id = s.id
                LEFT JOIN banking_info b ON e.id = b.employee_id
                LEFT JOIN education_experience ex ON e.id = ex.employee_id
                WHERE e.id = ? AND e.deleted_at IS NULL
            ");
            $stmt->execute([$user_id]);
            $employee = $stmt->fetch();

            if ($employee) {
                // Fetch leave summary
                $leave_stmt = $pdo->prepare("
                    SELECT lt.name, lt.days_per_year, 
                           COALESCE(SUM(CASE WHEN lr.status = 'Approved' THEN DATEDIFF(lr.end_date, lr.start_date) + 1 ELSE 0 END), 0) as used
                    FROM leave_types lt
                    LEFT JOIN leave_requests lr ON lt.id = lr.leave_type_id AND lr.employee_id = ?
                    GROUP BY lt.id, lt.name, lt.days_per_year
                ");
                $leave_stmt->execute([$user_id]);
                $employee['leave_summary'] = $leave_stmt->fetchAll();

                // Fetch salary history
                $salary_stmt = $pdo->prepare("
                    SELECT * FROM salary_history 
                    WHERE employee_id = ? 
                    ORDER BY change_date DESC, id DESC
                ");
                $salary_stmt->execute([$user_id]);
                $employee['salary_history'] = $salary_stmt->fetchAll();

                // Remove password for security
                unset($employee['password']);
                echo json_encode(['status' => 'success', 'data' => $employee]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Profile not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $pdo->beginTransaction();

                // 1. Update Basic Employee Info (Limited to specific fields for users)
                $f_name = trim($_POST['firstName'] ?? '');
                $m_name = trim($_POST['middleName'] ?? '');
                $l_name = trim($_POST['lastName'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $gender = $_POST['gender'] ?? '';
                $dob = $_POST['dob'] ?? null;
                $address = trim($_POST['address'] ?? '');
                $cnic = trim($_POST['idCard'] ?? '');
                $emergency_contact = trim($_POST['emergencyPhone'] ?? '');
                $emergency_relation = trim($_POST['emergencyRelation'] ?? '');

                if (empty($f_name) || empty($l_name)) {
                    throw new Exception("First and Last name are required.");
                }

                // Handle File Uploads
                $profile_pic_path = uploadFile($_FILES['profile_avatar'] ?? null, 'uploads/employees/profiles/');
                $resume_path = uploadFile($_FILES['resume_file'] ?? null, 'uploads/employees/resumes/');
                $id_card_path = uploadFile($_FILES['id_file'] ?? null, 'uploads/employees/id_cards/');

                // Handle Other Documents (Multiple)
                $new_other_docs = [];
                if (!empty($_FILES['other_files']['name'][0])) {
                    $files = $_FILES['other_files'];
                    foreach ($files['name'] as $key => $name) {
                        if ($files['error'][$key] === 0) {
                            $tmp_name = $files['tmp_name'][$key];
                            $ext = pathinfo($name, PATHINFO_EXTENSION);
                            $new_name = 'user_' . $user_id . '_doc_' . uniqid() . '.' . $ext;
                            $target_dir = '../../../uploads/employees/others/';
                            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                            
                            if (move_uploaded_file($tmp_name, $target_dir . $new_name)) {
                                $new_other_docs[] = 'uploads/employees/others/' . $new_name;
                            }
                        }
                    }
                }

                $query = "UPDATE employees SET 
                            first_name = ?, middle_name = ?, last_name = ?, phone = ?, 
                            gender = ?, dob = ?, address = ?, cnic_number = ?, 
                            emergency_contact = ?, emergency_relation = ?";
                $params = [$f_name, $m_name, $l_name, $phone, $gender, $dob, $address, $cnic, $emergency_contact, $emergency_relation];

                if ($profile_pic_path) {
                    $query .= ", profile_pic = ?";
                    $params[] = $profile_pic_path;
                    
                    // Cleanup old pic logic
                    $old_stmt = $pdo->prepare("SELECT profile_pic FROM employees WHERE id = ?");
                    $old_stmt->execute([$user_id]);
                    $old_pic = $old_stmt->fetchColumn();
                    if ($old_pic && file_exists('../../../' . $old_pic) && strpos($old_pic, 'default') === false) {
                        @unlink('../../../' . $old_pic);
                    }
                }
                if ($resume_path) {
                    $query .= ", resume_path = ?";
                    $params[] = $resume_path;
                }
                if ($id_card_path) {
                    $query .= ", id_card_path = ?";
                    $params[] = $id_card_path;
                }
                if (!empty($new_other_docs)) {
                    // Fetch existing docs to append
                    $e_stmt = $pdo->prepare("SELECT other_docs FROM employees WHERE id = ?");
                    $e_stmt->execute([$user_id]);
                    $existing_json = $e_stmt->fetchColumn();
                    $existing_docs = !empty($existing_json) ? json_decode($existing_json, true) : [];
                    $merged_docs = array_merge($existing_docs, $new_other_docs);
                    
                    $query .= ", other_docs = ?";
                    $params[] = json_encode($merged_docs);
                }

                $query .= " WHERE id = ?";
                $params[] = $user_id;

                $stmt = $pdo->prepare($query);
                $stmt->execute($params);

                // 2. Update Banking Info
                $b_stmt = $pdo->prepare("
                    INSERT INTO banking_info (employee_id, bank_name, account_type, account_title, account_number, branch_info)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    bank_name = VALUES(bank_name), account_type = VALUES(account_type), 
                    account_title = VALUES(account_title), account_number = VALUES(account_number), 
                    branch_info = VALUES(branch_info)
                ");
                $b_stmt->execute([
                    $user_id,
                    $_POST['bankName'] ?? '',
                    $_POST['accountType'] ?? '',
                    $_POST['accountTitle'] ?? '',
                    $_POST['accountNumber'] ?? '',
                    $_POST['bankBranch'] ?? ''
                ]);

                // 3. Update Education & Experience
                $e_stmt = $pdo->prepare("
                    INSERT INTO education_experience (employee_id, qualification, degree_cert, university, expertise, last_employer, last_job_title, exp_from, exp_to)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    qualification = VALUES(qualification), degree_cert = VALUES(degree_cert), 
                    university = VALUES(university), expertise = VALUES(expertise), 
                    last_employer = VALUES(last_employer), last_job_title = VALUES(last_job_title), 
                    exp_from = VALUES(exp_from), exp_to = VALUES(exp_to)
                ");
                $e_stmt->execute([
                    $user_id,
                    $_POST['qualification'] ?? '',
                    $_POST['degreeCert'] ?? '',
                    $_POST['college'] ?? '',
                    $_POST['expertise'] ?? '',
                    $_POST['lastEmployer'] ?? '',
                    $_POST['prevJobTitle'] ?? '',
                    $_POST['expFrom'] ?? null,
                    $_POST['expTo'] ?? null
                ]);

                $pdo->commit();

                // Fetch updated profile for the user to return
                $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
                $stmt->execute([$user_id]);
                $updated_user = $stmt->fetch();
                unset($updated_user['password']);

                // Update Session Name
                $middle_name_fmt = !empty($updated_user['middle_name']) ? $updated_user['middle_name'] . ' ' : '';
                $_SESSION['user_name'] = $updated_user['first_name'] . ' ' . $middle_name_fmt . $updated_user['last_name'];

                // [LOG]
                $logMsg = $profile_pic_path ? "Updated their personal profile details and changed their profile picture." : "Updated their personal profile information.";
                logActivity($user_id, "Updated Personal Profile", "Employees", $logMsg);

                echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully!', 'profile_pic' => $profile_pic_path, 'data' => $updated_user]);

            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}
