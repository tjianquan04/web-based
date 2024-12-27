<?php
// Get the current page name dynamically
$current_page = basename($_SERVER['PHP_SELF']);

// Query products with 'LimitedEdition' status and dateAdded within 2 weeks from the current date
// $stm = $_db->prepare('
//     SELECT * FROM product 
//     WHERE status = "LimitedEdition" 
//     AND invalidDate >= DATE_SUB(CURDATE(), INTERVAL 2 WEEK)
// ');
// $stm->execute();
// $limited = $stm->fetchAll(PDO::FETCH_ASSOC);



// $stm = $_db->prepare('SELECT * FROM category WHERE StockAlert = 1');  // assuming StockAlert is an integer column
// $stm->execute();
// $alertItem = $stm->fetchAll(PDO::FETCH_ASSOC);

// $stm = $_db->prepare('SELECT * FROM product WHERE status = "OutOfStock"');  // assuming StockAlert is an integer column
// $stm->execute();
// $oosItem = $stm->fetchAll(PDO::FETCH_ASSOC);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="stylesheet" href="/css/main.css">
    <!-- <link rel="stylesheet" href="/css/menu.css">
    <link rel="stylesheet" href="/css/category.css">
    <link rel="stylesheet" href="/css/flash_msg.css"> -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cms.cdn.91app.com.my/cms/common/iconFonts/v1.1.13/nine1/nine1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<div id="info"><?= temp('info') ?></div>
<style>
    .dropdown-content {
        display: none;
        margin-top: 35px;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    .dropdown-content a {
        float: none;
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        text-align: left;
    }

    .dropdown-content a:hover {
        background-color: #ddd;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }
</style>

<body>
    <header>
        <div class="header-container">
            <div class="container-left">
                <a href="/index.php"><img class="logo-img" src="/image/logo.png"></a>
            </div>
            <div class="container-right">
                <div class="container-right1">
                    <ul>
                        <div class="dropdown">
                            <li class="right">
                                <a href="/user/login.php" style="text-decoration: none; color: inherit; cursor: pointer;">
                                    <i class="ico ico-user"></i>
                                </a>
                                <div class="dropdown-content">
                                    <a href="#">My Account</a>
                                    <a href="/order_record.php">My Purchases</a>
                                    <a href="/myWishlist.php">My Wishlist<i class="fa-solid fa-heart-circle-check"></i></a>
                                    <a href="#">Log Out</a>
                                </div>
                            </li>
                        </div>
                        <li class="right">
                            <a href="/cart.php"><i class="ico ico-shopping"></i></a>
                            <a href="/myWishlist.php"><i class="fa-solid fa-heart-circle-check"></i></a>
                        </li>
                    </ul>
                </div>
                <div class="container-right2">
                    <nav>
                        <ul>
                            <div class="dropdown">
                                <li class="left">
                                    What's HOT !
                                    <i class="ico ico-chevron-down"></i>
                                </li>
                                <div class="dropdown-content">
                                    <a href="menu.php?newAdded=$newAdded">NEW In!</a>
                                    <a href="menu.php?limited=$limited">Limited Time</a>
                                    <a href="menu.php?alertItem=$alertItem">End Soon!</a>
                                </div>
                            </div>
                            <li class="left">
                                <a href="menu.php">Boots Products</a>
                            </li>

                            <li class="left">
                                <a href="menu.php?oosItem=$oosItem">Back Stock Soon!</a>
                            </li>


                        </ul>
                    </nav>
                    <span class="nav-search-box">
                        <form class="search-form">
                            <div class="search-wrapper">
                                <form method="GET" action="<?= htmlspecialchars($current_page) ?>">
                                    <input class="search-input" type="text" placeholder="Search" name="name" id="search-input" autocapitalize="off" value="<?= isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '' ?>">
                                    <input type="hidden" name="sort" value="<?= $sort ?>">
                                    <input type="hidden" name="dir" value="<?= $dir ?>">
                                    <button type="submit" class="search-btn">
                                        <i class="ico ico-search"></i>
                                    </button>
                                </form>


                                <!-- <input class="search-input" type="search" placeholder="Search" name="search-input" id="search-input" autocapitalize="off">
                                <a class="search-btn">
                                    <i class="ico ico-search"></i> 
                                </a>-->
                            </div>
                        </form>
                    </span>
                </div>
            </div>
        </div>
    </header>