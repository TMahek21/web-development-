<?php
/**
 * manage_students.php
 * =========================================================================
 * CHARUSAT STUDENT PORTAL - STUDENT MANAGEMENT (CRUD WITH SEARCH & FILTER)
 * Practical 11: MySQLi Prepared Statements, CRUD, Search, Filter & RBAC
 * =========================================================================
 * 
 * Access restricted strictly to authenticated administrators.
 * All database operations (SELECT, INSERT, UPDATE, DELETE) use MySQLi prepared statements.
 */

require_once __DIR__ . '/auth_session.php';
require_once __DIR__ . '/mysqli_connection.php';

// Enforce Administrator Access Control (Practical 10 RBAC)
requireRole('admin');

$currentUser  = getCurrentUser();
$adminName    = $currentUser['name'];
$adminInitial = strtoupper(substr($adminName, 0, 1));

// Initialize variables for feedback messages
$successMsg = '';
$errorMsg   = '';

// Check for redirect flash messages
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added':
            $successMsg = "Student record successfully added to the portal!";
            break;
        case 'updated':
            $successMsg = "Student record successfully updated!";
            break;
        case 'deleted':
            $successMsg = "Student record was permanently deleted.";
            break;
    }
}

// -------------------------------------------------------------------------
// 1. PROCESS POST ACTIONS: ADD, UPDATE, DELETE
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ========================
    // ACTION A: ADD (INSERT)
    // ========================
    if ($action === 'add') {
        $name   = trim($_POST['name'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $course = trim($_POST['course'] ?? '');

        // Validation
        if (empty($name) || empty($email) || empty($course)) {
            $errorMsg = "All fields (Name, Email, Course) are required.";
        } elseif (!preg_match("/^[a-zA-Z\s.'-]+$/", $name)) {
            $errorMsg = "Student name should contain letters and spaces only.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = "Please enter a valid university email address.";
        } else {
            // Check for duplicate email using MySQLi prepared statement
            $checkStmt = $conn->prepare("SELECT student_id FROM students WHERE email = ?");
            if ($checkStmt) {
                $checkStmt->bind_param("s", $email);
                $checkStmt->execute();
                $checkStmt->store_result();

                if ($checkStmt->num_rows > 0) {
                    $errorMsg = "A student with the email '" . htmlspecialchars($email) . "' is already enrolled.";
                }
                $checkStmt->close();
            }

            // If valid, insert record using MySQLi prepared statement
            if (empty($errorMsg)) {
                $insertStmt = $conn->prepare("INSERT INTO students (name, email, course) VALUES (?, ?, ?)");
                if ($insertStmt) {
                    $insertStmt->bind_param("sss", $name, $email, $course);
                    if ($insertStmt->execute()) {
                        $insertStmt->close();
                        header("Location: manage_students.php?msg=added");
                        exit;
                    } else {
                        error_log("Student INSERT error: " . $insertStmt->error);
                        $errorMsg = "Failed to add student. Please try again.";
                    }
                    $insertStmt->close();
                } else {
                    $errorMsg = "Database error while preparing student insertion.";
                }
            }
        }
    }

    // ========================
    // ACTION B: UPDATE (EDIT)
    // ========================
    elseif ($action === 'update') {
        $studentId = (int)($_POST['student_id'] ?? 0);
        $name      = trim($_POST['name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $course    = trim($_POST['course'] ?? '');

        if ($studentId <= 0) {
            $errorMsg = "Invalid student identifier.";
        } elseif (empty($name) || empty($email) || empty($course)) {
            $errorMsg = "All fields are required when updating student details.";
        } elseif (!preg_match("/^[a-zA-Z\s.'-]+$/", $name)) {
            $errorMsg = "Student name should contain letters and spaces only.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = "Please enter a valid email address.";
        } else {
            // Check if email is already taken by ANOTHER student
            $checkStmt = $conn->prepare("SELECT student_id FROM students WHERE email = ? AND student_id != ?");
            if ($checkStmt) {
                $checkStmt->bind_param("si", $email, $studentId);
                $checkStmt->execute();
                $checkStmt->store_result();

                if ($checkStmt->num_rows > 0) {
                    $errorMsg = "The email '" . htmlspecialchars($email) . "' is already used by another student.";
                }
                $checkStmt->close();
            }

            // Execute UPDATE using MySQLi prepared statement
            if (empty($errorMsg)) {
                $updateStmt = $conn->prepare("UPDATE students SET name = ?, email = ?, course = ? WHERE student_id = ?");
                if ($updateStmt) {
                    $updateStmt->bind_param("sssi", $name, $email, $course, $studentId);
                    if ($updateStmt->execute()) {
                        $updateStmt->close();
                        header("Location: manage_students.php?msg=updated");
                        exit;
                    } else {
                        error_log("Student UPDATE error: " . $updateStmt->error);
                        $errorMsg = "Failed to update student details.";
                    }
                    $updateStmt->close();
                } else {
                    $errorMsg = "Database error while preparing student update.";
                }
            }
        }
    }

    // ========================
    // ACTION C: DELETE
    // ========================
    elseif ($action === 'delete') {
        $studentId = (int)($_POST['student_id'] ?? 0);

        if ($studentId <= 0) {
            $errorMsg = "Invalid student record ID for deletion.";
        } else {
            // Permanent delete using MySQLi prepared statement
            $delStmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
            if ($delStmt) {
                $delStmt->bind_param("i", $studentId);
                if ($delStmt->execute()) {
                    if ($delStmt->affected_rows > 0) {
                        $delStmt->close();
                        header("Location: manage_students.php?msg=deleted");
                        exit;
                    } else {
                        $errorMsg = "No student found with ID #{$studentId} to delete.";
                    }
                } else {
                    error_log("Student DELETE error: " . $delStmt->error);
                    $errorMsg = "Failed to delete student record. It may be linked to active event registrations.";
                }
                $delStmt->close();
            } else {
                $errorMsg = "Database error while preparing student deletion.";
            }
        }
    }
}

// -------------------------------------------------------------------------
// 2. CHECK FOR EDIT MODE (GET edit_id)
// -------------------------------------------------------------------------
$isEditMode  = false;
$editStudent = null;

if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    if ($editId > 0) {
        $fetchStmt = $conn->prepare("SELECT student_id, name, email, course FROM students WHERE student_id = ?");
        if ($fetchStmt) {
            $fetchStmt->bind_param("i", $editId);
            $fetchStmt->execute();
            $result = $fetchStmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $isEditMode  = true;
                $editStudent = $row;
            } else {
                $errorMsg = "Student with ID #{$editId} not found.";
            }
            $fetchStmt->close();
        }
    }
}

// -------------------------------------------------------------------------
// 3. FETCH DISTINCT COURSES FOR FILTER DROPDOWN
// -------------------------------------------------------------------------
$courseList = [];
$coursesRes = $conn->query("SELECT DISTINCT course FROM students ORDER BY course ASC");
if ($coursesRes) {
    while ($cRow = $coursesRes->fetch_assoc()) {
        $courseList[] = $cRow['course'];
    }
}

// Predefined CHARUSAT Branches
$standardCourses = [
    "Computer Engineering",
    "Information Technology",
    "Computer Science & Engineering",
    "Artificial Intelligence & ML",
    "Electronics & Communication",
    "Civil Engineering",
    "Mechanical Engineering",
    "Electrical Engineering"
];
$allCourses = array_unique(array_merge($standardCourses, $courseList));
sort($allCourses);

// -------------------------------------------------------------------------
// 4. SEARCH, FILTER & PAGINATION PARAMETERS (GET)
// -------------------------------------------------------------------------
$searchQuery  = trim($_GET['search'] ?? '');
$courseFilter = trim($_GET['course_filter'] ?? 'all');
$currentPage  = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 8; // 8 students per page for clean layout

// Build dynamic WHERE clause with MySQLi prepared statement parameters
$whereClauses = [];
$paramValues  = [];
$paramTypes   = "";

// Search across name, email, and course
if (!empty($searchQuery)) {
    $whereClauses[] = "(name LIKE ? OR email LIKE ? OR course LIKE ?)";
    $wildcard = "%" . $searchQuery . "%";
    $paramValues[] = $wildcard;
    $paramValues[] = $wildcard;
    $paramValues[] = $wildcard;
    $paramTypes   .= "sss";
}

// Filter by specific course
if (!empty($courseFilter) && $courseFilter !== 'all') {
    $whereClauses[] = "course = ?";
    $paramValues[]  = $courseFilter;
    $paramTypes    .= "s";
}

$whereSql = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";

// Count Total Matching Records
$countSql = "SELECT COUNT(*) FROM students" . $whereSql;
$countStmt = $conn->prepare($countSql);

if (!empty($paramTypes)) {
    $countStmt->bind_param($paramTypes, ...$paramValues);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRecords = $countResult->fetch_row()[0];
$countStmt->close();

$totalPages = max(1, (int)ceil($totalRecords / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $perPage;

// Fetch Page Records with ORDER BY and LIMIT/OFFSET
$selectSql = "SELECT student_id, name, email, course FROM students" . $whereSql . " ORDER BY student_id DESC LIMIT ? OFFSET ?";
$selectStmt = $conn->prepare($selectSql);

$pageTypes = $paramTypes . "ii";
$pageParams = array_merge($paramValues, [$perPage, $offset]);
$selectStmt->bind_param($pageTypes, ...$pageParams);
$selectStmt->execute();
$studentsResult = $selectStmt->get_result();

$students = [];
while ($sRow = $studentsResult->fetch_assoc()) {
    $students[] = $sRow;
}
$selectStmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management (CRUD) - CHARUSAT Admin</title>
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

        /* CRUD Specific Styles */
        .crud-grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 992px) {
            .crud-grid {
                grid-template-columns: 1fr;
            }
        }

        .crud-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }
        body.dark-theme .crud-card {
            background: #1e293b;
            color: #f1f5f9;
        }

        .crud-card h3 {
            margin-top: 0;
            margin-bottom: 16px;
            color: #003366;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        body.dark-theme .crud-card h3 {
            color: #60a5fa;
        }

        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #334155;
        }
        body.dark-theme .form-group label {
            color: #cbd5e1;
        }
        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            background: #ffffff;
            color: #0f172a;
        }
        body.dark-theme .form-control {
            background: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }

        .btn-submit-action {
            background: #003366;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            width: 100%;
            transition: 0.2s;
        }
        .btn-submit-action:hover {
            background: #002244;
        }
        .btn-cancel-edit {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #64748b;
            font-size: 13px;
            text-decoration: none;
        }
        .btn-cancel-edit:hover {
            text-decoration: underline;
        }

        /* Search & Filter Bar */
        .search-filter-box {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        body.dark-theme .search-filter-box {
            background: #0f172a;
            border-color: #334155;
        }
        .search-filter-box .form-control {
            flex: 1;
            min-width: 180px;
        }
        .btn-search {
            background: #0284c7;
            color: white;
            border: none;
            padding: 9px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-clear-search {
            background: #e2e8f0;
            color: #475569;
            padding: 9px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
        body.dark-theme .btn-clear-search {
            background: #334155;
            color: #cbd5e1;
        }

        /* Student Table */
        .student-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .student-table th, .student-table td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        body.dark-theme .student-table th, 
        body.dark-theme .student-table td {
            border-color: #334155;
        }
        .student-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
        }
        body.dark-theme .student-table th {
            background: #0f172a;
            color: #94a3b8;
        }

        .course-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        body.dark-theme .course-badge {
            background: #0369a1;
            color: #e0f2fe;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .btn-edit {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            padding: 4px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-edit:hover {
            background: #fde68a;
        }
        .btn-delete {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-delete:hover {
            background: #ef4444;
            color: white;
        }

        /* Alert Notifications */
        .crud-alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .crud-alert-success {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .crud-alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 13px;
        }
        body.dark-theme .pagination-container {
            border-color: #334155;
            color: #94a3b8;
        }
        .pagination-links {
            display: flex;
            gap: 6px;
        }
        .page-link-btn {
            display: inline-block;
            padding: 6px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            text-decoration: none;
            color: #334155;
            font-weight: 600;
        }
        .page-link-btn.active {
            background: #003366;
            color: white;
            border-color: #003366;
        }
        body.dark-theme .page-link-btn {
            border-color: #334155;
            color: #cbd5e1;
        }

        .empty-results-box {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
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
            <input type="text" class="search-bar" placeholder="Search portal...">
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
            <a href="admin_dashboard.php">▦ Overview</a>
            <a href="manage_students.php" class="active">👨‍🎓 Student Management (CRUD)</a>
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

            <!-- PAGE HEADING -->
            <div class="page-heading">
                <div>
                    <h2>👨‍🎓 Student Management</h2>
                    <p>MySQLi Prepared Statements: Insert, View, Update, Delete, Search & Filter</p>
                </div>
                <div class="semester" style="border-color: #b91c1c;">
                    <small>Database</small>
                    <strong style="color: #0369a1;">MySQLi Active</strong>
                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Total: <?= $totalRecords ?> Students</div>
                </div>
            </div>

            <!-- Feedback Alert Banners -->
            <?php if (!empty($successMsg)): ?>
                <div class="crud-alert crud-alert-success">
                    ✅ <?= htmlspecialchars($successMsg) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <div class="crud-alert crud-alert-danger">
                    ⚠️ <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>

            <!-- CRUD GRID: FORM ON LEFT, TABLE ON RIGHT -->
            <div class="crud-grid">

                <!-- 1. ADD / EDIT STUDENT FORM CARD -->
                <div class="crud-card">
                    <h3>
                        <span><?= $isEditMode ? "✏️ Edit Student" : "➕ Add New Student" ?></span>
                        <?php if ($isEditMode): ?>
                            <span class="admin-badge" style="background:#0284c7;">#<?= (int)$editStudent['student_id'] ?></span>
                        <?php endif; ?>
                    </h3>

                    <form method="POST" action="manage_students.php" id="studentForm">
                        <input type="hidden" name="action" value="<?= $isEditMode ? 'update' : 'add' ?>">
                        <?php if ($isEditMode): ?>
                            <input type="hidden" name="student_id" value="<?= (int)$editStudent['student_id'] ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="name">Student Full Name *</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   placeholder="e.g. Priya Sharma" 
                                   value="<?= htmlspecialchars($isEditMode ? $editStudent['name'] : '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">University Email Address *</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   placeholder="e.g. priya.s@charusat.edu.in" 
                                   value="<?= htmlspecialchars($isEditMode ? $editStudent['email'] : '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="course">Course / Department *</label>
                            <select id="course" name="course" class="form-control" required>
                                <option value="">-- Select Course / Branch --</option>
                                <?php foreach ($allCourses as $c): ?>
                                    <option value="<?= htmlspecialchars($c) ?>" 
                                        <?= ($isEditMode && $editStudent['course'] === $c) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn-submit-action">
                            <?= $isEditMode ? "💾 Save Changes" : "➕ Register Student" ?>
                        </button>

                        <?php if ($isEditMode): ?>
                            <a href="manage_students.php" class="btn-cancel-edit">✕ Cancel Edit & Return</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- 2. STUDENT DIRECTORY (SELECT, SEARCH, FILTER & TABLE) -->
                <div class="crud-card">
                    <h3>
                        <span>📋 Enrolled Students Directory</span>
                        <small style="font-size: 13px; color: #64748b; font-weight: normal;">
                            Showing <?= count($students) ?> of <?= $totalRecords ?> records
                        </small>
                    </h3>

                    <!-- Search and Filter Form (GET) -->
                    <form method="GET" action="manage_students.php" class="search-filter-box">
                        <input type="text" name="search" class="form-control" 
                               placeholder="🔍 Search by name, email, or course..." 
                               value="<?= htmlspecialchars($searchQuery) ?>">

                        <select name="course_filter" class="form-control" style="max-width: 240px;">
                            <option value="all">-- All Departments --</option>
                            <?php foreach ($allCourses as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" 
                                    <?= ($courseFilter === $c) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="btn-search">Search</button>

                        <?php if (!empty($searchQuery) || ($courseFilter !== 'all' && !empty($courseFilter))): ?>
                            <a href="manage_students.php" class="btn-clear-search">Clear Filters</a>
                        <?php endif; ?>
                    </form>

                    <!-- Students Records Table -->
                    <div style="overflow-x: auto;">
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>Student Name</th>
                                    <th>Email Address</th>
                                    <th>Course / Branch</th>
                                    <th style="width: 140px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($students)): ?>
                                    <?php foreach ($students as $s): ?>
                                        <tr>
                                            <td><strong>#<?= (int)$s['student_id'] ?></strong></td>
                                            <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                                            <td><?= htmlspecialchars($s['email']) ?></td>
                                            <td>
                                                <span class="course-badge"><?= htmlspecialchars($s['course']) ?></span>
                                            </td>
                                            <td style="text-align: center;">
                                                <div class="action-buttons" style="justify-content: center;">
                                                    <a href="manage_students.php?edit_id=<?= (int)$s['student_id'] ?>" 
                                                       class="btn-edit" title="Edit student record">✏️ Edit</a>
                                                    
                                                    <button type="button" class="btn-delete" 
                                                            onclick="confirmStudentDelete(<?= (int)$s['student_id'] ?>, '<?= htmlspecialchars(addslashes($s['name'])) ?>')"
                                                            title="Permanently delete student">🗑️ Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-results-box">
                                                <div style="font-size: 36px; margin-bottom: 8px;">🔍</div>
                                                <h4 style="margin: 0 0 6px 0; color: #334155;">No Student Records Found</h4>
                                                <p style="margin: 0 0 12px 0;">No students match your active search and filter criteria.</p>
                                                <a href="manage_students.php" class="btn-clear-search">View All Students</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination-container">
                            <div>
                                Page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong>
                            </div>
                            <div class="pagination-links">
                                <?php
                                $queryParams = [];
                                if (!empty($searchQuery)) $queryParams['search'] = $searchQuery;
                                if (!empty($courseFilter) && $courseFilter !== 'all') $queryParams['course_filter'] = $courseFilter;
                                
                                // Previous link
                                if ($currentPage > 1):
                                    $prevParams = array_merge($queryParams, ['page' => $currentPage - 1]);
                                ?>
                                    <a href="manage_students.php?<?= http_build_query($prevParams) ?>" class="page-link-btn">← Prev</a>
                                <?php endif; ?>

                                <?php for ($p = 1; $p <= $totalPages; $p++): 
                                    $pageParams = array_merge($queryParams, ['page' => $p]);
                                ?>
                                    <a href="manage_students.php?<?= http_build_query($pageParams) ?>" 
                                       class="page-link-btn <?= ($p === $currentPage) ? 'active' : '' ?>">
                                        <?= $p ?>
                                    </a>
                                <?php endfor; ?>

                                <?php
                                // Next link
                                if ($currentPage < $totalPages):
                                    $nextParams = array_merge($queryParams, ['page' => $currentPage + 1]);
                                ?>
                                    <a href="manage_students.php?<?= http_build_query($nextParams) ?>" class="page-link-btn">Next →</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

            </div>

        </main>
    </div>

    <!-- Hidden Form for Safe POST Deletion -->
    <form id="deleteForm" method="POST" action="manage_students.php" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="student_id" id="deleteStudentId" value="">
    </form>

    <script>
        /**
         * Confirms with administrator before permanent deletion.
         * If confirmed, populates hidden form and executes POST deletion.
         * If cancelled, no action is performed.
         */
        function confirmStudentDelete(id, name) {
            const promptMessage = "Are you sure you want to permanently delete student:\\n\\n" +
                                  "• Name: " + name + "\\n" +
                                  "• ID: #" + id + "\\n\\n" +
                                  "WARNING: This action cannot be undone and will permanently remove the record from the MySQL database.";
            
            if (confirm(promptMessage)) {
                document.getElementById('deleteStudentId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
