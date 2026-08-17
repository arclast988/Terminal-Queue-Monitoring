/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: jeepneynvans
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-5ubuntu0.1 from Ubuntu

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES
(1,1,'dfdfdsfsd',1,0,'2026-06-12 08:45:10','2026-06-22 14:23:55');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=425 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES
(1,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 14:01:53'),
(2,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-18 14:05:50'),
(3,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 14:08:19'),
(4,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-18 14:09:26'),
(5,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 14:15:04'),
(6,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-18 14:18:21'),
(7,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 14:20:22'),
(8,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-18 14:21:38'),
(9,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 14:26:40'),
(10,1,'Create announcement','dsddfawsdasd','2026-06-18 14:27:36'),
(11,1,'Delete announcement','ID 2: dsddfawsdasd','2026-06-18 14:27:40'),
(12,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-18 14:34:48'),
(13,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 14:35:11'),
(14,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-18 14:36:13'),
(15,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 14:37:51'),
(16,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-18 14:38:23'),
(17,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 14:38:42'),
(18,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-18 14:39:43'),
(19,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 14:40:09'),
(20,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-18 14:46:10'),
(21,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 14:46:23'),
(22,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-18 14:46:26'),
(23,2,'Login','Staff logged in (noynayjaylo@gmail.com)','2026-06-18 14:46:49'),
(24,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 14:47:12'),
(25,2,'Depart Vehicle','Depart Vehicle for 112131 (ORMOC).','2026-06-18 14:51:42'),
(26,2,'Add to queue','Added 22323 to queue for ORMOC. Rule: Afternoon (40 min, starts at boarding).','2026-06-18 14:51:47'),
(27,2,'Start Boarding','Start Boarding for 22323 (ORMOC).','2026-06-18 14:51:49'),
(28,1,'Create route','PALOMPON → ORMOC (van, ₱1121).','2026-06-18 14:53:40'),
(29,1,'Update route','PALOMPON to ORMOC (minibus).','2026-06-18 14:54:04'),
(30,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-18 14:55:31'),
(31,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 14:59:42'),
(32,2,'Logout','Staff noynayjaylo@gmail.com logged out','2026-06-18 15:00:33'),
(33,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-18 15:29:09'),
(34,1,'Update departure rule','Updated departure rule: Late Night / Early Morning. Before: 60 min (00:00-05:00). After: 60 min (04:00-05:00).','2026-06-18 16:01:31'),
(35,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-19 10:51:23'),
(36,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-19 11:07:50'),
(37,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-19 11:46:20'),
(38,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-19 11:49:27'),
(39,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-19 12:33:52'),
(40,1,'Login','Admin logged in (arclast988@gmail.com)','2026-06-20 11:13:39'),
(41,1,'Logout','Admin arclast988@gmail.com logged out','2026-06-20 11:16:31'),
(42,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-20 11:16:36'),
(43,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-06-20 11:16:44'),
(44,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-20 11:17:20'),
(45,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-06-20 11:17:45'),
(46,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-20 11:20:19'),
(47,2,'Login','Admin logged in (noynayjaylo@gmail.com)','2026-06-20 11:22:08'),
(48,2,'Logout','Admin noynayjaylo@gmail.com logged out','2026-06-20 11:22:26'),
(49,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-20 11:22:32'),
(50,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-06-20 11:22:42'),
(51,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-20 11:23:34'),
(52,2,'Login','Admin logged in (noynayjaylo@gmail.com)','2026-06-20 11:26:41'),
(53,2,'Logout','Admin noynayjaylo@gmail.com logged out','2026-06-20 11:28:06'),
(54,1,'Update dispatcher routes','Updated dispatcher \"jycgrac@gmail.com\". Added: PALOMPON → ORMOC.','2026-06-20 11:31:05'),
(55,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-06-20 11:32:05'),
(56,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-06-20 11:32:13'),
(57,5,'Logout','Staff jycgrac@gmail.com logged out','2026-06-20 11:32:26'),
(58,2,'Login','Admin logged in (noynayjaylo@gmail.com)','2026-06-20 11:33:16'),
(59,2,'Logout','Admin noynayjaylo@gmail.com logged out','2026-06-20 11:36:26'),
(60,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-20 11:36:37'),
(61,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-06-20 11:37:12'),
(62,5,'Logout','Staff jycgrac@gmail.com logged out','2026-06-20 11:39:59'),
(63,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-06-20 11:40:42'),
(64,2,'Login','Admin logged in (noynayjaylo@gmail.com)','2026-06-20 11:40:47'),
(65,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-22 11:31:19'),
(66,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-06-22 11:31:32'),
(67,2,'Login','Admin logged in (noynayjaylo@gmail.com)','2026-06-22 13:44:02'),
(68,2,'Logout','Admin noynayjaylo@gmail.com logged out','2026-06-22 13:52:12'),
(69,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-22 13:52:17'),
(70,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-06-22 13:55:18'),
(71,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-06-22 13:55:50'),
(72,5,'Logout','Staff jycgrac@gmail.com logged out','2026-06-22 13:55:55'),
(73,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-22 14:00:03'),
(74,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-22 14:08:09'),
(75,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-06-22 14:09:51'),
(76,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-22 14:09:57'),
(77,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-06-22 14:10:48'),
(78,2,'Login','Admin logged in (noynayjaylo@gmail.com)','2026-06-22 14:10:57'),
(79,2,'Logout','Admin noynayjaylo@gmail.com logged out','2026-06-22 14:11:03'),
(80,2,'Login','Admin logged in (noynayjaylo@gmail.com)','2026-06-22 14:11:11'),
(81,2,'Logout','Admin noynayjaylo@gmail.com logged out','2026-06-22 14:11:13'),
(82,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-06-22 14:11:18'),
(83,5,'Logout','Staff jycgrac@gmail.com logged out','2026-06-22 14:11:21'),
(84,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-22 14:11:27'),
(85,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-06-22 14:23:02'),
(86,2,'Login','Admin logged in (noynayjaylo@gmail.com)','2026-06-22 14:23:08'),
(87,2,'Create announcement','jaylo\r\n','2026-06-22 14:23:18'),
(88,2,'Update announcement','ID 1: dfdfdsfsd','2026-06-22 14:23:23'),
(89,2,'Logout','Admin noynayjaylo@gmail.com logged out','2026-06-22 14:23:24'),
(90,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-22 14:23:40'),
(91,1,'Delete announcement','ID 3: jaylo\r\n','2026-06-22 14:23:47'),
(92,1,'Update announcement','ID 1: dfdfdsfsd','2026-06-22 14:23:55'),
(93,1,'Update vehicle','Updated vehicle 22323','2026-06-22 14:24:15'),
(94,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-06-22 14:24:22'),
(95,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-06-22 14:24:28'),
(96,5,'Depart Vehicle','Depart Vehicle for 22323 (ORMOC).','2026-06-22 14:24:34'),
(97,5,'Add to queue','Added 112131 to queue for ORMOC. Rule: Afternoon (40 min, starts at boarding).','2026-06-22 14:24:38'),
(98,5,'Start Boarding','Start Boarding for 112131 (ORMOC).','2026-06-22 14:24:40'),
(99,5,'Depart Vehicle','Depart Vehicle for 112131 (ORMOC).','2026-06-22 14:24:42'),
(100,5,'Logout','Staff jycgrac@gmail.com logged out','2026-06-22 14:24:45'),
(101,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-06-22 14:24:51'),
(102,1,'Update vehicle','Updated vehicle 22323','2026-06-22 14:25:06'),
(103,2,'Login','Admin logged in (noynayjaylo@gmail.com)','2026-06-22 15:14:25'),
(104,2,'Logout','Admin noynayjaylo@gmail.com logged out','2026-06-22 15:27:11'),
(105,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-15 19:09:43'),
(106,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-15 19:10:59'),
(107,2,'Login','Admin logged in (noynayjaylo@gmail.com)','2026-07-15 19:11:15'),
(108,2,'Logout','Admin noynayjaylo@gmail.com logged out','2026-07-15 19:11:25'),
(109,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-15 19:11:39'),
(110,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-15 19:12:39'),
(111,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-15 19:16:42'),
(112,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-15 19:17:05'),
(113,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-15 19:17:22'),
(114,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-15 19:17:43'),
(115,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-15 19:18:00'),
(116,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-15 19:27:26'),
(117,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-15 19:27:38'),
(118,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-15 19:28:28'),
(119,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-15 19:34:10'),
(120,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-15 19:36:27'),
(121,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-15 19:36:36'),
(122,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 17:44:13'),
(123,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 17:44:41'),
(124,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 17:45:00'),
(125,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 17:45:06'),
(126,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 17:46:47'),
(127,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 17:47:30'),
(128,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 17:53:52'),
(129,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 18:09:36'),
(130,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 18:09:44'),
(131,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 18:10:08'),
(132,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 18:11:33'),
(133,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 18:11:42'),
(134,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 18:11:47'),
(135,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 18:12:27'),
(136,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 18:14:27'),
(137,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 18:14:30'),
(138,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 18:14:35'),
(139,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 18:21:51'),
(140,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 18:21:57'),
(141,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 18:22:54'),
(142,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 18:23:02'),
(143,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 18:23:18'),
(144,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 18:23:24'),
(145,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 18:24:26'),
(146,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 18:24:38'),
(147,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 18:28:05'),
(148,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 18:28:11'),
(149,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 18:28:21'),
(150,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 18:29:37'),
(151,1,'Update dispatcher routes','Updated dispatcher \"jycgrac@gmail.com\". Added: PALOMPON → TACLOBAN.','2026-07-21 18:31:12'),
(152,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 19:26:02'),
(153,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 19:26:11'),
(154,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 19:26:48'),
(155,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 19:26:53'),
(156,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 19:28:18'),
(157,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 19:28:37'),
(158,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 19:29:54'),
(159,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 19:29:59'),
(160,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 19:33:21'),
(161,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 19:33:26'),
(162,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 19:34:09'),
(163,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 19:34:17'),
(164,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 19:35:33'),
(165,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 19:35:38'),
(166,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 19:37:16'),
(167,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 19:37:27'),
(168,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 19:42:41'),
(169,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 19:42:49'),
(170,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 19:42:50'),
(171,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 19:42:55'),
(172,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 19:43:14'),
(173,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 19:43:20'),
(174,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 19:49:33'),
(175,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 19:49:44'),
(176,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 19:50:54'),
(177,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 19:50:59'),
(178,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 19:51:33'),
(179,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 19:51:38'),
(180,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 20:24:10'),
(181,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 20:25:47'),
(182,1,'Create route','PALOMPON → BOGO (2 vehicle type(s)).','2026-07-21 20:27:27'),
(183,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 20:36:35'),
(184,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 20:52:00'),
(185,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-21 20:52:06'),
(186,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-21 20:52:17'),
(187,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 20:52:32'),
(188,1,'Update route group','PALOMPON → ORMOC.','2026-07-21 20:54:53'),
(189,1,'Delete route','PALOMPON → BOGO.','2026-07-21 20:55:03'),
(190,1,'Update route group','PALOMPON → BOGO.','2026-07-21 20:55:29'),
(191,1,'Update route group','PALOMPON → BOGO.','2026-07-21 20:55:37'),
(192,1,'Delete route','PALOMPON → ORMOC.','2026-07-21 20:55:48'),
(193,1,'Delete route','PALOMPON → TACLOBAN.','2026-07-21 20:56:30'),
(194,1,'Update route group','PALOMPON → ORMOC.','2026-07-21 20:56:42'),
(195,1,'Update route group','PALOMPON → TACLOBAN.','2026-07-21 20:57:08'),
(196,1,'Reassign vehicle route','Reassigned 22323 from None to PALOMPON → TACLOBAN','2026-07-21 20:57:16'),
(197,1,'Reassign vehicle route','Reassigned 112131 from None to PALOMPON → TACLOBAN','2026-07-21 20:57:21'),
(198,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 20:57:23'),
(199,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 21:07:12'),
(200,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 21:07:37'),
(201,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-21 21:11:07'),
(202,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-21 21:11:12'),
(203,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-28 11:41:59'),
(204,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-28 11:42:02'),
(205,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-28 11:44:18'),
(206,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-28 11:44:20'),
(207,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-28 11:44:25'),
(208,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-28 11:44:27'),
(209,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-28 11:45:09'),
(210,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-28 11:45:43'),
(211,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-28 11:49:14'),
(212,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-28 11:49:26'),
(213,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-28 11:49:31'),
(214,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-28 11:50:15'),
(215,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-28 11:50:21'),
(216,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-28 11:52:45'),
(217,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-28 11:52:52'),
(218,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-28 11:52:54'),
(219,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-28 11:53:00'),
(220,1,'Update dispatcher routes','Updated dispatcher \"jycgrac@gmail.com\". Added: PALOMPON → ORMOC, PALOMPON → TACLOBAN.','2026-07-28 11:54:54'),
(221,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-28 11:54:58'),
(222,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-28 11:55:05'),
(223,5,'Add to queue','Added 22323 to queue for TACLOBAN. Rule: Mid-Morning (40 min, starts at boarding).','2026-07-28 11:55:10'),
(224,5,'Start Boarding','Start Boarding for 22323 (TACLOBAN).','2026-07-28 11:55:16'),
(225,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-28 11:56:50'),
(226,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-28 12:18:37'),
(227,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-07-28 12:23:36'),
(228,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-07-28 12:23:43'),
(229,5,'Depart Vehicle','Depart Vehicle for 22323 (TACLOBAN).','2026-07-28 12:24:04'),
(230,5,'Logout','Staff jycgrac@gmail.com logged out','2026-07-28 12:41:12'),
(231,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-07-28 12:41:22'),
(232,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 17:31:35'),
(233,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 17:33:01'),
(234,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 17:34:48'),
(235,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 17:52:45'),
(236,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-02 17:53:01'),
(237,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-02 17:53:59'),
(238,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 17:54:17'),
(239,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 17:56:12'),
(240,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 17:58:16'),
(241,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 17:58:58'),
(242,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-02 17:59:15'),
(243,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-02 17:59:19'),
(244,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-02 17:59:25'),
(245,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-02 17:59:31'),
(246,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 17:59:35'),
(247,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 18:00:45'),
(248,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-02 18:01:03'),
(249,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-02 18:02:55'),
(250,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 18:03:02'),
(251,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 18:04:07'),
(252,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-02 18:04:24'),
(253,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-02 18:06:18'),
(254,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 18:06:27'),
(255,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 18:07:08'),
(256,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 18:07:21'),
(257,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 18:07:40'),
(258,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 18:10:04'),
(259,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 18:10:41'),
(260,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-02 18:10:47'),
(261,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-02 18:10:54'),
(262,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 18:12:44'),
(263,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 18:12:58'),
(264,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 18:13:18'),
(265,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 18:13:29'),
(266,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-02 18:13:37'),
(267,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-02 18:16:00'),
(268,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 18:17:03'),
(269,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 18:17:42'),
(270,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-02 18:17:48'),
(271,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-02 18:17:59'),
(272,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 18:18:56'),
(273,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 18:20:43'),
(274,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 18:20:52'),
(275,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 18:20:55'),
(276,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-02 18:21:01'),
(277,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-02 18:22:27'),
(278,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-02 18:22:33'),
(279,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-02 18:22:51'),
(280,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 18:23:55'),
(281,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 18:24:12'),
(282,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-02 18:24:22'),
(283,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-02 18:24:32'),
(284,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 18:24:41'),
(285,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-02 18:27:48'),
(286,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-02 18:28:37'),
(287,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-04 19:46:02'),
(288,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-04 19:46:08'),
(289,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-04 19:48:14'),
(290,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-04 19:48:53'),
(291,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-04 19:54:34'),
(292,1,'Create route','PALOMPON → KANANGA (jeepney, ₱150).','2026-08-04 20:09:48'),
(293,1,'Update route','PALOMPON to KANANGA (jeepney).','2026-08-04 20:10:09'),
(294,1,'Create route','PALOMPON → MANILA (van, ₱150).','2026-08-04 20:12:23'),
(295,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-04 20:27:41'),
(296,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-04 20:51:00'),
(297,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-04 20:52:58'),
(298,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-04 20:53:30'),
(299,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-04 20:54:07'),
(300,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-04 20:54:42'),
(301,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-04 21:09:13'),
(302,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-04 21:20:20'),
(303,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-04 21:20:36'),
(304,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-04 21:25:25'),
(305,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 12:49:48'),
(306,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 12:51:31'),
(307,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 13:07:32'),
(308,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 13:08:04'),
(309,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 13:32:26'),
(310,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 13:32:40'),
(311,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 13:33:04'),
(312,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 13:37:29'),
(313,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 13:58:07'),
(314,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 13:58:11'),
(315,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 14:00:39'),
(316,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 14:00:50'),
(317,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 14:03:44'),
(318,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 14:05:35'),
(319,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 14:09:43'),
(320,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 14:11:45'),
(321,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 14:36:50'),
(322,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 14:38:14'),
(323,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 15:14:52'),
(324,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 15:15:17'),
(325,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-09 15:44:15'),
(326,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-09 15:44:42'),
(327,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-09 15:47:08'),
(328,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-09 15:47:42'),
(329,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 16:21:16'),
(330,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 16:23:06'),
(331,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-09 16:23:15'),
(332,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-09 16:24:30'),
(333,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 16:24:45'),
(334,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 16:28:06'),
(335,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 16:30:38'),
(336,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 16:31:26'),
(337,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 16:33:20'),
(338,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 16:33:35'),
(339,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 16:56:24'),
(340,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 17:05:17'),
(341,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 17:05:47'),
(342,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 17:12:11'),
(343,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 17:27:27'),
(344,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 17:38:26'),
(345,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-09 17:38:36'),
(346,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-09 17:42:01'),
(347,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 17:48:53'),
(348,1,'Add discount','Student Workder Discount discount added at 21%.','2026-08-09 18:02:59'),
(349,1,'Delete discount','student_workder_discount discount (Student Workder Discount) deleted.','2026-08-09 18:03:07'),
(350,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 18:16:36'),
(351,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 18:19:30'),
(352,1,'Add vehicle type','Added vehicle type Jaylo.','2026-08-09 18:25:45'),
(353,1,'Add vehicle type','Added vehicle type Jay Ter.','2026-08-09 18:43:19'),
(354,1,'Create route','PALOMPON → SDS (1 vehicle type(s)).','2026-08-09 18:45:15'),
(355,1,'Delete route group','Deleted 1 vehicle type(s) for route to SDS.','2026-08-09 18:45:22'),
(356,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 18:45:36'),
(357,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 18:46:03'),
(358,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 18:46:17'),
(359,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 18:51:50'),
(360,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 18:52:06'),
(361,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 18:55:22'),
(362,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 18:55:28'),
(363,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-09 18:55:40'),
(364,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-09 18:56:21'),
(365,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 19:37:33'),
(366,1,'Delete vehicle type','Deleted vehicle type Jay Ter (jay_ter) and associated routes, fares, and vehicles.','2026-08-09 19:49:39'),
(367,1,'Delete vehicle type','Deleted vehicle type Jaylo (jaylo) and associated routes, fares, and vehicles.','2026-08-09 19:49:41'),
(368,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 20:05:25'),
(369,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 20:05:48'),
(370,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 20:08:15'),
(371,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 20:08:53'),
(372,1,'Add vehicle type','Added vehicle type Bus.','2026-08-09 20:09:41'),
(373,1,'Delete vehicle type','Deleted vehicle type Bus (bus) and associated routes, fares, and vehicles.','2026-08-09 20:14:49'),
(374,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-09 20:27:51'),
(375,5,'Add to queue','Added 112131 to queue for TACLOBAN. Rule: Evening (40 min, starts at boarding).','2026-08-09 20:27:57'),
(376,5,'Start Boarding','Start Boarding for 112131 (TACLOBAN).','2026-08-09 20:27:59'),
(377,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-09 20:28:00'),
(378,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 20:30:07'),
(379,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-09 20:34:36'),
(380,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-09 20:36:05'),
(381,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-11 17:26:03'),
(382,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-11 17:26:35'),
(383,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-11 17:26:45'),
(384,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-11 17:27:41'),
(385,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-11 17:27:52'),
(386,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-11 17:28:00'),
(387,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-11 17:37:56'),
(388,5,'Add to queue','Added 22323 to queue for TACLOBAN. Rule: Afternoon Rush (30 min, starts at boarding).','2026-08-11 17:38:28'),
(389,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-11 17:38:31'),
(390,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-11 18:06:30'),
(391,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-11 18:07:00'),
(392,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-11 18:08:57'),
(393,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-11 18:12:34'),
(394,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-11 18:13:54'),
(395,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-11 18:13:56'),
(396,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-11 18:14:05'),
(397,5,'Cancel Trip','Cancel Trip for 112131 (TACLOBAN).','2026-08-11 18:14:09'),
(398,5,'Cancel Trip','Cancel Trip for 22323 (TACLOBAN).','2026-08-11 18:14:11'),
(399,5,'Add to queue','Added 112131 to queue for TACLOBAN. Rule: Evening (40 min).','2026-08-11 18:14:14'),
(400,5,'Add to queue','Added 22323 to queue for TACLOBAN. Rule: Evening (40 min).','2026-08-11 18:14:17'),
(401,5,'Start Boarding','Start Boarding for 112131 (TACLOBAN).','2026-08-11 18:14:18'),
(402,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-11 18:14:40'),
(403,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-11 18:14:46'),
(404,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-11 18:15:40'),
(405,1,'Add vehicle type','Added vehicle type bus.','2026-08-11 18:19:19'),
(406,1,'Delete vehicle type','Deleted vehicle type bus (bus) and associated routes, fares, and vehicles.','2026-08-11 18:19:25'),
(407,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-11 18:20:46'),
(408,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-11 19:01:27'),
(409,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-11 19:01:49'),
(410,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-11 19:11:58'),
(411,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-11 19:14:03'),
(412,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-11 19:16:01'),
(413,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-11 19:16:08'),
(414,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-11 19:57:21'),
(415,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-11 19:57:35'),
(416,1,'Logout','Super_admin arclast988@gmail.com logged out','2026-08-11 20:09:42'),
(417,5,'Start Boarding','Start Boarding for 22323 (TACLOBAN).','2026-08-11 20:11:16'),
(418,5,'Cancel Trip','Cancel Trip for 112131 (TACLOBAN).','2026-08-11 20:11:23'),
(419,5,'Cancel Trip','Cancel Trip for 22323 (TACLOBAN).','2026-08-11 20:11:25'),
(420,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-11 20:12:46'),
(421,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-11 20:16:13'),
(422,5,'Login','Staff logged in (jycgrac@gmail.com)','2026-08-11 20:16:27'),
(423,5,'Logout','Staff jycgrac@gmail.com logged out','2026-08-11 20:47:55'),
(424,1,'Login','Super_admin logged in (arclast988@gmail.com)','2026-08-11 21:16:00');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `departure_rules` WRITE;
/*!40000 ALTER TABLE `departure_rules` DISABLE KEYS */;
INSERT INTO `departure_rules` VALUES
(1,1,NULL,'04:00:00','05:00:00',60,'Late Night / Early Morning','2026-06-03 11:18:52','2026-06-18 16:01:31'),
(2,1,NULL,'05:00:00','09:00:00',30,'Morning Rush','2026-06-03 11:18:52','2026-06-03 11:18:52'),
(3,1,NULL,'09:00:00','12:00:00',40,'Mid-Morning','2026-06-03 11:18:52','2026-06-03 11:18:52'),
(4,1,NULL,'12:00:00','15:00:00',40,'Afternoon','2026-06-03 11:18:52','2026-06-03 11:18:52'),
(5,1,NULL,'15:00:00','18:00:00',30,'Afternoon Rush','2026-06-03 11:18:52','2026-06-03 11:18:52'),
(6,1,NULL,'18:00:00','21:00:00',40,'Evening','2026-06-03 11:18:52','2026-06-03 11:18:52'),
(7,1,NULL,'21:00:00','23:59:00',60,'Late Evening','2026-06-03 11:18:52','2026-06-03 11:18:52');
/*!40000 ALTER TABLE `departure_rules` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fare_discounts`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `fare_discounts` WRITE;
/*!40000 ALTER TABLE `fare_discounts` DISABLE KEYS */;
INSERT INTO `fare_discounts` VALUES
(1,1,'pwd','PWD Discount',20.00,1,'2026-06-03 11:18:52','2026-06-03 11:18:52'),
(2,1,'senior_citizen','Senior Citizen Discount',20.00,1,'2026-06-03 11:18:52','2026-06-03 11:18:52'),
(3,1,'student','Student Discount',15.00,1,'2026-06-03 11:18:52','2026-06-03 11:18:52'),
(4,1,'regular','Regular Fare',0.00,1,'2026-06-03 11:18:52','2026-06-03 11:18:52');
/*!40000 ALTER TABLE `fare_discounts` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fares`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `fares` WRITE;
/*!40000 ALTER TABLE `fares` DISABLE KEYS */;
INSERT INTO `fares` VALUES
(53,6,1,88.80,'2026-07-21 20:55:37','2026-07-21 20:55:37'),
(54,6,4,111.00,'2026-07-21 20:55:37','2026-07-21 20:55:37'),
(55,6,2,88.80,'2026-07-21 20:55:37','2026-07-21 20:55:37'),
(56,6,3,94.35,'2026-07-21 20:55:37','2026-07-21 20:55:37'),
(57,5,1,896.80,'2026-07-21 20:56:42','2026-07-21 20:56:42'),
(58,5,4,1121.00,'2026-07-21 20:56:42','2026-07-21 20:56:42'),
(59,5,2,896.80,'2026-07-21 20:56:42','2026-07-21 20:56:42'),
(60,5,3,952.85,'2026-07-21 20:56:42','2026-07-21 20:56:42'),
(61,8,1,98585.60,'2026-07-21 20:56:42','2026-07-21 20:56:42'),
(62,8,4,123232.00,'2026-07-21 20:56:42','2026-07-21 20:56:42'),
(63,8,2,98585.60,'2026-07-21 20:56:42','2026-07-21 20:56:42'),
(64,8,3,104747.20,'2026-07-21 20:56:42','2026-07-21 20:56:42'),
(65,3,1,120.00,'2026-07-21 20:57:08','2026-07-21 20:57:08'),
(66,3,4,150.00,'2026-07-21 20:57:08','2026-07-21 20:57:08'),
(67,3,2,120.00,'2026-07-21 20:57:08','2026-07-21 20:57:08'),
(68,3,3,127.50,'2026-07-21 20:57:08','2026-07-21 20:57:08'),
(69,9,1,9849856.80,'2026-07-21 20:57:08','2026-07-21 20:57:08'),
(70,9,4,12312321.00,'2026-07-21 20:57:08','2026-07-21 20:57:08'),
(71,9,2,9849856.80,'2026-07-21 20:57:08','2026-07-21 20:57:08'),
(72,9,3,10465472.85,'2026-07-21 20:57:08','2026-07-21 20:57:08'),
(77,10,1,120.00,'2026-08-04 20:10:09','2026-08-04 20:10:09'),
(78,10,4,150.00,'2026-08-04 20:10:09','2026-08-04 20:10:09'),
(79,10,2,120.00,'2026-08-04 20:10:09','2026-08-04 20:10:09'),
(80,10,3,127.50,'2026-08-04 20:10:09','2026-08-04 20:10:09'),
(81,11,1,120.00,'2026-08-04 20:12:23','2026-08-04 20:12:23'),
(82,11,4,150.00,'2026-08-04 20:12:23','2026-08-04 20:12:23'),
(83,11,2,120.00,'2026-08-04 20:12:23','2026-08-04 20:12:23'),
(84,11,3,127.50,'2026-08-04 20:12:23','2026-08-04 20:12:23');
/*!40000 ALTER TABLE `fares` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
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
(20,'2026-05-23-000000','App\\Database\\Migrations\\RenameLogsToAuditLogs','default','App',1781589635,6),
(21,'2026-05-31-000000','App\\Database\\Migrations\\AddRouteIdToDepartureRules','default','App',1781589635,6),
(22,'2026-06-16-000000','App\\Database\\Migrations\\AddLoginRateLimitToUsers','default','App',1781589635,6),
(23,'2026-06-16-100000','App\\Database\\Migrations\\CreatePasswordResetTokensTable','default','App',1781589635,6),
(24,'2026-06-17-000000','App\\Database\\Migrations\\AddResetCodeToPasswordResetTokens','default','App',1781655759,7),
(25,'2026-06-17-000001','App\\Database\\Migrations\\AddEmailToUsers','default','App',1781655799,8),
(26,'2026-06-17-000002','App\\Database\\Migrations\\AddCodeAttemptsToPasswordResetTokens','default','App',1781658319,9),
(27,'2026-06-20-100000','App\\Database\\Migrations\\AddSuperAdminRole','default','App',1781925308,10),
(28,'2026-08-09-190000','App\\Database\\Migrations\\CreateVehicleTypes','default','App',1786270492,11),
(29,'2026-08-13-200000','App\\Database\\Migrations\\AddOperatorNameToVehicles','default','App',1787005140,12);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
  `reset_code` varchar(6) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `verified` tinyint(1) DEFAULT 0,
  `code_attempts` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES
(1,'arclast988@gmail.com','1c0816807d8a4763cf624088e04ce44678ca6f205c62d631f1d707f2462e3c41','311622','arclast988@gmail.com','2026-06-19 11:19:01',1,0,0,'2026-06-19 11:09:01'),
(2,'jycgrac@gmail.com','214e4b6bc4da7bbf3be7af38a81c1d5e60e6addae1d347fd32af98dc028da31a','684868','jycgrac@gmail.com','2026-07-15 19:23:04',0,0,0,'2026-07-15 19:13:04'),
(3,'arclast988@gmail.com','6c03a66754ec8e1cf0b15700cded82682d5ce4f732b2d9c96574e2b7f8f7cb03','686707','arclast988@gmail.com','2026-08-04 19:51:54',0,0,0,'2026-08-04 19:41:54'),
(4,'noynayjaylo@gmail.com','0714d1921b3e5f293b1d5bd7504140833fb09ac706c68f8fb4855d6bbb3c49f3','693692','noynayjaylo@gmail.com','2026-08-04 19:56:14',1,0,0,'2026-08-04 19:46:14'),
(5,'noynayjaylo@gmail.com','12516462e143a73aca8437e5af52a996668df3a7414139ac581ac7170a12ac81','070249','noynayjaylo@gmail.com','2026-08-04 19:57:24',0,0,0,'2026-08-04 19:47:24');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queue`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `queue` WRITE;
/*!40000 ALTER TABLE `queue` DISABLE KEYS */;
INSERT INTO `queue` VALUES
(11,2,9,'departed',14,0,'2026-07-28 11:55:10','2026-07-28 12:35:16','2026-07-28 12:24:04'),
(12,1,9,'canceled',0,0,'2026-08-09 20:27:57',NULL,NULL),
(13,2,9,'canceled',0,0,'2026-08-11 17:38:28',NULL,NULL),
(14,1,9,'canceled',20,0,'2026-08-11 18:14:14',NULL,NULL),
(15,2,9,'canceled',0,0,'2026-08-11 18:14:17',NULL,NULL);
/*!40000 ALTER TABLE `queue` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
  `vehicle_type` varchar(50) NOT NULL DEFAULT 'van',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_terminal_destination_vehicle` (`terminal_id`,`destination`,`vehicle_type`),
  KEY `terminal_id` (`terminal_id`),
  CONSTRAINT `routes_terminal_id_foreign` FOREIGN KEY (`terminal_id`) REFERENCES `terminals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `routes`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `routes` WRITE;
/*!40000 ALTER TABLE `routes` DISABLE KEYS */;
INSERT INTO `routes` VALUES
(3,1,'TACLOBAN','van','2026-06-13 17:45:42'),
(5,1,'ORMOC','van','2026-06-18 14:53:40'),
(6,1,'BOGO','van','2026-07-21 20:27:27'),
(8,1,'ORMOC','jeepney','2026-07-21 20:56:42'),
(9,1,'TACLOBAN','minibus','2026-07-21 20:57:08'),
(10,1,'KANANGA','jeepney','2026-08-04 20:09:48'),
(11,1,'MANILA','van','2026-08-04 20:12:23');
/*!40000 ALTER TABLE `routes` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terminals`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `terminals` WRITE;
/*!40000 ALTER TABLE `terminals` DISABLE KEYS */;
INSERT INTO `terminals` VALUES
(1,'PALOMPON','PALOMPON, LEYTE',50,'2026-06-03 11:18:52'),
(5,'dfdsf','dfd',10,'2026-08-04 20:26:01');
/*!40000 ALTER TABLE `terminals` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_routes`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `user_routes` WRITE;
/*!40000 ALTER TABLE `user_routes` DISABLE KEYS */;
INSERT INTO `user_routes` VALUES
(20,5,8,'2026-07-28 11:54:54'),
(21,5,5,'2026-07-28 11:54:54'),
(22,5,3,'2026-07-28 11:54:54'),
(23,5,9,'2026-07-28 11:54:54');
/*!40000 ALTER TABLE `user_routes` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
  `role` enum('super_admin','admin','staff') NOT NULL DEFAULT 'staff',
  `full_name` varchar(100) NOT NULL,
  `login_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'arclast988@gmail.com','$2y$10$CiJRci4T78G/Dndn3NAWjeqcSRnl02X6/Ejmanv9TvmdcSQxpwSOy','arclast988@gmail.com','super_admin','System Administrator',0,NULL,'2026-06-03 11:18:52','2026-08-11 21:16:00'),
(2,'noynayjaylo@gmail.com','$2y$12$x7/4x01cDuGR.0PjCtjG8emPAirOt18PT2dqdbtb/a5/pqiMLSR8W','noynayjaylo@gmail.com','admin','jaylo',0,NULL,'2026-06-06 19:10:10','2026-07-15 19:11:15'),
(5,'jycgrac@gmail.com','$2y$12$z8AHLovLZJK2327FhBUJ2uwn8SwLZIwQwQEhXEc0XAup3IlEinjAi','jycgrac@gmail.com','staff','Staff User',0,NULL,'2026-06-20 11:20:13','2026-08-11 20:16:27');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `vehicle_types`
--

DROP TABLE IF EXISTS `vehicle_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle_types`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `vehicle_types` WRITE;
/*!40000 ALTER TABLE `vehicle_types` DISABLE KEYS */;
INSERT INTO `vehicle_types` VALUES
(1,'Van','van',1,'2026-08-09 18:14:52','2026-08-09 18:14:52'),
(2,'Jeepney','jeepney',1,'2026-08-09 18:14:52','2026-08-09 18:14:52'),
(3,'Minibus','minibus',1,'2026-08-09 18:14:52','2026-08-09 18:14:52');
/*!40000 ALTER TABLE `vehicle_types` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

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
  `operator_name` varchar(100) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `status` enum('active','maintenance') NOT NULL DEFAULT 'active',
  `route_id` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate_number` (`plate_number`),
  KEY `idx_vehicles_route_id` (`route_id`),
  CONSTRAINT `fk_vehicles_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicles`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES
(1,'112131','jaylo','jaylo','minibus',20,'','active',9,'2026-06-06 19:16:22'),
(2,'22323','terrado','terrado','minibus',20,'','active',9,'2026-06-07 06:36:21');
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-08-18  6:20:08
