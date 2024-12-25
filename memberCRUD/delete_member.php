<?php
include('../_base.php');

//auth('Admin');

if (is_Post()) {
    $member_ids = req('id'); 

    if (!empty($member_ids)) {

        $member_ids = is_array($member_ids) ? $member_ids : explode(',', $member_ids);

        // Prepare the query
        $query = "DELETE FROM Member WHERE member_id = ?";
        $stmt = $_db->prepare($query);

        $deleted_ids = [];

        foreach ($member_ids as $id) {
            $stmt->execute([$id]);
            if ($stmt->rowCount() > 0) {
                $deleted_ids[] = $id;
            }
        }

        // Generate a message for all successfully deleted IDs
        if (!empty($deleted_ids)) {
            temp('info', 'Successfully deleted member IDs: ' . implode(', ', $deleted_ids));
        } else {
            temp('info', 'No members were deleted.');
        }
    } else {
        temp('info', 'No member ID(s) provided for deletion.');
    }

    redirect('../admin/member_management.php');
}
?>