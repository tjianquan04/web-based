<?php
// Include necessary files
include('_admin_head.php');
auth('Superadmin');

$_err = []; // Initialize error array

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $_user = new stdClass(); // Create an object to store user data
    $_user->admin_name = req('admin_name');
    $_user->email = req('email');
    $_user->password = req('password');
    $_user->confirm_password = req('confirm_password');
    $_user->phone_number = req('phone_number');
    $_user->role = req('role');
    $_user->status = req('status');
    $_user->photo = get_file('photo'); // Handle file upload for the photo

    // Validate required fields
    if (!$_user->admin_name || !$_user->email || !$_user->password) {
        $_err['error'] = 'Admin name, email, and password are required!';
    } else {
        // Validate email
        if (!is_email($_user->email)) {
            $_err['email_error'] = 'Please enter a valid email address.';
        } elseif (!is_unique($_user->email, 'admin', 'email')) {
            $_err['email_error'] = 'This email address is already registered.';
        }

        // Validate phone number
        if (!is_phone($_user->phone_number)) {
            $_err['phone_error'] = 'Phone number must start with 01 and contain 10 or 11 digits.';
        } elseif (!is_unique($_user->phone_number, 'admin', 'phone_number')) {
            $_err['phone_error'] = 'This phone number is already registered.';
        }

        // Validate password
        if (!is_password($_user->password)) {
            $_err['password_error'] = 'Password must be at least 8 characters, include at least one uppercase letter, and one special character.';
        }

        // Check confirm password
        if ($_user->password !== $_user->confirm_password) {
            $_err['confirm_password_error'] = 'Password and confirm password do not match.';
        }

        if (!$_err) {
            $result = addAdmin($_user);

            if ($result) {
                temp('AddingSuccess', "Account registered successfully");
                temp('showSwal', true); // Set flag to show SweetAlert
            }
        } else {
            temp('AddingFail', "Failed to register account. Please try again.");
            temp('showSwalFail', true); // Set flag to show SweetAlert for failure

            // Save form data for retention
            $_SESSION['admin_form_data'] = $_user;
        }
    }
}

$role_value = isset($_user->role) ? $_user->role : 'Admin';
$status_value = isset($_user->status) ? $_user->status : 'Active';

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Add Admin</title>
    <link rel="stylesheet" href="/css/flash_msg.css">
    <link rel="stylesheet" href="/css/edit_admin.css">
    <script src="/js/main.js"></script>
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
            <input type="text" id="admin_name" name="admin_name" placeholder="Enter Name" required >

            <!-- Password and Confirm Password Fields -->
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required >
            <?= isset($_err['password_error']) ? "<p class='error-message'>{$_err['password_error']}</p>" : ''; ?>

            <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required >
            <?= isset($_err['confirm_password_error']) ? "<p class='error-message'>{$_err['confirm_password_error']}</p>" : ''; ?>

            <!-- Admin Email -->
            <label for="email"><i class="fas fa-envelope"></i> Email</label>
            <input type="email" id="email" name="email" placeholder="Enter Email" required >
            <?= isset($_err['email_error']) ? "<p class='error-message'>{$_err['email_error']}</p>" : ''; ?>

            <!-- Phone Number -->
            <label for="phone_number"><i class="fas fa-phone"></i> Phone Number</label>
            <input type="text" id="phone_number" name="phone_number" placeholder="Enter Phone Number" >
            <?= isset($_err['phone_error']) ? "<p class='error-message'>{$_err['phone_error']}</p>" : ''; ?>

            <!-- Role -->
            <label for="role"><i class="fas fa-briefcase"></i> Role</label>
            <select id="role" name="role" required>
                <option value="Admin" <?= $role_value == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="Product Manager" <?= $role_value == 'Product Manager' ? 'selected' : ''; ?>>Product Manager</option>
            </select>

            <!-- Status -->
            <label for="status"><i class="fas fa-check-circle"></i> Status</label>
            <select id="status" name="status" required>
                <option value="Active" <?= $status_value == 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?= $status_value == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
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

    <script>
        // Auto uppercase admin_name input
        document.getElementById('admin_name').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>

    <?php if (temp('showSwal')): ?>
        <script>
            // Display swal() popup with the registration success message
            swal("Congrats", "<?= temp('AddingSuccess'); ?>", "success")
                .then(function() {
                    window.location.href = 'admin_dashboard.php'; // Redirect after the popup closes
                });
        </script>
    <?php endif; ?>

    <?php if (temp('showSwalFail')): ?>
        <script>
            // Display swal() popup with the registration failure message
            swal("Error", "<?= temp('AddingFail'); ?>", "error");
        </script>
    <?php endif; ?>
</body>

</html>