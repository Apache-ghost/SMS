-- Create curriculum tables
CREATE TABLE IF NOT EXISTS `curriculum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `curriculum_name` varchar(255) NOT NULL,
  `grade_level` varchar(50) NOT NULL,
  `academic_year` varchar(50) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_subjects` int(11) DEFAULT 0,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `grade_level` (`grade_level`),
  KEY `academic_year` (`academic_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `curriculum_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `curriculum_id` int(11) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `hours_per_week` int(11) DEFAULT 4,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `curriculum_id` (`curriculum_id`),
  CONSTRAINT `curriculum_subjects_ibfk_1` FOREIGN KEY (`curriculum_id`) REFERENCES `curriculum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
