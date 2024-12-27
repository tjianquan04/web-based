<link rel="stylesheet" href="/css/flash_msg.css">
<link rel="stylesheet" href="/css/editForm.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../js/admin_head.js"></script>
<?php
include '_admin_head.php';
auth('Superadmin', 'Product Manager');

// ----------------------------------------------------------------------------

if (is_post()) {
    $category_id   = req('category_id');
    $category_name = req('category_name');
    $sub_category  = req('sub_category') ?: null; // Assign null if empty
    $minStock = req('minStock');
    $category_photo = get_file('category_photo');

    // Validate: category_id
    if ($category_id == '') {
        $_err['category_id'] = 'Category ID is required.';
    } else if (!preg_match('/^[A-Za-z0-9]{3,10}$/', $category_id)) {
        $_err['category_id'] = 'Category ID must be 3-10 alphanumeric characters.';
    } else if (!is_unique($category_id, 'category', 'category_id')) {
        $_err['category_id'] = 'Category ID already exists.';
    }


    // Validate: category_name
    if ($category_name == '') {
        $_err['category_name'] = 'Category name is required.';
    } else if (strlen($category_name) > 100) {
        $_err['category_name'] = 'Category name must not exceed 100 characters.';
    }

    // Validate: category_photo (file)
    if (!$category_photo) {
        $_err['category_photo'] = 'Category photo is required.';
    } else if (!str_starts_with($category_photo->type, 'image/')) {
        $_err['category_photo'] = 'Category photo must be an image.';
    } else if ($category_photo->size > 1 * 1024 * 1024) {
        $_err['category_photo'] = 'Category photo size must not exceed 1MB.';
    }

    // DB operation
    if (!$_err) {
        
            // Save photo
            $photo_path = save_photo($category_photo, '../image');

            // Set default values for currentStock, stockAlert, and Status
        $currentStock = 0; // Default to 0
        $stockAlert = false; // Default to false
        $status = 'Active'; 

        $stm = $_db->prepare('
            INSERT INTO category (category_id, category_name, sub_category, category_photo, minStock, currentStock, stockAlert, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stm->execute([$category_id, $category_name, $sub_category, $photo_path, $minStock, $currentStock, $stockAlert, $status]);


        temp('UpdateSuccess', "Category is added");
        temp('showSwal', true); // Set flag to show SweetAlert
        
    }
    else{
        temp('EditFail', "Failed to add category. Please try again.");
            temp('showSwalFail', true); // Set flag to show SweetAlert for failure
    }
}

// ----------------------------------------------------------------------------

$_title = 'Category | Insert';
?>


<div class="container">

<a href="javascript:history.back()" class="back-button">&larr;</a>

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <label for="category_id">Category ID</label>
    <?= html_text('category_id', 'maxlength="10" placeholder="e.g., RAC001"') ?>
    <?= err('category_id') ?>

    <label for="category_name">Category Name</label>
    <?= html_text('category_name', 'maxlength="100"') ?>
    <?= err('category_name') ?>

    <label for="sub_category">Subcategory (optional)</label>
    <?= html_text('sub_category', 'maxlength="100" placeholder="Optional"') ?>
    <?= err('sub_category') ?>

    <label for="minStock">Minimum Stock</label>
    <?= html_number('minStock', 0, 100000, 1) ?>
    <?= err('minStock') ?>

    <label for="category_photo">Photo</label>
    <label class="upload" tabindex="0">
        <?= html_file('category_photo', 'image/*', 'hidden') ?>
        <img src="/images/photo.jpg" alt="Placeholder">
    </label>
    <?= err('category_photo') ?>

    <section>
        <button type="submit">Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>
</div>
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



