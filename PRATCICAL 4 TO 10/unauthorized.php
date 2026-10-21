<?php
/**
 * unauthorized.php
 * =========================================================================
 * CHARUSAT STUDENT PORTAL - UNAUTHORIZED ACCESS ERROR PAGE (403 FORBIDDEN)
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access, & Timeout
 * =========================================================================
 */
require_once __DIR__ . '/auth_session.php';
preventPageCaching();

// Check if user is logged in
$isAuth = isset($_SESSION['user_id']);
$userName = $_SESSION['name'] ?? 'User';
$userRole = $_SESSION['role'] ?? 'guest';
$userEmail = $_SESSION['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Access Denied - CHARUSAT Portal</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <style>
        .error-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .error-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 540px;
            width: 100%;
            padding: 40px;
            text-align: center;
            border-top: 6px solid #dc2626;
        }
        body.dark-theme .error-card {
            background: #1e293b;
            color: #f1f5f9;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .error-icon {
            font-size: 64px;
            margin-bottom: 15px;
        }
        .error-title {
            color: #dc2626;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .error-desc {
            color: #475569;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        body.dark-theme .error-desc {
            color: #94a3b8;
        }
        .user-info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 24px;
            text-align: left;
            font-size: 14px;
        }
        body.dark-theme .user-info-box {
            background: #0f172a;
            border-color: #334155;
        }
        .role-pill {
            display: inline-block;
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 10px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .btn-action {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.2s;
        }
        .btn-primary-action {
            background: #003366;
            color: white;
        }
        .btn-primary-action:hover {
            background: #002244;
        }
        .btn-secondary-action {
            background: #e2e8f0;
            color: #334155;
        }
        .btn-secondary-action:hover {
            background: #cbd5e1;
        }
    </style>
</head>
<body class="login-page">
    <img src="charusat.jpg" class="background" alt="CHARUSAT Campus">

    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">🛡️ 🚫</div>
            <h1 class="error-title">403 - Access Denied</h1>
            <p class="error-desc">
                You do not have administrative privileges to view this page. This area is strictly restricted to authorized CHARUSAT administrators.
            </p>

            <?php if ($isAuth): ?>
                <div class="user-info-box">
                    <div><strong>Logged In As:</strong> <?= htmlspecialchars($userName) ?></div>
                    <div><strong>Email:</strong> <?= htmlspecialchars($userEmail) ?></div>
                    <div><strong>Current Role:</strong> <span class="role-pill"><?= htmlspecialchars($userRole) ?></span></div>
                    <div style="margin-top: 6px; color: #dc2626; font-size: 12px;">Required Privilege: <strong>ADMIN</strong></div>
                </div>
                <div class="btn-group">
                    <a href="student_dashboard.php" class="btn-action btn-primary-action">← Student Dashboard</a>
                    <a href="logout.php" class="btn-action btn-secondary-action">🚪 Logout</a>
                </div>
            <?php else: ?>
                <div class="btn-group">
                    <a href="loginpage.php" class="btn-action btn-primary-action">Go to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <span id="theme-toggle" class="theme-btn login-theme-btn" title="Toggle Theme">🌙</span>
</body>
</html>
