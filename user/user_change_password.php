<?php
require '../_base.php';

$memberId = req('id');

$s = getMemberbyId($memberId);

if (is_post()) {
    $current_password = req('current_password');
    $new_password = req('new_password');
    $confirm_password = req('confirm_password');

    if(empty( $current_password)){
        $_err['current_password'] = 'Current password is required.';
    }else if (!validCurrentPassword($current_password, $memberId)){
        $_err['current_password'] = 'Current password is invalid.';
    }

    if(empty( $new_password)){
        $_err['new_password'] = 'New password is required.';
    }else if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)){
        $_err['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one digit, one special symbol, and be at least 8 characters.';
    }

    if(empty( $confirm_password)){
        $_err['confirm_password'] = 'Confirm password is required.';
    }else if($confirm_password != $new_password){
        $_err['confirm_password'] = 'Password is not matched.';
    }

    if (!$_err) {
            $stm = $_db->prepare('UPDATE member
                                  SET password = ?
                                  WHERE member_id = ?');
            $stm->execute([SHA1($new_password), $memberId]);
        

        temp('info','User password updated');
        redirect('/');
    }
}

include '../_head.php';
?>
<script src="../js/main.js"></script>
<link rel="stylesheet" href="/css/user_profile.css">

<body>
<div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>My Account</h2>
            <a href="user_profile.php?id=<?= $s->member_id?>" style="color: #ff5e3a;">Profile</a>
            <a href="user_address.php?id=<?= $s->member_id?>">Addresses</a>
            <a href="user_change_password.php?id=<?= $s->member_id?>">Change Password</a>
            <a href="user_top_up.php?id=<?= $s->member_id?>">Top Up</a>
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
                <?php html_password('email', 'New password','', 'class="input-field"'); ?>
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
