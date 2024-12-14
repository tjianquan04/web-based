<?php
include '../_base.php';

// ----------------------------------------------------------------------------

// TODO: (1) Delete expired tokens
$_db->query('DELETE FROM user_token WHERE expire < NOW()');

$id = req('id');

// TODO: (2) Is token id valid?
if (!is_exists($id, 'user_token', 'id')) {
    temp('info', 'Invalid token. Try again');
    redirect('user_login');
}

if (is_post()) {
    $password = req('password');
    $confirm  = req('confirm');

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    } else if (strlen($password) <= 7) {
        $_err['password'] = 'Must be more than 7 characters';
    } else if (!preg_match('/[A-Z]/', $password)) {
        $_err['password'] = 'Must include at least one uppercase letter';
    } else if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $_err['password'] = 'Must include at least one special character';
    }

    // Validate: confirm
    if ($confirm == '') {
        $_err['confirm'] = 'Required';
    } else if ($confirm != $password) {
        $_err['confirm'] = 'Passwords do not match';
    }

    // DB operation
    if (!$_err) {
        // TODO: Update user (password) based on token id + delete token
        $stm = $_db->prepare('
            UPDATE user
            SET password = SHA1(?)
            WHERE user_id = ( SELECT user_id FROM user_token WHERE id = ?);

            DELETE FROM user_token WHERE id = ?;
        ');
        $stm->execute([$password, $id, $id]);

        temp('info', 'Password updated successfully');
        redirect('login.php');
    }
}

// ----------------------------------------------------------------------------

$_title = 'User | Reset Password';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="/css/token.css">
</head>

<body>
    <div class="forgot-password-container">
        <div class="form-container">
            <h1>Change your password</h1>
            <p>Enter a new password below to change your password</p>

            <!-- Error Messages -->
            <div class="error-message"><?php err('empty_error'); ?></div>
            <div class="error-message"><?php err('email_error'); ?></div>

            <form method="post" class="form">
                <div class="form-group">
                    <label for="password">New password</label>
                    <input type="password" name="password" id="password" placeholder="Enter new password" required>
                    <?= err('password') ?>
                </div>

                <div class="form-group">
                    <label for="confirm">Confirm password</label>
                    <input type="password" name="confirm" id="confirm" placeholder="Confirm new password" required>
                    <?= err('confirm') ?>
                </div>

                <button type="submit">Change Password</button>
                <button type="reset">Reset</button>
            </form>
        </div>
    </div>
</body>

</html>
