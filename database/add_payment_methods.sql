-- Add payment_methods JSON column to users table
-- Run this in phpMyAdmin or via setup.php

ALTER TABLE users ADD COLUMN payment_methods JSON DEFAULT NULL;

-- Migrate existing data to new JSON format
UPDATE users SET payment_methods = JSON_OBJECT(
    'banks', IF(bank_name IS NOT NULL AND bank_name != '', 
        JSON_ARRAY(JSON_OBJECT('name', bank_name, 'account', COALESCE(bank_account, ''))), 
        JSON_ARRAY()),
    'ewallets', IF(ewallet_type IS NOT NULL AND ewallet_type != '', 
        JSON_ARRAY(JSON_OBJECT('type', ewallet_type, 'number', COALESCE(ewallet_number, ''))), 
        JSON_ARRAY()),
    'qris', IF(qris_image IS NOT NULL AND qris_image != '', 
        JSON_ARRAY(qris_image), 
        JSON_ARRAY())
) WHERE payment_methods IS NULL;
