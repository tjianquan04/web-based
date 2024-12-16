<?php
include '../_base.php';

// Fetch categories and subcategories
$categories = $_db->query('SELECT category_id, category_name, sub_category FROM category')->fetchAll();

$grouped_categories = [];
foreach ($categories as $cat) {
    $main_category = $cat->category_name;
    $subcategory = $cat->sub_category;
    $category_id = $cat->category_id;

    if (!isset($grouped_categories[$main_category])) {
        $grouped_categories[$main_category] = [
            'id' => $category_id,
            'subcategories' => []
        ];
    }

    if (!empty($subcategory)) {
        $grouped_categories[$main_category]['subcategories'][] = [
            'id' => $category_id,
            'name' => $subcategory
        ];
    }
}

// Handle form submission
if (is_post()) {
    $description = req('description');
    $unit_price = req('unit_price');
    $category_id = req('category_id');
    $sub_category = req('sub_category') ?: null;
    $stock_quantity = req('stock_quantity');

    $product_photos = $_FILES['product_photos']; // Capture the uploaded photos


    // If no subcategory is selected, set it to null
    if ($sub_category === '') {
        $sub_category = null;
    }


    // Fetch the category name and subcategory based on category_id
    $stm = $_db->prepare('
        SELECT category_name, sub_category
        FROM category
        WHERE category_id = ?
    ');


    $stm->execute([$category_id]);
    $category_data = $stm->fetch();

    if ($category_data) {
        $category_name = $category_data->category_name;
        $subcategory = $category_data->sub_category;
    } else {
        // If no matching category_id found
        $_err['category_name'] = 'Invalid category or subcategory.';
    }

    // Validate and insert the product if no errors
    if (empty($_err)) {
        $product_id = generate_product_id($category_id, $_db);

        if ($description == '') {
            $_err['description'] = 'Required.';
        }
        if ($unit_price == '' || !is_numeric($unit_price) || $unit_price < 1.00 || $unit_price > 9999.99) {
            $_err['unit_price'] = 'Invalid price (1.00 - 9999.99).';
        }
        if ($stock_quantity == '' || !is_numeric($stock_quantity) || $stock_quantity < 10) {
            $_err['stock_quantity'] = 'Minimum stock quantity is 10.';
        }

        // Validate product photos (file upload)
        //   if (empty($product_photos['name'][0])) {
        //     $_err['product_photos'] = 'At least one photo is required.';
        // } else {
        //     foreach ($product_photos['name'] as $key => $photo_name) {
        //         if (!str_starts_with($product_photos['type'][$key], 'image/')) {
        //             $_err['product_photos'] = 'Each photo must be an image.';
        //             break;
        //         }
        //         if ($product_photos['size'][$key] > 1 * 1024 * 1024) {
        //             $_err['product_photos'] = 'Each photo must not exceed 1MB.';
        //             break;
        //         }
        //     }
        // }


        // Validate product photos (file upload)
        if (empty($product_photos['name'][0])) {
            $_err['product_photos'] = 'At least one photo is required.';
        } else {
            foreach ($product_photos['name'] as $key => $photo_name) {
                // Check if the file is an image
                if (!str_starts_with($product_photos['type'][$key], 'image/')) {
                    $_err['product_photos'] = 'Each photo must be an image.';
                    break;
                }
                // Check the file size (1MB max)
                if ($product_photos['size'][$key] > 1 * 1024 * 1024) {
                    $_err['product_photos'] = 'Each photo must not exceed 1MB.';
                    break;
                }
            }
        }

        foreach ($_FILES['product_photos']['name'] as $key => $filename) {
            echo "Uploaded file name: " . $filename;
        }

        // Insert the product if no errors
        if (empty($_err)) {

            $defStatusStm = $_db->prepare('SELECT Status FROM category WHERE category_id = ?');
            $defStatusStm->execute([$category_id]);
            $defStatus = $defStatusStm->fetch(PDO::FETCH_OBJ); // Fetching as object        // Access the 'Status' property

            // Check if Status exists and is true
            $status = ($defStatus && $defStatus->Status) ? 'Active' : 'Inactive';

            $stm = $_db->prepare('
                INSERT INTO product (product_id, description, stock_quantity, unit_price, category_name, category_id,status) 
                VALUES (?, ?, ?, ?, ?, ?,?)
            ');
            $stm->execute([$product_id, $description, $stock_quantity, $unit_price, $category_name, $category_id, $status]);


            // Fetch current stock for the category
            $currentCatStock = $_db->prepare('SELECT currentStock FROM category WHERE category_id = ?');
            $currentCatStock->execute([$category_id]);
            $currentStock = $currentCatStock->fetchColumn();

            // Calculate new stock quantity
            $newStockQuantity = $currentStock + $stock_quantity;

            // Update the stock in the category table
            $updateCatStock = $_db->prepare('
            UPDATE category
            SET currentStock = ?
            WHERE category_id = ?
            ');
            $updateCatStock->execute([$newStockQuantity, $category_id]);

            // Save photos and get paths
            $photo_paths = [];
            foreach ($product_photos['tmp_name'] as $key => $tmp_name) {
                $photo_filename = save_photos($product_photos['tmp_name'][$key], '../product_gallery');
                $photo_paths[] = $photo_filename;
            }

            // Insert photos into product_photo table
            $stmt = $_db->prepare("INSERT INTO product_photo (product_photo_id, default_photo, product_id) VALUES (?, ?, ?)");

            // Loop through each photo, set the first photo as default
            foreach ($photo_paths as $index => $photo_filename) {
                $is_default = ($index === 0) ? 1 : 0; // First photo is set as default (TRUE), others are FALSE
                $stmt->execute([$photo_filename, $is_default, $product_id]);
            }


            temp('info', 'Product added successfully.');
            redirect('viewProduct.php');
        }
    } else {
        temp('info', 'Product added failed.');
        redirect('../index.php');
        // If there are validation errors, you don't insert the product
        // You can display error messages or handle it accordingly
    }
}

// Page setup and rendering
$_title = 'Product | Insert';
include '../_head.php';
?>

<style>
    /* Form Styling */
    form {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
        font-family: Arial, sans-serif;
    }

    form label {
        font-weight: bold;
        display: block;
        margin-bottom: 8px;
    }

    form input,
    form select,
    form button {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
    }

    form input:focus,
    form select:focus,
    form button:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }

    form button {
        background-color: #28a745;
        color: #fff;
        font-size: 16px;
        font-weight: bold;
        border: none;
        cursor: pointer;
    }

    form button:hover {
        background-color: #218838;
    }

    form button[type="reset"] {
        background-color: #dc3545;
    }

    form button[type="reset"]:hover {
        background-color: #c82333;
    }

    .error {
        color: red;
        font-size: 14px;
        margin-bottom: 10px;
    }
</style>

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <label for="category_id">Category</label>
    <?= html_select_with_subcategories('category_id', $grouped_categories) ?>
    <?= err('category_id') ?>

    <label for="description">Product Description</label>
    <?= html_text('description') ?>
    <?= err('description') ?>

    <label for="unit_price">Unit Price</label>
    <?= html_number('unit_price', 1.00, 9999.99, 1.00) ?>
    <?= err('unit_price') ?>

    <label for="stock_quantity">Stock Quantity</label>
    <?= html_number('stock_quantity', 10, 9999, 1) ?>
    <?= err('stock_quantity') ?>

    <!-- <label for="product_photos">Gallery</label>
    <input 
        type="file" 
        id="product_photos" 
        name="product_photos[]" 
        accept="image/*" 
        multiple 
        style="display: block; margin-bottom: 10px;"
    >
     -->
    <label for="product_photos">Choose multiple photos:</label>
    <input type="file" name="product_photos[]" multiple>
    <?= err('product_photos') ?>



    <section>
        <button>Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>

<?php
include '../_foot.php';
?>