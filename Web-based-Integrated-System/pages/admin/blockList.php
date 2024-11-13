<?php
require '../../include/_base.php';
auth('admin', 'superAdmin');
$title = 'Customer Blocked Listing';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';
require_once '../../lib/SimplePager.php';
$fields = [
    'id'         => 'ID',
    'username'       => 'Customer',
    'error_login'          => 'Blocked Type',
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


$p = new SimplePager("SELECT * FROM users WHERE (role = 'blocked' OR error_login > 3) ORDER BY $sort $dir", [], '10', $page);
$blockedCustomers = $p->result;

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

<div class="container">
    <h2>Blocked Customer Listing</h2>
    <div class="form-container">
        <form>
            <?= html_search('username', 'placeholder="Search Customer Name"'); ?>
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
            <?php foreach ($blockedCustomers as $cust): ?>
                <tr>
                    <td><?= $cust->id; ?></td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <img src="/img/userPic/<?= isset($cust->profile_picture) ? $cust->profile_picture : 'defaultUser.png'; ?>"
                                alt="Profile Picture"
                                style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;">
                            <?= encode($cust->username); ?>
                        </div>
                    </td>
                    <td><?= ($cust->role == 'blocked') ? "Account is Blocked By Admin" : "Too many Login Attempt" ?></td>

                    <td><?= date('d M Y', strtotime($cust->last_date_operate)); ?></td>
                    <td>
                        <div class="action-buttons">

                            <button class="action-btn view-btn" data-get="viewCust.php?id=<?= $cust->id ?>" title="View">
                                <img src="/img/adminIcon/view.svg" alt="View" title="View">
                            </button>

                            <button class="action-btn block-btn" data-confirm="<?= "Are you sure you want to unblock $cust->username [$cust->id]" ?>" data-post="unblockCust.php?id=<?= $cust->id ?>" title="Unblock User">
                                <img src="/img/adminIcon/unblock.svg" alt="unblock" title="Unblock User">
                            </button>


                            <button class="action-btn delete-btn" data-confirm="<?= "Are you sure you want to delete $cust->username [$cust->id]"  ?>" data-post="deleteCust.php?id=<?= $cust->id ?>" title="Delete User">
                                <img src="/img/adminIcon/delete.svg" alt="Delete" title="Delete User">
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
