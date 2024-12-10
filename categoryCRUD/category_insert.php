<?php
include '../_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $category_id   = req('category_id');
    $category_name = req('category_name');
    $sub_category  = req('sub_category') ?: null; // Assign null if empty
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
        try {
            // Save photo
            $photo_path = save_photo($category_photo, '../photos');

            $stm = $_db->prepare('
                INSERT INTO category (category_id, category_name, sub_category, category_photo)
                VALUES (?, ?, ?, ?)
            ');
            $stm->execute([$category_id, $category_name, $sub_category, $photo_path]);

            temp('info', 'Category successfully inserted.');
            redirect('index.php');
        } catch (Exception $e) {
            $_err['database'] = 'Failed to insert category: ' . $e->getMessage();
        }
    }
}

// ----------------------------------------------------------------------------

$_title = 'Category | Insert';
include '../_head.php';
?>

<p>
    <button data-get="index.php">Back to Index</button>
</p>

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <label for="category_id">Category ID</label>
    <?= html_text('category_id', 'maxlength="10" placeholder="e.g., RAC001"') ?>
    <?= err('category_id') ?>

    <label for="category_name">Category Name</label>
    <?= html_text('category_name', 'maxlength="100"') ?>
    <?= err('category_name') ?>

    <label for="sub_category">Subcategory (optional)</label>
    <?= html_text('sub_category', 'maxlength="100" placeholder="Optional")') ?>
    <?= err('sub_category') ?>

    <label for="category_photo">Photo</label>
    <label class="upload" tabindex="0">
        <?= html_file('category_photo', 'image/*', 'hidden') ?>
        <img src="/images/photo.jpg" alt="Placeholder">
    </label>
    <?= err('category_photo') ?>

    <section>
        <button>Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>

<?php
include '../_foot.php';
