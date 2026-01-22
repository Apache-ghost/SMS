-- Admin-Parent Messaging System - Database Update
-- This ensures the parent_messages table has all necessary fields
-- Date: January 21, 2026

-- Update parent_messages table structure to support all features
ALTER TABLE `parent_messages` 
  ADD COLUMN IF NOT EXISTS `message_body` TEXT AFTER `message`,
  ADD COLUMN IF NOT EXISTS `recipient_id` VARCHAR(40) AFTER `receiver_id`,
  ADD COLUMN IF NOT EXISTS `recipient_type` ENUM('teacher', 'parent', 'admin') AFTER `receiver_type`,
  ADD COLUMN IF NOT EXISTS `sent_date` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `created_at`,
  ADD COLUMN IF NOT EXISTS `is_archived` BOOLEAN DEFAULT 0 AFTER `is_read`,
  ADD COLUMN IF NOT EXISTS `is_starred` BOOLEAN DEFAULT 0 AFTER `is_archived`,
  ADD COLUMN IF NOT EXISTS `is_replied` BOOLEAN DEFAULT 0 AFTER `is_starred`,
  ADD COLUMN IF NOT EXISTS `parent_message_id` INT(20) NULL AFTER `message_body`,
  ADD COLUMN IF NOT EXISTS `message_type` ENUM('general', 'attendance', 'performance', 'behavior', 'fee', 'other') DEFAULT 'general' AFTER `subject`,
  ADD COLUMN IF NOT EXISTS `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal' AFTER `message_type`,
  ADD COLUMN IF NOT EXISTS `read_date` DATETIME NULL AFTER `read_at`;

-- Add foreign key for threaded messages (replies)
ALTER TABLE `parent_messages`
  ADD CONSTRAINT `fk_parent_message` 
  FOREIGN KEY (`parent_message_id`) 
  REFERENCES `parent_messages`(`message_id`) 
  ON DELETE CASCADE
  ON UPDATE CASCADE;

-- Create message_attachments table if not exists
CREATE TABLE IF NOT EXISTS `message_attachments` (
  `attachment_id` INT(20) NOT NULL AUTO_INCREMENT,
  `message_id` INT(20) NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_type` VARCHAR(100) NULL,
  `file_size` INT(20) NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attachment_id`),
  KEY `message_id` (`message_id`),
  CONSTRAINT `fk_message_attachment` 
    FOREIGN KEY (`message_id`) 
    REFERENCES `parent_messages`(`message_id`) 
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_parent_messages_recipient` 
  ON `parent_messages`(`recipient_id`, `recipient_type`, `is_read`);
  
CREATE INDEX IF NOT EXISTS `idx_parent_messages_sender` 
  ON `parent_messages`(`sender_id`, `sender_type`);
  
CREATE INDEX IF NOT EXISTS `idx_parent_messages_student` 
  ON `parent_messages`(`student_id`);

-- Update existing messages to use new column names if they don't have them
UPDATE `parent_messages` 
SET 
  `message_body` = `message` 
WHERE `message_body` IS NULL OR `message_body` = '';

UPDATE `parent_messages` 
SET 
  `recipient_id` = `receiver_id`,
  `recipient_type` = `receiver_type`
WHERE `recipient_id` IS NULL OR `recipient_id` = '';

UPDATE `parent_messages` 
SET 
  `sent_date` = `created_at`
WHERE `sent_date` IS NULL;

-- Success message
SELECT 'Admin-Parent Messaging System Database Updated Successfully!' AS Status;
