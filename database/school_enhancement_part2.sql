-- =============================================
-- SCHOOL MANAGEMENT SYSTEM - ENHANCEMENT MODULE PART 2
-- Assessment, Grading, Assignments & Parent Communication
-- Compatible with existing _sms.sql database
-- Version: 2.0
-- Date: December 29, 2025
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =============================================
-- SECTION 4: ASSESSMENT AND GRADING SYSTEM
-- =============================================

-- Assessment types (CA1, CA2, Quiz, Assignment, etc.)
CREATE TABLE IF NOT EXISTS `assessment_types` (
  `assessment_type_id` INT AUTO_INCREMENT PRIMARY KEY,
  `type_code` VARCHAR(20) UNIQUE NOT NULL,
  `type_name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `weightage` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Percentage weightage in final grade',
  `max_marks` DECIMAL(6,2) DEFAULT 100.00,
  `is_mandatory` TINYINT(1) DEFAULT 1,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default assessment types
INSERT INTO `assessment_types` (`type_code`, `type_name`, `weightage`, `max_marks`, `is_mandatory`) VALUES
('CA1', 'Continuous Assessment 1', 10.00, 100.00, 1),
('CA2', 'Continuous Assessment 2', 10.00, 100.00, 1),
('QUIZ', 'Quiz', 10.00, 100.00, 0),
('ASSIGNMENT', 'Assignment', 10.00, 100.00, 0),
('PRACTICAL', 'Practical Exam', 20.00, 100.00, 0),
('PROJECT', 'Project Work', 10.00, 100.00, 0),
('MIDTERM', 'Mid-term Exam', 20.00, 100.00, 1),
('FINAL', 'Final Exam', 40.00, 100.00, 1),
('PRESENTATION', 'Presentation', 5.00, 100.00, 0),
('PARTICIPATION', 'Class Participation', 5.00, 100.00, 0);

-- Exam schedule table
CREATE TABLE IF NOT EXISTS `exam_schedule` (
  `schedule_id` INT AUTO_INCREMENT PRIMARY KEY,
  `exam_name` VARCHAR(200) NOT NULL,
  `assessment_type_id` INT NOT NULL,
  `course_code` VARCHAR(20) NOT NULL,
  `class` INT NOT NULL,
  `section` VARCHAR(50),
  `exam_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `duration_minutes` INT,
  `classroom_id` INT,
  `max_marks` DECIMAL(6,2) NOT NULL,
  `min_passing_marks` DECIMAL(6,2),
  `academic_year` VARCHAR(20) NOT NULL,
  `semester` INT NOT NULL,
  `invigilator_id` VARCHAR(40),
  `instructions` TEXT,
  `status` ENUM('scheduled', 'ongoing', 'completed', 'cancelled') DEFAULT 'scheduled',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `assessment_type_id` (`assessment_type_id`),
  KEY `course_code` (`course_code`),
  KEY `exam_date` (`exam_date`),
  FOREIGN KEY (`assessment_type_id`) REFERENCES `assessment_types`(`assessment_type_id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_code`) REFERENCES `courses`(`course_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Grade scale table
CREATE TABLE IF NOT EXISTS `grade_scale` (
  `grade_id` INT AUTO_INCREMENT PRIMARY KEY,
  `grade` VARCHAR(5) NOT NULL,
  `grade_point` DECIMAL(3,2) NOT NULL,
  `min_percentage` DECIMAL(5,2) NOT NULL,
  `max_percentage` DECIMAL(5,2) NOT NULL,
  `description` VARCHAR(100),
  `is_passing` TINYINT(1) DEFAULT 1,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert standard grading scale
INSERT INTO `grade_scale` (`grade`, `grade_point`, `min_percentage`, `max_percentage`, `description`, `is_passing`) VALUES
('A+', 4.00, 90.00, 100.00, 'Outstanding', 1),
('A', 3.75, 85.00, 89.99, 'Excellent', 1),
('A-', 3.50, 80.00, 84.99, 'Very Good', 1),
('B+', 3.25, 75.00, 79.99, 'Good', 1),
('B', 3.00, 70.00, 74.99, 'Above Average', 1),
('B-', 2.75, 65.00, 69.99, 'Average', 1),
('C+', 2.50, 60.00, 64.99, 'Below Average', 1),
('C', 2.00, 50.00, 59.99, 'Satisfactory', 1),
('D', 1.50, 40.00, 49.99, 'Pass', 1),
('E', 1.00, 35.00, 39.99, 'Marginal Pass', 0),
('F', 0.00, 0.00, 34.99, 'Fail', 0);

-- Student marks table (detailed marks entry)
CREATE TABLE IF NOT EXISTS `student_marks` (
  `mark_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(40) NOT NULL,
  `course_code` VARCHAR(20) NOT NULL,
  `class` INT NOT NULL,
  `section` VARCHAR(50),
  `assessment_type_id` INT NOT NULL,
  `academic_year` VARCHAR(20) NOT NULL,
  `semester` INT NOT NULL,
  `marks_obtained` DECIMAL(6,2) NOT NULL,
  `total_marks` DECIMAL(6,2) NOT NULL,
  `percentage` DECIMAL(5,2) GENERATED ALWAYS AS ((marks_obtained / total_marks) * 100) STORED,
  `is_absent` TINYINT(1) DEFAULT 0,
  `remarks` TEXT,
  `entered_by` VARCHAR(40),
  `entry_date` DATE,
  `verified_by` VARCHAR(40),
  `verification_date` DATE,
  `status` ENUM('draft', 'verified', 'published') DEFAULT 'draft',
  `published_date` DATE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_mark` (`student_id`, `course_code`, `assessment_type_id`, `academic_year`, `semester`),
  KEY `student_id` (`student_id`),
  KEY `course_code` (`course_code`),
  KEY `assessment_type_id` (`assessment_type_id`),
  FOREIGN KEY (`assessment_type_id`) REFERENCES `assessment_types`(`assessment_type_id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_code`) REFERENCES `courses`(`course_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Cumulative grades (calculated from all assessments)
CREATE TABLE IF NOT EXISTS `cumulative_grades` (
  `cumulative_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(40) NOT NULL,
  `course_code` VARCHAR(20) NOT NULL,
  `class` INT NOT NULL,
  `section` VARCHAR(50),
  `academic_year` VARCHAR(20) NOT NULL,
  `semester` INT NOT NULL,
  `ca_marks` DECIMAL(6,2) DEFAULT 0.00 COMMENT 'Continuous Assessment marks',
  `exam_marks` DECIMAL(6,2) DEFAULT 0.00 COMMENT 'Exam marks',
  `practical_marks` DECIMAL(6,2) DEFAULT 0.00,
  `project_marks` DECIMAL(6,2) DEFAULT 0.00,
  `total_marks` DECIMAL(6,2) NOT NULL,
  `max_marks` DECIMAL(6,2) DEFAULT 100.00,
  `percentage` DECIMAL(5,2) GENERATED ALWAYS AS ((total_marks / max_marks) * 100) STORED,
  `grade` VARCHAR(5),
  `grade_point` DECIMAL(3,2),
  `credit_hours` INT DEFAULT 3,
  `grade_points_earned` DECIMAL(6,2) GENERATED ALWAYS AS (grade_point * credit_hours) STORED,
  `result_status` ENUM('pass', 'fail', 'absent', 'withheld') DEFAULT 'pass',
  `calculated_date` DATE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_cumulative` (`student_id`, `course_code`, `academic_year`, `semester`),
  KEY `student_id` (`student_id`),
  KEY `course_code` (`course_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Semester results (overall performance)
CREATE TABLE IF NOT EXISTS `semester_results` (
  `result_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(40) NOT NULL,
  `class` INT NOT NULL,
  `section` VARCHAR(50),
  `academic_year` VARCHAR(20) NOT NULL,
  `semester` INT NOT NULL,
  `total_credits` INT NOT NULL,
  `credits_earned` INT NOT NULL,
  `total_grade_points` DECIMAL(8,2) NOT NULL,
  `gpa` DECIMAL(3,2) GENERATED ALWAYS AS (total_grade_points / total_credits) STORED,
  `cgpa` DECIMAL(3,2),
  `percentage` DECIMAL(5,2),
  `total_marks_obtained` DECIMAL(10,2),
  `total_max_marks` DECIMAL(10,2),
  `rank_in_class` INT,
  `total_students` INT,
  `overall_result` ENUM('pass', 'fail', 'distinction', 'honors') NOT NULL,
  `generated_date` DATE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_result` (`student_id`, `academic_year`, `semester`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Class rankings
CREATE TABLE IF NOT EXISTS `class_rankings` (
  `ranking_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(40) NOT NULL,
  `class` INT NOT NULL,
  `section` VARCHAR(50),
  `academic_year` VARCHAR(20) NOT NULL,
  `semester` INT NOT NULL,
  `ranking_type` ENUM('class', 'section', 'department', 'overall') DEFAULT 'class',
  `rank_position` INT NOT NULL,
  `total_students` INT NOT NULL,
  `gpa` DECIMAL(3,2),
  `percentage` DECIMAL(5,2),
  `total_marks` DECIMAL(10,2),
  `calculated_date` DATE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `student_id` (`student_id`),
  KEY `ranking` (`class`, `academic_year`, `semester`, `ranking_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Report card templates
CREATE TABLE IF NOT EXISTS `report_card_templates` (
  `template_id` INT AUTO_INCREMENT PRIMARY KEY,
  `template_name` VARCHAR(100) NOT NULL,
  `template_type` ENUM('detailed', 'summary', 'transcript') DEFAULT 'detailed',
  `layout_config` TEXT COMMENT 'JSON configuration',
  `header_template` TEXT,
  `footer_template` TEXT,
  `include_attendance` TINYINT(1) DEFAULT 1,
  `include_conduct` TINYINT(1) DEFAULT 1,
  `include_remarks` TINYINT(1) DEFAULT 1,
  `include_signature` TINYINT(1) DEFAULT 1,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default templates
INSERT INTO `report_card_templates` (`template_name`, `template_type`, `include_attendance`, `include_conduct`) VALUES
('Standard Report Card', 'detailed', 1, 1),
('Summary Report Card', 'summary', 0, 1),
('Academic Transcript', 'transcript', 0, 0);

-- Report cards generated
CREATE TABLE IF NOT EXISTS `report_cards` (
  `report_card_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(40) NOT NULL,
  `academic_year` VARCHAR(20) NOT NULL,
  `semester` INT NOT NULL,
  `template_id` INT NOT NULL,
  `report_file_path` VARCHAR(500),
  `generated_date` DATE,
  `generated_by` VARCHAR(40),
  `status` ENUM('draft', 'finalized', 'published', 'archived') DEFAULT 'draft',
  `published_date` DATE,
  `download_count` INT DEFAULT 0,
  `parent_viewed` TINYINT(1) DEFAULT 0,
  `parent_view_date` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `student_id` (`student_id`),
  KEY `template_id` (`template_id`),
  FOREIGN KEY (`template_id`) REFERENCES `report_card_templates`(`template_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Grade validation rules
CREATE TABLE IF NOT EXISTS `grade_validation_rules` (
  `rule_id` INT AUTO_INCREMENT PRIMARY KEY,
  `rule_name` VARCHAR(100) NOT NULL,
  `rule_type` VARCHAR(50) NOT NULL,
  `rule_condition` TEXT NOT NULL,
  `error_message` VARCHAR(255),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default validation rules
INSERT INTO `grade_validation_rules` (`rule_name`, `rule_type`, `rule_condition`, `error_message`) VALUES
('Marks Range', 'range', 'marks >= 0 AND marks <= total_marks', 'Marks must be between 0 and total marks'),
('Attendance Minimum', 'attendance', 'attendance_percentage >= 75', 'Minimum 75% attendance required'),
('Grade Consistency', 'consistency', 'grade matches percentage range', 'Grade does not match percentage');

-- Grade audit log
CREATE TABLE IF NOT EXISTS `grade_audit_log` (
  `audit_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(40) NOT NULL,
  `course_code` VARCHAR(20) NOT NULL,
  `assessment_type_id` INT,
  `action` ENUM('create', 'update', 'delete', 'publish', 'verify') NOT NULL,
  `old_marks` DECIMAL(6,2),
  `new_marks` DECIMAL(6,2),
  `old_grade` VARCHAR(5),
  `new_grade` VARCHAR(5),
  `changed_by` VARCHAR(40) NOT NULL,
  `change_reason` TEXT,
  `ip_address` VARCHAR(50),
  `changed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `student_id` (`student_id`),
  KEY `changed_by` (`changed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Grade statistics (class/subject-wise)
CREATE TABLE IF NOT EXISTS `grade_statistics` (
  `stat_id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_code` VARCHAR(20) NOT NULL,
  `class` INT NOT NULL,
  `section` VARCHAR(50),
  `academic_year` VARCHAR(20) NOT NULL,
  `semester` INT NOT NULL,
  `assessment_type_id` INT,
  `total_students` INT DEFAULT 0,
  `students_appeared` INT DEFAULT 0,
  `students_passed` INT DEFAULT 0,
  `students_failed` INT DEFAULT 0,
  `highest_marks` DECIMAL(6,2),
  `lowest_marks` DECIMAL(6,2),
  `average_marks` DECIMAL(6,2),
  `median_marks` DECIMAL(6,2),
  `pass_percentage` DECIMAL(5,2),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `course_code` (`course_code`),
  KEY `class` (`class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
