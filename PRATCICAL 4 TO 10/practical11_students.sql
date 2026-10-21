-- =============================================================================
-- CHARUSAT STUDENTHUB - PRACTICAL 11 STUDENT MANAGEMENT DATABASE DUMP
-- Course: Web Development Frameworks (ITUE203) - Semester 3
-- Target: MySQL Server 9.0 / phpMyAdmin (Database: studenthub)
-- =============================================================================

USE `studenthub`;

-- Table structure for table `students`
CREATE TABLE IF NOT EXISTS `students` (
  `student_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `course` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `students` (At least 20 seeded records)
INSERT IGNORE INTO `students` (`name`, `email`, `course`) VALUES
  ('Mahek Tandel', '25dce112@charusat.edu.in', 'Computer Engineering'),
  ('Aarav Patel', 'aarav.ce@charusat.edu.in', 'Computer Engineering'),
  ('Ananya Sharma', 'ananya.s@charusat.edu.in', 'Computer Engineering'),
  ('Rohan Mehta', 'rohan.it@charusat.edu.in', 'Information Technology'),
  ('Diya Joshi', 'diya.j@charusat.edu.in', 'Information Technology'),
  ('Kavya Desai', 'kavya.d@charusat.edu.in', 'Computer Science & Engineering'),
  ('Yash Varma', 'yash.v@charusat.edu.in', 'Computer Science & Engineering'),
  ('Pranav Shah', 'pranav.ai@charusat.edu.in', 'Artificial Intelligence & ML'),
  ('Isha Trivedi', 'isha.t@charusat.edu.in', 'Artificial Intelligence & ML'),
  ('Harsh Kulkarni', 'harsh.ec@charusat.edu.in', 'Electronics & Communication'),
  ('Tanvi Pandya', 'tanvi.p@charusat.edu.in', 'Electronics & Communication'),
  ('Devanshi Dave', 'devanshi.d@charusat.edu.in', 'Computer Engineering'),
  ('Manav Solanki', 'manav.s@charusat.edu.in', 'Information Technology'),
  ('Pooja Prajapati', 'pooja.p@charusat.edu.in', 'Civil Engineering'),
  ('Nikhil Parmar', 'nikhil.p@charusat.edu.in', 'Civil Engineering'),
  ('Dhruv Panchal', 'dhruv.m@charusat.edu.in', 'Mechanical Engineering'),
  ('Vidhi Soni', 'vidhi.s@charusat.edu.in', 'Mechanical Engineering'),
  ('Rajesh Barot', 'rajesh.b@charusat.edu.in', 'Electrical Engineering'),
  ('Shruti Shukla', 'shruti.s@charusat.edu.in', 'Electrical Engineering'),
  ('Aditya Bhatt', 'aditya.b@charusat.edu.in', 'Computer Science & Engineering'),
  ('Meera Chauhan', 'meera.c@charusat.edu.in', 'Information Technology'),
  ('Siddharth Rawal', 'siddharth.r@charusat.edu.in', 'Artificial Intelligence & ML'),
  ('Jhanvi Thacker', 'jhanvi.t@charusat.edu.in', 'Computer Engineering'),
  ('Kunal Rathod', 'kunal.r@charusat.edu.in', 'Electronics & Communication'),
  ('Riddhi Patel', 'riddhi.p@charusat.edu.in', 'Information Technology');
