-- =============================================================================
-- CHARUSAT STUDENTHUB - RELATIONAL DATABASE SCHEMA & SEED DATA
-- Practical 8: Database Schema Design, Normalization, Constraints & Seed Data
-- Database Engine: MySQL Server 9.0 (InnoDB)
-- Character Set: utf8mb4 / Collation: utf8mb4_0900_ai_ci
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_0900_ai_ci;

USE `studenthub`;

-- -----------------------------------------------------------------------------
-- TABLE 1: students
-- Role: Master table storing registered student details.
-- Constraints:
--   - PRIMARY KEY (student_id): Unique integer identifier with AUTO_INCREMENT.
--   - UNIQUE KEY (email): Prevents multiple accounts with the same email.
--   - NOT NULL on name, email, course to preserve data integrity.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
  `student_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `course` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------------------------------
-- TABLE 2: events
-- Role: Master table storing campus event details.
-- Constraints:
--   - PRIMARY KEY (event_id): Unique integer identifier with AUTO_INCREMENT.
--   - NOT NULL on event_name.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` INT NOT NULL AUTO_INCREMENT,
  `event_name` VARCHAR(150) NOT NULL,
  `event_date` DATE DEFAULT NULL,
  `location` VARCHAR(150) DEFAULT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------------------------------
-- TABLE 3: registrations
-- Role: Associative / Junction table connecting students and events (Many-to-Many).
-- Constraints:
--   - PRIMARY KEY (registration_id): Unique integer identifier with AUTO_INCREMENT.
--   - FOREIGN KEY (student_id): References students(student_id).
--   - FOREIGN KEY (event_id): References events(event_id).
--   - COMPOSITE UNIQUE KEY (student_id, event_id): Enforces business rule
--     that prevents duplicate registrations by the same student for the same event.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `registrations` (
  `registration_id` INT NOT NULL AUTO_INCREMENT,
  `student_id` INT NOT NULL,
  `event_id` INT NOT NULL,
  `registration_date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`registration_id`),
  UNIQUE KEY `student_id` (`student_id`, `event_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- -----------------------------------------------------------------------------
-- TABLE 4: users
-- Role: Master user account table for Practical 9 (Secure User Registration).
-- Constraints:
--   - PRIMARY KEY (id): Unique integer identifier with AUTO_INCREMENT.
--   - UNIQUE KEY (email): Enforces email uniqueness across all user accounts.
--   - NOT NULL on name, email, password to preserve credential integrity.
--   - password: Holds secure bcrypt hash generated via password_hash().
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =============================================================================
-- SAMPLE SEED DATA
-- Using INSERT IGNORE so this script is idempotent (safe to import multiple times).
-- =============================================================================

-- 1. Sample Students (preserves existing records 1 & 2)
INSERT IGNORE INTO `students` (`student_id`, `name`, `email`, `course`) VALUES
(1, 'Mahek Tandel', '25dce112@charusat.edu.in', 'Computer Engineering'),
(2, 'Test Student', 'student@example.com', 'Computer Engineering'),
(3, 'Aarav Patel', 'aarav.ce@charusat.edu.in', 'Computer Engineering'),
(4, 'Ananya Sharma', 'ananya.s@charusat.edu.in', 'Computer Engineering'),
(5, 'Rohan Mehta', 'rohan.it@charusat.edu.in', 'Information Technology'),
(6, 'Diya Joshi', 'diya.j@charusat.edu.in', 'Computer Engineering');

-- 2. Sample Events (preserves existing record 1)
INSERT IGNORE INTO `events` (`event_id`, `event_name`, `event_date`, `location`) VALUES
(1, 'Tech Fest', '2026-11-15', 'DEPSTAR Campus'),
(2, 'Technical Workshop on AI & ML', '2026-08-15', 'Computer Lab 3'),
(3, 'Cultural Fest - Spandan', '2026-08-22', 'College Ground'),
(4, 'Web Development Bootcamp', '2026-09-01', 'DEPSTAR Lab 1'),
(5, 'Robotics League Championship', '2026-09-10', 'Central Seminar Hall');

-- 3. Sample Valid Registrations
-- Notice each row references valid student_ids (1, 3, 4, 5) and valid event_ids (1, 2, 4, 5)
-- Duplicate pairs are prevented by the UNIQUE KEY constraint.
INSERT IGNORE INTO `registrations` (`registration_id`, `student_id`, `event_id`, `registration_date`) VALUES
(1, 1, 1, '2026-10-13 13:10:48'),
(2, 1, 5, '2026-10-13 13:12:51'),
(3, 3, 2, '2026-10-13 13:13:01'),
(4, 4, 1, '2026-10-13 13:15:00'),
(5, 5, 4, '2026-10-13 13:16:00');

-- End of studenthub.sql
