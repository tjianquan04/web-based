<link rel="stylesheet" href="/css/flash_msg.css">
<?php
include('_admin_head.php');

// Ensure that the logged-in user is a superadmin
//if ($_SESSION['role'] !== 'superadmin') {
//    // If the logged-in user is not a superadmin, redirect to a different page (e.g., dashboard)
//    header('Location: dashboard.php');
//   exit();
//}

// Fetch all admins excluding the superadmin
$admins = getAllAdmins();
// Function to fetch all admins excluding 'superadmin'
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Collect and sanitize input
    $admin_name = $_POST['admin_name'];
    $adminEmail = $_POST['adminEmail'];
    $adminPassword = $_POST['adminPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if passwords match
    if ($adminPassword !== $confirmPassword) {
        echo "Passwords do not match!";
        exit;
    }

    // Call the function to add the admin
    $result = addAdmin($admin_name, $adminEmail, $adminPassword);
    
    // Handling success or failure
    if ($result) {
        temp('info','Admin added successfully!');
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
        <!-- Button to Add New Admin -->
        <button class="btn btn-add" onclick="openModal()">+ Add New Admin</button>

        <!-- Modal for Adding New Admin -->
        <div id="addAdminModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Add New Admin</h2>

                <form id="addAdminForm" method="POST">
                    <label for="admin_name">Admin Name<i class="fas fa-name"></i></label>
                    <div class="input-container">
                    <input type="text" id="admin_name" name="admin_name" placeholder="Enter Name" oninput="this.value = this.value.toUpperCase()" required>
                    </div><br>

                    <label for="adminEmail">Email<i class="fas fa-envelope"></i></label>
                    <div class="input-container">
                        <input type="email" id="adminEmail" name="adminEmail" placeholder="Enter Email" required>
                    </div><br>

                    <label for="adminPassword">Password<i class="fas fa-lock"></i></label>
                    <div class="input-container">
                        <input type="password" id="adminPassword" name="adminPassword" placeholder="Enter Password" required>
                    </div><br>

                    <label for="confirmPassword">Confirm Password<i class="fas fa-lock"></i></label>
                    <div class="input-container">
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
                    </div><br>

                    <div class="form-buttons">
                        <button type="submit" class="btn btn-submit">Add Admin</button>
                        <button type="button" class="btn btn-clear" onclick="clearForm()">Clear</button>
                    </div>
                </form>
            </div>
        </div>
</body>

</html>
<?php

?>