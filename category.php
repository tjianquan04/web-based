<?php
require '_base.php';
//-----------------------------------------------------------------------------
// Fetch all categories
$_categories = $_db->query('SELECT * FROM category')
    ->fetchAll();

// ----------------------------------------------------------------------------
$_title = 'All Categories with Subcategories';
include '_head.php';
?>

<h1>Categories and Subcategories</h1>

<p>
    <a href="?">All Categories</a>
</p>


<div class="product-items">
    <?php foreach ($_categories as $cat): ?>
        <!-- single product -->
        <div class="product">
            <div class="product-content">
                <div class="product-img">
                    <a href="menu.php?category_id=<?= $cat->category_id ?>"><img src="/image/<?= $cat->category_photo ?>" class="category"></a>
                </div>

            </div>
            <div class="product-info">
                <div class="product-info-top">
                    <h2 class="sm-title"><a href="menu.php?category_name=<?= $cat->category_name ?>"><?= $cat->category_name ?></a></h2>

                </div>
                <a href="menu.php?category_id=<?= $cat->category_id ?>"><?= $cat->sub_category ?></a>

            </div>
            <div class="off-info">
                <h2 class="sm-title">25% off</h2>
            </div>
        </div>

    <?php endforeach ?>
</div>


<?php
include '_foot.php';
?>