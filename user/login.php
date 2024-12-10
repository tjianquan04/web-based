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
        $error = 'Please enter both email and password.';
    } else {
        $user = validateUser($email, $password);
        if ($user) {
            // Set session data if login is successful
            $_SESSION['user'] = $user->username; // Store the username or other relevant data
            temp('info', 'Successful login'); 
            redirect('../index.php');
              // Redirect to the homepage or a protected page
        } else {
            $error = 'Invalid email or password.';  // If credentials are invalid
        }
    }
}

?>

<!-- Background image element -->
<div class="login-background"></div>

<main>
    <!-- Login form container -->
    <div class="login-container">
        <h1>Login</h1>

        <!-- Display error if there's any -->
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?= $error; ?></p>
        <?php endif; ?>

        <!-- Login form -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="E.g. abc@gmail.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <a href="forgot_pw.php" class="forgot-password">Forgot Password?</a>
                <input type="password" id="password" name="password" placeholder="Enter your password"required>
            </div>
            <!-- Remember me checkbox -->
            <div class="form-group">
                <label for="remember-me" class="remember-me-label">
                    <input type="checkbox" id="remember-me" name="remember-me">
                    Remember me
                </label>
            </div>
            <div>
                <button type="submit">Login</button>
                <p>Don't have an account? <a href="register.php">Sign Up</a></p>
            </div>
        </form>
    </div>

    

<?php



include '../_foot.php';
