<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/id.css">
    <link rel="stylesheet" href="/css/account.css">
    <link rel="stylesheet" href="/css/product.css">
    <link rel="stylesheet" href="/css/productDetail.css">
    <link rel="icon" href="/img/icon&logo/motto.png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <script src="/js/app.js"></script>
</head>

<body>
    <header id="main-header" class="flex z-9 items-center w-full fixed h-4rem h-8rem site-head">
        <div class="w-full">
            <div class="w-80">
                <nav class="relative flex site-max justify-between items-center translateY-20">
                    <div class="inline-flex relative">
                        <a href="/index.php" class="title font-medium">
                            Motto
                        </a>
                    </div>
                    <div class="flex flex-col" style="transform: translateY(30%);">
                        <ul class="flex justify-center">
                            <li class="cta header-li1">
                                <a href="/pages/product/productListingUser.php?type=Game" class="uline mx-20 font-medium">Games</a>
                            </li>
                            <li class="cta header-li2">
                                <a href="/pages/product/productListingUser.php?type=Accessories" class="uline mx-20 font-medium">Accessories</a>
                            </li>
                        </ul>
                        <div class="relative flex search-section w-full" style="z-index: 100;">
                            <div class="w-full relative justify-center items-center flex c-search">
                                <img src="/img/icon&logo/search.svg" height="20px" width="20px" alt="search" class="">
                                <form action="\pages\product\searchResults.php" method="get">
                                    <?= html_search('search', 'placeholder="Search"') ?>
                                </form>
                                <input id="burger-checkbox" class="burger-checkbox" type="checkbox">
                                <label for="burger-checkbox" class="burger" onclick="openNav()">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        <div id="myNav" class="overlay">
                            <div class="overlay-content">
                                <a href="/pages/product/productListingUser.php?type=Game">GAME</a>
                                <a href="/pages/product/productListingUser.php?type=Accessories">ACCESSORIES</a>
                            </div>

                        </div>
                    </div>
                    <div class="s:block">
                        <div class="flex items-center">
                            <?php
                            $profilePicture = isset($_user->profile_picture) ? $_user->profile_picture : 'default-pic.jpg';

                            if (isset($_SESSION['user'])) {
                                echo "
                                <div>
                                    <div class='profile-dropdown flex items-center'>
                                        <img src='/img/userPic/$profilePicture' class='profile-picture h-2.5vh flex'>
                                        <div class='font-medium'>$_user->username</div>
                                    </div>
                                    <div class='dropdown-content'>
                                        <a href='/pages/customer/account/profile.php'>My Profile</a>
                                        <a href='/pages/customer/account/transaction.php'>My Orders</a>
                                        <a href='/pages/customer/account/wishlist.php'>Wishlist</a>
                                        <a href='/pages/customer/cart.php'>My Cart</a>
                                        <a href='/pages/customer/id/logout.php'>Logout</a>
                                    </div>
                                </div>";
                                
                            
                            } else {
                                echo '<a href="/pages/customer/id/login.php" class="flex items-center"><img src="/img/icon&logo/profile-fill.svg" alt="profile" class="h-2.5vh flex"></a>';
                            }
                            ?>

                        </div>
                    </div>
                </nav>
            </div>
        </div>


    </header>
    <main class="main flex justify-center">