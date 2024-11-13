<?php
// ============================================================================
// PHP Setups
// ============================================================================

date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

$_db = new PDO('mysql:dbname=motto', 'root', '', [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,]);


// ============================================================================
// General Page Functions
// ============================================================================

// Global error array
$_err = [];

// Is GET request?
function is_get()
{
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// Obtain GET parameter
function get($key, $value = null)
{
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null)
{
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain REQUEST (GET and POST) parameter
function req($key, $value = null)
{
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Redirect to URL
function redirect($url = null)
{
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

// Set or get temporary session variable
function temp($key, $value = null)
{
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

// ============================================================================
// HTML Helpers
// ============================================================================

// Encode HTML special characters
function encode($value)
{
    return htmlentities($value);
}

// Generate <input type='text'>
function html_text($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' class='$key' value='$value' $attr>";
}

function html_text2($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' class='$key' $attr>";
}

function html_text1($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '-');
    echo "<input type='text' id='$key' name='$key' class='$key' value='$value' $attr>";
}
function html_text3($key)
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' class='$key' value='$value'>";
}
// Generate <textarea> with fixed size
function html_textArea($key, $attr = '', $width = '300px', $height = '150px')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<textarea id='$key' name='$key' class='$key' style='resize: none; width: $width; height: $height;' $attr>$value</textarea>";
}


// Generate <input type='search'>
function html_search($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='search' id='$key' class='$key' name='$key' value='$value' $attr>";
}

// Generate SINGLE <input type='checkbox'>
function html_checkbox($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    $status = $value == 1 ? 'checked' : '';
    echo "<input type='checkbox' id='$key' class='$key' name='$key' value='1' $status $attr>";
}

// Generate <input type='number'>
function html_number($key, $min = '', $max = '', $step = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' class='$key' name='$key' value='$value' min='$min' max='$max' step='$step' $attr>";
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false)
{
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <input type='file'>
function html_file($key, $accept = '', $value = '', $attr = '')
{
    echo "<input type='file' id='$key' name='$key' value='$value' accept='$accept' $attr>";
}

// Obtain uploaded file --> cast to object
function get_file($key)
{
    $f = $_FILES[$key] ?? null;

    if ($f && $f['error'] == 0) {
        return (object)$f;
    }

    return null;
}

// Crop, resize and save photo
function save_photo($f, $folder, $width = 200, $height = 200)
{
    $photo = uniqid() . '.jpg';

    require_once 'lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height)
        ->toFile("$folder/$photo", 'image/jpeg');

    return $photo;
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

function html_select1($key, $items, $default = '- Select One -', $attr = '', $selected = null)
{
    // Use passed selected value or fallback to global value
    $value = $selected ?? ($GLOBALS[$key] ?? '');

    echo "<select id='$key' class='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        // Check if the current option should be selected
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}


// Generate table headers <th>
function table_headers($fields, $sort, $dir, $href = '')
{
    foreach ($fields as $k => $v) {
        $d = 'asc'; // Default direction
        $c = '';    // Default class

        // TODO
        if ($k == $sort) {
            $d = $dir == 'asc' ? 'desc' : 'asc';
            $c = $dir;
        }

        echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$v</a></th>";
    }
}

// Is email?
function is_email($value)
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// Is Phone Number?
function is_phoneNo($value)
{
    return preg_match('/^\+?[0-9\s\-()]{7,14}$/', $value);
}


// Generate <input type='password'>
function html_password($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' class='$key' name='$key' value='$value' $attr>";
}

function html_password1($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' class='$key' name='$key'  $attr>";
}

// Generate <span class='err'>
function err($key)
{
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err1'>$_err[$key]</span>";
    } else {
        echo '<span></span>';
    }
}

function err1($arraykey) {
    // Check if $arraykey is an array
    if (is_array($arraykey)) {
        // Loop through the provided $arraykey
        foreach ($arraykey as $error) {
            // Check if the error message exists at index $i for the specific key
            echo "<span class='err'>$error</span><br>";
        }
    } else if (is_string($arraykey)) {
        // Handle the case where $arraykey is a string, just output it directly
        echo "<span class='err'>$arraykey</span><br>";
    } else {
        // If it's neither array nor string, handle accordingly or do nothing
        echo '<span></span>';
    }
}



// ============================================================================
// Security
// ============================================================================

// Global user object
$_user = $_SESSION['user'] ?? null;

// Login user
function login($user, $url = '/')
{
    $_SESSION['user'] = $user;
    redirect($url);
}

// Logout user
function logout($url = '/')
{
    unset($_SESSION['user']);
    redirect($url);
}

// Authorization
function auth(...$roles)
{
    global $_user;
    if ($_user) {
        if ($roles) {
            if (in_array($_user->role, $roles)) {
                return; // OK
            }
        } else {
            return; // OK
        }
    }

    redirect('/pages/customer/id/login.php');
}

function duplicated_data($key, $column_name, $table_name)
{
    global $_db;

    $query = "SELECT COUNT(*) AS count FROM $table_name WHERE $column_name = ?";

    $stm = $_db->prepare($query);
    $stm->execute([$key]);

    $result = $stm->fetch(PDO::FETCH_ASSOC);

    return $result['count'] > 0;
}

//Send Email
function send_admin_email($email, $subject, $body, $html, $attachment = '')
{
    $m = get_user_mail($email);
    $m->addAddress('beenetwork25@gmail.com');
    $m->Subject = $subject;
    $m->Body = $body;
    $m->isHTML($html);
    // Add attachment if provided
    if (!empty($attachment)) {
        $m->addAttachment($attachment);
    }

    // Attempt to send the email
    if ($m->send()) {
        temp('info', 'Email Sent');
    } else {
        temp('error', 'Failed to send email');
    }
}

// Initialize and return mail object
function get_user_mail($email)
{
    require_once 'PHPMailer.php';
    require_once 'SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->Username = 'beenetwork25@gmail.com';
    $m->Password = 'cvxj pzpa qpqi ezgu';
    $m->CharSet = 'utf-8';

    $m->setFrom($m->Username, '😺 User');

    if (!empty($email)) {
        $m->addReplyTo($email);
    }

    return $m;
}


//Send Email
function send_email($email, $subject, $body, $html, $attachment = '')
{
    $m = get_mail();
    $m->addAddress($email);
    $m->Subject = $subject;
    $m->Body = $body;
    $m->isHTML($html);

    // Add attachment if provided
    if (!empty($attachment)) {
        $m->addAttachment($attachment);
    }

    // Attempt to send the email
    if ($m->send()) {
        temp('info', 'Email Sent');
    } else {
        temp('error', 'Failed to send email');
    }
}

// Initialize and return mail object
function get_mail()
{
    require_once 'PHPMailer.php';
    require_once 'SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->Username = 'beenetwork25@gmail.com';
    $m->Password = 'cvxj pzpa qpqi ezgu';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, '😺 Admin');

    return $m;
}



// Return base url (host + port)
function base($path = '')
{
    return "http://$_SERVER[SERVER_NAME]:$_SERVER[SERVER_PORT]/$path";
}

// Is exists?
function is_exists($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

// Is unique?
function is_unique($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Define the months array for the dropdown
$months = [
    '1' => 'January',
    '2' => 'February',
    '3' => 'March',
    '4' => 'April',
    '5' => 'May',
    '6' => 'June',
    '7' => 'July',
    '8' => 'August',
    '9' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December'
];

// Define the years array (e.g., from 1900 to the current year)
$currentYear = date("Y");
$years = [];
for ($y = $currentYear; $y >= 1900; $y--) {
    $years[$y] = $y;
}

// Define the days array (1 to 31, dynamic adjustment will be done via JS)
$days = [];
for ($d = 1; $d <= 31; $d++) {
    $days[$d] = $d;
}

// ============================================================================
// Shopping Cart
// ============================================================================

// Get shopping cart
function get_cart() {
    // TODO
    return $_SESSION['cart'] ?? [];
}

// Set shopping cart
function set_cart($cart = []) {
    // TODO
    $_SESSION['cart'] = $cart;
}

// Update shopping cart
function update_cart($id, $unit) {
    // TODO
    $cart = get_cart();

    if($unit >= 1 && $unit <= 10 && is_exists($id,'product','id')){
        $cart[$id] = $unit;
        ksort($cart);
    }else{

        unset($cart[$id]);
    }

    set_cart($cart);
}