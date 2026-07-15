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
/*!50001 VIEW `view_document_summary` AS select `d`.`document_id` AS `document_id`,`d`.`tracking_code` AS `tracking_code`,`d`.`title` AS `title`,`dt`.`type_name` AS `type_name`,`d`.`status` AS `status`,`o`.`office_name` AS `current_office`,`u`.`full_name` AS `created_by`,`d`.`created_at` AS `created_at`,`d`.`updated_at` AS `updated_at` from (((`documents` `d` join `document_types` `dt` on(`d`.`type_id` = `dt`.`type_id`)) join `users` `u` on(`d`.`creator_id` = `u`.`user_id`)) left join `offices` `o` on(`d`.`current_office_id` = `o`.`office_id`)) */;
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
/*!50001 VIEW `view_office_secretaries` AS select `o`.`office_id` AS `office_id`,`o`.`office_name` AS `office_name`,`u`.`user_id` AS `secretary_user_id`,`u`.`full_name` AS `secretary_name`,`u`.`email` AS `secretary_email` from ((`office_secretaries` `os` join `offices` `o` on(`os`.`office_id` = `o`.`office_id`)) join `users` `u` on(`os`.`secretary_user_id` = `u`.`user_id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-14 21:01:47
