<?php
require '../../include/_base.php';
ob_start();
auth('admin', 'superAdmin');
$title = 'Change Password';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';

if (is_post()) {
    $current_password = req('current_password');
    $new_password = req('new_password');
    $confirm_password = req('confirm_password');

    if ($current_password == '') {
        $_err['current_password'] = 'Current password is required';
    }

    if ($new_password == '') {
        $_err['new_password'] = 'New password is required';
    } else if (strlen($new_password) < 8 || strlen($new_password) > 20) {
        $_err['new_password'] = 'Password must be between 8 and 20 characters';
    }


    if ($confirm_password == '' || $confirm_password != $new_password) {
        $_err['confirm_password'] = 'Passwords do not match';
    }

    if (!$_err) {

        $current_password_hashed = sha1($current_password);

        if ($current_password_hashed === $_user->password_hash) {
            $new_password_hashed = sha1($new_password);
            $stm = $_db->prepare('
                UPDATE users 
                SET password_hash = ?, error_login = 0, is_active = 1 
                WHERE id = ?;
            ');
            $stm->execute([$new_password_hashed, $_user->id]);

            temp('info', 'Password changed successfully');
            redirect('/pages/admin/changePassword.php');
        } else {
            $_err['current_password'] = 'Incorrect current password';
        }
    }

}
?>

<div class="w-full profile-section">
    <div class="profile-header">
        <h2>Change Password</h2>
    </div>

    <form method="post" class="password-form">
        <div class="profile-form-container1 flex">

            <!-- Password Change Section -->
            <div class="profile-details-section1">
                <div class="input-group1">
                    <label for="current_password">Current Password</label>
                    <div class="password-container">
                        <?= html_password('current_password', 'class="input-field" required') ?>
                        <span class="toggle-password" onclick="togglePasswordVisibility('current_password', this)">
                            <img src="/img/icon&logo/eye-slash.svg" alt="Unshow Password" class="eye-icon">
                        </span>
                    </div>
                    <?= err('current_password') ?>
                </div>

                <div class="input-group1">
                    <label for="new_password">New Password</label>
                    <div class="password-container">
                        <?= html_password('new_password', 'class="input-field" required') ?>
                        <span class="toggle-password" onclick="togglePasswordVisibility('new_password', this)">
                            <img src="/img/icon&logo/eye-slash.svg" alt="Unshow Password" class="eye-icon">
                        </span>
                    </div>
                    <?= err('new_password') ?>
                </div>

                <div class="input-group1">
                    <label for="confirm_password">Re-enter New Password</label>
                    <div class="password-container">
                        <?= html_password('confirm_password', 'class="input-field" required') ?>
                        <span class="toggle-password" onclick="togglePasswordVisibility('confirm_password', this)">
                            <img src="/img/icon&logo/eye-slash.svg" alt="Unshow Password" class="eye-icon">
                        </span>
                    </div>
                    <?= err('confirm_password') ?>
                </div>

                <div class="action-buttons1">
                    <input type="submit" value="Update Password" class="save-button1">
                    <button type="reset" class="discard-button">Discard</button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
include '../../include/_adminFooter.php';
ob_end_flush();
