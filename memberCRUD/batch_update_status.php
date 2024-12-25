<?php
include('../_base.php');

//auth('Admin');

if (is_Post()) {
    $member_ids = req('id'); 
    $status = req('batch-status'); 

    if (!empty($member_ids) && !empty($status)) {

        // Ensure $member_ids is an array 
        $member_ids = is_array($member_ids) ? $member_ids : explode(',', $member_ids);

        // Prepare the query to update the member status
        $query = "UPDATE Member SET status = ? WHERE member_id = ?";
        $stmt = $_db->prepare($query);

        $updated_ids = [];

        foreach ($member_ids as $id) {
            $stmt->execute([$status, $id]);
            if ($stmt->rowCount() > 0) {
                $updated_ids[] = $id;
            }
        }

        if (!empty($updated_ids)) {
            temp('info', 'Successfully updated member status for IDs: ' . implode(', ', $updated_ids));
        } else {
            temp('info', 'No members were updated.');
        }
    } else {
        temp('info', 'No member ID(s) or status provided for update.');
    }

    redirect('../admin/member_management.php');
}
?>