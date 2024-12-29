<?php
require '_base.php';

if (is_post()) {
    $addressLine = $_POST['address_line'];
    $postalCode = $_POST['postal_code'];
    $state = $_POST['state'];
    $addressId = 'A000021'; //need replace

    $member = $_SESSION['user'];
    authMember($member);
    $id =  $member->member_id;
    
    $isDefault = 0;


    $stm = $_db->prepare(
        'SELECT address_id
        FROM `address`
        ORDER BY address_id DESC
        LIMIT 1'
    );
    $stm->execute();
    $lastAddressId = $stm->fetchColumn();

    if ($lastAddressId) {
        $outputInt = (intval(substr($lastAddressId, 1))) + 1;
        $newAddressId = 'A' . str_pad($outputInt, 6, '0', STR_PAD_LEFT);
    } else {
        $newAddressId = 'A000001';
    }


    $stmt = $_db->prepare(
        'INSERT INTO address (`address_id`, `address_line`, `postal_code`, `state`, `is_default`, `member_id`) 
        VALUES (?,?,?,?,?,?)'
    );
    $result = $stmt->execute([$newAddressId,$addressLine, $postalCode, $state, $isDefault, $id]);

    if ($result) {
        echo 'Success';
    } else {
        echo 'Database update failed.';
    }
}
