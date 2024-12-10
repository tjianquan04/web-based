<?php
require '../_base.php';

// Get the product ID from the query string
$product_id = req('product_id');

// Fetch the product details
$stm = $_db->prepare('SELECT * FROM product WHERE product_id = ?');
$stm->execute([$product_id]);
$product = $stm->fetch();

// Fetch all photos for the product
$photo_stm = $_db->prepare('SELECT * FROM product_photo WHERE product_id = ?');
$photo_stm->execute([$product_id]);
$photos = $photo_stm->fetchAll();

// Fetch the subcategory from the database based on category_id
$subcategory_stm = $_db->prepare('SELECT sub_category FROM category WHERE category_id = ?');
$subcategory_stm->execute([$product->category_id]);  // Use $product->category_id
$subcategory = $subcategory_stm->fetch(PDO::FETCH_ASSOC); // Fetch as an associative array

if (!$product) {
    die('Product not found');
}



// Include the header
include '../_head.php';
?>

<div class="product-details">
    <h1>Product Details</h1>

    <div class="product-photos">
        <h3>Product Photos</h3>
        <?php if ($photos): ?>
            <div class="photo-gallery">
                <?php foreach ($photos as $photo): ?>
                    <img src="../image/<?= ($photo->photo) ?>" alt="Product Photo" class="product-photo">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No photos available for this product.</p>
        <?php endif; ?>
    </div>
    <div class="product-info">
        <h2><?= $product->description ?></h2>
        <p><strong>Product ID:</strong> <?= $product->product_id ?></p>
        <p><strong>Price:</strong> RM <?= number_format($product->unit_price, 2) ?></p>
        <p><strong>Stock Quantity:</strong> <?= $product->stock_quantity ?></p>

        <p><strong>Category:</strong> <?= $product->category_name ?></p>
        <p><strong>Subcategory:</strong> <?= $subcategory['sub_category'] ?: 'None' ?></p>
    </div>


</div>

<a href="viewProduct.php"><button>Back</button></a>

<?php
// Include the footer
include '../_foot.php';
?>