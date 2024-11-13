<?php
include '../../../include/_base.php';

$_title = 'Sign Up';

if (is_post()) {
    $email = req('email');
    $username = req('name');
    $phoneNo = req('phoneNo');
    $password = req('password');
    $selected_day = req('day');
    $selected_month = req('month');
    $selected_year = req('year');
    $id;

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid Email';
    } else if (duplicated_data($email, 'email', 'users')) {
        $_err['email'] = 'Duplicated Email';
    }

    // Validate: username
    if ($username == '') {
        $_err['username'] = 'Required';
    } else if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $_err['username'] = 'Username can only contain letters and numbers';
    } else if (strlen($username) > 35) {
        $_err['username'] = 'Username must be 35 characters or less';
    } else {
        $username = strtoupper($username);
    }

    // Validate: phoneNo
    if ($phoneNo == '') {
        $_err['phoneNo'] = 'Required';
    } else if (!is_phoneNo($phoneNo)) {
        $_err['phoneNo'] = 'Invalid Phone Number. PhIt must be within 10 to 11 digit';
    } else if (strlen($phoneNo) < 10) {
        $_err['password'] = 'Phone number must be within 10 to 11 digit';
    } else if (strlen($phoneNo) > 11) {
        $_err['password'] = 'Phone number must be within 10 to 11 digit';
    } else if (duplicated_data($phoneNo, 'phone_number', 'users')) {
        $_err['phoneNo'] = 'Duplicated Phone Number';
    }

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    } else if (strlen($password) < 8) {
        $_err['password'] = 'Password must be at least 8 characters long';
    } else if (strlen($password) > 20) {
        $_err['password'] = 'Password must be 20 characters or less';
    }


    if (empty($selected_day) || empty($selected_month) || empty($selected_year)) {
        $_err['dob'] = 'Date of Birth is required';
    } else {
        $dob = "$selected_year-$selected_month-$selected_day";
        if (!checkdate($selected_month, $selected_day, $selected_year)) {
            $_err['dob'] = 'Invalid Date of Birth';
        } else {
            $dobDate = new DateTime($dob);
            $today = new DateTime();
            if ($dobDate > $today) {
                $_err['dob'] = 'Date of Birth cannot be in the future';
            }
        }
    }


    $date = sprintf('%04d/%02d/%02d', $selected_year, $selected_month, $selected_day);

    // Login user
    if (!$_err) {
        $stm = $_db->prepare('SELECT COUNT(*) AS user_count FROM users');
        $stm->execute();
        $result = $stm->fetch();
        $userCount = $result->user_count;

        $id = 'U' . ($userCount + 1);
        $stm = $_db->prepare('
            INSERT INTO users (id, username, email, phone_number, password_hash, dob) VALUES (?,?,?,?,SHA1(?),?)
        ');
        $stm->execute([$id, $username, $email, $phoneNo, $password, $date]);

        $verifyUrl = "http://localhost:8000/pages/customer/id/verifyAccount.php?email=$email";
        $subject = 'Account Verification';
        $body = "
            <div style='width: 100%;'>
                <div style='display: flex; text-align: center; justify-content: center; background-color: #0077ed; padding: 1vw; color: #fff; font-size: 3vw; font-weight: 700;'>
                    Motto
                </div>
                <div style='font-size: 2vw; margin: 1vw 0;'>
                    Email Verification
                </div>
                <div style='font-size: 1vw; margin: 1vw 0;'>
                    Hi <span style='font-size: 1.3vw; font-weight: 500; color: #0077ed;'>$username</span>, <br>
                    You're almost set to start enjoying. Simply click the link below to verify your email address and get started.
                </div>
                <div style='display: flex; justify-content: center; margin: 2vw auto;'>   
                        <a style='font-size: 1vw; padding: 0.8vw 1vw; background-color: #0077ed; color: #fff; border: none; border-radius: 5px; text-decoration: none; font-weight: 500;' href='$verifyUrl'>
                        
                            Verify my email address
                        </a>
                </div>
            </div>";
        send_email($email, $subject, $body, true);
        redirect('/pages/customer/id/login.php');
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

<body class="body">
    <main>
        <div class="w-full h-full flex justify-center items-start">
            <div class="signup-card flex justify-start items-center">
                <div class="motto">
                    Motto
                </div>
                <div class="login-context">
                    Sign up
                </div>
                <form method="post">
                    <div class="input-group">
                        <?= html_text('name', 'oninput="this.value = this.value.toUpperCase()" required') ?>
                        <label for="name" class="user-label" style="background-color: #ededed;">Username</label>
                    </div>
                    <div class="alert justify-center flex">
                        <?= err('username') ?>
                    </div>

                    <div class="input-group">
                        <?= html_text('email', 'required') ?>
                        <label for="email" class="user-label" style="background-color: #ededed;">Email Address</label>
                    </div>
                    <div class="alert justify-center flex">
                        <?= err('email') ?>
                    </div>

                    <div class="input-group">
                        <?= html_text('phoneNo', 'required') ?>
                        <label for="phoneNo" class="user-label" style="background-color: #ededed;">Phone Number</label>
                    </div>
                    <div class="alert justify-center flex">
                        <?= err('phoneNo') ?>
                    </div>

                    <div class="input-group">
                        <?= html_password('password', 'required')  ?>
                        <label for="password" class="user-label" style="background-color: #ededed;">Password</label>
                    </div>
                    <div class="show-password flex items-center">
                        <?= html_checkbox('checkbox') ?>
                        <label for="checkbox">Show Passowrd</label>
                    </div>
                    <div class="alert justify-center flex">
                        <?= err('password') ?>
                    </div>

                    <div class="flex justify-between">
                        <div class="input-group">
                            <?= html_number('day', '1', '31', '1', 'required') ?>
                            <label for="day" class="user-label" style="background-color: #ededed;">Day</label>
                        </div>
                        <div class="input-group">
                            <?= html_number('month', '1', '12', '1', 'required') ?>
                            <label for="month" class="user-label" style="background-color: #ededed;">Month</label>
                        </div>
                        <div class="input-group">
                            <?= html_number('year', '1924', '2021', '1', 'required') ?>
                            <label for="year" class="user-label" style="background-color: #ededed;">Year</label>
                        </div>
                        <?= err('dob') ?>
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