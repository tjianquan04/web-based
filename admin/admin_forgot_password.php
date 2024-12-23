<?php
require '../_base.php'; // Include your database connection and helper functions

// Initialize error array
$_err = [];

// Cooldown time in seconds
$cooldownTime = 5 * 60; // 5 minutes

$lastRequestTime = $_SESSION['last_request_time'] ?? null;
$currentTime = time();
$remainingTime = 0;

if ($lastRequestTime) {
    // Check if the cooldown is still active
    $remainingTime = max(0, $cooldownTime - ($currentTime - $lastRequestTime));
    if ($remainingTime === 0) {
        // Reset the session cooldown if expired
        unset($_SESSION['last_request_time']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $remainingTime === 0) {
    $email = $_POST['email'] ?? ''; // Get the email input from the POST request

    if (empty($email)) {
        // Email input is empty, show an error
        $_err['empty_error'] = "Please enter your email address.";
    } else {
        // Call the validAdminEmail function to check if the email exists
        $admin = validAdminEmail($email);
        if ($admin) {
            // Proceed to send the email
            try {
                // Step 1: Generate a unique token
                $token = sha1(uniqid() . rand());

                // Step 2a: Delete old tokens for this admin
                $stmtDelete = $_db->prepare('DELETE FROM `admin_token` WHERE admin_id = ?');
                $stmtDelete->execute([$admin->admin_id]);

                // Step 2b: Insert a new token
                $stmtInsert = $_db->prepare('INSERT INTO `admin_token` (id, expire, admin_id) VALUES (?, ADDTIME(NOW(), "00:10"), ?)');
                $stmtInsert->execute([$token, $admin->admin_id]);

                // Step 3: Generate the reset URL
                $resetUrl = base("/admin/admin_token.php?id=$token");

                // Step 4: Configure and send the email
                $mail = get_mail();
                $mail->addAddress($admin->email, $admin->admin_name);
                $mail->isHTML(true);
                $mail->Subject = 'Reset Password';
                $mail->Body = "
                    <h1>Reset Your Password</h1>
                    <p>Click the link below to reset your password:</p>
                    <p><a href='$resetUrl'>$resetUrl</a></p>
                    <p>This link will expire in 10 minutes.</p>
                    <p>From, 😺 Admin</p>
                ";

                $mail->send();

                // Step 5: Set the last request time and provide feedback
                $_SESSION['last_request_time'] = $currentTime;
                temp('Email', "Email successfully sent!");
                temp('showSwal', true); // Set flag to show SweetAlert
            } catch (Exception $e) {
                // Handle any errors in the email-sending process
                $_err['email_error'] = "Failed to send reset email. Please try again later.";
            }
        } else {
            // Email does not exist in the database, show an error
            $_err['email_error'] = "Email address entered does not exist.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/css/forgot_password.css">
</head>

<body>
    <div class="forgot-password-container">
        <div class="form-container">
            <button class="back-button" onclick="history.back()">&larr;</button>
            <h1>Reset your password</h1>
            <p>We will send you an email to reset your password</p>

            <div class="error-message"><?php err('empty_error'); ?></div>
            <div class="error-message"><?php err('email_error'); ?></div>
            <div class="error-message"><?php err('cooldown_error'); ?></div>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div>
                    <button type="submit" id="emailButton" class="email-button">Email me</button>
                </div>
            </form>
        </div>

        <div class="image-container">
            <img src="/image/forgot-password.png" alt="Reset Password Image">
        </div>
    </div>

    <?php if (temp('showSwal')): ?>
        <script>
            swal("Congrats", "<?= temp('Email'); ?>", "success")
                .then(function() {
                    window.location.href = 'admin_login.php';
                });
        </script>
    <?php endif; ?>

    <?php if ($remainingTime > 0): ?>
        <script>
            const cooldownButton = document.getElementById('emailButton');
            cooldownButton.disabled = true;

            let cooldownTime = <?= $remainingTime ?>;

            function updateCooldown() {
                if (cooldownTime > 0) {
                    const minutes = Math.floor(cooldownTime / 60);
                    const seconds = cooldownTime % 60;
                    cooldownButton.textContent = `Cooldown: ${minutes}m ${seconds}s`;
                    cooldownTime--;
                } else {
                    cooldownButton.disabled = false;
                    cooldownButton.textContent = "Email me";
                }
            }

            updateCooldown(); // Initial call to set the button state
            setInterval(updateCooldown, 1000);
        </script>
    <?php endif; ?>
</body>

</html>