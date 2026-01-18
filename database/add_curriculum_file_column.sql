-- Add file_path column to curriculum_master table if it doesn't exist
ALTER TABLE `curriculum_master` 
ADD COLUMN IF NOT EXISTS `file_path` VARCHAR(500) NULL AFTER `total_credits`;

-- Verify the column was added
DESCRIBE `curriculum_master`;
