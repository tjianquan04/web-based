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

if (is_post()) {

    $currentDateTime = date('Y-m-d H:i:s');

    if (isset($_POST['topUpAmount']) && !empty($_POST['topUpAmount'])) {
        $topUpAmount = $_POST['topUpAmount'];

        // Continue processing
        $top_up_id = generateTopUpId();
        $trans_id = generateTransactionId();

        $stmt = $_db->prepare('INSERT INTO transactions (trans_id, trans_date, trans_amount, trans_type, trans_status, reference, member_id) VALUES (?, ?, ?, ?, ? , ?, ?)');
        $stmt->execute([$trans_id, $currentDateTime, $topUpAmount, "Top Up", "Completed", $top_up_id, $member->member_id]);

        $updatedBalance = getWalletBalanceAfterTransaction($trans_id, $member->member_id);
        if ($updatedBalance != null) {
            updateWalletBalance($updatedBalance, $member->member_id);
            temp('info', 'Top Up successful');
            $updatedMember = getMemberbyId($member->member_id);
            $_SESSION['user'] = $updatedMember;
            $member =  $_SESSION['user'];
            $transactions = getTransactionHistory($member->member_id);
        }
    } else {
        temp('info', 'Top-Up amount is required.');
    }
}

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
            <div class="wallet-section">
            <!-- Wallet Amount -->
            <div class="wallet-amount">
                <h3>Wallet Balance: RM <?= $member->wallet ?></h3>
            </div>
            <div class="reload-wallet">
                <button id="topUpButton" onclick="openModal('amountForm')">Top Up</button>
            </div>
            </div>

            <!-- Transaction History -->
            <div class="transaction-history">
                <h2>Transaction History</h2>

                <?= filter_select('transactionType', 'transaction_type', $transactionTypes, '', 'class="filter""') ?>

                <?= filter_select('transactionMonth', 'transaction_month', $transactionMonths, '', 'class="filter"'); ?>

                <?= filter_select('transactionYear', 'transaction_year', $transactionYears, '', 'class="filter"'); ?>

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
                        <?php usort($transactions, function ($a, $b) {
                            return strtotime($b->trans_date) - strtotime($a->trans_date);
                        }); ?>
                        <?php if ($transactions): ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?= $transaction->trans_id ?></td>
                                    <td><?= (new DateTime($transaction->trans_date))->format('d/m/Y H:i:s') ?></td>
                                    <td> <?= $transaction->trans_type === 'Top Up' ? '+' : '-' ?> RM <?= $transaction->trans_amount ?></td>
                                    <td><?= $transaction->trans_type ?></td>
                                    <td><?= $transaction->trans_status ?></td>
                                    <td><?= $transaction->reference ?></td>
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

<div id="amountForm" class="modal">
    <div class="modal-content">
        <button class="close-btn" onclick="closeModal('amountForm')">&times;</button>
        <h2>Top-Up Amount</h2>
        <form>
            <label for="amount">Amount : RM </label>
            <input type="number" id="amount" name="topUpAmount" placeholder="Enter Amount" class="amount-input" step="0.01" min="0" required />
            <div class="predefined-amounts">
                <button type="button" class="amount-button" data-amount="50">RM 50</button>
                <button type="button" class="amount-button" data-amount="100">RM 100</button>
                <button type="button" class="amount-button" data-amount="200">RM 200</button>
                <button type="button" class="amount-button" data-amount="500">RM 500</button>
            </div>
            <button type="button" class="continue-btn" onclick="showCardForm()">Continue</button>
        </form>
    </div>
</div>

<!-- Card Form Modal -->
<div id="cardForm" class="modal">
    <div id="cardDetailsModal">
        <button class="close-btn" onclick="closeModal('cardForm')">&times;</button>
        <table class="checkout-cardpopup-table">
            <tr>
                <td class="checkout-cardpopup-title" colspan="3"><b>Pay by Credit/Debit Card</b></td>
            </tr>
            <form method="post">
                <input type="hidden" id="topUpAmountHidden" name="topUpAmount" />
                <tr>
                    <td colspan="3" class="checkout-cardpopup-cardnumber">
                        <label for="cardNumber" class="checkout-cardpopup-infolabel">Card Number</label><br>
                        <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 1234 1234 1234" required autocomplete="off">
                    </td>
                </tr>
                <tr>
                    <td class="checkout-cardpopup-expiry">
                        <label for="cardExpiry" class="checkout-cardpopup-infolabel">Expiry</label>
                        <input type="text" id="cardExpiry" name="cardExpiry" placeholder="MM/YY" required autocomplete="off">
                    </td>
                    <td class="checkout-cardpopup-empty"></td>
                    <td class="checkout-cardpopup-cvv">
                        <label for="cardCVV" class="checkout-cardpopup-infolabel">CVV</label>
                        <input type="text" id="cardCVV" name="cardCVV" placeholder="CVV" required autocomplete="off">
                    </td>
                </tr>
                <tr>
                    <td class="checkout-cardpopup-country">
                        <label for="cardCountry" class="checkout-cardpopup-infolabel">Country</label>
                        <input type="text" id="cardCountry" name="cardCountry" placeholder="Country" list="country" required autocomplete="off">
                        <datalist id="country">
                            <option value="Malaysia">
                            <option value="United States">
                            <option value="China">
                            <option value="India">
                            <option value="Canada">
                            <option value="Australia">
                            <option value="United Kingdom">
                            <option value="Germany">
                            <option value="France">
                            <option value="Japan">
                            <option value="South Korea">
                            <option value="Singapore">
                            <option value="Italy">
                            <option value="Spain">
                            <option value="Brazil">
                            <option value="South Africa">
                            <option value="Mexico">
                            <option value="Russia">
                            <option value="Netherlands">
                            <option value="Switzerland">
                        </datalist>
                    </td>
                    <td class="checkout-cardpopup-empty"></td>
                    <td class="checkout-cardpopup-zipcode">
                        <label for="cardZipCode" class="checkout-cardpopup-infolabel">Zip</label>
                        <input type="text" id="cardZipCode" name="cardZipCode" placeholder="12345" required autocomplete="off">
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="checkout-cardpopup-pay">
                        <button type="button" id="checkout-cardpopup-pay-btn" class="checkout-cardpopup-pay-btn">Pay Now</button>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="checkout-cardpopup-cancel">
                        <button type="button" class="checkout-cardpopup-cancel-btn" onclick="goBackToAmount()">Back</button>
                    </td>
                </tr>
            </form>
        </table>
    </div>
</div>

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
                                <td>${transaction.reference}</td>
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

    function openModal(modalId) {
        document.getElementById(modalId).style.display = "flex";
    }

    // Close modal
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
    }

    // Show Card Form directly from Amount Form
    function showCardForm() {
        const amount = document.getElementById('amount').value;

        if (amount) {
            document.getElementById('topUpAmountHidden').value = amount;
            closeModal('amountForm');
            openModal('cardForm');
        } else {
            alert('Please enter a valid amount.');
        }
    }

    // Return back to Amount Form
    function goBackToAmount() {
        closeModal('cardForm');
        openModal('amountForm');
    }

    // Validate and Submit Card Form
    $(document).on('click', '#checkout-cardpopup-pay-btn', function(event) {
        event.preventDefault(); // Prevent default form submission

        const cardNumber = $("#cardNumber").val().trim();
        const cardExpiry = $("#cardExpiry").val().trim();
        const cardCVV = $("#cardCVV").val().trim();
        const cardCountry = $("#cardCountry").val().trim();
        const cardZipCode = $("#cardZipCode").val().trim();

        // Validate all fields are filled
        if (!cardNumber || !cardExpiry || !cardCVV || !cardCountry || !cardZipCode) {
            alert("Please fill in all fields.");
            return;
        }

        // Validate Card Number
        const cardNumberRegex = /^\d{4} \d{4} \d{4} \d{4}$/;
        if (!cardNumberRegex.test(cardNumber)) {
            alert('Invalid card number format. Use "xxxx xxxx xxxx xxxx".');
            return;
        }

        // Validate Expiry Date Format
        const expiryDateRegex = /^(0[1-9]|1[0-2])\/\d{2}$/;
        if (!expiryDateRegex.test(cardExpiry)) {
            alert('Invalid expiry date format. Use "MM/YY".');
            return;
        }

        // Validate Expiry Date is in the Future
        const [month, year] = cardExpiry.split('/');
        const currentDate = new Date();
        const expiry = new Date(`20${year}`, month - 1); // Convert MM/YY to full date
        if (expiry <= currentDate) {
            alert('Expiry date must be in the future.');
            return;
        }

        // Validate CVV
        const cvvRegex = /^\d{3}$/;
        if (!cvvRegex.test(cardCVV)) {
            alert('Invalid CVV. Must be exactly 3 digits.');
            return;
        }

        // Validate ZIP Code
        const zipRegex = /^\d{5}$/;
        if (!zipRegex.test(cardZipCode)) {
            alert('Invalid ZIP Code. Must be exactly 5 digits.');
            return;
        }

        // If all validations pass, proceed to submit the form
        alert('Card details validated. Processing payment...');
        $('form').submit();
    });

    document.querySelectorAll('.amount-button').forEach(button => {
        button.addEventListener('click', (event) => {
            const amount = event.target.getAttribute('data-amount');
            document.getElementById('amount').value = amount;
            document.getElementById('topUpAmountHidden').value = amount;
        });
    });
</script>
<?php
include '../_foot.php';
