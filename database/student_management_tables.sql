-- Student Management Enhancement Tables
-- Run this script to add new tables for admissions, promotions, and transfers
-- Date: January 3, 2026

-- Drop existing tables if they exist (will recreate them fresh)
DROP TABLE IF EXISTS `student_admissions`;
DROP TABLE IF EXISTS `student_enrollments`;
DROP TABLE IF EXISTS `student_promotions`;
DROP TABLE IF EXISTS `student_transfers`;

-- Table for student admissions tracking
CREATE TABLE `student_admissions` (
  `admission_id` INT(20) NOT NULL AUTO_INCREMENT,
  `student_id` VARCHAR(40) NOT NULL,
  `application_no` VARCHAR(50) NOT NULL,
  `application_date` DATE NOT NULL,
  `admission_for_class` VARCHAR(20) NOT NULL,
  `admission_for_section` VARCHAR(50),
  `admission_status` ENUM('pending', 'approved', 'rejected', 'waitlisted') DEFAULT 'pending',
  `interview_date` DATE,
  `interview_notes` TEXT,
  `entrance_test_score` DECIMAL(5,2),
  `approved_by` VARCHAR(40),
  `approved_date` DATE,
  `rejection_reason` TEXT,
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admission_id`),
  UNIQUE KEY `application_no` (`application_no`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for student enrollment history
CREATE TABLE `student_enrollments` (
  `enrollment_id` INT(20) NOT NULL AUTO_INCREMENT,
  `student_id` VARCHAR(40) NOT NULL,
  `academic_year` VARCHAR(20) NOT NULL,
  `class` VARCHAR(20) NOT NULL,
  `section` VARCHAR(50) NOT NULL,
  `roll_no` VARCHAR(20),
  `enrollment_date` DATE NOT NULL,
  `enrollment_status` ENUM('active', 'completed', 'discontinued') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`enrollment_id`),
  KEY `student_id` (`student_id`),
  KEY `academic_year` (`academic_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for student promotions
CREATE TABLE `student_promotions` (
  `promotion_id` INT(20) NOT NULL AUTO_INCREMENT,
  `student_id` VARCHAR(40) NOT NULL,
  `from_class` VARCHAR(20) NOT NULL,
  `from_section` VARCHAR(50) NOT NULL,
  `to_class` VARCHAR(20) NOT NULL,
  `to_section` VARCHAR(50) NOT NULL,
  `academic_year_from` VARCHAR(20) NOT NULL,
  `academic_year_to` VARCHAR(20) NOT NULL,
  `promotion_date` DATE NOT NULL,
  `promotion_status` ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
  `promotion_type` ENUM('regular', 'skip', 'detention') DEFAULT 'regular',
  `promoted_by` VARCHAR(40),
  `approved_by` VARCHAR(40),
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`promotion_id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for student transfers
CREATE TABLE `student_transfers` (
  `transfer_id` INT(20) NOT NULL AUTO_INCREMENT,
  `student_id` VARCHAR(40) NOT NULL,
  `transfer_type` ENUM('incoming', 'outgoing', 'internal') NOT NULL,
  `from_school` VARCHAR(200),
  `to_school` VARCHAR(200),
  `from_class` VARCHAR(20),
  `from_section` VARCHAR(50),
  `to_class` VARCHAR(20),
  `to_section` VARCHAR(50),
  `transfer_date` DATE NOT NULL,
  `request_date` DATE NOT NULL,
  `transfer_status` ENUM('requested', 'approved', 'rejected', 'completed') DEFAULT 'requested',
  `transfer_reason` TEXT,
  `tc_issued` BOOLEAN DEFAULT 0,
  `tc_issue_date` DATE,
  `tc_number` VARCHAR(50),
  `requested_by` VARCHAR(40),
  `approved_by` VARCHAR(40),
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`transfer_id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample data for testing (optional)
-- You can uncomment these after running the table creation

-- INSERT INTO student_admissions (student_id, application_no, application_date, admission_for_class, admission_status) 
-- SELECT id, CONCAT('APP', YEAR(NOW()), LPAD(FLOOR(RAND() * 9999), 4, '0')), CURDATE(), class, 'approved' 
-- FROM students LIMIT 5;

-- Success message
SELECT 'Student Management Tables Created Successfully!' AS Status;
