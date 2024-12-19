<?php
// Include necessary files
include('_admin_head.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $_user = new stdClass(); // Create an object to store user data
    $_user->admin_name = req('admin_name');
    $_user->email = req('email');
    $_user->password = req('password');
    $_user->phone_number = req('phone_number');
    $_user->role = req('role');
    $_user->status = req('status');
    $_user->photo = get_file('photo'); // Handle file upload for the photo

    // Validate required fields
    if (!$_user->admin_name || !$_user->email || !$_user->password) {
        err('error', 'Admin name, email, and password are required!');
    } else {
        // Call the function to add the admin to the database
        $result = addAdmin($_user);  // Pass the $_user object to the function

        if ($result) {
            temp('AddingSuccess', "Account register successfully");
            temp('showSwal', true); // Set flag to show SweetAlert
        } else {
            err('error', 'Failed to add admin. The email may already exist.');
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js"></script>
    <title>Add Admin</title>
    <link rel="stylesheet" href="/css/flash_msg.css">
    <link rel="stylesheet" href="/css/edit_admin.css">
    <script src="../js/main.js"></script>
</head>

<body>
    <div class="container">
        <h1>Add New Admin</h1>

        <!-- Form for adding new admin -->
        <form method="POST" enctype="multipart/form-data" class="admin-form" id="addAdminForm">

            <!-- Admin Photo -->
            <label class="upload admin-photo" tabindex="0">
                <input type="file" name="photo" accept="image/*" style="display: none;" />
                <img src="../image/default_user_photo.png" alt="Admin Photo" title="Click to upload new photo" />
            </label>

            <!-- Admin Name -->
            <label for="admin_name"><i class="fas fa-user"></i> Admin Name</label>
            <input type="text" id="admin_name" name="admin_name" placeholder="Enter Name" required>

            <!-- Password and Confirm Password Fields -->
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required>

            <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>

            <!-- Admin Email -->
            <label for="email"><i class="fas fa-envelope"></i> Email</label>
            <input type="email" id="email" name="email" placeholder="Enter Email" required>

            <!-- Phone Number -->
            <label for="phone_number"><i class="fas fa-phone"></i> Phone Number</label>
            <input type="text" id="phone_number" name="phone_number" placeholder="Enter Phone Number">

            <!-- Role -->
            <label for="role"><i class="fas fa-briefcase"></i> Role</label>
            <select id="role" name="role" required>
                <option value="Admin">Admin</option>
            </select>

            <!-- Status -->
            <label for="status"><i class="fas fa-check-circle"></i> Status</label>
            <select id="status" name="status" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>

            <!-- Form Buttons -->
            <!-- Buttons Section -->
            <section>
                <button type="submit" class="btn-submit">Confirm</button>
                <button type="button" class="btn-clear" onclick="clearForm()">Clear</button>
                <a href="admin_management.php"><button type="button" class="btn-cancel">Cancel</button></a>
            </section>
        </form>

        <!-- Display any errors -->
        <?php if (isset($error)) {
            echo "<p class='error-message'>$error</p>";
        } ?>
    </div>

    <?php if (temp('showSwal')): ?>
        <script>
            // Display swal() popup with the registration success message
            swal("Congrats", "<?= temp('AddingSuccess'); ?>", "success")
                .then(function() {
                    window.location.href = 'admin_dashboard.php'; // Redirect after the popup closes
                });
        </script>
    <?php endif; ?>
</body>

</html>