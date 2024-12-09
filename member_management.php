<?php
require '_base.php';

// Get the search input
$searchTerm = req('search') ?? '';

//Get page
$page = req('page', 1);

// Construct the base query
$query = 'SELECT * FROM Member';
$params = [];

// Add search conditions if a search term is provided
if ($searchTerm != "") {
    $query .= ' WHERE name LIKE ? OR email LIKE ? OR contact LIKE ?';
    $params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
}

require_once 'lib/SimplePager.php';
$p = new SimplePager($query, $params, 10, $page);

// Fetch results
$arr = $p->result;


// ----------------------------------------------------------------------------

include '_head.php';
?>
<script src="/js/main.js"></script>
<link rel="stylesheet" href="/css/member.css">

<div class="container">
    <form method="get">
        <?= html_search('search') ?>
        <button>Search</button>
    </form>
    <p class="records-count"><?= count($arr) ?> record(s)</p>
</div>

<table class="table">
    <tr>
        <th>No.</th>
        <th>Member ID</th>
        <th>Member Name</th>
        <th>Email Address</th>
        <th>Contact</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    <?php 
    $pageSize = 10; 
    $no = ($page - 1) * $pageSize + 1; 

    foreach ($arr as $s): 
        $rowClass = $s->status ? '' : 'inactive';
    ?>
    <tr class="<?= $rowClass ?>">
        <td><?= $no++ ?></td>
        <td><?= $s->member_id ?></td>
        <td><?= $s->name ?></td>
        <td><?= $s->email ?></td>
        <td><?= $s->contact ?></td>
        <td><?= $s->status ? 'Active' : 'Inactive' ?></td>
        <td>
            <button data-get="view_member_details.php?id=<?= $s->member_id ?>">View</button>
            <button data-get="edit_member_details.php?id=<?= $s->member_id ?>">Edit</button>
            <button data-post="block_member.php?id=<?= $s->member_id ?>">Block</button>
            <button data-post="delete_member.php?id=<?= $s->member_id ?>">Delete</button>
            <img src="<?= $s->profile_photo ?>" class="popup">
        </td>
    </tr>
    <?php endforeach ?>
</table>
<br>
<?= $p->html("search=$searchTerm") ?>
<br>

<?php
include '_foot.php';
