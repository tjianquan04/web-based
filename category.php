<link rel="stylesheet" href="/css/category.css">

<?php
require '_base.php';

//-----------------------------------------------------------------------------
// Fetch all categories or filtered categories based on search keyword
$name = req('name'); // Search keyword
$_categories = [];

if ($name) {
    $stm = $_db->prepare('SELECT * FROM category WHERE (category_name LIKE ? OR sub_category LIKE ?) AND status NOT LIKE "Discontinued"');
    $stm->execute(["%$name%", "%$name%"]);
    $_categories = $stm->fetchAll();
} else {
    $_categories = $_db->query('SELECT * FROM category WHERE status NOT LIKE "Discontinued"')->fetchAll();
}

// ----------------------------------------------------------------------------
$_title = 'All Categories with Subcategories';
include '_head.php';
?>


<?php if ($name): ?>
    <p>Showing results for: <strong><?= htmlspecialchars($name) ?></strong></p>
<?php endif; ?>

<div class="sidenav">
    <a href="#">All Categories</a>
    <?php foreach ($_categories as $c): ?>
        <a href="category.php?category_id=<?= $c->category_id ?>">
            <?= htmlspecialchars($c->category_name) ?>
            <?php if (!empty($c->sub_category)): ?>
                <br>- <?= htmlspecialchars($c->sub_category) ?>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="content">
    <div class="product-items">
        <?php if (count($_categories) > 0): ?>
            <?php foreach ($_categories as $cat): ?>
                <div class="product">
                    <div class="product-content">
                        <div class="product-img">
                            <a href="menu.php?category_id=<?= $cat->category_id ?>">
                                <img src="/image/<?= htmlspecialchars($cat->category_photo ?: 'default.png') ?>" class="category">
                            
                            
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-info-top">
                            <h2 class="sm-title">
                                <a href="menu.php?category_name=<?= urlencode($cat->category_name) ?>">
                                    <?= htmlspecialchars($cat->category_name) ?>
                                </a>
                            </h2>
                        </div>
                        <a href="menu.php?category_id=<?= $cat->category_id ?>">
                            <?= htmlspecialchars($cat->sub_category) ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No categories found matching your search criteria.</p>
        <?php endif; ?>
    </div>
</div>

<?php
include '_foot.php';
?>
