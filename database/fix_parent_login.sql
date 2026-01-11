-- Fix Parent Login - Direct Insert
-- This script creates the parent_users table and inserts a test account
-- Date: January 3, 2026

-- Ensure parent_users table exists
CREATE TABLE IF NOT EXISTS `parent_users` (
  `parent_user_id` INT(20) NOT NULL AUTO_INCREMENT,
  `guardian_id` VARCHAR(40) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `is_active` BOOLEAN DEFAULT 1,
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`parent_user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `guardian_id` (`guardian_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Delete existing parent account if it exists
DELETE FROM `parent_users` WHERE `email` = 'parent@gmail.com';

-- Insert parent account directly (no JOIN required)
-- Email: parent@gmail.com
-- Password: 123
-- Password hash generated: January 3, 2026
INSERT INTO `parent_users` (`guardian_id`, `email`, `password`, `is_active`)
VALUES ('S1718791292', 'parent@gmail.com', '$2y$10$M.qKn9Xkke7irIdZ.TBnGuuhxQIqDPvHo4OC6FUIcZVM9Wwvzb3c.', 1);

-- Verify the insertion
SELECT 
  parent_user_id,
  guardian_id,
  email,
  'Password hash exists' as password_status,
  is_active,
  created_at
FROM parent_users 
WHERE email = 'parent@gmail.com';
