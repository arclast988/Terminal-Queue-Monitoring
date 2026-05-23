
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
DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (4,1,'TESTING: Please be aware of weather conditions.',1,1,'2026-03-24 22:24:41','2026-03-24 22:24:41');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `departure_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departure_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `terminal_id` int(10) unsigned NOT NULL DEFAULT 1,
  `time_from` time NOT NULL,
  `time_to` time NOT NULL,
  `wait_minutes` int(11) NOT NULL DEFAULT 30,
  `label` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_departure_rules_terminal` (`terminal_id`),
  CONSTRAINT `fk_departure_rules_terminal` FOREIGN KEY (`terminal_id`) REFERENCES `terminals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `departure_rules` WRITE;
/*!40000 ALTER TABLE `departure_rules` DISABLE KEYS */;
INSERT INTO `departure_rules` VALUES (1,1,'00:00:00','05:00:00',60,'Late Night / Early Morning','2026-02-11 02:06:33','2026-05-16 09:06:24');
INSERT INTO `departure_rules` VALUES (2,1,'05:00:00','09:00:00',30,'Morning Rush','2026-02-11 02:06:33','2026-02-11 02:06:33');
INSERT INTO `departure_rules` VALUES (3,1,'09:00:00','12:00:00',40,'Mid-Morning','2026-02-11 02:06:33','2026-02-11 02:06:33');
INSERT INTO `departure_rules` VALUES (4,1,'12:00:00','15:00:00',40,'Afternoon','2026-02-11 02:06:33','2026-02-11 02:06:33');
INSERT INTO `departure_rules` VALUES (5,1,'15:00:00','18:00:00',30,'Afternoon Rush','2026-02-11 02:06:33','2026-02-11 02:06:33');
INSERT INTO `departure_rules` VALUES (6,1,'18:00:00','21:00:00',40,'Evening','2026-02-11 02:06:33','2026-02-11 02:06:33');
INSERT INTO `departure_rules` VALUES (8,1,'21:00:00','23:59:00',60,'Late Evening','2026-05-16 09:14:14','2026-05-16 09:15:08');
/*!40000 ALTER TABLE `departure_rules` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `fare_discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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

LOCK TABLES `fare_discounts` WRITE;
/*!40000 ALTER TABLE `fare_discounts` DISABLE KEYS */;
INSERT INTO `fare_discounts` VALUES (1,1,'pwd','PWD Discount',20.00,1,'2026-05-02 00:00:00','2026-05-22 22:07:09');
INSERT INTO `fare_discounts` VALUES (2,1,'senior_citizen','Senior Citizen Discount',20.00,1,'2026-05-02 00:00:00','2026-05-22 22:07:09');
INSERT INTO `fare_discounts` VALUES (3,1,'student','Student Discount',15.00,1,'2026-05-02 00:00:00','2026-05-02 00:00:00');
INSERT INTO `fare_discounts` VALUES (4,1,'regular','Regular Fare',0.00,1,'2026-05-22 19:23:34','2026-05-22 19:23:34');
/*!40000 ALTER TABLE `fare_discounts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `fares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `fares` WRITE;
/*!40000 ALTER TABLE `fares` DISABLE KEYS */;
INSERT INTO `fares` VALUES (1,1,4,155.00,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (2,1,1,124.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (3,1,2,124.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (4,1,3,131.75,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (5,2,4,300.00,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (6,2,1,240.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (7,2,2,240.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (8,2,3,255.00,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (9,3,4,10.00,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (10,3,1,8.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (11,3,2,8.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (12,3,3,8.50,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (13,4,4,140.00,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (14,4,1,112.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (15,4,2,112.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (16,4,3,119.00,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (17,7,4,300.00,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (18,7,1,240.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (19,7,2,240.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (20,7,3,255.00,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (21,8,4,150.00,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (22,8,1,120.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (23,8,2,120.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (24,8,3,127.50,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (25,13,4,121.00,'2026-05-22 19:23:34','2026-05-22 22:06:41');
INSERT INTO `fares` VALUES (26,13,1,96.80,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (27,13,2,96.80,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (28,13,3,102.85,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (37,16,4,50.00,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (38,16,1,40.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (39,16,2,40.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (40,16,3,42.50,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (41,17,4,50.00,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (42,17,1,40.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (43,17,2,40.00,'2026-05-22 19:23:34','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (44,17,3,42.50,'2026-05-22 19:23:34','2026-05-22 19:23:34');
INSERT INTO `fares` VALUES (57,15,1,160.00,'2026-05-22 20:57:28','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (58,15,2,160.00,'2026-05-22 20:57:28','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (59,15,3,170.00,'2026-05-22 20:57:28','2026-05-22 20:57:28');
INSERT INTO `fares` VALUES (60,15,4,200.00,'2026-05-22 20:57:28','2026-05-22 20:57:28');
INSERT INTO `fares` VALUES (65,14,1,80.00,'2026-05-22 21:41:07','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (66,14,2,80.00,'2026-05-22 21:41:07','2026-05-22 22:07:09');
INSERT INTO `fares` VALUES (67,14,3,85.00,'2026-05-22 21:41:07','2026-05-22 21:41:07');
INSERT INTO `fares` VALUES (68,14,4,100.00,'2026-05-22 21:41:07','2026-05-22 21:41:07');
/*!40000 ALTER TABLE `fares` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=805 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (602,1,'Login','Admin logged in (admin123)','2026-05-15 14:50:07');
INSERT INTO `audit_logs` VALUES (603,1,'Update route','PALOMPON → ORMOC (van).','2026-05-15 14:51:36');
INSERT INTO `audit_logs` VALUES (604,1,'Logout','Admin admin123 logged out','2026-05-15 15:06:57');
INSERT INTO `audit_logs` VALUES (605,2,'Login','Staff logged in (giogmarquez)','2026-05-15 15:07:02');
INSERT INTO `audit_logs` VALUES (606,2,'Add to queue','Added 726 HOF to queue for ORMOC.','2026-05-15 15:09:35');
INSERT INTO `audit_logs` VALUES (607,2,'Logout','Staff giogmarquez logged out','2026-05-15 15:14:14');
INSERT INTO `audit_logs` VALUES (608,1,'Login','Admin logged in (admin123)','2026-05-15 15:14:19');
INSERT INTO `audit_logs` VALUES (609,1,'Update route','PALOMPON → ORMOC (van).','2026-05-15 15:15:15');
INSERT INTO `audit_logs` VALUES (610,1,'Update fare','PALOMPON TO ORMOC (VAN) route fare changed. Before: PHP 5.00 | After: PHP 155.00.','2026-05-15 15:27:15');
INSERT INTO `audit_logs` VALUES (611,1,'Logout','Admin admin123 logged out','2026-05-15 15:27:29');
INSERT INTO `audit_logs` VALUES (612,2,'Login','Staff logged in (giogmarquez)','2026-05-15 15:27:33');
INSERT INTO `audit_logs` VALUES (613,2,'Logout','Staff giogmarquez logged out','2026-05-15 15:31:24');
INSERT INTO `audit_logs` VALUES (614,2,'Login','Staff logged in (giogmarquez)','2026-05-15 15:32:11');
INSERT INTO `audit_logs` VALUES (615,2,'Start Boarding','Start Boarding for 726 HOF (ORMOC).','2026-05-15 15:32:19');
INSERT INTO `audit_logs` VALUES (616,2,'Logout','Staff giogmarquez logged out','2026-05-15 15:32:23');
INSERT INTO `audit_logs` VALUES (617,2,'Login','Staff logged in (giogmarquez)','2026-05-15 15:38:02');
INSERT INTO `audit_logs` VALUES (618,2,'Depart Vehicle','Depart Vehicle for 726 HOF (ORMOC).','2026-05-15 15:38:05');
INSERT INTO `audit_logs` VALUES (619,2,'Add to queue','Added sdaasd to queue for ORMOC.','2026-05-15 15:38:13');
INSERT INTO `audit_logs` VALUES (620,2,'Add to queue','Added HELO123 to queue for TACLOBAN.','2026-05-15 15:38:20');
INSERT INTO `audit_logs` VALUES (621,2,'Logout','Staff giogmarquez logged out','2026-05-15 15:38:22');
INSERT INTO `audit_logs` VALUES (622,2,'Login','Staff logged in (giogmarquez)','2026-05-15 15:38:32');
INSERT INTO `audit_logs` VALUES (623,2,'Logout','Staff giogmarquez logged out','2026-05-15 15:38:50');
INSERT INTO `audit_logs` VALUES (624,2,'Login','Staff logged in (giogmarquez)','2026-05-15 15:38:56');
INSERT INTO `audit_logs` VALUES (625,2,'Start Boarding','Start Boarding for sdaasd (ORMOC).','2026-05-15 15:38:59');
INSERT INTO `audit_logs` VALUES (626,2,'Logout','Staff giogmarquez logged out','2026-05-15 15:39:00');
INSERT INTO `audit_logs` VALUES (627,1,'Login','Admin logged in (admin123)','2026-05-15 15:40:32');
INSERT INTO `audit_logs` VALUES (628,1,'Logout','Admin admin123 logged out','2026-05-15 15:42:09');
INSERT INTO `audit_logs` VALUES (629,1,'Login','Admin logged in (admin123)','2026-05-15 19:39:20');
INSERT INTO `audit_logs` VALUES (630,1,'Logout','Admin admin123 logged out','2026-05-15 19:39:47');
INSERT INTO `audit_logs` VALUES (631,5,'Login','Staff logged in (jay)','2026-05-15 19:39:54');
INSERT INTO `audit_logs` VALUES (632,5,'Logout','Staff jay logged out','2026-05-15 19:40:35');
INSERT INTO `audit_logs` VALUES (633,1,'Login','Admin logged in (admin123)','2026-05-15 19:40:40');
INSERT INTO `audit_logs` VALUES (634,2,'Login','Staff logged in (giogmarquez)','2026-05-15 19:54:04');
INSERT INTO `audit_logs` VALUES (635,1,'Logout','Admin admin123 logged out','2026-05-15 20:15:43');
INSERT INTO `audit_logs` VALUES (636,5,'Login','Staff logged in (jay)','2026-05-15 20:15:52');
INSERT INTO `audit_logs` VALUES (637,5,'Logout','Staff jay logged out','2026-05-15 21:23:33');
INSERT INTO `audit_logs` VALUES (638,2,'Logout','Staff giogmarquez logged out','2026-05-15 21:29:28');
INSERT INTO `audit_logs` VALUES (639,1,'Login','Admin logged in (admin123)','2026-05-15 21:37:47');
INSERT INTO `audit_logs` VALUES (640,1,'Create route','ORMOC → ORMOC (van, ₱100).','2026-05-15 21:38:36');
INSERT INTO `audit_logs` VALUES (641,1,'Create route','ORMOC → ORMOC (jeepney, ₱1000).','2026-05-15 21:38:54');
INSERT INTO `audit_logs` VALUES (642,1,'Create route','ORMOC → SAN ISIDRO (van, ₱12311).','2026-05-15 21:39:17');
INSERT INTO `audit_logs` VALUES (643,1,'Logout','Admin admin123 logged out','2026-05-15 21:39:19');
INSERT INTO `audit_logs` VALUES (644,1,'Login','Admin logged in (admin123)','2026-05-15 21:40:15');
INSERT INTO `audit_logs` VALUES (645,1,'Create route','ORMOC → SAN ISIDRO (jeepney, ₱122).','2026-05-15 21:42:21');
INSERT INTO `audit_logs` VALUES (646,1,'Logout','Admin admin123 logged out','2026-05-15 21:42:22');
INSERT INTO `audit_logs` VALUES (647,1,'Login','Admin logged in (admin123)','2026-05-15 21:42:39');
INSERT INTO `audit_logs` VALUES (648,1,'Update discount','pwd discount updated to 19.97%.','2026-05-15 21:46:19');
INSERT INTO `audit_logs` VALUES (649,1,'Update discount','pwd discount updated to 30%.','2026-05-15 21:46:34');
INSERT INTO `audit_logs` VALUES (650,1,'Login','Admin logged in (admin123)','2026-05-16 07:41:05');
INSERT INTO `audit_logs` VALUES (651,1,'Logout','Admin admin123 logged out','2026-05-16 07:41:54');
INSERT INTO `audit_logs` VALUES (652,5,'Login','Staff logged in (jay)','2026-05-16 07:42:12');
INSERT INTO `audit_logs` VALUES (653,5,'Update discount','pwd discount updated to 40%.','2026-05-16 07:46:38');
INSERT INTO `audit_logs` VALUES (654,5,'Start Boarding','Start Boarding for HELO123 (TACLOBAN).','2026-05-16 07:46:56');
INSERT INTO `audit_logs` VALUES (655,5,'Logout','Staff jay logged out','2026-05-16 08:04:03');
INSERT INTO `audit_logs` VALUES (656,5,'Login','Staff logged in (jay)','2026-05-16 08:04:16');
INSERT INTO `audit_logs` VALUES (657,1,'Login','Admin logged in (admin123)','2026-05-16 08:08:14');
INSERT INTO `audit_logs` VALUES (658,1,'Delete route','ORMOC → ORMOC.','2026-05-16 08:08:31');
INSERT INTO `audit_logs` VALUES (659,1,'Create route','PALOMPON → ORMOC (jeepney, ₱1212).','2026-05-16 08:08:55');
INSERT INTO `audit_logs` VALUES (660,1,'Delete route','ORMOC → ORMOC.','2026-05-16 08:08:59');
INSERT INTO `audit_logs` VALUES (661,1,'Logout','Admin admin123 logged out','2026-05-16 08:09:09');
INSERT INTO `audit_logs` VALUES (662,5,'Login','Staff logged in (jay)','2026-05-16 08:09:16');
INSERT INTO `audit_logs` VALUES (663,5,'Add to queue','Added 726 HOF to queue for SAN ISIDRO.','2026-05-16 08:09:52');
INSERT INTO `audit_logs` VALUES (664,5,'Logout','Staff jay logged out','2026-05-16 08:09:57');
INSERT INTO `audit_logs` VALUES (665,1,'Login','Admin logged in (admin123)','2026-05-16 08:10:33');
INSERT INTO `audit_logs` VALUES (666,5,'Logout','Staff jay logged out','2026-05-16 08:17:55');
INSERT INTO `audit_logs` VALUES (667,1,'Login','Admin logged in (admin123)','2026-05-16 08:18:14');
INSERT INTO `audit_logs` VALUES (668,1,'Create route','PALOMPON → CALUBIAN (van, ₱100).','2026-05-16 08:19:12');
INSERT INTO `audit_logs` VALUES (669,1,'Create route','PALOMPON → BOGO (jeepney, ₱150).','2026-05-16 08:23:03');
INSERT INTO `audit_logs` VALUES (670,1,'Create route','PALOMPON → VILLABA (van, ₱50).','2026-05-16 08:27:05');
INSERT INTO `audit_logs` VALUES (671,1,'Logout','Admin admin123 logged out','2026-05-16 08:50:27');
INSERT INTO `audit_logs` VALUES (672,5,'Login','Staff logged in (jay)','2026-05-16 08:50:51');
INSERT INTO `audit_logs` VALUES (673,5,'Add to queue','Added 999 999 to queue for ORMOC. Rule: Morning Rush (30 min).','2026-05-16 08:52:22');
INSERT INTO `audit_logs` VALUES (674,5,'Logout','Staff jay logged out','2026-05-16 08:53:45');
INSERT INTO `audit_logs` VALUES (675,1,'Login','Admin logged in (admin123)','2026-05-16 08:54:02');
INSERT INTO `audit_logs` VALUES (676,1,'Update departure rule','Updated departure rule: Late Night / Early Morning. Before: 60 min (12:00 AM–5:00 AM). After: 65 min (12:00 AM–5:00 AM).','2026-05-16 09:06:15');
INSERT INTO `audit_logs` VALUES (677,1,'Update departure rule','Updated departure rule: Late Night / Early Morning. Before: 65 min (12:00 AM–5:00 AM). After: 60 min (12:00 AM–5:00 AM).','2026-05-16 09:06:24');
INSERT INTO `audit_logs` VALUES (678,1,'Login','Admin logged in (admin123)','2026-05-16 09:13:37');
INSERT INTO `audit_logs` VALUES (679,1,'Delete announcement','ID 3: tyhfghcgh','2026-05-16 09:13:43');
INSERT INTO `audit_logs` VALUES (680,1,'Delete departure rule','Deleted departure rule: 21:00:00 - 23:59:59.','2026-05-16 09:13:50');
INSERT INTO `audit_logs` VALUES (681,1,'Create departure rule','Added departure rule: Unlabeled (9:14 PM – 9:16 PM, 30 min).','2026-05-16 09:14:14');
INSERT INTO `audit_logs` VALUES (682,1,'Update departure rule','Updated departure rule: #8. Before: 30 min (9:14 PM–9:16 PM). After: 60 min (9:00 PM–11:59 PM).','2026-05-16 09:15:09');
INSERT INTO `audit_logs` VALUES (683,5,'Login','Staff logged in (jay)','2026-05-16 09:15:49');
INSERT INTO `audit_logs` VALUES (684,5,'Depart Vehicle','Depart Vehicle for HELO123 (TACLOBAN).','2026-05-16 09:16:15');
INSERT INTO `audit_logs` VALUES (685,5,'Start Boarding','Start Boarding for 726 HOF (SAN ISIDRO).','2026-05-16 09:16:18');
INSERT INTO `audit_logs` VALUES (686,5,'Logout','Staff jay logged out','2026-05-16 09:37:55');
INSERT INTO `audit_logs` VALUES (687,1,'Update dispatcher routes','Updated dispatcher \"giogmarquez\". Added: PALOMPON → BOGO, PALOMPON → CALUBIAN.','2026-05-16 09:39:03');
INSERT INTO `audit_logs` VALUES (688,1,'Logout','Admin admin123 logged out','2026-05-16 09:39:47');
INSERT INTO `audit_logs` VALUES (689,2,'Login','Staff logged in (giogmarquez)','2026-05-16 09:40:20');
INSERT INTO `audit_logs` VALUES (690,1,'Logout','Admin admin123 logged out','2026-05-16 09:40:52');
INSERT INTO `audit_logs` VALUES (691,5,'Login','Staff logged in (jay)','2026-05-16 09:40:59');
INSERT INTO `audit_logs` VALUES (692,2,'Logout','Staff giogmarquez logged out','2026-05-16 09:41:09');
INSERT INTO `audit_logs` VALUES (693,5,'Logout','Staff jay logged out','2026-05-16 09:41:21');
INSERT INTO `audit_logs` VALUES (694,1,'Login','Admin logged in (admin123)','2026-05-16 09:42:37');
INSERT INTO `audit_logs` VALUES (695,1,'Login','Admin logged in (admin123)','2026-05-16 09:44:57');
INSERT INTO `audit_logs` VALUES (696,1,'Logout','Admin admin123 logged out','2026-05-16 09:45:28');
INSERT INTO `audit_logs` VALUES (697,1,'Login','Admin logged in (admin123)','2026-05-16 09:45:34');
INSERT INTO `audit_logs` VALUES (698,1,'Update dispatcher routes','Updated dispatcher \"jaylo\". Added: PALOMPON → BOGO, PALOMPON → ORMOC.','2026-05-16 09:45:46');
INSERT INTO `audit_logs` VALUES (699,1,'Update dispatcher routes','Updated dispatcher \"markjade\". Added: PALOMPON → ORMOC, PALOMPON → SAN ISIDRO.','2026-05-16 09:45:51');
INSERT INTO `audit_logs` VALUES (700,1,'Update dispatcher routes','Updated dispatcher \"jay\". Added: PALOMPON → VILLABA.','2026-05-16 09:45:55');
INSERT INTO `audit_logs` VALUES (701,1,'Update dispatcher routes','Updated dispatcher \"haha\". Added: PALOMPON → TACLOBAN, PALOMPON → VILLABA.','2026-05-16 09:46:01');
INSERT INTO `audit_logs` VALUES (702,1,'Update dispatcher routes','Updated dispatcher \"gojosaturo\". Added: PALOMPON → SAN ISIDRO, PALOMPON → TACLOBAN.','2026-05-16 09:46:18');
INSERT INTO `audit_logs` VALUES (703,1,'Logout','Admin admin123 logged out','2026-05-16 09:46:20');
INSERT INTO `audit_logs` VALUES (704,1,'Login','Admin logged in (admin123)','2026-05-16 09:46:25');
INSERT INTO `audit_logs` VALUES (705,1,'Delete route','ORMOC → SAN ISIDRO.','2026-05-16 09:46:31');
INSERT INTO `audit_logs` VALUES (706,1,'Delete route','ORMOC → SAN ISIDRO.','2026-05-16 09:46:35');
INSERT INTO `audit_logs` VALUES (707,1,'Logout','Admin admin123 logged out','2026-05-16 09:46:48');
INSERT INTO `audit_logs` VALUES (708,5,'Login','Staff logged in (jay)','2026-05-16 09:46:54');
INSERT INTO `audit_logs` VALUES (709,5,'Logout','Staff jay logged out','2026-05-16 09:47:17');
INSERT INTO `audit_logs` VALUES (710,1,'Login','Admin logged in (admin123)','2026-05-16 09:47:22');
INSERT INTO `audit_logs` VALUES (711,1,'Update dispatcher routes','Updated dispatcher \"jay\". Added: PALOMPON → TACLOBAN.','2026-05-16 09:47:33');
INSERT INTO `audit_logs` VALUES (712,1,'Logout','Admin admin123 logged out','2026-05-16 09:47:35');
INSERT INTO `audit_logs` VALUES (713,5,'Login','Staff logged in (jay)','2026-05-16 09:47:40');
INSERT INTO `audit_logs` VALUES (714,5,'Logout','Staff jay logged out','2026-05-16 09:49:41');
INSERT INTO `audit_logs` VALUES (715,1,'Logout','Admin admin123 logged out','2026-05-16 09:50:38');
INSERT INTO `audit_logs` VALUES (716,5,'Login','Staff logged in (jay)','2026-05-16 09:50:48');
INSERT INTO `audit_logs` VALUES (717,5,'Login','Staff logged in (jay)','2026-05-16 09:50:49');
INSERT INTO `audit_logs` VALUES (718,1,'Login','Admin logged in (admin123)','2026-05-16 09:52:00');
INSERT INTO `audit_logs` VALUES (719,1,'Logout','Admin admin123 logged out','2026-05-16 09:53:34');
INSERT INTO `audit_logs` VALUES (720,1,'Login','Admin logged in (admin123)','2026-05-16 09:53:55');
INSERT INTO `audit_logs` VALUES (721,5,'Login','Staff logged in (jay)','2026-05-16 09:54:06');
INSERT INTO `audit_logs` VALUES (722,5,'Logout','Staff jay logged out','2026-05-16 09:58:25');
INSERT INTO `audit_logs` VALUES (723,1,'Create route','PALOMPON → VILLABA (jeepney, ₱50).','2026-05-16 09:58:40');
INSERT INTO `audit_logs` VALUES (724,1,'Logout','Admin admin123 logged out','2026-05-16 09:58:50');
INSERT INTO `audit_logs` VALUES (725,5,'Login','Staff logged in (jay)','2026-05-16 09:59:29');
INSERT INTO `audit_logs` VALUES (726,1,'Login','Admin logged in (admin123)','2026-05-16 10:01:59');
INSERT INTO `audit_logs` VALUES (727,5,'Logout','Staff jay logged out','2026-05-16 10:17:38');
INSERT INTO `audit_logs` VALUES (728,1,'Login','Admin logged in (admin123)','2026-05-16 10:17:49');
INSERT INTO `audit_logs` VALUES (729,1,'Update dispatcher routes','Updated dispatcher \"jay\". Added: PALOMPON → VILLABA.','2026-05-16 10:20:14');
INSERT INTO `audit_logs` VALUES (730,1,'Logout','Admin admin123 logged out','2026-05-16 10:29:11');
INSERT INTO `audit_logs` VALUES (731,5,'Add to queue','Added 666 666 to queue for TACLOBAN. Rule: Mid-Morning (40 min).','2026-05-16 10:30:45');
INSERT INTO `audit_logs` VALUES (732,5,'Start Boarding','Start Boarding for 666 666 (TACLOBAN).','2026-05-16 10:30:56');
INSERT INTO `audit_logs` VALUES (733,1,'Login','Admin logged in (admin123)','2026-05-16 10:32:30');
INSERT INTO `audit_logs` VALUES (734,1,'Update dispatcher routes','Updated dispatcher \"jay\". Added: PALOMPON → ORMOC, PALOMPON → ORMOC.','2026-05-16 10:47:26');
INSERT INTO `audit_logs` VALUES (735,1,'Logout','Admin admin123 logged out','2026-05-16 10:54:11');
INSERT INTO `audit_logs` VALUES (736,5,'Logout','Staff jay logged out','2026-05-16 11:00:29');
INSERT INTO `audit_logs` VALUES (737,1,'Login','Admin logged in (admin123)','2026-05-16 11:00:35');
INSERT INTO `audit_logs` VALUES (738,1,'Register vehicle','Registered vehicle 123245 (jeepney) - Driver: dfdfd - Route: PALOMPON → VILLABA','2026-05-16 11:01:11');
INSERT INTO `audit_logs` VALUES (739,5,'Login','Staff logged in (jay)','2026-05-16 11:01:36');
INSERT INTO `audit_logs` VALUES (740,5,'Depart Vehicle','Depart Vehicle for 666 666 (TACLOBAN).','2026-05-16 11:02:00');
INSERT INTO `audit_logs` VALUES (741,5,'Start Boarding','Start Boarding for 999 999 (ORMOC).','2026-05-16 11:02:01');
INSERT INTO `audit_logs` VALUES (742,5,'Depart Vehicle','Depart Vehicle for 999 999 (ORMOC).','2026-05-16 11:02:03');
INSERT INTO `audit_logs` VALUES (743,5,'Add to queue','Added 123245 to queue for VILLABA. Rule: Mid-Morning (40 min).','2026-05-16 11:02:16');
INSERT INTO `audit_logs` VALUES (744,5,'Depart Vehicle','Depart Vehicle for sdaasd (ORMOC).','2026-05-16 11:06:09');
INSERT INTO `audit_logs` VALUES (745,5,'Add to queue','Added 666 666 to queue for TACLOBAN. Rule: Mid-Morning (40 min).','2026-05-16 11:06:18');
INSERT INTO `audit_logs` VALUES (746,5,'Logout','Staff jay logged out','2026-05-16 11:06:23');
INSERT INTO `audit_logs` VALUES (747,1,'Logout','Admin admin123 logged out','2026-05-16 11:59:33');
INSERT INTO `audit_logs` VALUES (748,5,'Login','Staff logged in (jay)','2026-05-16 11:59:40');
INSERT INTO `audit_logs` VALUES (749,5,'Logout','Staff jay logged out','2026-05-16 12:00:00');
INSERT INTO `audit_logs` VALUES (750,1,'Login','Admin logged in (admin123)','2026-05-16 12:00:09');
INSERT INTO `audit_logs` VALUES (751,1,'Login','Admin logged in (admin123)','2026-05-16 12:11:48');
INSERT INTO `audit_logs` VALUES (752,1,'Update vehicle','Updated vehicle 123245','2026-05-16 12:18:33');
INSERT INTO `audit_logs` VALUES (753,1,'Update vehicle','Updated vehicle 123245','2026-05-16 12:20:25');
INSERT INTO `audit_logs` VALUES (754,1,'Update vehicle','Updated vehicle 123245','2026-05-16 12:22:08');
INSERT INTO `audit_logs` VALUES (755,1,'Update vehicle','Updated vehicle 123245','2026-05-16 12:23:33');
INSERT INTO `audit_logs` VALUES (756,1,'Assign vehicle to route','Registered vehicle TEST-777 (van) - Driver: Test Driver Seven - Route: PALOMPON → CALUBIAN','2026-05-16 12:27:49');
INSERT INTO `audit_logs` VALUES (757,1,'Update vehicle','Updated vehicle 123245','2026-05-16 12:28:58');
INSERT INTO `audit_logs` VALUES (758,1,'Reassign vehicle route','Reassigned 123245 from PALOMPON → VILLABA to PALOMPON → BOGO','2026-05-16 12:33:31');
INSERT INTO `audit_logs` VALUES (759,1,'Update vehicle','Updated vehicle 123245','2026-05-16 12:35:38');
INSERT INTO `audit_logs` VALUES (760,1,'Update vehicle','Updated vehicle TEST-OLD','2026-05-16 12:40:04');
INSERT INTO `audit_logs` VALUES (761,1,'Assign vehicle to route','Registered vehicle TEST-777 (van) - Driver: Test Driver Seven - Route: PALOMPON → CALUBIAN','2026-05-16 12:41:34');
INSERT INTO `audit_logs` VALUES (762,1,'Logout','Admin admin123 logged out','2026-05-16 12:45:02');
INSERT INTO `audit_logs` VALUES (763,5,'Login','Staff logged in (jay)','2026-05-16 12:45:26');
INSERT INTO `audit_logs` VALUES (764,5,'Logout','Staff jay logged out','2026-05-16 12:45:36');
INSERT INTO `audit_logs` VALUES (765,5,'Login','Staff logged in (jay)','2026-05-16 12:49:56');
INSERT INTO `audit_logs` VALUES (766,5,'Logout','Staff jay logged out','2026-05-16 12:50:32');
INSERT INTO `audit_logs` VALUES (767,5,'Login','Staff logged in (jay)','2026-05-16 12:53:28');
INSERT INTO `audit_logs` VALUES (768,5,'Logout','Staff jay logged out','2026-05-16 12:53:33');
INSERT INTO `audit_logs` VALUES (769,5,'Login','Staff logged in (jay)','2026-05-16 12:53:50');
INSERT INTO `audit_logs` VALUES (770,5,'Logout','Staff jay logged out','2026-05-16 12:54:02');
INSERT INTO `audit_logs` VALUES (771,5,'Login','Staff logged in (jay)','2026-05-16 12:54:43');
INSERT INTO `audit_logs` VALUES (772,5,'Logout','Staff jay logged out','2026-05-16 12:54:58');
INSERT INTO `audit_logs` VALUES (773,5,'Login','Staff logged in (jay)','2026-05-16 12:55:48');
INSERT INTO `audit_logs` VALUES (774,5,'Logout','Staff jay logged out','2026-05-16 12:56:21');
INSERT INTO `audit_logs` VALUES (775,5,'Login','Staff logged in (jay)','2026-05-16 12:57:13');
INSERT INTO `audit_logs` VALUES (776,5,'Logout','Staff jay logged out','2026-05-16 12:57:36');
INSERT INTO `audit_logs` VALUES (777,5,'Login','Staff logged in (jay)','2026-05-16 12:59:12');
INSERT INTO `audit_logs` VALUES (778,5,'Logout','Staff jay logged out','2026-05-16 12:59:31');
INSERT INTO `audit_logs` VALUES (779,1,'Login','Admin logged in (admin123)','2026-05-16 13:06:53');
INSERT INTO `audit_logs` VALUES (780,1,'Logout','Admin admin123 logged out','2026-05-16 13:07:51');
INSERT INTO `audit_logs` VALUES (781,1,'Delete vehicle','Deleted vehicle 123245.','2026-05-16 13:08:10');
INSERT INTO `audit_logs` VALUES (782,1,'Logout','Admin admin123 logged out','2026-05-16 13:14:41');
INSERT INTO `audit_logs` VALUES (783,1,'Login','Admin logged in (admin123)','2026-05-22 19:58:34');
INSERT INTO `audit_logs` VALUES (784,1,'Logout','Admin admin123 logged out','2026-05-22 19:59:20');
INSERT INTO `audit_logs` VALUES (785,5,'Login','Staff logged in (jay)','2026-05-22 19:59:32');
INSERT INTO `audit_logs` VALUES (786,5,'Logout','Staff jay logged out','2026-05-22 20:03:49');
INSERT INTO `audit_logs` VALUES (787,1,'Login','Admin logged in (admin123)','2026-05-22 20:03:56');
INSERT INTO `audit_logs` VALUES (788,1,'Logout','Admin admin123 logged out','2026-05-22 20:04:54');
INSERT INTO `audit_logs` VALUES (789,5,'Login','Staff logged in (jay)','2026-05-22 20:04:59');
INSERT INTO `audit_logs` VALUES (790,5,'Logout','Staff jay logged out','2026-05-22 20:17:55');
INSERT INTO `audit_logs` VALUES (791,1,'Login','Admin logged in (admin123)','2026-05-22 20:18:01');
INSERT INTO `audit_logs` VALUES (792,1,'Logout','Admin admin123 logged out','2026-05-22 20:37:34');
INSERT INTO `audit_logs` VALUES (793,1,'Login','Admin logged in (admin123)','2026-05-22 20:56:07');
INSERT INTO `audit_logs` VALUES (794,1,'Update fare','PALOMPON TO BOGO (JEEPNEY) route fare changed. Before: PHP 150.00 | After: PHP 190.00.','2026-05-22 20:56:17');
INSERT INTO `audit_logs` VALUES (795,1,'Update route','PALOMPON to 180 (jeepney).','2026-05-22 20:56:35');
INSERT INTO `audit_logs` VALUES (796,1,'Update route','PALOMPON to TACLOBAN (jeepney).','2026-05-22 20:57:17');
INSERT INTO `audit_logs` VALUES (797,1,'Update fare','PALOMPON TO TACLOBAN (JEEPNEY) route fare changed. Before: PHP 190.00 | After: PHP 200.00.','2026-05-22 20:57:28');
INSERT INTO `audit_logs` VALUES (798,1,'Update route','PALOMPON to MAMAM (van).','2026-05-22 21:00:24');
INSERT INTO `audit_logs` VALUES (799,1,'Update discount','senior_citizen discount updated to 60%.','2026-05-22 21:01:07');
INSERT INTO `audit_logs` VALUES (800,1,'Logout','Admin admin123 logged out','2026-05-22 21:10:25');
INSERT INTO `audit_logs` VALUES (801,5,'Login','Staff logged in (jay)','2026-05-22 21:10:36');
INSERT INTO `audit_logs` VALUES (802,5,'Logout','Staff jay logged out','2026-05-22 21:11:07');
INSERT INTO `audit_logs` VALUES (803,1,'Login','Admin logged in (admin123)','2026-05-22 21:14:34');
INSERT INTO `audit_logs` VALUES (804,1,'Update route','PALOMPON to IS IT ME YOUR LOOKING FOR YEZZER (van).','2026-05-22 21:41:07');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026-02-03-144119','App\\Database\\Migrations\\InitialSchema','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (2,'2026-02-06-103530','App\\Database\\Migrations\\AddCurrentPassengersToQueue','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (3,'2026-02-06-104947','App\\Database\\Migrations\\AddVehicleTypeToRoutes','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (4,'2026-02-06-121011','App\\Database\\Migrations\\AddDefaultRouteToVehicles','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (5,'2026-02-08-100000','App\\Database\\Migrations\\CreateAnnouncementsTable','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (6,'2026-03-24-131600','App\\Database\\Migrations\\RemoveOperatorSimplifyVehicles','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (7,'2026-05-07-100000','App\\Database\\Migrations\\AddEstimatedDepartureToQueue','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (8,'2026-05-07-100100','App\\Database\\Migrations\\CreateDepartureRulesTable','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (9,'2026-05-07-100200','App\\Database\\Migrations\\CreateFareDiscountsTable','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (10,'2026-05-07-100300','App\\Database\\Migrations\\CleanupUsersRoleEnum','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (11,'2026-05-07-100400','App\\Database\\Migrations\\AddQueueIndexes','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (12,'2026-05-15-150000','App\\Database\\Migrations\\MoveRouteFareToFaresTable','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (13,'2026-05-15-180000','App\\Database\\Migrations\\MergeFaresBackIntoRoutes','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (14,'2026-05-16-000000','App\\Database\\Migrations\\DropTripStatusHistoryTable','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (15,'2026-05-16-100000','App\\Database\\Migrations\\CreateUserRoutesTable','default','App',1779448957,1);
INSERT INTO `migrations` VALUES (16,'2026-05-22-200000','App\\Database\\Migrations\\RedesignFaresAndConfigs','default','App',1779449014,2);
INSERT INTO `migrations` VALUES (17,'2026-05-22-201000','App\\Database\\Migrations\\HardenFareRedesignTerminalIndexes','default','App',1779451028,3);
INSERT INTO `migrations` VALUES (18,'2026-05-22-202000','App\\Database\\Migrations\\NormalizeRouteOriginsFromTerminals','default','App',1779452161,4);
INSERT INTO `migrations` VALUES (19,'2026-05-22-203000','App\\Database\\Migrations\\RemoveRouteOriginUseTerminal','default','App',1779454489,5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `queue` WRITE;
/*!40000 ALTER TABLE `queue` DISABLE KEYS */;
INSERT INTO `queue` VALUES (70,2,1,'departed',16,0,'2026-05-15 15:09:35','2026-05-15 15:39:35','2026-05-15 15:38:05');
INSERT INTO `queue` VALUES (71,8,1,'departed',23,0,'2026-05-15 15:38:13','2026-05-15 16:08:13','2026-05-16 11:06:09');
INSERT INTO `queue` VALUES (72,9,2,'departed',20,0,'2026-05-15 15:38:20','2026-05-15 16:08:20','2026-05-16 09:16:15');
INSERT INTO `queue` VALUES (73,2,4,'boarding',0,1,'2026-05-16 08:09:52','2026-05-16 08:39:52',NULL);
INSERT INTO `queue` VALUES (74,7,1,'departed',0,0,'2026-05-16 08:52:22','2026-05-16 09:22:22','2026-05-16 11:02:03');
INSERT INTO `queue` VALUES (75,6,7,'departed',13,0,'2026-05-16 10:30:44','2026-05-16 11:10:44','2026-05-16 11:02:00');
INSERT INTO `queue` VALUES (77,6,7,'waiting',0,3,'2026-05-16 11:06:18','2026-05-16 11:46:18',NULL);
/*!40000 ALTER TABLE `queue` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `routes` WRITE;
/*!40000 ALTER TABLE `routes` DISABLE KEYS */;
INSERT INTO `routes` VALUES (1,1,'ORMOC','van','2026-02-06 04:58:59');
INSERT INTO `routes` VALUES (2,1,'TACLOBAN','van','2026-02-06 05:33:02');
INSERT INTO `routes` VALUES (3,1,'JORDAN','van','2026-02-08 15:50:51');
INSERT INTO `routes` VALUES (4,1,'SAN ISIDRO','jeepney','2026-02-08 16:18:42');
INSERT INTO `routes` VALUES (7,1,'TACLOBAN','minibus','2026-02-17 21:29:37');
INSERT INTO `routes` VALUES (8,1,'ORMOC','minibus','2026-02-17 21:46:07');
INSERT INTO `routes` VALUES (13,1,'ORMOC','jeepney','2026-05-16 08:08:55');
INSERT INTO `routes` VALUES (14,1,'IS IT ME YOUR LOOKING FOR YEZZER','van','2026-05-16 08:19:12');
INSERT INTO `routes` VALUES (15,1,'TACLOBAN','jeepney','2026-05-16 08:23:03');
INSERT INTO `routes` VALUES (16,1,'VILLABA','van','2026-05-16 08:27:05');
INSERT INTO `routes` VALUES (17,1,'VILLABA','jeepney','2026-05-16 09:58:40');
/*!40000 ALTER TABLE `routes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `terminals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `terminals` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `terminals` WRITE;
/*!40000 ALTER TABLE `terminals` DISABLE KEYS */;
INSERT INTO `terminals` VALUES (1,'PALOMPON','PALOMPON, LEYTE',50,'2026-02-06 04:58:42');
/*!40000 ALTER TABLE `terminals` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `user_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `user_routes` WRITE;
/*!40000 ALTER TABLE `user_routes` DISABLE KEYS */;
INSERT INTO `user_routes` VALUES (3,2,15,'2026-05-16 09:39:39');
INSERT INTO `user_routes` VALUES (4,2,14,'2026-05-16 09:39:39');
INSERT INTO `user_routes` VALUES (7,4,8,'2026-05-16 09:45:51');
INSERT INTO `user_routes` VALUES (8,4,4,'2026-05-16 09:45:51');
INSERT INTO `user_routes` VALUES (12,7,4,'2026-05-16 09:46:18');
INSERT INTO `user_routes` VALUES (13,7,2,'2026-05-16 09:46:18');
INSERT INTO `user_routes` VALUES (16,3,15,'2026-05-16 09:53:27');
INSERT INTO `user_routes` VALUES (17,3,1,'2026-05-16 09:53:27');
INSERT INTO `user_routes` VALUES (21,5,1,'2026-05-16 10:47:26');
INSERT INTO `user_routes` VALUES (22,5,8,'2026-05-16 10:47:26');
INSERT INTO `user_routes` VALUES (23,5,7,'2026-05-16 10:47:26');
INSERT INTO `user_routes` VALUES (24,5,16,'2026-05-16 10:47:26');
INSERT INTO `user_routes` VALUES (25,5,17,'2026-05-16 10:47:26');
INSERT INTO `user_routes` VALUES (26,6,2,'2026-05-16 12:32:08');
INSERT INTO `user_routes` VALUES (27,6,16,'2026-05-16 12:32:08');
/*!40000 ALTER TABLE `user_routes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `full_name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin123','$2y$10$CiJRci4T78G/Dndn3NAWjeqcSRnl02X6/Ejmanv9TvmdcSQxpwSOy','admin','System Administrator','2026-02-06 04:19:50','2026-03-24 22:14:41');
INSERT INTO `users` VALUES (2,'giogmarquez','$2y$10$7fdRDIG7zef0wQndgUr1PetdTX1FXYdxL4pfKsBpRH7bu.Ji7M09C','staff','Gio Marquez','2026-02-06 04:54:23','2026-05-16 09:39:39');
INSERT INTO `users` VALUES (3,'jaylo','$2y$10$xmnKLUEPHj0STVQFQwmKo.aQ6uMUkm2jJS3nIMO1D8NCYFhbj0Uk2','staff','Jaylo Terrado','2026-02-06 05:37:24','2026-05-22 22:07:32');
INSERT INTO `users` VALUES (4,'markjade','$2y$10$MDysPLE5Y2xii3Xr0W.0TOJHsji7ssEkA14WBuCPx.d6XsB.cElaW','staff','Mark Jade Devota','2026-02-06 05:57:41','2026-05-16 09:45:51');
INSERT INTO `users` VALUES (5,'jay','$2y$10$bxmdk4JnbvNu0HvE4PIXBumjo7PlsxsnpmO7LBvPeRfNSRV7TSiLC','staff','jaylo','2026-02-08 11:49:08','2026-05-16 10:47:26');
INSERT INTO `users` VALUES (6,'haha','$2y$10$PVX3783A9siq6ahTCS81Heag7QX/SwP8GEDkjPWqm6t3Gmf/dqNVa','staff','haha','2026-02-08 16:07:08','2026-05-16 09:46:01');
INSERT INTO `users` VALUES (7,'gojosaturo','$2y$10$4Oy3OXHjGVTU3bi5dmyptued4IVnbLhsoVAt3VhjKtZdBSi/xfb1a','staff','Gojo Saturo','2026-02-28 11:17:31','2026-05-16 09:46:18');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES (2,'726 HOF','Mark Jade Devota','jeepney',16,'Mark Jade Devota','active',15,'2026-02-06 05:33:27');
INSERT INTO `vehicles` VALUES (4,'112ef','jaylo','jeepney',14,'jaylo','active',4,'2026-02-08 15:24:19');
INSERT INTO `vehicles` VALUES (6,'666 666','Gojo Saturo','minibus',30,'Gojo Saturo','active',7,'2026-02-28 11:19:46');
INSERT INTO `vehicles` VALUES (7,'999 999','jaylo','van',16,'jaylo','active',1,'2026-02-28 11:40:53');
INSERT INTO `vehicles` VALUES (8,'sdaasd','ahhah','minibus',23,'','active',8,'2026-03-24 22:22:24');
INSERT INTO `vehicles` VALUES (9,'HELO123','ahhah','jeepney',20,'','active',13,'2026-04-06 11:27:07');
INSERT INTO `vehicles` VALUES (11,'TEST-OLD','Test Driver Seven','van',15,'','active',14,'2026-05-16 12:27:49');
INSERT INTO `vehicles` VALUES (12,'TEST-777','Test Driver Seven','van',15,'','active',14,'2026-05-16 12:41:34');
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

