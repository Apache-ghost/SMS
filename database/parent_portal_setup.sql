-- Parent Portal Setup
-- Creates parent login account and links to student guardian
-- Date: January 3, 2026

-- Create parent_users table for login credentials
CREATE TABLE IF NOT EXISTS `parent_users` (
  `parent_user_id` INT(20) NOT NULL AUTO_INCREMENT,
  `guardian_id` VARCHAR(40) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `is_active` BOOLEAN DEFAULT 1,
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`parent_user_id`),
  KEY `guardian_id` (`guardian_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create parent_messages table for teacher-parent messaging
CREATE TABLE IF NOT EXISTS `parent_messages` (
  `message_id` INT(20) NOT NULL AUTO_INCREMENT,
  `sender_id` VARCHAR(40) NOT NULL,
  `sender_type` ENUM('teacher', 'parent', 'admin') NOT NULL,
  `receiver_id` VARCHAR(40) NOT NULL,
  `receiver_type` ENUM('teacher', 'parent', 'admin') NOT NULL,
  `student_id` VARCHAR(40) NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` BOOLEAN DEFAULT 0,
  `read_at` DATETIME NULL,
  `parent_id` VARCHAR(40) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create parent_notifications table for automated notifications
CREATE TABLE IF NOT EXISTS `parent_notifications` (
  `notification_id` INT(20) NOT NULL AUTO_INCREMENT,
  `parent_id` VARCHAR(40) NOT NULL,
  `student_id` VARCHAR(40) NOT NULL,
  `notification_type` ENUM('attendance', 'grades', 'assignment', 'announcement', 'event', 'message', 'fee', 'general') NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` BOOLEAN DEFAULT 0,
  `read_at` DATETIME NULL,
  `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
  `related_id` VARCHAR(40) NULL,
  `action_url` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `parent_id` (`parent_id`),
  KEY `student_id` (`student_id`),
  KEY `notification_type` (`notification_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default parent account (parent@gmail.com / 123)
-- Password hash for '123' using PASSWORD_DEFAULT (bcrypt)
INSERT INTO `parent_users` (`guardian_id`, `email`, `password`, `is_active`)
SELECT 
  id,
  'parent@gmail.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  1
FROM student_guardian
LIMIT 1
ON DUPLICATE KEY UPDATE email = email;

-- Link existing guardians to students (if not already linked)
-- This creates the parent-student relationship
CREATE TABLE IF NOT EXISTS `student_parent_link` (
  `link_id` INT(20) NOT NULL AUTO_INCREMENT,
  `student_id` VARCHAR(40) NOT NULL,
  `guardian_id` VARCHAR(40) NOT NULL,
  `relationship` VARCHAR(50) NOT NULL,
  `is_primary` BOOLEAN DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`link_id`),
  KEY `student_id` (`student_id`),
  KEY `guardian_id` (`guardian_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Populate student-parent links from existing data
INSERT INTO `student_parent_link` (`student_id`, `guardian_id`, `relationship`, `is_primary`)
SELECT s.id, sg.id, sg.relation, 1
FROM students s
JOIN student_guardian sg ON s.id = sg.id
ON DUPLICATE KEY UPDATE relationship = VALUES(relationship);

-- Success message
SELECT 'Parent Portal Setup Complete!' AS Status,
       'Login: parent@gmail.com' AS Email,
       'Password: 123' AS Password;
