<?php
include('admin_head.php');

// Ensure that the logged-in user is a superadmin
if ($_SESSION['role'] !== 'superadmin') {
    // If the logged-in user is not a superadmin, redirect to a different page (e.g., dashboard)
    header('Location: dashboard.php');
    exit();
}

// Fetch all admins excluding the superadmin
$admins = getAllAdmins();
 // Function to fetch all admins excluding 'superadmin'
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
                    <th>Admin ID</th>
                    <th>Admin Name</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Ensure $admins is an array
                if ($admins && is_array($admins)) {
                    $num = 1;  // Start numbering from 1
                    foreach ($admins as $row) {
                        // Display each admin in the table, assuming $row is an associative array
                        echo "<tr>
                            <td>" . $num++ . "</td>
                            <td>" . htmlspecialchars($row->admin_id) . "</td>
                            <td>" . htmlspecialchars($row->admin_name) . "</td>
                            <td>" . htmlspecialchars($row->role) . "</td>
                            <td>" . htmlspecialchars($row->status) . "</td>
                            <td>
                                <a href='view_admin.php?id=" . $row->admin_id . "' class='btn btn-view'>View</a>
                                <a href='edit_admin.php?id=" . $row->admin_id. "' class='btn btn-edit'>Edit</a>
                                <a href='delete_admin.php?id=" . $row->admin_id. "' class='btn btn-delete' onclick='return confirm(\"Are you sure you want to delete this admin?\");'>Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No admins found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
