<?php
include '_admin_head.php';

$category_id = req('category_id');

// Fetch the product details
$stm = $_db->prepare('SELECT * FROM category WHERE category_id = ?');
$stm->execute([$category_id]);
$category = $stm->fetch();


// Set the page title
$_title = 'Category Details';

?>
<link rel="stylesheet" href="../css/flash_msg.css">
<link rel="stylesheet" href="../css/detailsForm.css">
<script src="../js/main.js"></script>

<div class="container">
<a href="javascript:history.back()" class="back-button">&larr;</a>


    <div class="category-photo">
        <img src="../image/<?= htmlspecialchars($category->category_photo) ?>" alt="Category Photo" class="category-photo">
    </div>
    <div class="category-info">
    <table>
    <tr>
        <td class="label"><i class="fas fa-layer-group"></i> Category Name</td>
        <td class="value"><?= htmlspecialchars($category->category_name) ?></td>
    </tr>
    <tr>
        <td class="label"><i class="fas fa-sitemap"></i> Subcategory</td>
        <td class="value"><?= htmlspecialchars($category->sub_category ?: 'N/A') ?></td>
    </tr>
    <tr>
        <td class="label"><i class="fas fa-boxes"></i> Current Stock</td>
        <td class="value"><?= htmlspecialchars($category->currentStock) ?></td>
    </tr>
    <tr>
        <td class="label"><i class="fas fa-chart-line"></i> Minimum Stock</td>
        <td class="value"><?= htmlspecialchars($category->minStock) ?></td>
    </tr>
    <tr>
        <td class="label"><i class="fas fa-info-circle"></i> Status</td>
        <td class="value"><?= $category->Status ?></td>
    </tr>
</table>




    </div>

</div>