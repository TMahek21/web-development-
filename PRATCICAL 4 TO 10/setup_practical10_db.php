<?php
/**
 * setup_practical10_db.php
 * Configures the studenthub database for Practical 10:
 * 1. Ensures the users table has a `role` column (ENUM('admin', 'student') DEFAULT 'student')
 * 2. Seeds standard test accounts:
 *    - Admin: admin@charusat.edu.in / Admin@2026
 *    - Student: student@charusat.edu.in / Student@2026
 */
require_once __DIR__ . '/db.php';

try {
    echo "--- 1. Checking and Updating users Table Schema ---\n";
    
    // Check if role column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    $roleCol = $stmt->fetch();
    
    if (!$roleCol) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'student') NOT NULL DEFAULT 'student' AFTER password");
        echo "Successfully added 'role' column to users table.\n";
    } else {
        echo "'role' column already exists in users table.\n";
    }

    echo "\n--- 2. Seeding / Updating Test Accounts ---\n";

    // 1. Admin Account
    $adminEmail = 'admin@charusat.edu.in';
    $adminName  = 'CHARUSAT Administrator';
    $adminPass  = 'Admin@2026';
    $adminHash  = password_hash($adminPass, PASSWORD_DEFAULT);
    $adminRole  = 'admin';

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $existingAdmin = $stmt->fetch();

    if ($existingAdmin) {
        $updateStmt = $pdo->prepare("UPDATE users SET name = ?, password = ?, role = ? WHERE email = ?");
        $updateStmt->execute([$adminName, $adminHash, $adminRole, $adminEmail]);
        echo "Updated existing Admin account: $adminEmail\n";
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $insertStmt->execute([$adminName, $adminEmail, $adminHash, $adminRole]);
        echo "Inserted new Admin account: $adminEmail\n";
    }

    // 2. Student Account
    $studentEmail = 'student@charusat.edu.in';
    $studentName  = 'Mahek Patel';
    $studentPass  = 'Student@2026';
    $studentHash  = password_hash($studentPass, PASSWORD_DEFAULT);
    $studentRole  = 'student';

    $stmt->execute([$studentEmail]);
    $existingStudent = $stmt->fetch();

    if ($existingStudent) {
        $updateStmt = $pdo->prepare("UPDATE users SET name = ?, password = ?, role = ? WHERE email = ?");
        $updateStmt->execute([$studentName, $studentHash, $studentRole, $studentEmail]);
        echo "Updated existing Student account: $studentEmail\n";
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $insertStmt->execute([$studentName, $studentEmail, $studentHash, $studentRole]);
        echo "Inserted new Student account: $studentEmail\n";
    }

    // 3. Ensure any existing accounts have a role
    $pdo->exec("UPDATE users SET role = 'student' WHERE role IS NULL OR role = ''");

    echo "\n--- 3. Verifying Current Accounts in users Table ---\n";
    $users = $pdo->query("SELECT id, name, email, role, created_at FROM users")->fetchAll();
    foreach ($users as $u) {
        echo "  [ID: {$u['id']}] {$u['name']} ({$u['email']}) - Role: {$u['role']}\n";
    }

    echo "\nDatabase configuration for Practical 10 completed successfully.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
