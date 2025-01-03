<link rel="stylesheet" href="/css/flash_msg.css">
<link rel="stylesheet" href="/css/view_admin.css">
<script src="/js/admin_head.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php
include('_admin_head.php');

// Ensure that the logged-in user is a superadmin
auth('Admin', 'Superadmin', 'Product Manager');

// Fetch the admin data based on the 'id' passed in the query string
$admin_id = req('id'); // Get the ID of the admin to view
$admin = getAdminById($admin_id); 
?>

<div class="container">
<button class="back-button" onclick="history.back()">&larr;</button>
    <h1>Admin Profile</h1>
    
    
    <!-- Admin Photo -->
    <div class="admin-photo">
        <img src="<?= $admin->photo ? '../image/' . $admin->photo : '../image/default_user_photo.png' ?>" alt="Admin Photo" />
    </div>
    
    <div class="admin-info">
        <!-- Admin Information Table -->
        <table>
            <tr>
                <td class="label"><i class="fas fa-id-card"></i>Admin ID</td>
                <td class="value"><?= htmlspecialchars($admin->admin_id) ?></td>
            </tr>
            <tr>
                <td class="label"><i class="fas fa-user"></i>Admin Name</td>
                <td class="value"><?= htmlspecialchars($admin->admin_name) ?></td>
            </tr>
            <tr>
                <td class="label"><i class="fas fa-envelope"></i>Email</td>
                <td class="value"><?= htmlspecialchars($admin->email) ?></td>
            </tr>
            <tr>
                <td class="label"><i class="fas fa-phone"></i>Phone Number</td>
                <td class="value"><?= htmlspecialchars($admin->phone_number) ?></td>
            </tr>
            <tr>
                <td class="label"><i class="fas fa-briefcase"></i>Role</td>
                <td class="value"><?= htmlspecialchars($admin->role) ?></td>
            </tr>
            <tr>
                <td class="label"><i class="fas fa-check-circle"></i>Status</td>
                <td class="value"><?= htmlspecialchars($admin->status) ?></td>
            </tr>
        </table>
    </div>
</div>




