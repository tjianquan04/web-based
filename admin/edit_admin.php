<link rel="stylesheet" href="/css/flash_msg.css">
<link rel="stylesheet" href="/css/edit_admin.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/js/admin_head.js"></script>
<?php
include('_admin_head.php');

$_err = []; // Initialize error array

auth('Admin', 'Superadmin', 'Product Manager');

// Fetch the admin data based on the 'id' passed in the query string
$admin_id = req('id'); // Get the ID of the admin to edit
$admin = getAdminById($admin_id);

// Handle the form submission to update the admin info
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect the updated data from the form
    $admin_name = req('admin_name');
    $password = req('password');
    $confirm_password = req('confirm_password');
    $email = req('email');
    $phone_number = req('phone_number');
    $role = req('role');
    $status = req('status');
    $photo = get_file('photo'); // Handle file upload for the photo

    // Validate inputs
    if (!$admin_name || !$email) {
        err('error', 'Admin name and email are required!');
    } else {
        // Update admin data in the database
        if ($photo && str_starts_with($photo->type, 'image/')) {
            $photo_path = save_photo($photo, '../image');
            $admin->photo = $photo_path; // Update the photo path if new photo is uploaded
        }

        if ($_SESSION['role'] !== 'Superadmin') {
            // Retain the current role and status for non-Superadmin users
            $role = $admin->role;
            $status = $admin->status;
        }

        // Determine the password
        if ($password === 'DEFAULT_PASSWORD#') {
            // Retain the old password
            $hash_password = $admin->password;
        } else {
            // Hash the new password if provided
            $hash_password = sha1($password);
        }

        // Validate email
        if ($email !== req('email')) {
            if (!is_email(req('email'))) {
                $_err['email_error'] = 'Please enter a valid email address.';
            } elseif (!is_unique(req('email'), 'admin', 'email')) {
                $_err['email_error'] = 'This email address is already registered.';
            }
        }

        // Validate phone number
        if ($phone_number !== req('phone_number')) {
            if (!is_phone($phone_number)) {
                $_err['phone_error'] = 'Phone number must start with 01 and contain 10 or 11 digits.';
            } elseif (!is_unique($phone_number, 'admin', 'phone_number')) {
                $_err['phone_error'] = 'This phone number is already registered.';
            }
        }

        // Validate password
        if (!is_password($password)) {
            $_err['password_error'] = 'Password must be at least 8 characters, include at least one uppercase letter, and one special character.';
        }

        // Check confirm password
        if ($password !== $confirm_password) {
            $_err['confirm_password_error'] = 'Password and confirm password do not match.';
        }

        if (!$_err) {
            // Update the admin data in the database
            $stm = $_db->prepare('
        UPDATE admin
        SET admin_name = ?, password = ?, email = ?, phone_number = ?, role = ?, status = ?, photo = ?
        WHERE admin_id = ?
    ');
            $stm->execute([$admin_name, $hash_password, $email, $phone_number, $role, $status, $admin->photo, $admin_id]);

            // Update the session with the new details
            if ($_SESSION['user']->admin_id === $admin_id) {
                $_SESSION['user']->photo = $admin->photo;
                $_SESSION['user']->admin_name = $admin_name;
            }

            temp('UpdateSuccess', "Account updated successfully");
            temp('showSwal', true); // Set flag to show SweetAlert
        } else {
            temp('EditFail', "Failed to edit account. Please try again.");
            temp('showSwalFail', true); // Set flag to show SweetAlert for failure

            // Save form data for retention
            $_SESSION['admin_form_data'] = $_user;
        }
    }
}
?>



<div class="container">
<button class="back-button" onclick="history.back()">&larr;</button>
    <h1>Edit Profile</h1>
    <form method="POST" enctype="multipart/form-data" class="admin-form" id="addAdminForm">

        <label class="upload admin-photo" tabindex="0">
            <input type="file" name="photo" accept="image/*" style="display: none;" />
            <img
                src="<?= $admin->photo ? '../image/' . $admin->photo : '../image/default_user_photo.png' ?>"
                alt="Admin Photo"
                title="Click to upload new photo" />
        </label>

        <!-- Admin Information Form -->

        <label for="admin_name"><i class="fas fa-user"></i> Admin Name</label>
        <input type="text" id="admin_name" name="admin_name" value="<?= htmlspecialchars($admin->admin_name) ?>" required>

        <!-- Password -->
        <label for="password"><i class="fas fa-lock"></i> Password</label>
        <input
            type="password"
            id="password"
            name="password"
            value="DEFAULT_PASSWORD#"
            placeholder="Enter new password to change"
            required
            onfocus="clearPasswordField(this)"
            onblur="restoreDefaultPwIfEmpty(this)">
        <?= isset($_err['password_error']) ? "<p class='error-message'>{$_err['password_error']}</p>" : ''; ?>

        <!-- Confirm Password -->
        <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required
            value="DEFAULT_PASSWORD#"
            onfocus="clearPasswordField(this)"
            onblur="restoreDefaultPwIfEmpty(this)">
        <?= isset($_err['confirm_password_error']) ? "<p class='error-message'>{$_err['confirm_password_error']}</p>" : ''; ?>

        <!-- Email Fields -->
        <label for="email"><i class="fas fa-envelope"></i> Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin->email) ?>" required>
        <?= isset($_err['email_error']) ? "<p class='error-message'>{$_err['email_error']}</p>" : ''; ?>

        <!-- Phone Fields -->
        <label for="phone_number"><i class="fas fa-phone"></i> Phone Number</label>
        <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($admin->phone_number) ?>">
        <?= isset($_err['phone_error']) ? "<p class='error-message'>{$_err['phone_error']}</p>" : ''; ?>

        <!-- Role and Status (Visible only to Superadmin) -->
        <?php if ($_SESSION['role'] === 'Superadmin') : ?>
            <label for="role"><i class="fas fa-briefcase"></i> Role</label>
            <select id="role" name="role">
                <option value="Superadmin" <?= $admin->role == 'Superadmin' ? 'selected' : '' ?>>Superadmin</option>
                <option value="Admin" <?= $admin->role == 'Admin' ? 'selected' : '' ?>>Admin</option>
                <option value="Product Manager" <?= $admin->role == 'Product Manager' ? 'selected' : '' ?>>Product Manager</option>
            </select>

            <label for="status"><i class="fas fa-check-circle"></i> Status</label>
            <select id="status" name="status">
                <option value="Active" <?= $admin->status == 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= $admin->status == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        <?php endif; ?>

        <section>
            <button type="submit" class="btn-submit">Save Change</button>
            <button type="button" class="btn-clear" onclick="clearForm()">Clear</button>
            <a href="admin_management.php"><button type="button" class="btn-cancel">Cancel</button></a>
        </section>
    </form>

    <script>
        // Auto uppercase admin_name input
        document.getElementById('admin_name').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>

    <?php if (temp('showSwal')): ?>
        <script>
            // Determine the redirection URL based on the role
            const redirectUrl = <?= $_SESSION['user']->role === 'Superadmin'
                                    ? json_encode('admin_management.php')
                                    : json_encode('admin_dashboard.php') ?>;

            // Display swal() popup with the success message and redirect after user confirms
            swal("Congrats", "<?= temp('UpdateSuccess'); ?>", "success")
                .then(function() {
                    window.location.href = redirectUrl; // Redirect to the appropriate page
                });
        </script>
    <?php endif; ?>

    <?php if (temp('showSwalFail')): ?>
        <script>
            // Display swal() popup with the registration failure message
            swal("Error", "<?= temp('EditFail'); ?>", "error");
        </script>
    <?php endif; ?>
</div>