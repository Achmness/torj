<?php
session_start();

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'cashier' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

include "db.php";

$is_admin = ($_SESSION['role'] === 'admin');
$home_page = $is_admin ? 'neworder.php' : 'cashier.php';

$pending_orders = [];
$orders_query = "SELECT o.*,
    (SELECT GROUP_CONCAT(CONCAT(oi.product_name, ' x', oi.quantity) SEPARATOR ', ')
     FROM order_items oi WHERE oi.order_id = o.id) as items_summary
    FROM orders o
    WHERE o.payment_status = 'unpaid' OR o.payment_status IS NULL
    ORDER BY o.created_at DESC
    LIMIT 50";
$orders_result = mysqli_query($conn, $orders_query);
if ($orders_result) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $pending_orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Payment - Debug Café</title>
    <link rel="stylesheet" href="../style/process_payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="padding-top: 0px;">
    <div class="tab-navigation">
        <?php if ($is_admin): ?>
            <a href="admin.php" class="back-to-dashboard"><i class="fas fa-arrow-left"></i> Back</a>
        <?php endif; ?>
        <a href="<?php echo $home_page; ?>" class="back-to-dashboard">
            <i class="fas fa-shopping-cart"></i> New Orders
        </a>
        <a href="process_payment.php" class="back-to-dashboard">
            <i class="fas fa-cash-register"></i> Process Payment
        </a>
        <?php if (!$is_admin): ?>
            <a href="logout.php" class="back-to-dashboard"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php endif; ?>
    </div>

    <div class="container">
        <div class="tab-content active">
            <div class="payment-section">
                <h2><i class="fas fa-cash-register"></i> Pending Payments</h2>

                <div class="orders-grid">
                    <?php if (count($pending_orders) > 0): ?>
                        <?php foreach ($pending_orders as $order): ?>
                            <div class="order-card">
                                <div class="order-number">#<?php echo (int)$order['id']; ?></div>

                                <div class="order-details">
                                    <h3>
                                        <?php echo htmlspecialchars($order['customer_name']); ?>
                                        <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                        <span class="payment-badge">UNPAID</span>
                                    </h3>
                                    <p><strong>Total:</strong> ₱<?php echo number_format((float)$order['total'], 2); ?></p>
                                    <p><strong>Time:</strong> <?php echo date('M j, g:i A', strtotime($order['created_at'])); ?></p>
                                    <?php if (!empty($order['items_summary'])): ?>
                                        <div class="order-items">
                                            <strong>Items:</strong> <?php echo htmlspecialchars($order['items_summary']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="order-actions">
                                    <button class="btn-process" onclick='openPaymentModal(<?php echo (int)$order['id']; ?>, <?php echo json_encode($order['customer_name']); ?>, <?php echo (float)$order['total']; ?>)'>
                                        <i class="fas fa-money-bill-wave"></i> Process Payment
                                    </button>
                                    <button class="btn-view" onclick="viewOrderDetails(<?php echo (int)$order['id']; ?>)">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-orders">
                            <i cl   ass="fas fa-check-circle" style="font-size: 4rem; color: #27ae60; margin-bottom: 20px;"></i>
                            <p>No pending payments!</p>
                            <p style="font-size: 1rem; color: #999;">All orders have been paid.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="payment-modal">
        <div class="payment-modal-content" style="max-width: 600px;">
            <h3><i class="fas fa-receipt"></i> Order Details</h3>
            <div id="orderDetailsContent" style="max-height: 500px; overflow-y: auto;">
                <p style="text-align: center; padding: 40px; color: #999;">Loading...</p>
            </div>
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="closeOrderDetailsModal()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <div id="paymentModal" class="payment-modal">
        <div class="payment-modal-content">
            <h3><i class="fas fa-cash-register"></i> Process Payment</h3>

            <div style="background: #f9f9f9; padding: 15px; border-radius: 8px;">
                <p style="margin:0;"><strong>Order #<span id="modalOrderId"></span></strong></p>
                <p style="margin:0;"><strong>Customer:</strong> <span id="modalCustomerName"></span></p>
                <p style="font-size: 1.2rem; color: #666; margin:0;"><strong>Original Total: ₱<span id="modalOriginalTotal"></span></strong></p>
                <div style="margin-top: 15px; padding: 15px; background: #fff; border-radius: 8px; border: 2px solid #ECB212;">
                    <label style="display:block; margin-bottom:8px; font-weight:bold; color:#3d2d00;">Discount (%):</label>
                    <input type="number" id="discountPercent" min="0" max="100" step="0.01" value="0" style="width:95%; padding:10px; border:2px solid #E8E0D5; border-radius:8px; font-size:1rem;">
                    <p style="font-size:0.9rem; color:#666; margin:3px 0 0;">Discount Amount: ₱<span id="discountAmount">0.00</span></p>
                </div>
                <p style="font-size:1.5rem; color:#3d2d00; margin:3px 0 0; font-weight:bold;"><strong>Final Total: ₱<span id="modalTotal"></span></strong></p>
            </div>

            <h4 style="margin:0; color:#3d2d00;">Select Payment Method:</h4>

            <div class="payment-method-btns">
                <div class="payment-method-btn" onclick="selectPaymentMethod(event, 'cash')">
                    <i class="fas fa-money-bill-wave"></i>
                    Cash
                </div>
                <div class="payment-method-btn" onclick="selectPaymentMethod(event, 'online')">
                    <i class="fas fa-credit-card"></i>
                    Online/Card
                </div>
            </div>

            <div id="cashPaymentSection" class="cash-payment-section">
                <div class="form-group">
                    <label>Amount Received:</label>
                    <input type="number" id="cashReceived" placeholder="Enter amount" step="0.01" min="0">
                </div>
                <div class="change-display" id="changeDisplay">Change: ₱0.00</div>
            </div>

            <div id="onlinePaymentSection" class="online-payment-section">
                <h4 style="color:#3d2d00; margin-top:0;">Scan GCash QR Code</h4>
                <div class="qr-code-container">
                    <img src="../ggg.jpg" alt="GCash QR Code">
                    <p>Scan to Pay via GCash</p>
                </div>
                <p style="color:#666; font-size:0.9rem;">After payment, click Confirm Payment below</p>
            </div>

            <div class="modal-buttons">
                <button class="btn-confirm" id="confirmPaymentBtn" disabled onclick="confirmPayment()">
                    <i class="fas fa-check-circle"></i> Confirm Payment
                </button>
                <button class="btn-cancel" onclick="closePaymentModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <script>
    let currentOrderId = null;
    let currentOrderTotal = 0;
    let originalOrderTotal = 0;
    let selectedPaymentMethod = null;

    function openPaymentModal(orderId, customerName, total) {
        currentOrderId = orderId;
        currentOrderTotal = total;
        originalOrderTotal = total;
        selectedPaymentMethod = null;

        document.getElementById('modalOrderId').textContent = orderId;
        document.getElementById('modalCustomerName').textContent = customerName;
        document.getElementById('modalOriginalTotal').textContent = total.toFixed(2);
        document.getElementById('modalTotal').textContent = total.toFixed(2);
        document.getElementById('discountPercent').value = '0';
        document.getElementById('discountAmount').textContent = '0.00';
        document.getElementById('cashReceived').value = '';
        document.getElementById('changeDisplay').textContent = 'Change: ₱0.00';
        document.getElementById('cashPaymentSection').classList.remove('active');
        document.getElementById('onlinePaymentSection').classList.remove('active');
        document.getElementById('confirmPaymentBtn').disabled = true;
        document.querySelectorAll('.payment-method-btn').forEach(btn => btn.classList.remove('selected'));
        document.getElementById('paymentModal').classList.add('open');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.remove('open');
    }

    function selectPaymentMethod(evt, method) {
        selectedPaymentMethod = method;
        document.querySelectorAll('.payment-method-btn').forEach(btn => btn.classList.remove('selected'));
        evt.currentTarget.classList.add('selected');
        if (method === 'cash') {
            document.getElementById('cashPaymentSection').classList.add('active');
            document.getElementById('onlinePaymentSection').classList.remove('active');
            document.getElementById('confirmPaymentBtn').disabled = true;
        } else {
            document.getElementById('cashPaymentSection').classList.remove('active');
            document.getElementById('onlinePaymentSection').classList.add('active');
            document.getElementById('confirmPaymentBtn').disabled = false;
        }
    }

    document.getElementById('discountPercent').addEventListener('input', function() {
        const discountPercent = parseFloat(this.value) || 0;
        const discountAmount = (originalOrderTotal * discountPercent) / 100;
        const finalTotal = originalOrderTotal - discountAmount;
        currentOrderTotal = finalTotal;
        document.getElementById('discountAmount').textContent = discountAmount.toFixed(2);
        document.getElementById('modalTotal').textContent = finalTotal.toFixed(2);
        if (selectedPaymentMethod === 'cash') updateCashChange();
    });

    document.getElementById('cashReceived').addEventListener('input', updateCashChange);

    function updateCashChange() {
        const received = parseFloat(document.getElementById('cashReceived').value) || 0;
        const change = received - currentOrderTotal;
        const changeDisplay = document.getElementById('changeDisplay');
        if (change >= 0) {
            changeDisplay.textContent = `Change: ₱${change.toFixed(2)}`;
            changeDisplay.style.background = '#3d2d00';
            changeDisplay.style.color = '#ECB212';
            document.getElementById('confirmPaymentBtn').disabled = false;
        } else {
            changeDisplay.textContent = `Insufficient: ₱${Math.abs(change).toFixed(2)} more needed`;
            changeDisplay.style.background = '#e74c3c';
            changeDisplay.style.color = 'white';
            document.getElementById('confirmPaymentBtn').disabled = true;
        }
    }

    function confirmPayment() {
        if (!currentOrderId || !selectedPaymentMethod) {
            alert('Please select a payment method');
            return;
        }
        const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
        const discountAmount = (originalOrderTotal * discountPercent) / 100;
        let received = currentOrderTotal;
        let change = 0;

        if (selectedPaymentMethod === 'cash') {
            received = parseFloat(document.getElementById('cashReceived').value) || 0;
            change = received - currentOrderTotal;
            if (change < 0) {
                alert('Insufficient payment amount');
                return;
            }
        }

        fetch('../api/process_payment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                order_id: currentOrderId,
                payment_method: selectedPaymentMethod,
                amount_received: received,
                change: change,
                discount_percent: discountPercent,
                discount_amount: discountAmount,
                final_amount: currentOrderTotal
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Payment processed successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to process payment'));
            }
        })
        .catch(() => alert('Failed to process payment'));
    }

    document.getElementById('paymentModal').addEventListener('click', function(e) {
        if (e.target === this) closePaymentModal();
    });

    // Order Details Modal Functions
    function viewOrderDetails(orderId) {
        document.getElementById('orderDetailsModal').classList.add('open');
        document.getElementById('orderDetailsContent').innerHTML = '<p style="text-align: center; padding: 40px; color: #999;">Loading...</p>';
        
        fetch('../api/get_order_details.php?id=' + orderId)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    displayOrderDetails(data.order, data.items);
                } else {
                    document.getElementById('orderDetailsContent').innerHTML = '<p style="text-align: center; padding: 40px; color: #e74c3c;">Error loading order details</p>';
                }
            })
            .catch(() => {
                document.getElementById('orderDetailsContent').innerHTML = '<p style="text-align: center; padding: 40px; color: #e74c3c;">Failed to load order details</p>';
            });
    }

    function displayOrderDetails(order, items) {
        let html = `
            <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 10px; color: #3d2d00;">Order #${order.id}</h4>
                <p style="margin: 5px 0;"><strong>Customer:</strong> ${order.customer_name}</p>
                <p style="margin: 5px 0;"><strong>Status:</strong> <span class="status-badge status-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></p>
                <p style="margin: 5px 0;"><strong>Payment Status:</strong> <span class="payment-badge">${order.payment_status ? order.payment_status.toUpperCase() : 'UNPAID'}</span></p>
                <p style="margin: 5px 0;"><strong>Date:</strong> ${new Date(order.created_at).toLocaleString()}</p>
            </div>
            
            <h4 style="margin: 20px 0 10px; color: #3d2d00;">Order Items</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f9f9f9; border-bottom: 2px solid #ECB212;">
                        <th style="padding: 10px; text-align: left;">Item</th>
                        <th style="padding: 10px; text-align: center;">Qty</th>
                        <th style="padding: 10px; text-align: right;">Price</th>
                        <th style="padding: 10px; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        items.forEach(item => {
            const itemTotal = item.quantity * item.price;
            html += `
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;">${item.product_name}</td>
                    <td style="padding: 10px; text-align: center;">${item.quantity}</td>
                    <td style="padding: 10px; text-align: right;">₱${parseFloat(item.price).toFixed(2)}</td>
                    <td style="padding: 10px; text-align: right;">₱${itemTotal.toFixed(2)}</td>
                </tr>
            `;
        });
        
        html += `
                </tbody>
                <tfoot>
                    <tr style="background: #f9f9f9; font-weight: bold; border-top: 2px solid #3d2d00;">
                        <td colspan="3" style="padding: 15px; text-align: right;">TOTAL:</td>
                        <td style="padding: 15px; text-align: right; color: #3d2d00; font-size: 1.2rem;">₱${parseFloat(order.total).toFixed(2)}</td>
                    </tr>
                </tfoot>
            </table>
        `;
        
        document.getElementById('orderDetailsContent').innerHTML = html;
    }

    function closeOrderDetailsModal() {
        document.getElementById('orderDetailsModal').classList.remove('open');
    }

    document.getElementById('orderDetailsModal').addEventListener('click', function(e) {
        if (e.target === this) closeOrderDetailsModal();
    });
    </script>
</body>
</html>
