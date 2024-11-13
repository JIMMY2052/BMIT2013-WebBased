<?php
require '../../include/_base.php';
require '../../include/_head.php';

auth('customer');
extract((array)$_user);

$allProductRemoved = "";
$stm = $_db->prepare('SELECT * FROM cart WHERE user_id = ?');
$stm->execute([$_user->id]);
$cartItems = $stm->fetchAll(); // Fetch all items as objects
foreach ($cartItems as $item) {
    $stm = $_db->prepare('SELECT * FROM `cart` c JOIN product p ON p.id = c.product_id WHERE c.product_id = ? AND user_id = ?');
    $stm->execute([$item->product_id, $_user->id]);
    $temp = $stm->fetch();

    if (($temp->stock_quantity < $temp->quantity) && $temp->stock_quantity != 0) {
        $stm = $_db->prepare('UPDATE `cart` SET quantity = ? , subtotal = ROUND((SELECT price FROM product WHERE id = ?)*?.2) WHERE user_id = ? AND product_id = ?');
        $stm->execute([$temp->stock_quantity, $item->product_id,$temp->stock_quantity,$_user->id, $item->product_id]);
    } else if ($temp->stock_quantity == 0) {
        $stm = $_db->prepare('SELECT * FROM wish_list WHERE user_id = ? AND product_id = ?');
        $stm->execute([$_user->id, $item->product_id]);
        $temp = $stm->fetch();
        if (empty($temp->user_id)) {
            $stm = $_db->prepare('INSERT INTO wish_list (user_id,product_id) VALUES (?,?);');
            $stm->execute([$_user->id, $item->product_id]);
        }
        $stm = $_db->prepare('DELETE FROM `cart` WHERE user_id = ? AND product_id = ?;');
        $stm->execute([$_user->id, $item->product_id]);
        $allProductRemoved .= $item->product_id;
    }
}
if ($allProductRemoved != "") {
    temp('info', "$allProductRemoved have been removed from cart and moved to wish lish because of out of stock.");
}
$gotSearch = false;
$stm = $_db->prepare('SELECT * FROM cart WHERE user_id = ?');
$stm->execute([$_user->id]);
$cartItems = $stm->fetchAll();
if (is_post()) {
    $search = req('transaction-search');
    if ($search != "") {
        $gotSearch = true;
        $stm = $_db->prepare('SELECT * FROM cart WHERE user_id = ? AND product_id IN (SELECT id FROM product WHERE `name` LIKE ?)');
        $searchTarget = '%' . $search . '%';
        $stm->execute([$_user->id, $searchTarget]);
        $searchCart = $stm->fetchAll();
    }
}

$finalCart = $cartItems;

if (!empty($searchCart) && $gotSearch) {
    $finalCart = $searchCart;
} else if (empty($searchCart) && $gotSearch) {
    $finalCart = [];
}

$totalQuantity = 0;
$totalPrice = 0;

?>

<div class="w-full">
    <div class="cart">
        <div class="cart-title">
            My Cart
        </div>

        <div>
            <?php if (!empty($cartItems)) { ?>
                <form method="post">
                    <?= html_search('transaction-search', 'placeholder="Search"') ?>
                </form>
            <?php } ?>

            <div class="cart-des flex">
                <div class="cart-list">
                    <div class="cart-details">
                        <?php if (!empty($finalCart)): ?>
                            <?php foreach ($finalCart as $item): ?>
                                <?php
                                // Fetch product details with image
                                //Top part commented due to images giving null if the database has no inmages
                                $productStm = $_db->prepare('
                                    /*
                                    SELECT p.id, p.name, p.description, p.type,c.quantity, p.price, pi.url
                                    FROM cart c
                                    JOIN product p ON c.product_id = p.id
                                    JOIN product_image_video pi ON p.id = pi.product_id
                                    WHERE c.user_id = ? AND c.product_id = ? AND pi.type = "photo"
                                    */

                                    SELECT p.id, p.name, p.description, p.type, c.quantity, p.price, pi.url, p.type AS product_type
                                    FROM cart c
                                    JOIN product p ON c.product_id = p.id
                                    LEFT JOIN product_image_video pi ON p.id = pi.product_id AND pi.type = "photo"
                                    WHERE c.user_id = ? AND c.product_id = ?
                                ');

                                $productStm->execute([$item->user_id, $item->product_id]);
                                $product = $productStm->fetch();

                                //Gave a placeholder image if the database has no images
                                if ($product && isset($product->url) && !empty($product->url)) {
                                    $image_urls = explode(',', $product->url);
                                    $image_url = $image_urls[0]; // Get the first image URL
                                } else {
                                    // Handle the case where the product or URL is not available
                                    $image_url = 'noImageFound.jpg'; // Use a default/placeholder image
                                }

                                // $image_urls = explode(',', $product->url);
                                // $image_url = $image_urls[0];
                                $quantityInput = $product->quantity;

                                $stm = $_db->prepare('SELECT * FROM wish_list WHERE user_id =? and product_id =?');
                                $stm->execute([$item->user_id, $item->product_id]);
                                $w = $stm->fetch();

                                // Calculate totals
                                $totalQuantity += $item->quantity;
                                $totalPrice += $product->price * $item->quantity;
                                ?>

                                <div class="cart-detail flex">
                                    <a href="/pages/product/productDetail.php?id=<?= $item->product_id ?>">
                                        <img src="/img/<?= $product->type =  $product->type == 'Game' ? 'game' : 'accessories'; ?>/<?= htmlspecialchars($image_url) ?>" alt="<?= htmlspecialchars($product->name) ?>">
                                    </a>
                                    <div class="wishlist-info flex justify-between">
                                        <div>
                                            <div class="flex justify-between">
                                                <div class="wishlist-info-title">
                                                    <?= htmlspecialchars($product->name) ?>
                                                </div>

                                                <div class="withlist-info-price flex items-start">
                                                    <div class="new-price">
                                                        MYR <?= number_format($product->price, 2) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="wishlist-info-des">
                                                <?= htmlspecialchars($product->description) ?>
                                            </div>
                                        </div>


                                        <div class="flex justify-end items-end">
                                            <div class="quantity-container flex">
                                                <!-- Form to update quantity -->
                                                <form action="cart_backend.php" method="post">
                                                    <!-- Hidden input to send product ID and current quantity -->
                                                    <input type="hidden" name="product_id" value="<?= $item->product_id ?>">
                                                    <input type="hidden" name="user_id" value="<?= $item->user_id ?>">
                                                    <input type="hidden" name="quantity" value="<?= $item->quantity ?>">

                                                    <?php if ($product->product_type != "Game") { ?>
                                                        <!-- Decrease Button -->
                                                        <button type="submit" name="action" value="decrease" id="decreaseBtn" class="decreaseBtn">-</button>

                                                        <!-- Display Current Quantity -->
                                                        <?= html_text('quantityInput','disabled') ?>
                                                        <!-- Increase Button -->
                                                        <button type="submit" name="action" value="increase" id="increaseBtn" class="increaseBtn">+</button>
                                                    <?php } ?>

                                                    <input type="submit" name="action" value="Remove" class="remove">

                                                </form>

                                            </div>


                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php if (!empty($cartItems) && $gotSearch) { ?>
                                <div class="empty-cart flex items-center justify-center">
                                    No items found matching your search.
                                </div>
                            <?php } else { ?>
                                <div class="empty-cart flex items-center justify-center">
                                    Your cart is empty.
                                </div>
                            <?php } ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($finalCart)) { ?>
                    <div class="cart-summary">
                        <div class="summary-title">
                            Cart Summary
                        </div>

                        <form action="create-checkout-session.php" method="POST">
                            <div class="flex justify-between">
                                Total Quantity
                                <div>
                                    <?= $totalQuantity ?>
                                </div>
                            </div>
                            <hr>
                            <div class="flex justify-between">
                                Total Price
                                <div>
                                    MYR <?= number_format($totalPrice, 2) ?>
                                </div>
                            </div>

                            <div class="flex justify-center w-full">
                                <input type="hidden" name="total" value=<?= number_format($totalPrice, 2) ?>>
                                <input type="hidden" name="cartItems" value='<?= json_encode($cartItems) ?>'>
                                <input type="hidden" name="user_id" value="<?= $_user->id ?>">
                                <button class="btn full-rounded" type="submit">
                                    <span>Check Out</span>
                                    <div class="border full-rounded"></div>
                                </button>
                            </div>
                        </form>

                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php
include '../../include/_footer.php';
