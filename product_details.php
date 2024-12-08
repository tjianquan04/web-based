<?php
require '_base.php';

// Get the product ID from the query string
$product_id = req('product_id');

// Fetch the product details
$stm = $_db->prepare('SELECT * FROM product WHERE product_id = ?');
$stm->execute([$product_id]);
$product = $stm->fetch();

// If the product does not exist, display an error message
if (!$product) {
    $_title = 'Product Not Found';
    include '_head.php';
    echo "<p>Product not found.</p>";
    include '_foot.php';
    exit;
}

// Fetch all photos for the product
$photo_stm = $_db->prepare('SELECT * FROM product_photo WHERE product_id = ?');
$photo_stm->execute([$product_id]);
$photos = $photo_stm->fetchAll();

// Display the product details
$_title = $product->description;
include '_head.php';
?>

<h1><?= htmlspecialchars($product->description) ?></h1>
<p><strong>Price:</strong> RM <?= htmlspecialchars($product->unit_price) ?></p>
<p><strong>Category:</strong> <?= htmlspecialchars($product->category_name) ?></p>

<h2>Photos</h2>
<div class="product-photos">
    <?php if ($photos): ?>
        <?php foreach ($photos as $pho): ?>
            <img src="/image/<?= htmlspecialchars($pho->photo) ?>" alt="<?= $product->description ?>" class="category" >
        <?php endforeach; ?>
    <?php else: ?>
        <p>No photos available for this product.</p>
    <?php endif; ?>
</div>

<?php
include '_foot.php';
?>
