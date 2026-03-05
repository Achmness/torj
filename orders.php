<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include "db.php";

$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Admin';

$filter = $_GET['filter'] ?? 'all'; // all, pending, completed
$where = '';
if ($filter === 'pending') $where = " WHERE status = 'pending'";
if ($filter === 'completed') $where = " WHERE status = 'completed'";

$orders = [];
$result = mysqli_query($conn, "SELECT * FROM orders $where ORDER BY created_at DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Debug Café</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Debug Café" class="sidebar-logo">
            <h1 class="sidebar-brand">Debug Café</h1>
            <p class="sidebar-tagline">Coffee & Code</p>
        </div>
        <nav class="nav-links">
            <a href="admin.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="home.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i>
                <span>New Order</span>
            </a>
            <a href="orders.php" class="nav-item active">
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
        </nav>
        <a href="logout.php" class="nav-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <h2 class="dashboard-title">Orders</h2>
            <div class="user-greeting">
                <span>Welcome, <?php echo $fullname; ?></span>
                <div class="user-avatar"></div>
            </div>
        </header>

        <div class="filter-tabs">
            <a href="orders.php" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
            <a href="orders.php?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
            <a href="orders.php?filter=completed" class="filter-tab <?php echo $filter === 'completed' ? 'active' : ''; ?>">Completed</a>
        </div>

        <section class="recent-orders">
            <div class="orders-table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Table</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr class="order-row" data-id="<?php echo $order['id']; ?>">
                                    <td>#<?php echo (int)$order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['table_num']); ?></td>
                                    <td>₱<?php echo number_format((float)$order['total'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, g:i A', strtotime($order['created_at'])); ?></td>
                                    <td><a href="order_detail.php?id=<?php echo (int)$order['id']; ?>" class="btn-view">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-orders">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
