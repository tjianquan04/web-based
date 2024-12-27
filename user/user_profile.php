<?php
require '../_base.php';

$member = $_SESSION['user'];
authMember($member);

if (is_post()) {
    $name      = req('name');
    $email     = req('email');
    $contact   = req('contact');
    $file      = get_file('photo');

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
    if (empty($contact)) {
        $_err['contact'] = 'Contact number is required.';
    } else if (!preg_match('/^\d{10,15}$/', $contact)) {
        $_err['contact'] = 'Contact number must be 10-15 digits.';
    }

    //Validate RESET Password if its not empty
    if (!empty($password)) {
        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
            $_err['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one digit, one special symbol, and be at least 8 characters.';
        }
    }

    //Handle photo upload
    if ($file && str_starts_with($file->type, 'image/')) {
        $photo_path = save_photo($file, '../photos');
        $member->profile_photo = $photo_path; 
    }

    if (!$_err) {

        $stm = $_db->prepare('UPDATE member
                                  SET name = ?, email = ?, contact = ?, profile_photo = ?
                                  WHERE member_id = ?');
        $stm->execute([$name, $email, $contact, $member->profile_photo, $member->member_id]);

        $updatedMember = getMemberbyId($member->member_id);
        $_SESSION['user'] = $updatedMember;
        $member = $_SESSION['user'];
        
        temp('info', 'User Profile has updated');
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
            <a href="user_profile.php">Profile</a>
            <a href="user_address.php">Addresses</a>
            <a href="user_change_password.php">Change Password</a>
            <a href="user_wallet.php">My Wallet</a>
        </div>

        <!-- Profile Content -->
        <div class="content">
            <h1>Personal Info</h1>
            <form class="profile-form" method="POST" enctype="multipart/form-data">

                <label class="upload member-photo" tabindex="0">
                    <input type="file" id="photo" name="photo" accept="image/*" style="display: none;" />
                    <img
                        src="<?= $member->profile_photo ? '../photos/' . $member->profile_photo : '../photos/unknown.jpg' ?>"
                        alt="Member Photo"
                        title="Click to upload new photo" />
                </label>
                <br>

                <label for="name"><strong>Name</strong></label>
                <?php html_text('name', '', $member->name, 'class="input-field" maxlength="100" '); ?>
                <?= err('name') ?>
                <br>

                <label for="email"><strong>Email</strong></label>
                <?php html_email('email', '', $member->email, 'class="input-field"'); ?>
                <?= err('email') ?>
                <br>

                <label for="contact"><strong>Contact</strong></label>
                <?php html_text('contact', '', $member->contact, 'class="input-field"'); ?>
                <?= err('contact') ?>
                <br>

                <label for="registerDate"><strong>Registered Date</strong></label>
                <?php html_text('registerDate', '', $member->register_date, 'class="input-field" readonly'); ?>
                <br>

                <button type="submit">Save</button>
            </form>
        </div>
    </div>
</body>

<?php
include '../_foot.php';
