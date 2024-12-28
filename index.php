
<?php
require '_base.php';

// $stm = $_db->prepare(
//     'UPDATE order_record
//      SET order_status = "delivered"
//      WHERE order_status = "shipping" 
//      AND TIMESTAMPDIFF(DAY, order_date, NOW()) > 3'
// );
// $result = $stm->execute();


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

<style>
* {box-sizing: border-box}
.mySlides {display: none}
img {vertical-align: middle;}

/* Slideshow container */
.slideshow-container {
  max-width: 1500px;
  position: relative;
  margin: auto;
}
img{
  padding-top: 15px;
    width: 300px;
    height: 300px;
}

/* Next & previous buttons */
.prev, .next {
  cursor: pointer;
  position: absolute;
  top: 50%;
  width: auto;
  padding: 16px;
  margin-top: -22px;
  color: white;
  font-weight: bold;
  font-size: 18px;
  transition: 0.6s ease;
  border-radius: 0 3px 3px 0;
  user-select: none;
}

/* Position the "next button" to the right */
.next {
  right: 0;
  border-radius: 3px 0 0 3px;
}

/* On hover, add a black background color with a little bit see-through */
.prev:hover, .next:hover {
  background-color: rgba(0,0,0,0.8);
}

/* Caption text */
.text {
  color: #f2f2f2;
  font-size: 15px;
  padding: 8px 12px;
  position: absolute;
  bottom: 8px;
  width: 100%;
  text-align: center;
}

/* Number text (1/3 etc) */
.numbertext {
  color: #f2f2f2;
  font-size: 12px;
  padding: 8px 12px;
  position: absolute;
  top: 0;
}

/* The dots/bullets/indicators */
.dot {
  cursor: pointer;
  height: 15px;
  width: 15px;
  margin: 0 2px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
  transition: background-color 0.6s ease;
}

.active, .dot:hover {
  background-color: #717171;
}

/* Fading animation */
.fade {
  animation-name: fade;
  animation-duration: 1.5s;
}

@keyframes fade {
  from {opacity: .4} 
  to {opacity: 1}
}

/* On smaller screens, decrease text size */
@media only screen and (max-width: 300px) {
  .prev, .next,.text {font-size: 11px}
}

  /* Featured Products */
  .featured-products {
    padding: 20px;
    text-align: center;
  }
  
  h2{
    font-family: 'Times New Roman', Times, serif;
  }
  .product-grid {
    display: flex;
  flex-wrap: wrap; 
    gap: 20px;
    justify-content: center;
  }
  
  .product-card {
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 10px;
    text-align: center;
    width: 200px;
  }
  
  .product-card img {
    width: 100%;
    height: auto;
    border-radius: 5px;
  }

  .product-card a{
  text-decoration: none;
  color: #f2f2f2;
}
  .product-card button {
    margin-top: 10px;
    padding: 10px;
    background-color: #333;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
  }
  
  .product-card button:hover {
    background-color: #555;
  }
  
</style>
<div id="info"><?= temp('info') ?></div>

    <div class="slideshow-container">

<div class="mySlides fade">
  <div class="numbertext">1 / 3</div>
  <img src="image/slideshow1.png" style="width:100%">
  <div class="text">Caption Text</div>
</div>

<div class="mySlides fade">
  <div class="numbertext">2 / 3</div>
  <img src="image/slideshow.jpg" style="width:100%">
  <div class="text">Caption Two</div>
</div>

<div class="mySlides fade">
  <div class="numbertext">3 / 3</div>
  <img src="product_gallery\638514769327200002c.jpg" style="width:100%">
  <div class="text">Caption Three</div>
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
    <h2>Recommended For You <i class="fa-regular fa-heart"></i></h2>
<?php else: ?>
    <h2>Grab Yours Now</h2>
<?php endif; ?>



    <div class="product-grid">
      
        <?php foreach ($product as $p): ?>
            <div class="product-card">
                <img src="/product_gallery/<?= htmlspecialchars($productPhotos[$p->product_id] ?? 'default.jpg') ?>" 
                     alt="<?= htmlspecialchars($p->description) ?>" class="category">
                <h3><?= htmlspecialchars($p->description) ?></h3>
                <p>RM <?= htmlspecialchars($p->unit_price) ?></p>
                <button>
                    <a href="product_card.php?product_id=<?= htmlspecialchars($p->product_id) ?>">View</a>
                </button>
            </div>
        <?php endforeach; ?>
    </div>
    <br><h2>Boots Top Sales</h2>
    <div class="product-grid">
      <?php foreach ($topSales_product as $p): ?>
          <div class="product-card">
              <img src="/product_gallery/<?= htmlspecialchars($productPhotos[$p->product_id] ?? 'default.jpg') ?>" 
                   alt="<?= htmlspecialchars($p->description) ?>" class="category">
              <h3><?= htmlspecialchars($p->description) ?></h3>
              <p>RM <?= htmlspecialchars($p->unit_price) ?></p>
              <button>
                  <a href="product_card.php?product_id=<?= htmlspecialchars($p->product_id) ?>">View</a>
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
