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

    // Fetch required data before deleting the product
    $stm = $_db->prepare('
        SELECT p.category_id, p.stock_quantity, c.currentStock 
        FROM product p 
        JOIN category c ON p.category_id = c.category_id 
        WHERE p.product_id = ?
    ');
    $stm->execute([$product_id]);
    $product_data = $stm->fetch();


    $galleryStm = $_db->prepare(
        'SELECT product_photo_id
    FROM product_photo
    WHERE product_id = ?'
    );
    $galleryStm->execute([$product_id]);

    // Fetch all rows and store them in the $gallery array
    $gallery = $galleryStm->fetchAll(PDO::FETCH_OBJ);



    if (!$product_data) {
        temp('info', 'Product not found');
        error_log('Failed to delete: Product not found');
        redirect('viewProduct.php');
    }

    $category_id = $product_data->category_id;
    $stock_quantity = $product_data->stock_quantity;
    $currentStock = $product_data->currentStock;


    foreach ($gallery as $photo) {  // Delete associated photo 
        echo $photo->product_photo_id; // Access each product_photo_id
        // Delete associated photo 
        $photo_path = '../product_gallery/' . $photo->product_photo_id;
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }

        $deletePhotosStm = $_db->prepare('DELETE FROM product_photo WHERE product_id = ?');
        $deletePhotosStm->execute([$product_id]);
    }
    // Delete the product
    $deleteStm = $_db->prepare('DELETE FROM product WHERE product_id = ?');
    if ($deleteStm->execute([$product_id])) {
        // Update category stock
        $newStockQuantity = $currentStock - $stock_quantity;

        $updateCatStock = $_db->prepare('
            UPDATE category
            SET currentStock = ?
            WHERE category_id = ?
        ');
        $updateCatStock->execute([$newStockQuantity, $category_id]);





        temp('info', 'Product deleted successfully');
        error_log("Product with ID $product_id deleted successfully. Stock updated for category $category_id.");
    } else {
        temp('info', 'Failed to delete product');
        error_log('Failed to delete product. Error: ' . json_encode($deleteStm->errorInfo()));
    }
} else {
    temp('info', 'Invalid request method');
    error_log('Failed to delete: Invalid request method');
}

redirect('viewProduct.php');
