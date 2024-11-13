<?php
include '../../../include/_base.php';

if (is_post()) {
    $email = req('email');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    } else if (!duplicated_data($email, 'email','users')) {
        $_err['email'] = 'Not exists';
    }

    // Send reset token (if valid)
    if (!$_err) {
        // TODO: (1) Select user
        $stm = $_db->prepare('SELECT * FROM users WHERE email = ?');
        $stm->execute([$email]);
        $u = $stm->fetch();

        // TODO: (2) Generate token id
        $id = sha1(uniqid() . rand());

        // TODO: (3) Delete old and insert new token
        $stm = $_db->prepare('
            DELETE FROM password_reset WHERE user_id = ?;
            INSERT INTO password_reset (id,user_id, token_expired,last_date_operate) VALUES (?, ?,ADDTIME(NOW(),"00:005"),NOW());
        ');
        $stm->execute([$u->id, $id, $u->id,]);

        // TODO: (4) Generate token url
        $url = base("/pages/customer/id/changePassword.php?id=$id");

        $subject = 'Forgot Password';
        $body = "
        <div style='width: 100%;'>
                <div style='display: flex; text-align: center; justify-content: center; background-color: #0077ed; padding: 1vw; color: #fff; font-size: 3vw; font-weight: 700;'>
                    Motto
                </div>
                <div style='font-size: 2vw; margin: 1vw 0;'>
                    Please Reset Your Password
                </div>
                <div style='font-size: 1vw; margin: 1vw 0;'>
                    Hi <span style='font-size: 1.3vw; font-weight: 500; color: #0077ed;'>$username</span>, <br>
                    We have sent you this email in response to your request to reset your password. <br><br> 
                    To reset your password, please follow the link below:
                </div>
                <div style='display: flex; justify-content: center; margin: 2vw auto;'>   
                        <a style='font-size: 1vw; padding: 0.8vw 1vw; background-color: #0077ed; color: #fff; border: none; border-radius: 5px; text-decoration: none; font-weight: 500;' href='$url'>
                        
                            Reset Password
                        </a>
                </div>
            </div>";
        send_email($email, $subject, $body, true);
        // TODO: (5) Send email

        temp('info', 'Email sent');
        redirect('/');
    }
}
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
        <div class="w-full h-full flex justify-center items-start">

            <div class="forgot-password-card flex justify-start items-center">
                <div class="motto">
                    Motto
                </div>
                <div class="forgot-password-title">
                    Forgot Your Password?
                </div>
                <div class="forgot-password-des flex justify-center">
                    Please enter your account email.
                </div>
                <form action="" method="post">
                    <div class="input-group">
                        <?= html_text('email', 'required') ?>
                        <label for="email" class="user-label" style="background-color: #ededed;">Email Address</label>
                    </div>
                    <button class="btn full-rounded">
                        <span>Submit</span>
                        <div class="border full-rounded"></div>
                    </button>
                </form>
                <div class="create-acc">
                    Already have an account?<a href="login.php" class="create-acc-link">Sign in.</a>
                </div>
            </div>
        </div>
    </main>
</body>

</html>

<?php

?>