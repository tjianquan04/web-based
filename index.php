<?php
require '_base.php';

$_title = 'Home';

include '_head.php';
?>

<h1>home</h1>    
    <a href ="/about_me.php">About Me</a> 
    <a href ="/user/user_profile.php">Profile</a>
    <a href ="/category.php">All Category</a>
    <a href ="/menu.php">All Products</a>
    <a href ="/productCRUD/viewProduct.php">Product Management</a>
    <a href ="/categoryCRUD/viewCategory.php">Category Management</a>

<?php
include '_foot.php';
