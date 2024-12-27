<?php
include('_admin_head.php');

// Authentication check for Admin/Superadmin
auth('Admin', 'Superadmin', 'Product Manager');

// Get the order_id from the query string
$order_id = req('order_id'); // Get the ID of the order to view

// Retrieve order details
$order = getOrderById($order_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="/css/admin_management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            width: 30%; /* 30% width for th (field names) */
            background-color:rgb(255, 255, 255);
            color: rgb(0, 0, 0);
        }

        td {
            width: 70%; /* 70% width for td (data) */
        }
    </style>
</head>

<body>
    <div class="container">
    <button class="back-button" onclick="history.back()">&larr;</button>
        <h1>Order Details</h1>

        <!-- Check if order exists -->
        <?php if ($order): ?>
            <div class="order-info">
                <table>
                    <tr>
                        <th><i class="fas fa-id-card"></i> Order ID</th>
                        <td><?= htmlspecialchars($order->order_id) ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-box"></i> Product Total</th>
                        <td><?= htmlspecialchars($order->product_total) ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-truck"></i> Shipping Fee</th>
                        <td><?= htmlspecialchars($order->shipping_fee) ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-tags"></i> Discount</th>
                        <td><?= htmlspecialchars($order->discount) ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-dollar-sign"></i> Total Amount</th>
                        <td><?= htmlspecialchars($order->total_amount) ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-check-circle"></i> Order Status</th>
                        <td><?= htmlspecialchars($order->order_status) ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-calendar-alt"></i> Order Date</th>
                        <td><?= htmlspecialchars($order->order_date) ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-map-marker-alt"></i> Address</th>
                        <td><?= htmlspecialchars($order->address_line) ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-envelope"></i> Postal Code</th>
                        <td><?= htmlspecialchars($order->postal_code) ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-flag"></i> State</th>
                        <td><?= htmlspecialchars($order->state) ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-user"></i> Member ID</th>
                        <td><?= htmlspecialchars($order->member_id) ?></td>
                    </tr>
                </table>
            </div>
        <?php else: ?>
            <p>No order found with the provided ID.</p>
        <?php endif; ?>
    </div>
</body>

</html>
