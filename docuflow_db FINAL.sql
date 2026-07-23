-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 15, 2026 at 05:29 PM
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
(1, 'DOC-2026-SAMPLE1', 'Student Enrollment Approval', '/docuflow/uploads/26a121869f19739014e09a20135c859d.pdf', 1, 1, 5, NULL, 'Completed', '2026-07-01 00:00:00', '2026-07-01 02:30:00'),
(2, 'DOC-2026-SAMPLE2', 'Monthly Financial Report', '/docuflow/uploads/a6a5b9e5d4cba11a8e9ab5cb13d22873.pdf', 3, 1, 3, NULL, 'Completed', '2026-07-02 01:00:00', '2026-07-04 04:00:00'),
(3, 'DOC-2026-SAMPLE3', 'Facility Access Request', '/docuflow/uploads/98760dd3d02d1c394a0275e5455977f9.pdf', 4, 1, 2, NULL, 'Rejected', '2026-07-07 00:30:00', '2026-07-07 03:15:00'),
(4, 'DOC-2026-SAMPLE4', 'Academic Policy Memorandum', '/docuflow/uploads/50e27de8f7914e5fa6c3be66d2c0f963.pdf', 2, 1, 4, 1, 'Pending', '2026-07-10 05:00:00', '2026-07-10 05:00:00'),
(5, 'DOC-2026-SAMPLE5', 'Budget Approval Request', '/docuflow/uploads/38fc0046936680e371b875c0b8324c86.pdf', 1, 1, 5, 2, 'Pending', '2026-07-15 01:00:00', '2026-07-15 02:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `document_requests`
--

CREATE TABLE `document_requests` (
  `request_id` int(11) NOT NULL,
  `requested_by_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Created') NOT NULL DEFAULT 'Pending',
  `created_document_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_requests`
--

INSERT INTO `document_requests` (`request_id`, `requested_by_id`, `office_id`, `type_id`, `title`, `description`, `status`, `created_document_id`, `created_at`) VALUES
(1, 5, 1, 1, 'Student Enrollment Approval', 'Create an enrollment approval for routing to Finance.', 'Created', 1, '2026-06-30 23:45:00'),
(2, 5, 2, 1, 'Travel Budget Approval', 'Requesting a document for travel budget approval.', 'Pending', NULL, '2026-07-14 07:00:00'),
(3, 5, 3, 5, 'Enrollment Certification', 'Requesting certification of current enrollment.', 'Rejected', NULL, '2026-07-12 02:00:00');

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
(1, 1, 1, 2, 'Individual', 3, 'Signed', 'Enrollment details verified and approved.', '2026-07-01 02:30:00'),
(2, 2, 1, 3, 'Individual', 4, 'Signed', 'Monthly report reviewed and accepted.', '2026-07-04 04:00:00'),
(3, 3, 1, 3, 'Individual', 4, 'Rejected', 'Please attach the required authorization.', '2026-07-07 03:15:00'),
(4, 4, 1, 1, 'Individual', 2, 'Waiting', NULL, NULL),
(5, 5, 1, 2, 'Individual', 3, 'Received', 'Received for budget review.', '2026-07-15 02:00:00');

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
(1, 'Approval Form', 'A form routed to an office for review and approval.', 1),
(2, 'Memorandum', 'An official internal memorandum.', 1),
(3, 'Report', 'A formal operational or financial report.', 1),
(4, 'Request Letter', 'A written request requiring an office response.', 1),
(5, 'Certification', 'A document submitted for certification.', 1);

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
(3, 'Dean Office'),
(2, 'Finance Office'),
(1, 'Registrar Office');

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
(1, 2, '2026-06-01 01:00:00'),
(2, 3, '2026-06-01 01:00:00'),
(3, 4, '2026-06-01 01:00:00');

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
(1, 1, NULL, 'System Administrator', 'admin@docuflow.local', 'd77d720c703ac2c7c2f331f5e7f75c1fb04a316c907fa6b3fb819706d4b53d66', 1, 'Approved', '2026-06-01 00:00:00'),
(2, 2, 1, 'Rosa Registrar', 'registrar@docuflow.local', 'd77d720c703ac2c7c2f331f5e7f75c1fb04a316c907fa6b3fb819706d4b53d66', 1, 'Approved', '2026-06-01 00:05:00'),
(3, 2, 2, 'Felix Finance', 'finance@docuflow.local', 'd77d720c703ac2c7c2f331f5e7f75c1fb04a316c907fa6b3fb819706d4b53d66', 1, 'Approved', '2026-06-01 00:10:00'),
(4, 2, 3, 'Diana Dean', 'dean@docuflow.local', 'd77d720c703ac2c7c2f331f5e7f75c1fb04a316c907fa6b3fb819706d4b53d66', 1, 'Approved', '2026-06-01 00:15:00'),
(5, 3, 1, 'Marco Member', 'member@docuflow.local', 'd77d720c703ac2c7c2f331f5e7f75c1fb04a316c907fa6b3fb819706d4b53d66', 1, 'Approved', '2026-06-01 00:20:00');

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

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_office_secretaries`  AS SELECT `o`.`office_id` AS `office_id`, `o`.`office_name` AS `office_name`, `u`.`user_id` AS `secretary_user_id`, `u`.`full_name` AS `secretary_name`, `u`.`email` AS `secretary_email` FROM ((`office_secretaries` `os` join `offices` `o` on(`os`.`office_id` = `o`.`office_id`)) join `users` `u` on(`os`.`secretary_user_id` = `u`.`user_id`)) ;

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
-- Indexes for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_document_requests_requested_by` (`requested_by_id`),
  ADD KEY `fk_document_requests_office` (`office_id`),
  ADD KEY `fk_document_requests_type` (`type_id`),
  ADD KEY `fk_document_requests_document` (`created_document_id`);

--
-- Indexes for table `document_routes`
--
ALTER TABLE `document_routes`
  ADD PRIMARY KEY (`route_id`),
  ADD UNIQUE KEY `uq_document_office` (`document_id`,`office_id`),
  ADD KEY `fk_document_routes_office` (`office_id`),
  ADD KEY `fk_document_routes_signatory` (`signatory_user_id`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

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
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `document_routes`
--
ALTER TABLE `document_routes`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `office_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Constraints for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD CONSTRAINT `fk_document_requests_document` FOREIGN KEY (`created_document_id`) REFERENCES `documents` (`document_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_document_requests_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_document_requests_requested_by` FOREIGN KEY (`requested_by_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_document_requests_type` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `document_routes`
--
ALTER TABLE `document_routes`
  ADD CONSTRAINT `fk_document_routes_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_document_routes_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_document_routes_signatory` FOREIGN KEY (`signatory_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
