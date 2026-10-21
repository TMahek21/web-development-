<?php
/**
 * loginpage.php
 * =========================================================================
 * CHARUSAT STUDENT PORTAL - SECURE LOGIN PAGE
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access, & Timeout
 * =========================================================================
 */
require_once __DIR__ . '/auth_session.php';
require_once __DIR__ . '/db.php';

preventPageCaching();

// If user is already logged in, redirect them to their respective dashboard
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit;
    } else {
        header("Location: student_dashboard.php");
        exit;
    }
}

$errorMessage   = '';
$infoMessage    = '';
$warningMessage = '';
$emailInput     = '';

// Check for status messages passed via GET query parameters
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'logged_out':
            $infoMessage = "You have been successfully logged out.";
            break;
        case 'timeout':
            $warningMessage = "Your session has expired due to 15 minutes of inactivity. Please log in again.";
            break;
        case 'unauthorized':
            $errorMessage = "Access denied. Please log in to access the portal.";
            break;
        case 'bad_password':
            $errorMessage = "Invalid email or password. Please try again.";
            break;
    }
}

// Process POST Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailInput = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($emailInput) || empty($password)) {
        $errorMessage = "Please enter both Email/Student ID and Password.";
    } else {
        try {
            // Retrieve user record from MySQL database using secure PDO prepared statement
            $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $emailInput]);
            $user = $stmt->fetch();

            // Verify entered password against the stored bcrypt hash using password_verify()
            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);

                // Store safe user authentication details into PHP session
                $_SESSION['user_id']       = (int)$user['id'];
                $_SESSION['name']          = $user['name'];
                $_SESSION['email']         = $user['email'];
                $_SESSION['role']          = $user['role'];
                $_SESSION['last_activity'] = time();

                // Role-Based Redirection
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                    exit;
                } else {
                    header("Location: student_dashboard.php");
                    exit;
                }
            } else {
                // Generic error message prevents username/email enumeration attacks
                $errorMessage = "Invalid email or password. Please try again.";
            }
        } catch (PDOException $e) {
            error_log("Login Query Error: " . $e->getMessage());
            $errorMessage = "A database error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CHARUSAT Student Hub Portal</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <style>
        .login-alert {
            padding: 10px 14px;
            margin-bottom: 16px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.4;
            text-align: left;
            font-weight: 500;
        }
        .login-alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;
        }
        .login-alert-info {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #7dd3fc;
        }
        .login-alert-warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }
        .credentials-hint {
            margin-top: 15px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 8px;
            font-size: 12px;
            color: #334155;
            text-align: left;
            border: 1px dashed #94a3b8;
        }
        body.dark-theme .credentials-hint {
            background: rgba(30, 41, 59, 0.7);
            color: #cbd5e1;
            border-color: #475569;
        }
    </style>
</head>

<body class="login-page">
    <main class="loginbox">
        <h1>CHARUSAT STUDENT PORTAL</h1>
        <h2>Secure Login</h2>
        <img src="charusat.jpg" class="background" alt="CHARUSAT Campus">

        <!-- Status & Error Alert Notifications -->
        <?php if (!empty($errorMessage)): ?>
            <div class="login-alert login-alert-danger" role="alert">
                ⚠️ <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($infoMessage)): ?>
            <div class="login-alert login-alert-info" role="status">
                ℹ️ <?= htmlspecialchars($infoMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($warningMessage)): ?>
            <div class="login-alert login-alert-warning" role="alert">
                ⏳ <?= htmlspecialchars($warningMessage) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="loginpage.php" id="loginForm">
            <label class="details" for="studentId">Email / Student ID:</label><br>
            <input type="text" id="studentId" name="email" 
                   value="<?= htmlspecialchars($emailInput) ?>" 
                   placeholder="Enter your registered email" required autocomplete="email">
            <br><br>

            <label class="details" for="password">Password:</label><br>
            <input type="password" id="password" name="password" 
                   placeholder="Enter Password" required autocomplete="current-password">
            <br><br>

            <input type="submit" id="loginBtn" value="Login">
            <input type="reset" value="Clear">
            <br><br>

            <a href="javascript:void(0)" onclick="alert('Please contact the CHARUSAT Examination / IT Cell at itcell@charusat.edu.in for password recovery.')">Forgot Password?</a>
            <br><br>

            <p style="margin: 5px 0;">New User?</p>
            <a href="register.php">Create Account</a>

            <!-- Sample Credentials Helper Box for Testing -->
            <div class="credentials-hint">
                <strong>Demo Test Accounts:</strong><br>
                🎓 <strong>Student:</strong> student@charusat.edu.in / Student@2026<br>
                🛡️ <strong>Admin:</strong> admin@charusat.edu.in / Admin@2026
            </div>
        </form>
    </main>

    <!-- Dark/Light Theme Emoji Button in Bottom-Right Corner -->
    <span id="theme-toggle" class="theme-btn login-theme-btn" title="Toggle Theme">🌙</span>
</body>
</html>
