<?php
require '../../../include/_base.php';
ob_start();
require '../../../include/_head.php';

auth('customer');
extract((array)$_user);

$allowedStatuses = [
    'Johor' => 'Johor',
    'Kedah' => 'Kedah',
    'Kelantan' => 'Kelantan',
    'Melaka' => 'Melaka',
    'Negeri Sembilan' => 'Negeri Sembilan',
    'Pahang' => 'Pahang',
    'Penang' => 'Penang',
    'Perak' => 'Perak',
    'Perlis' => 'Perlis',
    'Sabah' => 'Sabah',
    'Sarawak' => 'Sarawak',
    'Selangor' => 'Selangor',
    'Terengganu' => 'Terengganu',
    'Kuala Lumpur' => 'Kuala Lumpur',
    'Labuan' => 'Labuan',
    'Putrajaya' => 'Putrajaya'
];

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

$name = $_user->username;
$email = $_user->email;
$phoneNo = $_user->phone_number;

$temp_email = $_user->email;
$temp_username = $_user->username;
$temp_phoneNo = $_user->phone_number;

if ($_user->address != null) {
    $urlArray = explode(', ', $_user->address);
    $address1 = $urlArray[0];
    $address2 = $urlArray[1];
    $postalCode = $urlArray[2];
    $city = $urlArray[3];
    $status = $urlArray[4];
}

if (is_post()) {
    $username = req('name');
    $email = req('email');
    $phoneNo = req('phoneNo');

    $line1 = req('address1');
    $line2 = req('address2');
    $postalCode = req('postalCode');
    $city = req('city');
    $status = req('status');

    $selected_day = req('day');
    $selected_month = req('month');
    $selected_year = req('year');
    $f = get_file('photoFile');

    $photo = req('photo'); // Get the photo value from hidden input


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




    // Validate email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid Email Format';
    } else if (duplicated_data($email, 'email', 'users') && $email != $temp_email) {
        $_err['email'] = 'Duplicated Email';
    }

    // Validate username
    if ($username == '') {
        $_err['username'] = 'Required';
    } else if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $_err['username'] = 'Username can only contain letters and numbers';
    } else if (strlen($username) > 35) {
        $_err['username'] = 'Username must be 35 characters or less';
    } else {
        $username = strtoupper($username);
    }

    // Validate phone number
    if ($phoneNo == '') {
        $_err['phoneNo'] = 'Required';
    } else if (!is_phoneNo($phoneNo)) {
        $_err['phoneNo'] = 'Invalid Phone Number. PhIt must be within 10 to 11 digit';
    } else if (strlen($phoneNo) < 10) {
        $_err['password'] = 'Phone number must be within 10 to 11 digit';
    } else if (strlen($phoneNo) > 11) {
        $_err['password'] = 'Phone number must be within 10 to 11 digit';
    } else if (duplicated_data($phoneNo, 'phone_number', 'users')&& $phoneNo != $temp_phoneNo) {
        $_err['phoneNo'] = 'Duplicated Phone Number';
    }

    if ($f != null) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = 'Must be an image (JPEG, PNG)';
        } else if ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Maximum file size is 1MB';
        }
    }

    if (!$_err) {
        $fullAddress = $line1 . ", " . $line2 . ", " . $postalCode . ", " . $city . ", " . $status;
        $date = sprintf('%04d/%02d/%02d', $selected_year, $selected_month, $selected_day);

        if ($f != null) {
            // Process the image
            $photo = uniqid() . '.jpg';
            require_once '../../../lib/SimpleImage.php';
            $img = new SimpleImage();
            $img->fromFile($f->tmp_name)
                ->thumbnail(200, 200)
                ->toFile("../../../img/userPic/$photo");
            $profile_picture = $photo;
        } else {
            // Keep the old profile picture
            $profile_picture = $_user->profile_picture;
        }

        if ($photo) {
            // Use the uploaded photo
            $profile_picture = $photo;
        } else {
            // Keep the old profile picture
            $profile_picture = $_user->profile_picture;
        }

        // Update the user details in the database
        $stm = $_db->prepare('UPDATE users SET username = ?, email = ?, phone_number = ?, dob = ?, `address` = ?, profile_picture = ? WHERE id = ?');
        $stm->execute([$username, $email, $phoneNo, $date, $fullAddress, $profile_picture, $_user->id]);

        // Fetch the updated user data
        $stm = $_db->prepare('SELECT * FROM users WHERE id = ?');
        $stm->execute([$_user->id]);
        $u = $stm->fetch();

        login($u, '/pages/customer/account/profile.php');
    }
}
?>

<div class="w-full flex">
    <div class="profile flex justify-between">
        <div class="sidebar flex ">
            <div class="account">
                <a href="profile.php" style="color: #0077ed;">ACCOUNT</a>
            </div>
            <hr>
            <div class="change-password">
                <a href="change-password.php">CHANGE PASSWORD</a>
            </div>
            <hr>
            <div class="transaction">
                <a href="transaction.php">MY ORDERS</a>
            </div>
        </div>

        <!-- Profile form section -->
        <div class="profile flex justify-between" style="justify-content: center; align-items: center; flex: 1;">
            <div class="profile-detail">
                <div class="profile-detail-title">
                    Profile Settings
                </div>
                <p>Manage your account details.</p>
                <?php err('photo') ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="profile-detail-section flex">
                        <div class="profile-detail-input">
                            <div class="input-group">
                                <?= html_text('name', 'oninput="this.value = this.value.toUpperCase()" required') ?>
                                <label for="name" class="user-label">Username</label>
                            </div>
                            <div class="alert">
                                <?= err('username') ?>
                            </div>
                            <div class="input-group">
                                <?= html_text('email', 'required') ?>
                                <label for="email" class="user-label">Email Address</label>
                            </div>
                            <div class="alert">
                                <?= err('email') ?>
                            </div>

                            <div class="input-group">
                                <?= html_text1('phoneNo', 'required') ?>
                                <label for="phoneNo" class="user-label">Phone Number</label>
                            </div>
                            <div class="alert">
                                <?= err('phoneNo') ?>
                            </div>

                            <div class="input-group">
                                <?= html_text3('address1') ?>
                                <label for="address1" class="user-label">Address line 1</label>
                            </div>
                            <div class="alert">
                                <?= err('address1') ?>
                            </div>

                            <div class="input-group">
                                <?= html_text3('address2') ?>
                                <label for="address2" class="user-label">Address line 2</label>
                            </div>
                            <div class="alert">
                                <?= err('address2') ?>
                            </div>
                            <div class="flex justify-even">
                                <div class="input-group">
                                    <?= html_text3('postalCode') ?>
                                    <label for="postalCode" class="user-label">Postal Code</label>
                                </div>
                                <div class="alert">
                                    <?= err('postalCode') ?>
                                </div>

                                <div class="input-group">
                                    <?= html_text3('city') ?>
                                    <label for="city" class="user-label">City</label>
                                </div>
                                <div class="alert">
                                    <?= err('city') ?>
                                </div>

                            </div>

                            <div class="input-group">
                                <?= html_select1('status', $allowedStatuses) ?>
                                <label for="status" class="user-label">Status</label>
                            </div>
                            <div class="alert">
                                <?= err('status') ?>
                            </div>

                            <div class="flex justify-between">
                                <div class="input-group">
                                    <?= html_number('day', '1', '31', '1', 'required') ?>
                                    <label for="day" class="user-label">Day</label>
                                </div>
                                <div class="input-group">
                                    <?= html_number('month', '1', '12', '1', 'required') ?>
                                    <label for="month" class="user-label">Month</label>
                                </div>
                                <div class="input-group">
                                    <?= html_number('year', '1', 'required') ?>
                                    <label for="year" class="user-label">Year</label>
                                </div>
                            </div>

                            <!-- Hidden input to store photo -->
                            <input type="hidden" id="photoInput" name="photo" value="">

                            <!-- Save Button -->
                            <div class="save-container flex justify-center">
                                <input type="submit" value="Save" class="save-button">
                            </div>
                        </div>


                        <!-- Profile detail photo section -->
                        <div class="profile-detail-photo flex justify-center items-center" style="margin-top:100px">

                            <div id="drop-area" class="drop-area">
                                <!-- Profile picture preview -->
                                <img id="profilePreview" src="../../../img/userPic/<?= isset($_user->profile_picture) ? $_user->profile_picture : 'default-pic.jpg' ?>" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover;" />

                                <!-- Video element for webcam feed -->
                                <video id="video" autoplay></video>


                                <input type="file" id="fileInput" name="photoFile" accept="image/*" style="display:none;">

                            </div>

                            <div class="webcam-controls flex justify-between">
                                <a href="#" onClick="startCam()">
                                    <i class="fas fa-video"></i> <!-- Font Awesome icon for Start Cam -->
                                </a>
                                <a href="#" onClick="stopCam()">
                                    <i class="fas fa-video-slash"></i> <!-- Font Awesome icon for Stop Cam -->
                                </a>
                                <a href="#" onClick="takePhoto()">
                                    <i class="fas fa-camera"></i> <!-- Font Awesome icon for Take Photo -->
                                </a>

                            </div>


                            <p>Click ,Drag & Drop or take photo</p>
                            <div class="profile-photo-limit">
                                <p>File size: maximum 1 MB</p>
                                <p>File extension: .JPEG, .PNG</p>
                            </div>


                        </div>


                    </div>

            </div>
            </form>

        </div>
    </div>
</div>
<!-- Add the canvas element (make sure it's hidden) -->
<canvas id="canvas" style="display:none;"></canvas>

<script>
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('fileInput');
    const profilePreview = document.getElementById('profilePreview');
    const photoInput = document.getElementById('photoInput'); // Hidden input for storing the filename

    // Handle drag over
    dropArea.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropArea.classList.add('dragover');
    });

    // Handle drag leave
    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('dragover');
    });

    // Handle drop
    dropArea.addEventListener('drop', (event) => {
        event.preventDefault();
        dropArea.classList.remove('dragover');
        const files = event.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    // Allow clicking to upload
    dropArea.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (event) => {
        const files = event.target.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    // Handle file input
    function handleFile(file) {
        if (!file.type.startsWith('image/')) {
            alert('Please upload an image file.');
            return;
        }
        if (file.size > 1 * 1024 * 1024) {
            alert('Maximum file size is 1MB');
            return;
        }

        // Send the image to the server for saving and get the unique filename
        saveFileToServer(file);
    }

    // Function to send the file to the server for saving
    const saveFileToServer = (file) => {
        const formData = new FormData();
        formData.append("image", file);

        // Send an AJAX request to the PHP script
        fetch('../save_image.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the profile picture preview with the saved image URL from the server
                    profilePreview.src = `../../../img/userPic/${data.file}`;

                    // Update the hidden input with the saved image filename
                    photoInput.value = data.file; // Store the unique filename in hidden input
                } else {
                    alert('Error saving the file.');
                }
            })
            .catch((error) => {
                console.error('Error occurred while saving the file.', error);
            });
    };

    // Start Cam function
    const startCam = () => {
        const video = document.getElementById("video");
        const profilePreview = document.getElementById("profilePreview");

        if (navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices
                .getUserMedia({
                    video: true
                })
                .then((stream) => {
                    video.srcObject = stream;
                    video.style.display = "block"; // Show video
                    profilePreview.style.display = "none"; // Hide profile picture when video starts
                })
                .catch(function(error) {
                    console.log("Something went wrong!", error);
                });
        }
    };

    // Stop Cam function
    const stopCam = () => {
        const video = document.getElementById("video");
        const profilePreview = document.getElementById("profilePreview");

        if (video.srcObject) {
            const stream = video.srcObject;
            const tracks = stream.getTracks();

            // Stop all tracks (this will stop the webcam)
            tracks.forEach(track => track.stop());

            // Clear the video source and show the profile picture
            video.srcObject = null;
            video.style.display = "none"; // Hide the video
            profilePreview.style.display = "block"; // Show the profile picture
        }
    };

    // Take Photo function
    const takePhoto = () => {
        const video = document.getElementById("video");
        const canvas = document.getElementById("canvas");
        const profilePreview = document.getElementById("profilePreview");
        const photoInput = document.getElementById("photoInput");

        // Set canvas dimensions to match video stream
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // Draw the video frame on the canvas
        const context = canvas.getContext("2d");
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Convert canvas to base64 image
        const dataUrl = canvas.toDataURL("image/png");

        // Update the profile picture preview with the captured image
        profilePreview.src = dataUrl;
        profilePreview.style.display = "block"; // Show captured image
        video.style.display = "none"; // Hide the video

        // Stop the webcam after taking the photo
        stopCam();

        // Set the captured photo in the hidden input
        photoInput.value = dataUrl;

        // Send the image to the server for saving
        savePhotoToServer(dataUrl);
    };

    // Function to send the captured photo to the server
    const savePhotoToServer = (dataUrl) => {
        const blob = dataURItoBlob(dataUrl);
        const formData = new FormData();
        formData.append("image", blob, "webcam_photo.png");

        // Send an AJAX request to the PHP script
        fetch('../save_image.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the profile picture preview with the saved image URL from the server
                    document.getElementById('profilePreview').src = `../../../img/userPic/${data.file}`;

                    // Update the hidden input with the saved image filename
                    document.getElementById('photoInput').value = data.file; // Set the unique filename into hidden input
                }
            })
            .catch((error) => {
                console.error('Error occurred while saving the photo.', error);
            });
    };

    // Utility function to convert base64 to Blob
    function dataURItoBlob(dataURI) {
        const byteString = atob(dataURI.split(',')[1]);
        const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
        const ab = new ArrayBuffer(byteString.length);
        const ia = new Uint8Array(ab);
        for (let i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i);
        }
        return new Blob([ab], {
            type: mimeString
        });
    }
</script>


<?php
include '../../../include/_footer.php';
ob_end_flush();
?>