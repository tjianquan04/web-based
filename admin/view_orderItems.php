<?php
include('_admin_head.php');

// Authentication check for Admin/Superadmin
auth('Admin', 'Superadmin', 'Product Manager');

// Get the order_id from the query string
$order_id = req('order_id'); // Get the ID of the order to view

// Retrieve order items for the given order_id
$order_items = getOrderItemsByOrderId($order_id);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Items</title>
    <link rel="stylesheet" href="/css/admin_management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .order-box {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
        }

        .order-box h2 {
            margin: 0 0 10px;
            font-size: 1.5em;
        }

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
            color: rgb(0, 0, 0);
            background-color:rgb(255, 255, 255);
        }
    </style>
</head>

<body>
    <div class="container">
    <button class="back-button" onclick="history.back()">&larr;</button>
        <h2>Order Items for Order ID: <?= htmlspecialchars($order_id) ?></h2>

        <!-- Check if order items exist -->
        <?php if ($order_items && count($order_items) > 0): ?>
            <?php foreach ($order_items as $item): ?>
                <div class="order-box">
                    <h3>Order <?= htmlspecialchars($item->orderItem_id) ?></h3>
                    <table>
                        <tr>
                            <th><i class="fas fa-cube"></i> Quantity</th>
                            <td><?= htmlspecialchars($item->quantity) ?></td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-dollar-sign"></i> Total Price</th>
                            <td><?= htmlspecialchars($item->total_price) ?></td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-box-open"></i> Product ID</th>
                            <td><?= htmlspecialchars($item->product_id) ?></td>
                        </tr>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No items found for this order ID.</p>
        <?php endif; ?>
    </div>
</body>

</html>
