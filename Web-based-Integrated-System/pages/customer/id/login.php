<?php
include '../../../include/_base.php';

$_title = 'Login';

if (is_post()) {
    $email    = req('email');
    $password = req('password');


    if (empty($email)) {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }


    if (empty($password)) {
        $_err['password'] = 'Required';
    }

    if (empty($_err)) {
        $stm = $_db->prepare('
            SELECT * FROM users
            WHERE email = ?
        ');
        $stm->execute([$email]);
        $user = $stm->fetch();

        if ($user) {
            if (sha1($password) === $user->password_hash && $user->is_verified == 1 && $user->is_active == 1 && $user->role == 'customer' && $user->error_login < 3) {
                temp('info', 'Login successfully');
                updateLoginError($email, true);
                login($user);
            } else if (sha1($password) === $user->password_hash && $user->is_verified == 1 && $user->is_active == 1 && $user->error_login <= 3 && ($user->role == 'admin' || $user->role == 'superAdmin')) {
                temp('info', 'Login successfully');
                updateLoginError($email, true);
                login($user, '/pages/admin/dashboard.php');
            } else {
                updateLoginError($email, false);
                if (($user->role == 'admin' || $user->role == 'blockedAdmin') && $user->is_active == 0) {
                    $_err['login'] = 'Your account has been deleted by super admin.';
                } else if ($user->role == 'blockedAdmin') {
                    $_err['login'] = 'Your account has been blocked by super admin.';
                } else if ($user->role == 'blocked') {
                    $_err['login'] = 'Your account has been blocked by admin.';
                }else if ($user->error_login > 3) {
                    $_err['login'] = 'Too many attempt.';
                } else if ($user->is_active == 0 && $user->role == 'Customer') {
                    $_err['login'] = 'Account Inactive';
                } else if ($user->is_verified == 0) {
                    $_err['login'] = 'Account need to be verify';
                } else $_err['login'] = 'The password or email entered wrongly';
            }
        } else {
            $_err['email'] = 'No user found with this email';
        }
    }
}

/**
 * Update the login error count and deactivate user if necessary.
 *
 * @param string $email
 * @param bool $login
 */
function updateLoginError($email, $login)
{
    global $_db;

    $stm = $_db->prepare('
        SELECT * FROM users
        WHERE email = ?
    ');
    $stm->execute([$email]);
    $user = $stm->fetch();

    if ($user) {
        $errorLogin = $user->error_login;
        $isActive = $user->is_active;
        $isVerify =  $user->is_verified;
        if ($login) {
            $stm = $_db->prepare('
                UPDATE users
                SET error_login = 0
                WHERE email = ?
            ');
            $stm->execute([$email]);
        } else {
            if ($isVerify != 0) {
                $errorLogin++;

                $stm = $_db->prepare('
                UPDATE users
                SET error_login = ?
                WHERE email = ?
            ');
                $stm->execute([$errorLogin, $email]);
            }
        }
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
            <div class="login-card flex justify-start items-center">
                <div class="motto">Motto</div>
                <div class="login-context">Login</div>
                <form method="post">
                    <div class="input-group">
                        <?= html_text('email', 'required') ?>
                        <label for="email" class="user-label" style="background-color: #ededed;">Email Address</label>
                    </div>
                    <div class="alert justify-center flex">
                        <?= err('email') ?>
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

                    <div class="block-alert flex justify-center">
                        <?= err('login') ?>
                    </div>

                    <button class="btn full-rounded">
                        <span>Submit</span>
                        <div class="border full-rounded"></div>
                    </button>
                </form>
                <?= err('password') ?>
                <div class="forgot-password">
                    <a href="forgotPassword.php">Forgot password?</a>
                </div>
                <div class="create-acc">
                    I don't have an account. <a href="signup.php" class="create-acc-link">Create an account.</a>
                </div>

            </div>
        </div>
    </main>
</body>

</html>