
<?php
include '../../include/_base.php';
$title = 'Games';
$file = $_SERVER['PHP_SELF'];
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_head.php';
require_once '../../lib/SimplePager.php';

extract((array)$_user);

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

if (isset($_GET['type'])) {
    $product_type = $_GET['type'];

    // Initialize an array to store filter conditions
    $filters = [];

    // Check if platform filter is set and not empty
    if (isset($_GET['platform']) && !empty($_GET['platform'])) {
        $platforms = explode(',', $_GET['platform']);
        foreach ($platforms as $platform) {
            $filters[] = "category_type LIKE '%" . $conn->real_escape_string(trim($platform)) . "%'";
        }
    }

    // Check if genre filter is set and not empty
    if (isset($_GET['genre']) && !empty($_GET['genre'])) {
        $genres = explode(',', $_GET['genre']);
        foreach ($genres as $genre) {
            $filters[] = "category_type LIKE '%" . $conn->real_escape_string(trim($genre)) . "%'";
        }
    }

    // Check if mode filter is set and not empty
    if (isset($_GET['mode']) && !empty($_GET['mode'])) {
        $modes = explode(',', $_GET['mode']);
        foreach ($modes as $mode) {
            $filters[] = "category_type LIKE '%" . $conn->real_escape_string(trim($mode)) . "%'";
        }
    }

    if (isset($_GET['accessory']) && !empty($_GET['accessory'])) {
        $accessories = explode(',', $_GET['accessory']);
        foreach ($accessories as $accessory) {
            $filters[] = "category_type LIKE '%" . $conn->real_escape_string(trim($accessory)) . "%'";
        }
    }

    if (isset($_GET['console']) && !empty($_GET['console'])) {
        $consoles = explode(',', $_GET['console']);
        foreach ($consoles as $console) {
            $filters[] = "category_type LIKE '%" . $conn->real_escape_string(trim($console)) . "%'";
        }
    }

    // Construct the SQL query by appending filters if there are any
    $filterQuery = '';
    if (!empty($filters)) {
        // Combine filters with AND for multi-category filtering
        $filterQuery = ' AND (' . implode(' OR ', $filters) . ')';
    }

    // Using SimplePager for paginated results with prepared statements
    $page = req('page', 1);
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc'; // Default sort is name ascending

    // Modify the query for sorting
    $sortQuery = '';
    switch ($sort) {
        case 'name_asc':
            $sortQuery = ' ORDER BY name ASC';
            break;
        case 'name_desc':
            $sortQuery = ' ORDER BY name DESC';
            break;
        case 'price_asc':
            $sortQuery = ' ORDER BY price ASC';
            break;
        case 'price_desc':
            $sortQuery = ' ORDER BY price DESC';
            break;
        default:
            $sortQuery = ' ORDER BY name ASC';
    }

    // Modify the SQL query to include the sorting option
    $query = "SELECT * FROM product WHERE type = :product_type $filterQuery $sortQuery";

    // Pass the product type as a parameter
    $p = new SimplePager($query, ['product_type' => $product_type], '12', $page);

    // Fetch results
    $arr = $p->result;

    $arrTest = $conn->query("SELECT * FROM product WHERE type = '$product_type' $filterQuery $sortQuery");

    



    if ($arr) {
        // Display the total number of records
        $numRows = count($arr);
        echo "Total products: " . $numRows;

        // Fetching data for filters
        $arr1 = glob('img/game/*.jpg');
        $arr1 = array_map('basename', $arr1);

        $gameFilterQuery = $conn->query("SELECT category_type, type FROM product WHERE type = '$product_type'");

        $consoleType = [];
        $gameMode = [];
        $gameGenre = [];
        $accessoriesType = [];
        $accessoriesGenre = [];

        // Process each product for categorizing filters
        while ($filter = $gameFilterQuery->fetch_assoc()) {
            $categoryTypes = $filter['category_type'];
            $type = $filter['type'];
            $genreArray = explode(', ', $categoryTypes);

            if ($type == 'Game') {
                foreach ($genreArray as $categoryType) {
                    if (strpos($categoryType, 'PS5') !== false || strpos($categoryType, 'Windows') !== false || strpos($categoryType, 'Switch') !== false) {
                        $consoleType[] = $categoryType;
                    } elseif (strpos($categoryType, 'Single-Player')      !== false || 
                              strpos($categoryType, 'Co-op')              !== false || 
                              strpos($categoryType, 'Competitive')        !== false || 
                              strpos($categoryType, 'Online Multiplayer') !== false ||
                              strpos($categoryType, 'Multiplayer')        !== false) {
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
        // Output product grid
        echo "<h2> $product_type List</h2>";
        echo "<div class='product_grid_container'>";
        foreach  ($arr as $row) {
            // Output individual product details
        }
        echo "</div>";

    } else {
        echo "No $product_type found";
    }

} else {
    echo "Product type not specified.";
}
?>

<div>
        <p>
            <?= $p->count ?> of <?= $p->item_count ?> record(s) |
            Page <?= $p->page ?> of <?= $p->page_count ?>
        </p>
        <form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type); ?>">

            <!-- Filter Bar -->
            <div class="filter-bar" onclick="toggleFilters()">
                <span>Filter</span>
                <span class="arrow">&#9660;</span> <!-- Dropdown arrow -->
            </div>

            <!-- Filters Section (Initially Hidden) -->
            <div id="filters" class="filters-section" style="display: none;">
                <div class="filters">

                    <?php if ($product_type == 'Game'): ?>
                        <!-- Platform Filter (As Buttons) -->
                        <div class="filter-category">
                            <h3>Platform</h3>
                            <div class="button-group">
                                <?php foreach (array_unique($consoleType) as $platform): ?>
                                    <button type="button" class="product_detail_button reverse_color" data-type="platform" data-value="<?= strtolower($platform); ?>"><?= $platform; ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Genre Filter (As Buttons) -->
                        <div class="filter-category">
                            <h3>Genre</h3>
                            <div class="button-group">
                                <?php foreach (array_unique($gameGenre) as $genre): ?>
                                    <button type="button" class="filter-button" data-type="genre" data-value="<?= strtolower($genre); ?>"><?= $genre; ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Game Mode Filter (As Buttons) -->
                        <div class="filter-category">
                            <h3>Game Mode</h3>
                            <div class="button-group">
                                <?php foreach (array_unique($gameMode) as $mode): ?>
                                    <button type="button" class="filter-button" data-type="mode" data-value="<?= strtolower($mode); ?>"><?= $mode; ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    <?php elseif ($product_type == 'Accessories'): ?>
                        <!-- Console Type Filter (As Buttons) -->
                        <div class="filter-category">
                            <h3>Console Type</h3>
                            <div class="button-group">
                                <?php foreach (array_unique($accessoriesType) as $console): ?>
                                    <button type="button" class="filter-button" data-type="console" data-value="<?= strtolower($console); ?>"><?= $console; ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Accessory Type Filter (As Buttons) -->
                        <div class="filter-category">
                            <h3>Accessory Type</h3>
                            <div class="button-group">
                                <?php foreach (array_unique($accessoriesGenre) as $accessory): ?>
                                    <button type="button" class="filter-button" data-type="accessory_type" data-value="<?= strtolower($accessory); ?>"><?= $accessory; ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    <?php endif; ?>

                    <!-- Hidden inputs to store selected values -->
                    <input type="hidden" name="platform" id="platform">
                    <input type="hidden" name="genre" id="genre">
                    <input type="hidden" name="mode" id="mode">
                    <input type="hidden" name="console" id="console">
                    <input type="hidden" name="accessory_type" id="accessory_type">

                    <!-- Submit Button -->
                    <button type="submit">Filter</button>
                </div>
            </div>
        </form>
         
        <form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type); ?>">

            <!-- Add sorting dropdown -->
            <label for="sort">Sort by:</label>
            <select name="sort" id="sort">
                <option value="name_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                <option value="name_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                <option value="price_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
                <option value="price_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
            </select>

            <!-- Filters and submit button -->
            <button type="submit">Apply</button>
        </form>

        <?php
        echo "<h2> $product_type List</h2>";
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
                        } elseif($row->stock_quantity == 0){
                            echo "<button class='product_detail_button reverse_color width_100' disabled>Sold Out</button>";
                            if ($checkWishlist && $checkWishlist->num_rows == 0) {
                                // Add to wishlist form
                                echo "<button type='submit' name='action' value='addToWishlist' class='product_detail_button reverse_color pointer width_100'>Add to Wishlist</button>";
                            }
                        }elseif (empty($checkGameBuyAlr->num_rows)) {
                            echo "<button type='submit' name='action' value='addToCart' class='brighten product_detail_button original_color width_100 pointer'>Add to Cart</button>";
                            if (empty($checkWishlist->num_rows)) {
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
        } else {
            echo "<p class = 'empty-product-list flex items-center justify-center'>No $product_type found</p>";
        }
        ?>
</div>
<?php
if ($p->page > 1) {
    echo "<a href='?type=$product_type&page=" . ($p->page - 1) . "&sort=$sort'>Previous</a> ";
} else {
    echo "<span class='disabled'>Previous</span> ";
}

echo "<span>" . $p->page . "</span> ";

if ($p->page < $p->page_count) {
    echo "<a href='?type=$product_type&page=" . ($p->page + 1) . "&sort=$sort'>Next</a>";
} else {
    echo "<span class='disabled'>Next</span>";
}
?>
    
<!-- JavaScript for Toggling Filters -->
<script>
    // Track selected filters
    const selectedFilters = {
        platform: [],
        genre: [],
        mode: [],
        console: [],
        accessory_type: []
    };

    // Add event listeners to all filter buttons
    document.querySelectorAll('.filter-button').forEach(button => {
        button.addEventListener('click', function () {
            const type = this.getAttribute('data-type');
            const value = this.getAttribute('data-value');

            if (selectedFilters[type].includes(value)) {
                selectedFilters[type] = selectedFilters[type].filter(item => item !== value);
                this.classList.remove('selected');
            } else {
                selectedFilters[type].push(value);
                this.classList.add('selected');
            }

            document.getElementById(type).value = selectedFilters[type].join(',');

            // Debugging: Check the hidden input value
            console.log(type + ': ' + selectedFilters[type].join(','));
        });
    });

    // Toggle filter section visibility
    function toggleFilters() {
        const filters = document.getElementById('filters');
        const arrow = document.querySelector('.filter-bar .arrow');

        if (filters.style.display === 'none') {
            filters.style.display = 'block';
            arrow.innerHTML = '&#9650;'; // Up arrow when expanded
        } else {
            filters.style.display = 'none';
            arrow.innerHTML = '&#9660;'; // Down arrow when collapsed
        }
    }
    
</script>

<?php
    include '../../include/_footer.php';
    ?>    
