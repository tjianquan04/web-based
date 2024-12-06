<link rel="stylesheet" href="/css/flash_msg.css">
<?php
require '../_base.php';

// Initialize error message
$error = '';

// Process the form when it's submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate login details
    if (empty($admin_id) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Validate admin credentials
        $admin = validateAdmin($admin_id, $password);

        if ($admin) {
            // Login successful: store admin details in session
            $_SESSION['admin'] = $admin->admin_id; // Store admin ID
            $_SESSION['role'] = $admin->role;     // Store admin role (superadmin/admin)

            // Set a temporary flash message
            temp('info', 'Successful login');

            // Redirect to admin homepage
            redirect('admin_dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.'; // Invalid credentials
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

        <!-- Display error message if any -->
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?= $error; ?></p>
        <?php endif; ?>

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
