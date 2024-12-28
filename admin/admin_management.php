<?php
include('_admin_head.php');
require_once '../lib/SimplePager.php';

// Superadmin authentication
auth('Superadmin', 'Admin', 'Product Manager');

// Sanitize and validate the sort and direction parameters
$valid_columns = ['admin_id', 'admin_name', 'role', 'status']; // List of valid columns for sorting
$valid_dirs = ['asc', 'desc']; // Valid directions

$sort = in_array(req('sort'), $valid_columns) ? req('sort') : 'admin_id';
$dir = in_array(req('dir'), $valid_dirs) ? req('dir') : 'asc';

$page = req('page', 1);
$search = req('search', ''); // Capture the search term
$search_query = '';
$params = []; // Query parameters for prepared statements

if (!empty($search)) {
    $search_query = " AND (admin_id LIKE :search OR admin_name LIKE :search OR email LIKE :search OR role LIKE :search)";
    $params[':search'] = '%' . $search . '%'; // Add wildcard search term
}

// Process Batch Actions
// Process Batch Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete'])) {
        // Process Batch Delete
        $selected_ids = $_POST['selected'] ?? [];
        if (!empty($selected_ids)) {
            $message = batchDelete($selected_ids);
        } else {
            $message = "No selected admins for batch delete.";
        }
    } else {
        // Process Batch Update
        $selected_ids = $_POST['selected'] ?? [];
        $new_role = req('role', '');
        $new_status = req('status', '');

        if (!empty($selected_ids)) {
            batchUpdate($selected_ids, $new_role, $new_status);
        } else {
            $message = "No selected admins for batch update.";
        }
    }
}


// Integrate search query into the final query
$p = new SimplePager(
    "SELECT * FROM admin WHERE `role` != 'superadmin' $search_query ORDER BY $sort $dir",
    $params,
    10, // Items per page
    $page
);

$admins = $p->result;
$total_admins = $_db->query("SELECT COUNT(*) FROM admin WHERE `role` != 'superadmin'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <link rel="stylesheet" href="/css/admin_management.css">
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
        <form method="POST" action="admin_management.php">
            <table border="1">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" /></th>
                        <th>No.</th>
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
                        <th>
                            <a href="?sort=admin_name&dir=<?= ($sort == 'admin_name' && $dir == 'asc') ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&page=<?= htmlspecialchars($page) ?>">
                                Admin Name
                                <?php if ($sort == 'admin_name'): ?>
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
                        <th>
                            <a href="?sort=role&dir=<?= ($sort == 'role' && $dir == 'asc') ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&page=<?= htmlspecialchars($page) ?>">
                                Role
                                <?php if ($sort == 'role'): ?>
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
                        <th>
                            <a href="?sort=status&dir=<?= ($sort == 'status' && $dir == 'asc') ? 'desc' : 'asc' ?>&search=<?= urlencode($search) ?>&page=<?= htmlspecialchars($page) ?>">
                                Status
                                <?php if ($sort == 'status'): ?>
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
                    <button type="button" id="batchUpdateBtn" class="btn btn-update">Batch Update</button>
                    <button type="submit" id="batchDeleteBtn" class="btn btn-delete" name="delete" value="1" onclick='return confirm("Are you sure you want to delete the selected admins?");'> - Batch Delete</button>
                </div>
                <div class="pagination-container">
                    <div class="pagination">
                        <?= generateDynamicPagination($p, $sort, $dir, $search); ?>
                    </div>
                </div>
            </div>

            <!-- Batch Update Modal -->
            <div id="batchUpdateModal" class="modal">
                <div class="modal-content">
                    <span class="close-btn">&times;</span>
                    <h2>Batch Update Selected</h2>
                    <form id="batchUpdateForm">
                        <label for="modalRole">Role:</label>
                        <select id="modalRole" name="role">
                            <option value="Admin">Admin</option>
                            <option value="Product Manager">Product Manager</option>
                        </select>

                        <label for="modalStatus">Status:</label>
                        <select id="modalStatus" name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>

                        <button type="submit" class="btn btn-update">Update</button>
                    </form>
                </div>
            </div>

            <!-- JavaScript for Modal -->
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const batchUpdateBtn = document.getElementById('batchUpdateBtn');
                    const modal = document.getElementById('batchUpdateModal');
                    const closeBtn = document.querySelector('.close-btn');
                    const batchUpdateForm = document.getElementById('batchUpdateForm');
                    const checkboxes = document.querySelectorAll('input[name="selected[]"]');

                    // Batch Update
                    batchUpdateBtn.addEventListener('click', () => {
                        const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;

                        if (selectedCount === 0) {
                            alert("No selected records for updating.");
                            return;
                        }

                        modal.style.display = "block";
                    });

                    closeBtn.addEventListener('click', () => {
                        modal.style.display = "none";
                    });

                    window.addEventListener('click', (event) => {
                        if (event.target === modal) {
                            modal.style.display = "none";
                        }
                    });

                    batchUpdateForm.addEventListener('submit', (event) => {
                        event.preventDefault();

                        const role = document.getElementById('modalRole').value;
                        const status = document.getElementById('modalStatus').value;

                        const selectedIds = Array.from(checkboxes)
                            .filter(cb => cb.checked)
                            .map(cb => cb.value);

                        fetch('batch_update.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    selectedIds,
                                    role,
                                    status
                                }),
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Batch update successful.');
                                    location.reload();
                                } else {
                                    alert('Batch update failed.');
                                }
                            })
                            .catch(error => console.error('Error:', error));

                        modal.style.display = "none";
                    });

                    // Select All Checkbox
                    const selectAllCheckbox = document.getElementById('selectAll');
                    selectAllCheckbox.addEventListener('change', () => {
                        checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
                    });

                    // Batch Delete
                    document.getElementById('batchDeleteBtn').addEventListener('click', (event) => {
                        const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;

                        if (selectedCount === 0) {
                            event.preventDefault(); // Prevent form submission
                            alert("No selected records for deletion.");
                        }
                    });
                });
            </script>

</body>

</html>