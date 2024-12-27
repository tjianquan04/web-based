<link rel="stylesheet" href="/css/flash_msg.css">
<link rel="stylesheet" href="/css/editForm.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../js/admin_head.js"></script>
<?php
include '_admin_head.php';
auth('Superadmin', 'Product Manager');

if (is_get()) {
    $category_id = req('category_id');

    // Fetch the category details from the database
    $stm = $_db->prepare('SELECT * FROM category WHERE category_id = ?');
    $stm->execute([$category_id]);
    $category = $stm->fetch();


    $status = $category->Status; // Directly use the status from the database
    $currentStock = $category->currentStock;



    extract((array)$category);
    $_SESSION['category_photo'] = $category->category_photo; // Store current photo path in session
}

if (is_post()) {
    $category_id    = req('category_id');
    $category_name  = req('category_name');
    $sub_category   = req('sub_category');
    $f              = get_file('category_photo');
    $category_photo = $_SESSION['category_photo']; // Use the session's stored photo initially

    $currentStock = req('currentStock');
    $minStock = req('minStock');

    $status = req('status');

    // Validate minStock
    if (!is_numeric($minStock) || $minStock < 0) {
        $_err['minStock'] = 'Minimum stock must be a non-negative number';
    }

    // Validate category_photo (only if a file is uploaded)
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['category_photo'] = 'File must be an image';
        } elseif ($f->size > 1 * 1024 * 1024) {
            $_err['category_photo'] = 'File size must be less than 1MB';
        }
    }

    // Proceed with database operations if no validation errors
    if (!$_err) {
        // If a new photo is uploaded, delete the old one and save the new one
        if ($f) {
            if (file_exists("../image/$category_photo")) {
                unlink("../image/$category_photo"); // Delete the old photo
            }
            $category_photo = save_photo($f, '../image'); // Save new photo and update the variable
        }


        // Update the category details
        $stm = $_db->prepare('
            UPDATE category
            SET category_photo=?, minStock = ?, status = ?
            WHERE category_id = ?
        ');
        $update_result = $stm->execute([$category_photo, $minStock, $status, $category_id]);

        // Check if current stock is below the minimum stock and set stockAlert
        $stock_alert = ($currentStock < $minStock);

        // Update the category table with the new stockAlert value
        $stm = $_db->prepare('
            UPDATE category
            SET stockAlert = ?
            WHERE category_id = ?
            ');
        $stm->execute([$stock_alert, $category_id]);

        // Update all products' status based on the category status
        $product_status = $status ; // Map boolean status to 'Active' or 'Inactive'

        $stm = $_db->prepare('
    UPDATE product
    SET status = ?
    WHERE category_id = ?
');
        $stm->execute([$product_status, $category_id]);


        // Optionally, if stockAlert is true, handle additional logic like notifications
        if ($stock_alert) {
            // Retrieve the email address of the Product Manager from the database
            $query = $_db->prepare('SELECT email FROM admin WHERE role = ?');
            $query->execute(['Product Manager']);
            $admin = $query->fetch();

            // Check if a Product Manager was found
            if ($admin) {
                // Send the email to the Product Manager
                sendStockAlertEmail($admin->email, 'Low Stock Alert', 'Current stock is below the minimum threshold.', true, "../image/$category_photo");
            } else {
                temp('error', 'No Product Manager found to send email to.');
            }
        }

        // Success or failure message
        if ($update_result) {

            temp('UpdateSuccess', "Category is updated successfully");
            temp('showSwal', true); // Set flag to show SweetAlert

        } else {
            temp('EditFail', "Failed to edit category. Please try again.");
            temp('showSwalFail', true); // Set flag to show SweetAlert for failure
        }
    }
}


$_title = 'Category | Update';
?>


<div class="container">
<a href="viewCategory.php" class="back-button">&larr;</a>

    <form method="post" class="form" enctype="multipart/form-data" novalidate>
        <label for="photo">Category Photo</label>
        <label class="upload" tabindex="0">
            <?= html_file('category_photo', 'image/*', 'hidden') ?>
            <img src="../image/<?= htmlspecialchars($category_photo) ?>" alt="<?= htmlspecialchars($category_name) . ' ' . htmlspecialchars($sub_category) ?>" onerror="this.src='/image/photo.jpg';">
        </label>
        <?= err('category_photo') ?>

        <label for="category_id">Category ID</label>
        <input type="text" id="category_id" class="form-input" name="category_id" value="<?= htmlspecialchars($category_id) ?>" disabled>

        <label for="category_name">Category Name</label>
        <input type="text" id="category_name" name="category_name" class="form-input" value="<?= htmlspecialchars($category_name) ?>" disabled>

        <label for="sub_category">Subcategory</label>
        <input type="text" id="sub_category" name="sub_category" class="form-input" value="<?= $sub_category ?>" disabled>

        <label for="currentStock">Current Stock</label>
        <input type="number" class="form-input" value="<?= $currentStock ?>" disabled>
        <input type="hidden" name="currentStock" value="<?= $currentStock ?>">


        <label for="minStock">Minimum Stock</label>
        <?= html_number('minStock', 0, 100000, 1) ?>
        <?= err('minStock') ?>




        <!-- Category Active Checkbox -->
        <label>Category Status: </label>
            <?php if ($status == 1): ?>
                <input type="radio"  name="status" value="Active" checked>Active
                <br><br><input type="radio"   name="Inactive" value="0">Inactive
            <?php else: ?>
                <input type="radio" name="status" value="Active">Active
                <br><br><input type="radio"  name="status" value="Inactive" checked>Inactive
            <?php endif; ?>
        



        <section>
            <button type="submit">Submit</button>
            <button type="reset">Reset</button>
        </section>
    </form>

    <?php if (temp('showSwal')): ?>
        <script>

            // Display swal() popup with the success message and redirect after user confirms
            swal("Congrats", "<?= temp('UpdateSuccess'); ?>", "success")
                .then(function() {
                    window.location.href = "viewCategory.php"; // Redirect to the appropriate page
                });
        </script>
    <?php endif; ?>

    <?php if (temp('showSwalFail')): ?>
        <script>
            // Display swal() popup with the registration failure message
            swal("Error", "<?= temp('EditFail'); ?>", "error");
        </script>
    <?php endif; ?>

</div>