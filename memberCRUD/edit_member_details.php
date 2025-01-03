<?php
require '../admin/_admin_head.php';

$memberId = req('id');

$s = getMemberbyId($memberId);

$addressArr = getAllAddressbyMemberId($memberId);

if (is_post() && (req('form_type') == 'member_details')) {
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
    }else if ($email != $s->email){
        if(is_exists($email,'member', 'email')){
        $_err['email'] = $email.' has exists.';
        }
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

        if (empty($password)) {
            $stm = $_db->prepare('UPDATE member
                                  SET name = ?, email = ?, contact = ?, status = ?, profile_photo = ?
                                  WHERE member_id = ?');
            $stm->execute([$name, $email, $contact, $status, $s->profile_photo, $memberId]);
        } else if (!empty($password)) {
            $stm = $_db->prepare('UPDATE member
                                  SET name = ?, email = ?, contact = ?, password = SHA1(?) ,status = ?, profile_photo = ?
                                  WHERE member_id = ?');
            $stm->execute([$name, $email, $contact, $password, $status, $s->profile_photo, $memberId]);
        }

        temp('UpdateSuccess', "$memberId details updated");
        temp('showSwal', true); // Set flag to show SweetAlert
    }
} else if (is_post() && (req('form_type') == 'address_update')) {

    $address_id    = req('address_id');
    $address_line  = req('address_line_' . $address_id);
    $state        = req('state_' . $address_id);
    $postal_code   = req('postal_code_' . $address_id);
    $is_default   = req('is_default_' . $address_id);

    // Validate Address Line
    if (empty($address_line)) {
        $_err['address_line_' . $address_id] = 'Address is required.';
    }

    // Validate State
    if (empty($state)) {
        $_err['state_' . $address_id] = 'State is required.';
    } else if (!preg_match('/^[a-zA-Z\s]+$/', $state)) {
        $_err['state_' . $address_id] = 'State must contain only letters.';
    }

    // Validate Postal Code
    if (empty($postal_code)) {
        $_err['postal_code_' . $address_id] = 'Postal code is required.';
    } else if (!preg_match('/^\d{5}$/', $postal_code)) {
        $_err['postal_code_' . $address_id] = 'Postal code must be exactly 5 digits.';
    }

    if ($is_default == 1) {
        // Query to check if another default address exists for the same member
        $otherDefaultStm = $_db->prepare('SELECT address_id FROM address WHERE member_id = ? AND is_default = 1 AND address_id != ?');
        $otherDefaultStm->execute([$memberId, $address_id]);
        $otherDefault = $otherDefaultStm->fetch();

        if ($otherDefault) {
            $_err['is_default_' . $address_id] = 'Another default address already exists. Only one default address is allowed.';
        }
    }

    if (!$_err) {

        // Update the selected address
        $updateAddressStm = $_db->prepare('UPDATE address SET address_line = ?, state = ?, postal_code = ?, is_default = ? WHERE address_id = ?');
        $updateAddressStm->execute([$address_line, $state, $postal_code, $is_default, $address_id]);
        temp('UpdateSuccess', "$memberId . ' details updated'");
        temp('showSwal', true); // Set flag to show SweetAlert
    }
}

?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js"></script>
<script src="../js/main.js"></script>
<link rel="stylesheet" href="../css/edit_member.css">

<body>
    <div class="container">
        <div class="member-details">
            <h2>Member Details</h2>

            <form method="post" enctype="multipart/form-data" class="profile-form">
                <input type="hidden" name="form_type" value="member_details">

                <label class="upload member-photo" tabindex="0">
                    <input type="file" id="photo" name="photo" accept="image/*" style="display: none;" onchange="previewImage(event)" />
                    <img
                        id="profile-photo-preview"
                        src="<?= $s->profile_photo ? '../photos/' . $s->profile_photo : '../photos/unknown.jpg' ?>"
                        alt="Member Photo"
                        title="Click to upload new photo" />
                </label>
                <?= err('photo') ?>
                </label>
                <br>

                <h4>Member ID: <?= $s->member_id ?></h4><br>


                <label for="name">Name:</label>
                <?php html_text('name', 'Enter Name', $s->name, 'class="input-field" maxlength="100" '); ?>
                <?= err('name') ?>
                <br>

                <label for="email">Email:</label>
                <?php html_email('email', 'Enter Email', $s->email, 'class="input-field"'); ?>
                <?= err('email') ?>
                <br>

                <label for="contact">Contact:</label>
                <?php html_text('contact', 'Enter Contact Number', $s->contact, 'class="input-field"'); ?>
                <?= err('contact') ?>
                <br>

                <label for="password">Password (RESET):</label>
                <?php html_password('password', 'Reset Password', '', 'class="input-field"'); ?>
                <?= err('password') ?>
                <br>

                <label for="wallet">Wallet :</label>
                <?php html_text('wallet', '', 'RM' . $s->wallet, 'class="input-field" readonly'); ?>
                <br>

                <label for="registerDate">Registered Date :</label>
                <?php html_text('registerDate', '', $s->register_date, 'class="input-field" readonly'); ?>
                <br>

                <label for="status">Status:</label>
                <select id="status" name="status" class="input-field">
                    <option value="Active" <?= $s->status == 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= $s->status == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <br>
                <button type="submit" class="save-btn">Save Changes</button>
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
                    <form method="post" class="address-form">
                        <input type="hidden" name="form_type" value="address_update">
                        <input type="hidden" name="address_id" value="<?= $address->address_id ?>">

                        <label for="address_line_<?= $address->address_id ?>">Address:</label>
                        <?php html_text('address_line_' . $address->address_id, '', $address->address_line, '" class="input-field"'); ?>
                        <?= err('address_line_' . $address->address_id) ?>
                        <br>

                        <label for="state_<?= $address->address_id ?>">State:</label>
                        <?php html_select('state_' . $address->address_id, $_states, '', 'class="input-field"', $address->state) ?>
                        <?= err('state_' . $address->address_id) ?>
                        <br>

                        <label for="postal_code_<?= $address->address_id ?>">Postal Code:</label>
                        <?php html_text('postal_code_' . $address->address_id, '', $address->postal_code, '" class="input-field" '); ?>
                        <?= err('postal_code_' . $address->address_id) ?>
                        <br>

                        <label for="is_default_<?= $address->address_id ?>">Is Default:</label>
                        <select id="is_default_<?= $address->address_id ?>" name="is_default_<?= $address->address_id ?>" class="input-field">
                            <option value="1" <?= $address->is_default == 1 ? 'selected' : '' ?>>TRUE</option>
                            <option value="0" <?= $address->is_default == 0 ? 'selected' : '' ?>>FALSE</option>
                        </select>
                        <?= err('is_default_' . $address->address_id) ?>
                        <br>

                        <button type="submit" class="save-btn">Save Changes</button>
                        <button data-post="delete_address.php?id=<?= $address->address_id ?>" delete-confirm data-address-ids="<?= $address->address_id ?>" class="delete-btn">Delete</button>
                    </form>
                    <hr>
                <?php endforeach; ?>
                <button data-get="add_address.php?id=<?= $address->member_id ?>" class="add-btn"> Add new address </button>
            <?php endif; ?>
        </div>
    </div>
    <button class="go-back" data-get="../admin/member_management.php">Go Back</button>
    <?php if (temp('showSwal')): ?>
        <script>
            // Display swal() popup with the success message and redirect after user confirms
            swal("Congrats", "<?= temp('UpdateSuccess'); ?>", "success")
                .then(function() {
                    window.location.href = redirectUrl; // Redirect to the appropriate page
                });
        </script>
    <?php endif; ?>
</body>

<script>
    $('[data-get]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.get;
        location.href = url || location.href; // Use location.href to navigate
    });

    function previewImage(event) {
        var file = event.target.files[0];
        var reader = new FileReader();

        reader.onload = function(e) {
            var preview = document.getElementById('profile-photo-preview');
            preview.src = e.target.result;
        };

        if (file) {
            reader.readAsDataURL(file);
        }
    }
</script>