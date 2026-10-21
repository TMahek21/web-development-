<?php
/* =========================================================================
   CHARUSAT STUDENTHUB - TEST REGISTRATION (test_registration.php)
   Practical 8: Demonstrates INSERT Query Using PDO Prepared Statements
   Topic: Parameterized INSERT, Input Validation & Duplicate Prevention
   ========================================================================= */

require_once "db.php";

$message = "";
$messageType = ""; // 'success' or 'error'

// Handle CLI execution for automated test demonstration
if (php_sapi_name() === 'cli') {
    echo "=====================================================\n";
    echo "  CHARUSAT StudentHub - Prepared Statement (INSERT)\n";
    echo "=====================================================\n";

    // CLI Automated Test 1: Fetch a valid student and event
    $s = $pdo->query("SELECT student_id, name FROM students ORDER BY student_id ASC LIMIT 1")->fetch();
    $e = $pdo->query("SELECT event_id, event_name FROM events ORDER BY event_id DESC LIMIT 1")->fetch();

    if ($s && $e) {
        $sid = $s['student_id'];
        $eid = $e['event_id'];

        echo "Test Case 1: Checking for existing registration for Student #$sid ({$s['name']}) & Event #$eid ({$e['event_name']})...\n";
        $checkStmt = $pdo->prepare("SELECT registration_id FROM registrations WHERE student_id = :sid AND event_id = :eid");
        $checkStmt->execute(['sid' => $sid, 'eid' => $eid]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            echo "[RESULT] Duplicate detected! Student #$sid is already registered for Event #$eid (Registration ID #{$existing['registration_id']}).\n";
            echo "[TEST PASSED] Duplicate registration prevented.\n";
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO registrations (student_id, event_id, registration_date) VALUES (:student_id, :event_id, NOW())");
            $insertStmt->execute(['student_id' => $sid, 'event_id' => $eid]);
            $newId = $pdo->lastInsertId();
            echo "[RESULT] Registration inserted successfully with ID #$newId!\n";
            echo "[TEST PASSED] Prepared statement INSERT verified.\n";
        }

        // Test Case 2: Intentional duplicate attempt
        echo "\nTest Case 2: Attempting intentional duplicate registration for Student #$sid & Event #$eid...\n";
        try {
            $insertStmt2 = $pdo->prepare("INSERT INTO registrations (student_id, event_id, registration_date) VALUES (:student_id, :event_id, NOW())");
            $insertStmt2->execute(['student_id' => $sid, 'event_id' => $eid]);
            echo "[RESULT] FAILED: Duplicate was allowed!\n";
        } catch (PDOException $ex) {
            echo "[RESULT] SUCCESS: Duplicate blocked by MySQL UNIQUE constraint (Code: " . $ex->getCode() . ")!\n";
        }
    }
    echo "=====================================================\n";
    exit(0);
}

// -------------------------------------------------------------------------
// BROWSER POST / GET HANDLING
// -------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentId = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
    $eventId   = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;

    // 1. Basic Input Validation
    if ($studentId <= 0 || $eventId <= 0) {
        $message = "Invalid Input: Please select both a valid student and a valid event.";
        $messageType = "error";
    } else {
        try {
            // 2. Validate Student exists using Prepared Statement SELECT
            $chkStudent = $pdo->prepare("SELECT student_id, name, email FROM students WHERE student_id = :sid");
            $chkStudent->execute(['sid' => $studentId]);
            $studentData = $chkStudent->fetch();

            // 3. Validate Event exists using Prepared Statement SELECT
            $chkEvent = $pdo->prepare("SELECT event_id, event_name FROM events WHERE event_id = :eid");
            $chkEvent->execute(['eid' => $eventId]);
            $eventData = $chkEvent->fetch();

            if (!$studentData) {
                $message = "Validation Error: Student ID #{$studentId} does not exist in the database.";
                $messageType = "error";
            } elseif (!$eventData) {
                $message = "Validation Error: Event ID #{$eventId} does not exist in the database.";
                $messageType = "error";
            } else {
                // 4. Duplicate Check using Prepared Statement SELECT
                $chkDuplicate = $pdo->prepare("SELECT registration_id, registration_date FROM registrations WHERE student_id = :sid AND event_id = :eid");
                $chkDuplicate->execute(['sid' => $studentId, 'eid' => $eventId]);
                $duplicateRow = $chkDuplicate->fetch();

                if ($duplicateRow) {
                    $message = "⚠️ Duplicate Registration Prevented: {$studentData['name']} is ALREADY registered for '{$eventData['event_name']}' (Registered on: {$duplicateRow['registration_date']}).";
                    $messageType = "error";
                } else {
                    // 5. Prepared Statement INSERT
                    $insertSql = "INSERT INTO registrations (student_id, event_id, registration_date) VALUES (:student_id, :event_id, NOW())";
                    $insertStmt = $pdo->prepare($insertSql);
                    $insertStmt->execute([
                        'student_id' => $studentId,
                        'event_id'   => $eventId
                    ]);

                    $regId = $pdo->lastInsertId();
                    $message = "✅ Registration Successful! {$studentData['name']} is registered for '{$eventData['event_name']}' (Registration ID: #{$regId}).";
                    $messageType = "success";
                }
            }
        } catch (PDOException $e) {
            // Check for MySQL Duplicate Entry Key error code (1062 / 23000)
            if ($e->getCode() == 23000) {
                $message = "⚠️ Duplicate Registration Prevented: Student is already registered for this event (Database Unique Constraint).";
                $messageType = "error";
            } else {
                error_log("Registration error: " . $e->getMessage());
                $message = "Database error occurred while processing registration. Please try again.";
                $messageType = "error";
            }
        }
    }
}

// Fetch all students for the dropdown
$allStudents = $pdo->query("SELECT student_id, name, email, course FROM students ORDER BY student_id ASC")->fetchAll();

// Fetch all events for the dropdown
$allEvents = $pdo->query("SELECT event_id, event_name, event_date, location FROM events ORDER BY event_id ASC")->fetchAll();

// Fetch existing registrations with JOIN query
$regList = $pdo->query("
    SELECT 
        r.registration_id,
        r.registration_date,
        s.student_id,
        s.name AS student_name,
        s.email AS student_email,
        e.event_id,
        e.event_name,
        e.event_date,
        e.location
    FROM registrations r
    JOIN students s ON r.student_id = s.student_id
    JOIN events e ON r.event_id = e.event_id
    ORDER BY r.registration_id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test INSERT Registration - CHARUSAT StudentHub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .test-box {
            max-width: 950px;
            margin: 30px auto;
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
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #334155;
            font-size: 14px;
        }
        .form-group select, .form-group input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
            box-sizing: border-box;
        }
        .btn-submit {
            background: #003366;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .btn-submit:hover {
            background: #002244;
        }
        .alert {
            padding: 14px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        .alert-success {
            background: #dcfce7;
            border: 1px solid #86efac;
            color: #15803d;
        }
        .alert-error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #b91c1c;
        }
        .reg-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .reg-table th, .reg-table td {
            padding: 10px 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .reg-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 600;
        }
        .viva-box {
            background: #f8fafc;
            border-left: 4px solid #003366;
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
                <h1 style="color: #003366; margin: 5px 0 0 0; font-size: 24px;">Prepared Statement: INSERT Event Registration</h1>
            </div>
            <a href="test_connection.php" style="color: #003366; text-decoration: none; font-weight: 600; font-size: 14px;">← Back to Connection Test</a>
        </div>

        <!-- Alert Message -->
        <?php if (!empty($message)): ?>
            <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Code Snippet for Viva -->
        <div class="code-box">
<span style="color: #94a3b8;">// STEP 1: Duplicate Check Prepared Statement</span>
$chk = $pdo->prepare("SELECT registration_id FROM registrations WHERE student_id = :sid AND event_id = :eid");
$chk->execute(['sid' => $studentId, 'eid' => $eventId]);

<span style="color: #94a3b8;">// STEP 2: Secure Parameterized INSERT Prepared Statement</span>
$sql = "INSERT INTO registrations (student_id, event_id, registration_date) VALUES (:student_id, :event_id, NOW())";
$stmt = $pdo->prepare($sql);
$stmt->execute(['student_id' => $studentId, 'event_id' => $eventId]);
        </div>

        <!-- Registration Form -->
        <h3 style="color: #003366; margin-top: 20px; margin-bottom: 15px;">Register a Student for an Event</h3>
        <form method="POST" action="test_registration.php" style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
            <div class="form-grid">
                <div class="form-group">
                    <label for="student_id">Select Student (from `students` table):</label>
                    <select name="student_id" id="student_id" required>
                        <option value="">-- Choose Student --</option>
                        <?php foreach ($allStudents as $st): ?>
                            <option value="<?= $st['student_id'] ?>">
                                #<?= $st['student_id'] ?>: <?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars($st['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="event_id">Select Event (from `events` table):</label>
                    <select name="event_id" id="event_id" required>
                        <option value="">-- Choose Event --</option>
                        <?php foreach ($allEvents as $ev): ?>
                            <option value="<?= $ev['event_id'] ?>">
                                #<?= $ev['event_id'] ?>: <?= htmlspecialchars($ev['event_name']) ?> (<?= htmlspecialchars($ev['event_date']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="submit" class="btn-submit">Execute INSERT Prepared Statement</button>
                <span style="font-size: 13px; color: #64748b;">Duplicate combinations will be safely caught and rejected.</span>
            </div>
        </form>

        <!-- Current Registrations Table -->
        <h3 style="color: #003366; margin-top: 30px; margin-bottom: 10px;">
            Current Registrations in Database (<?= count($regList) ?> Total)
        </h3>
        <?php if (!empty($regList)): ?>
            <table class="reg-table">
                <thead>
                    <tr>
                        <th>Reg ID</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Event Name</th>
                        <th>Event Date</th>
                        <th>Venue</th>
                        <th>Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($regList as $r): ?>
                        <tr>
                            <td><code>#<?= htmlspecialchars($r['registration_id']) ?></code></td>
                            <td><strong><?= htmlspecialchars($r['student_name']) ?></strong></td>
                            <td><?= htmlspecialchars($r['student_email']) ?></td>
                            <td><?= htmlspecialchars($r['event_name']) ?></td>
                            <td><?= htmlspecialchars($r['event_date']) ?></td>
                            <td><?= htmlspecialchars($r['location']) ?></td>
                            <td><small><?= htmlspecialchars($r['registration_date']) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #64748b;">No registrations recorded yet.</p>
        <?php endif; ?>

        <!-- Viva Explanation Note -->
        <div class="viva-box">
            <strong style="color: #003366; font-size: 15px;">🎓 Viva Explanation: Duplicate Prevention & Integrity</strong><br>
            <strong>How are duplicates prevented?</strong><br>
            1. <strong>Database Engine Level:</strong> The table definition contains a composite unique constraint: <code>UNIQUE KEY (student_id, event_id)</code>. MySQL will reject any duplicate combination.<br>
            2. <strong>Application Level:</strong> Before executing the INSERT, PHP runs a <code>SELECT COUNT(*)</code> prepared statement to check if the student is already registered, allowing us to return a friendly message instead of a database crash.
        </div>

        <div style="margin-top: 25px; display: flex; gap: 12px;">
            <a href="test_connection.php" style="background: #e2e8f0; color: #1e293b; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;">Connection Test</a>
            <a href="test_students.php" style="background: #e2e8f0; color: #1e293b; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;">Test SELECT Query</a>
            <a href="event.php" style="background: #003366; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;">StudentHub Event Registration →</a>
        </div>
    </div>

</body>
</html>
