-- =============================================
-- SCHOOL MANAGEMENT SYSTEM - ENHANCEMENT MODULE
-- Compatible with existing _sms.sql database
-- Version: 2.0
-- Date: December 29, 2025
-- =============================================

-- This script adds new academic management features without modifying existing tables
-- All new tables have unique names to avoid conflicts

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =============================================
-- SECTION 1: DEPARTMENTS AND COURSES
-- =============================================

-- Departments table (ICT, BMS, Renewable Energy, Journalism)
CREATE TABLE IF NOT EXISTS `departments` (
  `department_id` INT AUTO_INCREMENT PRIMARY KEY,
  `department_code` VARCHAR(10) UNIQUE NOT NULL,
  `department_name` VARCHAR(100) NOT NULL,
  `department_head` VARCHAR(40),
  `description` TEXT,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default departments
INSERT INTO `departments` (`department_code`, `department_name`, `description`, `is_active`) VALUES
('ICT', 'Information and Communication Technology', 'Computer Science and IT programs', 1),
('BMS', 'Business Management Studies', 'Business and Management programs', 1),
('REN', 'Renewable Energy', 'Renewable Energy and Sustainability programs', 1),
('JMC', 'Journalism and Mass Communication', 'Media and Communication programs', 1);

-- Courses table (enhanced version, won't conflict with subjects)
CREATE TABLE IF NOT EXISTS `courses` (
  `course_id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_code` VARCHAR(20) UNIQUE NOT NULL,
  `course_name` VARCHAR(255) NOT NULL,
  `department_code` VARCHAR(10),
  `course_type` ENUM('core', 'elective', 'lab', 'project') DEFAULT 'core',
  `credit_hours` INT DEFAULT 3,
  `level` INT,
  `semester_offered` VARCHAR(20),
  `max_marks` INT DEFAULT 100,
  `min_passing_marks` INT DEFAULT 40,
  `description` TEXT,
  `syllabus_file` VARCHAR(500),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`department_code`) REFERENCES `departments`(`department_code`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Classrooms/Venues table
CREATE TABLE IF NOT EXISTS `classrooms` (
  `classroom_id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_code` VARCHAR(20) UNIQUE NOT NULL,
  `room_name` VARCHAR(100) NOT NULL,
  `building` VARCHAR(100),
  `floor` INT,
  `capacity` INT,
  `room_type` ENUM('classroom', 'lab', 'auditorium', 'library', 'sports') DEFAULT 'classroom',
  `facilities` TEXT,
  `is_available` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default classrooms
INSERT INTO `classrooms` (`room_code`, `room_name`, `building`, `capacity`, `room_type`) VALUES
('R101', 'Room 101', 'Main Building', 40, 'classroom'),
('R102', 'Room 102', 'Main Building', 40, 'classroom'),
('LAB1', 'Computer Lab 1', 'ICT Block', 30, 'lab'),
('LAB2', 'Science Lab', 'Science Block', 35, 'lab'),
('AUD1', 'Main Auditorium', 'Admin Block', 200, 'auditorium'),
('LIB1', 'Central Library', 'Library Block', 100, 'library'),
('SPORT1', 'Sports Ground', 'Sports Complex', 500, 'sports');

-- =============================================
-- SECTION 2: STUDENT MANAGEMENT ENHANCEMENTS
-- =============================================

-- Parent/Guardian table (enhanced version)
CREATE TABLE IF NOT EXISTS `parent_guardian` (
  `guardian_id` VARCHAR(20) PRIMARY KEY,
  `fname` VARCHAR(100) NOT NULL,
  `lname` VARCHAR(100) NOT NULL,
  `relationship` VARCHAR(50),
  `occupation` VARCHAR(100),
  `annual_income` DECIMAL(12,2),
  `phone` VARCHAR(20) NOT NULL,
  `alternate_phone` VARCHAR(20),
  `email` VARCHAR(100),
  `address` VARCHAR(500),
  `city` VARCHAR(100),
  `state` VARCHAR(100),
  `zip` VARCHAR(20),
  `nationality` VARCHAR(50) DEFAULT 'Cameroon',
  `aadhar_no` VARCHAR(20),
  `is_primary_contact` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Student-Parent linking table
CREATE TABLE IF NOT EXISTS `student_parent` (
  `link_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(40) NOT NULL,
  `parent_id` VARCHAR(20) NOT NULL,
  `relation_type` ENUM('father', 'mother', 'guardian', 'other') NOT NULL,
  `is_emergency_contact` TINYINT(1) DEFAULT 0,
  `can_pickup` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `student_id` (`student_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Student documents table
CREATE TABLE IF NOT EXISTS `student_documents` (
  `document_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(40) NOT NULL,
  `document_type` VARCHAR(100) NOT NULL,
  `document_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` BIGINT,
  `uploaded_by` VARCHAR(40),
  `upload_date` DATE,
  `expiry_date` DATE,
  `is_verified` TINYINT(1) DEFAULT 0,
  `verified_by` VARCHAR(40),
  `verified_date` DATE,
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Student academic history
CREATE TABLE IF NOT EXISTS `student_academic_history` (
  `history_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(40) NOT NULL,
  `academic_year` VARCHAR(20) NOT NULL,
  `class` VARCHAR(20) NOT NULL,
  `section` VARCHAR(50),
  `roll_no` VARCHAR(20),
  `result` ENUM('pass', 'fail', 'promoted', 'detained', 'transferred') NOT NULL,
  `total_marks_obtained` DECIMAL(8,2),
  `total_max_marks` DECIMAL(8,2),
  `percentage` DECIMAL(5,2),
  `grade` VARCHAR(10),
  `rank` INT,
  `attendance_percentage` DECIMAL(5,2),
  `conduct_grade` VARCHAR(10),
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Student transfer records
CREATE TABLE IF NOT EXISTS `student_transfers` (
  `transfer_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(40) NOT NULL,
  `transfer_type` ENUM('incoming', 'outgoing', 'internal') NOT NULL,
  `from_school` VARCHAR(200),
  `to_school` VARCHAR(200),
  `from_class` VARCHAR(20),
  `to_class` VARCHAR(20),
  `transfer_date` DATE NOT NULL,
  `reason` TEXT,
  `tc_issued` TINYINT(1) DEFAULT 0,
  `tc_number` VARCHAR(50),
  `tc_issue_date` DATE,
  `documents_transferred` TEXT,
  `fee_clearance` TINYINT(1) DEFAULT 0,
  `library_clearance` TINYINT(1) DEFAULT 0,
  `status` ENUM('pending', 'approved', 'completed', 'cancelled') DEFAULT 'pending',
  `approved_by` VARCHAR(40),
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Parent-teacher meetings
CREATE TABLE IF NOT EXISTS `parent_teacher_meetings` (
  `meeting_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(40) NOT NULL,
  `parent_id` VARCHAR(20) NOT NULL,
  `teacher_id` VARCHAR(40),
  `meeting_date` DATETIME NOT NULL,
  `meeting_type` ENUM('scheduled', 'emergency', 'routine', 'progress_review') DEFAULT 'scheduled',
  `agenda` TEXT,
  `discussion_points` TEXT,
  `action_items` TEXT,
  `next_meeting_date` DATE,
  `status` ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
  `conducted_by` VARCHAR(40),
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `student_id` (`student_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- SECTION 3: CURRICULUM AND TIMETABLE MANAGEMENT
-- =============================================

-- Curriculum master table
CREATE TABLE IF NOT EXISTS `curriculum_master` (
  `curriculum_id` INT AUTO_INCREMENT PRIMARY KEY,
  `curriculum_name` VARCHAR(200) NOT NULL,
  `academic_year` VARCHAR(20) NOT NULL,
  `class` INT NOT NULL,
  `department_code` VARCHAR(10),
  `total_credits` INT,
  `status` ENUM('draft', 'approved', 'active', 'archived') DEFAULT 'draft',
  `approved_by` VARCHAR(40),
  `approved_date` DATE,
  `effective_from` DATE,
  `effective_to` DATE,
  `created_by` VARCHAR(40),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `academic_year` (`academic_year`),
  KEY `class` (`class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Curriculum subjects mapping
CREATE TABLE IF NOT EXISTS `curriculum_subjects` (
  `curriculum_subject_id` INT AUTO_INCREMENT PRIMARY KEY,
  `curriculum_id` INT NOT NULL,
  `course_code` VARCHAR(20) NOT NULL,
  `class` INT NOT NULL,
  `semester` INT,
  `is_mandatory` TINYINT(1) DEFAULT 1,
  `min_attendance_required` DECIMAL(5,2) DEFAULT 75.00,
  `theory_hours` INT DEFAULT 0,
  `practical_hours` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `curriculum_id` (`curriculum_id`),
  KEY `course_code` (`course_code`),
  FOREIGN KEY (`curriculum_id`) REFERENCES `curriculum_master`(`curriculum_id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_code`) REFERENCES `courses`(`course_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Subject-Teacher assignments
CREATE TABLE IF NOT EXISTS `subject_teachers` (
  `assignment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `curriculum_subject_id` INT NOT NULL,
  `teacher_id` VARCHAR(40) NOT NULL,
  `class` INT NOT NULL,
  `section` VARCHAR(50),
  `academic_year` VARCHAR(20) NOT NULL,
  `is_primary_teacher` TINYINT(1) DEFAULT 1,
  `assigned_from` DATE,
  `assigned_to` DATE,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `curriculum_subject_id` (`curriculum_subject_id`),
  KEY `teacher_id` (`teacher_id`),
  FOREIGN KEY (`curriculum_subject_id`) REFERENCES `curriculum_subjects`(`curriculum_subject_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Timetable master
CREATE TABLE IF NOT EXISTS `timetable_master` (
  `timetable_id` INT AUTO_INCREMENT PRIMARY KEY,
  `academic_year` VARCHAR(20) NOT NULL,
  `semester` INT,
  `class` INT NOT NULL,
  `section` VARCHAR(50) NOT NULL,
  `effective_from` DATE NOT NULL,
  `effective_to` DATE,
  `status` ENUM('draft', 'approved', 'active', 'archived') DEFAULT 'draft',
  `approved_by` VARCHAR(40),
  `approved_date` DATE,
  `created_by` VARCHAR(40),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `academic_year` (`academic_year`),
  KEY `class_section` (`class`, `section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Timetable periods
CREATE TABLE IF NOT EXISTS `timetable_periods` (
  `period_id` INT AUTO_INCREMENT PRIMARY KEY,
  `timetable_id` INT NOT NULL,
  `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
  `period_number` INT NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `curriculum_subject_id` INT,
  `teacher_id` VARCHAR(40),
  `classroom_id` INT,
  `period_type` ENUM('lecture', 'lab', 'tutorial', 'break', 'sports', 'library') DEFAULT 'lecture',
  `is_break` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `timetable_id` (`timetable_id`),
  KEY `curriculum_subject_id` (`curriculum_subject_id`),
  FOREIGN KEY (`timetable_id`) REFERENCES `timetable_master`(`timetable_id`) ON DELETE CASCADE,
  FOREIGN KEY (`curriculum_subject_id`) REFERENCES `curriculum_subjects`(`curriculum_subject_id`) ON DELETE SET NULL,
  FOREIGN KEY (`classroom_id`) REFERENCES `classrooms`(`classroom_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Timetable conflicts log
CREATE TABLE IF NOT EXISTS `timetable_conflicts` (
  `conflict_id` INT AUTO_INCREMENT PRIMARY KEY,
  `timetable_id` INT NOT NULL,
  `conflict_type` ENUM('teacher_clash', 'venue_clash', 'student_clash') NOT NULL,
  `period_id_1` INT NOT NULL,
  `period_id_2` INT,
  `conflict_description` TEXT,
  `resolution_status` ENUM('pending', 'resolved', 'ignored') DEFAULT 'pending',
  `resolved_by` VARCHAR(40),
  `resolved_date` DATE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `timetable_id` (`timetable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Substitute teacher assignments
CREATE TABLE IF NOT EXISTS `substitute_teachers` (
  `substitute_id` INT AUTO_INCREMENT PRIMARY KEY,
  `original_teacher_id` VARCHAR(40) NOT NULL,
  `substitute_teacher_id` VARCHAR(40) NOT NULL,
  `curriculum_subject_id` INT NOT NULL,
  `class` INT NOT NULL,
  `section` VARCHAR(50),
  `substitution_date` DATE NOT NULL,
  `start_time` TIME,
  `end_time` TIME,
  `reason` TEXT,
  `status` ENUM('pending', 'approved', 'completed', 'cancelled') DEFAULT 'pending',
  `approved_by` VARCHAR(40),
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `original_teacher_id` (`original_teacher_id`),
  KEY `substitute_teacher_id` (`substitute_teacher_id`),
  KEY `curriculum_subject_id` (`curriculum_subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
