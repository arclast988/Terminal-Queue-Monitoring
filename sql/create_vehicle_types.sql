-- SQL to create the `vehicle_types` table if migrations can't be run
-- Run with: mysql -u <user> -p jeepneynvans < sql/create_vehicle_types.sql

CREATE TABLE IF NOT EXISTS `vehicle_types` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_vehicle_types_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: seed initial types
INSERT INTO `vehicle_types` (`name`, `slug`, `is_active`) VALUES
  ('Van', 'van', 1),
  ('Jeepney', 'jeepney', 1),
  ('Minibus', 'minibus', 1)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `is_active`=VALUES(`is_active`);
