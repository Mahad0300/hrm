-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 23, 2026 at 05:02 PM
-- Server version: 10.11.11-MariaDB-ubu2204
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `newh_hrm`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `type` enum('IMPORTANT','CELEBRATION','UPDATE','HOLIDAY') DEFAULT 'UPDATE',
  `target_depts` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `is_notified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `shift_id` int(11) DEFAULT NULL,
  `clock_in` datetime DEFAULT NULL,
  `clock_out` datetime DEFAULT NULL,
  `working_hours` varchar(20) DEFAULT NULL,
  `status` enum('ON TIME','LATE IN','HALF DAY','ABSENT','WEEKEND','HOLIDAY','LEAVE') DEFAULT 'ON TIME',
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banking_info`
--

CREATE TABLE `banking_info` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `bank_name` varchar(150) DEFAULT NULL,
  `account_type` varchar(50) DEFAULT 'IBN',
  `account_title` varchar(150) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `branch_info` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `cnic_number` varchar(25) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `applied_date` date DEFAULT NULL,
  `status` enum('New','Shortlisted','Interview','Offer','Hired','Rejected','Duplicated','Banned') NOT NULL DEFAULT 'New',
  `duplicate_of` int(11) DEFAULT NULL,
  `duplicate_reason` varchar(255) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_walk_in` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidate_answers`
--

CREATE TABLE `candidate_answers` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `answer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidate_history`
--

CREATE TABLE `candidate_history` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `status_from` varchar(50) DEFAULT NULL,
  `status_to` varchar(50) NOT NULL,
  `feedback` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `manager` int(11) DEFAULT NULL,
  `head` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `education_experience`
--

CREATE TABLE `education_experience` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `qualification` varchar(150) DEFAULT NULL,
  `degree_cert` varchar(150) DEFAULT NULL,
  `university` varchar(150) DEFAULT NULL,
  `expertise` text DEFAULT NULL,
  `last_employer` varchar(150) DEFAULT NULL,
  `last_job_title` varchar(150) DEFAULT NULL,
  `exp_from` date DEFAULT NULL,
  `exp_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `biometric_id` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','HR','Employee') DEFAULT 'Employee',
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `cnic_number` varchar(25) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact` varchar(25) DEFAULT NULL,
  `emergency_relation` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `reports_to` int(11) DEFAULT NULL,
  `shift_id` int(11) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `job_type` enum('Permanent','Probation','Internship') DEFAULT 'Permanent',
  `salary` decimal(12,2) DEFAULT 0.00,
  `joining_date` date DEFAULT NULL,
  `status` enum('Pending','Active','On Leave','Terminated','Exit') DEFAULT 'Pending',
  `id_card_path` varchar(255) DEFAULT NULL,
  `other_docs` text DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `target_dept` varchar(100) DEFAULT NULL,
  `show_in_announcement` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `is_notified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_access_meta`
--

CREATE TABLE `hr_access_meta` (
  `id` tinyint(1) NOT NULL DEFAULT 1,
  `version` int(11) NOT NULL DEFAULT 1,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_page_permissions`
--

CREATE TABLE `hr_page_permissions` (
  `id` int(11) NOT NULL,
  `page_key` varchar(80) NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 1,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `can_export` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `interviewer_id` int(11) DEFAULT NULL,
  `interview_date` datetime NOT NULL,
  `interview_type` varchar(255) DEFAULT 'Onsite',
  `location` text DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled','Rescheduled') DEFAULT 'Scheduled',
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Close') NOT NULL DEFAULT 'Active',
  `posted_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_questions`
--

CREATE TABLE `job_questions` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `question_text` varchar(255) NOT NULL,
  `answer_type` varchar(50) DEFAULT 'TEXT INPUT',
  `is_required` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kpi_goals`
--

CREATE TABLE `kpi_goals` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `goal_name` varchar(255) NOT NULL,
  `target_score` int(11) NOT NULL DEFAULT 100,
  `achieved_score` int(11) NOT NULL DEFAULT 0,
  `reviewer_comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kpi_reviews`
--

CREATE TABLE `kpi_reviews` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `period` enum('Monthly','Quarterly','Annual') NOT NULL,
  `review_date` date NOT NULL,
  `overall_rating` decimal(4,2) DEFAULT NULL,
  `status` enum('Excelling','Good','On Track','Below Target','Poor') DEFAULT 'On Track',
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_note` text DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `days_per_year` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'System',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_recipients`
--

CREATE TABLE `notification_recipients` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `month_year` char(7) NOT NULL,
  `basic_salary` decimal(12,2) NOT NULL,
  `allowances` decimal(12,2) DEFAULT 0.00,
  `deductions` decimal(12,2) DEFAULT 0.00,
  `net_payable` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'Bank Transfer',
  `status` enum('Paid','Pending') DEFAULT 'Pending',
  `paid_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `house_rent` decimal(12,2) DEFAULT 0.00,
  `utility` decimal(12,2) DEFAULT 0.00,
  `fuel` decimal(12,2) DEFAULT 0.00,
  `mobile` decimal(12,2) DEFAULT 0.00,
  `medical` decimal(12,2) DEFAULT 0.00,
  `leaves_count` int(11) DEFAULT 0,
  `lates_count` int(11) DEFAULT 0,
  `halfdays_count` int(11) DEFAULT 0,
  `loan_deduction` decimal(12,2) DEFAULT 0.00,
  `provident_fund` decimal(12,2) DEFAULT 0.00,
  `professional_tax` decimal(12,2) DEFAULT 0.00,
  `other_deduction` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

CREATE TABLE `policies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` enum('Draft','Active') DEFAULT 'Active',
  `created_by` int(11) DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_history`
--

CREATE TABLE `salary_history` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `type` enum('Increment','Decrement') NOT NULL,
  `previous_salary` decimal(12,2) NOT NULL,
  `new_salary` decimal(12,2) NOT NULL,
  `amount_change` decimal(12,2) NOT NULL,
  `change_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `grace_time` int(11) DEFAULT 0,
  `halfday_hours` decimal(4,2) DEFAULT 0.00,
  `timing` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `target_dept_id` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `status` enum('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `closed_by` int(11) DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `resolution_duration` varchar(100) DEFAULT NULL,
  `reopen_count` int(11) DEFAULT 0,
  `employee_unread` int(11) DEFAULT 0,
  `it_unread` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_internal` tinyint(1) DEFAULT 0,
  `is_system` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `selector` varchar(255) NOT NULL,
  `hashed_validator` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_date` (`created_at`),
  ADD KEY `idx_log_emp` (`employee_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ann_type` (`type`),
  ADD KEY `idx_ann_dates` (`start_date`,`end_date`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emp_date` (`employee_id`,`date`),
  ADD KEY `idx_attendance_date` (`date`),
  ADD KEY `idx_attendance_status` (`status`),
  ADD KEY `shift_id` (`shift_id`);

--
-- Indexes for table `banking_info`
--
ALTER TABLE `banking_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id_2` (`employee_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_candidate_status` (`status`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `candidate_answers`
--
ALTER TABLE `candidate_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `candidate_history`
--
ALTER TABLE `candidate_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dept_manager` (`manager`),
  ADD KEY `idx_dept_head` (`head`);

--
-- Indexes for table `education_experience`
--
ALTER TABLE `education_experience`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id_2` (`employee_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_emp_status` (`status`),
  ADD KEY `idx_emp_dept` (`department_id`),
  ADD KEY `idx_emp_role` (`role`),
  ADD KEY `idx_emp_name` (`last_name`,`first_name`),
  ADD KEY `shift_id` (`shift_id`),
  ADD KEY `idx_emp_reports_to` (`reports_to`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_date` (`event_date`);

--
-- Indexes for table `hr_access_meta`
--
ALTER TABLE `hr_access_meta`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hr_page_permissions`
--
ALTER TABLE `hr_page_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_hr_page_permissions_key` (`page_key`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_interview_status` (`status`),
  ADD KEY `idx_interview_date` (`interview_date`),
  ADD KEY `idx_interviewer` (`interviewer_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_status` (`status`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `job_questions`
--
ALTER TABLE `job_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `kpi_goals`
--
ALTER TABLE `kpi_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `review_id` (`review_id`);

--
-- Indexes for table `kpi_reviews`
--
ALTER TABLE `kpi_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kpi_period` (`period`),
  ADD KEY `idx_kpi_date` (`review_date`),
  ADD KEY `idx_kpi_reviewer` (`reviewer_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_leave_status` (`status`),
  ADD KEY `idx_leave_dates` (`start_date`,`end_date`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `leave_type_id` (`leave_type_id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notif_emp` (`notification_id`,`employee_id`),
  ADD KEY `idx_notif_unread` (`employee_id`,`is_read`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emp_month` (`employee_id`,`month_year`),
  ADD KEY `idx_payroll_month` (`month_year`),
  ADD KEY `idx_payroll_status` (`status`);

--
-- Indexes for table `policies`
--
ALTER TABLE `policies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salary_history`
--
ALTER TABLE `salary_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `meta_key` (`meta_key`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `selector` (`selector`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banking_info`
--
ALTER TABLE `banking_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidate_answers`
--
ALTER TABLE `candidate_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidate_history`
--
ALTER TABLE `candidate_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `education_experience`
--
ALTER TABLE `education_experience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_page_permissions`
--
ALTER TABLE `hr_page_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_questions`
--
ALTER TABLE `job_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kpi_goals`
--
ALTER TABLE `kpi_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kpi_reviews`
--
ALTER TABLE `kpi_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_history`
--
ALTER TABLE `salary_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `banking_info`
--
ALTER TABLE `banking_info`
  ADD CONSTRAINT `banking_info_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `candidate_answers`
--
ALTER TABLE `candidate_answers`
  ADD CONSTRAINT `candidate_answers_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `education_experience`
--
ALTER TABLE `education_experience`
  ADD CONSTRAINT `education_experience_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_emp_reports_to` FOREIGN KEY (`reports_to`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `interviews_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interviews_ibfk_2` FOREIGN KEY (`interviewer_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_questions`
--
ALTER TABLE `job_questions`
  ADD CONSTRAINT `job_questions_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kpi_goals`
--
ALTER TABLE `kpi_goals`
  ADD CONSTRAINT `kpi_goals_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `kpi_reviews` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kpi_reviews`
--
ALTER TABLE `kpi_reviews`
  ADD CONSTRAINT `kpi_reviews_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kpi_reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD CONSTRAINT `notification_recipients_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_recipients_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_history`
--
ALTER TABLE `salary_history`
  ADD CONSTRAINT `salary_history_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD CONSTRAINT `ticket_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
