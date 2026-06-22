-- Clean Schema for Transport Vehicle Queuing Management System
-- Generated: 2026-06-20
-- No dummy data, includes default configuration and administrator account only.

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Drop Tables in Correct Order to Avoid Constraint Issues
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `user_routes`;
DROP TABLE IF EXISTS `queue`;
DROP TABLE IF EXISTS `vehicles`;
DROP TABLE IF EXISTS `fares`;
DROP TABLE IF EXISTS `routes`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `departure_rules`;
DROP TABLE IF EXISTS `fare_discounts`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `terminals`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Table structure for table `terminals`
-- --------------------------------------------------------
CREATE TABLE `terminals` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `routes`
-- --------------------------------------------------------
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `fare_discounts`
-- --------------------------------------------------------
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `fares`
-- --------------------------------------------------------
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `announcements`
-- --------------------------------------------------------
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

-- --------------------------------------------------------
-- Table structure for table `departure_rules`
-- --------------------------------------------------------
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `vehicles`
-- --------------------------------------------------------
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `queue`
-- --------------------------------------------------------
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `password_reset_tokens`
-- --------------------------------------------------------
CREATE TABLE `password_reset_tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `token` varchar(64) NOT NULL,
  `reset_code` varchar(6) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `verified` tinyint(1) DEFAULT 0,
  `code_attempts` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `user_routes`
-- --------------------------------------------------------
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `audit_logs`
-- --------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `migrations`
-- --------------------------------------------------------
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================================
-- Seed Initial Professional & Core Configuration Data Only
-- ========================================================

-- 1. Create Default Terminal
INSERT INTO `terminals` (`id`, `name`, `location`, `capacity`, `created_at`) VALUES
(1, 'PALOMPON', 'PALOMPON, LEYTE', 50, NOW());

-- 2. Create Default Standard Discounts
INSERT INTO `fare_discounts` (`id`, `terminal_id`, `type`, `label`, `discount_percent`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'pwd', 'PWD Discount', 20.00, 1, NOW(), NOW()),
(2, 1, 'senior_citizen', 'Senior Citizen Discount', 20.00, 1, NOW(), NOW()),
(3, 1, 'student', 'Student Discount', 15.00, 1, NOW(), NOW()),
(4, 1, 'regular', 'Regular Fare', 0.00, 1, NOW(), NOW());

-- 3. Create Default Departure Rules (Standard operating hours rules)
INSERT INTO `departure_rules` (`id`, `terminal_id`, `time_from`, `time_to`, `wait_minutes`, `label`, `created_at`, `updated_at`) VALUES
(1, 1, '00:00:00', '05:00:00', 60, 'Late Night / Early Morning', NOW(), NOW()),
(2, 1, '05:00:00', '09:00:00', 30, 'Morning Rush', NOW(), NOW()),
(3, 1, '09:00:00', '12:00:00', 40, 'Mid-Morning', NOW(), NOW()),
(4, 1, '12:00:00', '15:00:00', 40, 'Afternoon', NOW(), NOW()),
(5, 1, '15:00:00', '18:00:00', 30, 'Afternoon Rush', NOW(), NOW()),
(6, 1, '18:00:00', '21:00:00', 40, 'Evening', NOW(), NOW()),
(7, 1, '21:00:00', '23:59:00', 60, 'Late Evening', NOW(), NOW());

-- 4. Create Default Administrator Account (Username: admin@gmail.com, Password: admin123, Role: super_admin)
INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `role`, `full_name`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, 'admin@gmail.com', '$2y$10$CiJRci4T78G/Dndn3NAWjeqcSRnl02X6/Ejmanv9TvmdcSQxpwSOy', 'admin@gmail.com', 'super_admin', 'System Administrator', 0, NULL, NOW(), NOW());

-- 5. Seed Core Migration Tracking (Matches DB state code)
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2026-02-03-144119', 'App\\Database\\Migrations\\InitialSchema', 'default', 'App', 1779448957, 1),
(2, '2026-02-06-103530', 'App\\Database\\Migrations\\AddCurrentPassengersToQueue', 'default', 'App', 1779448957, 1),
(3, '2026-02-06-104947', 'App\\Database\\Migrations\\AddVehicleTypeToRoutes', 'default', 'App', 1779448957, 1),
(4, '2026-02-06-121011', 'App\\Database\\Migrations\\AddDefaultRouteToVehicles', 'default', 'App', 1779448957, 1),
(5, '2026-02-08-100000', 'App\\Database\\Migrations\\CreateAnnouncementsTable', 'default', 'App', 1779448957, 1),
(6, '2026-03-24-131600', 'App\\Database\\Migrations\\RemoveOperatorSimplifyVehicles', 'default', 'App', 1779448957, 1),
(7, '2026-05-07-100000', 'App\\Database\\Migrations\\AddEstimatedDepartureToQueue', 'default', 'App', 1779448957, 1),
(8, '2026-05-07-100100', 'App\\Database\\Migrations\\CreateDepartureRulesTable', 'default', 'App', 1779448957, 1),
(9, '2026-05-07-100200', 'App\\Database\\Migrations\\CreateFareDiscountsTable', 'default', 'App', 1779448957, 1),
(10, '2026-05-07-100300', 'App\\Database\\Migrations\\CleanupUsersRoleEnum', 'default', 'App', 1779448957, 1),
(11, '2026-05-07-100400', 'App\\Database\\Migrations\\AddQueueIndexes', 'default', 'App', 1779448957, 1),
(12, '2026-05-15-150000', 'App\\Database\\Migrations\\MoveRouteFareToFaresTable', 'default', 'App', 1779448957, 1),
(13, '2026-05-15-180000', 'App\\Database\\Migrations\\MergeFaresBackIntoRoutes', 'default', 'App', 1779448957, 1),
(14, '2026-05-16-000000', 'App\\Database\\Migrations\\DropTripStatusHistoryTable', 'default', 'App', 1779448957, 1),
(15, '2026-05-16-100000', 'App\\Database\\Migrations\\CreateUserRoutesTable', 'default', 'App', 1779448957, 1),
(16, '2026-05-22-200000', 'App\\Database\\Migrations\\RedesignFaresAndConfigs', 'default', 'App', 1779449014, 2),
(17, '2026-05-22-201000', 'App\\Database\\Migrations\\HardenFareRedesignTerminalIndexes', 'default', 'App', 1779451028, 3),
(18, '2026-05-22-202000', 'App\\Database\\Migrations\\NormalizeRouteOriginsFromTerminals', 'default', 'App', 1779452161, 4),
(19, '2026-05-22-203000', 'App\\Database\\Migrations\\RemoveRouteOriginUseTerminal', 'default', 'App', 1779454489, 5),
(20, '2026-05-23-000000', 'App\\Database\\Migrations\\RenameLogsToAuditLogs', 'default', 'App', 1780479842, 6),
(21, '2026-05-31-000000', 'App\\Database\\Migrations\\AddRouteIdToDepartureRules', 'default', 'App', 1780479842, 6),
(22, '2026-06-09-000002', 'App\\Database\\Migrations\\CreatePasswordResetTokensTable', 'default', 'App', 1781015812, 7),
(23, '2026-06-20-100000', 'App\\Database\\Migrations\\AddSuperAdminRole', 'default', 'App', 1781924858, 8),
(24, '2026-06-16-000000', 'App\\Database\\Migrations\\AddLoginRateLimitToUsers', 'default', 'App', 1781929194, 7),
(25, '2026-06-16-100000', 'App\\Database\\Migrations\\CreatePasswordResetTokensTable', 'default', 'App', 1781929194, 7),
(26, '2026-06-17-000001', 'App\\Database\\Migrations\\AddEmailToUsers', 'default', 'App', 1781929194, 7),
(27, '2026-06-17-000000', 'App\\Database\\Migrations\\AddResetCodeToPasswordResetTokens', 'default', 'App', 1781929201, 9),
(28, '2026-06-17-000002', 'App\\Database\\Migrations\\AddCodeAttemptsToPasswordResetTokens', 'default', 'App', 1781929201, 9);
