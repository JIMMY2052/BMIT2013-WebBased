<?php
require '../../include/_base.php';
ob_start();
auth('admin', 'superAdmin');
$title = 'Admin Profile';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';

extract((array)$_user);

if ($dob != null) {
    $parts = explode('-', $dob);
    $year = $parts[0];
    $month = $parts[1];
    $day = $parts[2];
} else {
    $year = null;
    $month = null;
    $day = null;
}

$temp_email = $email;
$temp_username = $username;
$temp_phoneNo = $phone_number;

if (is_post()) {
    $username = strtoupper(req('username'));
    $email = req('email');
    $phone_number = req('phone_number');
    $address = req('address');
    $selected_day = req('day');
    $selected_month = req('month');
    $selected_year = req('year');
    $f = get_file('photo');
    $webcamPhoto = req('webcam_photo');

    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid Email';
    } else if (duplicated_data($email, 'email', 'users') && $email != $temp_email) {
        $_err['email'] = 'Duplicated Email';
    }

    if ($username == '') {
        $_err['username'] = 'Username is required';
    } else if (preg_match('/\s/', $username)) {
        $_err['username'] = 'Username cannot contain spaces';
    } else if (strlen($username) > 35) {
        $_err['username'] = 'Username cannot exceed 35 characters';
    } else if (duplicated_data($username, 'username', 'users') && $username != $temp_username) {
        $_err['username'] = 'Duplicated Username';
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

    if ($address == '') {
        $_err['address'] = 'Address is required';
    }

    if ($f != null) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = 'Must be an image';
        } else if ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Maximum 1MB only';
        }
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

    if (!$_err) {
        if ($webcamPhoto != '') {
            $image_parts = explode(";base64,", $webcamPhoto);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file_name = uniqid() . '.png';
            $file = "../../img/userPic/$file_name";
            file_put_contents($file, $image_base64);

            $stm = $_db->prepare('UPDATE users SET profile_picture = ?, username = ?, email = ?, phone_number = ?, address = ?, dob = ? WHERE id = ?');
            $stm->execute([$file_name, $username, $email, $phone_number, $address, $dob, $_user->id]);

            $stm = $_db->prepare('SELECT * FROM users WHERE id = ?');
            $stm->execute([$_user->id]);
            $u = $stm->fetch();
            temp('info', 'Successfully edited profile');
            login($u, '/pages/admin/adminProfile.php');
        }


        if ($f != null) {
            $photo = uniqid() . '.jpg';
            require_once '../../lib/SimpleImage.php';
            $img = new SimpleImage();
            $img->fromFile($f->tmp_name)
                ->thumbnail(200, 200)
                ->toFile("../../img/userPic/$photo");

            $stm = $_db->prepare('UPDATE users SET username = ?, email = ?, phone_number = ?, address = ?, profile_picture = ?, dob = ? WHERE id = ?');
            $stm->execute([$username, $email, $phone_number, $address, $photo, $dob, $_user->id]);
            $stm = $_db->prepare('SELECT * FROM users WHERE id = ?');
            $stm->execute([$_user->id]);
            $u = $stm->fetch();

            login($u, '/pages/admin/adminProfile.php');
        } else if (req('remove_picture')) {
            $currentProfilePicture = $_user->profile_picture;
            if ($currentProfilePicture && $currentProfilePicture !== 'defaultUser.png') {
                $filePath = "../../img/userPic/" . $currentProfilePicture;

                unlink($filePath);


                $stm = $_db->prepare('UPDATE users SET profile_picture = ?, username = ?, email = ?, phone_number = ?, address = ?, dob = ? WHERE id = ?');
                $stm->execute(['defaultUser.png', $username, $email, $phone_number, $address, $dob, $_user->id]);
            }

            $stm = $_db->prepare('SELECT * FROM users WHERE id = ?');
            $stm->execute([$_user->id]);
            $u = $stm->fetch();

            temp('info', 'Successfully edited profile');
            login($u, '/pages/admin/adminProfile.php');
        } else {
            $stm = $_db->prepare('UPDATE users SET username = ?, email = ?, phone_number = ?, address = ?, dob = ? WHERE id = ?');
            $stm->execute([$username, $email, $phone_number, $address, $dob, $_user->id]);
            $stm = $_db->prepare('SELECT * FROM users WHERE id = ?');
            $stm->execute([$_user->id]);
            $u = $stm->fetch();
            temp('info', 'Successfully edited profile');
            login($u, '/pages/admin/adminProfile.php');
        }
    } else {
        temp('info', 'Unsuccessful update profile');
    }
}
?>

<div id="info"><?= temp('info') ?></div>
<div class="w-full profile-section">
    <div class="profile-header">
        <h2>Profile Details</h2>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="profile-form-container flex">
            <div class="profile-avatar-section">
                <div class="avatar-preview">

                    <img src="/img/userPic/<?= isset($_user->profile_picture) ? $_user->profile_picture : 'defaultUser.png' ?>"
                        alt="<?= $_user->username ?>"
                        class="profile-avatar-img"
                        id="profile-pic">

                    <video id="video" autoplay></video>

                    <label for="file-upload" class="edit-icon upload">
                        <img src="/img/adminIcon/edit.svg" alt="Edit Icon" class="edit-icon-img">
                        <input type="file" name="photo" id="file-upload" accept="image/*" class="file-input">
                    </label>


                    <?php if (isset($_user->profile_picture) && $_user->profile_picture !== 'defaultUser.png'): ?>
                        <button type="submit" name="remove_picture" class="remove-icon delete-photo-btn" value="1">
                            <img src="/img/adminIcon/delete.svg" alt="Remove Icon" class="remove-icon-img">
                        </button>
                    <?php endif; ?>
                </div>
                <?= err('photo') ?>
            </div>

            <div class="webcam-section">
                <h4>Take a Photo</h4>
                <br>
                <button type="button" class="btn btn-primary" id="start-cam">Start Cam</button>
                <button type="button" class="btn btn-danger" id="stop-cam" style="display:none;">Stop Cam</button>
                <button type="button" class="btn btn-success" id="take-photo" style="display:none;">Take Photo</button>

                <canvas id="canvas" style="display:none;"></canvas>
                <input type="hidden" name="webcam_photo" id="webcam_photo">
            </div>


            <div class="profile-details-section">
                <div class="input-group">
                    <label for="username">Username</label>
                    <?= html_text('username', 'required data-upper'); ?>
                    <?= err('username') ?>
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <?= html_text('email', 'required'); ?>
                    <?= err('email') ?>
                </div>

                <div class="input-group">
                    <label for="phoneNo">Phone Number</label>
                    <?= html_text('phone_number', 'required'); ?>
                    <?= err('phoneNo') ?>
                </div>

                <div class="input-group">
                    <label for="address">Address</label>
                    <?= html_text('address', 'required'); ?>
                    <?= err('address') ?>
                </div>

                <div class="input-group dob-group">
                    <label>Date of Birth</label>
                    <?= html_select1('day', $days, '- Select Day -', 'class="dob-select" id="day"', $day); ?>
                    <?= html_select1('month', $months, '- Select Month -', 'class="dob-select" id="month"', $month); ?>
                    <?= html_select1('year', $years, '- Select Year -', 'class="dob-select" id="year"', $year); ?>
                    <?= err('dob') ?>
                </div>

                <div class="action-buttons2">
                    <input type="submit" value="Save Changes" class="save-button">
                    <button type="reset" class="discard-button">Discard</button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
include '../../include/_adminFooter.php';
ob_end_flush();
