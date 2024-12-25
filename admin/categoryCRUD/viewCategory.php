<?php
include '../_admin_head.php';
require_once '../../lib/SimplePager.php';


// Set page number
$page = req('page', 1);


// ----------------------------------------------------------------------------

$name = req('name'); // Search keyword
$sort = req('sort', 'category_name'); // Default sorting field
$dir = req('dir', 'asc'); // Default sorting direction

// Base query
$query = 'SELECT * FROM category';

// Append search filters
$params = [];
if ($name) {
    $query .= ' WHERE category_name LIKE ? OR sub_category LIKE ?';
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

<link rel="stylesheet" href="/css/admin_management.css">


<div class="container">

<p><?= count($_categories) ?> record(s)</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th onclick="window.location.href='?sort=category_id&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>'">Category ID</th>
            <th onclick="window.location.href='?sort=category_id&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>'">Category Name</th>
            <th>Subcategory</th>
            <th onclick="window.location.href='?sort=currentStock&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>&name=<?= urlencode($name) ?>'">Total Current Stock</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $numofcategories = 1; ?>
        <?php foreach ($_categories as $_category): ?>
            <!-- <tr onclick="showCategoryDetails('<?= $_category->category_id ?>', '<?= $_category->category_name ?>', '<?= $_category->sub_category ?>', '<?= $_category->category_photo ?>')"> -->
            <tr onclick="window.location.href='category_details.php?category_id=<?= $_category->category_id ?>'">
                <td><?= $numofcategories++ ?></td>
                <td><?= $_category->category_id ?></td>
                <td class="description-cell"><?= $_category->category_name ?></td>
                <?php if (!empty($_category->sub_category)): ?>
                    <td><?= $_category->sub_category ?></td>
                <?php else: ?>
                    <td>-</td>
                <?php endif; ?>

                <td><?= $_category->currentStock ?></td>

                <td>
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

<a href="category_insert.php"><button>Add new category</button></a>
<div class="pagination">
    <?= generateDynamicPagination($p, $sort, $dir); ?>
</div>

<!-- Popup Modal -->
<div class="popup-overlay" id="categoryPopup">
    <div class="popup">
        <!-- Close Button (X) -->
        <span class="popup-close" onclick="closeCategoryPopup()">&times;</span>

        <h3>Category Details</h3>
        <p><strong>Category ID:</strong> <span id="popupCategoryID"></span></p>
        <p><strong>Category Name:</strong> <span id="popupCategoryName"></span></p>
        <p><strong>Subcategory:</strong> <span id="popupSubCategory"></span></p>
        <p><strong>Photo:</strong></p>
        <img src="" id="popupCategoryPhoto" alt="Category Photo" class="category-photo" style="width: 150px; height: auto;">
    </div>
</div>

</div>
<!-- <script>
    function showCategoryDetails(categoryID, categoryName, subCategory, categoryPhoto) {
        // Set the values in the popup
        document.getElementById('popupCategoryID').textContent = categoryID;
        document.getElementById('popupCategoryName').textContent = categoryName;
        document.getElementById('popupSubCategory').textContent = subCategory;
        document.getElementById('popupCategoryPhoto').src = "../image/" + categoryPhoto;

        // Show the popup
        document.getElementById('categoryPopup').style.display = 'block';
    }

    function closeCategoryPopup() {
        // Hide the popup
        document.getElementById('categoryPopup').style.display = 'none';
    }
</script> -->

