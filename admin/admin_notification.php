<?php
include('_admin_head.php');

auth('Admin', 'Superadmin', 'Product Manager');

$lowStockProducts = getLowStockProducts(); // Fetch low stock details
$outOfStockProducts = getOutOfStockProducts(); // Fetch out-of-stock details
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/admin_notification.css">
    <title>Admin Notification</title>
</head>
<body>
<div class="container">
    <h2>Admin Notifications</h2>
    <?php if (empty($lowStockProducts) && empty($outOfStockProducts)): ?>
        <p>No notifications at this time.</p>
    <?php else: ?>
        <?php if (!empty($outOfStockProducts)): ?>
            <div class="alert alert-danger">
                <strong>Out of Stock!</strong>
                <ul>
                    <?php foreach ($outOfStockProducts as $product): ?>
                        <li><?= htmlspecialchars($product['product_id'] ?? 'Unknown') ?> - <?= htmlspecialchars($product['description'] ?? 'Unknown') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($lowStockProducts)): ?>
            <div class="alert alert-warning">
                <strong>Low Stock!</strong>
                <ul>
                    <?php foreach ($lowStockProducts as $product): ?>
                        <li>
                            <?= htmlspecialchars($product['product_id'] ?? 'Unknown') ?> - 
                            <?= htmlspecialchars($product['description'] ?? 'Unknown') ?> 
                            (Stock: <?= htmlspecialchars($product['stock_quantity'] ?? 'N/A') ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
    
</body>
</html>


