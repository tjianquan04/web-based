<?php
include '../_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $product_id = req('product_id');

    // Delete photo
    $stm = $_db->prepare('SELECT * FROM product WHERE product_id = ?');
    $stm->execute([$product_id]);
    $photo = $stm->fetchAll();
 
    temp('info', 'Product deleted');
}

redirect('viewProduct.php');

// ----------------------------------------------------------------------------
