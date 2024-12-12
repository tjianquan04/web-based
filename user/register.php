<?php
require '../_base.php';
// ----------------------------------------------------------------------------

if (is_post()) {
    $name     = req('name');
    $email    = req('email');
    $password = req('password');
    $confirmPassword = req('confirm_password');

    // Validate Email
    if (empty($email)) {
        $_err['email'] = 'Email is required.';
    } else if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $_err['email'] = 'Invalid email format.';
    } else if (is_exists($email,'member', 'email')){
        $_err['email'] = 'Email has already exist.';
    }

    // Validate Password
    if (empty($password)) {
        $_err['password'] = 'Password is required.';
    } else if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $_err['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one digit, one special symbol, and be at least 8 characters.';
    }

    // Validate Confirm Password
    if (empty($confirmPassword)) {
        $_err['confirm_password'] = 'Confirm password is required.';
    } else if ($password !== $confirmPassword) {
        $_err['confirm_password'] = 'Passwords do not match.';
    }


    if (empty($_err)) {
        echo "Form submitted successfully!";

        $user_id = getNextUserId();

            // Insert the user into the database
            $stmt = $_db->prepare("INSERT INTO member (member_id, name, email, contact, password, status, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $email, "-", SHA1($password), 0, 'unknown.jpg']);

            redirect('login.php');
            exit;
    }

}

// ----------------------------------------------------------------------------
$_title = 'Sign Up | Boost.do';
include '../_head.php';

?>
<link rel="stylesheet" href="../css/register.css">

<body>
    <main>
        <div class="form-section">
            <div class="form-header">
                <h1>Create an Account</h1>
            </div>
            <form method="post" class="form">
                <label for="name">Email Address</label>
                <?php html_text('name', 'e.g. henry', '', 'class="form-control"'); ?>
                <?php err('name'); ?>

                <label for="email">Email Address</label>
                <?php html_email('email', 'e.g. henry@gmail.com', '', 'class="form-control"'); ?>
                <?php err('email'); ?>

                <label for="password">Password</label>
                <?php html_password('password', 'e.g. henry123', '', 'class="form-control" maxlength="60"'); ?>
                <?php err('password'); ?>

                <label for="confirm_password">Confirm Password</label>
                <?php html_password('confirm_password', 'Re-enter your password', '', 'class="form-control" maxlength="60"'); ?>
                <?php err('confirm_password'); ?>

                <button type="submit">Create Account</button>
            </form>
            <div class="link-container">
                <span>Have an account?</span>
                <a href="/user/login.php">Log in here</a>
            </div>
        </div>
    </main>
</body>

<?php
include '../_foot.php';
