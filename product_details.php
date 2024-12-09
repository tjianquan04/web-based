<?php
require '_base.php';

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



// Display the product details
$_title = $product->description;
include '_head.php';
?>


<link rel="stylesheet" type="text/css" media="screen" href="/css/menu.css" />
<script src="/js/menu.js" defer></script>
<script src="/js/slider.js" defer></script>
<script src="/js/cart.js" defer></script>

<div class="container">
    <section class="main">
        <div class="default gallery">
            <div class="main-img">
                <!-- Display main product images -->
                <?php if ($photos): ?>
                    <?php foreach ($photos as $index => $pho): ?>
                        <img
                            class="<?= $index === 0 ? 'active' : '' ?>"
                            src="/image/<?= $pho->photo ?>"
                            alt="<?= $product->description ?>" />
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No photos available for this product.</p>
                <?php endif; ?>
            </div>
            <div class="thumb-list">
                <!-- Display thumbnails -->
                <?php if ($photos): ?>
                    <?php foreach ($photos as $index => $pho): ?>
                        <div class="<?= $index === 0 ? 'active' : '' ?>">
                            <img
                                src="/image/<?= $pho->photo ?>"
                                alt="<?= $product->description ?>" />
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <h2><a href="/index.php">Boots.Do</a></h2>
            <h3 class="product-name"><?= $product->description ?> 
        </h3>
            
            <p class="product-desc">
                <a href="/menu.php?category_name=<?= $product->category_name ?>">
                    <?= $product->category_name ?>
                </a>
                <br>
                <?php if($subcategory) :?>
                    <a href="/menu.php?category_id=<?= $product->category_id ?>">
                    <?= $subcategory['sub_category'] ?>
                </a>
                    <?php endif; ?>
                
            </p>
            <div class="price-info">
                <div class="price">
                    <span class="current-price">RM <?= $product->unit_price ?></span>
                   
            </div>
            <div class="add-to-cart-container">
                <div class="counter">
                    <button class="minus">
                        <i class="fa-solid fa-minus"></i>
                    </button>
                    <span class="count">0</span>
                    <button class="plus">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                <button class="add-to-cart">
                    <span>
                        <i class="ico ico-shopping"></i>
                    </span>
                    <span>Add to cart</span>
                </button>
            </div>
        </div>
    </section>
</div>

<?php
include '_foot.php';
?>