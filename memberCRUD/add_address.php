<?php
require '../_base.php';
// ----------------------------------------------------------------------------

$address_id = getNextAddressId();
$member_id = req('id');
$addressArr = getAllAddressbyMemberId($member_id);

if (is_post()) {

    $address_line = req('address_line');
    $state        = req('state');
    $postal_code  = req('postal_code');

   
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

// Check if the member has a default address
$isTrue = 0; 
$is_default = 0;

if (empty($addressArr)) {
    $is_default = 1;
} else {
    foreach ($addressArr as $address) {
        if ($address->is_default) {
            $isTrue = 1;
            break;
        }
    }

    if (!$isTrue) {
        $is_default = 1;
    }
}

    // Output
    if (!$_err) {

        $addressStm = $_db->prepare('INSERT INTO address (address_id, address_line, postal_code, state, is_default, member_id) VALUES(?,?,?,?,?,?)');

        $addressStm -> execute([$address_id, $address_line,$postal_code,$state, $is_default, $member_id]);

        temp('info','New address has added');
        redirect('/edit_member_details.php?id='.$member_id);
    }
}

// ----------------------------------------------------------------------------
$_title = 'Add address';
include '../_head.php';
?>
<link rel="stylesheet" href="../css/add_address.css">

<div class="address-container">
<h2>New Address</h2>
<form method="post" class="form" enctype="multipart/form-data" novalidate>
    
    <label for="id"><strong>Address ID :</strong></label>
    <?= html_text('id', '', $address_id,' class="input-field" disabled') ?>

    <label for="address_line"><strong>Address:</strong></label>
    <?php html_text('address_line', '', '', '" class="input-field"'); ?>
    <?= err('address_line') ?>

    <label for="state"><strong>State:</strong></label>
    <?= html_select('state', $_states, '-- Select State --', 'class="input-field"') ?>
    <?= err('state') ?>

    <label for="postal_code"><strong>Postal Code:</strong></label>
    <?php html_text('postal_code', '', '', '" class="input-field"'); ?>
    <?= err('postal_code') ?>

    <section>
        <button type="submit">Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>
</div>
<button class="go-back" onclick="window.history.back()">Go Back</button>

<?php
include '../_foot.php';
