-- Exam Management System Enhancement
-- Run this SQL to add exam results tracking

-- Update exams table to add exam_date and description if not exists
ALTER TABLE `exams` 
ADD COLUMN IF NOT EXISTS `exam_date` DATE NULL AFTER `section`,
ADD COLUMN IF NOT EXISTS `description` TEXT NULL AFTER `passing_marks`;

-- Create exam_results table
CREATE TABLE IF NOT EXISTS `exam_results` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `exam_id` VARCHAR(40) NOT NULL,
  `student_id` VARCHAR(20) NOT NULL,
  `marks_obtained` DECIMAL(10,2) NOT NULL,
  `grade` VARCHAR(5) NOT NULL,
  `status` ENUM('pass', 'fail') NOT NULL,
  `remarks` TEXT NULL,
  `published_date` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `exam_id` (`exam_id`),
  KEY `student_id` (`student_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`exam_id`) REFERENCES `exams`(`exam_id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_exam_date ON exams(exam_date);
CREATE INDEX IF NOT EXISTS idx_exam_class_section ON exams(class, section);
CREATE INDEX IF NOT EXISTS idx_results_student ON exam_results(student_id, exam_id);
CREATE INDEX IF NOT EXISTS idx_results_published ON exam_results(published_date);

-- Sample data (optional - remove if you don't want sample data)
-- INSERT INTO exams (exam_id, exam_title, subject, class, section, exam_date, total_marks, passing_marks, description) 
-- VALUES 
-- ('E17376847001', 'Mid-Term Mathematics', 'Mathematics', '10', 'A', '2026-02-15', '100', '40', 'Mid-term examination covering chapters 1-5'),
-- ('E17376847002', 'English Literature Test', 'English', '10', 'A', '2026-02-20', '50', '20', 'Test on Shakespeare and Modern Poetry');
