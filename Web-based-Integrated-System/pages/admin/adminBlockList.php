<?php
require '../../include/_base.php';
auth('superAdmin');
$title = 'Admin Blocked Listing';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';
require_once '../../lib/SimplePager.php';
$fields = [
    'id'         => 'ID',
    'username'       => 'Admin',
    'error_login'          => 'Blocked Type',
    'last_date_operate' => 'Registered Date',
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


$p = new SimplePager("SELECT * FROM users WHERE (role = 'blockedAdmin' OR error_login > 3) AND is_active = 1 ORDER BY $sort $dir", [], '10', $page);
$blockedAdmin = $p->result;

$name = $_SESSION["username"] ?? null;
if ($name  != null) {

    if (strlen($name) == 1) {
        $likePattern = "$name%";
    } else {
        $likePattern = "%$name%";
    }

    $p = new SimplePager("SELECT * FROM users WHERE username LIKE ?  AND (role = 'blockedAdmin' OR error_login > 3) AND is_active = 1 ORDER BY $sort $dir", [$likePattern], '10', $page);
    $adminomers = $p->result;
}

?>

<div class="container">
    <h2>Blocked admin Listing</h2>
    <div class="form-container">
        <form>
            <?= html_search('username', 'placeholder="Search adminomer Name"'); ?>
        </form>
        <p>
            <?= $p->count ?> of <?= $p->item_count ?> record(s) |
            Page <?= $p->page ?> of <?= $p->page_count ?>
        </p>
    </div>
    <table class="table">
        <thead>

            <tr>
                <?= table_headers($fields, $sort, $dir, "page=$page"); ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($blockedAdmin as $admin): ?>
                <tr>
                    <td><?= $admin->id; ?></td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <img src="/img/userPic/<?= isset($admin->profile_picture) ? $admin->profile_picture : 'defaultUser.png'; ?>"
                                alt="Profile Picture"
                                style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;">
                            <?= encode($admin->username); ?>
                        </div>
                    </td>
                    <td><?= ($admin->role == 'blockedAdmin') ? "Account is Blocked By Super Admin" : "Too many Login Attempt" ?></td>

                    <td><?= date('d M Y', strtotime($admin->last_date_operate)); ?></td>
                    <td>
                        <div class="action-buttons">

                            <button class="action-btn view-btn" data-get="viewAdmin.php?id=<?= $admin->id ?>" title="View">
                                <img src="/img/adminIcon/view.svg" alt="View" title="View">
                            </button>

                            <button class="action-btn block-btn" data-confirm="<?= "Are you sure you want to unblock $admin->username [$admin->id]" ?>" data-post="unblockAdmin.php?id=<?= $admin->id ?>" title="Unblock Admin">
                                <img src="/img/adminIcon/unblock.svg" alt="unblock" title="Unblock Admin">
                            </button>


                            <button class="action-btn delete-btn" data-confirm="<?= "Are you sure you want to delete $admin->username [$admin->id]"  ?>" data-post="deleteAdmin.php?id=<?= $admin->id ?>" title="Delete Admin">
                                <img src="/img/adminIcon/delete.svg" alt="Delete" title="Delete Admin">
                            </button>
                        </div>
                    </td>

                </tr>
            <?php endforeach; ?>

        </tbody>

    </table>
</div>
<?= $p->html("sort=$sort&dir=$dir") ?>

<?php
include '../../include/_adminFooter.php';
