CREATE TABLE `roles` (
  `role_id` int PRIMARY KEY AUTO_INCREMENT,
  `role_name` varchar(50) UNIQUE NOT NULL
);

CREATE TABLE `offices` (
  `office_id` int PRIMARY KEY AUTO_INCREMENT,
  `office_name` varchar(100) UNIQUE NOT NULL,
  `is_active` boolean DEFAULT true
);

CREATE TABLE `users` (
  `user_id` int PRIMARY KEY AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `office_id` int,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) UNIQUE NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` ENUM ('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp
);

CREATE TABLE `document_types` (
  `type_id` int PRIMARY KEY AUTO_INCREMENT,
  `type_name` varchar(100) NOT NULL,
  `created_by` int,
  `owner_office_id` int,
  `is_active` boolean DEFAULT true
);

CREATE TABLE `document_type_offices` (
  `type_id` int,
  `office_id` int,
  PRIMARY KEY (`type_id`, `office_id`)
);

CREATE TABLE `documents` (
  `document_id` int PRIMARY KEY AUTO_INCREMENT,
  `tracking_code` varchar(50) UNIQUE NOT NULL,
  `title` varchar(255) NOT NULL,
  `type_id` int,
  `creator_id` int,
  `current_office_id` int,
  `status` ENUM ('Pending', 'Signed', 'Finished') NOT NULL DEFAULT 'Pending',
  `file_path` varchar(255),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `document_assignments` (
  `assignment_id` int PRIMARY KEY AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `assigned_to_user_id` int NOT NULL,
  `assigned_by_user_id` int,
  `office_id` int,
  `status` ENUM ('Pending', 'Signed', 'Rejected', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `remarks` text,
  `signed_file_path` varchar(255),
  `assigned_at` timestamp,
  `acted_at` timestamp
);

CREATE TABLE `document_trails` (
  `trail_id` int PRIMARY KEY AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `action_by_user_id` int,
  `from_office_id` int,
  `to_office_id` int,
  `action_taken` ENUM ('Created', 'Received', 'Assigned', 'Signed', 'Rejected', 'Released', 'Forwarded', 'Finished', 'Cancelled', 'Requested') NOT NULL,
  `remarks` text,
  `created_at` timestamp
);

CREATE TABLE `document_requests` (
  `request_id` int PRIMARY KEY AUTO_INCREMENT,
  `requested_by_user_id` int NOT NULL,
  `assigned_secretary_id` int,
  `type_id` int,
  `document_id` int,
  `title` varchar(255) NOT NULL,
  `description` text,
  `status` ENUM ('Pending', 'Approved', 'Rejected', 'Created') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp,
  `processed_at` timestamp
);

ALTER TABLE `roles` COMMENT = 'Examples: Admin, Secretary, Member';

ALTER TABLE `users` COMMENT = 'Users may be Admin, Secretary, or Member. Secretaries and members belong to an office.';

ALTER TABLE `document_types` COMMENT = 'If owner_office_id is NULL, the type is system-wide. If not NULL, it is office-specific.';

ALTER TABLE `document_type_offices` COMMENT = 'Allows one document type to be available to one or more offices.';

ALTER TABLE `documents` COMMENT = 'Main record of each document being tracked in the system.';

ALTER TABLE `document_assignments` COMMENT = 'Stores who needs to sign, approve, reject, or act on the document.';

ALTER TABLE `document_trails` COMMENT = 'Paper trail/history of every document action.';

ALTER TABLE `document_requests` COMMENT = 'Used when a member requests the secretary to create a new document.';

ALTER TABLE `users` ADD FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

ALTER TABLE `users` ADD FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`);

ALTER TABLE `document_types` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

ALTER TABLE `document_types` ADD FOREIGN KEY (`owner_office_id`) REFERENCES `offices` (`office_id`);

ALTER TABLE `document_type_offices` ADD FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`);

ALTER TABLE `document_type_offices` ADD FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`);

ALTER TABLE `documents` ADD FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`);

ALTER TABLE `documents` ADD FOREIGN KEY (`creator_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `documents` ADD FOREIGN KEY (`current_office_id`) REFERENCES `offices` (`office_id`);

ALTER TABLE `document_assignments` ADD FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`);

ALTER TABLE `document_assignments` ADD FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `document_assignments` ADD FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `document_assignments` ADD FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`);

ALTER TABLE `document_trails` ADD FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`);

ALTER TABLE `document_trails` ADD FOREIGN KEY (`action_by_user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `document_trails` ADD FOREIGN KEY (`from_office_id`) REFERENCES `offices` (`office_id`);

ALTER TABLE `document_trails` ADD FOREIGN KEY (`to_office_id`) REFERENCES `offices` (`office_id`);

ALTER TABLE `document_requests` ADD FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `document_requests` ADD FOREIGN KEY (`assigned_secretary_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `document_requests` ADD FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`);

ALTER TABLE `document_requests` ADD FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`);
