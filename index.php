<link rel="stylesheet" href="/css/index.css">


<?php
require '_base.php';

$stm = $_db->prepare(
    'UPDATE order_record
     SET order_status = "delivered"
     WHERE order_status = "shipping" 
     AND TIMESTAMPDIFF(DAY, order_date, NOW()) > 3'
);
$result = $stm->execute();


// Get the member_id securely, e.g., from session
// session_start();
// $member_id = $_SESSION['member_id'] ?? null;
// if (!$member_id) {
//     echo json_encode(['success' => false, 'message' => 'User not logged in.']);
//     exit;
// }


$wishlist_item=[];
$member_id = null;
if(!empty($_SESSION['user'])){

  $member = $_SESSION['user'];
  authMember($member);
  $member_id =  $member->member_id;

}


$wishlist_stm = $_db->prepare("
    SELECT p.* 
    FROM wishlist w
    JOIN product p ON w.product_id = p.product_id
    WHERE w.member_id = ? ");
$wishlist_stm->execute([$member_id]);
$wishlist_item = $wishlist_stm->fetchAll();



$updateStatusStm = $_db->prepare("
    UPDATE product 
    SET status = 'Inactive' 
    WHERE status LIKE 'LimitedEdition' 
    AND invalidDate = CURDATE()
");
$updateStatusStm->execute();

// // Check if the product is already in the wishlist for the logged-in user
// $member_id = $_SESSION['member_id'];  // Assuming the user is logged in


$stm = $_db->prepare("SELECT * FROM product WHERE status LIKE 'LimitedEdition' AND invalidDate >= DATE_SUB(CURDATE(), INTERVAL 2 WEEK)");
$stm->execute();
$feature_product = $stm->fetchAll();


$stm = $_db->prepare(
  "SELECT 
      p.product_id, 
      p.description,
      p.unit_price,
      COUNT(oi.product_id) AS frequency
  FROM 
      orderitem oi
  JOIN 
      product p ON oi.product_id = p.product_id
  GROUP BY 
      oi.product_id
  ORDER BY 
      frequency DESC
  LIMIT 5"
);
$stm->execute();
$topSales_product = $stm->fetchAll(PDO::FETCH_OBJ); 

// Use wishlist items if available; otherwise, use featured products
$product = !empty($wishlist_item) ? $wishlist_item : $feature_product;
// Fetch default product photos
$productPhotos = [];
$photoQuery = "SELECT * FROM product_photo WHERE default_photo = 1";
$photoStmt = $_db->prepare($photoQuery);
$photoStmt->execute();
foreach ($photoStmt->fetchAll() as $photo) {
    $productPhotos[$photo->product_id] = $photo->product_photo_id;
}

$_title = 'Home';

include '_head.php';
?>

    <div class="slideshow-container">

<div class="mySlides fade">
  <div class="numbertext">1 / 3</div>
  <img src="image/slideshow1.png" style="width:100%" class="slidesshow">
 
</div>

<div class="mySlides fade">
  <div class="numbertext">2 / 3</div>
  <img src="image/slideshow.jpg" style="width:100%" class="slidesshow">
  
</div>

<div class="mySlides fade">
  <div class="numbertext">3 / 3</div>
  <img src="image\slideshow3.webp" style="width:100%" class="slidesshow">
  
</div>




<a class="prev" onclick="plusSlides(-1)">❮</a>
<a class="next" onclick="plusSlides(1)">❯</a>

</div>
<br>

<div style="text-align:center">
  <span class="dot" onclick="currentSlide(1)"></span> 
  <span class="dot" onclick="currentSlide(2)"></span> 
  <span class="dot" onclick="currentSlide(3)"></span> 
</div>

<!-- Featured Products -->
<section class="featured-products">
<?php if (!empty($wishlist_item)): ?>
    <h2 class="productTitle">Recommended For You <i class="fa-regular fa-heart"></i></h2>
<?php else: ?>
    <h2 class="productTitle">Grab Yours Now</h2>
<?php endif; ?>



    <div class="productGrid">
      
        <?php foreach ($product as $p): ?>
            <div class="productCard">
                <img src="/product_gallery/<?= htmlspecialchars($productPhotos[$p->product_id] ?? 'default.jpg') ?>" 
                     alt="<?= htmlspecialchars($p->description) ?>" class="category">
                <h3><?= htmlspecialchars($p->description) ?></h3>
                <p>RM <?= htmlspecialchars($p->unit_price) ?></p>
                <button>
                    <a href="/product_card.php?product_id=<?= htmlspecialchars($p->product_id) ?>">View</a>
                </button>
            </div>
        <?php endforeach; ?>
    </div>
    <br><h2 class="productTitle">Boots Top Sales</h2>
    <div class="productGrid">
      <?php foreach ($topSales_product as $p): ?>
          <div class="productCard">
              <img src="/product_gallery/<?= htmlspecialchars($productPhotos[$p->product_id] ?? 'default.jpg') ?>" 
                   alt="<?= htmlspecialchars($p->description) ?>" class="category">
              <h3><?= htmlspecialchars($p->description) ?></h3>
              <p>RM <?= htmlspecialchars($p->unit_price) ?></p>
              <button>
                  <a href="/product_card.php?product_id=<?= htmlspecialchars($p->product_id) ?>">View</a>
              </button>
          </div>
      <?php endforeach; ?>
  </div>
</section>

<?php
include '_foot.php';
?>
<script>
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  if (n > slides.length) {slideIndex = 1}    
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " active";
}
</script>
