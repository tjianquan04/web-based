<?php
include('_admin_head.php');
require_once '../lib/SimplePager.php';

// Authentication
auth('Admin', 'Superadmin', 'Product Manager');

// Define valid columns and directions for sorting
$valid_columns = ['order_id', 'order_date', 'total_amount', 'order_status'];
$valid_dirs = ['asc', 'desc'];

// Retrieve sort and direction from query parameters or use defaults
$sort = in_array(req('sort'), $valid_columns) ? req('sort') : 'order_id';
$dir = in_array(req('dir'), $valid_dirs) ? req('dir') : 'asc';

// Retrieve current page or set default to 1
$page = req('page', 1);

// Search logic
$search = req('search', ''); // Capture the search term
$search_query = '';
$params = []; // Query parameters for prepared statements

if (!empty($search)) {
    // Append the search condition with bind parameters
    $search_query = " AND (order_id LIKE :search OR member_id LIKE :search)";
    $params[':search'] = '%' . $search . '%'; // Add wildcard search term
}

// Initialize pagination with sorting, searching, and pagination
$p = new SimplePager(
    "SELECT * FROM order_record WHERE 1=1 $search_query ORDER BY $sort $dir",
    $params,
    10, // Items per page
    $page
);

// Fetch paginated results
$order_records = $p->result;

// Get total number of records
$total_records = $_db->query("SELECT COUNT(*) FROM order_record")->fetchColumn();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order</title>
    <link rel="stylesheet" href="/css/admin_management.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="admin-management-header">
            <h1>Order Records</h1>

            <!-- Search Bar -->
            <div class="search-bar-container">
                <form action="view_order.php" method="GET">
                    <input type="text" name="search" placeholder="Search by ID, Address, Member ID..." value="<?= htmlspecialchars($search) ?>" />
                    <!-- Retain sort and dir parameters during search -->
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                    <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
                    <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
                </form>
            </div>

            <!-- Total Record -->
            <span class="total-record">Total Orders: <?= $total_records ?></span>
        </div>

        <!-- Order Table -->
        <!-- Order Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th> <!-- Row Number -->
                    <th>
                        <a href="?sort=order_id&dir=<?= $sort === 'order_id' && $dir === 'asc' ? 'desc' : 'asc' ?>">
                            Order ID
                            <?php if ($sort == 'order_id'): ?>
                                <?php if ($dir == 'asc'): ?>
                                    <i class="fas fa-arrow-up arrow-right"></i>
                                <?php else: ?>
                                    <i class="fas fa-arrow-down arrow-right"></i>
                                <?php endif; ?>
                            <?php else: ?>
                                <i class="fas fa-sort arrow-right"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=order_date&dir=<?= $sort === 'order_date' && $dir === 'asc' ? 'desc' : 'asc' ?>">
                            Order Date
                            <?php if ($sort == 'order_date'): ?>
                                <?php if ($dir == 'asc'): ?>
                                    <i class="fas fa-arrow-up arrow-right"></i>
                                <?php else: ?>
                                    <i class="fas fa-arrow-down arrow-right"></i>
                                <?php endif; ?>
                            <?php else: ?>
                                <i class="fas fa-sort arrow-right"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=total_amount&dir=<?= $sort === 'total_amount' && $dir === 'asc' ? 'desc' : 'asc' ?>">
                            Total Amount
                            <?php if ($sort == 'total_amount'): ?>
                                <?php if ($dir == 'asc'): ?>
                                    <i class="fas fa-arrow-up arrow-right"></i>
                                <?php else: ?>
                                    <i class="fas fa-arrow-down arrow-right"></i>
                                <?php endif; ?>
                            <?php else: ?>
                                <i class="fas fa-sort arrow-right"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=order_status&dir=<?= $sort === 'order_status' && $dir === 'asc' ? 'desc' : 'asc' ?>">
                            Status
                            <?php if ($sort == 'order_status'): ?>
                                <?php if ($dir == 'asc'): ?>
                                    <i class="fas fa-arrow-up arrow-right"></i>
                                <?php else: ?>
                                    <i class="fas fa-arrow-down arrow-right"></i>
                                <?php endif; ?>
                            <?php else: ?>
                                <i class="fas fa-sort arrow-right"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($order_records): ?>
                    <?php $num = ($page - 1) * 10 + 1; // Start numbering from 1 
                    ?>
                    <?php foreach ($order_records as $order): ?>
                        <tr>
                            <td><?= $num++ ?></td> <!-- Row Number -->
                            <td><?= htmlspecialchars($order->order_id) ?></td>
                            <td><?= htmlspecialchars($order->order_date) ?></td>
                            <td><?= htmlspecialchars($order->total_amount) ?></td>
                            <td><?= htmlspecialchars($order->order_status) ?></td>
                            <td>
                                <a href="view_order_details.php?order_id=<?= urlencode($order->order_id) ?>" class='btn btn-view'><i class='fas fa-eye'></i>Order Details</a>
                                <a href="view_orderItems.php?order_id=<?= urlencode($order->order_id) ?>" class='btn btn-view'><i class='fas fa-box'></i>Order Items</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>


        <!-- Pagination -->
        <div class="pagination" style="margin-top :15px; justify-content: flex-end;">
            <?= generateDynamicPagination($p, $sort, $dir, $search); ?>
        </div>
    </div>

</body>

</html>