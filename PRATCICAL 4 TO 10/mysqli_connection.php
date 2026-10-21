<?php
/* =========================================================================
   CHARUSAT STUDENTHUB - MYSQLI DATABASE CONNECTION (mysqli_connection.php)
   Practical 9: Secure User Registration using PHP & MySQLi Prepared Statements
   Database: studenthub | Server: MySQL Server 9.0 on localhost:3306
   ========================================================================= */

// Database Configuration
$host     = "localhost";
$username = "root";
$password = "taytay21";
$dbname   = "studenthub";
$port     = 3306;

// Create MySQLi Connection (Object-Oriented style)
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Check Connection safely without exposing credentials or internal database errors
if ($conn->connect_error) {
    error_log("MySQLi Connection failed: " . $conn->connect_error);
    die("Database connection failed. Please ensure MySQL Server 9.0 is running.");
}

// Set UTF-8 character encoding
$conn->set_charset("utf8mb4");

// Automatically ensure the users table exists for Practical 9
$createUsersTable = "CREATE TABLE IF NOT EXISTS `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";

$conn->query($createUsersTable);
?>
