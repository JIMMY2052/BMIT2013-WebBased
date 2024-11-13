<?php
require '../../include/_base.php';


if (is_post()) {
    $id = req('id');

    $stm = $_db->prepare('UPDATE users SET role = ?, error_login = ? WHERE id = ?');
        $stm->execute(["admin",0,$id]);

    temp('info', "Admin [$id] has been unblocked");
}

redirect('adminListing.php');