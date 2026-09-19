-- ====================================================================
-- Palompon Transit Terminal Management System (PTTM)
-- PostgreSQL Database Schema & Full Data Migration
-- ====================================================================

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS audit_logs CASCADE;
DROP TABLE IF EXISTS password_reset_tokens CASCADE;
DROP TABLE IF EXISTS vehicle_assignments CASCADE;
DROP TABLE IF EXISTS queue CASCADE;
DROP TABLE IF EXISTS departure_rules CASCADE;
DROP TABLE IF EXISTS fares CASCADE;
DROP TABLE IF EXISTS fare_discounts CASCADE;
DROP TABLE IF EXISTS user_routes CASCADE;
DROP TABLE IF EXISTS vehicles CASCADE;
DROP TABLE IF EXISTS routes CASCADE;
DROP TABLE IF EXISTS vehicle_types CASCADE;
DROP TABLE IF EXISTS announcements CASCADE;
DROP TABLE IF EXISTS terminals CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- --------------------------------------------------------------------
-- 1. Users Table
-- --------------------------------------------------------------------
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'staff',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NULL UNIQUE,
    failed_login_attempts INT DEFAULT 0,
    login_attempts INT DEFAULT 0,
    lockout_until TIMESTAMP NULL,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------
-- 2. Terminals Table
-- --------------------------------------------------------------------
CREATE TABLE terminals (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NULL,
    capacity INT DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------
-- 3. Vehicle Types Table
-- --------------------------------------------------------------------
CREATE TABLE vehicle_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(30) DEFAULT '#c62828',
    icon VARCHAR(50) DEFAULT 'fa-bus',
    is_active SMALLINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------
-- 4. Routes Table
-- --------------------------------------------------------------------
CREATE TABLE routes (
    id SERIAL PRIMARY KEY,
    terminal_id INT NOT NULL REFERENCES terminals(id) ON DELETE CASCADE,
    destination VARCHAR(100) NOT NULL,
    fare NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    vehicle_type VARCHAR(50) NOT NULL DEFAULT 'van',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------
-- 5. Vehicles Table
-- --------------------------------------------------------------------
CREATE TABLE vehicles (
    id SERIAL PRIMARY KEY,
    plate_number VARCHAR(20) NOT NULL UNIQUE,
    driver_name VARCHAR(100) NULL,
    operator_name VARCHAR(100) NULL,
    type VARCHAR(50) NOT NULL,
    capacity INT NOT NULL DEFAULT 14,
    owner_name VARCHAR(100) NOT NULL DEFAULT '',
    photo VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    route_id INT NULL REFERENCES routes(id) ON DELETE SET NULL,
    default_route_id INT NULL REFERENCES routes(id) ON DELETE SET NULL,
    scheduled_departure_time TIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------
-- 6. User Routes Table
-- --------------------------------------------------------------------
CREATE TABLE user_routes (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    route_id INT NOT NULL REFERENCES routes(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------
-- 7. Fare Discounts Table
-- --------------------------------------------------------------------
CREATE TABLE fare_discounts (
    id SERIAL PRIMARY KEY,
    terminal_id INT NOT NULL DEFAULT 1 REFERENCES terminals(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL,
    label VARCHAR(100) NOT NULL,
    discount_percent NUMERIC(5,2) NOT NULL DEFAULT 0.00,
    is_active SMALLINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_fare_discounts_terminal_type UNIQUE (terminal_id, type)
);

-- --------------------------------------------------------------------
-- 8. Fares Table
-- --------------------------------------------------------------------
CREATE TABLE fares (
    id SERIAL PRIMARY KEY,
    terminal_id INT NULL REFERENCES terminals(id) ON DELETE SET NULL,
    route_id INT NOT NULL REFERENCES routes(id) ON DELETE CASCADE,
    fare_discount_id INT NOT NULL REFERENCES fare_discounts(id) ON DELETE CASCADE,
    amount NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_fares_route_discount UNIQUE (route_id, fare_discount_id)
);

-- --------------------------------------------------------------------
-- 9. Departure Rules Table
-- --------------------------------------------------------------------
CREATE TABLE departure_rules (
    id SERIAL PRIMARY KEY,
    terminal_id INT NOT NULL DEFAULT 1 REFERENCES terminals(id) ON DELETE CASCADE,
    route_id INT NULL REFERENCES routes(id) ON DELETE SET NULL,
    time_from TIME NOT NULL,
    time_to TIME NOT NULL,
    wait_minutes INT NOT NULL DEFAULT 30,
    label VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------
-- 10. Queue Table
-- --------------------------------------------------------------------
CREATE TABLE queue (
    id SERIAL PRIMARY KEY,
    vehicle_id INT NOT NULL REFERENCES vehicles(id) ON DELETE CASCADE,
    route_id INT NOT NULL REFERENCES routes(id) ON DELETE CASCADE,
    plate_number VARCHAR(20) NOT NULL,
    driver_name VARCHAR(100) NULL,
    operator_name VARCHAR(100) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'waiting',
    current_passengers INT NOT NULL DEFAULT 0,
    position INT NOT NULL DEFAULT 1,
    arrival_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estimated_departure TIMESTAMP NULL,
    departure_time TIMESTAMP NULL
);


-- --------------------------------------------------------------------
-- 12. Announcements Table
-- --------------------------------------------------------------------
CREATE TABLE announcements (
    id SERIAL PRIMARY KEY,
    terminal_id INT NOT NULL DEFAULT 1 REFERENCES terminals(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    is_active SMALLINT NOT NULL DEFAULT 1,
    priority INT NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------
-- 13. Audit Logs Table
-- --------------------------------------------------------------------
CREATE TABLE audit_logs (
    id SERIAL PRIMARY KEY,
    user_id INT NULL REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------
-- 14. Password Reset Tokens Table
-- --------------------------------------------------------------------
CREATE TABLE password_reset_tokens (
    id SERIAL PRIMARY KEY,
    user_id INT NULL REFERENCES users(id) ON DELETE CASCADE,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    reset_code VARCHAR(10) NULL,
    code_attempts INT NOT NULL DEFAULT 0,
    used SMALLINT NOT NULL DEFAULT 0,
    verified SMALLINT NOT NULL DEFAULT 0,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------------------
-- Performance Indexes
-- --------------------------------------------------------------------
CREATE INDEX idx_queue_route_status_pos ON queue (route_id, status, position);
CREATE INDEX idx_queue_status_departure ON queue (status, departure_time);
CREATE INDEX idx_queue_vehicle ON queue (vehicle_id);
CREATE INDEX idx_vehicles_route_id ON vehicles (route_id);
CREATE INDEX idx_vehicles_default_route_id ON vehicles (default_route_id);
CREATE INDEX idx_vehicles_plate ON vehicles (plate_number);
CREATE INDEX idx_routes_terminal ON routes (terminal_id);
CREATE INDEX IF NOT EXISTS idx_routes_status ON routes (status);
CREATE INDEX idx_audit_logs_user_ts ON audit_logs (user_id, timestamp);
CREATE INDEX idx_announcements_active_sort ON announcements (is_active, sort_order);

-- ====================================================================
-- DATA POPULATION FROM ORIGINAL DATABASE
-- ====================================================================

-- 1. Users
INSERT INTO users (id, username, password_hash, role, full_name, email, failed_login_attempts, lockout_until, created_at, updated_at) VALUES
(1, 'arclast988@gmail.com', '$2y$10$CiJRci4T78G/Dndn3NAWjeqcSRnl02X6/Ejmanv9TvmdcSQxpwSOy', 'super_admin', 'System Administrator', 'arclast988@gmail.com', 0, NULL, '2026-06-03 11:18:52', '2026-08-11 21:16:00'),
(2, 'noynayjaylo@gmail.com', '$2y$12$x7/4x01cDuGR.0PjCtjG8emPAirOt18PT2dqdbtb/a5/pqiMLSR8W', 'admin', 'jaylo', 'noynayjaylo@gmail.com', 0, NULL, '2026-06-06 19:10:10', '2026-07-15 19:11:15'),
(3, 'admin', '$2y$10$lVlqUyemBdPcAtglHrJQtemFCknZCakvsAVI6wIwnwuEF9RwpfdGu', 'super_admin', 'Terminal Administrator', 'admin@terminal.local', 0, NULL, '2026-06-03 11:18:52', '2026-08-11 21:16:00'),
(4, 'staff', '$2y$10$TQJ5rS46mxqUztie7HI7kuJZHf1FoUpf.hsi5osu5WoIxemSQilxa', 'staff', 'Lead Dispatcher', 'staff@terminal.local', 0, NULL, '2026-06-20 11:20:13', '2026-08-11 20:16:27'),
(5, 'jycgrac@gmail.com', '$2y$12$z8AHLovLZJK2327FhBUJ2uwn8SwLZIwQwQEhXEc0XAup3IlEinjAi', 'staff', 'Staff User', 'jycgrac@gmail.com', 0, NULL, '2026-06-20 11:20:13', '2026-08-11 20:16:27')
ON CONFLICT (username) DO NOTHING;

SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));

-- 2. Terminals
INSERT INTO terminals (id, name, location, capacity, created_at) VALUES
(1, 'PALOMPON', 'PALOMPON, LEYTE', 50, '2026-06-03 11:18:52'),
(5, 'dfdsf', 'dfd', 10, '2026-08-04 20:26:01')
ON CONFLICT (id) DO NOTHING;

SELECT setval('terminals_id_seq', (SELECT MAX(id) FROM terminals));

-- 3. Vehicle Types
INSERT INTO vehicle_types (id, name, slug, color, icon, is_active, created_at, updated_at) VALUES
(1, 'Van', 'van', '#c62828', 'fa-van-shuttle', 1, '2026-08-09 18:14:52', '2026-08-09 18:14:52'),
(2, 'Jeepney', 'jeepney', '#1565c0', 'fa-truck-front', 1, '2026-08-09 18:14:52', '2026-08-09 18:14:52'),
(3, 'Minibus', 'minibus', '#2e7d32', 'fa-bus', 1, '2026-08-09 18:14:52', '2026-08-09 18:14:52'),
(4, 'Bus', 'bus', '#ea580c', 'fa-bus-simple', 1, '2026-08-09 18:14:52', '2026-08-09 18:14:52')
ON CONFLICT (slug) DO NOTHING;

SELECT setval('vehicle_types_id_seq', (SELECT MAX(id) FROM vehicle_types));

-- 4. Routes
INSERT INTO routes (id, terminal_id, destination, fare, vehicle_type, created_at) VALUES
(3, 1, 'TACLOBAN', 150.00, 'van', '2026-06-13 17:45:42'),
(5, 1, 'ORMOC', 1121.00, 'van', '2026-06-18 14:53:40'),
(6, 1, 'BOGO', 111.00, 'van', '2026-07-21 20:27:27'),
(8, 1, 'ORMOC', 123232.00, 'jeepney', '2026-07-21 20:56:42'),
(9, 1, 'TACLOBAN', 12312321.00, 'minibus', '2026-07-21 20:57:08'),
(10, 1, 'KANANGA', 150.00, 'jeepney', '2026-08-04 20:09:48'),
(11, 1, 'MANILA', 150.00, 'van', '2026-08-04 20:12:23')
ON CONFLICT (id) DO NOTHING;

SELECT setval('routes_id_seq', (SELECT MAX(id) FROM routes));

-- 5. Vehicles
INSERT INTO vehicles (id, plate_number, driver_name, operator_name, type, capacity, owner_name, status, route_id, default_route_id, created_at) VALUES
(1, '112131', 'jaylo', 'jaylo', 'minibus', 20, 'jaylo', 'active', 9, 9, '2026-06-06 19:16:22'),
(2, '22323', 'terrado', 'terrado', 'minibus', 20, 'terrado', 'active', 9, 9, '2026-06-07 06:36:21')
ON CONFLICT (plate_number) DO NOTHING;

SELECT setval('vehicles_id_seq', (SELECT MAX(id) FROM vehicles));

-- 6. User Routes (Staff Dispatcher Route Assignments)
INSERT INTO user_routes (id, user_id, route_id, created_at) VALUES
(20, 5, 8, '2026-07-28 11:54:54'),
(21, 5, 5, '2026-07-28 11:54:54'),
(22, 5, 3, '2026-07-28 11:54:54'),
(23, 5, 9, '2026-07-28 11:54:54'),
(24, 4, 3, '2026-07-28 11:54:54'),
(25, 4, 5, '2026-07-28 11:54:54'),
(26, 4, 8, '2026-07-28 11:54:54'),
(27, 4, 9, '2026-07-28 11:54:54')
ON CONFLICT (id) DO NOTHING;

SELECT setval('user_routes_id_seq', (SELECT MAX(id) FROM user_routes));

-- 7. Fare Discounts
INSERT INTO fare_discounts (id, terminal_id, type, label, discount_percent, is_active, created_at, updated_at) VALUES
(1, 1, 'pwd', 'PWD Discount', 20.00, 1, '2026-06-03 11:18:52', '2026-06-03 11:18:52'),
(2, 1, 'senior_citizen', 'Senior Citizen Discount', 20.00, 1, '2026-06-03 11:18:52', '2026-06-03 11:18:52'),
(3, 1, 'student', 'Student Discount', 15.00, 1, '2026-06-03 11:18:52', '2026-06-03 11:18:52'),
(4, 1, 'regular', 'Regular Fare', 0.00, 1, '2026-06-03 11:18:52', '2026-06-03 11:18:52')
ON CONFLICT (id) DO NOTHING;

SELECT setval('fare_discounts_id_seq', (SELECT MAX(id) FROM fare_discounts));

-- 8. Fares
INSERT INTO fares (id, terminal_id, route_id, fare_discount_id, amount, created_at, updated_at) VALUES
(53, 1, 6, 1, 88.80, '2026-07-21 20:55:37', '2026-07-21 20:55:37'),
(54, 1, 6, 4, 111.00, '2026-07-21 20:55:37', '2026-07-21 20:55:37'),
(55, 1, 6, 2, 88.80, '2026-07-21 20:55:37', '2026-07-21 20:55:37'),
(56, 1, 6, 3, 94.35, '2026-07-21 20:55:37', '2026-07-21 20:55:37'),
(57, 1, 5, 1, 896.80, '2026-07-21 20:56:42', '2026-07-21 20:56:42'),
(58, 1, 5, 4, 1121.00, '2026-07-21 20:56:42', '2026-07-21 20:56:42'),
(59, 1, 5, 2, 896.80, '2026-07-21 20:56:42', '2026-07-21 20:56:42'),
(60, 1, 5, 3, 952.85, '2026-07-21 20:56:42', '2026-07-21 20:56:42'),
(61, 1, 8, 1, 98585.60, '2026-07-21 20:56:42', '2026-07-21 20:56:42'),
(62, 1, 8, 4, 123232.00, '2026-07-21 20:56:42', '2026-07-21 20:56:42'),
(63, 1, 8, 2, 98585.60, '2026-07-21 20:56:42', '2026-07-21 20:56:42'),
(64, 1, 8, 3, 104747.20, '2026-07-21 20:56:42', '2026-07-21 20:56:42'),
(65, 1, 3, 1, 120.00, '2026-07-21 20:57:08', '2026-07-21 20:57:08'),
(66, 1, 3, 4, 150.00, '2026-07-21 20:57:08', '2026-07-21 20:57:08'),
(67, 1, 3, 2, 120.00, '2026-07-21 20:57:08', '2026-07-21 20:57:08'),
(68, 1, 3, 3, 127.50, '2026-07-21 20:57:08', '2026-07-21 20:57:08'),
(69, 1, 9, 1, 9849856.80, '2026-07-21 20:57:08', '2026-07-21 20:57:08'),
(70, 1, 9, 4, 12312321.00, '2026-07-21 20:57:08', '2026-07-21 20:57:08'),
(71, 1, 9, 2, 9849856.80, '2026-07-21 20:57:08', '2026-07-21 20:57:08'),
(72, 1, 9, 3, 10465472.85, '2026-07-21 20:57:08', '2026-07-21 20:57:08'),
(77, 1, 10, 1, 120.00, '2026-08-04 20:10:09', '2026-08-04 20:10:09'),
(78, 1, 10, 4, 150.00, '2026-08-04 20:10:09', '2026-08-04 20:10:09'),
(79, 1, 10, 2, 120.00, '2026-08-04 20:10:09', '2026-08-04 20:10:09'),
(80, 1, 10, 3, 127.50, '2026-08-04 20:10:09', '2026-08-04 20:10:09'),
(81, 1, 11, 1, 120.00, '2026-08-04 20:12:23', '2026-08-04 20:12:23'),
(82, 1, 11, 4, 150.00, '2026-08-04 20:12:23', '2026-08-04 20:12:23'),
(83, 1, 11, 2, 120.00, '2026-08-04 20:12:23', '2026-08-04 20:12:23'),
(84, 1, 11, 3, 127.50, '2026-08-04 20:12:23', '2026-08-04 20:12:23')
ON CONFLICT (id) DO NOTHING;

SELECT setval('fares_id_seq', (SELECT MAX(id) FROM fares));

-- 9. Departure Rules
INSERT INTO departure_rules (id, terminal_id, route_id, time_from, time_to, wait_minutes, label, created_at, updated_at) VALUES
(1, 1, NULL, '00:00:00', '05:00:00', 60, 'Late Night / Early Morning', '2026-06-03 11:18:52', '2026-06-18 16:01:31'),
(2, 1, NULL, '05:00:00', '09:00:00', 30, 'Morning Rush', '2026-06-03 11:18:52', '2026-06-03 11:18:52'),
(3, 1, NULL, '09:00:00', '12:00:00', 40, 'Mid-Morning', '2026-06-03 11:18:52', '2026-06-03 11:18:52'),
(4, 1, NULL, '12:00:00', '15:00:00', 40, 'Afternoon', '2026-06-03 11:18:52', '2026-06-03 11:18:52'),
(5, 1, NULL, '15:00:00', '18:00:00', 30, 'Afternoon Rush', '2026-06-03 11:18:52', '2026-06-03 11:18:52'),
(6, 1, NULL, '18:00:00', '21:00:00', 40, 'Evening', '2026-06-03 11:18:52', '2026-06-03 11:18:52'),
(7, 1, NULL, '21:00:00', '23:59:00', 60, 'Late Evening', '2026-06-03 11:18:52', '2026-06-03 11:18:52')
ON CONFLICT (id) DO NOTHING;

SELECT setval('departure_rules_id_seq', (SELECT MAX(id) FROM departure_rules));

-- 10. Announcements
INSERT INTO announcements (id, terminal_id, message, is_active, priority, sort_order, created_at, updated_at) VALUES
(1, 1, 'Welcome to Palompon Transit Terminal Management System. Please check live departure schedules and board assigned vehicles on time.', 1, 1, 0, '2026-06-12 08:45:10', '2026-06-22 14:23:55')
ON CONFLICT (id) DO NOTHING;

SELECT setval('announcements_id_seq', (SELECT MAX(id) FROM announcements));

-- 11. Sample Queue Entries
INSERT INTO queue (id, vehicle_id, route_id, plate_number, driver_name, operator_name, status, current_passengers, position, arrival_time, estimated_departure) VALUES
(1, 1, 9, '112131', 'jaylo', 'jaylo', 'boarding', 12, 1, CURRENT_TIMESTAMP - INTERVAL '15 minutes', CURRENT_TIMESTAMP + INTERVAL '15 minutes'),
(2, 2, 9, '22323', 'terrado', 'terrado', 'waiting', 0, 2, CURRENT_TIMESTAMP - INTERVAL '5 minutes', CURRENT_TIMESTAMP + INTERVAL '45 minutes')
ON CONFLICT (id) DO NOTHING;

SELECT setval('queue_id_seq', (SELECT MAX(id) FROM queue));

-- 12. Password Reset Tokens (clean initialized)
SELECT setval('password_reset_tokens_id_seq', 1, false);

-- 13. Audit Logs (clean initialized)
SELECT setval('audit_logs_id_seq', 1, false);

-- --------------------------------------------------------------------
-- Grant Permissions to jeepney_user
-- --------------------------------------------------------------------
DO $$
BEGIN
    IF EXISTS (SELECT FROM pg_roles WHERE rolname = 'jeepney_user') THEN
        GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO jeepney_user;
        GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO jeepney_user;
        ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO jeepney_user;
        ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO jeepney_user;
    END IF;
END
$$;
