-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2026 at 03:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jeepneynvans`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) UNSIGNED NOT NULL,
  `message` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `message`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(3, 'tyhfghcgh', 1, 0, '2026-03-24 22:17:49', '2026-03-24 22:17:49'),
(4, 'TESTING: Please be aware of weather conditions.', 1, 1, '2026-03-24 22:24:41', '2026-03-24 22:24:41');

-- --------------------------------------------------------

--
-- Table structure for table `departure_rules`
--

CREATE TABLE `departure_rules` (
  `id` int(11) NOT NULL,
  `time_from` time NOT NULL,
  `time_to` time NOT NULL,
  `wait_minutes` int(11) NOT NULL DEFAULT 30,
  `label` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departure_rules`
--

INSERT INTO `departure_rules` (`id`, `time_from`, `time_to`, `wait_minutes`, `label`, `created_at`, `updated_at`) VALUES
(1, '00:00:00', '05:00:00', 60, 'Late Night / Early Morning', '2026-02-11 02:06:33', '2026-02-11 02:06:33'),
(2, '05:00:00', '09:00:00', 30, 'Morning Rush', '2026-02-11 02:06:33', '2026-02-11 02:06:33'),
(3, '09:00:00', '12:00:00', 40, 'Mid-Morning', '2026-02-11 02:06:33', '2026-02-11 02:06:33'),
(4, '12:00:00', '15:00:00', 40, 'Afternoon', '2026-02-11 02:06:33', '2026-02-11 02:06:33'),
(5, '15:00:00', '18:00:00', 30, 'Afternoon Rush', '2026-02-11 02:06:33', '2026-02-11 02:06:33'),
(6, '18:00:00', '21:00:00', 40, 'Evening', '2026-02-11 02:06:33', '2026-02-11 02:06:33'),
(7, '21:00:00', '23:59:59', 60, 'Late Night', '2026-02-11 02:06:33', '2026-02-11 02:06:33');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `details`, `timestamp`) VALUES
(542, 1, 'Logout', 'Admin admin123 logged out', '2026-03-26 06:22:40'),
(543, 5, 'Login', 'Staff logged in (jay)', '2026-03-26 06:22:50'),
(544, 5, 'Logout', 'Staff jay logged out', '2026-03-26 06:23:12'),
(545, 1, 'Login', 'Admin logged in (admin123)', '2026-03-26 06:29:55'),
(546, 5, 'Login', 'Staff logged in (jay)', '2026-03-31 09:14:35'),
(547, 5, 'Logout', 'Staff jay logged out', '2026-03-31 09:15:18'),
(548, 1, 'Login', 'Admin logged in (admin123)', '2026-03-31 09:48:42'),
(549, 1, 'Logout', 'Admin admin123 logged out', '2026-03-31 09:49:14'),
(550, 1, 'Login', 'Admin logged in (admin123)', '2026-04-06 11:03:13'),
(551, 1, 'Logout', 'Admin admin123 logged out', '2026-04-06 11:03:44'),
(552, 5, 'Login', 'Staff logged in (jay)', '2026-04-06 11:03:51'),
(553, 5, 'Logout', 'Staff jay logged out', '2026-04-06 11:04:08'),
(554, 1, 'Login', 'Admin logged in (admin123)', '2026-04-06 11:16:39'),
(555, 1, 'Logout', 'Admin admin123 logged out', '2026-04-06 11:16:45'),
(556, 1, 'Login', 'Admin logged in (admin123)', '2026-04-06 11:26:46'),
(557, 1, 'Register vehicle', 'Registered vehicle HELO123 (jeepney) - Driver: ahhah', '2026-04-06 11:27:07'),
(558, 1, 'Logout', 'Admin admin123 logged out', '2026-04-06 11:37:26'),
(559, 5, 'Login', 'Staff logged in (jay)', '2026-04-06 12:03:39'),
(560, 5, 'Logout', 'Staff jay logged out', '2026-04-06 12:04:55'),
(561, 1, 'Login', 'Admin logged in (admin123)', '2026-04-06 12:05:09'),
(562, 1, 'Logout', 'Admin admin123 logged out', '2026-04-06 12:05:26'),
(563, 5, 'Login', 'Staff logged in (jay)', '2026-04-06 12:05:32'),
(564, 5, 'Logout', 'Staff jay logged out', '2026-04-06 12:05:58'),
(565, 1, 'Login', 'Admin logged in (admin123)', '2026-04-06 12:06:04'),
(566, 1, 'Logout', 'Admin admin123 logged out', '2026-04-06 12:07:34'),
(567, 5, 'Login', 'Staff logged in (jay)', '2026-04-06 12:07:39'),
(568, 5, 'Logout', 'Staff jay logged out', '2026-04-06 12:10:16'),
(569, 1, 'Login', 'Admin logged in (admin123)', '2026-04-06 12:10:30'),
(570, 1, 'Logout', 'Admin admin123 logged out', '2026-04-06 12:17:35'),
(571, 5, 'Login', 'Staff logged in (jay)', '2026-04-06 12:17:41'),
(572, 5, 'Logout', 'Staff jay logged out', '2026-04-06 12:18:09'),
(573, 1, 'Login', 'Admin logged in (admin123)', '2026-04-06 12:18:14'),
(574, 1, 'Login', 'Admin logged in (admin123)', '2026-04-06 12:19:02'),
(575, 1, 'Logout', 'Admin admin123 logged out', '2026-04-06 12:19:16'),
(576, 5, 'Login', 'Staff logged in (jay)', '2026-04-06 12:19:32'),
(577, 1, 'Logout', 'Admin admin123 logged out', '2026-04-06 12:22:36'),
(578, 1, 'Login', 'Admin logged in (admin123)', '2026-04-06 12:23:29'),
(579, 5, 'Add to queue', 'Added sdaasd to queue for TACLOBAN.', '2026-04-06 12:38:30'),
(580, 5, 'Add to queue', 'Added HELO123 to queue for TACLOBAN.', '2026-04-06 12:41:54'),
(581, 5, 'Cancel Trip', 'Cancel Trip for HELO123 (TACLOBAN).', '2026-04-06 12:42:30'),
(582, 5, 'Add to queue', 'Added HELO123 to queue for Jordan.', '2026-04-06 12:42:44'),
(583, 5, 'Cancel Trip', 'Cancel Trip for 726 HOF (ORMOC).', '2026-04-06 12:43:29'),
(584, 5, 'Add to queue', 'Added 726 HOF to queue for TACLOBAN.', '2026-04-06 12:43:44'),
(585, 5, 'Cancel Trip', 'Cancel Trip for 666 666 (TACLOBAN).', '2026-04-06 12:46:37'),
(586, 5, 'Cancel Trip', 'Cancel Trip for 666 666 (TACLOBAN).', '2026-04-06 12:46:45'),
(587, 5, 'Cancel Trip', 'Cancel Trip for 112ef (TACLOBAN).', '2026-04-06 12:46:50'),
(588, 5, 'Logout', 'Staff jay logged out', '2026-04-06 12:51:25'),
(589, 5, 'Login', 'Staff logged in (jay)', '2026-04-06 12:52:03'),
(590, 1, 'Queue Status Update', 'Queue ID 66 status changed to boarding (Admin)', '2026-04-06 12:53:44'),
(591, 1, 'Queue Status Update', 'Queue ID 68 status changed to boarding (Admin)', '2026-04-06 12:54:27'),
(592, 1, 'Queue Status Update', 'Queue ID 69 status changed to boarding (Admin)', '2026-04-06 12:54:29'),
(593, 1, 'Queue Status Update', 'Queue ID 69 status changed to boarding (Admin)', '2026-04-06 12:54:30'),
(594, 1, 'Queue Status Update', 'Queue ID 66 status changed to departed (Admin)', '2026-04-06 12:55:19'),
(595, 1, 'Queue Status Update', 'Queue ID 66 status changed to departed (Admin)', '2026-04-06 12:55:23'),
(596, 1, 'Queue Status Update', 'Queue ID 68 status changed to departed (Admin)', '2026-04-06 12:55:50'),
(597, 1, 'Logout', 'Admin admin123 logged out', '2026-04-06 13:11:28'),
(598, 1, 'Login', 'Admin logged in (admin123)', '2026-04-06 13:20:45'),
(599, 1, 'Logout', 'Admin admin123 logged out', '2026-04-06 13:20:53'),
(600, 5, 'Login', 'Staff logged in (jay)', '2026-04-06 13:20:58'),
(601, 1, 'Login', 'Admin logged in (admin123)', '2026-04-06 13:21:59');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
  `id` int(11) UNSIGNED NOT NULL,
  `vehicle_id` int(11) UNSIGNED NOT NULL,
  `route_id` int(11) UNSIGNED NOT NULL,
  `status` enum('waiting','boarding','departed','canceled') NOT NULL DEFAULT 'waiting',
  `current_passengers` int(11) NOT NULL DEFAULT 0,
  `position` int(11) NOT NULL DEFAULT 0,
  `arrival_time` datetime NOT NULL,
  `estimated_departure` datetime DEFAULT NULL,
  `departure_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `queue`
--

INSERT INTO `queue` (`id`, `vehicle_id`, `route_id`, `status`, `current_passengers`, `position`, `arrival_time`, `estimated_departure`, `departure_time`) VALUES
(3, 2, 1, 'departed', 0, 3, '2026-02-06 05:41:22', '2026-02-06 06:11:22', '2026-02-06 07:19:17'),
(4, 2, 1, 'departed', 16, 4, '2026-02-06 15:50:37', '2026-02-06 16:20:37', '2026-02-06 20:01:40'),
(5, 2, 1, 'departed', 2, 5, '2026-02-06 20:35:47', '2026-02-06 21:05:47', '2026-02-08 15:15:25'),
(8, 2, 2, 'departed', 0, 8, '2026-02-08 15:21:45', '2026-02-08 15:51:45', '2026-02-08 16:15:00'),
(9, 4, 2, 'canceled', 1, 9, '2026-02-08 15:25:59', '2026-02-08 15:55:59', NULL),
(11, 4, 3, 'departed', 5, 11, '2026-02-08 16:01:42', '2026-02-08 16:31:42', '2026-02-08 16:01:51'),
(12, 4, 3, 'departed', 0, 12, '2026-02-08 16:13:05', '2026-02-08 16:43:05', '2026-02-08 16:15:47'),
(13, 2, 2, 'departed', 11, 13, '2026-02-08 16:19:33', '2026-02-08 16:49:33', '2026-02-08 16:20:51'),
(14, 2, 4, 'departed', 2, 14, '2026-02-08 16:20:26', '2026-02-08 16:50:26', '2026-02-08 16:41:36'),
(15, 4, 2, 'canceled', 0, 15, '2026-02-08 16:42:10', '2026-02-08 17:12:10', NULL),
(18, 4, 1, 'departed', 14, 18, '2026-02-08 17:06:47', '2026-02-08 17:36:47', '2026-02-11 09:25:50'),
(19, 2, 3, 'departed', 5, 19, '2026-02-09 20:35:55', '2026-02-09 21:05:55', '2026-02-11 09:26:24'),
(23, 2, 3, 'departed', 10, 23, '2026-02-11 10:12:20', '2026-02-11 10:42:20', '2026-02-17 18:59:20'),
(24, 4, 3, 'departed', 1, 24, '2026-02-11 11:13:23', '2026-02-11 11:43:23', '2026-02-17 18:59:49'),
(25, 2, 2, 'departed', 16, 25, '2026-02-17 19:00:00', '2026-02-17 19:30:00', '2026-02-17 19:00:54'),
(26, 4, 1, 'departed', 3, 26, '2026-02-17 19:00:00', '2026-02-17 19:30:00', '2026-02-17 19:26:34'),
(27, 2, 1, 'departed', 0, 27, '2026-02-17 20:00:00', '2026-02-17 20:30:00', '2026-02-17 19:28:23'),
(29, 4, 2, 'departed', 0, 29, '2026-02-17 21:30:00', '2026-02-17 22:00:00', '2026-02-17 19:28:26'),
(30, 2, 2, 'canceled', 0, 30, '2026-02-17 19:28:00', '2026-02-17 19:58:00', NULL),
(31, 2, 1, 'departed', 16, 31, '2026-02-17 20:48:00', '2026-02-17 21:18:00', '2026-02-17 20:52:45'),
(34, 2, 2, 'departed', 5, 34, '2026-02-28 08:34:00', '2026-02-28 09:04:00', '2026-02-28 08:52:09'),
(35, 4, 2, 'departed', 6, 35, '2026-02-28 08:34:00', '2026-02-28 09:04:00', '2026-02-28 08:52:09'),
(36, 2, 1, 'departed', 16, 36, '2026-02-28 09:52:00', '2026-02-28 10:22:00', '2026-02-28 09:16:18'),
(38, 4, 2, 'departed', 14, 38, '2026-02-28 00:15:00', '2026-02-28 00:45:00', '2026-02-28 09:16:20'),
(39, 2, 1, 'canceled', 0, 39, '2026-02-28 11:34:00', '2026-02-28 12:04:00', NULL),
(40, 2, 1, 'canceled', 0, 40, '2026-02-28 10:45:31', '2026-02-28 11:15:31', NULL),
(41, 2, 1, 'departed', 0, 41, '2026-02-28 10:48:10', '2026-02-28 11:18:10', '2026-02-28 10:48:26'),
(43, 2, 2, 'canceled', 0, 43, '2026-02-28 10:55:27', '2026-02-28 11:25:27', NULL),
(44, 2, 1, 'canceled', 0, 44, '2026-02-28 10:58:49', '2026-02-28 11:28:49', NULL),
(45, 2, 1, 'departed', 3, 45, '2026-02-28 11:00:24', '2026-02-28 11:30:24', '2026-02-28 11:41:33'),
(46, 6, 1, 'departed', 2, 46, '2026-02-28 11:33:01', '2026-02-28 12:03:01', '2026-02-28 12:06:37'),
(47, 7, 1, 'departed', 2, 47, '2026-02-28 11:41:23', '2026-02-28 12:11:23', '2026-02-28 12:06:37'),
(48, 2, 1, 'departed', 16, 48, '2026-03-08 12:55:57', '2026-03-08 13:25:57', '2026-03-08 14:00:28'),
(49, 7, 2, 'departed', 16, 49, '2026-03-08 14:00:42', '2026-03-08 14:30:42', '2026-03-08 14:01:31'),
(50, 2, 2, 'departed', 16, 50, '2026-03-08 14:01:42', '2026-03-08 14:31:42', '2026-03-08 14:27:47'),
(51, 6, 2, 'departed', 30, 51, '2026-03-08 14:26:42', '2026-03-08 14:56:42', '2026-03-08 14:27:54'),
(52, 2, 1, 'departed', 1, 0, '2026-03-08 14:33:39', '2026-03-08 15:03:39', '2026-03-08 14:57:50'),
(53, 7, 3, 'departed', 0, 0, '2026-03-08 14:58:30', '2026-03-08 15:28:30', '2026-03-08 14:58:54'),
(54, 2, 1, 'departed', 0, 52, '2026-03-08 15:00:35', '2026-03-08 15:30:35', '2026-03-08 15:01:46'),
(55, 6, 1, 'departed', 25, 53, '2026-03-08 15:04:24', '2026-03-08 15:34:24', '2026-03-08 15:06:02'),
(56, 6, 2, 'departed', 30, 0, '2026-03-08 15:08:10', '2026-03-08 15:38:10', '2026-03-08 15:11:27'),
(57, 2, 1, 'departed', 0, 54, '2026-03-08 15:11:39', '2026-03-08 15:41:39', '2026-03-08 15:12:17'),
(58, 2, 2, 'departed', 16, 0, '2026-03-08 15:15:08', '2026-03-08 15:45:08', '2026-03-08 15:18:02'),
(59, 7, 7, 'departed', 0, 0, '2026-03-08 15:18:10', '2026-03-08 15:48:10', '2026-03-08 15:18:21'),
(60, 4, 7, 'departed', 0, 0, '2026-03-08 15:18:34', '2026-03-08 15:48:34', '2026-03-24 22:22:57'),
(61, 2, 1, 'departed', 0, 0, '2026-03-08 15:20:14', '2026-03-08 15:50:14', '2026-03-08 15:35:13'),
(62, 6, 8, 'departed', 0, 0, '2026-03-08 15:24:55', '2026-03-08 15:54:55', '2026-03-08 15:32:23'),
(63, 2, 1, 'canceled', 13, 1, '2026-03-24 22:16:48', '2026-03-24 22:46:48', NULL),
(64, 6, 2, 'canceled', 18, 55, '2026-03-25 09:12:29', '2026-03-25 09:42:29', NULL),
(65, 4, 2, 'canceled', 2, 56, '2026-03-25 09:16:08', '2026-03-25 09:46:08', NULL),
(66, 8, 2, 'departed', 23, 0, '2026-04-06 12:38:30', '2026-04-06 13:18:30', '2026-04-06 12:55:23'),
(67, 9, 2, 'canceled', 0, 58, '2026-04-06 12:41:54', '2026-04-06 13:21:54', NULL),
(68, 9, 3, 'departed', 20, 0, '2026-04-06 12:42:44', '2026-04-06 13:22:44', '2026-04-06 12:55:50'),
(69, 2, 2, 'boarding', 16, 1, '2026-04-06 12:43:44', '2026-04-06 13:13:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` int(11) UNSIGNED NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `vehicle_type` enum('jeepney','van','minibus') NOT NULL DEFAULT 'van',
  `terminal_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `origin`, `destination`, `fare`, `vehicle_type`, `terminal_id`, `created_at`) VALUES
(1, 'PALOMPON', 'ORMOC', 150.00, 'van', 1, '2026-02-06 04:58:59'),
(2, 'PALOMPON', 'TACLOBAN', 300.00, 'van', 1, '2026-02-06 05:33:02'),
(3, 'Palompon', 'Jordan', 10.00, 'van', 1, '2026-02-08 15:50:51'),
(4, 'Palompon', 'San Isidro', 140.00, 'jeepney', 1, '2026-02-08 16:18:42'),
(7, 'PALOMPON', 'TACLOBAN', 300.00, 'minibus', 1, '2026-02-17 21:29:37'),
(8, 'PALOMPON', 'ORMOC', 150.00, 'minibus', 1, '2026-02-17 21:46:07');

-- --------------------------------------------------------

--
-- Table structure for table `terminals`
--

CREATE TABLE `terminals` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terminals`
--

INSERT INTO `terminals` (`id`, `name`, `location`, `capacity`, `created_at`) VALUES
(1, 'PALOMPON', 'PALOMPON, LEYTE', 50, '2026-02-06 04:58:42');

-- --------------------------------------------------------

--
-- Table structure for table `trip_status_history`
--

CREATE TABLE `trip_status_history` (
  `id` int(11) UNSIGNED NOT NULL,
  `queue_id` int(11) UNSIGNED NOT NULL,
  `status` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_by_user_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_status_history`
--

INSERT INTO `trip_status_history` (`id`, `queue_id`, `status`, `timestamp`, `updated_by_user_id`) VALUES
(5, 3, 'arrived/waiting', '2026-02-06 05:41:22', 2),
(6, 3, 'boarding', '2026-02-06 05:55:59', 2),
(7, 3, 'departed', '2026-02-06 07:19:17', 2),
(10, 4, 'arrived/waiting', '2026-02-06 15:50:37', 2),
(11, 4, 'boarding', '2026-02-06 15:50:39', 2),
(12, 4, 'departed', '2026-02-06 20:01:40', 2),
(13, 5, 'arrived/waiting', '2026-02-06 20:35:47', 2),
(14, 5, 'boarding', '2026-02-06 20:35:50', 2),
(18, 5, 'departed', '2026-02-08 15:15:25', 1),
(22, 8, 'arrived/waiting', '2026-02-08 15:21:45', 1),
(23, 9, 'arrived/waiting', '2026-02-08 15:25:59', 1),
(24, 8, 'boarding', '2026-02-08 15:57:11', 1),
(25, 9, 'boarding', '2026-02-08 15:57:20', 1),
(26, 9, 'canceled', '2026-02-08 15:57:29', 1),
(28, 11, 'arrived/waiting', '2026-02-08 16:01:42', 1),
(29, 11, 'boarding', '2026-02-08 16:01:49', 1),
(30, 11, 'departed', '2026-02-08 16:01:51', 1),
(31, 12, 'arrived/waiting', '2026-02-08 16:13:05', 1),
(32, 12, 'boarding', '2026-02-08 16:13:14', 1),
(35, 8, 'departed', '2026-02-08 16:15:00', 6),
(36, 12, 'departed', '2026-02-08 16:15:47', 6),
(37, 13, 'arrived/waiting', '2026-02-08 16:19:33', 1),
(38, 14, 'arrived/waiting', '2026-02-08 16:20:26', 1),
(39, 13, 'boarding', '2026-02-08 16:20:49', 6),
(40, 13, 'departed', '2026-02-08 16:20:51', 6),
(41, 14, 'boarding', '2026-02-08 16:41:28', 1),
(42, 14, 'departed', '2026-02-08 16:41:36', 1),
(43, 15, 'arrived/waiting', '2026-02-08 16:42:10', 1),
(45, 15, 'canceled', '2026-02-08 16:45:08', 1),
(51, 18, 'arrived/waiting', '2026-02-08 17:06:47', 1),
(52, 19, 'arrived/waiting', '2026-02-09 20:35:55', 1),
(53, 18, 'boarding', '2026-02-09 20:52:25', 1),
(54, 19, 'boarding', '2026-02-09 20:54:07', 1),
(59, 18, 'departed', '2026-02-11 09:25:51', 1),
(61, 19, 'departed', '2026-02-11 09:26:24', 1),
(64, 23, 'arrived/waiting', '2026-02-11 10:12:20', 1),
(65, 24, 'arrived/waiting', '2026-02-11 11:13:23', 6),
(68, 23, 'boarding', '2026-02-17 18:59:19', 2),
(69, 23, 'departed', '2026-02-17 18:59:20', 2),
(70, 24, 'boarding', '2026-02-17 18:59:49', 2),
(71, 24, 'departed', '2026-02-17 18:59:49', 2),
(72, 25, 'arrived/waiting', '2026-02-17 19:00:31', 2),
(73, 26, 'arrived/waiting', '2026-02-17 19:00:37', 2),
(74, 25, 'boarding', '2026-02-17 19:00:39', 2),
(75, 25, 'departed', '2026-02-17 19:00:54', 2),
(76, 26, 'boarding', '2026-02-17 19:00:58', 2),
(77, 26, 'departed', '2026-02-17 19:26:34', 2),
(78, 27, 'arrived/waiting', '2026-02-17 19:27:02', 2),
(80, 29, 'arrived/waiting', '2026-02-17 19:27:41', 2),
(81, 27, 'boarding', '2026-02-17 19:28:22', 2),
(82, 27, 'departed', '2026-02-17 19:28:23', 2),
(85, 29, 'boarding', '2026-02-17 19:28:25', 2),
(86, 29, 'departed', '2026-02-17 19:28:26', 2),
(87, 30, 'arrived/waiting', '2026-02-17 19:28:38', 2),
(88, 30, 'canceled', '2026-02-17 19:28:41', 2),
(89, 31, 'arrived/waiting', '2026-02-17 20:49:01', 2),
(90, 31, 'boarding', '2026-02-17 20:49:04', 2),
(91, 31, 'departed', '2026-02-17 20:52:45', 2),
(97, 34, 'arrived/waiting', '2026-02-28 08:34:11', 2),
(98, 35, 'arrived/waiting', '2026-02-28 08:34:21', 2),
(99, 34, 'boarding', '2026-02-28 08:51:04', 2),
(100, 35, 'boarding', '2026-02-28 08:51:05', 2),
(102, 34, 'departed', '2026-02-28 08:52:09', 2),
(103, 35, 'departed', '2026-02-28 08:52:09', 2),
(104, 36, 'arrived/waiting', '2026-02-28 08:52:34', 2),
(105, 36, 'boarding', '2026-02-28 08:52:37', 2),
(108, 38, 'arrived/waiting', '2026-02-28 09:15:48', 2),
(109, 38, 'boarding', '2026-02-28 09:15:51', 2),
(110, 36, 'departed', '2026-02-28 09:16:18', 2),
(112, 38, 'departed', '2026-02-28 09:16:20', 2),
(113, 39, 'arrived/waiting', '2026-02-28 10:34:41', 2),
(114, 39, 'boarding', '2026-02-28 10:34:43', 2),
(115, 39, 'canceled', '2026-02-28 10:45:23', 2),
(116, 40, 'arrived/waiting', '2026-02-28 10:45:31', 2),
(117, 40, 'boarding', '2026-02-28 10:45:34', 2),
(118, 40, 'canceled', '2026-02-28 10:48:02', 2),
(119, 41, 'arrived/waiting', '2026-02-28 10:48:10', 2),
(120, 41, 'boarding', '2026-02-28 10:48:12', 2),
(121, 41, 'departed', '2026-02-28 10:48:26', 2),
(125, 43, 'arrived/waiting', '2026-02-28 10:55:27', 2),
(126, 43, 'boarding', '2026-02-28 10:58:36', 2),
(127, 43, 'canceled', '2026-02-28 10:58:44', 2),
(128, 44, 'arrived/waiting', '2026-02-28 10:58:49', 2),
(129, 44, 'boarding', '2026-02-28 10:58:57', 2),
(130, 44, 'canceled', '2026-02-28 11:00:18', 2),
(131, 45, 'arrived/waiting', '2026-02-28 11:00:24', 2),
(132, 45, 'boarding', '2026-02-28 11:00:27', 2),
(133, 46, 'arrived/waiting', '2026-02-28 11:33:01', 2),
(134, 47, 'arrived/waiting', '2026-02-28 11:41:23', 2),
(135, 46, 'boarding', '2026-02-28 11:41:28', 2),
(136, 47, 'boarding', '2026-02-28 11:41:28', 2),
(137, 45, 'departed', '2026-02-28 11:41:33', 2),
(138, 46, 'departed', '2026-02-28 12:06:37', 2),
(139, 47, 'departed', '2026-02-28 12:06:37', 2),
(140, 48, 'arrived/waiting', '2026-03-08 12:55:57', 2),
(141, 48, 'boarding', '2026-03-08 14:00:22', 1),
(142, 48, 'departed', '2026-03-08 14:00:28', 1),
(143, 49, 'arrived/waiting', '2026-03-08 14:00:42', 1),
(144, 49, 'boarding', '2026-03-08 14:00:55', 1),
(145, 49, 'departed', '2026-03-08 14:01:31', 1),
(146, 50, 'arrived/waiting', '2026-03-08 14:01:42', 1),
(147, 51, 'arrived/waiting', '2026-03-08 14:26:42', 2),
(148, 50, 'boarding', '2026-03-08 14:27:43', 2),
(149, 50, 'departed', '2026-03-08 14:27:47', 2),
(150, 51, 'boarding', '2026-03-08 14:27:51', 2),
(151, 51, 'departed', '2026-03-08 14:27:54', 2),
(152, 52, 'arrived/waiting', '2026-03-08 14:33:39', 2),
(153, 52, 'boarding', '2026-03-08 14:37:46', 2),
(154, 54, 'arrived/waiting', '2026-03-08 15:00:35', 2),
(155, 54, 'boarding', '2026-03-08 15:00:49', 2),
(156, 54, 'departed', '2026-03-08 15:01:46', 2),
(157, 55, 'arrived/waiting', '2026-03-08 15:04:24', 2),
(158, 55, 'boarding', '2026-03-08 15:05:26', 2),
(159, 55, 'departed', '2026-03-08 15:06:02', 2),
(160, 57, 'arrived/waiting', '2026-03-08 15:11:39', 2),
(161, 57, 'boarding', '2026-03-08 15:11:41', 2),
(162, 57, 'departed', '2026-03-08 15:12:17', 2),
(163, 60, 'arrived/waiting', '2026-03-08 15:18:34', 2),
(164, 61, 'arrived/waiting', '2026-03-08 15:20:14', 2),
(165, 62, 'arrived/waiting', '2026-03-08 15:24:55', 2),
(166, 62, 'boarding', '2026-03-08 15:24:58', 2),
(167, 61, 'boarding', '2026-03-08 15:32:34', 2),
(168, 60, 'boarding', '2026-03-24 21:47:11', 5),
(169, 64, 'arrived/waiting', '2026-03-25 09:12:29', 5),
(170, 64, 'boarding', '2026-03-25 09:12:40', 5),
(171, 65, 'arrived/waiting', '2026-03-25 09:16:08', 5),
(172, 66, 'arrived/waiting', '2026-04-06 12:38:30', 5),
(173, 67, 'arrived/waiting', '2026-04-06 12:41:54', 5),
(174, 67, 'canceled', '2026-04-06 12:42:30', 5),
(175, 68, 'arrived/waiting', '2026-04-06 12:42:44', 5),
(176, 63, 'canceled', '2026-04-06 12:43:29', 5),
(177, 69, 'arrived/waiting', '2026-04-06 12:43:44', 5),
(178, 64, 'canceled', '2026-04-06 12:46:37', 5),
(179, 64, 'canceled', '2026-04-06 12:46:45', 5),
(180, 65, 'canceled', '2026-04-06 12:46:50', 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','staff','operator') NOT NULL DEFAULT 'operator',
  `full_name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `full_name`, `created_at`, `updated_at`) VALUES
(1, 'admin123', '$2y$10$CiJRci4T78G/Dndn3NAWjeqcSRnl02X6/Ejmanv9TvmdcSQxpwSOy', 'admin', 'System Administrator', '2026-02-06 04:19:50', '2026-03-24 22:14:41'),
(2, 'giogmarquez', '$2y$10$vy40lNJ1ZPYm4u4rhvtibOnMS8v2QyAj4Q6DZ7KXga/R6bwWsk4z6', 'staff', 'Gio Marquez', '2026-02-06 04:54:23', '2026-02-06 04:54:28'),
(3, 'jaylo', '$2y$10$rF.PNsrjCsANPcV3s8pwWur0Wr5NkNnzmNrQJMA.GQt2mvjlvMoP.', 'staff', 'Jaylo Terrado', '2026-02-06 05:37:24', '2026-03-24 13:26:25'),
(4, 'markjade', '$2y$10$MDysPLE5Y2xii3Xr0W.0TOJHsji7ssEkA14WBuCPx.d6XsB.cElaW', 'staff', 'Mark Jade Devota', '2026-02-06 05:57:41', '2026-03-24 13:26:25'),
(5, 'jay', '$2y$10$bxmdk4JnbvNu0HvE4PIXBumjo7PlsxsnpmO7LBvPeRfNSRV7TSiLC', 'staff', 'jaylo', '2026-02-08 11:49:08', '2026-03-24 13:26:25'),
(6, 'haha', '$2y$10$PVX3783A9siq6ahTCS81Heag7QX/SwP8GEDkjPWqm6t3Gmf/dqNVa', 'staff', 'haha', '2026-02-08 16:07:08', '2026-03-08 13:23:38'),
(7, 'gojosaturo', '$2y$10$4Oy3OXHjGVTU3bi5dmyptued4IVnbLhsoVAt3VhjKtZdBSi/xfb1a', 'staff', 'Gojo Saturo', '2026-02-28 11:17:31', '2026-03-24 13:26:25');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) UNSIGNED NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `type` enum('jeepney','van','minibus') NOT NULL,
  `default_route_id` int(11) UNSIGNED DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `scheduled_departure_time` time DEFAULT NULL,
  `status` enum('active','maintenance') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `plate_number`, `driver_name`, `type`, `default_route_id`, `capacity`, `owner_name`, `scheduled_departure_time`, `status`, `created_at`) VALUES
(2, '726 HOF', 'Mark Jade Devota', 'jeepney', 1, 16, 'Mark Jade Devota', '12:30:00', 'active', '2026-02-06 05:33:27'),
(4, '112ef', 'jaylo', 'jeepney', 2, 14, 'jaylo', '07:55:00', 'active', '2026-02-08 15:24:19'),
(6, '666 666', 'Gojo Saturo', 'minibus', 1, 30, 'Gojo Saturo', NULL, 'active', '2026-02-28 11:19:46'),
(7, '999 999', 'jaylo', 'van', 1, 16, 'jaylo', NULL, 'active', '2026-02-28 11:40:53'),
(8, 'sdaasd', 'ahhah', 'minibus', NULL, 23, '', NULL, 'active', '2026-03-24 22:22:24'),
(9, 'HELO123', 'ahhah', 'jeepney', NULL, 20, '', NULL, 'active', '2026-04-06 11:27:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_is_active_index` (`is_active`);

--
-- Indexes for table `departure_rules`
--
ALTER TABLE `departure_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `terminal_id` (`terminal_id`);

--
-- Indexes for table `terminals`
--
ALTER TABLE `terminals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trip_status_history`
--
ALTER TABLE `trip_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `queue_id` (`queue_id`),
  ADD KEY `updated_by_user_id` (`updated_by_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`),
  ADD KEY `vehicles_default_route_id_foreign` (`default_route_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `departure_rules`
--
ALTER TABLE `departure_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=602;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `terminals`
--
ALTER TABLE `terminals`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trip_status_history`
--
ALTER TABLE `trip_status_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `queue`
--
ALTER TABLE `queue`
  ADD CONSTRAINT `queue_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `queue_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `routes`
--
ALTER TABLE `routes`
  ADD CONSTRAINT `routes_terminal_id_foreign` FOREIGN KEY (`terminal_id`) REFERENCES `terminals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `trip_status_history`
--
ALTER TABLE `trip_status_history`
  ADD CONSTRAINT `trip_status_history_queue_id_foreign` FOREIGN KEY (`queue_id`) REFERENCES `queue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `trip_status_history_updated_by_user_id_foreign` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_default_route_id_foreign` FOREIGN KEY (`default_route_id`) REFERENCES `routes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
