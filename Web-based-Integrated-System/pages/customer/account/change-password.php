<?php
require '../../../include/_base.php';

require '../../../include/_head.php';

auth('customer');

if (is_post()) {
    $newPassword = req('newPassword');
    $confirmPassword = req('confirmPassword');
    $currentPassword = req('currentPassword');

    // Validate: password
    if ($currentPassword == '') {
        $_err['currentPassword'] = 'Required';
    } else if (strlen($currentPassword) < 8) {
        $_err['currentPassword'] = 'Password must be at least 8 characters long';
    } else if (strlen($currentPassword) > 20) {
        $_err['currentPassword'] = 'Password must be 20 characters or less';
    }


    // Validate: password
    if ($newPassword == '') {
        $_err['newPassword'] = 'Required';
    } else if (strlen($newPassword) < 8) {
        $_err['newPassword'] = 'Password must be at least 8 characters long';
    } else if (strlen($newPassword) > 20) {
        $_err['newPassword'] = 'Password must be 20 characters or less';
    }

    // Validate: password
    if ($confirmPassword == '') {
        $_err['confirmPassword'] = 'Required';
    } else if (strlen($confirmPassword) < 8) {
        $_err['confirmPassword'] = 'Password must be at least 8 characters long';
    } else if (strlen($confirmPassword) > 20) {
        $_err['confirmPassword'] = 'Password must be 20 characters or less';
    }

    if($confirmPassword != $newPassword){
        $_err['confirmPassword'] = 'Not same with new password';
    }

    // Login user
    if (!$_err) {
        $stm = $_db->prepare('SELECT * FROM users WHERE password_hash = SHA1(?)');
        $stm->execute([$currentPassword]);
        $t = $stm->fetch();

        if ($t != null) {
            $stm = $_db->prepare('UPDATE users SET password_hash = SHA1(?) WHERE id = ?');
            $stm->execute([$confirmPassword, $_user->id]);
        }else{
            $_err['currentPassword'] = 'Invalid Password';
        }
    }
}

?>

<div class="w-full">
    <div class="profile flex justify-between">
        <div class="sidebar flex ">
            <div class="account">
                <a href="profile.php">ACCOUNT</a>
            </div>
            <hr>
            <div class="change-password">
                <a href="change-password.php" style="color: #0077ed;">CHANGE PASSWORD</a>
            </div>
            <hr>
            <div class="transaction">
                <a href="transaction.php">MY ORDERS</a>
            </div>
        </div>

        <div class="change-password-container">
            <div class="change-password-title">
                Change Password
            </div>
            <p>For your account's security, do not share your password with anyone else</p>

            <form method="post">
                <div class="change-password-input">
                    <div class="flex">
                        <div class="input-group">
                            <?= html_password('currentPassword', 'required')  ?>
                            <label for="currentPassword" class="user-label">Current Password</label>
                        </div>
                        <label class="eye-container" style="transform: translateX(-250%);">
                            <?= html_checkbox('currentPassCheckbox') ?>
                            <img src="../../../img/icon&logo/eye.svg" alt="" class="eye">
                            <img src="../../../img/icon&logo/eye-slash.svg" alt="" class="eye-slash">
                        </label>
                    </div>
                    <div class="alert">
                            <?= err('currentPassword') ?>
                        </div>

                    <div class="flex">
                        <div class="input-group">
                            <?= html_password('newPassword', 'required')  ?>
                            <label for="newPassword" class="user-label">New Password</label>
                        </div>
                        <label class="eye-container" style="transform: translateX(-250%);">
                            <?= html_checkbox('newPassCheckbox') ?>
                            <img src="../../../img/icon&logo/eye.svg" alt="" class="eye">
                            <img src="../../../img/icon&logo/eye-slash.svg" alt="" class="eye-slash">
                        </label>
                    </div>
                    <div class="alert">
                            <?= err('newPassword') ?>
                        </div>

                    <div class="flex">
                        <div class="input-group">
                            <?= html_password('confirmPassword', 'required')  ?>
                            <label for="confirmPassword" class="user-label">Confirm Password</label>
                        </div>
                        <label class="eye-container" style="transform: translateX(-250%);">
                            <?= html_checkbox('confirmPassCheckbox') ?>
                            <img src="../../../img/icon&logo/eye.svg" alt="" class="eye">
                            <img src="../../../img/icon&logo/eye-slash.svg" alt="" class="eye-slash">
                        </label>
                    </div>
                    <div class="alert">
                            <?= err('confirmPassword') ?>
                        </div>



                    <div class="save-container flex">
                        <input type="submit" value="Save" class="save-button">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<?php
include '../../../include/_footer.php';

?>