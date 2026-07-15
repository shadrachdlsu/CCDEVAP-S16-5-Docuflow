-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: localhost    Database: docuflow_db
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `tracking_code` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `current_office_id` int(11) DEFAULT NULL,
  `status` enum('Created','Pending','Received','Released','For Signature','Signed','Rejected','Completed','Recalled') NOT NULL DEFAULT 'Created',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`document_id`),
  UNIQUE KEY `tracking_code` (`tracking_code`),
  KEY `fk_documents_type` (`type_id`),
  KEY `fk_documents_creator` (`creator_id`),
  KEY `fk_documents_current_office` (`current_office_id`),
  CONSTRAINT `fk_documents_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_documents_current_office` FOREIGN KEY (`current_office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_documents_type` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`type_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES (1,'DOC-2026-0001','Sample Approval Form','/uploads/sample_approval_form.pdf',2,2,NULL,'Pending','2026-07-13 03:31:42','2026-07-13 20:14:57'),(2,'DOC-2026-17852DE3','Report to the dean','/docuflow/uploads/a6a5b9e5d4cba11a8e9ab5cb13d22873.pdf',4,7,3,'Pending','2026-07-13 19:15:32','2026-07-13 19:15:32'),(3,'DOC-2026-94E55E02','s','/docuflow/uploads/98760dd3d02d1c394a0275e5455977f9.pdf',3,7,1,'Pending','2026-07-13 19:28:58','2026-07-13 19:28:58');
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-14 21:01:47
