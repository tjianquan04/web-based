<script src="https://cdn.jsdelivr.net/npm/sweetalert/dist/sweetalert.min.js"></script>
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
    temp('DeleteSucess', "Account delete successfully");
    temp('showSwal', true); // Set flag to show SweetAlert
}
?>
<?php if (temp('showSwal')): ?>
    <script>
        // Display swal() popup with the registration success message
        swal("Congrats", "<?= temp('AddingSuccess'); ?>", "success")
            .then(function() {
                window.location.href = 'admin_management.php'; // Redirect after the popup closes
            });
    </script>
<?php endif; ?>