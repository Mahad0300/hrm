-- =============================================================================
-- HRM KPI Module - Complete SQL Migration + Data Seed
-- Generated: 2026-07-24 01:32:52
-- Run this ONCE on any fresh copy of the hrm database.
-- Safe to re-run: uses IF NOT EXISTS / ON DUPLICATE KEY / etc.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- STEP 1: Add new columns (safe – skips if already exists via stored procedure)
-- -----------------------------------------------------------------------------

-- 1a. employees.job_description
ALTER TABLE `employees`
    ADD COLUMN IF NOT EXISTS `job_description` TEXT DEFAULT NULL AFTER `job_title`;

-- 1b. kpi_goals.weight
ALTER TABLE `kpi_goals`
    ADD COLUMN IF NOT EXISTS `weight` DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER `goal_name`;

-- 1c. kpi_reviews.period_month (YYYY-MM)
ALTER TABLE `kpi_reviews`
    ADD COLUMN IF NOT EXISTS `period_month` VARCHAR(7) DEFAULT NULL AFTER `period`;

-- -----------------------------------------------------------------------------
-- STEP 2: Update employee job descriptions from KPI - FEB.xlsx
-- -----------------------------------------------------------------------------

-- EMP 3: Syed Bukhari
UPDATE `employees` SET `job_description` = 'Note: Focuses on creating user-friendly and responsive interfaces.\n• Develop and maintain responsive web designs and interfaces.\n• Collaborate with backend teams to integrate APIs and dynamic content.\n• Optimize applications for performance and accessibility.\n• Ensure consistent UI/UX standards across all platforms.' WHERE `id` = 3;

-- EMP 4: Muhammad Abiden
UPDATE `employees` SET `job_description` = 'Note: Focuses on building and maintaining robust server-side logic and APIs.\n• Develop, test, and optimize backend code for performance and scalability.\n• Collaborate with frontend developers to integrate user-facing elements.\n• Ensure data security, integrity, and efficient database management.\n• Troubleshoot and debug backend issues to maintain uptime and stability.' WHERE `id` = 4;

-- EMP 5: Shayan Shaikh
UPDATE `employees` SET `job_description` = 'Note: Specializes in designing, developing, and maintaining WordPress websites.\n• Customize and maintain WordPress themes and plugins.\n• Ensure website performance, responsiveness, and security.\n• Optimize websites for SEO and user experience.\n• Collaborate with content and design teams for site updates/ working in shopify too' WHERE `id` = 5;

-- EMP 6: Faisal Khan
UPDATE `employees` SET `job_description` = 'Note: Creates visual content that supports marketing and brand identity.\n• Design promotional materials, social media graphics, and web visuals.\n• Collaborate with content and marketing teams for creative campaigns.\n• Maintain consistency with brand guidelines and aesthetics.\n• Revise designs based on feedback and ensure timely delivery.' WHERE `id` = 6;

-- EMP 7: Owais Ahmed
UPDATE `employees` SET `job_description` = 'Note: Oversees advanced SEO strategies to drive website ranking and traffic.\n• Manage complex SEO campaigns and competitor analysis.\n• Develop link-building strategies and oversee on/off-page optimization.\n• Track analytics to measure SEO performance and report insights.\n• Mentor junior SEO team members and guide best practices.' WHERE `id` = 7;

-- EMP 8: Affan Ahmed
UPDATE `employees` SET `job_description` = 'Note: Enhances website visibility and search performance through SEO strategies.\n• Conduct keyword research and on-page optimization.\n• Develop and manage off-page SEO campaigns and backlinks.\n• Monitor and report website performance metrics (Google Analytics, Search Console).\n• Collaborate with content writers to align SEO with content strategy.' WHERE `id` = 8;

-- EMP 9: Anoushay Amir
UPDATE `employees` SET `job_description` = 'Note: Focuses on delivering excellent customer service and issue resolution.\n• Respond to customer queries promptly via chat, email, or call.\n• Maintain accurate customer records and feedback logs.\n• Escalate unresolved issues to higher support levels.\n• Ensure customer satisfaction through timely follow-up and support.' WHERE `id` = 9;

-- EMP 10: Anousha Noman
UPDATE `employees` SET `job_description` = 'Note: Focuses on delivering excellent customer service and issue resolution.\n• Respond to customer queries promptly via chat, email, or call.\n• Maintain accurate customer records and feedback logs.\n• Escalate unresolved issues to higher support levels.\n• Ensure customer satisfaction through timely follow-up and support.' WHERE `id` = 10;

-- EMP 11: Bisma Wajeeha
UPDATE `employees` SET `job_description` = 'Note: Manages HR functions and supports employee engagement and compliance.\n• Assist in recruitment, onboarding, and employee documentation.\n• Maintain HR records and update employee data accurately.\n• Support payroll processing and attendance management.\n• Coordinate employee engagement and training programs.' WHERE `id` = 11;

-- EMP 16: zain Khan
UPDATE `employees` SET `job_description` = 'Note: Focuses on building and maintaining robust server-side logic and APIs.\n• Develop, test, and optimize backend code for performance and scalability.\n• Collaborate with frontend developers to integrate user-facing elements.\n• Ensure data security, integrity, and efficient database management.\n• Troubleshoot and debug backend issues to maintain uptime and stability.' WHERE `id` = 16;

-- EMP 17: Adnan Asad
UPDATE `employees` SET `job_description` = 'Note: Oversees IT operations and ensures infrastructure efficiency.\n• Lead the IT team in managing network, servers, and system security.\n• Develop and implement IT policies, strategies, and procedures.\n• Supervise maintenance, upgrades, and troubleshooting of IT systems.\n• Monitor IT performance metrics and align with organizational goals.' WHERE `id` = 17;

-- EMP 18: Faiz Raza
UPDATE `employees` SET `job_description` = 'Note: Ensures smooth operation of IT systems and user support.\n• Provide technical support to employees and resolve IT issues promptly.\n• Maintain and monitor systems, networks, and hardware.\n• Assist in software installation, configuration, and troubleshooting.\n• Document and track support activities to ensure resolution quality.' WHERE `id` = 18;

-- EMP 19: Abdul Samad
UPDATE `employees` SET `job_description` = 'Note: Responsible for ensuring system stability, performance, and security.\n• Manage and maintain servers, databases, and backup systems.\n• Implement security protocols and regular system monitoring.\n• Troubleshoot and resolve hardware/software issues efficiently.\n• Support IT operations and assist in infrastructure upgrades' WHERE `id` = 19;

-- -----------------------------------------------------------------------------
-- STEP 3: February 2026 KPI Reviews + Goals
-- (Attendance 40pts | Dependability 30pts | Manager Feedback 30pts)
-- Skips if a Feb-2026 review already exists for that employee.
-- -----------------------------------------------------------------------------

-- EMP 4: Muhammad Abiden | Overall: 98.6% | Excelling
INSERT INTO `kpi_reviews`
    (`employee_id`, `reviewer_id`, `period`, `period_month`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`)
SELECT 4, 1, 'Monthly', '2026-02', '2026-02-28', '4.93', 'Excelling', 'zain is fine with everything he just needs to work on his behavior, in terms on his work he\'s 10/10, in terms of managing the team he\'s 10/10. he needs to work on his appearance because he\'s presenting a team otherwise he\'s good to go', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `kpi_reviews` WHERE `employee_id` = 4 AND `period_month` = '2026-02'
);

-- Goals for EMP 4 (inserted only if review was just created above)
SET @last_review_id_4 = (SELECT id FROM kpi_reviews WHERE employee_id = 4 AND period_month = '2026-02' LIMIT 1);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_4, 'Attendance', 40, 40, 39, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_4 AND goal_name = 'Attendance'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_4, 'Dependability', 30, 30, 30, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_4 AND goal_name = 'Dependability'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_4, 'Manager\'s Feedback', 30, 30, 30, 'zain is fine with everything he just needs to work on his behavior, in terms on his work he\'s 10/10, in terms of managing the team he\'s 10/10. he needs to work on his appearance because he\'s presenting a team otherwise he\'s good to go'
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_4 AND goal_name = 'Manager\'s Feedback'
);

-- EMP 3: Syed Bukhari | Overall: 100% | Excelling
INSERT INTO `kpi_reviews`
    (`employee_id`, `reviewer_id`, `period`, `period_month`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`)
SELECT 3, 1, 'Monthly', '2026-02', '2026-02-28', '5.00', 'Excelling', 'He is working on Chatrox, and there have been no complaints regarding his work. His performance is good, and he maintains good behavior with all team members.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `kpi_reviews` WHERE `employee_id` = 3 AND `period_month` = '2026-02'
);

-- Goals for EMP 3 (inserted only if review was just created above)
SET @last_review_id_3 = (SELECT id FROM kpi_reviews WHERE employee_id = 3 AND period_month = '2026-02' LIMIT 1);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_3, 'Attendance', 40, 40, 40, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_3 AND goal_name = 'Attendance'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_3, 'Dependability', 30, 30, 30, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_3 AND goal_name = 'Dependability'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_3, 'Manager\'s Feedback', 30, 30, 30, 'He is working on Chatrox, and there have been no complaints regarding his work. His performance is good, and he maintains good behavior with all team members.'
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_3 AND goal_name = 'Manager\'s Feedback'
);

-- EMP 5: Shayan Shaikh | Overall: 94.4% | Good
INSERT INTO `kpi_reviews`
    (`employee_id`, `reviewer_id`, `period`, `period_month`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`)
SELECT 5, 1, 'Monthly', '2026-02', '2026-02-28', '4.72', 'Good', 'He is working on Shopify for Vaporedge. A minor issue was reported yesterday, which was resolved by Shayan as it was client-based work and could not be risked due to the error. In terms of behavior, he maintains good conduct with everyone.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `kpi_reviews` WHERE `employee_id` = 5 AND `period_month` = '2026-02'
);

-- Goals for EMP 5 (inserted only if review was just created above)
SET @last_review_id_5 = (SELECT id FROM kpi_reviews WHERE employee_id = 5 AND period_month = '2026-02' LIMIT 1);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_5, 'Attendance', 40, 40, 37, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_5 AND goal_name = 'Attendance'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_5, 'Dependability', 30, 30, 30, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_5 AND goal_name = 'Dependability'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_5, 'Manager\'s Feedback', 30, 30, 27, 'He is working on Shopify for Vaporedge. A minor issue was reported yesterday, which was resolved by Shayan as it was client-based work and could not be risked due to the error. In terms of behavior, he maintains good conduct with everyone.'
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_5 AND goal_name = 'Manager\'s Feedback'
);

-- EMP 6: Faisal Khan | Overall: 95% | Excelling
INSERT INTO `kpi_reviews`
    (`employee_id`, `reviewer_id`, `period`, `period_month`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`)
SELECT 6, 1, 'Monthly', '2026-02', '2026-02-28', '4.75', 'Excelling', 'He is currently working on posting tasks. His only feedback is that he does not prefer his work being criticized and tends to be somewhat dominant in his approach.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `kpi_reviews` WHERE `employee_id` = 6 AND `period_month` = '2026-02'
);

-- Goals for EMP 6 (inserted only if review was just created above)
SET @last_review_id_6 = (SELECT id FROM kpi_reviews WHERE employee_id = 6 AND period_month = '2026-02' LIMIT 1);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_6, 'Attendance', 40, 40, 40, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_6 AND goal_name = 'Attendance'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_6, 'Dependability', 30, 30, 30, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_6 AND goal_name = 'Dependability'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_6, 'Manager\'s Feedback', 30, 30, 25, 'He is currently working on posting tasks. His only feedback is that he does not prefer his work being criticized and tends to be somewhat dominant in his approach.'
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_6 AND goal_name = 'Manager\'s Feedback'
);

-- EMP 17: Adnan Asad | Overall: 100% | Excelling
INSERT INTO `kpi_reviews`
    (`employee_id`, `reviewer_id`, `period`, `period_month`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`)
SELECT 17, 1, 'Monthly', '2026-02', '2026-02-28', '5.00', 'Excelling', 'He is good at his work but needs to be more receptive by listening carefully first before proceeding with tasks.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `kpi_reviews` WHERE `employee_id` = 17 AND `period_month` = '2026-02'
);

-- Goals for EMP 17 (inserted only if review was just created above)
SET @last_review_id_17 = (SELECT id FROM kpi_reviews WHERE employee_id = 17 AND period_month = '2026-02' LIMIT 1);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_17, 'Attendance', 40, 40, 40, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_17 AND goal_name = 'Attendance'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_17, 'Dependability', 30, 30, 30, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_17 AND goal_name = 'Dependability'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_17, 'Manager\'s Feedback', 30, 30, 30, 'He is good at his work but needs to be more receptive by listening carefully first before proceeding with tasks.'
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_17 AND goal_name = 'Manager\'s Feedback'
);

-- EMP 19: Abdul Samad | Overall: 0% | Poor
INSERT INTO `kpi_reviews`
    (`employee_id`, `reviewer_id`, `period`, `period_month`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`)
SELECT 19, 1, 'Monthly', '2026-02', '2026-02-28', '0.00', 'Poor', 'new joining', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `kpi_reviews` WHERE `employee_id` = 19 AND `period_month` = '2026-02'
);

-- Goals for EMP 19 (inserted only if review was just created above)
SET @last_review_id_19 = (SELECT id FROM kpi_reviews WHERE employee_id = 19 AND period_month = '2026-02' LIMIT 1);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_19, 'Attendance', 40, 40, 0, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_19 AND goal_name = 'Attendance'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_19, 'Dependability', 30, 30, 0, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_19 AND goal_name = 'Dependability'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_19, 'Manager\'s Feedback', 30, 30, 0, 'new joining'
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_19 AND goal_name = 'Manager\'s Feedback'
);

-- EMP 18: Faiz Raza | Overall: 100% | Excelling
INSERT INTO `kpi_reviews`
    (`employee_id`, `reviewer_id`, `period`, `period_month`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`)
SELECT 18, 1, 'Monthly', '2026-02', '2026-02-28', '5.00', 'Excelling', 'There are no complaints regarding his work. The only issue is that when he is asked to come on alternate days, he shows some minor resistance. Otherwise, there are no concerns.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `kpi_reviews` WHERE `employee_id` = 18 AND `period_month` = '2026-02'
);

-- Goals for EMP 18 (inserted only if review was just created above)
SET @last_review_id_18 = (SELECT id FROM kpi_reviews WHERE employee_id = 18 AND period_month = '2026-02' LIMIT 1);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_18, 'Attendance', 40, 40, 40, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_18 AND goal_name = 'Attendance'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_18, 'Dependability', 30, 30, 30, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_18 AND goal_name = 'Dependability'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_18, 'Manager\'s Feedback', 30, 30, 30, 'There are no complaints regarding his work. The only issue is that when he is asked to come on alternate days, he shows some minor resistance. Otherwise, there are no concerns.'
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_18 AND goal_name = 'Manager\'s Feedback'
);

-- EMP 7: Owais Ahmed | Overall: 94.6% | Good
INSERT INTO `kpi_reviews`
    (`employee_id`, `reviewer_id`, `period`, `period_month`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`)
SELECT 7, 1, 'Monthly', '2026-02', '2026-02-28', '4.73', 'Good', 'for the month of february no issues ive found related to his work its going so far so far so goofd issues were found in the attendance needs to be fixed, and when it comes to the team management  he\'s doing good job', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `kpi_reviews` WHERE `employee_id` = 7 AND `period_month` = '2026-02'
);

-- Goals for EMP 7 (inserted only if review was just created above)
SET @last_review_id_7 = (SELECT id FROM kpi_reviews WHERE employee_id = 7 AND period_month = '2026-02' LIMIT 1);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_7, 'Attendance', 40, 40, 35, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_7 AND goal_name = 'Attendance'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_7, 'Dependability', 30, 30, 30, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_7 AND goal_name = 'Dependability'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_7, 'Manager\'s Feedback', 30, 30, 30, 'for the month of february no issues ive found related to his work its going so far so far so goofd issues were found in the attendance needs to be fixed, and when it comes to the team management  he\'s doing good job'
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_7 AND goal_name = 'Manager\'s Feedback'
);

-- EMP 8: Affan Ahmed | Overall: 95% | Excelling
INSERT INTO `kpi_reviews`
    (`employee_id`, `reviewer_id`, `period`, `period_month`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`)
SELECT 8, 1, 'Monthly', '2026-02', '2026-02-28', '4.75', 'Excelling', 'Daily reporting is being done consistently, but sometimes updating the working sheet is missed. There has been noticeable improvement in February.\nThere are some behavioral concerns, as there is a need to develop more professional conduct. Their meeting participation, team collaboration, and communication skills are relatively weak and need improvement.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `kpi_reviews` WHERE `employee_id` = 8 AND `period_month` = '2026-02'
);

-- Goals for EMP 8 (inserted only if review was just created above)
SET @last_review_id_8 = (SELECT id FROM kpi_reviews WHERE employee_id = 8 AND period_month = '2026-02' LIMIT 1);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_8, 'Attendance', 40, 40, 40, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_8 AND goal_name = 'Attendance'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_8, 'Dependability', 30, 30, 30, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_8 AND goal_name = 'Dependability'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_8, 'Manager\'s Feedback', 30, 30, 25, 'Daily reporting is being done consistently, but sometimes updating the working sheet is missed. There has been noticeable improvement in February.\nThere are some behavioral concerns, as there is a need to develop more professional conduct. Their meeting participation, team collaboration, and communication skills are relatively weak and need improvement.'
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_8 AND goal_name = 'Manager\'s Feedback'
);

-- EMP 9: Anoushay Amir | Overall: 100% | Excelling
INSERT INTO `kpi_reviews`
    (`employee_id`, `reviewer_id`, `period`, `period_month`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`)
SELECT 9, 1, 'Monthly', '2026-02', '2026-02-28', '5.00', 'Excelling', 'needs to improve her attendance rest of when it comes to her work is 100%', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `kpi_reviews` WHERE `employee_id` = 9 AND `period_month` = '2026-02'
);

-- Goals for EMP 9 (inserted only if review was just created above)
SET @last_review_id_9 = (SELECT id FROM kpi_reviews WHERE employee_id = 9 AND period_month = '2026-02' LIMIT 1);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_9, 'Attendance', 40, 40, 40, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_9 AND goal_name = 'Attendance'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_9, 'Dependability', 30, 30, 30, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_9 AND goal_name = 'Dependability'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_9, 'Manager\'s Feedback', 30, 30, 30, 'needs to improve her attendance rest of when it comes to her work is 100%'
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_9 AND goal_name = 'Manager\'s Feedback'
);

-- EMP 10: Anousha Noman | Overall: 0% | Poor
INSERT INTO `kpi_reviews`
    (`employee_id`, `reviewer_id`, `period`, `period_month`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`)
SELECT 10, 1, 'Monthly', '2026-02', '2026-02-28', '0.00', 'Poor', 'new joining', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `kpi_reviews` WHERE `employee_id` = 10 AND `period_month` = '2026-02'
);

-- Goals for EMP 10 (inserted only if review was just created above)
SET @last_review_id_10 = (SELECT id FROM kpi_reviews WHERE employee_id = 10 AND period_month = '2026-02' LIMIT 1);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_10, 'Attendance', 40, 40, 0, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_10 AND goal_name = 'Attendance'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_10, 'Dependability', 30, 30, 0, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_10 AND goal_name = 'Dependability'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_10, 'Manager\'s Feedback', 30, 30, 0, 'new joining'
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_10 AND goal_name = 'Manager\'s Feedback'
);

-- EMP 11: Bisma Wajeeha | Overall: 86.6% | Good
INSERT INTO `kpi_reviews`
    (`employee_id`, `reviewer_id`, `period`, `period_month`, `review_date`, `overall_rating`, `status`, `feedback`, `created_at`)
SELECT 11, 1, 'Monthly', '2026-02', '2026-02-28', '4.33', 'Good', 'the weakest area is the hiring rest everything is fine.', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `kpi_reviews` WHERE `employee_id` = 11 AND `period_month` = '2026-02'
);

-- Goals for EMP 11 (inserted only if review was just created above)
SET @last_review_id_11 = (SELECT id FROM kpi_reviews WHERE employee_id = 11 AND period_month = '2026-02' LIMIT 1);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_11, 'Attendance', 40, 40, 37, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_11 AND goal_name = 'Attendance'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_11, 'Dependability', 30, 30, 30, ''
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_11 AND goal_name = 'Dependability'
);
INSERT INTO `kpi_goals` (`review_id`, `goal_name`, `weight`, `target_score`, `achieved_score`, `reviewer_comment`)
SELECT @last_review_id_11, 'Manager\'s Feedback', 30, 30, 20, 'the weakest area is the hiring rest everything is fine.'
WHERE NOT EXISTS (
    SELECT 1 FROM kpi_goals WHERE review_id = @last_review_id_11 AND goal_name = 'Manager\'s Feedback'
);

-- -----------------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 1;
-- Done. All KPI v3 columns, job descriptions, and February 2026 reviews added.
-- =============================================================================