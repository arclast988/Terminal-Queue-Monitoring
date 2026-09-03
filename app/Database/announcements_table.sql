-- Run this in PostgreSQL to create the announcements table
-- if the migration has not been run.

CREATE TABLE IF NOT EXISTS announcements (
  id SERIAL PRIMARY KEY,
  message TEXT,
  is_active SMALLINT DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT NULL
);

CREATE INDEX IF NOT EXISTS announcements_is_active_index ON announcements (is_active);
