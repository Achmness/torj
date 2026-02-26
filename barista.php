<?php
session_start();

// Only allow baristas to access
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'barista'){
    header("Location: login.php");
    exit();
}

$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Barista';

// Mock data - replace with database queries later
$pending_orders = [
    ['id' => 101, 'customer_name' => 'Alice', 'table_num' => '5', 'total' => 12.5, 'status' => 'pending'],
    ['id' => 102, 'customer_name' => 'Bob', 'table_num' => '3', 'total' => 7.0, 'status' => 'pending']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barista Dashboard - Debug Café</title>
    <link rel="stylesheet" href="admin.css"> <!-- reuse same styles -->
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
            <a href="#" class="nav-item active">
                <i class="fas fa-list"></i>
                <span>Pending Orders</span>
            </a>
            <a href="#" class="nav-item">
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

        <!-- Pending Orders -->
        <section class="recent-orders">
            <div class="recent-orders-header">
                <h3 class="recent-orders-title">Pending Orders</h3>
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pending_orders) > 0): ?>
                            <?php foreach ($pending_orders as $order): ?>
                                <tr class="order-row" data-id="<?php echo $order['id']; ?>">
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['table_num']); ?></td>
                                    <td>$<?php echo number_format((float)$order['total'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                            <span class="btn-complete-badge" onclick="markCompleted(<?php echo $order['id']; ?>)">
                                <i class="fas fa-check"></i> Complete
                            </span>
                        </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-orders">No pending orders.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        function markCompleted(orderId){
            // Here you can send an AJAX request to a PHP script to update order status
            alert("Order #" + orderId + " marked as completed!");
            // Optionally, remove row or reload page after marking completed
        }
    </script>
<style>
    .btn-complete-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background-color: #27AE60; /* green */
    color: white;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
}

.btn-complete-badge:hover {
    background-color: #2ecc71;
    transform: translateY(-2px);
}

.btn-complete-badge i {
    font-size: 0.85rem;
}
</style>
</body>
</html>