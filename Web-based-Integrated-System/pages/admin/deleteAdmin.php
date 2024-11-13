<?php
require '../../include/_base.php';
auth('superAdmin');

if (is_post()) {
    $id = req('id');

    $stm = $_db->prepare('UPDATE users SET is_active = ? WHERE id = ?');
        $stm->execute(["0",$id]);

    temp('info', "Admin account [$id] deleted");
}

redirect('adminListing.php');