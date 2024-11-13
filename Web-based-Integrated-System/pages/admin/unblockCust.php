<?php
require '../../include/_base.php';


if (is_post()) {
    $id = req('id');

    $stm = $_db->prepare('UPDATE users SET role = ?, error_login = ? WHERE id = ?');
        $stm->execute(["customer",0,$id]);

    temp('info', "Customer [$id] has been unblocked");
}

redirect('customerListing.php');