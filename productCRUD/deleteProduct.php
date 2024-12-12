<?php
include '../_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $product_id = req('product_id');

    if (empty($product_id)) {
        temp('info', 'No product ID provided');
        error_log('Failed to delete: Product ID not provided');
        redirect('viewProduct.php');
    }

    error_log("Attempting to delete product with ID: $product_id");

    // Delete product
    $stm = $_db->prepare('DELETE FROM product WHERE product_id = ?');
    if ($stm->execute([$product_id])) {
        temp('info', 'Product deleted successfully');
        error_log("Product with ID $product_id deleted successfully");
    } else {
        temp('info', 'Failed to delete product');
        error_log('Failed to delete product. Error: ' . json_encode($stm->errorInfo()));
    }
} else {
    temp('info', 'Invalid request method');
    error_log('Failed to delete: Invalid request method');
}

redirect('viewProduct.php');
?>
