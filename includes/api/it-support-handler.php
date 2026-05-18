<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'Employee';

// Check if user is IT staff
$is_it_staff = false;
$stmt = $pdo->prepare("SELECT d.name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.id = ?");
$stmt->execute([$user_id]);
$dept_name = $stmt->fetchColumn() ?: '';
if (stripos($dept_name, 'IT') !== false || stripos($dept_name, 'Support') !== false || $user_role === 'Admin') {
    $is_it_staff = true;
}

try {
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
                // Regular users only see their own tickets
                $query .= " WHERE t.employee_id = :user_id";
            }
            $query .= " ORDER BY t.created_at DESC";
            
            $stmt = $pdo->prepare($query);
            if (!$is_it_staff) {
                $stmt->bindParam(':user_id', $user_id);
            }
            $stmt->execute();
            $tickets = $stmt->fetchAll();
            
            // Format data for frontend
            $formatted_tickets = [];
            foreach ($tickets as $t) {
                $created_time = new DateTime($t['created_at']);
                
                $unread_count = 0;
                if ($t['employee_id'] == $user_id) {
                    // Logged in user is the creator (Employee view)
                    $unread_count = (int)$t['employee_unread'];
                } else {
                    // Logged in user is not the creator (IT Agent / Admin view)
                    if ($is_it_staff) {
                        // If not claimed yet (assigned_to is null or 0), all IT see it
                        // If claimed, only the assigned agent sees the unread badge
                        if (!$t['assigned_to'] || $t['assigned_to'] == $user_id) {
                            $unread_count = (int)$t['it_unread'];
                        }
                    }
                }

                $current_year = date('Y');
                $ticket_year = $created_time->format('Y');
                if ($ticket_year === $current_year) {
                    $display_date = $created_time->format('M d, h:i A');
                } else {
                    $display_date = $created_time->format('M d, Y \a\t h:i A'); // Or "M d, Y h:i A" as requested
                    // Wait, the user specifically requested: May 19,2025 02:04 AM.
                    // Let's use: $created_time->format('M d, Y h:i A') or 'M d, Y h:i A'
                    $display_date = $created_time->format('M d, Y h:i A');
                }

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
                    'msg_count' => (int)$t['msg_count'],
                    'unread_count' => $unread_count
                ];
            }
            echo json_encode(['success' => true, 'data' => $formatted_tickets]);
            break;

        case 'get_ticket_details':
            $ticket_id = $_GET['ticket_id'] ?? 0;
            
            // Get ticket details to identify creator and assigned agent
            $stmt = $pdo->prepare("SELECT employee_id, assigned_to FROM support_tickets WHERE id = ?");
            $stmt->execute([$ticket_id]);
            $ticket = $stmt->fetch();
            
            if (!$ticket) {
                echo json_encode(['success' => false, 'message' => 'Ticket not found']);
                exit;
            }
            
            $ticket_creator = $ticket['employee_id'];
            $assigned_to = $ticket['assigned_to'];
            
            // Clear unread count for the active viewer
            if ($user_id == $ticket_creator) {
                $stmt = $pdo->prepare("UPDATE support_tickets SET employee_unread = 0 WHERE id = ?");
                $stmt->execute([$ticket_id]);
            } else {
                // IT Staff is viewing. Only clear it_unread if ticket is unassigned OR viewed by the assigned agent
                if (!$assigned_to || $assigned_to == $user_id) {
                    $stmt = $pdo->prepare("UPDATE support_tickets SET it_unread = 0 WHERE id = ?");
                    $stmt->execute([$ticket_id]);
                }
            }
            
            // Get ticket details again or use fetched values
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
            
            if (!$is_it_staff && $ticket['employee_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }

            // Get messages
            $msgQuery = "SELECT m.*, TRIM(CONCAT_WS(' ', s.first_name, s.middle_name, s.last_name)) as sender_name 
                         FROM ticket_messages m 
                         JOIN employees s ON m.sender_id = s.id 
                         WHERE m.ticket_id = ? ORDER BY m.created_at ASC";
            $stmt = $pdo->prepare($msgQuery);
            $stmt->execute([$ticket_id]);
            $raw_messages = $stmt->fetchAll();
            
            $messages = [];
            foreach ($raw_messages as $m) {
                // Skip internal messages for non-IT staff
                if ($m['is_internal'] && !$is_it_staff) continue;
                
                $msgTime = new DateTime($m['created_at']);
                
                // Add Initial ticket description as first message if needed, or handle normally. 
                // We'll treat description as part of the ticket itself or append it at the UI.
                
                $messages[] = [
                    'sender' => $m['sender_name'],
                    'sender_id' => $m['sender_id'],
                    'text' => nl2br(htmlspecialchars($m['message'])),
                    'time' => $msgTime->format('M d, h:i A'),
                    'is_internal' => (bool)$m['is_internal'],
                    'is_system' => (bool)$m['is_system']
                ];
            }

            // If there are no messages, put the description as the first message
            if (empty($messages)) {
                $created_time = new DateTime($ticket['created_at']);
                $messages[] = [
                    'sender' => $ticket['user'],
                    'sender_id' => $ticket['employee_id'],
                    'text' => nl2br(htmlspecialchars($ticket['description'])),
                    'time' => $created_time->format('M d, h:i A'),
                    'is_internal' => false,
                    'is_system' => false
                ];
            }

            $created_time = new DateTime($ticket['created_at']);
            $formatted_ticket = [
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
                'messages' => $messages
            ];
            
            echo json_encode(['success' => true, 'data' => $formatted_ticket]);
            break;

        case 'create_ticket':
            $category = $_POST['category'] ?? '';
            if ($category === 'Other') {
                $category = $_POST['custom_category'] ?? 'Other';
            }
            $subject = $_POST['subject'] ?? '';
            $description = $_POST['description'] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO support_tickets (employee_id, subject, category, description, status, it_unread) VALUES (?, ?, ?, ?, 'Open', 1)");
            $stmt->execute([$user_id, $subject, $category, $description]);
            $ticket_id = $pdo->lastInsertId();
            
            // Insert initial message
            $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message, is_internal) VALUES (?, ?, ?, 0)");
            $stmt->execute([$ticket_id, $user_id, $description]);
            
            echo json_encode(['success' => true, 'message' => 'Ticket created successfully']);
            break;

        case 'send_message':
            $ticket_id = $_POST['ticket_id'] ?? 0;
            $message = $_POST['message'] ?? '';
            $is_internal = (isset($_POST['is_internal']) && $_POST['is_internal'] === 'true') ? 1 : 0;
            
            if (!$is_it_staff) $is_internal = 0; // Enforce
            
            // Get ticket details to know creator
            $stmt = $pdo->prepare("SELECT employee_id FROM support_tickets WHERE id = ?");
            $stmt->execute([$ticket_id]);
            $ticket_creator = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message, is_internal) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ticket_id, $user_id, $message, $is_internal]);
            
            // Increment unread count based on actual creator vs other responder
            if ($user_id == $ticket_creator) {
                // Creator sent the message (always treated as Employee)
                $stmt = $pdo->prepare("UPDATE support_tickets SET it_unread = it_unread + 1 WHERE id = ?");
                $stmt->execute([$ticket_id]);
            } else {
                // Someone else sent the message (treated as IT/responder)
                if ($is_internal === 0) {
                    $stmt = $pdo->prepare("UPDATE support_tickets SET employee_unread = employee_unread + 1 WHERE id = ?");
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
            $ticket_id = $_POST['ticket_id'] ?? 0;
            
            $stmt = $pdo->prepare("UPDATE support_tickets SET assigned_to = ?, status = 'In Progress' WHERE id = ?");
            $stmt->execute([$user_id, $ticket_id]);
            
            echo json_encode(['success' => true]);
            break;

        case 'handover_ticket':
            if (!$is_it_staff) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $ticket_id = $_POST['ticket_id'] ?? 0;
            
            // Verify ownership
            $stmt = $pdo->prepare("SELECT assigned_to FROM support_tickets WHERE id = ?");
            $stmt->execute([$ticket_id]);
            $owner = $stmt->fetchColumn();
            if ($owner != $user_id && $user_role !== 'Admin') {
                echo json_encode(['success' => false, 'message' => 'Only the assigned agent can handover this ticket.']);
                exit;
            }
            
            $new_assignee_id = $_POST['new_assignee_id'] ?? 0;
            
            // Get new assignee name
            $stmt = $pdo->prepare("SELECT TRIM(CONCAT_WS(' ', first_name, middle_name, last_name)) FROM employees WHERE id = ?");
            $stmt->execute([$new_assignee_id]);
            $new_assignee_name = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("UPDATE support_tickets SET assigned_to = ? WHERE id = ?");
            $stmt->execute([$new_assignee_id, $ticket_id]);
            
            // Internal note
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
            $ticket_id = $_POST['ticket_id'] ?? 0;
            
            // Verify ownership and get created_at
            $stmt = $pdo->prepare("SELECT assigned_to, created_at FROM support_tickets WHERE id = ?");
            $stmt->execute([$ticket_id]);
            $t = $stmt->fetch();
            
            if ($t['assigned_to'] != $user_id && $user_role !== 'Admin') {
                echo json_encode(['success' => false, 'message' => 'Only the assigned agent can resolve this ticket.']);
                exit;
            }
            
            $created_at = $t['created_at'];
            
            $created_time = new DateTime($created_at);
            $now = new DateTime();
            $diff = $created_time->diff($now);
            
            $durationParts = [];
            if ($diff->d > 0) $durationParts[] = $diff->d . " days";
            if ($diff->h > 0) $durationParts[] = $diff->h . " hours";
            if ($diff->i > 0) $durationParts[] = $diff->i . " mins";
            $durationStr = empty($durationParts) ? "less than a minute" : implode(" ", $durationParts);
            
            $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'Resolved', resolved_by = ?, resolved_at = NOW(), resolution_duration = ? WHERE id = ?");
            $stmt->execute([$user_id, $durationStr, $ticket_id]);
            
            echo json_encode(['success' => true]);
            break;

        case 'close_ticket':
            if (!$is_it_staff) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $ticket_id = $_POST['ticket_id'] ?? 0;
            
            // Verify ownership and get created_at
            $stmt = $pdo->prepare("SELECT assigned_to, created_at FROM support_tickets WHERE id = ?");
            $stmt->execute([$ticket_id]);
            $t = $stmt->fetch();
            
            if ($t['assigned_to'] != $user_id && $user_role !== 'Admin') {
                echo json_encode(['success' => false, 'message' => 'Only the assigned agent can close this ticket.']);
                exit;
            }
            
            $created_at = $t['created_at'];
            $created_time = new DateTime($created_at);
            $now = new DateTime();
            $diff = $created_time->diff($now);
            
            $days = $diff->d;
            $hours = $diff->h;
            $mins = $diff->i;
            
            $durationStr = '';
            if ($days > 0) $durationStr .= "$days days ";
            if ($hours > 0) $durationStr .= "$hours hrs ";
            $durationStr .= "$mins mins";
            
            $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'Closed', resolved_by = ?, resolved_at = NOW(), resolution_duration = ? WHERE id = ?");
            $stmt->execute([$user_id, $durationStr, $ticket_id]);
            
            echo json_encode(['success' => true]);
            break;

        case 'reopen_ticket':
            $ticket_id = $_POST['ticket_id'] ?? 0;
            
            $stmt = $pdo->prepare("SELECT employee_id, resolution_duration, assigned_to FROM support_tickets WHERE id = ?");
            $stmt->execute([$ticket_id]);
            $tinfo = $stmt->fetch();
            
            if ($tinfo['employee_id'] != $user_id && $user_role !== 'Admin') {
                echo json_encode(['success' => false, 'message' => 'Only the ticket creator can reopen this ticket.']);
                exit;
            }
            
            $res_dur = $tinfo['resolution_duration'] ?? 'Unknown time';
            
            $agentName = "an unknown agent";
            if ($tinfo['assigned_to']) {
                $s2 = $pdo->prepare("SELECT TRIM(CONCAT_WS(' ', first_name, middle_name, last_name)) FROM employees WHERE id = ?");
                $s2->execute([$tinfo['assigned_to']]);
                $agentName = $s2->fetchColumn();
            }
            
            $stmt = $pdo->prepare("UPDATE support_tickets SET status = 'In Progress', reopen_count = reopen_count + 1, it_unread = it_unread + 1 WHERE id = ?");
            $stmt->execute([$ticket_id]);
            
            // Note visible to everyone
            $note = "Ticket Re-opened. Previously resolved by $agentName in $res_dur.";
            $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message, is_internal, is_system) VALUES (?, ?, ?, 0, 1)");
            $stmt->execute([$ticket_id, $user_id, $note]);
            
            echo json_encode(['success' => true]);
            break;

        case 'get_it_staff':
            // Get all IT Staff for handover
            $stmt = $pdo->prepare("SELECT e.id, TRIM(CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name)) as name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE d.name LIKE '%IT%' OR d.name LIKE '%Support%'");
            $stmt->execute();
            $staff = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $staff]);
            break;

        case 'get_dashboard_stats':
            if (!$is_it_staff) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            
            // Get ticket counts by status
            $stats = [
                'All' => 0,
                'Open' => 0,
                'In_Progress' => 0,
                'Resolved' => 0,
                'Closed' => 0
            ];
            
            // Count total
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets");
            $stmt->execute();
            $stats['All'] = (int)$stmt->fetchColumn();
            
            // Count by status
            $stmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM support_tickets GROUP BY status");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $status = $row['status'];
                if ($status == 'In Progress' || $status == 'In-Progress') {
                    $stats['In_Progress'] += (int)$row['cnt'];
                } else {
                    $stats[$status] = (int)$row['cnt'];
                }
            }
            
            // Get top agents who resolved tickets
            $stmt = $pdo->prepare("
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
            $stmt->execute();
            $top_resolvers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Map profile images correctly
            foreach ($top_resolvers as &$resolver) {
                $resolver['profile_img'] = $resolver['profile_img'] ? '../' . $resolver['profile_img'] : null;
            }
            
            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'top_resolvers' => $top_resolvers
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
?>
