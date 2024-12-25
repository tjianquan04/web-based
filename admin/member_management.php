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

$total_members = $_db->query("SELECT COUNT(*) FROM member")->fetchColumn();

// ----------------------------------------------------------------------------

?>
<script src="../js/main.js"></script>
<link rel="stylesheet" href="../css/admin_management.css">

<div class="container">
    <form method="get">
        <?= html_search('search') ?>
        <button>Search</button>
    </form>
    <div class="top-right">
        <!-- Total Record -->
        <span class="total-record">Total:<?= $total_members ?> Records</span>
    <form method="post" id="batch">
       <button formaction="../memberCRUD/insert_records.php">Insert</button>
       <button formaction="../memberCRUD/delete_member.php" delete-confirm id="batch-delete" >Delete</button>
       
       <div class="batch-update-status">
        <select class="form-control" name="batch-status" id="batch-status">
            <option value = '' disabled selected>Select-Status</option>
            <option value="Active">Active</option>
            <option value="Iactive">Inactive</option>
        </select>
        <button formaction="../memberCRUD/batch_update_status.php" id="batch-update">Update</button>
       </div>
    </form> 
    </div>


<table class="table">
    <tr>
        <th>#</th>
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
                Name
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
        $rowClass = ($s->status === 'Active') ? '' : 'inactive';
    ?>
        <tr class="<?= $rowClass ?>">
            <td>
                 <input type="checkbox" name="id[]" value="<?= $s->member_id ?>" form="batch">
            </td>
            <td><?= $no++ ?></td>
            <td><?= $s->member_id ?></td>
            <td><?= $s->name ?></td>
            <td><?= $s->email ?></td>
            <td><?= $s->contact ?></td>
            <td><?= $s->status ?></td>
            <td>
            
            <button class= "btn btn-view" data-get="../memberCRUD/view_member_details.php?id=<?= $s->member_id ?>">
                <i class='fas fa-eye'></i>View
            </button>
            <button class= "btn btn-edit" data-get="../memberCRUD/edit_member_details.php?id=<?= $s->member_id ?>">
                <i class='fas fa-tools'></i>Edit
            </button>
            <button data-post="../memberCRUD/update_member_status.php?id=<?= $s->member_id ?>"
                class="btn  <?= ($s->status === 'Active') ? 'block' : 'unblock' ?>">
                <?php if ($s->status === 'Active'): ?>
                    <i class="fas fa-user-slash"></i> Block
                <?php else: ?>
                    <i class="fas fa-user"></i> Unblock
                <?php endif; ?>
            </button>
                <button class= "btn btn-delete" data-post="../memberCRUD/delete_member.php?id=<?= $s->member_id ?>" delete-confirm data-member-ids="<?= $s->member_id ?>"><i class='fas fa-trash-alt'></i>Delete</button>
            </td>
        </tr>
    <?php endforeach ?>
</table>
<br>
<div class="pagination">
                <?= generateDynamicPagination($p, $sort, $dir, $searchTerm); ?>
</div>
<br>
</div>

<script>
    $('#batch-delete').on('click', function(e) {
    // Find all checked checkboxes and collect their values
    const selectedIds = [];
    $('input[name="id[]"]:checked').each(function() {
        selectedIds.push($(this).val());
    });

    // If there are selected IDs, set them as a comma-separated string in data-member-ids
    if (selectedIds.length > 0) {
        $(this).attr('data-member-ids', selectedIds.join(','));
    } else {
        // If no IDs are selected, prevent form submission and show an alert
        alert('Please select at least one member to delete.');
        e.preventDefault();
    }
});

$('#batch-update').on('click', function(e) {
    // Find all checked checkboxes and collect their values
    const selectedIds = [];
    $('input[name="id[]"]:checked').each(function() {
        selectedIds.push($(this).val());
    });

    // Get the selected status from the dropdown
    const selectedStatus = $('#batch-status').val();

    // If there are selected IDs and a valid status is selected, set them as a comma-separated string in data-member-ids and data-status
    if (selectedIds.length > 0 && selectedStatus) {
        $(this).attr('data-member-ids', selectedIds.join(','));
        $(this).attr('data-status', selectedStatus);
    } else {
        // If no IDs are selected or no status is selected, prevent form submission and show an alert
        alert('Please select at least one member and a status to update.');
        e.preventDefault();
    }
});
</script>
</html>
