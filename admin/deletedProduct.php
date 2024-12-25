<?php
include '_admin_head.php';
require_once '../lib/SimplePager.php';

auth( 'Superadmin', 'Product Manager');

// Set page number
$page = req('page', 1);

// ----------------------------------------------------------------------------
// Fetching sorting and search parameters
$name = req('name'); // Search keyword
$sort = req('sort', 'description'); // Default sorting field
$dir = req('dir', 'asc'); // Default sorting direction

// Define allowed sorting columns
$valid_sort_columns = ['product_id', 'description', 'category_name', 'unit_price', 'stock_quantity', 'status', 'dateAdded'];

// Ensure $sort is valid
if (!in_array($sort, $valid_sort_columns)) {
    $sort = 'description';  // Default to description if the sort column is invalid
}

// Base query
$query = "SELECT * FROM product WHERE status  LIKE 'Discontinued'";

// Append search filters
$params = [];
if ($name) {
    $query .= ' WHERE description LIKE ?';
    $params[] = "%$name%";
}

// Add sorting
$query .= " ORDER BY " . ($sort === 'category_name' ? 'category_name' : "$sort") . " $dir";

// Use SimplePager for pagination
$p = new SimplePager(
    $query,      // Use the constructed query
    $params,     // Pass parameters for filtering
    10,          // Items per page
    $page        // Current page number
);

$_products = $p->result;

echo "Item Count: " . $p->item_count;
echo "Limit: " . $p->limit;
echo "Page Count: " . $p->page_count;

// ----------------------------------------------------------------------------

$_title = 'Product | Index';
?>

<style>
    .back-button {
    text-decoration: none;
    font-size: 1.5em;
    color:rgb(0, 0, 0);
    margin-right: 10px;
    transition: color 0.3s ease;
}

.back-button:hover {
    color:rgb(220, 0, 0);
}

</style>
 <link rel="stylesheet" href="/css/admin_management.css">


<div class="container">

<a href="product_index.php" class="back-button">&larr;</a>

    <span class="total-record"><?= count($_products) ?> record(s)</span>

    <table>
    <thead>
        <tr>
            <th onclick="window.location.href='?sort=product_id&dir=<?= $sort === 'product_id' && $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>&page=<?= $page ?>'">Product ID</th>
            <th onclick="window.location.href='?sort=description&dir=<?= $sort === 'description' && $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>&page=<?= $page ?>'">Description</th>
            <th onclick="window.location.href='?sort=category_name&dir=<?= $sort === 'category_name' && $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>&page=<?= $page ?>'">
                Category
            </th>
            <th onclick="window.location.href='?sort=unit_price&dir=<?= $sort === 'unit_price' && $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>&page=<?= $page ?>'">Unit Price</th>
            <th onclick="window.location.href='?sort=stock_quantity&dir=<?= $sort === 'stock_quantity' && $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>&page=<?= $page ?>'">Stock</th>
            <th onclick="window.location.href='?sort=status&dir=<?= $sort === 'status' && $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>&page=<?= $page ?>'">Status</th>
            <th onclick="window.location.href='?sort=dateAdded&dir=<?= $sort === 'dateAdded' && $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>&page=<?= $page ?>'">Date Added</th>
            <th onclick="window.location.href='?sort=dateAdded&dir=<?= $sort === 'dateAdded' && $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>&page=<?= $page ?>'">Date Deleted</th>
        </tr>
    </thead>
    <tbody>
        <?php $num = 1; ?>
        <?php foreach ($_products as $product): ?>
            <tr onclick="window.location.href='product_details.php?product_id=<?= $product->product_id ?>'">
                <td><?= $product->product_id ?></td>
                <td><?= $product->description ?></td>
                <td>
                    <?= $product->category_name ?>
                    <?php if (!empty($product->sub_category)): ?>
                        <br>- <?= $product->sub_category ?>
                    <?php endif; ?>
                </td>
                <td><?= $product->unit_price ?></td>
                <td><?= $product->stock_quantity ?></td>
                <td><?= $product->status ?></td>
                <td><?= $product->dateAdded ?></td>
                <td><?= $product->dateDeleted ?></td>
             
            </tr>
        <?php endforeach; ?>
    </tbody>
    </table>

   
    <div class="pagination">
    <?= generateDynamicPagination($p, $sort, $dir); ?>
    </div>
</div>



