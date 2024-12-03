<?php
require '_base.php';
// ----------------------------------------------------------------------------

if (is_post()) {
    // Input
    //$id       = trim($_POST['id'] ?? '');
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $contact  = trim($_POST['contact'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate Name
    if (empty($name)) {
        $_err['name'] = 'Name is required.';
    } else if (strlen($name) > 100) {
        $_err['name'] = 'Maximum 100 characters.';
    }

    if (empty($email)) {
        $_err['email'] = 'Email is required.';
    } else if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $_err['email'] = 'Invalid email format.';
    }

    // Validate Contact Number
    if (empty($_POST['contact'])) {
        $_err['contact'] = 'Contact number is required.';
    } else if (!preg_match('/^\d{10,15}$/', $_POST['contact'])) {
        $_err['contact'] = 'Contact number must be 10-15 digits.';
    }

    // Validate Password
    if (empty($password)) {
        $_err['password'] = 'Password is required.';
    } else if (strlen($password) < 8) {
        $_err['password'] = 'Password must be at least 8 characters.';
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

<div class="form-section">
    <div class="form-header">
        <h1>Create an Account</h1>
    </div>
    <div class="link-container">
        <span>Already have an account?</span>
        <a href="login.php">Log in here</a>
    </div>
    <form method="post" class="form">
        <label for="name">Name</label>
        <?php html_text('name', 'e.g. Henry', $_POST, 'class="form-control" maxlength="100"'); ?>
        <?php err('name'); ?>

        <label for="email">Email Address</label>
        <?php html_email('email', 'e.g. henry@gmail.com', $_POST, 'class="form-control"'); ?>
        <?php err('email'); ?>

        <label for="contact">Mobile Number</label>
        <?php html_text('contact', 'e.g. 60123456789', $_POST, 'class="form-control" maxlength="15"'); ?>
        <?php err('contact'); ?>

        <label for="password">Password</label>
        <?php html_password('password', 'e.g. henry123', $_POST, 'class="form-control" maxlength="60"'); ?>
        <?php err('password'); ?>

        <button type="submit">Create Account</button>
        <button type="reset">Reset</button>
    </form>
</div>

<script>
    //Register member password field
    function togglePasswordVisibility() {
        var passwordField = document.getElementById('password');
        var showPasswordCheckbox = document.getElementById('show-password');
        // Change between password and text input types
        if (showPasswordCheckbox.checked) {
            passwordField.type = 'text';
        } else {
            passwordField.type = 'password';
        }
    }
</script>

<?php
include '_foot.php';
