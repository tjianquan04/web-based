<link rel="stylesheet" href="/css/menu.css">

<?php
require '_base.php';

// Capture query parameters
$category = req('category_name');  // Main category
$category_id = req('category_id'); // Subcategory
$name = req('name');               // Search keyword
$sort = req('sort', 'description'); // Sorting field
$dir = req('dir', 'asc');           // Sorting direction

// Fetch products
$product = fetchProducts($_db, $category, $category_id, $name, $sort, $dir);

$categoriesStm = $_db->prepare("SELECT * FROM category");
$categoriesStm->execute();
$categories = $categoriesStm->fetchAll();
// ----------------------------------------------------------------------------
$_title = $category ? "Products in $category" : "Products in Subcategory $category_id";
include '_head.php';
?>



<?php if (count($product) > 0): ?>


    <div class="sidenav">
        <a href="menu.php">All Products</a>
        <?php foreach ($categories as $c): ?>
            <a href="menu.php?category_id=<?= $c->category_id ?>">
                <?= $c->category_name ?>
                <?php if (!empty($c->sub_category)): ?>
                    <br>-<?= $c->sub_category ?>
                <?php endif ?>
            </a>
        <?php endforeach; ?>


        

    </div>
    <div class="menu-content">
        <div class="product-items">
            <?php foreach ($product as $p): ?>
                <!-- single product -->
                <div class="product">
                    <div class="product-content">
                        <div class="product-img">
                            <a href="product_card.php?product_id=<?= $p->product_id ?>"><img src="/product_gallery/<?= $p->product_photo_id ?>" alt="Product Photo" class="category">

                            </a>
                        </div>

                    </div>
                    <div class="product-info">
                        <div class="product-info-top">
                            <h2 class="sm-title"><a href="product_card.php?product_id=<?= $p->product_id ?>"><?= $p->description ?></a></h2>

                        </div>
                        <?= $p->unit_price ?>

                    </div>

                </div>

            <?php endforeach ?>
        </div>
    </div>


<?php else: ?>
    <p>No products found matching your search criteria.</p>
<?php endif ?>



<script>

</script>