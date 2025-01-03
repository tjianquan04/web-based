
<?php
include '../_base.php';

auth('Superadmin', 'Product Manager');
// ----------------------------------------------------------------------------


// Check if category_id is provided
if (!isset($_GET['product_id'])) {
    die("Error: No product ID provided.");
}

$product_id = $_GET['product_id'];
  
    error_log("Attempting to delete product with ID: $product_id");

    // Fetch required data before deleting the product
    $stm = $_db->prepare(
        'SELECT p.category_id, p.stock_quantity, c.currentStock, c.minStock, c.StockAlert, c.category_photo, c.category_name
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
    $currentStock = $product_data->currentStock ?? 0;
    $minStock = $product_data->minStock ?? 0;
    $StockAlert = $product_data->StockAlert;
    $category_photo = $product_data->category_photo;
    $category_name = $product_data->category_name;
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
    $updateStatusStm = $_db->prepare('UPDATE product SET status = ? ,stock_quantity = 0, dateDeleted = NOW() WHERE product_id = ?');
    if ($updateStatusStm->execute(['Discontinued', $product_id])) {
        // After updating the product's status to 'Discontinued', update category stock
        $newStockQuantity = $currentStock - $stock_quantity;

        $updateCatStock = $_db->prepare('
            UPDATE category
            SET currentStock = ?
            WHERE category_id = ?'
        );
        $updateCatStock->execute([$newStockQuantity, $category_id]);

           // Check stock alert status
           $stock_alert = ($newStockQuantity < $minStock);
           $updateStockAlertStm = $_db->prepare('UPDATE category SET StockAlert = ? WHERE category_id = ?');
           $updateStockAlertStm->execute([$stock_alert, $category_id]);

           // Optionally notify Product Manager if stock alert is triggered
           if ($stock_alert) {
               $adminQuery = $_db->prepare('SELECT email FROM admin WHERE role = ?');
               $adminQuery->execute(['Product Manager']);
               $admin = $adminQuery->fetch(PDO::FETCH_OBJ);

               $email_info = "<b>Category ID: <b> " . $category_id . "<br><b>Category Name: <b>" . $category_name; 

               if ($admin) {
                   sendStockAlertEmail($admin->email, 'Low Stock Alert', 'Current stock is below the minimum threshold. <br>'. $email_info, true, "../image/".$category_photo);
               } else {
                   error_log("No Product Manager found for stock alert notification.");
               }
           }

        temp('info', 'Product have been updated to discontinued.');
        redirect('product_index.php');
        error_log("Product with ID $product_id set to 'Discontinued' successfully. Stock updated for category $category_id.");
    } else {
        // Set failure message for SweetAlert
        
        temp('info', 'Product have been failed to discontinued.');
        redirect('product_index.php');
        error_log('Failed to update product status. Error: ' . json_encode($updateStatusStm->errorInfo()));
    }
    

    
   
?>


        