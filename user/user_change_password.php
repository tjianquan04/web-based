<?php
require '../_base.php';

$member = $_SESSION['user'];
authMember($member);

if (is_post()) {
    $current_password = req('current_password');
    $new_password = req('new_password');
    $confirm_password = req('confirm_password');

    if(empty( $current_password)){
        $_err['current_password'] = 'Current password is required.';
    }else if (!validCurrentPassword($current_password, $member->member_id)){
        $_err['current_password'] = 'Current password is invalid.';
    }

    if(empty( $new_password)){
        $_err['new_password'] = 'New password is required.';
    }else if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_password)){
        $_err['new_password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one digit, one special symbol, and be at least 8 characters.';
    }

    if(empty( $confirm_password)){
        $_err['confirm_password'] = 'Confirm password is required.';
    }else if($confirm_password != $new_password){
        $_err['confirm_password'] = 'Password is not matched.';
    }

    if (!$_err) {
            $stm = $_db->prepare('UPDATE member
                                  SET password = SHA1(?)
                                  WHERE member_id = ?');
            $stm->execute([$new_password, $member->member_id]);
        

        temp('info','User password updated');
    }
}

include '../_head.php';
?>
<script src="../js/main.js"></script>
<link rel="stylesheet" href="../css/user_profile.css">

<body>
<div class="user-profile-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>My Account</h2>
            <a href="user_profile.php">Profile</a>
            <a href="user_address.php">Addresses</a>
            <a href="user_change_password.php">Change Password</a>
            <a href="user_wallet.php">My Wallet</a>
        </div>

        <!-- Profile Content -->
        <div class="content">
            <h1>Change Password</h1>
            <form class="profile-form" method="POST">

                <label for="current_password"><strong>Current Password</strong></label>
                <?php html_password('current_password', 'Current password', '', 'class="input-field"'); ?>
                <?= err('current_password') ?>
                <br>

                <label for="new_password"><strong>New Password</strong></label>
                <?php html_password('new_password', 'New password','', 'class="input-field"'); ?>
                <?= err('new_password') ?>
                <br>

                <label for="confirm_password"><strong>Confirm Password</strong></label>
                <?php html_password('confirm_password', 'Confirm password', '', 'class="input-field"'); ?>
                <?= err('confirm_password') ?>
                <br>

                <button type="submit">Save</button>
            </form>
        </div>
    </div>
</body>

<?php
include '../_foot.php';
