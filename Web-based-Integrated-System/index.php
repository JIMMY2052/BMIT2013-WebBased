<?php
require 'include/_base.php';
ob_start();
require 'include/_head.php';

if ($_user != null) {
    if ($_user->role == 'admin' || $_user->role == 'superAdmin') {
        redirect('/pages/admin/dashboard.php');
    }
}

$fields = [
    'id' => 'Product ID',
    'name' => 'Name',
    'description' => 'Description',
    'type' => 'Type',
    'category_type' => 'Category',
    'price' => 'Price',
    'stock_quantity' => 'Stock Quantity',
    'release_date' => 'Release Date',
    'is_active' => 'Active',
    'last_date_operate' => 'Last Operated'
];

// Database connection
$conn = new mysqli("localhost", "root", "", "motto");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$arr = $conn->query("SELECT * FROM product ORDER BY release_date DESC LIMIT 4");

$arrTopNewRelease = $conn->query("
    SELECT pi.product_id, p.name, p.price, p.release_date, pi.url, p.type
    FROM product p 
    LEFT JOIN product_image_video pi ON p.id = pi.product_id AND pi.type = 'photo'
    WHERE p.type = 'Game'
    ORDER BY p.release_date DESC
    LIMIT 4
");

$arrTopSellingQuery = $conn->query("
    SELECT i.product_id, p.name, p.price, pi.url, p.type,SUM(i.quantity) AS total_quantity_sold
    FROM item i
    JOIN product p ON i.product_id = p.id
    LEFT JOIN product_image_video pi ON p.id = pi.product_id AND pi.type = 'photo'
    WHERE p.type = 'Game' 
    GROUP BY i.product_id, p.name, p.price, pi.url
    ORDER BY total_quantity_sold DESC
    LIMIT 4
");

$arrTopFreeQuery = $conn->query("
    SELECT i.product_id, p.name, p.price, p.type,pi.url, SUM(i.quantity) AS total_quantity_sold
    FROM item i
    JOIN product p ON i.product_id = p.id
    LEFT JOIN product_image_video pi ON p.id = pi.product_id AND pi.type = 'photo'
    WHERE p.price = 0 AND p.type = 'Game'
    GROUP BY i.product_id, p.name, p.price, pi.url
    ORDER BY total_quantity_sold DESC
    LIMIT 4
");

$comingSoonQuery = $conn->query("
    SELECT pi.product_id, p.name, p.price, p.type, p.release_date, pi.url
    FROM product p 
    JOIN product_image_video pi ON p.id = pi.product_id AND pi.type = 'photo'
    WHERE release_date > curdate() AND p.type = 'Game'
    ORDER BY p.release_date
    LIMIT 4
    ");

$topProducts = array();
$newRelease = array();
$comingSoon = array();
$topFree = array();

if ($arrTopSellingQuery->num_rows > 0 || $arrTopFreeQuery->num_rows > 0) {
    // Fetch the result into the array
    while ($row = $arrTopSellingQuery->fetch_assoc()) {
        // Process image URLs for each product
        $urls = $row['url'];  // Fetch the 'url' column
        $imageArray = explode(',', $urls);  // Split the URL string by commas
        $first_image_url = trim($imageArray[0]);  // Get the first image URL
        if (empty($urls)) {
            $first_image_url = "noImageFound.jpg";
        }

        // Store product information
        $topProducts[] = array(
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'url' => $first_image_url,  // Use the first image URL
            'total_quantity_sold' => $row['total_quantity_sold']
        );
    }

    while ($row = $arrTopFreeQuery->fetch_assoc()) {
        // Process image URLs for each product
        $urls = $row['url'];  // Fetch the 'url' column
        $imageArray = explode(',', $urls);  // Split the URL string by commas
        $first_image_url = trim($imageArray[0]);  // Get the first image URL
        if (empty($urls)) {
            $first_image_url = "noImageFound.jpg";
        }
        // Store product information
        $topFree[] = array(
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'url' => $first_image_url,  // Use the first image URL
            'total_quantity_sold' => $row['total_quantity_sold']
        );
    }

    // You can print or process $topProducts further as needed
}
if ($arrTopNewRelease->num_rows > 0 || $comingSoonQuery->num_rows > 0) {
    while ($row = $arrTopNewRelease->fetch_assoc()) {
        // Process image URLs for each product
        $urls = $row['url'];  // Fetch the 'url' column

        $imageArray = explode(',', $urls);  // Split the URL string by commas
        $first_image_url = trim($imageArray[0]);  // Get the first image URL
        if (empty($urls)) {
            $first_image_url = "noImageFound.jpg";
        }

        // Store product information
        $newRelease[] = array(
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'url' => $first_image_url,  // Use the first image URL
            'release_date' => $row['release_date']
        );
    }

    while ($row = $comingSoonQuery->fetch_assoc()) {
        // Process image URLs for each product
        $urls = $row['url'];  // Fetch the 'url' column
        $imageArray = explode(',', $urls);  // Split the URL string by commas
        $first_image_url = trim($imageArray[0]);  // Get the first image URL
        if (empty($urls)) {
            $first_image_url = "noImageFound.jpg";
        }
        // Store product information
        $comingSoon[] = array(
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'url' => $first_image_url,  // Use the first image URL
            'release_date' => $row['release_date']
        );
    }
} else {
    echo "No products found.";
}
?>

<section>
    <div class="title flex items-end justify-between">
        <h2>
            <div class="title-size" style="color: #0000ff;">TOP NEW</div>
            <div class="title-size">RELEASE</div>
        </h2>
        <a href="/pages/product/productListingUser.php?type=Game" class="check-out-more relative flex items-center">
            <div class="icon_cont flex justify-center items-center">
                <span class="icon">🡪</span>
            </div>
            <span class="text_button relative inline-block">Check out more</span>
        </a>
    </div>
    <div class="c-card">
        <div class="u-card flex">
            <?php
            if (!empty($newRelease)) {
                foreach ($newRelease as $product) {
                    echo "<div class='card pointer'>";
                    echo "<a href='pages\product\productDetail.php?id=".$product['product_id']."'>";
                    echo "<div class='flex justify-center'>";
                    echo "<img src='/img/game/" . $product['url'] . "' alt='" . $product['name'] . "'>";
                    echo "</div>";
                    echo "<div class='card-title'>";
                    echo "<h3>"
                        . $product['name'] .
                        "</h3>";
                    if ($product['price'] == 0) {
                        echo "<h4> FREE </h4>";
                    } else
                        echo "<h4>MYR "
                            . $product['price'] .
                            "</h4>";
                    echo "</div>";
                    echo "</a>";
                    echo "</div>";
                }
            } else {
                echo "<p class = 'empty-product-list flex items-center justify-center'>No Top New Release Products Found.</p>";
            }
            ?>
        </div>
    </div>
</section>

<section>
    <div class="title flex items-end justify-between" style="margin-top: 8vw;">
        <h3>
            <div class="trusted-size">(TRUSTED BY VISIONARIES)</div>
        </h3>
    </div>
    <div class="logos" style="margin-bottom: 4vw;">
        <div class="logos-slide">
            <img src="img/icon&logo/steam-logo.png" alt="">
            <img src="img/icon&logo/riot-logo.png" alt="">
            <img src="img/icon&logo/Epic-Games-Logo.png" alt="">
            <img src="img/icon&logo/rog-logo.png" alt="">
            <img src="img/icon&logo/Nintendo_Switch_Logo.svg.png" alt="">
            <img src="img/icon&logo/Xbox-logo.png" alt="">
            <img src="img/icon&logo/Mastercard-logo-black-and-white.png" alt="">
            <img src="img/icon&logo/google-logo-black-transparent.png" alt="">
            <img src="img/icon&logo/razer-logo.png" alt="">
        </div>
        <div class="logos-slide">
            <img src="img/icon&logo/steam-logo.png" alt="">
            <img src="img/icon&logo/riot-logo.png" alt="">
            <img src="img/icon&logo/Epic-Games-Logo.png" alt="">
            <img src="img/icon&logo/rog-logo.png" alt="">
            <img src="img/icon&logo/Nintendo_Switch_Logo.svg.png" alt="">
            <img src="img/icon&logo/Xbox-logo.png" alt="">
            <img src="img/icon&logo/Mastercard-logo-black-and-white.png" alt="">
            <img src="img/icon&logo/google-logo-black-transparent.png" alt="">
            <img src="img/icon&logo/razer-logo.png" alt="">
        </div>
    </div>
</section>

<section>
    <div class="title flex items-end justify-between">
        <h2>
            <div class="title-size">ACCESSORIES</div>
        </h2>
    </div>
    <div class="c-card">
        <div class="u-card flex justify-center">
            <a href="/pages/product/productListingUser.php?type=Accessories">
        
                <div class="accessories-card flex justify-even pointer">
                    <div class="accessories-ps5 flex justify-center items-center">
                        <img src="img/accessories/ps5-slim-disc-console-featured-hardware-image-block-02-en-15nov23.png" alt="">
                        <h3>
                            PlayStation
                        </h3>
                    </div>
                    <div class="accessories-switch flex justify-center items-center">
                        <img src="img/accessories/nintendo_switch_v2_nredblue.png" alt="">
                        <h3>
                            Nintendo Switch
                        </h3>
                    </div>
                    <div class="accessories-allay flex justify-center items-center">
                        <img src="img/accessories/1ed4d6eebcc1f570b2115d346ba1a823_2.png" alt="">
                        <h3>
                            ROG Allay
                        </h3>
                    </div>
                </div>
            </a>
        </div>
    </div>
</section>

<section>
    <div class="title flex items-end justify-between">
        <h2>
            <div class="title-size">TOP</div>
            <div class="title-size" style="color: #0000ff;">SELLING</div>
        </h2>
        <a href="/pages/product/productListingUser.php?type=Game&sort=price_asc" class="check-out-more relative flex items-center">
            <div class="icon_cont flex justify-center items-center">
                <span class="icon">🡪</span>
            </div>
            <span class="text_button relative inline-block">Check out more</span>
        </a>
    </div>
    <div class="c-card">
        <div class="u-card flex">

            <?php
            if (!empty($topProducts)) {
                foreach ($topProducts as $product) {
                    echo "<div class='card pointer'>";
                    echo "<a href='pages\product\productDetail.php?id=".$product['product_id']."'>";
                    echo "<div class='flex justify-center'>";
                    echo "<img src='/img/game/" . $product['url'] . "' alt='" . $product['name'] . "'>";
                    echo "</div>";
                    echo "<div class='card-title'>";
                    echo "<h3>"
                        . $product['name'] .
                        "</h3>";
                    if ($product['price'] == 0) {
                        echo "<h4> FREE </h4>";
                    } else
                        echo "<h4>MYR "
                            . $product['price'] .
                            "</h4>";
                    echo "</div>";
                    echo "</a>";
                    echo "</div>";
                }
            } else {
                echo "<p class = 'empty-product-list flex items-center justify-center'>No Top Selling Games Found</p>";
            }
            ?>
        </div>
    </div>
</section>

<section>
    <div class="motto">
        <div class="motto-slide">
            <div class="spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="spinner"></div>
            <div class="motto-word">
                Motto
            </div>
        </div>
        <div class="motto-slide">
            <div class="spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="spinner"></div>
            <div class="motto-word">
                Motto
            </div>
        </div>
    </div>
    <div class="motto" style="margin-bottom: 4vw;">
        <div class="sec-motto-slide">
            <div class="sec-spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="sec-spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="sec-spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="sec-spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="sec-spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="sec-spinner"></div>
            <div class="motto-word">
                Motto
            </div>
        </div>
        <div class="sec-motto-slide">
            <div class="sec-spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="sec-spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="sec-spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="sec-spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="sec-spinner"></div>
            <div class="motto-word">
                Motto
            </div>
            <div class="sec-spinner"></div>
            <div class="motto-word">
                Motto
            </div>
        </div>
    </div>
</section>

<section>
    <div class="title flex items-end justify-between">
        <h2>
            <div class="title-size">TOP <span style="color: #0000ff;">FREE</span> GAMES</div>
        </h2>
        <a href="/pages/product/productListingUser.php?type=Game" class="check-out-more relative flex items-center">
            <div class="icon_cont flex justify-center items-center">
                <span class="icon">🡪</span>
            </div>
            <span class="text_button relative inline-block">Check out more</span>
        </a>
    </div>
    <div class="c-card">
        <div class="u-card flex">
            <?php
            if (!empty($topFree)) {
                foreach ($topFree as $product) {
                    echo "<div class='card pointer'>";
                    echo "<a href='pages\product\productDetail.php?id=".$product['product_id']."'>";
                    echo "<div class='flex justify-center'>";
                    echo "<img src='/img/game/" . $product['url'] . "' alt='" . $product['name'] . "'>";
                    echo "</div>";
                    echo "<div class='card-title'>";
                    echo "<h3>"
                        . $product['name'] .
                        "</h3>";
                    if ($product['price'] == 0) {
                        echo "<h4> FREE </h4>";
                    } else
                        echo "<h4>MYR "
                            . $product['price'] .
                            "</h4>";
                    echo "</div>";
                    echo "</a>";
                    echo "</div>";
                }
            } else {
                echo "<p class = 'empty-product-list flex items-center justify-center'>No Top Free Games Found</p>";
            }
            ?>
        </div>
    </div>
</section>

<section>
    <div class="title flex items-end justify-between">
        <h2>
            <div class="title-size">COMING SOON</div>
        </h2>
        <a href="/pages/product/productListingUser.php?type=Game" class="check-out-more relative flex items-center">
            <div class="icon_cont flex justify-center items-center">
                <span class="icon">🡪</span>
            </div>
            <span class="text_button relative inline-block">Check out more</span>
        </a>
    </div>
    <div class="c-card">
        <div class="u-card flex">
            <?php
            if (!empty($comingSoon)) {
                foreach ($comingSoon as $product) {
                    echo "<div class='card pointer'>";
                    echo "<a href='pages\product\productDetail.php?id=".$product['product_id']."'>";
                    echo "<div class='flex justify-center'>";
                    echo "<img src='/img/game/" . $product['url'] . "' alt='" . $product['name'] . "'>";
                    echo "</div>";
                    echo "<div class='card-title'>";
                    echo "<h3>"
                        . $product['name'] .
                        "</h3>";
                    if ($product['price'] == 0) {
                        echo "<h4> FREE </h4>";
                    } else
                        echo "<h4>MYR "
                            . $product['price'] .
                            "</h4>";
                    echo "</div>";
                    echo "</a>";
                    echo "</div>";
                }
            } else {
                echo "<p class = 'empty-product-list flex items-center justify-center'>No Coming Soon Games Found</p>";
            }
            ?>
        </div>
    </div>
</section>

<?php
include 'include/_footer.php';
ob_flush();
