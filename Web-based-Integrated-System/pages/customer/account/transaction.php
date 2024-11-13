<?php
require '../../../include/_base.php';

require '../../../include/_head.php';

auth('customer');
extract((array)$_user);
if (is_post()) {
    $action = req('action');
    $order_id = req('order_id');

    if ($action == 'orderRefund') {
        $stm = $_db->prepare('UPDATE `order` SET refund_status = "request" WHERE id = ?');
        $stm->execute([$order_id]);
    }
}

$stm = $_db->prepare('SELECT * FROM `order` WHERE user_id = ?');
$stm->execute([$_user->id]);
$orderList = $stm->fetchAll();

$searchOrder = [];
$filterOrder = [];
$gotSearch = false;
$gotFilter = false;
if (is_post()) {
    $search = req('transaction-search');
    if ($search != "") {
        $gotSearch = true;
        $stm = $_db->prepare(
            'SELECT DISTINCT o.* FROM `order` o
             JOIN item i ON i.order_id = o.id
             JOIN product p ON p.id = i.product_id
             WHERE o.user_id = ? AND p.name LIKE ?'
        );
        $searchTarget = '%' . $search . '%';
        // Try to execute the query and handle potential errors
        $stm->execute([$_user->id, $searchTarget]);
        $searchOrder = $stm->fetchAll();
    }
    $filterTargetStatus = req('status');
    $filterTargetRefundStatus = req('refund_status');
    if ($filterTargetStatus != "" && $filterTargetStatus != "all") {
        $gotFilter = true;
        $stm = $_db->prepare(
            'SELECT DISTINCT o.* FROM `order` o
             JOIN item i ON i.order_id = o.id
             JOIN product p ON p.id = i.product_id
             WHERE o.user_id = ? AND o.status = ?'
        );
        $searchTarget = '%' . $search . '%';
        $stm->execute([$_user->id, $filterTargetStatus]);
        $filterOrder = $stm->fetchAll();
    } else if ($filterTargetRefundStatus != "" && $filterTargetRefundStatus != "all") {
        $gotFilter = true;
        $stm = $_db->prepare(
            'SELECT DISTINCT o.* FROM `order` o
             JOIN item i ON i.order_id = o.id
             JOIN product p ON p.id = i.product_id
             WHERE o.user_id = ? AND o.refund_status = ?'
        );
        $searchTarget = '%' . $search . '%';
        $stm->execute([$_user->id, $filterTargetRefundStatus]);
        $filterOrder = $stm->fetchAll();
    }
}

$finalOrderList = $orderList;

if (!empty($searchOrder) && $gotSearch) {
    $finalOrderList = $searchOrder;
} else if (!empty($filterOrder) && $gotFilter) {
    $finalOrderList = $filterOrder;
} else if (empty($searchOrder) && $gotSearch) {
    $finalOrderList = [];
} else if (empty($filterOrder) && $gotFilter) {
    $finalOrderList = [];
}

?>

<div class="w-full">
    <div class="profile flex justify-between">
        <div class="sidebar flex ">
            <div class="account">
                <a href="profile.php">ACCOUNT</a>
            </div>
            <hr>
            <div class="change-password">
                <a href="change-password.php">CHANGE PASSWORD</a>
            </div>
            <hr>
            <div class="transaction">
                <a href="" style="color: #0077ed;">MY ORDERS</a>
            </div>
        </div>

        <form method="post">
            <div class="transaction-container flex">
                <div class="transaction-header flex items-center">
                    <button class="all flex justify-center items-center" name="status" value="all">
                        All
                    </button>
                    <button class="packaging flex justify-center items-center" name="status" value="completed">
                        Completed
                    </button>
                    <button class="shipping flex justify-center items-center" name="status" value="pending">
                        Pending
                    </button>
                    <button class="delivered flex justify-center items-center" name="status" value="shipping">
                        Shipping
                    </button>
                    <button class="cancelled flex justify-center items-center" name="status" value="cancelled">
                        Cancelled
                    </button>
                    <button class="cancelled flex justify-center items-center" name="refund_status" value="request">
                        Request Refund
                    </button>
                    <button class="cancelled flex justify-center items-center" name="refund_status" value="approved">
                        Approved
                    </button>
                    <button class="return flex justify-center items-center" name="refund_status" value="declined">
                        Declined
                    </button>
                </div>
        </form>

        <form action="" method="post">
            <?= html_search('transaction-search', 'placeholder="Search"') ?>
        </form>

        <?php if ($finalOrderList) { ?>
            <?php foreach ($finalOrderList as $order) { ?>
                <div class="transaction-detail">
                    <form method="post">


                        <?php

                        $stm = $_db->prepare('SELECT * , p.type AS product_type FROM `order` o JOIN item i ON i.order_id = o.id 
                        JOIN product p ON p.id = i.product_id 
                        LEFT JOIN product_image_video piv ON piv.product_id = p.id AND piv.type = "photo"
                        WHERE o.id = ? AND o.user_id = ?
                        ');

                        $stm->execute([$order->id, $order->user_id]);
                        $products = $stm->fetchAll();
                        $totalQuantity = 0;
                        $orderTotal = 0.0;
                        ?>
                        <div class="transaction-info">

                            <div class="order-id flex justify-end">
                                Order ID : <?= $order->id ?>
                            </div>
                            <div class="flex justify-center">
                                <hr>
                            </div>
                            <input type="hidden" name="order_id" value="<?= $order->id ?>">

                            <?php foreach ($products as $product) {
                                $orderTotal += $product->subtotal;
                                $totalQuantity += $product->quantity;
                                $urlArray = explode(',', $product->url);
                                $first_image_url = trim($urlArray[0]);

                            ?>


                                <!-- each product in the order list -->
                                <div class="order-list flex">

                                    <img src="/img/<?= $product->product_type == 'Game' ? 'game' : 'accessories'; ?>/<?= $first_image_url = (isset($first_image_url) && $first_image_url != "") ? $first_image_url : "noImageFound.jpg" ?>" alt="">

                                    <div class="order-list-detail w-full flex justify-between">
                                        <div class="order-list-title">
                                            <?= $product->name ?>
                                        </div>
                                        <div class="flex justify-end" style="flex-direction: column;">
                                            <div class="order-quantity-price flex justify-end">
                                                <div class="order-quantity flex justify-end">
                                                    Quantity :

                                                    <div class="quantity">
                                                        <?= $product->quantity ?>
                                                    </div>
                                                </div>
                                                <div class="order-price flex ">
                                                    Total Price(RM) :

                                                    <div class="price">
                                                        <?= number_format($product->subtotal, 2) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            <?php } ?>

                            <div class="flex justify-center">
                                <hr>
                            </div>

                            <div class="order-summary flex justify-between">


                                <div class="detail-refund-buyAgain flex items-end">
                                    <a href="orderDetail.php?id=<?= $order->id ?>" class="order-detail">
                                        Order Details
                                    </a>
                                    <?php if ($products[0]->product_type != "Game") { ?>

                                        <?php if ($order->status == "completed") { ?>
                                            <?php if ($order->refund_status == "request") { ?>
                                                <button class="order-refund" disabled>
                                                    Refund Request Sent
                                                </button>
                                            <?php } else if ($order->refund_status == "approved") { ?>
                                                <button class="order-refund" disabled>
                                                    Refund Request Approved
                                                </button>
                                            <?php } else if ($order->refund_status == "declined") { ?>
                                                <button class="order-refund" disabled>
                                                    Refund Request Declined
                                                </button>
                                            <?php } else { ?>
                                                <button type="submit" class="order-refund" name="action" value="orderRefund">
                                                    Request For Refund/Return
                                                </button>
                                    <?php }
                                        }
                                    } ?>
                                </div>


                                <div class="flex justify-even" style="flex-direction: column;">
                                    <div class="order-total-quantity flex justify-end">
                                        Total Quantity :

                                        <div class="quantity">
                                            <?= $totalQuantity ?>
                                        </div>
                                    </div>
                                    <div class="order-total-price flex justify-end">
                                        Order Total :

                                        <div class="price">
                                            <?= $orderTotal ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                </form>
            <?php  }
        } else { ?>
            <?php if (!empty($orderList) && empty($searchOrder) && $gotSearch) { ?>
                <div class="empty-cart flex items-center justify-center">
                    No orders found matching your search.
                </div>
            <?php } else if (!empty($orderList) && empty($filterOrder) && $gotFilter) { ?>
                <div class="empty-cart flex items-center justify-center">
                    No orders found matching your filter.
                </div>
            <?php } else { ?>
                <div class="empty-cart flex items-center justify-center">
                    Your order is empty.
                </div>
        <?php }
        } ?>
    </div>

</div>
</div>
</div>

<?php
include '../../../include/_footer.php';

?>