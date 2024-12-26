<link rel="stylesheet" href="/css/menu.css">
<link rel="stylesheet" href="/css/category.css">
<?php
require '_base.php';

// Capture query parameters
$category = req('category_name');  // Main category
$category_id = req('category_id'); // Subcategory
$name = req('name');               // Search keyword
$sort = req('sort', 'description'); // Sorting field
$dir = req('dir', 'asc');           // Sorting direction

$params = [];
$query = "SELECT * FROM product WHERE status NOT LIKE 'Discontinued'";

if ($category) {
    $query .= " AND category_name LIKE ?";
    $params[] = '%' . $category . '%';
}
if ($category_id) {
    $query .= " AND category_id LIKE ?";
    $params[] = '%' . $category_id . '%';
}
if ($name) {
    $query .= " AND description LIKE ?";
    $params[] = '%' . $name . '%';
}

$query .= " ORDER BY $sort $dir";

$stmt = $_db->prepare($query);
$stmt->execute($params);
$product = $stmt->fetchAll();

// Fetch default product photos
$productPhotos = [];
$photoQuery = "SELECT * FROM product_photo WHERE default_photo = 1";
$photoStmt = $_db->prepare($photoQuery);
$photoStmt->execute();
foreach ($photoStmt->fetchAll() as $photo) {
    $productPhotos[$photo->product_id] = $photo->product_photo_id;
}

// Fetch categories
$categoriesStm = $_db->prepare("SELECT * FROM category WHERE status NOT LIKE 'Discontinue'");
$categoriesStm->execute();
$categories = $categoriesStm->fetchAll();

// ----------------------------------------------------------------------------
$_title = $category ? "Products in $category" : "Products in Subcategory $category_id";
include '_head.php';
?>


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
        <?php if (count($product) > 0): ?>
            <?php foreach ($product as $p): ?>
                <!-- single product -->
                <div class="product">
                    <div class="product-content">
                        <div class="product-img">
                            <a href="product_card.php?product_id=<?= $p->product_id ?>">
                                <img src="/product_gallery/<?= $productPhotos[$p->product_id] ?? 'default.jpg' ?>" alt="Product Photo" class="category">
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-info-top">
                            <h2 class="sm-title">
                                <a href="product_card.php?product_id=<?= $p->product_id ?>"><?= $p->description ?></a>
                            </h2>
                        </div>
                        <?= $p->unit_price ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
        <?php else: ?>
    <p>No products found matching your search criteria.</p>
<?php endif ?>

    </div>
