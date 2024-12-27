<?php
require '_base.php';

if (is_post()) {
    $id = $_POST['addressId'];
    $ori_id = $_POST['oriaddressId'];

    $stm = $_db->prepare('UPDATE address SET is_default = 0 WHERE address_id = ?');
    $result = $stm->execute([$ori_id]);

    $stm2 = $_db->prepare('UPDATE address SET is_default = 1 WHERE address_id = ?');
    $result2 = $stm2->execute([$id]);

    if ($result && $result2) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to change location.']);
    }
    exit;
}
