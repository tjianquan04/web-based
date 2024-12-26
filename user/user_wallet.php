<?php
require '../_base.php';

$member = $_SESSION['user'];
authMember($member);

$transactions = getTransactionHistory($member->member_id);

$transactionTypes = [
    '' => 'All Types',
    'Top Up' => 'Top Up',
    'Purchase' => 'Purchase'
];

$transactionMonths = [
    '' => 'All Months',
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December'
];

$transactionYears = [
    '' => 'All Years',
    '2023' => '2023',
    '2024' => '2024',
    '2025' => '2025'
];

include '../_head.php';
?>
<script src="../js/main.js"></script>
<link rel="stylesheet" href="/css/user_wallet.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>My Account</h2>
            <a href="user_profile.php">Profile</a>
            <a href="user_address.php">Addresses</a>
            <a href="user_change_password.php">Change Password</a>
            <a href="user_wallet.php">My Wallet</a>
        </div>

        <!-- Profile Content -->
        <div class="content">
            <!-- Wallet Amount -->
            <div class="wallet-amount">
                <h3>Wallet Balance: RM <?= $member->wallet ?></h3>
            </div>

            <!-- Transaction History -->
            <div class="transaction-history">
                <h2>Transaction History</h2>

                <?= filter_select('transactionType', 'transaction_type', $transactionTypes, '', 'class="filter""') ?>

                <?= filter_select('transactionMonth', 'transaction_month', $transactionMonths, '','class="filter"'); ?>

                <?= filter_select('transactionYear', 'transaction_year', $transactionYears,'','class="filter"'); ?>

                <table class="transaction-table" id="transactionTable">
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
                                    <td> <?= $transaction->trans_type === 'Top Up' ? '+' : '-' ?> RM <?= $transaction->trans_amount ?></td>
                                    <td><?= $transaction->trans_type ?></td>
                                    <td><?= $transaction->trans_status ?></td>
                                    <td><?= $transaction->order_id ? $transaction->order_id :  $transaction->top_up_id ?></td>
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

<script>
    $(document).ready(function() {
        function filterTransactions() {
            const type = $('#transactionType').val();
            const month = $('#transactionMonth').val();
            const year = $('#transactionYear').val();

            $.ajax({
                url: 'filter_transactions.php', // Backend URL
                method: 'POST',
                data: {
                    type: type,
                    month: month,
                    year: year
                },
                dataType: 'json',
                success: function(data) {
                    const tbody = $('#transactionTable tbody');
                    tbody.empty(); // Clear existing table rows

                    if (data.length > 0) {

                        data.reverse(); //display latest

                        // Append rows for each transaction
                        data.forEach(transaction => {
                            tbody.append(`
                            <tr>
                                <td>${transaction.trans_id}</td>
                                <td>${new Date(transaction.trans_date).toLocaleDateString('en-GB')}</td>
                                <td>RM ${transaction.trans_amount}</td>
                                <td>${transaction.trans_type}</td>
                                <td>${transaction.trans_status}</td>
                                <td>${transaction.order_id || transaction.top_up_id}</td>
                            </tr>
                        `);
                        });
                    } else {
                        tbody.append('<tr><td colspan="6">No transactions found.</td></tr>');
                    }
                },
                error: function() {
                    alert('Error loading transactions. Please try again.');
                }
            });
        }

        // Trigger filtering on dropdown change
        $('.filter').change(filterTransactions);
    });
</script>
<?php
include '../_foot.php';
