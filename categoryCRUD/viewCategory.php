<?php
include '../_base.php';
require_once '../lib/SimplePager.php';

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
include '../_head.php';
?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background-color: #f9f9f9;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        background-color: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    th,
    td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #4CAF50;
        color: white;
        font-weight: bold;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    a.btn {
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 5px;
        color: white;
        font-size: 14px;
        margin: 0 5px;
    }

    a.btn-edit {
        background-color: #007BFF;
    }

    a.btn-delete {
        background-color: #DC3545;
    }

    button {
        background-color: #28a745;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }

    button:hover {
        background-color: #218838;
    }

    .popup-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .popup {
        width: 90%;
        max-width: 500px;
        margin: 100px auto;
        padding: 20px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        position: relative;
    }

    .popup h3 {
        margin-top: 0;
        font-size: 20px;
        color: #333;
        border-bottom: 2px solid #4CAF50;
        padding-bottom: 10px;
    }

    .popup-close {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 24px;
        color: #aaa;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .popup-close:hover {
        color: #000;
    }

    .popup img {
        display: block;
        margin: 10px auto;
        border-radius: 10px;
        max-height: 200px;
        max-width: 100%;
        object-fit: cover;
    }

    .category-photo {
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 5px;
    }

    p strong {
        color: #555;
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

<?php
include '../_foot.php';
?>