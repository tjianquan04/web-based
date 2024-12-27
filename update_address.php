<?php
require '_base.php';

if (is_post()) {
    $addressId = $_POST['address_id'];
    $addressLine = $_POST['address_line'];
    $postalCode = $_POST['postal_code'];
    $state = $_POST['state'];

    $stmt = $_db->prepare(
        'UPDATE address
         SET address_line = ?, postal_code = ?, state = ?
         WHERE address_id = ?'
    );
    $result = $stmt->execute([$addressLine, $postalCode, $state, $addressId]);

    if ($result) {
        echo 'Success';
    } else {
        echo 'Database update failed.';
    }
}
