<link rel="stylesheet" href="/css/flash_msg.css">
<link rel="stylesheet" href="/css/login.css">

<?php
require '../_base.php';

// Start the session
$_title = 'Login | Boost.do';
include '../_head.php';

// Login processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Verify reCAPTCHA response
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $recaptchaSecret = '6LeIlpsqAAAAACwTfwZw9rzsNDiO1uSbGQSFa7Xq'; // Replace with your Google reCAPTCHA secret key
    $recaptchaVerify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse");
    $recaptchaResult = json_decode($recaptchaVerify, true);

    if (!$recaptchaResult['success']) {
        $_err['captcha_error'] = 'Please confirm you are not a robot.';
    } elseif (empty($email) || empty($password)) {
        $_err['empty_error'] = 'Please enter both email and password.';
    } else {
        // Fetch user details by email
        $stmt = $_db->prepare("SELECT * FROM `member` WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$user) {
            $_err['login_error'] = 'Invalid email or password.';
        } elseif ($user->status === 'Inactive') {
            $_err['login_error'] = 'Your account has been locked due to multiple failed login attempts.';
        } elseif ($user->password === sha1($password)) {
            // Successful login, reset login attempts
            $stmt = $_db->prepare("UPDATE `member` SET login_attempts = 0 WHERE email = ?");
            $stmt->execute([$email]);

            // Handle "Remember Me" functionality
            if (isset($_POST['remember'])) {
                setcookie('remember_user_email', $email, time() + (30 * 24 * 60 * 60), "/"); // 30 days
                setcookie('remember_user_password', $password, time() + (30 * 24 * 60 * 60), "/");
            } else {
                setcookie('remember_user_email', '', time() - 3600, "/");
                setcookie('remember_user_password', '', time() - 3600, "/");
            }

            // Set session data for successful login
            temp('info', 'Successful login');
            userLogin($user, '../index.php'); // Redirect to dashboard or protected page
        } else {
            // Increment login attempts
            $new_attempts = $user->login_attempts + 1;

            // Update login attempts in the database
            $stmt = $_db->prepare("UPDATE `member` SET login_attempts = ? WHERE email = ?");
            $stmt->execute([$new_attempts, $email]);

            if ($new_attempts >= 3) {
                // Lock account if login attempts exceed threshold
                $stmt = $_db->prepare("UPDATE `member` SET status = 'Inactive' WHERE email = ?");
                $stmt->execute([$email]);
                $_err['login_error'] = 'Your account has been locked due to multiple failed login attempts.';
            } else {
                $remaining_attempts = 3 - $new_attempts;
                $_err['login_error'] = "Invalid email or password.";
            }
        }
    }
}

// Prepopulate form if "Remember Me" cookies are set
$rememberedEmail = $_COOKIE['remember_user_email'] ?? '';
$rememberedPassword = $_COOKIE['remember_user_password'] ?? '';

?>

<!-- Background image element -->
<div class="login-background"></div>

<body>
    <div class="login-container">
        <!-- Left Section: Login Form -->
        <div class="form-container">
            <h1>WELCOME BACK</h1>
            <p>Welcome back! Please enter your details.</p>

            <!-- Error Messages -->
            <div class="error-messages">
                <?php if ($_err) : ?>
                    <?= err('login_error') ?>
                    <?= err('empty_error') ?>
                    <?= err('captcha_error') ?>
                <?php endif; ?>
            </div>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" value="<?= htmlspecialchars($rememberedEmail) ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" value="<?= htmlspecialchars($rememberedPassword) ?>" required>
                </div>
                <div class="options">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="user_forgot_password.php">Forgot password?</a>
                </div>

                <!-- Google reCAPTCHA -->
                <div class="captcha-container">
                    <div class="g-recaptcha" data-sitekey="6LeIlpsqAAAAAOsISeaetWiRvuURavKCacJ5syAD"></div>
                </div>

                <button type="submit" class="btn-signin">Sign in</button>
            </form>
            <p class="signup-text">Don't have an account? <a href="register.php">Sign up for free!</a></p>
        </div>

        <!-- Right Section: Image -->
        <!-- Slideshow container -->
        <div class="slideshow-container">
            <div class="mySlides fade">
                <img src="../image/badminton_shop.png" alt="Image 1">
            </div>

            <div class="mySlides fade">
                <img src="../image/badminton_shop1.png" alt="Image 2">
            </div>

            <div class="mySlides fade">
                <img src="../image/badminton_shop2.png" alt="Image 3">
            </div>
        </div>
    </div>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script> <!-- Google reCAPTCHA Script -->
    <script src="/js/main.js"></script>
</body>

<?php include '../_foot.php'; ?>
