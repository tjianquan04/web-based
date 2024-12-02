<?php
require '../_base.php';

//start the session
session_start();

$_title = 'Login | Boost.do';

include '../_head.php';

//login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } 
    else{
        $user = validateUser($email, $password);

        if ($user) {
            // Set session data if login is successful
            $_SESSION['user'] = $user->username;  // Store the username or other relevant data
            redirect('../index.php');  // Redirect to the homepage or a protected page
        } else {
            $error = 'Invalid email or password.';  // If credentials are invalid
        }
    }
}
?>

<!-- Login Form -->
<div class="login-container">
    <h1>Login</h1>
    
    <!-- Display error if there's any -->
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= $error; ?></p>
    <?php endif; ?>
    
    <!-- Login form -->
    <form action="" method="POST">
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <button type="submit">Login</button>
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </form>
</div>



<?php
include '../_foot.php';
