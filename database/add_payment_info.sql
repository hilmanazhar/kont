-- Add payment info columns to users table
-- Run this AFTER schema.sql

ALTER TABLE users ADD COLUMN bank_name VARCHAR(50) DEFAULT NULL;
ALTER TABLE users ADD COLUMN bank_account VARCHAR(50) DEFAULT NULL;
ALTER TABLE users ADD COLUMN ewallet_type VARCHAR(50) DEFAULT NULL;
ALTER TABLE users ADD COLUMN ewallet_number VARCHAR(50) DEFAULT NULL;
ALTER TABLE users ADD COLUMN qris_image VARCHAR(255) DEFAULT NULL;
