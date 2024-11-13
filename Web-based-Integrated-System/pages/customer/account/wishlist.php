<?php
require '../../../include/_base.php';

require '../../../include/_head.php';

auth('customer');
extract((array)$_user);

// Fetch all cart items for the logged-in user
$stm = $_db->prepare('SELECT * FROM wish_list WHERE user_id = ?');
$stm->execute([$_user->id]);
$wishItems = $stm->fetchAll(); // Fetch all items as objects
foreach ($wishItems as $item) {
    $stm = $_db->prepare('DELETE w
    FROM wish_list w
    JOIN product p ON p.id = w.product_id
    JOIN item i ON i.product_id = p.id
    JOIN `order` o ON o.id = i.order_id
    WHERE w.user_id = ? 
      AND w.product_id = ?
      AND p.type = "Game"
      AND o.user_id = ?
');
    $stm->execute([$_user->id, $item->product_id, $_user->id]);
}
$stm = $_db->prepare('SELECT * FROM wish_list WHERE user_id = ?');
$stm->execute([$_user->id]);
$wishItems = $stm->fetchAll();
// Get the sorting option from POST request
$sortOption = isset($_POST['sort']) ? $_POST['sort'] : 'alphabetical';

// Modify SQL query based on the selected sorting option
$orderBy = 'p.name ASC'; // Default sorting (A-Z)

if ($sortOption == 'reverse-alphabetical') {
    $orderBy = 'p.name DESC'; // Z-A
} elseif ($sortOption == 'l-h') {
    $orderBy = 'p.price ASC'; // Price Low to High
} elseif ($sortOption == 'h-l') {
    $orderBy = 'p.price DESC'; // Price High to Low
}

// Fetch sorted wishlist items based on selected sorting order
$stm = $_db->prepare("
    SELECT w.user_id, w.product_id, pi.url, p.name, p.description, p.price 
    FROM wish_list w 
    JOIN product p ON w.product_id = p.id 
    LEFT JOIN product_image_video pi ON p.id = pi.product_id AND pi.type = 'photo' 
    WHERE w.user_id = ? 
    ORDER BY $orderBy
");
$stm->execute([$_user->id]);
$wishItems = $stm->fetchAll();

?>

<div class="w-full">
    <div class="wishlist flex">
        <div class="wishlist-title">
            My Wishlist
        </div>
        <!--<div class="empty-wishlist flex items-center justify-center">
            You haven't added anything to your wishlist yet.
        </div>   -->

        <div class="wishlist-des flex justify-center">
            <?php if ($wishItems) { ?>
                <div class="wishlist-list">
                <div class="wishlist-sort flex items-center">
    Sort By:
    <form id="sortForm" action="" method="POST">
        <select name="sort" id="sort" onchange="submitSorting()">
            <option value="alphabetical" <?= isset($_POST['sort']) && $_POST['sort'] == 'alphabetical' ? 'selected' : '' ?>>Alphabetical (A-Z)</option>
            <option value="reverse-alphabetical" <?= isset($_POST['sort']) && $_POST['sort'] == 'reverse-alphabetical' ? 'selected' : '' ?>>Alphabetical (Z-A)</option>
            <option value="l-h" <?= isset($_POST['sort']) && $_POST['sort'] == 'l-h' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="h-l" <?= isset($_POST['sort']) && $_POST['sort'] == 'h-l' ? 'selected' : '' ?>>Price: High to Low</option>
        </select>
    </form>
</div>


                    <div class="wishlist-details">

                        <?php if ($wishItems): ?>
                            <?php foreach ($wishItems as $item): ?>
                                <?php
                                // Fetch product details with image
                                //Top part commented due to images giving null if the database has no inmages
                                $stm = $_db->prepare('
                                    /*
                                        SELECT w.user_id , w.product_id, pi.url, p.name, p.description, p.price FROM wish_list w 
                                        JOIN product p ON w.product_id = p.id 
                                        JOIN product_image_video pi ON p.id = pi.product_id 
                                        WHERE w.user_id = ? AND w.product_id = ? AND pi.type = "photo" 
                                    */
                                    SELECT w.user_id, w.product_id, pi.url, p.name, p.description, p.price ,p.type AS product_type
                                    FROM wish_list w
                                    JOIN product p ON w.product_id = p.id
                                    LEFT JOIN product_image_video pi ON p.id = pi.product_id AND pi.type = "photo"
                                    WHERE w.user_id = ? AND w.product_id = ?
                                ');

                                $cartStm = $_db->prepare('SELECT * FROM cart c JOIN product p ON p.id = c.product_id WHERE c.user_id = ? AND c.product_id = ?');
                                $cartStm->execute([$item->user_id, $item->product_id]);
                                $c = $cartStm->fetch();
                                $stm1 = $_db->prepare('SELECT * FROM product WHERE id = ?');
                                $stm1->execute([$item->product_id]);
                                $temp = $stm1->fetch();
                                $enoughStock = $temp->stock_quantity != 0 ? true : false;

                                $stm->execute([$item->user_id, $item->product_id]);
                                $product = $stm->fetch();
                                if ($product && isset($product->url) && !empty($product->url)) {
                                    $image_urls = explode(',', $product->url);
                                    $image_url = $image_urls[0]; // Get the first image URL
                                } else {
                                    // Handle the case where the product or URL is not available
                                    $image_url = 'noImageFound.jpg'; // Use a default/placeholder image
                                }

                                // $image_urls = explode(',', $product->url);
                                // $image_url = $image_urls[0];
                                ?>
                                <form action="wishlist_backend.php" method="post">
                                    <div class="wishlist-detail flex"><a href="/pages/product/productDetail.php?id=<?= $item->product_id ?>">
                                    <img src="/img/<?= $product->product_type == 'Game' ? 'game' : 'accessories'; ?>/<?= htmlspecialchars($image_url) ?>" alt="">
                                    </a>
                                        <div class="wishlist-info flex justify-between">
                                            <div>
                                                <div class="flex justify-between">
                                                    <div class="wishlist-info-title">
                                                        <?= $product->name ?>
                                                    </div>
                                                    <div class="withlist-info-price flex items-start">
                                                        <div class="new-price">
                                                        MYR <?= number_format($product->price, 2) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="wishlist-info-des">
                                                    <?= $product->description ?>
                                                </div>
                                            </div>
                                            <input type="hidden" name="product_id" value="<?= $item->product_id ?>">
                                            <input type="hidden" name="user_id" value="<?= $item->user_id ?>">

                                            <div class="flex justify-end">
                                                <?php if (!$c) { ?>
                                                    <?php if ($enoughStock) { ?>
                                                        <input type="submit" name="action" value="addToCart" class="add-to-cart">
                                                <?php }
                                                } ?>
                                                <input type="submit" name="action" value="remove" class="remove">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </div>
                
            <?php } else { ?>
                <div class="empty-cart flex items-center justify-center">
                    Your Wish List is empty.
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<script>
function submitSorting() {
    // Get the form and submit it
    document.getElementById('sortForm').submit();
}
</script>

<?php
include '../../../include/_footer.php';

?>