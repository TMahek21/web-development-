<?php
/* =========================================================================
   CHARUSAT STUDENTHUB - EVENT REGISTRATION PORTAL (event.php)
   Practical 8: Database Integration with MySQL Server 9.0 using PDO
   Features:
     - Prepared Statements for SELECT (students & events)
     - Prepared Statements for INSERT (registrations)
     - Input Validation & Duplicate Registration Prevention
     - Full UI consistency with CHARUSAT StudentHub Theme & Layout
   ========================================================================= */

require_once "db.php";

$portalMessage     = "";
$portalMessageType = ""; // 'success' or 'error'

// Selected student ID for form (default: 1 = Mahek Tandel)
$currentStudentId = 1;

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentId = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
    $eventId   = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    $currentStudentId = $studentId;

    if ($studentId <= 0 || $eventId <= 0) {
        $portalMessage     = "Invalid Submission: Please select a valid student and event.";
        $portalMessageType = "error";
    } else {
        try {
            // STEP 1: Validate Student ID using Prepared Statement SELECT
            $stmtStudent = $pdo->prepare("SELECT student_id, name, email FROM students WHERE student_id = :sid");
            $stmtStudent->execute(['sid' => $studentId]);
            $studentRow = $stmtStudent->fetch();

            // STEP 2: Validate Event ID using Prepared Statement SELECT
            $stmtEvent = $pdo->prepare("SELECT event_id, event_name, event_date, location FROM events WHERE event_id = :eid");
            $stmtEvent->execute(['eid' => $eventId]);
            $eventRow = $stmtEvent->fetch();

            if (!$studentRow) {
                $portalMessage     = "Validation Error: Student record not found.";
                $portalMessageType = "error";
            } elseif (!$eventRow) {
                $portalMessage     = "Validation Error: Selected event not found in database.";
                $portalMessageType = "error";
            } else {
                // STEP 3: Check for Duplicate Registration using Prepared Statement SELECT
                $chkDup = $pdo->prepare("SELECT registration_id, registration_date FROM registrations WHERE student_id = :sid AND event_id = :eid");
                $chkDup->execute(['sid' => $studentId, 'eid' => $eventId]);
                $existingReg = $chkDup->fetch();

                if ($existingReg) {
                    $portalMessage = "⚠️ Duplicate Registration Prevented: {$studentRow['name']} is ALREADY registered for '{$eventRow['event_name']}' (Registered on: {$existingReg['registration_date']}).";
                    $portalMessageType = "error";
                } else {
                    // STEP 4: Insert Registration using Prepared Statement INSERT
                    $insSql = "INSERT INTO registrations (student_id, event_id, registration_date) VALUES (:sid, :eid, NOW())";
                    $insStmt = $pdo->prepare($insSql);
                    $insStmt->execute([
                        'sid' => $studentId,
                        'eid' => $eventId
                    ]);

                    $newRegId = $pdo->lastInsertId();
                    $portalMessage = "🎉 Registration Successful! {$studentRow['name']} is confirmed for '{$eventRow['event_name']}' (Registration ID: #{$newRegId}).";
                    $portalMessageType = "success";
                }
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $portalMessage     = "⚠️ Duplicate Registration Prevented: Unique constraint violation in database.";
                $portalMessageType = "error";
            } else {
                error_log("Event registration DB error: " . $e->getMessage());
                $portalMessage     = "A database error occurred. Your registration could not be completed.";
                $portalMessageType = "error";
            }
        }
    }
}

// Fetch all students from MySQL database using Prepared Statement
$studentsStmt = $pdo->prepare("SELECT student_id, name, email, course FROM students ORDER BY student_id ASC");
$studentsStmt->execute();
$allStudents = $studentsStmt->fetchAll();

// Fetch all events from MySQL database using Prepared Statement
$eventsStmt = $pdo->prepare("SELECT event_id, event_name, event_date, location FROM events ORDER BY event_date ASC, event_id ASC");
$eventsStmt->execute();
$allEvents = $eventsStmt->fetchAll();

// Selected event for aside preview (default to first event)
$previewEvent = !empty($allEvents) ? $allEvents[0] : [
    'event_id'   => 1,
    'event_name' => 'Campus Event',
    'event_date' => '2026-10-15',
    'location'   => 'Main Auditorium'
];

// Fetch count of registrations for the current student
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE student_id = :sid");
$countStmt->execute(['sid' => $currentStudentId]);
$studentParticipationCount = (int)$countStmt->fetchColumn();

// Fetch recent registrations from database with JOIN
$recentRegStmt = $pdo->prepare("
    SELECT 
        r.registration_id,
        r.registration_date,
        s.name AS student_name,
        e.event_name,
        e.event_date,
        e.location
    FROM registrations r
    JOIN students s ON r.student_id = s.student_id
    JOIN events e ON r.event_id = e.event_id
    ORDER BY r.registration_id DESC
    LIMIT 6
");
$recentRegStmt->execute();
$recentRegistrations = $recentRegStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Event Registration - CHARUSAT StudentHub (Database Powered)</title>

    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <style>
        .db-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 10px;
        }
        .portal-alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .portal-alert.success {
            background-color: #dcfce7;
            border: 1px solid #86efac;
            color: #166534;
        }
        .portal-alert.error {
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }
        .reg-table-container {
            margin-top: 30px;
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .live-reg-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 10px;
        }
        .live-reg-table th, .live-reg-table td {
            padding: 10px 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .live-reg-table th {
            background-color: #f8fafc;
            color: #003366;
            font-weight: 700;
        }
        .live-reg-table tr:hover {
            background-color: #f1f5f9;
        }
        .test-links-banner {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #1e40af;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .test-links-banner a {
            color: #003366;
            font-weight: 700;
            text-decoration: underline;
        }
    </style>
</head>

<body class="event-registration-page">

    <!-- MENU TOGGLE -->
    <input type="checkbox" id="menu-toggle">

    <!-- TOP BAR -->
    <header class="topbar">

        <label for="menu-toggle" class="menu-btn">☰</label>

        <h1>CHARUSAT STUDENT PORTAL</h1>

        <div class="topicons">
            <span>💬</span>
            <span>🔔</span>
            <span id="theme-toggle" class="theme-btn" title="Toggle Theme">🌙</span>
            <span class="profile-circle">M</span>
        </div>

    </header>


    <div class="container">

        <!-- ================= SIDEBAR ================= -->

        <nav class="sidebar">

            <div class="logo">
                🎓 CHARUSAT
            </div>

            <div class="menu-title">
                MAIN
            </div>

            <a href="dashboardpage.html">
                ▦ Dashboard
            </a>

            <a href="profile.html">
                👤 Student Profile
            </a>

            <a href="result.html">
                📊 Result
            </a>

            <a href="attendance.html">
                📅 Attendance & Timetable
            </a>


            <div class="menu-title">
                SERVICES
            </div>

            <a href="feeportal.html">
                💰 Fee Portal
            </a>

            <a href="annoucements.html">
                📢 Announcements
            </a>

            <a href="calender.html">
                🗓 Calendar
            </a>

            <a href="event.php" class="active">
                🎫 Event Registration
            </a>

            <a href="ceritificate.html">
                📜 Certificate Viewer
            </a>
            <a href="faq.html">FAQ</a>
            <a href="about.html">About Us</a>
            <a href="faculty.html">Meet Faculty</a>


            <div class="menu-title">
                SETTINGS
            </div>

            <a href="settings.html">
                ⚙ Settings
            </a>

        </nav>

        <!-- EVENT CONTENT -->
        <main class="content event-registration-content">

            <!-- PAGE INTRO -->
            <section class="event-intro">

                <div>
                    <span class="event-eyebrow">
                        CAMPUS ACTIVITIES <span class="db-badge-pill">● MySQL 9.0 Active</span>
                    </span>

                    <h2>Register for an Event</h2>

                    <p>
                        Choose an upcoming event and register to participate in campus activities.
                        All registrations are stored securely in MySQL Server 9.0 using PDO prepared statements.
                    </p>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <a href="test_connection.php" class="browse-events" style="background: #0284c7; color: white;">
                        Database Status ⚡
                    </a>
                    <button type="button" id="open-event-modal" class="browse-events" style="border: none; cursor: pointer; background: #003366; color: white;">
                        View Event Details ℹ
                    </button>
                    <a href="vevents.html" class="browse-events">
                        Browse Events →
                    </a>
                </div>

            </section>

            <!-- Verification Links Banner for Practical 8 -->
            <div class="test-links-banner">
                <div>
                    <strong>Practical 8 Test Endpoints:</strong>
                    PDO & Prepared Statements are active.
                </div>
                <div style="display: flex; gap: 15px;">
                    <a href="test_connection.php">Connection Test</a>
                    <a href="test_students.php">SELECT Prepared Statement</a>
                    <a href="test_registration.php">INSERT Prepared Statement</a>
                </div>
            </div>

            <!-- REGISTRATION AREA -->
            <section class="event-registration-area">

                <!-- FORM SIDE -->
                <div class="registration-panel">

                    <div class="registration-heading">

                        <div>
                            <span class="event-eyebrow">
                                YOUR DETAILS
                            </span>

                            <h3>Event Registration Form</h3>
                        </div>

                        <span class="event-symbol">✦</span>

                    </div>

                    <!-- Notification Alert -->
                    <?php if (!empty($portalMessage)): ?>
                        <div class="portal-alert <?= $portalMessageType === 'success' ? 'success' : 'error' ?>">
                            <?= htmlspecialchars($portalMessage) ?>
                        </div>
                    <?php endif; ?>

                    <form action="event.php" method="POST">

                        <!-- STUDENT SELECTION (FETCHED FROM `students` TABLE) -->
                        <div class="event-form-group">
                            <label for="student_id">
                                Registered Student (from <code>students</code> table):
                            </label>
                            <select name="student_id" id="student_id" required onchange="updateStudentInfo(this)">
                                <?php foreach ($allStudents as $st): ?>
                                    <option value="<?= $st['student_id'] ?>" 
                                            data-name="<?= htmlspecialchars($st['name']) ?>" 
                                            data-email="<?= htmlspecialchars($st['email']) ?>"
                                            data-course="<?= htmlspecialchars($st['course']) ?>"
                                            <?= ($currentStudentId == $st['student_id']) ? 'selected' : '' ?>>
                                        #<?= $st['student_id'] ?>: <?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars($st['course']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- STUDENT DETAILS READONLY DISPLAY -->
                        <div class="event-form-row">
                            <div class="event-form-group">
                                <label>Student Name</label>
                                <input type="text" id="disp-student-name" value="Mahek Tandel" readonly style="background: #f8fafc;">
                            </div>
                            <div class="event-form-group">
                                <label>Student Email</label>
                                <input type="email" id="disp-student-email" value="25dce112@charusat.edu.in" readonly style="background: #f8fafc;">
                            </div>
                        </div>

                        <!-- EVENT SELECTION (FETCHED FROM `events` TABLE) -->
                        <div class="event-form-group">
                            <label for="event_id">
                                Select Event (from <code>events</code> table):
                            </label>

                            <select name="event_id" id="event_id" data-db-source="mysql" required onchange="updateEventPreview(this)">
                                <?php foreach ($allEvents as $ev): ?>
                                    <option value="<?= $ev['event_id'] ?>" 
                                            data-name="<?= htmlspecialchars($ev['event_name']) ?>"
                                            data-date="<?= htmlspecialchars($ev['event_date']) ?>"
                                            data-location="<?= htmlspecialchars($ev['location']) ?>">
                                        #<?= $ev['event_id'] ?>: <?= htmlspecialchars($ev['event_name']) ?> (<?= htmlspecialchars($ev['event_date']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- PARTICIPATION TYPE -->
                        <div class="event-form-group">
                            <label>Participation Type</label>
                            <div class="event-choice-row">
                                <label class="event-choice">
                                    <input type="radio" name="participation" value="Participant" checked>
                                    Participant
                                </label>
                                <label class="event-choice">
                                    <input type="radio" name="participation" value="Volunteer">
                                    Volunteer
                                </label>
                            </div>
                        </div>

                        <!-- ADDITIONAL MESSAGE -->
                        <div class="event-form-group">
                            <label>Additional Requirements / Notes</label>
                            <textarea name="message" rows="3" placeholder="Any special requirements, dietary preferences, or team notes..."></textarea>
                        </div>

                        <!-- BUTTONS -->
                        <div class="event-form-actions">
                            <input type="submit" value="Confirm Registration" class="event-register-button">
                            <input type="reset" value="Clear" class="event-clear-button">
                        </div>

                    </form>

                </div>

                <!-- EVENT PREVIEW (DYNAMIC) -->
                <aside class="selected-event">

                    <span class="selected-label">
                        SELECTED EVENT PREVIEW
                    </span>

                    <div class="selected-date">
                        <span id="preview-month"><?= strtoupper(date('M', strtotime($previewEvent['event_date'] ?? 'now'))) ?></span>
                        <strong id="preview-day"><?= date('d', strtotime($previewEvent['event_date'] ?? 'now')) ?></strong>
                        <small id="preview-year"><?= date('Y', strtotime($previewEvent['event_date'] ?? 'now')) ?></small>
                    </div>

                    <span class="selected-category" id="preview-cat">
                        CAMPUS EVENT
                    </span>

                    <h3 id="preview-title">
                        <?= htmlspecialchars($previewEvent['event_name'] ?? 'Tech Fest') ?>
                    </h3>

                    <p id="preview-desc">
                        Official CHARUSAT campus activity. Registered students receive verifiable certificates.
                    </p>

                    <div class="selected-details">
                        <div>
                            <span>TIME</span>
                            <strong>10:00 AM</strong>
                        </div>
                        <div>
                            <span>VENUE</span>
                            <strong id="preview-venue"><?= htmlspecialchars($previewEvent['location'] ?? 'Main Auditorium') ?></strong>
                        </div>
                        <div>
                            <span>ELIGIBILITY</span>
                            <strong>All Students</strong>
                        </div>
                    </div>

                    <div class="registration-status">
                        <span>REGISTRATION</span>
                        <strong>OPEN (LIVE DB)</strong>
                    </div>

                </aside>

            </section>

            <!-- LIVE REGISTRATIONS FROM DATABASE TABLE -->
            <div class="reg-table-container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                        <span class="event-eyebrow">DATABASE INTEGRATION</span>
                        <h3 style="color: #003366; margin: 0; font-size: 18px;">Recent Live Registrations (from `registrations` Table)</h3>
                    </div>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600;">
                        Auto-updated on submission
                    </span>
                </div>

                <?php if (!empty($recentRegistrations)): ?>
                    <table class="live-reg-table">
                        <thead>
                            <tr>
                                <th>Reg ID</th>
                                <th>Student Name</th>
                                <th>Event Name</th>
                                <th>Event Date</th>
                                <th>Location</th>
                                <th>Registration Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRegistrations as $reg): ?>
                                <tr>
                                    <td><code>#<?= htmlspecialchars($reg['registration_id']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($reg['student_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($reg['event_name']) ?></td>
                                    <td><?= htmlspecialchars($reg['event_date']) ?></td>
                                    <td><?= htmlspecialchars($reg['location']) ?></td>
                                    <td><small><?= htmlspecialchars($reg['registration_date']) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #64748b; font-size: 14px;">No registrations recorded yet.</p>
                <?php endif; ?>
            </div>

            <!-- PARTICIPATION STATS SECTION -->
            <section class="event-participation" style="margin-top: 30px;">

                <div class="participation-number">
                    <strong><?= str_pad((string)$studentParticipationCount, 2, '0', STR_PAD_LEFT) ?></strong>
                    <span>EVENTS</span>
                </div>

                <div class="participation-info">
                    <span class="event-eyebrow">
                        YOUR PARTICIPATION RECORD
                    </span>

                    <h3>
                        You have registered for <?= $studentParticipationCount ?> event(s)
                    </h3>

                    <p>
                        Your campus activity records are stored in the relational database.
                        Duplicate registrations for the same event are automatically rejected by unique constraints.
                    </p>
                </div>

                <a href="ceritificate.html" class="certificate-link">
                    View Certificates →
                </a>

            </section>

        </main>

    </div>

    <!-- EVENT DETAILS MODAL -->
    <div id="event-modal" class="modal-overlay">
        <div class="modal-box">
            <span id="close-modal" class="modal-close">&times;</span>
            <span class="event-eyebrow">CAMPUS EVENT DETAILS</span>
            <h2>CHARUSAT Tech Fest 2026</h2>
            <p><strong>📅 Date:</strong> 15 November 2026</p>
            <p><strong>⏰ Time:</strong> 9:00 AM - 5:00 PM</p>
            <p><strong>📍 Venue:</strong> DEPSTAR Campus, CHARUSAT</p>
            <p><strong>👥 Eligibility:</strong> Open for all registered students</p>
            <p>Participate in coding competitions, workshops, robotics league, and cultural celebrations.</p>
            <button id="modal-register-btn" class="event-register-button" style="margin-top: 15px; width: 100%; border: none; cursor: pointer;">Close Details</button>
        </div>
    </div>

    <!-- Client-side sync for dynamic select preview -->
    <script>
        function updateStudentInfo(selectElem) {
            let selectedOpt = selectElem.options[selectElem.selectedIndex];
            if (selectedOpt) {
                let name = selectedOpt.getAttribute('data-name');
                let email = selectedOpt.getAttribute('data-email');
                if (name) document.getElementById('disp-student-name').value = name;
                if (email) document.getElementById('disp-student-email').value = email;
            }
        }

        function updateEventPreview(selectElem) {
            let selectedOpt = selectElem.options[selectElem.selectedIndex];
            if (selectedOpt) {
                let name = selectedOpt.getAttribute('data-name');
                let dateStr = selectedOpt.getAttribute('data-date');
                let location = selectedOpt.getAttribute('data-location');

                if (name) document.getElementById('preview-title').textContent = name;
                if (location) document.getElementById('preview-venue').textContent = location;

                if (dateStr) {
                    let d = new Date(dateStr);
                    if (!isNaN(d.getTime())) {
                        const months = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
                        document.getElementById('preview-month').textContent = months[d.getMonth()];
                        document.getElementById('preview-day').textContent = String(d.getDate()).padStart(2, '0');
                        document.getElementById('preview-year').textContent = d.getFullYear();
                    }
                }
            }
        }

        // Initialize display values on page load
        document.addEventListener('DOMContentLoaded', function() {
            let sSelect = document.getElementById('student_id');
            if (sSelect) updateStudentInfo(sSelect);
            let eSelect = document.getElementById('event_id');
            if (eSelect) updateEventPreview(eSelect);
        });
    </script>

</body>

</html>
