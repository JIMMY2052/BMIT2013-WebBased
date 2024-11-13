<?php
// Include Composer's autoloader to load Stripe's library
require '../../composer/vendor/autoload.php';
require '../../include/_base.php';
auth('customer');


// Set Stripe API key
\Stripe\Stripe::setApiKey('sk_test_51PzdMe05xyD6hqdgbiSjdMahFgpyZ5eYRe3KrNZI9qZvde2wjtbaUuJiC2RP2pAgpbyqPNFtjobInnploqeweZ8o00ZYXB1WXR');

$newId;
// Check if session ID exists in URL
if (isset($_GET['session_id'])) {
    $session_id = $_GET['session_id'];

    // Retrieve the session from Stripe
    try {
        $session = \Stripe\Checkout\Session::retrieve($session_id);

        // Access session details
        $payment_status = $session->payment_status;
        $customer_email = $session->customer_email;
        $total_amount = $session->amount_total / 100; // Convert from cents to the currency unit
        $payment_method = implode(", ", $session->payment_method_types); // Convert array to string

        // Check if shipping address exists before accessing it
        if (isset($session->shipping_details) && isset($session->shipping_details->address)) {
            $shipping_address = $session->shipping_details->address;

            $address_line1 = $shipping_address->line1;
            $address_line2 = $shipping_address->line2;
            $address_city = $shipping_address->city;
            $address_postal_code = $shipping_address->postal_code;
            $address_state = $shipping_address->state;
            $address_country = $shipping_address->country;

            $final_address = $address_line1.' '.$address_line2.' '.$address_postal_code.' '.$address_city.' '.$address_state;

        } 
    } catch (Exception $e) {
        exit;
    }
} else {

    exit;
}

// Process order creation in your database
$user_id = req('user_id');
$stm = $_db->prepare('SELECT * FROM cart WHERE user_id = ?');
$stm->execute([$user_id]);
$cartItems = $stm->fetchAll();
$gamesOrder = [];
$othersOrder = [];
foreach($cartItems as $item){
    $stm = $_db->prepare('SELECT `type` FROM product WHERE id = ?');
    $stm->execute([$item->product_id]);
    $temp = $stm->fetch();
    if($temp->type == 'Game'){
        $gamesOrder []= $item;
    }else{
        $othersOrder[] = $item;
    }
}

if($gamesOrder){
// Fetch last order ID to generate a new one
$stm = $_db->query("SELECT COUNT(*) AS row_count FROM `order`");
$lastId = $stm->fetch();
if ($lastId) {
    // Extract the numeric part from the ID
    $newNumericPart = $lastId->row_count +1;

    // Format the new ID by padding with leading zeros if necessary
    $newId = 'ORD' . $newNumericPart; // P002, P003, etc.
}else{
    $newId = 'ORD1';
}

// Insert the order into the database
$stm = $_db->prepare('INSERT INTO `order`(`id`, `user_id`, `total_amount`, `payment_method`, `shipping_address`, `status`, `last_date_operate`) 
    VALUES (?,?,?,?,?,?,NOW());');
$stm->execute([$newId, $user_id, $total_amount, $payment_method, $final_address, "completed"]);

// Move cart items to order items and remove them from the cart
$stm = $_db->prepare('SELECT * FROM cart WHERE user_id = ?');
$stm->execute([$user_id]);
$cartItems = $stm->fetchAll();
foreach ($gamesOrder as $item) {
    $stm = $_db->prepare('INSERT INTO item (order_id, product_id, price, quantity, subtotal, last_date_operate)
        VALUES (?, ?, ?, ?, ?, NOW());
        DELETE FROM cart WHERE user_id = ? AND product_id = ?;
    ');
    $stm->execute([
        $newId,
        $item->product_id,
        $item->price,
        $item->quantity,
        $item->subtotal,
        $user_id,
        $item->product_id
    ]);
    $stm = $_db->prepare('SELECT * FROM product WHERE id = ?');
    $stm->execute([$item->product_id]);
    $temp=$stm->fetch();
    if($temp->type != "Game"){
        $stm = $_db->prepare('UPDATE product SET stock_quantity = stock_quantity - ? WHERE id = ?');
        $stm->execute([$item->quantity,$item->product_id]);
    }}
}
if ($othersOrder){
    $stm = $_db->query("SELECT COUNT(*) AS row_count FROM `order`");
    $lastId = $stm->fetch();
    if ($lastId) {
        // Extract the numeric part from the ID
        $newNumericPart = $lastId->row_count +1;
    
        // Format the new ID by padding with leading zeros if necessary
        $newId = 'ORD' . $newNumericPart; // P002, P003, etc.
    }else{
        $newId = 'ORD1';
    }
    
    // Insert the order into the database
    $stm = $_db->prepare('INSERT INTO `order`(`id`, `user_id`, `total_amount`, `payment_method`, `shipping_address`, `status`, `last_date_operate`) 
        VALUES (?,?,?,?,?,?,NOW());');
    $stm->execute([$newId, $user_id, $total_amount, $payment_method, $final_address, "pending"]);
    
    // Move cart items to order items and remove them from the cart
    $stm = $_db->prepare('SELECT * FROM cart WHERE user_id = ?');
    $stm->execute([$user_id]);
    $cartItems = $stm->fetchAll();
    foreach ($othersOrder as $item) {
        $stm = $_db->prepare('INSERT INTO item (order_id, product_id, price, quantity, subtotal, last_date_operate)
            VALUES (?, ?, ?, ?, ?, NOW());
            DELETE FROM cart WHERE user_id = ? AND product_id = ?;
        ');
        $stm->execute([
            $newId,
            $item->product_id,
            $item->price,
            $item->quantity,
            $item->subtotal,
            $user_id,
            $item->product_id
        ]);
        $stm = $_db->prepare('SELECT * FROM product WHERE id = ?');
        $stm->execute([$item->product_id]);
        $temp=$stm->fetch();
        if($temp->type != "Game"){
            $stm = $_db->prepare('UPDATE product SET stock_quantity = stock_quantity - ? WHERE id = ?');
            $stm->execute([$item->quantity,$item->product_id]);
        }}

}

$stm = $_db->prepare('SELECT *  FROM `order` o JOIN item i ON i.order_id = o.id 
                        JOIN product p ON p.id = i.product_id 
                        LEFT JOIN product_image_video piv ON piv.product_id = p.id AND piv.type = "photo"
                        WHERE o.id = ? AND o.user_id = ?
                        ');
$stm->execute([$newId, $_user->id]);
$temp = $stm->fetchAll();
$subject = 'Order Summary - Thank you for your purchase!';
$body = "
    <div style='width: 100%;'>
        <div style='display: flex; text-align: center; justify-content: center; background-color: #0077ed; padding: 1vw; color: #fff; font-size: 3vw; font-weight: 700;'>
            Motto
        </div>
        <div style='font-size: 2vw; margin: 1vw 0;'>
            Order Summary
        </div>
        <div style='font-size: 1vw; margin: 1vw 0;'>
            Hi <span style='font-size: 1.3vw; font-weight: 500; color: #0077ed;'>$_user->username</span>, <br>
            Thank you for shopping with us! Here’s a summary of your order:
        </div>
        <div style='font-size: 1vw; margin: 1vw 0;'>
            <strong>Order ID:</strong> $newId <br>
            <strong>Total Amount:</strong> RM$total_amount <br>
            <strong>Shipping Address:</strong> $final_address
        </div>
        <div style='font-size: 1.5vw; margin: 1vw 0; font-weight: bold;'>Items Purchased:</div>
        <table style='width: 100%; border-collapse: collapse;'>
            <thead>
                <tr style='background-color: #0077ed; color: white;'>
                    <th style='padding: 10px; border: 1px solid #ddd;'>Product Name</th>
                    <th style='padding: 10px; border: 1px solid #ddd;'>Price</th>
                    <th style='padding: 10px; border: 1px solid #ddd;'>Quantity</th>
                    <th style='padding: 10px; border: 1px solid #ddd;'>Subtotal</th>
                </tr>
            </thead>
            <tbody>";

// Loop through the items to add them to the email body
foreach ($temp as $item) {
    // Calculate the subtotal for each item
    $subtotal = $item->price * $item->quantity;

    // Append the row to the email body
    $body .= "
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$item->name}</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>RM" . number_format($item->price, 2) . "</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>{$item->quantity}</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>RM" . number_format($subtotal, 2) . "</td>
                </tr>";
}


$body .= "
            </tbody>
        </table>
        <div style='margin: 2vw 0;'>
            <p>We hope to see you again soon!</p>
        </div>
    </div>";

// Send the email
send_email($customer_email, $subject, $body, true);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/id.css">
    <script src="/js/app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    <main>
        <div class="w-full flex justify-center items-start">
            <div class="login-card flex justify-start items-center" style="margin-top: 12vw;">
                <img src="/img/icon&logo/correct_.png" alt="" class="correct-icon">
                <?php echo "Payment successful! Thank you for your purchase."; ?>
                <form action="/" method="get">
                    <button type="submit" class="btn full-rounded">
                        <span>Back To Home</span>
                        <div class="border full-rounded" style="width: 27.2em;"></div>
                    </button>
                </form>
            </div>

        </div>
    </main>
</body>