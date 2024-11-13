<?php
include '../../include/_base.php';
include '../../include/_head.php';
require_once '../../lib/SimplePager.php';

$_title = 'Games';
extract((array)$_user);
$currentPage = basename($_SERVER['PHP_SELF']);
$conn = new mysqli("localhost", "root", "", "motto");
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
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch product details from the product table
    $productQuery = $conn->query("SELECT * FROM product WHERE id = '$product_id'");
    $product = $productQuery->fetch_assoc();

    // Fetch product media (images/videos) from the product_image_video table
    $mediaQuery = $conn->query("SELECT url FROM product_image_video WHERE product_id = '$product_id'");

    $genreQuery = $conn->query("SELECT category_type, type FROM product WHERE id = '$product_id'");

    // Check if product exists
    if ($product) {
        // Store the media files in an array
        $imageFiles = [];
        $videoFiles = [];
        $consoleType = [];
        $gameMode = [];
        $gameGenre = [];

        while ($media = $mediaQuery->fetch_assoc()) {
            $urls = $media['url']; // Comma-separated string of URLs

            // Split the URLs by commas and loop through each URL
            $urlArray = explode(', ', $urls);
            foreach ($urlArray as $url) {
                if (
                    strpos($url, '.jpg') !== false ||
                    strpos($url, '.avif') !== false
                ) {
                    $imageFiles[] = $url; // Add image URLs
                } elseif (strpos($url, '.mp4') !== false) {
                    $videoFiles[] = $url; // Add video URLs
                }
            }
        }

        while ($genre = $genreQuery->fetch_assoc()) {
            $categoryTypes = $genre['category_type'];
            $type = $genre['type'];

            $genreArray = explode(', ', $categoryTypes);
            if ($type == 'Game') {
                foreach ($genreArray as $categoryType) {
                    if (strpos($categoryType, 'PS5') !== false || strpos($categoryType, 'Windows') !== false || strpos($categoryType, 'Switch') !== false) {
                        $consoleType[] = $categoryType;
                    } elseif (
                        strpos($categoryType, 'Single-Player') !== false ||
                        strpos($categoryType, 'Co-op') !== false ||
                        strpos($categoryType, 'Competitive') !== false ||
                        strpos($categoryType, 'Online Multiplayer') !== false ||
                        strpos($categoryType, 'Multiplayer') !== false
                    ) {
                        $gameMode[] = $categoryType;
                    } else {
                        $gameGenre[] = $categoryType;
                    }
                }
            } elseif ($type == 'Accessories') {
                foreach ($genreArray as $categoryType) {
                    if (strpos($categoryType, 'PS5') !== false || strpos($categoryType, 'Windows') !== false || strpos($categoryType, 'Switch') !== false) {
                        $accessoriesType[] = $categoryType;
                    } else {
                        $accessoriesGenre[] = $categoryType;
                    }
                }
            }
        }
    } else {
        echo "Product not found!";
        exit();
    }
} else {
    echo "No product selected!";
    exit();
}

if (is_post()) {
    // TODO
    $id = req('id');
    $unit = req('unit');
    update_cart($id, $unit);
    redirect();
}

$id  = req('id');
$stm = $_db->prepare('SELECT * FROM product WHERE id = ?');
$stm->execute([$id]);
$p = $stm->fetch();


// Handle "Add to Cart" functionality
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];

    // Fetch product details for the selected product
    $productQuery = $conn->query("SELECT * FROM product WHERE id = '$product_id'");
    $product = $productQuery->fetch_assoc();

    if ($product) {
        // Initialize cart if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Add product to the cart (or increase quantity if it already exists)
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += 1; // Increment quantity
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1
            ];
        }

        // Success message
        echo "<p>Product added to cart!</p>";
    } else {
        echo "<p>Product not found!</p>";
    }
}
if (!empty($_user)) {
    $product_id = $_GET['id'];
    $checkWishlist = $conn->query("SELECT * FROM wish_list WHERE product_id = '$product_id' AND user_id = '$_user->id'");
    $checkGameBuyAlr = $conn->query("SELECT * FROM `order` o 
                                    JOIN item i ON i.order_id = o.id 
                                    JOIN product p ON p.id = i.product_id 
                                    WHERE o.user_id = '$_user->id' 
                                    AND p.type = 'Game' AND i.product_id = '$product_id';
                                    ");
}
?>


<div>
    <div class="container">
        <!-- Main Media Display -->
        <div class="main-media">
            <h1><?php echo $product['name']; ?></h1>

            <?php
            // Merge image and video arrays to create a media array for the slideshow
            $mediaFiles = array_merge($imageFiles, $videoFiles);
            $mediaTypes = array_fill(0, count($imageFiles), 'image'); // Type 'image' for images
            $mediaTypes = array_merge($mediaTypes, array_fill(0, count($videoFiles), 'video')); // Type 'video' for videos
            if (!empty($videoFiles)) {

                // Display the first video as default
                echo "<video id='mainDisplay' class='main-media-display' autoplay muted loop onended='nextMedia()'>
                        <source src='/img/video/" . $videoFiles[0] . "' type='video/mp4'>
                        </video>";
            } elseif (!empty($imageFiles)) {

                if ($product['type'] == 'Game') {
                    // Display the first image as default
                    echo "<img id='mainDisplay' src='/img/game/" . $imageFiles[0] . "' alt='Product Image' class='main-media-display'>";
                } else
                    echo "<img id='mainDisplay' src='/img/accessories/" . $imageFiles[0] . "' alt='Product Image' class='main-media-display'>";
            } else {
                // No media available
                echo "<img id='mainDisplay' src='/img/game/noImageFound.jpg' alt='No Media Available' class='main-media-display'>";
            }
            ?>

            <!-- Thumbnail Row (Now inside the main media section, under the main image/video) -->
            <div class="thumbnail-row">
                <?php
                // Display video thumbnails (you can use a placeholder image for video thumbnails)
                foreach ($videoFiles as $video) {
                    echo "<video src='/img/video/" . $video . "' alt='Thumbnail' class='thumbnail' muted loop autoplay onclick='changeMedia(\"/img/video/" . $video . "\", \"video\")'></video>";
                }
                // Display image thumbnails
                if (count($imageFiles) > 1) {
                    if ($product['type'] == 'Game') {
                        for ($i = 0; $i < count($imageFiles); $i++) {
                            echo "<img src='/img/game/" . $imageFiles[$i] . "' alt='Thumbnail' class='thumbnail' onclick='changeMedia(\"/img/game/" . $imageFiles[$i] . "\", \"photo\")'>";
                        }
                    } else {
                        for ($i = 0; $i < count($imageFiles); $i++) {

                            echo "<img src='/img/accessories/" . $imageFiles[$i] . "' alt='Thumbnail' class='thumbnail' onclick='changeMedia(\"/img/accessories/" . $imageFiles[$i] . "\", \"photo\")'>";
                        }
                    }
                }

                ?>
            </div>
            <h2>Overview: </h2>
            <h5 class="weight300 justify-center margin20"><?php echo $product['description']; ?></h5>
        </div>

        <!-- Product Info Section (Right) -->
        <div class="product-info">



            <p>
                <strong>Platform: </strong>
            </p>
            <?php
            if ($product['type'] == 'Game') {
                if (!empty($consoleType)) {
                    for ($x = 0; $x < count($consoleType); $x++) {
                        // Windows Switch PS5        
                        echo "<a href='productListingUser.php?type=Game&platform=" . strtolower($consoleType[$x]) . "'>";
                        echo "<button class='product_detail_button reverse_color pointer'>
                                $consoleType[$x]
                        </button>";
                        echo "</a>";
                    }
                } else {
                    // No media available
                    echo "Unknown";
                }
            } else {
                if (!empty($accessoriesType)) {
                    for ($x = 0; $x < count($accessoriesType); $x++) {
                        // Windows Switch PS5  
                        echo "<a href='productListingUser.php?type=Accessories&console=" . strtolower($accessoriesType[$x]) . "'>";
                        echo "<button class = 'product_detail_button reverse_color pointer'>
                            $accessoriesType[$x]
                        </button>";
                        echo "</a>";
                    }
                } else {
                    // No media available
                    echo "Unknown";
                }
            }

            ?>
            </p>


            <?php
            echo "<p>";
            if ($product['type'] == 'Game') {
                echo "<strong>Genres: </strong>";
                echo "</p>";
                if (!empty($gameGenre)) {
                    // Display the first video as default
                    for ($x = 0; $x < count($gameGenre); $x++) {
                        echo "<a href='productListingUser.php?type=Game&genre=" . strtolower($gameGenre[$x]) . "'>";
                        echo "<button class = 'product_detail_button reverse_color pointer'>
                                        $gameGenre[$x]
                                    </button>";
                        echo "</a>";
                    }
                } else {
                    // No media available
                    echo "Unknown";
                }
            } else {
                echo "<strong>Type: </strong>";
                echo "</p>";
                if (!empty($accessoriesGenre)) {
                    // Display the first video as default
                    for ($x = 0; $x < count($accessoriesGenre); $x++) {
                        echo "<a href='productListingUser.php?type=Accessories&accessory_type=" . strtolower($accessoriesGenre[$x]) . "'>";
                        echo "<button class = 'product_detail_button reverse_color pointer'>
                                        $accessoriesGenre[$x]
                                    </button>";
                        echo "</a>";
                    }
                } else {
                    // No media available
                    echo "Unknown";
                }
            }
            ?>





            <?php
            if ($product['type'] == 'Game') {
                echo "<p>";
                echo "<strong>Game Mode: </strong>";
                echo "</p>";
                if (!empty($gameMode)) {
                    // Display the first video as default
                    for ($x = 0; $x < count($gameMode); $x++) {
                        echo "<a href='productListingUser.php?type=Game&genre=" . strtolower($gameMode[$x]) . "'>";
                        echo "<button class = 'product_detail_button reverse_color pointer'>
                                    $gameMode[$x]
                                </button>";
                        echo "</a>";
                    }
                } else {
                    // No media available
                    echo "Unknown";
                }
            } else {
            }
            "</div>";
            ?>

            <h3>
                <strong>

                    <?php
                    if ($product['price'] != 0) {
                        echo "MYR";
                        echo $product['price'];
                    } else
                        echo "FREE";

                    ?>
                </strong>
            </h3>

            <!-- Add to Cart Button -->

            <form action="product_backend.php" method="post">
                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                <?php 
                if ($product['release_date'] > date("Y-m-d")) { ?>
                    <button class='product_detail_button reverse_color width_100' disabled>Coming Soon</button>
                <?php } 
                elseif ($product['stock_quantity'] == 0) { ?>
                    <button class="product_detail_button reverse_color width_100" disabled> Sold Out </button>
                    <?php if (empty($checkWishlist->num_rows)) { ?>
                        <button type='submit' name='action' value='addToWishlist' class='product_detail_button reverse_color width_100 pointer'>Add to Wishlist</button>
                    <?php $checkWishlist = "";
                    } ?>
                <?php } elseif (empty($checkGameBuyAlr->num_rows)) { ?>
                    <button type="submit" name="action" value="addToCart" class="brighten product_detail_button original_color width_100 pointer">Add to Cart</button>
                    <?php if (empty($checkWishlist->num_rows)) { ?>
                        <button type='submit' name='action' value='addToWishlist' class='product_detail_button reverse_color width_100 pointer'>Add to Wishlist</button>
                    <?php $checkWishlist = "";
                    }
                } else { ?>
                    <button class='product_detail_button original_color width_100' disabled>Buy Already</button>
                <?php $checkGameBuyAlr = "";
                } ?>
            </form>


        </div>
    </div>
</div>

<?php
include '../../include/_footer.php';

?>

<script>
    // Array to hold media files and types for slideshow
    let mediaFiles = <?php echo json_encode($mediaFiles); ?>;
    let mediaTypes = <?php echo json_encode($mediaTypes); ?>;
    let currentMediaIndex = 1; // Start with the second media (skipping the first image)

    // Function to change media (either image or video)
    function changeMedia(src, type) {
        const mainDisplay = document.getElementById('mainDisplay');

        if (type === 'photo') {
            mainDisplay.outerHTML = "<img id='mainDisplay' src='" + src + "' alt='Product Image' class='main-media-display'>";
            // Automatically advance the media after a 5-second delay for images
            autoAdvanceMedia(5000);
        } else if (type === 'video') {
            mainDisplay.outerHTML = "<video id='mainDisplay' class='main-media-display' autoplay muted loop onended='nextMedia()'><source src='" + src + "' type='video/mp4'></video>";
        }
    }

    // Function to automatically advance to the next media (only for images)
    function nextMedia() {
        currentMediaIndex = (currentMediaIndex + 1) % mediaFiles.length; // Loop through media

        // Skip the first image if we're looping back to the start
        if (currentMediaIndex === 0 && mediaTypes[currentMediaIndex] === 'photo') {
            currentMediaIndex = 1; // Skip to the second media
        }

        let nextMediaFile = mediaFiles[currentMediaIndex];
        let nextMediaType = mediaTypes[currentMediaIndex];

        if (nextMediaType === 'photo') {
            if ($product['type'] == 'Game') {
                changeMedia('/img/game/' + nextMediaFile, 'photo');
            } else
                changeMedia('/img/accessories/' + nextMediaFile, 'photo');
        } else if (nextMediaType === 'video') {
            changeMedia('/img/video/' + nextMediaFile, 'video');
        }
    }

    // Automatically switch to the next media after a certain delay for images
    function autoAdvanceMedia(delay) {
        // Only auto-advance if the current media is an image
        if (mediaTypes[currentMediaIndex] === 'photo') {
            setTimeout(function() {
                nextMedia();
            }, delay);
        }
    }

    // Start automatic media switching for images
    window.onload = function() {
        // Ensure that auto-advance applies only to images and not videos
        if (mediaTypes[currentMediaIndex] === 'photo') {
            autoAdvanceMedia(5000); // 5 seconds delay for images
        }
    };
</script>
</main>

</html>