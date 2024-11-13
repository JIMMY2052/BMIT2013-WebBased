<?php
require '../../include/_base.php';
auth('admin', 'superAdmin');
$title = 'Accessories Orders Listing';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';
require_once '../../lib/SimplePager.php';

$fields = [
    'id'                 => 'Order ID',
    'user_id'            => 'Customer',
    'total_amount'       => 'Total Amount',
    'payment_method'     => 'Payment Method',
    'status'             => 'Status',
    'refund_status'      => 'Refund Status',
    'last_date_operate'  => 'Created At',
];

$stm = $_db->query('SELECT * FROM users');
$customers = $stm->fetchAll();

$page = req('page', 1);
$orderId = req('orderid');
$status = req('status', 'all');
$sort = req('sort');
key_exists($sort, $fields) || $sort = 'id';
$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';


$query = "SELECT DISTINCT o.* FROM `order` o JOIN item i ON o.id = i.order_id JOIN product p ON i.product_id = p.id WHERE p.type = 'Accessories'";

$queryParams = [];
if ($orderId) {
    $query .= " AND o.id LIKE ?";
    $queryParams[] = "%$orderId%";
}


if ($status !== 'all') {
    $query .= " AND o.status = ?";
    $queryParams[] = strtolower($status);
}

$query .= " ORDER BY o.$sort $dir";


$p = new SimplePager($query, $queryParams, '10', $page);
$orders = $p->result;


$stm = $_db->prepare('SELECT COUNT(*) FROM `order` o JOIN item i ON o.id = i.order_id JOIN product p ON i.product_id = p.id WHERE p.type = ?');
$stm->execute(['Accessories']);
$count_accessories_orders = $stm->fetchColumn();


?>

<div class="container">
    <h2>Accessories Order Listing</h2>
    <div class="container">

        <div class="order-statistics">
            <div class="game-stat-box">
                <img src="/img/adminIcon/accessoriesIcon.svg" alt="Accessories Orders">
                <div>
                    <p>Accessories Order</p>
                    <h3><?= $count_accessories_orders; ?></h3>
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
        <form method="get">
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
                            echo '-';
                        } else { ?>
                            <span class="status <?= strtolower($order->refund_status); ?>"><?= ucfirst($order->refund_status) ?></span>
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

<!-- Pagination -->
<?= $p->html("sort=$sort&dir=$dir&status=$status") ?>

<?php
include '../../include/_adminFooter.php';
