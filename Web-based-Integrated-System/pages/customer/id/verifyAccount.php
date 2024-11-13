<?php
include '../../../include/_base.php';

$_title = 'Verify Account';

$email = req('email');

$stm = $_db->prepare('
    UPDATE users SET is_verified = 1
    WHERE email = ?
');
$stm->execute([$email]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification</title>
    <link rel="stylesheet" href="/css/successfulDialog.css">
</head>

<body>
    <div class="successfulDialog">
        <div class="success-icon">✔️</div>
        <h1>Your Account Is Verified</h1>
        <p>Thank you for verifying your account.</p>
        
        <a class="button" href="/login">Go to Login</a>
    </div>
</body>

</html>
