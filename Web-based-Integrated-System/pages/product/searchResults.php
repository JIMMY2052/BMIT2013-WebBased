<?php

include '../../include/_base.php';
$title = 'Games';
$file = $_SERVER['PHP_SELF'];
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_head.php';
require_once '../../lib/SimplePager.php';
// Database connection
$conn = new mysqli("localhost", "root", "", "motto");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the search term from the query string
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Prepare the SQL query to search both 'Games' and 'Accessories'
$queryGames = "SELECT * FROM product WHERE (type = 'Game' OR type = 'Accessories') AND (name LIKE '%$search%')";

// Execute both queries
$resultGames = $conn->query($queryGames);

$page = req('page', 1);
$p = new SimplePager($queryGames, [], '12', $page);
$arr = $p->result;
// Using SimplePager for paginated results with prepared statements
$page = req('page', 1);

?>

<h2>Search Results for "<?= htmlspecialchars($search) ?>"</h2>

<!-- Display Games -->
<p>
    <?= $p->count ?> of <?= $p->item_count ?> record(s) |
    Page <?= $p->page ?> of <?= $p->page_count ?>
</p>
<?php
if (count($arr) > 0) {
    echo "<div class='product_grid_container'>";
        // Output data of each row in a grid layout
        foreach($arr as $row) {
            $product_id = $row->id;
            if ($row->is_active == 1) {
                // Fetch the first image for the current product
                 // Correctly use $row to get product ID from current product
        
                echo "<div class='card pointer height_disable'>";
                echo "<a href='productDetail.php?id=$product_id'>";
                $checkWishlist = null;
                $checkGameBuyAlr = null;
        
                if(!empty($_user)){
                    // Fetch wishlist and purchase status for the user
                    $checkWishlist = $conn->query("SELECT * FROM wish_list WHERE product_id = '$product_id' AND user_id = '$_user->id'");
                    $checkGameBuyAlr = $conn->query("SELECT * FROM `order` o 
                                                    JOIN item i ON i.order_id = o.id 
                                                    JOIN product p ON p.id = i.product_id 
                                                    WHERE o.user_id = '$_user->id' 
                                                    AND p.type = 'Game' AND i.product_id = '$product_id'");
                }
        
                // Fetch image URL for the product
                $arrImage = $conn->query("SELECT url FROM product_image_video WHERE product_id = '$product_id' ORDER BY product_id ASC LIMIT 1");
        
                echo "<div class='flex justify-center'>";
                if ($arrImage && $arrImage->num_rows > 0) {
                    // If an image exists, use the first image
                    $image = $arrImage->fetch_assoc();
                    $urls = $image['url'];  // Get the URL field (which is a comma-separated string)
        
                    // Split the URLs by commas and get the first image
                    $urlArray = explode(', ', $urls);
                    $first_image_url = trim($urlArray[0]);
                    if($row->type == 'Game'){
                        echo "<img src='/img/game/$first_image_url' alt='Product Image' class='product_image'>";
                    } else
                    echo "<img src='/img/accessories/$first_image_url' alt='Product Image' class='product_image'>";
                    
                    
                } else {
                    // If no image exists, use a placeholder image
                    echo "<img src='/img/game/noImageFound.jpg' alt='Product Image' class='product_image'>";
                }
                echo "</div>";
        
                // Display product information
                echo "<div class='card-title'>";
                echo "<h3>".$row->name."</h3>";
        
                if($row->price != 0){
                    echo "<h4>MYR".$row->price."</h4>";
                } else {
                    echo "<h4>FREE</h4>";
                }
                echo "</div>";
                echo "</a>";
        
                // Add to cart form
                echo "<form action='product_backend.php' method='post' class='card-title'>";
                echo "<input type='hidden' name='product_id' value='$product_id'>";
                if($row->release_date > date("Y-m-d")){
                    echo "<button class='product_detail_button reverse_color width_100' disabled>Coming Soon</button>";
                } elseif ($checkGameBuyAlr && $checkGameBuyAlr->num_rows == 0) {
                    echo "<button type='submit' name='action' value='addToCart' class='brighten product_detail_button original_color width_100 pointer'>Add to Cart</button>";
                    if ($checkWishlist && $checkWishlist->num_rows == 0) {
                        // Add to wishlist form
                        echo "<button type='submit' name='action' value='addToWishlist' class='product_detail_button reverse_color pointer width_100'>Add to Wishlist</button>";
                    }
                } 
                else {
                    echo "<button class='product_detail_button reverse_color width_100' disabled>Buy Already</button>";
                }
        
                echo "</form>";
                echo "</div>";
            }
        }
    echo "</div>";
} else {
    echo "<p class = 'empty-product-list flex items-center justify-center'>No Product found</p>";
}
if ($p->page > 1) {
    echo "<a href='?search=$search&page=" . ($p->page - 1) . "'>Previous</a>";
} else {
    echo "<span class='disabled'>Previous</span> ";
}

echo "<span>" . $p->page . "</span> ";

if ($p->page < $p->page_count) {
    echo "<a href='?search=$search&page=" . ($p->page + 1) . "'>Next</a>";
} else {
    echo "<span class='disabled'>Next</span>";
}
?>



<?php
$conn->close();
?>
