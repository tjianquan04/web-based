<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/flash_msg.css">
    <link rel="stylesheet" href="/css/category.css">
    <link rel="stylesheet" href="https://cms.cdn.91app.com.my/cms/common/iconFonts/v1.1.13/nine1/nine1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<div id="info"><?= temp('info') ?></div>

<body>
    <header>
        <div class="header-container">
            <div class="container-left">
                <a href="/index.php"><img src="/image/logo.png"></a>
            </div>
            <div class="container-right">
                <div class="container-right1">
                    <ul>
                        <li class="right">
                            <a href="/user/login.php" style="text-decoration: none; color: inherit; cursor: pointer;">
                                <i class="ico ico-user"></i>
                            </a>
                        </li>
                        <li class="right">
                            <i class="ico ico-shopping"></i>
                        </li>
                    </ul>
                </div>
                <div class="container-right2">
                    <nav>
                        <ul>
                            <li class="left">
                                What's Hot
                                <i class="ico ico-chevron-down"></i>
                            </li>
                            <li class="left">
                                Men
                                <i class="ico ico-chevron-down"></i>
                            </li>
                            <li class="left">
                                Women
                                <i class="ico ico-chevron-down"></i>
                            </li>
                            <li class="left">
                                Sports
                                <i class="ico ico-chevron-down"></i>
                            </li>
                        </ul>
                    </nav>
                    <span class="nav-search-box">
                        <form class="search-form">
                            <div class="search-wrapper">
                                <form method="GET" action="menu.php">
                                    <input class="search-input" type="text" placeholder="Search" name="name" id="search-input" autocapitalize="off" value="<?= isset($_GET['name']) ? $_GET['name'] : '' ?>">
                                    <a class="search-btn">
                                    <i class="ico ico-search"></i> 
                                </a>
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