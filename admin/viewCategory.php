<script src="/js/admin_head.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
$query = "SELECT * FROM category WHERE status NOT LIKE 'Discontinued'";

// Append search filters
$params = [];
if (!empty($search)) {
    $search_query = " AND (category_id LIKE :search OR category_name LIKE :search OR sub_category LIKE :search OR currentStock LIKE :search OR Status LIKE :search )";
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
if (isset($_POST['batchDlt']) && isset($_POST['selectedIDs']) && !empty($_POST['selectedIDs'])) {
    $selectedIDs = $_POST['selectedIDs']; // Array of selected category IDs

    // Loop through selected category IDs for deletion
    foreach ($selectedIDs as $categoryID) {

        // Update category status to 'Discontinued'
        $update_category_stm = $_db->prepare('UPDATE category SET status = "Discontinued", dateDeleted = NOW(), currentStock = 0, StockAlert = false WHERE category_id = ?');
        $update_category_stm->execute([$categoryID]);

        // Fetch associated category photo and delete it
        $categoryStm = $_db->prepare('SELECT category_photo FROM category WHERE category_id = ?');
        $categoryStm->execute([$categoryID]);
        $category = $categoryStm->fetch(PDO::FETCH_OBJ);

        if ($category && isset($category->category_photo)) {
            $photo_path = '../image/' . $category->category_photo;
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
        }

        // Get all product photos associated with this category
        $galleryStm = $_db->prepare('SELECT product_id FROM product WHERE category_id = ?');
        $galleryStm->execute([$categoryID]);
        $products = $galleryStm->fetchAll(PDO::FETCH_OBJ);

        // Loop through each product to delete associated photos
        foreach ($products as $product) {
            $productId = $product->product_id;

            $productPhotosStm = $_db->prepare('SELECT product_photo_id FROM product_photo WHERE product_id = ?');
            $productPhotosStm->execute([$productId]);
            $photos = $productPhotosStm->fetchAll(PDO::FETCH_OBJ);

            // Delete associated product photos
            foreach ($photos as $photo) {
                $photo_path = '../product_gallery/' . $photo->product_photo_id;
                if (file_exists($photo_path)) {
                    unlink($photo_path);
                }

                // Delete photo from the database
                $deletePhotosStm = $_db->prepare('DELETE FROM product_photo WHERE product_photo_id = ?');
                $deletePhotosStm->execute([$photo->product_photo_id]);
            }

            // Update product status to 'Discontinued'
            $updateProductStm = $_db->prepare('UPDATE product SET status = "Discontinued", stock_quantity = 0, dateDeleted = NOW() WHERE product_id = ?');
            $updateProductStm->execute([$productId]);
        }

        // Update category status after deleting photos
        $stmt = $_db->prepare('UPDATE category SET status = ?, currentStock = 0, dateDeleted = NOW() WHERE category_id = ?');
        $stmt->execute(['Discontinued', $categoryID]);

        // Update all products associated with this category to 'Discontinued'
        $updateProductsStm = $_db->prepare('UPDATE product SET status = ?, stock_quantity = 0, dateDeleted = NOW() WHERE category_id = ?');
        $updateProductsStm->execute(['Discontinued', $categoryID]);
    }
} else {
    error_log("No categories selected for deletion.");
}


// ---

// ----------------------------------------------------------------------------

$_title = 'Category Management';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/product.css">
</head>
<body>

<div class="container">
    <a href="viewCategory.php" class="back-button">&larr;</a>
    <h2>Category Management</h2>
    <div class="search-bar-container">
        <form action="viewCategory.php" method="GET">
            <input type="text" name="search" placeholder="Search by keyword..." value="<?= htmlspecialchars($search) ?>" />

            <!-- Hidden fields for sort, dir, and page -->
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
            <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
        </form>
    </div>

    <p><?= count($_categories) ?> record(s)</p>
    <form action="viewCategory.php" method="POST">

        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" />SelectAll</th>
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
                    <th onclick="window.location.href='?sort=currentStock&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>'">Total Current Stock
                        <?php if ($sort == 'currentStock'): ?>
                            <?php if ($dir == 'asc'): ?>
                                <i class="fas fa-arrow-up arrow-right"></i>
                            <?php else: ?>
                                <i class="fas fa-arrow-down arrow-right"></i>
                            <?php endif; ?>
                        <?php else: ?>
                            <i class="fas fa-sort arrow-right"></i>
                        <?php endif; ?>
                    </th>
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_categories as $_category): ?>

                    <!-- <tr onclick="window.location.href='category_details.php?category_id=<?= $_category->category_id ?>'"> -->
                    <td><input type="checkbox" name="selectedIDs[]" value="<?= $_category->category_id ?>" /></td>

                    <td><?= $_category->category_id ?>
                    </td>
                    <td class="description-cell"><?= $_category->category_name ?>

                    </td>
                    <?php if (!empty($_category->sub_category)): ?>
                        <td><?= $_category->sub_category ?></td>
                    <?php else: ?>
                        <td>-</td>
                    <?php endif; ?>

                    <td><?= $_category->currentStock ?>
                    </td>
                    <td><?= $_category->Status ?>

                    </td>
                    <td>
                        <a href='category_details.php?category_id=<?= $_category->category_id ?>' class='btn btn-view'><i class='fas fa-tools'></i>View</a>

                        <a href='category_update.php?category_id=<?= $_category->category_id ?>' class='btn btn-edit'><i class='fas fa-tools'></i>Edit</a>
                        <!-- <a href="category_delete.php?category_id=<?= $_category->category_id ?>" class='btn btn-delete' onclick='return confirm("Are you sure you want to delete this Category?")'> -->
                        <a href="category_delete.php?category_id=<?= $_category->category_id ?>" class='btn btn-delete'>

                            <i class='fas fa-trash-alt'></i>Delete
                        </a>



                    </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        With rows :
        <button type="submit" name="batchDlt" id="batchDlt" class="btn btn-delete" style="width: 140px; font-family:'Times New Roman', Times, serif;" onclick="return confirmDelete();">
            <i class="fa-solid fa-xmark"></i> Batch Delete
        </button>

    </form>

    <a href="category_insert.php" class="btn btn-add"><i class="fa-solid fa-plus"></i>Add New Category</a>
    <a href="deletedCategory.php" class="btn btn-trash"><i class="fa-solid fa-trash-can"></i>Trash</a>

    <div class="pagination">
        <?= generateDynamicPagination($p, $sort, $dir, $search); ?>
    </div>
</div>
</body>
</html>


<script>
    // Select all checkboxes logic
    document.getElementById("selectAll").addEventListener("click", function() {
        let checkboxes = document.querySelectorAll('input[name="selectedIDs[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    // Function to prevent submission if no rows are selected
    function confirmDelete() {
        let checkboxes = document.querySelectorAll('input[name="selectedIDs[]"]:checked');
        if (checkboxes.length === 0) {
            alert("Please select at least one category to delete.");
            return false;
        }
        return confirm('Are you sure you want to delete the selected categories?');
    }
</script>