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
  `capacity` int(11) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `status` enum('active','maintenance') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `plate_number`, `driver_name`, `type`, `capacity`, `owner_name`, `status`, `created_at`) VALUES
(2, '726 HOF', 'Mark Jade Devota', 'jeepney', 16, 'Mark Jade Devota', 'active', '2026-02-06 05:33:27'),
(4, '112ef', 'jaylo', 'jeepney', 14, 'jaylo', 'active', '2026-02-08 15:24:19'),
(6, '666 666', 'Gojo Saturo', 'minibus', 30, 'Gojo Saturo', 'active', '2026-02-28 11:19:46'),
(7, '999 999', 'jaylo', 'van', 16, 'jaylo', 'active', '2026-02-28 11:40:53'),
(8, 'sdaasd', 'ahhah', 'minibus', 23, '', 'active', '2026-03-24 22:22:24'),
(9, 'HELO123', 'ahhah', 'jeepney', 20, '', 'active', '2026-04-06 11:27:07');

-- --------------------------------------------------------

--
-- Table structure for table `fare_discounts`
--

CREATE TABLE `fare_discounts` (
  `id` int(11) UNSIGNED NOT NULL,
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

INSERT INTO `fare_discounts` (`id`, `type`, `label`, `discount_percent`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'pwd', 'PWD Discount', 20.00, 1, '2026-05-02 00:00:00', '2026-05-02 00:00:00'),
(2, 'senior_citizen', 'Senior Citizen Discount', 20.00, 1, '2026-05-02 00:00:00', '2026-05-02 00:00:00'),
(3, 'student', 'Student Discount', 15.00, 1, '2026-05-02 00:00:00', '2026-05-02 00:00:00');

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
-- Indexes for table `fare_discounts`
--
ALTER TABLE `fare_discounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fare_discounts_type_unique` (`type`);

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
  ADD UNIQUE KEY `plate_number` (`plate_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `fare_discounts`
--
ALTER TABLE `fare_discounts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
