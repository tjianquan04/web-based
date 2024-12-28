<?php
require '../_base.php';

auth('Admin', 'Superadmin', 'Product Manager');

$lowStockCount = countLowStockProducts();
$outOfStockCount = countOutOfStockProducts();

// Get admin role
$admin_role = $_SESSION['role'] ?? NULL;
updateSessionData($_SESSION['user']->admin_id);

$total_sales = getTotalSales();
$total_orders = getTotalOrders();
$total_members = getTotalMembers();

if (isset($_GET['chartData'])) {
    $metric = $_GET['metric'] ?? 'users';

    $data = match ($metric) {
        'orders' => getOrdersGroupedByYear($_GET['year']),
        'sales' => getSalesGroupedByYear($_GET['year']),
        'users' => getUsersGroupedByYear($_GET['year']),
        'product_categories' => getProductSalesByCategory($_GET['year']), // New data source for pie chart
        default => [],
    };

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/css/admin_dashboard.css">
    <link rel="stylesheet" href="/css/flash_msg.css">
    <link rel="stylesheet" href="/css/admin_head.css">
    <script src="/js/admin_head.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Admin Dashboard</title>
</head>
<!-- Header Side -->
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
                <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>

                <!-- Only Product Manager can see this section -->
                <?php if ($admin_role === 'Product Manager' || $admin_role === 'Superadmin'): ?>
                    <a href="product_index.php"><i class="fas fa-tachometer-alt"></i> Product Management</a>
                    <a href="viewCategory.php"><i class="fas fa-tachometer-alt"></i> Category Management</a>
                <?php endif; ?>

                <a href="javascript:void(0)" onclick="toggleMenu('order-menu')"><i class="fas fa-box"></i> Order Management</a>
                <ul id="order-menu" class="submenu">
                    <li><a href="view_order.php">View Orders</a></li>
                </ul>

                <a href="javascript:void(0)" onclick="toggleMenu('user-menu')"><i class="fas fa-users"></i> User Management</a>
                <ul id="user-menu" class="submenu">
                    <li><a href="member_management.php">Manage Users</a></li>
                </ul>

                <?php if ($admin_role === 'Superadmin'): ?>
                    <a href="javascript:void(0)" onclick="toggleMenu('admin-menu')"><i class="fas fa-users-cog"></i> Admin Management</a>
                    <ul id="admin-menu" class="submenu">
                        <li><a href="admin_management.php">Manage Admins</a></li>
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
            <header class="header">
                <h2>Welcome, <?= htmlspecialchars($_SESSION['user']->admin_name) ?></h2>
                <div class="header-right">
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
            </header>
        </main>
    </div>
</body>

<!-- Dashboard Side -->

<body>
    <div class="container">
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="icon"><i class="fas fa-users"></i></div>
                <div class="text">
                    <h3>Total Users</h3>
                    <p class="stat-value"><?= $total_members ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="text">
                    <h3>Total Sales</h3>
                    <p class="stat-value">MYR <?= number_format($total_sales, 2) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="text">
                    <h3>Total Orders</h3>
                    <p class="stat-value"><?= $total_orders ?></p>
                </div>
            </div>
        </div>

        <h2>Line Chart</h2>
        <div class="chart-controls">
            <div class="toggle-metrics">
                <button class="metric-btn active" data-metric="users">Users</button>
                <button class="metric-btn" data-metric="orders">Orders</button>
                <button class="metric-btn" data-metric="sales">Sales</button>
            </div>
            <select id="yearDropdown">
                <option value="2024" selected>2024</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
                <option value="2021">2021</option>
            </select>
        </div>
        <div class="chart-container">
            <canvas id="lineChart"></canvas>
        </div>
        <!-- Pie Chart Section -->
        <h2>Category Sales Distribution</h2>
        <div class="chart-controls">
            <select id="yearPieDropdown">
                <option value="2024" selected>2024</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
                <option value="2021">2021</option>
            </select>
        </div>

        <div class="chart-container">
            <canvas id="pieChart"></canvas>
        </div>
    </div>

    <script>
        const ctxLine = document.getElementById('lineChart').getContext('2d');
        const ctxPie = document.getElementById('pieChart').getContext('2d');

        const lineChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Users',
                    data: [],
                    borderColor: '#4bc0c0',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    },
                    tooltip: {
                        enabled: true
                    },
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Months'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Amount'
                        },
                        beginAtZero: true
                    },
                },
            },
        });

        const pieChart = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    },
                    tooltip: {
                        enabled: true
                    },
                },
            },
        });


        const fetchChartData = (metric, year) => {
            const url = `/admin/admin_dashboard.php?chartData=1&metric=${metric}&year=${year}`;
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const values = Array(12).fill(0);
                    data.forEach(item => {
                        const monthIndex = parseInt(item.month, 10) - 1;
                        if (monthIndex >= 0 && monthIndex < 12) {
                            values[monthIndex] = parseFloat(item.total || 0);
                        }
                    });
                    lineChart.data.datasets[0].data = values;
                    lineChart.data.datasets[0].label = metric.charAt(0).toUpperCase() + metric.slice(1);
                    lineChart.update();
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                    alert('Failed to load chart data. Please try again.');
                });
        };

        function fetchPieChartData(year) {
            fetch(`/admin/admin_dashboard.php?chartData=1&metric=product_categories&year=${year}`)
                .then(response => response.json())
                .then(data => {
                    const labels = data.map(item => item.category_name);
                    const values = data.map(item => item.total_sold);
                    pieChart.data.labels = labels;
                    pieChart.data.datasets[0].data = values;
                    pieChart.update();
                })
                .catch(error => {
                    console.error('Error fetching pie chart data:', error);
                    alert('Failed to load chart data. Please try again.');
                });
        }

        document.getElementById('yearPieDropdown').addEventListener('change', (event) => {
            const year = event.target.value;
            fetchPieChartData(year);
        });


        document.querySelectorAll('.metric-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.metric-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                const metric = button.dataset.metric;
                const year = document.getElementById('yearDropdown').value;
                fetchChartData(metric, year);
            });
        });

        document.getElementById('yearDropdown').addEventListener('change', (event) => {
            const year = event.target.value;
            const metric = document.querySelector('.metric-btn.active').dataset.metric;
            fetchChartData(metric, year);
        });

        // Ensure pie chart is fetched separately
        window.onload = () => {
            fetchChartData('users', '2024'); // Default to 'users' metric
            fetchPieChartData('2024'); // Pie chart data
        };

        document.addEventListener('DOMContentLoaded', () => {

            const currentDateElement = document.getElementById('currentDate');
            const options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                weekday: 'long'
            };
            const currentDate = new Date().toLocaleDateString('en-US', options);
            currentDateElement.textContent = currentDate;

            //Dropdown interaction
            const userProfile = document.getElementById('userProfile');
            userProfile.addEventListener('click', (e) => {
                e.stopPropagation();
                userProfile.classList.toggle('active');
            });

            document.addEventListener('click', () => {
                userProfile.classList.remove('active');
            });
        });
    </script>
</body>

</html>