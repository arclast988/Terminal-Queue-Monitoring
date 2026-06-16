/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: jeepneynvans
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `terminal_id` int(10) unsigned NOT NULL DEFAULT 1,
  `message` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_is_active_index` (`is_active`),
  KEY `fk_announcements_terminal` (`terminal_id`),
  CONSTRAINT `fk_announcements_terminal` FOREIGN KEY (`terminal_id`) REFERENCES `terminals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES
(1,1,'Login','Admin logged in (admin123)','2026-06-02 15:25:58'),
(2,1,'Logout','Admin admin123 logged out','2026-06-02 15:26:53'),
(3,1,'Login','Admin logged in (admin123)','2026-06-02 15:27:00'),
(4,1,'Logout','Admin admin123 logged out','2026-06-02 15:27:10'),
(5,1,'Login','Admin logged in (admin123)','2026-06-02 15:29:39'),
(6,1,'Create route','PALOMPON → ORMOC (van, ₱150).','2026-06-02 15:29:49'),
(7,1,'Create route','PALOMPON → TACLOBAN (jeepney, ₱150).','2026-06-02 15:31:17'),
(8,1,'Create user with routes','Created dispatcher \"giogmarquez\" with routes: PALOMPON → ORMOC','2026-06-02 15:31:42'),
(9,1,'Assign vehicle to route','Registered vehicle 726 HOF (van) - Driver: GIO MARQUEZ - Route: PALOMPON → ORMOC','2026-06-02 15:32:15'),
(10,1,'Logout','Admin admin123 logged out','2026-06-02 15:32:28'),
(11,2,'Login','Staff logged in (giogmarquez)','2026-06-02 15:32:50'),
(12,2,'Add to queue','Added 726 HOF to queue for ORMOC. Rule: Afternoon Rush (30 min, starts at boarding).','2026-06-02 15:32:53'),
(13,2,'Start Boarding','Start Boarding for 726 HOF (ORMOC).','2026-06-02 15:32:56'),
(14,2,'Logout','Staff giogmarquez logged out','2026-06-02 15:33:01'),
(15,2,'Login','Staff logged in (giogmarquez)','2026-06-02 16:16:29'),
(16,2,'Logout','Staff giogmarquez logged out','2026-06-02 16:16:36'),
(17,1,'Login','Admin logged in (admin123)','2026-06-02 16:16:40'),
(18,1,'Logout','Admin admin123 logged out','2026-06-02 16:24:07'),
(19,1,'Login','Admin logged in (admin123)','2026-06-02 16:24:25'),
(20,1,'Logout','Admin admin123 logged out','2026-06-02 16:24:43'),
(21,1,'Login','Admin logged in (admin123)','2026-06-02 17:06:16'),
(22,1,'Logout','Admin admin123 logged out','2026-06-02 17:07:35'),
(23,1,'Login','Admin logged in (admin123)','2026-06-02 17:25:50'),
(24,1,'Logout','Admin admin123 logged out','2026-06-02 17:25:56'),
(25,1,'Login','Admin logged in (admin123)','2026-06-02 18:00:55'),
(26,1,'Logout','Admin admin123 logged out','2026-06-02 18:02:08'),
(27,2,'Login','Staff logged in (giogmarquez)','2026-06-03 17:50:46'),
(28,2,'Depart Vehicle','Depart Vehicle for 726 HOF (ORMOC).','2026-06-03 17:50:49'),
(29,2,'Logout','Staff giogmarquez logged out','2026-06-03 17:50:50'),
(30,2,'Login','Staff logged in (giogmarquez)','2026-06-03 18:08:04'),
(31,2,'Add to queue','Added 726 HOF to queue for ORMOC. Rule: Evening (40 min, starts at boarding).','2026-06-03 18:08:08'),
(32,2,'Logout','Staff giogmarquez logged out','2026-06-03 18:08:11'),
(33,1,'Login','Admin logged in (admin123)','2026-06-09 08:18:52'),
(34,1,'Logout','Admin admin123 logged out','2026-06-09 08:19:35'),
(35,2,'Login','Staff logged in (giogmarquez)','2026-06-09 08:19:40'),
(36,2,'Logout','Staff giogmarquez logged out','2026-06-09 08:19:52'),
(37,2,'Login','Staff logged in (giogmarquez)','2026-06-09 08:43:51'),
(38,2,'Logout','Staff giogmarquez logged out','2026-06-09 08:43:54'),
(39,1,'Login','Admin logged in (admin123)','2026-06-09 21:14:34'),
(40,1,'Logout','Admin admin123 logged out','2026-06-09 21:14:39'),
(41,1,'Login','Admin logged in (admin123)','2026-06-09 21:16:00'),
(42,1,'Logout','Admin admin123 logged out','2026-06-09 21:16:15'),
(43,1,'Login','Admin logged in (admin123)','2026-06-09 22:44:21'),
(44,1,'Logout','Admin admin123 logged out','2026-06-09 22:44:46'),
(45,1,'Login','Admin logged in (admin123)','2026-06-09 22:44:51'),
(46,1,'Logout','Admin admin123 logged out','2026-06-09 22:47:06'),
(47,2,'Login','Staff logged in (giogmarquez)','2026-06-09 22:47:36'),
(48,2,'Logout','Staff giogmarquez logged out','2026-06-09 22:47:46'),
(49,1,'Login','Admin logged in (admin123)','2026-06-09 22:47:51'),
(50,1,'Logout','Admin admin123 logged out','2026-06-09 22:53:42'),
(51,1,'Login','Admin logged in (admin123)','2026-06-13 19:46:42'),
(52,1,'Logout','Admin admin123 logged out','2026-06-13 19:47:20'),
(53,1,'Login','Admin logged in (admin123)','2026-06-13 19:48:20'),
(54,1,'Logout','Admin admin123 logged out','2026-06-13 19:48:57'),
(55,2,'Login','Staff logged in (giogmarquez)','2026-06-13 19:49:42'),
(56,2,'Logout','Staff giogmarquez logged out','2026-06-13 19:50:48'),
(57,2,'Login','Staff logged in (giogmarquez)','2026-06-13 19:50:57'),
(58,2,'Start Boarding','Start Boarding for 726 HOF (ORMOC).','2026-06-13 19:51:36'),
(59,2,'Logout','Staff giogmarquez logged out','2026-06-13 19:51:40'),
(60,1,'Login','Admin logged in (admin123)','2026-06-16 10:45:57'),
(61,1,'Logout','Admin admin123 logged out','2026-06-16 11:19:28'),
(62,1,'Login','Admin logged in (admin123)','2026-06-16 11:19:41'),
(63,1,'Login','Admin logged in (admin123)','2026-06-16 11:24:54'),
(64,1,'Logout','Admin admin123 logged out','2026-06-16 11:24:59'),
(65,2,'Login','Staff logged in (giogmarquez)','2026-06-16 11:45:36'),
(66,2,'Logout','Staff giogmarquez logged out','2026-06-16 11:45:46');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departure_rules`
--

DROP TABLE IF EXISTS `departure_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `departure_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `terminal_id` int(10) unsigned NOT NULL DEFAULT 1,
  `route_id` int(10) unsigned DEFAULT NULL,
  `time_from` time NOT NULL,
  `time_to` time NOT NULL,
  `wait_minutes` int(11) NOT NULL DEFAULT 30,
  `label` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_departure_rules_terminal` (`terminal_id`),
  KEY `fk_departure_rules_route` (`route_id`),
  CONSTRAINT `fk_departure_rules_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_departure_rules_terminal` FOREIGN KEY (`terminal_id`) REFERENCES `terminals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departure_rules`
--

LOCK TABLES `departure_rules` WRITE;
/*!40000 ALTER TABLE `departure_rules` DISABLE KEYS */;
INSERT INTO `departure_rules` VALUES
(1,1,NULL,'00:00:00','05:00:00',60,'Late Night / Early Morning','2026-06-02 15:07:50','2026-06-02 15:07:50'),
(2,1,NULL,'05:00:00','09:00:00',30,'Morning Rush','2026-06-02 15:07:50','2026-06-02 15:07:50'),
(3,1,NULL,'09:00:00','12:00:00',40,'Mid-Morning','2026-06-02 15:07:50','2026-06-02 15:07:50'),
(4,1,NULL,'12:00:00','15:00:00',40,'Afternoon','2026-06-02 15:07:50','2026-06-02 15:07:50'),
(5,1,NULL,'15:00:00','18:00:00',30,'Afternoon Rush','2026-06-02 15:07:50','2026-06-02 15:07:50'),
(6,1,NULL,'18:00:00','21:00:00',40,'Evening','2026-06-02 15:07:50','2026-06-02 15:07:50'),
(7,1,NULL,'21:00:00','23:59:00',60,'Late Evening','2026-06-02 15:07:50','2026-06-02 15:07:50');
/*!40000 ALTER TABLE `departure_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fare_discounts`
--

DROP TABLE IF EXISTS `fare_discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fare_discounts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `terminal_id` int(10) unsigned NOT NULL DEFAULT 1,
  `type` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `fare_discounts_terminal_type_unique` (`terminal_id`,`type`),
  CONSTRAINT `fk_fare_discounts_terminal` FOREIGN KEY (`terminal_id`) REFERENCES `terminals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fare_discounts`
--

LOCK TABLES `fare_discounts` WRITE;
/*!40000 ALTER TABLE `fare_discounts` DISABLE KEYS */;
INSERT INTO `fare_discounts` VALUES
(1,1,'pwd','PWD Discount',20.00,1,'2026-06-02 15:07:50','2026-06-02 15:07:50'),
(2,1,'senior_citizen','Senior Citizen Discount',20.00,1,'2026-06-02 15:07:50','2026-06-02 15:07:50'),
(3,1,'student','Student Discount',15.00,1,'2026-06-02 15:07:50','2026-06-02 15:07:50'),
(4,1,'regular','Regular Fare',0.00,1,'2026-06-02 15:07:50','2026-06-02 15:07:50');
/*!40000 ALTER TABLE `fare_discounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fares`
--

DROP TABLE IF EXISTS `fares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fares` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `route_id` int(10) unsigned NOT NULL,
  `fare_discount_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_route_discount` (`route_id`,`fare_discount_id`),
  KEY `fare_discount_id` (`fare_discount_id`),
  CONSTRAINT `fares_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fares_ibfk_2` FOREIGN KEY (`fare_discount_id`) REFERENCES `fare_discounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fares`
--

LOCK TABLES `fares` WRITE;
/*!40000 ALTER TABLE `fares` DISABLE KEYS */;
INSERT INTO `fares` VALUES
(1,1,1,120.00,'2026-06-02 15:29:49','2026-06-02 15:29:49'),
(2,1,2,120.00,'2026-06-02 15:29:49','2026-06-02 15:29:49'),
(3,1,3,127.50,'2026-06-02 15:29:49','2026-06-02 15:29:49'),
(4,1,4,150.00,'2026-06-02 15:29:49','2026-06-02 15:29:49'),
(5,2,1,120.00,'2026-06-02 15:31:17','2026-06-02 15:31:17'),
(6,2,2,120.00,'2026-06-02 15:31:17','2026-06-02 15:31:17'),
(7,2,3,127.50,'2026-06-02 15:31:17','2026-06-02 15:31:17'),
(8,2,4,150.00,'2026-06-02 15:31:17','2026-06-02 15:31:17');
/*!40000 ALTER TABLE `fares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'2026-02-03-144119','App\\Database\\Migrations\\InitialSchema','default','App',1779448957,1),
(2,'2026-02-06-103530','App\\Database\\Migrations\\AddCurrentPassengersToQueue','default','App',1779448957,1),
(3,'2026-02-06-104947','App\\Database\\Migrations\\AddVehicleTypeToRoutes','default','App',1779448957,1),
(4,'2026-02-06-121011','App\\Database\\Migrations\\AddDefaultRouteToVehicles','default','App',1779448957,1),
(5,'2026-02-08-100000','App\\Database\\Migrations\\CreateAnnouncementsTable','default','App',1779448957,1),
(6,'2026-03-24-131600','App\\Database\\Migrations\\RemoveOperatorSimplifyVehicles','default','App',1779448957,1),
(7,'2026-05-07-100000','App\\Database\\Migrations\\AddEstimatedDepartureToQueue','default','App',1779448957,1),
(8,'2026-05-07-100100','App\\Database\\Migrations\\CreateDepartureRulesTable','default','App',1779448957,1),
(9,'2026-05-07-100200','App\\Database\\Migrations\\CreateFareDiscountsTable','default','App',1779448957,1),
(10,'2026-05-07-100300','App\\Database\\Migrations\\CleanupUsersRoleEnum','default','App',1779448957,1),
(11,'2026-05-07-100400','App\\Database\\Migrations\\AddQueueIndexes','default','App',1779448957,1),
(12,'2026-05-15-150000','App\\Database\\Migrations\\MoveRouteFareToFaresTable','default','App',1779448957,1),
(13,'2026-05-15-180000','App\\Database\\Migrations\\MergeFaresBackIntoRoutes','default','App',1779448957,1),
(14,'2026-05-16-000000','App\\Database\\Migrations\\DropTripStatusHistoryTable','default','App',1779448957,1),
(15,'2026-05-16-100000','App\\Database\\Migrations\\CreateUserRoutesTable','default','App',1779448957,1),
(16,'2026-05-22-200000','App\\Database\\Migrations\\RedesignFaresAndConfigs','default','App',1779449014,2),
(17,'2026-05-22-201000','App\\Database\\Migrations\\HardenFareRedesignTerminalIndexes','default','App',1779451028,3),
(18,'2026-05-22-202000','App\\Database\\Migrations\\NormalizeRouteOriginsFromTerminals','default','App',1779452161,4),
(19,'2026-05-22-203000','App\\Database\\Migrations\\RemoveRouteOriginUseTerminal','default','App',1779454489,5),
(20,'2026-05-23-000000','App\\Database\\Migrations\\RenameLogsToAuditLogs','default','App',1780479842,6),
(21,'2026-05-31-000000','App\\Database\\Migrations\\AddRouteIdToDepartureRules','default','App',1780479842,6),
(22,'2026-06-09-000002','App\\Database\\Migrations\\CreatePasswordResetTokensTable','default','App',1781015812,7);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES
(1,'giogmarquez','3c79b309f481d82c2fb831a072f2ab0a2c8f7f148a8308e67a8cf37b23645028','2026-06-16 12:44:00',0,'2026-06-16 11:44:00'),
(2,'giogmarquez','e47507b1a777e951e748273e58f50a2de4ea1c30d48a98372ddc40b3954e8578','2026-06-16 12:44:09',0,'2026-06-16 11:44:09'),
(3,'giogmarquez','cb62012bef1bd049454020647fc569cc4a5969e4d35cab3466820c92639268ef','2026-06-16 12:45:20',1,'2026-06-16 11:45:20');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS `queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `queue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(11) unsigned NOT NULL,
  `route_id` int(11) unsigned NOT NULL,
  `status` enum('waiting','boarding','departed','canceled') NOT NULL DEFAULT 'waiting',
  `current_passengers` int(11) NOT NULL DEFAULT 0,
  `position` int(11) NOT NULL DEFAULT 0,
  `arrival_time` datetime NOT NULL,
  `estimated_departure` datetime DEFAULT NULL,
  `departure_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_id` (`vehicle_id`),
  KEY `route_id` (`route_id`),
  CONSTRAINT `queue_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `queue_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queue`
--

LOCK TABLES `queue` WRITE;
/*!40000 ALTER TABLE `queue` DISABLE KEYS */;
INSERT INTO `queue` VALUES
(1,1,1,'departed',10,0,'2026-06-02 15:32:53','2026-06-02 16:02:56','2026-06-03 17:50:49'),
(2,1,1,'boarding',22,1,'2026-06-03 18:08:08','2026-06-13 20:31:36',NULL);
/*!40000 ALTER TABLE `queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `routes`
--

DROP TABLE IF EXISTS `routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `routes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `terminal_id` int(11) unsigned NOT NULL,
  `destination` varchar(100) NOT NULL,
  `vehicle_type` enum('jeepney','van','minibus') NOT NULL DEFAULT 'van',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_terminal_destination_vehicle` (`terminal_id`,`destination`,`vehicle_type`),
  KEY `terminal_id` (`terminal_id`),
  CONSTRAINT `routes_terminal_id_foreign` FOREIGN KEY (`terminal_id`) REFERENCES `terminals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `routes`
--

LOCK TABLES `routes` WRITE;
/*!40000 ALTER TABLE `routes` DISABLE KEYS */;
INSERT INTO `routes` VALUES
(1,1,'ORMOC','van','2026-06-02 15:29:49'),
(2,1,'TACLOBAN','jeepney','2026-06-02 15:31:17');
/*!40000 ALTER TABLE `routes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `terminals`
--

DROP TABLE IF EXISTS `terminals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `terminals` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terminals`
--

LOCK TABLES `terminals` WRITE;
/*!40000 ALTER TABLE `terminals` DISABLE KEYS */;
INSERT INTO `terminals` VALUES
(1,'PALOMPON','PALOMPON, LEYTE',50,'2026-06-02 15:07:50');
/*!40000 ALTER TABLE `terminals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_routes`
--

DROP TABLE IF EXISTS `user_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_routes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `route_id` int(11) unsigned NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_route` (`user_id`,`route_id`),
  KEY `idx_ur_user_id` (`user_id`),
  KEY `idx_ur_route_id` (`route_id`),
  CONSTRAINT `fk_uroutes_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_uroutes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_routes`
--

LOCK TABLES `user_routes` WRITE;
/*!40000 ALTER TABLE `user_routes` DISABLE KEYS */;
INSERT INTO `user_routes` VALUES
(1,2,1,'2026-06-02 15:31:42');
/*!40000 ALTER TABLE `user_routes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `full_name` varchar(100) NOT NULL,
  `login_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin123','$2y$10$CiJRci4T78G/Dndn3NAWjeqcSRnl02X6/Ejmanv9TvmdcSQxpwSOy','admin123@palompon-transit.test','admin','System Administrator',0,NULL,'2026-06-02 15:07:50','2026-06-16 11:36:36'),
(2,'giogmarquez','$2y$12$Y2GnMPJFxZOh8ucJZ9IcTut03R7rN0KLdpTMx9HLakz6yaML5RO7G','giogmarquez@palompon-transit.test','staff','Gio Marquez',0,NULL,'2026-06-02 15:31:42','2026-06-16 11:45:36');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `plate_number` varchar(20) NOT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `type` enum('jeepney','van','minibus') NOT NULL,
  `capacity` int(11) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `status` enum('active','maintenance') NOT NULL DEFAULT 'active',
  `route_id` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate_number` (`plate_number`),
  KEY `idx_vehicles_route_id` (`route_id`),
  CONSTRAINT `fk_vehicles_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicles`
--

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES
(1,'726 HOF','GIO MARQUEZ','van',22,'','active',1,'2026-06-02 15:32:15');
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-16 13:15:04
