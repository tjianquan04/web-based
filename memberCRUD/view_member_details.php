<?php

include '../admin/_admin_head.php';

$memberId = req('id');

// Get member data from member table
$stm = $_db->prepare('SELECT * FROM member WHERE member_id = ?');
$stm->execute([$memberId]);
$s = $stm->fetch();
if (!$s) {
    redirect('../admin/member_management.php');
}

// Get member address from address table
$addressStm = $_db->prepare('SELECT * FROM address WHERE member_id = ?');
$addressStm->execute([$memberId]);
$addressArr = $addressStm->fetchAll();


?>

<link rel="stylesheet" href="../css/view_member.css">

<body>
    <div class="container">
        <h2>Member Profile</h2>
        <div class="profile-details">
            <div class="member-photo">
            <img src="<?= $s->profile_photo ? '../photos/' . $s->profile_photo : '../photos/unknown.jpg' ?>" alt="Profile Photo">
            </div>
            <div class="member-info">
                <table>
                    <tr>
                    <td class="label"><i class="fas fa-id-card"></i>Member ID</td>
                    <td class="value"><?= $s->member_id ?></td>
                    </tr>
                    <tr>
                    <td class="label"><i class="fas fa-user"></i>Name</td>
                    <td class="value"><?= $s->name ?></td>
                    </tr>
                    <tr>
                    <td class="label"><i class="fas fa-envelope"></i>Email</td>
                    <td class="value"><?= $s->email ?></td>
                    </tr>
                    <tr>
                    <td class="label"><i class="fas fa-phone"></i>Contact</td>
                    <td class="value"><?= $s->contact ?></td>
                    </tr>
                    <tr>
                    <td class="label"><i class="fa fa-credit-card"></i>Wallet</td>
                    <td class="value">RM <?= $s->wallet ?></td>
                    </tr>
                    <tr>
                    <td class="label"><i class="fa fa-calendar"></i>Registered Date</td>
                    <td class="value"><?= $s->register_date ?></td>
                    </tr>
                    <tr>
                    <td class="label"><i class="fas fa-check-circle"></i>Status</td>
                    <td class="value"><?= $s->status ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <br>
        <h2>Member Addresses</h2>
            <?php foreach ($addressArr as $address): ?>
                <div class="address <?= $address->is_default ? 'default-address' : '' ?>">
                    <p><strong>Address ID:</strong> <?= $address->address_id ?></p>
                    <p><strong>Address:</strong> <?= $address->address_line . ', ' . $address->postal_code . ', ' . $address->state ?></p>
                    <?php if ($address->is_default): ?>
                        <p style="color: green;">(Default Address)</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

    <button class="go-back" onclick="history.back()">Go Back</button>
    </div>
</body>

