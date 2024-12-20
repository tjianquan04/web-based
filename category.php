<?php
require '_base.php';

//-----------------------------------------------------------------------------
// Fetch all categories or filtered categories based on search keyword
$name = req('name'); // Search keyword

if ($name) {
    // Fetch categories based on the search keyword
    $stm = $_db->prepare('SELECT * FROM category WHERE category_name LIKE ? OR sub_category LIKE ?');
    $stm->execute(["%$name%", "%$name%"]);
    $_categories = $stm->fetchAll();
    
} else {
    // Fetch all categories if no search keyword is provided
    $_categories = $_db->query('SELECT * FROM category')->fetchAll();
}

// ----------------------------------------------------------------------------
$_title = 'All Categories with Subcategories';
include '_head.php';
?>

<h1>Categories and Subcategories</h1>

<p>
    <a href="?">All Categories</a>
</p>

<div class="product-items">
    <?php if (count($_categories) > 0): ?>
        <?php foreach ($_categories as $cat): ?>
            <!-- single product -->
            <div class="product">
                <div class="product-content">
                    <div class="product-img">
                        <a href="menu.php?category_id=<?= $cat->category_id ?>"><img src="/image/<?= htmlspecialchars($cat->category_photo) ?>" class="category"></a>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-info-top">
                        <h2 class="sm-title"><a href="menu.php?category_name=<?= urlencode($cat->category_name) ?>"><?= htmlspecialchars($cat->category_name) ?></a></h2>
                    </div>
                    <a href="menu.php?category_id=<?= $cat->category_id ?>"><?= htmlspecialchars($cat->sub_category) ?></a>
                </div>
                <div class="off-info">
                    <h2 class="sm-title">25% off</h2>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No categories found matching your search criteria.</p>
    <?php endif; ?>
</div>

<?php
include '_foot.php';
?>
