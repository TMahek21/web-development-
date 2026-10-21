<?php
/* =========================================================================
   CHARUSAT STUDENTHUB - PRACTICAL 9 AUTOMATED TEST SUITE (test_practical9.php)
   Topic: Basic Testing for Practical 9 Secure User Registration
   Tests:
     1. Empty required fields
     2. Invalid email format
     3. Valid user registration
     4. Duplicate email rejection
     5. Password stored as secure hash verification
   ========================================================================= */

require_once "mysqli_connection.php";

$testResults = [];

// Helper function to simulate a POST request directly to register.php
function postToRegister($postData) {
    // In CLI mode, simulate an isolated HTTP POST request directly via PHP CLI
    if (php_sapi_name() === 'cli') {
        $tempPostFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "post_test_" . uniqid() . ".php";
        $code = '<?php
            $_SERVER["REQUEST_METHOD"] = "POST";
            $_POST = ' . var_export($postData, true) . ';
            chdir("' . addslashes(__DIR__) . '");
            ob_start();
            require "register.php";
            $html = ob_get_clean();
            echo $html;
        ';
        file_put_contents($tempPostFile, $code);
        $response = shell_exec("C:\\xampp\\php\\php.exe \"$tempPostFile\"");
        @unlink($tempPostFile);
        return $response;
    }

    // If running from browser via Apache, test via HTTP cURL
    $ch = curl_init();
    $url = "http://localhost/webpages/register.php";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Fallback if cURL fails
    if ($httpCode === 0 || empty($response)) {
        $tempPostFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "post_test_" . uniqid() . ".php";
        $code = '<?php
            $_SERVER["REQUEST_METHOD"] = "POST";
            $_POST = ' . var_export($postData, true) . ';
            chdir("' . addslashes(__DIR__) . '");
            ob_start();
            require "register.php";
            $html = ob_get_clean();
            echo $html;
        ';
        file_put_contents($tempPostFile, $code);
        $response = shell_exec("C:\\xampp\\php\\php.exe \"$tempPostFile\"");
        @unlink($tempPostFile);
    }

    return $response;
}

// -------------------------------------------------------------------------
// TEST CASE 1: Empty Required Fields
// -------------------------------------------------------------------------
$beforeCountStmt = $conn->query("SELECT COUNT(*) AS total FROM users");
$beforeCount = (int)$beforeCountStmt->fetch_assoc()['total'];

$postData1 = [
    'name'            => '',
    'email'           => '',
    'password'        => '',
    'confirmPassword' => ''
];
$resp1 = postToRegister($postData1);

$afterCountStmt = $conn->query("SELECT COUNT(*) AS total FROM users");
$afterCount1 = (int)$afterCountStmt->fetch_assoc()['total'];

$hasNameErr  = (strpos($resp1, "Please enter your full name") !== false);
$hasEmailErr = (strpos($resp1, "Please enter your email address") !== false);
$hasPassErr  = (strpos($resp1, "Please enter a password") !== false);
$dbUnchanged1 = ($beforeCount === $afterCount1);

$passed1 = $hasNameErr && $hasEmailErr && $hasPassErr && $dbUnchanged1;

$testResults[] = [
    'name'     => 'Empty Required Fields',
    'input'    => 'Name = "", Email = "", Password = ""',
    'expected' => 'Form rejected with error messages for Name, Email, Password. No record inserted in users table.',
    'actual'   => ($hasNameErr ? 'Name error shown; ' : 'Missing name err; ') .
                  ($hasEmailErr ? 'Email error shown; ' : 'Missing email err; ') .
                  ($hasPassErr ? 'Password error shown; ' : 'Missing pass err; ') .
                  ($dbUnchanged1 ? 'Users count unchanged (' . $afterCount1 . ').' : 'Count changed!'),
    'status'   => $passed1 ? 'PASSED' : 'FAILED'
];

// -------------------------------------------------------------------------
// TEST CASE 2: Invalid Email Format
// -------------------------------------------------------------------------
$postData2 = [
    'name'            => 'Valid Test User',
    'email'           => 'invalid-email-format',
    'password'        => 'Password@123',
    'confirmPassword' => 'Password@123'
];
$resp2 = postToRegister($postData2);

$afterCountStmt = $conn->query("SELECT COUNT(*) AS total FROM users");
$afterCount2 = (int)$afterCountStmt->fetch_assoc()['total'];

$hasInvalidEmailErr = (strpos($resp2, "Please enter a valid email address") !== false);
$dbUnchanged2       = ($afterCount1 === $afterCount2);
$passed2            = $hasInvalidEmailErr && $dbUnchanged2;

$testResults[] = [
    'name'     => 'Invalid Email Format',
    'input'    => 'Email = "invalid-email-format"',
    'expected' => 'Form rejected with "Please enter a valid email address". No record inserted.',
    'actual'   => ($hasInvalidEmailErr ? 'Invalid email format error displayed. ' : 'Error message missing. ') .
                  ($dbUnchanged2 ? 'Users table count remained ' . $afterCount2 . '.' : 'Count changed!'),
    'status'   => $passed2 ? 'PASSED' : 'FAILED'
];

// -------------------------------------------------------------------------
// TEST CASE 3: Valid Registration
// -------------------------------------------------------------------------
$testEmail = "practical9.demo@charusat.edu.in";

// Ensure email is clean for this test
$conn->query("DELETE FROM users WHERE email = '" . $conn->real_escape_string($testEmail) . "'");

$postData3 = [
    'name'            => 'Practical Nine Demo',
    'email'           => $testEmail,
    'password'        => 'MySecurePass#2026',
    'confirmPassword' => 'MySecurePass#2026',
    'mobile'          => '9876543210',
    'course'          => 'B.Tech Computer Engineering',
    'year'            => '2nd Year',
    'gender'          => 'Male',
    'terms'           => 'on'
];
$resp3 = postToRegister($postData3);

// Verify record in database
$checkStmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
$checkStmt->bind_param("s", $testEmail);
$checkStmt->execute();
$regUser = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

$hasSuccessMsg = (strpos($resp3, "Registration successful") !== false);
$insertedInDb  = ($regUser !== null && $regUser['email'] === $testEmail);
$passed3       = $hasSuccessMsg && $insertedInDb;

$testResults[] = [
    'name'     => 'Valid Registration',
    'input'    => 'Name = "Practical Nine Demo", Email = "' . $testEmail . '", Password = "MySecurePass#2026"',
    'expected' => 'Displays success banner "Registration successful! Your account has been created." and inserts record into users table.',
    'actual'   => ($hasSuccessMsg ? 'Success banner displayed. ' : 'Success banner missing. ') .
                  ($insertedInDb ? 'User record created in database with ID #' . $regUser['id'] . '.' : 'Record not found in database.'),
    'status'   => $passed3 ? 'PASSED' : 'FAILED'
];

// -------------------------------------------------------------------------
// TEST CASE 4: Duplicate Email Rejection
// -------------------------------------------------------------------------
$beforeCountStmt = $conn->query("SELECT COUNT(*) AS total FROM users");
$beforeDupCount = (int)$beforeCountStmt->fetch_assoc()['total'];

// Attempt registering with the same email again
$postData4 = [
    'name'            => 'Duplicate User Attempt',
    'email'           => $testEmail,
    'password'        => 'AnotherPassword#999',
    'confirmPassword' => 'AnotherPassword#999',
    'mobile'          => '9123456789',
    'course'          => 'B.Tech Information Technology',
    'year'            => '3rd Year',
    'gender'          => 'Female',
    'terms'           => 'on'
];
$resp4 = postToRegister($postData4);

$afterCountStmt = $conn->query("SELECT COUNT(*) AS total FROM users");
$afterDupCount = (int)$afterCountStmt->fetch_assoc()['total'];

$hasDuplicateErr = (strpos($resp4, "This email is already registered") !== false);
$dupBlocked      = ($beforeDupCount === $afterDupCount);
$passed4         = $hasDuplicateErr && $dupBlocked;

$testResults[] = [
    'name'     => 'Duplicate Email Rejection',
    'input'    => 'Email = "' . $testEmail . '" (Already exists in users table)',
    'expected' => 'Registration rejected with "This email is already registered...". No new row inserted.',
    'actual'   => ($hasDuplicateErr ? 'Duplicate email warning displayed. ' : 'Duplicate warning missing. ') .
                  ($dupBlocked ? 'Users count unchanged (' . $afterDupCount . ').' : 'Duplicate row was inserted!'),
    'status'   => $passed4 ? 'PASSED' : 'FAILED'
];

// -------------------------------------------------------------------------
// TEST CASE 5: Confirm Password Stored as Secure Hash (Never Plain-text)
// -------------------------------------------------------------------------
$hashStmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
$hashStmt->bind_param("s", $testEmail);
$hashStmt->execute();
$hashRow = $hashStmt->get_result()->fetch_assoc();
$hashStmt->close();

$storedHash = $hashRow['password'] ?? '';
$plainPassword = 'MySecurePass#2026';

$isNotPlaintext  = ($storedHash !== $plainPassword && strlen($storedHash) > 0);
$isBcryptHash    = (strpos($storedHash, '$2y$') === 0 || strpos($storedHash, '$2a$') === 0) && (strlen($storedHash) === 60);
$isVerifyValid   = password_verify($plainPassword, $storedHash);
$passed5         = $isNotPlaintext && $isBcryptHash && $isVerifyValid;

$testResults[] = [
    'name'     => 'Password Hashed Securely',
    'input'    => 'Plain Password = "MySecurePass#2026"',
    'expected' => 'Stored password is NOT plain text; must be a valid 60-character bcrypt hash ($2y$...) verifiable via password_verify().',
    'actual'   => ($isNotPlaintext ? 'Password is NOT plain text. ' : 'FAILED: Stored as plain text! ') .
                  ($isBcryptHash ? 'Verified 60-char bcrypt format (' . substr($storedHash, 0, 15) . '...). ' : 'Invalid hash structure. ') .
                  ($isVerifyValid ? 'password_verify() returned TRUE.' : 'password_verify() returned FALSE.'),
    'status'   => $passed5 ? 'PASSED' : 'FAILED'
];

// -------------------------------------------------------------------------
// CLI OUTPUT
// -------------------------------------------------------------------------
if (php_sapi_name() === 'cli') {
    echo "=======================================================================\n";
    echo "  CHARUSAT STUDENTHUB - PRACTICAL 9 AUTOMATED TEST REPORT\n";
    echo "  Target: MySQL Server 9.0 (studenthub.users) | Driver: MySQLi\n";
    echo "=======================================================================\n\n";

    $allPassed = true;
    foreach ($testResults as $index => $res) {
        $num = $index + 1;
        echo "[$num] TEST: {$res['name']} -> [{$res['status']}]\n";
        echo "    Input:    {$res['input']}\n";
        echo "    Expected: {$res['expected']}\n";
        echo "    Actual:   {$res['actual']}\n\n";
        if ($res['status'] !== 'PASSED') {
            $allPassed = false;
        }
    }

    echo "=======================================================================\n";
    echo $allPassed ? "  SUMMARY: ALL 5 PRACTICAL 9 TESTS PASSED SUCCESSFULLY! ✅\n" : "  SUMMARY: SOME TESTS FAILED! ❌\n";
    echo "=======================================================================\n";
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 9 - Test Verification Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .test-container {
            max-width: 900px;
            margin: 40px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .test-header {
            border-bottom: 2px solid #003366;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .test-header h1 {
            color: #003366;
            margin: 0 0 8px 0;
            font-size: 24px;
        }
        .test-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .test-table th, .test-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }
        .test-table th {
            background-color: #003366;
            color: white;
        }
        .badge-pass {
            background-color: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        .badge-fail {
            background-color: #dc3545;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        .test-nav {
            margin-top: 25px;
            text-align: center;
        }
        .test-nav a {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 18px;
            background: #003366;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
        }
        .test-nav a:hover {
            background: #002244;
        }
    </style>
</head>
<body class="login-page">
    <div class="test-container">
        <div class="test-header">
            <h1>CHARUSAT StudentHub - Practical 9 Verification Report</h1>
            <p style="margin: 0; color: #555;">Topic: Secure User Registration with MySQLi Prepared Statements & Password Hashing</p>
        </div>

        <table class="test-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 25%;">Test Case</th>
                    <th style="width: 30%;">Expected Result</th>
                    <th style="width: 30%;">Actual Result</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($testResults as $idx => $t): ?>
                    <tr>
                        <td><strong><?php echo $idx + 1; ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($t['name']); ?></strong><br><small style="color: #666;"><?php echo htmlspecialchars($t['input']); ?></small></td>
                        <td><?php echo htmlspecialchars($t['expected']); ?></td>
                        <td><?php echo htmlspecialchars($t['actual']); ?></td>
                        <td>
                            <span class="<?php echo $t['status'] === 'PASSED' ? 'badge-pass' : 'badge-fail'; ?>">
                                <?php echo htmlspecialchars($t['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="test-nav">
            <a href="register.php">Go to Registration Page</a>
            <a href="loginpage.html">Go to Login Page</a>
        </div>
    </div>
</body>
</html>
