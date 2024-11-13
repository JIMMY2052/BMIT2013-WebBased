<?php
require '../../include/_base.php';
auth('admin', 'superAdmin');

if (is_post()) {
    $id = req('id');

    $stm = $_db->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stm->execute(["blocked",$id]);

    temp('info', "Customer [$id] has been blocked");
}

redirect('blockList.php');