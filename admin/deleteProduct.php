<script src="https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js"></script>

<?php
include '../_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $product_id = req('product_id');
    error_log("Attempting to delete product with ID: $product_id");

    // Fetch required data before deleting the product
    $stm = $_db->prepare('
        SELECT p.category_id, p.stock_quantity, c.currentStock 
        FROM product p 
        JOIN category c ON p.category_id = c.category_id 
        WHERE p.product_id = ?'
    );
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

    // Get product details
    $category_id = $product_data->category_id;
    $stock_quantity = $product_data->stock_quantity;
    $currentStock = $product_data->currentStock;

    // Delete associated photos
    foreach ($gallery as $photo) {  
        $photo_path = '../product_gallery/' . $photo->product_photo_id;
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }

        $deletePhotosStm = $_db->prepare('DELETE FROM product_photo WHERE product_id = ?');
        $deletePhotosStm->execute([$product_id]);
    }

    // Update the status of the product to 'Discontinued' instead of deleting it
    $updateStatusStm = $_db->prepare('UPDATE product SET status = ? WHERE product_id = ?');
    if ($updateStatusStm->execute(['Discontinued', $product_id])) {
        // After updating the product's status to 'Discontinued', update category stock
        $newStockQuantity = $currentStock - $stock_quantity;

        $updateCatStock = $_db->prepare('
            UPDATE category
            SET currentStock = ?
            WHERE category_id = ?'
        );
        $updateCatStock->execute([$newStockQuantity, $category_id]);

        // Set success message for SweetAlert
        temp('UpdateSuccess', "Product status set to 'Discontinued' and stock updated.");
        temp('showSwal', true); // Show success message via SweetAlert
        error_log("Product with ID $product_id set to 'Discontinued' successfully. Stock updated for category $category_id.");
    } else {
        // Set failure message for SweetAlert
        temp('UpdateFail', "Failed to update product status.");
        temp('showSwalFail', true); // Show failure message via SweetAlert
        error_log('Failed to update product status. Error: ' . json_encode($updateStatusStm->errorInfo()));
    }
    
}
    
   
?>

<?php if (temp('showSwal')): ?>
    <script>
        // Display swal() popup with the update success message
        swal("Congrats", "<?= temp('UpdateSuccess'); ?>", "success")
            .then(function() {
                window.location.href = 'product_index.php'; // Redirect after the popup closes
            });
    </script>
<?php endif; ?>

<?php if (temp('showSwalFail')): ?>
    <script>
        // Display swal() popup with the update failure message
        swal("Error", "<?= temp('UpdateFail'); ?>", "error")
            .then(function() {
                window.location.href = 'product_index.php'; // Redirect after the popup closes
            });
    </script>
<?php endif; ?>
        