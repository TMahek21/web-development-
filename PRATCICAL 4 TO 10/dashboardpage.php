<?php
/**
 * dashboardpage.php
 * Wrapper forwarding to student_dashboard.php with session protection
 */
require_once __DIR__ . '/auth_session.php';
requireLogin();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
} else {
    header("Location: student_dashboard.php");
    exit;
}
?>
