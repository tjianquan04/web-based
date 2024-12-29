<link rel="stylesheet" href="/css/flash_msg.css">

<?php
require '../_base.php';

// Set the maximum login attempts
define('MAX_LOGIN_ATTEMPTS', 3);

// Process the form when it's submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'] ?? '';
    $password = $_POST['password'] ?? '';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? ''; // Get reCAPTCHA response

    // Validate reCAPTCHA
    $recaptchaSecret = '6LeIlpsqAAAAACwTfwZw9rzsNDiO1uSbGQSFa7Xq'; // Replace with your Google reCAPTCHA secret key
    $recaptchaVerifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    $response = file_get_contents($recaptchaVerifyUrl . "?secret=$recaptchaSecret&response=$recaptchaResponse");
    $recaptchaData = json_decode($response, true);

    if (!$recaptchaData['success']) {
        $_err['recaptcha_error'] = 'Please verify you are not a robot.';
    } else {
        // Validate login details
        if (empty($admin_id) || empty($password)) {
            $_err['login_error'] = 'Please enter both username and password.';
        } else {
            // Check admin credentials
            $admin = validateAdmin($admin_id, $password);

            if ($admin) {
                // Check if admin's account is active
                if ($admin->status !== 'Active') {
                    $_err['status_error'] = 'Your account is inactive. Please contact support.';
                } else {
                    // Reset login attempts on successful login
                    $resetQuery = "UPDATE admin SET login_attempts = 0 WHERE admin_id = ?";
                    $_db->prepare($resetQuery)->execute([$admin_id]);

                    // Handle "Remember Me" functionality
                    if (isset($_POST['remember'])) {
                        setcookie('remember_admin_id', $admin_id, time() + (30 * 24 * 60 * 60), "/"); // Save for 30 days
                        setcookie('remember_password', $password, time() + (30 * 24 * 60 * 60), "/");
                    } else {
                        // Clear cookies if "Remember Me" is not checked
                        setcookie('remember_admin_id', '', time() - 3600, "/");
                        setcookie('remember_password', '', time() - 3600, "/");
                    }

                    // Admin is valid and active, perform login
                    temp('info', 'Login Successful');
                    login($admin, 'admin_dashboard.php');
                    exit();
                }
            } else {
                // Fetch admin details to check login attempts
                $query = "SELECT login_attempts FROM admin WHERE admin_id = ?";
                $stmt = $_db->prepare($query);
                $stmt->execute([$admin_id]);
                $adminData = $stmt->fetch(PDO::FETCH_OBJ);

                if ($adminData) {
                    $newAttempts = $adminData->login_attempts + 1;

                    // Increment login attempts
                    $updateAttemptsQuery = "UPDATE admin SET login_attempts = ? WHERE admin_id = ?";
                    $_db->prepare($updateAttemptsQuery)->execute([$newAttempts, $admin_id]);

                    if ($newAttempts >= MAX_LOGIN_ATTEMPTS) {
                        // Deactivate account after exceeding attempts
                        $deactivateQuery = "UPDATE admin SET status = 'Inactive' WHERE admin_id = ?";
                        $_db->prepare($deactivateQuery)->execute([$admin_id]);
                        $_err['login_error'] = 'Your account has been deactivated due to multiple failed login attempts. Please contact support.';
                    } else {
                        $_err['login_error'] = 'Invalid username or password. ';
                    }
                } else {
                    $_err['login_error'] = 'Invalid username or password.';
                }
            }
        }
    }
}

// Prepopulate login form if "Remember Me" cookies exist
$rememberedAdminId = $_COOKIE['remember_admin_id'] ?? '';
$rememberedPassword = $_COOKIE['remember_password'] ?? '';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="/css/admin_login.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script> <!-- Include Google reCAPTCHA API -->
</head>


<body>
    <div class="login-container">
        <!-- Left Section: Form -->
        <div class="form-container">
            <h1>Admin Login</h1>

            <!-- Error Messages -->
            <div class="error-messages">
                <?php if ($_err) : ?>
                    <?= err('login_error') ?>
                    <?= err('status_error') ?>
                    <?= err('recaptcha_error') ?>
                <?php endif; ?>
            </div>

            <!-- Login Form -->
            <form action="" method="POST">
                <div class="form-group">
                    <label for="admin_id">Admin ID</label>
                    <input type="text" id="admin_id" name="admin_id" placeholder="Enter your admin ID" value="<?= htmlspecialchars($rememberedAdminId) ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" value="<?= htmlspecialchars($rememberedPassword) ?>" required>
                </div>
                <div class="options">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="/admin/admin_forgot_password.php">Forgot password?</a>
                </div>

                <!-- Google reCAPTCHA Widget -->
                <div class="captcha-container">
                    <div class="g-recaptcha" data-sitekey="6LeIlpsqAAAAAOsISeaetWiRvuURavKCacJ5syAD"></div> <!-- Replace with your site key -->
                </div>
                <div>
                    <button type="submit">Login</button>
                </div>
            </form>
        </div>

        <!-- Right Section: Image -->
        <div class="image-container">
            <img src="../image/security.png" alt="Security Image">
        </div>
    </div>
</body>

</html>