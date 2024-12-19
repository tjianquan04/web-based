<link rel="stylesheet" href="../css/categoryForm.css">

<?php
require '../_base.php';

if (is_get()) {
    $category_id = req('category_id');

    // Fetch the category details from the database
    $stm = $_db->prepare('SELECT * FROM category WHERE category_id = ?');
    $stm->execute([$category_id]);
    $category = $stm->fetch();


    if (!$category) {
        redirect('index.php'); // If no category found, redirect to index
    }

    $status = $category->Status; // Directly use the status from the database



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
    // $status = req('status') === '1' ? true : false; // Converts '1' to true and '0' to false

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
        $stock_alert = ($currentStock < $minStock) ? true : false;

        $stm = $_db->prepare('
            UPDATE category
            SET stockAlert = ?
            WHERE category_id = ?
        ');
        $stm->execute([$stock_alert, $category_id]);

        // Update all products' status based on the category status
        $product_status = ($status == true) ? 'Active' : 'Inactive'; // Map boolean status to 'Active' or 'Inactive'

        $stm = $_db->prepare('
    UPDATE product
    SET status = ?
    WHERE category_id = ?
');
        $stm->execute([$product_status, $category_id]);


        // Optionally, if stockAlert is true, handle additional logic like notifications
        if ($stock_alert) {
            // Example: Send notifications to admins about low stock (if required)
        }

        // Success or failure message
        if ($update_result) {

            temp('info', 'Category updated successfully.');
            redirect('ViewCategory.php');
        } else {
            $_err['update'] = 'Failed to update the category. Please try again.';
        }
    }
}


$_title = 'Category | Update';
include '../_head.php';
?>

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <label for="category_id">Category ID</label>
    <input type="text" id="category_id" name="category_id" value="<?= htmlspecialchars($category_id) ?>" disabled>

    <label for="category_name">Category Name</label>
    <input type="text" id="category_name" name="category_name" value="<?= htmlspecialchars($category_name) ?>" disabled>

    <label for="sub_category">Subcategory</label>
    <input type="text" id="sub_category" name="sub_category" value="<?= $sub_category ?>" disabled>

    <label for="currentStock">Current Stock</label>
    <input type="number" value="<?= $currentStock ?>" disabled>

    <label for="minStock">Minimum Stock</label>
    <?= html_number('minStock', 0, 100000, 1) ?>
    <?= err('minStock') ?>

    <label for="photo">Category Photo</label>
    <label class="upload" tabindex="0">
        <?= html_file('category_photo', 'image/*', 'hidden') ?>
        <img src="../image/<?= htmlspecialchars($category_photo) ?>" alt="<?= htmlspecialchars($category_name) . ' ' . htmlspecialchars($sub_category) ?>" onerror="this.src='/image/photo.jpg';">
    </label>
    <?= err('category_photo') ?>


    <!-- Category Active Checkbox -->
    <label>Active:
        <?php if ($status == 1): ?>
            Active<input type="radio" name="status" value="1" checked>
            Inactive<input type="radio" name="status" value="0">
        <?php else: ?>
            Active<input type="radio" name="status" value="1">
            Inactive<input type="radio" name="status" value="0" checked>
        <?php endif; ?>
    </label>



    <section>
        <button type="submit">Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>

<script>
    // Photo preview
    $('label.upload input[type=file]').on('change', e => {
        const f = e.target.files[0];
        const img = $(e.target).siblings('img')[0];

        if (!img) return;

        img.dataset.src ??= img.src;

        if (f?.type.startsWith('image/')) {
            img.src = URL.createObjectURL(f);
        } else {
            img.src = img.dataset.src;
            e.target.value = '';
        }
    });
</script>

<?php
include '../_foot.php';
?>