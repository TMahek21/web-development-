<?php
/* =========================================================================
   CHARUSAT STUDENTHUB - TEST STUDENTS (test_students.php)
   Practical 8: Demonstrates SELECT Query Using PDO Prepared Statements
   Topic: SQL Injection Prevention via Parameterized Queries
   ========================================================================= */

require_once "db.php";

// Input course filter (default to Computer Engineering, or take from GET)
$filterCourse = isset($_GET['course']) ? trim($_GET['course']) : "Computer Engineering";

// STEP 1: Define SQL statement with a named placeholder (:course)
// NEVER concatenate input like: "SELECT * FROM students WHERE course = '" . $course . "'"
$sql = "SELECT student_id, name, email, course FROM students WHERE course = :course ORDER BY student_id ASC";

// STEP 2: Prepare the statement with the database engine
// The SQL template is compiled and verified before parameters are bound
$stmt = $pdo->prepare($sql);

// STEP 3: Execute the prepared statement with bound parameter array
// Parameters are sent separately from the SQL command, neutralizing SQL injection
$stmt->execute(["course" => $filterCourse]);

// STEP 4: Fetch all matching rows as an associative array
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If invoked from CLI (Command Line Interface)
if (php_sapi_name() === 'cli') {
    echo "=====================================================\n";
    echo "  CHARUSAT StudentHub - Prepared Statement (SELECT)\n";
    echo "=====================================================\n";
    echo "Query: $sql\n";
    echo "Bound Parameter [:course] = \"$filterCourse\"\n";
    echo "Matching Records Found: " . count($students) . "\n";
    echo "-----------------------------------------------------\n";
    foreach ($students as $s) {
        echo "[ID: {$s['student_id']}] {$s['name']} | {$s['email']} | {$s['course']}\n";
    }
    echo "=====================================================\n";
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test SELECT Prepared Statement - CHARUSAT StudentHub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .test-box {
            max-width: 900px;
            margin: 35px auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 51, 102, 0.1);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #1e293b;
        }
        .code-box {
            background: #1e293b;
            color: #f8fafc;
            padding: 16px 20px;
            border-radius: 8px;
            font-family: Consolas, monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 15px 0;
            line-height: 1.5;
        }
        .filter-form {
            display: flex;
            gap: 12px;
            align-items: center;
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 25px;
        }
        .filter-form select, .filter-form button {
            padding: 9px 16px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
        }
        .filter-form button {
            background: #003366;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .student-table th, .student-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .student-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 600;
        }
        .viva-box {
            background: #f0fdf4;
            border-left: 4px solid #16a34a;
            padding: 15px 20px;
            border-radius: 4px;
            margin-top: 25px;
            font-size: 14px;
            line-height: 1.6;
        }
    </style>
</head>
<body style="background: #f0f4f8; margin: 0; padding: 20px;">

    <div class="test-box">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px;">
            <div>
                <span style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase;">CHARUSAT StudentHub &bull; Practical 8</span>
                <h1 style="color: #003366; margin: 5px 0 0 0; font-size: 24px;">Prepared Statement: SELECT Query</h1>
            </div>
            <a href="test_connection.php" style="color: #003366; text-decoration: none; font-weight: 600; font-size: 14px;">← Back to Connection Test</a>
        </div>

        <p style="color: #475569; font-size: 14px;">
            This script demonstrates retrieving student records from the MySQL <code>students</code> table using a secure PDO Prepared Statement with named parameters.
        </p>

        <!-- PHP Code Display for Viva Explanation -->
        <div class="code-box">
<span style="color: #94a3b8;">// 1. Prepare SQL template with named placeholder</span>
$sql = "SELECT student_id, name, email, course FROM students WHERE course = :course";
$stmt = $pdo->prepare($sql);

<span style="color: #94a3b8;">// 2. Execute with safely bound parameter array (neutralizes SQL injection)</span>
$stmt->execute(["course" => "<?= htmlspecialchars($filterCourse) ?>"]);

<span style="color: #94a3b8;">// 3. Fetch records as associative array</span>
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        </div>

        <!-- Filter Form -->
        <form method="GET" action="test_students.php" class="filter-form">
            <label for="course-select" style="font-weight: 600; font-size: 14px; color: #334155;">Filter by Course:</label>
            <select name="course" id="course-select">
                <option value="Computer Engineering" <?= $filterCourse === 'Computer Engineering' ? 'selected' : '' ?>>Computer Engineering</option>
                <option value="Information Technology" <?= $filterCourse === 'Information Technology' ? 'selected' : '' ?>>Information Technology</option>
            </select>
            <button type="submit">Execute Query</button>
            <a href="test_students.php" style="color: #64748b; font-size: 13px; text-decoration: none;">Reset</a>
        </form>

        <!-- Results Table -->
        <h3 style="color: #003366; margin-bottom: 10px;">
            Results for: <span style="color: #0284c7;"><?= htmlspecialchars($filterCourse) ?></span> 
            (<?= count($students) ?> found)
        </h3>

        <?php if (!empty($students)): ?>
            <table class="student-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Student ID</th>
                        <th style="width: 30%;">Full Name</th>
                        <th style="width: 30%;">Email Address</th>
                        <th style="width: 25%;">Course</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $row): ?>
                        <tr>
                            <td><code>#<?= htmlspecialchars($row['student_id']) ?></code></td>
                            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-size: 13px;"><?= htmlspecialchars($row['course']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 20px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #991b1b;">
                No students found enrolled in <strong><?= htmlspecialchars($filterCourse) ?></strong>.
            </div>
        <?php endif; ?>

        <!-- Viva Guide Box -->
        <div class="viva-box">
            <strong style="color: #15803d; font-size: 15px;">🎓 Viva Explanation Note:</strong><br>
            <strong>Why are Prepared Statements secure?</strong><br>
            In traditional SQL string concatenation, user input can alter SQL syntax (e.g. <code>' OR '1'='1</code>).
            With PDO prepared statements, the database engine compiles the SQL query structure in step 1 (<code>prepare</code>), and treats incoming parameters purely as literal values in step 2 (<code>execute</code>). Even if an attacker injects SQL commands, they are treated as harmless string data.
        </div>

        <div style="margin-top: 25px; display: flex; gap: 12px;">
            <a href="test_registration.php" style="background: #003366; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;">Next: Test INSERT Prepared Statement →</a>
            <a href="test_connection.php" style="background: #e2e8f0; color: #1e293b; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;">Connection Test</a>
        </div>
    </div>

</body>
</html>