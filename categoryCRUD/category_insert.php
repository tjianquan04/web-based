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

    // if($sub_category==null){
    //     $sub_category='';

    // }

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

            $stm = $_db->prepare('
                INSERT INTO category (category_id, category_name, sub_category, category_photo)
                VALUES (?, ?, ?, ?)
            ');
            $stm->execute([$category_id, $category_name, $sub_category, $photo_path]);

            temp('info', 'Category successfully inserted.');
            redirect('../index.php');
        
    }
    else{
        temp('info', 'Category failed inserted.');
    }
}

// ----------------------------------------------------------------------------

$_title = 'Category | Insert';
include '../_head.php';
?>
<style>
/* Form Container Styling */
form {
    max-width: 600px;
    margin: 40px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    font-family: Arial, sans-serif;
}

/* Labels and Inputs */
form label {
    font-weight: bold;
    display: block;
    margin-bottom: 8px;
}

form input, form button, form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

form input:focus, form textarea:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* File Upload Styling */
.upload {
    display: inline-block;
    position: relative;
    cursor: pointer;
    text-align: center;
    width: 100%;
    height: 150px;
    margin-bottom: 20px;
    border: 2px dashed #ccc;
    border-radius: 4px;
    background-color: #f9f9f9;
}

.upload img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

.upload:hover {
    background-color: #f0f0f0;
    border-color: #007bff;
}

/* Error Messages */
.error {
    color: red;
    font-size: 14px;
    margin-bottom: 10px;
}

/* Buttons */
form button {
    background-color: #28a745;
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    padding: 10px 20px;
    border-radius: 5px;
    transition: background-color 0.3s;
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

/* Back Button */
p button {
    background-color: #007bff;
    color: #fff;
    font-size: 14px;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.3s;
}

p button:hover {
    background-color: #0056b3;
}
</style>

<p>
    <button onclick="window.location.href='index.php'">Back to Index</button>
</p>

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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.querySelector('input[name="category_photo"]');
    const previewImage = document.querySelector('.upload img');

    fileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            previewImage.src = '/images/photo.jpg'; // Placeholder image
        }
    });
});
</script>


<?php
include '../_foot.php';
?>

