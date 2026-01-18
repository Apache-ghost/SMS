-- Quick setup for announcements tables
-- Run this in phpMyAdmin selecting the '_sms' database

-- Check if table exists, if not create it
CREATE TABLE IF NOT EXISTS `announcements` (
  `announcement_id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `announcement_type` ENUM('general', 'academic', 'event', 'holiday', 'exam', 'fee', 'emergency', 
                           'achievement', 'sports', 'cultural', 'maintenance') DEFAULT 'general',
  `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
  `target_audience` ENUM('all', 'students', 'parents', 'teachers', 'staff', 'class_specific', 'department_specific') NOT NULL,
  `class` VARCHAR(10) COMMENT 'For class-specific announcements',
  `section` VARCHAR(10) COMMENT 'For section-specific announcements',
  `department_code` VARCHAR(10) COMMENT 'For department-specific announcements',
  `display_from` DATETIME NOT NULL,
  `display_until` DATETIME NOT NULL,
  `is_pinned` TINYINT(1) DEFAULT 0,
  `allow_comments` TINYINT(1) DEFAULT 0,
  `view_count` INT DEFAULT 0,
  `attachment_path` VARCHAR(500),
  `image_path` VARCHAR(500),
  `external_link` VARCHAR(500),
  `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft',
  `published_by` VARCHAR(20),
  `published_date` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `target_audience` (`target_audience`),
  KEY `announcement_type` (`announcement_type`),
  KEY `status` (`status`),
  KEY `display_dates` (`display_from`, `display_until`),
  KEY `class_section` (`class`, `section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Announcement recipients tracking
CREATE TABLE IF NOT EXISTS `announcement_recipients` (
  `recipient_id` INT AUTO_INCREMENT PRIMARY KEY,
  `announcement_id` INT NOT NULL,
  `user_id` VARCHAR(20) NOT NULL,
  `user_type` ENUM('student', 'parent', 'teacher', 'staff') NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `read_date` DATETIME,
  `acknowledged` TINYINT(1) DEFAULT 0,
  `acknowledged_date` DATETIME,
  KEY `announcement_id` (`announcement_id`),
  KEY `user_id` (`user_id`, `user_type`),
  FOREIGN KEY (`announcement_id`) REFERENCES `announcements`(`announcement_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Announcement comments (optional)
CREATE TABLE IF NOT EXISTS `announcement_comments` (
  `comment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `announcement_id` INT NOT NULL,
  `commenter_id` VARCHAR(20) NOT NULL,
  `commenter_type` ENUM('student', 'parent', 'teacher', 'admin') NOT NULL,
  `comment_text` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `announcement_id` (`announcement_id`),
  FOREIGN KEY (`announcement_id`) REFERENCES `announcements`(`announcement_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert a test announcement (optional - remove if not needed)
-- INSERT INTO `announcements` 
-- (`title`, `content`, `announcement_type`, `priority`, `target_audience`, `display_from`, `display_until`, `status`, `published_by`, `published_date`)
-- VALUES 
-- ('Welcome to School', 'This is a test announcement to verify the system is working.', 'general', 'normal', 'students', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'published', '1001', NOW());
