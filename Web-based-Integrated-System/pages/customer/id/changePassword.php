<?php
include '../../../include/_base.php';

$_title = 'Forgot Password';

$_db->query('DELETE FROM password_reset WHERE token_expired < NOW() ');


if (is_post()) {
    $password = req('password');

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    }
    else if (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Between 5-100 characters';
    }

    // DB operation
    if (!$_err) {
        $stm = $_db->prepare('
        UPDATE users SET password_hash = SHA1(?), error_login = 0 WHERE id = (SELECT user_id FROM password_reset WHERE id = ?);
        DELETE FROM password_reset WHERE id = ?;
        ');
        $stm->execute([$password,$id,$id]);

        temp('info', 'Record updated');
        redirect('login.php');
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
        <div class="w-full h-full flex justify-center items-center">
            <div class="signup-card flex justify-start items-center">
                <div class="motto">
                    Motto
                </div>
                <div class="login-context">
                    Change New Password
                </div>
                <form method="post">
                    <div class="input-group">
                        <?= html_password('password', 'required')  ?>
                        <label for="password" class="user-label">Password</label>
                    </div>

                    <div class="show-password flex items-center">
                        <?= html_checkbox('checkbox') ?>
                        <label for="checkbox">Show Passowrd</label>
                    </div>

                    <button class="btn full-rounded">
                        <span>Submit</span>
                        <div class="border full-rounded"></div>
                    </button>
                </form>

                <div class="create-acc">
                    Don't want to change? Back to <a href="/index.php" class="create-acc-link">Main Page</a>.
                </div>
            </div>
        </div>
    </main>
</body>

</html>