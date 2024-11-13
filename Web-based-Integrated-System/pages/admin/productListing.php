<?php
require '../../include/_base.php';
$title = 'Product Listing';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';
require_once '../../lib/SimplePager.php';

// Define the fields for sorting and display
$fields = [
    'id' => 'Product ID',
    'product_img' => 'Product Image',
    'name' => 'Name',
    'description' => 'Description',
    'type' => 'Type',
    'category_type' => 'Category',
    'price' => 'Price(RM)',
    'stock_quantity' => 'Stock Quantity',
    'release_date' => 'Release Date',
    'is_active' => 'Active',
];


// Get the page number, search query, sort field, and sort direction from request
$page = req('page', 1);
$search = req('Search'); // This captures the search input from the user
$sort = req('sort', 'id'); // Default sort by 'id'
$dir = req('dir', 'asc'); // Default sorting direction is 'asc'

// Validate sorting direction and field
if (!in_array($dir, ['asc', 'desc'])) {
    $dir = 'asc';
}

if (!array_key_exists($sort, $fields)) {
    $sort = 'id';
}


// Base SQL query for products
$sql = "SELECT * FROM product WHERE 1=1 ";


// Add search functionality (by ID or Name)
$params = [];
if ($search) {
    $sql .= " AND (id = ? OR name LIKE ? OR category_type LIKE ? OR type LIKE ?)";
    $params[] = $search;
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
// Append sorting to the SQL query
$sql .= " ORDER BY $sort $dir";

// Initialize SimplePager with the final query and parameters
$p = new SimplePager($sql, $params, '10', $page);
$products = $p->result;

?>

<div class="container">
    <h2>Product Listing</h2>
    <div class="form-container">
        <form method="get" action="<?= $currentPage ?>" class="form-container-product">
            <!-- Search field for product ID or name -->
            <input type="text" name="Search" placeholder="Search Product ID/Name/Type/Category" value="<?= htmlspecialchars($search); ?>">

        </form>
        <p>
            <!-- Display record count and pagination info -->
            <?= $p->count ?> of <?= $p->item_count ?> record(s) |
            Page <?= $p->page ?> of <?= $p->page_count ?>
        </p>
    </div>
    <!-- Product Listing Table -->
    <table class="table" style="font-size: 14px;">
        <thead>
            <tr>
                <!-- Loop through fields and make each column sortable -->
                <?php foreach ($fields as $field => $label):
                    // Toggle sorting direction for each column
                    $newDir = ($sort == $field && $dir == 'asc') ? 'desc' : 'asc';
                ?>
                    <th>
                        <a href="<?= $currentPage ?>?sort=<?= $field ?>&dir=<?= $newDir ?>&Search=<?= htmlspecialchars($search) ?>">
                            <?= $label ?>
                            <?php if ($sort == $field): ?>
                                <!-- Show the current sort direction -->
                                <?= $dir == 'asc' ? '▲' : '▼' ?>
                            <?php endif; ?>
                        </a>
                    </th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Loop through the product records and display each product in the table -->
            <?php foreach ($products as $product):
                $sql_images = "SELECT GROUP_CONCAT(url SEPARATOR ',') AS urls FROM product_image_video WHERE product_id = ? AND type = 'photo'";
                $images_stmt = $_db->prepare($sql_images);
                $images_stmt->execute([$product->id]);
                $image_result = $images_stmt->fetch(); // Fetch a single row
                $image_urls = [];

                // Check if URLs were found
                if (!empty($image_result->urls)) {
                    $image_urls = explode(', ', $image_result->urls);
                } else {
                    $image_urls[] = 'default.jpg'; 
                }
                
            ?>
                <div>
                    <tr>
                        <td><?= $product->id; ?></td>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <!-- Display only the first image URL -->
                                    <?php $image_url = $image_urls[0];
                                    ?>
                                    <?php if ($product->type == 'Accessories') :?>
                                        
                                        <img src="../../img/accessories/<?= htmlspecialchars($image_url) ?>"
                                            alt="Accessories Picture"
                                            style="width: 100px; height: 100px; margin-right: 10px;">
                                    <?php elseif ($product->type == 'Game') : ?>
                                        <img src="../../img/game/<?= htmlspecialchars($image_url) ?>"
                                            alt="Game Picture"
                                            style="width: 100px; height: 100px; margin-right: 10px;">
                                    <?php  else : ?> 
                                        <img src="../../img/game/default.jpg"
                                            alt="Null Picture"
                                            style="width: 100px; height: 100px; margin-right: 10px;">
                                    <?php endif; ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($product->name); ?></td>
                        <td><?= htmlspecialchars($product->description); ?></td>
                        <td><?= htmlspecialchars($product->type); ?></td>
                        <td><?= htmlspecialchars($product->category_type); ?></td>
                        <td><?= number_format($product->price, 2); ?></td>
                        <td><?= $product->stock_quantity; ?></td>
                        <td><?= date('Y-m-d', strtotime($product->release_date)); ?></td>
                        <td><?= $product->is_active ? 'Yes' : 'No'; ?></td>
                        <td>
                            <!-- Action Buttons: View, Dropdown (Edit, Copy, Delete) -->
                            <div class="action-buttons-wrapper">
                                <a href="viewProduct.php?id=<?= $product->id ?>" class="btn btn-view" title="View">
                                    <img src="/img/adminIcon/view.svg" alt="View" title="View" style="padding-right: 10px;">
                                </a>
                                <div class="product-action-buttons">
                                    <button class="action-dropdown-btn" onclick="toggleActionMenu(this)">
                                        <img src="/img/adminIcon/dropDownBlack.svg" alt="Dropdown Icon">
                                    </button>
                                    <ul class="product-action-menu">
                                        <!-- Edit Product -->
                                        <li style="padding:0%">
                                            <form action="editProduct.php?id=<?= $product->id ?>" class="product-edit-form" title="Edit">
                                                <input type="hidden" name="id" value="<?= $product->id ?>">
                                                <button type="submit" class="product-edit-btn" title="Edit">
                                                    <img src="/img/adminIcon/edit.svg" alt="Edit"> Edit
                                                </button>
                                            </form>
                                        </li>

                                        <!-- Delete Product -->
                                        <li style="padding:0%">
                                            <form action="deleteProduct.php" method="POST" class="product-delete-form" onsubmit="return confirm('Are you sure you want to delete product <?= $product->id; ?>?');">
                                                <input type="hidden" name="id" value="<?= $product->id ?>">
                                                <button type="submit" class="product-delete-btn" title="Delete">
                                                    <img src="/img/adminIcon/delete.svg" alt="Delete"> Deactivate
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination Links -->
<?= $p->html("sort=$sort&dir=$dir&Search=" . urlencode($search)) ?>

<?php
include '../../include/_adminFooter.php';
?>