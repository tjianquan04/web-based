<?php
require '../_base.php';

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

// Initialize an error array
$_err = [];


if ($unit_price === '' || !is_numeric($unit_price) || $unit_price < 1.00 || $unit_price > 9999.99) {
    $_err['unit_price'] = 'Invalid price (1.00 - 9999.99).';
}

if ($stock_quantity === '' || !is_numeric($stock_quantity) || $stock_quantity < 0) {
    $_err['stock_quantity'] = 'Minimum stock quantity is 0.';
}

if ($status == 'LimitedEdition' && $invalid_date !== null) {
    if (!strtotime($invalid_date)) {
        $_err['invalid_date'] = 'Invalid date format.';
    } else {
        $stmt = $_db->prepare('SELECT dateAdded FROM product WHERE product_id = ?');
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            $added_date = $product->dateAdded;
            $invalid_date_timestamp = strtotime($invalid_date);
            $added_date_timestamp = strtotime($added_date);
            $current_date_timestamp = time();

            if ($invalid_date_timestamp <= $added_date_timestamp) {
                $_err['invalid_date'] = 'Invalid date must be after the product added date.';
            }
            if ($invalid_date_timestamp <= $current_date_timestamp) {
                $_err['invalid_date'] = 'Invalid date must be after the current date.';
            }
        } else {
            $_err['product'] = 'Product not found.';
        }
    }
}

if (!empty($_err)) {
    $_SESSION['errors'] = $_err;
    header("Location: {$_SERVER['HTTP_REFERER']}");
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
        // Log the stock quantity to a file or output it
        error_log("Stock quantity is zero for product_id: $product_id");


        // Execute the status update
        $stm = $_db->prepare('UPDATE product SET status = ? WHERE product_id = ?');
        $stm->execute(['OutOfStock', $product_id]);

        // Check the result of the query
        if ($stm->rowCount() > 0) {
            echo "Debug: Status updated to 'OutOfStock' for product_id: $product_id";
        } else {
            echo "Debug: Failed to update status for product_id: $product_id";
        }
    }

    // Calculate the total stock quantity of all products in the same category
    $stmt = $_db->prepare('SELECT SUM(stock_quantity) AS total_stock FROM product WHERE category_id = ?');
    $stmt->execute([$category_id]);
    $category_stock = $stmt->fetch();

    $total_stock_quantity_in_category = $category_stock->total_stock ?? 0; // Default to 0 if no products found

    $stm = $_db->prepare('UPDATE category SET currentStock = ? WHERE category_id = ?');
    $update_result = $stm->execute([$total_stock_quantity_in_category, $category_id]);


    // Fetch the category details from the database
    $stm = $_db->prepare('SELECT * FROM category WHERE category_id = ?');
    $stm->execute([$category_id]);
    $category = $stm->fetch();
    $minStock = $category->minStock;
    $stockAlert = $category->StockAlert;

    // Check if current stock is below the minimum stock and set stockAlert
    $stock_alert = ($total_stock_quantity_in_category < $minStock);

    // Update the category table with the new stockAlert value
    $stm = $_db->prepare('UPDATE category SET stockAlert = ? WHERE category_id = ?');
    $stm->execute([$stock_alert, $category_id]);


    if ($updateProduct) {
        $_db->commit();
        temp('info', 'Product updated successfully.');
        redirect('viewProduct.php');
    } else {
        $_db->rollBack();
        $_err['update'] = 'Failed to update the product. Please try again.';
        $_SESSION['errors'] = $_err;
        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit;
    }
} catch (Exception $e) {
    $_db->rollBack();
    $_err['exception'] = 'An error occurred: ' . $e->getMessage();
    $_SESSION['errors'] = $_err;
    header("Location: {$_SERVER['HTTP_REFERER']}");
    exit;
}

if (!empty($_SESSION['errors'])) {
    foreach ($_SESSION['errors'] as $error) {
        echo "<p>Error: $error</p>";
    }
    unset($_SESSION['errors']);
}
