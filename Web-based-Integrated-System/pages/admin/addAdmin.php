<?php
require '../../include/_base.php';
ob_start();
auth('superAdmin');
$title = 'Add Admin';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';

if (is_post()) {
    $username = req('username');
    $email = req('email');
    $phone_number = req('phone_number');
    $password = req('password');
    $confirmPassword = req('confirm_password');

    if ($password == null) {
        $_err['password'] = 'Password is Required';
    } else if (strlen($password) < 8) {
        $_err['password'] = 'Password must be at least 8 characters long';
    } else if (strlen($password) > 20) {
        $_err['password'] = 'Password must be 20 characters or less';
    }

    if ($confirmPassword == '' || $confirmPassword != $password) {
        $_err['confirmPassword'] = 'Passwords do not match';
    }

    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid Email';
    } else if (duplicated_data($email, 'email', 'users')) {
        $_err['email'] = 'Duplicated Email';
    }

    if ($username == '') {
        $_err['username'] = 'Required';
    } else if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $_err['username'] = 'Username can only contain letters and numbers';
    } else if (strlen($username) > 35) {
        $_err['username'] = 'Username must be 35 characters or less';
    } else if (duplicated_data($username, 'username', 'users')) {
        $_err['username'] = 'Duplicated Username';
    } else {
        $username = strtoupper($username);
    }


    if ($phone_number == '') {
        $_err['phoneNo'] = 'Required';
    } else if (!is_phoneNo($phone_number)) {
        $_err['phoneNo'] = 'Invalid Phone Number. PhIt must be within 10 to 11 digit';
    } else if (strlen($phone_number) < 10) {
        $_err['password'] = 'Phone number must be within 10 to 11 digit';
    } else if (strlen($phone_number) > 11) {
        $_err['password'] = 'Phone number must be within 10 to 11 digit';
    } else if (duplicated_data($phone_number, 'phone_number', 'users')) {
        $_err['phoneNo'] = 'Duplicated Phone Number';
    }

    if (!$_err) {
        $stm = $_db->prepare('SELECT COUNT(*) AS user_count FROM users');
        $stm->execute();
        $result = $stm->fetch();
        $userCount = $result->user_count;
        $id = 'U' . ($userCount + 1);

        $stm = $_db->prepare('INSERT INTO users (id, username, email, phone_number, is_verified, `role`, password_hash) VALUES (?,?,?,?,?,?,SHA1(?))');
        $stm->execute([$id, strtoupper($username), $email, $phone_number, '1', 'admin', $password]);

        temp('info', "Added New Admin ID[$id]");
        redirect();
    }
}

?>

<div class="w-full profile-section">
    <div class="profile-header">
        <h2>Add Admin</h2>
    </div>

    <form method="post">
        <div class="profile-form-container flex">

            <!-- Admin Details Section -->
            <div class="profile-details-section">
                <div class="input-group">
                    <label for="admin_name">Username</label>
                    <?= html_text2('username', 'class="input-field" required style="width:500px; text-transform: uppercase;"'); ?>
                    <?= err('username') ?>
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <?= html_text2('email', 'class="input-field" required style="width:500px;"'); ?>
                    <?= err('email') ?>
                </div>

                <div class="input-group">
                    <label for="phone_number">Phone Number</label>
                    <?= html_text2('phone_number', 'class="input-field" required style="width:500px;"'); ?>
                    <?= err('phone_number') ?>
                </div>

                <div class="input-group1">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <?= html_password1('password', 'class="input-field" required style="width:720px;"') ?>
                        <span class="toggle-password" onclick="togglePasswordVisibility('password', this)">
                            <img src="/img/icon&logo/eye-slash.svg" alt="Unshow Password" class="eye-icon">
                        </span>
                    </div>
                    <?= err('password') ?>
                </div>

                <div class="input-group1">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-container">
                        <?= html_password1('confirm_password', 'class="input-field" required style="width:720px;"') ?>
                        <span class="toggle-password" onclick="togglePasswordVisibility('confirm_password', this)">
                            <img src="/img/icon&logo/eye-slash.svg" alt="Unshow Password" class="eye-icon">
                        </span>
                    </div>
                    <?= err('confirmPassword') ?>
                </div>

                <div class="action-buttons">
                    <input type="submit" value="Add Admin" class="save-button">
                    <button type="reset" class="discard-button">Discard</button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
include '../../include/_adminFooter.php';
ob_end_flush();
