<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include "db.php";

$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Admin';

$transactions = [];
$result = mysqli_query($conn, "SELECT * FROM orders WHERE status = 'completed' ORDER BY created_at DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $transactions[] = $row;
    }
}

$total_earnings = 0;
foreach ($transactions as $t) $total_earnings += (float)$t['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Debug Café</title>
    <link rel="stylesheet" href="../style/admin.css">
    <link rel="stylesheet" href="../style/transactions.css">
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
            <a href="admin.php" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="neworder.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>New Order</span></a>
            <a href="orders.php" class="nav-item"><i class="fas fa-list"></i><span>Orders</span></a>
            <a href="transactions.php" class="nav-item active"><i class="fas fa-exchange-alt"></i><span>Transactions</span></a>
            <a href="products.php" class="nav-item"><i class="fas fa-box"></i><span>Manage Products</span></a>
            <a href="users.php" class="nav-item"><i class="fas fa-box"></i><span>Manage Users</span></a>
        </nav>
        <a href="logout.php" class="nav-logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <h2 class="dashboard-title">Transaction History</h2>
            <div class="user-greeting">
                <span>Welcome, <?php echo $fullname; ?></span>
                <div class="user-avatar"></div>
            </div>
        </header>

        <div class="summary-card summary-card-gold" style="margin-bottom: 24px; max-width: 300px;">
            <i class="fas fa-dollar-sign"></i>
            <p class="summary-value">₱<?php echo number_format($total_earnings, 2); ?></p>
            <p class="summary-label">TOTAL COMPLETED REVENUE</p>
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
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($transactions) > 0): ?>
                            <?php foreach ($transactions as $t): ?>
                                <tr>
                                    <td>#<?php echo (int)$t['id']; ?></td>
                                    <td><?php echo htmlspecialchars($t['customer_name']); ?></td>
                                    <td>₱<?php echo number_format((float)$t['total'], 2); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($t['created_at'])); ?></td>
                                    <td><a href="order_detail.php?id=<?php echo (int)$t['id']; ?>" class="btn-view">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-orders">No completed transactions yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
