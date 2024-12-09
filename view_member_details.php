<?php
require '_base.php';

$memberId = req('id');

// Get member data from member table
$stm = $_db->prepare('SELECT * FROM member WHERE member_id = ?');
$stm->execute([$memberId]);
$s = $stm->fetch();
if (!$s) {
    redirect('/member_management.php');
}

// Get member address from address table
$addressStm = $_db->prepare('SELECT * FROM address WHERE member_id = ?');
$addressStm->execute([$memberId]);
$addressArr = $addressStm->fetchAll();

include '_head.php';
?>

<link rel="stylesheet" href="/css/member_details.css">

<body>
    <div class="profile-container">
        <h2>Member Details</h2>
        <div class="profile-details">
            <img src="<?= $s->profile_photo ?>" alt="Profile Photo">
            <div class="details">
                <h4>Member ID: <?= $s->member_id ?></h4>
                <p><strong>Name:</strong> <?= $s->name ?></p>
                <p><strong>Email:</strong> <?= $s->email ?></p>
                <p><strong>Contact:</strong> <?= $s->contact ?></p>
                <p><strong>Status:</strong> 
                    <span class="<?= $s->status == 1 ? 'status-active' : 'status-inactive' ?>">
                        <?= $s->status == 1 ? 'Active' : 'Inactive' ?>
                    </span>
                </p>
            </div>
        </div>
        <br>
        <div class="address-container">
            <h2>Member Addresses</h2>
            <?php foreach ($addressArr as $address): ?>
                <div class="address <?= $address->is_default ? 'default-address' : '' ?>">
                    <p><strong>Address:</strong> <?= $address->address_line ?></p>
                    <p><strong>State:</strong> <?= $address->state?></p>
                    <p><strong>Postal Code:</strong> <?= $address->postal_code ?></p>
                    <?php if ($address->is_default): ?>
                        <p style="color: green;">(Default Address)</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

<?php
include '_foot.php';
?>
