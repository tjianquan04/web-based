<?php
include('_base.php');

//auth('Admin');

if (is_Post()) {
    $member_id = req('id');

    $stm = $_db->prepare('DELETE FROM member WHERE member_id = ?');
    $stm->execute([$member_id]);

    temp('info', 'Member ID: '.$member_id.' deleted successfully!');

    redirect('member_management.php');
}
?>