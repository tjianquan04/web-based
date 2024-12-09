<?php
include '../_base.php';


if (is_post()) {
    $description = req('description');
    $unit_price = req('unit_price');
    $category_name = req('category_name');
    $category_id = req('category_id');
    $stock_quantity = req('stock_quantity');

    // Validate: description
    if ($description == '') {
        $_err['description'] = 'Required';
    } else if (strlen($description) > 100) {
        $_err['description'] = 'Maximum 100 characters';
    }

    // Validate: price
    if ($unit_price == '') {
        $_err['unit_price'] = 'Required';
    } else if (!is_money($unit_price)) {
        $_err['unit_price'] = 'Must be money';
    } else if ($unit_price < 1.00 || $unit_price > 9999.99) {
        $_err['unit_price'] = 'Must between 1.00 - 9999.99';
    }

    // Generate product ID
    if (!$_err) {
        $product_id = generate_product_id($category_name, $sub_category, $_db);

        $stm = $_db->prepare('
            INSERT INTO `product`(`product_id`, `description`, `stock_quantity`, `unit_price`, `category_name`, `category_id`) 
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stm->execute([$product_id, $description, $stock_quantity, $unit_price, $category_name, $category_id]);

        temp('info', 'Record inserted');
        redirect('../index.php');
    }
}

// Fetch categories and subcategories
$_categories = $_db->query('SELECT category_id, category_name, sub_category FROM category')->fetchAll();

$grouped_categories = [];
foreach ($_categories as $category) {
    $main_category = $category->category_name;
    $subcategory = $category->sub_category;
    $category_id = $category->category_id;

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

$_title = 'Product | Insert';
include '../_head.php';
?>

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <label for="category_name">Category</label>
    <?= html_select_with_subcategories('category_name', $grouped_categories) ?>
    <?= err('category_name') ?>

    <label for="description">Product Description</label>
    <?= html_text('description', 'maxlength="100"') ?>
    <?= err('description') ?>

    <label for="unit_price">Unit Price</label>
    <?= html_number('unit_price', 1.00, 9999.99, 1.00) ?>
    <?= err('unit_price') ?>

    <label for="stock_quantity">Stock Quantity</label>
    <?= html_number('stock_quantity', 10, 9999, 1) ?>
    <?= err('stock_quantity') ?>

    <label for="photo">Photos</label>
    <input type="file" name="photos[]" accept="image/*" multiple>
    <?= err('photos') ?>

    <label for="default_photo">Default Photo</label>
    <select name="default_photo">
        <!-- This can be dynamically populated with uploaded photo options -->
    </select>
    <?= err('default_photo') ?>

    <section>
        <button>Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>

<?php
include '../_foot.php';
