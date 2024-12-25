<?php
require '../_base.php';

$memberId = req('id');

$s = getMemberbyId($memberId);

if (is_post()) {
    $name      = req('name');
    $email     = req('email');
    $contact   = req('contact');
    $file      = get_file('photo');
    // $currentPhoto = req('current_photo');
    var_dump($file->type);

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
        $s->profile_photo = $photo_path; 
    }

    if (!$_err) {

        $stm = $_db->prepare('UPDATE member
                                  SET name = ?, email = ?, contact = ?, profile_photo = ?
                                  WHERE member_id = ?');
        $stm->execute([$name, $email, $contact, $s->profile_photo, $memberId]);


        temp('info', 'Profile updated');
        redirect('user_profile.php?id=' . $memberId);
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
            <a href="user_profile.php?id=<?= $s->member_id ?>">Profile</a>
            <a href="user_address.php?id=<?= $s->member_id ?>">Addresses</a>
            <a href="user_change_password.php?id=<?= $s->member_id ?>">Change Password</a>
            <a href="user_wallet.php?id=<?= $s->member_id ?>">My Wallet</a>
        </div>

        <!-- Profile Content -->
        <div class="content">
            <h1>Personal Info</h1>
            <form class="profile-form" method="POST" enctype="multipart/form-data">

                <label class="upload member-photo" tabindex="0">
                    <input type="file" id="photo" name="photo" accept="image/*" style="display: none;" />
                    <img
                        src="<?= $s->profile_photo ? '../photos/' . $s->profile_photo : '../photos/unknown.jpg' ?>"
                        alt="Member Photo"
                        title="Click to upload new photo" />
                </label>
                <br>

                <label for="name"><strong>Name</strong></label>
                <?php html_text('name', '', $s->name, 'class="input-field" maxlength="100" '); ?>
                <?= err('name') ?>
                <br>

                <label for="email"><strong>Email</strong></label>
                <?php html_email('email', '', $s->email, 'class="input-field"'); ?>
                <?= err('email') ?>
                <br>

                <label for="contact"><strong>Contact</strong></label>
                <?php html_text('contact', '', $s->contact, 'class="input-field"'); ?>
                <?= err('contact') ?>
                <br>

                <label for="registerDate"><strong>Registered Date</strong></label>
                <?php html_text('registerDate', '', $s->register_date, 'class="input-field" readonly'); ?>
                <br>

                <button type="submit">Save</button>
            </form>
        </div>
    </div>
</body>

<?php
include '../_foot.php';
