<?php
/* =========================================================================
   CHARUSAT STUDENTHUB - DATABASE CONNECTION (db.php)
   Practical 8: PHP PDO Database Connectivity & Prepared Statements
   Target: MySQL Server 9.0 on localhost:3306 (Database: studenthub)
   ========================================================================= */

// Configurable Database Credentials
$host     = "localhost";
$dbname   = "studenthub";
$username = "root";
$password = "taytay21";
$charset  = "utf8mb4";

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// PDO Driver Configuration Options
$options = [
    // Throw PDOException on errors for robust error handling
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Return rows as associative arrays by default
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Use native MySQL prepared statements
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Establish PDO Connection
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // NOTE: We deliberately do NOT echo a success message here.
    // This allows db.php to be cleanly required by other PHP files
    // (such as test_students.php, test_registration.php, and web pages)
    // without polluting HTTP headers, JSON payloads, or page layouts.
} catch (PDOException $e) {
    // Safely handle connection failures without exposing database passwords in public output
    error_log("Database connection failure: " . $e->getMessage());
    die("Database connection failed. Please verify that the MySQL Server service is running.");
}
?>
