<?php
require '_base.php';
// ----------------------------------------------------------------------------

$member_id = getNextUserId();
$address_id = getNextAddressId();

if (is_post()) {
    // Input
    $name      = req('name');
    $email     = req('email');
    $contact   = req('contact');
    $password  = req('password');
    $status    = req('status');
    $file      = get_file('photo');

    $address_line = req('address_line');
    $state        = req('state');
    $postal_code  = req('postal_code');

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

    if (empty($password)) {
        $_err['password'] = 'Password is required.';
    } else if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $_err['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one digit, one special symbol, and be at least 8 characters.';
    }

    // Validate Address Line
    if (empty($address_line)) {
        $_err['address_line'] = 'Address is required.';
    }

    // Validate State
    if (empty($state)) {
        $_err['state'] = 'State is required.';
    } else if (!preg_match('/^[a-zA-Z\s]+$/', $state)) {
        $_err['state'] = 'State must contain only letters.';
    }

    // Validate Postal Code
    if (empty($postal_code)) {
        $_err['postal_code'] = 'Postal code is required.';
    } else if (!preg_match('/^\d{5}$/', $postal_code)) {
        $_err['postal_code'] = 'Postal code must be exactly 5 digits.';
    }

    //Validate photo file
    if (!$file) {
        $_err['photo'] = 'Required';
    } else if (!str_starts_with($file->type, 'image/')) {
        $_err['photo'] = 'Must be image';
    } else if ($f->size > 1 * 1024 * 1024) {
        $_err['photo'] = 'Maximum 1MB';
    }

    // Output
    if (!$_err) {
        $photo = save_photo($f, 'photos');

        $stm = $_db->prepare('INSERT INTO member (member_id, email, contact, password, status,profile_photo) VALUES(?,?,?,?,?,?)');

        $stm->execute([$member_id, $name, $email, $contact, SHA1($password), $status, $photo]);

        $addressStm = $_db->prepare('INSERT INTO address (address_id, address_line, postal_code, state, is_default, member_id) VALUES(?,?,?,?,?,?)');

        $addressStm -> execute([$address_id, $address_line,$postal_code,$state, 1, $member_id]);

        temp('info','New member '. $member_id .' has added');
        redirect('/member_management.php');
    }
}

// ----------------------------------------------------------------------------
$_title = 'Add Member';
include '_head.php';
?>
<link rel="stylesheet" href="/css/add_member.css">

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <label for="id"><strong>Member Id :</strong></label>
    <?= html_text('id', '', $member_id, 'disabled') ?>

    <label for="photo"><strong>Photo: </strong></label><br>
    <label class="upload" tabindex="0">
        <?= html_file('photo', 'image/*', 'hidden') ?>
        <img src="/photos/unknown.jpg">
    </label>
    <?= err('photo') ?><br>

    <label for="name"><strong>Name:</strong></label>
    <?php html_text('name', 'Enter Name', '', 'class="input-field" maxlength="100"'); ?>
    <?= err('name') ?>
    <br>

    <label for="email"><strong>Email:</strong></label>
    <?php html_email('email', 'Enter Email', '', 'class="input-field"'); ?>
    <?= err('email') ?>
    <br>

    <label for="contact"><strong>Contact:</strong></label>
    <?php html_text('contact', 'Enter Contact Number', '', 'class="input-field"'); ?>
    <?= err('contact') ?>
    <br>

    <label for="password"><strong>Password:</strong></label>
    <?php html_password('password', 'Enter Password', '', 'class="input-field"'); ?>
    <?= err('password') ?>
    <br>

    <label for="status"><strong>Status:</strong></label>
    <select id="status" name="status" class="input-field">
        <option value="1">Active</option>
        <option value="0">Inactive</option>
    </select>
    <br>

    <label for="address_line"><strong>Address:</strong></label>
    <?php html_text('address_line', '', '', '" class="input-field"'); ?>
    <?= err('password') ?>
    <br>

    <label for="state"><strong>State:</strong></label>
    <?php html_text('state', '', '', '" class="input-field" '); ?>
    <?= err('password') ?>
    <br>

    <label for="postal_code"><strong>Postal Code:</strong></label>
    <?php html_text('postal_code', '', '', '" class="input-field"'); ?>
    <?= err('password') ?>
    <br>

    <section>
        <button>Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>

<button class="go-back" onclick="window.history.back()">Go Back</button>

<?php
include '_foot.php';
