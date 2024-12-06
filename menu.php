<?php
require '_base.php';
//-----------------------------------------------------------------------------
// Capture the query parameters
$category = req('category_name');  // For main category
$category_id = req('category_id');  // For subcategory by ID
$name = req('name'); // For searching purpose

// Capture the sort and dir parameters (default to name ascending if not provided)
$sort = req('sort', 'description');  // Default sort by 'description' (name)
$dir = req('dir', 'asc');  // Default direction is 'asc'

// Initialize an empty products array
$product = [];

// Check if a category is selected (main category or subcategory)
if ($category) {
    // Fetch products or subcategories based on the category name
    $stm = $_db->prepare("SELECT * FROM product WHERE category_name = ? ORDER BY $sort $dir");
    $stm->execute([$category]);
    $product = $stm->fetchAll();
} elseif ($category_id) {
    // Fetch products based on the category ID (for subcategory)
    $stm = $_db->prepare("SELECT * FROM product WHERE category_id = ? ORDER BY $sort $dir");
    $stm->execute([$category_id]);
    $product = $stm->fetchAll();
} elseif ($name) {
    // Query the database to search for products by description
    $stm = $_db->prepare("SELECT * FROM product WHERE description LIKE ? ORDER BY $sort $dir");
    $stm->execute(["%$name%"]);
    $product = $stm->fetchAll();
} else {
    // Fetch all products if no category or search term is provided
    $stm = $_db->query("SELECT * FROM product ORDER BY $sort $dir");
    $product = $stm->fetchAll();
}

// ----------------------------------------------------------------------------
$_title = $category ? "Products in $category" : "Products in Subcategory $category_id";
include '_head.php';
?>

<h1>Products</h1>

<?php if (count($product) > 0): ?>
    <table class="table">
        <tr>
            <th>
                <a href="?sort=description&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>">Product Name</a>
            </th>
            <th>
                <a href="?sort=product_id&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>">Product ID</a>
            </th>
            <th>
                <a href="?sort=unit_price&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>">Price</a>
            </th>
        </tr>

        <?php foreach ($product as $p): ?>
            <tr>
                <td><?= $p->description ?></td>
                <td><?= $p->product_id ?></td>
                <td><?= $p->unit_price ?></td>
            </tr>
        <?php endforeach ?>
    </table>
<?php else: ?>
    <p>No products found matching your search criteria.</p>
<?php endif ?>

<?php
include '_foot.php';
