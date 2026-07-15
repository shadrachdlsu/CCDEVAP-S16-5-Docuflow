-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: docuflow_db
-- ------------------------------------------------------
-- Server version	9.7.0

CREATE DATABASE IF NOT EXISTS `docuflow_db`;
USE `docuflow_db`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

-- SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'e14aae42-62f2-11f1-8443-10ffe080e4e0:1-165';

--
-- Table structure for table `document_assignments`
--

DROP TABLE IF EXISTS `document_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_assignments` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `assigned_to_user_id` int NOT NULL,
  `assigned_by_user_id` int DEFAULT NULL,
  `office_id` int DEFAULT NULL,
  `status` enum('Pending','Signed','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
  `remarks` text,
  `signed_file_path` varchar(255) DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `acted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`assignment_id`),
  KEY `document_id` (`document_id`),
  KEY `assigned_to_user_id` (`assigned_to_user_id`),
  KEY `assigned_by_user_id` (`assigned_by_user_id`),
  KEY `office_id` (`office_id`),
  CONSTRAINT `document_assignments_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`),
  CONSTRAINT `document_assignments_ibfk_2` FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `document_assignments_ibfk_3` FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `document_assignments_ibfk_4` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores who needs to sign, approve, reject, or act on the document.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_assignments`
--

LOCK TABLES `document_assignments` WRITE;
/*!40000 ALTER TABLE `document_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_requests`
--

DROP TABLE IF EXISTS `document_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `requested_by_id` int NOT NULL,
  `office_id` int NOT NULL,
  `type_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `status` enum('Pending','Approved','Rejected','Created') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `created_document_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_requests`
--

LOCK TABLES `document_requests` WRITE;
/*!40000 ALTER TABLE `document_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_routes`
--

DROP TABLE IF EXISTS `document_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_routes` (
  `route_id` int NOT NULL AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `step_no` int NOT NULL DEFAULT '0',
  `office_id` int DEFAULT NULL,
  `recipient_scope` enum('Individual','Office') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Individual',
  `signatory_user_id` int DEFAULT NULL,
  `status` enum('Waiting','Received','For Signature','Signed','Rejected','Released','Skipped','Completed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Waiting',
  `remarks` text COLLATE utf8mb4_general_ci,
  `acted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`route_id`),
  UNIQUE KEY `uq_document_office` (`document_id`,`office_id`),
  KEY `fk_document_routes_office` (`office_id`),
  KEY `fk_document_routes_signatory` (`signatory_user_id`),
  CONSTRAINT `fk_document_routes_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_document_routes_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_document_routes_signatory` FOREIGN KEY (`signatory_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_routes`
--

LOCK TABLES `document_routes` WRITE;
/*!40000 ALTER TABLE `document_routes` DISABLE KEYS */;
INSERT INTO `document_routes` VALUES (1,1,1,1,'Individual',NULL,'Received',NULL,NULL),(2,1,2,2,'Individual',7,'Signed',NULL,'2026-07-13 20:14:57'),(3,1,3,3,'Individual',NULL,'Waiting',NULL,NULL),(4,2,1,3,'Individual',10,'Waiting',NULL,NULL),(5,3,1,1,'Individual',NULL,'Waiting',NULL,NULL),(6,3,2,4,'Individual',NULL,'Waiting',NULL,NULL),(7,3,3,3,'Individual',10,'Waiting',NULL,NULL),(8,3,4,2,'Individual',NULL,'Waiting',NULL,NULL),(9,4,1,3,'Individual',10,'Signed',NULL,'2026-07-14 15:39:48'),(13,8,1,3,'Individual',10,'Rejected',NULL,'2026-07-14 17:52:48'),(14,9,1,3,'Individual',10,'Signed','THIS SUCKS','2026-07-14 17:58:08'),(15,10,1,3,'Individual',10,'Rejected','231321','2026-07-14 17:59:14'),(16,11,1,3,'Individual',10,'Waiting',NULL,NULL);
/*!40000 ALTER TABLE `document_routes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_trails`
--

DROP TABLE IF EXISTS `document_trails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_trails` (
  `trail_id` int NOT NULL AUTO_INCREMENT,
  `document_id` int NOT NULL,
  `action_by_user_id` int DEFAULT NULL,
  `from_office_id` int DEFAULT NULL,
  `to_office_id` int DEFAULT NULL,
  `action_taken` enum('Created','Received','Assigned','Signed','Rejected','Released','Forwarded','Finished','Cancelled','Requested') NOT NULL,
  `remarks` text,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`trail_id`),
  KEY `document_id` (`document_id`),
  KEY `action_by_user_id` (`action_by_user_id`),
  KEY `from_office_id` (`from_office_id`),
  KEY `to_office_id` (`to_office_id`),
  CONSTRAINT `document_trails_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`),
  CONSTRAINT `document_trails_ibfk_2` FOREIGN KEY (`action_by_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `document_trails_ibfk_3` FOREIGN KEY (`from_office_id`) REFERENCES `offices` (`office_id`),
  CONSTRAINT `document_trails_ibfk_4` FOREIGN KEY (`to_office_id`) REFERENCES `offices` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Paper trail/history of every document action.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_trails`
--

LOCK TABLES `document_trails` WRITE;
/*!40000 ALTER TABLE `document_trails` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_trails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_type_offices`
--

DROP TABLE IF EXISTS `document_type_offices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_type_offices` (
  `type_id` int NOT NULL,
  `office_id` int NOT NULL,
  PRIMARY KEY (`type_id`,`office_id`),
  KEY `office_id` (`office_id`),
  CONSTRAINT `document_type_offices_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`),
  CONSTRAINT `document_type_offices_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Allows one document type to be available to one or more offices.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_type_offices`
--

LOCK TABLES `document_type_offices` WRITE;
/*!40000 ALTER TABLE `document_type_offices` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_type_offices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_types`
--

DROP TABLE IF EXISTS `document_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_types` (
  `type_id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `type_name` (`type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_types`
--

LOCK TABLES `document_types` WRITE;
/*!40000 ALTER TABLE `document_types` DISABLE KEYS */;
INSERT INTO `document_types` VALUES (2,'Approval Form','Document that requires approval',1),(3,'Memorandum','Office memorandum',1),(4,'Report','Official report',1);
/*!40000 ALTER TABLE `document_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `document_id` int NOT NULL AUTO_INCREMENT,
  `tracking_code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type_id` int NOT NULL,
  `requires_signature` tinyint(1) NOT NULL DEFAULT '1',
  `creator_id` int NOT NULL,
  `current_office_id` int DEFAULT NULL,
  `status` enum('Created','Pending','Received','Released','For Signature','Signed','Rejected','Completed','Recalled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Created',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`document_id`),
  UNIQUE KEY `tracking_code` (`tracking_code`),
  KEY `fk_documents_type` (`type_id`),
  KEY `fk_documents_creator` (`creator_id`),
  KEY `fk_documents_current_office` (`current_office_id`),
  CONSTRAINT `fk_documents_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_documents_current_office` FOREIGN KEY (`current_office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_documents_type` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES (1,'DOC-2026-0001','Sample Approval Form','/uploads/sample_approval_form.pdf',2,1,2,NULL,'Pending','2026-07-13 03:31:42','2026-07-13 20:14:57'),(2,'DOC-2026-17852DE3','Report to the dean','/docuflow/uploads/a6a5b9e5d4cba11a8e9ab5cb13d22873.pdf',4,1,7,3,'Pending','2026-07-13 19:15:32','2026-07-13 19:15:32'),(3,'DOC-2026-94E55E02','s','/docuflow/uploads/98760dd3d02d1c394a0275e5455977f9.pdf',3,1,7,1,'Pending','2026-07-13 19:28:58','2026-07-13 19:28:58'),(4,'DOC-2026-77F7DA05','testing2','/docuflow/uploads/26a121869f19739014e09a20135c859d.pdf',2,1,4,NULL,'Completed','2026-07-14 15:39:06','2026-07-14 15:39:48'),(8,'DOC-2026-8D354FD2','HAI','/docuflow/uploads/601b37cc916aa3fbb8b0119f973dd9d0.pdf',2,1,4,NULL,'Rejected','2026-07-14 17:51:27','2026-07-14 17:52:48'),(9,'DOC-2026-44C6E8DF','BLAH BLAH','/docuflow/uploads/a1f6e390bbad9efe1b14d4c115ddf3cb.pdf',3,1,4,NULL,'Completed','2026-07-14 17:57:07','2026-07-14 17:58:08'),(10,'DOC-2026-C6A29540','142','/docuflow/uploads/1382ae119f166db5e961e46461d5d25e.pdf',3,1,4,NULL,'Rejected','2026-07-14 17:58:39','2026-07-14 17:59:14'),(11,'DOC-2026-897111A2','5231','/docuflow/uploads/50e27de8f7914e5fa6c3be66d2c0f963.pdf',2,1,10,NULL,'Pending','2026-07-14 18:12:07','2026-07-14 18:12:07');
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `office_secretaries`
--

DROP TABLE IF EXISTS `office_secretaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `office_secretaries` (
  `office_id` int NOT NULL,
  `secretary_user_id` int NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`office_id`),
  UNIQUE KEY `secretary_user_id` (`secretary_user_id`),
  CONSTRAINT `fk_office_secretaries_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_office_secretaries_user` FOREIGN KEY (`secretary_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `office_secretaries`
--

LOCK TABLES `office_secretaries` WRITE;
/*!40000 ALTER TABLE `office_secretaries` DISABLE KEYS */;
INSERT INTO `office_secretaries` VALUES (1,2,'2026-07-13 03:31:42'),(2,3,'2026-07-13 03:31:42'),(3,4,'2026-07-14 11:00:47'),(4,5,'2026-07-13 03:31:42');
/*!40000 ALTER TABLE `office_secretaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offices`
--

DROP TABLE IF EXISTS `offices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offices` (
  `office_id` int NOT NULL AUTO_INCREMENT,
  `office_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`office_id`),
  UNIQUE KEY `office_name` (`office_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offices`
--

LOCK TABLES `offices` WRITE;
/*!40000 ALTER TABLE `offices` DISABLE KEYS */;
INSERT INTO `offices` VALUES (3,'Dean Office'),(2,'Finance Office'),(4,'IT Office'),(1,'Registrar Office');
/*!40000 ALTER TABLE `offices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin'),(3,'Member'),(2,'Secretary');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `office_id` int DEFAULT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `registration_status` enum('Pending','Approved','Rejected') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Approved',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_role` (`role_id`),
  KEY `fk_users_office` (`office_id`),
  CONSTRAINT `fk_users_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,NULL,'System Admin','admin@docuflow.local','$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82',1,'Approved','2026-07-13 03:31:42'),(2,2,1,'Registrar Secretary','registrar.secretary@docuflow.local','$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82',1,'Approved','2026-07-13 03:31:42'),(3,2,2,'Finance Secretary','finance.secretary@docuflow.local','$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82',1,'Approved','2026-07-13 03:31:42'),(4,2,3,'Dean Secretary','secretary@docuflow.local','$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82',1,'Approved','2026-07-13 03:31:42'),(5,2,4,'IT Secretary','it.secretary@docuflow.local','$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82',1,'Approved','2026-07-13 03:31:42'),(6,3,1,'Juan Member','juan.member@docuflow.local','$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82',1,'Approved','2026-07-13 03:31:42'),(7,3,2,'Maria Signatory','member@docuflow.local','$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82',1,'Approved','2026-07-13 03:31:42'),(8,1,NULL,'System Administrator','admin@office.gov','$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82',1,'Approved','2026-07-13 04:24:46'),(9,2,1,'Sample Secretary','secretary@office.gov','$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82',1,'Approved','2026-07-13 04:24:46'),(10,3,3,'Sample Member','member2@office.gov','$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82',1,'Approved','2026-07-13 04:24:46'),(11,3,1,'Shad','shad@paje.me','$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82',0,'Pending','2026-07-14 18:17:16'),(12,3,4,'keith','tesdting@docuflow.local','$2a$12$yx.Mr1lOf1u4E6Fw9ONgFOy4drHhjELYaJ/O0/RqT1BK0U6X/PK82',1,'Approved','2026-07-14 18:27:13');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `view_document_summary`
--

DROP TABLE IF EXISTS `view_document_summary`;
/*!50001 DROP VIEW IF EXISTS `view_document_summary`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_document_summary` AS SELECT 
 1 AS `document_id`,
 1 AS `tracking_code`,
 1 AS `title`,
 1 AS `type_name`,
 1 AS `status`,
 1 AS `current_office`,
 1 AS `created_by`,
 1 AS `created_at`,
 1 AS `updated_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_office_secretaries`
--

DROP TABLE IF EXISTS `view_office_secretaries`;
/*!50001 DROP VIEW IF EXISTS `view_office_secretaries`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_office_secretaries` AS SELECT 
 1 AS `office_id`,
 1 AS `office_name`,
 1 AS `secretary_user_id`,
 1 AS `secretary_name`,
 1 AS `secretary_email`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `view_document_summary`
--

/*!50001 DROP VIEW IF EXISTS `view_document_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_document_summary` AS select `d`.`document_id` AS `document_id`,`d`.`tracking_code` AS `tracking_code`,`d`.`title` AS `title`,`dt`.`type_name` AS `type_name`,`d`.`status` AS `status`,`o`.`office_name` AS `current_office`,`u`.`full_name` AS `created_by`,`d`.`created_at` AS `created_at`,`d`.`updated_at` AS `updated_at` from (((`documents` `d` join `document_types` `dt` on((`d`.`type_id` = `dt`.`type_id`))) join `users` `u` on((`d`.`creator_id` = `u`.`user_id`))) left join `offices` `o` on((`d`.`current_office_id` = `o`.`office_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_office_secretaries`
--

/*!50001 DROP VIEW IF EXISTS `view_office_secretaries`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_office_secretaries` AS select `o`.`office_id` AS `office_id`,`o`.`office_name` AS `office_name`,`u`.`user_id` AS `secretary_user_id`,`u`.`full_name` AS `secretary_name`,`u`.`email` AS `secretary_email` from ((`office_secretaries` `os` join `offices` `o` on((`os`.`office_id` = `o`.`office_id`))) join `users` `u` on((`os`.`secretary_user_id` = `u`.`user_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-15 21:41:01
