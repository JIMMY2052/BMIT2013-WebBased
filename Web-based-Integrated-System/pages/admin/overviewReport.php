<?php
require '../../include/_base.php';
$title = 'OverviewReport';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';
require_once '../../lib/SimplePager.php';
$fields = [
    'id'         => 'ID',
    'username'       => 'Customer',
    'dob'          => 'Age',
    'is_verified'     => 'Verified',
    'last_date_operate' => 'Joined Date',
];

$page = req('page', 1);

$username = req('username');
$sort = req('sort');
if ($username == null && $sort == null) {
    unset($_SESSION["username"]);
}

key_exists($sort, $fields) || $sort = 'id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';


if ($username != null) {
    $_SESSION["username"] = $username;
}


$p = new SimplePager("SELECT * FROM users WHERE role = 'customer' AND is_active = 1 ORDER BY $sort $dir", [], '10', $page);
$customers = $p->result;

$name = $_SESSION["username"] ?? null;
if ($name  != null) {

    if (strlen($name) == 1) {
        $likePattern = "$name%";
    } else {
        $likePattern = "%$name%";
    }

    $p = new SimplePager("SELECT * FROM users WHERE username LIKE ? AND role = 'customer' AND is_active = 1 ORDER BY $sort $dir", [$likePattern], '10', $page);
    $customers = $p->result;
}

?>



<?php
include '../../include/_adminFooter.php';
?>