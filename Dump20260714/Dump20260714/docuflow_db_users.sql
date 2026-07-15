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
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `office_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `registration_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_role` (`role_id`),
  KEY `fk_users_office` (`office_id`),
  CONSTRAINT `fk_users_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`office_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,NULL,'System Admin','admin@docuflow.local','d77d720c703ac2c7c2f331f5e7f75c1fb04a316c907fa6b3fb819706d4b53d66',1,'Approved','2026-07-13 03:31:42'),(2,2,1,'Registrar Secretary','registrar.secretary@docuflow.local','d77d720c703ac2c7c2f331f5e7f75c1fb04a316c907fa6b3fb819706d4b53d66',1,'Approved','2026-07-13 03:31:42'),(3,2,2,'Finance Secretary','finance.secretary@docuflow.local','d77d720c703ac2c7c2f331f5e7f75c1fb04a316c907fa6b3fb819706d4b53d66',1,'Approved','2026-07-13 03:31:42'),(4,2,3,'Dean Secretary','dean.secretary@docuflow.local','CHANGE_ME',1,'Approved','2026-07-13 03:31:42'),(5,2,4,'IT Secretary','it.secretary@docuflow.local','CHANGE_ME',1,'Approved','2026-07-13 03:31:42'),(6,3,1,'Juan Member','juan.member@docuflow.local','CHANGE_ME',1,'Approved','2026-07-13 03:31:42'),(7,3,2,'Maria Signatory','member@docuflow.local','d77d720c703ac2c7c2f331f5e7f75c1fb04a316c907fa6b3fb819706d4b53d66',1,'Approved','2026-07-13 03:31:42'),(8,1,NULL,'System Administrator','admin@office.gov','$2y$10$BPeFCzftM.cjT1Nfu.Yb7.dfTSWYvH3qCjkQd7FsrzEHmm.XbItJ2',1,'Approved','2026-07-13 04:24:46'),(9,2,1,'Sample Secretary','secretary@office.gov','$2y$10$0YLgfzaGR9GDNPbI.gRg2uKPgpmbh1/lbQ9lpjBUAgGJrcxZXHI0C',1,'Approved','2026-07-13 04:24:46'),(10,3,1,'Sample Member','member@office.gov','$2y$10$HP4Fp7vl14Bad/UhYX4eJOU6b0PbPM.SrWHa.fgi2LyGQXEBjXJla',1,'Approved','2026-07-13 04:24:46');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
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
