<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="https://cms.cdn.91app.com.my/cms/common/iconFonts/v1.1.13/nine1/nine1.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

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
                            <i class="ico ico-user"></i>
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
                                <input class="search-input" type="search" placeholder="Search" name="search-input" id="search-input" autocapitalize="off">
                                <a class="search-btn">
                                    <i class="ico ico-search"></i>
                                </a>
                            </div>
                        </form>
                    </span>
                </div>
            </div>
        </div>
    </header>

    <h1>home</h1>
    <a href ="/register.php">register</a>
