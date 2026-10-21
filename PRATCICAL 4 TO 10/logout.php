<?php
/**
 * logout.php
 * =========================================================================
 * CHARUSAT STUDENT PORTAL - SECURE LOGOUT HANDLER
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access, & Timeout
 * =========================================================================
 * 
 * Safely clears all session data, destroys the server session, expires the
 * session cookie in the client browser, sends anti-cache headers, and redirects
 * to loginpage.php with a confirmation message.
 */

require_once __DIR__ . '/auth_session.php';

// 1. Send anti-caching HTTP headers
preventPageCaching();

// 2. Clear all session variables
$_SESSION = [];

// 3. Expire and remove the session cookie in client browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Destroy the server-side session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// 5. Redirect to login page with logout confirmation notification
header("Location: loginpage.php?msg=logged_out");
exit;
?>
