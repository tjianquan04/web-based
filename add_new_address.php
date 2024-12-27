<?php
require '_base.php';

if (is_post()) {
    $addressLine = $_POST['address_line'];
    $postalCode = $_POST['postal_code'];
    $state = $_POST['state'];
    $addressId = 'A000021'; //need replace
    $memberId = 'M000001'; //need replace
    $isDefault = 0;

    $stmt = $_db->prepare(
        'INSERT INTO address (`address_id`, `address_line`, `postal_code`, `state`, `is_default`, `member_id`) 
        VALUES (?,?,?,?,?,?)'
    );
    $result = $stmt->execute([$addressId,$addressLine, $postalCode, $state, $isDefault, $memberId]);

    if ($result) {
        echo 'Success';
    } else {
        echo 'Database update failed.';
    }
}
