<?php
require '../_base.php';

// Initialize variables for form data
$username = $email = $password = $phone_number = '';
$error = '';

$_title = 'Sign Up | Boost.do';

include '../_head.php'; // Include the header

// Process signup form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';

    // Validate the input
    if (empty($username) || empty($email) || empty($password) || empty($phone_number)) {
        $error = 'Please fill all required fields.';
    } else {
        // Check if the email already exists in the database
        $stmt = $_db->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $error = 'Email already exists.';
        } else {
            $user_id = getNextUserId();
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert the user into the database
            $stmt = $_db->prepare("INSERT INTO user (user_id, user_name, email, password, phone_number) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $username, $email, $hashedPassword, $phone_number]);

            // Redirect to login page after successful signup
            redirect('login.php');
            exit;
        }
    }
}
?>

<!-- Signup Form -->
<div class="signup-container">
    <h1>Sign Up</h1>

    <!-- Display error if there's any -->
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= $error; ?></p>
    <?php endif; ?>

    <!-- Signup form -->
    <form action="" method="POST">
        <div>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <label for="phone_number">Phone number</label>
            <input type="text" id="phone_number" name="phone_number" required>
        </div>
        <div>
            <button type="submit">Sign Up</button>
        </div>
    </form>
</div>

<?php
include '../_foot.php'; // Include the footer
?>
