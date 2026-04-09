<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

include "db.php";

$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Admin';

$today = date('Y-m-d');
$today_earnings = 0;
$orders_today = 0;
$pending_count = 0;
$mock_orders = [];

$r = mysqli_query($conn, "SELECT SUM(total) as t, COUNT(*) as c FROM orders WHERE DATE(created_at) = '$today' AND status = 'completed'");
if ($r && $row = mysqli_fetch_assoc($r)) {
    $today_earnings = (float)($row['t'] ?? 0);
    $orders_today = (int)($row['c'] ?? 0);
}

$r = mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status = 'pending'");
if ($r && $row = mysqli_fetch_assoc($r)) $pending_count = (int)($row['c'] ?? 0);

$r = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
if ($r) while ($row = mysqli_fetch_assoc($r)) $mock_orders[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Debug Café</title>
    <link rel="stylesheet" href="../style/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../images/logo.png" alt="Debug Café" class="sidebar-logo">
            <h1 class="sidebar-brand">Debug Café</h1>
            <p class="sidebar-tagline">Coffee & Code</p>
        </div>
        <nav class="nav-links">
            <a href="admin.php" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="neworder.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i>
                <span>New Order</span>
            </a>
            <a href="orders.php" class="nav-item">
                <i class="fas fa-list"></i>
                <span>Orders</span>
            </a>
            <a href="transactions.php" class="nav-item">
                <i class="fas fa-exchange-alt"></i>
                <span>Transactions</span>
            </a>
            <a href="products.php" class="nav-item">
                <i class="fas fa-box"></i>
                <span>Manage Products</span>
            </a>
            <a href="users.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Manage Users</span>
            </a>
        </nav>
        <a href="logout.php" class="nav-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <h2 class="dashboard-title">Admin Dashboard</h2>
            <div class="user-greeting">
                <span>Welcome, <?php echo $fullname; ?></span>
                <div class="user-avatar"></div>
            </div>
        </header>

        <!-- Summary Cards -->
        <section class="summary-cards">
            <div class="summary-card summary-card-gold">
                <i class="fas fa-dollar-sign"></i>
                <p class="summary-value">₱<?php echo number_format((float)$today_earnings, 2); ?></p>
                <p class="summary-label">TODAY'S EARNINGS</p>
            </div>
            <div class="summary-card">
                <i class="fas fa-list"></i>
                <p class="summary-value"><?php echo (int)$orders_today; ?> Orders</p>
                <p class="summary-label">ORDERS TODAY</p>
            </div>
            <a href="orders.php?filter=pending" class="summary-card summary-card-badge" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="icon-wrapper">
                    <i class="fas fa-bell"></i>
                    <span class="badge"><?php echo (int)$pending_count; ?></span>
                </div>
                <p class="summary-value"><?php echo (int)$pending_count; ?> Pending</p>
                <p class="summary-label">PENDING ORDERS</p>
            </a>
        </section>

        <!-- Action Tiles -->
        <section class="action-tiles">
            <a href="neworder.php" class="action-tile">
                <i class="fas fa-shopping-cart"></i>
                <span>New Order</span>
            </a>
            <a href="orders.php" class="action-tile">
                <i class="fas fa-list"></i>
                <span>View Orders</span>
            </a>
            <a href="transactions.php" class="action-tile">
                <i class="fas fa-exchange-alt"></i>
                <span>Transaction History</span>
            </a>
            <a href="products.php" class="action-tile">
                <i class="fas fa-box"></i>
                <span>Manage Products</span>
            </a>
        </section>

        <!-- Recent Orders -->
        <section class="recent-orders">
            <div class="recent-orders-header">
                <h3 class="recent-orders-title">Recent Orders</h3>
                <a href="orders.php" class="btn-view-all">VIEW ALL</a>
            </div>
            <div class="orders-table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Table</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($mock_orders) > 0): ?>
                            <?php foreach ($mock_orders as $order): ?>
                                <tr class="order-row" data-id="<?php echo $order['id']; ?>" onclick="window.location='order_detail.php?id=<?php echo (int)$order['id']; ?>'">
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td>₱<?php echo number_format((float)$order['total'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><a href="order_detail.php?id=<?php echo (int)$order['id']; ?>" class="order-link"><i class="fas fa-chevron-right"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-orders">No orders yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
