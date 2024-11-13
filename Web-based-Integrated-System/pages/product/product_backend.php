<?php
require '../../include/_base.php';
auth('customer');
$previousPage = $_SERVER['HTTP_REFERER'];
extract((array)$_user);

if (is_post()) {
    $productId = req('product_id');
    $userId = $_user->id;
    $action = req('action');

    if ($action == "addToWishlist") {
        $stm = $_db->prepare('INSERT INTO wish_list (user_id,product_id) VALUES(?,?);');
        $stm->execute([$userId, $productId]);
    } else if ($action == "addToCart") {
        $stm = $_db->prepare('SELECT * FROM cart c JOIN product p ON p.id = c.product_id WHERE c.product_id = ? AND c.user_id = ?');
        $stm->execute([$productId, $userId]);
        $c = $stm->fetch(); // Fetch the first result if it exists

        if ($c) {
            if ($c->type != 'Game') {
                $stm = $_db->prepare('UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?');
                $stm->execute([$userId, $productId]);
            }
        } else {
            $stm = $_db->prepare('INSERT INTO cart (user_id, product_id, price, quantity,subtotal) VALUES (?, ?, (SELECT price FROM product WHERE id = ?), 1,(SELECT price FROM product WHERE id = ?))');
            $stm->execute([$userId, $productId, $productId, $productId]);
        }
    }

    redirect($previousPage);
}