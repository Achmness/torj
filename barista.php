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
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .btn-complete-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #27AE60;
            color: white;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            border: none;
        }

        .btn-complete-badge:hover {
            background-color: #2ecc71;
            transform: translateY(-2px);
        }

        .btn-complete-badge i {
            font-size: 0.85rem;
        }

        .filter-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .filter-tab {
            padding: 0.8rem 1.5rem;
            background: white;
            border: 2px solid #E8E0D5;
            border-radius: 8px;
            text-decoration: none;
            color: #5d4037;
            font-weight: 600;
            transition: all 0.3s;
        }

        .filter-tab:hover {
            border-color: #8B6F47;
        }

        .filter-tab.active {
            background: #ECB212;
            border-color: #ECB212;
            color: #3d2d00;
        }

        .order-details-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #5d4037;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            border: none;
        }

        .order-details-btn:hover {
            background-color: #8B6F47;
        }

        .status-badge.status-completed {
            background: #4caf50;
            color: white;
        }

        .status-select {
            padding: 6px 12px;
            border: 2px solid #E8E0D5;
            border-radius: 8px;
            background: white;
            color: #5d4037;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .status-select:hover {
            border-color: #ECB212;
        }
        
        .status-select:focus {
            outline: none;
            border-color: #8B6F47;
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.1);
        }
        
        .status-select option {
            padding: 8px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-badge.status-cancelled {
            background: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Debug Café" class="sidebar-logo">
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
                                    <td><?php echo htmlspecialchars($order['table_num']); ?></td>
                                    <td>₱<?php echo number_format((float)$order['total'], 2); ?></td>
                                    <td>
                                        <?php if ($order['status'] === 'completed' || $order['status'] === 'cancelled'): ?>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        <?php else: ?>
                                            <select class="status-select" data-order-id="<?php echo $order['id']; ?>">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
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

    <script>
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const orderId = this.dataset.orderId;
                const newStatus = this.value;
                
                if (confirm(`Update order #${orderId} status to "${newStatus}"?`)) {
                    fetch('api/barista_update_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Order status updated successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + (data.error || 'Failed to update status'));
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to update order status');
                        location.reload();
                    });
                } else {
                    location.reload();
                }
            });
        });
    </script>
</body>
</html>
