<link rel="stylesheet" href="/css/flash_msg.css">
<link rel="stylesheet" href="/css/edit_admin.css">
<script src="../js/main.js"></script>
<?php
include('_admin_head.php');

auth('Admin', 'Superadmin');

// Fetch the admin data based on the 'id' passed in the query string
$admin_id = req('id'); // Get the ID of the admin to edit
$admin = getAdminById($admin_id);

// Handle the form submission to update the admin info
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect the updated data from the form
    $admin_name = req('admin_name');
    $password = req('password');
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

        // Determine the password
        if ($password === '********') {
            // Retain the old password
            $hash_password = $admin->password;
        } else {
            // Hash the new password if provided
            $hash_password = sha1($password);
        }

        // Update the admin data in the database
        $stm = $_db->prepare('
            UPDATE admin
            SET admin_name = ?, password = ?, email = ?, phone_number = ?, role = ?, status = ?, photo = ?
            WHERE admin_id = ?
        ');
        $stm->execute([$admin_name, $hash_password, $email, $phone_number, $role, $status, $admin->photo, $admin_id]);

        temp('info', 'Admin updated successfully!');
        redirect('admin_management.php');
    }
}
?>

<div class="container">
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
            value="********"
            placeholder="Enter new password to change"
            required
            onfocus="clearPasswordField(this)"
            onblur="restoreDefaultPwIfEmpty(this)">

        <!-- Email Fields -->
        <label for="email"><i class="fas fa-envelope"></i> Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin->email) ?>" required>

        <!-- Phone Fields -->
        <label for="phone_number"><i class="fas fa-phone"></i> Phone Number</label>
        <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($admin->phone_number) ?>">

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

</div>