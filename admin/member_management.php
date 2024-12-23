<?php
include '../_base.php';
include '../_head.php';
// Get the search input
$searchTerm = req('search') ?? '';

//Get page
$page = req('page', 1);

//validate the sort parameters
$valid_columns = ['member_id', 'name', 'email', 'contact', 'status'];
$valid_dirs = ['asc', 'desc'];

// Retrieve sort and direction from query parameters or use defaults
$sort = in_array(req('sort'), $valid_columns) ? req('sort') : 'member_id';  // Default to 'member_id'
$dir = in_array(req('dir'), $valid_dirs) ? req('dir') : 'asc';  // Default to 'asc'

// Construct the base query
$query = 'SELECT * FROM Member';
$params = [];

// Add search conditions if a search term is provided
if ($searchTerm != "") {
    $query .= ' WHERE name LIKE ? OR email LIKE ? OR contact LIKE ?';
    $params = ["%$searchTerm%", "%$searchTerm%", "%$searchTerm%"];
}

$query .= " ORDER BY $sort $dir";

require_once '../lib/SimplePager.php';
$p = new SimplePager($query, $params, 10, $page);

// Fetch results
$arr = $p->result;


// ----------------------------------------------------------------------------

?>
<script src="../js/main.js"></script>
<link rel="stylesheet" href="../css/member.css">

<div class="container">
    <form method="get">
        <?= html_search('search') ?>
        <button>Search</button>
    </form>
    <div class="top-right">
    </div>
</div>

<table class="table">
    <tr>
        <th>No.</th>
        <th>
            <a href="?sort=member_id&dir=<?= ($sort == 'member_id' && $dir == 'asc') ? 'desc' : 'asc' ?>">
                Member ID
                <?php if ($sort == 'member_id'): ?>
                    <?php if ($dir == 'asc'): ?>
                        <i class="fas fa-arrow-up arrow-right"></i> <!-- Up arrow for ascending -->
                    <?php else: ?>
                        <i class="fas fa-arrow-down arrow-right"></i> <!-- Down arrow for descending -->
                    <?php endif; ?>
                <?php else: ?>
                    <i class="fas fa-sort arrow-right"></i> <!-- Default sort icon -->
                <?php endif; ?>
            </a>
        </th>
        <th>
            <a href="?sort=name&dir=<?= ($sort == 'name' && $dir == 'asc') ? 'desc' : 'asc' ?>">
                Member Name
                <?php if ($sort == 'name'): ?>
                    <?php if ($dir == 'asc'): ?>
                        <i class="fas fa-arrow-up arrow-right"></i> <!-- Up arrow for ascending -->
                    <?php else: ?>
                        <i class="fas fa-arrow-down arrow-right"></i> <!-- Down arrow for descending -->
                    <?php endif; ?>
                <?php else: ?>
                    <i class="fas fa-sort arrow-right"></i> <!-- Default sort icon -->
                <?php endif; ?>
            </a>

        </th>
        <th>
            <a href="?sort=email&dir=<?= ($sort == 'email' && $dir == 'asc') ? 'desc' : 'asc' ?>">
                Email Address
                <?php if ($sort == 'email'): ?>
                    <?php if ($dir == 'asc'): ?>
                        <i class="fas fa-arrow-up arrow-right"></i> <!-- Up arrow for ascending -->
                    <?php else: ?>
                        <i class="fas fa-arrow-down arrow-right"></i> <!-- Down arrow for descending -->
                    <?php endif; ?>
                <?php else: ?>
                    <i class="fas fa-sort arrow-right"></i> <!-- Default sort icon -->
                <?php endif; ?>
            </a>

        </th>
        <th>
            <a href="?sort=contact&dir=<?= ($sort == 'contact' && $dir == 'asc') ? 'desc' : 'asc' ?>">
                Contact
                <?php if ($sort == 'contact'): ?>
                    <?php if ($dir == 'asc'): ?>
                        <i class="fas fa-arrow-up arrow-right"></i> <!-- Up arrow for ascending -->
                    <?php else: ?>
                        <i class="fas fa-arrow-down arrow-right"></i> <!-- Down arrow for descending -->
                    <?php endif; ?>
                <?php else: ?>
                    <i class="fas fa-sort arrow-right"></i> <!-- Default sort icon -->
                <?php endif; ?>
            </a>
        </th>
        <th>
            <a href="?sort=status&dir=<?= ($sort == 'status' && $dir == 'asc') ? 'desc' : 'asc' ?>">
                Status
                <?php if ($sort == 'status'): ?>
                    <?php if ($dir == 'asc'): ?>
                        <i class="fas fa-arrow-up arrow-right"></i> <!-- Up arrow for ascending -->
                    <?php else: ?>
                        <i class="fas fa-arrow-down arrow-right"></i> <!-- Down arrow for descending -->
                    <?php endif; ?>
                <?php else: ?>
                    <i class="fas fa-sort arrow-right"></i> <!-- Default sort icon -->
                <?php endif; ?>
            </a>
        </th>
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
                <button data-get="../memberCRUD/view_member_details.php?id=<?= $s->member_id ?>"><i class='fas fa-eye'></i>View</button>
                <button data-get="../memberCRUD/edit_member_details.php?id=<?= $s->member_id ?>"><i class='fas fa-tools'></i>Edit</button>
                <button data-post="../memberCRUD/update_member_status.php?id=<?= $s->member_id ?>"
                    class="block-btn <?= $s->status ? 'block' : 'unblock' ?>">
                    <?php if ($s->status == true): ?>
                        <i class="fas fa-user-slash"></i>      
                    <?php elseif($s->status == false): ?>
                    <i class="fas fa-user"></i>
                    <?php endif; ?>
                    <?= $s->status ? 'Block' : 'Unblock' ?>
                </button>
                <button data-post="../memberCRUD/delete_member.php?id=<?= $s->member_id ?>" delete-confirm data-member-id="<?= $s->member_id ?>"><i class='fas fa-trash-alt'></i>Delete</button>
            </td>
        </tr>
    <?php endforeach ?>
</table>
<br>
<?= $p->html("search=$searchTerm") ?>
<br>

</html>
