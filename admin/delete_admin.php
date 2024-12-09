<?php
include('_admin_head.php');

// Ensure that the logged-in user is a superadmin
auth('Superadmin');

// Check if we have an admin id to delete
if (isset($_GET['id'])) {
    $admin_id = $_GET['id'];

    // Prepare the SQL statement to delete the admin from the database
    $stm = $_db->prepare('DELETE FROM admin WHERE admin_id = ?');
    $stm->execute([$admin_id]);

    // Set a flash message to inform the user that the deletion was successful
    temp('info', 'Admin deleted successfully!');

    // Redirect back to the admin management page
    redirect('admin_management.php');
}
?>
