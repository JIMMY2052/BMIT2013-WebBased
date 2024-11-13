<?php

$stm = $_db->prepare('SELECT * FROM users WHERE email = ?');
$stm->execute([$_user->email]);
$user = $stm->fetch();
$_SESSION['user'] = $user;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/img/icon&logo/motto.png">
    <title><?= $title ?? 'Untitled' ?></title>
    <link rel="stylesheet" href="/css/adminSideBar.css">
    <link rel="stylesheet" href="/css/customerTable.css">
    <link rel="stylesheet" href="/css/viewCust.css">
    <link rel="stylesheet" href="/css/orderListing.css">
    <link rel="stylesheet" href="/css/productListing.css">
    <link rel="stylesheet" href="/css/viewProduct.css">
    <link rel="stylesheet" href="/css/adminProfile.css">
    <link rel="stylesheet" href="/css/adminChangePassword.css">
    <link rel="stylesheet" href="/css/addProduct.css">
    <link rel="stylesheet" href="/css/orderDetail.css">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/editProduct.css">
    <link rel="stylesheet" href="/css/orderGameListing.css">
    <link rel="stylesheet" href="/css/acc.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    <div id="info"><?= temp('info') ?></div>
    <nav id="sidebar">
        <ul>
            <li>
                <span class="logo">MOTTO</span>
                <button onclick=toggleSidebar() id="toggle-btn">
                    <img src="/img/adminIcon/keyDoubleArrow.svg">
                </button>
            </li>
            <li class="<?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
                <a href="dashboard.php">
                    <img src="/img/adminIcon/dashboard.svg">
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <button onclick=toggleSubMenu(this) class="dropdown-btn">
                    <img src="/img/adminIcon/person.svg">
                    <span>Customers</span>
                    <img src="/img/adminIcon/keyArrowDown.svg">
                </button>
                <ul class="sub-menu">
                    <div>
                        <li><a href="customerListing.php">Customer Listing</a></li>
                        <li><a href="blockList.php">Blocked Customer List </a></li>
                    </div>
                </ul>
            </li>
            <li>
                <button onclick=toggleSubMenu(this) class="dropdown-btn">
                    <img src="/img/adminIcon/product.svg">
                    <span>Product</span>
                    <img src="/img/adminIcon/keyArrowDown.svg">
                </button>
                <ul class="sub-menu">
                    <div>
                        <li><a href="productListing.php">Product Listing</a></li>
                        <li><a href="addProduct.php">Add Product</a></li>
                    </div>
                </ul>
            </li>

            <li>
                <button onclick=toggleSubMenu(this) class="dropdown-btn">
                    <img src="/img/adminIcon/order.svg">
                    <span>Orders</span>
                    <img src="/img/adminIcon/keyArrowDown.svg">
                </button>
                <ul class="sub-menu">
                    <div>
                        <li><a href="orderListing.php">All Order Listing</a></li>
                        <li><a href="gameOrderListing.php">Game Order List</a></li>
                        <li><a href="accessoriesOrderListing.php">Accessories Order List</a></li>
                    </div>
                </ul>
            </li>
            <?php if ($_user->role == 'superAdmin'): ?>
                <li>
                <button onclick=toggleSubMenu(this) class="dropdown-btn">
                    <img src="/img/adminIcon/admin.svg">
                    <span>Admin</span>
                    <img src="/img/adminIcon/keyArrowDown.svg">
                </button>
                <ul class="sub-menu">
                    <div>
                        <li><a href="adminListing.php">Admin Listing</a></li>
                        <li><a href="adminBlockList.php">Admin Blocked List</a></li>
                    </div>
                </ul>
            </li>
        <?php endif; ?>
            <li>
                <button onclick=toggleSubMenu(this) class="dropdown-btn">
                    <img src="/img/adminIcon/person.svg">
                    <span>Account</span>
                    <img src="/img/adminIcon/keyArrowDown.svg">
                </button>
                <ul class="sub-menu">
                    <div>
                        <li><a href="adminProfile.php">Profile Detail</a></li>
                        <li><a href="changePassword.php">Change Password</a></li>
                    </div>
                </ul>
            </li>
        </ul>


        <?php if ($_user): ?>
            <div class="admin-profile">
                <img src="/img/userPic/<?= isset($_user->profile_picture) ? $_user->profile_picture : 'defaultUser.png'; ?>" />
                <div class="user-info">
                    <span class="admin-name"><?= strtoupper($_user->username); ?></span><br />
                    <span class="admin-role"><?= $_user->role == 'superAdmin' ? 'Super Admin' : 'Admin' ?></span>
                </div>

                <a href="/pages/customer/id/logout.php" class="logout-btn">Logout</a>
            </div>
        <?php endif; ?>

    </nav>
    <main>