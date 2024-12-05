<?php
require '_base.php';
// ----------------------------------------------------------------------------

if (is_post()) {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    // Validate Email
    if (empty($email)) {
        $_err['email'] = 'Email is required.';
    } else if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $_err['email'] = 'Invalid email format.';
    }

    // Validate Password
    if (empty($password)) {
        $_err['password'] = 'Password is required.';
    } else if (strlen($password) < 8) {
        $_err['password'] = 'Password must be at least 8 characters.';
    }

    // Validate Confirm Password
    if (empty($confirmPassword)) {
        $_err['confirm_password'] = 'Confirm password is required.';
    } else if ($password !== $confirmPassword) {
        $_err['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($_err)) {
        echo "Form submitted successfully!";
    }

    // Output
    // if (!$_err) {
    //     $stm = $_db->prepare('INSERT INTO student
    //     (id, name, gender, program_id)
    //     VALUES(?, ?, ?, ?)');
    //     $stm->execute([$id, $name, $gender, $program_id]);
    // }
}

// ----------------------------------------------------------------------------
include '_head.php';

?>
<link rel="stylesheet" href="/css/register.css">

<body>
    <main>
        <div class="form-section">
            <div class="form-header">
                <h1>Create an Account</h1>
            </div>
            <div class="link-container">
                <span>Have an account?</span>
                <a href="/user/login.php">Log in here</a>
            </div>
            <form method="post" class="form">
                <label for="email">Email Address</label>
                <?php html_email('email', 'e.g. henry@gmail.com', $_POST, 'class="form-control"'); ?>
                <?php err('email'); ?>

                <label for="password">Password</label>
                <?php html_password('password', 'e.g. henry123', $_POST, 'class="form-control" maxlength="60"'); ?>
                <?php err('password'); ?>

                <label for="confirm_password">Confirm Password</label>
                <?php html_password('confirm_password', 'Re-enter your password', $_POST, 'class="form-control" maxlength="60"'); ?>
                <?php err('confirm_password'); ?>

                <button type="submit">Create Account</button>
            </form>
        </div>
    </main>
</body>

<?php
include '_foot.php';
