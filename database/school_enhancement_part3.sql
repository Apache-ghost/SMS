-- =============================================
-- SCHOOL MANAGEMENT SYSTEM - ENHANCEMENT MODULE PART 3
-- Assignments & Parent Communication
-- Compatible with existing _sms.sql database
-- Version: 2.0
-- Date: December 29, 2025
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =============================================
-- SECTION 5: ASSIGNMENT MANAGEMENT
-- =============================================

-- Assignments table
CREATE TABLE IF NOT EXISTS `assignments` (
  `assignment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `assignment_code` VARCHAR(50) UNIQUE NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `instructions` TEXT,
  `course_code` VARCHAR(20) NOT NULL,
  `class` INT NOT NULL,
  `section` VARCHAR(10),
  `teacher_id` VARCHAR(20),
  `assignment_type` ENUM('homework', 'project', 'lab', 'essay', 'presentation', 'quiz', 'practice') DEFAULT 'homework',
  `max_marks` DECIMAL(5,2) DEFAULT 100.00,
  `weightage` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Weightage in final grade (%)',
  `difficulty_level` ENUM('easy', 'medium', 'hard', 'advanced') DEFAULT 'medium',
  `estimated_duration` INT COMMENT 'Estimated time in minutes',
  `assigned_date` DATE NOT NULL,
  `due_date` DATETIME NOT NULL,
  `late_submission_allowed` TINYINT(1) DEFAULT 1,
  `late_penalty_per_day` DECIMAL(5,2) DEFAULT 5.00 COMMENT 'Percentage deduction per day',
  `max_late_days` INT DEFAULT 3,
  `allow_resubmission` TINYINT(1) DEFAULT 0,
  `max_resubmissions` INT DEFAULT 1,
  `submission_format` VARCHAR(255) COMMENT 'PDF, DOC, ZIP, etc.',
  `max_file_size` INT DEFAULT 10485760 COMMENT 'Max file size in bytes (default 10MB)',
  `academic_year` VARCHAR(10) NOT NULL,
  `semester` INT NOT NULL,
  `status` ENUM('draft', 'published', 'closed', 'cancelled') DEFAULT 'draft',
  `published_date` DATETIME,
  `total_submissions` INT DEFAULT 0,
  `graded_submissions` INT DEFAULT 0,
  `created_by` VARCHAR(20),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `course_code` (`course_code`),
  KEY `class_section` (`class`, `section`),
  KEY `teacher_id` (`teacher_id`),
  KEY `due_date` (`due_date`),
  KEY `academic_year_semester` (`academic_year`, `semester`),
  KEY `status` (`status`),
  FOREIGN KEY (`course_code`) REFERENCES `courses`(`course_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Assignment attachments table
CREATE TABLE IF NOT EXISTS `assignment_attachments` (
  `attachment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `assignment_id` INT NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_type` VARCHAR(50),
  `file_size` BIGINT COMMENT 'File size in bytes',
  `attachment_type` ENUM('reference', 'template', 'rubric', 'sample', 'other') DEFAULT 'reference',
  `description` TEXT,
  `uploaded_by` VARCHAR(20),
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `assignment_id` (`assignment_id`),
  FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`assignment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Student submissions table
CREATE TABLE IF NOT EXISTS `student_submissions` (
  `submission_id` INT AUTO_INCREMENT PRIMARY KEY,
  `assignment_id` INT NOT NULL,
  `student_id` VARCHAR(20) NOT NULL,
  `submission_number` INT DEFAULT 1 COMMENT 'For resubmissions',
  `submission_date` DATETIME NOT NULL,
  `submission_text` TEXT COMMENT 'Text submission or comments',
  `is_late` TINYINT(1) DEFAULT 0,
  `days_late` INT DEFAULT 0,
  `late_penalty_applied` DECIMAL(5,2) DEFAULT 0.00,
  `status` ENUM('submitted', 'graded', 'returned', 'resubmit_required') DEFAULT 'submitted',
  `plagiarism_score` DECIMAL(5,2) COMMENT 'Plagiarism percentage if checked',
  `word_count` INT,
  `ip_address` VARCHAR(50),
  `submission_method` ENUM('online', 'offline', 'email') DEFAULT 'online',
  `version` INT DEFAULT 1,
  `is_latest` TINYINT(1) DEFAULT 1,
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_latest_submission` (`assignment_id`, `student_id`, `is_latest`),
  KEY `assignment_id` (`assignment_id`),
  KEY `student_id` (`student_id`),
  KEY `status` (`status`),
  KEY `submission_date` (`submission_date`),
  FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`assignment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Submission attachments table
CREATE TABLE IF NOT EXISTS `submission_attachments` (
  `attachment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `submission_id` INT NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_type` VARCHAR(50),
  `file_size` BIGINT COMMENT 'File size in bytes',
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `submission_id` (`submission_id`),
  FOREIGN KEY (`submission_id`) REFERENCES `student_submissions`(`submission_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Assignment grades table
CREATE TABLE IF NOT EXISTS `assignment_grades` (
  `grade_id` INT AUTO_INCREMENT PRIMARY KEY,
  `submission_id` INT NOT NULL UNIQUE,
  `assignment_id` INT NOT NULL,
  `student_id` VARCHAR(20) NOT NULL,
  `marks_obtained` DECIMAL(6,2) NOT NULL,
  `max_marks` DECIMAL(6,2) NOT NULL,
  `percentage` DECIMAL(5,2) GENERATED ALWAYS AS ((marks_obtained / max_marks) * 100) STORED,
  `grade` VARCHAR(5),
  `feedback` TEXT,
  `private_notes` TEXT COMMENT 'Internal notes not visible to student',
  `grading_rubric` TEXT COMMENT 'Detailed rubric breakdown',
  `graded_by` VARCHAR(20),
  `graded_date` DATETIME,
  `last_modified_by` VARCHAR(20),
  `last_modified_date` DATETIME,
  `status` ENUM('draft', 'finalized', 'published') DEFAULT 'draft',
  `published_date` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `submission_id` (`submission_id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `student_id` (`student_id`),
  KEY `graded_by` (`graded_by`),
  KEY `status` (`status`),
  FOREIGN KEY (`submission_id`) REFERENCES `student_submissions`(`submission_id`) ON DELETE CASCADE,
  FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`assignment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Submission comments table (for detailed feedback threads)
CREATE TABLE IF NOT EXISTS `submission_comments` (
  `comment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `submission_id` INT NOT NULL,
  `commenter_id` VARCHAR(20) NOT NULL,
  `commenter_type` ENUM('teacher', 'student', 'admin') NOT NULL,
  `comment_text` TEXT NOT NULL,
  `parent_comment_id` INT COMMENT 'For threaded comments',
  `is_private` TINYINT(1) DEFAULT 0 COMMENT 'Private comments only visible to teachers',
  `commented_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `submission_id` (`submission_id`),
  KEY `parent_comment_id` (`parent_comment_id`),
  FOREIGN KEY (`submission_id`) REFERENCES `student_submissions`(`submission_id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_comment_id`) REFERENCES `submission_comments`(`comment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Assignment statistics table
CREATE TABLE IF NOT EXISTS `assignment_statistics` (
  `stat_id` INT AUTO_INCREMENT PRIMARY KEY,
  `assignment_id` INT NOT NULL UNIQUE,
  `total_students` INT DEFAULT 0,
  `total_submissions` INT DEFAULT 0,
  `on_time_submissions` INT DEFAULT 0,
  `late_submissions` INT DEFAULT 0,
  `pending_submissions` INT DEFAULT 0,
  `graded_count` INT DEFAULT 0,
  `pending_grading` INT DEFAULT 0,
  `highest_marks` DECIMAL(6,2),
  `lowest_marks` DECIMAL(6,2),
  `average_marks` DECIMAL(6,2),
  `median_marks` DECIMAL(6,2),
  `pass_rate` DECIMAL(5,2),
  `average_submission_time` INT COMMENT 'Average time taken in hours',
  `last_calculated` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `assignment_id` (`assignment_id`),
  FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`assignment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Assignment reminders table
CREATE TABLE IF NOT EXISTS `assignment_reminders` (
  `reminder_id` INT AUTO_INCREMENT PRIMARY KEY,
  `assignment_id` INT NOT NULL,
  `reminder_type` ENUM('due_soon', 'overdue', 'not_submitted', 'grade_published') NOT NULL,
  `recipient_type` ENUM('student', 'teacher', 'all') NOT NULL,
  `recipient_id` VARCHAR(20),
  `reminder_date` DATETIME NOT NULL,
  `message` TEXT,
  `is_sent` TINYINT(1) DEFAULT 0,
  `sent_date` DATETIME,
  `send_method` ENUM('email', 'sms', 'notification', 'all') DEFAULT 'notification',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `assignment_id` (`assignment_id`),
  KEY `reminder_date` (`reminder_date`),
  KEY `is_sent` (`is_sent`),
  FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`assignment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Assignment peer review table (optional feature)
CREATE TABLE IF NOT EXISTS `assignment_peer_reviews` (
  `review_id` INT AUTO_INCREMENT PRIMARY KEY,
  `assignment_id` INT NOT NULL,
  `submission_id` INT NOT NULL,
  `reviewer_student_id` VARCHAR(20) NOT NULL,
  `review_text` TEXT,
  `rating` INT CHECK (rating BETWEEN 1 AND 5),
  `review_criteria` TEXT COMMENT 'JSON format criteria scores',
  `is_anonymous` TINYINT(1) DEFAULT 1,
  `reviewed_date` DATETIME,
  `status` ENUM('pending', 'completed', 'approved', 'rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `assignment_id` (`assignment_id`),
  KEY `submission_id` (`submission_id`),
  KEY `reviewer_student_id` (`reviewer_student_id`),
  FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`assignment_id`) ON DELETE CASCADE,
  FOREIGN KEY (`submission_id`) REFERENCES `student_submissions`(`submission_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- SECTION 6: PARENT COMMUNICATION
-- =============================================

-- Parent portal access table
CREATE TABLE IF NOT EXISTS `parent_portal_access` (
  `portal_id` INT AUTO_INCREMENT PRIMARY KEY,
  `parent_id` VARCHAR(20) NOT NULL,
  `username` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20),
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login` DATETIME,
  `login_count` INT DEFAULT 0,
  `password_reset_token` VARCHAR(255),
  `password_reset_expires` DATETIME,
  `email_verified` TINYINT(1) DEFAULT 0,
  `phone_verified` TINYINT(1) DEFAULT 0,
  `two_factor_enabled` TINYINT(1) DEFAULT 0,
  `two_factor_secret` VARCHAR(100),
  `preferred_language` VARCHAR(10) DEFAULT 'en',
  `notification_preferences` TEXT COMMENT 'JSON format preferences',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `parent_id` (`parent_id`),
  KEY `email` (`email`),
  FOREIGN KEY (`parent_id`) REFERENCES `parent_guardian`(`guardian_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Parent messages table
CREATE TABLE IF NOT EXISTS `parent_messages` (
  `message_id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject` VARCHAR(255) NOT NULL,
  `message_body` TEXT NOT NULL,
  `sender_id` VARCHAR(20) NOT NULL,
  `sender_type` ENUM('teacher', 'admin', 'parent') NOT NULL,
  `recipient_id` VARCHAR(20) NOT NULL,
  `recipient_type` ENUM('teacher', 'admin', 'parent') NOT NULL,
  `student_id` VARCHAR(20) COMMENT 'Student context for the message',
  `message_type` ENUM('inquiry', 'concern', 'appreciation', 'complaint', 'general', 'urgent') DEFAULT 'general',
  `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
  `parent_message_id` INT COMMENT 'For threaded conversations',
  `is_read` TINYINT(1) DEFAULT 0,
  `read_date` DATETIME,
  `is_replied` TINYINT(1) DEFAULT 0,
  `is_archived` TINYINT(1) DEFAULT 0,
  `is_starred` TINYINT(1) DEFAULT 0,
  `scheduled_send` DATETIME COMMENT 'For scheduled messages',
  `sent_date` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `sender_id` (`sender_id`, `sender_type`),
  KEY `recipient_id` (`recipient_id`, `recipient_type`),
  KEY `student_id` (`student_id`),
  KEY `parent_message_id` (`parent_message_id`),
  KEY `is_read` (`is_read`),
  KEY `sent_date` (`sent_date`),
  FOREIGN KEY (`parent_message_id`) REFERENCES `parent_messages`(`message_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Message attachments table
CREATE TABLE IF NOT EXISTS `message_attachments` (
  `attachment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `message_id` INT NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_type` VARCHAR(50),
  `file_size` BIGINT,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `message_id` (`message_id`),
  FOREIGN KEY (`message_id`) REFERENCES `parent_messages`(`message_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Parent notifications table
CREATE TABLE IF NOT EXISTS `parent_notifications` (
  `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
  `parent_id` VARCHAR(20) NOT NULL,
  `student_id` VARCHAR(20),
  `notification_type` ENUM('grade_update', 'attendance_alert', 'assignment_due', 'assignment_grade', 
                           'exam_schedule', 'fee_reminder', 'announcement', 'event', 'conduct_issue', 
                           'achievement', 'meeting_request', 'leave_status', 'general') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
  `reference_type` VARCHAR(50) COMMENT 'Table name of reference',
  `reference_id` INT COMMENT 'ID of referenced record',
  `action_url` VARCHAR(500) COMMENT 'Link to view details',
  `is_read` TINYINT(1) DEFAULT 0,
  `read_date` DATETIME,
  `send_email` TINYINT(1) DEFAULT 1,
  `email_sent` TINYINT(1) DEFAULT 0,
  `email_sent_date` DATETIME,
  `send_sms` TINYINT(1) DEFAULT 0,
  `sms_sent` TINYINT(1) DEFAULT 0,
  `sms_sent_date` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME COMMENT 'Auto-delete after this date',
  KEY `parent_id` (`parent_id`),
  KEY `student_id` (`student_id`),
  KEY `notification_type` (`notification_type`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`parent_id`) REFERENCES `parent_guardian`(`guardian_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Announcements table
CREATE TABLE IF NOT EXISTS `announcements` (
  `announcement_id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `announcement_type` ENUM('general', 'academic', 'event', 'holiday', 'exam', 'fee', 'emergency', 
                           'achievement', 'sports', 'cultural', 'maintenance') DEFAULT 'general',
  `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
  `target_audience` ENUM('all', 'students', 'parents', 'teachers', 'staff', 'class_specific', 'department_specific') NOT NULL,
  `class` INT COMMENT 'For class-specific announcements',
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
  KEY `class_section` (`class`, `section`),
  FOREIGN KEY (`department_code`) REFERENCES `departments`(`department_code`) ON DELETE CASCADE
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

-- School calendar table
CREATE TABLE IF NOT EXISTS `school_calendar` (
  `event_id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_title` VARCHAR(255) NOT NULL,
  `event_description` TEXT,
  `event_type` ENUM('holiday', 'exam', 'meeting', 'parent_teacher_meeting', 'sports', 'cultural', 
                    'academic', 'workshop', 'seminar', 'competition', 'field_trip', 'vacation', 'other') NOT NULL,
  `event_category` ENUM('academic', 'co-curricular', 'administrative', 'general') DEFAULT 'general',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `start_time` TIME,
  `end_time` TIME,
  `is_all_day` TINYINT(1) DEFAULT 0,
  `location` VARCHAR(255),
  `organizer` VARCHAR(100),
  `target_audience` ENUM('all', 'students', 'parents', 'teachers', 'staff', 'class_specific', 'department_specific') NOT NULL,
  `class` INT COMMENT 'For class-specific events',
  `section` VARCHAR(10) COMMENT 'For section-specific events',
  `department_code` VARCHAR(10) COMMENT 'For department-specific events',
  `color_code` VARCHAR(7) DEFAULT '#3788d8' COMMENT 'Hex color for calendar display',
  `is_recurring` TINYINT(1) DEFAULT 0,
  `recurrence_pattern` TEXT COMMENT 'JSON format for recurring events',
  `max_participants` INT,
  `registration_required` TINYINT(1) DEFAULT 0,
  `registration_deadline` DATETIME,
  `is_cancelled` TINYINT(1) DEFAULT 0,
  `cancellation_reason` TEXT,
  `attachment_path` VARCHAR(500),
  `reminder_enabled` TINYINT(1) DEFAULT 1,
  `reminder_days_before` INT DEFAULT 1,
  `created_by` VARCHAR(20),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `event_type` (`event_type`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `target_audience` (`target_audience`),
  KEY `class_section` (`class`, `section`),
  FOREIGN KEY (`department_code`) REFERENCES `departments`(`department_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Calendar event registrations
CREATE TABLE IF NOT EXISTS `calendar_registrations` (
  `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT NOT NULL,
  `user_id` VARCHAR(20) NOT NULL,
  `user_type` ENUM('student', 'parent', 'teacher', 'staff') NOT NULL,
  `registration_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `attendance_status` ENUM('registered', 'attended', 'absent', 'cancelled') DEFAULT 'registered',
  `remarks` TEXT,
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`, `user_type`),
  FOREIGN KEY (`event_id`) REFERENCES `school_calendar`(`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Parent activity log
CREATE TABLE IF NOT EXISTS `parent_activity_log` (
  `log_id` INT AUTO_INCREMENT PRIMARY KEY,
  `parent_id` VARCHAR(20) NOT NULL,
  `activity_type` ENUM('login', 'logout', 'view_grades', 'view_attendance', 'view_assignments', 
                       'send_message', 'view_notice', 'download_report', 'update_profile', 'other') NOT NULL,
  `activity_description` VARCHAR(500),
  `ip_address` VARCHAR(50),
  `user_agent` VARCHAR(255),
  `session_id` VARCHAR(100),
  `activity_timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `parent_id` (`parent_id`),
  KEY `activity_type` (`activity_type`),
  KEY `activity_timestamp` (`activity_timestamp`),
  FOREIGN KEY (`parent_id`) REFERENCES `parent_guardian`(`guardian_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Announcement comments (if enabled)
CREATE TABLE IF NOT EXISTS `announcement_comments` (
  `comment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `announcement_id` INT NOT NULL,
  `commenter_id` VARCHAR(20) NOT NULL,
  `commenter_type` ENUM('student', 'parent', 'teacher', 'staff') NOT NULL,
  `comment_text` TEXT NOT NULL,
  `parent_comment_id` INT COMMENT 'For nested comments',
  `is_approved` TINYINT(1) DEFAULT 0,
  `approved_by` VARCHAR(20),
  `approved_date` DATETIME,
  `commented_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `announcement_id` (`announcement_id`),
  KEY `parent_comment_id` (`parent_comment_id`),
  FOREIGN KEY (`announcement_id`) REFERENCES `announcements`(`announcement_id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_comment_id`) REFERENCES `announcement_comments`(`comment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Message templates for automated notifications
CREATE TABLE IF NOT EXISTS `notification_templates` (
  `template_id` INT AUTO_INCREMENT PRIMARY KEY,
  `template_name` VARCHAR(100) UNIQUE NOT NULL,
  `template_type` VARCHAR(50) NOT NULL,
  `subject_template` VARCHAR(255),
  `message_template` TEXT NOT NULL,
  `variables` TEXT COMMENT 'JSON array of available variables',
  `is_active` TINYINT(1) DEFAULT 1,
  `language` VARCHAR(10) DEFAULT 'en',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default notification templates
INSERT INTO `notification_templates` (`template_name`, `template_type`, `subject_template`, `message_template`, `variables`) VALUES
('grade_published', 'grade_update', 'New Grade Published for {student_name}', 
 'Dear Parent,\n\nA new grade has been published for {student_name} in {course_name}.\nGrade: {grade}\nMarks: {marks_obtained}/{total_marks}\n\nPlease login to the parent portal to view complete details.\n\nBest Regards,\n{school_name}',
 '["student_name", "course_name", "grade", "marks_obtained", "total_marks", "school_name"]'),
('attendance_alert', 'attendance_alert', 'Attendance Alert for {student_name}', 
 'Dear Parent,\n\n{student_name} was marked {status} on {date}.\n\nIf you have any concerns, please contact the class teacher.\n\nBest Regards,\n{school_name}',
 '["student_name", "status", "date", "school_name"]'),
('assignment_due', 'assignment_due', 'Assignment Due Reminder for {student_name}', 
 'Dear Parent,\n\nThis is a reminder that the assignment "{assignment_title}" for {course_name} is due on {due_date}.\n\nPlease ensure {student_name} completes and submits the assignment on time.\n\nBest Regards,\n{school_name}',
 '["student_name", "assignment_title", "course_name", "due_date", "school_name"]'),
('exam_schedule', 'exam_schedule', 'Exam Schedule for {student_name}', 
 'Dear Parent,\n\nThe exam schedule for {exam_name} has been published.\nExam Date: {exam_date}\nSubject: {subject_name}\n\nPlease login to the parent portal to view the complete schedule.\n\nBest Regards,\n{school_name}',
 '["student_name", "exam_name", "exam_date", "subject_name", "school_name"]');

-- =============================================
-- PERFORMANCE INDEXES
-- =============================================

CREATE INDEX idx_assignment_due_date ON assignments(due_date, status);
CREATE INDEX idx_assignment_class ON assignments(class, section, academic_year);
CREATE INDEX idx_submission_student ON student_submissions(student_id, assignment_id);
CREATE INDEX idx_submission_status ON student_submissions(status, is_latest);
CREATE INDEX idx_assignment_grade_status ON assignment_grades(status, published_date);
CREATE INDEX idx_parent_messages_recipient ON parent_messages(recipient_id, recipient_type, is_read);
CREATE INDEX idx_parent_messages_sender ON parent_messages(sender_id, sender_type);
CREATE INDEX idx_parent_notifications ON parent_notifications(parent_id, is_read, created_at);
CREATE INDEX idx_announcements_display ON announcements(status, display_from, display_until);
CREATE INDEX idx_calendar_dates ON school_calendar(start_date, end_date, target_audience);

COMMIT;
