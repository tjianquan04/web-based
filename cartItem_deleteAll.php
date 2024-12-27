<?php
require '_base.php';

if (is_post()) {
    $id = $_POST['cartItemId'];

    $stm = $_db->prepare('DELETE FROM cartitem where cartItem_id = ?');
    $result = $stm->execute([$id]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to delete selected item.']);
    }
    exit;
}

