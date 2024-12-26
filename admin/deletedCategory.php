<?php
include '_admin_head.php';
require_once '../lib/SimplePager.php';

auth('Superadmin', 'Product Manager');

// Set page number
$page = req('page', 1);

// ----------------------------------------------------------------------------

$name = req('name'); // Search keyword
$sort = req('sort', 'category_name'); // Default sorting field
$dir = req('dir', 'asc'); // Default sorting direction

// Base query - Now filtering by status 'Discontinued'
$query = 'SELECT * FROM category WHERE status = "Discontinued"';

// Append search filters if provided
$params = [];
if ($name) {
    $query .= ' AND (category_name LIKE ? OR sub_category LIKE ?)';
    $params[] = "%$name%";
    $params[] = "%$name%";
}

// Append sorting
$query .= " ORDER BY $sort $dir";

// Use SimplePager for pagination
$p = new SimplePager(
    $query,      // Use the constructed query
    $params,     // Pass parameters for filtering
    10,          // Items per page
    $page        // Current page number
);

$_categories = $p->result;

echo "Item Count: " . $p->item_count;
echo "Limit: " . $p->limit;
echo "Page Count: " . $p->page_count;
// ---

// ----------------------------------------------------------------------------

$_title = 'Category Management';

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
<a href="viewCategory.php" class="back-button">&larr;</a>

    <p><?= count($_categories) ?> record(s) found</p> 

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th onclick="window.location.href='?sort=category_id&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>'">Category ID</th>
                <th onclick="window.location.href='?sort=category_id&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>'">Category Name</th>
                <th>Subcategory</th>
                <th>Date deleted</th>
              
            </tr>
        </thead>
        <tbody>
            <?php $numofcategories = 1; ?>
            <?php foreach ($_categories as $_category): ?>
                <tr onclick="window.location.href='category_details.php?category_id=<?= $_category->category_id ?>'">
                    <td><?= $numofcategories++ ?></td>
                    <td><?= $_category->category_id ?></td>
                    <td class="description-cell"><?= $_category->category_name ?></td>
                    <?php if (!empty($_category->sub_category)): ?>
                        <td><?= $_category->sub_category ?></td>
                    <?php else: ?>
                        <td>-</td>
                    <?php endif; ?>

                    <td><?= $_category->dateDeleted ?></td>

                   
                   
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <div class="pagination">
        <?= generateDynamicPagination($p, $sort, $dir); ?>
    </div>
</div>
