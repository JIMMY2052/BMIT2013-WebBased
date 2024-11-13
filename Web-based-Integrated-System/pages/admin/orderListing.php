<?php
require '../../include/_base.php';
auth('admin', 'superAdmin');
$title = 'OrderListing';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';
require_once '../../lib/SimplePager.php';


$fields = [
    'id'                 => 'Order ID',
    'user_id'      => 'Customer',
    'total_amount'       => 'Total Amount',
    'payment_method'     => 'Payment Method',
    'status'             => 'Status',
    'refund_status'      => 'Refund Status',
    'last_date_operate'         => 'Created At',
];


$stm = $_db->query('SELECT * FROM users');
$customers = $stm->fetchAll();

$page = req('page', 1);
$orderId = req('orderid');
$status = req('status');
$sort = req('sort');
if ($orderId == null && $sort == null) {
    unset($_SESSION["order_id"]);
}

if ($status == null && $sort == null) {
    unset($_SESSION["status"]);
}

key_exists($sort, $fields) || $sort = 'id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';


if ($orderId != null) {
    $_SESSION["order_id"] = $orderId;
}

if ($status != null) {
    $_SESSION["status"] = $status;
}

$query = "SELECT * FROM `order`";
$queryParams = [];

if ($status !== 'all' && $status != null) {
    $query .= " WHERE `status` = ?";
    $queryParams[] = $status;
}

$query .= " ORDER BY $sort $dir";

$p = new SimplePager($query, $queryParams, '10', $page);
$orders = $p->result;


$ord_ID = $_SESSION["order_id"] ?? null;
if ($ord_ID  != null) {

    if (strlen($ord_ID) == 1) {
        $likePattern = "$ord_ID%";
    } else {
        $likePattern = "%$ord_ID%";
    }

    $p = new SimplePager("SELECT * FROM `order` WHERE id LIKE ? ORDER BY $sort $dir", [$likePattern], '10', $page);
    $orders = $p->result;
}

$status = $_SESSION["status"] ?? null;
if ($status  != null && $status != 'all') {
    $p = new SimplePager("SELECT * FROM `order` WHERE `status` = ? ORDER BY $sort $dir", [$status], '10', $page);
    $orders = $p->result;
}

$stm = $_db->prepare('SELECT COUNT(*) FROM `order` WHERE `status` = ?');
$stm->execute(['Completed']);
$count_order_completed = $stm->fetchColumn();

$stm = $_db->prepare('SELECT COUNT(*) FROM `order` WHERE `status` = ?');
$stm->execute(['pending']);
$count_order_pending = $stm->fetchColumn();

$stm = $_db->prepare('SELECT COUNT(*) FROM `order` WHERE `status` = ?');
$stm->execute(['cancelled']);
$count_order_cancel = $stm->fetchColumn();
?>

<div class="container">
    <h2>Order Listing</h2>
    <div class="container">

        <div class="order-statistics">

            <div class="stat-box">
                <img src="/img/adminIcon/completedOrder.png" alt="Order Completed">
                <div>
                    <p>Order Completed</p>
                    <h3> <?= $count_order_completed; ?></h3>
                </div>
            </div>

            <div class="stat-box">
                <img src="/img/adminIcon/pendingOrder.png" alt="Order Pending">
                <div>
                    <p>Order Pending</p>
                    <h3><?= $count_order_pending; ?></h3>
                </div>
            </div>

            <div class="stat-box">
                <img src="/img/adminIcon/cancelOrder.png" alt="Order Cancel">
                <div>
                    <p>Order Cancel</p>
                    <h3><?= $count_order_cancel; ?></h3>
                </div>
            </div>

        </div>


    </div>
    <div class="status-filter">
        <a href="?status=all" class="<?= ($status == 'all') ? 'active' : '' ?>">All orders</a>
        <a href="?status=completed" class="<?= ($status == 'completed') ? 'active' : '' ?>">Completed</a>
        <a href="?status=pending" class="<?= ($status == 'pending') ? 'active' : '' ?>">Pending</a>
        <a href="?status=shipping" class="<?= ($status == 'shipping') ? 'active' : '' ?>">Shipping</a>
        <a href="?status=cancelled" class="<?= ($status == 'cancelled') ? 'active' : '' ?>">Cancelled</a>
    </div>

    <div class="form-container">

        <form>

            <?= html_search('orderid', 'placeholder="Search Order ID"'); ?>
        </form>
        <p>
            <?= $p->count ?> of <?= $p->item_count ?> record(s) |
            Page <?= $p->page ?> of <?= $p->page_count ?>
        </p>
    </div>
    <table class="table">
        <thead>
            <tr>
                <?= table_headers($fields, $sort, $dir, "page=$page"); ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order):
                foreach ($customers as $cust) {
                    if ($cust->id ==  $order->user_id) {
                        $custName = $cust->username;
                    }
                }
            ?>
                <tr>
                    <td><?= $order->id; ?></td>
                    <td><?= $custName ?><?= "($order->user_id)" ?></td>
                    <td>RM <?= number_format($order->total_amount, 2); ?></td>
                    <td><?= encode($order->payment_method); ?></td>
                    <td><span class="status <?= strtolower($order->status); ?>"><?= ucfirst($order->status) ?></span></td>
                    <td>
                        <?php
                        if ($order->refund_status == null) {
                            echo '&nbsp &nbsp &nbsp &nbsp &nbsp -';
                        } else { ?>
                            <span class="status <?= strtolower($order->refund_status); ?>"><?= ucfirst($order->refund_status) ?>
                    </td>
                <?php } ?>
                <td><?= date('d M Y', strtotime($order->last_date_operate)); ?></td>

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


<?= $p->html("sort=$sort&dir=$dir") ?>

<?php
include '../../include/_adminFooter.php';
