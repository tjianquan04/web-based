<?php
include('../_base.php');

//auth('Admin');

if (is_Post()) {
    $member_id = req('id');
    $member = getMemberbyId($member_id);
    $currentStatus = $member->status;

    if($currentStatus == 'Active'){
        $newStatus = 'Inactive';
    }else if($currentStatus == 'Inactive'){
        $newStatus = "Active";
    }
     

    $stmt = $_db->prepare("UPDATE member SET status = ? WHERE member_id = ?");
    $stmt->execute([$newStatus, $member_id]);

    temp('info', 'Member '.$member_id.' status has updated!');

    redirect('../admin/member_management.php');
}
?>