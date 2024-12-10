<?php
require '_base.php';

$memberId = req('id');

// Get member data from member table
$stm = $_db->prepare('SELECT * FROM member WHERE member_id = ?');
$stm->execute([$memberId]);
$s = $stm->fetch();

// Get member address from address table
$addressStm = $_db->prepare('SELECT * FROM address WHERE member_id = ?');
$addressStm->execute([$memberId]);
$addressArr = $addressStm->fetchAll();

//validation
if (is_post() && req('form_type') === 'member_details') {
    $name      = req('name');
    $email     = req('email');
    $contact   = req('contact');
    $status    = req('status');
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

    //Validate photo file
    if (!$file) {
        $_err['photo'] = 'Required';
    } else if (!str_starts_with($file->type, 'image/')) {
        $_err['photo'] = 'Must be image';
    } else if ($file->size > 1 * 1024 * 1024) {
        $_err['photo'] = 'Maximum 1MB';
    }


    if (!$_err) {
        $photo = save_photo($file, 'photos');

        $stm = $_db->prepare('UPDATE member
                              SET name = ?, email = ?, contact = ?, status = ?, profile_photo = ?
                              WHERE member_id = ?');
        $stm->execute([$name, $email, $contact, $status, $photo, $memberId]);

        temp('info', $memberId . ' details updated');
        redirect('/member_management.php');
    }

    //address
    if (is_post() && req('form_type') === 'address_update') {
        $addressId    = req('address_id');
        $addressLine  = req('address_line');
        $state        = req('state');
        $postalCode   = req('postal_code');

        if (!empty($addressId)) {
            $stm = $_db->prepare('UPDATE address SET address_line = ?, state = ?, postal_code = ? WHERE address_id = ?');
            $stm->execute([$addressLine, $state, $postalCode, $addressId]);

            temp('info', 'Address updated successfully');
            redirect('/member_management.php');
        }
    }
}

include '_head.php';
?>

<link rel="stylesheet" href="/css/member_details.css">

<body>
    <div class="profile-container">
        <h2>Member Details</h2>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="form_type" value="member_details">

            <label for="id"><strong>Member ID: </strong></label>
            <?= html_text('id', '', $memberId,'disabled') ?>
            <br>

            <label for="photo"><strong>Photo:</strong></label><br>
            <label class="upload" tabindex="0" >
            <?= html_file('photo',$s->profile_photo ?  $s->profile_photo : '/photos/unknown.jpg' ,'image/*', 'hidden') ?>
            <img
                src="<?= $s->profile_photo ?  $s->profile_photo : '/photos/unknown.jpg' ?>"
                alt="Member Photo" title="Click to upload photo"/>
            </label>
            <?= err('photo') ?>
            </label>
            <br>

            <label for="name"><strong>Name:</strong></label>
            <?php html_text('name', 'Enter Name', $s->name, 'class="input-field" maxlength="100" '); ?>
            <?= err('name') ?>
            <br>

            <label for="email"><strong>Email:</strong></label>
            <?php html_email('email', 'Enter Email', $s->email, 'class="input-field"'); ?>
            <?= err('email') ?>
            <br>

            <label for="contact"><strong>Contact:</strong></label>
            <?php html_text('contact', 'Enter Contact Number', $s->contact, 'class="input-field"'); ?>
            <?= err('contact') ?>
            <br>

            <label for="status"><strong>Status:</strong></label>
            <select id="status" name="status" class="input-field">
                <option value="1" <?= $s->status == 1 ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= $s->status == 0 ? 'selected' : '' ?>>Inactive</option>
            </select>
            <br>

            <button type="submit" class="save-btn">Save Member Details</button>
        </form>

        <h2>Member Addresses</h2>

        <!-- Address Update Form -->
        <?php foreach ($addressArr as $address): ?>
            <form method="post">
                <input type="hidden" name="form_type" value="address_update">
                <input type="hidden" name="address_id" value="<?= $address->id ?>">

                <label for="address_line"><strong>Address:</strong></label>
                <?php html_text('address_line', '', $address->address_line, '" class="input-field" required'); ?>
                <br>

                <label for="state"><strong>State:</strong></label>
                <?php html_text('state', '', $address->state, '" class="input-field" required'); ?>
                <br>

                <label for="postal_code"><strong>Postal Code:</strong></label>
                <?php html_text('postal_code', '', $address->postal_code, '" class="input-field" pattern="\\d{5}" title="Postal code must be 5 digits" required'); ?>
                <br>

                <button type="submit" class="save-btn">Save Address</button>
            </form>
            <hr>
        <?php endforeach; ?>
    </div>
    <button class="go-back" onclick="window.history.back()">Go Back</button>
</body>

<?php
include '_foot.php';
