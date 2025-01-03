<?php
require '_base.php';


$member_id = null;

if (!empty($_SESSION) && isset($_SESSION['user'])) {
    $member = $_SESSION['user'];

    $member_id = $member->member_id;
}





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


$wishlist_check_stm = $_db->prepare('SELECT 1 FROM wishlist WHERE member_id = ? AND product_id = ?');
$wishlist_check_stm->execute([$member_id, $product_id]);
$is_in_wishlist = $wishlist_check_stm->fetchColumn(); // This returns 1 if the product is in the wishlist

$rating_stm = $_db->prepare(
    'SELECT *
    FROM item_rating
    INNER JOIN orderitem
    on item_rating.orderItem_id = orderitem.orderItem_id
    INNER JOIN order_record
    on orderItem.order_id = order_record.order_id
    INNER JOIN member
    on order_record.member_id = member.member_id
    WHERE orderitem.product_id = ?'
);
$rating_stm->execute([$product_id]);
$arr_ratings = $rating_stm->fetchAll();

// Display the product details
$_title = $product->description;
include '_head.php';
?>

<!-- Include necessary CSS and JavaScript -->
<link rel="stylesheet" type="text/css" media="screen" href="/css/menu.css" />
<script src="/js/menu.js" defer></script>
<script src="/js/cart.js" defer></script>
<script src="/js/slider.js" defer></script>
<script src="/js/addtocart.js"></script>

<div class="container">
    <a href="javascript:history.back()" class="back-button">
        <i class="fa-solid fa-arrow-left-long"></i></a>

    <section class="main">
        <div class="default gallery">
            <div class="main-img">
                <!-- Display main product images -->
                <?php foreach ($photos as $index => $pho): ?>
                    <img
                        class="<?= $index === 0 ? 'active' : '' ?>"
                        src="/product_gallery/<?= $pho->product_photo_id ?>"
                        alt="<?= $product->description ?>" />
                <?php endforeach; ?>
            </div>
            <div class="thumb-list">
                <?php foreach ($photos as $index => $pho): ?>
                    <div class="<?= $index === 0 ? 'active' : '' ?>">
                        <img
                            src="/product_gallery/<?= $pho->product_photo_id ?>"
                            alt="<?= $product->description ?>" />
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="content">
            <h2><a href="/index.php">Boots.Do</a></h2>
            <h3 class="product-name"><?= $product->description ?>

                <?php if ($member_id !== null): ?>
                    <div class="wishlist">
                        <!-- Check if the product is in the wishlist and set the appropriate icon class -->
                        <i
                            class="wishlist-icon fa-sharp <?= $is_in_wishlist ? 'fa-solid' : 'fa-regular' ?> fa-heart"
                            data-product-id="<?= $product->product_id ?>"
                            aria-label="Add to wishlist"></i>
                    </div>
                <?php endif; ?>

            </h3>

            <p class="product-desc">
                <a href="/menu.php?category_name=<?= $product->category_name ?>">
                    <?= $product->category_name ?>
                </a>
                <br>
                <?php if ($subcategory) : ?>
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
                    <!-- Counter with minus and plus buttons, always visible -->
                    <div class="counter">
                        <button type="button" class="minus" data-product-id="<?= $product->product_id ?>" disabled
                            <?php if ($product->status == 'OutOfStock'): ?>
                            disabled
                            <?php endif; ?>>
                            <i class="fa-solid fa-minus"></i>
                        </button>

                        <span class="count" data-product-id="<?= $product->product_id ?>">1</span>

                        <button type="button" class="plus" data-product-id="<?= $product->product_id ?>"
                            <?php if ($product->status == 'OutOfStock'): ?>
                            disabled
                            <?php endif; ?>>
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>

                    <!-- Add to cart button -->
                    <button type="button" class="add-to-cart" id="addToCart-btn" data-product-id="<?= $product->product_id ?> " data-member-id="<?= $member_id ?? '' ?>"
                        <?php if ($product->status == 'OutOfStock'): ?>
                        disabled
                        <?php endif; ?>>
                        <span>
                            <i class="ico ico-shopping"></i>
                        </span>
                        <span>
                            <?php echo ($product->status == 'OutOfStock') ? 'Out of Stock' : 'Add to cart'; ?>
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </section>
</div>

<div class="product-review-container">
    <table class="product-review-table">
        <tr>
            <td colspan="2" class="product-review-title">Product Ratings</td>
        </tr>
        <?php foreach ($arr_ratings as $rating):
        ?>
            <tr>
                <td colspan="2" class="product-review-hrline">
                    <hr>
                </td>
            </tr>
            <tr class="product-review-row">
                <td class="product-review-memberPhoto">
                    <label class="reviewmemberPhoto"><img src="/photos/<?= $rating->profile_photo ?>"></label>
                </td>
                <td>
                    <?= $rating->name ?><br>
                    <?php if (($rating->rating_star) == "Amazing") { ?>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    <?php } else if (($rating->rating_star) == "Good") { ?>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                    <?php } else if (($rating->rating_star) == "Fair") { ?>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                    <?php } else if (($rating->rating_star) == "Poor") { ?>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                    <?php } else { ?>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <span style="font-size: 15px; color: rgb(100,100,100);"><?= $rating->rating_date ?></span><br><br>
                    <span style="font-size: 15px; color: rgb(100,100,100);">Quality:</span> <?= $rating->rating_star ?><br><br>
                    <?= $rating->comment ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</div>

<?php
include '_foot.php';
?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Attach click event listeners to all wishlist icons
        document.querySelectorAll('.wishlist-icon').forEach(icon => {
            icon.addEventListener('click', () => {
                const productId = icon.getAttribute('data-product-id');
                const action = icon.classList.contains('fa-solid') ? 'remove' : 'add';

                // Send AJAX request to backend to add/remove from wishlist
                fetch('/wishlist-action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            action: action
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            icon.classList.toggle('fa-regular');
                            icon.classList.toggle('fa-solid');
                            icon.classList.toggle('fa-heart-circle-check');
                        } else {
                            console.error('Error: ', data.message);
                            alert('There was an issue with adding/removing from wishlist: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('AJAX Error:', error);
                        alert('An error occurred.');
                    });
            });
        });
    });
</script>