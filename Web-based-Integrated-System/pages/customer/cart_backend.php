<?php
require '../../include/_base.php';

if (is_post()) {
    $productId = req('product_id'); // Fetch product ID from the form
    $userId = req('user_id');
    $new_quantity = (int)req('quantity'); // Fetch current quantity
    $action = req('action');

    if ($action == "increase" || $action == "decrease") {
        $stm = $_db->prepare('SELECT stock_quantity FROM product WHERE id = ?');
        $stm->execute([$productId]);
        $product=$stm->fetch();

        if ($action == "increase") $new_quantity++;
        else if ($action == "decrease") $new_quantity--;
        if ($new_quantity > 0 && $product->stock_quantity>= $new_quantity) {
            // Update the quantity in the cart table
            $stm = $_db->prepare('UPDATE cart SET quantity = ? , subtotal = ROUND((SELECT price FROM product WHERE id = ?)*?)  WHERE user_id = ? AND product_id = ?');
            $stm->execute([$new_quantity,$productId, $new_quantity,$userId, $productId]);
        }
    } else if ($action == "Remove") {

        $stm = $_db->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?');
        $stm->execute([$userId, $productId]);
    }

    redirect('cart.php');
}
