<?php
// admin/assets/api/employee_handler.php
require_once '../../../includes/db_connect.php';
require_once '../../../includes/auth_helper.php';
require_once '../../../includes/api/activity_helper.php';
require_once '../../../includes/api/rate_limiter.php';

header('Content-Type: application/json');

// Session & Role Check (Optional: Allow Onboarding without full session if needed)
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Rate limit public-facing endpoints
if ($action === 'check_email' && !checkRateLimit('check_email', 15, 60)) { rateLimitExceeded(); }
if ($action === 'onboard' && !checkRateLimit('onboard', 5, 60)) { rateLimitExceeded(); }

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
    
    // Security: Validate file extension
    $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts)) return null;
    
    // Security: Validate file size (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) return null;
    
    $newName = uniqid('EMP_') . '.' . $ext;
    $targetPath = '../../../' . $targetDir . $newName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetDir . $newName;
    }
    return null;
}

/**
 * Email availability: active row blocks use; exited row still holds UNIQUE email.
 *
 * @return array{code: 'available'|'active'|'exited', employee_id?: int, employee_name?: string}
 */
function resolveEmployeeEmailAvailability(PDO $pdo, string $email, ?int $excludeId = null): array
{
    $email = trim($email);
    $activeSql = "SELECT id, first_name, last_name FROM employees WHERE email = ? AND deleted_at IS NULL";
    $activeParams = [$email];
    if ($excludeId) {
        $activeSql .= " AND id != ?";
        $activeParams[] = $excludeId;
    }
    $stmt = $pdo->prepare($activeSql);
    $stmt->execute($activeParams);
    $active = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($active) {
        return [
            'code' => 'active',
            'employee_id' => (int) $active['id'],
            'employee_name' => trim($active['first_name'] . ' ' . $active['last_name']),
        ];
    }

    $exitedSql = "SELECT id, first_name, last_name FROM employees WHERE email = ? AND deleted_at IS NOT NULL";
    $exitedParams = [$email];
    if ($excludeId) {
        $exitedSql .= " AND id != ?";
        $exitedParams[] = $excludeId;
    }
    $exitedStmt = $pdo->prepare($exitedSql);
    $exitedStmt->execute($exitedParams);
    $exited = $exitedStmt->fetch(PDO::FETCH_ASSOC);
    if ($exited) {
        return [
            'code' => 'exited',
            'employee_id' => (int) $exited['id'],
            'employee_name' => trim($exited['first_name'] . ' ' . $exited['last_name']),
        ];
    }

    return ['code' => 'available'];
}

function employeeEmailAvailabilityMessage(array $availability): string
{
    if ($availability['code'] === 'active') {
        return 'This email is already registered to an active employee.';
    }
    if ($availability['code'] === 'exited') {
        $name = $availability['employee_name'] ?: 'a former employee';
        $empId = $availability['employee_id'];
        return "This email belongs to an exited employee ({$name}, EMP-{$empId}). Restore them from the Exit list or use a different email.";
    }
    return 'Email is available.';
}

function assertEmployeeEmailAvailable(PDO $pdo, string $email, ?int $excludeId = null): void
{
    $availability = resolveEmployeeEmailAvailability($pdo, $email, $excludeId);
    if ($availability['code'] !== 'available') {
        throw new Exception(employeeEmailAvailabilityMessage($availability));
    }
}

/** Required-field rules: optional = experience dates, other docs, middle_name. */
function validateEmployeeProfilePayload(array $post, array $files, string $context, ?array $existing = null): void
{
    $optional = array_flip(['middle_name', 'experience_from', 'experience_to', 'other_documents']);
    $adminOnly = array_flip(['department_id', 'shift_id', 'job_type', 'salary', 'joining_date']);
    $labels = [
        'first_name' => 'First Name', 'last_name' => 'Last Name', 'gender' => 'Gender', 'phone' => 'Phone',
        'email' => 'Email', 'dob' => 'Date of Birth', 'cnic_number' => 'ID Card Number', 'address' => 'Address',
        'emergency_contact' => 'Emergency Contact', 'emergency_relation' => 'Emergency Contact Relation',
        'job_title' => 'Job Title', 'bank_name' => 'Bank Name', 'account_type' => 'Account Type',
        'account_title' => 'Account Title', 'account_number' => 'Account Number', 'branch_info' => 'Bank Branch',
        'qualification' => 'Qualification', 'degree_certification' => 'Degree / Certification',
        'college_university' => 'College / University', 'professional_expertise' => 'Professional Expertise',
        'last_employer' => 'Last Employer', 'last_designation' => 'Last Job Title',
        'department_id' => 'Department', 'shift_id' => 'Shift Timing', 'job_type' => 'Job Type',
        'salary' => 'Salary', 'joining_date' => 'Joining Date',
    ];

    $textFields = [
        'first_name', 'last_name', 'gender', 'phone', 'email', 'dob', 'cnic_number', 'address',
        'emergency_contact', 'emergency_relation', 'job_title',
        'bank_name', 'account_type', 'account_title', 'account_number', 'branch_info',
        'qualification', 'degree_certification', 'college_university', 'professional_expertise',
        'last_employer', 'last_designation',
    ];

    if (in_array($context, ['admin_create', 'admin_finalize', 'admin_update'], true)) {
        $textFields = array_merge($textFields, ['department_id', 'shift_id', 'job_type', 'salary', 'joining_date']);
    }

    foreach ($textFields as $field) {
        if (isset($optional[$field]) || ($context === 'onboard' && isset($adminOnly[$field]))) {
            continue;
        }
        if (trim((string) ($post[$field] ?? '')) === '') {
            throw new Exception(($labels[$field] ?? $field) . ' is required.');
        }
    }

    if (!filter_var(trim($post['email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format.');
    }
    if (strlen(preg_replace('/\D/', '', (string) ($post['phone'] ?? ''))) !== 11) {
        throw new Exception('Phone number must be exactly 11 digits.');
    }
    if (strlen(preg_replace('/\D/', '', (string) ($post['emergency_contact'] ?? ''))) !== 11) {
        throw new Exception('Emergency contact must be exactly 11 digits.');
    }
    if (strlen(preg_replace('/\D/', '', (string) ($post['cnic_number'] ?? ''))) < 13) {
        throw new Exception('ID card number must be at least 13 digits.');
    }

    if (in_array($context, ['admin_create', 'admin_finalize'], true)) {
        $pwd = trim($post[$context === 'admin_finalize' ? 'candidate_admin_password' : 'password'] ?? '');
        if ($pwd === '') {
            throw new Exception('Login password is required.');
        }
        if (strlen($pwd) < 6) {
            throw new Exception('Login password must be at least 6 characters.');
        }
    }

    $hasResume = (!empty($files['resume']['name']) && ($files['resume']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK)
        || !empty($existing['resume_path']);
    $hasId = (!empty($files['id_card']['name']) && ($files['id_card']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK)
        || !empty($existing['id_card_path']);

    if (!$hasResume) {
        throw new Exception('Resume attachment is required.');
    }
    if (!$hasId) {
        throw new Exception('ID card attachment is required.');
    }
}

switch ($action) {
        case 'fetch_pending':
        try {
            $stmt = $pdo->query("SELECT id, first_name, middle_name, last_name, email, phone, job_title, department_id, created_at FROM employees WHERE status = 'Pending' AND deleted_at IS NULL ORDER BY created_at DESC");
            $employees = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $employees]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
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
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
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

            // Strip 'EMP-', 'EMP-0', and leading zeros from ID search
            if (!empty($id_search)) {
                $id_search = preg_replace('/^EMP-0*/i', '', $id_search);
                $id_search = ltrim($id_search, '0');
            }

            $where = [];
            if ($status === 'Exit') {
                $where[] = "e.deleted_at IS NOT NULL";
            } else {
                $where[] = "e.deleted_at IS NULL";
            }
            $params = [];

            // 1. ID Filter
            if (!empty($id_search)) {
                $where[] = "e.id LIKE ?";
                $params[] = "%$id_search%";
            }
            // 2. Name Filter
            if (!empty($name_search)) {
                $where[] = "(e.first_name LIKE ? OR e.last_name LIKE ? OR e.middle_name LIKE ?)";
                $params[] = "%$name_search%";
                $params[] = "%$name_search%";
                $params[] = "%$name_search%";
            }
            // 3. Dept Filter
            if (!empty($dept)) {
                $where[] = "e.department_id = ?";
                $params[] = $dept;
            }
            // 4. Role Filter
            if (!empty($role)) {
                $where[] = "e.role = ?";
                $params[] = $role;
            } elseif (empty($id_search) && empty($name_search)) {
                // Default view (no search): only HR/Employee
                $where[] = "e.role IN ('Employee', 'HR')";
            }

            // 5. Status Filter
            if ($status === 'Exit') {
                // Already handled e.deleted_at IS NOT NULL above
            } elseif (!empty($status)) {
                $where[] = "e.status = ?";
                $params[] = $status;
            } elseif (empty($id_search) && empty($name_search)) {
                // Default view (no search): only Active/On Leave
                $where[] = "e.status IN ('Active', 'On Leave')";
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
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'check_email':
        $email = trim($_GET['email'] ?? '');
        if (empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Email required.']);
            exit;
        }

        try {
            $excludeId = isset($_GET['exclude_id']) ? (int) $_GET['exclude_id'] : null;
            $availability = resolveEmployeeEmailAvailability($pdo, $email, $excludeId ?: null);
            if ($availability['code'] === 'available') {
                echo json_encode(['status' => 'success', 'message' => 'Email is available.']);
            } elseif ($availability['code'] === 'exited') {
                echo json_encode([
                    'status' => 'exited',
                    'code' => 'exit_employee',
                    'message' => employeeEmailAvailabilityMessage($availability),
                    'employee_id' => $availability['employee_id'],
                    'employee_name' => $availability['employee_name'] ?? null,
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => employeeEmailAvailabilityMessage($availability)]);
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

                $validationContext = ($action === 'onboard') ? 'onboard' : (($action === 'add') ? 'admin_create' : null);
                if ($validationContext) {
                    validateEmployeeProfilePayload($_POST, $_FILES, $validationContext, null);
                } elseif (empty($f_name) || empty($l_name) || empty($email)) {
                    throw new Exception("First Name, Last Name, and Email are mandatory.");
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email format.");
                }

                assertEmployeeEmailAvailable($pdo, $email);

                // File Uploads
                $id_card_path = uploadFile($_FILES['id_card'] ?? null, 'uploads/employees/id_cards/');
                $resume_path = uploadFile($_FILES['resume'] ?? null, 'uploads/employees/resumes/');

                // Other Documents (Multi-upload)
                $other_docs_paths = [];
                if (!empty($_FILES['other_documents']['name'][0])) {
                    $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'];
                    $files = $_FILES['other_documents'];
                    foreach ($files['name'] as $key => $name) {
                        if ($files['error'][$key] === 0) {
                            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                            if (in_array($ext, $allowed_exts)) {
                                $tmp_name = $files['tmp_name'][$key];
                                $new_name = uniqid('doc_') . '.' . $ext;
                                $target_dir = '../../../uploads/employees/others/';
                                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                                
                                if (move_uploaded_file($tmp_name, $target_dir . $new_name)) {
                                    $other_docs_paths[] = 'uploads/employees/others/' . $new_name;
                                }
                            }
                        }
                    }
                }
                $other_docs_json = !empty($other_docs_paths) ? json_encode($other_docs_paths) : null;

                if ($validationContext && !$id_card_path) {
                    throw new Exception("ID card attachment is required.");
                }
                if ($validationContext && !$resume_path) {
                    throw new Exception("Resume attachment is required.");
                }

                if ($action === 'onboard') {
                    $password = '';
                } elseif ($action === 'add') {
                    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
                } else {
                    $password = password_hash('Emp123@#', PASSWORD_DEFAULT);
                }

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

                if ($validationContext) {
                    $b_stmt = $pdo->prepare("INSERT INTO banking_info (employee_id, bank_name, account_type, account_title, account_number, branch_info) VALUES (?, ?, ?, ?, ?, ?)");
                    $b_stmt->execute([
                        $employee_id,
                        $_POST['bank_name'],
                        $_POST['account_type'] ?? 'IBN',
                        $_POST['account_title'] ?? '',
                        $_POST['account_number'] ?? '',
                        $_POST['branch_info'] ?? ''
                    ]);

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
                } elseif (!empty($_POST['bank_name'])) {
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

                if (!$validationContext && (!empty($_POST['qualification']) || !empty($_POST['last_employer']))) {
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

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $message = 'A server error occurred. Please try again.';
                if (strpos($e->getMessage(), '1062') !== false && stripos($e->getMessage(), 'email') !== false) {
                    $message = 'This email is already in use. If the employee previously exited, restore them from the Exit list or use a different email.';
                }
                echo json_encode(['status' => 'error', 'message' => $message]);
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
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
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

            $existingStmt = $pdo->prepare("SELECT * FROM employees WHERE id = ? AND deleted_at IS NULL");
            $existingStmt->execute([$id]);
            $existingRow = $existingStmt->fetch(PDO::FETCH_ASSOC);
            if (!$existingRow) {
                throw new Exception('Employee not found.');
            }

            $updateContext = array_key_exists('candidate_admin_password', $_POST) ? 'admin_finalize' : 'admin_update';
            validateEmployeeProfilePayload($_POST, $_FILES, $updateContext, $existingRow);

            $newEmail = trim($_POST['email'] ?? '');
            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format.');
            }
            assertEmployeeEmailAvailable($pdo, $newEmail, (int) $id);

            // 0. Fetch current salary to check for Increments/Decrements
            $old_salary = (float)($existingRow['salary'] ?? 0);
            $new_salary = (float)($_POST['salary'] ?? 0);

            // 0. Handle File Uploads for Edit
            $id_card_path = uploadFile($_FILES['id_card'] ?? null, 'uploads/employees/id_cards/');
            $resume_path = uploadFile($_FILES['resume'] ?? null, 'uploads/employees/resumes/');

            // Handle Other Documents (Append to existing)
            $new_other_docs = [];
            if (!empty($_FILES['other_documents']['name'][0])) {
                $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'];
                $files = $_FILES['other_documents'];
                foreach ($files['name'] as $key => $name) {
                    if ($files['error'][$key] === 0) {
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        if (in_array($ext, $allowed_exts)) {
                            $tmp_name = $files['tmp_name'][$key];
                            $new_name = uniqid('doc_') . '.' . $ext;
                            $target_dir = '../../../uploads/employees/others/';
                            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                            if (move_uploaded_file($tmp_name, $target_dir . $new_name)) {
                                $new_other_docs[] = 'uploads/employees/others/' . $new_name;
                            }
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
            } elseif (!empty($_POST['password'])) {
                $editPwd = trim($_POST['password']);
                if (strlen($editPwd) < 6) {
                    throw new Exception('Login password must be at least 6 characters.');
                }
                $sql .= ", password = ?";
                $params[] = password_hash($editPwd, PASSWORD_DEFAULT);
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
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = 'A server error occurred. Please try again.';
            if (strpos($e->getMessage(), '1062') !== false && stripos($e->getMessage(), 'email') !== false) {
                $message = 'This email is already in use. If the employee previously exited, restore them from the Exit list or use a different email.';
            }
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $message]);
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
        break;

    case 'restore':
        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Employee ID required.']);
            exit;
        }

        try {
            $chk = $pdo->prepare("SELECT id, first_name, last_name, deleted_at FROM employees WHERE id = ?");
            $chk->execute([$id]);
            $row = $chk->fetch(PDO::FETCH_ASSOC);

            if (!$row || empty($row['deleted_at'])) {
                echo json_encode(['status' => 'error', 'message' => 'Employee is not in the exit list or was already restored.']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE employees SET status = 'Active', deleted_at = NULL WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                $admin_id = $_SESSION['user_id'] ?? 0;
                $e_name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                logActivity($admin_id, "Restored Employee Profile", "Employees", "Reactivated team member: $e_name");

                echo json_encode(['status' => 'success', 'message' => 'Employee restored to active directory.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Unable to restore employee.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
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
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;
    
    case 'fetch_hierarchy_roles':
        try {
            // 1. Dynamically find the ID of the 'Manager' department
            $stmtDept = $pdo->query("SELECT id FROM departments WHERE name = 'Manager' AND deleted_at IS NULL LIMIT 1");
            $managerDept = $stmtDept->fetch();
            $managerDeptId = $managerDept ? $managerDept['id'] : 0;

            // 2. Managers: Only from the identified 'Manager' department
            $stmtM = $pdo->prepare("SELECT id, first_name, middle_name, last_name FROM employees WHERE department_id = ? AND deleted_at IS NULL ORDER BY first_name ASC");
            $stmtM->execute([$managerDeptId]);
            $managers = $stmtM->fetchAll();
            
            // 3. Heads: Regular employees, excluding the 'Manager' department
            $stmtH = $pdo->prepare("SELECT id, first_name, middle_name, last_name FROM employees WHERE role = 'Employee' AND department_id != ? AND deleted_at IS NULL ORDER BY first_name ASC");
            $stmtH->execute([$managerDeptId]);
            $heads = $stmtH->fetchAll();
            
            echo json_encode(['status' => 'success', 'managers' => $managers, 'heads' => $heads]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}
?>
