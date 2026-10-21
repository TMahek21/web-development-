<?php
/**
 * student_dashboard.php
 * =========================================================================
 * CHARUSAT STUDENT PORTAL - STUDENT DASHBOARD
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access, & Timeout
 * =========================================================================
 */
require_once __DIR__ . '/auth_session.php';
require_once __DIR__ . '/db.php';

// Enforce authentication & check 15-minute inactivity timeout
requireLogin();

// Role check: If an administrator accesses student dashboard, redirect them to admin area
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}

$currentUser = getCurrentUser();
$studentName = $currentUser['name'];
$studentEmail = $currentUser['email'];
$studentInitial = strtoupper(substr($studentName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - CHARUSAT Portal</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <style>
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        .role-badge-student {
            background: #dbeafe;
            color: #1e40af;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .btn-topbar-logout {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.2s;
        }
        .btn-topbar-logout:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }
        .session-info-badge {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            color: #475569;
            margin-top: 6px;
        }
        body.dark-theme .session-info-badge {
            background: #1e293b;
            border-color: #334155;
            color: #94a3b8;
        }
    </style>
</head>

<body>

    <!-- MENU CHECKBOX -->
    <input type="checkbox" id="menu-toggle">

    <!-- ================= TOP BAR ================= -->
    <header class="topbar">
        <label for="menu-toggle" class="menu-btn">☰</label>

        <div class="search-wrapper">
            <input type="text" class="search-bar" placeholder="Search portal...">
        </div>

        <h1>CHARUSAT STUDENT PORTAL</h1>

        <div class="topicons">
            <span title="Messages">💬</span>
            <span title="Notifications">🔔</span>
            <span id="theme-toggle" class="theme-btn" title="Toggle Theme">🌙</span>
            <div class="topbar-user">
                <span class="profile-circle" title="<?= htmlspecialchars($studentName) ?>"><?= htmlspecialchars($studentInitial) ?></span>
                <a href="logout.php" class="btn-topbar-logout" title="End session safely">🚪 Logout</a>
            </div>
        </div>
    </header>

    <div class="container">

        <!-- ================= SIDEBAR ================= -->
        <nav class="sidebar">
            <div class="logo">🎓 CHARUSAT</div>

            <div class="menu-title">MAIN</div>
            <a href="student_dashboard.php" class="active">▦ Dashboard</a>
            <a href="profile.html">👤 Student Profile</a>
            <a href="result.html">📊 Result</a>
            <a href="attendance.html">📅 Attendance & Timetable</a>

            <div class="menu-title">SERVICES</div>
            <a href="feeportal.html">💰 Fee Portal</a>
            <a href="annoucements.html">📢 Announcements</a>
            <a href="calender.html">🗓 Calendar</a>
            <a href="event.html">🎫 Event Registration</a>
            <a href="ceritificate.html">📜 Certificate Viewer</a>
            <a href="faq.html">FAQ</a>
            <a href="about.html">About Us</a>
            <a href="faculty.html">Meet Faculty</a>

            <div class="menu-title">ACCOUNT</div>
            <a href="settings.html">⚙ Settings</a>
            <a href="logout.php" style="color: #ef4444; font-weight: 600;">🚪 Logout</a>
        </nav>

        <!-- ================= MAIN CONTENT ================= -->
        <main class="content">

            <!-- NOTIFICATION BANNER -->
            <div class="notification-banner" id="dashboard-notification">
                <div class="notification-content">
                    📢 <strong>Session Active:</strong> Welcome, <?= htmlspecialchars($studentName) ?>! Your student session is active with a 15-minute inactivity security policy.
                </div>
                <button id="close-notification" class="notification-close-btn" title="Close Notification">&times;</button>
            </div>

            <!-- PAGE HEADING -->
            <div class="page-heading">
                <div>
                    <h2>👋 Welcome back, <?= htmlspecialchars($studentName) ?>!</h2>
                    <p>
                        Role: <span class="role-badge-student">Student</span> &bull; 
                        Email: <strong><?= htmlspecialchars($studentEmail) ?></strong>
                    </p>
                    <div class="session-info-badge">
                        🔒 Authenticated Session (Role: Student) &bull; Auto-timeout in 15 mins of inactivity
                    </div>
                </div>

                <div class="semester">
                    <small>Semester</small>
                    <strong>3 of 8</strong>
                    <div class="progress">
                        <div class="progress-fill" style="width: 37.5%;"></div>
                    </div>
                </div>
            </div>

            <!-- WELCOME BOX -->
            <div class="welcome-box">
                <div class="welcome-text">
                    <h2>Stay Updated with CHARUSAT</h2>
                    <p>
                        Keep track of your attendance, assignments, examinations, announcements, and important academic activities all in one place.
                    </p>
                    <a href="annoucements.html" class="learn-btn">View Announcements →</a>
                </div>
                <div class="welcome-icon">🎓</div>
            </div>

            <!-- OVERVIEW STATS -->
            <div class="section-title">
                <h3>Academic Overview</h3>
            </div>

            <div class="stats-row">
                <div class="stat-box attendance-card">
                    <div class="stat-title">Attendance</div>
                    <div class="stat-value">89%</div>
                    <div class="stat-small">Good attendance record</div>
                </div>

                <div class="stat-box assignment-card">
                    <div class="stat-title">Assignments</div>
                    <div class="stat-value">3</div>
                    <div class="stat-small">Pending submission</div>
                </div>

                <div class="stat-box exam-card">
                    <div class="stat-title">Current CGPA</div>
                    <div class="stat-value">8.92</div>
                    <div class="stat-small">Semester 2 Record</div>
                </div>

                <div class="stat-box credits-card">
                    <div class="stat-title">Events Enrolled</div>
                    <div class="stat-value">2</div>
                    <div class="stat-small">Active campus fests</div>
                </div>
            </div>

            <!-- QUICK SHORTCUTS -->
            <div class="section-title">
                <h3>Quick Services</h3>
            </div>

            <div class="quick-grid">
                <a href="event.html" class="quick-card">
                    <div class="quick-icon">🎫</div>
                    <div>
                        <strong>Campus Events</strong>
                        <p>Browse and register for upcoming fests</p>
                    </div>
                </a>

                <a href="result.html" class="quick-card">
                    <div class="quick-icon">📊</div>
                    <div>
                        <strong>Results Portal</strong>
                        <p>View semester examination grades</p>
                    </div>
                </a>

                <a href="attendance.html" class="quick-card">
                    <div class="quick-icon">📅</div>
                    <div>
                        <strong>Attendance & Schedule</strong>
                        <p>Weekly lecture timetable</p>
                    </div>
                </a>

                <a href="logout.php" class="quick-card" style="border-left: 4px solid #ef4444;">
                    <div class="quick-icon">🚪</div>
                    <div>
                        <strong style="color: #dc2626;">Log Out</strong>
                        <p>End your authenticated session safely</p>
                    </div>
                </a>
            </div>

        </main>
    </div>

</body>
</html>
