<?php
require '_base.php';

// Get the search input
$searchTerm = req('search') ?? '';

if($searchTerm == ""){
    //If there are no search input, select all
    $stm = $_db->query('SELECT * FROM Member');
}else{
    //If there are search input, select according input
    $stm = $_db->prepare('SELECT * FROM Member WHERE name LIKE ? OR email LIKE ? OR contact LIKE ?');
    $stm->execute(["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"]);
}

// Fetch all results
$arr = $stm->fetchAll();

// ----------------------------------------------------------------------------

include '_head.php';
?>
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
        <th>Status</th>
        <th>Actions</th>
    </tr>

    <?php 
    $no = 1; 
    foreach ($arr as $s): 
    ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $s->member_id ?></td>
        <td><?= $s->name ?></td>
        <td><?= $s->status ? 'Active' : 'Inactive' ?></td>
        <td>
            <button data-get="viewMemberDetails.php?id=<?= $s->member_id ?>">View</button>
            <button data-get="editMemberDetails.php?id=<?= $s->member_id ?>">Edit</button>
            <button data-post="blockMember.php?id=<?= $s->member_id ?>">Block</button>
        </td>
    </tr>
    <?php endforeach ?>
</table>

<?php
include '_foot.php';
