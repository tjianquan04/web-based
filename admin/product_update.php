<?php
include "../_base.php";

auth('Superadmin', 'Product Manager');
// Retrieve input values
$product_id = req('product_id');
$unit_price = req('unit_price');
$stock_quantity = req('stock_quantity'); // Ensure it's an integer
$status = req('status');
$invalid_date = req('invalid_date') ?? null;

$stm = $_db->prepare('SELECT * FROM product WHERE product_id = ?');
$stm->execute([$product_id]);
$product = $stm->fetch();

$category_id = $product->category_id;

$stm = $_db->prepare('SELECT * FROM category WHERE category_id = ?');
$stm->execute([$category_id]);
$category = $stm->fetch();

// Initialize an error array
$_err = [];

if ($unit_price === '' || !is_numeric($unit_price) || $unit_price < 1.00 || $unit_price > 9999.99) {
    $_err['unit_price'] = 'Invalid price.(RM 1.00 - RM 9999.99)';
}

if ($stock_quantity === '' || !is_numeric($stock_quantity) || $stock_quantity < 0) {
    $_err['stock_quantity'] = 'Minimum stock quantity is 0.';
}

if ($status == 'LimitedEdition' && $invalid_date !== null) {
    $invalid_date_object = DateTime::createFromFormat('Y-m-d', $invalid_date);
    if (!$invalid_date_object) {
        $_err['invalid_date'] = 'Invalid date format.';
    } else {
        $added_date_object = new DateTime($product->dateAdded);
        $current_date_object = new DateTime();

        if ($invalid_date_object <= $added_date_object) {
            $_err['invalid_date'] = 'Invalid date must be after the product added date.';
        }
        if ($invalid_date_object <= $current_date_object) {
            $_err['invalid_date'] = 'Invalid date must be after the current date.';
        }
    }
}


if (!empty($_err)) {
    $_SESSION['errors'] = $_err;
    header("Location: product_details.php?product_id=$product_id");
    exit;
}

try {
    $_db->beginTransaction();

    $update_stm = $_db->prepare('UPDATE product SET unit_price = ?, stock_quantity = ?, status = ? WHERE product_id = ?');
    $updateProduct = $update_stm->execute([$unit_price, $stock_quantity, $status, $product_id]);


    // Determine the value of invalidDate based on status and invalid_date
    $invalidDateValue = null; // Default to null

    if ($status == 'LimitedEdition' && $invalid_date !== null) {
        $invalidDateValue = $invalid_date;  // Set to the provided invalid_date if conditions are met
    }




    // Update the product with the determined invalidDate value
    $stm = $_db->prepare('UPDATE product SET invalidDate = ? WHERE product_id = ?');
    $stm->execute([$invalidDateValue, $product_id]);

    if ($stock_quantity == 0) {
        error_log("Stock quantity is zero for product_id: $product_id");

        // Execute the status update
        $stm = $_db->prepare('UPDATE product SET status = ? WHERE product_id = ?');
        $stm->execute(['OutOfStock', $product_id]);
    }

    // Calculate the total stock quantity of all products in the same category
    $stmt = $_db->prepare('SELECT SUM(stock_quantity) AS total_stock FROM product WHERE category_id = ?');
    $stmt->execute([$category_id]);
    $category_stock = $stmt->fetch();

    $total_stock_quantity_in_category = $category_stock->total_stock ?? 0;

    $stm = $_db->prepare('UPDATE category SET currentStock = ? WHERE category_id = ?');
    $update_result = $stm->execute([$total_stock_quantity_in_category, $category_id]);

    // Update category stockAlert

    $stock_alert = ($total_stock_quantity_in_category < $category->minStock) ? 1 : 0;
    
    $stm = $_db->prepare('UPDATE category SET stockAlert = ? WHERE category_id = ?');
    $stm->execute([$stock_alert, $category_id]);

    if ($stock_alert) {
        // Retrieve the email address of the Product Manager from the database
        $query = $_db->prepare('SELECT email FROM admin WHERE role = ?');
        $query->execute(['Product Manager']);
        $admin = $query->fetch();
       

        // Check if a Product Manager was found
        if ($admin) {
            error_log($admin->email);
            // Send the email to the Product Manager
            sendStockAlertEmail($admin->email, 'Low Stock Alert', 'Current stock is below the minimum threshold.', true,  "../image/".$category->category_photo);
        } else {
            temp('error', 'No Product Manager found to send email to.');
        }
    }


    $checkStm = $_db->prepare('
    SELECT m.member_id, m.email
    FROM wishlist w
    JOIN member m ON w.member_id = m.member_id
    WHERE w.product_id = ?
');
    $checkStm->execute([$product_id]);
    $member_wish = $checkStm->fetchAll(PDO::FETCH_ASSOC); // Fetch results as an associative array


    if ($updateProduct) {
        $_db->commit();
    $email_info = "<br>Product Name: " . $product->description . "<br>Price: RM " . $unit_price . "<br>In-Stock quantity:  " . $stock_quantity . "<br>Status:  " . $status;

        foreach ($member_wish as $member) {
            // Send the email to the member
            sendStockAlertEmail($member['email'], 'Product Update', 'The product in your wishlist has been updated <br>' . $email_info, true, null);
        }



        $_SESSION['success'] = "Product updated successfully: The product ID $product_id has been updated.";
        header("Location: product_details.php?product_id=$product_id");
        exit;
    } else {
        $_db->rollBack();
        $_err['update'] = 'Failed to update the product. Please try again.';
        $_SESSION['errors'] = $_err;
        header("Location: product_details.php?product_id=$product_id");
        exit;
    }
} catch (Exception $e) {
    $_db->rollBack();
    error_log("[ERROR] Product update failed: " . $e->getMessage());
    $_err['exception'] = 'An unexpected error occurred. Please try again later.';
    $_SESSION['errors'] = $_err;
    header("Location: product_details.php?product_id=$product_id");
    exit;
}
