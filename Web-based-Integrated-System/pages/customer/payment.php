<?php
require '../../include/_base.php';
require '../../include/_head.php';

?>

<div class="w-full" style="background-color: #f5f5f5;">
    <div class="payment-container">
        <div class="payment-title">
            Check Out
        </div>
        <hr>
        <form action="" method="get">
            <div class="delivery-address-container flex justify-between items-center">
                <div>
                    <div class="delivery-address-title">
                        Deliver Address
                    </div>

                    <div class="delivery-details flex justify-even">
                        <div class="delivery-name">
                            Name : Loo Jie Yang
                        </div>
                        <div class="delivery-phoneNo">
                            Phone No. : 01158618591
                        </div>
                        <div class="delivery-address">
                            Address : B-20-9 , PV13 Block B Platinum Lake Condominium, W.P. Kuala Lumpur, 53300 W.P. Kuala Lumpur
                        </div>
                    </div>
                </div>

                <a href="/pages/customer/account/profile.php" class="delivery-edit">Edit</a>
            </div>
        </form>

        <hr>

        <div class="payment-table">
            <div class="payment-th flex justify-between">
                <div class="product-name-th">
                    Product(s) Ordered
                </div>
                <div class="flex">
                    <div class="product-per-price-th flex justify-end">
                        Unit Price
                    </div>
                    <div class="product-amount-th flex justify-end">
                        Amount
                    </div>
                    <div class="product-subtotal-th flex justify-end">
                        Subtotal
                    </div>
                </div>

            </div>

            <div class="payment-td flex justify-between items-center">
                <div class="product-name-td flex items-center">
                    <img src="/img/game/blackMythWukong-1.jpg" alt="">
                    <input type="text" class="product-name" disabled>
                </div>
                <div class="flex">
                    <div class="product-per-price-td  flex justify-end">
                        MYR219
                    </div>
                    <div class="product-amount-td flex justify-end">
                        2
                    </div>
                    <div class="product-subtotal-td flex justify-end">
                        MYR438
                    </div>
                </div>

            </div>

            <div class="payment-td flex justify-between items-center">
                <div class="product-name-td flex items-center">
                    <img src="/img/game/blackMythWukong-1.jpg" alt="">
                    <input type="text" class="product-name" disabled>
                </div>
                <div class="flex">
                    <div class="product-per-price-td  flex justify-end">
                        MYR219
                    </div>
                    <div class="product-amount-td flex justify-end">
                        2
                    </div>
                    <div class="product-subtotal-td flex justify-end">
                        MYR438
                    </div>
                </div>
            </div>

            <hr>

            <div class="check-out-summary flex justify-end">
                Order Total (2 items) : MYR 438
            </div>
            <div class="flex justify-end">
                <button class="btn full-rounded" type="submit">
                    <span>Check Out</span>
                    <div class="border full-rounded"></div>
                </button>
            </div>
        </div>
    </div>
</div>

<?php
include '../../include/_footer.php';
?>