<link rel="stylesheet" href="/css/flash_msg.css">
<link rel="stylesheet" href="/css/login.css">
<?php
require '../_base.php';


//start the session

$_title = 'Login | Boost.do';

include '../_head.php';

//login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_err['empty_error'] = 'Please enter both email and password.';
    } else {
        $user = validateUser($email, $password);
        if ($user) {
            // Set session data if login is successful
            temp('info', 'Successful login'); 
            login($user, '../index.php'); // Store the username or other relevant data
              // Redirect to the homepage or a protected page
        } else {
            $_err['login_error'] = 'Invalid email or password.';  // If credentials are invalid
        }
    }
}

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
        <div class="error-message"><?php err('login_error'); ?></div>
        <div class="error-message"><?php err('empty_error'); ?></div>


            <form action="" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="options">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="forgot_password.php">Forgot password?</a>
                </div>
                <button type="submit" class="btn-signin">Sign in</button>
            </form>
            <p class="signup-text">Don't have an account? <a href="signup.php">Sign up for free!</a></p>
        </div>

        <!-- Right Section: Image -->
        <div class="image-container">
            <img src="../image/badminton_shop.png" alt="shop" class="login-image">
        </div>
    </div>
</body>

    

<?php
include '../_foot.php';
