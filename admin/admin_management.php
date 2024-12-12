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

// Modify SQL query to allow sorting by any of the valid columns
$p = new SimplePager("SELECT * FROM admin WHERE `role` != 'superadmin' ORDER BY $sort $dir", [], 10, $page);
$admins = $p->result;

// Handle POST request for adding admin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_name = $_POST['admin_name'];
    $adminEmail = $_POST['adminEmail'];
    $adminPassword = $_POST['adminPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($adminPassword !== $confirmPassword) {
        echo "Passwords do not match!";
        exit;
    }

    $result = addAdmin($admin_name, $adminEmail, $adminPassword);

    if ($result) {
        temp('info', 'Admin added successfully!');
        redirect('admin_management.php');
    } else {
        echo "Failed to add admin!";
    }
}

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
        <h1>Admin Management</h1>

        <!-- Admin Table -->
        <table border="1">
            <thead>
                <tr>
                    <th>#</th>
                    <th>
                        <a href="?sort=admin_id&dir=<?= ($sort == 'admin_id' && $dir == 'asc') ? 'desc' : 'asc' ?>">
                            Admin ID
                            <?php if ($sort == 'admin_id'): ?>
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
                        <a href="?sort=admin_name&dir=<?= ($sort == 'admin_name' && $dir == 'asc') ? 'desc' : 'asc' ?>">
                            Admin Name
                            <?php if ($sort == 'admin_name'): ?>
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
                        <a href="?sort=role&dir=<?= ($sort == 'role' && $dir == 'asc') ? 'desc' : 'asc' ?>">
                            Role
                            <?php if ($sort == 'role'): ?>
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($admins && is_array($admins)) {
                    $num = ($page - 1) * 10 + 1; // Start numbering from 1
                    foreach ($admins as $row) {
                        echo "<tr>
                    <td>" . $num++ . "</td>
                    <td>" . htmlspecialchars($row->admin_id) . "</td>
                    <td>" . htmlspecialchars($row->admin_name) . "</td>
                    <td>" . htmlspecialchars($row->role) . "</td>
                    <td>" . htmlspecialchars($row->status) . "</td>
                    <td>
                        <a href='view_admin.php?id=" . $row->admin_id . "' class='btn btn-view'><i class='fas fa-eye'></i>View</a>
                        <a href='edit_admin.php?id=" . $row->admin_id . "' class='btn btn-edit'><i class='fas fa-tools'></i>Edit</a>
                        <a href='delete_admin.php?id=" . $row->admin_id . "' class='btn btn-delete' onclick='return confirm(\"Are you sure you want to delete this admin?\");'><i class='fas fa-trash-alt'></i>Delete</a>
                    </td>
                </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No admins found.</td></tr>";
                }
                ?>
            </tbody>
        </table>


        <!-- Button to Add New Admin and pagination-->
        <div class="pagination-container">
            <a href="admin_add.php" class="btn btn-add">+ Add New Admin</a>
            <div class="pagination">
                <?= generateDynamicPagination($p, $sort, $dir); ?>
            </div>
        </div>
    </div>
</body>

</html>