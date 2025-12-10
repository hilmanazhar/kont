-- Add is_rotation column to expenses for Listrik rotation tracking
-- Run this AFTER running schema.sql

ALTER TABLE expenses ADD COLUMN is_rotation BOOLEAN DEFAULT FALSE AFTER category;
