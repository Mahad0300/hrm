<?php
// includes/email_helper.php

require_once __DIR__ . '/email_config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends a recruitment notification email to a candidate based on status transitions.
 *
 * @param int $candidate_id The candidate's database ID.
 * @param string $status The target status (Interview, Shortlisted, Hired, Rejected, Banned).
 * @param array $extra_data Optional data (e.g., ['date' => 'YYYY-MM-DD', 'time' => 'HH:MM']).
 * @return array ['status' => 'success'|'error', 'message' => '...']
 */
function sendCandidateStatusEmail($candidate_id, $status, $extra_data = []) {
    global $pdo;

    if (!$candidate_id || !$status) {
        return ['status' => 'error', 'message' => 'Candidate ID and status are required.'];
    }

    try {
        // 1. Fetch Candidate and Job Info
        $stmt = $pdo->prepare("
            SELECT c.name as candidate_name, c.email as candidate_email, j.title as job_title
            FROM candidates c
            LEFT JOIN jobs j ON c.job_id = j.id
            WHERE c.id = ? AND c.deleted_at IS NULL
        ");
        $stmt->execute([$candidate_id]);
        $cand = $stmt->fetch();

        if (!$cand) {
            return ['status' => 'error', 'message' => "Candidate with ID {$candidate_id} not found."];
        }

        if (empty($cand['candidate_email'])) {
            return ['status' => 'error', 'message' => 'Candidate email address is empty.'];
        }

        // 2. Map Status to HTML Template
        $template_map = [
            'interview'   => 'interview.html',
            'shortlisted' => 'shortlisted.html',
            'hired'       => 'hired.html',
            'rejected'    => 'rejection.html',
            'banned'      => 'ban.html'
        ];

        $status_key = strtolower(trim($status));
        if (!isset($template_map[$status_key])) {
            return ['status' => 'success', 'message' => "No email template mapped for status: '{$status}'."];
        }

        $template_file = __DIR__ . '/email_templates/' . $template_map[$status_key];
        if (!file_exists($template_file)) {
            return ['status' => 'error', 'message' => "Email template file not found: '{$template_file}'."];
        }

        $html_content = file_get_contents($template_file);

        // 3. Resolve Date and Time Placeholders
        $date_input = $extra_data['date'] ?? null;
        $time_input = $extra_data['time'] ?? null;

        // If not supplied, try to pull the latest interview session info
        if (!$date_input || !$time_input) {
            $iStmt = $pdo->prepare("
                SELECT DATE(interview_date) as idate, TIME(interview_date) as itime 
                FROM interviews 
                WHERE candidate_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $iStmt->execute([$candidate_id]);
            $interview = $iStmt->fetch();

            if ($interview) {
                if (!$date_input) {
                    $date_input = $interview['idate'];
                }
                if (!$time_input) {
                    $time_input = $interview['itime'];
                }
            }
        }

        // Apply fallback formatting
        $formatted_date = '';
        if ($date_input) {
            $formatted_date = date('F j, Y', strtotime($date_input));
        } else {
            $formatted_date = ($status_key === 'hired') ? 'To be finalized during onboarding' : 'To be decided';
        }

        $formatted_time = '';
        if ($time_input) {
            $formatted_time = date('h:i A', strtotime($time_input));
        } else {
            $formatted_time = ($status_key === 'hired') ? '09:00 AM' : 'To be decided';
        }

        // 4. Perform Placeholder Replacements
        $logo_url = defined('HRM_EMAIL_LOGO_URL') ? HRM_EMAIL_LOGO_URL : (defined('HRM_BASE_URL') ? HRM_BASE_URL : 'http://localhost/hrmnew') . '/images/loginimage/logo.png';
        
        // Convert relative logo paths to absolute URLs
        $html_content = str_replace('../../images/loginimage/logo.png', $logo_url, $html_content);

        $replacements = [
            '{{candidate_name}}' => htmlspecialchars($cand['candidate_name']),
            '{{job_title}}'      => htmlspecialchars($cand['job_title'] ?: 'Designation'),
            '{{interview_date}}' => htmlspecialchars($formatted_date),
            '{{interview_time}}' => htmlspecialchars($formatted_time)
        ];

        foreach ($replacements as $placeholder => $value) {
            $html_content = str_replace($placeholder, $value, $html_content);
        }

        // 5. Send Email via PHPMailer
        $mail = new PHPMailer(true);

        // Server Settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($cand['candidate_email'], $cand['candidate_name']);

        // Subject Mapping
        $subject_map = [
            'interview'   => 'Interview Schedule Invitation - Richmond Tech Group',
            'shortlisted' => 'Recruitment Update: Shortlisted - Richmond Tech Group',
            'hired'       => 'Congratulations! Onboarding & Offer Details - Richmond Tech Group',
            'rejected'    => 'Recruitment Status Update - Richmond Tech Group',
            'banned'      => 'Application Status Update - Richmond Tech Group'
        ];

        $mail->isHTML(true);
        $mail->Subject = $subject_map[$status_key] ?? 'Recruitment Application Update';
        $mail->Body    = $html_content;
        $mail->AltBody = "Dear {$cand['candidate_name']},\n\nYour application status for the position of {$cand['job_title']} has been updated to: " . ucfirst($status) . ".";

        $mail->send();
        return ['status' => 'success', 'message' => 'Email successfully sent.'];

    } catch (Exception $e) {
        return ['status' => 'error', 'message' => "Mailer Exception: " . $mail->ErrorInfo];
    } catch (\PDOException $e) {
        return ['status' => 'error', 'message' => "Database Error: " . $e->getMessage()];
    } catch (\Throwable $e) {
        return ['status' => 'error', 'message' => "Generic Error: " . $e->getMessage()];
    }
}
