<?php
include '_admin_head.php';
require_once '../lib/SimplePager.php';

auth('Superadmin', 'Product Manager');

// Set page number
$page = req('page', 1);

// ----------------------------------------------------------------------------

$sort = req('sort', 'category_name'); // Default sorting field
$dir = req('dir', 'asc'); // Default sorting direction
$search = req('search', ''); // Capture the search term
$search_query = '';
// Base query
$query = "SELECT * FROM category WHERE status LIKE 'Discontinued'";

// Append search filters
$params = [];
if (!empty($search)) {
    $search_query = " AND (category_id LIKE :search OR category_name LIKE :search OR sub_category LIKE :search OR Status LIKE :search )";
    $params[':search'] = '%' . $search . '%';  // Wildcard search term
    $query .= $search_query;
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



<link rel="stylesheet" href="/css/product.css">

<div class="container">
<a href="viewCategory.php" class="back-button">&larr;</a>
<div class="search-bar-container">
        <form action="viewCategory.php" method="GET">
            <input type="text" name="search" placeholder="Search by keyword..." value="<?= htmlspecialchars($search) ?>" />

            <!-- Hidden fields for sort, dir, and page -->
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
            <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
        </form>
    </div>
    <p><?= count($_categories) ?> record(s) found</p> 

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th onclick="window.location.href='?sort=category_id&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>'">Category ID
                    <?php if ($sort == 'category_id'): ?>
                        <?php if ($dir == 'asc'): ?>
                            <i class="fas fa-arrow-up arrow-right"></i>
                        <?php else: ?>
                            <i class="fas fa-arrow-down arrow-right"></i>
                        <?php endif; ?>
                    <?php else: ?>
                        <i class="fas fa-sort arrow-right"></i>
                    <?php endif; ?>
                </th>
                <th onclick="window.location.href='?sort=category_id&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>'">Category Name
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
                <th>Subcategory</th>
          
                <th onclick="window.location.href='?sort=Status&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>'">Status
                    <?php if ($sort == 'Status'): ?>
                        <?php if ($dir == 'asc'): ?>
                            <i class="fas fa-arrow-up arrow-right"></i>
                        <?php else: ?>
                            <i class="fas fa-arrow-down arrow-right"></i>
                        <?php endif; ?>
                    <?php else: ?>
                        <i class="fas fa-sort arrow-right"></i>
                    <?php endif; ?>
                </th>
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
            
                    <td><?= $_category->Status ?>
                    <td><?= $_category->dateDeleted ?></td>

                   
                   
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

    <div class="pagination">
        <?= generateDynamicPagination($p, $sort, $dir,$search); ?>
    </div>
</div>
