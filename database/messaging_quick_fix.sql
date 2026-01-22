-- Quick Database Fix for Messaging System
-- Run this in phpMyAdmin to ensure compatibility

-- Add missing columns if they don't exist
ALTER TABLE `parent_messages` 
  ADD COLUMN IF NOT EXISTS `message_body` TEXT AFTER `message`,
  ADD COLUMN IF NOT EXISTS `recipient_id` VARCHAR(40) AFTER `receiver_id`,
  ADD COLUMN IF NOT EXISTS `recipient_type` ENUM('teacher', 'parent', 'admin') AFTER `receiver_type`,
  ADD COLUMN IF NOT EXISTS `sent_date` DATETIME AFTER `created_at`;

-- Copy data from old columns to new columns if empty
UPDATE `parent_messages` 
SET 
  `message_body` = `message`,
  `recipient_id` = `receiver_id`,
  `recipient_type` = `receiver_type`,
  `sent_date` = `created_at`
WHERE `message_body` IS NULL OR `message_body` = '';

-- Ensure sent_date is populated for existing records
UPDATE `parent_messages` 
SET `sent_date` = `created_at`
WHERE `sent_date` IS NULL;

SELECT 'Database updated successfully!' AS status;
