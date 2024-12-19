<?php
include('../_base.php');

//auth('Admin');

if (is_Post()) {
    $address_id = req('id');
    $address = getAddressbyId($address_id);
    $memberId = $address->member_id;

    $stm = $_db->prepare('DELETE FROM address WHERE address_id = ?');
    $stm->execute([$address_id]);

    temp('info', 'Address deleted successfully!');

    redirect('/edit_member_details.php?id='.$memberId);
}
?>