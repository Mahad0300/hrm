-- Run once on existing database (phpMyAdmin → hrm → SQL)
ALTER TABLE `support_tickets`
  ADD COLUMN `admin_unread` int(11) NOT NULL DEFAULT 0 AFTER `it_unread`;
