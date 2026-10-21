<?php
/* =========================================================================
   CHARUSAT STUDENTHUB - DATABASE CONNECTION TEST (test_connection.php)
   Practical 8: Verifies PDO Connectivity to MySQL Server 9.0 (studenthub)
   ========================================================================= */

// Start buffering output
ob_start();

$connectionStatus = false;
$errorMessage     = "";
$serverVersion    = "";
$currentDb        = "";
$tableStats       = [];

try {
    // 1. Require the shared db.php connection
    require_once "db.php";

    // 2. Query MySQL server version and current database
    $versionStmt = $pdo->query("SELECT VERSION() AS ver, DATABASE() AS cur_db");
    $meta = $versionStmt->fetch();
    $serverVersion = $meta['ver'] ?? 'Unknown';
    $currentDb     = $meta['cur_db'] ?? 'Unknown';

    // 3. Inspect the three required tables
    $requiredTables = ['students', 'events', 'registrations'];
    foreach ($requiredTables as $tbl) {
        $countStmt = $pdo->query("SELECT COUNT(*) AS total FROM `$tbl`");
        $cnt = $countStmt->fetchColumn();
        $tableStats[$tbl] = [
            'status' => 'Found',
            'count'  => (int)$cnt
        ];
    }

    $connectionStatus = true;
} catch (PDOException $e) {
    $connectionStatus = false;
    $errorMessage = $e->getMessage();
}

// If invoked from CLI (terminal)
if (php_sapi_name() === 'cli') {
    echo "=====================================================\n";
    echo "  CHARUSAT StudentHub - Database Connection Test\n";
    echo "=====================================================\n";
    if ($connectionStatus) {
        echo "[STATUS] SUCCESS: Connected to MySQL Server successfully!\n";
        echo "[SERVER] MySQL Version: " . $serverVersion . "\n";
        echo "[DB]     Database: " . $currentDb . "\n";
        echo "[DRIVER] PDO MySQL Driver: Active\n";
        echo "[TABLES] Verified Required Tables:\n";
        foreach ($tableStats as $tbl => $data) {
            echo "         - {$tbl}: {$data['count']} record(s)\n";
        }
    } else {
        echo "[STATUS] FAILED: Could not connect to database.\n";
        echo "[ERROR]  " . $errorMessage . "\n";
    }
    echo "=====================================================\n";
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test - CHARUSAT StudentHub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .test-container {
            max-width: 850px;
            margin: 40px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 51, 102, 0.12);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1e293b;
        }
        .test-header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .test-header h1 {
            color: #003366;
            margin: 0;
            font-size: 24px;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 14px;
        }
        .status-badge.success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .status-badge.error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
        }
        .info-card span {
            display: block;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .info-card strong {
            font-size: 16px;
            color: #003366;
        }
        .tables-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .tables-table th, .tables-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .tables-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            font-size: 14px;
        }
        .nav-links {
            margin-top: 30px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .nav-links a {
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: #003366;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #002244;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #1e293b;
        }
        .btn-secondary:hover {
            background: #cbd5e1;
        }
    </style>
</head>
<body style="background: #f0f4f8; margin: 0; padding: 20px;">

    <div class="test-container">
        <div class="test-header">
            <div>
                <span style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">CHARUSAT StudentHub &bull; Practical 8</span>
                <h1>MySQL 9.0 Database Connection Test</h1>
            </div>
            <?php if ($connectionStatus): ?>
                <span class="status-badge success">● Connected Successfully</span>
            <?php else: ?>
                <span class="status-badge error">✕ Connection Failed</span>
            <?php endif; ?>
        </div>

        <?php if ($connectionStatus): ?>
            <div class="info-grid">
                <div class="info-card">
                    <span>Database Engine</span>
                    <strong>MySQL Server 9.0</strong>
                </div>
                <div class="info-card">
                    <span>Connection Driver</span>
                    <strong>PHP PDO (utf8mb4)</strong>
                </div>
                <div class="info-card">
                    <span>Active Database</span>
                    <strong><?= htmlspecialchars($currentDb) ?></strong>
                </div>
                <div class="info-card">
                    <span>Server Version</span>
                    <strong><?= htmlspecialchars($serverVersion) ?></strong>
                </div>
            </div>

            <h3 style="color: #003366; margin-top: 25px; margin-bottom: 10px;">Verified Schema Tables</h3>
            <table class="tables-table">
                <thead>
                    <tr>
                        <th>Table Name</th>
                        <th>Status</th>
                        <th>Record Count</th>
                        <th>Schema Role</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>students</code></td>
                        <td><span style="color: #15803d; font-weight: 600;">✓ Present</span></td>
                        <td><strong><?= $tableStats['students']['count'] ?></strong> records</td>
                        <td>Student profiles, names, unique emails, courses</td>
                    </tr>
                    <tr>
                        <td><code>events</code></td>
                        <td><span style="color: #15803d; font-weight: 600;">✓ Present</span></td>
                        <td><strong><?= $tableStats['events']['count'] ?></strong> records</td>
                        <td>Campus events, dates, venues</td>
                    </tr>
                    <tr>
                        <td><code>registrations</code></td>
                        <td><span style="color: #15803d; font-weight: 600;">✓ Present</span></td>
                        <td><strong><?= $tableStats['registrations']['count'] ?></strong> records</td>
                        <td>Junction table (student_id FK, event_id FK, unique constraint)</td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top: 25px; padding: 15px; background: #eff6ff; border-left: 4px solid #003366; border-radius: 4px; font-size: 14px; color: #1e3a8a;">
                <strong>Practical 8 Connectivity Verification:</strong>
                PDO connection is established using <code>PDO::ATTR_ERRMODE_EXCEPTION</code>. Credentials are kept server-side in <code>db.php</code> and never exposed in client scripts.
            </div>

        <?php else: ?>
            <div style="padding: 20px; background: #fee2e2; border-left: 4px solid #b91c1c; border-radius: 4px; color: #7f1d1d;">
                <h3 style="margin-top: 0;">Connection Error Details:</h3>
                <p><?= htmlspecialchars($errorMessage) ?></p>
                <p><strong>Troubleshooting:</strong> Ensure MySQL Server 9.0 service is running on port 3306 and the password in <code>db.php</code> matches your MySQL root password.</p>
            </div>
        <?php endif; ?>

        <div class="nav-links">
            <a href="test_students.php" class="btn-primary">Test SELECT Prepared Statement →</a>
            <a href="test_registration.php" class="btn-primary">Test INSERT Registration →</a>
            <a href="event.php" class="btn-secondary">Open StudentHub Event Registration</a>
            <a href="dashboardpage.html" class="btn-secondary">StudentHub Dashboard</a>
        </div>
    </div>

</body>
</html>
