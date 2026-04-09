<?php
session_start();

// Only allow baristas to access
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'barista'){
    header("Location: login.php");
    exit();
}

include "db.php";

$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Barista';

// Fetch orders from database
$filter = $_GET['filter'] ?? 'pending';
$where = '';
if ($filter === 'pending') {
    $where = " WHERE status IN ('pending', 'preparing', 'ready')";
} elseif ($filter === 'completed') {
    $where = " WHERE status = 'completed'";
}

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
    <title>Barista Dashboard - Debug Café</title>
    <link rel="stylesheet" href="../style/admin.css">
    <link rel="stylesheet" href="../style/barista.css">
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
            <a href="barista.php?filter=pending" class="nav-item <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i>
                <span>Pending Orders</span>
            </a>
            <a href="barista.php?filter=completed" class="nav-item <?php echo $filter === 'completed' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i>
                <span>Completed Orders</span>
            </a>
        </nav>
        <a href="logout.php" class="nav-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <h2 class="dashboard-title">Barista Dashboard</h2>
            <div class="user-greeting">
                <span>Welcome, <?php echo $fullname; ?></span>
                <div class="user-avatar"></div>
            </div>
        </header>

        <section class="recent-orders">
            <div class="recent-orders-header">
                <h3 class="recent-orders-title">
                    <?php echo $filter === 'pending' ? 'Pending Orders' : 'Completed Orders'; ?>
                </h3>
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
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr class="order-row" data-id="<?php echo $order['id']; ?>">
                                    <td>#<?php echo (int)$order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td>₱<?php echo number_format((float)$order['total'], 2); ?></td>
                                    <td>
                                        <?php if ($order['status'] === 'completed' || $order['status'] === 'cancelled'): ?>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        <?php else: ?>
                                            <select class="status-select" data-order-id="<?php echo $order['id']; ?>">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, g:i A', strtotime($order['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-orders">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="../js/barista.js"></script>
</body>
</html>
