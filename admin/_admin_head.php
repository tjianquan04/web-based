<?php
require '../_base.php';
// Check if admin is logged in
//if (!isset($_SESSION['admin'])) {
//    header('Location: admin.php');
//    exit();
//}

// Get admin role
$admin_role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/css/flash_msg.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/css/admin_head.css"> <!-- Link to the external CSS -->
</head>
<div id="info"><?= temp('info') ?></div>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Menu -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav class="menu">
    <!-- Top-Level Menu Items -->
    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>

    <a href="javascript:void(0)" onclick="toggleMenu('product-menu')"><i class="fas fa-cogs"></i> Product Management</a>
    <ul id="product-menu" class="submenu">
        <li><a href="#">Add Product</a></li>
        <li><a href="#">Manage Inventory</a></li>
    </ul>

    <a href="javascript:void(0)" onclick="toggleMenu('order-menu')"><i class="fas fa-box"></i> Order Management</a>
    <ul id="order-menu" class="submenu">
        <li><a href="#">View Orders</a></li>
    </ul>

    <a href="javascript:void(0)" onclick="toggleMenu('user-menu')"><i class="fas fa-users"></i> User Management</a>
    <ul id="user-menu" class="submenu">
        <li><a href="#">Manage Users</a></li>
    </ul>

    <?php if ($admin_role === 'Superadmin'): ?>
        <a href="javascript:void(0)" onclick="toggleMenu('admin-menu')"><i class="fas fa-users-cog"></i> Admin Management</a>
        <ul id="admin-menu" class="submenu">
            <li><a href="admin_management.php">Manage Admins</a></li>
        </ul>
    <?php endif; ?>

    <a href="#"><i class="fas fa-cogs"></i> Settings</a>
    <a href="admin_login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>

        </aside>

        <!-- Main Content -->
        <main class="header-content">
            <header class="header">
                <h2>Welcome, <?= htmlspecialchars($_SESSION['admin']) ?>!</h1>
                <div class="header-right">
                    <i class="fas fa-bell"></i>
                    <i class="fas fa-user-circle"></i>
                </div>
            </header>
        </main>
    </div>
    <script src="/js/main.js"></script>