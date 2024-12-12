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

    error_log("Form submitted, category_name: $category_id");  // Debugging submitted category_id

    // If no subcategory is selected, set it to null
    if ($sub_category === '') {
        $sub_category = null;
    }

    // Debugging the form values
    error_log("Category ID: $category_id");
    error_log("Subcategory: $sub_category");

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
    
        // Log the fetched data
        error_log("Fetched Category Name: $category_name, Subcategory: $subcategory");
    } else {
        // If no matching category_id found
        error_log("No category found for category_id: $category_id");
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
    
        // Insert the product if no errors
        if (empty($_err)) {
            $stm = $_db->prepare('
                INSERT INTO product (product_id, description, stock_quantity, unit_price, category_name, category_id) 
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            $stm->execute([$product_id, $description, $stock_quantity, $unit_price, $category_name, $category_id]);
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

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <label for="category_id">Category</label>
    <?= html_select_with_subcategories('category_id', $grouped_categories) ?>
    <?= err('category_id') ?>

    <label for="description">Product Description</label>
    <?= html_text('description', 'maxlength="100"') ?>
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

<?php
include '../_foot.php';
?>
