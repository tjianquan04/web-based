<?php
require '_base.php';

$_title = 'Home';

include '_head.php';
?>

<h1>home</h1>
    <a href ="/user/register.php">register</a>
    <a href ="/category.php">All Category</a>
    <a href ="/menu.php">All Products</a>
    <a href ="/productCRUD/viewProduct.php">Product Management</a>
    <a href ="/categoryCRUD/viewCategory.php">Category Management</a>

<?php
include '_foot.php';
