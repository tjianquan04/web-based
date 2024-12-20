<?php
require '../_base.php';

$memberId = req('id');

$s = getMemberbyId($memberId);
$addressArr = getAllAddressbyMemberId($memberId);

if (is_post()) {

    $address_id    = req('address_id');
    $address_line  = req('address_line_'.$address_id);
    $state        = req('state_'.$address_id);
    $postal_code   = req('postal_code_'.$address_id);
    $is_default   = req('is_default_'.$address_id);   

    // Validate Address Line
    if (empty($address_line)) {
        $_err['address_line_'.$address_id] = 'Address is required.';
    }

    // Validate State
    if (empty($state)) {
        $_err['state_'.$address_id] = 'State is required.';
    } else if (!preg_match('/^[a-zA-Z\s]+$/', $state)) {
        $_err['state_'.$address_id] = 'State must contain only letters.';
    }

    // Validate Postal Code
    if (empty($postal_code)) {
        $_err['postal_code_'.$address_id] = 'Postal code is required.';
    } else if (!preg_match('/^\d{5}$/', $postal_code)) {
        $_err['postal_code_'.$address_id] = 'Postal code must be exactly 5 digits.';
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

        temp('info', 'Address updated successfully');
        redirect('/');
    }
}

include '../_head.php';
?>
<script src="../js/main.js"></script>
<link rel="stylesheet" href="../css/user_profile.css">

<body>
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>My Account</h2>
        <a href="user_profile.php?id=<?= $s->member_id?>">Profile</a>
        <a href="user_address.php?id=<?= $s->member_id?>" class="active-link">Addresses</a>
        <a href="user_change_password.php?id=<?= $s->member_id?>">Change Password</a>
        <a href="user_top_up.php?id=<?= $s->member_id?>">Top Up</a>
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
                        <input type="hidden" name="address_id" value="<?= $address->address_id ?>">

                        <label for="address_line_<?= $address->address_id ?>"><strong>Address:</strong></label>
                        <?php html_text('address_line_'.$address->address_id, '', $address->address_line, '" class="input-field"'); ?>
                        <?= err('address_line_'.$address->address_id) ?>
                        <br>

                        <label for="state_<?= $address->address_id ?>"><strong>State:</strong></label>
                        <?php html_select('state_'.$address->address_id, $_states, '- Select a State -', 'class="input-field"', $address->state) ?>
                        <?= err('state_'.$address->address_id) ?>
                        <br>

                        <label for="postal_code_<?= $address->address_id ?>"><strong>Postal Code:</strong></label>
                        <?php html_text('postal_code_'.$address->address_id, '', $address->postal_code, '" class="input-field" '); ?>
                        <?= err('postal_code_'.$address->address_id) ?>
                        <br>

                        <label for="is_default_<?= $address->address_id ?>"><strong>Is Default:</strong></label>
                        <select id="is_default_<?= $address->address_id ?>" name="is_default_<?= $address->address_id ?>" class="input-field">
                            <option value="1" <?= $address->is_default == 1 ? 'selected' : '' ?>>TRUE</option>
                            <option value="0" <?= $address->is_default == 0 ? 'selected' : '' ?>>FALSE</option>
                        </select>
                        <?= err('is_default_'.$address->address_id) ?>
                        <br>

                        <button type="submit" class="save-btn">Save Changes</button>
                        <button data-post="delete_address.php?id=<?= $address->address_id ?>" delete-confirm data-address-id="<?= $address->address_id ?>" class="delete-btn">Delete</button>
                    </form>
                    <hr>
                <?php endforeach; ?>
                <button data-get="add_address.php?id=<?= $address->member_id ?>" class="add-btn"> Add new address </button>
            <?php endif; ?>
        </div>
    </div>
</body>

<?php
include '../_foot.php';
