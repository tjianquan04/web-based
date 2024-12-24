<?php
require '../_base.php';
require_once '../lib/SimplePager.php';

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
$query = 'SELECT * FROM product ';

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
include '../_head.php';
?>


<style>
    /* Styling */
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-size: 15px;
        text-align: left;
    }

    thead {
        background-color: #f4f4f4;
    }

    th,
    td {
        padding: 12px;
        border: 1px solid #ddd;
    }

    tr:hover {
        background-color: #f0f0f0;
        cursor: pointer;
    }

    .btn {
        display: inline-block;
        margin: 5px;
        padding: 8px 12px;
        color: #fff;
        text-decoration: none;
        font-size: 14px;
        border-radius: 4px;
    }

    .btn-edit {
        background-color: #007bff;
    }

    .btn-edit:hover {
        background-color: #0056b3;
    }

    .btn-delete {
        background-color: #dc3545;
    }

    .btn-delete:hover {
        background-color: #c82333;
    }

    .btn-add {
        background-color: rgb(108, 235, 137);
    }

    .btn-add:hover {
        background-color: #218838;
    }

    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 20px 0;
    }

    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 20px 0;
    }

    .pagination {
        display: flex;
    }

    .pagination a,
    .pagination span {
        display: inline-block;
        padding: 10px 15px;
        margin: 0 5px;
        text-decoration: none;
        color: #333;
        /* Dark grey for text */
        border: 1px solid #ccc;
        /* Light grey border */
        border-radius: 4px;
        background-color: #fff;
        /* White background */
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .pagination a:hover {
        background-color: #333;
        /* Dark grey hover background */
        color: white;
        /* White text on hover */
        border-color: #333;
        /* Match border to hover background */
    }

    .pagination .active {
        background-color: #666;
        /* Medium grey for active state */
        color: white;
        /* White text for active state */
        border-color: #666;
        /* Match border to active background */
        cursor: default;
    }

    .pagination a.disabled {
        pointer-events: none;
        color: #999;
        /* Light grey text for disabled */
        background-color: #f9f9f9;
        /* Slightly darker background for disabled */
        border-color: #ddd;
        /* Match disabled border to grey */
    }

    .pagination a:first-child,
    .pagination a:last-child {
        font-weight: bold;
    }

    @media (max-width: 600px) {

        .pagination a,
        .pagination span {
            padding: 8px 10px;
            font-size: 15px;
        }
    }
</style>
<p><?= count($_products) ?> record(s)</p>
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
            <th>Action</th>
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
                <td>
                    <a href="#" class="btn btn-edit"><i class="fas fa-tools"></i> Edit</a>
                    <form action="deleteProduct.php" method="POST" style="display: inline;">
                        <input type="hidden" name="product_id" value="<?= $product->product_id ?>">
                        <button type="submit" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this product?')">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="addProduct.php" class="btn btn-add"><i class="fas fa-plus-circle"></i> Add New Product</a>
<div class="pagination">
    <?= generateDynamicPagination($p, $sort, $dir); ?>
</div>


<?php include '../_foot.php'; ?>