<?php
require_once '../db_connect.php';
require_once '../auth_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'Employee';

if (!in_array($user_role, ['Employee', 'Admin', 'HR'], true)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

require_once __DIR__ . '/../access_control_helper.php';
hrGuardApiRequest($pdo, $action);

$is_admin = in_array($user_role, ['Admin', 'HR'], true);
$is_it_staff = $is_admin;
$stmt = $pdo->prepare("SELECT d.name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.id = ?");
$stmt->execute([$user_id]);
$dept_name = $stmt->fetchColumn() ?: '';
if (!$is_it_staff && (stripos($dept_name, 'IT') !== false || stripos($dept_name, 'Support') !== false)) {
    $is_it_staff = true;
}

function itFetchTicket(PDO $pdo, $ticket_id)
{
    $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE id = ?");
    $stmt->execute([(int) $ticket_id]);
    return $stmt->fetch();
}

function itCanAccessTicket($ticket, $user_id, $is_it_staff, $is_admin = false)
{
    if (!$ticket) {
        return false;
    }
    if ($is_it_staff || $is_admin) {
        return true;
    }
    return (int) $ticket['employee_id'] === (int) $user_id;
}

function itCanManageTicket($ticket, $user_id, $is_admin = false)
{
    if (!$ticket) {
        return false;
    }
    if ($is_admin) {
        return true;
    }
    if (empty($ticket['assigned_to'])) {
        return false;
    }
    return (int) $ticket['assigned_to'] === (int) $user_id;
}

function itIsInternalFlag($value)
{
    return in_array($value, ['true', '1', 1, true], true) ? 1 : 0;
}

function itHasAdminUnreadColumn(PDO $pdo)
{
    static $has = null;
    if ($has === null) {
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM `support_tickets` LIKE 'admin_unread'");
            $has = $stmt && (int) $stmt->rowCount() > 0;
        } catch (Exception $e) {
            $has = false;
        }
    }
    return $has;
}

try {
    $has_admin_unread_col = itHasAdminUnreadColumn($pdo);
    switch ($action) {
        case 'get_tickets':
            $internal_filter = $is_it_staff ? "" : "AND m.is_internal = 0";
            $query = "SELECT t.*, 
                             TRIM(CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name)) as user,
                             e.profile_pic as profile_img,
                             TRIM(CONCAT_WS(' ', a.first_name, a.middle_name, a.last_name)) as assigned_to_name,
                             (SELECT COUNT(*) FROM ticket_messages m WHERE m.ticket_id = t.id AND m.is_system = 0 $internal_filter) as msg_count
                      FROM support_tickets t
                      JOIN employees e ON t.employee_id = e.id
                      LEFT JOIN employees a ON t.assigned_to = a.id";

            if (!$is_it_staff) {
                $query .= " WHERE t.employee_id = :user_id";
            }
            $query .= " ORDER BY t.created_at DESC";

            $stmt = $pdo->prepare($query);
            if (!$is_it_staff) {
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            }
            $stmt->execute();
            $tickets = $stmt->fetchAll();

            $formatted_tickets = [];
            foreach ($tickets as $t) {
                $created_time = new DateTime($t['created_at']);

                $unread_count = 0;
                if ((int) $t['employee_id'] === $user_id) {
                    $unread_count = (int) $t['employee_unread'];
                } elseif ($is_admin && $has_admin_unread_col) {
                    $unread_count = (int) ($t['admin_unread'] ?? 0);
                } elseif ($is_admin) {
                    $unread_count = (int) $t['it_unread'];
                } elseif ($is_it_staff && (!$t['assigned_to'] || (int) $t['assigned_to'] === $user_id)) {
                    $unread_count = (int) $t['it_unread'];
                }

                $display_date = $created_time->format('Y') === date('Y')
                    ? $created_time->format('M d, h:i A')
                    : $created_time->format('M d, Y h:i A');

                $formatted_tickets[] = [
                    'id' => $t['id'],
                    'subject' => $t['subject'],
                    'status' => $t['status'] == 'In Progress' ? 'In-Progress' : $t['status'],
                    'category' => $t['category'],
                    'time' => $created_time->format('h:i A'),
                    'date' => $display_date,
                    'user' => $t['user'],
                    'employee_id' => $t['employee_id'],
                    'assigned_to' => $t['assigned_to'],
                    'assigned_to_name' => $t['assigned_to_name'],
                    'profile_img' => $t['profile_img'] ? '../' . $t['profile_img'] : null,
                    'msg_count' => (int) $t['msg_count'],
                    'unread_count' => $unread_count,
                ];
            }
            echo json_encode(['success' => true, 'data' => $formatted_tickets]);
            break;

        case 'get_ticket_details':
            $ticket_id = (int) ($_GET['ticket_id'] ?? 0);
            $ticketRow = itFetchTicket($pdo, $ticket_id);

            if (!itCanAccessTicket($ticketRow, $user_id, $is_it_staff, $is_admin)) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }

            $ticket_creator = $ticketRow['employee_id'];
            $assigned_to = $ticketRow['assigned_to'];

            if ($user_id == $ticket_creator) {
                $stmt = $pdo->prepare("UPDATE support_tickets SET employee_unread = 0 WHERE id = ?");
                $stmt->execute([$ticket_id]);
            } elseif ($is_admin && $has_admin_unread_col) {
                $stmt = $pdo->prepare("UPDATE support_tickets SET admin_unread = 0 WHERE id = ?");
                $stmt->execute([$ticket_id]);
            } elseif ($is_it_staff && (!$assigned_to || (int) $assigned_to === $user_id)) {
                $stmt = $pdo->prepare("UPDATE support_tickets SET it_unread = 0 WHERE id = ?");
                $stmt->execute([$ticket_id]);
            }

            $stmt = $pdo->prepare("SELECT t.*, 
                             TRIM(CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name)) as user,
                             e.profile_pic as profile_img,
                             TRIM(CONCAT_WS(' ', a.first_name, a.middle_name, a.last_name)) as assigned_to_name
                      FROM support_tickets t
                      JOIN employees e ON t.employee_id = e.id
                      LEFT JOIN employees a ON t.assigned_to = a.id
                      WHERE t.id = ?");
            $stmt->execute([$ticket_id]);
            $ticket = $stmt->fetch();

            if (!$ticket) {
                echo json_encode(['success' => false, 'message' => 'Ticket not found']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT m.*, TRIM(CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name)) as sender_name 
                         FROM ticket_messages m 
                         JOIN employees s ON m.sender_id = s.id 
                         WHERE m.ticket_id = ? ORDER BY m.created_at ASC");
            $stmt->execute([$ticket_id]);
            $raw_messages = $stmt->fetchAll();

            $messages = [];
            foreach ($raw_messages as $m) {
                if ($m['is_internal'] && !$is_it_staff) {
                    continue;
                }

                $msgTime = new DateTime($m['created_at']);
                $messages[] = [
                    'sender' => $m['sender_name'],
                    'sender_id' => $m['sender_id'],
                    'text' => nl2br(htmlspecialchars($m['message'])),
                    'time' => $msgTime->format('M d, h:i A'),
                    'is_internal' => (bool) $m['is_internal'],
                    'is_system' => (bool) $m['is_system'],
                ];
            }

            if (empty($messages)) {
                $created_time = new DateTime($ticket['created_at']);
                $messages[] = [
                    'sender' => $ticket['user'],
                    'sender_id' => $ticket['employee_id'],
                    'text' => nl2br(htmlspecialchars($ticket['description'])),
                    'time' => $created_time->format('M d, h:i A'),
                    'is_internal' => false,
                    'is_system' => false,
                ];
            }

            $created_time = new DateTime($ticket['created_at']);
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $ticket['id'],
                    'subject' => $ticket['subject'],
                    'status' => $ticket['status'] == 'In Progress' ? 'In-Progress' : $ticket['status'],
                    'category' => $ticket['category'],
                    'time' => $created_time->format('h:i A'),
                    'date' => $created_time->format('M d, Y'),
                    'user' => $ticket['user'],
                    'employee_id' => $ticket['employee_id'],
                    'assigned_to' => $ticket['assigned_to'],
                    'assigned_to_name' => $ticket['assigned_to_name'],
                    'profile_img' => $ticket['profile_img'] ? '../' . $ticket['profile_img'] : null,
                    'resolution_time' => $ticket['resolution_duration'],
                    'reopened_count' => $ticket['reopen_count'],
                    'messages' => $messages,
                ],
            ]);
            break;

        case 'create_ticket':
            if ($is_admin) {
                echo json_encode(['success' => false, 'message' => 'Admins cannot create tickets from this panel.']);
                exit;
            }
            $category = $_POST['category'] ?? '';
            if ($category === 'Other') {
                $category = trim($_POST['custom_category'] ?? 'Other');
            }
            $subject = trim($_POST['subject'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($subject === '' || $description === '') {
                echo json_encode(['success' => false, 'message' => 'Subject and description are required.']);
                exit;
            }

            if ($has_admin_unread_col) {
                $stmt = $pdo->prepare("INSERT INTO support_tickets (employee_id, subject, category, description, status, it_unread, admin_unread) VALUES (?, ?, ?, ?, 'Open', 1, 1)");
            } else {
                $stmt = $pdo->prepare("INSERT INTO support_tickets (employee_id, subject, category, description, status, it_unread) VALUES (?, ?, ?, ?, 'Open', 1)");
            }
            $stmt->execute([$user_id, $subject, $category, $description]);
            $ticket_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message, is_internal) VALUES (?, ?, ?, 0)");
            $stmt->execute([$ticket_id, $user_id, $description]);

            echo json_encode(['success' => true, 'message' => 'Ticket created successfully']);
            break;

        case 'send_message':
            $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            $is_internal = $is_it_staff ? itIsInternalFlag($_POST['is_internal'] ?? false) : 0;

            if ($message === '') {
                echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
                exit;
            }

            $ticketRow = itFetchTicket($pdo, $ticket_id);
            if (!itCanAccessTicket($ticketRow, $user_id, $is_it_staff, $is_admin)) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }

            $ticket_creator = (int) $ticketRow['employee_id'];

            $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message, is_internal) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ticket_id, $user_id, $message, $is_internal]);

            if ($user_id === $ticket_creator) {
                if ($has_admin_unread_col) {
                    $stmt = $pdo->prepare("UPDATE support_tickets SET it_unread = it_unread + 1, admin_unread = admin_unread + 1 WHERE id = ?");
                } else {
                    $stmt = $pdo->prepare("UPDATE support_tickets SET it_unread = it_unread + 1 WHERE id = ?");
                }
                $stmt->execute([$ticket_id]);
            } elseif ($is_internal) {
                if ($is_admin) {
                    $stmt = $pdo->prepare("UPDATE support_tickets SET it_unread = it_unread + 1 WHERE id = ?");
                    $stmt->execute([$ticket_id]);
                } elseif ($has_admin_unread_col) {
                    $stmt = $pdo->prepare("UPDATE support_tickets SET admin_unread = admin_unread + 1 WHERE id = ?");
                    $stmt->execute([$ticket_id]);
                }
            } else {
                $stmt = $pdo->prepare("UPDATE support_tickets SET employee_unread = employee_unread + 1 WHERE id = ?");
                $stmt->execute([$ticket_id]);
                if ($is_admin) {
                    $stmt = $pdo->prepare("UPDATE support_tickets SET it_unread = it_unread + 1 WHERE id = ?");
                    $stmt->execute([$ticket_id]);
                } elseif ($has_admin_unread_col) {
                    $stmt = $pdo->prepare("UPDATE support_tickets SET admin_unread = admin_unread + 1 WHERE id = ?");
                    $stmt->execute([$ticket_id]);
                }
            }

            echo json_encode(['success' => true]);
            break;

        case 'claim_ticket':
            if (!$is_it_staff) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
            $ticketRow = itFetchTicket($pdo, $ticket_id);

            if (!$ticketRow) {
                echo json_encode(['success' => false, 'message' => 'Ticket not found']);
                exit;
            }
            if (!$is_admin && !empty($ticketRow['assigned_to']) && (int) $ticketRow['assigned_to'] !== $user_id) {
                echo json_encode(['success' => false, 'message' => 'This ticket is already assigned to another agent.']);
                exit;
            }

            if ($is_admin) {
                $stmt = $pdo->prepare("UPDATE support_tickets SET assigned_to = ?, status = 'In Progress' WHERE id = ?");
                $stmt->execute([$user_id, $ticket_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE support_tickets SET assigned_to = ?, status = 'In Progress' WHERE id = ? AND (assigned_to IS NULL OR assigned_to = 0 OR assigned_to = ?)");
                $stmt->execute([$user_id, $ticket_id, $user_id]);
            }

            echo json_encode(['success' => true]);
            break;

        case 'handover_ticket':
            if (!$is_it_staff) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
            $ticketRow = itFetchTicket($pdo, $ticket_id);

            if (!itCanAccessTicket($ticketRow, $user_id, $is_it_staff, $is_admin)) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            if (!itCanManageTicket($ticketRow, $user_id, $is_admin)) {
                echo json_encode(['success' => false, 'message' => 'Only the assigned agent can handover this ticket.']);
                exit;
            }

            $new_assignee_id = (int) ($_POST['new_assignee_id'] ?? 0);
            if ($new_assignee_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select an agent.']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT TRIM(CONCAT_WS(' ', first_name, middle_name, last_name)) FROM employees WHERE id = ?");
            $stmt->execute([$new_assignee_id]);
            $new_assignee_name = $stmt->fetchColumn() ?: 'another agent';

            $stmt = $pdo->prepare("UPDATE support_tickets SET assigned_to = ? WHERE id = ?");
            $stmt->execute([$new_assignee_id, $ticket_id]);

            $note = "Ticket handed over to $new_assignee_name.";
            $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message, is_internal, is_system) VALUES (?, ?, ?, 1, 1)");
            $stmt->execute([$ticket_id, $user_id, $note]);

            echo json_encode(['success' => true]);
            break;

        case 'resolve_ticket':
            if (!$is_it_staff) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
            $ticketRow = itFetchTicket($pdo, $ticket_id);

            if (!itCanManageTicket($ticketRow, $user_id, $is_admin)) {
                echo json_encode(['success' => false, 'message' => 'Only the assigned agent can resolve this ticket.']);
                exit;
            }

            $created_time = new DateTime($ticketRow['created_at']);
            $diff = $created_time->diff(new DateTime());
            $durationParts = [];
            if ($diff->d > 0) {
                $durationParts[] = $diff->d . ' days';
            }
            if ($diff->h > 0) {
                $durationParts[] = $diff->h . ' hours';
            }
            if ($diff->i > 0) {
                $durationParts[] = $diff->i . ' mins';
            }
            $durationStr = empty($durationParts) ? 'less than a minute' : implode(' ', $durationParts);

            $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'Resolved', resolved_by = ?, resolved_at = NOW(), resolution_duration = ? WHERE id = ?");
            $stmt->execute([$user_id, $durationStr, $ticket_id]);

            echo json_encode(['success' => true]);
            break;

        case 'close_ticket':
            if (!$is_it_staff) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
            $ticketRow = itFetchTicket($pdo, $ticket_id);

            if (!itCanManageTicket($ticketRow, $user_id, $is_admin)) {
                echo json_encode(['success' => false, 'message' => 'Only the assigned agent can close this ticket.']);
                exit;
            }

            $created_time = new DateTime($ticketRow['created_at']);
            $diff = $created_time->diff(new DateTime());
            $durationStr = trim(
                ($diff->d > 0 ? $diff->d . ' days ' : '') .
                ($diff->h > 0 ? $diff->h . ' hrs ' : '') .
                $diff->i . ' mins'
            );

            $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'Closed', closed_by = ?, closed_at = NOW(), resolution_duration = ? WHERE id = ?");
            $stmt->execute([$user_id, $durationStr, $ticket_id]);

            echo json_encode(['success' => true]);
            break;

        case 'reopen_ticket':
            $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
            $ticketRow = itFetchTicket($pdo, $ticket_id);

            if (!$ticketRow || ((int) $ticketRow['employee_id'] !== $user_id && !$is_admin)) {
                echo json_encode(['success' => false, 'message' => 'Only the ticket creator can reopen this ticket.']);
                exit;
            }

            $res_dur = $ticketRow['resolution_duration'] ?? 'Unknown time';
            $agentName = 'an unknown agent';
            if ($ticketRow['assigned_to']) {
                $s2 = $pdo->prepare("SELECT TRIM(CONCAT_WS(' ', first_name, middle_name, last_name)) FROM employees WHERE id = ?");
                $s2->execute([$ticketRow['assigned_to']]);
                $agentName = $s2->fetchColumn() ?: $agentName;
            }

            if ($has_admin_unread_col) {
                $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'In Progress', reopen_count = reopen_count + 1, it_unread = it_unread + 1, admin_unread = admin_unread + 1 WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'In Progress', reopen_count = reopen_count + 1, it_unread = it_unread + 1 WHERE id = ?");
            }
            $stmt->execute([$ticket_id]);

            $note = "Ticket Re-opened. Previously resolved by $agentName in $res_dur.";
            $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message, is_internal, is_system) VALUES (?, ?, ?, 0, 1)");
            $stmt->execute([$ticket_id, $user_id, $note]);

            echo json_encode(['success' => true]);
            break;

        case 'get_it_staff':
            if (!$is_it_staff) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT e.id, TRIM(CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name)) as name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE d.name LIKE '%IT%' OR d.name LIKE '%Support%'");
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'get_dashboard_stats':
            if (!$is_it_staff) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }

            $stats = [
                'All' => 0,
                'Open' => 0,
                'In_Progress' => 0,
                'Resolved' => 0,
                'Closed' => 0,
            ];

            $stmt = $pdo->query("SELECT COUNT(*) FROM support_tickets");
            $stats['All'] = (int) $stmt->fetchColumn();

            $stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM support_tickets GROUP BY status");
            foreach ($stmt->fetchAll() as $row) {
                $status = $row['status'];
                if ($status === 'In Progress' || $status === 'In-Progress') {
                    $stats['In_Progress'] += (int) $row['cnt'];
                } else {
                    $stats[$status] = (int) $row['cnt'];
                }
            }

            $stmt = $pdo->query("
                SELECT t.resolved_by as agent_id, 
                       TRIM(CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name)) as agent_name,
                       e.profile_pic as profile_img,
                       COUNT(*) as resolved_count 
                FROM support_tickets t
                JOIN employees e ON t.resolved_by = e.id
                WHERE t.status IN ('Resolved', 'Closed') AND t.resolved_by IS NOT NULL
                GROUP BY t.resolved_by, agent_name, e.profile_pic
                ORDER BY resolved_count DESC
                LIMIT 5
            ");
            $top_resolvers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($top_resolvers as &$resolver) {
                $resolver['profile_img'] = $resolver['profile_img'] ? '../' . $resolver['profile_img'] : null;
            }

            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'top_resolvers' => $top_resolvers,
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
