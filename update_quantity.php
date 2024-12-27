<?php
require '_base.php';

if (is_post()) {
    $cartItemId = $_POST['cartItemId'];
    $quantity = $_POST['quantity'];

    // Update the database
    $stm = $_db->prepare('UPDATE cartitem SET quantity = ? WHERE cartItem_id = ?');
    $result = $stm->execute([$quantity, $cartItemId]);

    if ($result) {
        echo json_encode(['success' => true, 'quantity' => $quantity]);
    } else {
        echo json_encode(['error' => 'Failed to update quantity.']);
    }
    exit;
}
