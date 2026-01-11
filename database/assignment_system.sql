-- Assignment Submission and Grading System
-- Date: January 3, 2026
-- NOTE: Uses existing assignments table, only creates submissions table

-- Create assignment submissions table
CREATE TABLE IF NOT EXISTS `assignment_submissions` (
  `submission_id` INT(20) NOT NULL AUTO_INCREMENT,
  `assignment_id` INT(20) NOT NULL,
  `student_id` VARCHAR(40) NOT NULL,
  `submission_text` TEXT NULL,
  `attachment` VARCHAR(255) NULL,
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('submitted', 'graded', 'late', 'resubmit') DEFAULT 'submitted',
  `marks_obtained` INT(10) NULL,
  `feedback` TEXT NULL,
  `graded_by` VARCHAR(40) NULL,
  `graded_at` DATETIME NULL,
  PRIMARY KEY (`submission_id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `student_id` (`student_id`),
  UNIQUE KEY `unique_submission` (`assignment_id`, `student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Success message
SELECT 'Assignment Submission System Setup Complete!' AS Status,
       'Table Created: assignment_submissions' AS Info,
       'Using existing assignments table' AS Note;
