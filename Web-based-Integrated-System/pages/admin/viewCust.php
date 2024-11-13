<?php
require '../../include/_base.php';
auth('admin', 'superAdmin');
$title = 'View Customer';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';
require_once '../../lib/SimplePager.php';
$page = req('page', 1);

if (is_get()) {
    $id = req('id');


    $stm = $_db->prepare('SELECT * FROM users WHERE id = ?');
    $stm->execute([$id]);
    $cust = $stm->fetch();


    if (!$cust) {
        redirect('customerListing.php');
        temp('info', "Not found the record");
    }
}


$p = new SimplePager("SELECT * FROM `order` WHERE user_id = ?", [$id], '5', $page);
$porders = $p->result;


$stm = $_db->prepare('SELECT * FROM `order` WHERE user_id = ?');
$stm->execute([$cust->id]);
$orders = $stm->fetchAll();


$stm = $_db->prepare('SELECT COUNT(*) FROM `order` WHERE user_id = ? AND status = "Completed"');
$stm->execute([$cust->id]);
$count_Completed_Orders = $stm->fetchColumn();

$totalExpense = 0;

foreach ($orders as $order) {
    if ($order->status == 'Completed') {
        $totalExpense += $order->total_amount;
    }
}

$dob = new DateTime($cust->dob);
$today = new DateTime();
$age = $today->diff($dob)->y;

?>

<div class="container">
    <div class="profile-section">
        <div class="profile-header">
            <img src="/img/userPic/<?= isset($cust->profile_picture) ? $cust->profile_picture : 'defaultUser.png'; ?>" alt="Customer Photo" class="profile-pic" />
            <div class="customer-info">
                <h2><?= $cust->username ?> <?= $cust->is_verified ? '<img src="/img/adminIcon/verified.svg" alt="Verified">' : '<img src="/img/adminIcon/notVerified.svg" alt="not Verified">'; ?></h2>
                <p><strong>Account ID &nbsp &nbsp:</strong> #<?= $cust->id ?></p>
                <p><strong>Email&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp: </strong><?= $cust->email; ?></p>
                <p><strong>Phone&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp: </strong><?= $cust->phone_number; ?></p>
                <p><strong>D.O.B&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp: </strong><?= date('d-m-Y', strtotime($cust->dob)); ?><?= " ($age yrs)" ?></p>
                <p><strong>Joined Date&nbsp: </strong><?= date('d-m-Y', strtotime($cust->last_date_operate)); ?></p>
            </div>
        </div>

        <div class="customer-details">
            <p><?= ($cust->role == 'customer' && $cust->error_login <= 3) ? '<span class="status active">Active User</span>' : '<span class="status blocked">Blocked User</span>'; ?></p>
        </div>
    </div>

    <div class="cust-statistics">
        <div class="stat-box">
            <img src="/img/adminIcon/receipt.svg" alt="Total Invoice Icon">
            <div>
                <p>Total Invoice</p>
                <h3><?= count($orders); ?></h3>
            </div>
        </div>
        <div class="stat-box">
            <img src="/img/adminIcon/package.svg" alt="Total Order Icon">
            <div>
                <p>Total Completed Order</p>
                <h3><?= $count_Completed_Orders ?></h3>
            </div>
        </div>
        <div class="stat-box">
            <img src="/img/adminIcon/expense.svg" alt="Total Expense Icon">
            <div>
                <p>Total Expense</p>
                <h3>RM <?= number_format($totalExpense, 2); ?></h3>
            </div>
        </div>
    </div>

    <div class="transaction-history">
        <h3>Transaction History</h3>
        <p>
            <?= $p->count ?> of <?= $p->item_count ?> record(s) |
            Page <?= $p->page ?> of <?= $p->page_count ?>
        </p>
        <table>
            <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>Status</th>
                    <th>Total Amount</th>
                    <th>Order Date</th>
                    <th>Payment Method</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($porders as $order): ?>
                    <tr>
                        <td> <?= $order->id ?> </td>
                        <td><span class='status <?= ucfirst($order->status); ?>'><?= ucfirst($order->status); ?></span></td>
                        <td>RM <?= number_format($order->total_amount, 2); ?></td>
                        <td><?= date('d M Y', strtotime($order->last_date_operate)); ?></td>
                        <td><?= $order->payment_method ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view-btn" data-get="viewOrder.php?orderid=<?= $order->id ?>" title="View">
                                    <img src="/img/adminIcon/view.svg" alt="View" title="View">
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>

<?= $p->html("id=$id") ?>


<?php
include '../../include/_adminFooter.php';
