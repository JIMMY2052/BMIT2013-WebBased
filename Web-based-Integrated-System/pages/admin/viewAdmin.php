<?php
require '../../include/_base.php';
auth('superAdmin');
$title = 'View Admin Profile';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';
require_once '../../lib/SimplePager.php';
$page = req('page', 1);

if (is_get()) {
    $id = req('id');


    $stm = $_db->prepare('SELECT * FROM users WHERE id = ?');
    $stm->execute([$id]);
    $admin = $stm->fetch();


    if (!$admin) {
        redirect('adminListing.php');
        temp('info', "Not found the record");
    }
}


$dob = new DateTime($admin->dob);
$today = new DateTime();
$age = $today->diff($dob)->y;

?>

<div class="container">
    <div class="profile-section">
        <div class="profile-header">
            <img src="/img/userPic/<?= isset($_user->profile_picture) ? $_user->profile_picture : 'defaultUser.png' ?>" alt="Admin Photo" class="profile-pic">
            <div class="customer-info">

                <p><strong>Account ID &nbsp &nbsp:</strong> #<?= $admin->id ?></p>
                <p><strong>Email&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp: </strong><?= $admin->email; ?></p>
                <p><strong>Phone&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp: </strong><?= $admin->phone_number; ?></p>
                <p><strong>D.O.B&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp: </strong><?= isset($admin->dob) ? date('d-m-Y', strtotime($admin->dob)) : '-'; ?><?= isset($age) ? "($age yrs)" : '' ?></p>
                <p><strong>Registered Date&nbsp: </strong><?= date('d-m-Y', strtotime($admin->last_date_operate)); ?></p>
            </div>
        </div>

        <div class="customer-details">
            <p><?= ($admin->role == 'admin') ? '<span class="status active">Active Admin</span>' : '<span class="status blocked">Blocked Admin</span>'; ?></p>
        </div>
    </div>


</div>


<?php
include '../../include/_adminFooter.php';
