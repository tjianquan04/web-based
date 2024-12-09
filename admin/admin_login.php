<link rel="stylesheet" href="/css/flash_msg.css">
<?php
require '../_base.php';

// Process the form when it's submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate login details
    if (empty($admin_id) || empty($password)) {
        $_err['login_error'] = 'Please enter both username and password.';
    } else {
        // Validate admin credentials
        $admin = validateAdmin($admin_id, $password);

        if ($admin) {
            // Example: Check if admin's account is active (assuming status is 'Active')
            if ($admin->status !== 'Active') {
                $_err['status_error'] = 'Your account is inactive. Please contact support.';
            } else {
                // Admin is valid and active, perform login
                temp('info', 'Login successfully!');
                login($admin, 'admin_dashboard.php');
                exit(); // Exit after successful login
            }
        } else {
            $_err['login_error'] = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>


<body>

    <div class="login-container">
        <h1>Admin Login</h1>

        <!-- Display login error message if exists -->
        <?php err('login_error'); ?>

        <!-- Display status error message if exists -->
        <?php err('status_error'); ?>


        <!-- Login Form -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="admin_id">Admin ID</label>
                <input type="text" id="admin_id" name="admin_id" placeholder="Enter your admin ID" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <div>
                <button type="submit">Login</button>
            </div>
        </form>
    </div>

</body>

</html>
<?php
