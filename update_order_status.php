<?php
require '_base.php';

if (is_post()) {
    $orderId = $_POST['orderId'];
    $status = "completed";

    $stm = $_db->prepare(
        'UPDATE order_record
         SET order_status = ?
         WHERE order_id = ?'
    );
    $result = $stm->execute([$status,$orderId]);

    if ($result) {
        echo 'Success';
    } else {
        echo 'Database update failed.';
    }
}
