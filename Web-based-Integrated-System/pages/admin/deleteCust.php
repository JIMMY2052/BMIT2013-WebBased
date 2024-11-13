<?php
require '../../include/_base.php';
auth('admin', 'superAdmin');

if (is_post()) {
    $id = req('id');

    $stm = $_db->prepare('UPDATE users SET is_active = ? WHERE id = ?');
        $stm->execute(["0",$id]);

    temp('info', "Customer account [$id] deleted");
}

redirect('customerListing.php');