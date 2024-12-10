<?php
require '_base.php';

$memberId = req('id');

$s = getMemberbyId($memberId);

$addressArr = getAllAddressbyId($memberId);

//validation
if (is_post() && req('form_type') === 'member_details') {
    $name      = req('name');
    $email     = req('email');
    $contact   = req('contact');
    $password  = req('password');
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

    //Validate RESET Password if its not empty
    if (!empty($password)) {
        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
            $_err['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one digit, one special symbol, and be at least 8 characters.';
        }
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

        if (empty($password)) {
            $stm = $_db->prepare('UPDATE member
                                  SET name = ?, email = ?, contact = ?, status = ?, profile_photo = ?
                                  WHERE member_id = ?');
            $stm->execute([$name, $email, $contact, $status, $photo, $memberId]);
        } else if (!empty($password)) {
            $stm = $_db->prepare('UPDATE member
                                  SET name = ?, email = ?, contact = ?, password = SHA1(?) ,status = ?, profile_photo = ?
                                  WHERE member_id = ?');
            $stm->execute([$name, $email, $contact, $password, $status, $photo, $memberId]);
        }

        temp('info', $memberId . ' details updated');
        redirect('/member_management.php');
    }

    if (is_post() && req('form_type') === 'address_update') {

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

        $addressId    = req('address_id');
        $addressLine  = req('address_line');
        $state        = req('state');
        $postalCode   = req('postal_code');
        $is_default   = req('is_default');

        if (!$_err) {
            $_db->beginTransaction(); // Start transaction 
    
            try {
                // If the address is being set as default
                if ($is_default == 1) {
                    // Check if there are existing default address for this member
                    $currentDefaultStm = $_db->prepare('SELECT address_id FROM address WHERE member_id = ? AND is_default = 1');
                    $currentDefaultStm->execute([$memberId]);
                    $currentDefault = $currentDefaultStm->fetch();
    
                    // If there is an existing default address, reset its `is_default`
                    if ($currentDefault && $currentDefault['address_id'] != $addressId) {
                        $resetDefaultStm = $_db->prepare('UPDATE address SET is_default = 0 WHERE address_id = ?');
                        $resetDefaultStm->execute([$currentDefault['address_id']]);
                    }
                }
    
                // Update the selected address
                $updateAddressStm = $_db->prepare('UPDATE address SET address_line = ?, state = ?, postal_code = ?, is_default = ? WHERE address_id = ?');
                $updateAddressStm->execute([$addressLine, $state, $postalCode, $is_default, $addressId]);
    
                $_db->commit(); // Commit transaction
    
                temp('info', 'Address updated successfully');
                redirect('/member_management.php');
            } catch (Exception $e) {
                $_db->rollBack(); // Roll back changes on error
                temp('error', 'Error updating address: ' . $e->getMessage());
            }
        }
    }
}

include '_head.php';
?>

<link rel="stylesheet" href="/css/edit_member.css">

<body>
    <div class="profile-container">
        <div class="member-details">
            <h2>Member Details</h2>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="form_type" value="member_details">

                <label for="id"><strong>Member ID: </strong></label>
                <p id="id"><?= $memberId ?></p>
                <br>

                <label for="photo"><strong>Photo:</strong></label><br>
                <label class="upload" tabindex="0">
                    <?= html_file('photo', $s->profile_photo ?  'photos/' . $s->profile_photo : '/photos/unknown.jpg', 'image/*', 'hidden') ?>
                    <img
                        src="<?= $s->profile_photo ? 'photos/' . $s->profile_photo : '/photos/unknown.jpg' ?>"
                        alt="Member Photo" title="Click to upload photo" />
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

                <label for="password"><strong>Password (RESET):</strong></label>
                <?php html_password('password', 'Reset Password', '', 'class="input-field"'); ?>
                <?= err('password') ?>
                <br>

                <label for="status"><strong>Status:</strong></label>
                <select id="status" name="status" class="input-field">
                    <option value="1" <?= $s->status == 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $s->status == 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
                <br>

                <button type="submit" class="save-btn">Save Member Details</button>
            </form>
        </div>

        <div class="address-details">
            <h2>Member Addresses</h2>

            <?php if (empty($addressArr)): ?>
                <div class="no-address">
                    No addresses found. Please add a new address.
                </div>
            <?php else: ?>

                <?php foreach ($addressArr as $address): ?>
                    <form method="post">
                        <input type="hidden" name="form_type" value="address_update">
                        <input type="hidden" name="address_id" value="<?= $address->id ?>">

                        <label for="address_line"><strong>Address:</strong></label>
                        <?php html_text('address_line', '', $address->address_line, '" class="input-field"'); ?>
                        <?= err('address_line') ?>
                        <br>

                        <label for="state"><strong>State:</strong></label>
                        <?php html_text('state', '', $address->state, '" class="input-field"'); ?>
                        <?= err('state') ?>
                        <br>

                        <label for="postal_code"><strong>Postal Code:</strong></label>
                        <?php html_text('postal_code', '', $address->postal_code, '" class="input-field" '); ?>
                        <?= err('postal_code') ?>
                        <br>

                        <label for="is_default"><strong>Is Default:</strong></label>
                        <select id="is_default" name="is_default" class="input-field">
                            <option value="1" <?= $address->is_default == 1 ? 'selected' : '' ?>>TRUE</option>
                            <option value="0" <?= $address->is_default == 0 ? 'selected' : '' ?>>FALSE</option>
                        </select>
                        <br>

                        <button type="submit" class="save-btn">Save Address</button>
                        <button data-post="delete_address.php?id=<?= $address->address_id ?>" delete-confirm="<?= $address->address_id ?>" class="delete-btn">Delete</button>
                    </form>
                    <hr>
                <?php endforeach; ?>
                <button data-get="add_address.php?id=<?= $address->member_id ?>" class="add-btn"> Add new address </button>
            <?php endif; ?>
        </div>
    </div>
    <button class="go-back" onclick="window.history.back()">Go Back</button>
</body>

<?php
include '_foot.php';
