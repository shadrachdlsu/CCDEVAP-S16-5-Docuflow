-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2026 at 10:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `docuflow_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `document_id` int(11) NOT NULL,
  `tracking_code` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `requires_signature` tinyint(1) NOT NULL DEFAULT 1,
  `creator_id` int(11) NOT NULL,
  `current_office_id` int(11) DEFAULT NULL,
  `status` enum('Created','Pending','Received','Released','For Signature','Signed','Rejected','Completed','Recalled') NOT NULL DEFAULT 'Created',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`document_id`, `tracking_code`, `title`, `file_path`, `type_id`, `requires_signature`, `creator_id`, `current_office_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'DOC-2026-001', 'Annual Budget Allocation Request', 'pdfs/dummy-doc-001.pdf', 1, 1, 6, 1, 'Pending', '2026-05-06 01:11:00', '2026-05-07 02:11:00'),
(2, 'DOC-2026-002', 'IT Equipment Procurement Plan', 'pdfs/dummy-doc-002.pdf', 2, 1, 7, 2, 'Completed', '2026-05-11 02:12:00', '2026-05-13 03:12:00'),
(3, 'DOC-2026-003', 'Employee Training Program Proposal', 'pdfs/dummy-doc-003.pdf', 3, 1, 10, 3, 'Signed', '2026-05-16 03:13:00', '2026-05-19 04:13:00'),
(4, 'DOC-2026-004', 'Procurement Approval Stationery', 'pdfs/dummy-doc-004.pdf', 4, 1, 11, 4, 'Rejected', '2026-05-21 04:14:00', '2026-05-21 05:14:00'),
(5, 'DOC-2026-005', 'Quarterly Financial Audit Report', 'pdfs/dummy-doc-005.pdf', 5, 0, 12, 5, 'Received', '2026-05-26 05:15:00', '2026-05-27 06:15:00'),
(6, 'DOC-2026-006', 'Infrastructure Maintenance Plan', 'pdfs/dummy-doc-006.pdf', 6, 1, 13, 6, 'For Signature', '2026-05-04 06:16:00', '2026-05-06 07:16:00'),
(7, 'DOC-2026-007', 'Staff Performance Review Guidelines', 'pdfs/dummy-doc-007.pdf', 1, 1, 14, 7, 'Released', '2026-05-09 07:17:00', '2026-05-12 08:17:00'),
(8, 'DOC-2026-008', 'Software License Renewal Request', 'pdfs/dummy-doc-008.pdf', 2, 1, 15, 1, 'Created', '2026-05-14 08:18:00', '2026-05-14 09:18:00'),
(9, 'DOC-2026-009', 'Campus Facility Upgrade Proposal', 'pdfs/dummy-doc-009.pdf', 3, 1, 16, 2, 'Recalled', '2026-05-19 00:19:00', '2026-05-20 01:19:00'),
(10, 'DOC-2026-010', 'Departmental Operating Expenses', 'pdfs/dummy-doc-010.pdf', 4, 0, 17, 3, 'Pending', '2026-05-24 01:20:00', '2026-05-26 02:20:00'),
(11, 'DOC-2026-011', 'Research Grant Application Form', 'pdfs/dummy-doc-011.pdf', 5, 1, 18, 4, 'Completed', '2026-05-02 02:21:00', '2026-05-05 03:21:00'),
(12, 'DOC-2026-012', 'Security Policy Update Notice', 'pdfs/dummy-doc-012.pdf', 6, 1, 6, 5, 'Signed', '2026-05-07 03:22:00', '2026-05-07 04:22:00'),
(13, 'DOC-2026-013', 'Vendor Service Agreement Renewal', 'pdfs/dummy-doc-013.pdf', 1, 1, 7, 6, 'Rejected', '2026-05-12 04:23:00', '2026-05-13 05:23:00'),
(14, 'DOC-2026-014', 'Health and Safety Inspection Report', 'pdfs/dummy-doc-014.pdf', 2, 1, 10, 7, 'Received', '2026-06-17 05:24:00', '2026-06-19 06:24:00'),
(15, 'DOC-2026-015', 'Strategic Planning Draft 2026', 'pdfs/dummy-doc-015.pdf', 3, 0, 11, 1, 'For Signature', '2026-06-22 06:25:00', '2026-06-25 07:25:00'),
(16, 'DOC-2026-016', 'Curriculum Revision Proposal', 'pdfs/dummy-doc-016.pdf', 4, 1, 12, 2, 'Released', '2026-06-27 07:26:00', '2026-06-27 08:26:00'),
(17, 'DOC-2026-017', 'Student Travel Approval Form', 'pdfs/dummy-doc-017.pdf', 5, 1, 13, 3, 'Created', '2026-06-05 08:27:00', '2026-06-06 09:27:00'),
(18, 'DOC-2026-018', 'Laboratory Supplies Requisition', 'pdfs/dummy-doc-018.pdf', 6, 1, 14, 4, 'Recalled', '2026-06-10 00:28:00', '2026-06-12 01:28:00'),
(19, 'DOC-2026-019', 'Network Infrastructure Audit', 'pdfs/dummy-doc-019.pdf', 1, 1, 15, 5, 'Pending', '2026-06-15 01:29:00', '2026-06-18 02:29:00'),
(20, 'DOC-2026-020', 'Event Hosting Approval Request', 'pdfs/dummy-doc-020.pdf', 2, 0, 16, 6, 'Completed', '2026-06-20 02:30:00', '2026-06-20 03:30:00'),
(21, 'DOC-2026-021', 'Marketing & Outreach Plan', 'pdfs/dummy-doc-021.pdf', 3, 1, 17, 7, 'Signed', '2026-06-25 03:31:00', '2026-06-26 04:31:00'),
(22, 'DOC-2026-022', 'Data Privacy Compliance Review', 'pdfs/dummy-doc-022.pdf', 4, 1, 18, 1, 'Rejected', '2026-06-03 04:32:00', '2026-06-05 05:32:00'),
(23, 'DOC-2026-023', 'Energy Conservation Directive', 'pdfs/dummy-doc-023.pdf', 5, 1, 6, 2, 'Received', '2026-06-08 05:33:00', '2026-06-11 06:33:00'),
(24, 'DOC-2026-024', 'Workplace Ergonomics Survey', 'pdfs/dummy-doc-024.pdf', 6, 1, 7, 3, 'For Signature', '2026-06-13 06:34:00', '2026-06-13 07:34:00'),
(25, 'DOC-2026-025', 'Emergency Response Action Plan', 'pdfs/dummy-doc-025.pdf', 1, 0, 10, 4, 'Released', '2026-06-18 07:35:00', '2026-06-19 08:35:00'),
(26, 'DOC-2026-026', 'Faculty Promotion Evaluation', 'pdfs/dummy-doc-026.pdf', 2, 1, 11, 5, 'Created', '2026-06-23 08:36:00', '2026-06-25 09:36:00'),
(27, 'DOC-2026-027', 'Asset Disposal Request Form', 'pdfs/dummy-doc-027.pdf', 3, 1, 12, 6, 'Recalled', '2026-07-01 00:37:00', '2026-07-04 01:37:00'),
(28, 'DOC-2026-028', 'Cloud Migration Proposal', 'pdfs/dummy-doc-028.pdf', 4, 1, 13, 7, 'Pending', '2026-07-06 01:38:00', '2026-07-06 02:38:00'),
(29, 'DOC-2026-029', 'Disaster Recovery Testing Log', 'pdfs/dummy-doc-029.pdf', 5, 1, 14, 1, 'Completed', '2026-07-11 02:39:00', '2026-07-12 03:39:00'),
(30, 'DOC-2026-030', 'Internal Audit Findings Summary', 'pdfs/dummy-doc-030.pdf', 6, 0, 15, 2, 'Signed', '2026-07-16 03:40:00', '2026-07-18 04:40:00'),
(31, 'DOC-2026-031', 'Travel Expense Reimbursement', 'pdfs/dummy-doc-031.pdf', 1, 1, 16, 3, 'Rejected', '2026-07-21 04:41:00', '2026-07-24 05:41:00'),
(32, 'DOC-2026-032', 'Overtime Work Approval Request', 'pdfs/dummy-doc-032.pdf', 2, 1, 17, 4, 'Received', '2026-07-26 05:42:00', '2026-07-26 06:42:00'),
(33, 'DOC-2026-033', 'Vehicle Fleet Maintenance Log', 'pdfs/dummy-doc-033.pdf', 3, 1, 18, 5, 'For Signature', '2026-07-04 06:43:00', '2026-07-05 07:43:00'),
(34, 'DOC-2026-034', 'Scholarship Application Summary', 'pdfs/dummy-doc-034.pdf', 4, 1, 6, 6, 'Released', '2026-07-09 07:44:00', '2026-07-11 08:44:00'),
(35, 'DOC-2026-035', 'Library Resource Acquisition', 'pdfs/dummy-doc-035.pdf', 5, 0, 7, 7, 'Created', '2026-07-14 08:45:00', '2026-07-17 09:45:00'),
(36, 'DOC-2026-036', 'Waste Management Protocol', 'pdfs/dummy-doc-036.pdf', 6, 1, 10, 1, 'Recalled', '2026-07-19 00:46:00', '2026-07-19 01:46:00'),
(37, 'DOC-2026-037', 'Cafeteria Operations Review', 'pdfs/dummy-doc-037.pdf', 1, 1, 11, 2, 'Pending', '2026-07-24 01:47:00', '2026-07-25 02:47:00'),
(38, 'DOC-2026-038', 'Telecommuting Policy Framework', 'pdfs/dummy-doc-038.pdf', 2, 1, 12, 3, 'Completed', '2026-07-02 02:48:00', '2026-07-04 03:48:00'),
(39, 'DOC-2026-039', 'Community Relations Report', 'pdfs/dummy-doc-039.pdf', 3, 1, 13, 4, 'Signed', '2026-07-07 03:49:00', '2026-07-10 04:49:00'),
(40, 'DOC-2026-040', 'Risk Management Assessment', 'pdfs/dummy-doc-040.pdf', 4, 0, 14, 5, 'Rejected', '2026-08-12 04:50:00', '2026-08-12 05:50:00'),
(41, 'DOC-2026-041', 'Key Card Access Log Summary', 'pdfs/dummy-doc-041.pdf', 5, 1, 15, 6, 'Received', '2026-08-17 05:51:00', '2026-08-18 06:51:00'),
(42, 'DOC-2026-042', 'Print & Publishing Requisition', 'pdfs/dummy-doc-042.pdf', 6, 1, 16, 7, 'For Signature', '2026-08-22 06:52:00', '2026-08-24 07:52:00'),
(43, 'DOC-2026-043', 'Alumni Association Update', 'pdfs/dummy-doc-043.pdf', 1, 1, 17, 1, 'Released', '2026-08-27 07:53:00', '2026-08-28 08:53:00'),
(44, 'DOC-2026-044', 'Graduate Studies Prospectus', 'pdfs/dummy-doc-044.pdf', 2, 1, 18, 2, 'Created', '2026-08-05 08:54:00', '2026-08-05 09:54:00'),
(45, 'DOC-2026-045', 'Copyright License Clearance', 'pdfs/dummy-doc-045.pdf', 3, 0, 6, 3, 'Recalled', '2026-08-10 00:55:00', '2026-08-11 01:55:00'),
(46, 'DOC-2026-046', 'Internship Placement Agreement', 'pdfs/dummy-doc-046.pdf', 4, 1, 7, 4, 'Pending', '2026-08-15 01:56:00', '2026-08-17 02:56:00'),
(47, 'DOC-2026-047', 'Honorarium Disbursement Form', 'pdfs/dummy-doc-047.pdf', 5, 1, 10, 5, 'Completed', '2026-08-20 02:57:00', '2026-08-23 03:57:00'),
(48, 'DOC-2026-048', 'Building Renovation Permit', 'pdfs/dummy-doc-048.pdf', 6, 1, 11, 6, 'Signed', '2026-08-25 03:10:00', '2026-08-25 04:10:00'),
(49, 'DOC-2026-049', 'Water Quality Test Results', 'pdfs/dummy-doc-049.pdf', 1, 1, 12, 7, 'Rejected', '2026-08-03 04:11:00', '2026-08-04 05:11:00'),
(50, 'DOC-2026-050', 'Annual General Report 2026', 'pdfs/dummy-doc-050.pdf', 2, 0, 13, 1, 'Received', '2026-08-08 05:12:00', '2026-08-10 06:12:00'),
(51, 'DOC-2026-REG-01', 'Official Transcript of Records Request', 'pdfs/dummy-doc-001.pdf', 2, 1, 6, 1, 'Pending', '2026-08-01 09:00:00', '2026-08-01 09:00:00'),
(52, 'DOC-2026-REG-02', 'Academic Clearance Certificate', 'pdfs/dummy-doc-002.pdf', 1, 1, 11, 1, 'Signed', '2026-08-02 10:00:00', '2026-08-02 11:30:00'),
(53, 'DOC-2026-FIN-01', 'Q3 Departmental Budget Allocation Request', 'pdfs/dummy-doc-003.pdf', 1, 1, 7, 2, 'Pending', '2026-08-01 10:00:00', '2026-08-01 10:00:00'),
(54, 'DOC-2026-FIN-02', 'Requisition Order for Lab Workstations', 'pdfs/dummy-doc-004.pdf', 5, 1, 14, 2, 'For Signature', '2026-08-02 14:00:00', '2026-08-02 14:30:00'),
(55, 'DOC-2026-DEN-01', 'Faculty Sabbatical Leave Request', 'pdfs/dummy-doc-005.pdf', 2, 1, 10, 3, 'Pending', '2026-08-01 11:00:00', '2026-08-01 11:00:00'),
(56, 'DOC-2026-DEN-02', 'Curriculum Revision Endorsement', 'pdfs/dummy-doc-006.pdf', 3, 1, 15, 3, 'Completed', '2026-08-03 09:00:00', '2026-08-03 16:00:00'),
(57, 'DOC-2026-IT-01', 'Server Infrastructure Upgrade Procurement', 'pdfs/dummy-doc-007.pdf', 5, 1, 12, 4, 'Pending', '2026-08-01 13:00:00', '2026-08-01 13:00:00'),
(58, 'DOC-2026-IT-02', 'Annual Network Security Audit Report', 'pdfs/dummy-doc-008.pdf', 4, 1, 18, 4, 'Received', '2026-08-02 15:00:00', '2026-08-02 15:15:00'),
(59, 'DOC-2026-HR-01', 'Staff Promotion & Salary Adjustment Proposal', 'pdfs/dummy-doc-009.pdf', 2, 1, 13, 5, 'Pending', '2026-08-01 14:00:00', '2026-08-01 14:00:00'),
(60, 'DOC-2026-HR-02', 'Institutional Onboarding Directive 2026', 'pdfs/dummy-doc-010.pdf', 6, 1, 13, 5, 'Signed', '2026-08-03 11:00:00', '2026-08-03 14:20:00'),
(61, 'DOC-2026-ADM-01', 'International Student Eligibility Clearance', 'pdfs/dummy-doc-011.pdf', 2, 1, 16, 6, 'Pending', '2026-08-01 15:00:00', '2026-08-01 15:00:00'),
(62, 'DOC-2026-ADM-02', 'Academic Scholarship Grant Recommendation', 'pdfs/dummy-doc-012.pdf', 1, 1, 16, 6, 'Completed', '2026-08-02 08:30:00', '2026-08-03 10:00:00'),
(63, 'DOC-2026-RND-01', 'AI & Quantum Computing Research Grant Proposal', 'pdfs/dummy-doc-013.pdf', 1, 1, 17, 7, 'Pending', '2026-08-01 16:00:00', '2026-08-01 16:00:00'),
(64, 'DOC-2026-RND-02', 'Publication Ethics & Integrity Directive', 'pdfs/dummy-doc-014.pdf', 6, 1, 17, 7, 'Signed', '2026-08-03 13:00:00', '2026-08-03 15:45:00'),
(65, 'DOC-2026-SEC-R01', 'Registrar Internal Policy Memo', 'pdfs/dummy-doc-001.pdf', 3, 1, 2, 2, 'Pending', '2026-07-20 08:00:00', '2026-07-20 08:00:00'),
(66, 'DOC-2026-SEC-R02', 'Student Enrollment Verification Request', 'pdfs/dummy-doc-002.pdf', 2, 1, 6, 1, 'Pending', '2026-07-21 09:00:00', '2026-07-21 09:00:00'),
(67, 'DOC-2026-SEC-R03', 'Diploma Release Endorsement', 'pdfs/dummy-doc-003.pdf', 2, 1, 11, 1, 'Signed', '2026-07-22 10:00:00', '2026-07-22 12:00:00'),
(68, 'DOC-2026-SEC-F01', 'Financial Compliance Review Memo', 'pdfs/dummy-doc-004.pdf', 4, 1, 3, 4, 'Pending', '2026-07-20 08:30:00', '2026-07-20 08:30:00'),
(69, 'DOC-2026-SEC-F02', 'Tuition Fee Adjustment Request', 'pdfs/dummy-doc-005.pdf', 2, 1, 7, 2, 'Pending', '2026-07-21 09:30:00', '2026-07-21 09:30:00'),
(70, 'DOC-2026-SEC-F03', 'Budget Reconciliation Certificate', 'pdfs/dummy-doc-006.pdf', 4, 1, 14, 2, 'Signed', '2026-07-22 10:30:00', '2026-07-22 12:30:00'),
(71, 'DOC-2026-SEC-D01', 'Dean Office Operations Summary', 'pdfs/dummy-doc-007.pdf', 3, 1, 4, 1, 'Pending', '2026-07-20 09:00:00', '2026-07-20 09:00:00'),
(72, 'DOC-2026-SEC-D02', 'Faculty Workload Redistribution Plan', 'pdfs/dummy-doc-008.pdf', 2, 1, 10, 3, 'Pending', '2026-07-21 10:00:00', '2026-07-21 10:00:00'),
(73, 'DOC-2026-SEC-D03', 'Academic Calendar Amendment Approval', 'pdfs/dummy-doc-009.pdf', 3, 1, 15, 3, 'Signed', '2026-07-22 11:00:00', '2026-07-22 13:00:00'),
(74, 'DOC-2026-SEC-I01', 'IT Infrastructure Quarterly Review', 'pdfs/dummy-doc-010.pdf', 4, 1, 5, 2, 'Pending', '2026-07-20 09:30:00', '2026-07-20 09:30:00'),
(75, 'DOC-2026-SEC-I02', 'Cybersecurity Incident Escalation Form', 'pdfs/dummy-doc-011.pdf', 4, 1, 12, 4, 'Pending', '2026-07-21 10:30:00', '2026-07-21 10:30:00'),
(76, 'DOC-2026-SEC-I03', 'Software License Audit Clearance', 'pdfs/dummy-doc-012.pdf', 5, 1, 18, 4, 'Signed', '2026-07-22 11:30:00', '2026-07-22 13:30:00'),
(77, 'DOC-2026-SEC-H01', 'HR Annual Compliance Certification', 'pdfs/dummy-doc-013.pdf', 6, 1, 9, 4, 'Pending', '2026-07-20 10:00:00', '2026-07-20 10:00:00'),
(78, 'DOC-2026-SEC-H02', 'Employee Grievance Resolution Form', 'pdfs/dummy-doc-014.pdf', 2, 1, 13, 5, 'Pending', '2026-07-21 11:00:00', '2026-07-21 11:00:00'),
(79, 'DOC-2026-SEC-H03', 'Workplace Safety Compliance Report', 'pdfs/dummy-doc-001.pdf', 4, 1, 13, 5, 'Signed', '2026-07-22 12:00:00', '2026-07-22 14:00:00'),
(80, 'DOC-2026-SEC-A01', 'Admissions Enrollment Projections Report', 'pdfs/dummy-doc-002.pdf', 4, 1, 19, 4, 'Pending', '2026-07-20 10:30:00', '2026-07-20 10:30:00'),
(81, 'DOC-2026-SEC-A02', 'Transfer Student Credential Assessment', 'pdfs/dummy-doc-003.pdf', 2, 1, 16, 6, 'Pending', '2026-07-21 11:30:00', '2026-07-21 11:30:00'),
(82, 'DOC-2026-SEC-A03', 'Freshmen Orientation Program Approval', 'pdfs/dummy-doc-004.pdf', 2, 1, 16, 6, 'Signed', '2026-07-22 12:30:00', '2026-07-22 14:30:00'),
(83, 'DOC-2026-SEC-N01', 'R&D Grant Utilization Summary', 'pdfs/dummy-doc-005.pdf', 1, 1, 20, 4, 'Pending', '2026-07-20 11:00:00', '2026-07-20 11:00:00'),
(84, 'DOC-2026-SEC-N02', 'Research Paper Submission Clearance', 'pdfs/dummy-doc-006.pdf', 1, 1, 17, 7, 'Pending', '2026-07-21 12:00:00', '2026-07-21 12:00:00'),
(85, 'DOC-2026-SEC-N03', 'Lab Equipment Procurement Endorsement', 'pdfs/dummy-doc-007.pdf', 5, 1, 17, 7, 'Signed', '2026-07-22 13:00:00', '2026-07-22 15:00:00');


-- --------------------------------------------------------

--
-- Table structure for table `document_assignments`
--

CREATE TABLE `document_assignments` (
  `assignment_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `assigned_to_user_id` int(11) NOT NULL,
  `assigned_by_user_id` int(11) DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Signed','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `signed_file_path` varchar(255) DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `acted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_assignments`
--

INSERT INTO `document_assignments` (`assignment_id`, `document_id`, `assigned_to_user_id`, `assigned_by_user_id`, `office_id`, `status`, `remarks`, `signed_file_path`, `assigned_at`, `acted_at`) VALUES
(1, 1, 7, 6, 1, 'Pending', 'Action required for approval workflow', NULL, '2026-05-06 01:11:00', NULL),
(2, 2, 10, 7, 2, 'Signed', 'Action required for approval workflow', NULL, '2026-05-11 02:12:00', '2026-05-13 03:12:00'),
(3, 3, 12, 10, 3, 'Signed', 'Action required for approval workflow', NULL, '2026-05-16 03:13:00', '2026-05-19 04:13:00'),
(4, 4, 14, 11, 4, 'Rejected', 'Action required for approval workflow', NULL, '2026-05-21 04:14:00', '2026-05-21 05:14:00'),
(5, 6, 7, 13, 6, 'Pending', 'Action required for approval workflow', NULL, '2026-05-04 06:16:00', NULL),
(6, 7, 10, 14, 7, 'Pending', 'Action required for approval workflow', NULL, '2026-05-09 07:17:00', NULL),
(7, 8, 12, 15, 1, 'Pending', 'Action required for approval workflow', NULL, '2026-05-14 08:18:00', NULL),
(8, 9, 14, 16, 2, 'Pending', 'Action required for approval workflow', NULL, '2026-05-19 00:19:00', NULL),
(9, 11, 7, 18, 4, 'Signed', 'Action required for approval workflow', NULL, '2026-05-02 02:21:00', '2026-05-05 03:21:00'),
(10, 12, 10, 6, 5, 'Signed', 'Action required for approval workflow', NULL, '2026-05-07 03:22:00', '2026-05-07 04:22:00'),
(11, 13, 12, 7, 6, 'Rejected', 'Action required for approval workflow', NULL, '2026-05-12 04:23:00', '2026-05-13 05:23:00'),
(12, 14, 14, 10, 7, 'Pending', 'Action required for approval workflow', NULL, '2026-06-17 05:24:00', NULL),
(13, 16, 7, 12, 2, 'Pending', 'Action required for approval workflow', NULL, '2026-06-27 07:26:00', NULL),
(14, 17, 10, 13, 3, 'Pending', 'Action required for approval workflow', NULL, '2026-06-05 08:27:00', NULL),
(15, 18, 12, 14, 4, 'Pending', 'Action required for approval workflow', NULL, '2026-06-10 00:28:00', NULL),
(16, 19, 14, 15, 5, 'Pending', 'Action required for approval workflow', NULL, '2026-06-15 01:29:00', NULL),
(17, 21, 7, 17, 7, 'Signed', 'Action required for approval workflow', NULL, '2026-06-25 03:31:00', '2026-06-26 04:31:00'),
(18, 22, 10, 18, 1, 'Rejected', 'Action required for approval workflow', NULL, '2026-06-03 04:32:00', '2026-06-05 05:32:00'),
(19, 23, 12, 6, 2, 'Pending', 'Action required for approval workflow', NULL, '2026-06-08 05:33:00', NULL),
(20, 24, 14, 7, 3, 'Pending', 'Action required for approval workflow', NULL, '2026-06-13 06:34:00', NULL),
(21, 26, 7, 11, 5, 'Pending', 'Action required for approval workflow', NULL, '2026-06-23 08:36:00', NULL),
(22, 27, 10, 12, 6, 'Pending', 'Action required for approval workflow', NULL, '2026-07-01 00:37:00', NULL),
(23, 28, 12, 13, 7, 'Pending', 'Action required for approval workflow', NULL, '2026-07-06 01:38:00', NULL),
(24, 29, 14, 14, 1, 'Signed', 'Action required for approval workflow', NULL, '2026-07-11 02:39:00', '2026-07-12 03:39:00'),
(25, 31, 7, 16, 3, 'Rejected', 'Action required for approval workflow', NULL, '2026-07-21 04:41:00', '2026-07-24 05:41:00'),
(26, 32, 10, 17, 4, 'Pending', 'Action required for approval workflow', NULL, '2026-07-26 05:42:00', NULL),
(27, 33, 12, 18, 5, 'Pending', 'Action required for approval workflow', NULL, '2026-07-04 06:43:00', NULL),
(28, 34, 14, 6, 6, 'Pending', 'Action required for approval workflow', NULL, '2026-07-09 07:44:00', NULL),
(29, 36, 7, 10, 1, 'Pending', 'Action required for approval workflow', NULL, '2026-07-19 00:46:00', NULL),
(30, 37, 10, 11, 2, 'Pending', 'Action required for approval workflow', NULL, '2026-07-24 01:47:00', NULL),
(31, 38, 12, 12, 3, 'Signed', 'Action required for approval workflow', NULL, '2026-07-02 02:48:00', '2026-07-04 03:48:00'),
(32, 39, 14, 13, 4, 'Signed', 'Action required for approval workflow', NULL, '2026-07-07 03:49:00', '2026-07-10 04:49:00'),
(33, 41, 7, 15, 6, 'Pending', 'Action required for approval workflow', NULL, '2026-08-17 05:51:00', NULL),
(34, 42, 10, 16, 7, 'Pending', 'Action required for approval workflow', NULL, '2026-08-22 06:52:00', NULL),
(35, 43, 12, 17, 1, 'Pending', 'Action required for approval workflow', NULL, '2026-08-27 07:53:00', NULL),
(36, 44, 14, 18, 2, 'Pending', 'Action required for approval workflow', NULL, '2026-08-05 08:54:00', NULL),
(37, 46, 7, 7, 4, 'Pending', 'Action required for approval workflow', NULL, '2026-08-15 01:56:00', NULL),
(38, 47, 10, 10, 5, 'Signed', 'Action required for approval workflow', NULL, '2026-08-20 02:57:00', '2026-08-23 03:57:00'),
(39, 48, 12, 11, 6, 'Signed', 'Action required for approval workflow', NULL, '2026-08-25 03:10:00', '2026-08-25 04:10:00'),
(40, 49, 14, 12, 7, 'Rejected', 'Action required for approval workflow', NULL, '2026-08-03 04:11:00', '2026-08-04 05:11:00'),
(41, 52, 11, 2, 1, 'Signed', 'Academic clearance assigned and signed', NULL, '2026-08-02 10:15:00', '2026-08-02 11:30:00'),
(42, 54, 14, 3, 2, 'Pending', 'Requisition assigned to David Chen for verification', NULL, '2026-08-02 14:15:00', NULL),
(43, 56, 15, 4, 3, 'Signed', 'Curriculum proposal assigned and finalized', NULL, '2026-08-03 09:30:00', '2026-08-03 16:00:00'),
(44, 58, 18, 5, 4, 'Pending', 'Network audit assigned to Liam Wilson', NULL, '2026-08-02 15:10:00', NULL),
(45, 60, 13, 9, 5, 'Signed', 'Onboarding policy assigned to Elena Gomez', NULL, '2026-08-03 11:30:00', '2026-08-03 14:20:00'),
(46, 62, 16, 19, 6, 'Signed', 'Scholarship recommendation assigned to Robert Taylor', NULL, '2026-08-02 09:00:00', '2026-08-03 10:00:00'),
(47, 64, 17, 20, 7, 'Signed', 'Ethics directive assigned to Sophia Martinez', NULL, '2026-08-03 13:30:00', '2026-08-03 15:45:00'),
(48, 66, 2, 2, 1, 'Pending', 'Enrollment verification assigned to Registrar Secretary', NULL, '2026-07-21 09:15:00', NULL),
(49, 67, 2, 2, 1, 'Signed', 'Diploma release signed by Registrar Secretary', NULL, '2026-07-22 10:15:00', '2026-07-22 12:00:00'),
(50, 69, 3, 3, 2, 'Pending', 'Tuition adjustment assigned to Finance Secretary', NULL, '2026-07-21 09:45:00', NULL),
(51, 70, 3, 3, 2, 'Signed', 'Budget reconciliation signed by Finance Secretary', NULL, '2026-07-22 10:45:00', '2026-07-22 12:30:00'),
(52, 72, 4, 4, 3, 'Pending', 'Workload plan assigned to Dean Secretary', NULL, '2026-07-21 10:15:00', NULL),
(53, 73, 4, 4, 3, 'Signed', 'Calendar amendment signed by Dean Secretary', NULL, '2026-07-22 11:15:00', '2026-07-22 13:00:00'),
(54, 75, 5, 5, 4, 'Pending', 'Cyber incident form assigned to IT Secretary', NULL, '2026-07-21 10:45:00', NULL),
(55, 76, 5, 5, 4, 'Signed', 'License audit signed by IT Secretary', NULL, '2026-07-22 11:45:00', '2026-07-22 13:30:00'),
(56, 78, 9, 9, 5, 'Pending', 'Grievance form assigned to HR Secretary', NULL, '2026-07-21 11:15:00', NULL),
(57, 79, 9, 9, 5, 'Signed', 'Safety report signed by HR Secretary', NULL, '2026-07-22 12:15:00', '2026-07-22 14:00:00'),
(58, 81, 19, 19, 6, 'Pending', 'Transfer assessment assigned to Admissions Secretary', NULL, '2026-07-21 11:45:00', NULL),
(59, 82, 19, 19, 6, 'Signed', 'Orientation program signed by Admissions Secretary', NULL, '2026-07-22 12:45:00', '2026-07-22 14:30:00'),
(60, 84, 20, 20, 7, 'Pending', 'Research clearance assigned to R&D Secretary', NULL, '2026-07-21 12:15:00', NULL),
(61, 85, 20, 20, 7, 'Signed', 'Lab procurement signed by R&D Secretary', NULL, '2026-07-22 13:15:00', '2026-07-22 15:00:00');


-- --------------------------------------------------------

--
-- Table structure for table `document_routes`
--

CREATE TABLE `document_routes` (
  `route_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `step_no` int(11) NOT NULL DEFAULT 0,
  `office_id` int(11) DEFAULT NULL,
  `recipient_scope` enum('Individual','Office') NOT NULL DEFAULT 'Individual',
  `signatory_user_id` int(11) DEFAULT NULL,
  `status` enum('Waiting','Received','For Signature','Signed','Rejected','Released','Skipped','Completed') NOT NULL DEFAULT 'Waiting',
  `remarks` text DEFAULT NULL,
  `acted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_routes`
--

INSERT INTO `document_routes` (`route_id`, `document_id`, `step_no`, `office_id`, `recipient_scope`, `signatory_user_id`, `status`, `remarks`, `acted_at`) VALUES
(1, 1, 1, 1, 'Individual', 7, 'Waiting', NULL, NULL),
(2, 2, 1, 2, 'Individual', 10, 'Signed', NULL, '2026-05-13 03:12:00'),
(3, 3, 1, 3, 'Individual', 12, 'Signed', NULL, '2026-05-19 04:13:00'),
(4, 4, 1, 4, 'Individual', 14, 'Rejected', NULL, '2026-05-21 05:14:00'),
(5, 5, 1, 5, 'Individual', 15, 'Waiting', NULL, NULL),
(6, 6, 1, 6, 'Individual', 7, 'Waiting', NULL, NULL),
(7, 7, 1, 7, 'Individual', 10, 'Waiting', NULL, NULL),
(8, 8, 1, 1, 'Individual', 12, 'Waiting', NULL, NULL),
(9, 9, 1, 2, 'Individual', 14, 'Waiting', NULL, NULL),
(10, 10, 1, 3, 'Individual', 15, 'Waiting', NULL, NULL),
(11, 11, 1, 4, 'Individual', 7, 'Signed', NULL, '2026-05-05 03:21:00'),
(12, 12, 1, 5, 'Individual', 10, 'Signed', NULL, '2026-05-07 04:22:00'),
(13, 13, 1, 6, 'Individual', 12, 'Rejected', NULL, '2026-05-13 05:23:00'),
(14, 14, 1, 7, 'Individual', 14, 'Waiting', NULL, NULL),
(15, 15, 1, 1, 'Individual', 15, 'Waiting', NULL, NULL),
(16, 16, 1, 2, 'Individual', 7, 'Waiting', NULL, NULL),
(17, 17, 1, 3, 'Individual', 10, 'Waiting', NULL, NULL),
(18, 18, 1, 4, 'Individual', 12, 'Waiting', NULL, NULL),
(19, 19, 1, 5, 'Individual', 14, 'Waiting', NULL, NULL),
(20, 20, 1, 6, 'Individual', 15, 'Signed', NULL, '2026-06-20 03:30:00'),
(21, 21, 1, 7, 'Individual', 7, 'Signed', NULL, '2026-06-26 04:31:00'),
(22, 22, 1, 1, 'Individual', 10, 'Rejected', NULL, '2026-06-05 05:32:00'),
(23, 23, 1, 2, 'Individual', 12, 'Waiting', NULL, NULL),
(24, 24, 1, 3, 'Individual', 14, 'Waiting', NULL, NULL),
(25, 25, 1, 4, 'Individual', 15, 'Waiting', NULL, NULL),
(26, 26, 1, 5, 'Individual', 7, 'Waiting', NULL, NULL),
(27, 27, 1, 6, 'Individual', 10, 'Waiting', NULL, NULL),
(28, 28, 1, 7, 'Individual', 12, 'Waiting', NULL, NULL),
(29, 29, 1, 1, 'Individual', 14, 'Signed', NULL, '2026-07-12 03:39:00'),
(30, 30, 1, 2, 'Individual', 15, 'Signed', NULL, '2026-07-18 04:40:00'),
(31, 31, 1, 3, 'Individual', 7, 'Rejected', NULL, '2026-07-24 05:41:00'),
(32, 32, 1, 4, 'Individual', 10, 'Waiting', NULL, NULL),
(33, 33, 1, 5, 'Individual', 12, 'Waiting', NULL, NULL),
(34, 34, 1, 6, 'Individual', 14, 'Waiting', NULL, NULL),
(35, 35, 1, 7, 'Individual', 15, 'Waiting', NULL, NULL),
(36, 36, 1, 1, 'Individual', 7, 'Waiting', NULL, NULL),
(37, 37, 1, 2, 'Individual', 10, 'Waiting', NULL, NULL),
(38, 38, 1, 3, 'Individual', 12, 'Signed', NULL, '2026-07-04 03:48:00'),
(39, 39, 1, 4, 'Individual', 14, 'Signed', NULL, '2026-07-10 04:49:00'),
(40, 40, 1, 5, 'Individual', 15, 'Rejected', NULL, '2026-08-12 05:50:00'),
(41, 41, 1, 6, 'Individual', 7, 'Waiting', NULL, NULL),
(42, 42, 1, 7, 'Individual', 10, 'Waiting', NULL, NULL),
(43, 43, 1, 1, 'Individual', 12, 'Waiting', NULL, NULL),
(44, 44, 1, 2, 'Individual', 14, 'Waiting', NULL, NULL),
(45, 45, 1, 3, 'Individual', 15, 'Waiting', NULL, NULL),
(46, 46, 1, 4, 'Individual', 7, 'Waiting', NULL, NULL),
(47, 47, 1, 5, 'Individual', 10, 'Signed', NULL, '2026-08-23 03:57:00'),
(48, 48, 1, 6, 'Individual', 12, 'Signed', NULL, '2026-08-25 04:10:00'),
(49, 49, 1, 7, 'Individual', 14, 'Rejected', NULL, '2026-08-04 05:11:00'),
(50, 50, 1, 1, 'Individual', 15, 'Waiting', NULL, NULL),
(51, 51, 1, 1, 'Office', NULL, 'Waiting', 'Routed to Registrar Office queue', NULL),
(52, 52, 1, 1, 'Individual', 11, 'Signed', 'Signed by Alex Santos', '2026-08-02 11:30:00'),
(53, 53, 1, 2, 'Office', NULL, 'Waiting', 'Routed to Finance Office queue', NULL),
(54, 54, 1, 2, 'Individual', 14, 'For Signature', 'Assigned to David Chen', NULL),
(55, 55, 1, 3, 'Office', NULL, 'Waiting', 'Routed to Dean Office queue', NULL),
(56, 56, 1, 3, 'Individual', 15, 'Completed', 'Finalized by Dean Secretary', '2026-08-03 16:00:00'),
(57, 57, 1, 4, 'Office', NULL, 'Waiting', 'Routed to IT Office queue', NULL),
(58, 58, 1, 4, 'Individual', 18, 'Received', 'Received by IT Office', '2026-08-02 15:15:00'),
(59, 59, 1, 5, 'Office', NULL, 'Waiting', 'Routed to HR Office queue', NULL),
(60, 60, 1, 5, 'Individual', 13, 'Signed', 'Signed by HR Secretary', '2026-08-03 14:20:00'),
(61, 61, 1, 6, 'Office', NULL, 'Waiting', 'Routed to Admissions Office queue', NULL),
(62, 62, 1, 6, 'Individual', 16, 'Completed', 'Completed by Admissions Office', '2026-08-03 10:00:00'),
(63, 63, 1, 7, 'Office', NULL, 'Waiting', 'Routed to R&D Office queue', NULL),
(64, 64, 1, 7, 'Individual', 17, 'Signed', 'Signed by R&D Secretary', '2026-08-03 15:45:00'),
(65, 65, 1, 2, 'Individual', 7, 'Waiting', 'Registrar Secretary created and routed to Finance', NULL),
(66, 66, 1, 1, 'Individual', 2, 'Waiting', 'Enrollment verification routed to Registrar Secretary', NULL),
(67, 67, 1, 1, 'Individual', 2, 'Signed', 'Diploma release signed by Registrar Secretary', '2026-07-22 12:00:00'),
(68, 68, 1, 4, 'Individual', 12, 'Waiting', 'Finance Secretary created and routed to IT', NULL),
(69, 69, 1, 2, 'Individual', 3, 'Waiting', 'Tuition adjustment routed to Finance Secretary', NULL),
(70, 70, 1, 2, 'Individual', 3, 'Signed', 'Budget reconciliation signed by Finance Secretary', '2026-07-22 12:30:00'),
(71, 71, 1, 1, 'Individual', 6, 'Waiting', 'Dean Secretary created and routed to Registrar', NULL),
(72, 72, 1, 3, 'Individual', 4, 'Waiting', 'Workload plan routed to Dean Secretary', NULL),
(73, 73, 1, 3, 'Individual', 4, 'Signed', 'Calendar amendment signed by Dean Secretary', '2026-07-22 13:00:00'),
(74, 74, 1, 2, 'Individual', 14, 'Waiting', 'IT Secretary created and routed to Finance', NULL),
(75, 75, 1, 4, 'Individual', 5, 'Waiting', 'Cyber incident form routed to IT Secretary', NULL),
(76, 76, 1, 4, 'Individual', 5, 'Signed', 'License audit signed by IT Secretary', '2026-07-22 13:30:00'),
(77, 77, 1, 4, 'Individual', 18, 'Waiting', 'HR Secretary created and routed to IT', NULL),
(78, 78, 1, 5, 'Individual', 9, 'Waiting', 'Grievance form routed to HR Secretary', NULL),
(79, 79, 1, 5, 'Individual', 9, 'Signed', 'Safety report signed by HR Secretary', '2026-07-22 14:00:00'),
(80, 80, 1, 4, 'Individual', 12, 'Waiting', 'Admissions Secretary created and routed to IT', NULL),
(81, 81, 1, 6, 'Individual', 19, 'Waiting', 'Transfer assessment routed to Admissions Secretary', NULL),
(82, 82, 1, 6, 'Individual', 19, 'Signed', 'Orientation program signed by Admissions Secretary', '2026-07-22 14:30:00'),
(83, 83, 1, 4, 'Individual', 18, 'Waiting', 'R&D Secretary created and routed to IT', NULL),
(84, 84, 1, 7, 'Individual', 20, 'Waiting', 'Research clearance routed to R&D Secretary', NULL),
(85, 85, 1, 7, 'Individual', 20, 'Signed', 'Lab procurement signed by R&D Secretary', '2026-07-22 15:00:00');


-- --------------------------------------------------------

--
-- Table structure for table `document_trails`
--

CREATE TABLE `document_trails` (
  `trail_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `action_by_user_id` int(11) DEFAULT NULL,
  `from_office_id` int(11) DEFAULT NULL,
  `to_office_id` int(11) DEFAULT NULL,
  `action_taken` enum('Created','Received','Assigned','Signed','Rejected','Released','Forwarded','Finished','Cancelled','Requested') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_trails`
--

INSERT INTO `document_trails` (`trail_id`, `document_id`, `action_by_user_id`, `from_office_id`, `to_office_id`, `action_taken`, `remarks`, `created_at`) VALUES
(1, 1, 6, NULL, 1, 'Created', 'Document created and uploaded', '2026-05-06 01:11:00'),
(2, 2, 7, NULL, 2, 'Created', 'Document created and uploaded', '2026-05-11 02:12:00'),
(3, 2, 10, 2, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-05-13 03:12:00'),
(4, 3, 10, NULL, 3, 'Created', 'Document created and uploaded', '2026-05-16 03:13:00'),
(5, 3, 12, 3, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-05-19 04:13:00'),
(6, 4, 11, NULL, 4, 'Created', 'Document created and uploaded', '2026-05-21 04:14:00'),
(7, 4, 14, 4, NULL, 'Rejected', 'Document returned due to missing attachments', '2026-05-21 05:14:00'),
(8, 5, 12, NULL, 5, 'Created', 'Document created and uploaded', '2026-05-26 05:15:00'),
(9, 6, 13, NULL, 6, 'Created', 'Document created and uploaded', '2026-05-04 06:16:00'),
(10, 7, 14, NULL, 7, 'Created', 'Document created and uploaded', '2026-05-09 07:17:00'),
(11, 8, 15, NULL, 1, 'Created', 'Document created and uploaded', '2026-05-14 08:18:00'),
(12, 9, 16, NULL, 2, 'Created', 'Document created and uploaded', '2026-05-19 00:19:00'),
(13, 10, 17, NULL, 3, 'Created', 'Document created and uploaded', '2026-05-24 01:20:00'),
(14, 11, 18, NULL, 4, 'Created', 'Document created and uploaded', '2026-05-02 02:21:00'),
(15, 11, 7, 4, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-05-05 03:21:00'),
(16, 12, 6, NULL, 5, 'Created', 'Document created and uploaded', '2026-05-07 03:22:00'),
(17, 12, 10, 5, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-05-07 04:22:00'),
(18, 13, 7, NULL, 6, 'Created', 'Document created and uploaded', '2026-05-12 04:23:00'),
(19, 13, 12, 6, NULL, 'Rejected', 'Document returned due to missing attachments', '2026-05-13 05:23:00'),
(20, 14, 10, NULL, 7, 'Created', 'Document created and uploaded', '2026-06-17 05:24:00'),
(21, 15, 11, NULL, 1, 'Created', 'Document created and uploaded', '2026-06-22 06:25:00'),
(22, 16, 12, NULL, 2, 'Created', 'Document created and uploaded', '2026-06-27 07:26:00'),
(23, 17, 13, NULL, 3, 'Created', 'Document created and uploaded', '2026-06-05 08:27:00'),
(24, 18, 14, NULL, 4, 'Created', 'Document created and uploaded', '2026-06-10 00:28:00'),
(25, 19, 15, NULL, 5, 'Created', 'Document created and uploaded', '2026-06-15 01:29:00'),
(26, 20, 16, NULL, 6, 'Created', 'Document created and uploaded', '2026-06-20 02:30:00'),
(27, 20, 15, 6, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-06-20 03:30:00'),
(28, 21, 17, NULL, 7, 'Created', 'Document created and uploaded', '2026-06-25 03:31:00'),
(29, 21, 7, 7, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-06-26 04:31:00'),
(30, 22, 18, NULL, 1, 'Created', 'Document created and uploaded', '2026-06-03 04:32:00'),
(31, 22, 10, 1, NULL, 'Rejected', 'Document returned due to missing attachments', '2026-06-05 05:32:00'),
(32, 23, 6, NULL, 2, 'Created', 'Document created and uploaded', '2026-06-08 05:33:00'),
(33, 24, 7, NULL, 3, 'Created', 'Document created and uploaded', '2026-06-13 06:34:00'),
(34, 25, 10, NULL, 4, 'Created', 'Document created and uploaded', '2026-06-18 07:35:00'),
(35, 26, 11, NULL, 5, 'Created', 'Document created and uploaded', '2026-06-23 08:36:00'),
(36, 27, 12, NULL, 6, 'Created', 'Document created and uploaded', '2026-07-01 00:37:00'),
(37, 28, 13, NULL, 7, 'Created', 'Document created and uploaded', '2026-07-06 01:38:00'),
(38, 29, 14, NULL, 1, 'Created', 'Document created and uploaded', '2026-07-11 02:39:00'),
(39, 29, 14, 1, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-07-12 03:39:00'),
(40, 30, 15, NULL, 2, 'Created', 'Document created and uploaded', '2026-07-16 03:40:00'),
(41, 30, 15, 2, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-07-18 04:40:00'),
(42, 31, 16, NULL, 3, 'Created', 'Document created and uploaded', '2026-07-21 04:41:00'),
(43, 31, 7, 3, NULL, 'Rejected', 'Document returned due to missing attachments', '2026-07-24 05:41:00'),
(44, 32, 17, NULL, 4, 'Created', 'Document created and uploaded', '2026-07-26 05:42:00'),
(45, 33, 18, NULL, 5, 'Created', 'Document created and uploaded', '2026-07-04 06:43:00'),
(46, 34, 6, NULL, 6, 'Created', 'Document created and uploaded', '2026-07-09 07:44:00'),
(47, 35, 7, NULL, 7, 'Created', 'Document created and uploaded', '2026-07-14 08:45:00'),
(48, 36, 10, NULL, 1, 'Created', 'Document created and uploaded', '2026-07-19 00:46:00'),
(49, 37, 11, NULL, 2, 'Created', 'Document created and uploaded', '2026-07-24 01:47:00'),
(50, 38, 12, NULL, 3, 'Created', 'Document created and uploaded', '2026-07-02 02:48:00'),
(51, 38, 12, 3, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-07-04 03:48:00'),
(52, 39, 13, NULL, 4, 'Created', 'Document created and uploaded', '2026-07-07 03:49:00'),
(53, 39, 14, 4, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-07-10 04:49:00'),
(54, 40, 14, NULL, 5, 'Created', 'Document created and uploaded', '2026-08-12 04:50:00'),
(55, 40, 15, 5, NULL, 'Rejected', 'Document returned due to missing attachments', '2026-08-12 05:50:00'),
(56, 41, 15, NULL, 6, 'Created', 'Document created and uploaded', '2026-08-17 05:51:00'),
(57, 42, 16, NULL, 7, 'Created', 'Document created and uploaded', '2026-08-22 06:52:00'),
(58, 43, 17, NULL, 1, 'Created', 'Document created and uploaded', '2026-08-27 07:53:00'),
(59, 44, 18, NULL, 2, 'Created', 'Document created and uploaded', '2026-08-05 08:54:00'),
(60, 45, 6, NULL, 3, 'Created', 'Document created and uploaded', '2026-08-10 00:55:00'),
(61, 46, 7, NULL, 4, 'Created', 'Document created and uploaded', '2026-08-15 01:56:00'),
(62, 47, 10, NULL, 5, 'Created', 'Document created and uploaded', '2026-08-20 02:57:00'),
(63, 47, 10, 5, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-08-23 03:57:00'),
(64, 48, 11, NULL, 6, 'Created', 'Document created and uploaded', '2026-08-25 03:10:00'),
(65, 48, 12, 6, NULL, 'Signed', 'Document reviewed and digitally approved', '2026-08-25 04:10:00'),
(66, 49, 12, NULL, 7, 'Created', 'Document created and uploaded', '2026-08-03 04:11:00'),
(67, 49, 14, 7, NULL, 'Rejected', 'Document returned due to missing attachments', '2026-08-04 05:11:00'),
(68, 50, 13, NULL, 1, 'Created', 'Document created and uploaded', '2026-08-08 05:12:00'),
(69, 51, 6, NULL, 1, 'Created', 'Official transcript request created by student member', '2026-08-01 09:00:00'),
(70, 52, 11, NULL, 1, 'Created', 'Academic clearance certificate created', '2026-08-02 10:00:00'),
(71, 52, 2, 1, NULL, 'Signed', 'Academic clearance signed by Registrar Secretary', '2026-08-02 11:30:00'),
(72, 53, 7, NULL, 2, 'Created', 'Q3 Budget allocation request submitted', '2026-08-01 10:00:00'),
(73, 54, 14, NULL, 2, 'Created', 'Requisition order for workstations created', '2026-08-02 14:00:00'),
(74, 55, 10, NULL, 3, 'Created', 'Faculty sabbatical leave request submitted', '2026-08-01 11:00:00'),
(75, 56, 15, NULL, 3, 'Created', 'Curriculum revision proposal created', '2026-08-03 09:00:00'),
(76, 56, 4, 3, NULL, 'Finished', 'Curriculum revision endorsed and finalized', '2026-08-03 16:00:00'),
(77, 57, 12, NULL, 4, 'Created', 'Server infrastructure upgrade request created', '2026-08-01 13:00:00'),
(78, 58, 18, NULL, 4, 'Created', 'Network security audit report submitted', '2026-08-02 15:00:00'),
(79, 59, 13, NULL, 5, 'Created', 'Staff promotion proposal submitted', '2026-08-01 14:00:00'),
(80, 60, 13, NULL, 5, 'Created', 'Onboarding directive document created', '2026-08-03 11:00:00'),
(81, 61, 16, NULL, 6, 'Created', 'International student clearance created', '2026-08-01 15:00:00'),
(82, 62, 16, NULL, 6, 'Created', 'Scholarship grant recommendation created', '2026-08-02 08:30:00'),
(83, 65, 2, NULL, 2, 'Created', 'Internal policy memo created by Registrar Secretary', '2026-07-20 08:00:00'),
(84, 66, 6, NULL, 1, 'Created', 'Enrollment verification request submitted', '2026-07-21 09:00:00'),
(85, 67, 11, NULL, 1, 'Created', 'Diploma release endorsement submitted', '2026-07-22 10:00:00'),
(86, 67, 2, 1, NULL, 'Signed', 'Diploma release signed by Registrar Secretary', '2026-07-22 12:00:00'),
(87, 68, 3, NULL, 4, 'Created', 'Financial compliance memo created by Finance Secretary', '2026-07-20 08:30:00'),
(88, 69, 7, NULL, 2, 'Created', 'Tuition fee adjustment request submitted', '2026-07-21 09:30:00'),
(89, 70, 14, NULL, 2, 'Created', 'Budget reconciliation certificate submitted', '2026-07-22 10:30:00'),
(90, 70, 3, 2, NULL, 'Signed', 'Budget reconciliation signed by Finance Secretary', '2026-07-22 12:30:00'),
(91, 71, 4, NULL, 1, 'Created', 'Operations summary created by Dean Secretary', '2026-07-20 09:00:00'),
(92, 72, 10, NULL, 3, 'Created', 'Faculty workload plan submitted', '2026-07-21 10:00:00'),
(93, 73, 15, NULL, 3, 'Created', 'Academic calendar amendment submitted', '2026-07-22 11:00:00'),
(94, 73, 4, 3, NULL, 'Signed', 'Calendar amendment signed by Dean Secretary', '2026-07-22 13:00:00'),
(95, 74, 5, NULL, 2, 'Created', 'IT quarterly review created by IT Secretary', '2026-07-20 09:30:00'),
(96, 75, 12, NULL, 4, 'Created', 'Cybersecurity incident form submitted', '2026-07-21 10:30:00'),
(97, 76, 18, NULL, 4, 'Created', 'Software license audit submitted', '2026-07-22 11:30:00'),
(98, 76, 5, 4, NULL, 'Signed', 'License audit signed by IT Secretary', '2026-07-22 13:30:00'),
(99, 77, 9, NULL, 4, 'Created', 'HR compliance cert created by HR Secretary', '2026-07-20 10:00:00'),
(100, 78, 13, NULL, 5, 'Created', 'Employee grievance form submitted', '2026-07-21 11:00:00'),
(101, 79, 13, NULL, 5, 'Created', 'Workplace safety report submitted', '2026-07-22 12:00:00'),
(102, 79, 9, 5, NULL, 'Signed', 'Safety report signed by HR Secretary', '2026-07-22 14:00:00'),
(103, 80, 19, NULL, 4, 'Created', 'Enrollment projections created by Admissions Secretary', '2026-07-20 10:30:00'),
(104, 81, 16, NULL, 6, 'Created', 'Transfer credential assessment submitted', '2026-07-21 11:30:00'),
(105, 82, 16, NULL, 6, 'Created', 'Freshmen orientation approval submitted', '2026-07-22 12:30:00'),
(106, 82, 19, 6, NULL, 'Signed', 'Orientation program signed by Admissions Secretary', '2026-07-22 14:30:00'),
(107, 83, 20, NULL, 4, 'Created', 'Grant utilization summary created by R&D Secretary', '2026-07-20 11:00:00'),
(108, 84, 17, NULL, 7, 'Created', 'Research paper clearance submitted', '2026-07-21 12:00:00'),
(109, 85, 17, NULL, 7, 'Created', 'Lab equipment procurement endorsement submitted', '2026-07-22 13:00:00'),
(110, 85, 20, 7, NULL, 'Signed', 'Lab procurement signed by R&D Secretary', '2026-07-22 15:00:00');


-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

CREATE TABLE `document_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_types`
--

INSERT INTO `document_types` (`type_id`, `type_name`, `description`, `is_active`) VALUES
(1, 'Proposal', 'Project and grant proposals requiring multi-level review', 1),
(2, 'Approval Form', 'Formal document that requires sign-off', 1),
(3, 'Memorandum', 'Internal office policy or communication notice', 1),
(4, 'Report', 'Official departmental status or financial report', 1),
(5, 'Requisition Order', 'Procurement and equipment purchase request', 1),
(6, 'Policy Directive', 'Institutional guideline or policy directive', 1);

-- --------------------------------------------------------

--
-- Table structure for table `document_type_offices`
--

CREATE TABLE `document_type_offices` (
  `type_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_type_offices`
--

INSERT INTO `document_type_offices` (`type_id`, `office_id`) VALUES
(1, 1),
(1, 3),
(1, 7),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(4, 2),
(4, 3),
(4, 4),
(4, 7),
(5, 2),
(5, 4),
(5, 5),
(6, 1),
(6, 3),
(6, 5);

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `office_id` int(11) NOT NULL,
  `office_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`office_id`, `office_name`) VALUES
(6, 'Admissions Office'),
(3, 'Dean Office'),
(2, 'Finance Office'),
(5, 'Human Resources Office'),
(4, 'IT Office'),
(1, 'Registrar Office'),
(7, 'Research & Development Office');

-- --------------------------------------------------------

--
-- Table structure for table `office_secretaries`
--

CREATE TABLE `office_secretaries` (
  `office_id` int(11) NOT NULL,
  `secretary_user_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office_secretaries`
--

INSERT INTO `office_secretaries` (`office_id`, `secretary_user_id`, `assigned_at`) VALUES
(1, 2, '2026-05-01 00:15:00'),
(2, 3, '2026-05-01 00:30:00'),
(3, 4, '2026-05-01 00:45:00'),
(4, 5, '2026-05-01 01:00:00'),
(5, 9, '2026-05-03 02:00:00'),
(6, 19, '2026-06-06 01:00:00'),
(7, 20, '2026-06-06 02:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(3, 'Member'),
(2, 'Secretary');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('require_admin_approval', '1', '2026-08-05 08:57:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `office_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `registration_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role_id`, `office_id`, `full_name`, `email`, `password_hash`, `is_active`, `registration_status`, `created_at`) VALUES
(1, 1, NULL, 'System Admin', 'admin@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-01 00:00:00'),
(2, 2, 1, 'Registrar Secretary', 'registrar.secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-01 00:15:00'),
(3, 2, 2, 'Finance Secretary', 'finance.secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-01 00:30:00'),
(4, 2, 3, 'Dean Secretary', 'secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-01 00:45:00'),
(5, 2, 4, 'IT Secretary', 'it.secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-01 01:00:00'),
(6, 3, 1, 'Juan Member', 'juan.member@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-02 02:00:00'),
(7, 3, 2, 'Maria Signatory', 'member@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-02 03:30:00'),
(8, 1, NULL, 'System Administrator', 'admin@office.gov', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-03 01:00:00'),
(9, 2, 5, 'HR Secretary', 'hr.secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-03 02:00:00'),
(10, 3, 3, 'Sample Member', 'member2@office.gov', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-04 06:00:00'),
(11, 3, 1, 'Alex Santos', 'alex.santos@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-10 01:12:00'),
(12, 3, 4, 'Keith Rodriguez', 'keith@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-12 03:45:00'),
(13, 3, 5, 'Elena Gomez', 'elena.gomez@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-15 06:20:00'),
(14, 3, 2, 'David Chen', 'david.chen@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-18 08:05:00'),
(15, 3, 3, 'Patricia Cruz', 'patricia.cruz@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-20 00:50:00'),
(16, 3, 6, 'Robert Taylor', 'robert.taylor@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-05-25 02:30:00'),
(17, 3, 7, 'Sophia Martinez', 'sophia.martinez@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-06-01 03:15:00'),
(18, 3, 4, 'Liam Wilson', 'liam.wilson@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-06-05 05:40:00'),
(19, 2, 6, 'Admissions Secretary', 'admissions.secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-06-06 01:00:00'),
(20, 2, 7, 'R&D Secretary', 'rnd.secretary@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 1, 'Approved', '2026-06-06 02:00:00'),
(21, 3, 1, 'Shad Paje', 'shad@paje.me', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 0, 'Pending', '2026-07-20 10:17:16'),
(22, 3, 2, 'Carlos Reyes', 'carlos.reyes@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 0, 'Pending', '2026-07-22 01:30:00'),
(23, 3, 5, 'Angela Diaz', 'angela.diaz@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 0, 'Pending', '2026-07-25 06:15:00'),
(24, 3, 3, 'Marcus Vance', 'marcus.vance@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 0, 'Rejected', '2026-07-28 03:00:00'),
(25, 3, 4, 'Jessica Alba', 'jessica.alba@docuflow.local', '$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82', 0, 'Rejected', '2026-07-29 08:45:00');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_document_summary`
-- (See below for the actual view)
--
CREATE TABLE `view_document_summary` (
`document_id` int(11)
,`tracking_code` varchar(50)
,`title` varchar(255)
,`type_name` varchar(50)
,`status` enum('Created','Pending','Received','Released','For Signature','Signed','Rejected','Completed','Recalled')
,`current_office` varchar(100)
,`created_by` varchar(100)
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_office_secretaries`
-- (See below for the actual view)
--
CREATE TABLE `view_office_secretaries` (
`office_id` int(11)
,`office_name` varchar(100)
,`secretary_user_id` int(11)
,`secretary_name` varchar(100)
,`secretary_email` varchar(100)
);

-- --------------------------------------------------------

--
-- Structure for view `view_document_summary`
--
DROP TABLE IF EXISTS `view_document_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_document_summary`  AS SELECT `d`.`document_id` AS `document_id`, `d`.`tracking_code` AS `tracking_code`, `d`.`title` AS `title`, `dt`.`type_name` AS `type_name`, `d`.`status` AS `status`, `o`.`office_name` AS `current_office`, `u`.`full_name` AS `created_by`, `d`.`created_at` AS `created_at`, `d`.`updated_at` AS `updated_at` FROM (((`documents` `d` join `document_types` `dt` on(`d`.`type_id` = `dt`.`type_id`)) join `users` `u` on(`d`.`creator_id` = `u`.`user_id`)) left join `offices` `o` on(`d`.`current_office_id` = `o`.`office_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_office_secretaries`
--
DROP TABLE IF EXISTS `view_office_secretaries`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_office_secretaries`  AS SELECT `os`.`office_id` AS `office_id`, `o`.`office_name` AS `office_name`, `u`.`user_id` AS `secretary_user_id`, `u`.`full_name` AS `secretary_name`, `u`.`email` AS `secretary_email` FROM ((`office_secretaries` `os` join `users` `u` on(`os`.`secretary_user_id` = `u`.`user_id`)) join `offices` `o` on(`os`.`office_id` = `o`.`office_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`),
  ADD UNIQUE KEY `tracking_code` (`tracking_code`),
  ADD KEY `fk_documents_type` (`type_id`),
  ADD KEY `fk_documents_creator` (`creator_id`),
  ADD KEY `fk_documents_current_office` (`current_office_id`);

--
-- Indexes for table `document_assignments`
--
ALTER TABLE `document_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `assigned_to_user_id` (`assigned_to_user_id`),
  ADD KEY `assigned_by_user_id` (`assigned_by_user_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `document_routes`
--
ALTER TABLE `document_routes`
  ADD PRIMARY KEY (`route_id`),
  ADD UNIQUE KEY `uq_document_office` (`document_id`,`office_id`),
  ADD KEY `fk_document_routes_office` (`office_id`),
  ADD KEY `fk_document_routes_signatory` (`signatory_user_id`);

--
-- Indexes for table `document_trails`
--
ALTER TABLE `document_trails`
  ADD PRIMARY KEY (`trail_id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `action_by_user_id` (`action_by_user_id`),
  ADD KEY `from_office_id` (`from_office_id`),
  ADD KEY `to_office_id` (`to_office_id`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `document_type_offices`
--
ALTER TABLE `document_type_offices`
  ADD PRIMARY KEY (`type_id`,`office_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`office_id`),
  ADD UNIQUE KEY `office_name` (`office_name`);

--
-- Indexes for table `office_secretaries`
--
ALTER TABLE `office_secretaries`
  ADD PRIMARY KEY (`office_id`),
  ADD UNIQUE KEY `secretary_user_id` (`secretary_user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_role` (`role_id`),
  ADD KEY `fk_users_office` (`office_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `document_assignments`
--
ALTER TABLE `document_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `document_routes`
--
ALTER TABLE `document_routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `document_trails`
--
ALTER TABLE `document_trails`
  MODIFY `trail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;


--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `office_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_documents_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_documents_current_office` FOREIGN KEY (`current_office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_documents_type` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`) ON UPDATE CASCADE;

--
-- Constraints for table `document_assignments`
--
ALTER TABLE `document_assignments`
  ADD CONSTRAINT `document_assignments_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`),
  ADD CONSTRAINT `document_assignments_ibfk_2` FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `document_assignments_ibfk_3` FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `document_assignments_ibfk_4` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`);

--
-- Constraints for table `document_routes`
--
ALTER TABLE `document_routes`
  ADD CONSTRAINT `fk_document_routes_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_document_routes_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_document_routes_signatory` FOREIGN KEY (`signatory_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `document_trails`
--
ALTER TABLE `document_trails`
  ADD CONSTRAINT `document_trails_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`),
  ADD CONSTRAINT `document_trails_ibfk_2` FOREIGN KEY (`action_by_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `document_trails_ibfk_3` FOREIGN KEY (`from_office_id`) REFERENCES `offices` (`office_id`),
  ADD CONSTRAINT `document_trails_ibfk_4` FOREIGN KEY (`to_office_id`) REFERENCES `offices` (`office_id`);

--
-- Constraints for table `document_type_offices`
--
ALTER TABLE `document_type_offices`
  ADD CONSTRAINT `document_type_offices_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`),
  ADD CONSTRAINT `document_type_offices_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`);

--
-- Constraints for table `office_secretaries`
--
ALTER TABLE `office_secretaries`
  ADD CONSTRAINT `fk_office_secretaries_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_office_secretaries_user` FOREIGN KEY (`secretary_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
