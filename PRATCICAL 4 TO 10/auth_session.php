<?php
/**
 * auth_session.php
 * =========================================================================
 * CHARUSAT STUDENT PORTAL - SESSION & ACCESS CONTROL MODULE
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access, & Timeout
 * =========================================================================
 * 
 * Provides centralized session configuration, inactivity timeout tracking (15 mins),
 * role verification (student vs admin), and anti-caching HTTP response headers.
 */

// 1. CONFIGURE SECURE SESSION SETTINGS & START SESSION
if (session_status() === PHP_SESSION_NONE) {
    // Configure session cookie parameters safely for local XAMPP HTTP development
    session_set_cookie_params([
        'lifetime' => 0,          // Expire when browser closes
        'path'     => '/',        // Available across all portal pages
        'domain'   => '',         // Default host
        'secure'   => false,      // false for local HTTP development on localhost
        'httponly' => true,       // Prevents JavaScript access to session cookie (XSS defense)
        'samesite' => 'Lax'       // CSRF mitigation
    ]);

    session_start();
}

// 2. SESSION TIMEOUT CONSTANT (15 minutes = 900 seconds)
if (!defined('SESSION_TIMEOUT_DURATION')) {
    define('SESSION_TIMEOUT_DURATION', 900); // 15 minutes in seconds
}

/**
 * Enforces anti-caching HTTP headers.
 * Ensures browsers do not serve cached versions of protected dashboard pages
 * when the user clicks the browser Back button after logging out.
 */
function preventPageCaching() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
}

/**
 * Clears and completely destroys the active session and expires the session cookie.
 */
function terminateSession() {
    // Unset all session variables
    $_SESSION = [];

    // Expire the session cookie in browser
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

    // Destroy the server-side session data
    session_destroy();
}

/**
 * Checks whether a user is currently authenticated and validates session inactivity timeout.
 * 
 * @return bool True if authenticated and session is still active; False otherwise.
 */
function checkSessionTimeout() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Check if session has exceeded 15 minutes (900s) of inactivity
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_DURATION)) {
        terminateSession();
        return true; // Indicates timeout occurred
    }

    // Update last activity timestamp on current authenticated request
    $_SESSION['last_activity'] = time();
    return false;
}

/**
 * Enforces that a user must be logged in.
 * If not authenticated or timed out, redirects immediately to login page.
 */
function requireLogin() {
    preventPageCaching();

    // Check timeout first
    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_DURATION)) {
            terminateSession();
            header("Location: loginpage.php?msg=timeout");
            exit;
        }
        // Update activity timestamp
        $_SESSION['last_activity'] = time();
    } else {
        // Not authenticated
        header("Location: loginpage.php?msg=unauthorized");
        exit;
    }
}

/**
 * Enforces Role-Based Access Control (RBAC).
 * 
 * @param string $requiredRole The required role ('admin' or 'student')
 */
function requireRole($requiredRole) {
    // First, verify the user is logged in and not timed out
    requireLogin();

    $userRole = $_SESSION['role'] ?? '';

    // If role does not match, block access
    if ($userRole !== $requiredRole) {
        if ($userRole === 'student' && $requiredRole === 'admin') {
            // Student attempting to access admin page
            header("Location: unauthorized.php");
            exit;
        } else {
            // Any other unauthorized access
            header("Location: loginpage.php?msg=unauthorized");
            exit;
        }
    }
}

/**
 * Returns current authenticated user information from session.
 * 
 * @return array
 */
function getCurrentUser() {
    return [
        'id'    => $_SESSION['user_id'] ?? null,
        'name'  => $_SESSION['name'] ?? 'Guest',
        'email' => $_SESSION['email'] ?? '',
        'role'  => $_SESSION['role'] ?? 'guest'
    ];
}
?>
