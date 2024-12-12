<?php
include '../_base.php';

// ----------------------------------------------------------------------------

$_categories = $_db->query('SELECT * FROM category')->fetchAll();

// ----------------------------------------------------------------------------

$_title = 'Category Management';
include '../_head.php';
?>

<style>
    .popup {
        width: 80%;
        max-width: 600px;
        height: auto;
        padding: 20px;
        background-color: white;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
        border-radius: 10px;
        position: relative;
    }

    .popup-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    .popup-close {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 25px;
        color: #aaa;
        cursor: pointer;
    }

    tr {
        cursor: pointer;
    }

    tr:hover {
        background-color: #f0f0f0;
    }
</style>

<p><?= count($_categories) ?> record(s)</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Category ID</th>
            <th>Category Name</th>
            <th>Subcategory</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $numofcategories = 1; ?>
        <?php foreach ($_categories as $_category): ?>
            <tr onclick="showCategoryDetails('<?= $_category->category_id ?>', '<?= $_category->category_name ?>', '<?= $_category->sub_category ?>', '<?= $_category->category_photo ?>')">
                <td><?= $numofcategories++ ?></td>
                <td><?= $_category->category_id ?></td>
                <td class="description-cell"><?= $_category->category_name ?></td>
                <td><?= $_category->sub_category ?></td>
                <td>
                    <a href='#' class='btn btn-edit'><i class='fas fa-tools'></i>Edit</a>
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

<script>
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
</script>

<?php
include '../_foot.php';
?>
