<?php
require '../../include/_base.php';
require '../../composer/vendor/autoload.php'; // Make sure Composer's autoload is included

\Stripe\Stripe::setApiKey('sk_test_51PzdMe05xyD6hqdgbiSjdMahFgpyZ5eYRe3KrNZI9qZvde2wjtbaUuJiC2RP2pAgpbyqPNFtjobInnploqeweZ8o00ZYXB1WXR'); // Your Stripe secret key

// Retrieve cart items and total from session or database
$cartItems = json_decode(req('cartItems'), true); // Decode JSON to array
$user_id = req('user_id');
$total = req('total');

// Check if $cartItems is a valid array
if (!is_array($cartItems)) {
    die('Error: Cart items should be an array.');
}

$finalItems = [];

// Fetch product details for each cart item
foreach ($cartItems as $cartItem) {

    // Here we assume $cartItem is an associative array, so we use $cartItem['product_id']
    $stm = $_db->prepare('SELECT p.id, p.name, p.description, p.type, c.quantity, p.price
                          FROM cart c
                          JOIN product p ON c.product_id = p.id
                          WHERE c.user_id = ? AND c.product_id = ?');
    $stm->execute([$user_id, $cartItem['product_id']]);
    $item = $stm->fetch();

    if ($item) {
        // Add this item to the final array for Stripe checkout
        $finalItems[] = [
            'price_data' => [
                'currency' => 'myr',
                'product_data' => [
                    'name' => $item->name,
                    'description' => $item->description,
                ],
                'unit_amount' => intval($item->price * 100), // Convert price to cents by multiplying by 100
            ],
            'quantity' => $cartItem['quantity'], // Quantity comes from the cartItem, not the product query
        ];
    }
}

// Fetch user details
$stm = $_db->prepare('SELECT * FROM users WHERE id = ?');
$stm->execute([$user_id]);
$userDetail = $stm->fetch();

// Ensure we have items to process
if (empty($finalItems)) {
    die('Error: No valid items found for the checkout session.');
}

// Create a Stripe Checkout session
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => $finalItems,
    'mode' => 'payment',
    'success_url' => "http://localhost:8000/pages/customer/success.php?session_id={CHECKOUT_SESSION_ID}&user_id=$user_id", // Redirect after successful payment
    'cancel_url' => 'http://localhost:8000/pages/customer/cancel.php', // Redirect after cancelled payment
    'customer_email' => $userDetail->email, // Pass customer email here
    'shipping_address_collection' => [ // Collect shipping address at checkout
        'allowed_countries' => ['MY'], // Limit to your specific countries
    ],
]);

// Redirect to the Stripe Checkout page
header('Location: ' . $session->url, true, 303);
exit;
