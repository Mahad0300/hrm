-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2026 at 10:32 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hrm`
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

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `employee_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 22, 'User Login', '[Authentication] Syed Bukhari logged in as Employee', '::1', '2026-04-30 19:08:20'),
(2, 1, 'Deleted Announcement', '[Announcements] ID: 11', '::1', '2026-04-30 19:23:31'),
(3, 1, 'Created Announcement', '[Announcements] Title: add new announcement ', '::1', '2026-04-30 19:30:29'),
(4, 1, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'add new announcement \' (Reference ID: 12)', '::1', '2026-04-30 20:36:34'),
(5, 1, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'test title\'', '::1', '2026-04-30 20:38:50'),
(6, 1, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'test title test\'', '::1', '2026-04-30 20:39:45'),
(7, 1, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'test title test\'', '::1', '2026-04-30 20:40:20'),
(8, 1, 'Updated Event', '[Event Calendar] Modified the details of event: \'Test Event Notification\' (Scheduled for 2026-05-02)', '::1', '2026-04-30 20:44:04'),
(9, 1, 'Updated Event', '[Event Calendar] Modified the details of event: \'Test Event Notification Test\' (Scheduled for May 02, 2026)', '::1', '2026-04-30 20:46:37'),
(10, 1, 'Created Event', '[Event Calendar] Scheduled a new company event: \'yteyuqw\' on May 01, 2026', '::1', '2026-04-30 20:47:12'),
(11, 1, 'Deleted Event', '[Event Calendar] Permanently removed the event: \'Test Event Notification Test\'', '::1', '2026-04-30 20:47:25'),
(12, 1, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'test activity ewrwewre\'', '::1', '2026-04-30 20:50:38'),
(13, 1, 'Scheduled Interview', '[Job Management] Scheduled an interview session for candidate \'adas\' on May 01, 2026 at 20:00.', '::1', '2026-04-30 20:53:25'),
(14, 1, 'Rescheduled Interview', '[Job Management] Rescheduled the interview session for \'adas\' to May 01, 2026 at 20:00.', '::1', '2026-04-30 20:55:13'),
(15, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'adas\' from \'Interview\' to \'Offer\'.', '::1', '2026-04-30 20:55:25'),
(16, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'adas\' from \'Offer\' to \'Hired\'.', '::1', '2026-04-30 20:55:31'),
(17, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'adas\' from \'Hired\' to \'Banned\'.', '::1', '2026-04-30 20:55:44'),
(18, 1, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Syed Bukhari (Period: quarterly)', '::1', '2026-04-30 21:04:14'),
(19, 1, 'Deleted Performance Review', '[KPI Management] Permanently removed the performance appraisal record for team member: Syed Bukhari', '::1', '2026-04-30 21:04:45'),
(20, 1, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Syed Bukhari (Period: quarterly)', '::1', '2026-04-30 21:05:05'),
(21, 22, 'Submitted Leave Request', '[Leave Management] Applied for Sick Leave from May 04, 2026 to May 05, 2026', '::1', '2026-04-30 21:09:28'),
(22, 22, 'Updated Leave Request', '[Leave Management] Updated Sick Leave request for the period: May 04, 2026 to May 05, 2026', '::1', '2026-04-30 21:09:54'),
(23, 1, 'Approved Leave', '[Leave Management] Formally approved the Sick Leave request for team member: Syed Bukhari', '::1', '2026-04-30 21:10:17'),
(24, 1, 'Rejected Leave', '[Leave Management] Declined the Sick Leave request for team member: Syed Bukhari', '::1', '2026-04-30 21:10:35'),
(25, 1, 'Updated Employee Profile', '[Employees] Finalized and updated the profile details for team member: Syed Bukhari', '::1', '2026-04-30 21:11:53'),
(26, 1, 'Deleted Employee Profile', '[Employees] Moved team member: Syed Bukhari to the Exit list.', '::1', '2026-04-30 21:12:14'),
(27, 22, 'Updated Personal Profile', '[Employees] Updated their personal profile details and changed their profile picture.', '::1', '2026-04-30 21:16:55'),
(28, 1, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Test Test', '::1', '2026-04-30 21:26:35'),
(29, 1, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Test Test', '::1', '2026-04-30 21:31:29'),
(30, 1, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: test test', '::1', '2026-04-30 21:54:58'),
(31, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: test test', '::1', '2026-04-30 21:58:03'),
(32, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: test test', '::1', '2026-04-30 22:34:35'),
(33, 1, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: user user', '::1', '2026-04-30 22:39:03'),
(34, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-01 16:18:20'),
(35, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-01 16:20:23'),
(36, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'adas\' from \'Banned\' to \'Banned\'.', '::1', '2026-05-01 17:24:03'),
(37, 1, 'Approved Leave', '[Leave Management] Formally approved the Sick Leave request for team member: Syed Bukhari', '::1', '2026-05-01 18:33:50'),
(38, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-01 18:40:31'),
(39, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-05 17:57:42'),
(40, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-06 15:29:13'),
(41, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-06 15:38:10'),
(42, 1, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'Announcement Title Test\'', '::1', '2026-05-06 17:49:34'),
(43, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Syed Bukhari', '::1', '2026-05-13 00:18:06'),
(44, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-06 21:07:58'),
(45, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-07 15:25:46'),
(46, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-07 15:25:56'),
(47, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-07 15:32:46'),
(48, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-07 15:32:55'),
(49, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-07 15:49:08'),
(50, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-07 16:12:30'),
(51, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-07 22:04:52'),
(52, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-07 23:13:18'),
(53, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-11 15:23:20'),
(54, 33, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-11 16:11:24'),
(55, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Syed Bukhari', '::1', '2026-05-11 16:34:36'),
(56, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Syed Bukhari', '::1', '2026-05-11 18:33:17'),
(57, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-11 18:36:23'),
(58, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-12 15:43:08'),
(59, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Ahmed Khan', '::1', '2026-05-12 17:26:55'),
(60, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Ahmed Khan', '::1', '2026-05-12 17:27:11'),
(61, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-12 22:11:31'),
(62, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-13 00:03:02'),
(63, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-13 00:06:20'),
(64, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-13 00:06:37'),
(65, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-13 15:36:06'),
(66, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-13 16:44:31'),
(67, 1, 'Updated Job Status', '[Job Management] Changed status for \'test activity ewrwewre\' to \'Close\'.', '::1', '2026-05-13 17:00:42'),
(68, 1, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'testttt again Job Title\'', '::1', '2026-05-13 17:59:11'),
(69, 1, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'testttt again Job Title\'', '::1', '2026-05-13 17:59:28'),
(70, 1, 'Scheduled Interview', '[Job Management] Scheduled an interview session for candidate \'Test\' on May 13, 2026 at 10:00.', '::1', '2026-05-13 18:20:45'),
(71, 1, 'Rescheduled Interview', '[Job Management] Rescheduled the interview session for \'Test\' to May 14, 2026 at 23:00.', '::1', '2026-05-13 18:21:33'),
(72, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Test\' from \'Interview\' to \'Offer\'.', '::1', '2026-05-13 18:22:14'),
(73, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Test\' from \'Offer\' to \'Hired\'.', '::1', '2026-05-13 18:22:57'),
(74, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-13 19:39:52'),
(75, 1, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'Announcement Title Test\'', '::1', '2026-05-13 20:32:23'),
(76, 1, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'Announcement Title Test\'', '::1', '2026-05-13 20:46:20'),
(77, 1, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'Announcement Title Test\'', '::1', '2026-05-13 20:47:53'),
(78, 1, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'Announcement Title Test\'', '::1', '2026-05-13 20:48:31'),
(79, 1, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'Announcement Title Test Test\'', '::1', '2026-05-13 20:49:00'),
(80, 1, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'Announcement Title Test Test\'', '::1', '2026-05-13 20:49:12'),
(81, 1, 'Deleted Event', '[Event Calendar] Permanently removed the event: \'test event \'', '::1', '2026-05-13 20:55:50'),
(82, 1, 'Created Event', '[Event Calendar] Scheduled a new company event: \'Event Title Test\' on May 18, 2026', '::1', '2026-05-13 21:11:36'),
(83, 33, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-13 21:13:29'),
(84, 1, 'Updated Event', '[Event Calendar] Modified the details of event: \'Event Title Test\' (Scheduled for May 18, 2026)', '::1', '2026-05-13 21:14:23'),
(85, 1, 'Deleted Event', '[Event Calendar] Permanently removed the event: \'Event Title Test\'', '::1', '2026-05-13 21:18:59'),
(86, 1, 'Created Event', '[Event Calendar] Scheduled a new company event: \'Event Title Test\' on May 18, 2026', '::1', '2026-05-13 21:21:13'),
(87, 1, 'Deleted Event', '[Event Calendar] Permanently removed the event: \'Event Title Test\'', '::1', '2026-05-13 21:21:34'),
(88, 1, 'Created Event', '[Event Calendar] Scheduled a new company event: \'Event Title  Test\' on May 18, 2026', '::1', '2026-05-13 21:22:52'),
(89, 1, 'Updated Event', '[Event Calendar] Modified the details of event: \'Event Title  Test\' (Scheduled for May 18, 2026)', '::1', '2026-05-13 21:23:16'),
(90, 1, 'Updated Event', '[Event Calendar] Modified the details of event: \'Event Title  Test\' (Scheduled for May 18, 2026)', '::1', '2026-05-13 21:31:02'),
(91, 1, 'Updated Event', '[Event Calendar] Modified the details of event: \'Event Title  Test\' (Scheduled for May 18, 2026)', '::1', '2026-05-13 21:33:59'),
(92, 1, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Ayesha Siddiqui (Period: monthly)', '::1', '2026-05-13 22:02:48'),
(93, 1, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Ayesha Siddiqui (Period: monthly)', '::1', '2026-05-13 22:15:20'),
(94, 1, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Ayesha Siddiqui (Period: monthly)', '::1', '2026-05-13 22:15:45'),
(95, 1, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Ayesha Siddiqui (Period: monthly)', '::1', '2026-05-13 22:16:14'),
(96, 1, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Ayesha Siddiqui (Period: monthly)', '::1', '2026-05-13 22:23:28'),
(97, 1, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Ayesha Siddiqui (Period: monthly)', '::1', '2026-05-13 22:32:14'),
(98, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Shayan Shaikh', '::1', '2026-05-13 22:49:43'),
(99, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-14 15:08:33'),
(100, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-14 15:26:42'),
(101, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-14 16:04:43'),
(102, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-14 16:21:13'),
(103, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-14 20:21:16'),
(104, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-15 17:37:59'),
(105, 22, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-05-15 18:35:15');

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

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `type`, `target_depts`, `start_date`, `end_date`, `created_by`, `is_notified`, `created_at`, `updated_at`, `deleted_at`) VALUES
(10, 'Announcement Title Test', 'Test Discription', 'CELEBRATION', 'Manager,Production,Marketing,Chat Support', '2026-05-06', '2026-05-08', 1, 1, '2026-04-30 18:03:53', '2026-05-13 20:32:23', '2026-05-13 20:32:23'),
(11, 'adasd', 'assas', 'UPDATE', 'everyone', '2026-05-01', '2026-05-02', 1, 0, '2026-04-30 19:21:06', '2026-04-30 19:23:31', '2026-04-30 19:23:31'),
(12, 'add new announcement ', 'test', 'CELEBRATION', 'everyone', '2026-04-30', '2026-05-07', 1, 1, '2026-04-30 19:30:29', '2026-04-30 20:36:34', '2026-04-30 20:36:34'),
(13, 'test title test', 'test', 'IMPORTANT', 'everyone', '2026-04-30', '2026-05-07', 1, 1, '2026-04-30 20:38:50', '2026-04-30 20:40:20', '2026-04-30 20:40:20'),
(14, 'Announcement Title Test', 'Announcement Description Test', 'IMPORTANT', 'everyone', '2026-05-14', '2026-05-21', 1, 1, '2026-05-13 20:46:20', '2026-05-13 20:47:53', '2026-05-13 20:47:53'),
(15, 'Announcement Title Test Test', 'Announcement Description Test', 'IMPORTANT', 'Manager,Production', '2026-05-14', '2026-05-21', 1, 1, '2026-05-13 20:48:31', '2026-05-13 20:49:12', NULL),
(16, 'Dynamic Update Test', 'This is a test announcement to verify dashboard live feed.', 'IMPORTANT', 'everyone', '2026-05-14', '2026-05-21', 1, 0, '2026-05-14 18:46:28', NULL, NULL);

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

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `date`, `shift_id`, `clock_in`, `clock_out`, `working_hours`, `status`, `message`, `created_at`, `updated_at`) VALUES
(6, 22, '2026-05-06', 6, '2026-05-06 20:19:46', '2026-05-07 05:00:50', '8h 41m', 'LATE IN', NULL, '2026-05-06 15:19:46', '2026-05-07 00:00:50'),
(7, 22, '2026-05-07', 6, '2026-05-07 20:05:28', '2026-05-08 04:42:54', '8h 37m', 'ON TIME', 'wqeeweweq', '2026-05-07 15:05:28', '2026-05-07 16:40:11'),
(11, 22, '2026-05-08', 6, '2026-05-09 00:37:01', '2026-05-09 03:42:04', '3h 05m', 'HALF DAY', 'asdsaadsdasdasd', '2026-05-08 19:37:01', '2026-05-07 16:40:01'),
(17, 22, '2026-05-11', 7, '2026-05-11 21:00:00', '2026-05-12 06:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-12 16:20:07', '2026-05-11 20:56:58'),
(25, 33, '2026-05-08', 7, '2026-05-08 21:00:00', '2026-05-09 06:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-08 00:09:29', '2026-05-08 00:11:01'),
(26, 37, '2026-05-08', 5, '2026-05-08 19:00:00', '2026-05-09 04:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-08 00:09:29', '2026-05-08 00:11:01'),
(27, 29, '2026-05-08', 6, '2026-05-08 20:00:00', '2026-05-09 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-08 00:09:29', '2026-05-08 00:11:01'),
(28, 25, '2026-05-08', 5, '2026-05-08 19:00:00', '2026-05-09 04:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-08 00:09:29', '2026-05-08 00:11:01'),
(33, 33, '2026-05-12', 7, NULL, NULL, '—', 'HOLIDAY', NULL, '2026-05-11 16:09:52', NULL),
(34, 37, '2026-05-12', 5, NULL, NULL, '—', 'HOLIDAY', NULL, '2026-05-11 16:09:52', NULL),
(35, 29, '2026-05-12', 6, NULL, NULL, '—', 'HOLIDAY', NULL, '2026-05-11 16:09:52', NULL),
(36, 25, '2026-05-12', 5, NULL, NULL, '—', 'HOLIDAY', NULL, '2026-05-11 16:09:52', NULL),
(37, 33, '2026-05-11', 7, '2026-05-11 21:00:00', '2026-05-12 06:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 16:11:40', '2026-05-11 20:54:05'),
(38, 22, '2026-01-21', 7, '2026-01-21 20:55:00', '2026-01-22 06:05:00', '9h 10m', 'ON TIME', 'Routine check-in', '2026-05-11 18:29:39', NULL),
(39, 22, '2026-01-22', 7, '2026-01-22 21:45:00', '2026-01-23 06:00:00', '8h 15m', 'LATE IN', 'Traffic delay', '2026-05-11 18:29:39', NULL),
(40, 22, '2026-01-23', 7, '2026-01-23 21:00:00', '2026-01-24 00:30:00', '3h 30m', 'HALF DAY', 'Family emergency', '2026-05-11 18:29:39', NULL),
(41, 22, '2026-01-26', 7, '2026-01-26 20:50:00', '2026-01-27 06:00:00', '9h 10m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(42, 22, '2026-01-27', 7, NULL, NULL, NULL, 'ABSENT', 'Sick Leave', '2026-05-11 18:29:39', NULL),
(43, 22, '2026-01-28', 7, '2026-01-28 21:10:00', '2026-01-29 05:55:00', '8h 45m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(44, 22, '2026-01-29', 7, '2026-01-29 22:10:00', '2026-01-30 06:00:00', '7h 50m', 'LATE IN', NULL, '2026-05-11 18:29:39', NULL),
(45, 22, '2026-01-30', 7, '2026-01-30 20:58:00', '2026-01-31 06:02:00', '9h 04m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(46, 22, '2026-02-02', 7, '2026-02-02 20:45:00', '2026-02-03 06:00:00', '9h 15m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(47, 22, '2026-02-03', 7, '2026-02-03 21:05:00', '2026-02-04 06:10:00', '9h 05m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(48, 22, '2026-02-04', 7, '2026-02-04 22:30:00', '2026-02-05 06:00:00', '7h 30m', 'LATE IN', 'Bike issue', '2026-05-11 18:29:39', NULL),
(49, 22, '2026-02-05', 7, '2026-02-05 21:00:00', '2026-02-06 00:45:00', '3h 45m', 'HALF DAY', 'Personal work', '2026-05-11 18:29:39', NULL),
(50, 22, '2026-02-06', 7, '2026-02-06 20:55:00', '2026-02-07 05:50:00', '8h 55m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(51, 22, '2026-02-09', 7, '2026-02-09 21:15:00', '2026-02-10 06:00:00', '8h 45m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(52, 22, '2026-02-10', 7, NULL, NULL, NULL, 'ABSENT', NULL, '2026-05-11 18:29:39', NULL),
(53, 22, '2026-02-11', 7, '2026-02-11 20:50:00', '2026-02-12 06:00:00', '9h 10m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(54, 22, '2026-02-12', 7, '2026-02-12 21:40:00', '2026-02-13 06:00:00', '8h 20m', 'LATE IN', NULL, '2026-05-11 18:29:39', NULL),
(55, 22, '2026-02-13', 7, '2026-02-13 21:00:00', '2026-02-14 06:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(56, 22, '2026-02-16', 7, '2026-02-16 20:55:00', '2026-02-17 06:00:00', '9h 05m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(57, 22, '2026-02-17', 7, '2026-02-17 21:00:00', '2026-02-18 01:00:00', '4h 00m', 'ON TIME', 'Left early with permission', '2026-05-11 18:29:39', NULL),
(58, 22, '2026-02-18', 7, '2026-02-18 22:15:00', '2026-02-19 06:00:00', '7h 45m', 'LATE IN', NULL, '2026-05-11 18:29:39', NULL),
(59, 22, '2026-02-19', 7, '2026-02-19 21:10:00', '2026-02-20 05:55:00', '8h 45m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(60, 22, '2026-02-20', 7, '2026-02-20 21:00:00', '2026-02-21 00:15:00', '3h 15m', 'HALF DAY', NULL, '2026-05-11 18:29:39', NULL),
(61, 22, '2026-02-23', 7, '2026-02-23 20:40:00', '2026-02-24 06:10:00', '9h 30m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(62, 22, '2026-02-24', 7, '2026-02-24 21:20:00', '2026-02-25 06:00:00', '8h 40m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(63, 22, '2026-02-25', 7, '2026-02-25 21:55:00', '2026-02-26 06:00:00', '8h 05m', 'LATE IN', NULL, '2026-05-11 18:29:39', NULL),
(64, 22, '2026-02-26', 7, '2026-02-26 21:00:00', '2026-02-27 05:50:00', '8h 50m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(65, 22, '2026-02-27', 7, NULL, NULL, NULL, 'ABSENT', NULL, '2026-05-11 18:29:39', NULL),
(66, 22, '2026-03-02', 7, '2026-03-02 20:55:00', '2026-03-03 06:00:00', '9h 05m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(67, 22, '2026-03-03', 7, '2026-03-03 21:10:00', '2026-03-04 06:00:00', '8h 50m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(68, 22, '2026-03-04', 7, '2026-03-04 22:20:00', '2026-03-05 06:00:00', '7h 40m', 'LATE IN', NULL, '2026-05-11 18:29:39', NULL),
(69, 22, '2026-03-05', 7, '2026-03-05 21:00:00', '2026-03-06 00:30:00', '3h 30m', 'HALF DAY', NULL, '2026-05-11 18:29:39', NULL),
(70, 22, '2026-03-06', 7, '2026-03-06 20:50:00', '2026-03-07 06:10:00', '9h 20m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(71, 22, '2026-03-09', 7, '2026-03-09 21:45:00', '2026-03-10 06:00:00', '8h 15m', 'LATE IN', NULL, '2026-05-11 18:29:39', NULL),
(72, 22, '2026-03-10', 7, '2026-03-10 20:55:00', '2026-03-11 06:05:00', '9h 10m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(73, 22, '2026-03-11', 7, '2026-03-11 21:00:00', '2026-03-12 06:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(74, 22, '2026-03-12', 7, NULL, NULL, NULL, 'ABSENT', NULL, '2026-05-11 18:29:39', NULL),
(75, 22, '2026-03-13', 7, '2026-03-13 21:10:00', '2026-03-14 06:00:00', '8h 50m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(76, 22, '2026-03-16', 7, '2026-03-16 20:45:00', '2026-03-17 06:00:00', '9h 15m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(77, 22, '2026-03-17', 7, '2026-03-17 21:00:00', '2026-03-18 06:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(78, 22, '2026-03-18', 7, '2026-03-18 22:50:00', '2026-03-19 06:00:00', '7h 10m', 'LATE IN', 'Personal reason', '2026-05-11 18:29:39', NULL),
(79, 22, '2026-03-19', 7, '2026-03-19 21:15:00', '2026-03-20 06:00:00', '8h 45m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(80, 22, '2026-03-20', 7, '2026-03-20 21:00:00', '2026-03-21 00:45:00', '3h 45m', 'HALF DAY', NULL, '2026-05-11 18:29:39', NULL),
(81, 22, '2026-03-23', 7, '2026-03-23 20:55:00', '2026-03-24 06:00:00', '9h 05m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(82, 22, '2026-03-24', 7, '2026-03-24 21:05:00', '2026-03-25 06:10:00', '9h 05m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(83, 22, '2026-03-25', 7, '2026-03-25 21:50:00', '2026-03-26 06:00:00', '8h 10m', 'LATE IN', NULL, '2026-05-11 18:29:39', NULL),
(84, 22, '2026-03-26', 7, '2026-03-26 21:00:00', '2026-03-27 05:55:00', '8h 55m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(85, 22, '2026-03-27', 7, NULL, NULL, NULL, 'ABSENT', NULL, '2026-05-11 18:29:39', NULL),
(86, 22, '2026-03-30', 7, '2026-03-30 20:50:00', '2026-03-31 06:00:00', '9h 10m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(87, 22, '2026-03-31', 7, '2026-03-31 21:15:00', '2026-04-01 06:00:00', '8h 45m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(88, 22, '2026-04-01', 7, '2026-04-01 22:15:00', '2026-04-02 06:00:00', '7h 45m', 'LATE IN', NULL, '2026-05-11 18:29:39', NULL),
(89, 22, '2026-04-02', 7, '2026-04-02 21:00:00', '2026-04-03 01:00:00', '4h 00m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(90, 22, '2026-04-03', 7, '2026-04-03 20:55:00', '2026-04-04 06:00:00', '9h 05m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(91, 22, '2026-04-06', 7, '2026-04-06 20:45:00', '2026-04-07 06:00:00', '9h 15m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(92, 22, '2026-04-07', 7, '2026-04-07 21:10:00', '2026-04-08 06:00:00', '8h 50m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(93, 22, '2026-04-08', 7, '2026-04-08 22:45:00', '2026-04-09 06:00:00', '7h 15m', 'LATE IN', NULL, '2026-05-11 18:29:39', NULL),
(94, 22, '2026-04-09', 7, '2026-04-09 21:00:00', '2026-04-10 00:30:00', '3h 30m', 'HALF DAY', NULL, '2026-05-11 18:29:39', NULL),
(95, 22, '2026-04-10', 7, '2026-04-10 21:00:00', '2026-04-11 06:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(96, 22, '2026-04-13', 7, NULL, NULL, NULL, 'ABSENT', NULL, '2026-05-11 18:29:39', NULL),
(97, 22, '2026-04-14', 7, '2026-04-14 20:55:00', '2026-04-15 06:05:00', '9h 10m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(98, 22, '2026-04-15', 7, '2026-04-15 21:15:00', '2026-04-16 06:00:00', '8h 45m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(99, 22, '2026-04-16', 7, '2026-04-16 22:00:00', '2026-04-17 06:00:00', '8h 00m', 'LATE IN', NULL, '2026-05-11 18:29:39', NULL),
(100, 22, '2026-04-17', 7, '2026-04-17 21:00:00', '2026-04-18 06:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(101, 22, '2026-04-20', 7, '2026-04-20 20:50:00', '2026-04-21 06:00:00', '9h 10m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(102, 22, '2026-04-21', 7, '2026-04-21 21:10:00', '2026-04-22 06:00:00', '8h 50m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(103, 22, '2026-04-22', 7, '2026-04-22 22:30:00', '2026-04-23 06:00:00', '7h 30m', 'LATE IN', NULL, '2026-05-11 18:29:39', NULL),
(104, 22, '2026-04-23', 7, '2026-04-23 21:00:00', '2026-04-24 00:15:00', '3h 15m', 'HALF DAY', NULL, '2026-05-11 18:29:39', NULL),
(105, 22, '2026-04-24', 7, '2026-04-24 21:00:00', '2026-04-25 06:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(106, 22, '2026-04-27', 7, '2026-04-27 20:55:00', '2026-04-28 06:00:00', '9h 05m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(107, 22, '2026-04-28', 7, NULL, NULL, NULL, 'ABSENT', NULL, '2026-05-11 18:29:39', NULL),
(108, 22, '2026-04-29', 7, '2026-04-29 21:15:00', '2026-04-30 06:00:00', '8h 45m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(109, 22, '2026-04-30', 7, '2026-04-30 22:10:00', '2026-05-01 06:00:00', '7h 50m', 'LATE IN', NULL, '2026-05-11 18:29:39', NULL),
(110, 22, '2026-05-01', 7, '2026-05-01 21:00:00', '2026-05-02 06:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(111, 22, '2026-05-04', 7, '2026-05-04 20:45:00', '2026-05-05 06:00:00', '9h 15m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(112, 22, '2026-05-05', 7, '2026-05-05 21:05:00', '2026-05-06 06:10:00', '9h 05m', 'ON TIME', NULL, '2026-05-11 18:29:39', NULL),
(114, 28, '2026-05-11', 5, '2026-05-11 19:00:00', '2026-05-12 04:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 20:56:58', NULL),
(115, 34, '2026-05-11', 5, '2026-05-11 19:00:00', '2026-05-12 04:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 20:56:58', NULL),
(116, 31, '2026-05-11', 5, '2026-05-11 19:00:00', '2026-05-12 04:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 20:56:58', NULL),
(117, 32, '2026-05-11', 6, '2026-05-11 20:00:00', '2026-05-12 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 20:56:58', NULL),
(118, 37, '2026-05-11', 5, '2026-05-11 19:00:00', '2026-05-12 04:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 20:56:58', NULL),
(119, 35, '2026-05-11', 6, '2026-05-11 20:00:00', '2026-05-12 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 20:56:58', NULL),
(120, 29, '2026-05-11', 6, '2026-05-11 20:00:00', '2026-05-12 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 20:56:58', NULL),
(121, 26, '2026-05-11', 6, '2026-05-11 20:00:00', '2026-05-12 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 20:56:58', NULL),
(122, 25, '2026-05-11', 5, '2026-05-11 19:00:00', '2026-05-12 04:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 20:56:58', NULL),
(123, 27, '2026-05-11', 5, '2026-05-11 19:00:00', '2026-05-12 04:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 20:56:58', NULL),
(124, 36, '2026-05-11', 7, '2026-05-11 21:00:00', '2026-05-12 06:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 20:56:58', NULL),
(125, 30, '2026-05-11', 7, '2026-05-11 21:00:00', '2026-05-12 06:00:00', '9h 00m', 'ON TIME', NULL, '2026-05-11 20:56:58', NULL),
(131, 22, '2026-05-12', 7, NULL, NULL, NULL, 'ABSENT', NULL, '2026-05-14 17:56:56', NULL),
(132, 22, '2026-05-13', 7, NULL, NULL, NULL, 'ABSENT', NULL, '2026-05-14 17:56:56', NULL),
(133, 22, '2026-05-14', 7, '2026-05-14 22:56:56', '2026-05-14 22:56:56', '0h 00m', 'ABSENT', 'Auto-closed: Missed check-out', '2026-05-14 17:56:56', '2026-05-15 18:57:19'),
(134, 22, '2026-05-15', 7, '2026-05-15 23:57:19', NULL, NULL, 'LATE IN', NULL, '2026-05-15 18:57:19', NULL);

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

--
-- Dumping data for table `banking_info`
--

INSERT INTO `banking_info` (`id`, `employee_id`, `bank_name`, `account_type`, `account_title`, `account_number`, `branch_info`, `updated_at`) VALUES
(52, 22, 'Bank Alfalah', 'IBN', 'Syed Mahad Bukhari', '444444444444', 'Hydri', NULL),
(62, 25, 'Bank Islami', 'IBN', 'test', 'test', 'test', NULL),
(64, 26, 'Habib Metro', 'IBN', 'test', 'test', 'test', NULL),
(66, 27, 'Soneri', 'IBN', 'test', '787897989879898778', 'test', NULL),
(70, 28, '', 'IBN', 'Ahmed Ali Khan', '789465123789', 'Gulshan Iqbal', NULL),
(72, 38, 'Meezan', 'IBN', 'Shayan Shaikh', '789546213456978', 'PIB', NULL);

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
  `status` enum('New','Interview','Offer','Hired','Rejected','Duplicated','Banned') DEFAULT 'New',
  `duplicate_of` int(11) DEFAULT NULL,
  `duplicate_reason` varchar(255) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `name`, `email`, `phone`, `cnic_number`, `address`, `job_id`, `applied_date`, `status`, `duplicate_of`, `duplicate_reason`, `resume_path`, `created_at`, `updated_at`, `deleted_at`) VALUES
(17, 'Test Full Name', 'testing@gmail.com', '1231-2123123', '87954-6210021-3', 'Test Residential Address ', 9, '2026-04-30', 'Interview', NULL, NULL, 'uploads/candidates/resumes/RES_69f38bd341c3b.pdf', '2026-04-30 17:05:23', '2026-04-30 17:07:36', NULL),
(18, 'adas', 'sa@gmail', '4322-2222222', '43544-1433443-4', 'fdaads', 10, '2026-05-01', 'Banned', NULL, NULL, 'uploads/candidates/resumes/RES_69f3c10bba1c5.pdf', '2026-04-30 20:52:27', '2026-05-01 17:24:03', NULL),
(19, 'Test', 'test@gmail.com', '8974-5623145', '46597-8465213-1', 'test Address', 11, '2026-05-13', 'Hired', NULL, NULL, 'uploads/candidates/resumes/RES_6a04c0ae834db.pdf', '2026-05-13 18:19:26', '2026-05-13 18:22:57', NULL);

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

--
-- Dumping data for table `candidate_answers`
--

INSERT INTO `candidate_answers` (`id`, `candidate_id`, `question_text`, `answer`, `created_at`) VALUES
(61, 17, 'What is your current salary?', 'What is your current salary? * Test', '2026-04-30 17:05:23'),
(62, 17, 'How many years of experience do you have?', 'How many years of experience do you have? * Test', '2026-04-30 17:05:23'),
(63, 17, 'Portfolio Link', 'Portfolio Link * Test', '2026-04-30 17:05:23'),
(64, 17, 'LinkedIn Profile', 'LinkedIn Profile * Test', '2026-04-30 17:05:23'),
(65, 17, 'When can you start?', 'When can you start? * test', '2026-04-30 17:05:23'),
(66, 17, 'Add Custom Question', 'Add Custom Question * Test', '2026-04-30 17:05:23'),
(67, 18, 'What is your current salary?', 'asdfsdaffa', '2026-04-30 20:52:27'),
(68, 19, 'What is your current salary?', 'test', '2026-05-13 18:19:26'),
(69, 19, 'How many years of experience do you have?', 'test', '2026-05-13 18:19:26'),
(70, 19, 'Portfolio Link', 'test', '2026-05-13 18:19:26'),
(71, 19, 'LinkedIn Profile', 'test', '2026-05-13 18:19:26'),
(72, 19, 'When can you start?', 'test', '2026-05-13 18:19:26'),
(73, 19, 'Test job ', 'test', '2026-05-13 18:19:26');

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

--
-- Dumping data for table `candidate_history`
--

INSERT INTO `candidate_history` (`id`, `candidate_id`, `status_from`, `status_to`, `feedback`, `created_by`, `created_at`) VALUES
(16, 17, 'New', 'Interview', 'Interview scheduled for April 30-2026, 8:00 PM. Interview Notes / Feedback Test', 1, '2026-04-30 17:07:36'),
(17, 17, 'Interview', 'Interview', 'Interview rescheduled. Previous: April 30-2026, 8:00 PM. New: May 1-2026, 9:00 PM. Notes: Interview Notes / Feedback Test', 1, '2026-04-30 17:34:27'),
(18, 18, 'New', 'Interview', 'Interview scheduled for May 1-2026, 8:00 PM. qrweqrqwe', 1, '2026-04-30 20:53:25'),
(19, 18, 'Interview', 'Interview', 'Interview rescheduled. Previous: May 1-2026, 8:00 PM. New: May 2-2026, 9:00 PM. Notes: qrweqrqwe', 1, '2026-04-30 20:53:38'),
(20, 18, 'Interview', 'Interview', 'Interview rescheduled. Previous: May 2-2026, 9:00 PM. New: May 1-2026, 8:00 PM. Notes: qrweqrqwe dsas', 1, '2026-04-30 20:55:13'),
(21, 18, 'Interview', 'Offer', 'adssaaas', 1, '2026-04-30 20:55:25'),
(22, 18, 'Offer', 'Hired', 'addsa', 1, '2026-04-30 20:55:31'),
(23, 18, 'Hired', 'Banned', '', 1, '2026-04-30 20:55:44'),
(24, 18, 'Banned', 'Banned', '', 1, '2026-05-01 17:24:03'),
(25, 19, 'New', 'Interview', 'Interview scheduled for May 13-2026, 10:00 AM. Move  New to Interview', 1, '2026-05-13 18:20:45'),
(26, 19, 'Interview', 'Interview', 'Interview rescheduled. Previous: May 13-2026, 10:00 AM. New: May 14-2026, 11:00 PM. Notes: Move  New to Interview Edit', 1, '2026-05-13 18:21:33'),
(27, 19, 'Interview', 'Offer', 'Move Interview to Offer', 1, '2026-05-13 18:22:14'),
(28, 19, 'Offer', 'Hired', 'Move Offer to Hired', 1, '2026-05-13 18:22:57');

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

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `manager`, `head`, `created_at`, `updated_at`, `deleted_at`) VALUES
(8, 'Manager', NULL, NULL, '2026-04-15 21:18:52', NULL, NULL),
(9, 'Production', 22, 29, '2026-04-15 21:18:59', '2026-05-13 16:43:39', NULL),
(10, 'Marketing', 28, 31, '2026-04-15 21:19:09', '2026-05-13 16:43:07', NULL),
(11, 'Chat Support', 28, 33, '2026-04-15 21:19:47', '2026-05-13 16:43:01', NULL),
(12, 'Test 1', NULL, NULL, '2026-04-15 21:19:53', '2026-04-15 21:19:58', '2026-04-15 21:19:58');

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

--
-- Dumping data for table `education_experience`
--

INSERT INTO `education_experience` (`id`, `employee_id`, `qualification`, `degree_cert`, `university`, `expertise`, `last_employer`, `last_job_title`, `exp_from`, `exp_to`) VALUES
(52, 22, 'Intermediate', 'Certificate', 'College', 'Development', 'Test Last employer', 'Test Last designation', NULL, NULL),
(62, 25, 'test', 'test', 'test', 'test', 'test', 'test', NULL, NULL),
(64, 26, 'test', 'test', 'test', 'test', 'test', 'test', NULL, NULL),
(66, 27, 'test', 'test', 'test', 'test', 'test', 'test', NULL, NULL),
(70, 28, 'Intermediate', 'Test', 'test', 'test', 'test', 'test', NULL, NULL),
(72, 38, 'intermediate', 'Degree', 'College', 'Wordpress', 'Test', 'Test', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
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

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `password`, `role`, `gender`, `dob`, `phone`, `cnic_number`, `address`, `emergency_contact`, `emergency_relation`, `department_id`, `shift_id`, `job_title`, `job_type`, `salary`, `joining_date`, `status`, `id_card_path`, `other_docs`, `resume_path`, `profile_pic`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'System', '', 'Admin', 'admin@gmail.com', '$2y$12$RKq6kP5En4KGCUYd3.hBIuE1WdPKNb7GFcnlE21gAgoiEA0i0noeS', 'Admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0.00, '2026-04-06', 'Active', NULL, NULL, NULL, NULL, '2026-04-06 17:51:22', NULL, NULL),
(22, 'Syed', 'Mahad', 'Bukhari', 'mahad@gmail.com', '$2y$10$MJSzzQkWueYLdZlP6A3pjOF2FtmO3h8IEpKJ./Gj0Q4dQG6LXX2D2', 'Employee', 'Male', '2002-07-05', '2222-2222227', '1111111111111', 'North Nazimabad', '33333333333', 'Test Emergency contact relation', 8, 7, 'Frontend Developer', 'Permanent', 40000.00, '2026-01-20', 'Active', 'uploads/employees/id_cards/EMP_69dffe42dcfe4.pdf', NULL, 'uploads/employees/resumes/EMP_69dffe42dd1fa.pdf', 'uploads/employees/profiles/user_22_69f3c6c79e71e.png', '2026-04-15 21:08:18', '2026-05-11 18:33:17', NULL),
(25, 'test', 'test', 'test', 'test@gmail.com', '$2y$10$X4oArjIECxAau3.mp4y99.Pt.y5dt42xOj/ghJb4RbUN9N7WgYsyK', 'Employee', 'Male', '2000-01-02', '2222-2222222', '1111111111111', 'test', '33333333333', 'Test Emergency contact relation', 9, 5, 'test', 'Permanent', 65000.00, '2026-05-04', 'Active', 'uploads/employees/id_cards/EMP_69f3cfb2604a1.pdf', NULL, 'uploads/employees/resumes/EMP_69f3cfb2606a4.pdf', NULL, '2026-04-30 21:54:58', '2026-04-30 21:58:03', NULL),
(26, 'test', 'test', 'test', 'test1@gmail.com', '$2y$10$SR6ZJqPiUKin/28qO3Kw3OwX3/Xugu2A3v1VfJ0mAX8XYN5l.C1km', 'Employee', 'Male', '2002-05-05', '2222-2222222', '1111111111111', 'north', '33333333333', 'testttt', 10, 6, 'test', 'Probation', 45000.00, '2026-05-05', 'Active', 'uploads/employees/id_cards/EMP_69f3d853ab1b8.pdf', NULL, 'uploads/employees/resumes/EMP_69f3d853ab3b9.pdf', NULL, '2026-04-30 22:31:47', '2026-04-30 22:34:35', NULL),
(27, 'user', '', 'user', 'test2@gmail.com', '$2y$10$zG/p6Il95cae.1MWZllMyek46hyMsmafgaR8XeIRxay54zzOa68KW', 'Employee', 'Male', '2003-05-07', '3333-3333333', '4444444444444', 'test', '55555555555', 'test', 11, 5, 'Job Title Test', 'Permanent', 90000.00, '2026-05-04', 'Active', 'uploads/employees/id_cards/EMP_69f3da0719fe3.pdf', NULL, 'uploads/employees/resumes/EMP_69f3da071a1c4.pdf', NULL, '2026-04-30 22:39:03', NULL, NULL),
(28, 'Ahmed', 'Ali', 'Khan', 'ahmed@gmail.com', '$2y$10$zG/p6Il95cae.1MWZllMyek46hyMsmafgaR8XeIRxay54zzOa68KW', 'Employee', 'Male', '1995-03-12', '0300-1111111', '42101-1111111-1', 'Gulshan-e-Iqbal, Karachi', '0300-9999999', 'Brother', 8, 5, 'Backend Developer', 'Permanent', 30000.00, '2026-05-01', 'Active', NULL, NULL, NULL, NULL, '2026-05-07 22:46:12', '2026-05-12 17:26:55', NULL),
(29, 'Sara', '', 'Ahmed', 'sara@gmail.com', '$2y$10$zG/p6Il95cae.1MWZllMyek46hyMsmafgaR8XeIRxay54zzOa68KW', 'Employee', 'Female', '1998-06-25', '0300-2222222', '42101-2222222-2', 'DHA Phase 6, Karachi', '0300-8888888', 'Father', 9, 6, 'UI/UX Designer', 'Permanent', 60000.00, '2026-05-02', 'Active', NULL, NULL, NULL, NULL, '2026-05-07 22:46:12', NULL, NULL),
(30, 'Zain', 'Ul', 'Abidin', 'zain@gmail.com', '$2y$10$zG/p6Il95cae.1MWZllMyek46hyMsmafgaR8XeIRxay54zzOa68KW', 'Employee', 'Male', '1992-11-10', '0300-3333333', '42101-3333333-3', 'North Karachi', '0300-7777777', 'Mother', 10, 7, 'Full Stack Developer', 'Permanent', 85000.00, '2026-05-03', 'Active', NULL, NULL, NULL, NULL, '2026-05-07 22:46:12', NULL, NULL),
(31, 'Fatima', '', 'Zehra', 'fatima@gmail.com', '$2y$10$zG/p6Il95cae.1MWZllMyek46hyMsmafgaR8XeIRxay54zzOa68KW', 'Employee', 'Female', '1996-08-15', '0300-4444444', '42101-4444444-4', 'Malir Cantt, Karachi', '0300-6666666', 'Husband', 11, 5, 'QA Engineer', 'Probation', 45000.00, '2026-05-04', 'Active', NULL, NULL, NULL, NULL, '2026-05-07 22:46:12', NULL, NULL),
(32, 'Hamza', 'Bin', 'Tariq', 'hamza@gmail.com', '$2y$10$zG/p6Il95cae.1MWZllMyek46hyMsmafgaR8XeIRxay54zzOa68KW', 'Employee', 'Male', '1994-12-30', '0300-5555555', '42101-5555555-5', 'Federal B Area, Karachi', '0300-5555555', 'Sister', 8, 6, 'SEO Specialist', 'Permanent', 40000.00, '2026-05-05', 'Active', NULL, NULL, NULL, NULL, '2026-05-07 22:46:12', NULL, NULL),
(33, 'Ayesha', '', 'Siddiqui', 'ayesha@gmail.com', '$2y$12$RKq6kP5En4KGCUYd3.hBIuE1WdPKNb7GFcnlE21gAgoiEA0i0noeS', 'Employee', 'Female', '1997-04-22', '0300-6666666', '42101-6666666-6', 'Clifton, Karachi', '0300-4444444', 'Friend', 9, 7, 'Content Writer', 'Internship', 25000.00, '2026-05-06', 'Active', NULL, NULL, NULL, NULL, '2026-05-07 22:46:12', '2026-05-11 16:10:42', NULL),
(34, 'Bilal', '', 'Sheikh', 'bilal@gmail.com', '$2y$10$zG/p6Il95cae.1MWZllMyek46hyMsmafgaR8XeIRxay54zzOa68KW', 'Employee', 'Male', '1990-01-18', '0300-7777777', '42101-7777777-7', 'Garden West, Karachi', '0300-3333333', 'Wife', 10, 5, 'Project Manager', 'Permanent', 120000.00, '2026-05-07', 'Active', NULL, NULL, NULL, NULL, '2026-05-07 22:46:12', NULL, NULL),
(35, 'Nimra', 'Arshad', 'Rao', 'nimra@gmail.com', '$2y$10$zG/p6Il95cae.1MWZllMyek46hyMsmafgaR8XeIRxay54zzOa68KW', 'Employee', 'Female', '1999-09-09', '0300-8888888', '42101-8888888-8', 'PECHS Block 2, Karachi', '0300-2222222', 'Uncle', 11, 6, 'HR Executive', 'Permanent', 50000.00, '2026-05-08', 'Active', NULL, NULL, NULL, NULL, '2026-05-07 22:46:12', NULL, NULL),
(36, 'Usman', '', 'Ghani', 'usman@gmail.com', '$2y$10$zG/p6Il95cae.1MWZllMyek46hyMsmafgaR8XeIRxay54zzOa68KW', 'Employee', 'Male', '1993-02-14', '0300-9999999', '42101-9999999-9', 'Korangi, Karachi', '0300-1111111', 'Aunt', 8, 7, 'Network Engineer', 'Permanent', 70000.00, '2026-05-09', 'Active', NULL, NULL, NULL, NULL, '2026-05-07 22:46:12', NULL, NULL),
(37, 'Hina', '', 'Pervez', 'hina@gmail.com', '$2y$10$zG/p6Il95cae.1MWZllMyek46hyMsmafgaR8XeIRxay54zzOa68KW', 'HR', 'Female', '1991-05-20', '0300-0000000', '42101-0000000-0', 'Saddar, Karachi', '0300-0000000', 'Cousin', 9, 5, 'Marketing Head', 'Permanent', 95000.00, '2026-05-10', 'Active', NULL, NULL, NULL, NULL, '2026-05-07 22:46:12', '2026-05-11 21:14:28', NULL),
(38, 'Shayan', NULL, 'Shaikh', 'shayan@gmail.com', '$2y$10$aw1Kb0jIU2hOOBCgYt4AqeytXt4rnJ0pQ39LG6a9tdivrG8Xs41Km', 'Employee', 'Male', '2005-05-07', '5645-6465465', '7898987987987', 'PIB', '78546213456', 'Father', 9, 6, 'Wordpress Developer', 'Permanent', 60000.00, '2026-05-18', 'Active', 'uploads/employees/id_cards/EMP_6a04fd82901fe.jpeg', NULL, 'uploads/employees/resumes/EMP_6a04fd8290448.pdf', NULL, '2026-05-13 22:38:58', '2026-05-13 22:49:43', NULL);

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

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `category`, `target_dept`, `show_in_announcement`, `created_by`, `is_notified`, `created_at`, `updated_at`) VALUES
(16, 'yteyuqw', 'jkgdgsjsd', '2026-05-01', '20:00:00', 'Holiday', 'Production', 0, 1, 0, '2026-04-30 20:47:12', NULL),
(19, 'Event Title  Test', 'Description Test', '2026-05-18', '20:00:00', 'Holiday', 'Manager,Production', 1, 1, 1, '2026-05-13 21:22:52', '2026-05-13 21:33:59');

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

--
-- Dumping data for table `interviews`
--

INSERT INTO `interviews` (`id`, `candidate_id`, `interviewer_id`, `interview_date`, `interview_type`, `location`, `status`, `feedback`, `created_at`, `updated_at`) VALUES
(11, 17, NULL, '2026-05-01 21:00:00', 'Onsite', NULL, 'Scheduled', 'Interview Notes / Feedback Test', '2026-04-30 17:07:36', '2026-04-30 17:34:27'),
(12, 18, NULL, '2026-05-01 20:00:00', 'Onsite', NULL, 'Scheduled', 'qrweqrqwe dsas', '2026-04-30 20:53:25', '2026-04-30 20:55:13'),
(13, 19, NULL, '2026-05-14 23:00:00', 'Onsite', NULL, 'Scheduled', 'Move  New to Interview Edit', '2026-05-13 18:20:45', '2026-05-13 18:21:33');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `type` enum('Full-time','Part-time','Contract','Internship') DEFAULT 'Full-time',
  `description` text DEFAULT NULL,
  `status` enum('Active','Draft','Archived','Close') DEFAULT 'Active',
  `posted_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `department_id`, `location`, `type`, `description`, `status`, `posted_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9, 'Test Job', 11, 'North Nazimabad Block D, Near Ship Owner College at Hill View Apartment karachi.', 'Full-time', 'tes discription ', 'Active', '2026-04-30', '2026-04-30 17:00:46', NULL, NULL),
(10, 'test activity ewrwewre', 11, 'North Nazimabad Block D, Near Ship Owner College at Hill View Apartment karachi.', 'Full-time', 'discription', 'Close', '2026-05-01', '2026-04-30 20:48:53', '2026-05-13 17:00:42', NULL),
(11, 'testttt again Job Title', 11, 'North Nazimabad Block D, Near Ship Owner College at Hill View Apartment karachi.', 'Full-time', 'testttt again  Job Description\r\n', 'Active', '2026-05-13', '2026-05-13 17:59:11', '2026-05-13 17:59:28', NULL);

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

--
-- Dumping data for table `job_questions`
--

INSERT INTO `job_questions` (`id`, `job_id`, `question_text`, `answer_type`, `is_required`) VALUES
(30, 9, 'What is your current salary?', 'Text Answer', 1),
(31, 9, 'How many years of experience do you have?', 'Text Answer', 1),
(32, 9, 'Portfolio Link', 'Text Answer', 1),
(33, 9, 'LinkedIn Profile', 'Text Answer', 1),
(34, 9, 'When can you start?', 'Text Answer', 1),
(35, 9, 'Add Custom Question', 'Text Answer', 1),
(36, 10, 'What is your current salary?', 'Text Answer', 1),
(43, 11, 'What is your current salary?', 'Text Answer', 1),
(44, 11, 'How many years of experience do you have?', 'Text Answer', 1),
(45, 11, 'Portfolio Link', 'Text Answer', 1),
(46, 11, 'LinkedIn Profile', 'Text Answer', 1),
(47, 11, 'When can you start?', 'Text Answer', 1),
(48, 11, 'Test job ', 'Text Answer', 1);

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

--
-- Dumping data for table `kpi_goals`
--

INSERT INTO `kpi_goals` (`id`, `review_id`, `goal_name`, `target_score`, `achieved_score`, `reviewer_comment`) VALUES
(52, 10, 'Test', 100, 60, 'Answer'),
(53, 10, 'Test 1', 100, 95, 'Answer 1'),
(54, 10, 'Test 3', 100, 40, 'Answer 3'),
(55, 10, 'Test 4', 100, 65, 'Answer 4'),
(56, 10, 'Test 5', 100, 34, 'Answer 5');

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

--
-- Dumping data for table `kpi_reviews`
--

INSERT INTO `kpi_reviews` (`id`, `employee_id`, `reviewer_id`, `period`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`, `updated_at`) VALUES
(10, 33, 1, 'Monthly', '2026-05-14', 3.00, 'On Track', 'General Feedback / Comments Test\r\nGeneral Feedback / Comments Test 1\r\nGeneral Feedback / Comments Test 2\r\nGeneral Feedback / Comments Test 3', '2026-05-13 22:02:48', '2026-05-13 22:32:14');

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

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `employee_id`, `leave_type_id`, `start_date`, `end_date`, `reason`, `document_path`, `status`, `admin_note`, `applied_at`, `updated_at`) VALUES
(14, 22, 1, '2026-05-04', '2026-05-05', '2 days leave', 'uploads/leaves/leave_22_1777583394.pdf', 'Approved', 'Test', '2026-04-30 21:09:28', '2026-05-01 18:33:50');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `days_per_year` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `days_per_year`) VALUES
(1, 'Sick Leave', 20),
(2, 'Casual Leave', 15),
(3, 'Annual Leave', 15);

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

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `target_url`, `sender_id`, `type`, `created_at`, `updated_at`) VALUES
(22, 'New Job Application', 'New application received from Test Full Name for position: Test Job.', 'job-candidates.php', NULL, 'Recruitment', '2026-04-30 17:05:23', NULL),
(23, 'New Leave Request Submitted', 'Syed Mahad Bukhari has submitted a new leave request (Sick Leave, From 2026-05-01 to 2026-05-01). Awaiting your approval.', 'leave-management.php', 22, 'Leave', '2026-04-30 17:36:46', NULL),
(24, 'Leave Request Rejected', 'Your leave request has been Rejected. Remarks: Reject...', 'leave-management.php', 1, 'Leave', '2026-04-30 17:39:00', NULL),
(25, 'Leave Request Approved', 'Your leave request has been Approved. Remarks: approve...', 'leave-management.php', 1, 'Leave', '2026-04-30 17:39:29', NULL),
(26, 'Upcoming Event', 'New Event: Test Event Notification on 2026-05-02 at 21:00.', 'event-calendar.php', 1, 'System', '2026-04-30 17:55:59', NULL),
(27, 'New Company Announcement', 'New Announcement: Announcement Title Test. Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-04-30 18:03:53', NULL),
(28, 'Upcoming Event', 'New Event: test event  on 30 Apr, 2026 at 11:30 PM.', 'event-calendar.php', 1, 'System', '2026-04-30 18:07:39', NULL),
(29, 'New Leave Request Submitted', 'Syed Mahad Bukhari has submitted a new leave request (Casual Leave, From 2026-05-02 to 2026-05-05). Awaiting your approval.', 'leave-management.php', 22, 'Leave', '2026-04-30 18:08:35', NULL),
(30, 'New Leave Request Submitted', 'Syed Mahad Bukhari has submitted a new leave request (Annual Leave, From 01 May, 2026 to 05 May, 2026). Awaiting your approval.', 'leave-management.php', 22, 'Leave', '2026-04-30 18:10:50', NULL),
(31, 'New Company Announcement', 'New Announcement: add new announcement . Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-04-30 19:30:29', NULL),
(32, 'New Company Announcement', 'New Announcement: test title. Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-04-30 20:38:50', NULL),
(33, 'New Job Application', 'New application received from adas for position: test activity ewrwewre.', 'job-candidates.php', NULL, 'Recruitment', '2026-04-30 20:52:27', NULL),
(34, 'New Leave Request Submitted', 'Syed Mahad Bukhari has submitted a new leave request (Sick Leave, From 04 May, 2026 to 05 May, 2026). Awaiting your approval.', 'leave-management.php', 22, 'Leave', '2026-04-30 21:09:28', NULL),
(35, 'Leave Request Approved', 'Your leave request has been Approved. Remarks: Test...', 'leave-management.php', 1, 'Leave', '2026-04-30 21:10:17', NULL),
(36, 'Leave Request Rejected', 'Your leave request has been Rejected. Remarks: Test...', 'leave-management.php', 1, 'Leave', '2026-04-30 21:10:35', NULL),
(37, 'Leave Request Approved', 'Your leave request has been Approved. Remarks: Test...', 'leave-management.php', 1, 'Leave', '2026-05-01 18:33:50', NULL),
(38, 'New Job Application', 'New application received from Test for position: testttt again Job Title.', 'job-candidates.php', NULL, 'Recruitment', '2026-05-13 18:19:26', NULL),
(39, 'New Company Policy', 'New company policy available: Test. Please review it in Company Policies.', 'policies.php', 1, 'System', '2026-05-13 20:28:26', NULL),
(40, 'New Company Policy', 'New company policy available: Test. Please review it in Company Policies.', 'policies.php', 1, 'System', '2026-05-13 20:31:48', NULL),
(41, 'New Company Announcement', 'New Announcement: Announcement Title Test. Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-05-13 20:46:20', NULL),
(42, 'New Company Announcement', 'New Announcement: Announcement Title Test. Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-05-13 20:48:31', NULL),
(43, 'Upcoming Event', 'New Event: Event Title Test on 18 May, 2026 at 08:00 PM.', 'event-calendar.php', 1, 'System', '2026-05-13 21:11:36', NULL),
(44, 'Upcoming Event', 'New Event: Event Title Test on 18 May, 2026 at 08:00 PM.', 'event-calendar.php', 1, 'System', '2026-05-13 21:21:12', NULL),
(45, 'Upcoming Event', 'New Event: Event Title  Test on 18 May, 2026 at 08:00 PM.', 'event-calendar.php', 1, 'System', '2026-05-13 21:22:52', NULL),
(46, 'Event Updated', 'Event Updated: Event Title  Test is scheduled for 18 May, 2026 at 08:00 PM.', 'event-calendar.php', 1, 'System', '2026-05-13 21:23:16', NULL),
(47, 'Event Updated', 'Event Updated: Event Title  Test is scheduled for 18 May, 2026 at 08:00 PM.', 'event-calendar.php', 1, 'System', '2026-05-13 21:31:02', NULL),
(48, 'Event Updated', 'Event Updated: Event Title  Test is scheduled for 18 May, 2026 at 08:00 PM.', 'event-calendar.php', 1, 'System', '2026-05-13 21:33:59', NULL);

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

--
-- Dumping data for table `notification_recipients`
--

INSERT INTO `notification_recipients` (`id`, `notification_id`, `employee_id`, `is_read`, `read_at`) VALUES
(124, 31, 22, 1, '2026-05-01 18:29:33'),
(126, 33, 1, 1, '2026-05-01 18:31:16'),
(127, 34, 1, 1, '2026-05-01 18:31:15'),
(128, 35, 22, 1, '2026-05-01 18:29:33'),
(129, 36, 22, 1, '2026-05-01 18:29:32'),
(130, 37, 22, 1, '2026-05-01 18:34:30'),
(131, 38, 1, 0, NULL),
(132, 38, 37, 0, NULL),
(133, 39, 22, 1, '2026-05-13 20:28:37'),
(134, 39, 25, 0, NULL),
(135, 39, 26, 0, NULL),
(136, 39, 27, 0, NULL),
(137, 39, 28, 0, NULL),
(138, 39, 29, 0, NULL),
(139, 39, 30, 0, NULL),
(140, 39, 31, 0, NULL),
(141, 39, 32, 0, NULL),
(142, 39, 33, 0, NULL),
(143, 39, 34, 0, NULL),
(144, 39, 35, 0, NULL),
(145, 39, 36, 0, NULL),
(146, 40, 22, 0, NULL),
(147, 40, 25, 0, NULL),
(148, 40, 26, 0, NULL),
(149, 40, 27, 0, NULL),
(150, 40, 28, 0, NULL),
(151, 40, 29, 0, NULL),
(152, 40, 30, 0, NULL),
(153, 40, 31, 0, NULL),
(154, 40, 32, 0, NULL),
(155, 40, 33, 0, NULL),
(156, 40, 34, 0, NULL),
(157, 40, 35, 0, NULL),
(158, 40, 36, 0, NULL),
(159, 41, 22, 0, NULL),
(160, 41, 25, 0, NULL),
(161, 41, 26, 0, NULL),
(162, 41, 27, 0, NULL),
(163, 41, 28, 0, NULL),
(164, 41, 29, 0, NULL),
(165, 41, 30, 0, NULL),
(166, 41, 31, 0, NULL),
(167, 41, 32, 0, NULL),
(168, 41, 33, 0, NULL),
(169, 41, 34, 0, NULL),
(170, 41, 35, 0, NULL),
(171, 41, 36, 0, NULL),
(172, 41, 37, 0, NULL),
(173, 42, 22, 0, NULL),
(174, 42, 25, 0, NULL),
(175, 42, 26, 0, NULL),
(176, 42, 27, 0, NULL),
(177, 42, 28, 0, NULL),
(178, 42, 29, 0, NULL),
(179, 42, 30, 0, NULL),
(180, 42, 31, 0, NULL),
(181, 42, 32, 0, NULL),
(182, 42, 33, 0, NULL),
(183, 42, 34, 0, NULL),
(184, 42, 35, 0, NULL),
(185, 42, 36, 0, NULL),
(186, 42, 37, 0, NULL),
(187, 43, 25, 0, NULL),
(188, 43, 29, 0, NULL),
(189, 43, 33, 0, NULL),
(190, 43, 37, 0, NULL),
(191, 44, 25, 0, NULL),
(192, 44, 29, 0, NULL),
(193, 44, 33, 0, NULL),
(194, 44, 37, 0, NULL),
(195, 45, 25, 0, NULL),
(196, 45, 29, 0, NULL),
(197, 45, 33, 0, NULL),
(198, 45, 37, 0, NULL),
(199, 46, 22, 0, NULL),
(200, 46, 28, 0, NULL),
(201, 46, 32, 0, NULL),
(202, 46, 36, 0, NULL),
(203, 47, 25, 0, NULL),
(204, 47, 29, 0, NULL),
(205, 47, 33, 0, NULL),
(206, 47, 37, 0, NULL),
(207, 48, 22, 0, NULL),
(208, 48, 28, 0, NULL),
(209, 48, 32, 0, NULL),
(210, 48, 36, 0, NULL),
(211, 48, 25, 0, NULL),
(212, 48, 29, 0, NULL),
(213, 48, 33, 0, NULL),
(214, 48, 37, 0, NULL);

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
  `professional_tax` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`id`, `employee_id`, `month_year`, `basic_salary`, `allowances`, `deductions`, `net_payable`, `payment_method`, `status`, `paid_date`, `created_at`, `updated_at`, `house_rent`, `utility`, `fuel`, `mobile`, `medical`, `leaves_count`, `lates_count`, `halfdays_count`, `loan_deduction`, `provident_fund`, `professional_tax`) VALUES
(7, 22, '2026-05', 20000.00, 0.00, 2666.67, 37333.33, 'Bank Transfer', 'Paid', NULL, '2026-05-12 22:03:33', NULL, 8000.00, 4000.00, 2000.00, 2000.00, 4000.00, 1, 2, 2, 0.00, 0.00, 0.00),
(8, 22, '2026-04', 20000.00, 0.00, 5333.33, 34666.67, 'Bank Transfer', 'Paid', NULL, '2026-05-12 22:04:56', NULL, 8000.00, 4000.00, 2000.00, 2000.00, 4000.00, 2, 5, 2, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

CREATE TABLE `policies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` enum('Draft','Active','Archived') DEFAULT 'Active',
  `created_by` int(11) DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `policies`
--

INSERT INTO `policies` (`id`, `title`, `content`, `status`, `created_by`, `effective_date`, `created_at`, `updated_at`) VALUES
(7, 'Test', 'Test Content', 'Active', 1, '2026-05-14', '2026-05-13 20:31:48', NULL);

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

--
-- Dumping data for table `salary_history`
--

INSERT INTO `salary_history` (`id`, `employee_id`, `type`, `previous_salary`, `new_salary`, `amount_change`, `change_date`, `created_at`) VALUES
(10, 22, 'Increment', 0.00, 50000.00, 50000.00, '2026-04-15', '2026-04-15 21:20:32'),
(11, 22, 'Increment', 50000.00, 60000.00, 10000.00, '2026-04-15', '2026-04-15 21:21:05'),
(12, 22, 'Decrement', 60000.00, 40000.00, 20000.00, '2026-04-15', '2026-04-15 21:26:29'),
(13, 25, 'Increment', 0.00, 65000.00, 65000.00, '2026-05-01', '2026-04-30 21:58:03'),
(14, 26, 'Increment', 0.00, 45000.00, 45000.00, '2026-05-01', '2026-04-30 22:34:35'),
(15, 28, 'Decrement', 55000.00, 30000.00, 25000.00, '2026-05-12', '2026-05-12 17:26:55'),
(16, 38, 'Increment', 0.00, 60000.00, 60000.00, '2026-05-14', '2026-05-13 22:49:43');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `meta_key` varchar(255) NOT NULL,
  `meta_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `meta_key`, `meta_value`) VALUES
(1, 'payroll_start_day', '23'),
(2, 'payroll_end_day', '24');

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

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `name`, `start_time`, `end_time`, `grace_time`, `halfday_hours`, `timing`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 'A', '19:00:00', '04:00:00', 15, 4.00, '', '2026-04-15 21:16:41', NULL, NULL),
(6, 'B', '20:00:00', '05:00:00', 15, 4.00, '', '2026-04-15 21:17:22', '2026-05-06 15:17:33', NULL),
(7, 'D', '21:00:00', '06:00:00', 30, 4.00, '', '2026-04-15 21:18:12', '2026-05-14 17:04:17', NULL),
(8, 'Test', '20:15:00', '05:15:00', 25, 5.00, '', '2026-05-13 18:24:45', '2026-05-13 18:25:17', '2026-05-13 18:25:17');

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
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `status` enum('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `closed_by` int(11) DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `resolution_duration` varchar(100) DEFAULT NULL,
  `reopen_count` int(11) DEFAULT 0
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
  `attachment` varchar(255) DEFAULT NULL,
  `is_internal` tinyint(1) DEFAULT 0,
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
  ADD KEY `shift_id` (`shift_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_date` (`event_date`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `banking_info`
--
ALTER TABLE `banking_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `candidate_answers`
--
ALTER TABLE `candidate_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `candidate_history`
--
ALTER TABLE `candidate_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `education_experience`
--
ALTER TABLE `education_experience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `job_questions`
--
ALTER TABLE `job_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `kpi_goals`
--
ALTER TABLE `kpi_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `kpi_reviews`
--
ALTER TABLE `kpi_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=215;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `salary_history`
--
ALTER TABLE `salary_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL;

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
