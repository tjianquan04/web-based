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


$minPrice = req('minPrice', 0);     // Min price
$maxPrice = req('maxPrice', 10000); // Max price

$params = [];
$query = "SELECT * FROM product WHERE status NOT LIKE 'Discontinued' AND status NOT LIKE 'Inactive'";

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


// Add price range filter
$query .= " AND unit_price BETWEEN ? AND ?";
$params[] = $minPrice;
$params[] = $maxPrice;

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
$categoriesStm = $_db->prepare("SELECT * FROM category WHERE status NOT LIKE 'Discontinued'");
$categoriesStm->execute();
$categories = $categoriesStm->fetchAll();




// ----------------------------------------------------------------------------
$_title = $category ? "Products in $category" : "Products in Subcategory $category_id";
include '_head.php';
?>


<div class="sidenav">
    <h4>Products Category</h4>
    <a href="menu.php">All Products</a>
    <?php foreach ($categories as $c): ?>
        <a href="menu.php?category_id=<?= $c->category_id ?>">
            <?= $c->category_name ?>
            <?php if (!empty($c->sub_category)): ?>
                <br>-<?= $c->sub_category ?>
            <?php endif ?>
        </a>
    <?php endforeach; ?>
    <h4>Price Range</h4>
    <div class="wrapper">
    <div class="price-input">
        <div class="field">
          <span>Min</span>
          <input type="number" class="input-min" value="<?= $minPrice ?>">
        </div>
        <div class="separator">-</div>
        <div class="field">
          <span>Max</span>
          <input type="number" class="input-max" value="<?= $maxPrice ?>">
        </div>
      </div>
      <div class="slider">
        <div class="progress"></div>
      </div>
      <div class="range-input">
        <input type="range" class="range-min" min="0" max="10000" step="1" value="<?= $minPrice ?>">
        <input type="range" class="range-max" min="0" max="10000" step="1" value="<?= $maxPrice ?>">
      </div>
    </div>
    

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
                            <?php if ($p->status == 'OutOfStock'): ?>
                <div class="out-of-stock-overlay">
                    <span>Out of Stock</span>
                </div>
            <?php endif; ?>
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

<script>
const rangeInput = document.querySelectorAll(".range-input input"),
      priceInput = document.querySelectorAll(".price-input input"),
      range = document.querySelector(".slider .progress");
let priceGap = 1; // Set the gap to 1

// Function to update the progress bar based on the current price values
function updateProgressBar(minPrice, maxPrice) {
    const minRange = 0,
          maxRange = 10000;
    // Calculate the percentage for each slider and update the progress bar
    const leftPercentage = ((minPrice / maxRange) * 100);
    const rightPercentage = 100 - ((maxPrice / maxRange) * 100);

    range.style.left = `${leftPercentage}%`;
    range.style.right = `${rightPercentage}%`;
}

// Listen for input changes on number inputs
priceInput.forEach(input => {
    input.addEventListener("input", e => {
        let minPrice = parseInt(priceInput[0].value),
            maxPrice = parseInt(priceInput[1].value);

        // Ensure that the gap condition is respected
        if ((maxPrice - minPrice >= priceGap) && maxPrice <= rangeInput[1].max) {
            if (e.target.className === "input-min") {
                rangeInput[0].value = minPrice;
                updateProgressBar(minPrice, maxPrice); // Update progress on input change
            } else {
                rangeInput[1].value = maxPrice;
                updateProgressBar(minPrice, maxPrice); // Update progress on input change
            }
        }
    });
});

// Listen for input changes on range sliders
rangeInput.forEach(input => {
    input.addEventListener("input", e => {
        let minVal = parseInt(rangeInput[0].value),
            maxVal = parseInt(rangeInput[1].value);

        // Ensure that the gap condition is respected
        if ((maxVal - minVal) < priceGap) {
            if (e.target.className === "range-min") {
                rangeInput[0].value = maxVal - priceGap;  // Adjust min slider
            } else {
                rangeInput[1].value = minVal + priceGap;  // Adjust max slider
            }
        } else {
            priceInput[0].value = minVal;
            priceInput[1].value = maxVal;
            updateProgressBar(minVal, maxVal); // Update progress on range slider change
        }
    });
});

// Apply filter when the user presses "Enter" after entering values in the inputs
priceInput.forEach(input => {
    input.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            let minPrice = parseInt(priceInput[0].value),
                maxPrice = parseInt(priceInput[1].value);

            // Apply filter when "Enter" is pressed
            applyPriceFilter(minPrice, maxPrice);
        }
    });
});

rangeInput.forEach(input => {
    input.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            let minPrice = parseInt(priceInput[0].value),
                maxPrice = parseInt(priceInput[1].value);

            // Apply filter when "Enter" is pressed
            applyPriceFilter(minPrice, maxPrice);
        }
    });
});

// Function to apply the price filter by reloading the page with updated query params
function applyPriceFilter(minPrice, maxPrice) {
    window.location.href = `menu.php?minPrice=${minPrice}&maxPrice=${maxPrice}`;
}

</script>

