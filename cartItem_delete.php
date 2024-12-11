
<?php
require '_base.php';

if (is_post()) {
    $id = req('id');

    $stm = $_db->prepare('DELETE FROM cartitem WHERE cartItem_id = ?');
    $stm->execute([$id]);

}

redirect('/cart.php');
