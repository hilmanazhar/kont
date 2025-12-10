-- Admin Role Separation - Version 2
-- Run this to:
-- 1. Change Hilman to regular member
-- 2. Create new admin account "Admin Kontrakan"

-- Fix Hilman role to member (was accidentally cleared)
UPDATE users SET role = 'member' WHERE username = 'hilman';

-- Create admin account if not exists
-- Password: admin123 (same hash as kontrakan123)
INSERT INTO users (username, password_hash, display_name, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Kontrakan', 'admin')
ON DUPLICATE KEY UPDATE role = 'admin', display_name = 'Admin Kontrakan';

-- Verify changes
SELECT id, username, display_name, role FROM users ORDER BY role DESC, display_name;
