<?php
include('_base.php');

//auth('Admin');

if (is_Post()) {
    $member_id = req('id');

    $stm = $_db->prepare('DELETE FROM address WHERE member_id = ?');
    $stm->execute([$member_id]);

    temp('info', 'Address deleted successfully!');

    redirect('edit_member_details.php');
}
?>