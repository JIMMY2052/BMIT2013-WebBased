<?php
require '../../include/_base.php';


if (is_post()) {
    $id = req('id');

    $stm = $_db->prepare('UPDATE product SET is_active = ? WHERE id = ?;');
        $stm->execute(["0",$id]);

    temp('info', "Record [$id] deleted");
}

redirect('productListing.php');