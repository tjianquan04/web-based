<?php
include '_admin_head.php';

auth('Superadmin', 'Product Manager');
// Fetch categories and subcategories
$categories = $_db->query('SELECT * FROM category WHERE status NOT LIKE "Discontinued"')->fetchAll();

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
        SELECT *
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
            $_err['description'] = 'Product description is required.';
        }
        if ($unit_price == '' || !is_numeric($unit_price) || $unit_price < 1.00 || $unit_price > 9999.99) {
            $_err['unit_price'] = 'Unit price must be between RM (1.00 and 9999.99.)';
        }
        if ($stock_quantity == '' || !is_numeric($stock_quantity) || $stock_quantity < 10) {
            $_err['stock_quantity'] = 'Minimum stock quantity is 10.';
        }

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

        // Insert the product if no errors
        if (empty($_err)) {

            $defStatusStm = $_db->prepare('SELECT Status FROM category WHERE category_id = ?');
            $defStatusStm->execute([$category_id]);
            $defStatus = $defStatusStm->fetch(PDO::FETCH_OBJ); // Fetching as object        // Access the 'Status' property

            // Check if Status exists and is true
            $status = ($defStatus && $defStatus->Status) ? 'Active' : 'Inactive';

            $stm = $_db->prepare('
                INSERT INTO product (product_id, description, stock_quantity, unit_price, category_name, category_id,status,dateAdded) 
                VALUES (?, ?, ?, ?, ?, ?,?,CURRENT_TIMESTAMP)
            ');
            $stm->execute([$product_id, $description, $stock_quantity, $unit_price, $category_data->category_name, $category_id, $status]);


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


            $stock_alert = ($newStockQuantity < $category_data->minStock) ? 1 : 0;
            // Update the category table with the new stockAlert value
            $stm = $_db->prepare('
        UPDATE category
        SET stockAlert = ?
        WHERE category_id = ?
        ');
            $stm->execute([$stock_alert, $category_id]);
            if ($stock_alert) {
                // Retrieve the email address of the Product Manager from the database
                $query = $_db->prepare('SELECT email FROM admin WHERE role = ?');
                $query->execute(['Product Manager']);
                $admin = $query->fetch();

                // Check if a Product Manager was found
                if ($admin) {
                    // Send the email to the Product Manager
                    // Ensure all category data fields are set and valid
                    $category_id = $category_data->category_id ?? 'Unknown';
                    $category_name = $category_data->category_name ?? 'Unnamed Category';
                    $minStock = $category_data->minStock;
                    $category_photo = $category_data->category_photo ;

                    // Prepare the email content
                    $emailSubject = 'Low Stock Alert';
                    $emailMessage = "Current stock is below the minimum threshold.<br>
                 <strong>Category ID:</strong> {$category_id}<br>
                 <strong>Category Name:</strong> {$category_name}<br>
                 <strong>Minimum Stock Required:</strong> {$minStock}";

                 
                    // Specify the photo path (ensure the file exists)
                    $photoPath = "../image/" .$category_photo;

                    // Send the stock alert email
                    sendStockAlertEmail(
                        $admin->email,
                        $emailSubject,
                        $emailMessage,
                        true,
                        "../image/" .$category_photo
                    );
                    
                } else {
                    temp('error', 'No Product Manager found to send email to.');
                }
            }

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



            temp('UpdateSuccess', "Product added successfully.");
            temp('showSwal', true); // Set flag to show SweetAlert

        } else {
            temp('AddingFail', "Failed to add product. Please try again.");
            temp('showSwalFail', true); // Set flag to show SweetAlert for failure

        }
    }
}

// Page setup and rendering
$_title = 'Product | Insert';

?>


<link rel="stylesheet" href="/css/flash_msg.css">
<link rel="stylesheet" href="/css/add_product.css">
<script src="/js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<div class="container">
    <a href="product_index.php" class="back-button">&larr;</a>

    <h1>Insert a new product</h1>
    <form method="post" class="form" enctype="multipart/form-data" novalidate class="product-form" id="addProductForm">

        <label class="upload product-photo" tabindex="0">
            Choose up to 3 photos:
            <input type="file" id="product_photos" name="product_photos[]" multiple>
            <div id="image-preview-container">
                <img id="photo1-preview" class="product-photo-preview" style="display:none;" />
                <img id="photo2-preview" class="product-photo-preview" style="display:none;" />
                <img id="photo3-preview" class="product-photo-preview" style="display:none;" />
            </div>


            <img id="new-photo-placeholder" src="" alt="Product Photo" title="Click to upload a new photo" class="product-photo-preview" />
        </label>




        <?= err('product_photos') ?>

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




        <section>
            <button>Submit</button>
            <button type="reset">Reset</button>
        </section>
    </form>

    <?php if (isset($error)) {
        echo "<p class='error-message'>$error</p>";
    } ?>

    <?php if (temp('showSwal')): ?>
        <script>
            // Display swal() popup with the registration success message
            swal("Congrats", "<?= temp('AddingSuccess'); ?>", "success")
                .then(function() {
                    window.location.href = 'product_index.php'; // Redirect after the popup closes
                });
        </script>
    <?php endif; ?>

    <?php if (temp('showSwalFail')): ?>
        <script>
            // Display swal() popup with the registration failure message
            swal("Error", "<?= temp('AddingFail'); ?>", "error");
        </script>
    <?php endif; ?>
</div>

<script>
    document.querySelector('#product_photos').addEventListener('change', function(event) {
        const files = event.target.files; // Get the selected files
        const previewContainer = document.querySelector('#image-preview-container');
        const previewImages = previewContainer.querySelectorAll('.product-photo-preview');
        const newPhotoPlaceholder = document.querySelector('#new-photo-placeholder');

        // Clear any previous previews
        previewImages.forEach(img => img.style.display = 'none');

        // Check if any files were selected
        if (files && files.length > 0) {
            // Limit the number of photos to 3 (or any other limit)
            if (files.length > 3) {
                alert('You can only select up to 3 photos.');
                event.target.value = ''; // Clear the file input if more than 3 files are selected
                return;
            }

            // Loop through selected files and display each as a preview
            for (let i = 0; i < files.length; i++) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Only show the images that correspond to selected files
                    if (previewImages[i]) {
                        previewImages[i].src = e.target.result; // Set the image source to the file data
                        previewImages[i].style.display = 'inline'; // Make the preview visible
                    }
                };
                reader.readAsDataURL(files[i]);
            }
            newPhotoPlaceholder.style.display = 'none';


        }
    });
</script>