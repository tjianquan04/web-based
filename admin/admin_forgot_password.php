<?php
require '../_base.php'; // Include your database connection and helper functions

$_err = []; // Initialize error array
$cooldownTime = 10 * 60; // Cooldown time in seconds (10 minutes)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? ''; // Get the email input

    if (empty($email)) {
        $_err['empty_error'] = "Please enter your email address.";
    } else {
        $stmt = $_db->prepare('SELECT admin_id, email, admin_name, last_email_sent FROM admin WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_OBJ);

        if ($admin) {
            $currentTime = time();
            $lastEmailSentTime = $admin->last_email_sent ? strtotime($admin->last_email_sent) : 0;

            if ($currentTime - $lastEmailSentTime < $cooldownTime) {
                // Cooldown still active
                $remainingTime = $cooldownTime - ($currentTime - $lastEmailSentTime);
                $_err['cooldown_error'] = "You can request a reset email for this address again in " . gmdate("i:s", $remainingTime) . ".";
            } else {
                try {
                    // Step 1: Generate a unique token
                    $token = sha1(uniqid() . rand());

                    // Step 2: Remove old tokens and insert a new one
                    $_db->prepare('DELETE FROM admin_token WHERE admin_id = ?')->execute([$admin->admin_id]);
                    $_db->prepare('INSERT INTO admin_token (id, expire, admin_id) VALUES (?, ADDTIME(NOW(), "00:10"), ?)')
                        ->execute([$token, $admin->admin_id]);

                    // Step 3: Send the reset email
                    $resetUrl = base("/admin/admin_token.php?id=$token");
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

                    // Step 4: Update the last email sent time
                    $_db->prepare('UPDATE admin SET last_email_sent = NOW() WHERE admin_id = ?')->execute([$admin->admin_id]);

                    temp('Email', "Email successfully sent!");
                    temp('showSwal', true); // Show success message
                } catch (Exception $e) {
                    $_err['email_error'] = "Failed to send reset email. Please try again later.";
                }
            }
        } else {
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
</body>

</html>
