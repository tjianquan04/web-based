<link rel="stylesheet" href="/css/admin_head.css"> <!-- Example stylesheet -->
<link rel="stylesheet" href="/css/flash_msg.css"> <!-- Additional styles -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
<script src="/js/admin_head.js"></script> <!-- Custom script -->
<?php
session_start(); // Start session before any output
require '../_base.php';

auth('Admin', 'Superadmin', 'Product Manager');

// Get admin role
$admin_role = $_SESSION['role'] ?? NULL;
updateSessionData($_SESSION['user']->admin_id);
$lowStockCount = countLowStockProducts();
$outOfStockCount = countOutOfStockProducts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
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
                <h2 style="color: azure;">Admin Panel</h2>
            </div>
            <nav class="menu">
                <!-- Top-Level Menu Items -->
                <a href="/admin/admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>

                <!-- Only Product Manager can see this section -->
                <?php if ($admin_role === 'Product Manager' || $admin_role === 'Superadmin'): ?>
                    <?php if ($admin_role === 'Product Manager' || $admin_role === 'Superadmin'): ?>
                    <a href="/admin/product_index.php"><i class="fas fa-tachometer-alt"></i> Product Management</a>
                    <a href="/admin/viewCategory.php"><i class="fas fa-tachometer-alt"></i> Category Management</a>
                <?php endif; ?>
                <?php endif; ?>

                <a href="javascript:void(0)" onclick="toggleMenu('order-menu')"><i class="fas fa-box"></i> Order Management</a>
                <ul id="order-menu" class="submenu">
                    <li><a href="/admin/view_order.php">View Orders</a></li>
                </ul>

                <a href="javascript:void(0)" onclick="toggleMenu('user-menu')"><i class="fas fa-users"></i> User Management</a>
                <ul id="user-menu" class="submenu">
                    <li><a href="/admin/member_management.php">Manage Users</a></li>
                </ul>

                <?php if ($admin_role === 'Superadmin'): ?>
                    <a href="javascript:void(0)" onclick="toggleMenu('admin-menu')"><i class="fas fa-users-cog"></i> Admin Management</a>
                    <ul id="admin-menu" class="submenu">
                        <li><a href="/admin/admin_management.php">Manage Admins</a></li>
                    </ul>
                <?php endif; ?>

                <!-- Logout Form -->
                <form action="" method="POST" style="display:inline;">
                    <button type="submit" name="logout" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </nav>

        </aside>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
            // Call the logout function to handle session and redirection
            logout('admin_login.php');
        }
        ?>

        <main class="header-content">
            <div class="header">
                <h2>Welcome, <?= htmlspecialchars($_SESSION['user']->admin_name) ?></h2>
                <div class="header-right">
                    <!-- Notification Icon -->
                    <a href="/admin/admin_notification.php" style="position: relative; text-decoration: none;">
                        <i class="fas fa-bell" id="notificationIcon"></i>
                        <?php if ($lowStockCount > 0 || $outOfStockCount > 0): ?>
                            <span id="notificationCount">
                                (<?= $lowStockCount + $outOfStockCount ?>)
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- User Profile Section -->
                    <div class="user-profile" id="userProfile" tabindex="0">
                        <!-- Check if there's a user photo, otherwise display the default photo -->
                        <img src="<?= isset($_SESSION['user']->photo) && $_SESSION['user']->photo ? '../image/' . $_SESSION['user']->photo : '../image/default_user_photo.png' ?>"
                            alt="User Photo" class="user-icon">
                        <div class="user-details">
                            <p class="admin-name"><?= htmlspecialchars($_SESSION['user']->role) ?></p>
                            <p class="current-date" id="currentDate"></p>
                        </div>
                        <div class="dropdown-content" id="profileDropdown">
                            <a href="view_admin.php?id=<?= $_SESSION['user']->admin_id ?>">View Profile</a>
                            <a href="edit_admin.php?id=<?= $_SESSION['user']->admin_id ?>">Edit Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>