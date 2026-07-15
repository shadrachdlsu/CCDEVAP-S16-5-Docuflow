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
-- Table structure for table `document_routes`
--

DROP TABLE IF EXISTS `document_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_routes` (
  `route_id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `step_no` int(11) NOT NULL DEFAULT 0,
  `office_id` int(11) DEFAULT NULL,
  `signatory_user_id` int(11) DEFAULT NULL,
  `status` enum('Waiting','Received','For Signature','Signed','Rejected','Released','Skipped','Completed') NOT NULL DEFAULT 'Waiting',
  `acted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`route_id`),
  UNIQUE KEY `uq_document_office` (`document_id`,`office_id`),
  KEY `fk_document_routes_office` (`office_id`),
  KEY `fk_document_routes_signatory` (`signatory_user_id`),
  CONSTRAINT `fk_document_routes_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`document_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_document_routes_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_document_routes_signatory` FOREIGN KEY (`signatory_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_routes`
--

LOCK TABLES `document_routes` WRITE;
/*!40000 ALTER TABLE `document_routes` DISABLE KEYS */;
INSERT INTO `document_routes` VALUES (1,1,1,1,NULL,'Received',NULL),(2,1,2,2,7,'Signed','2026-07-13 20:14:57'),(3,1,3,3,NULL,'Waiting',NULL),(4,2,1,3,NULL,'Waiting',NULL),(5,3,1,1,NULL,'Waiting',NULL),(6,3,2,4,NULL,'Waiting',NULL),(7,3,3,3,NULL,'Waiting',NULL),(8,3,4,2,NULL,'Waiting',NULL);
/*!40000 ALTER TABLE `document_routes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-14 21:01:46
