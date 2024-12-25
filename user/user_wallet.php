<?php
require '../_base.php';

$memberId = req('id');
$s = getMemberbyId($memberId);

$transactions = getTransactionHistory($memberId);

include '../_head.php';
?>
<script src="../js/main.js"></script>
<link rel="stylesheet" href="/css/user_wallet.css">

<body>
<div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>My Account</h2>
            <a href="user_profile.php?id=<?= $s->member_id?>" style="color: #ff5e3a;">Profile</a>
            <a href="user_address.php?id=<?= $s->member_id?>">Addresses</a>
            <a href="user_change_password.php?id=<?= $s->member_id?>">Change Password</a>
            <a href="user_wallet.php?id=<?= $s->member_id?>">My Wallet</a>
        </div>

        <!-- Profile Content -->
        <div class="content">
        <!-- Wallet Amount -->
        <div class="wallet-amount">
            <h3>Wallet Balance: RM <?= $s-> wallet ?></h3>
        </div>

        <!-- Transaction History -->
        <div class="transaction-history">
            <h2>Transaction History</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Date</th>
                        <th>Amount (RM)</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Reference ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transactions): ?>
                        <?php foreach (array_reverse($transactions) as $transaction): ?>
                            <tr>
                                <td><?= $transaction->trans_id ?></td>
                                <td><?= (new DateTime($transaction->trans_date))->format('d/m/Y H:i:s') ?></td>
                                <td> <?= $transaction->trans_type === 'Top Up'? '+':'-' ?> RM <?= $transaction->trans_amount ?></td>
                                <td><?= $transaction->trans_type ?></td>
                                <td><?= $transaction->trans_status ?></td>
                                <td><?=$transaction->order_id ? $transaction->order_id:  $transaction->top_up_id?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</body>

<?php
include '../_foot.php';
