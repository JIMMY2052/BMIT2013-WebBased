<?php
require '../../include/_base.php';
auth('superAdmin');

if (is_post()) {
    $id = req('id');

    $stm = $_db->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stm->execute(["blockedAdmin",$id]);

    temp('info', "Admin [$id] has been blocked");
}

redirect('adminBlockList.php');