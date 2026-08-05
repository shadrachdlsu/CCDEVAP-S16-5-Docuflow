-- ------------------------------------------------------
-- Database: docuflow_db
-- ------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS `docuflow_db`;
CREATE DATABASE `docuflow_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `docuflow_db`;

--
-- Table structure for table `roles`
--
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `role_id` INT NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Secretary'),
(3, 'Member');

--
-- Table structure for table `offices`
--
DROP TABLE IF EXISTS `offices`;
CREATE TABLE `offices` (
  `office_id` INT NOT NULL AUTO_INCREMENT,
  `office_name` VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`office_id`),
  UNIQUE KEY `office_name` (`office_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `offices` (`office_id`, `office_name`) VALUES
(1, 'Registrar Office'),
(2, 'Finance Office'),
(3, 'Dean Office'),
(4, 'IT Office'),
(5, 'Human Resources Office'),
(6, 'Admissions Office'),
(7, 'Research & Development Office');

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `role_id` INT NOT NULL,
  `office_id` INT DEFAULT NULL,
  `full_name` VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `registration_status` ENUM('Pending','Approved','Rejected') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Approved',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_role` (`role_id`),
  KEY `fk_users_office` (`office_id`),
  CONSTRAINT `fk_users_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `users` (`user_id`, `role_id`, `office_id`, `full_name`, `email`, `password_hash`, `is_active`, `registration_status`, `created_at`) VALUES
(1, 1, NULL, 'System Admin', 'admin@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-01 08:00:00'),
(2, 2, 1, 'Registrar Secretary', 'registrar.secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-01 08:15:00'),
(3, 2, 2, 'Finance Secretary', 'finance.secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-01 08:30:00'),
(4, 2, 3, 'Dean Secretary', 'secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-01 08:45:00'),
(5, 2, 4, 'IT Secretary', 'it.secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-01 09:00:00'),
(6, 3, 1, 'Juan Member', 'juan.member@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-02 10:00:00'),
(7, 3, 2, 'Maria Signatory', 'member@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-02 11:30:00'),
(8, 1, NULL, 'System Administrator', 'admin@office.gov', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-03 09:00:00'),
(9, 2, 5, 'HR Secretary', 'hr.secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-03 10:00:00'),
(10, 3, 3, 'Sample Member', 'member2@office.gov', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-04 14:00:00'),
(11, 3, 1, 'Alex Santos', 'alex.santos@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-10 09:12:00'),
(12, 3, 4, 'Keith Rodriguez', 'keith@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-12 11:45:00'),
(13, 3, 5, 'Elena Gomez', 'elena.gomez@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-15 14:20:00'),
(14, 3, 2, 'David Chen', 'david.chen@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-18 16:05:00'),
(15, 3, 3, 'Patricia Cruz', 'patricia.cruz@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-20 08:50:00'),
(16, 3, 6, 'Robert Taylor', 'robert.taylor@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-25 10:30:00'),
(17, 3, 7, 'Sophia Martinez', 'sophia.martinez@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-06-01 11:15:00'),
(18, 3, 4, 'Liam Wilson', 'liam.wilson@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-06-05 13:40:00'),
(19, 2, 6, 'Admissions Secretary', 'admissions.secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-06-06 09:00:00'),
(20, 2, 7, 'R&D Secretary', 'rnd.secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-06-06 10:00:00'),
(21, 3, 1, 'Shad Paje', 'shad@paje.me', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 0, 'Pending', '2026-07-20 18:17:16'),
(22, 3, 2, 'Carlos Reyes', 'carlos.reyes@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 0, 'Pending', '2026-07-22 09:30:00'),
(23, 3, 5, 'Angela Diaz', 'angela.diaz@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 0, 'Pending', '2026-07-25 14:15:00'),
(24, 3, 3, 'Marcus Vance', 'marcus.vance@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 0, 'Rejected', '2026-07-28 11:00:00'),
(25, 3, 4, 'Jessica Alba', 'jessica.alba@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 0, 'Rejected', '2026-07-29 16:45:00');

--
-- Table structure for table `office_secretaries`
--
DROP TABLE IF EXISTS `office_secretaries`;
CREATE TABLE `office_secretaries` (
  `office_id` INT NOT NULL,
  `secretary_user_id` INT NOT NULL,
  `assigned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`office_id`),
  UNIQUE KEY `secretary_user_id` (`secretary_user_id`),
  CONSTRAINT `fk_office_secretaries_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_office_secretaries_user` FOREIGN KEY (`secretary_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `office_secretaries` (`office_id`, `secretary_user_id`, `assigned_at`) VALUES
(1, 2, '2026-05-01 08:15:00'),
(2, 3, '2026-05-01 08:30:00'),
(3, 4, '2026-05-01 08:45:00'),
(4, 5, '2026-05-01 09:00:00'),
(5, 9, '2026-05-03 10:00:00'),
(6, 19, '2026-06-06 09:00:00'),
(7, 20, '2026-06-06 10:00:00');

--
-- Table structure for table `document_types`
--
DROP TABLE IF EXISTS `document_types`;
CREATE TABLE `document_types` (
  `type_id` INT NOT NULL AUTO_INCREMENT,
  `type_name` VARCHAR(50) COLLATE utf8mb4_general_ci NOT NULL,
  `description` TEXT COLLATE utf8mb4_general_ci,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `type_name` (`type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `document_types` (`type_id`, `type_name`, `description`, `is_active`) VALUES
(1, 'Proposal', 'Project and grant proposals requiring multi-level review', 1),
(2, 'Approval Form', 'Formal document that requires sign-off', 1),
(3, 'Memorandum', 'Internal office policy or communication notice', 1),
(4, 'Report', 'Official departmental status or financial report', 1),
(5, 'Requisition Order', 'Procurement and equipment purchase request', 1),
(6, 'Policy Directive', 'Institutional guideline or policy directive', 1);

--
-- Table structure for table `document_type_offices`
--
DROP TABLE IF EXISTS `document_type_offices`;
CREATE TABLE `document_type_offices` (
  `type_id` INT NOT NULL,
  `office_id` INT NOT NULL,
  PRIMARY KEY (`type_id`, `office_id`),
  KEY `office_id` (`office_id`),
  CONSTRAINT `document_type_offices_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`),
  CONSTRAINT `document_type_offices_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `document_type_offices` (`type_id`, `office_id`) VALUES
(1, 1), (1, 3), (1, 7),
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7),
(3, 1), (3, 2), (3, 3), (3, 4), (3, 5),
(4, 2), (4, 3), (4, 4), (4, 7),
(5, 2), (5, 4), (5, 5),
(6, 1), (6, 3), (6, 5);
--
-- Table structure for table `documents`
--
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `document_id` INT NOT NULL AUTO_INCREMENT,
  `tracking_code` VARCHAR(50) COLLATE utf8mb4_general_ci NOT NULL,
  `title` VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` VARCHAR(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type_id` INT NOT NULL,
  `requires_signature` TINYINT(1) NOT NULL DEFAULT 1,
  `creator_id` INT NOT NULL,
  `current_office_id` INT DEFAULT NULL,
  `status` ENUM('Created','Pending','Received','Released','For Signature','Signed','Rejected','Completed','Recalled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Created',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`document_id`),
  UNIQUE KEY `tracking_code` (`tracking_code`),
  KEY `fk_documents_type` (`type_id`),
  KEY `fk_documents_creator` (`creator_id`),
  KEY `fk_documents_current_office` (`current_office_id`),
  CONSTRAINT `fk_documents_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_documents_current_office` FOREIGN KEY (`current_office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_documents_type` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `documents` (`document_id`, `tracking_code`, `title`, `file_path`, `type_id`, `requires_signature`, `creator_id`, `current_office_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'DOC-2026-001', 'Annual Budget Allocation Request', 'pdfs/dummy-doc-001.pdf', 1, 1, 6, 1, 'Pending', '2026-05-06 09:11:00', '2026-05-07 10:11:00'),
(2, 'DOC-2026-002', 'IT Equipment Procurement Plan', 'pdfs/dummy-doc-002.pdf', 2, 1, 7, 2, 'Completed', '2026-05-11 10:12:00', '2026-05-13 11:12:00'),
(3, 'DOC-2026-003', 'Employee Training Program Proposal', 'pdfs/dummy-doc-003.pdf', 3, 1, 10, 3, 'Signed', '2026-05-16 11:13:00', '2026-05-19 12:13:00'),
(4, 'DOC-2026-004', 'Procurement Approval Stationery', 'pdfs/dummy-doc-004.pdf', 4, 1, 11, 4, 'Rejected', '2026-05-21 12:14:00', '2026-05-21 13:14:00'),
(5, 'DOC-2026-005', 'Quarterly Financial Audit Report', 'pdfs/dummy-doc-005.pdf', 5, 0, 12, 5, 'Received', '2026-05-26 13:15:00', '2026-05-27 14:15:00'),
(6, 'DOC-2026-006', 'Infrastructure Maintenance Plan', 'pdfs/dummy-doc-006.pdf', 6, 1, 13, 6, 'For Signature', '2026-05-04 14:16:00', '2026-05-06 15:16:00'),
(7, 'DOC-2026-007', 'Staff Performance Review Guidelines', 'pdfs/dummy-doc-007.pdf', 1, 1, 14, 7, 'Released', '2026-05-09 15:17:00', '2026-05-12 16:17:00'),
(8, 'DOC-2026-008', 'Software License Renewal Request', 'pdfs/dummy-doc-008.pdf', 2, 1, 15, 1, 'Created', '2026-05-14 16:18:00', '2026-05-14 17:18:00'),
(9, 'DOC-2026-009', 'Campus Facility Upgrade Proposal', 'pdfs/dummy-doc-009.pdf', 3, 1, 16, 2, 'Recalled', '2026-05-19 08:19:00', '2026-05-20 09:19:00'),
(10, 'DOC-2026-010', 'Departmental Operating Expenses', 'pdfs/dummy-doc-010.pdf', 4, 0, 17, 3, 'Pending', '2026-05-24 09:20:00', '2026-05-26 10:20:00'),
(11, 'DOC-2026-011', 'Research Grant Application Form', 'pdfs/dummy-doc-011.pdf', 5, 1, 18, 4, 'Completed', '2026-05-02 10:21:00', '2026-05-05 11:21:00'),
(12, 'DOC-2026-012', 'Security Policy Update Notice', 'pdfs/dummy-doc-012.pdf', 6, 1, 6, 5, 'Signed', '2026-05-07 11:22:00', '2026-05-07 12:22:00'),
(13, 'DOC-2026-013', 'Vendor Service Agreement Renewal', 'pdfs/dummy-doc-013.pdf', 1, 1, 7, 6, 'Rejected', '2026-05-12 12:23:00', '2026-05-13 13:23:00'),
(14, 'DOC-2026-014', 'Health and Safety Inspection Report', 'pdfs/dummy-doc-014.pdf', 2, 1, 10, 7, 'Received', '2026-06-17 13:24:00', '2026-06-19 14:24:00'),
(15, 'DOC-2026-015', 'Strategic Planning Draft 2026', 'pdfs/dummy-doc-015.pdf', 3, 0, 11, 1, 'For Signature', '2026-06-22 14:25:00', '2026-06-25 15:25:00'),
(16, 'DOC-2026-016', 'Curriculum Revision Proposal', 'pdfs/dummy-doc-016.pdf', 4, 1, 12, 2, 'Released', '2026-06-27 15:26:00', '2026-06-27 16:26:00'),
(17, 'DOC-2026-017', 'Student Travel Approval Form', 'pdfs/dummy-doc-017.pdf', 5, 1, 13, 3, 'Created', '2026-06-05 16:27:00', '2026-06-06 17:27:00'),
(18, 'DOC-2026-018', 'Laboratory Supplies Requisition', 'pdfs/dummy-doc-018.pdf', 6, 1, 14, 4, 'Recalled', '2026-06-10 08:28:00', '2026-06-12 09:28:00'),
(19, 'DOC-2026-019', 'Network Infrastructure Audit', 'pdfs/dummy-doc-019.pdf', 1, 1, 15, 5, 'Pending', '2026-06-15 09:29:00', '2026-06-18 10:29:00'),
(20, 'DOC-2026-020', 'Event Hosting Approval Request', 'pdfs/dummy-doc-020.pdf', 2, 0, 16, 6, 'Completed', '2026-06-20 10:30:00', '2026-06-20 11:30:00'),
(21, 'DOC-2026-021', 'Marketing & Outreach Plan', 'pdfs/dummy-doc-021.pdf', 3, 1, 17, 7, 'Signed', '2026-06-25 11:31:00', '2026-06-26 12:31:00'),
(22, 'DOC-2026-022', 'Data Privacy Compliance Review', 'pdfs/dummy-doc-022.pdf', 4, 1, 18, 1, 'Rejected', '2026-06-03 12:32:00', '2026-06-05 13:32:00'),
(23, 'DOC-2026-023', 'Energy Conservation Directive', 'pdfs/dummy-doc-023.pdf', 5, 1, 6, 2, 'Received', '2026-06-08 13:33:00', '2026-06-11 14:33:00'),
(24, 'DOC-2026-024', 'Workplace Ergonomics Survey', 'pdfs/dummy-doc-024.pdf', 6, 1, 7, 3, 'For Signature', '2026-06-13 14:34:00', '2026-06-13 15:34:00'),
(25, 'DOC-2026-025', 'Emergency Response Action Plan', 'pdfs/dummy-doc-025.pdf', 1, 0, 10, 4, 'Released', '2026-06-18 15:35:00', '2026-06-19 16:35:00'),
(26, 'DOC-2026-026', 'Faculty Promotion Evaluation', 'pdfs/dummy-doc-026.pdf', 2, 1, 11, 5, 'Created', '2026-06-23 16:36:00', '2026-06-25 17:36:00'),
(27, 'DOC-2026-027', 'Asset Disposal Request Form', 'pdfs/dummy-doc-027.pdf', 3, 1, 12, 6, 'Recalled', '2026-07-01 08:37:00', '2026-07-04 09:37:00'),
(28, 'DOC-2026-028', 'Cloud Migration Proposal', 'pdfs/dummy-doc-028.pdf', 4, 1, 13, 7, 'Pending', '2026-07-06 09:38:00', '2026-07-06 10:38:00'),
(29, 'DOC-2026-029', 'Disaster Recovery Testing Log', 'pdfs/dummy-doc-029.pdf', 5, 1, 14, 1, 'Completed', '2026-07-11 10:39:00', '2026-07-12 11:39:00'),
(30, 'DOC-2026-030', 'Internal Audit Findings Summary', 'pdfs/dummy-doc-030.pdf', 6, 0, 15, 2, 'Signed', '2026-07-16 11:40:00', '2026-07-18 12:40:00'),
(31, 'DOC-2026-031', 'Travel Expense Reimbursement', 'pdfs/dummy-doc-031.pdf', 1, 1, 16, 3, 'Rejected', '2026-07-21 12:41:00', '2026-07-24 13:41:00'),
(32, 'DOC-2026-032', 'Overtime Work Approval Request', 'pdfs/dummy-doc-032.pdf', 2, 1, 17, 4, 'Received', '2026-07-26 13:42:00', '2026-07-26 14:42:00'),
(33, 'DOC-2026-033', 'Vehicle Fleet Maintenance Log', 'pdfs/dummy-doc-033.pdf', 3, 1, 18, 5, 'For Signature', '2026-07-04 14:43:00', '2026-07-05 15:43:00'),
(34, 'DOC-2026-034', 'Scholarship Application Summary', 'pdfs/dummy-doc-034.pdf', 4, 1, 6, 6, 'Released', '2026-07-09 15:44:00', '2026-07-11 16:44:00'),
(35, 'DOC-2026-035', 'Library Resource Acquisition', 'pdfs/dummy-doc-035.pdf', 5, 0, 7, 7, 'Created', '2026-07-14 16:45:00', '2026-07-17 17:45:00'),
(36, 'DOC-2026-036', 'Waste Management Protocol', 'pdfs/dummy-doc-036.pdf', 6, 1, 10, 1, 'Recalled', '2026-07-19 08:46:00', '2026-07-19 09:46:00'),
(37, 'DOC-2026-037', 'Cafeteria Operations Review', 'pdfs/dummy-doc-037.pdf', 1, 1, 11, 2, 'Pending', '2026-07-24 09:47:00', '2026-07-25 10:47:00'),
(38, 'DOC-2026-038', 'Telecommuting Policy Framework', 'pdfs/dummy-doc-038.pdf', 2, 1, 12, 3, 'Completed', '2026-07-02 10:48:00', '2026-07-04 11:48:00'),
(39, 'DOC-2026-039', 'Community Relations Report', 'pdfs/dummy-doc-039.pdf', 3, 1, 13, 4, 'Signed', '2026-07-07 11:49:00', '2026-07-10 12:49:00'),
(40, 'DOC-2026-040', 'Risk Management Assessment', 'pdfs/dummy-doc-040.pdf', 4, 0, 14, 5, 'Rejected', '2026-08-12 12:50:00', '2026-08-12 13:50:00'),
(41, 'DOC-2026-041', 'Key Card Access Log Summary', 'pdfs/dummy-doc-041.pdf', 5, 1, 15, 6, 'Received', '2026-08-17 13:51:00', '2026-08-18 14:51:00'),
(42, 'DOC-2026-042', 'Print & Publishing Requisition', 'pdfs/dummy-doc-042.pdf', 6, 1, 16, 7, 'For Signature', '2026-08-22 14:52:00', '2026-08-24 15:52:00'),
(43, 'DOC-2026-043', 'Alumni Association Update', 'pdfs/dummy-doc-043.pdf', 1, 1, 17, 1, 'Released', '2026-08-27 15:53:00', '2026-08-28 16:53:00'),
(44, 'DOC-2026-044', 'Graduate Studies Prospectus', 'pdfs/dummy-doc-044.pdf', 2, 1, 18, 2, 'Created', '2026-08-05 16:54:00', '2026-08-05 17:54:00'),
(45, 'DOC-2026-045', 'Copyright License Clearance', 'pdfs/dummy-doc-045.pdf', 3, 0, 6, 3, 'Recalled', '2026-08-10 08:55:00', '2026-08-11 09:55:00'),
(46, 'DOC-2026-046', 'Internship Placement Agreement', 'pdfs/dummy-doc-046.pdf', 4, 1, 7, 4, 'Pending', '2026-08-15 09:56:00', '2026-08-17 10:56:00'),
(47, 'DOC-2026-047', 'Honorarium Disbursement Form', 'pdfs/dummy-doc-047.pdf', 5, 1, 10, 5, 'Completed', '2026-08-20 10:57:00', '2026-08-23 11:57:00'),
(48, 'DOC-2026-048', 'Building Renovation Permit', 'pdfs/dummy-doc-048.pdf', 6, 1, 11, 6, 'Signed', '2026-08-25 11:10:00', '2026-08-25 12:10:00'),
(49, 'DOC-2026-049', 'Water Quality Test Results', 'pdfs/dummy-doc-049.pdf', 1, 1, 12, 7, 'Rejected', '2026-08-03 12:11:00', '2026-08-04 13:11:00'),
(50, 'DOC-2026-050', 'Annual General Report 2026', 'pdfs/dummy-doc-050.pdf', 2, 0, 13, 1, 'Received', '2026-08-08 13:12:00', '2026-08-10 14:12:00');

--
-- Table structure for table `document_assignments`
--
DROP TABLE IF EXISTS `document_assignments`;
CREATE TABLE `document_assignments` (
  `assignment_id` INT NOT NULL AUTO_INCREMENT,
  `document_id` INT NOT NULL,
  `assigned_to_user_id` INT NOT NULL,
  `assigned_by_user_id` INT DEFAULT NULL,
  `office_id` INT DEFAULT NULL,
  `status` ENUM('Pending','Signed','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
  `remarks` TEXT,
  `signed_file_path` VARCHAR(255) DEFAULT NULL,
  `assigned_at` TIMESTAMP NULL DEFAULT NULL,
  `acted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`assignment_id`),
  KEY `document_id` (`document_id`),
  KEY `assigned_to_user_id` (`assigned_to_user_id`),
  KEY `assigned_by_user_id` (`assigned_by_user_id`),
  KEY `office_id` (`office_id`),
  CONSTRAINT `document_assignments_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`),
  CONSTRAINT `document_assignments_ibfk_2` FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `document_assignments_ibfk_3` FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `document_assignments_ibfk_4` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `document_assignments` (`assignment_id`, `document_id`, `assigned_to_user_id`, `assigned_by_user_id`, `office_id`, `status`, `remarks`, `signed_file_path`, `assigned_at`, `acted_at`) VALUES
(1, 1, 7, 6, 1, 'Pending', 'Action required for approval workflow', NULL, '2026-05-06 09:11:00', NULL),
(2, 2, 10, 7, 2, 'Signed', 'Action required for approval workflow', NULL, '2026-05-11 10:12:00', '2026-05-13 11:12:00'),
(3, 3, 12, 10, 3, 'Signed', 'Action required for approval workflow', NULL, '2026-05-16 11:13:00', '2026-05-19 12:13:00'),
(4, 4, 14, 11, 4, 'Rejected', 'Action required for approval workflow', NULL, '2026-05-21 12:14:00', '2026-05-21 13:14:00'),
(5, 6, 7, 13, 6, 'Pending', 'Action required for approval workflow', NULL, '2026-05-04 14:16:00', NULL),
(6, 7, 10, 14, 7, 'Pending', 'Action required for approval workflow', NULL, '2026-05-09 15:17:00', NULL),
(7, 8, 12, 15, 1, 'Pending', 'Action required for approval workflow', NULL, '2026-05-14 16:18:00', NULL),
(8, 9, 14, 16, 2, 'Pending', 'Action required for approval workflow', NULL, '2026-05-19 08:19:00', NULL),
(9, 11, 7, 18, 4, 'Signed', 'Action required for approval workflow', NULL, '2026-05-02 10:21:00', '2026-05-05 11:21:00'),
(10, 12, 10, 6, 5, 'Signed', 'Action required for approval workflow', NULL, '2026-05-07 11:22:00', '2026-05-07 12:22:00'),
(11, 13, 12, 7, 6, 'Rejected', 'Action required for approval workflow', NULL, '2026-05-12 12:23:00', '2026-05-13 13:23:00'),
(12, 14, 14, 10, 7, 'Pending', 'Action required for approval workflow', NULL, '2026-06-17 13:24:00', NULL),
(13, 16, 7, 12, 2, 'Pending', 'Action required for approval workflow', NULL, '2026-06-27 15:26:00', NULL),
(14, 17, 10, 13, 3, 'Pending', 'Action required for approval workflow', NULL, '2026-06-05 16:27:00', NULL),
(15, 18, 12, 14, 4, 'Pending', 'Action required for approval workflow', NULL, '2026-06-10 08:28:00', NULL),
(16, 19, 14, 15, 5, 'Pending', 'Action required for approval workflow', NULL, '2026-06-15 09:29:00', NULL),
(17, 21, 7, 17, 7, 'Signed', 'Action required for approval workflow', NULL, '2026-06-25 11:31:00', '2026-06-26 12:31:00'),
(18, 22, 10, 18, 1, 'Rejected', 'Action required for approval workflow', NULL, '2026-06-03 12:32:00', '2026-06-05 13:32:00'),
(19, 23, 12, 6, 2, 'Pending', 'Action required for approval workflow', NULL, '2026-06-08 13:33:00', NULL),
(20, 24, 14, 7, 3, 'Pending', 'Action required for approval workflow', NULL, '2026-06-13 14:34:00', NULL),
(21, 26, 7, 11, 5, 'Pending', 'Action required for approval workflow', NULL, '2026-06-23 16:36:00', NULL),
(22, 27, 10, 12, 6, 'Pending', 'Action required for approval workflow', NULL, '2026-07-01 08:37:00', NULL),
(23, 28, 12, 13, 7, 'Pending', 'Action required for approval workflow', NULL, '2026-07-06 09:38:00', NULL),
(24, 29, 14, 14, 1, 'Signed', 'Action required for approval workflow', NULL, '2026-07-11 10:39:00', '2026-07-12 11:39:00'),
(25, 31, 7, 16, 3, 'Rejected', 'Action required for approval workflow', NULL, '2026-07-21 12:41:00', '2026-07-24 13:41:00'),
(26, 32, 10, 17, 4, 'Pending', 'Action required for approval workflow', NULL, '2026-07-26 13:42:00', NULL),
(27, 33, 12, 18, 5, 'Pending', 'Action required for approval workflow', NULL, '2026-07-04 14:43:00', NULL),
(28, 34, 14, 6, 6, 'Pending', 'Action required for approval workflow', NULL, '2026-07-09 15:44:00', NULL),
(29, 36, 7, 10, 1, 'Pending', 'Action required for approval workflow', NULL, '2026-07-19 08:46:00', NULL),
(30, 37, 10, 11, 2, 'Pending', 'Action required for approval workflow', NULL, '2026-07-24 09:47:00', NULL),
(31, 38, 12, 12, 3, 'Signed', 'Action required for approval workflow', NULL, '2026-07-02 10:48:00', '2026-07-04 11:48:00'),
(32, 39, 14, 13, 4, 'Signed', 'Action required for approval workflow', NULL, '2026-07-07 11:49:00', '2026-07-10 12:49:00'),
(33, 41, 7, 15, 6, 'Pending', 'Action required for approval workflow', NULL, '2026-08-17 13:51:00', NULL),
(34, 42, 10, 16, 7, 'Pending', 'Action required for approval workflow', NULL, '2026-08-22 14:52:00', NULL),
(35, 43, 12, 17, 1, 'Pending', 'Action required for approval workflow', NULL, '2026-08-27 15:53:00', NULL),
(36, 44, 14, 18, 2, 'Pending', 'Action required for approval workflow', NULL, '2026-08-05 16:54:00', NULL),
(37, 46, 7, 7, 4, 'Pending', 'Action required for approval workflow', NULL, '2026-08-15 09:56:00', NULL),
(38, 47, 10, 10, 5, 'Signed', 'Action required for approval workflow', NULL, '2026-08-20 10:57:00', '2026-08-23 11:57:00'),
(39, 48, 12, 11, 6, 'Signed', 'Action required for approval workflow', NULL, '2026-08-25 11:10:00', '2026-08-25 12:10:00'),
(40, 49, 14, 12, 7, 'Rejected', 'Action required for approval workflow', NULL, '2026-08-03 12:11:00', '2026-08-04 13:11:00');

--
-- Table structure for table `document_routes`
--
DROP TABLE IF EXISTS `document_routes`;
CREATE TABLE `document_routes` (
  `route_id` INT NOT NULL AUTO_INCREMENT,
  `document_id` INT NOT NULL,
  `step_no` INT NOT NULL DEFAULT '0',
  `office_id` INT DEFAULT NULL,
  `recipient_scope` ENUM('Individual','Office') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Individual',
  `signatory_user_id` INT DEFAULT NULL,
  `status` ENUM('Waiting','Received','For Signature','Signed','Rejected','Released','Skipped','Completed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Waiting',
  `remarks` TEXT COLLATE utf8mb4_general_ci,
  `acted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`route_id`),
  UNIQUE KEY `uq_document_office` (`document_id`,`office_id`),
  KEY `fk_document_routes_office` (`office_id`),
  KEY `fk_document_routes_signatory` (`signatory_user_id`),
  CONSTRAINT `fk_document_routes_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_document_routes_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_document_routes_signatory` FOREIGN KEY (`signatory_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `document_routes` (`route_id`, `document_id`, `step_no`, `office_id`, `recipient_scope`, `signatory_user_id`, `status`, `remarks`, `acted_at`) VALUES
(1, 1, 1, 1, 'Individual', 7, 'Waiting', NULL, NULL),
(2, 2, 1, 2, 'Individual', 10, 'Signed', NULL, '2026-05-13 11:12:00'),
(3, 3, 1, 3, 'Individual', 12, 'Signed', NULL, '2026-05-19 12:13:00'),
(4, 4, 1, 4, 'Individual', 14, 'Rejected', NULL, '2026-05-21 13:14:00'),
(5, 5, 1, 5, 'Individual', 15, 'Waiting', NULL, NULL),
(6, 6, 1, 6, 'Individual', 7, 'Waiting', NULL, NULL),
(7, 7, 1, 7, 'Individual', 10, 'Waiting', NULL, NULL),
(8, 8, 1, 1, 'Individual', 12, 'Waiting', NULL, NULL),
(9, 9, 1, 2, 'Individual', 14, 'Waiting', NULL, NULL),
(10, 10, 1, 3, 'Individual', 15, 'Waiting', NULL, NULL),
(11, 11, 1, 4, 'Individual', 7, 'Signed', NULL, '2026-05-05 11:21:00'),
(12, 12, 1, 5, 'Individual', 10, 'Signed', NULL, '2026-05-07 12:22:00'),
(13, 13, 1, 6, 'Individual', 12, 'Rejected', NULL, '2026-05-13 13:23:00'),
(14, 14, 1, 7, 'Individual', 14, 'Waiting', NULL, NULL),
(15, 15, 1, 1, 'Individual', 15, 'Waiting', NULL, NULL),
(16, 16, 1, 2, 'Individual', 7, 'Waiting', NULL, NULL),
(17, 17, 1, 3, 'Individual', 10, 'Waiting', NULL, NULL),
(18, 18, 1, 4, 'Individual', 12, 'Waiting', NULL, NULL),
(19, 19, 1, 5, 'Individual', 14, 'Waiting', NULL, NULL),
(20, 20, 1, 6, 'Individual', 15, 'Signed', NULL, '2026-06-20 11:30:00'),
(21, 21, 1, 7, 'Individual', 7, 'Signed', NULL, '2026-06-26 12:31:00'),
(22, 22, 1, 1, 'Individual', 10, 'Rejected', NULL, '2026-06-05 13:32:00'),
(23, 23, 1, 2, 'Individual', 12, 'Waiting', NULL, NULL),
(24, 24, 1, 3, 'Individual', 14, 'Waiting', NULL, NULL),
(25, 25, 1, 4, 'Individual', 15, 'Waiting', NULL, NULL),
(26, 26, 1, 5, 'Individual', 7, 'Waiting', NULL, NULL),
(27, 27, 1, 6, 'Individual', 10, 'Waiting', NULL, NULL),
(28, 28, 1, 7, 'Individual', 12, 'Waiting', NULL, NULL),
(29, 29, 1, 1, 'Individual', 14, 'Signed', NULL, '2026-07-12 11:39:00'),
(30, 30, 1, 2, 'Individual', 15, 'Signed', NULL, '2026-07-18 12:40:00'),
(31, 31, 1, 3, 'Individual', 7, 'Rejected', NULL, '2026-07-24 13:41:00'),
(32, 32, 1, 4, 'Individual', 10, 'Waiting', NULL, NULL),
(33, 33, 1, 5, 'Individual', 12, 'Waiting', NULL, NULL),
(34, 34, 1, 6, 'Individual', 14, 'Waiting', NULL, NULL),
(35, 35, 1, 7, 'Individual', 15, 'Waiting', NULL, NULL),
(36, 36, 1, 1, 'Individual', 7, 'Waiting', NULL, NULL),
(37, 37, 1, 2, 'Individual', 10, 'Waiting', NULL, NULL),
(38, 38, 1, 3, 'Individual', 12, 'Signed', NULL, '2026-07-04 11:48:00'),
(39, 39, 1, 4, 'Individual', 14, 'Signed', NULL, '2026-07-10 12:49:00'),
(40, 40, 1, 5, 'Individual', 15, 'Rejected', NULL, '2026-08-12 13:50:00'),
(41, 41, 1, 6, 'Individual', 7, 'Waiting', NULL, NULL),
(42, 42, 1, 7, 'Individual', 10, 'Waiting', NULL, NULL),
(43, 43, 1, 1, 'Individual', 12, 'Waiting', NULL, NULL),
(44, 44, 1, 2, 'Individual', 14, 'Waiting', NULL, NULL),
(45, 45, 1, 3, 'Individual', 15, 'Waiting', NULL, NULL),
(46, 46, 1, 4, 'Individual', 7, 'Waiting', NULL, NULL),
(47, 47, 1, 5, 'Individual', 10, 'Signed', NULL, '2026-08-23 11:57:00'),
(48, 48, 1, 6, 'Individual', 12, 'Signed', NULL, '2026-08-25 12:10:00'),
(49, 49, 1, 7, 'Individual', 14, 'Rejected', NULL, '2026-08-04 13:11:00'),
(50, 50, 1, 1, 'Individual', 15, 'Waiting', NULL, NULL);

--
-- Table structure for table `document_trails`
--
DROP TABLE IF EXISTS `document_trails`;
CREATE TABLE `document_trails` (
  `trail_id` INT NOT NULL AUTO_INCREMENT,
  `document_id` INT NOT NULL,
  `action_by_user_id` INT DEFAULT NULL,
  `from_office_id` INT DEFAULT NULL,
  `to_office_id` INT DEFAULT NULL,
  `action_taken` ENUM('Created','Received','Assigned','Signed','Rejected','Released','Forwarded','Finished','Cancelled','Requested') NOT NULL,
  `remarks` TEXT,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`trail_id`),
  KEY `document_id` (`document_id`),
  KEY `action_by_user_id` (`action_by_user_id`),
  KEY `from_office_id` (`from_office_id`),
  KEY `to_office_id` (`to_office_id`),
  CONSTRAINT `document_trails_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`),
  CONSTRAINT `document_trails_ibfk_2` FOREIGN KEY (`action_by_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `document_trails_ibfk_3` FOREIGN KEY (`from_office_id`) REFERENCES `offices` (`office_id`),
  CONSTRAINT `document_trails_ibfk_4` FOREIGN KEY (`to_office_id`) REFERENCES `offices` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `document_trails` (`trail_id`, `document_id`, `action_by_user_id`, `from_office_id`, `to_office_id`, `action_taken`, `remarks`, `created_at`) VALUES
(1, 1, 6, NULL, 1, 'Created', 'Document created and uploaded', '2026-05-06 09:11:00'),
(2, 2, 7, NULL, 2, 'Created', 'Document created and uploaded', '2026-05-11 10:12:00'),
(3, 2, 10, 2, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-05-13 11:12:00'),
(4, 3, 10, NULL, 3, 'Created', 'Document created and uploaded', '2026-05-16 11:13:00'),
(5, 3, 12, 3, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-05-19 12:13:00'),
(6, 4, 11, NULL, 4, 'Created', 'Document created and uploaded', '2026-05-21 12:14:00'),
(7, 4, 14, 4, NULL, 'Rejected', 'Document returned due to missing attachments', '2026-05-21 13:14:00'),
(8, 5, 12, NULL, 5, 'Created', 'Document created and uploaded', '2026-05-26 13:15:00'),
(9, 6, 13, NULL, 6, 'Created', 'Document created and uploaded', '2026-05-04 14:16:00'),
(10, 7, 14, NULL, 7, 'Created', 'Document created and uploaded', '2026-05-09 15:17:00'),
(11, 8, 15, NULL, 1, 'Created', 'Document created and uploaded', '2026-05-14 16:18:00'),
(12, 9, 16, NULL, 2, 'Created', 'Document created and uploaded', '2026-05-19 08:19:00'),
(13, 10, 17, NULL, 3, 'Created', 'Document created and uploaded', '2026-05-24 09:20:00'),
(14, 11, 18, NULL, 4, 'Created', 'Document created and uploaded', '2026-05-02 10:21:00'),
(15, 11, 7, 4, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-05-05 11:21:00'),
(16, 12, 6, NULL, 5, 'Created', 'Document created and uploaded', '2026-05-07 11:22:00'),
(17, 12, 10, 5, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-05-07 12:22:00'),
(18, 13, 7, NULL, 6, 'Created', 'Document created and uploaded', '2026-05-12 12:23:00'),
(19, 13, 12, 6, NULL, 'Rejected', 'Document returned due to missing attachments', '2026-05-13 13:23:00'),
(20, 14, 10, NULL, 7, 'Created', 'Document created and uploaded', '2026-06-17 13:24:00'),
(21, 15, 11, NULL, 1, 'Created', 'Document created and uploaded', '2026-06-22 14:25:00'),
(22, 16, 12, NULL, 2, 'Created', 'Document created and uploaded', '2026-06-27 15:26:00'),
(23, 17, 13, NULL, 3, 'Created', 'Document created and uploaded', '2026-06-05 16:27:00'),
(24, 18, 14, NULL, 4, 'Created', 'Document created and uploaded', '2026-06-10 08:28:00'),
(25, 19, 15, NULL, 5, 'Created', 'Document created and uploaded', '2026-06-15 09:29:00'),
(26, 20, 16, NULL, 6, 'Created', 'Document created and uploaded', '2026-06-20 10:30:00'),
(27, 20, 15, 6, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-06-20 11:30:00'),
(28, 21, 17, NULL, 7, 'Created', 'Document created and uploaded', '2026-06-25 11:31:00'),
(29, 21, 7, 7, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-06-26 12:31:00'),
(30, 22, 18, NULL, 1, 'Created', 'Document created and uploaded', '2026-06-03 12:32:00'),
(31, 22, 10, 1, NULL, 'Rejected', 'Document returned due to missing attachments', '2026-06-05 13:32:00'),
(32, 23, 6, NULL, 2, 'Created', 'Document created and uploaded', '2026-06-08 13:33:00'),
(33, 24, 7, NULL, 3, 'Created', 'Document created and uploaded', '2026-06-13 14:34:00'),
(34, 25, 10, NULL, 4, 'Created', 'Document created and uploaded', '2026-06-18 15:35:00'),
(35, 26, 11, NULL, 5, 'Created', 'Document created and uploaded', '2026-06-23 16:36:00'),
(36, 27, 12, NULL, 6, 'Created', 'Document created and uploaded', '2026-07-01 08:37:00'),
(37, 28, 13, NULL, 7, 'Created', 'Document created and uploaded', '2026-07-06 09:38:00'),
(38, 29, 14, NULL, 1, 'Created', 'Document created and uploaded', '2026-07-11 10:39:00'),
(39, 29, 14, 1, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-07-12 11:39:00'),
(40, 30, 15, NULL, 2, 'Created', 'Document created and uploaded', '2026-07-16 11:40:00'),
(41, 30, 15, 2, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-07-18 12:40:00'),
(42, 31, 16, NULL, 3, 'Created', 'Document created and uploaded', '2026-07-21 12:41:00'),
(43, 31, 7, 3, NULL, 'Rejected', 'Document returned due to missing attachments', '2026-07-24 13:41:00'),
(44, 32, 17, NULL, 4, 'Created', 'Document created and uploaded', '2026-07-26 13:42:00'),
(45, 33, 18, NULL, 5, 'Created', 'Document created and uploaded', '2026-07-04 14:43:00'),
(46, 34, 6, NULL, 6, 'Created', 'Document created and uploaded', '2026-07-09 15:44:00'),
(47, 35, 7, NULL, 7, 'Created', 'Document created and uploaded', '2026-07-14 16:45:00'),
(48, 36, 10, NULL, 1, 'Created', 'Document created and uploaded', '2026-07-19 08:46:00'),
(49, 37, 11, NULL, 2, 'Created', 'Document created and uploaded', '2026-07-24 09:47:00'),
(50, 38, 12, NULL, 3, 'Created', 'Document created and uploaded', '2026-07-02 10:48:00'),
(51, 38, 12, 3, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-07-04 11:48:00'),
(52, 39, 13, NULL, 4, 'Created', 'Document created and uploaded', '2026-07-07 11:49:00'),
(53, 39, 14, 4, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-07-10 12:49:00'),
(54, 40, 14, NULL, 5, 'Created', 'Document created and uploaded', '2026-08-12 12:50:00'),
(55, 40, 15, 5, NULL, 'Rejected', 'Document returned due to missing attachments', '2026-08-12 13:50:00'),
(56, 41, 15, NULL, 6, 'Created', 'Document created and uploaded', '2026-08-17 13:51:00'),
(57, 42, 16, NULL, 7, 'Created', 'Document created and uploaded', '2026-08-22 14:52:00'),
(58, 43, 17, NULL, 1, 'Created', 'Document created and uploaded', '2026-08-27 15:53:00'),
(59, 44, 18, NULL, 2, 'Created', 'Document created and uploaded', '2026-08-05 16:54:00'),
(60, 45, 6, NULL, 3, 'Created', 'Document created and uploaded', '2026-08-10 08:55:00'),
(61, 46, 7, NULL, 4, 'Created', 'Document created and uploaded', '2026-08-15 09:56:00'),
(62, 47, 10, NULL, 5, 'Created', 'Document created and uploaded', '2026-08-20 10:57:00'),
(63, 47, 10, 5, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-08-23 11:57:00'),
(64, 48, 11, NULL, 6, 'Created', 'Document created and uploaded', '2026-08-25 11:10:00'),
(65, 48, 12, 6, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-08-25 12:10:00'),
(66, 49, 12, NULL, 7, 'Created', 'Document created and uploaded', '2026-08-03 12:11:00'),
(67, 49, 14, 7, NULL, 'Rejected', 'Document returned due to missing attachments', '2026-08-04 13:11:00'),
(68, 50, 13, NULL, 1, 'Created', 'Document created and uploaded', '2026-08-08 13:12:00');

--
-- Table structure for table `document_requests`
--
DROP TABLE IF EXISTS `document_requests`;
CREATE TABLE `document_requests` (
  `request_id` INT NOT NULL AUTO_INCREMENT,
  `requested_by_id` INT NOT NULL,
  `office_id` INT NOT NULL,
  `type_id` INT DEFAULT NULL,
  `title` VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` TEXT COLLATE utf8mb4_general_ci,
  `status` ENUM('Pending','Approved','Rejected','Created') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `created_document_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`),
  KEY `fk_document_requests_requested_by` (`requested_by_id`),
  KEY `fk_document_requests_office` (`office_id`),
  KEY `fk_document_requests_type` (`type_id`),
  KEY `fk_document_requests_document` (`created_document_id`),
  CONSTRAINT `fk_document_requests_document` FOREIGN KEY (`created_document_id`) REFERENCES `documents` (`document_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_document_requests_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_document_requests_requested_by` FOREIGN KEY (`requested_by_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_document_requests_type` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `document_requests` (`request_id`, `requested_by_id`, `office_id`, `type_id`, `title`, `description`, `status`, `created_document_id`, `created_at`) VALUES
(1, 6, 1, 2, 'Transcript of Records Request', 'Official transcript request for graduate school application', 'Approved', 1, '2026-05-02 10:30:00'),
(2, 7, 2, 4, 'Financial Clearance Certificate', 'Request clearance certificate for summer term', 'Created', 2, '2026-05-05 11:00:00'),
(3, 11, 4, 3, 'IT Access Key Re-issuance', 'Requesting multi-factor token renewal', 'Pending', NULL, '2026-07-21 09:15:00'),
(4, 14, 5, 1, 'Overtime Clearance Form', 'Requesting HR approval for weekend overtime', 'Rejected', NULL, '2026-07-24 14:00:00');

--
-- View structure for view `view_document_summary`
--
CREATE OR REPLACE VIEW `view_document_summary` AS
SELECT 
  `d`.`document_id` AS `document_id`,
  `d`.`tracking_code` AS `tracking_code`,
  `d`.`title` AS `title`,
  `dt`.`type_name` AS `type_name`,
  `d`.`status` AS `status`,
  `o`.`office_name` AS `current_office`,
  `u`.`full_name` AS `created_by`,
  `d`.`created_at` AS `created_at`,
  `d`.`updated_at` AS `updated_at`
FROM `documents` `d`
JOIN `document_types` `dt` ON `d`.`type_id` = `dt`.`type_id`
JOIN `users` `u` ON `d`.`creator_id` = `u`.`user_id`
LEFT JOIN `offices` `o` ON `d`.`current_office_id` = `o`.`office_id`;

--
-- View structure for view `view_office_secretaries`
--
CREATE OR REPLACE VIEW `view_office_secretaries` AS
SELECT 
  `os`.`office_id` AS `office_id`,
  `o`.`office_name` AS `office_name`,
  `u`.`user_id` AS `secretary_user_id`,
  `u`.`full_name` AS `secretary_name`,
  `u`.`email` AS `secretary_email`
FROM `office_secretaries` `os`
JOIN `users` `u` ON `os`.`secretary_user_id` = `u`.`user_id`
JOIN `offices` `o` ON `os`.`office_id` = `o`.`office_id`;

--
-- Table structure for table `system_settings`
--
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_key` VARCHAR(100) NOT NULL PRIMARY KEY,
  `setting_value` VARCHAR(255) NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('require_admin_approval', '1');

SET FOREIGN_KEY_CHECKS = 1;
