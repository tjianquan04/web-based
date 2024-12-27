<?php
// Get the current page name dynamically
$current_page = basename($_SERVER['PHP_SELF']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="stylesheet" href="/css/main.css">

    <link rel="stylesheet" href="/css/flash_msg.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cms.cdn.91app.com.my/cms/common/iconFonts/v1.1.13/nine1/nine1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<div id="info"><?= temp('info') ?></div>
<style>

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
                                <a href="/user/login.php" style="text-decoration: none; color: inherit; cursor: pointer; font-size:25px;">
                                    <i class="ico ico-user"></i>
                                </a>
                                <div class="dropdown-content">
                                    <a href="/user/user_profile.php">My Account</a>
                                    <a href="/order_record.php">My Purchases</a>
                                    <a href="/myWishlist.php">My Wishlist<i class="fa-solid fa-heart-circle-check"></i></a>
                                    <a href="#" onclick="logoutFunction(event);" class="btn btn-logout">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>

                                    <form id="logoutForm" action="" method="POST" style="display:none;">
                                        <button type="submit" name="logout"></button>
                                    </form>

                                    <?php
                                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
                                        // Call the logout function to handle session and redirection
                                        logout('login.php');
                                    }
                                    ?>


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

<script>
function logoutFunction(event) {
    // Prevent the default action of the link (which is to reload the page)
    event.preventDefault();

    // Submit the hidden form to trigger the POST request
    document.getElementById('logoutForm').submit();
}
</script>