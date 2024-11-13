<?php
require '../../../include/_base.php';
require '../../../include/_head.php';

extract((array)$_user);

$order_id = req('id');

if (is_post()) {
    $action = req('action');

    if ($action == 'orderRefund') {
        $stm = $_db->prepare('UPDATE `order` SET refund_status = "request" WHERE id = ?');
        $stm->execute([$order_id]);
    }
}

$stm = $_db->prepare('SELECT * , p.type AS product_type,p.id AS productId FROM `order` o 
                      JOIN item i ON i.order_id = o.id 
                      JOIN product p ON p.id = i.product_id 
                      LEFT JOIN product_image_video piv ON piv.product_id = p.id AND piv.type = "photo"
                      WHERE o.id = ? AND o.user_id = ?');
$stm->execute([$order_id, $_user->id]);
$items = $stm->fetchAll();

$total = 0.0;
$totalQuantity = 0;
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

        <?php if(!empty($items)) { ?>
        <div class="orderDetail-container">
            <div class="goBack-orderID flex justify-between">
                <a href="/pages/customer/account/transaction.php">
                    <button class="goBack items-center flex">

                        <img src="/img/icon&logo/arrow-left_.png" alt="">
                        BACK
                    </button>
                </a>
                <div class="order-id">
                    Order ID : <?= $items[0]->order_id ?>
                </div>
            </div>

            <hr>

            <div class="delivery-status-container flex justify-center">
                <div class="step-wizard flex justify-center items-center">
                    <ul class="step-wizard-list flex">
                        <?php if ($items[0]->product_type != "Game") { ?>
                            <?php if ($items[0]->refund_status == null) { ?>
                                <li class="step-wizard-item flex <?= $items[0]->status == "pending" ? "current-item" : "" ?>">
                                    <span class="progress-count">1</span>
                                    <span class="progress-label">Pending</span>
                                </li>
                                <li class="step-wizard-item flex <?= $items[0]->status == "shipping" ? "current-item" : "" ?>">
                                    <span class="progress-count">2</span>
                                    <span class="progress-label">Shipping</span>
                                </li>
                                <li class="step-wizard-item flex <?= $items[0]->status == "completed" ? "current-item" : "" ?>">
                                    <span class="progress-count">3</span>
                                    <span class="progress-label">Completed</span>
                                </li>
                            <?php } else if ($items[0]->refund_status != null) { ?>
                                <li class="step-wizard-item flex">
                                    <span class="progress-count">1</span>
                                    <span class="progress-label">Request</span>
                                </li>
                                <li class="step-wizard-item flex <?= !($items[0]->refund_status == "declined" || $items[0]->refund_status == "approved") ? "current-item" : "" ?>">
                                    <span class="progress-count">2</span>
                                    <span class="progress-label">Pending</span>
                                </li>
                                <li class="step-wizard-item flex <?= ($items[0]->refund_status == "declined" || $items[0]->refund_status == "approved") ? "current-item" : "" ?>">
                                    <span class="progress-count">3</span>
                                    <span class="progress-label"><?= ($items[0]->refund_status == "declined" || $items[0]->refund_status == "approved") ? $items[0]->refund_status : "Result" ?></span>
                                </li>
                            <?php } ?>
                        <?php } else { ?>
                            <li class="step-wizard-item flex current-item">
                                <span class="progress-count">1</span>
                                <span class="progress-label">Completed</span>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>


            <!-- <div class="delivery-status-container">//current-item
                <div class="cancellation">
                    Cancellation Completed
                </div>
            </div> -->

            <hr>
            <form method="post">
                <?php if ($items[0]->status == "completed") { ?>
                    <div class="request-refund-container flex justify-end">
                        <?php if ($items[0]->product_type != "Game") { ?>
                        <?php if ($items[0]->status == "completed") ?>
                        <?php if ($items[0]->refund_status == "request") { ?>
                            <button class="request-refund" disabled>
                                Refund Request Sent 
                            </button>
                        <?php } else if ($items[0]->refund_status == "approved") { ?>
                            <button class="request-refund" disabled>
                                Refund Request Approved
                            </button>
                        <?php } else if ($items[0]->refund_status == "declined") { ?>
                            <button class="request-refund" disabled>
                                Refund Request Declined
                            </button>
                        <?php } else { ?>
                            <button type="submit" class="request-refund" name="action" value="orderRefund">
                                Request For Refund/Return
                            </button>
                        <?php }} ?>

                    </div>
                <?php }  ?>
            </form>


            <!-- 
            <div class="buyAgain-container flex justify-end">
                <button class="buyAgain">
                    Buy Again
                </button>
            </div> -->

            <hr>

            <form action="" method="get">
                <div class="delivery-address-container">
                    <div class="delivery-address-title">
                        Deliver Address
                    </div>

                    <div class="delivery-details flex justify-even">
                        <div class="delivery-name">
                            Name : <?= $_user->username ?>
                        </div>
                        <div class="delivery-phoneNo">
                            Phone No. : <?= $_user->phone_number ?>
                        </div>
                        <div class="delivery-address">
                            Address : <?= $_user->address ?>
                        </div>
                    </div>
                </div>
            </form>

            <hr>

            <?php foreach ($items as $item) {
                $total += $item->subtotal;
                $totalQuantity += 1;
                $url = explode(', ', $item->url);
                $image_url = trim($url[0]);

                // Check if the image URL is set and not empty, if not, use a default image
                $image_url = (isset($image_url) && $image_url != "") ? $image_url : "noImageFound.jpg";
            ?>

                <div class="order-details flex justify-between items-center">
                    <div class="flex">
                        <a href="/pages/product/productDetail.php?id=<?= $item->productId ?>">
                            <img src="/img/<?= $item->product_type == "Game" ? "game/" : "accessories/" ?><?= $image_url ?>" alt="">
                        </a>
                        <div class="order-des flex justify-center">
                            <div>
                                <?= $item->name ?>
                            </div>
                            <div>
                                Quantity : <?= $item->quantity ?>
                            </div>
                        </div>
                    </div>



                    <div>

                        MYR <?= number_format($item->price, 2) ?>
                    </div>

                </div>
            <?php } ?>

            <hr>

            <div class="check-out-summary flex justify-end">
                Order Total ( <?= $totalQuantity ?> items) : MYR <?= $total ?>
            </div>
        </div>

    </div>
    <?php } else { ?>
        <h1 class="profile flex justify-between">Order Not Found</h1>
       <?php } ?>
</div>

<?php
include '../../../include/_footer.php';
