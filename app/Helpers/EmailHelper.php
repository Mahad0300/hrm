<?php
/**
 * Email Configuration & Helper
 * From: includes/email_config.php + includes/email_helper.php
 */

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper
{
    private static string $baseUrl = '';
    private static string $logoUrl = '';

    /**
     * Initialize email configuration
     */
    public static function init(): void
    {
        if (empty(self::$baseUrl)) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $basePath = EnvHelper::get('HRM_BASE_PATH', '/hrmnew');
            $basePath = $basePath === '/' ? '' : rtrim($basePath, '/');
            self::$baseUrl = $protocol . '://' . $host . $basePath;
            self::$logoUrl = self::$baseUrl . '/public/images/loginimage/logo.png';
        }
    }

    /**
     * Get SMTP configuration from environment
     */
    private static function getSmtpConfig(): array
    {
        self::init();
        return [
            'host' => EnvHelper::get('SMTP_HOST', 'business95.web-hosting.com'),
            'port' => (int)EnvHelper::get('SMTP_PORT', 465),
            'user' => EnvHelper::get('SMTP_USER', 'recruitment@richmondtechgroup.com'),
            'password' => EnvHelper::get('SMTP_PASSWORD', '$Rtg123!@#$'),
            'secure' => EnvHelper::get('SMTP_SECURE', 'ssl'),
            'from_email' => EnvHelper::get('SMTP_FROM_EMAIL', 'recruitment@richmondtechgroup.com'),
            'from_name' => EnvHelper::get('SMTP_FROM_NAME', 'Richmond Tech Group'),
        ];
    }

    /**
     * Format shift time range "8:00 PM - 5:00 AM"
     */
    public static function formatShiftTimeRange(?string $start, ?string $end): string
    {
        if (!$start || !$end) {
            return '';
        }

        $startTs = strtotime($start);
        $endTs = strtotime($end);
        if (!$startTs || !$endTs) {
            return '';
        }

        return date('g:i A', $startTs) . ' - ' . date('g:i A', $endTs);
    }

    /**
     * Build shift labels for emails
     */
    public static function buildShiftEmailLabels(array $shift): array
    {
        $range = self::formatShiftTimeRange($shift['start_time'] ?? null, $shift['end_time'] ?? null);
        $name = trim((string)($shift['name'] ?? ''));

        return [
            'shift_name' => $range !== '' ? trim($name . ' (' . $range . ')') : $name,
            'reporting_time' => $range,
        ];
    }

    /**
     * Send recruitment status email to candidate
     */
    public static function sendCandidateStatusEmail(\PDO $pdo, int $candidateId, string $status, array $extraData = []): array
    {
        if (!$candidateId || !$status) {
            return ['status' => 'error', 'message' => 'Candidate ID and status are required.'];
        }

        try {
            // Fetch Candidate and Job Info
            $stmt = $pdo->prepare("
                SELECT c.name as candidate_name, c.email as candidate_email, j.title as job_title, j.id as job_id
                FROM candidates c
                LEFT JOIN jobs j ON c.job_id = j.id
                WHERE c.id = ? AND c.deleted_at IS NULL
            ");
            $stmt->execute([$candidateId]);
            $cand = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$cand) {
                return ['status' => 'error', 'message' => "Candidate with ID {$candidateId} not found."];
            }

            if (empty($cand['candidate_email'])) {
                return ['status' => 'error', 'message' => 'Candidate email address is empty.'];
            }

            // Map Status to HTML Template
            $templateMap = [
                'interview'   => 'interview.html',
                'shortlisted' => 'shortlisted.html',
                'hired'       => 'hired.html',
                'rejected'    => 'rejection.html',
                'banned'      => 'ban.html'
            ];

            $statusKey = strtolower(trim($status));
            if (!isset($templateMap[$statusKey])) {
                return ['status' => 'success', 'message' => "No email template mapped for status: '{$status}'."];
            }

            $templateFile = (defined('VIEW_DIR') ? VIEW_DIR : (defined('ROOT_DIR') ? ROOT_DIR : dirname(__DIR__, 2)) . '/views') . '/emails/' . $templateMap[$statusKey];
            if (!file_exists($templateFile)) {
                return ['status' => 'error', 'message' => "Email template file not found: '{$templateFile}'."];
            }

            $htmlContent = file_get_contents($templateFile);

            // Replace placeholders
            $interviewMode = (isset($extraData['interview_type']) && $extraData['interview_type'] === 'Online') ? 'Online Interview' : 'Onsite Interview';
            $joiningDate = !empty($extraData['date']) ? date('M d, Y', strtotime($extraData['date'])) : date('M d, Y');
            $reportingTime = !empty($extraData['reporting_time']) 
                ? $extraData['reporting_time'] 
                : (!empty($extraData['time']) ? date('g:i A', strtotime($extraData['time'])) : '09:00 AM');

            $htmlContent = str_replace([
                '{{CANDIDATE_NAME}}',
                '{{candidate_name}}',
                '{{JOB_TITLE}}',
                '{{job_title}}',
                '{{interview_date}}',
                '{{interview_time}}',
                '{{joining_date}}',
                '{{reporting_time}}',
                '{{INTERVIEW_MODE}}',
                'Onsite Interview',
            ], [
                $cand['candidate_name'],
                $cand['candidate_name'],
                $cand['job_title'] ?? 'Position',
                $cand['job_title'] ?? 'Position',
                !empty($extraData['date']) ? date('M d, Y', strtotime($extraData['date'])) : date('M d, Y'),
                !empty($extraData['time']) ? date('g:i A', strtotime($extraData['time'])) : '10:00 AM',
                $joiningDate,
                $reportingTime,
                $interviewMode,
                $interviewMode,
            ], $htmlContent);

            // Send via PHPMailer
            return self::sendEmail(
                $cand['candidate_email'],
                "Application Status Update: {$status}",
                $htmlContent
            );

        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Email send failed: ' . $e->getMessage()];
        }
    }

    /**
     * Generic email sending via PHPMailer
     */
    public static function sendEmail(string $toEmail, string $subject, string $htmlBody): array
    {
        try {
            $config = self::getSmtpConfig();
            
            $mailer = new PHPMailer(true);
            $mailer->CharSet = 'UTF-8';
            $mailer->Timeout = 5; // 5-second socket connection timeout
            $mailer->isSMTP();
            $mailer->Host = $config['host'];
            $mailer->Port = $config['port'];
            $mailer->SMTPAuth = true;
            $mailer->Username = $config['user'];
            $mailer->Password = $config['password'];
            $mailer->SMTPSecure = $config['secure'];

            $mailer->setFrom($config['from_email'], $config['from_name']);
            $mailer->addAddress($toEmail);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $htmlBody;

            $mailer->send();
            
            return ['status' => 'success', 'message' => 'Email sent successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
?>
