<?php
include('_admin_head.php');
require_once '../lib/SimplePager.php';

// Superadmin authentication
auth('Superadmin');

// Sanitize and validate the sort and direction parameters
$valid_columns = ['admin_id', 'admin_name', 'role', 'status'];  // List of valid columns for sorting
$valid_dirs = ['asc', 'desc'];  // Valid directions

// Retrieve sort and direction from query parameters or use defaults
$sort = in_array(req('sort'), $valid_columns) ? req('sort') : 'admin_id';  // Default to 'admin_id'
$dir = in_array(req('dir'), $valid_dirs) ? req('dir') : 'asc';  // Default to 'asc'

// Set page number
$page = req('page', 1);

// Search logic
$search = req('search', ''); // Capture the search term
$search_query = '';
$params = []; // Query parameters for prepared statements

if (!empty($search)) {
    // Append the search condition
    $search_query = " AND (admin_id LIKE :search OR admin_name LIKE :search OR email LIKE :search OR role LIKE :search)";
    $params[':search'] = '%' . $search . '%'; // Add wildcard search term
}

// Integrate search query into the final query
$p = new SimplePager(
    "SELECT * FROM admin WHERE `role` != 'superadmin' $search_query ORDER BY $sort $dir",
    $params,
    10, // Items per page
    $page
);

$admins = $p->result;

// Get total number of admins (excluding 'superadmin')
$total_admins = $_db->query("SELECT COUNT(*) FROM admin WHERE `role` != 'superadmin'")->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <link rel="stylesheet" href="/css/admin_management.css">
    <script src="/js/swal.js"></script>
</head>

<body>
    <div class="container">
        <div class="admin-management-header">
            <h1>Admin Management</h1>

            <!-- Search Bar -->
            <div class="search-bar-container">
                <form action="admin_management.php" method="GET">
                    <input type="text" name="search" placeholder="Search by ID, Name, Email, Role..." value="<?= htmlspecialchars($search) ?>" />

                    <!-- Hidden fields for sort, dir, and page -->
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                    <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
                    <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
                </form>
            </div>

            <!-- Total Record -->
            <span class="total-record">Total Records of Admin: <?= $total_admins ?></span>
        </div>

        <!-- Admin Table -->
        <form id="batchForm" action="batch_action.php" method="POST">
            <table border="1">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" /></th>
                        <th>#</th>
                        <th>
                            <a href="?sort=admin_id&dir=<?= ($sort == 'admin_id' && $dir == 'asc') ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&page=<?= htmlspecialchars($page) ?>">
                                Admin ID
                                <?php if ($sort == 'admin_id'): ?>
                                    <?php if ($dir == 'asc'): ?>
                                        <i class="fas fa-arrow-up arrow-right"></i>
                                    <?php else: ?>
                                        <i class="fas fa-arrow-down arrow-right"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-sort arrow-right"></i>
                                <?php endif; ?>
                            </a>
                        </th>

                        <th>Admin Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($admins && is_array($admins)) {
                        $num = ($page - 1) * 10 + 1; // Start numbering from 1
                        foreach ($admins as $row) {
                            echo "<tr>
                            <td><input type='checkbox' name='selected[]' value='" . htmlspecialchars($row->admin_id) . "' /></td>
                            <td>" . $num++ . "</td>
                            <td>" . htmlspecialchars($row->admin_id) . "</td>
                            <td>" . htmlspecialchars($row->admin_name) . "</td>
                            <td>" . htmlspecialchars($row->role) . "</td>
                            <td>" . htmlspecialchars($row->status) . "</td>
                            <td>
                                <a href='view_admin.php?id=" . $row->admin_id . "' class='btn btn-view'>View</a>
                                <a href='edit_admin.php?id=" . $row->admin_id . "' class='btn btn-edit'>Edit</a>
                                <a href='delete_admin.php?id=" . $row->admin_id . "' class='btn btn-delete' onclick='return confirm(\"Are you sure you want to delete this admin?\");'><i class='fas fa-trash-alt'></i>Delete</a>
                            </td>
                        </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No admins found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Buttons for Batch Action -->
            <div class="batch-actions-container">
                <div class="batch-actions-left">
                    <a href="admin_add.php" class="btn btn-add">+ Add New Admin</a>
                    <button type="button" id="batchActionBtn" class="btn btn-batch">Batch Action</button>
                </div>
                <div class="pagination-container">
                    <div class="pagination">
                        <?= generateDynamicPagination($p, $sort, $dir, $search); ?>
                    </div>
                </div>
            </div>

        </form>


    </div>

    <!-- Batch Action Modal -->
    <div id="batchModal" class="modal hidden">
        <div class="modal-content">
            <h2>Batch Actions</h2>
            <form action="batch_process.php" method="POST">
                <input type="hidden" name="selected_ids" id="selectedIds" />
                <div>
                    <label for="batchRole">Update Role:</label>
                    <input type="text" id="batchRole" name="role" />
                </div>
                <div>
                    <label for="batchStatus">Update Status:</label>
                    <input type="text" id="batchStatus" name="status" />
                </div>
                <button type="submit" name="action" value="update">Update</button>
                <button type="submit" name="action" value="delete">Batch Delete</button>
            </form>
        </div>
    </div>

    <script>
        // Handle "Select All" checkbox
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });

        // Handle Batch Action button
        document.getElementById('batchActionBtn').addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('input[name="selected[]"]:checked'));

            if (selected.length === 0) {
                showSwal('No selected record', 'error');
                return;
            }

            const selectedIds = selected.map(input => input.value).join(',');
            document.getElementById('selectedIds').value = selectedIds;

            document.getElementById('batchModal').classList.remove('hidden');
        });

        // Close modal logic
        document.querySelector('.modal .close').addEventListener('click', function() {
            document.getElementById('batchModal').classList.add('hidden');
        });

        function showSwal(message, type) {
            // Replace with your Swal implementation
            alert(`${type.toUpperCase()}: ${message}`);
        }
    </script>
</body>

</html>