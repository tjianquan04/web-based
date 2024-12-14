<?php
include('_base.php');

//auth('Admin');

if (is_Post()) {
    $member_id = req('id');
    $member = getMemberbyId($member_id);
    $currentStatus = $member->status;

    $newStatus = $currentStatus ? 0 : 1;

    $stmt = $_db->prepare("UPDATE member SET status = ? WHERE member_id = ?");
    $stmt->execute([$newStatus, $member_id]);

    temp('info', 'Member '.$member_id.' status has updated!');

    redirect('member_management.php');
}
?>