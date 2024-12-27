<?php
include '_admin_head.php';
require_once '../lib/SimplePager.php';

auth( 'Superadmin', 'Product Manager');

// Set page number
$page = req('page', 1);

// ----------------------------------------------------------------------------
// Fetching sorting and search parameters
$sort = req('sort', 'description'); // Default sorting field
$dir = req('dir', 'asc'); // Default sorting direction
$search = req('search', ''); // Capture the search term
$search_query = '';
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
if (!empty($search)) {
    $search_query = " AND (product_id LIKE :search OR description LIKE :search OR category_name LIKE :search OR unit_price LIKE :search OR stock_quantity LIKE :search OR status LIKE :search OR dateAdded LIKE :search OR dateDeleted LIKE :search )";
    $params[':search'] = '%' . $search . '%';  // Wildcard search term
    $query .= $search_query;
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


 <link rel="stylesheet" href="/css/product.css">


<div class="container">

<a href="product_index.php" class="back-button">&larr;</a>
<div class="search-bar-container">
                <form action="deletedProduct.php" method="GET">
                    <input type="text" name="search" placeholder="Search by keyword..." value="<?= htmlspecialchars($search) ?>" />

                    <!-- Hidden fields for sort, dir, and page -->
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                    <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
                    <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
                </form>
            </div>

    <span class="total-record"><?= count($_products) ?> record(s)</span>

    <table>
    <thead>
    <tr>
            <th onclick="window.location.href='?sort=product_id&dir=<?= $sort === 'product_id' && $dir === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>'">Product ID
            <?php if ($sort == 'product_id'): ?>
                                    <?php if ($dir == 'asc'): ?>
                                        <i class="fas fa-arrow-up arrow-right"></i>
                                    <?php else: ?>
                                        <i class="fas fa-arrow-down arrow-right"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-sort arrow-right"></i>
                                <?php endif; ?>

            </th>
            <th onclick="window.location.href='?sort=description&dir=<?= $sort === 'description' && $dir === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>'">Description
            <?php if ($sort == 'description'): ?>
                                    <?php if ($dir == 'asc'): ?>
                                        <i class="fas fa-arrow-up arrow-right"></i>
                                    <?php else: ?>
                                        <i class="fas fa-arrow-down arrow-right"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-sort arrow-right"></i>
                                <?php endif; ?>
            </th>
            <th onclick="window.location.href='?sort=category_name&dir=<?= $sort === 'category_name' && $dir === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>'">
                Category
                <?php if ($sort == 'category_name'): ?>
                                    <?php if ($dir == 'asc'): ?>
                                        <i class="fas fa-arrow-up arrow-right"></i>
                                    <?php else: ?>
                                        <i class="fas fa-arrow-down arrow-right"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-sort arrow-right"></i>
                                <?php endif; ?>
            </th>
            <th onclick="window.location.href='?sort=unit_price&dir=<?= $sort === 'unit_price' && $dir === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>'">Unit Price (RM)
            <?php if ($sort == 'unit_price'): ?>
                                    <?php if ($dir == 'asc'): ?>
                                        <i class="fas fa-arrow-up arrow-right"></i>
                                    <?php else: ?>
                                        <i class="fas fa-arrow-down arrow-right"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-sort arrow-right"></i>
                                <?php endif; ?>
            </th>
            <th onclick="window.location.href='?sort=stock_quantity&dir=<?= $sort === 'stock_quantity' && $dir === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>'">Stock
            <?php if ($sort == 'stock_quantity'): ?>
                                    <?php if ($dir == 'asc'): ?>
                                        <i class="fas fa-arrow-up arrow-right"></i>
                                    <?php else: ?>
                                        <i class="fas fa-arrow-down arrow-right"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-sort arrow-right"></i>
                                <?php endif; ?>
            </th>
            <th onclick="window.location.href='?sort=status&dir=<?= $sort === 'status' && $dir === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>'">Status
            <?php if ($sort == 'status'): ?>
                                    <?php if ($dir == 'asc'): ?>
                                        <i class="fas fa-arrow-up arrow-right"></i>
                                    <?php else: ?>
                                        <i class="fas fa-arrow-down arrow-right"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-sort arrow-right"></i>
                                <?php endif; ?>
            </th>
            <th onclick="window.location.href='?sort=dateAdded&dir=<?= $sort === 'dateAdded' && $dir === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>'">Date Added
            <?php if ($sort == 'dateAdded'): ?>
                                    <?php if ($dir == 'asc'): ?>
                                        <i class="fas fa-arrow-up arrow-right"></i>
                                    <?php else: ?>
                                        <i class="fas fa-arrow-down arrow-right"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-sort arrow-right"></i>
                                <?php endif; ?>
            </th>
            <th onclick="window.location.href='?sort=dateDeleted&dir=<?= $sort === 'dateAdded' && $dir === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>'">Date Deleted
            <?php if ($sort == 'dateAdded'): ?>
                                    <?php if ($dir == 'asc'): ?>
                                        <i class="fas fa-arrow-up arrow-right"></i>
                                    <?php else: ?>
                                        <i class="fas fa-arrow-down arrow-right"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-sort arrow-right"></i>
                                <?php endif; ?>
            </th>
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
                <td><?= number_format($product->unit_price, 2) ?></td>
                <td><?= $product->stock_quantity ?></td>
                <td><?= $product->status ?></td>
                <td><?= $product->dateAdded ?></td>
                <td><?= $product->dateDeleted ?></td>
             
            </tr>
        <?php endforeach; ?>
    </tbody>
    </table>

   
    <div class="pagination">
    <?= generateDynamicPagination($p, $sort, $dir,$search); ?>
    </div>
</div>



