<?php
include '../../include/_base.php';

if (is_post()) {
    $email = req('email');
    $username = req('name');
    $phoneNo = req('phoneNo');
    $content = req('content');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid Email';
    }

    // Validate: username
    if ($username == '') {
        $_err['username'] = 'Required';
    } else if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $_err['username'] = 'Username can only contain letters and numbers';
    } else {
        $username = strtoupper($username);
    }

    // Validate: phoneNo
    if ($phoneNo == '') {
        $_err['phoneNo'] = 'Required';
    } else if (!is_phoneNo($phoneNo)) {
        $_err['phoneNo'] = 'Invalid Phone Number';
    }

    if (!$_err) {
        $subject = 'User Inquiry';
        $body = $content;
        send_admin_email($email, $subject, $body, false);
    }
}

include '../..//include/_head.php'
?>

<script>
    let map;

    function initMap() {
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 16,
            center: {
                lat: 3.2152552,
                lng: 101.7265571
            },
        });

        // Add a marker at the center of the map with a custom icon
        const marker = new google.maps.Marker({
            position: {
                lat: 3.2152552,
                lng: 101.7265571
            },
            map: map,
            title: "Center of the Map",
            icon: {
                url: "http://localhost:8000/img/icon&logo/Map_pin_icon.svg.png", // Replace with your custom icon URL
                scaledSize: new google.maps.Size(30, 40), // Resize the icon
            }
        });
    }

    window.initMap = initMap;
</script>
<div class="w-full justify-center flex">
    <div class="contactUs-container flex justify-between ">
        <div class="contactUs-form">
            <div class="contactUs-context">
                Contact Us
            </div>
            <form method="post">
                <div class="input-group">
                    <?= html_text('name', 'required') ?>
                    <label for="name" class="user-label">Username</label>
                </div>
                <div class="input-group">
                    <?= html_text('email', 'required') ?>
                    <label for="email" class="user-label">Email Address</label>
                </div>
                <div class="input-group">
                    <?= html_text('phoneNo', 'required') ?>
                    <label for="phoneNo" class="user-label">Phone Number</label>
                </div>
                <div class="input-group">
                    <?= html_textArea('content', 'required', '350px', '150px') ?>
                    <label for="content" class="user-label">Content</label>
                </div>
                <button class="btn full-rounded">
                    <span>Submit</span>
                    <div class="border full-rounded"></div>
                </button>
            </form>
        </div>

        <div id="map" class="map"></div> <!-- Map container -->
    </div>
</div>

</main>

<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAAEFH_zXpMYNcQJFscENyIKxtLGLTcnQ8&callback=initMap"
    defer>
</script>

<?php
include '../../include/_footer.php';
?>