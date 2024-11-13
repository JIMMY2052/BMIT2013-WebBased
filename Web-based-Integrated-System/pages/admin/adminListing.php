<?php
require '../../include/_base.php';
auth('superAdmin');
$title = 'Admin Listing';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';
require_once '../../lib/SimplePager.php';
$fields = [
    'id'         => 'ID',
    'username'       => 'Admin',
    'email'     => 'Email',
    'dob'          => 'Age',
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


$p = new SimplePager("SELECT * FROM users WHERE role = 'admin' AND is_active = 1 AND error_login <= 3 ORDER BY $sort $dir", [], '10', $page);
$admins = $p->result;

$name = $_SESSION["username"] ?? null;
if ($name  != null) {

    if (strlen($name) == 1) {
        $likePattern = "$name%";
    } else {
        $likePattern = "%$name%";
    }

    $p = new SimplePager("SELECT * FROM users WHERE username LIKE ? AND role = 'admin' AND is_active = 1 AND error_login <= 3 ORDER BY $sort $dir", [$likePattern], '10', $page);
    $admins = $p->result;
}

?>

<div class="container">
    <h2>Admin Listing</h2>
    <div class="top-right-button">
        <a href="addAdmin.php" class="add-admin-button">Add Admin</a>
    </div>
    <div class="form-container">
        <form>
            <?= html_search('username', 'placeholder="Search Admin Name"'); ?>
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
            <?php foreach ($admins as $admin):
                $dob = new DateTime($admin->dob);
                $today = new DateTime();
                $age = $today->diff($dob)->y; ?>
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
                    <td><?= $admin->email ?></td>
                    <td><?= $age ?></td>
                    <td><?= date('d M Y', strtotime($admin->last_date_operate)); ?></td>
                    <td>
                        <div class="action-buttons">

                            <button class="action-btn view-btn" data-get="viewAdmin.php?id=<?= $admin->id ?>" title="View">
                                <img src="/img/adminIcon/view.svg" alt="View" title="View">
                            </button>

                            <button class="action-btn block-btn" data-confirm="<?= "Are you sure you want to block $admin->username [$admin->id]" ?>" data-post="blockAdmin.php?id=<?= $admin->id ?>" title="Block Admin">
                                <img src="/img/adminIcon/block.svg" alt="Block" title="Block Admin">
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
