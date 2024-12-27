<link rel="stylesheet" href="/css/product.css">
<script src="../js/admin_head.js"></script>

<?php
include '_admin_head.php';
require_once '../lib/SimplePager.php';

auth('Superadmin', 'Product Manager');

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
$params = [];
// Ensure $sort is valid
if (!in_array($sort, $valid_sort_columns)) {
    $sort = 'description';  // Default to description if the sort column is invalid
}

// Base query
$query = "SELECT * FROM product WHERE status NOT LIKE 'Discontinued'";

if (!empty($search)) {
    $search_query = " AND (product_id LIKE :search OR description LIKE :search OR category_name LIKE :search OR unit_price LIKE :search OR stock_quantity LIKE :search OR status LIKE :search OR dateAdded LIKE :search )";
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


if (isset($_POST['batchDlt']) && isset($_POST['selectedIDs']) && !empty($_POST['selectedIDs'])) {
    $selectedIDs = $_POST['selectedIDs']; // Array of selected product IDs

    foreach ($selectedIDs as $productId) {
        try {
            // Fetch required data for the product
            $stm = $_db->prepare(
                'SELECT p.category_id, p.stock_quantity, c.currentStock, c.minStock, c.StockAlert, c.category_photo
                 FROM product p 
                 JOIN category c ON p.category_id = c.category_id 
                 WHERE p.product_id = ?'
            );
            $stm->execute([$productId]);
            $product_data = $stm->fetch(PDO::FETCH_OBJ);

            if (!$product_data) {
                error_log("Product ID $productId not found.");
                continue;
            }

            // Extract product and category details
            $category_id = $product_data->category_id;
            $stock_quantity = $product_data->stock_quantity;
            $currentStock = $product_data->currentStock ?? 0;
            $minStock = $product_data->minStock ?? 0;
            $StockAlert = $product_data->StockAlert;
            $category_photo = $product_data->category_photo;

            // Delete associated photos
            $galleryStm = $_db->prepare('SELECT product_photo_id FROM product_photo WHERE product_id = ?');
            $galleryStm->execute([$productId]);
            $photos = $galleryStm->fetchAll(PDO::FETCH_OBJ);

            foreach ($photos as $photo) {
                $photo_path = "../product_gallery/{$photo->product_photo_id}";
                if (file_exists($photo_path)) {
                    unlink($photo_path);
                }
            }

            // Delete all photos for the product from the database
            $deletePhotosStm = $_db->prepare('DELETE FROM product_photo WHERE product_id = ?');
            $deletePhotosStm->execute([$productId]);

            // Update product status
            $updateProductStm = $_db->prepare('UPDATE product SET status = ?, stock_quantity = 0, dateDeleted = NOW() WHERE product_id = ?');
            $updateProductStm->execute(['Discontinued', $productId]);

            // Update category stock
            $newStockQuantity = max(0, $currentStock - $stock_quantity);
            $updateCatStockStm = $_db->prepare('UPDATE category SET currentStock = ? WHERE category_id = ?');
            $updateCatStockStm->execute([$newStockQuantity, $category_id]);

            // Check stock alert status
            $stock_alert = ($newStockQuantity < $minStock);
            $updateStockAlertStm = $_db->prepare('UPDATE category SET StockAlert = ? WHERE category_id = ?');
            $updateStockAlertStm->execute([$stock_alert, $category_id]);

            // Optionally notify Product Manager if stock alert is triggered
            if ($stock_alert) {
                $adminQuery = $_db->prepare('SELECT email FROM admin WHERE role = ?');
                $adminQuery->execute(['Product Manager']);
                $admin = $adminQuery->fetch(PDO::FETCH_OBJ);

                if ($admin) {
                    sendStockAlertEmail($admin->email, 'Low Stock Alert', 'Current stock is below the minimum threshold.', true, "../image/$category_photo");
                } else {
                    error_log("No Product Manager found for stock alert notification.");
                }
            }
        } catch (Exception $e) {
            error_log("Error processing Product ID $productId: " . $e->getMessage());
        }
    }
} else {
    error_log("No products selected for deletion.");
}
 

// ----------------------------------------------------------------------------

$_title = 'Product | Index';
?>


<div class="container">
    <a href="javascript:history.back()" class="back-button">&larr;</a>
    <div class="search-bar-container">
        <form action="product_index.php" method="GET">
            <input type="text" name="search" placeholder="Search by keyword..." value="<?= htmlspecialchars($search) ?>" />
            <!-- Hidden fields for sort, dir, and page -->
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
            <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
        </form>
    </div>

    <span class="total-record"><?= count($_products) ?> record(s)</span>

    <form action="product_index.php" method="POST">
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" />SelectAll</th>
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

                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($_products)): ?>
                    <tr>
                        <td colspan="8" class="no-records">No products found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($_products as $product): ?>
                        <!-- <tr onclick="window.location.href='product_details.php?product_id=<?= $product->product_id ?>'"> -->
                        <td><input type="checkbox" name="selectedIDs[]" value="<?= $product->product_id ?>" /></td>
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
                        <td>
                            <a href='product_details.php?product_id=<?= $product->product_id ?>' class='btn btn-view'><i class='fas fa-tools'></i>View</a>

                            <a href="deleteProduct.php?product_id=<?= $product->product_id ?>" class='btn btn-delete'>
                                <i class="fa-solid fa-xmark"></i>Delete
                            </a>
                        </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        With rows :
        <button type="submit" name="batchDlt" id="batchDlt" class="btn btn-delete" style="width: 140px; font-family:'Times New Roman', Times, serif;" onclick="return confirmDelete();">
            <i class="fa-solid fa-xmark"></i> Batch Delete
        </button>
    </form>

    <a href="addProduct.php" class="btn btn-add"><i class="fas fa-plus-circle"></i> Add New Product</a>
    <a href="deletedProduct.php" class="btn btn-trash"><i class="fa-solid fa-trash-can"></i>Trash</a>

    <div class="pagination">
        <?= generateDynamicPagination($p, $sort, $dir, $search); ?>
    </div>
</div>

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
            alert("Please select at least one product to delete.");
            return false;
        }
        return confirm('Are you sure you want to delete the selected products?');
    }
</script>