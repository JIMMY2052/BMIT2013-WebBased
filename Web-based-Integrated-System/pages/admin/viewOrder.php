<?php
require '../../include/_base.php';
ob_start();
auth('admin', 'superAdmin');
$title = 'Order Details';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';

$orderID = req('orderid');

$stm = $_db->prepare("SELECT * FROM `order` WHERE id = ?");
$stm->execute([$orderID]);
$order = $stm->fetch();


$customerStm = $_db->prepare("SELECT * FROM users WHERE id = ?");
$customerStm->execute([$order->user_id]);
$customer = $customerStm->fetch();

$orderItemsStm = $_db->prepare("SELECT * FROM item WHERE order_id = ?");
$orderItemsStm->execute([$order->id]);
$orderItems = $orderItemsStm->fetchAll();

$ordered_products = [];
$is_Game = 0;
$grandTotal = 0;
foreach ($orderItems as $item) {
    $order_product_stm = $_db->prepare("SELECT * FROM product WHERE id = ?");
    $order_product_stm->execute([$item->product_id]);
    $product = $order_product_stm->fetch();

    $image_stm = $_db->prepare("SELECT * FROM product_image_video WHERE product_id = ?");
    $image_stm->execute([$item->product_id]);
    $product_images_record = $image_stm->fetch();
    $product_images = $product_images_record ? $product_images_record->url : null;
    $urlArray = explode(', ', $product_images);
    $first_image_url = trim($urlArray[0]);

    $ordered_products[] = [
        'productId' => $product->id,
        'description' => $product->description,
        'type' => $product->type,
        'images' => $first_image_url,
        'quantity' => $item->quantity,
        'subtotal' => $item->subtotal,
        'price' => $item->price
    ];
    $grandTotal += ($item->price) * $item->quantity;
    if ($product->type == 'Game') {
        $is_Game = 1;
    }
}

if (is_post()) {
    $new_status = req('status');
    $new_refund_status = req('refund_status');


    if (isset($new_refund_status)) {
        if ($new_refund_status === 'approved') {
            $new_status = 'cancelled';
        } elseif ($new_refund_status === 'declined') {
            $new_status = 'completed';
        }


        $updateStm = $_db->prepare("UPDATE `order` SET `status` = ?, refund_status = ? WHERE id = ?");
        $updateStm->execute([strtolower($new_status), strtolower($new_refund_status), $order->id]);

        temp('info', "Refund status updated to '$new_refund_status' and order status automatically set to '$new_status'");
        header('Location: ' . $_SERVER['PHP_SELF'] . '?orderid=' . $orderID);
        exit;
    }


    if (isset($new_status)) {
        $updateStm = $_db->prepare("UPDATE `order` SET `status` = ? WHERE id = ?");
        $updateStm->execute([strtolower($new_status), $order->id]);

        temp('info', "Order status updated to '$new_status'");
        header('Location: ' . $_SERVER['PHP_SELF'] . '?orderid=' . $orderID);
        exit;
    }
}


?>

<div class="container order-details">
    <h2>Order Details</h2>

    <div class="order-overview">
        <div class="order-info-box">
            <h4>Order Details</h4>
            <p><strong>Order ID: </strong>(#<?= $order->id ?>)</p>
            <p><strong>Order Type: </strong> <?php if ($is_Game > 0) {
                                                    echo 'Game';
                                                } else {
                                                    echo 'Accessories';
                                                }  ?></p>
            <p><strong>Date Added:</strong> <?= date('d/m/Y', strtotime($order->last_date_operate)); ?></p>
            <p><strong>Payment Method:</strong> <?= $order->payment_method ?></p>
        </div>
        <div class="customer-info-box">
            <h4>Customer Details</h4>
            <p><strong>Customer:</strong> <?= $customer->username ?> </p>
            <p><strong>Customer ID :</strong> <?= $customer->id ?></p>
            <p><strong>Email:</strong> <?= $customer->email ?></p>
            <p><strong>Phone:</strong> <?= $customer->phone_number ?></p>
        </div>
    </div>

    <?php if ($is_Game == 0): ?>
        <div class="address-section">
            <div class="shipping-address">
                <h4>Shipping Address</h4>
                <p><?= $order->shipping_address ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($is_Game == 0) { ?>
        <div class="status-section">
            <form method="post" id="status-form">
                <h4>Change Order Status</h4>
                <select name="status" id="status" class="status-dropdown">
                    <option value="pending" <?= (strtolower($order->status) == 'pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= (strtolower($order->status) == 'completed') ? 'selected' : '' ?>>Completed</option>
                    <option value="shipping" <?= (strtolower($order->status) == 'shipping') ? 'selected' : '' ?>>Shipping</option>
                    <option value="cancelled" <?= (strtolower($order->status) == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                </select>

                <?php if (strtolower($order->refund_status) == 'request'): ?>
                    <h4>Change Refund Status</h4>
                    <select name="refund_status" id="refund_status" class="status-dropdown">
                        <option value="request" <?= (strtolower($order->refund_status) == 'request') ? 'selected' : '' ?>>Request for Refund</option>
                        <option value="approved" <?= (strtolower($order->refund_status) == 'approved') ? 'selected' : '' ?>>Refund Approved</option>
                        <option value="declined" <?= (strtolower($order->refund_status) == 'declined') ? 'selected' : '' ?>>Refund Declined</option>
                    </select>
                <?php endif; ?>

                <button type="submit" class="update-button">Update Status</button>
            </form>
        </div>
    <?php  }  ?>

    <div class="order-items-section">
        <h4>Order #<?= $order->id ?></h4>
        <table class="orderTable">
            <thead class="theadTable">
                <tr>
                    <th>Product</th>
                    <th>Images</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ordered_products as $product_data): ?>
                    <tr>
                        <td><?= $product_data['productId'] ?></td>
                        <td><img src="/img/<?= $product_data['type'] === 'Game' ? 'game' : 'accessories' ?>/<?= $product_data['images'] = isset($product_data['images'])&& $product_data['images']!="" ? $product_data['images'] : "default.jpg" ?>" alt="Product Image" style="width:100px; height:100px;"></td>
                        <td><?= $product_data['description'] ?></td>
                        <td><?= $product_data['type'] ?></td>
                        <td><?= $product_data['quantity'] ?></td>
                        <td>RM <?= number_format($product_data['price'], 2) ?></td>
                        <td>RM <?= number_format($product_data['subtotal'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="6"><strong>Grand Total</strong></td>
                    <td><strong>RM <?= number_format($grandTotal, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#status-form').on('submit', function(e) {
            var refundStatus = $('#refund_status').val();

            if (refundStatus === 'approved' || refundStatus === 'declined') {
                var confirmationMessage = (refundStatus === 'approved') ?
                    "Are you sure you want to approve this refund?" :
                    "Are you sure you want to decline this refund?";

                var confirmed = confirm(confirmationMessage);
                if (!confirmed) {
                    e.preventDefault();
                }
            }
        });
    });
</script>
<?php
include '../../include/_adminFooter.php';
ob_end_flush();
