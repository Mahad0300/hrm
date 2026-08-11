-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2026 at 07:58 PM
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
(0, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-20 16:27:51'),
(1, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-10 22:31:31'),
(2, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-10 22:36:52'),
(3, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-10 22:39:16'),
(4, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Muhammad Abiden', '::1', '2026-06-10 22:59:38'),
(5, 2, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Syed Bukhari', '::1', '2026-06-10 23:00:18'),
(6, 1, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Shayan Shaikh', '::1', '2026-06-10 23:10:20'),
(7, 2, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Faisal Khan', '::1', '2026-06-10 23:19:11'),
(8, 1, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Owais Ahmed', '::1', '2026-06-10 23:31:35'),
(9, 2, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Affan Ahmed', '::1', '2026-06-10 23:36:20'),
(10, 1, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Anoushay Amir', '::1', '2026-06-10 23:39:27'),
(11, 1, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Anousha Noman', '::1', '2026-06-10 23:42:16'),
(12, 2, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Bisma Wajeeha', '::1', '2026-06-10 23:48:37'),
(13, 2, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Ahsan Zaman', '::1', '2026-06-10 23:55:28'),
(14, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 15:41:33'),
(15, 1, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Ahmed Hashmi', '::1', '2026-06-11 17:58:09'),
(16, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 17:58:22'),
(17, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 17:58:22'),
(18, 2, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Abdul Hadi', '::1', '2026-06-11 18:01:48'),
(19, 2, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Abdul Hadi', '::1', '2026-06-11 18:02:31'),
(20, 2, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: zain Khan', '::1', '2026-06-11 18:04:23'),
(21, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 18:04:40'),
(22, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 18:26:23'),
(23, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 18:28:26'),
(24, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-11 18:32:23'),
(25, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-11 18:32:47'),
(26, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-11 18:32:55'),
(27, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-11 18:33:09'),
(28, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 18:33:26'),
(29, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 18:42:37'),
(30, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 18:43:14'),
(31, 2, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Adnan Asad', '::1', '2026-06-11 20:18:56'),
(32, 2, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Faiz Raza', '::1', '2026-06-11 20:20:52'),
(33, 2, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Abdul Samad', '::1', '2026-06-11 20:24:03'),
(34, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 20:58:44'),
(35, 1, 'Created Employee Profile', '[Employees] Successfully onboarded a new team member: Syed Anwer', '::1', '2026-06-11 21:04:08'),
(36, 1, 'Updated Hierarchy Settings', '[Organization] CEO: Shayan Siddiqui; CTO: Ahad Iqbal; Managers assigned: 3', '::1', '2026-06-11 21:05:38'),
(37, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Syed Bukhari', '::1', '2026-06-11 21:13:18'),
(38, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Syed Bukhari', '::1', '2026-06-11 21:13:33'),
(39, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 21:33:14'),
(40, 1, 'Updated Hierarchy Settings', '[Organization] CEO: Shayan Siddiqui; CTO: Ahad Iqbal; Managers assigned: 3', '::1', '2026-06-11 21:37:48'),
(41, 2, 'Updated Hierarchy Settings', '[Organization] CEO: Shayan Siddiqui; CTO: Ahad Iqbal; Managers assigned: 3', '::1', '2026-06-11 21:41:08'),
(42, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 21:43:20'),
(43, 2, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'Test Annountment\'', '::1', '2026-06-11 21:44:59'),
(44, 2, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'Test Annountment\'', '::1', '2026-06-11 21:45:10'),
(45, 2, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'Test Annountment\'', '::1', '2026-06-11 21:45:25'),
(46, 2, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'Test Annountment\'', '::1', '2026-06-11 21:45:46'),
(47, 2, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'Test Annountment\'', '::1', '2026-06-11 21:46:16'),
(48, 2, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'Test Annountment\'', '::1', '2026-06-11 21:46:32'),
(49, 2, 'Created Event', '[Event Calendar] Scheduled a new company event: \'Test Event\' on Jun 12, 2026', '::1', '2026-06-11 21:49:38'),
(50, 2, 'Updated Event', '[Event Calendar] Modified the details of event: \'Test Event\' (Scheduled for Jun 12, 2026)', '::1', '2026-06-11 21:49:51'),
(51, 2, 'Updated Event', '[Event Calendar] Modified the details of event: \'Test Event\' (Scheduled for Jun 12, 2026)', '::1', '2026-06-11 21:50:14'),
(52, 2, 'Deleted Event', '[Event Calendar] Permanently removed the event: \'Test Event\'', '::1', '2026-06-11 21:50:43'),
(53, 2, 'Created Event', '[Event Calendar] Scheduled a new company event: \'test event \' on Jun 12, 2026', '::1', '2026-06-11 21:51:06'),
(54, 2, 'Deleted Event', '[Event Calendar] Permanently removed the event: \'test event \'', '::1', '2026-06-11 21:55:45'),
(55, 2, 'Created Event', '[Event Calendar] Scheduled a new company event: \'Test Event\' on Jun 12, 2026', '::1', '2026-06-11 21:56:10'),
(56, 2, 'Deleted Event', '[Event Calendar] Permanently removed the event: \'Test Event\'', '::1', '2026-06-11 21:56:23'),
(57, 2, 'Created Event', '[Event Calendar] Scheduled a new company event: \'Test Event\' on Jun 12, 2026', '::1', '2026-06-11 21:56:41'),
(58, 2, 'Updated Event', '[Event Calendar] Modified the details of event: \'Test Event\' (Scheduled for Jun 12, 2026)', '::1', '2026-06-11 21:56:51'),
(59, 2, 'Updated Event', '[Event Calendar] Modified the details of event: \'Test Event\' (Scheduled for Jun 12, 2026)', '::1', '2026-06-11 21:57:24'),
(60, 2, 'Deleted Event', '[Event Calendar] Permanently removed the event: \'Test Event\'', '::1', '2026-06-11 22:09:54'),
(61, 2, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'Test Annountment\'', '::1', '2026-06-11 22:16:24'),
(62, 2, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'Test Annountment\'', '::1', '2026-06-11 22:16:56'),
(63, 2, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'Test Annountment\'', '::1', '2026-06-11 22:24:19'),
(64, 2, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'test\'', '::1', '2026-06-11 22:24:33'),
(65, 2, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'test\'', '::1', '2026-06-11 22:24:42'),
(66, 2, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'test\'', '::1', '2026-06-11 22:37:59'),
(67, 2, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'test\'', '::1', '2026-06-11 22:38:23'),
(68, 2, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'test\'', '::1', '2026-06-11 22:38:36'),
(69, 2, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'test\'', '::1', '2026-06-11 22:39:16'),
(70, 2, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'sadsad\'', '::1', '2026-06-11 22:39:22'),
(71, 2, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'sadsad\'', '::1', '2026-06-11 22:39:35'),
(72, 2, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'sadsad\'', '::1', '2026-06-11 22:39:41'),
(73, 2, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'asdsda\'', '::1', '2026-06-11 22:39:55'),
(74, 2, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'asdsda\'', '::1', '2026-06-11 22:40:07'),
(75, 2, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'asdsda\'', '::1', '2026-06-11 22:40:15'),
(76, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 22:51:59'),
(77, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-11 23:05:25'),
(78, 3, 'Submitted Leave Request', '[Leave Management] Applied for Sick Leave from Jun 12, 2026 to Jun 15, 2026', '::1', '2026-06-11 23:06:16'),
(79, 3, 'Submitted Leave Request', '[Leave Management] Applied for Sick Leave from Jun 12, 2026 to Jun 15, 2026', '::1', '2026-06-11 23:14:49'),
(80, 2, 'Approved Leave', '[Leave Management] Formally approved the Sick Leave request for team member: Syed Bukhari', '::1', '2026-06-11 23:14:56'),
(81, 3, 'Updated Personal Profile', '[Employees] Updated their personal profile details and changed their profile picture.', '::1', '2026-06-11 23:20:47'),
(82, 2, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Syed Bukhari', '::1', '2026-06-11 23:21:07'),
(83, 2, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Syed Bukhari (Period: quarterly)', '::1', '2026-06-11 23:23:09'),
(84, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-12 15:11:54'),
(85, 1, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Shayan Shaikh (Period: annual)', '::1', '2026-06-12 15:22:57'),
(86, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-12 15:24:41'),
(87, 17, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-12 15:27:01'),
(88, 18, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-12 15:42:40'),
(89, 19, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-12 15:44:51'),
(90, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Syed Bukhari', '::1', '2026-06-12 16:02:15'),
(91, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Muhammad Abiden', '::1', '2026-06-12 16:02:32'),
(92, 4, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-12 16:17:34'),
(93, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-12 16:27:03'),
(94, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-12 16:39:05'),
(95, 4, 'Submitted Leave Request', '[Leave Management] Applied for Casual Leave from May 01, 2026 to May 01, 2026', '::1', '2026-06-12 16:52:33'),
(96, 2, 'Approved Leave', '[Leave Management] Formally approved the Casual Leave request for team member: Muhammad Abiden', '::1', '2026-06-12 16:53:42'),
(97, 4, 'Submitted Leave Request', '[Leave Management] Applied for Casual Leave from May 08, 2026 to May 08, 2026', '::1', '2026-06-12 17:21:14'),
(98, 2, 'Approved Leave', '[Leave Management] Formally approved the Casual Leave request for team member: Muhammad Abiden', '::1', '2026-06-12 17:22:03'),
(99, 2, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'testtttt\'', '::1', '2026-06-12 17:29:41'),
(100, 2, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'Junior / Associate Developer\'', '::1', '2026-06-12 17:33:49'),
(101, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-12 17:38:47'),
(102, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-12 17:39:06'),
(103, 2, 'Scheduled Interview', '[Job Management] Scheduled an interview session for candidate \'mahad\' on Jun 12, 2026 at 23:20.', '::1', '2026-06-12 18:17:06'),
(104, 2, 'Rescheduled Interview', '[Job Management] Rescheduled the interview session for \'mahad\' to Jun 15, 2026 at 08:00.', '::1', '2026-06-12 18:18:57'),
(105, 2, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'mahad\' from \'Interview\' to \'Shortlisted\'.', '::1', '2026-06-12 18:19:54'),
(106, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'mahad\' from \'Shortlisted\' to \'Offer\'.', '::1', '2026-06-12 18:37:55'),
(107, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'mahad\' from \'Offer\' to \'Hired\'.', '::1', '2026-06-12 18:38:45'),
(108, 2, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'Junior / Associate Developer\'', '::1', '2026-06-12 19:26:47'),
(109, 2, 'Scheduled Interview', '[Job Management] Scheduled an interview session for candidate \'Mahad\' on Jun 15, 2026 at 20:00.', '::1', '2026-06-12 20:27:01'),
(110, 2, 'Rescheduled Interview', '[Job Management] Rescheduled the interview session for \'Mahad\' to Jun 13, 2026 at 21:00.', '::1', '2026-06-12 20:27:36'),
(111, 2, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Mahad\' from \'Interview\' to \'Shortlisted\'.', '::1', '2026-06-12 20:28:36'),
(112, 2, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Mahad\' from \'Shortlisted\' to \'Offer\'.', '::1', '2026-06-12 20:29:04'),
(113, 2, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Mahad\' from \'Offer\' to \'Hired\'.', '::1', '2026-06-12 20:34:34'),
(114, 2, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Mahad\' from \'Offer\' to \'Hired\'.', '::1', '2026-06-12 20:36:50'),
(115, 2, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Mahad\' from \'Hired\' to \'Banned\'.', '::1', '2026-06-12 20:37:46'),
(116, 2, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Mahad\' from \'Interview\' to \'Rejected\'.', '::1', '2026-06-12 20:38:15'),
(117, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-12 21:14:15'),
(118, 2, 'Deleted Employee Profile', '[Employees] Moved team member: Bisma Wajeeha to the Exit list.', '::1', '2026-06-12 21:34:21'),
(119, 2, 'Restored Employee Profile', '[Employees] Reactivated team member: Bisma Wajeeha', '::1', '2026-06-12 21:34:42'),
(120, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-15 15:18:32'),
(121, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-17 17:14:46'),
(122, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-17 17:18:09'),
(123, 1, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'\'', '::1', '2026-06-17 17:25:19'),
(124, 1, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'\'', '::1', '2026-06-17 17:25:53'),
(125, 1, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'Unknown\'', '::1', '2026-06-17 17:34:37'),
(126, 1, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'Unknown\'', '::1', '2026-06-17 17:34:44'),
(127, 1, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'sadsdasda\'', '::1', '2026-06-17 17:36:36'),
(128, 1, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'sadsdasda\'', '::1', '2026-06-17 17:37:17'),
(129, 1, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'\'', '::1', '2026-06-17 17:43:39'),
(130, 1, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'Unknown\'', '::1', '2026-06-17 17:43:59'),
(131, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-18 16:54:35'),
(132, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-18 17:25:35'),
(133, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 17:25:48'),
(134, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 17:25:56'),
(135, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 17:25:59'),
(136, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 17:26:06'),
(137, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:08:14'),
(138, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:08:21'),
(139, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:08:29'),
(140, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:08:46'),
(141, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:09:01'),
(142, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:09:09'),
(143, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:09:18'),
(144, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:09:20'),
(145, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:09:39'),
(146, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:09:45'),
(147, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:09:56'),
(148, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:10:15'),
(149, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:11:04'),
(150, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:11:11'),
(151, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:11:21'),
(152, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:12:37'),
(153, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:12:55'),
(154, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:13:09'),
(155, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:13:16'),
(156, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:13:25'),
(157, 2, 'Created Event', '[Event Calendar] Scheduled a new company event: \'asdad\' on Jun 19, 2026', '::1', '2026-06-18 21:14:44'),
(158, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:15:00'),
(159, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:15:06'),
(160, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:16:20'),
(161, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:16:30'),
(162, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:16:42'),
(163, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:16:50'),
(164, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:16:58'),
(165, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:17:05'),
(166, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:17:27'),
(167, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:17:33'),
(168, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:17:49'),
(169, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:18:05'),
(170, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:18:30'),
(171, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:18:40'),
(172, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:27:48'),
(173, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:28:10'),
(174, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:28:18'),
(175, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:29:30'),
(176, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:30:18'),
(177, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:31:38'),
(178, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:32:11'),
(179, 2, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'sadsdasda\'', '::1', '2026-06-18 21:32:17'),
(180, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:32:27'),
(181, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:32:37'),
(182, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:32:50'),
(183, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:32:59'),
(184, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:33:34'),
(185, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:33:40'),
(186, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:33:51'),
(187, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:34:39'),
(188, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:34:47'),
(189, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:34:59'),
(190, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:35:05'),
(191, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:35:14'),
(192, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:35:28'),
(193, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:35:36'),
(194, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:35:45'),
(195, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:35:52'),
(196, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:36:01'),
(197, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:36:29'),
(198, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:36:35'),
(199, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:36:40'),
(200, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:36:45'),
(201, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:36:58'),
(202, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:37:23'),
(203, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:37:34'),
(204, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:37:50'),
(205, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-18 21:38:06'),
(206, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-18 21:38:54'),
(207, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-22 15:54:32'),
(208, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-22 19:28:09'),
(209, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-24 18:16:37'),
(210, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-26 21:21:30'),
(211, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-26 21:21:41'),
(212, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-26 21:37:56'),
(213, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-26 21:41:01'),
(214, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-26 21:46:03'),
(215, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-26 21:47:55'),
(216, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-26 21:48:23'),
(217, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-26 22:09:28'),
(218, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 16:15:45'),
(219, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 16:16:25'),
(220, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 16:21:28'),
(221, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 16:39:49'),
(222, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 17:14:51'),
(223, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 17:28:29'),
(224, 3, 'Submitted Leave Request', '[Leave Management] Applied for Sick Leave from Jun 30, 2026 to Jul 01, 2026', '::1', '2026-06-29 19:17:10'),
(225, 1, 'Rejected Leave', '[Leave Management] Declined the Sick Leave request for team member: Syed Bukhari', '::1', '2026-06-29 19:17:35'),
(226, 1, 'Approved Leave', '[Leave Management] Formally approved the Sick Leave request for team member: Syed Bukhari', '::1', '2026-06-29 19:17:44'),
(227, 1, 'Created Event', '[Event Calendar] Scheduled a new company event: \'testttt\' on Jun 30, 2026', '::1', '2026-06-29 19:20:55'),
(228, 1, 'Updated Event', '[Event Calendar] Modified the details of event: \'testttt\' (Scheduled for Jun 30, 2026)', '::1', '2026-06-29 19:21:22'),
(229, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 21:25:03'),
(230, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 21:30:56'),
(231, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 21:39:14'),
(232, 4, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 21:40:38'),
(233, 4, 'Submitted Leave Request', '[Leave Management] Applied for Sick Leave from Jun 30, 2026 to Jul 01, 2026', '::1', '2026-06-29 21:41:31'),
(234, 1, 'Approved Leave', '[Leave Management] Formally approved the Sick Leave request for team member: Muhammad Abiden', '::1', '2026-06-29 21:41:38'),
(235, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 21:43:10'),
(236, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 21:58:35'),
(237, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-06-29 21:59:10'),
(238, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 22:17:20'),
(239, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-29 22:35:59'),
(240, 2, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Muhammad Abiden', '::1', '2026-06-29 23:36:15'),
(241, 2, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Muhammad Abiden', '::1', '2026-06-29 23:36:41'),
(242, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-30 16:56:44'),
(243, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-30 17:46:49'),
(244, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-30 21:12:18'),
(245, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-30 21:19:36'),
(246, 4, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-06-30 21:26:25'),
(247, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-01 15:20:43'),
(248, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-01 20:15:24'),
(249, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-01 20:19:10'),
(250, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-01 20:36:40'),
(251, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-01 20:55:29'),
(252, 4, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-01 21:15:20'),
(253, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-02 16:39:50'),
(254, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-02 16:43:00'),
(255, 2, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'sadsdasda\'', '::1', '2026-07-02 20:17:24'),
(256, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-06 15:24:56'),
(257, 4, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-06 15:25:32'),
(258, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-06 16:05:38'),
(259, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-06 20:36:30'),
(260, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-06 20:41:35'),
(261, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-06 20:41:57'),
(262, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-06 20:45:50'),
(263, 3, 'Submitted Leave Request', '[Leave Management] Applied for Annual Leave from Jul 07, 2026 to Jul 07, 2026', '::1', '2026-07-06 21:30:05'),
(264, 1, 'Approved Leave', '[Leave Management] Formally approved the Annual Leave request for team member: Syed Bukhari', '::1', '2026-07-06 21:30:47'),
(265, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-06 21:56:06'),
(266, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-06 22:17:19'),
(267, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 22:21:59'),
(268, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 22:22:21'),
(269, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 22:22:39'),
(270, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 22:23:05'),
(271, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 22:24:17'),
(272, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 23:20:10'),
(273, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 23:36:23'),
(274, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 23:36:28'),
(275, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 23:36:33'),
(276, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 23:36:39'),
(277, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 23:36:44'),
(278, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 23:37:07'),
(279, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 23:37:12'),
(280, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 23:37:26'),
(281, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 23:37:36'),
(282, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 23:37:48'),
(283, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-06 23:38:05'),
(284, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-07 15:22:55'),
(285, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-07 15:23:48'),
(286, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:32:42'),
(287, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:32:51'),
(288, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:32:55'),
(289, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:33:08'),
(290, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:33:21'),
(291, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:33:36'),
(292, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-07 15:34:44'),
(293, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:34:59'),
(294, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:35:12'),
(295, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:45:57'),
(296, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:46:01'),
(297, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:46:06'),
(298, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:46:14'),
(299, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:46:29'),
(300, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:46:44'),
(301, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:46:54'),
(302, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:47:05'),
(303, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:47:21'),
(304, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:47:25'),
(305, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:47:34'),
(306, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:47:37'),
(307, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:49:42'),
(308, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:49:58'),
(309, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:50:26'),
(310, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:50:36'),
(311, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:50:54'),
(312, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:51:00'),
(313, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:51:13'),
(314, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:51:42'),
(315, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:51:52'),
(316, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:51:58'),
(317, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:52:04'),
(318, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:52:11'),
(319, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:52:21'),
(320, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:52:28'),
(321, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:52:45'),
(322, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:52:50'),
(323, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:53:16'),
(324, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:53:24'),
(325, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:54:08'),
(326, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:54:23'),
(327, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:54:40'),
(328, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:54:51'),
(329, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:54:59'),
(330, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:55:10'),
(331, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:55:53'),
(332, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:57:22'),
(333, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:57:30'),
(334, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:57:35'),
(335, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:57:40'),
(336, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:58:06'),
(337, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:58:18'),
(338, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:58:28'),
(339, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:58:38'),
(340, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:59:01'),
(341, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 15:59:08'),
(342, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:00:22'),
(343, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:00:44'),
(344, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:00:56'),
(345, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:01:09'),
(346, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:01:16'),
(347, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:02:04'),
(348, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:02:13'),
(349, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:02:19'),
(350, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:02:28'),
(351, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:02:35'),
(352, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:02:42'),
(353, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:03:30'),
(354, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:08:29'),
(355, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:08:36'),
(356, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:08:45'),
(357, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:08:50'),
(358, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:09:29'),
(359, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:10:27'),
(360, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:10:38'),
(361, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:10:45'),
(362, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:11:03'),
(363, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:11:20'),
(364, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:12:16'),
(365, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:13:12'),
(366, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:13:29');
INSERT INTO `activity_logs` (`id`, `employee_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(367, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:13:38'),
(368, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:14:59'),
(369, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:15:39'),
(370, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-07 16:23:15'),
(371, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:23:27'),
(372, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:23:39'),
(373, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:23:47'),
(374, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:25:03'),
(375, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-07 16:27:36'),
(376, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:28:00'),
(377, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:30:55'),
(378, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:31:11'),
(379, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-07 16:32:06'),
(380, 17, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-07 20:19:12'),
(381, 19, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-07 20:19:33'),
(382, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-07 20:23:51'),
(383, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-07 20:24:08'),
(384, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '127.0.0.1', '2026-07-09 22:11:52'),
(385, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-09 23:02:04'),
(386, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-09 23:02:20'),
(387, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-09 23:02:32'),
(388, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-09 23:02:40'),
(389, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-09 23:02:50'),
(390, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 15:33:02'),
(391, 4, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 15:33:16'),
(392, 17, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 15:40:35'),
(393, 18, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 15:48:53'),
(394, 19, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 15:49:32'),
(395, 18, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 15:49:44'),
(396, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 15:54:43'),
(397, 1, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'testtttt\'', '::1', '2026-07-13 15:55:59'),
(398, 1, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'testtttt\'', '::1', '2026-07-13 15:58:31'),
(399, 1, 'Updated Job Status', '[Job Management] Changed status for \'testtttt\' to \'Close\'.', '::1', '2026-07-13 15:58:39'),
(400, 1, 'Updated Job Status', '[Job Management] Changed status for \'testtttt\' to \'Close\'.', '::1', '2026-07-13 15:58:44'),
(401, 1, 'Updated Job Status', '[Job Management] Changed status for \'testtttt\' to \'Active\'.', '::1', '2026-07-13 15:59:05'),
(402, 1, 'Updated Job Status', '[Job Management] Changed status for \'testtttt\' to \'Active\'.', '::1', '2026-07-13 15:59:41'),
(403, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 16:01:47'),
(404, 2, 'Updated Job Status', '[Job Management] Changed status for \'testtttt\' to \'Close\'.', '::1', '2026-07-13 16:01:52'),
(405, 2, 'Updated Job Status', '[Job Management] Changed status for \'testtttt\' to \'Active\'.', '::1', '2026-07-13 16:01:58'),
(406, 2, 'Updated Job Status', '[Job Management] Changed status for \'testtttt\' to \'Close\'.', '::1', '2026-07-13 16:02:12'),
(407, 2, 'Updated Job Status', '[Job Management] Changed status for \'testtttt\' to \'Active\'.', '::1', '2026-07-13 16:02:24'),
(408, 2, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'testtttt 111111111111111\'', '::1', '2026-07-13 16:08:40'),
(409, 1, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'testtttt 22222222222\'', '::1', '2026-07-13 16:08:59'),
(410, 2, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'testtttt 111111111111111\'', '::1', '2026-07-13 16:09:12'),
(411, 1, 'Scheduled Interview', '[Job Management] Scheduled an interview session for candidate \'Syed Mahad bukhari\' on Jul 13, 2026 at 21:30.', '::1', '2026-07-13 16:18:53'),
(412, 1, 'Rescheduled Interview', '[Job Management] Rescheduled the interview session for \'Syed Mahad bukhari\' to Jul 13, 2026 at 21:46.', '::1', '2026-07-13 16:21:00'),
(413, 1, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'Junior / Associate Developer\'', '::1', '2026-07-13 16:28:25'),
(414, 1, 'Scheduled Interview', '[Job Management] Scheduled an interview session for candidate \'Syed Mahad bukhari\' on Jul 13, 2026 at 21:35.', '::1', '2026-07-13 16:29:53'),
(415, 1, 'Rescheduled Interview', '[Job Management] Rescheduled the interview session for \'Syed Mahad bukhari\' to Jul 13, 2026 at 21:40.', '::1', '2026-07-13 16:30:27'),
(416, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Interview\' to \'Shortlisted\'.', '::1', '2026-07-13 16:32:35'),
(417, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Shortlisted\' to \'Offer\'.', '::1', '2026-07-13 16:33:24'),
(418, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Offer\' to \'Hired\'.', '::1', '2026-07-13 16:34:31'),
(419, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Offer\' to \'Hired\'.', '::1', '2026-07-13 16:42:05'),
(420, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Offer\' to \'Hired\'.', '::1', '2026-07-13 16:45:12'),
(421, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Offer\' to \'Hired\'.', '::1', '2026-07-13 16:48:56'),
(422, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Offer\' to \'Hired\'.', '::1', '2026-07-13 16:50:46'),
(423, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Offer\' to \'Hired\'.', '::1', '2026-07-13 16:55:50'),
(424, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Offer\' to \'Hired\'.', '::1', '2026-07-13 16:57:39'),
(425, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'New\' to \'Rejected\'.', '::1', '2026-07-13 17:07:45'),
(426, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'New\' to \'Banned\'.', '::1', '2026-07-13 17:08:59'),
(427, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 17:23:11'),
(428, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-13 17:28:31'),
(429, 1, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Abdul Hadi', '::1', '2026-07-13 17:30:43'),
(430, 2, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Abdul Samad', '::1', '2026-07-13 17:31:08'),
(431, 2, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Adnan Asad', '::1', '2026-07-13 17:31:33'),
(432, 2, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Ahad Iqbal', '::1', '2026-07-13 17:32:12'),
(433, 2, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Ahmed Hashmi', '::1', '2026-07-13 17:32:36'),
(434, 2, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: Faiz Raza', '::1', '2026-07-13 17:32:57'),
(435, 2, 'Completed Employee Onboarding', '[Employees] Formally completed and finalized the onboarding profile for new team member: zain Khan', '::1', '2026-07-13 17:33:22'),
(436, 2, 'Deleted Employee Profile', '[Employees] Moved team member: zain Khan to the Exit list.', '::1', '2026-07-13 17:33:58'),
(437, 2, 'Restored Employee Profile', '[Employees] Reactivated team member: zain Khan', '::1', '2026-07-13 17:34:18'),
(438, 1, 'Deleted Employee Profile', '[Employees] Moved team member: zain Khan to the Exit list.', '::1', '2026-07-13 17:34:31'),
(439, 1, 'Restored Employee Profile', '[Employees] Reactivated team member: zain Khan', '::1', '2026-07-13 17:34:38'),
(440, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 17:36:34'),
(441, 3, 'Submitted Leave Request', '[Leave Management] Applied for Sick Leave from Jul 08, 2026 to Jul 09, 2026', '::1', '2026-07-13 17:43:29'),
(442, 1, 'Rejected Leave', '[Leave Management] Declined the Sick Leave request for team member: Syed Bukhari', '::1', '2026-07-13 17:43:43'),
(443, 3, 'Submitted Leave Request', '[Leave Management] Applied for Casual Leave from Jul 08, 2026 to Jul 09, 2026', '::1', '2026-07-13 17:44:07'),
(444, 3, 'Updated Leave Request', '[Leave Management] Updated Casual Leave request for the period: Jul 08, 2026 to Jul 09, 2026', '::1', '2026-07-13 17:45:15'),
(445, 1, 'Approved Leave', '[Leave Management] Formally approved the Casual Leave request for team member: Syed Bukhari', '::1', '2026-07-13 17:45:35'),
(446, 1, 'Created Event', '[Event Calendar] Scheduled a new company event: \'test\' on Jul 13, 2026', '::1', '2026-07-13 17:46:51'),
(447, 1, 'Updated Event', '[Event Calendar] Modified the details of event: \'test\' (Scheduled for Jul 13, 2026)', '::1', '2026-07-13 17:47:05'),
(448, 1, 'Updated Event', '[Event Calendar] Modified the details of event: \'test\' (Scheduled for Jul 13, 2026)', '::1', '2026-07-13 17:47:24'),
(449, 1, 'Updated Event', '[Event Calendar] Modified the details of event: \'test\' (Scheduled for Jul 13, 2026)', '::1', '2026-07-13 17:48:02'),
(450, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 17:51:40'),
(451, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 17:52:26'),
(452, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 17:52:45'),
(453, 2, 'Updated Event', '[Event Calendar] Modified the details of event: \'test\' (Scheduled for Jul 13, 2026)', '::1', '2026-07-13 17:55:23'),
(454, 2, 'Updated Event', '[Event Calendar] Modified the details of event: \'test\' (Scheduled for Jul 13, 2026)', '::1', '2026-07-13 17:55:35'),
(455, 2, 'Updated Event', '[Event Calendar] Modified the details of event: \'test\' (Scheduled for Jul 13, 2026)', '::1', '2026-07-13 18:04:39'),
(456, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 18:04:52'),
(457, 1, 'Updated Event', '[Event Calendar] Modified the details of event: \'test\' (Scheduled for Jul 13, 2026)', '::1', '2026-07-13 18:06:44'),
(458, 1, 'Created Event', '[Event Calendar] Scheduled a new company event: \'test\' on Jul 13, 2026', '::1', '2026-07-13 18:11:28'),
(459, 1, 'Updated Event', '[Event Calendar] Modified the details of event: \'test\' (Scheduled for Jul 13, 2026)', '::1', '2026-07-13 18:11:47'),
(460, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 18:11:59'),
(461, 2, 'Updated Event', '[Event Calendar] Modified the details of event: \'test\' (Scheduled for Jul 13, 2026)', '::1', '2026-07-13 18:12:13'),
(462, 17, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 18:12:28'),
(463, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 18:15:23'),
(464, 2, 'Updated Event', '[Event Calendar] Modified the details of event: \'test\' (Scheduled for Jul 13, 2026)', '::1', '2026-07-13 18:15:36'),
(465, 2, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Shayan Shaikh (Period: monthly)', '::1', '2026-07-13 18:32:49'),
(466, 2, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Shayan Shaikh (Period: monthly)', '::1', '2026-07-13 18:33:08'),
(467, 2, 'Deleted Performance Review', '[KPI Management] Permanently removed the performance appraisal record for team member: Shayan Shaikh', '::1', '2026-07-13 18:33:17'),
(468, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 18:33:22'),
(469, 1, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Syed Bukhari (Period: monthly)', '::1', '2026-07-13 18:34:12'),
(470, 1, 'Updated Performance Review', '[KPI Management] Modified the performance appraisal details for team member: Syed Bukhari (Period: monthly)', '::1', '2026-07-13 18:35:27'),
(471, 1, 'Deleted Performance Review', '[KPI Management] Permanently removed the performance appraisal record for team member: Syed Bukhari', '::1', '2026-07-13 18:35:30'),
(472, 1, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'Announcement Title Test\'', '::1', '2026-07-13 18:38:04'),
(473, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 18:38:29'),
(474, 1, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'Announcement Title Test\'', '::1', '2026-07-13 18:38:48'),
(475, 1, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'Announcement Title Test\'', '::1', '2026-07-13 18:39:03'),
(476, 1, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'Announcement Title Test\'', '::1', '2026-07-13 18:40:03'),
(477, 1, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'Announcement Title Test\'', '::1', '2026-07-13 18:40:18'),
(478, 1, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'Announcement Title\'', '::1', '2026-07-13 18:40:45'),
(479, 1, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'Announcement Title\'', '::1', '2026-07-13 18:40:51'),
(480, 1, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'Announcement Title\'', '::1', '2026-07-13 18:41:07'),
(481, 1, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'Announcement Title\'', '::1', '2026-07-13 18:41:29'),
(482, 1, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'Announcement Title\'', '::1', '2026-07-13 18:41:35'),
(483, 1, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'Announcement Title\'', '::1', '2026-07-13 18:47:05'),
(484, 1, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'Announcement Title \'', '::1', '2026-07-13 18:47:38'),
(485, 7, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 18:47:53'),
(486, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 18:48:04'),
(487, 1, 'Updated Announcement', '[Announcements] Modified the details of announcement: \'Announcement Title \'', '::1', '2026-07-13 18:48:18'),
(488, 1, 'Deleted Announcement', '[Announcements] Permanently removed the announcement: \'Announcement Title \'', '::1', '2026-07-13 18:49:26'),
(489, 1, 'Created Announcement', '[Announcements] Published a new company announcement titled: \'Announcement Title Test\'', '::1', '2026-07-13 18:49:52'),
(490, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 19:54:18'),
(491, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 20:01:22'),
(492, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 20:01:25'),
(493, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 20:14:09'),
(494, 1, 'Hierarchy Settings Updated', '[Organization] Updated CEO/CTO and manager department assignments.', '::1', '2026-07-13 21:10:46'),
(495, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 21:26:20'),
(496, 1, 'Hierarchy Settings Updated', '[Organization] Updated CEO/CTO and manager department assignments.', '::1', '2026-07-13 21:33:29'),
(497, 1, 'Hierarchy Settings Updated', '[Organization] Updated CEO/CTO and manager department assignments.', '::1', '2026-07-13 21:37:33'),
(498, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 21:42:27'),
(499, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 21:45:44'),
(500, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-13 21:53:59'),
(501, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-20 16:28:26'),
(502, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-20 16:28:42'),
(503, 3, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-20 16:29:14'),
(504, 3, 'Clock In', '[Attendance] Employee checked in for date: 2026-07-20 at 09:46 PM.', '::1', '2026-07-20 16:46:08'),
(505, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-20 16:51:20'),
(506, 1, 'Scheduled Interview', '[Job Management] Scheduled an Onsite interview session for candidate \'Syed Mahad bukhari\' on Jul 20, 2026 at 23:50.', '::1', '2026-07-20 18:46:53'),
(507, 1, 'Scheduled Interview', '[Job Management] Scheduled an Onsite interview session for candidate \'Syed Mahad bukhari\' on Jul 20, 2026 at 23:59.', '::1', '2026-07-20 18:55:45'),
(508, 1, 'Scheduled Interview', '[Job Management] Scheduled an Onsite interview session for candidate \'Syed Mahad bukhari\' on Jul 21, 2026 at 20:00.', '::1', '2026-07-20 19:39:37'),
(509, 2, 'Rescheduled Interview', '[Job Management] Rescheduled the Onsite interview session for \'Syed Mahad bukhari\' to Jul 21, 2026 at 21:05.', '::1', '2026-07-20 19:40:00'),
(510, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Interview\' to \'Shortlisted\'.', '::1', '2026-07-20 19:42:36'),
(511, 2, 'Scheduled Interview', '[Job Management] Scheduled an Onsite interview session for candidate \'Syed Mahad bukhari\' on Jul 21, 2026 at 20:00.', '::1', '2026-07-20 19:46:34'),
(512, 2, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Interview\' to \'Shortlisted\'.', '::1', '2026-07-20 19:47:17'),
(513, 2, 'Scheduled Interview', '[Job Management] Scheduled an Onsite interview session for candidate \'Syed Mahad bukhari\' on Jul 21, 2026 at 20:00.', '::1', '2026-07-20 19:53:23'),
(514, 2, 'Rescheduled Interview', '[Job Management] Rescheduled the Onsite interview session for \'Syed Mahad bukhari\' to Jul 21, 2026 at 21:00.', '::1', '2026-07-20 19:53:43'),
(515, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Interview\' to \'Shortlisted\'.', '::1', '2026-07-20 19:54:30'),
(516, 1, 'Rescheduled Interview', '[Job Management] Rescheduled the Online interview session for \'Syed Mahad bukhari\' to Jul 22, 2026 at 22:00.', '::1', '2026-07-20 19:55:30'),
(517, 1, 'Scheduled Interview', '[Job Management] Scheduled an Onsite interview session for candidate \'Syed Mahad bukhari\' on Jul 21, 2026 at 20:00.', '::1', '2026-07-20 20:05:15'),
(518, 2, 'Rescheduled Interview', '[Job Management] Rescheduled the Onsite interview session for \'Syed Mahad bukhari\' to Jul 21, 2026 at 21:00.', '::1', '2026-07-20 20:06:03'),
(519, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Interview\' to \'Shortlisted\'.', '::1', '2026-07-20 20:06:46'),
(520, 1, 'Rescheduled Interview', '[Job Management] Rescheduled the Onsite interview session for \'Syed Mahad bukhari\' to Jul 22, 2026 at 23:00.', '::1', '2026-07-20 20:07:09'),
(521, 2, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Shortlisted\' to \'Offer\'.', '::1', '2026-07-20 20:07:56'),
(522, 2, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Offer\' to \'Hired\'.', '::1', '2026-07-20 20:08:38'),
(523, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Offer\' to \'Hired\'.', '::1', '2026-07-20 20:11:44'),
(524, 2, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Offer\' to \'Hired\'.', '::1', '2026-07-20 20:20:35'),
(525, 1, 'Updated Candidate Status', '[Job Management] Updated status for candidate \'Syed Mahad bukhari\' from \'Offer\' to \'Hired\'.', '::1', '2026-07-20 20:34:37'),
(526, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-20 21:52:09'),
(527, 1, 'Updated Job Opening', '[Job Management] Modified the requirements and details for position: \'Junior / Associate Developer\'', '::1', '2026-07-20 23:26:33'),
(528, 1, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-21 15:13:46'),
(529, 4, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-21 15:28:30'),
(530, 4, 'Clock In', '[Attendance] Employee checked in for date: 2026-07-21 at 08:28 PM.', '::1', '2026-07-21 15:28:37'),
(531, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-21 15:45:38'),
(532, 5, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-21 16:51:13'),
(533, 5, 'Submitted Leave Request', '[Leave Management] Applied for Annual Leave from Jul 14, 2026 to Jul 20, 2026', '::1', '2026-07-21 16:52:20'),
(534, 2, 'Approved Leave', '[Leave Management] Formally approved the Annual Leave request for team member: Shayan Shaikh', '::1', '2026-07-21 16:52:29'),
(535, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 16:59:36'),
(536, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 16:59:57'),
(537, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:00:13'),
(538, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:00:37'),
(539, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:01:21'),
(540, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:01:28'),
(541, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:01:40'),
(542, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:05:28'),
(543, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:05:51'),
(544, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:05:59'),
(545, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:06:07'),
(546, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:11:54'),
(547, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:12:04'),
(548, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:12:08'),
(549, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:12:14'),
(550, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:12:20'),
(551, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:12:24'),
(552, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:12:32'),
(553, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:12:50'),
(554, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:12:55'),
(555, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:13:04'),
(556, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:13:12'),
(557, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:13:21'),
(558, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:13:28'),
(559, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:13:35'),
(560, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:13:44'),
(561, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:14:26'),
(562, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:14:40'),
(563, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:14:49'),
(564, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:14:53'),
(565, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:15:02'),
(566, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:15:14'),
(567, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:15:35'),
(568, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:15:48'),
(569, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:16:14'),
(570, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:16:43'),
(571, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:17:00'),
(572, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:17:21'),
(573, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:17:33'),
(574, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:17:59'),
(575, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:18:12'),
(576, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:18:26'),
(577, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:19:30'),
(578, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:19:47'),
(579, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:19:57'),
(580, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:20:02'),
(581, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:20:15'),
(582, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:20:26'),
(583, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:20:37'),
(584, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:20:51'),
(585, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:21:08'),
(586, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:21:17'),
(587, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:21:24'),
(588, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:21:40'),
(589, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:21:48'),
(590, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:21:57'),
(591, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:22:11'),
(592, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:22:26'),
(593, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:22:31'),
(594, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:22:37'),
(595, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:22:50'),
(596, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:23:03'),
(597, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:23:11'),
(598, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:23:22'),
(599, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:23:30'),
(600, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:23:42'),
(601, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:23:52'),
(602, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:23:59'),
(603, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:24:10'),
(604, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:24:39'),
(605, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:24:48'),
(606, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:25:01'),
(607, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:25:14'),
(608, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:29:00'),
(609, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:29:22'),
(610, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-21 17:32:41'),
(611, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-21 17:34:21'),
(612, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-21 17:36:46'),
(613, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:42:41'),
(614, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:42:48'),
(615, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:42:52'),
(616, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:42:57'),
(617, 2, 'User Login', '[Authentication] User authenticated successfully and accessed the system dashboard.', '::1', '2026-07-21 17:47:04'),
(618, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:48:36'),
(619, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:49:22'),
(620, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:49:33'),
(621, 1, 'Access Control Updated', '[Security] HR portal permissions matrix was updated by Admin.', '::1', '2026-07-21 17:50:21');

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
(1, 'Test Annountment', 'Test Description', 'IMPORTANT', 'Production,Marketing', '2026-06-12', '2026-06-19', 2, 1, '2026-06-11 21:44:59', '2026-06-11 21:45:25', '2026-06-11 21:45:25'),
(2, 'Test Annountment', 'Test description', 'IMPORTANT', 'Production', '2026-06-15', '2026-06-19', 2, 1, '2026-06-11 21:45:46', '2026-06-11 21:46:32', '2026-06-11 21:46:32'),
(3, 'Test Annountment', 'Test description', 'IMPORTANT', 'Production,Marketing', '2026-06-12', '2026-06-19', 2, 1, '2026-06-11 22:16:24', '2026-06-11 22:24:19', '2026-06-11 22:24:19'),
(4, 'test', 'test', 'IMPORTANT', 'Production', '2026-06-12', '2026-06-19', 2, 0, '2026-06-11 22:24:33', '2026-06-11 22:37:59', '2026-06-11 22:37:59'),
(5, 'test', 'test', 'CELEBRATION', 'Production', '2026-06-12', '2026-06-19', 2, 1, '2026-06-11 22:38:23', '2026-06-11 22:39:16', '2026-06-11 22:39:16'),
(6, 'sadsad', 'sdasadas', 'IMPORTANT', 'Production', '2026-06-12', '2026-06-19', 2, 1, '2026-06-11 22:39:22', '2026-06-11 22:39:41', '2026-06-11 22:39:41'),
(7, 'asdsda', 'ssdadasd', 'IMPORTANT', 'Production', '2026-06-12', '2026-06-19', 2, 1, '2026-06-11 22:39:55', '2026-06-11 22:40:15', '2026-06-11 22:40:15'),
(8, '', 'Test Websocket', 'CELEBRATION', 'everyone', '2026-06-17', '2026-06-24', 1, 1, '2026-06-17 17:25:18', '2026-06-17 17:34:44', '2026-06-17 17:34:44'),
(9, '', 'sadsasdasadsdasdasdasdsda', 'CELEBRATION', 'everyone', '2026-06-17', '2026-06-24', 1, 1, '2026-06-17 17:25:53', '2026-06-17 17:34:37', '2026-06-17 17:34:37'),
(10, 'sadsdasda', 'sdasasdasda', 'CELEBRATION', 'everyone', '2026-06-17', '2026-06-24', 1, 1, '2026-06-17 17:36:36', '2026-06-17 17:37:17', '2026-06-17 17:37:17'),
(11, '', 'ssadsadad', 'CELEBRATION', 'everyone', '2026-06-17', '2026-06-24', 1, 1, '2026-06-17 17:43:38', '2026-06-17 17:43:59', '2026-06-17 17:43:59'),
(12, 'sadsdasda', 'sasdasdasda', 'IMPORTANT', 'everyone', '2026-06-19', '2026-06-26', 2, 1, '2026-06-18 21:32:17', '2026-06-18 21:32:17', NULL),
(13, 'sadsdasda', 'sdasdasdasdasda', 'IMPORTANT', 'everyone', '2026-07-03', '2026-07-10', 2, 1, '2026-07-02 20:17:24', '2026-07-02 20:17:24', NULL),
(14, 'Announcement Title Test', 'testttt', 'IMPORTANT', 'Production', '2026-07-13', '2026-07-20', 1, 1, '2026-07-13 18:38:04', '2026-07-13 18:40:18', '2026-07-13 18:40:18'),
(15, 'Announcement Title', 'test test test test', 'IMPORTANT', 'Production,Marketing', '2026-07-13', '2026-07-20', 1, 1, '2026-07-13 18:40:45', '2026-07-13 18:41:07', '2026-07-13 18:41:07'),
(16, 'Announcement Title', 'test', 'IMPORTANT', 'Production,Marketing', '2026-07-13', '2026-07-20', 1, 1, '2026-07-13 18:41:29', '2026-07-13 18:47:05', '2026-07-13 18:47:05'),
(17, 'Announcement Title ', 'Test Description', 'IMPORTANT', 'Production,Marketing', '2026-07-13', '2026-07-20', 1, 1, '2026-07-13 18:47:38', '2026-07-13 18:49:26', '2026-07-13 18:49:26'),
(18, 'Announcement Title Test', 'Test Description', 'IMPORTANT', 'Production', '2026-07-14', '2026-07-20', 1, 0, '2026-07-13 18:49:52', NULL, NULL);

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
(106, 4, '2026-01-21', 2, '2026-01-21 20:07:00', '2026-01-22 05:01:00', '8h 47m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(107, 4, '2026-01-22', 2, '2026-01-22 20:05:00', '2026-01-23 05:01:00', '8h 51m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(108, 4, '2026-01-23', 2, '2026-01-23 20:09:00', '2026-01-24 05:04:00', '8h 36m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(109, 4, '2026-01-26', 2, '2026-01-26 20:03:00', '2026-01-27 05:02:00', '8h 40m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(110, 4, '2026-01-27', 2, '2026-01-27 20:11:00', '2026-01-28 05:03:00', '8h 40m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(111, 4, '2026-01-28', 2, '2026-01-28 20:02:00', '2026-01-29 05:01:00', '8h 41m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(112, 4, '2026-01-29', 2, '2026-01-29 20:08:00', '2026-01-30 05:01:00', '8h 39m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(113, 4, '2026-01-30', 2, '2026-01-30 20:13:00', '2026-01-31 05:01:00', '8h 48m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(114, 4, '2026-02-02', 2, '2026-02-02 20:06:00', '2026-02-03 05:05:00', '8h 55m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(115, 4, '2026-02-03', 2, '2026-02-03 20:10:00', '2026-02-04 05:02:00', '8h 44m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(116, 4, '2026-02-04', 2, '2026-02-04 20:14:00', '2026-02-05 05:00:00', '8h 49m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(117, 4, '2026-02-05', 2, '2026-02-05 20:04:00', '2026-02-06 05:03:00', '8h 52m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(118, 4, '2026-02-06', 2, '2026-02-06 20:07:00', '2026-02-07 05:03:00', '8h 51m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(119, 4, '2026-02-09', 2, '2026-02-09 20:12:00', '2026-02-10 05:05:00', '8h 55m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(120, 4, '2026-02-10', 2, '2026-02-10 20:05:00', '2026-02-11 00:20:00', '3h 15m', 'HALF DAY', NULL, '2026-06-12 17:08:18', NULL),
(121, 4, '2026-02-11', 2, NULL, NULL, NULL, 'ABSENT', NULL, '2026-06-12 17:08:18', NULL),
(122, 4, '2026-02-12', 2, '2026-02-12 20:26:00', '2026-02-13 05:50:00', '8h 31m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(123, 4, '2026-02-13', 2, '2026-02-13 20:05:00', '2026-02-14 05:03:00', '8h 35m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(124, 4, '2026-02-16', 2, '2026-02-16 20:01:00', '2026-02-17 05:00:00', '8h 51m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(125, 4, '2026-02-17', 2, '2026-02-17 20:46:00', '2026-02-18 05:48:00', '8h 09m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(126, 4, '2026-02-18', 2, '2026-02-18 20:09:00', '2026-02-19 05:04:00', '8h 52m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(127, 4, '2026-02-19', 2, '2026-02-19 20:02:00', '2026-02-20 05:03:00', '8h 36m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(128, 4, '2026-02-20', 2, '2026-02-20 20:00:00', '2026-02-21 05:05:00', '8h 37m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(129, 4, '2026-02-23', 2, NULL, NULL, NULL, 'ABSENT', NULL, '2026-06-12 17:08:18', NULL),
(130, 4, '2026-02-24', 2, '2026-02-24 20:07:00', '2026-02-25 05:01:00', '8h 46m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(131, 4, '2026-02-25', 2, '2026-02-25 20:05:00', '2026-02-26 05:02:00', '8h 31m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(132, 4, '2026-02-26', 2, '2026-02-26 20:13:00', '2026-02-27 05:04:00', '8h 49m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(133, 4, '2026-02-27', 2, '2026-02-27 20:11:00', '2026-02-28 05:00:00', '8h 38m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(134, 4, '2026-03-02', 2, '2026-03-02 20:06:00', '2026-03-03 05:01:00', '8h 31m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(135, 4, '2026-03-03', 2, '2026-03-03 20:33:00', '2026-03-04 05:56:00', '8h 37m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(136, 4, '2026-03-04', 2, '2026-03-04 20:10:00', '2026-03-05 05:02:00', '8h 55m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(137, 4, '2026-03-05', 2, '2026-03-05 20:08:00', '2026-03-06 05:03:00', '8h 38m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(138, 4, '2026-03-06', 2, '2026-03-06 20:03:00', '2026-03-07 05:01:00', '8h 51m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(139, 4, '2026-03-09', 2, '2026-03-09 20:34:00', '2026-03-10 05:49:00', '8h 07m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(140, 4, '2026-03-10', 2, '2026-03-10 20:09:00', '2026-03-11 05:03:00', '8h 41m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(141, 4, '2026-03-11', 2, NULL, NULL, NULL, 'ABSENT', NULL, '2026-06-12 17:08:18', NULL),
(142, 4, '2026-03-12', 2, '2026-03-12 20:07:00', '2026-03-13 00:20:00', '3h 15m', 'HALF DAY', NULL, '2026-06-12 17:08:18', NULL),
(143, 4, '2026-03-13', 2, '2026-03-13 20:04:00', '2026-03-14 05:02:00', '8h 55m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(144, 4, '2026-03-16', 2, '2026-03-16 20:12:00', '2026-03-17 05:05:00', '8h 55m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(145, 4, '2026-03-17', 2, '2026-03-17 20:02:00', '2026-03-18 05:05:00', '8h 33m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(146, 4, '2026-03-18', 2, '2026-03-18 20:07:00', '2026-03-19 05:00:00', '8h 49m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(147, 4, '2026-03-19', 2, '2026-03-19 20:07:00', '2026-03-20 05:01:00', '8h 47m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(148, 4, '2026-03-20', 2, '2026-03-20 20:05:00', '2026-03-21 05:01:00', '8h 51m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(149, 4, '2026-03-23', 2, '2026-03-23 20:55:00', '2026-03-24 05:49:00', '8h 42m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(150, 4, '2026-03-24', 2, '2026-03-24 20:09:00', '2026-03-25 05:04:00', '8h 36m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(151, 4, '2026-03-25', 2, '2026-03-25 20:03:00', '2026-03-26 05:02:00', '8h 40m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(152, 4, '2026-03-26', 2, NULL, NULL, NULL, 'ABSENT', NULL, '2026-06-12 17:08:18', NULL),
(153, 4, '2026-03-27', 2, '2026-03-27 20:11:00', '2026-03-28 05:03:00', '8h 40m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(154, 4, '2026-03-30', 2, '2026-03-30 20:02:00', '2026-03-31 05:01:00', '8h 41m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(155, 4, '2026-03-31', 2, '2026-03-31 20:10:00', '2026-04-01 00:20:00', '3h 15m', 'HALF DAY', NULL, '2026-06-12 17:08:18', NULL),
(156, 4, '2026-04-01', 2, '2026-04-01 20:59:00', '2026-04-02 05:58:00', '8h 14m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(157, 4, '2026-04-02', 2, '2026-04-02 20:08:00', '2026-04-03 05:01:00', '8h 39m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(158, 4, '2026-04-03', 2, '2026-04-03 20:13:00', '2026-04-04 05:01:00', '8h 48m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(159, 4, '2026-04-06', 2, '2026-04-06 20:06:00', '2026-04-07 05:05:00', '8h 55m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(160, 4, '2026-04-07', 2, '2026-04-07 20:10:00', '2026-04-08 05:02:00', '8h 44m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(161, 4, '2026-04-08', 2, '2026-04-08 20:08:00', '2026-04-09 00:20:00', '3h 15m', 'HALF DAY', NULL, '2026-06-12 17:08:18', NULL),
(162, 4, '2026-04-09', 2, NULL, NULL, NULL, 'ABSENT', NULL, '2026-06-12 17:08:18', NULL),
(163, 4, '2026-04-10', 2, '2026-04-10 20:14:00', '2026-04-11 05:00:00', '8h 49m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(164, 4, '2026-04-13', 2, '2026-04-13 20:04:00', '2026-04-14 05:03:00', '8h 52m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(165, 4, '2026-04-14', 2, '2026-04-14 20:07:00', '2026-04-15 05:03:00', '8h 51m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(166, 4, '2026-04-15', 2, '2026-04-15 20:12:00', '2026-04-16 05:05:00', '8h 55m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(167, 4, '2026-04-16', 2, '2026-04-16 20:05:00', '2026-04-17 05:03:00', '8h 35m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(168, 4, '2026-04-17', 2, '2026-04-17 20:27:00', '2026-04-18 05:58:00', '8h 20m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(169, 4, '2026-04-20', 2, NULL, NULL, NULL, 'ABSENT', NULL, '2026-06-12 17:08:18', NULL),
(170, 4, '2026-04-21', 2, '2026-04-21 20:01:00', '2026-04-22 05:00:00', '8h 51m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(171, 4, '2026-04-22', 2, '2026-04-22 20:09:00', '2026-04-23 05:04:00', '8h 52m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(172, 4, '2026-04-23', 2, '2026-04-23 20:02:00', '2026-04-24 05:03:00', '8h 36m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(173, 4, '2026-04-24', 2, '2026-04-24 20:44:00', '2026-04-25 05:50:00', '8h 33m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(174, 4, '2026-04-27', 2, '2026-04-27 20:00:00', '2026-04-28 05:05:00', '8h 37m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(175, 4, '2026-04-28', 2, '2026-04-28 20:37:00', '2026-04-29 05:51:00', '8h 22m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(176, 4, '2026-04-29', 2, '2026-04-29 20:50:00', '2026-04-30 05:55:00', '8h 06m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(177, 4, '2026-04-30', 2, '2026-04-30 20:54:00', '2026-05-01 05:50:00', '8h 44m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(178, 4, '2026-05-01', 2, NULL, NULL, NULL, 'ABSENT', NULL, '2026-06-12 17:08:18', NULL),
(179, 4, '2026-05-04', 2, '2026-05-04 20:07:00', '2026-05-05 05:01:00', '8h 46m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(180, 4, '2026-05-05', 2, '2026-05-05 20:05:00', '2026-05-06 05:02:00', '8h 31m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(181, 4, '2026-05-06', 2, '2026-05-06 20:13:00', '2026-05-07 05:04:00', '8h 49m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(182, 4, '2026-05-07', 2, '2026-05-07 20:42:00', '2026-05-08 05:55:00', '8h 35m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(183, 4, '2026-05-08', 2, NULL, NULL, '', 'LEAVE', 'Approved Casual Leave', '2026-06-12 17:08:18', '2026-07-06 16:35:48'),
(184, 4, '2026-05-11', 2, '2026-05-11 20:11:00', '2026-05-12 05:00:00', '8h 38m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(185, 4, '2026-05-12', 2, '2026-05-12 20:06:00', '2026-05-13 05:01:00', '8h 31m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(186, 4, '2026-05-13', 2, '2026-05-13 20:11:00', '2026-05-14 00:20:00', '3h 15m', 'HALF DAY', NULL, '2026-06-12 17:08:18', NULL),
(187, 4, '2026-05-14', 2, '2026-05-14 20:10:00', '2026-05-15 05:02:00', '8h 55m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(188, 4, '2026-05-15', 2, '2026-05-15 20:08:00', '2026-05-16 05:03:00', '8h 38m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(189, 4, '2026-05-18', 2, '2026-05-18 20:03:00', '2026-05-19 05:01:00', '8h 51m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(190, 4, '2026-05-19', 2, '2026-05-19 20:09:00', '2026-05-20 05:03:00', '8h 41m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(191, 4, '2026-05-20', 2, '2026-05-20 20:04:00', '2026-05-21 05:02:00', '8h 55m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(192, 4, '2026-05-21', 2, '2026-05-21 20:12:00', '2026-05-22 05:05:00', '8h 55m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(193, 4, '2026-05-22', 2, '2026-05-22 20:38:00', '2026-05-23 05:52:00', '8h 32m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(194, 4, '2026-05-25', 2, '2026-05-25 20:02:00', '2026-05-26 05:05:00', '8h 33m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(195, 4, '2026-05-26', 2, '2026-05-26 20:07:00', '2026-05-27 05:00:00', '8h 49m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(196, 4, '2026-05-27', 2, '2026-05-27 20:07:00', '2026-05-28 05:01:00', '8h 47m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(197, 4, '2026-05-28', 2, '2026-05-28 20:05:00', '2026-05-29 05:01:00', '8h 51m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(198, 4, '2026-05-29', 2, '2026-05-29 20:09:00', '2026-05-30 05:04:00', '8h 36m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(199, 4, '2026-06-01', 2, '2026-06-01 20:03:00', '2026-06-02 05:02:00', '8h 40m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(200, 4, '2026-06-02', 2, '2026-06-02 20:33:00', '2026-06-03 05:56:00', '8h 37m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(201, 4, '2026-06-03', 2, '2026-06-03 20:11:00', '2026-06-04 05:03:00', '8h 40m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(202, 4, '2026-06-04', 2, '2026-06-04 20:02:00', '2026-06-05 05:01:00', '8h 41m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(203, 4, '2026-06-05', 2, NULL, NULL, NULL, 'ABSENT', NULL, '2026-06-12 17:08:18', NULL),
(204, 4, '2026-06-08', 2, '2026-06-08 20:08:00', '2026-06-09 05:01:00', '8h 39m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(205, 4, '2026-06-09', 2, '2026-06-09 20:29:00', '2026-06-10 05:49:00', '8h 07m', 'LATE IN', NULL, '2026-06-12 17:08:18', NULL),
(206, 4, '2026-06-10', 2, '2026-06-10 20:13:00', '2026-06-11 05:01:00', '8h 48m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(207, 4, '2026-06-11', 2, '2026-06-11 20:06:00', '2026-06-12 05:05:00', '8h 55m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(208, 4, '2026-06-12', 2, '2026-06-12 20:10:00', '2026-06-13 05:02:00', '8h 44m', 'ON TIME', NULL, '2026-06-12 17:08:18', NULL),
(229, 4, '2026-06-30', NULL, NULL, NULL, '', 'LEAVE', NULL, '2026-06-29 21:41:38', '2026-07-06 16:35:48'),
(230, 4, '2026-07-01', NULL, NULL, NULL, '', 'LEAVE', NULL, '2026-06-29 21:41:38', '2026-07-06 16:35:48'),
(259, 4, '2026-07-02', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 16:45:10', NULL),
(260, 4, '2026-07-03', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 16:45:10', NULL),
(261, 4, '2026-07-06', 2, '2026-07-06 21:45:10', NULL, NULL, 'LATE IN', NULL, '2026-07-06 16:45:10', NULL),
(351, 3, '2026-06-08', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:30', NULL),
(352, 3, '2026-06-09', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:30', NULL),
(353, 3, '2026-06-10', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:30', NULL),
(354, 3, '2026-06-11', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:30', NULL),
(355, 3, '2026-06-12', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:30', NULL),
(356, 3, '2026-06-15', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:30', NULL),
(357, 3, '2026-06-16', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:30', NULL),
(358, 3, '2026-06-17', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:30', NULL),
(359, 3, '2026-06-18', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:30', NULL),
(360, 3, '2026-06-19', 2, NULL, NULL, '', 'HOLIDAY', NULL, '2026-07-06 20:54:31', NULL),
(361, 3, '2026-06-22', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:31', NULL),
(362, 3, '2026-06-23', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:31', NULL),
(363, 3, '2026-06-24', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:31', NULL),
(364, 3, '2026-06-25', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:31', NULL),
(365, 3, '2026-06-26', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:31', NULL),
(366, 3, '2026-06-29', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:54:31', NULL),
(367, 3, '2026-06-30', 2, NULL, NULL, '', 'LEAVE', NULL, '2026-07-06 20:54:31', NULL),
(373, 3, '2026-07-01', 2, NULL, NULL, '', 'LEAVE', NULL, '2026-07-06 20:58:19', NULL),
(374, 3, '2026-07-02', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:58:19', NULL),
(375, 3, '2026-07-03', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-06 20:58:19', NULL),
(376, 3, '2026-07-06', 2, '2026-07-07 01:58:19', NULL, '', 'LATE IN', NULL, '2026-07-06 20:58:19', '2026-07-06 20:58:19'),
(377, 3, '2026-07-07', 2, '2026-07-07 20:00:00', '2026-07-08 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-06 21:30:47', '2026-07-21 16:31:40'),
(378, 4, '2026-07-07', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-13 15:33:24', NULL),
(379, 4, '2026-07-08', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-13 15:33:24', NULL),
(380, 4, '2026-07-09', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-13 15:33:24', NULL),
(381, 4, '2026-07-10', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-13 15:33:24', NULL),
(382, 4, '2026-07-13', 2, '2026-07-13 20:33:24', NULL, NULL, 'LATE IN', NULL, '2026-07-13 15:33:24', NULL),
(386, 3, '2026-07-08', 2, '2026-07-08 20:00:00', '2026-07-09 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-13 17:45:35', '2026-07-21 16:31:40'),
(387, 3, '2026-07-09', 2, '2026-07-09 20:00:00', '2026-07-10 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-13 17:45:35', '2026-07-21 16:31:40'),
(388, 3, '2026-07-10', 2, '2026-07-10 20:00:00', '2026-07-11 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-13 17:45:44', '2026-07-21 16:31:40'),
(389, 3, '2026-07-13', 2, '2026-07-13 20:00:00', '2026-07-14 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-13 17:45:44', '2026-07-21 16:31:40'),
(390, 3, '2026-07-14', 2, '2026-07-14 20:00:00', '2026-07-15 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-20 16:46:07', '2026-07-21 16:31:40'),
(391, 3, '2026-07-15', 2, '2026-07-15 20:00:00', '2026-07-16 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-20 16:46:07', '2026-07-21 16:31:40'),
(392, 3, '2026-07-16', 2, '2026-07-16 20:00:00', '2026-07-17 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-20 16:46:07', '2026-07-21 16:32:48'),
(393, 3, '2026-07-17', 2, '2026-07-17 20:00:00', '2026-07-18 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-20 16:46:07', '2026-07-21 16:32:48'),
(394, 3, '2026-07-20', 2, '2026-07-20 21:46:07', NULL, NULL, 'LATE IN', NULL, '2026-07-20 16:46:07', NULL),
(395, 4, '2026-06-22', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-21 15:28:35', NULL),
(396, 4, '2026-06-23', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-21 15:28:35', NULL),
(397, 4, '2026-06-24', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-21 15:28:35', NULL),
(398, 4, '2026-06-25', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-21 15:28:35', NULL),
(399, 4, '2026-06-26', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-21 15:28:35', NULL),
(400, 4, '2026-06-29', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-21 15:28:35', NULL),
(401, 4, '2026-07-14', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-21 15:28:35', NULL),
(402, 4, '2026-07-15', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-21 15:28:35', NULL),
(403, 4, '2026-07-16', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-21 15:28:35', NULL),
(404, 4, '2026-07-17', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-21 15:28:35', NULL),
(405, 4, '2026-07-20', 2, NULL, NULL, '', 'ABSENT', NULL, '2026-07-21 15:28:36', NULL),
(406, 4, '2026-07-21', 2, '2026-07-21 20:28:36', NULL, NULL, 'LATE IN', NULL, '2026-07-21 15:28:36', NULL),
(407, 3, '2026-07-11', 2, '2026-07-11 20:00:00', '2026-07-12 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:31:40', NULL),
(408, 3, '2026-07-12', 2, '2026-07-12 20:00:00', '2026-07-13 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:31:40', NULL),
(480, 5, '2026-06-22', 2, '2026-06-22 20:00:00', '2026-06-23 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(481, 5, '2026-06-23', 2, '2026-06-23 20:00:00', '2026-06-24 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(482, 5, '2026-06-24', 2, '2026-06-24 20:00:00', '2026-06-25 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(483, 5, '2026-06-25', 2, '2026-06-25 20:00:00', '2026-06-26 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(484, 5, '2026-06-26', 2, '2026-06-26 20:00:00', '2026-06-27 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(485, 5, '2026-06-29', 2, '2026-06-29 20:00:00', '2026-06-30 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(486, 5, '2026-06-30', 2, '2026-06-30 20:00:00', '2026-07-01 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(487, 5, '2026-07-01', 2, '2026-07-01 20:00:00', '2026-07-02 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(488, 5, '2026-07-02', 2, '2026-07-02 20:00:00', '2026-07-03 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(489, 5, '2026-07-03', 2, '2026-07-03 20:00:00', '2026-07-04 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(490, 5, '2026-07-06', 2, '2026-07-06 20:00:00', '2026-07-07 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(491, 5, '2026-07-07', 2, '2026-07-07 20:00:00', '2026-07-08 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(492, 5, '2026-07-08', 2, '2026-07-08 20:00:00', '2026-07-09 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(493, 5, '2026-07-09', 2, '2026-07-09 20:00:00', '2026-07-10 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(494, 5, '2026-07-10', 2, '2026-07-10 20:00:00', '2026-07-11 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(495, 5, '2026-07-13', 2, '2026-07-13 20:00:00', '2026-07-14 05:00:00', '9h 00m', 'ON TIME', NULL, '2026-07-21 16:51:52', NULL),
(496, 5, '2026-07-14', 2, NULL, NULL, '', 'LEAVE', NULL, '2026-07-21 16:51:52', '2026-07-21 16:52:29'),
(497, 5, '2026-07-15', NULL, NULL, NULL, '', 'LEAVE', NULL, '2026-07-21 16:52:29', NULL),
(498, 5, '2026-07-16', NULL, NULL, NULL, '', 'LEAVE', NULL, '2026-07-21 16:52:29', NULL),
(499, 5, '2026-07-17', NULL, NULL, NULL, '', 'LEAVE', NULL, '2026-07-21 16:52:29', NULL),
(500, 5, '2026-07-20', NULL, NULL, NULL, '', 'LEAVE', NULL, '2026-07-21 16:52:29', NULL);

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
(1, 3, 'Habib Metro', 'IBN', 'Syed Mahad Bukhari', '978754561213211', 'Hydri', NULL),
(2, 4, 'UBL', 'IBN', 'Muhammad Zain Ul Abiden', '98745465454654', 'Liaquatabad', NULL),
(5, 5, 'Meezan', 'IBN', 'Shayan Shaikh', '78954654564654546', 'PIB', NULL),
(6, 6, 'HBL', 'IBN', 'Faisal Wahab Khan', '897878798789787987', 'Nagan', NULL),
(7, 7, 'Meezan', 'IBN', 'Owais Ahmed', '78975454554', 'Azizabad', NULL),
(8, 8, 'Meezan', 'IBN', 'Affan Ahmed', '8798879878798798', 'Orangi Town', NULL),
(9, 9, 'Faysal', 'IBN', 'Anoushay Amir', '8348273847283', 'North Nazimabd', NULL),
(10, 10, 'Faysal', 'IBN', 'Anousha Noman', '78798787987', 'Father', NULL),
(11, 11, 'UBL', 'IBN', 'Bisma Wajeeha', '8789545464', 'Gulzar-e-Hijri', NULL),
(12, 12, 'Allied', 'IBN', 'Ahsan Uz Zaman', '789789897987', 'Gulzar-e-Hijri', NULL),
(13, 13, 'Meezan', 'IBN', 'Ahad Iqbal', '87897897987', 'Test', NULL),
(14, 14, 'Bank Alfalah', 'IBN', 'Ahmed Hashmi', '987879878979', 'Test', NULL),
(15, 15, 'Meezan', 'IBN', 'Abdul Hadi', '7895464454', 'North Nazimabad ', NULL),
(17, 16, 'Faysal', 'IBN', 'Zain Khan', '789787897987', 'North Karachi', NULL),
(18, 17, 'Bank Alfalah', 'IBN', 'adnan Asad', '789878979798', 'Test', NULL),
(19, 18, 'Habib Metro', 'IBN', 'Faiz Raza', '9879879879879879', 'Rizvia', NULL),
(20, 19, 'Soneri', 'IBN', 'Abdul Aziz Samad', '897879787987', 'Test', NULL),
(21, 20, 'Bank Islami', 'IBN', 'Syed Wahaj Anwer', '8798798798', 'Nazimabad', NULL);

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

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `name`, `email`, `phone`, `cnic_number`, `address`, `job_id`, `applied_date`, `status`, `duplicate_of`, `duplicate_reason`, `resume_path`, `created_at`, `updated_at`, `deleted_at`, `is_walk_in`) VALUES
(10, 'Syed Mahad bukhari', 'syedmahadbukhari928@gmail.com', '0335-0239042', '42401-4328822-1', 'North Nazimabad', 2, '2026-07-13', 'Hired', NULL, NULL, 'uploads/candidates/resumes/RES_6a551b4fa0ec5.pdf', '2026-07-13 17:07:27', '2026-07-20 20:34:37', NULL, 0),
(16, 'test', 'test@gmail.com', '0211-4655798', '98798-7987987-9', 'test', 4, '2026-07-21', 'New', NULL, NULL, 'uploads/candidates/resumes/WALK_6a5e9b675f108.pdf', '2026-07-20 22:04:23', NULL, NULL, 1),
(18, 'test', 'test@gmail.com', '7987-2387987', '98798-7987987-9', 'testetst', 2, '2026-07-21', 'Duplicated', 16, 'Matched Email, CNIC with test', 'uploads/candidates/resumes/WALK_6a5ea53a8d804.pdf', '2026-07-20 22:46:18', NULL, NULL, 1),
(20, 'testest', 'syedmahadbukhari928@gmail.com', '9789-7986545', '98798-4651659-8', 'testtestest', 4, '2026-07-21', 'Duplicated', 10, 'Matched Email with Syed Mahad bukhari', 'uploads/candidates/resumes/RES_6a5ea7f3057dc.pdf', '2026-07-20 22:57:55', NULL, NULL, 0);

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
(41, 10, 'What is your current salary?', 'test', '2026-07-13 17:07:27'),
(42, 10, 'How many years of experience do you have?', 'test', '2026-07-13 17:07:27'),
(43, 10, 'Portfolio Link', 'test', '2026-07-13 17:07:27'),
(44, 10, 'LinkedIn Profile', 'test', '2026-07-13 17:07:27'),
(45, 10, 'When can you start?', 'test', '2026-07-13 17:07:27');

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
(40, 10, 'New', 'Interview', 'Interview scheduled for July 21-2026, 8:00 PM (Onsite). test ', 1, '2026-07-20 20:05:15'),
(41, 10, 'Interview', 'Interview', 'Interview rescheduled (Onsite). Previous: July 21-2026, 8:00 PM. New: July 21-2026, 9:00 PM. Notes: test 1', 2, '2026-07-20 20:06:03'),
(42, 10, 'Interview', 'Shortlisted', 'test 2', 1, '2026-07-20 20:06:46'),
(43, 10, 'Interview', 'Interview', 'Interview rescheduled (Onsite). Previous: July 22-2026, 7:00 PM. New: July 22-2026, 11:00 PM. Notes: test 3', 1, '2026-07-20 20:07:09'),
(44, 10, 'Shortlisted', 'Offer', 'test 4', 2, '2026-07-20 20:07:56'),
(45, 10, 'Offer', 'Hired', 'test 5', 2, '2026-07-20 20:08:38'),
(46, 10, 'Offer', 'Hired', 'test 6', 1, '2026-07-20 20:11:44'),
(47, 10, 'Offer', 'Hired', 'test', 2, '2026-07-20 20:20:35'),
(48, 10, 'Offer', 'Hired', 'test 5', 1, '2026-07-20 20:34:37'),
(50, 16, 'New', 'New', 'Registered via Walk-In Interview Form', 0, '2026-07-20 22:04:23'),
(51, 17, 'New', 'Duplicated', 'Walk-In Registration - Duplicated: Matched Email with test', 0, '2026-07-20 22:39:32'),
(52, 18, 'New', 'Duplicated', 'Walk-In Registration - Duplicated: Matched Email, CNIC with test', 0, '2026-07-20 22:46:18');

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
(1, 'Production', 12, NULL, '2026-06-10 22:57:00', '2026-07-13 21:37:33', NULL),
(2, 'Marketing', 12, NULL, '2026-06-10 22:57:12', '2026-07-13 21:37:33', NULL),
(3, 'IT', 14, NULL, '2026-06-10 22:57:19', '2026-07-13 21:37:33', NULL),
(4, 'HR', NULL, NULL, '2026-06-10 22:57:24', '2026-07-13 21:37:33', NULL),
(5, 'Chat Support', 12, NULL, '2026-06-10 22:57:29', '2026-07-13 21:37:33', NULL),
(6, 'Management', NULL, NULL, '2026-06-10 23:51:39', '2026-07-13 21:32:37', NULL),
(7, 'Scraper', 14, NULL, '2026-06-11 18:02:21', '2026-07-13 21:37:33', NULL),
(8, 'Finance', 20, NULL, '2026-06-11 21:04:58', '2026-07-13 21:37:33', NULL);

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
(1, 3, 'Intermediate', 'Certificate', 'Institute', 'Development', '-', '-', NULL, NULL),
(2, 4, 'Intermediate', 'Certificate', 'Institute', 'Development', '-', '-', NULL, NULL),
(5, 5, 'Intermediate', 'Degree', 'College', 'Development', '-', '-', NULL, NULL),
(6, 6, 'Intermediate', 'Degree', 'College ', 'Graphic designer', '-', '-', NULL, NULL),
(7, 7, 'Beachelor', 'Dgeree', 'University', 'Marketing', '-', '-', NULL, NULL),
(8, 8, 'bachelor', 'Degreee', 'University', 'Seo', '-', '-', NULL, NULL),
(9, 9, 'Intermediate', 'Degree', 'College ', 'Chat support', '-', '-', NULL, NULL),
(10, 10, 'Intermediate', 'Degree', 'College', 'Chat Support', '-', '-', NULL, NULL),
(11, 11, 'bachelor', 'Degree', 'College ', 'Test', '-', '-', NULL, NULL),
(12, 12, 'Mbachelor', 'Degree', 'University', 'Test', '-', '-', NULL, NULL),
(13, 13, 'Test', 'test', 'test', 'test', 'test', 'test', NULL, NULL),
(14, 14, 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', NULL, NULL),
(15, 15, 'test', 'test', 'test', 'test', 'test', 'test', NULL, NULL),
(17, 16, 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', NULL, NULL),
(18, 17, 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', NULL, NULL),
(19, 18, 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', NULL, NULL),
(20, 19, 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', NULL, NULL),
(21, 20, 'Test', 'Test', 'Test', 'Test', 'Test', 'Test', NULL, NULL);

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

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `first_name`, `middle_name`, `last_name`, `email`, `password`, `role`, `gender`, `dob`, `phone`, `cnic_number`, `address`, `emergency_contact`, `emergency_relation`, `department_id`, `reports_to`, `shift_id`, `job_title`, `job_type`, `salary`, `joining_date`, `status`, `id_card_path`, `other_docs`, `resume_path`, `profile_pic`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin', NULL, 'Rtg', 'admin@gmail.com', '$2y$12$RKq6kP5En4KGCUYd3.hBIuE1WdPKNb7GFcnlE21gAgoiEA0i0noeS', 'Admin', 'Male', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Permanent', 0.00, NULL, 'Active', NULL, NULL, NULL, NULL, '2026-06-10 22:28:58', '2026-07-07 15:23:42', NULL),
(2, 'Hr', NULL, 'Rtg', 'hr@gmail.com', '$2y$12$RKq6kP5En4KGCUYd3.hBIuE1WdPKNb7GFcnlE21gAgoiEA0i0noeS', 'HR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, 'Permanent', 0.00, NULL, 'Active', NULL, NULL, NULL, NULL, '2026-06-10 22:32:20', '2026-06-10 23:21:17', NULL),
(3, 'Syed', 'Mahad', 'Bukhari', 'mahad@gmail.com', '$2y$10$MgycBpvxJLaKbGmG91di8.pC9bhQquB6SGSdNf0e/TYCnEzUvCssO', 'Employee', 'Male', '2002-07-05', '9879-8754543', '0321164654656', 'North Nazimabad Block D', '03215465456', 'Father', 1, NULL, 2, 'Frontend Developer', 'Permanent', 70000.00, '2026-01-20', 'Active', 'uploads/employees/id_cards/EMP_6a29ea02cf71a.pdf', NULL, 'uploads/employees/resumes/EMP_6a29ea02cf8cd.pdf', 'uploads/employees/profiles/user_3_6a2b42cf4e49f.png', '2026-06-10 22:49:38', '2026-06-12 16:02:15', NULL),
(4, 'Muhammad', 'Zain Ul', 'Abiden', 'zain@gmail.com', '$2y$10$9MHfZaQ5JhdZyghwEx/Yt.2JCQANoJBsDjsG6UH9zHM5aPTBR4P2i', 'Employee', 'Male', '2003-08-19', '7987-4654212', '8798465433213', 'Liaquatabad', '87455454654', 'Father', 1, NULL, 2, 'Backend Developer', 'Permanent', 90000.00, '2026-01-20', 'Active', 'uploads/employees/id_cards/EMP_6a29eaed1cc6c.pdf', NULL, 'uploads/employees/resumes/EMP_6a29eaed1ce30.pdf', NULL, '2026-06-10 22:53:33', '2026-06-29 23:36:41', NULL),
(5, 'Shayan', '', 'Shaikh', 'shayan@gmail.com', '$2y$10$FBne2obFvY.VtikbbpgExecM79zq4nRS/BjNlWSmkypIH93VgwNi6', 'Employee', 'Male', '2005-02-26', '7879-8745465', '7987465421231', 'PIB', '89754546545', 'Father', 1, NULL, 2, 'Wordpress', 'Permanent', 60000.00, '2025-03-09', 'Active', 'uploads/employees/id_cards/EMP_6a29eedc01e61.pdf', NULL, 'uploads/employees/resumes/EMP_6a29eedc02017.pdf', NULL, '2026-06-10 23:10:20', NULL, NULL),
(6, 'Faisal', 'Wahab', 'Khan', 'faisal@gmail.com', '$2y$10$ZFIRP5i0KdaBJcDXT65pjOHK/v2BuyZzAAac7D8OeYSlXASpH/lxm', 'Employee', 'Male', '2025-10-09', '7988-9798798', '9876546546579', 'Nagan', '78787987898', 'Spouse', 1, NULL, 2, 'Graphic Designer', 'Permanent', 90000.00, '2025-10-09', 'Active', 'uploads/employees/id_cards/EMP_6a29f0efbf5db.pdf', NULL, 'uploads/employees/resumes/EMP_6a29f0efbf7b8.pdf', NULL, '2026-06-10 23:19:11', NULL, NULL),
(7, 'Owais', '', 'Ahmed', 'owais@gmail.com', '$2y$10$dAmk/YGpnK3Ki/2LVJ252.tzegtOEMECOARArskCiMNoPamXwS7lW', 'Employee', 'Male', '2000-01-01', '9879-8798798', '9879879879879', 'Azizabad', '78789784654', 'Father', 2, NULL, 3, 'Seo Executive', 'Permanent', 100000.00, '2025-09-08', 'Active', 'uploads/employees/id_cards/EMP_6a29f3d70e206.pdf', NULL, 'uploads/employees/resumes/EMP_6a29f3d70e47d.pdf', NULL, '2026-06-10 23:31:35', NULL, NULL),
(8, 'Affan', '', 'Ahmed', 'affan@gmail.com', '$2y$10$UtSMtV4y/kHyf5XES210s.9Lkot3k1ki3B3yvoc0DX6Z13xHhNTpy', 'Employee', 'Male', '2000-02-01', '8979-8798798', '5465465465465', 'Orangi Town', '78987989879', 'Father', 2, NULL, 3, 'Seo Executive', 'Permanent', 60000.00, '2025-01-05', 'Active', 'uploads/employees/id_cards/EMP_6a29f4f461c85.pdf', NULL, 'uploads/employees/resumes/EMP_6a29f4f462064.pdf', NULL, '2026-06-10 23:36:20', NULL, NULL),
(9, 'Anoushay', '', 'Amir', 'anoushay@gmail.com', '$2y$10$ZJ99piA95kr4zuquBcRWWOmL5r3R5fdD812kj7a1Z4y8wl.L8t8Ke', 'Employee', 'Male', '2002-02-12', '8979-8787989', '9879879879879', 'North Nazimabad Block D', '87243872483', 'Father', 5, NULL, 3, 'Chat Support', 'Permanent', 60000.00, '2025-03-10', 'Active', 'uploads/employees/id_cards/EMP_6a29f5afc84a0.pdf', NULL, 'uploads/employees/resumes/EMP_6a29f5afc864d.pdf', NULL, '2026-06-10 23:39:27', NULL, NULL),
(10, 'Anousha', '', 'Noman', 'anousha@gmail.com', '$2y$10$8lpiogEcEGaon0.lWTUhRuIR8Zn0dJFQPQAekHmOEhZ8gB6klJIPS', 'Employee', 'Male', '2002-10-01', '8798-7987987', '9879879879879', 'North Nazimabad', '98798798798', 'Father', 5, NULL, 3, 'Chat Support', 'Permanent', 60000.00, '2025-11-01', 'Active', 'uploads/employees/id_cards/EMP_6a29f65813538.pdf', NULL, 'uploads/employees/resumes/EMP_6a29f65813722.pdf', NULL, '2026-06-10 23:42:16', NULL, NULL),
(11, 'Bisma', '', 'Wajeeha', 'bisma@gmail.com', '$2y$10$PfE1DsQXDDQR7G4ZbgzFI.T0AanAWT66v/tVkub1Tdx6sRtJjpeGC', 'Employee', 'Male', '1998-02-04', '9878-7987987', '9879879879879', 'Gulzar-e-Hijri', '87987878979', 'Father', 4, NULL, 3, 'Hr Recuritment', 'Permanent', 70000.00, '2025-12-08', 'Active', 'uploads/employees/id_cards/EMP_6a29f7d4ddc62.pdf', NULL, 'uploads/employees/resumes/EMP_6a29f7d4ddeb0.pdf', NULL, '2026-06-10 23:48:37', '2026-06-12 21:34:42', NULL),
(12, 'Ahsan', 'Uz', 'Zaman', 'ahsan@gmail.com', '$2y$10$yIc.xy28QKJtXbmmea13NOtwDuzxyv0M.YX.iwyyZdbv6H3t9VYSu', 'Employee', 'Male', '1995-06-05', '7898-7987987', '9879879879879', 'Gulzar-e-Hijri', '78987987879', 'Father', 6, NULL, 3, 'Operation Head', 'Permanent', 250000.00, '2023-12-12', 'Active', 'uploads/employees/id_cards/EMP_6a29f9704a275.pdf', NULL, 'uploads/employees/resumes/EMP_6a29f9704a42c.pdf', NULL, '2026-06-10 23:55:28', NULL, NULL),
(13, 'Ahad', NULL, 'Iqbal', 'ahad@gmail.com', '$2y$10$ktkBdAGT8Kwgv/O5f/N/1.D.1Wm5ua3XZU8JdeexTrx5Tm6r8z0nu', 'Employee', 'Male', '1990-02-06', '7898-6546546', '9879879879846', 'Test', '98798798798', 'Test', 6, NULL, 3, 'CTO', 'Permanent', 300000.00, '2023-11-12', 'Active', 'uploads/employees/id_cards/EMP_6a2ae94392f1b.pdf', NULL, 'uploads/employees/resumes/EMP_6a2ae943930e3.pdf', NULL, '2026-06-11 16:58:43', '2026-07-13 17:32:12', NULL),
(14, 'Ahmed', NULL, 'Hashmi', 'ahmed@gmail.com', '$2y$10$azxuyNdwa4rBCQ7NHAjb0eizLoudsMH9jF4AoNnRnxbeaOCh/2uuu', 'Employee', 'Male', '1990-10-31', '8798-7987987', '5465465465465', 'Test', '98798798798', 'Test', 6, NULL, 3, 'Test', 'Permanent', 300000.00, '2023-02-04', 'Active', 'uploads/employees/id_cards/EMP_6a2af7314ce1f.pdf', NULL, 'uploads/employees/resumes/EMP_6a2af7314d005.pdf', NULL, '2026-06-11 17:58:09', '2026-07-13 17:32:36', NULL),
(15, 'Abdul', NULL, 'Hadi', 'hadi@gmail.com', '$2y$10$i0Fnftx83CotTO/3L0aMC.cz9x.A.Ds.Tinvsyz/AeU7BGW7/9gOi', 'Employee', 'Male', '2005-01-01', '8798-7987987', '9879879879879', 'North Nazimabad', '89798998798', 'Mother', 7, NULL, 2, 'Scraper', 'Permanent', 40000.00, '2024-05-05', 'Active', 'uploads/employees/id_cards/EMP_6a2af80c7d113.pdf', NULL, 'uploads/employees/resumes/EMP_6a2af80c7d2d0.pdf', NULL, '2026-06-11 18:01:48', '2026-07-13 17:30:43', NULL),
(16, 'zain', NULL, 'Khan', 'zainkhan@gmail.com', '$2y$10$geupXFTcBMbbWaTXqYAHuuHG8h7KjXBp94lC17H9DX9Wj4Up3MwyK', 'Employee', 'Male', '2026-06-03', '8798-7987987', '7987987987987', 'North Karachi', '87987787987', 'Father', 7, NULL, 2, 'Scraper', 'Permanent', 40000.00, '2026-06-02', 'Active', 'uploads/employees/id_cards/EMP_6a2af8a6e2938.pdf', NULL, 'uploads/employees/resumes/EMP_6a2af8a6e2ae9.pdf', NULL, '2026-06-11 18:04:23', '2026-07-13 17:34:38', NULL),
(17, 'Adnan', NULL, 'Asad', 'adnan@gmail.com', '$2y$10$bckSZfx8S3sLw7hQIoMLWOg0r6ASMH7xJ/7FdSZjmoVIbjnRBxkNy', 'Employee', 'Male', '2026-06-02', '9879-8798798', '6546546546546', 'Test', '77897987987', 'Father', 3, NULL, 1, 'IT Head', 'Permanent', 100000.00, '2025-04-17', 'Active', 'uploads/employees/id_cards/EMP_6a2b18302806b.pdf', NULL, 'uploads/employees/resumes/EMP_6a2b183028228.pdf', NULL, '2026-06-11 20:18:56', '2026-07-13 17:31:33', NULL),
(18, 'Faiz', NULL, 'Raza', 'faiz@gmail.com', '$2y$10$daBUtiXkHgLUA.HjCFPtHOCYU5RruL3XbXilfkIPKCTcEvpA8UMem', 'Employee', 'Male', '2026-06-01', '7897-9879879', '9879879879879', 'Rizvia', '79876545654', 'Father', 3, NULL, 1, 'IT', 'Permanent', 60000.00, '2025-06-20', 'Active', 'uploads/employees/id_cards/EMP_6a2b18a4b30b0.pdf', NULL, 'uploads/employees/resumes/EMP_6a2b18a4b324d.pdf', NULL, '2026-06-11 20:20:52', '2026-07-13 17:32:57', NULL),
(19, 'Abdul', 'Aziz', 'Samad', 'aziz@gmail.com', '$2y$10$ZJi3LJ11OccN55FpPehkrOXQFF.s22O4d.wL0fKmUecuVxUefqS5S', 'Employee', 'Male', '2026-06-01', '8787-9879879', '8798798798798', 'Test', '87987987987', 'test', 3, NULL, 1, 'IT', 'Permanent', 50000.00, '2026-01-01', 'Active', 'uploads/employees/id_cards/EMP_6a2b19634a4a5.pdf', NULL, 'uploads/employees/resumes/EMP_6a2b19634a69b.pdf', NULL, '2026-06-11 20:24:03', '2026-07-13 17:31:08', NULL),
(20, 'Syed', 'Wahaj', 'Anwer', 'wahaj@gmail.com', '$2y$10$lMBN4Z1uIGqoPD9NhkcdWue1NMh7fYjWLqN/JQbMeAFnDVwZZ2cpq', 'Employee', 'Male', '2026-06-01', '7987-9879879', '9898798798798', 'Nazimabad', '97987987987', 'Test', 6, NULL, 3, 'finance', 'Permanent', 87987.00, '2024-02-05', 'Pending', 'uploads/employees/id_cards/EMP_6a2b22c856868.pdf', NULL, 'uploads/employees/resumes/EMP_6a2b22c856a44.pdf', NULL, '2026-06-11 21:04:08', '2026-06-18 21:10:56', NULL);

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
(8, 'test', 'test test test test test test test test test', '2026-07-13', '23:15:00', 'Celebration', 'Chat Support', 1, 1, 1, '2026-07-13 18:11:28', '2026-07-13 18:15:36');

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

--
-- Dumping data for table `hr_page_permissions`
--

INSERT INTO `hr_page_permissions` (`id`, `page_key`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `updated_at`) VALUES
(1, 'index', 1, 0, 0, 0, 0, '2026-07-21 17:50:20'),
(2, 'employees', 1, 1, 1, 1, 0, '2026-07-21 17:50:20'),
(3, 'attendance', 1, 0, 1, 0, 0, '2026-07-21 17:50:20'),
(4, 'leave-management', 1, 0, 1, 0, 0, '2026-07-21 17:50:20'),
(5, 'new-joining', 1, 1, 0, 1, 0, '2026-07-21 17:50:20'),
(6, 'hierarchy', 1, 0, 0, 0, 0, '2026-07-21 17:50:20'),
(7, 'kpi-management', 1, 1, 1, 1, 0, '2026-07-21 17:50:20'),
(8, 'event-calendar', 1, 1, 1, 1, 0, '2026-07-21 17:50:20'),
(9, 'job-list', 1, 1, 1, 1, 0, '2026-07-21 17:50:20'),
(10, 'create-job', 0, 0, 0, 0, 0, '2026-07-21 17:50:21'),
(11, 'job-candidates', 1, 1, 1, 1, 0, '2026-07-21 17:50:20'),
(12, 'interviews', 0, 0, 0, 0, 0, '2026-07-21 17:50:21'),
(13, 'payroll', 1, 1, 1, 0, 1, '2026-07-21 17:50:20'),
(14, 'activity-logs', 1, 0, 0, 0, 0, '2026-07-21 17:50:20'),
(15, 'announcements', 1, 1, 1, 1, 0, '2026-07-21 17:50:20'),
(16, 'notifications', 1, 0, 1, 1, 0, '2026-07-21 17:50:21'),
(17, 'it-support', 1, 0, 1, 0, 0, '2026-07-21 17:50:21'),
(18, 'shifts', 1, 1, 1, 1, 0, '2026-07-21 17:50:21'),
(19, 'department-management', 1, 1, 1, 1, 0, '2026-07-21 17:50:21'),
(20, 'policy-management', 1, 1, 1, 1, 0, '2026-07-21 17:50:21'),
(21, 'payroll-settings', 1, 0, 1, 0, 0, '2026-07-21 17:50:21'),
(22, 'employee-profile', 0, 0, 0, 0, 0, '2026-07-21 17:50:21'),
(23, 'attendance-log', 0, 0, 0, 0, 0, '2026-07-21 17:50:21'),
(24, 'edit-job', 0, 0, 1, 0, 0, '2026-07-21 17:50:21'),
(25, 'candidate-detail', 0, 0, 0, 0, 0, '2026-07-21 17:50:21'),
(26, 'kpi-report', 0, 0, 0, 0, 0, '2026-07-21 17:50:21'),
(27, 'payslip-print', 0, 0, 0, 0, 1, '2026-07-21 17:50:21'),
(28, 'hierarchy-settings', 1, 0, 1, 0, 0, '2026-07-21 17:50:21'),
(5293, 'walk-in-candidates', 0, 1, 1, 1, 0, '2026-07-21 17:50:20');

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
(12, 10, NULL, '2026-07-21 21:00:00', 'Onsite', '', 'Scheduled', 'test 1', '2026-07-20 20:05:15', '2026-07-20 20:06:03'),
(13, 10, NULL, '2026-07-22 23:00:00', 'Onsite', '', 'Scheduled', 'test 3', '2026-07-20 20:06:46', '2026-07-20 20:07:09');

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

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `department_id`, `location`, `description`, `status`, `posted_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Junior / Associate Developer', 1, 'North Nazimabad Block D, Near Ship Owner College at Hill View Apartment karachi.', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since 1966, when designers at Letraset and James Mosley, the librarian at St Bride Printing Library in London, took a 1914 Cicero translation and scrambled it to make dummy text for Letraset\'s Body Type sheets. It has survived not only many decades, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised thanks to these sheets and more recently with desktop publishing software like Aldus PageMaker and Microsoft Word including versions of Lorem Ipsum. Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since 1966, when designers at Letraset and James Mosley, the librarian at St Bride Printing Library in London, took a 1914 Cicero translation and scrambled it to make dummy text for Letraset\'s Body Type sheets. It has survived not only many decades, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised thanks to these sheets and more recently with desktop publishing software like Aldus PageMaker and Microsoft Word including versions of Lorem Ipsum. Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since 1966, when designers at Letraset and James Mosley, the librarian at St Bride Printing Library in London, took a 1914 Cicero translation and scrambled it to make dummy text for Letraset\'s Body Type sheets. It has survived not only many decades, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised thanks to these sheets and more recently with desktop publishing software like Aldus PageMaker and Microsoft Word including versions of Lorem Ipsum.', 'Active', '2026-06-13', '2026-06-12 19:26:47', '2026-07-20 23:26:33', NULL),
(3, 'testtttt 111111111111111', 8, 'North Nazimabad Block D, Near Ship Owner College at Hill View Apartment karachi.', 'test  1111111111', 'Active', '2026-07-13', '2026-07-13 15:55:59', '2026-07-13 16:09:12', NULL),
(4, 'testtttt 22222222222', 3, 'North Nazimabad Block D, Near Ship Owner College at Hill View Apartment karachi.', 'test  222222222222', 'Active', '2026-07-13', '2026-07-13 15:58:31', '2026-07-13 16:08:59', NULL);

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
(46, 2, 'What is your current salary?', 'Text Answer', 1),
(47, 2, 'How many years of experience do you have?', 'Text Answer', 1),
(48, 2, 'Portfolio Link', 'Text Answer', 1),
(49, 2, 'LinkedIn Profile', 'Text Answer', 1),
(50, 2, 'When can you start?', 'Text Answer', 1);

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
(8, 3, 'test', 100, 80, 'answer answer  1'),
(9, 3, 'test', 100, 80, 'answer answer  2'),
(10, 3, 'Test', 100, 80, 'answer answer  3'),
(13, 4, 'testtttt', 100, 80, 'answer 1'),
(14, 4, 'test', 100, 80, 'answer 2'),
(15, 4, 'test', 100, 80, 'answer 3');

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
(3, 5, 2, 'Monthly', '2026-07-13', 3.00, 'On Track', '', '2026-07-13 18:32:49', NULL),
(4, 3, 1, 'Monthly', '2026-07-13', 3.00, 'On Track', 'General Feedback / Comments', '2026-07-13 18:34:12', NULL);

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
(4, 4, 2, '2026-05-08', '2026-05-08', 'testttt', 'uploads/leaves/leave_4_1781284874.pdf', 'Approved', '', '2026-06-12 17:21:14', '2026-06-12 17:22:03'),
(5, 3, 1, '2026-06-30', '2026-07-01', 'Test ', 'uploads/leaves/leave_3_1782760630.jpg', 'Approved', 'testttttt ', '2026-06-29 19:17:10', '2026-06-29 19:17:44'),
(6, 4, 1, '2026-06-30', '2026-07-01', 'testtttt', NULL, 'Approved', '', '2026-06-29 21:41:31', '2026-06-29 21:41:37'),
(7, 3, 3, '2026-07-07', '2026-07-07', 'testtttt ', 'uploads/leaves/leave_3_1783373405.jpg', 'Approved', '', '2026-07-06 21:30:05', '2026-07-06 21:30:47'),
(8, 3, 1, '2026-07-08', '2026-07-09', 'test', 'uploads/leaves/leave_3_1783964609.pdf', 'Rejected', '', '2026-07-13 17:43:29', '2026-07-13 17:43:43'),
(9, 3, 2, '2026-07-08', '2026-07-09', 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32', 'uploads/leaves/leave_3_1783964647.jpg', 'Approved', '', '2026-07-13 17:44:07', '2026-07-13 17:45:34'),
(10, 5, 3, '2026-07-14', '2026-07-20', 'test', 'uploads/leaves/leave_5_1784652740.pdf', 'Approved', '', '2026-07-21 16:52:20', '2026-07-21 16:52:29');

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
(1, 'Sick Leave', 8),
(2, 'Casual Leave', 8),
(3, 'Annual Leave', 12);

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
(1, 'New Company Announcement', 'New Announcement: Test Annountment. Check the announcements page for details.', 'announcements.php', 2, 'System', '2026-06-11 21:44:59', NULL),
(2, 'New Company Announcement', 'New Announcement: Test Annountment. Check the announcements page for details.', 'announcements.php', 2, 'System', '2026-06-11 21:45:46', NULL),
(3, 'Upcoming Event', 'New Event: Test Event on 12 Jun, 2026 at 03:00 AM.', 'event-calendar.php', 2, 'System', '2026-06-11 21:49:38', NULL),
(4, 'Event Updated', 'Event Updated: Test Event is scheduled for 12 Jun, 2026 at 03:00 AM.', 'event-calendar.php', 2, 'System', '2026-06-11 21:49:51', NULL),
(5, 'Event Updated', 'Event Updated: Test Event is scheduled for 12 Jun, 2026 at 03:00 AM.', 'event-calendar.php', 2, 'System', '2026-06-11 21:50:14', NULL),
(6, 'Upcoming Event', 'New Event: test event  on 12 Jun, 2026 at 03:51 AM.', 'event-calendar.php', 2, 'System', '2026-06-11 21:51:06', NULL),
(7, 'Upcoming Event', 'New Event: Test Event on 12 Jun, 2026 at 03:56 AM.', 'event-calendar.php', 2, 'System', '2026-06-11 21:56:10', NULL),
(8, 'Upcoming Event', 'New Event: Test Event on 12 Jun, 2026 at 03:00 AM.', 'event-calendar.php', 2, 'System', '2026-06-11 21:56:41', NULL),
(9, 'Event Updated', 'Event Updated: Test Event is scheduled for 12 Jun, 2026 at 03:00 AM.', 'event-calendar.php', 2, 'System', '2026-06-11 21:56:51', NULL),
(10, 'Event Updated', 'Event Updated: Test Event is scheduled for 12 Jun, 2026 at 03:00 AM.', 'event-calendar.php', 2, 'System', '2026-06-11 21:57:24', NULL),
(11, 'New Company Announcement', 'New Announcement: Test Annountment. Check the announcements page for details.', 'announcements.php', 2, 'System', '2026-06-11 22:16:24', NULL),
(12, 'Announcement Updated', 'Announcement Updated: Test Annountment. Check the announcements page for details.', 'announcements.php', 2, 'System', '2026-06-11 22:16:56', NULL),
(13, 'New Company Announcement', 'New Announcement: test. Check the announcements page for details.', 'announcements.php', 2, 'System', '2026-06-11 22:38:36', NULL),
(14, 'New Company Announcement', 'New Announcement: sadsad. Check the announcements page for details.', 'announcements.php', 2, 'System', '2026-06-11 22:39:22', NULL),
(15, 'Announcement Updated', 'Announcement Updated: sadsad. Check the announcements page for details.', 'announcements.php', 2, 'System', '2026-06-11 22:39:35', NULL),
(16, 'New Company Announcement', 'New Announcement: asdsda. Check the announcements page for details.', 'announcements.php', 2, 'System', '2026-06-11 22:40:07', NULL),
(17, 'New Leave Request Submitted', 'Syed Mahad Bukhari has submitted a new leave request (Sick Leave, From 12 Jun, 2026 to 15 Jun, 2026). Awaiting your approval.', 'leave-management.php', 3, 'Leave', '2026-06-11 23:06:16', NULL),
(18, 'New Leave Request Submitted', 'Syed Mahad Bukhari has submitted a new leave request (Sick Leave, From 12 Jun, 2026 to 15 Jun, 2026). Awaiting your approval.', 'leave-management.php', 3, 'Leave', '2026-06-11 23:14:49', NULL),
(19, 'Leave Request Approved', 'Your leave request has been Approved.', 'leave-management.php', 2, 'Leave', '2026-06-11 23:14:56', NULL),
(20, 'New Company Policy', 'New company policy available: testtttt. Please review it in Company Policies.', 'policies.php', 2, 'System', '2026-06-11 23:25:41', NULL),
(21, 'New Leave Request Submitted', 'Muhammad Zain Ul Abiden has submitted a new leave request (Casual Leave, From 01 May, 2026 to 01 May, 2026). Awaiting your approval.', 'leave-management.php', 4, 'Leave', '2026-06-12 16:52:33', NULL),
(22, 'Leave Request Approved', 'Your leave request has been Approved.', 'leave-management.php', 2, 'Leave', '2026-06-12 16:53:42', NULL),
(23, 'New Leave Request Submitted', 'Muhammad Zain Ul Abiden has submitted a new leave request (Casual Leave, From 08 May, 2026 to 08 May, 2026). Awaiting your approval.', 'leave-management.php', 4, 'Leave', '2026-06-12 17:21:14', NULL),
(24, 'Leave Request Approved', 'Your leave request has been Approved.', 'leave-management.php', 2, 'Leave', '2026-06-12 17:22:03', NULL),
(25, 'New Job Application', 'New application received from Test for position: testtttt.', 'job-candidates.php', NULL, 'Recruitment', '2026-06-12 17:30:44', NULL),
(26, 'New Job Application', 'New application received from Mahad for position: Junior / Associate Developer.', 'job-candidates.php', NULL, 'Recruitment', '2026-06-12 19:27:43', NULL),
(27, 'New Job Application', 'New application received from test 1 for position: Junior / Associate Developer.', 'job-candidates.php', NULL, 'Recruitment', '2026-06-12 20:44:37', NULL),
(28, 'New Job Application', 'New application received from test 2 for position: Junior / Associate Developer.', 'job-candidates.php', NULL, 'Recruitment', '2026-06-12 20:45:20', NULL),
(29, 'New Job Application', 'New application received from test 3 for position: Junior / Associate Developer.', 'job-candidates.php', NULL, 'Recruitment', '2026-06-12 20:46:12', NULL),
(30, 'New Job Application', 'New application received from test for position: Junior / Associate Developer.', 'job-candidates.php', NULL, 'Recruitment', '2026-06-12 20:47:15', NULL),
(31, 'New Company Announcement', 'New Announcement: . Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-06-17 17:25:18', NULL),
(32, 'New Company Announcement', 'New Announcement: . Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-06-17 17:25:53', NULL),
(33, 'New Company Announcement', 'New Announcement: sadsdasda. Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-06-17 17:36:36', NULL),
(34, 'New Company Announcement', 'New Announcement: . Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-06-17 17:43:38', NULL),
(35, 'Upcoming Event', 'New Event: asdad on 19 Jun, 2026 at 03:14 AM.', 'event-calendar.php', 2, 'System', '2026-06-18 21:14:44', NULL),
(36, 'New Company Announcement', 'New Announcement: sadsdasda. Check the announcements page for details.', 'announcements.php', 2, 'System', '2026-06-18 21:32:17', NULL),
(37, 'New Leave Request Submitted', 'Syed Mahad Bukhari has submitted a new leave request (Sick Leave, From 30 Jun, 2026 to 01 Jul, 2026). Awaiting your approval.', 'leave-management.php', 3, 'Leave', '2026-06-29 19:17:10', NULL),
(38, 'Leave Request Rejected', 'Your leave request has been Rejected. Remarks: testttttt ...', 'leave-management.php', 1, 'Leave', '2026-06-29 19:17:35', NULL),
(39, 'Leave Request Approved', 'Your leave request has been Approved. Remarks: testttttt ...', 'leave-management.php', 1, 'Leave', '2026-06-29 19:17:44', NULL),
(40, 'Upcoming Event', 'New Event: testttt on 30 Jun, 2026 at 12:22 AM.', 'event-calendar.php', 1, 'System', '2026-06-29 19:20:54', NULL),
(41, 'Event Updated', 'Event Updated: testttt is scheduled for 30 Jun, 2026 at 12:22 AM.', 'event-calendar.php', 1, 'System', '2026-06-29 19:21:22', NULL),
(42, 'New Company Policy', 'New company policy available: testttttt. Please review it in Company Policies.', 'policies.php', 1, 'System', '2026-06-29 19:22:20', NULL),
(43, 'New Leave Request Submitted', 'Muhammad Zain Ul Abiden has submitted a new leave request (Sick Leave, From 30 Jun, 2026 to 01 Jul, 2026). Awaiting your approval.', 'leave-management.php', 4, 'Leave', '2026-06-29 21:41:31', NULL),
(44, 'Leave Request Approved', 'Your leave request has been Approved.', 'leave-management.php', 1, 'Leave', '2026-06-29 21:41:38', NULL),
(45, 'New Company Announcement', 'New Announcement: sadsdasda. Check the announcements page for details.', 'announcements.php', 2, 'System', '2026-07-02 20:17:24', NULL),
(47, 'New Company Policy', 'New company policy available: test CSRF. Please review it in Company Policies.', 'policies', 1, 'System', '2026-07-06 21:03:41', NULL),
(48, 'New Leave Request Submitted', 'Syed Mahad Bukhari has submitted a new leave request (Annual Leave, From 07 Jul, 2026 to 07 Jul, 2026). Awaiting your approval.', 'leave-management.php', 3, 'Leave', '2026-07-06 21:30:05', NULL),
(49, 'Leave Request Approved', 'Your leave request has been Approved.', 'leave-management.php', 1, 'Leave', '2026-07-06 21:30:47', NULL),
(50, 'New Job Application', 'New application received from Syed Mahad bukhari for position: testtttt 111111111111111.', 'job-candidates.php', NULL, 'Recruitment', '2026-07-13 16:14:59', NULL),
(51, 'New Job Application', 'New application received from Syed Mahad bukhari for position: Junior / Associate Developer.', 'job-candidates.php', NULL, 'Recruitment', '2026-07-13 16:17:55', NULL),
(52, 'New Job Application', 'New application received from Syed Mahad bukhari for position: Junior / Associate Developer.', 'job-candidates.php', NULL, 'Recruitment', '2026-07-13 16:29:20', NULL),
(53, 'New Job Application', 'New application received from Syed Mahad bukhari for position: Junior / Associate Developer.', 'job-candidates.php', NULL, 'Recruitment', '2026-07-13 17:07:27', NULL),
(54, 'New Job Application', 'New application received from test for position: testtttt 22222222222.', 'job-candidates.php', NULL, 'Recruitment', '2026-07-13 17:19:27', NULL),
(55, 'New Job Application', 'New application received from test 1 for position: testtttt 22222222222.', 'job-candidates.php', NULL, 'Recruitment', '2026-07-13 17:20:17', NULL),
(56, 'New Job Application', 'New application received from test 2 for position: testtttt 22222222222.', 'job-candidates.php', NULL, 'Recruitment', '2026-07-13 17:20:58', NULL),
(57, 'New Job Application', 'New application received from test 3 for position: testtttt 22222222222.', 'job-candidates.php', NULL, 'Recruitment', '2026-07-13 17:22:10', NULL),
(58, 'New Leave Request Submitted', 'Syed Mahad Bukhari has submitted a new leave request (Sick Leave, From 08 Jul, 2026 to 09 Jul, 2026). Awaiting your approval.', 'leave-management.php', 3, 'Leave', '2026-07-13 17:43:29', NULL),
(59, 'Leave Request Rejected', 'Your leave request has been Rejected.', 'leave-management.php', 1, 'Leave', '2026-07-13 17:43:43', NULL),
(60, 'New Leave Request Submitted', 'Syed Mahad Bukhari has submitted a new leave request (Casual Leave, From 08 Jul, 2026 to 09 Jul, 2026). Awaiting your approval.', 'leave-management.php', 3, 'Leave', '2026-07-13 17:44:07', NULL),
(61, 'Leave Request Approved', 'Your leave request has been Approved.', 'leave-management.php', 1, 'Leave', '2026-07-13 17:45:35', NULL),
(62, 'Upcoming Event', 'New Event: test on 13 Jul, 2026 at 11:00 PM.', 'event-calendar.php', 1, 'System', '2026-07-13 17:46:51', NULL),
(63, 'Event Updated', 'Event Updated: test is scheduled for 13 Jul, 2026 at 11:00 PM.', 'event-calendar.php', 1, 'System', '2026-07-13 17:47:05', NULL),
(64, 'Event Updated', 'Event Updated: test is scheduled for 13 Jul, 2026 at 11:00 PM.', 'event-calendar.php', 1, 'System', '2026-07-13 17:47:24', NULL),
(65, 'Event Updated', 'Event Updated: test is scheduled for 13 Jul, 2026 at 11:00 PM.', 'event-calendar.php', 1, 'System', '2026-07-13 17:48:02', NULL),
(66, 'Event Updated', 'Event Updated: test is scheduled for 13 Jul, 2026 at 11:00 PM.', 'event-calendar.php', 2, 'System', '2026-07-13 17:55:23', NULL),
(67, 'Event Updated', 'Event Updated: test is scheduled for 13 Jul, 2026 at 11:00 PM.', 'event-calendar.php', 2, 'System', '2026-07-13 17:55:34', NULL),
(68, 'Event Updated', 'Event Updated: test is scheduled for 13 Jul, 2026 at 11:00 PM.', 'event-calendar.php', 2, 'System', '2026-07-13 18:04:39', NULL),
(69, 'Event Updated', 'Event Updated: test is scheduled for 13 Jul, 2026 at 11:00 PM.', 'event-calendar.php', 1, 'System', '2026-07-13 18:06:44', NULL),
(70, 'Upcoming Event', 'New Event: test on 13 Jul, 2026 at 11:15 PM.', 'event-calendar.php', 1, 'System', '2026-07-13 18:11:28', NULL),
(71, 'Event Updated', 'Event Updated: test is scheduled for 13 Jul, 2026 at 11:15 PM.', 'event-calendar.php', 1, 'System', '2026-07-13 18:11:47', NULL),
(72, 'Event Updated', 'Event Updated: test is scheduled for 13 Jul, 2026 at 11:15 PM.', 'event-calendar.php', 2, 'System', '2026-07-13 18:12:13', NULL),
(73, 'Event Updated', 'Event Updated: test is scheduled for 13 Jul, 2026 at 11:15 PM.', 'event-calendar.php', 2, 'System', '2026-07-13 18:15:36', NULL),
(74, 'New Company Announcement', 'New Announcement: Announcement Title Test. Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-07-13 18:38:48', NULL),
(75, 'New Company Announcement', 'New Announcement: Announcement Title. Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-07-13 18:40:45', NULL),
(76, 'New Company Announcement', 'New Announcement: Announcement Title. Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-07-13 18:41:29', NULL),
(77, 'New Company Announcement', 'New Announcement: Announcement Title . Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-07-13 18:47:38', NULL),
(78, 'New Company Announcement', 'New Announcement: Announcement Title . Check the announcements page for details.', 'announcements.php', 1, 'System', '2026-07-13 18:48:18', NULL),
(79, 'New Company Policy', 'New company policy available: Employee Leave Policy. Please review it in Company Policies.', 'policies', 1, 'System', '2026-07-13 18:54:16', NULL),
(80, 'New Company Policy', 'New company policy available: Employee Working Hours and Attendance Policy. Please review it in Company Policies.', 'policies', 1, 'System', '2026-07-13 18:54:59', NULL),
(81, 'New Company Policy', 'New company policy available: Employee Working Hours and Attendance Policy. Please review it in Company Policies.', 'policies', 2, 'System', '2026-07-13 19:54:32', NULL),
(82, 'New Job Application', 'New application received from testing for position: testtttt 22222222222.', 'job-candidates.php', NULL, 'Recruitment', '2026-07-20 22:56:45', NULL),
(83, 'New Job Application', 'New application received from testest for position: testtttt 22222222222.', 'job-candidates.php', NULL, 'Recruitment', '2026-07-20 22:57:55', NULL),
(84, 'New Leave Request Submitted', 'Shayan Shaikh has submitted a new leave request (Annual Leave, From 14 Jul, 2026 to 20 Jul, 2026). Awaiting your approval.', 'leave-management.php', 5, 'Leave', '2026-07-21 16:52:20', NULL),
(85, 'Leave Request Approved', 'Your leave request has been Approved.', 'leave-management.php', 2, 'Leave', '2026-07-21 16:52:29', NULL);

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
(1, 1, 7, 0, NULL),
(2, 1, 8, 0, NULL),
(4, 2, 4, 0, NULL),
(5, 2, 5, 0, NULL),
(6, 2, 6, 0, NULL),
(7, 3, 7, 0, NULL),
(8, 3, 8, 0, NULL),
(10, 4, 4, 0, NULL),
(11, 4, 5, 0, NULL),
(12, 4, 6, 0, NULL),
(13, 4, 7, 0, NULL),
(14, 4, 8, 0, NULL),
(15, 5, 1, 1, '2026-07-06 21:42:46'),
(17, 5, 4, 0, NULL),
(18, 5, 5, 0, NULL),
(19, 5, 6, 0, NULL),
(20, 5, 7, 0, NULL),
(21, 5, 8, 0, NULL),
(22, 5, 9, 0, NULL),
(23, 5, 10, 0, NULL),
(24, 5, 11, 0, NULL),
(25, 5, 12, 0, NULL),
(26, 5, 13, 0, NULL),
(27, 5, 14, 0, NULL),
(28, 5, 15, 0, NULL),
(29, 5, 16, 0, NULL),
(30, 5, 17, 0, NULL),
(31, 5, 18, 0, NULL),
(32, 5, 19, 0, NULL),
(33, 5, 20, 0, NULL),
(35, 6, 4, 0, NULL),
(36, 6, 5, 0, NULL),
(37, 6, 6, 0, NULL),
(38, 6, 7, 0, NULL),
(39, 6, 8, 0, NULL),
(41, 7, 4, 0, NULL),
(42, 7, 5, 0, NULL),
(43, 7, 6, 0, NULL),
(44, 7, 7, 0, NULL),
(45, 7, 8, 0, NULL),
(46, 8, 7, 0, NULL),
(47, 8, 8, 0, NULL),
(49, 9, 4, 0, NULL),
(50, 9, 5, 0, NULL),
(51, 9, 6, 0, NULL),
(52, 9, 7, 0, NULL),
(53, 9, 8, 0, NULL),
(55, 10, 4, 0, NULL),
(56, 10, 5, 0, NULL),
(57, 10, 6, 0, NULL),
(58, 10, 7, 0, NULL),
(59, 10, 8, 0, NULL),
(60, 11, 7, 0, NULL),
(61, 11, 8, 0, NULL),
(63, 12, 4, 0, NULL),
(64, 12, 5, 0, NULL),
(65, 12, 6, 0, NULL),
(67, 13, 4, 0, NULL),
(68, 13, 5, 0, NULL),
(69, 13, 6, 0, NULL),
(70, 14, 7, 0, NULL),
(71, 14, 8, 0, NULL),
(73, 15, 4, 0, NULL),
(74, 15, 5, 0, NULL),
(75, 15, 6, 0, NULL),
(77, 16, 4, 0, NULL),
(78, 16, 5, 0, NULL),
(79, 16, 6, 0, NULL),
(80, 17, 1, 1, '2026-07-20 16:56:44'),
(81, 17, 2, 1, '2026-07-13 21:27:22'),
(82, 18, 1, 1, '2026-07-20 16:56:44'),
(83, 18, 2, 1, '2026-07-13 21:27:22'),
(86, 20, 4, 0, NULL),
(87, 20, 5, 0, NULL),
(88, 20, 6, 0, NULL),
(89, 20, 7, 0, NULL),
(90, 20, 8, 0, NULL),
(91, 20, 9, 0, NULL),
(92, 20, 10, 0, NULL),
(93, 20, 11, 0, NULL),
(94, 20, 12, 0, NULL),
(95, 20, 13, 0, NULL),
(96, 20, 14, 0, NULL),
(97, 20, 15, 0, NULL),
(98, 20, 16, 0, NULL),
(99, 20, 17, 0, NULL),
(100, 20, 18, 0, NULL),
(101, 20, 19, 0, NULL),
(102, 20, 20, 0, NULL),
(103, 21, 1, 1, '2026-07-20 16:56:44'),
(104, 21, 2, 1, '2026-07-13 21:27:22'),
(105, 22, 4, 0, NULL),
(106, 23, 1, 1, '2026-07-20 16:56:44'),
(107, 23, 2, 1, '2026-07-13 21:27:22'),
(108, 24, 4, 0, NULL),
(109, 25, 1, 1, '2026-07-20 16:56:44'),
(110, 25, 2, 1, '2026-07-13 21:27:22'),
(111, 26, 1, 1, '2026-07-20 16:56:44'),
(112, 26, 2, 1, '2026-07-13 21:27:22'),
(113, 27, 1, 1, '2026-07-20 16:56:44'),
(114, 27, 2, 1, '2026-07-13 21:27:22'),
(116, 28, 2, 1, '2026-07-13 21:27:22'),
(117, 29, 1, 1, '2026-07-20 16:56:44'),
(118, 29, 2, 1, '2026-07-13 21:27:22'),
(119, 30, 1, 1, '2026-07-20 16:56:44'),
(120, 30, 2, 1, '2026-07-13 21:27:22'),
(121, 31, 2, 1, '2026-07-13 21:27:22'),
(123, 31, 4, 0, NULL),
(124, 31, 5, 0, NULL),
(125, 31, 6, 0, NULL),
(126, 31, 7, 0, NULL),
(127, 31, 8, 0, NULL),
(128, 31, 9, 0, NULL),
(129, 31, 10, 0, NULL),
(130, 31, 11, 0, NULL),
(131, 31, 12, 0, NULL),
(132, 31, 13, 0, NULL),
(133, 31, 14, 0, NULL),
(134, 31, 15, 0, NULL),
(135, 31, 16, 0, NULL),
(136, 31, 17, 0, NULL),
(137, 31, 18, 0, NULL),
(138, 31, 19, 0, NULL),
(139, 31, 20, 0, NULL),
(140, 32, 2, 1, '2026-07-13 21:27:22'),
(142, 32, 4, 0, NULL),
(143, 32, 5, 0, NULL),
(144, 32, 6, 0, NULL),
(145, 32, 7, 0, NULL),
(146, 32, 8, 0, NULL),
(147, 32, 9, 0, NULL),
(148, 32, 10, 0, NULL),
(149, 32, 11, 0, NULL),
(150, 32, 12, 0, NULL),
(151, 32, 13, 0, NULL),
(152, 32, 14, 0, NULL),
(153, 32, 15, 0, NULL),
(154, 32, 16, 0, NULL),
(155, 32, 17, 0, NULL),
(156, 32, 18, 0, NULL),
(157, 32, 19, 0, NULL),
(158, 32, 20, 0, NULL),
(159, 33, 2, 1, '2026-07-06 22:21:21'),
(161, 33, 4, 0, NULL),
(162, 33, 5, 0, NULL),
(163, 33, 6, 0, NULL),
(164, 33, 7, 0, NULL),
(165, 33, 8, 0, NULL),
(166, 33, 9, 0, NULL),
(167, 33, 10, 0, NULL),
(168, 33, 11, 0, NULL),
(169, 33, 12, 0, NULL),
(170, 33, 13, 0, NULL),
(171, 33, 14, 0, NULL),
(172, 33, 15, 0, NULL),
(173, 33, 16, 0, NULL),
(174, 33, 17, 0, NULL),
(175, 33, 18, 0, NULL),
(176, 33, 19, 0, NULL),
(177, 33, 20, 0, NULL),
(178, 34, 2, 1, '2026-07-13 21:27:22'),
(180, 34, 4, 0, NULL),
(181, 34, 5, 0, NULL),
(182, 34, 6, 0, NULL),
(183, 34, 7, 0, NULL),
(184, 34, 8, 0, NULL),
(185, 34, 9, 0, NULL),
(186, 34, 10, 0, NULL),
(187, 34, 11, 0, NULL),
(188, 34, 12, 0, NULL),
(189, 34, 13, 0, NULL),
(190, 34, 14, 0, NULL),
(191, 34, 15, 0, NULL),
(192, 34, 16, 0, NULL),
(193, 34, 17, 0, NULL),
(194, 34, 18, 0, NULL),
(195, 34, 19, 0, NULL),
(196, 34, 20, 0, NULL),
(197, 35, 1, 1, '2026-07-20 16:56:44'),
(199, 35, 4, 0, NULL),
(200, 35, 5, 0, NULL),
(201, 35, 6, 0, NULL),
(202, 35, 7, 0, NULL),
(203, 35, 8, 0, NULL),
(204, 35, 9, 0, NULL),
(205, 35, 10, 0, NULL),
(206, 35, 11, 0, NULL),
(207, 35, 12, 0, NULL),
(208, 35, 13, 0, NULL),
(209, 35, 14, 0, NULL),
(210, 35, 15, 0, NULL),
(211, 35, 16, 0, NULL),
(212, 35, 17, 0, NULL),
(213, 35, 18, 0, NULL),
(214, 35, 19, 0, NULL),
(215, 36, 1, 1, '2026-07-20 16:56:44'),
(217, 36, 4, 0, NULL),
(218, 36, 5, 0, NULL),
(219, 36, 6, 0, NULL),
(220, 36, 7, 0, NULL),
(221, 36, 8, 0, NULL),
(222, 36, 9, 0, NULL),
(223, 36, 10, 0, NULL),
(224, 36, 11, 0, NULL),
(225, 36, 12, 0, NULL),
(226, 36, 13, 0, NULL),
(227, 36, 14, 0, NULL),
(228, 36, 15, 0, NULL),
(229, 36, 16, 0, NULL),
(230, 36, 17, 0, NULL),
(231, 36, 18, 0, NULL),
(232, 36, 19, 0, NULL),
(233, 37, 1, 1, '2026-07-20 16:56:44'),
(237, 40, 2, 1, '2026-07-13 21:27:22'),
(239, 40, 4, 0, NULL),
(240, 40, 5, 0, NULL),
(241, 40, 6, 0, NULL),
(242, 40, 7, 0, NULL),
(243, 40, 8, 0, NULL),
(244, 40, 9, 0, NULL),
(245, 40, 10, 0, NULL),
(246, 40, 11, 0, NULL),
(247, 40, 12, 0, NULL),
(248, 40, 13, 0, NULL),
(249, 40, 14, 0, NULL),
(250, 40, 15, 0, NULL),
(251, 40, 16, 0, NULL),
(252, 40, 17, 0, NULL),
(253, 40, 18, 0, NULL),
(254, 40, 19, 0, NULL),
(257, 41, 4, 0, NULL),
(258, 41, 5, 0, NULL),
(259, 41, 6, 0, NULL),
(260, 41, 7, 0, NULL),
(261, 41, 8, 0, NULL),
(262, 41, 9, 0, NULL),
(263, 41, 10, 0, NULL),
(264, 41, 11, 0, NULL),
(265, 41, 12, 0, NULL),
(266, 41, 13, 0, NULL),
(267, 41, 14, 0, NULL),
(268, 41, 15, 0, NULL),
(269, 41, 16, 0, NULL),
(270, 41, 17, 0, NULL),
(271, 41, 18, 0, NULL),
(272, 41, 19, 0, NULL),
(274, 42, 4, 0, NULL),
(275, 42, 5, 0, NULL),
(276, 42, 6, 0, NULL),
(277, 42, 7, 0, NULL),
(278, 42, 8, 0, NULL),
(279, 42, 9, 0, NULL),
(280, 42, 10, 0, NULL),
(281, 42, 11, 0, NULL),
(282, 42, 12, 0, NULL),
(283, 42, 13, 0, NULL),
(284, 42, 14, 0, NULL),
(285, 42, 15, 0, NULL),
(286, 42, 16, 0, NULL),
(287, 42, 17, 0, NULL),
(288, 42, 18, 0, NULL),
(289, 42, 19, 0, NULL),
(290, 43, 1, 1, '2026-07-20 16:56:44'),
(291, 43, 2, 1, '2026-07-06 22:21:18'),
(292, 44, 4, 0, NULL),
(293, 45, 1, 1, '2026-07-20 16:56:44'),
(295, 45, 4, 0, NULL),
(296, 45, 5, 0, NULL),
(297, 45, 6, 0, NULL),
(298, 45, 7, 0, NULL),
(299, 45, 8, 0, NULL),
(300, 45, 9, 0, NULL),
(301, 45, 10, 0, NULL),
(302, 45, 11, 0, NULL),
(303, 45, 12, 0, NULL),
(304, 45, 13, 0, NULL),
(305, 45, 14, 0, NULL),
(306, 45, 15, 0, NULL),
(307, 45, 16, 0, NULL),
(308, 45, 17, 0, NULL),
(309, 45, 18, 0, NULL),
(310, 45, 19, 0, NULL),
(329, 47, 4, 0, NULL),
(330, 47, 5, 0, NULL),
(331, 47, 6, 0, NULL),
(332, 47, 7, 0, NULL),
(333, 47, 8, 0, NULL),
(334, 47, 9, 0, NULL),
(335, 47, 10, 0, NULL),
(336, 47, 11, 0, NULL),
(337, 47, 12, 0, NULL),
(338, 47, 13, 0, NULL),
(339, 47, 14, 0, NULL),
(340, 47, 15, 0, NULL),
(341, 47, 16, 0, NULL),
(342, 47, 17, 0, NULL),
(343, 47, 18, 0, NULL),
(344, 47, 19, 0, NULL),
(345, 48, 1, 1, '2026-07-20 16:56:44'),
(346, 48, 2, 1, '2026-07-06 22:21:17'),
(348, 50, 1, 1, '2026-07-20 16:56:44'),
(349, 50, 2, 1, '2026-07-13 21:27:22'),
(350, 51, 1, 1, '2026-07-20 16:56:44'),
(351, 51, 2, 1, '2026-07-13 21:27:22'),
(352, 52, 1, 1, '2026-07-20 16:56:44'),
(353, 52, 2, 1, '2026-07-13 21:27:22'),
(354, 53, 1, 1, '2026-07-20 16:56:44'),
(355, 53, 2, 1, '2026-07-13 21:27:22'),
(356, 54, 1, 1, '2026-07-20 16:56:44'),
(357, 54, 2, 1, '2026-07-13 21:27:22'),
(358, 55, 1, 1, '2026-07-20 16:56:44'),
(359, 55, 2, 1, '2026-07-13 21:27:22'),
(360, 56, 1, 1, '2026-07-20 16:56:44'),
(361, 56, 2, 1, '2026-07-13 21:27:22'),
(362, 57, 1, 1, '2026-07-20 16:56:44'),
(363, 57, 2, 1, '2026-07-13 21:27:22'),
(364, 58, 1, 1, '2026-07-20 16:56:44'),
(365, 58, 2, 1, '2026-07-13 21:27:22'),
(367, 60, 1, 1, '2026-07-20 16:56:44'),
(368, 60, 2, 1, '2026-07-13 21:27:22'),
(370, 62, 2, 1, '2026-07-13 21:27:22'),
(372, 62, 4, 0, NULL),
(373, 62, 5, 0, NULL),
(374, 62, 6, 0, NULL),
(375, 62, 7, 0, NULL),
(376, 62, 8, 0, NULL),
(377, 62, 9, 0, NULL),
(378, 62, 10, 0, NULL),
(379, 62, 11, 0, NULL),
(380, 62, 12, 0, NULL),
(381, 62, 13, 0, NULL),
(382, 62, 14, 0, NULL),
(383, 62, 15, 0, NULL),
(384, 62, 16, 0, NULL),
(385, 62, 17, 0, NULL),
(386, 62, 18, 0, NULL),
(387, 62, 19, 0, NULL),
(388, 63, 2, 1, '2026-07-13 21:27:22'),
(390, 63, 4, 0, NULL),
(391, 63, 5, 0, NULL),
(392, 63, 6, 0, NULL),
(393, 63, 7, 0, NULL),
(394, 63, 8, 0, NULL),
(395, 63, 9, 0, NULL),
(396, 63, 10, 0, NULL),
(397, 63, 11, 0, NULL),
(398, 63, 12, 0, NULL),
(399, 63, 13, 0, NULL),
(400, 63, 14, 0, NULL),
(401, 63, 15, 0, NULL),
(402, 63, 16, 0, NULL),
(403, 63, 17, 0, NULL),
(404, 63, 18, 0, NULL),
(405, 63, 19, 0, NULL),
(406, 64, 7, 0, NULL),
(407, 64, 8, 0, NULL),
(408, 64, 17, 0, NULL),
(409, 64, 18, 0, NULL),
(410, 64, 19, 0, NULL),
(411, 65, 7, 0, NULL),
(412, 65, 8, 0, NULL),
(413, 65, 17, 0, NULL),
(414, 65, 18, 0, NULL),
(415, 65, 19, 0, NULL),
(416, 65, 2, 1, '2026-07-13 21:27:22'),
(417, 65, 11, 0, NULL),
(418, 66, 7, 0, NULL),
(419, 66, 8, 0, NULL),
(420, 66, 17, 0, NULL),
(421, 66, 18, 0, NULL),
(422, 66, 19, 0, NULL),
(423, 66, 11, 0, NULL),
(424, 67, 9, 0, NULL),
(425, 67, 10, 0, NULL),
(427, 68, 4, 0, NULL),
(428, 68, 5, 0, NULL),
(429, 68, 6, 0, NULL),
(430, 69, 9, 0, NULL),
(431, 69, 10, 0, NULL),
(432, 70, 2, 1, '2026-07-13 21:27:22'),
(434, 70, 4, 0, NULL),
(435, 70, 5, 0, NULL),
(436, 70, 6, 0, NULL),
(437, 70, 7, 0, NULL),
(438, 70, 8, 0, NULL),
(439, 70, 9, 0, NULL),
(440, 70, 10, 0, NULL),
(441, 70, 11, 0, NULL),
(442, 70, 12, 0, NULL),
(443, 70, 13, 0, NULL),
(444, 70, 14, 0, NULL),
(445, 70, 15, 0, NULL),
(446, 70, 16, 0, NULL),
(447, 70, 17, 0, NULL),
(448, 70, 18, 0, NULL),
(449, 70, 19, 0, NULL),
(450, 71, 7, 0, NULL),
(451, 71, 8, 0, NULL),
(453, 72, 4, 0, NULL),
(454, 72, 5, 0, NULL),
(455, 72, 6, 0, NULL),
(456, 73, 9, 0, NULL),
(457, 73, 10, 0, NULL),
(458, 74, 2, 1, '2026-07-13 21:27:22'),
(460, 74, 4, 0, NULL),
(461, 74, 5, 0, NULL),
(462, 74, 6, 0, NULL),
(463, 74, 7, 0, NULL),
(464, 74, 8, 0, NULL),
(465, 74, 9, 0, NULL),
(466, 74, 10, 0, NULL),
(467, 74, 11, 0, NULL),
(468, 74, 12, 0, NULL),
(469, 74, 13, 0, NULL),
(470, 74, 14, 0, NULL),
(471, 74, 15, 0, NULL),
(472, 74, 16, 0, NULL),
(473, 74, 17, 0, NULL),
(474, 74, 18, 0, NULL),
(475, 74, 19, 0, NULL),
(476, 75, 7, 0, NULL),
(477, 75, 8, 0, NULL),
(478, 76, 7, 0, NULL),
(479, 76, 8, 0, NULL),
(480, 77, 7, 0, NULL),
(481, 77, 8, 0, NULL),
(482, 78, 3, 0, NULL),
(483, 78, 4, 0, NULL),
(484, 78, 5, 0, NULL),
(485, 78, 6, 0, NULL),
(486, 79, 3, 0, NULL),
(487, 79, 4, 0, NULL),
(488, 79, 5, 0, NULL),
(489, 79, 6, 0, NULL),
(490, 79, 7, 0, NULL),
(491, 79, 8, 0, NULL),
(492, 79, 9, 0, NULL),
(493, 79, 10, 0, NULL),
(494, 79, 11, 0, NULL),
(495, 79, 12, 0, NULL),
(496, 79, 13, 0, NULL),
(497, 79, 14, 0, NULL),
(498, 79, 15, 0, NULL),
(499, 79, 16, 0, NULL),
(500, 79, 17, 0, NULL),
(501, 79, 18, 0, NULL),
(502, 79, 19, 0, NULL),
(503, 80, 3, 0, NULL),
(504, 80, 4, 0, NULL),
(505, 80, 5, 0, NULL),
(506, 80, 6, 0, NULL),
(507, 80, 7, 0, NULL),
(508, 80, 8, 0, NULL),
(509, 80, 9, 0, NULL),
(510, 80, 10, 0, NULL),
(511, 80, 11, 0, NULL),
(512, 80, 12, 0, NULL),
(513, 80, 13, 0, NULL),
(514, 80, 14, 0, NULL),
(515, 80, 15, 0, NULL),
(516, 80, 16, 0, NULL),
(517, 80, 17, 0, NULL),
(518, 80, 18, 0, NULL),
(519, 80, 19, 0, NULL),
(520, 81, 3, 0, NULL),
(521, 81, 4, 0, NULL),
(522, 81, 5, 0, NULL),
(523, 81, 6, 0, NULL),
(524, 81, 7, 0, NULL),
(525, 81, 8, 0, NULL),
(526, 81, 9, 0, NULL),
(527, 81, 10, 0, NULL),
(528, 81, 11, 0, NULL),
(529, 81, 12, 0, NULL),
(530, 81, 13, 0, NULL),
(531, 81, 14, 0, NULL),
(532, 81, 15, 0, NULL),
(533, 81, 16, 0, NULL),
(534, 81, 17, 0, NULL),
(535, 81, 18, 0, NULL),
(536, 81, 19, 0, NULL),
(537, 82, 1, 0, NULL),
(538, 82, 2, 1, '2026-07-21 15:57:46'),
(539, 83, 1, 0, NULL),
(540, 83, 2, 1, '2026-07-21 15:57:46'),
(541, 84, 1, 0, NULL),
(542, 84, 2, 0, NULL),
(543, 85, 5, 0, NULL);

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

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`id`, `employee_id`, `month_year`, `basic_salary`, `allowances`, `deductions`, `net_payable`, `payment_method`, `status`, `paid_date`, `created_at`, `updated_at`, `house_rent`, `utility`, `fuel`, `mobile`, `medical`, `leaves_count`, `lates_count`, `halfdays_count`, `loan_deduction`, `provident_fund`, `professional_tax`, `other_deduction`) VALUES
(8, 4, '2026-02', 45000.00, 0.00, 4500.00, 85500.00, 'Bank Transfer', 'Paid', NULL, '2026-06-12 17:11:37', NULL, 18000.00, 9000.00, 4500.00, 4500.00, 9000.00, 1, 2, 1, 0.00, 0.00, 0.00, 0.00),
(9, 4, '2026-03', 45000.00, 0.00, 7500.00, 82500.00, 'Bank Transfer', 'Paid', NULL, '2026-06-12 17:12:48', NULL, 18000.00, 9000.00, 4500.00, 4500.00, 9000.00, 2, 2, 1, 0.00, 0.00, 0.00, 0.00),
(10, 4, '2026-04', 45000.00, 0.00, 15000.00, 75000.00, 'Bank Transfer', 'Paid', NULL, '2026-06-12 17:13:21', NULL, 18000.00, 9000.00, 4500.00, 4500.00, 9000.00, 3, 3, 2, 0.00, 0.00, 0.00, 0.00),
(11, 4, '2026-05', 45000.00, 0.00, 7500.00, 82500.00, 'Bank Transfer', 'Paid', NULL, '2026-06-12 17:21:33', '2026-06-12 17:22:37', 18000.00, 9000.00, 4500.00, 4500.00, 9000.00, 1, 5, 1, 0.00, 0.00, 0.00, 0.00);

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

--
-- Dumping data for table `policies`
--

INSERT INTO `policies` (`id`, `title`, `content`, `status`, `created_by`, `effective_date`, `created_at`, `updated_at`) VALUES
(4, 'Employee Leave Policy', '<p class=\"isSelectedEnd\">Employees are entitled to up to 20 days of paid annual leave per calendar year, accrued monthly. Leave requests should be submitted at least 7 days in advance through the company\'s leave management system and are subject to manager approval based on business needs.</p><p class=\"isSelectedEnd\">Employees may also take up to 10 days of paid sick leave annually. For absences of more than two consecutive working days, a medical certificate may be required.</p><p>Additional leave options include:</p><li>Maternity Leave: 16 weeks of paid leave.</li><li>Paternity Leave: 2 weeks of paid leave.</li><li>Bereavement Leave: Up to 3 paid working days for the loss of an immediate family member.</li><li>Unpaid Leave: May be granted in exceptional circumstances with prior management approval.<br>Unused annual leave may be carried forward for up to three months into the following calendar year, subject to a maximum carryover of five days. The company reserves the right to amend this policy at any time to meet operational or legal requirements.</li>', 'Active', 1, '2026-07-13', '2026-07-13 18:54:16', NULL),
(5, 'Employee Working Hours and Attendance Policy', '<p class=\"isSelectedEnd\"><strong>Working Hours</strong></p><ul data-spread=\"false\"><li>Standard office hours are <strong>9:00 AM to 6:00 PM</strong>, Monday through Friday.</li><li>Employees are entitled to a <strong>1-hour unpaid lunch break</strong> between 12:00 PM and 2:00 PM.</li><li>Total working time is <strong>40 hours per week</strong>.</li></ul><p class=\"isSelectedEnd\"><strong>Attendance</strong></p><ul data-spread=\"false\"><li>Employees are expected to arrive on time and be ready to begin work at the scheduled start time.</li><li>A grace period of <strong>10 minutes</strong> is allowed for occasional delays.</li><li>Repeated late arrivals or early departures may be reviewed by the employee\'s manager.</li></ul><p class=\"isSelectedEnd\"><strong>Flexible Hours</strong></p><ul data-spread=\"false\"><li>Where approved, employees may start work between <strong>8:00 AM and 10:00 AM</strong>, provided they complete their required daily hours and remain available during core business hours of <strong>10:00 AM to 4:00 PM</strong>.</li></ul><p class=\"isSelectedEnd\"><strong>Overtime</strong></p><ul data-spread=\"false\"><li>Overtime must be approved in advance by the employee\'s manager.</li><li>Eligible employees will receive overtime compensation or time off in lieu in accordance with company policy.</li></ul><p class=\"isSelectedEnd\"><strong>Remote Work</strong></p><ul data-spread=\"false\"><li>Employees approved for remote work must be available during core business hours and attend scheduled meetings.</li><li>Attendance should be recorded through the company\'s designated time-tracking system.</li></ul><p>The company may update this policy from time to time to meet operational or legal requirements.</p>', 'Active', 1, '2026-07-14', '2026-07-13 18:54:59', '2026-07-13 19:54:32');

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
(1, 4, 'Increment', 0.00, 90000.00, 90000.00, '2026-06-11', '2026-06-10 22:59:38'),
(2, 3, 'Increment', 0.00, 60000.00, 60000.00, '2026-06-11', '2026-06-10 23:00:18'),
(3, 3, 'Increment', 60000.00, 70000.00, 10000.00, '2026-06-12', '2026-06-11 23:21:07'),
(4, 15, 'Decrement', 8789789.00, 40000.00, 8749789.00, '2026-07-13', '2026-07-13 17:30:43'),
(5, 19, 'Decrement', 78979.00, 50000.00, 28979.00, '2026-07-13', '2026-07-13 17:31:08'),
(6, 17, 'Decrement', 789456.00, 100000.00, 689456.00, '2026-07-13', '2026-07-13 17:31:33'),
(7, 13, 'Decrement', 987987.00, 300000.00, 687987.00, '2026-07-13', '2026-07-13 17:32:12'),
(8, 14, 'Decrement', 879879.00, 300000.00, 579879.00, '2026-07-13', '2026-07-13 17:32:36'),
(9, 18, 'Decrement', 879987.00, 60000.00, 819987.00, '2026-07-13', '2026-07-13 17:32:57'),
(10, 16, 'Decrement', 87897.00, 40000.00, 47897.00, '2026-07-13', '2026-07-13 17:33:22');

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
(1, 'hr_permissions_revision', '275'),
(2, 'hr_capability_migration', '8'),
(3, 'org_ceo_mode', 'manual'),
(4, 'org_ceo_employee_id', ''),
(5, 'org_ceo_manual_name', 'Shayan Siddiqui'),
(6, 'org_ceo_manual_title', 'CEO'),
(7, 'org_cto_employee_id', '13'),
(23, 'payroll_start_day', '21'),
(24, 'payroll_end_day', '20'),
(64, 'org_management_dept_id', '6');

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
(1, 'A', '19:00:00', '04:00:00', 15, 4.00, '', '2026-06-10 22:55:38', NULL, NULL),
(2, 'B', '20:00:00', '05:00:00', 15, 4.00, '', '2026-06-10 22:56:11', NULL, NULL),
(3, 'C', '21:00:00', '06:00:00', 15, 4.00, '', '2026-06-10 22:56:46', NULL, NULL),
(4, 'test', '22:01:00', '07:01:00', 16, 5.00, '', '2026-07-13 19:58:53', '2026-07-20 20:08:21', '2026-07-20 20:08:21');

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

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `employee_id`, `subject`, `category`, `target_dept_id`, `assigned_to`, `description`, `status`, `created_at`, `resolved_by`, `resolved_at`, `closed_by`, `closed_at`, `resolution_duration`, `reopen_count`, `employee_unread`, `it_unread`) VALUES
(1, 3, 'Test', 'Hardware', NULL, 19, 'Test', 'Resolved', '2026-06-12 15:32:20', 19, '2026-06-12 15:50:25', NULL, NULL, '18 mins', 1, 0, 0),
(2, 4, 'test Ticket Subject', 'test Specify Category', NULL, 18, 'test Description', 'Closed', '2026-07-13 15:40:07', 17, '2026-07-13 15:44:11', 18, '2026-07-13 15:49:53', '9 mins', 1, 0, 0);

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

--
-- Dumping data for table `ticket_messages`
--

INSERT INTO `ticket_messages` (`id`, `ticket_id`, `sender_id`, `message`, `is_internal`, `is_system`, `created_at`) VALUES
(1, 1, 3, 'Test', 0, 0, '2026-06-12 15:32:20'),
(2, 1, 17, 'testtttt', 0, 0, '2026-06-12 15:32:54'),
(3, 1, 3, 'testtt', 0, 0, '2026-06-12 15:33:04'),
(4, 1, 17, 'test', 0, 0, '2026-06-12 15:33:24'),
(5, 1, 3, 'sadas', 0, 0, '2026-06-12 15:33:35'),
(6, 1, 17, 'Ticket handed over to Faiz  Raza.', 1, 1, '2026-06-12 15:42:06'),
(7, 1, 18, 'yesss', 0, 0, '2026-06-12 15:43:09'),
(8, 1, 3, 'testttt', 0, 0, '2026-06-12 15:43:17'),
(9, 1, 18, 'xyz', 1, 0, '2026-06-12 15:43:29'),
(10, 1, 3, 'Ticket Re-opened. Previously resolved by Faiz  Raza in 11 mins.', 0, 1, '2026-06-12 15:44:10'),
(11, 1, 3, 'issue not solved', 0, 0, '2026-06-12 15:44:27'),
(12, 1, 18, 'Ticket handed over to Abdul Aziz Samad.', 1, 1, '2026-06-12 15:44:37'),
(13, 1, 19, 'sdsadsa', 0, 0, '2026-06-12 15:48:00'),
(14, 2, 4, 'test Description', 0, 0, '2026-07-13 15:40:07'),
(15, 2, 17, 'test msg', 0, 0, '2026-07-13 15:43:58'),
(16, 2, 4, 'Ticket Re-opened. Previously resolved by Adnan  Asad in 4 mins.', 0, 1, '2026-07-13 15:44:29'),
(17, 2, 17, 'Ticket handed over to Faiz  Raza.', 1, 1, '2026-07-13 15:48:43'),
(18, 2, 18, 'test', 0, 0, '2026-07-13 15:49:04'),
(19, 2, 18, 'test krlo', 1, 0, '2026-07-13 15:49:17');

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=622;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=501;

--
-- AUTO_INCREMENT for table `banking_info`
--
ALTER TABLE `banking_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `candidate_answers`
--
ALTER TABLE `candidate_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `candidate_history`
--
ALTER TABLE `candidate_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `education_experience`
--
ALTER TABLE `education_experience`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `hr_page_permissions`
--
ALTER TABLE `hr_page_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7731;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `job_questions`
--
ALTER TABLE `job_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `kpi_goals`
--
ALTER TABLE `kpi_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `kpi_reviews`
--
ALTER TABLE `kpi_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `notification_recipients`
--
ALTER TABLE `notification_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=544;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `salary_history`
--
ALTER TABLE `salary_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
