<?php
/* =========================================================================
   CHARUSAT STUDENTHUB - STUDENT REGISTRATION (register.php)
   Practical 9: Secure User Registration using PHP & MySQLi Prepared Statements
   Topic: Form Handling, Validation, Password Hashing & MySQLi Prepared INSERT
   ========================================================================= */

// Include MySQLi database connection
require_once "mysqli_connection.php";

// Initialize variables for form data and error messages
$name            = "";
$email           = "";
$mobile          = "";
$password        = "";
$confirmPassword = "";
$course          = "";
$year            = "";
$gender          = "";
$termsChecked    = false;

$errors     = array();
$successMsg = "";

// Helper function to sanitize input safely
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

// 1. CHECK IF FORM IS SUBMITTED VIA POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // A. SANITIZE SUBMITTED VALUES
    $name            = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $email           = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $mobile          = isset($_POST['mobile']) ? sanitizeInput($_POST['mobile']) : '';
    $password        = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
    $course          = isset($_POST['course']) ? sanitizeInput($_POST['course']) : '';
    $year            = isset($_POST['year']) ? sanitizeInput($_POST['year']) : '';
    $gender          = isset($_POST['gender']) ? sanitizeInput($_POST['gender']) : '';
    $termsChecked    = isset($_POST['terms']);

    // B. SERVER-SIDE VALIDATION

    // 1. Validate Full Name (Required, alphabets and spaces only)
    if (empty($name)) {
        $errors['name'] = "Please enter your full name.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors['name'] = "Name must contain letters and spaces only.";
    }

    // 2. Validate Email Address (Required, standard email format)
    if (empty($email)) {
        $errors['email'] = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address (e.g. name@domain.com).";
    }

    // 3. Validate Password (Required, minimum 8 characters)
    if (empty($password)) {
        $errors['password'] = "Please enter a password.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters long.";
    }

    // 4. Validate Confirm Password (Required, must match password)
    if (empty($confirmPassword)) {
        $errors['confirmPassword'] = "Please confirm your password.";
    } elseif ($confirmPassword !== $password) {
        $errors['confirmPassword'] = "Passwords do not match.";
    }

    // 5. Validate Mobile Number (if submitted via form)
    if (isset($_POST['mobile'])) {
        if (empty($mobile)) {
            $errors['mobile'] = "Please enter your mobile number.";
        } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
            $errors['mobile'] = "Mobile number must be exactly 10 digits.";
        }
    }

    // 6. Validate Course Selection (if submitted via form)
    if (isset($_POST['course']) && empty($course)) {
        $errors['course'] = "Please select a course.";
    }

    // 7. Validate Year Selection (if submitted via form)
    if (isset($_POST['year']) && empty($year)) {
        $errors['year'] = "Please select your year of study.";
    }

    // 8. Validate Gender (if submitted via form)
    if (isset($_POST['gender']) && empty($gender)) {
        $errors['gender'] = "Please select your gender.";
    }

    // 9. Validate Terms and Conditions (if submitted via form)
    if ((isset($_POST['course']) || isset($_POST['mobile'])) && !$termsChecked) {
        $errors['terms'] = "You must accept the Terms and Conditions.";
    }

    // C. CHECK FOR DUPLICATE EMAIL USING MYSQLI PREPARED STATEMENT
    if (empty($errors['email'])) {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($checkStmt) {
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $errors['email'] = "This email is already registered. Please login or use a different email.";
            }
            $checkStmt->close();
        } else {
            error_log("Database prepare error during duplicate email check: " . $conn->error);
            $errors['general'] = "A server error occurred. Please try again later.";
        }
    }

    // D. SECURE REGISTRATION INSERT USING MYSQLI PREPARED STATEMENT
    if (empty($errors)) {
        // Securely hash password using PHP's standard password_hash (bcrypt)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare INSERT query with parameter placeholders
        $insertStmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");

        if ($insertStmt) {
            // Bind parameters: 's' = string (name), 's' = string (email), 's' = string (password hash)
            $insertStmt->bind_param("sss", $name, $email, $hashedPassword);

            if ($insertStmt->execute()) {
                $successMsg = "Registration successful! Your account has been created.";

                // Reset form fields after successful registration
                $name            = "";
                $email           = "";
                $mobile          = "";
                $password        = "";
                $confirmPassword = "";
                $course          = "";
                $year            = "";
                $gender          = "";
                $termsChecked    = false;
            } else {
                // If a duplicate email somehow slipped through (MySQL Error 1062)
                if ($conn->errno === 1062) {
                    $errors['email'] = "This email is already registered. Please login or use a different email.";
                } else {
                    error_log("Database insert error: " . $insertStmt->error);
                    $errors['general'] = "Registration failed due to a database error. Please try again.";
                }
            }
            $insertStmt->close();
        } else {
            error_log("Database prepare error during user insert: " . $conn->error);
            $errors['general'] = "Registration failed due to a server error. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - CHARUSAT Portal</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>

<body class="login-page register-page">

    <main class="loginbox registerbox">
        <h1>CHARUSAT STUDENT PORTAL</h1>
        <h2>Student Registration</h2>
        <img src="charusat.jpg" class="background" alt="CHARUSAT Campus">

        <!-- SUCCESS MESSAGE BANNER -->
        <?php if (!empty($successMsg)): ?>
            <div id="regSuccessMsg" class="success-message" style="display: block;">
                ✅ <?php echo htmlspecialchars($successMsg); ?>
                <br>
                <a href="loginpage.html" class="login-link">Click here to Login</a>
            </div>
        <?php endif; ?>

        <!-- DUPLICATE EMAIL / GENERAL SERVER ERROR BANNER -->
        <?php if (!empty($errors['general'])): ?>
            <div class="portal-status-msg error-msg" style="display: block; margin-bottom: 15px;">
                ⚠️ <?php echo htmlspecialchars($errors['general']); ?>
            </div>
        <?php elseif (!empty($errors['email']) && strpos($errors['email'], 'already registered') !== false): ?>
            <div class="portal-status-msg error-msg" style="display: block; margin-bottom: 15px;">
                ⚠️ <?php echo htmlspecialchars($errors['email']); ?>
            </div>
        <?php endif; ?>

        <form id="registrationForm" action="register.php" method="POST" novalidate>

            <!-- 1. FULL NAME -->
            <div class="form-group">
                <label for="regName" class="form-label">Full Name:</label>
                <input type="text" id="regName" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter Full Name" required class="<?php echo isset($errors['name']) ? 'invalid-input' : ''; ?>">
                <small class="error-msg" id="nameError"><?php echo isset($errors['name']) ? htmlspecialchars($errors['name']) : ''; ?></small>
            </div>

            <!-- 2. EMAIL ADDRESS -->
            <div class="form-group">
                <label for="regEmail" class="form-label">Email Address:</label>
                <input type="email" id="regEmail" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter Email Address" required class="<?php echo isset($errors['email']) ? 'invalid-input' : ''; ?>">
                <small class="error-msg" id="emailError"><?php echo isset($errors['email']) ? htmlspecialchars($errors['email']) : ''; ?></small>
            </div>

            <!-- 3. MOBILE NUMBER -->
            <div class="form-group">
                <label for="regMobile" class="form-label">Mobile Number:</label>
                <input type="tel" id="regMobile" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" placeholder="10-digit Mobile Number" maxlength="10" required class="<?php echo isset($errors['mobile']) ? 'invalid-input' : ''; ?>">
                <small class="error-msg" id="mobileError"><?php echo isset($errors['mobile']) ? htmlspecialchars($errors['mobile']) : ''; ?></small>
            </div>

            <!-- 4. PASSWORD -->
            <div class="form-group">
                <label for="regPassword" class="form-label">Password:</label>
                <input type="password" id="regPassword" name="password" placeholder="Enter Password" required class="<?php echo isset($errors['password']) ? 'invalid-input' : ''; ?>">
                <div id="strengthMeter" class="strength-meter">Strength: <span id="strengthText">-</span></div>
                <small class="error-msg" id="passwordError"><?php echo isset($errors['password']) ? htmlspecialchars($errors['password']) : ''; ?></small>
            </div>

            <!-- 5. CONFIRM PASSWORD -->
            <div class="form-group">
                <label for="regConfirmPassword" class="form-label">Confirm Password:</label>
                <input type="password" id="regConfirmPassword" name="confirmPassword" placeholder="Confirm Password" required class="<?php echo isset($errors['confirmPassword']) ? 'invalid-input' : ''; ?>">
                <small class="error-msg" id="confirmPasswordError"><?php echo isset($errors['confirmPassword']) ? htmlspecialchars($errors['confirmPassword']) : ''; ?></small>
            </div>

            <!-- 6. COURSE -->
            <div class="form-group">
                <label for="regCourse" class="form-label">Course:</label>
                <select id="regCourse" name="course" required class="<?php echo isset($errors['course']) ? 'invalid-input' : ''; ?>">
                    <option value="">-- Select Course --</option>
                    <option value="B.Tech Computer Engineering" <?php echo ($course === 'B.Tech Computer Engineering') ? 'selected' : ''; ?>>B.Tech Computer Engineering</option>
                    <option value="B.Tech Information Technology" <?php echo ($course === 'B.Tech Information Technology') ? 'selected' : ''; ?>>B.Tech Information Technology</option>
                    <option value="B.Tech Computer Science" <?php echo ($course === 'B.Tech Computer Science') ? 'selected' : ''; ?>>B.Tech Computer Science</option>
                    <option value="BCA" <?php echo ($course === 'BCA') ? 'selected' : ''; ?>>BCA</option>
                    <option value="MCA" <?php echo ($course === 'MCA') ? 'selected' : ''; ?>>MCA</option>
                </select>
                <small class="error-msg" id="courseError"><?php echo isset($errors['course']) ? htmlspecialchars($errors['course']) : ''; ?></small>
            </div>

            <!-- 7. YEAR -->
            <div class="form-group">
                <label for="regYear" class="form-label">Year of Study:</label>
                <select id="regYear" name="year" required class="<?php echo isset($errors['year']) ? 'invalid-input' : ''; ?>">
                    <option value="">-- Select Year --</option>
                    <option value="1st Year" <?php echo ($year === '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                    <option value="2nd Year" <?php echo ($year === '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                    <option value="3rd Year" <?php echo ($year === '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                    <option value="4th Year" <?php echo ($year === '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                </select>
                <small class="error-msg" id="yearError"><?php echo isset($errors['year']) ? htmlspecialchars($errors['year']) : ''; ?></small>
            </div>

            <!-- 8. GENDER -->
            <div class="form-group">
                <label class="form-label">Gender:</label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="Male" id="genderMale" <?php echo ($gender === 'Male') ? 'checked' : ''; ?>> Male</label>
                    <label><input type="radio" name="gender" value="Female" id="genderFemale" <?php echo ($gender === 'Female') ? 'checked' : ''; ?>> Female</label>
                    <label><input type="radio" name="gender" value="Other" id="genderOther" <?php echo ($gender === 'Other') ? 'checked' : ''; ?>> Other</label>
                </div>
                <small class="error-msg" id="genderError"><?php echo isset($errors['gender']) ? htmlspecialchars($errors['gender']) : ''; ?></small>
            </div>

            <!-- 9. TERMS AND CONDITIONS -->
            <div class="form-group checkbox-group">
                <label for="regTerms">
                    <input type="checkbox" id="regTerms" name="terms" <?php echo $termsChecked ? 'checked' : ''; ?>> I accept the <a href="#" onclick="alert('Terms & Conditions: Follow all university regulations.'); return false;">Terms and Conditions</a>
                </label>
                <small class="error-msg" id="termsError"><?php echo isset($errors['terms']) ? htmlspecialchars($errors['terms']) : ''; ?></small>
            </div>

            <!-- FORM ACTIONS -->
            <div class="form-actions">
                <input type="submit" id="regSubmitBtn" value="Register">
                <input type="reset" id="regResetBtn" value="Clear">
            </div>

            <p style="margin-top: 15px; text-align: center;">Already have an account? <a href="loginpage.html">Login Here</a></p>

        </form>

    </main>

    <!-- Dark/Light Theme Emoji Button in Bottom-Right Corner -->
    <span id="theme-toggle" class="theme-btn login-theme-btn" title="Toggle Theme">🌙</span>

</body>

</html>
