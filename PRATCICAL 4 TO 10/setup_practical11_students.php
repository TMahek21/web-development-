<?php
/**
 * setup_practical11_students.php
 * =========================================================================
 * CHARUSAT STUDENT PORTAL - PRACTICAL 11 DATABASE SEEDING SCRIPT
 * Seeds at least 20 student records across various CHARUSAT engineering branches
 * avoiding duplicates if run multiple times.
 * =========================================================================
 */
require_once __DIR__ . '/mysqli_connection.php';

echo "--- 1. Checking students Table Schema ---\n";

// Ensure students table exists with expected schema
$createTableSql = "CREATE TABLE IF NOT EXISTS `students` (
  `student_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `course` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

if (!$conn->query($createTableSql)) {
    die("Failed to verify/create students table: " . $conn->error);
}

echo "students table schema verified successfully.\n";

// 2. Define at least 25 realistic student records across CHARUSAT institutes
$seedStudents = [
    // Existing known students (will be preserved if present, or inserted if missing)
    ["name" => "Mahek Tandel", "email" => "25dce112@charusat.edu.in", "course" => "Computer Engineering"],
    ["name" => "Aarav Patel", "email" => "aarav.ce@charusat.edu.in", "course" => "Computer Engineering"],
    ["name" => "Ananya Sharma", "email" => "ananya.s@charusat.edu.in", "course" => "Computer Engineering"],
    ["name" => "Rohan Mehta", "email" => "rohan.it@charusat.edu.in", "course" => "Information Technology"],
    ["name" => "Diya Joshi", "email" => "diya.j@charusat.edu.in", "course" => "Information Technology"],
    ["name" => "Kavya Desai", "email" => "kavya.d@charusat.edu.in", "course" => "Computer Science & Engineering"],
    ["name" => "Yash Varma", "email" => "yash.v@charusat.edu.in", "course" => "Computer Science & Engineering"],
    ["name" => "Pranav Shah", "email" => "pranav.ai@charusat.edu.in", "course" => "Artificial Intelligence & ML"],
    ["name" => "Isha Trivedi", "email" => "isha.t@charusat.edu.in", "course" => "Artificial Intelligence & ML"],
    ["name" => "Harsh Kulkarni", "email" => "harsh.ec@charusat.edu.in", "course" => "Electronics & Communication"],
    ["name" => "Tanvi Pandya", "email" => "tanvi.p@charusat.edu.in", "course" => "Electronics & Communication"],
    ["name" => "Devanshi Dave", "email" => "devanshi.d@charusat.edu.in", "course" => "Computer Engineering"],
    ["name" => "Manav Solanki", "email" => "manav.s@charusat.edu.in", "course" => "Information Technology"],
    ["name" => "Pooja Prajapati", "email" => "pooja.p@charusat.edu.in", "course" => "Civil Engineering"],
    ["name" => "Nikhil Parmar", "email" => "nikhil.p@charusat.edu.in", "course" => "Civil Engineering"],
    ["name" => "Dhruv Panchal", "email" => "dhruv.m@charusat.edu.in", "course" => "Mechanical Engineering"],
    ["name" => "Vidhi Soni", "email" => "vidhi.s@charusat.edu.in", "course" => "Mechanical Engineering"],
    ["name" => "Rajesh Barot", "email" => "rajesh.b@charusat.edu.in", "course" => "Electrical Engineering"],
    ["name" => "Shruti Shukla", "email" => "shruti.s@charusat.edu.in", "course" => "Electrical Engineering"],
    ["name" => "Aditya Bhatt", "email" => "aditya.b@charusat.edu.in", "course" => "Computer Science & Engineering"],
    ["name" => "Meera Chauhan", "email" => "meera.c@charusat.edu.in", "course" => "Information Technology"],
    ["name" => "Siddharth Rawal", "email" => "siddharth.r@charusat.edu.in", "course" => "Artificial Intelligence & ML"],
    ["name" => "Jhanvi Thacker", "email" => "jhanvi.t@charusat.edu.in", "course" => "Computer Engineering"],
    ["name" => "Kunal Rathod", "email" => "kunal.r@charusat.edu.in", "course" => "Electronics & Communication"],
    ["name" => "Riddhi Patel", "email" => "riddhi.p@charusat.edu.in", "course" => "Information Technology"]
];

echo "\n--- 2. Seeding Student Records into Database ---\n";
$insertStmt = $conn->prepare("INSERT INTO students (name, email, course) VALUES (?, ?, ?)");
$checkStmt  = $conn->prepare("SELECT student_id FROM students WHERE email = ?");

$insertedCount = 0;
$skippedCount  = 0;

foreach ($seedStudents as $student) {
    // Check if student with this email already exists
    $checkStmt->bind_param("s", $student['email']);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows === 0) {
        $insertStmt->bind_param("sss", $student['name'], $student['email'], $student['course']);
        if ($insertStmt->execute()) {
            $insertedCount++;
        }
    } else {
        $skippedCount++;
    }
}

$checkStmt->close();
$insertStmt->close();

echo "Seeding completed: {$insertedCount} records inserted, {$skippedCount} records already existed.\n";

// 3. Count total students in database
$countRes = $conn->query("SELECT COUNT(*) FROM students");
$totalStudents = $countRes->fetch_row()[0];
echo "Total student records currently in database: {$totalStudents}\n";

// 4. Generate standalone SQL file practical11_students.sql for phpMyAdmin import
$sqlDump = "-- =============================================================================\n";
$sqlDump .= "-- CHARUSAT STUDENTHUB - PRACTICAL 11 STUDENT MANAGEMENT DATABASE DUMP\n";
$sqlDump .= "-- Course: Web Development Frameworks (ITUE203) - Semester 3\n";
$sqlDump .= "-- Target: MySQL Server 9.0 / phpMyAdmin (Database: studenthub)\n";
$sqlDump .= "-- =============================================================================\n\n";
$sqlDump .= "USE `studenthub`;\n\n";
$sqlDump .= "-- Table structure for table `students`\n";
$sqlDump .= "CREATE TABLE IF NOT EXISTS `students` (\n";
$sqlDump .= "  `student_id` INT NOT NULL AUTO_INCREMENT,\n";
$sqlDump .= "  `name` VARCHAR(100) NOT NULL,\n";
$sqlDump .= "  `email` VARCHAR(100) NOT NULL,\n";
$sqlDump .= "  `course` VARCHAR(100) NOT NULL,\n";
$sqlDump .= "  PRIMARY KEY (`student_id`),\n";
$sqlDump .= "  UNIQUE KEY `email` (`email`)\n";
$sqlDump .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;\n\n";
$sqlDump .= "-- Dumping data for table `students` (At least 20 seeded records)\n";
$sqlDump .= "INSERT IGNORE INTO `students` (`name`, `email`, `course`) VALUES\n";

$valueLines = [];
foreach ($seedStudents as $s) {
    $n = addslashes($s['name']);
    $e = addslashes($s['email']);
    $c = addslashes($s['course']);
    $valueLines[] = "  ('{$n}', '{$e}', '{$c}')";
}
$sqlDump .= implode(",\n", $valueLines) . ";\n";

file_put_contents(__DIR__ . '/practical11_students.sql', $sqlDump);
echo "\nGenerated standalone SQL dump file: practical11_students.sql (" . strlen($sqlDump) . " bytes)\n";
?>
