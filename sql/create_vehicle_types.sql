-- SQL to create the vehicle_types table if migrations cannot be run.
-- Run with: psql -U <user> -d jeepneynvans -f sql/create_vehicle_types.sql

CREATE TABLE IF NOT EXISTS vehicle_types (
  id SERIAL PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(50) NOT NULL UNIQUE,
  is_active SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Optional: seed initial types
INSERT INTO vehicle_types (name, slug, is_active)
VALUES
  ('Van', 'van', 1),
  ('Jeepney', 'jeepney', 1),
  ('Minibus', 'minibus', 1)
ON CONFLICT (slug) DO UPDATE SET
  name = EXCLUDED.name,
  is_active = EXCLUDED.is_active;
