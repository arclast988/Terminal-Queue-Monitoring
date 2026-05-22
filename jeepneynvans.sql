-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2026 at 03:11 PM
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
  `terminal_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `message` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `terminal_id`, `message`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(4, 1, 'TESTING: Please be aware of weather conditions.', 1, 1, '2026-03-24 22:24:41', '2026-03-24 22:24:41');

-- --------------------------------------------------------

--
-- Table structure for table `departure_rules`
--

CREATE TABLE `departure_rules` (
  `id` int(11) NOT NULL,
  `terminal_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
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

INSERT INTO `departure_rules` (`id`, `terminal_id`, `time_from`, `time_to`, `wait_minutes`, `label`, `created_at`, `updated_at`) VALUES
(1, 1, '00:00:00', '05:00:00', 60, 'Late Night / Early Morning', '2026-02-11 02:06:33', '2026-05-16 09:06:24'),
(2, 1, '05:00:00', '09:00:00', 30, 'Morning Rush', '2026-02-11 02:06:33', '2026-02-11 02:06:33'),
(3, 1, '09:00:00', '12:00:00', 40, 'Mid-Morning', '2026-02-11 02:06:33', '2026-02-11 02:06:33'),
(4, 1, '12:00:00', '15:00:00', 40, 'Afternoon', '2026-02-11 02:06:33', '2026-02-11 02:06:33'),
(5, 1, '15:00:00', '18:00:00', 30, 'Afternoon Rush', '2026-02-11 02:06:33', '2026-02-11 02:06:33'),
(6, 1, '18:00:00', '21:00:00', 40, 'Evening', '2026-02-11 02:06:33', '2026-02-11 02:06:33'),
(8, 1, '21:00:00', '23:59:00', 60, 'Late Evening', '2026-05-16 09:14:14', '2026-05-16 09:15:08');

-- --------------------------------------------------------

--
-- Table structure for table `fares`
--

CREATE TABLE `fares` (
  `id` int(10) UNSIGNED NOT NULL,
  `route_id` int(10) UNSIGNED NOT NULL,
  `fare_discount_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fares`
--

INSERT INTO `fares` (`id`, `route_id`, `fare_discount_id`, `amount`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 155.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(2, 1, 1, 93.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(3, 1, 2, 62.00, '2026-05-22 19:23:34', '2026-05-22 21:01:07'),
(4, 1, 3, 131.75, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(5, 2, 4, 300.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(6, 2, 1, 180.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(7, 2, 2, 120.00, '2026-05-22 19:23:34', '2026-05-22 21:01:07'),
(8, 2, 3, 255.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(9, 3, 4, 10.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(10, 3, 1, 6.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(11, 3, 2, 4.00, '2026-05-22 19:23:34', '2026-05-22 21:01:07'),
(12, 3, 3, 8.50, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(13, 4, 4, 140.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(14, 4, 1, 84.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(15, 4, 2, 56.00, '2026-05-22 19:23:34', '2026-05-22 21:01:07'),
(16, 4, 3, 119.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(17, 7, 4, 300.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(18, 7, 1, 180.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(19, 7, 2, 120.00, '2026-05-22 19:23:34', '2026-05-22 21:01:07'),
(20, 7, 3, 255.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(21, 8, 4, 150.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(22, 8, 1, 90.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(23, 8, 2, 60.00, '2026-05-22 19:23:34', '2026-05-22 21:01:07'),
(24, 8, 3, 127.50, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(25, 13, 4, 1212.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(26, 13, 1, 727.20, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(27, 13, 2, 484.80, '2026-05-22 19:23:34', '2026-05-22 21:01:07'),
(28, 13, 3, 1030.20, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(37, 16, 4, 50.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(38, 16, 1, 30.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(39, 16, 2, 20.00, '2026-05-22 19:23:34', '2026-05-22 21:01:07'),
(40, 16, 3, 42.50, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(41, 17, 4, 50.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(42, 17, 1, 30.00, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(43, 17, 2, 20.00, '2026-05-22 19:23:34', '2026-05-22 21:01:07'),
(44, 17, 3, 42.50, '2026-05-22 19:23:34', '2026-05-22 19:23:34'),
(57, 15, 1, 120.00, '2026-05-22 20:57:28', '2026-05-22 20:57:28'),
(58, 15, 2, 80.00, '2026-05-22 20:57:28', '2026-05-22 21:01:07'),
(59, 15, 3, 170.00, '2026-05-22 20:57:28', '2026-05-22 20:57:28'),
(60, 15, 4, 200.00, '2026-05-22 20:57:28', '2026-05-22 20:57:28'),
(61, 14, 1, 60.00, '2026-05-22 21:00:24', '2026-05-22 21:00:24'),
(62, 14, 2, 40.00, '2026-05-22 21:00:24', '2026-05-22 21:01:07'),
(63, 14, 3, 85.00, '2026-05-22 21:00:24', '2026-05-22 21:00:24'),
(64, 14, 4, 100.00, '2026-05-22 21:00:24', '2026-05-22 21:00:24');

-- --------------------------------------------------------

--
-- Table structure for table `fare_discounts`
--

CREATE TABLE `fare_discounts` (
  `id` int(11) UNSIGNED NOT NULL,
  `terminal_id` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `type` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fare_discounts`
--

INSERT INTO `fare_discounts` (`id`, `terminal_id`, `type`, `label`, `discount_percent`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'pwd', 'PWD Discount', 40.00, 1, '2026-05-02 00:00:00', '2026-05-16 07:46:38'),
(2, 1, 'senior_citizen', 'Senior Citizen Discount', 60.00, 1, '2026-05-02 00:00:00', '2026-05-22 21:01:07'),
(3, 1, 'student', 'Student Discount', 15.00, 1, '2026-05-02 00:00:00', '2026-05-02 00:00:00'),
(4, 1, 'regular', 'Regular Fare', 0.00, 1, '2026-05-22 19:23:34', '2026-05-22 19:23:34');

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
(602, 1, 'Login', 'Admin logged in (admin123)', '2026-05-15 14:50:07'),
(603, 1, 'Update route', 'PALOMPON → ORMOC (van).', '2026-05-15 14:51:36'),
(604, 1, 'Logout', 'Admin admin123 logged out', '2026-05-15 15:06:57'),
(605, 2, 'Login', 'Staff logged in (giogmarquez)', '2026-05-15 15:07:02'),
(606, 2, 'Add to queue', 'Added 726 HOF to queue for ORMOC.', '2026-05-15 15:09:35'),
(607, 2, 'Logout', 'Staff giogmarquez logged out', '2026-05-15 15:14:14'),
(608, 1, 'Login', 'Admin logged in (admin123)', '2026-05-15 15:14:19'),
(609, 1, 'Update route', 'PALOMPON → ORMOC (van).', '2026-05-15 15:15:15'),
(610, 1, 'Update fare', 'PALOMPON TO ORMOC (VAN) route fare changed. Before: PHP 5.00 | After: PHP 155.00.', '2026-05-15 15:27:15'),
(611, 1, 'Logout', 'Admin admin123 logged out', '2026-05-15 15:27:29'),
(612, 2, 'Login', 'Staff logged in (giogmarquez)', '2026-05-15 15:27:33'),
(613, 2, 'Logout', 'Staff giogmarquez logged out', '2026-05-15 15:31:24'),
(614, 2, 'Login', 'Staff logged in (giogmarquez)', '2026-05-15 15:32:11'),
(615, 2, 'Start Boarding', 'Start Boarding for 726 HOF (ORMOC).', '2026-05-15 15:32:19'),
(616, 2, 'Logout', 'Staff giogmarquez logged out', '2026-05-15 15:32:23'),
(617, 2, 'Login', 'Staff logged in (giogmarquez)', '2026-05-15 15:38:02'),
(618, 2, 'Depart Vehicle', 'Depart Vehicle for 726 HOF (ORMOC).', '2026-05-15 15:38:05'),
(619, 2, 'Add to queue', 'Added sdaasd to queue for ORMOC.', '2026-05-15 15:38:13'),
(620, 2, 'Add to queue', 'Added HELO123 to queue for TACLOBAN.', '2026-05-15 15:38:20'),
(621, 2, 'Logout', 'Staff giogmarquez logged out', '2026-05-15 15:38:22'),
(622, 2, 'Login', 'Staff logged in (giogmarquez)', '2026-05-15 15:38:32'),
(623, 2, 'Logout', 'Staff giogmarquez logged out', '2026-05-15 15:38:50'),
(624, 2, 'Login', 'Staff logged in (giogmarquez)', '2026-05-15 15:38:56'),
(625, 2, 'Start Boarding', 'Start Boarding for sdaasd (ORMOC).', '2026-05-15 15:38:59'),
(626, 2, 'Logout', 'Staff giogmarquez logged out', '2026-05-15 15:39:00'),
(627, 1, 'Login', 'Admin logged in (admin123)', '2026-05-15 15:40:32'),
(628, 1, 'Logout', 'Admin admin123 logged out', '2026-05-15 15:42:09'),
(629, 1, 'Login', 'Admin logged in (admin123)', '2026-05-15 19:39:20'),
(630, 1, 'Logout', 'Admin admin123 logged out', '2026-05-15 19:39:47'),
(631, 5, 'Login', 'Staff logged in (jay)', '2026-05-15 19:39:54'),
(632, 5, 'Logout', 'Staff jay logged out', '2026-05-15 19:40:35'),
(633, 1, 'Login', 'Admin logged in (admin123)', '2026-05-15 19:40:40'),
(634, 2, 'Login', 'Staff logged in (giogmarquez)', '2026-05-15 19:54:04'),
(635, 1, 'Logout', 'Admin admin123 logged out', '2026-05-15 20:15:43'),
(636, 5, 'Login', 'Staff logged in (jay)', '2026-05-15 20:15:52'),
(637, 5, 'Logout', 'Staff jay logged out', '2026-05-15 21:23:33'),
(638, 2, 'Logout', 'Staff giogmarquez logged out', '2026-05-15 21:29:28'),
(639, 1, 'Login', 'Admin logged in (admin123)', '2026-05-15 21:37:47'),
(640, 1, 'Create route', 'ORMOC → ORMOC (van, ₱100).', '2026-05-15 21:38:36'),
(641, 1, 'Create route', 'ORMOC → ORMOC (jeepney, ₱1000).', '2026-05-15 21:38:54'),
(642, 1, 'Create route', 'ORMOC → SAN ISIDRO (van, ₱12311).', '2026-05-15 21:39:17'),
(643, 1, 'Logout', 'Admin admin123 logged out', '2026-05-15 21:39:19'),
(644, 1, 'Login', 'Admin logged in (admin123)', '2026-05-15 21:40:15'),
(645, 1, 'Create route', 'ORMOC → SAN ISIDRO (jeepney, ₱122).', '2026-05-15 21:42:21'),
(646, 1, 'Logout', 'Admin admin123 logged out', '2026-05-15 21:42:22'),
(647, 1, 'Login', 'Admin logged in (admin123)', '2026-05-15 21:42:39'),
(648, 1, 'Update discount', 'pwd discount updated to 19.97%.', '2026-05-15 21:46:19'),
(649, 1, 'Update discount', 'pwd discount updated to 30%.', '2026-05-15 21:46:34'),
(650, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 07:41:05'),
(651, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 07:41:54'),
(652, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 07:42:12'),
(653, 5, 'Update discount', 'pwd discount updated to 40%.', '2026-05-16 07:46:38'),
(654, 5, 'Start Boarding', 'Start Boarding for HELO123 (TACLOBAN).', '2026-05-16 07:46:56'),
(655, 5, 'Logout', 'Staff jay logged out', '2026-05-16 08:04:03'),
(656, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 08:04:16'),
(657, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 08:08:14'),
(658, 1, 'Delete route', 'ORMOC → ORMOC.', '2026-05-16 08:08:31'),
(659, 1, 'Create route', 'PALOMPON → ORMOC (jeepney, ₱1212).', '2026-05-16 08:08:55'),
(660, 1, 'Delete route', 'ORMOC → ORMOC.', '2026-05-16 08:08:59'),
(661, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 08:09:09'),
(662, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 08:09:16'),
(663, 5, 'Add to queue', 'Added 726 HOF to queue for SAN ISIDRO.', '2026-05-16 08:09:52'),
(664, 5, 'Logout', 'Staff jay logged out', '2026-05-16 08:09:57'),
(665, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 08:10:33'),
(666, 5, 'Logout', 'Staff jay logged out', '2026-05-16 08:17:55'),
(667, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 08:18:14'),
(668, 1, 'Create route', 'PALOMPON → CALUBIAN (van, ₱100).', '2026-05-16 08:19:12'),
(669, 1, 'Create route', 'PALOMPON → BOGO (jeepney, ₱150).', '2026-05-16 08:23:03'),
(670, 1, 'Create route', 'PALOMPON → VILLABA (van, ₱50).', '2026-05-16 08:27:05'),
(671, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 08:50:27'),
(672, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 08:50:51'),
(673, 5, 'Add to queue', 'Added 999 999 to queue for ORMOC. Rule: Morning Rush (30 min).', '2026-05-16 08:52:22'),
(674, 5, 'Logout', 'Staff jay logged out', '2026-05-16 08:53:45'),
(675, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 08:54:02'),
(676, 1, 'Update departure rule', 'Updated departure rule: Late Night / Early Morning. Before: 60 min (12:00 AM–5:00 AM). After: 65 min (12:00 AM–5:00 AM).', '2026-05-16 09:06:15'),
(677, 1, 'Update departure rule', 'Updated departure rule: Late Night / Early Morning. Before: 65 min (12:00 AM–5:00 AM). After: 60 min (12:00 AM–5:00 AM).', '2026-05-16 09:06:24'),
(678, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 09:13:37'),
(679, 1, 'Delete announcement', 'ID 3: tyhfghcgh', '2026-05-16 09:13:43'),
(680, 1, 'Delete departure rule', 'Deleted departure rule: 21:00:00 - 23:59:59.', '2026-05-16 09:13:50'),
(681, 1, 'Create departure rule', 'Added departure rule: Unlabeled (9:14 PM – 9:16 PM, 30 min).', '2026-05-16 09:14:14'),
(682, 1, 'Update departure rule', 'Updated departure rule: #8. Before: 30 min (9:14 PM–9:16 PM). After: 60 min (9:00 PM–11:59 PM).', '2026-05-16 09:15:09'),
(683, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 09:15:49'),
(684, 5, 'Depart Vehicle', 'Depart Vehicle for HELO123 (TACLOBAN).', '2026-05-16 09:16:15'),
(685, 5, 'Start Boarding', 'Start Boarding for 726 HOF (SAN ISIDRO).', '2026-05-16 09:16:18'),
(686, 5, 'Logout', 'Staff jay logged out', '2026-05-16 09:37:55'),
(687, 1, 'Update dispatcher routes', 'Updated dispatcher \"giogmarquez\". Added: PALOMPON → BOGO, PALOMPON → CALUBIAN.', '2026-05-16 09:39:03'),
(688, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 09:39:47'),
(689, 2, 'Login', 'Staff logged in (giogmarquez)', '2026-05-16 09:40:20'),
(690, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 09:40:52'),
(691, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 09:40:59'),
(692, 2, 'Logout', 'Staff giogmarquez logged out', '2026-05-16 09:41:09'),
(693, 5, 'Logout', 'Staff jay logged out', '2026-05-16 09:41:21'),
(694, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 09:42:37'),
(695, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 09:44:57'),
(696, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 09:45:28'),
(697, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 09:45:34'),
(698, 1, 'Update dispatcher routes', 'Updated dispatcher \"jaylo\". Added: PALOMPON → BOGO, PALOMPON → ORMOC.', '2026-05-16 09:45:46'),
(699, 1, 'Update dispatcher routes', 'Updated dispatcher \"markjade\". Added: PALOMPON → ORMOC, PALOMPON → SAN ISIDRO.', '2026-05-16 09:45:51'),
(700, 1, 'Update dispatcher routes', 'Updated dispatcher \"jay\". Added: PALOMPON → VILLABA.', '2026-05-16 09:45:55'),
(701, 1, 'Update dispatcher routes', 'Updated dispatcher \"haha\". Added: PALOMPON → TACLOBAN, PALOMPON → VILLABA.', '2026-05-16 09:46:01'),
(702, 1, 'Update dispatcher routes', 'Updated dispatcher \"gojosaturo\". Added: PALOMPON → SAN ISIDRO, PALOMPON → TACLOBAN.', '2026-05-16 09:46:18'),
(703, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 09:46:20'),
(704, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 09:46:25'),
(705, 1, 'Delete route', 'ORMOC → SAN ISIDRO.', '2026-05-16 09:46:31'),
(706, 1, 'Delete route', 'ORMOC → SAN ISIDRO.', '2026-05-16 09:46:35'),
(707, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 09:46:48'),
(708, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 09:46:54'),
(709, 5, 'Logout', 'Staff jay logged out', '2026-05-16 09:47:17'),
(710, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 09:47:22'),
(711, 1, 'Update dispatcher routes', 'Updated dispatcher \"jay\". Added: PALOMPON → TACLOBAN.', '2026-05-16 09:47:33'),
(712, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 09:47:35'),
(713, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 09:47:40'),
(714, 5, 'Logout', 'Staff jay logged out', '2026-05-16 09:49:41'),
(715, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 09:50:38'),
(716, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 09:50:48'),
(717, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 09:50:49'),
(718, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 09:52:00'),
(719, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 09:53:34'),
(720, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 09:53:55'),
(721, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 09:54:06'),
(722, 5, 'Logout', 'Staff jay logged out', '2026-05-16 09:58:25'),
(723, 1, 'Create route', 'PALOMPON → VILLABA (jeepney, ₱50).', '2026-05-16 09:58:40'),
(724, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 09:58:50'),
(725, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 09:59:29'),
(726, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 10:01:59'),
(727, 5, 'Logout', 'Staff jay logged out', '2026-05-16 10:17:38'),
(728, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 10:17:49'),
(729, 1, 'Update dispatcher routes', 'Updated dispatcher \"jay\". Added: PALOMPON → VILLABA.', '2026-05-16 10:20:14'),
(730, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 10:29:11'),
(731, 5, 'Add to queue', 'Added 666 666 to queue for TACLOBAN. Rule: Mid-Morning (40 min).', '2026-05-16 10:30:45'),
(732, 5, 'Start Boarding', 'Start Boarding for 666 666 (TACLOBAN).', '2026-05-16 10:30:56'),
(733, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 10:32:30'),
(734, 1, 'Update dispatcher routes', 'Updated dispatcher \"jay\". Added: PALOMPON → ORMOC, PALOMPON → ORMOC.', '2026-05-16 10:47:26'),
(735, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 10:54:11'),
(736, 5, 'Logout', 'Staff jay logged out', '2026-05-16 11:00:29'),
(737, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 11:00:35'),
(738, 1, 'Register vehicle', 'Registered vehicle 123245 (jeepney) - Driver: dfdfd - Route: PALOMPON → VILLABA', '2026-05-16 11:01:11'),
(739, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 11:01:36'),
(740, 5, 'Depart Vehicle', 'Depart Vehicle for 666 666 (TACLOBAN).', '2026-05-16 11:02:00'),
(741, 5, 'Start Boarding', 'Start Boarding for 999 999 (ORMOC).', '2026-05-16 11:02:01'),
(742, 5, 'Depart Vehicle', 'Depart Vehicle for 999 999 (ORMOC).', '2026-05-16 11:02:03'),
(743, 5, 'Add to queue', 'Added 123245 to queue for VILLABA. Rule: Mid-Morning (40 min).', '2026-05-16 11:02:16'),
(744, 5, 'Depart Vehicle', 'Depart Vehicle for sdaasd (ORMOC).', '2026-05-16 11:06:09'),
(745, 5, 'Add to queue', 'Added 666 666 to queue for TACLOBAN. Rule: Mid-Morning (40 min).', '2026-05-16 11:06:18'),
(746, 5, 'Logout', 'Staff jay logged out', '2026-05-16 11:06:23'),
(747, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 11:59:33'),
(748, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 11:59:40'),
(749, 5, 'Logout', 'Staff jay logged out', '2026-05-16 12:00:00'),
(750, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 12:00:09'),
(751, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 12:11:48'),
(752, 1, 'Update vehicle', 'Updated vehicle 123245', '2026-05-16 12:18:33'),
(753, 1, 'Update vehicle', 'Updated vehicle 123245', '2026-05-16 12:20:25'),
(754, 1, 'Update vehicle', 'Updated vehicle 123245', '2026-05-16 12:22:08'),
(755, 1, 'Update vehicle', 'Updated vehicle 123245', '2026-05-16 12:23:33'),
(756, 1, 'Assign vehicle to route', 'Registered vehicle TEST-777 (van) - Driver: Test Driver Seven - Route: PALOMPON → CALUBIAN', '2026-05-16 12:27:49'),
(757, 1, 'Update vehicle', 'Updated vehicle 123245', '2026-05-16 12:28:58'),
(758, 1, 'Reassign vehicle route', 'Reassigned 123245 from PALOMPON → VILLABA to PALOMPON → BOGO', '2026-05-16 12:33:31'),
(759, 1, 'Update vehicle', 'Updated vehicle 123245', '2026-05-16 12:35:38'),
(760, 1, 'Update vehicle', 'Updated vehicle TEST-OLD', '2026-05-16 12:40:04'),
(761, 1, 'Assign vehicle to route', 'Registered vehicle TEST-777 (van) - Driver: Test Driver Seven - Route: PALOMPON → CALUBIAN', '2026-05-16 12:41:34'),
(762, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 12:45:02'),
(763, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 12:45:26'),
(764, 5, 'Logout', 'Staff jay logged out', '2026-05-16 12:45:36'),
(765, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 12:49:56'),
(766, 5, 'Logout', 'Staff jay logged out', '2026-05-16 12:50:32'),
(767, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 12:53:28'),
(768, 5, 'Logout', 'Staff jay logged out', '2026-05-16 12:53:33'),
(769, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 12:53:50'),
(770, 5, 'Logout', 'Staff jay logged out', '2026-05-16 12:54:02'),
(771, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 12:54:43'),
(772, 5, 'Logout', 'Staff jay logged out', '2026-05-16 12:54:58'),
(773, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 12:55:48'),
(774, 5, 'Logout', 'Staff jay logged out', '2026-05-16 12:56:21'),
(775, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 12:57:13'),
(776, 5, 'Logout', 'Staff jay logged out', '2026-05-16 12:57:36'),
(777, 5, 'Login', 'Staff logged in (jay)', '2026-05-16 12:59:12'),
(778, 5, 'Logout', 'Staff jay logged out', '2026-05-16 12:59:31'),
(779, 1, 'Login', 'Admin logged in (admin123)', '2026-05-16 13:06:53'),
(780, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 13:07:51'),
(781, 1, 'Delete vehicle', 'Deleted vehicle 123245.', '2026-05-16 13:08:10'),
(782, 1, 'Logout', 'Admin admin123 logged out', '2026-05-16 13:14:41'),
(783, 1, 'Login', 'Admin logged in (admin123)', '2026-05-22 19:58:34'),
(784, 1, 'Logout', 'Admin admin123 logged out', '2026-05-22 19:59:20'),
(785, 5, 'Login', 'Staff logged in (jay)', '2026-05-22 19:59:32'),
(786, 5, 'Logout', 'Staff jay logged out', '2026-05-22 20:03:49'),
(787, 1, 'Login', 'Admin logged in (admin123)', '2026-05-22 20:03:56'),
(788, 1, 'Logout', 'Admin admin123 logged out', '2026-05-22 20:04:54'),
(789, 5, 'Login', 'Staff logged in (jay)', '2026-05-22 20:04:59'),
(790, 5, 'Logout', 'Staff jay logged out', '2026-05-22 20:17:55'),
(791, 1, 'Login', 'Admin logged in (admin123)', '2026-05-22 20:18:01'),
(792, 1, 'Logout', 'Admin admin123 logged out', '2026-05-22 20:37:34'),
(793, 1, 'Login', 'Admin logged in (admin123)', '2026-05-22 20:56:07'),
(794, 1, 'Update fare', 'PALOMPON TO BOGO (JEEPNEY) route fare changed. Before: PHP 150.00 | After: PHP 190.00.', '2026-05-22 20:56:17'),
(795, 1, 'Update route', 'PALOMPON to 180 (jeepney).', '2026-05-22 20:56:35'),
(796, 1, 'Update route', 'PALOMPON to TACLOBAN (jeepney).', '2026-05-22 20:57:17'),
(797, 1, 'Update fare', 'PALOMPON TO TACLOBAN (JEEPNEY) route fare changed. Before: PHP 190.00 | After: PHP 200.00.', '2026-05-22 20:57:28'),
(798, 1, 'Update route', 'PALOMPON to MAMAM (van).', '2026-05-22 21:00:24'),
(799, 1, 'Update discount', 'senior_citizen discount updated to 60%.', '2026-05-22 21:01:07'),
(800, 1, 'Logout', 'Admin admin123 logged out', '2026-05-22 21:10:25'),
(801, 5, 'Login', 'Staff logged in (jay)', '2026-05-22 21:10:36'),
(802, 5, 'Logout', 'Staff jay logged out', '2026-05-22 21:11:07');

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

--
-- Dumping data for table `migrations`
--

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
(19, '2026-05-22-203000', 'App\\Database\\Migrations\\RemoveRouteOriginUseTerminal', 'default', 'App', 1779454489, 5);

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
(70, 2, 1, 'departed', 16, 0, '2026-05-15 15:09:35', '2026-05-15 15:39:35', '2026-05-15 15:38:05'),
(71, 8, 1, 'departed', 23, 0, '2026-05-15 15:38:13', '2026-05-15 16:08:13', '2026-05-16 11:06:09'),
(72, 9, 2, 'departed', 20, 0, '2026-05-15 15:38:20', '2026-05-15 16:08:20', '2026-05-16 09:16:15'),
(73, 2, 4, 'boarding', 0, 1, '2026-05-16 08:09:52', '2026-05-16 08:39:52', NULL),
(74, 7, 1, 'departed', 0, 0, '2026-05-16 08:52:22', '2026-05-16 09:22:22', '2026-05-16 11:02:03'),
(75, 6, 7, 'departed', 13, 0, '2026-05-16 10:30:44', '2026-05-16 11:10:44', '2026-05-16 11:02:00'),
(77, 6, 7, 'waiting', 0, 3, '2026-05-16 11:06:18', '2026-05-16 11:46:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` int(11) UNSIGNED NOT NULL,
  `terminal_id` int(11) UNSIGNED NOT NULL,
  `destination` varchar(100) NOT NULL,
  `vehicle_type` enum('jeepney','van','minibus') NOT NULL DEFAULT 'van',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `terminal_id`, `destination`, `vehicle_type`, `created_at`) VALUES
(1, 1, 'ORMOC', 'van', '2026-02-06 04:58:59'),
(2, 1, 'TACLOBAN', 'van', '2026-02-06 05:33:02'),
(3, 1, 'JORDAN', 'van', '2026-02-08 15:50:51'),
(4, 1, 'SAN ISIDRO', 'jeepney', '2026-02-08 16:18:42'),
(7, 1, 'TACLOBAN', 'minibus', '2026-02-17 21:29:37'),
(8, 1, 'ORMOC', 'minibus', '2026-02-17 21:46:07'),
(13, 1, 'ORMOC', 'jeepney', '2026-05-16 08:08:55'),
(14, 1, 'MAMAM', 'van', '2026-05-16 08:19:12'),
(15, 1, 'TACLOBAN', 'jeepney', '2026-05-16 08:23:03'),
(16, 1, 'VILLABA', 'van', '2026-05-16 08:27:05'),
(17, 1, 'VILLABA', 'jeepney', '2026-05-16 09:58:40');

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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `full_name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `full_name`, `created_at`, `updated_at`) VALUES
(1, 'admin123', '$2y$10$CiJRci4T78G/Dndn3NAWjeqcSRnl02X6/Ejmanv9TvmdcSQxpwSOy', 'admin', 'System Administrator', '2026-02-06 04:19:50', '2026-03-24 22:14:41'),
(2, 'giogmarquez', '$2y$10$7fdRDIG7zef0wQndgUr1PetdTX1FXYdxL4pfKsBpRH7bu.Ji7M09C', 'staff', 'Gio Marquez', '2026-02-06 04:54:23', '2026-05-16 09:39:39'),
(3, 'jaylo', '', 'staff', 'Jaylo Terrado', '2026-02-06 05:37:24', '2026-05-16 12:43:54'),
(4, 'markjade', '$2y$10$MDysPLE5Y2xii3Xr0W.0TOJHsji7ssEkA14WBuCPx.d6XsB.cElaW', 'staff', 'Mark Jade Devota', '2026-02-06 05:57:41', '2026-05-16 09:45:51'),
(5, 'jay', '$2y$10$bxmdk4JnbvNu0HvE4PIXBumjo7PlsxsnpmO7LBvPeRfNSRV7TSiLC', 'staff', 'jaylo', '2026-02-08 11:49:08', '2026-05-16 10:47:26'),
(6, 'haha', '$2y$10$PVX3783A9siq6ahTCS81Heag7QX/SwP8GEDkjPWqm6t3Gmf/dqNVa', 'staff', 'haha', '2026-02-08 16:07:08', '2026-05-16 09:46:01'),
(7, 'gojosaturo', '$2y$10$4Oy3OXHjGVTU3bi5dmyptued4IVnbLhsoVAt3VhjKtZdBSi/xfb1a', 'staff', 'Gojo Saturo', '2026-02-28 11:17:31', '2026-05-16 09:46:18');

-- --------------------------------------------------------

--
-- Table structure for table `user_routes`
--

CREATE TABLE `user_routes` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `route_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_routes`
--

INSERT INTO `user_routes` (`id`, `user_id`, `route_id`, `created_at`) VALUES
(3, 2, 15, '2026-05-16 09:39:39'),
(4, 2, 14, '2026-05-16 09:39:39'),
(7, 4, 8, '2026-05-16 09:45:51'),
(8, 4, 4, '2026-05-16 09:45:51'),
(12, 7, 4, '2026-05-16 09:46:18'),
(13, 7, 2, '2026-05-16 09:46:18'),
(16, 3, 15, '2026-05-16 09:53:27'),
(17, 3, 1, '2026-05-16 09:53:27'),
(21, 5, 1, '2026-05-16 10:47:26'),
(22, 5, 8, '2026-05-16 10:47:26'),
(23, 5, 7, '2026-05-16 10:47:26'),
(24, 5, 16, '2026-05-16 10:47:26'),
(25, 5, 17, '2026-05-16 10:47:26'),
(26, 6, 2, '2026-05-16 12:32:08'),
(27, 6, 16, '2026-05-16 12:32:08');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) UNSIGNED NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `type` enum('jeepney','van','minibus') NOT NULL,
  `capacity` int(11) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `status` enum('active','maintenance') NOT NULL DEFAULT 'active',
  `route_id` int(11) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `plate_number`, `driver_name`, `type`, `capacity`, `owner_name`, `status`, `route_id`, `created_at`) VALUES
(2, '726 HOF', 'Mark Jade Devota', 'jeepney', 16, 'Mark Jade Devota', 'active', 15, '2026-02-06 05:33:27'),
(4, '112ef', 'jaylo', 'jeepney', 14, 'jaylo', 'active', 4, '2026-02-08 15:24:19'),
(6, '666 666', 'Gojo Saturo', 'minibus', 30, 'Gojo Saturo', 'active', 7, '2026-02-28 11:19:46'),
(7, '999 999', 'jaylo', 'van', 16, 'jaylo', 'active', 1, '2026-02-28 11:40:53'),
(8, 'sdaasd', 'ahhah', 'minibus', 23, '', 'active', 8, '2026-03-24 22:22:24'),
(9, 'HELO123', 'ahhah', 'jeepney', 20, '', 'active', 13, '2026-04-06 11:27:07'),
(11, 'TEST-OLD', 'Test Driver Seven', 'van', 15, '', 'active', 14, '2026-05-16 12:27:49'),
(12, 'TEST-777', 'Test Driver Seven', 'van', 15, '', 'active', 14, '2026-05-16 12:41:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_is_active_index` (`is_active`),
  ADD KEY `fk_announcements_terminal` (`terminal_id`);

--
-- Indexes for table `departure_rules`
--
ALTER TABLE `departure_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_departure_rules_terminal` (`terminal_id`);

--
-- Indexes for table `fares`
--
ALTER TABLE `fares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_route_discount` (`route_id`,`fare_discount_id`),
  ADD KEY `fare_discount_id` (`fare_discount_id`);

--
-- Indexes for table `fare_discounts`
--
ALTER TABLE `fare_discounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fare_discounts_terminal_type_unique` (`terminal_id`,`type`);

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
  ADD UNIQUE KEY `unique_terminal_destination_vehicle` (`terminal_id`,`destination`,`vehicle_type`),
  ADD KEY `terminal_id` (`terminal_id`);

--
-- Indexes for table `terminals`
--
ALTER TABLE `terminals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_routes`
--
ALTER TABLE `user_routes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_route` (`user_id`,`route_id`),
  ADD KEY `idx_ur_user_id` (`user_id`),
  ADD KEY `idx_ur_route_id` (`route_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`),
  ADD KEY `idx_vehicles_route_id` (`route_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `fares`
--
ALTER TABLE `fares`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `fare_discounts`
--
ALTER TABLE `fare_discounts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=803;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `terminals`
--
ALTER TABLE `terminals`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_routes`
--
ALTER TABLE `user_routes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcements_terminal` FOREIGN KEY (`terminal_id`) REFERENCES `terminals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departure_rules`
--
ALTER TABLE `departure_rules`
  ADD CONSTRAINT `fk_departure_rules_terminal` FOREIGN KEY (`terminal_id`) REFERENCES `terminals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fares`
--
ALTER TABLE `fares`
  ADD CONSTRAINT `fares_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fares_ibfk_2` FOREIGN KEY (`fare_discount_id`) REFERENCES `fare_discounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fare_discounts`
--
ALTER TABLE `fare_discounts`
  ADD CONSTRAINT `fk_fare_discounts_terminal` FOREIGN KEY (`terminal_id`) REFERENCES `terminals` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `user_routes`
--
ALTER TABLE `user_routes`
  ADD CONSTRAINT `fk_uroutes_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_uroutes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_vehicles_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
