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

// ----------------------------------------------------------------------------
$_title = $category ? "Products in $category" : "Products in Subcategory $category_id";
include '_head.php';
?>

<h1>Products</h1>

<?php if (count($product) > 0): ?>
   
            <button><a href="?sort=description&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>&category_name=<?= urlencode($category) ?>&category_id=<?= urlencode($category_id) ?>&name=<?= urlencode($name) ?>">Sort By Product Name</a>
            </button>
            <button><a href="?sort=product_id&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>&category_name=<?= urlencode($category) ?>&category_id=<?= urlencode($category_id) ?>&name=<?= urlencode($name) ?>">Sort By Product ID</a>
            </button>
            <button>
            <a href="?sort=unit_price&dir=<?= $dir === 'asc' ? 'desc' : 'asc' ?>&category_name=<?= urlencode($category) ?>&category_id=<?= urlencode($category_id) ?>&name=<?= urlencode($name) ?>">Sort By Price</a>
            </button>
  


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
            <div class="off-info">
                <h2 class="sm-title">25% off</h2>
            </div>
        </div>

    <?php endforeach ?>
</div>
   


<?php else: ?>
    <p>No products found matching your search criteria.</p>
<?php endif ?>

<?php
include '_foot.php';
?>
