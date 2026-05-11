<?php
// admin/assets/api/employee_handler.php
require_once '../../../includes/db_connect.php';
require_once '../../../includes/auth_helper.php';
require_once '../../../includes/api/activity_helper.php';

header('Content-Type: application/json');

// Session & Role Check (Optional: Allow Onboarding without full session if needed)
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Standard Auth check for Admin actions
if ($action !== 'onboard' && $action !== 'check_email' && (!isLoggedIn() || !in_array($_SESSION['user_role'], ['Admin', 'HR']))) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

/**
 * Validation Helpers
 */
function validatePhone($phone) {
    // Strip hyphens or any non-digit characters for validation
    $cleanPhone = preg_replace('/\D/', '', $phone);
    return preg_match('/^[0-9]{11}$/', $cleanPhone);
}

function uploadFile($file, $targetDir) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = uniqid('EMP_') . '.' . $ext;
    $targetPath = '../../../' . $targetDir . $newName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetDir . $newName;
    }
    return null;
}

switch ($action) {
        case 'fetch_pending':
        try {
            $stmt = $pdo->query("SELECT id, first_name, middle_name, last_name, email, phone, job_title, department_id, created_at FROM employees WHERE status = 'Pending' AND deleted_at IS NULL ORDER BY created_at DESC");
            $employees = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $employees]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_requirements':
        try {
            $depts = $pdo->query("SELECT id, name FROM departments WHERE deleted_at IS NULL")->fetchAll();
            $shifts = $pdo->query("SELECT id, name, start_time, end_time FROM shifts WHERE deleted_at IS NULL")->fetchAll();
            echo json_encode([
                'status' => 'success',
                'departments' => $depts,
                'shifts' => $shifts
            ]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_directory':
        try {
            $id_search = trim($_GET['id_search'] ?? '');
            $name_search = trim($_GET['name_search'] ?? '');
            $dept = $_GET['department'] ?? '';
            $role = $_GET['role'] ?? '';
            $status = $_GET['status'] ?? '';
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 10);
            $offset = ($page - 1) * $limit;

            // If they are searching for Exit employees, we allow e.deleted_at to be NOT NULL
            $where = [];
            if ($status === 'Exit') {
                $where[] = "e.deleted_at IS NOT NULL";
            } else {
                $where[] = "e.deleted_at IS NULL";
            }
            $params = [];

            // Apply specific role/status logic
            if (empty($role) && empty($status) && empty($id_search) && empty($name_search) && empty($dept)) {
                $where[] = "e.role IN ('Employee', 'HR')";
                $where[] = "e.status IN ('Active', 'On Leave')";
            } else {
                if (!empty($id_search)) {
                    $where[] = "e.id LIKE ?";
                    $params[] = "%$id_search%";
                }
                if (!empty($name_search)) {
                    $where[] = "(e.first_name LIKE ? OR e.last_name LIKE ? OR e.middle_name LIKE ?)";
                    $params[] = "%$name_search%";
                    $params[] = "%$name_search%";
                    $params[] = "%$name_search%";
                }
                if (!empty($dept)) {
                    $where[] = "e.department_id = ?";
                    $params[] = $dept;
                }
                if (!empty($role)) {
                    $where[] = "e.role = ?";
                    $params[] = $role;
                }
                if (!empty($status)) {
                    $where[] = "e.status = ?";
                    $params[] = $status;
                }
            }

            $whereSql = implode(" AND ", $where);
            
            // Total count for pagination
            $countSql = "SELECT COUNT(*) FROM employees e WHERE $whereSql";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $totalEntries = $countStmt->fetchColumn();

            // Fetch data
            $sql = "SELECT e.*, d.name as dept_name 
                    FROM employees e 
                    LEFT JOIN departments d ON e.department_id = d.id 
                    WHERE $whereSql 
                    ORDER BY e.created_at DESC 
                    LIMIT $limit OFFSET $offset";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $employees = $stmt->fetchAll();

            echo json_encode([
                'status' => 'success',
                'data' => $employees,
                'total' => $totalEntries,
                'page' => $page,
                'limit' => $limit
            ]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'check_email':
        $email = trim($_GET['email'] ?? '');
        if (empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Email required.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM employees WHERE email = ? AND deleted_at IS NULL");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'This email is already registered.']);
            } else {
                echo json_encode(['status' => 'success', 'message' => 'Email is available.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error.']);
        }
        break;

    case 'add':
    case 'onboard':
    case 'hire_candidate':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $pdo->beginTransaction();

                // 1. Basic Employee Info
                $f_name = trim($_POST['first_name'] ?? '');
                $l_name = trim($_POST['last_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $gender = $_POST['gender'] ?? '';
                $dept_id = $_POST['department_id'] ?? null;
                $shift_id = $_POST['shift_id'] ?? null;
                $candidate_id = $_POST['candidate_id'] ?? null;
                $job_title = trim($_POST['job_title'] ?? '');

                // Enterprise Validations (Clean non-digits for validation)
                if (empty($f_name) || empty($l_name) || empty($email)) {
                    throw new Exception("First Name, Last Name, and Email are mandatory.");
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email format.");
                }

                // Check for duplicate email
                $emailCheck = $pdo->prepare("SELECT id FROM employees WHERE email = ? AND deleted_at IS NULL");
                $emailCheck->execute([$email]);
                if ($emailCheck->rowCount() > 0) {
                    throw new Exception("This email is already registered. Please use a different email or contact HR.");
                }

                // Clean and Validate Phone
                $clean_phone = preg_replace('/\D/', '', $phone);
                if (!empty($phone) && !validatePhone($clean_phone)) {
                    throw new Exception("Phone number must be exactly 11 digits.");
                }

                // File Uploads
                $id_card_path = uploadFile($_FILES['id_card'] ?? null, 'uploads/employees/id_cards/');
                $resume_path = uploadFile($_FILES['resume'] ?? null, 'uploads/employees/resumes/');

                // Other Documents (Multi-upload)
                $other_docs_paths = [];
                if (!empty($_FILES['other_documents']['name'][0])) {
                    $files = $_FILES['other_documents'];
                    foreach ($files['name'] as $key => $name) {
                        if ($files['error'][$key] === 0) {
                            $tmp_name = $files['tmp_name'][$key];
                            $ext = pathinfo($name, PATHINFO_EXTENSION);
                            $new_name = uniqid('doc_') . '.' . $ext;
                            $target_dir = '../../../uploads/employees/others/';
                            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                            
                            if (move_uploaded_file($tmp_name, $target_dir . $new_name)) {
                                $other_docs_paths[] = 'uploads/employees/others/' . $new_name;
                            }
                        }
                    }
                }
                $other_docs_json = !empty($other_docs_paths) ? json_encode($other_docs_paths) : null;

                if ($action === 'onboard' && !$id_card_path) {
                    throw new Exception("ID Card Attachment is required for onboarding.");
                }

                // Temporary Password for new employees
                $password = ($action === 'onboard') ? '' : password_hash('Emp123@#', PASSWORD_DEFAULT);

                // Determine initial status
                $initialStatus = 'Pending';
                if ($action === 'add' || $action === 'hire_candidate') {
                    $initialStatus = 'Active';
                }

                $stmt = $pdo->prepare("INSERT INTO employees (
                    first_name, middle_name, last_name, email, password, phone, gender, 
                    dob, cnic_number, address, emergency_contact, emergency_relation,
                    department_id, shift_id, job_title, salary, joining_date, status, id_card_path, resume_path, other_docs
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $f_name, 
                    $_POST['middle_name'] ?? '',
                    $l_name, 
                    $email, 
                    $password, 
                    $phone, 
                    $gender,
                    $_POST['dob'] ?? null,
                    preg_replace('/\D/', '', $_POST['cnic_number'] ?? ''), 
                    $_POST['address'] ?? '',
                    preg_replace('/\D/', '', $_POST['emergency_contact'] ?? ''), 
                    $_POST['emergency_relation'] ?? '',
                    $dept_id, 
                    $shift_id,
                    $job_title,
                    $_POST['salary'] ?? 0,
                    !empty($_POST['joining_date']) ? $_POST['joining_date'] : null,
                    $initialStatus, 
                    $id_card_path, 
                    $resume_path,
                    $other_docs_json
                ]);
                $employee_id = $pdo->lastInsertId();

                // 2. Banking Info (If provided)
                if (!empty($_POST['bank_name'])) {
                    $b_stmt = $pdo->prepare("INSERT INTO banking_info (employee_id, bank_name, account_type, account_title, account_number, branch_info) VALUES (?, ?, ?, ?, ?, ?)");
                    $b_stmt->execute([
                        $employee_id,
                        $_POST['bank_name'],
                        $_POST['account_type'] ?? 'IBN',
                        $_POST['account_title'] ?? '',
                        $_POST['account_number'] ?? '',
                        $_POST['branch_info'] ?? ''
                    ]);
                }

                // 3. Education & Experience (If provided)
                if (!empty($_POST['qualification']) || !empty($_POST['last_employer'])) {
                    $e_stmt = $pdo->prepare("INSERT INTO education_experience (employee_id, qualification, degree_cert, university, expertise, last_employer, last_job_title, exp_from, exp_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $e_stmt->execute([
                        $employee_id,
                        $_POST['qualification'] ?? '',
                        $_POST['degree_certification'] ?? '',
                        $_POST['college_university'] ?? '',
                        $_POST['professional_expertise'] ?? '',
                        $_POST['last_employer'] ?? '',
                        $_POST['last_designation'] ?? '',
                        !empty($_POST['experience_from']) ? $_POST['experience_from'] : null,
                        !empty($_POST['experience_to']) ? $_POST['experience_to'] : null
                    ]);
                }

                // 4. Update Candidate Status (If hiring)
                if ($action === 'hire_candidate' && $candidate_id) {
                    $c_stmt = $pdo->prepare("UPDATE candidates SET status = 'Hired' WHERE id = ?");
                    $c_stmt->execute([$candidate_id]);
                }

                $pdo->commit();
                
                // [LOG] - Log only if NOT from public joining form and Admin is logged in
                $admin_id = $_SESSION['user_id'] ?? 0;
                $source = $_POST['source'] ?? '';
                if ($admin_id > 0 && $source !== 'joining_form') {
                    logActivity($admin_id, "Created Employee Profile", "Employees", "Successfully onboarded a new team member: $f_name $l_name");
                }

                echo json_encode(['status' => 'success', 'message' => 'Employee record created successfully.', 'id' => $employee_id]);

            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
        break;

    case 'get_employee':
        $id = $_GET['id'] ?? '';
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Employee ID required.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                SELECT e.*, b.bank_name, b.account_type, b.account_title, b.account_number, b.branch_info,
                       ex.qualification, ex.degree_cert as degree_certification, ex.university as college_university, 
                       ex.expertise as professional_expertise, ex.last_employer, ex.last_job_title as last_designation, 
                       ex.exp_from as experience_from, ex.exp_to as experience_to
                FROM employees e
                LEFT JOIN banking_info b ON e.id = b.employee_id
                LEFT JOIN education_experience ex ON e.id = ex.employee_id
                WHERE e.id = ?
            ");
            $stmt->execute([$id]);
            $employee = $stmt->fetch();

            if ($employee) {
                echo json_encode(['status' => 'success', 'data' => $employee]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Employee not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'update':
        $id = $_POST['employee_id_hidden'] ?? ''; // From hidden input
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Internal error: Record ID missing.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // 0. Fetch current salary to check for Increments/Decrements
            $chk_stmt = $pdo->prepare("SELECT salary FROM employees WHERE id = ?");
            $chk_stmt->execute([$id]);
            $old_salary = (float)$chk_stmt->fetchColumn() ?: 0;
            $new_salary = (float)($_POST['salary'] ?? 0);

            // 0. Handle File Uploads for Edit
            $id_card_path = uploadFile($_FILES['id_card'] ?? null, 'uploads/employees/id_cards/');
            $resume_path = uploadFile($_FILES['resume'] ?? null, 'uploads/employees/resumes/');

            // Handle Other Documents (Append to existing)
            $new_other_docs = [];
            if (!empty($_FILES['other_documents']['name'][0])) {
                $files = $_FILES['other_documents'];
                foreach ($files['name'] as $key => $name) {
                    if ($files['error'][$key] === 0) {
                        $tmp_name = $files['tmp_name'][$key];
                        $ext = pathinfo($name, PATHINFO_EXTENSION);
                        $new_name = uniqid('doc_') . '.' . $ext;
                        $target_dir = '../../../uploads/employees/others/';
                        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                        if (move_uploaded_file($tmp_name, $target_dir . $new_name)) {
                            $new_other_docs[] = 'uploads/employees/others/' . $new_name;
                        }
                    }
                }
            }

            // 1. Update main record
            $status = $_POST['status'] ?? 'Active';
            $params = [
                $_POST['first_name'], 
                $_POST['middle_name'] ?: null, 
                $_POST['last_name'], 
                $_POST['gender'],
                $_POST['dob'] ?? null,
                $_POST['phone'], 
                $_POST['email'], 
                $_POST['department_id'], 
                $_POST['shift_id'], 
                $_POST['job_title'], 
                $status,
                $_POST['job_type'] ?? null,
                $_POST['salary'] ?? null,
                $_POST['joining_date'] ?? null,
                $_POST['cnic_number'] ?? null,
                $_POST['address'] ?? null,
                $_POST['emergency_contact'] ?? null,
                $_POST['emergency_relation'] ?? null
            ];

            $sql = "UPDATE employees SET 
                    first_name = ?, middle_name = ?, last_name = ?, gender = ?, dob = ?, 
                    phone = ?, email = ?, department_id = ?, shift_id = ?, job_title = ?, status = ?,
                    job_type = ?, salary = ?, joining_date = ?, cnic_number = ?, address = ?,
                    emergency_contact = ?, emergency_relation = ?";

            if ($id_card_path) {
                $sql .= ", id_card_path = ?";
                $params[] = $id_card_path;
            }
            if ($resume_path) {
                $sql .= ", resume_path = ?";
                $params[] = $resume_path;
            }
            if (!empty($new_other_docs)) {
                // Fetch existing docs to append
                $e_stmt = $pdo->prepare("SELECT other_docs FROM employees WHERE id = ?");
                $e_stmt->execute([$id]);
                $existing_json = $e_stmt->fetchColumn();
                $existing_docs = !empty($existing_json) ? json_decode($existing_json, true) : [];
                $merged_docs = array_merge($existing_docs, $new_other_docs);
                
                $sql .= ", other_docs = ?";
                $params[] = json_encode($merged_docs);
            }

            if (!empty($_POST['candidate_admin_password'])) {
                $sql .= ", password = ?";
                $params[] = password_hash($_POST['candidate_admin_password'], PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // 2. Update Banking
            $stmt = $pdo->prepare("
                INSERT INTO banking_info (employee_id, bank_name, account_type, account_title, account_number, branch_info)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                bank_name = VALUES(bank_name), account_type = VALUES(account_type), 
                account_title = VALUES(account_title), account_number = VALUES(account_number), 
                branch_info = VALUES(branch_info)
            ");
            $stmt->execute([
                $id, 
                $_POST['bank_name'] ?? '', 
                $_POST['account_type'] ?? 'IBN', 
                $_POST['account_title'] ?? '', 
                $_POST['account_number'] ?? '', 
                $_POST['branch_info'] ?? ''
            ]);

            // 3. Update Education & Experience
            $stmt = $pdo->prepare("
                INSERT INTO education_experience (employee_id, qualification, degree_cert, university, expertise, last_employer, last_job_title, exp_from, exp_to)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                qualification = VALUES(qualification), degree_cert = VALUES(degree_cert), 
                university = VALUES(university), expertise = VALUES(expertise), 
                last_employer = VALUES(last_employer), last_job_title = VALUES(last_job_title), 
                exp_from = VALUES(exp_from), exp_to = VALUES(exp_to)
            ");
            $stmt->execute([
                $id,
                $_POST['qualification'] ?? '',
                $_POST['degree_certification'] ?? '',
                $_POST['college_university'] ?? '',
                $_POST['professional_expertise'] ?? '',
                $_POST['last_employer'] ?? '',
                $_POST['last_designation'] ?? '',
                !empty($_POST['experience_from']) ? $_POST['experience_from'] : null,
                !empty($_POST['experience_to']) ? $_POST['experience_to'] : null
            ]);

            // 1.5. Record Salary History if changed
            if ($new_salary != $old_salary) {
                $change_type = ($new_salary > $old_salary) ? 'Increment' : 'Decrement';
                $amount_change = abs($new_salary - $old_salary);
                
                $h_stmt = $pdo->prepare("
                    INSERT INTO salary_history (employee_id, type, previous_salary, new_salary, amount_change, change_date)
                    VALUES (?, ?, ?, ?, ?, CURRENT_DATE())
                ");
                $h_stmt->execute([$id, $change_type, $old_salary, $new_salary, $amount_change]);
            }

            $pdo->commit();

            // [LOG]
            $admin_id = $_SESSION['user_id'] ?? 0;
            $e_name = ($_POST['first_name'] ?? '') . ' ' . ($_POST['last_name'] ?? '');
            logActivity($admin_id, "Completed Employee Onboarding", "Employees", "Formally completed and finalized the onboarding profile for new team member: $e_name");

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Employee record finalized successfully.']);
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Transaction failed: ' . $e->getMessage()]);
            exit;
        }
        break;

    case 'delete':
        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Employee ID required.']);
            exit;
        }

        try {
            // Soft delete: Change status to 'Exit' and set deleted_at
            $stmt = $pdo->prepare("UPDATE employees SET status = 'Exit', deleted_at = CURRENT_TIMESTAMP() WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                // [LOG]
                $admin_id = $_SESSION['user_id'] ?? 0;
                $e_stmt = $pdo->prepare("SELECT first_name, last_name FROM employees WHERE id = ?");
                $e_stmt->execute([$id]);
                $e_data = $e_stmt->fetch();
                $e_name = ($e_data['first_name'] ?? 'Unknown') . ' ' . ($e_data['last_name'] ?? '');
                logActivity($admin_id, "Deleted Employee Profile", "Employees", "Moved team member: $e_name to the Exit list.");

                echo json_encode(['status' => 'success', 'message' => 'Employee record moved to Exit list.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Employee not found or already exited.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;
    
    case 'fetch_hierarchy_roles':
        try {
            // Managers: Only from the 'Manager' department (ID 5)
            $stmtM = $pdo->query("SELECT id, first_name, middle_name, last_name FROM employees WHERE department_id = 5 AND deleted_at IS NULL ORDER BY first_name ASC");
            $managers = $stmtM->fetchAll();
            
            // Heads: Regular employees, excluding Admin/HR roles AND excluding the 'Manager' department (ID 5)
            $stmtH = $pdo->query("SELECT id, first_name, middle_name, last_name FROM employees WHERE role = 'Employee' AND department_id != 5 AND deleted_at IS NULL ORDER BY first_name ASC");
            $heads = $stmtH->fetchAll();
            
            echo json_encode(['status' => 'success', 'managers' => $managers, 'heads' => $heads]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}
?>
