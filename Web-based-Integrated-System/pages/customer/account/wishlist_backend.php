<?php
require '../../../include/_base.php';

if (is_post()) {
    $productId = req('product_id'); // Fetch product ID from the form
    $userId = req('user_id');
    $action = req('action');

    if ($action == "remove") {

        $stm = $_db->prepare('DELETE FROM wish_list WHERE user_id = ? AND product_id = ?');
        $stm->execute([$userId,$productId]);

    } else if ($action == "addToCart") {


        $stm = $_db->prepare('SELECT * FROM `cart` WHERE user_id = ? AND product_id = ?;');
        $checkCart = $stm->execute([$userId, $productId]);

        if (empty($checkCart->$row)) {
            $stm = $_db->prepare('SELECT * FROM product WHERE id = ?');
            $stm->execute([$productId]);
            $temp = $stm->fetch();
            $stm = $_db->prepare('INSERT INTO `cart`(`user_id`, `product_id`,`price`,`quantity`,`subtotal`,`last_date_operate`) VALUES (?,?,?,1,?,?)');
            $stm->execute([$userId, $productId, $temp->price, $temp->price, "NOW()"]);
        }else{
            $stm = $_db->prepare('UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?');
            $stm->execute([$userId,$productId]);
         }
        // $stm = $_db->prepare('DELETE FROM wish_list WHERE user_id = ? AND product_id = ?');
        // $stm->execute([$userId,$productId]);
        
    }

    redirect('wishlist.php');
}