<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include "db.php";

$order_id = (int)($_GET['id'] ?? 0);
if (!$order_id) {
    header("Location: orders.php");
    exit();
}

$order = null;
$items = [];

$r = mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id");
if ($r && $row = mysqli_fetch_assoc($r)) {
    $order = $row;
    $r2 = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = $order_id ORDER BY id");
    if ($r2) {
        while ($item = mysqli_fetch_assoc($r2)) $items[] = $item;
    }
}

if (!$order) {
    header("Location: orders.php");
    exit();
}

$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> - Debug Café</title>
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
            <a href="admin.php" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="neworder.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>New Order</span></a>
            <a href="orders.php" class="nav-item active"><i class="fas fa-list"></i><span>Orders</span></a>
            <a href="transactions.php" class="nav-item"><i class="fas fa-exchange-alt"></i><span>Transactions</span></a>
            <a href="products.php" class="nav-item"><i class="fas fa-box"></i><span>Manage Products</span></a>
        </nav>
        <a href="logout.php" class="nav-logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <h2 class="dashboard-title">Order #<?php echo $order_id; ?></h2>
            <a href="orders.php" class="btn-back-link"><i class="fas fa-arrow-left"></i> Back to Orders</a>
        </header>

        <section class="order-detail-card">
            <div class="order-detail-meta">
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>Date:</strong> <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
                <p><strong>Status:</strong>
                    <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                    <?php if ($order['status'] === 'pending'): ?>
                        <button type="button" class="btn-complete" data-id="<?php echo $order_id; ?>">
                            <i class="fas fa-check"></i> Mark Completed
                        </button>
                    <?php endif; ?>
                </p>
            </div>
            <table class="order-items-table">
                <thead>
                    <tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td>₱<?php echo number_format((float)$item['price'], 2); ?></td>
                            <td><?php echo (int)$item['quantity']; ?></td>
                            <td>₱<?php echo number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="order-total">
                <strong>Total: ₱<?php echo number_format((float)$order['total'], 2); ?></strong>
            </div>
        </section>
    </main>
    <script>
        document.querySelector('.btn-complete')?.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch('api/order_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({order_id: id, status: 'completed'})
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) location.reload();
                else alert(d.error || 'Failed');
            });
        });
    </script>
</body>
</html>
