<?php
/**
 * admin_dashboard.php
 * =========================================================================
 * CHARUSAT STUDENT PORTAL - ADMINISTRATOR DASHBOARD
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access, & Timeout
 * =========================================================================
 * 
 * Restricted strictly to users with role = 'admin'.
 * Any student or unauthenticated visitor attempting to access this page
 * is immediately blocked and redirected.
 */

require_once __DIR__ . '/auth_session.php';
require_once __DIR__ . '/db.php';

// Enforce that user must be logged in AND have the 'admin' role
requireRole('admin');

$currentUser  = getCurrentUser();
$adminName    = $currentUser['name'];
$adminEmail   = $currentUser['email'];
$adminInitial = strtoupper(substr($adminName, 0, 1));

// Retrieve administrative portal metrics dynamically from database
try {
    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalEvents = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $totalRegistrations = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();

    // Fetch recent user accounts
    $recentUsersStmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC LIMIT 6");
    $recentUsers = $recentUsersStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Admin Dashboard Metrics Query Error: " . $e->getMessage());
    $totalUsers = $totalStudents = $totalEvents = $totalRegistrations = 0;
    $recentUsers = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Console - CHARUSAT Portal</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <style>
        .admin-topbar {
            background-color: #0f172a;
            border-bottom: 2px solid #b91c1c;
        }
        .admin-badge {
            background: #b91c1c;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .btn-admin-logout {
            background: #b91c1c;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.2s;
        }
        .btn-admin-logout:hover {
            background: #dc2626;
        }
        .admin-summary-box {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: white;
            padding: 24px;
            border-radius: 14px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .admin-summary-box h2 {
            color: #f8fafc;
            margin-top: 0;
            font-size: 22px;
        }
        .admin-table-wrapper {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
            overflow-x: auto;
        }
        body.dark-theme .admin-table-wrapper {
            background: #1e293b;
            color: #f1f5f9;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .admin-table th, .admin-table td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        body.dark-theme .admin-table th, 
        body.dark-theme .admin-table td {
            border-color: #334155;
        }
        .admin-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
        }
        body.dark-theme .admin-table th {
            background-color: #0f172a;
            color: #94a3b8;
        }
        .role-pill-admin {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
        }
        .role-pill-student {
            background: #e0f2fe;
            color: #0369a1;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
        }
        .security-notice {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        body.dark-theme .security-notice {
            background: #450a0a;
            color: #fecaca;
        }
    </style>
</head>

<body>

    <!-- MENU TOGGLE -->
    <input type="checkbox" id="menu-toggle">

    <!-- ================= TOP BAR ================= -->
    <header class="topbar admin-topbar">
        <label for="menu-toggle" class="menu-btn">☰</label>

        <div class="search-wrapper">
            <input type="text" class="search-bar" placeholder="Search accounts, events, logs...">
        </div>

        <h1 style="color: #ffffff;">
            🛡️ CHARUSAT ADMIN PORTAL
        </h1>

        <div class="topicons">
            <span id="theme-toggle" class="theme-btn" title="Toggle Theme">🌙</span>
            <span class="admin-badge">ADMIN</span>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="profile-circle" style="background: #b91c1c;" title="<?= htmlspecialchars($adminName) ?>"><?= htmlspecialchars($adminInitial) ?></span>
                <a href="logout.php" class="btn-admin-logout" title="Safely terminate session">🚪 Logout</a>
            </div>
        </div>
    </header>

    <div class="container">

        <!-- ================= SIDEBAR ================= -->
        <nav class="sidebar">
            <div class="logo">🛡️ ADMIN CONSOLE</div>

            <div class="menu-title">ADMINISTRATION</div>
            <a href="admin_dashboard.php" class="active">▦ Overview</a>
            <a href="manage_students.php">👨‍🎓 Student Management (CRUD)</a>
            <a href="test_students.php">👥 Enrolled Students</a>
            <a href="event.php">🎫 Event Management</a>
            <a href="test_registration.php">📝 Event Registrations</a>

            <div class="menu-title">PORTAL NAVIGATION</div>
            <a href="student_dashboard.php">🎓 View Student Portal</a>
            <a href="vevents.html">📅 Campus Events List</a>
            <a href="faq.html">❓ Portal FAQs</a>

            <div class="menu-title">SECURITY & SESSION</div>
            <a href="settings.html">⚙ Portal Settings</a>
            <a href="logout.php" style="color: #ef4444; font-weight: 600;">🚪 Logout</a>
        </nav>

        <!-- ================= MAIN CONTENT ================= -->
        <main class="content">

            <!-- Security Notice Banner -->
            <div class="security-notice">
                🔒 <strong>Restricted Access Area:</strong> You are logged into the Administrative Controller as <strong><?= htmlspecialchars($adminName) ?></strong> (<?= htmlspecialchars($adminEmail) ?>). Session timeout is set to 15 minutes of inactivity.
            </div>

            <!-- Page Heading -->
            <div class="page-heading">
                <div>
                    <h2>Administrator Overview</h2>
                    <p>System metrics, user directory, and database management</p>
                </div>
                <div class="semester" style="border-color: #b91c1c;">
                    <small>System Status</small>
                    <strong style="color: #16a34a;">● ACTIVE</strong>
                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">MySQL 9.0 Connected</div>
                </div>
            </div>

            <!-- Welcome Box -->
            <div class="admin-summary-box">
                <h2>Welcome, <?= htmlspecialchars($adminName) ?>!</h2>
                <p style="margin: 0; line-height: 1.5; color: #cbd5e1;">
                    You have full administrative privileges to oversee the CHARUSAT StudentHub portal. From this console, you can audit registered user accounts, inspect event registrations, monitor system connections, and enforce portal policies.
                </p>
            </div>

            <!-- Metric Stats Row -->
            <div class="section-title">
                <h3>Portal Statistics</h3>
            </div>

            <div class="stats-row">
                <div class="stat-box attendance-card">
                    <div class="stat-title">Portal Users</div>
                    <div class="stat-value"><?= $totalUsers ?></div>
                    <div class="stat-small">Registered user accounts</div>
                </div>

                <div class="stat-box assignment-card">
                    <div class="stat-title">Student Records</div>
                    <div class="stat-value"><?= $totalStudents ?></div>
                    <div class="stat-small"><a href="manage_students.php" style="color: #0284c7; font-weight: 600; text-decoration: none;">Manage Students (CRUD) →</a></div>
                </div>

                <div class="stat-box exam-card">
                    <div class="stat-title">Active Events</div>
                    <div class="stat-value"><?= $totalEvents ?></div>
                    <div class="stat-small">Scheduled campus events</div>
                </div>

                <div class="stat-box credits-card">
                    <div class="stat-title">Registrations</div>
                    <div class="stat-value"><?= $totalRegistrations ?></div>
                    <div class="stat-small">Event seats booked</div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="admin-table-wrapper">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0; font-size: 18px;">Registered User Directory</h3>
                    <small style="color: #64748b;">Showing latest registered accounts</small>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Email Address</th>
                            <th>Assigned Role</th>
                            <th>Created Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentUsers)): ?>
                            <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td><strong>#<?= (int)$user['id'] ?></strong></td>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="role-pill-admin">ADMIN</span>
                                        <?php else: ?>
                                            <span class="role-pill-student">STUDENT</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: #64748b; font-size: 13px;"><?= htmlspecialchars($user['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b; padding: 20px;">No user records found in database.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</body>
</html>
