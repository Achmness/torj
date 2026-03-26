<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Admin';

$products = [];
$r = mysqli_query($conn, "SELECT * FROM products ORDER BY category, name");
if ($r) while ($row = mysqli_fetch_assoc($r)) $products[] = $row;

// Get pending/unpaid orders for payment processing
$pending_orders = [];
$orders_query = "SELECT o.*, 
    (SELECT GROUP_CONCAT(CONCAT(oi.product_name, ' x', oi.quantity) SEPARATOR ', ') 
     FROM order_items oi WHERE oi.order_id = o.id) as items_summary
    FROM orders o 
    WHERE o.payment_status = 'unpaid' OR o.payment_status IS NULL
    ORDER BY o.created_at DESC 
    LIMIT 20";
$orders_result = mysqli_query($conn, $orders_query);
if ($orders_result) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $pending_orders[] = $row;
    }
}

$upload_dir = __DIR__ . '/../uploads/products/';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
$basePath = ($basePath === '' || $basePath === '.') ? '' : $basePath;
$imgBase = $basePath ? $basePath . '/' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../style/neworder.css">
    <title>New Order - Debug Café</title>

</head>
<body style="padding-top: 30px;">
    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <a href="admin.php" class="back-to-dashboard">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <a href="neworder.php" class="back-to-dashboard"><i class="fas fa-shopping-cart"></i> New Order</a>
        <a href="process_payment.php" class="back-to-dashboard"><i class="fas fa-cash-register"></i> Process Payment</a>
    </div>

    <div class="container">
        
        <!-- NEW ORDER TAB -->
        <div id="neworder-tab" class="tab-content active">
            <div class="neworder-main">
                <div class="outer">
            <div class="inner-1">
                <div class="innerimage">
                    
                    <img src="../logo.png" class="imagecafe" alt="Logo">

                    <p class="nav-header">PRODUCTS</p>
                    <div class="prod">
                        <p class="nav-item active" data-cat="hot">Hot Drinks</p>
                        <p class="nav-item" data-cat="cold">Cold Drinks</p>
                        <p class="nav-item" data-cat="bread">Bread and Pastries</p>
                    </div>
                    
                </div>
            </div>

            <div class="inner-2">
                <div class="product-grid" id="productGrid">
                    <?php
                    $idx = 0;
                    foreach ($products as $p):
                        $pid = (int)($p['p_id'] ?? $p['id'] ?? 0);
                        $name = htmlspecialchars($p['name'] ?? '');
                        $price = (float)($p['price'] ?? 0);
                        $cat = $p['category'] ?? 'hot';
                        $img = !empty($p['image']) && file_exists($upload_dir . $p['image'])
                            ? $imgBase . '../uploads/products/' . htmlspecialchars($p['image'])
                            : 'logo.png';
                        $idx++;
                    ?>
                    <div class="product-card" data-name="<?php echo $name; ?>" data-price="<?php echo $price; ?>" data-category="<?php echo htmlspecialchars($cat); ?>" style="--order: <?php echo $idx; ?>">
                        <img src="<?php echo $img; ?>" alt="">
                        <p class="product-name"><?php echo $name; ?></p>
                        <p class="product-price">₱<?php echo number_format($price, 2); ?></p>
                        <div class="quantity-controls"><button class="qty-btn minus">-</button><span class="qty-number">0</span><button class="qty-btn plus">+</button></div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($products) === 0): ?>
                        <p style="grid-column:1/-1; text-align:center; padding:40px;">No products yet. Add products in <a href="products.php">Manage Products</a>.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="inner-3">
                <p class="cart-text">CURRENT ORDER</p>
                <p id="timestamp" class="timestamp-text">No order active</p>
                <div id="receipt-items" class="receipt-items-container">
                    <p class="empty-msg">Select items to begin...</p>
                </div>
                <div class="receipt-footer">
                    <p class="total-text" id="grand-total">TOTAL: ₱0.00</p>
                    <button class="checkout-btn" id="placeOrderBtn">PLACE ORDER</button>
                    <button class="checkout-btn" id="printBtn" style="margin-top:8px">PRINT RECEIPT</button>
                </div>
            </div>
                </div>
            </div>
        </div>

  
        <div id="payment-tab" class="tab-content">
            <div class="payment-section">
                <h2><i class="fas fa-cash-register"></i> Pending Payments</h2>
                
                <div class="orders-grid">
                    <?php if (count($pending_orders) > 0): ?>
                        <?php foreach ($pending_orders as $order): ?>
                            <div class="order-card">
                                <div class="order-number">#<?php echo $order['id']; ?></div>
                                
                                <div class="order-details">
                                    <h3>
                                        <?php echo htmlspecialchars($order['customer_name']); ?>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                        <span class="payment-badge">UNPAID</span>
                                    </h3>
                                    <p><strong>Table:</strong> <?php echo htmlspecialchars($order['table_num']); ?></p>
                                    <p><strong>Total:</strong> ₱<?php echo number_format($order['total'], 2); ?></p>
                                    <p><strong>Time:</strong> <?php echo date('M j, g:i A', strtotime($order['created_at'])); ?></p>
                                    
                                    <?php if (!empty($order['items_summary'])): ?>
                                        <div class="order-items">
                                            <strong>Items:</strong> <?php echo htmlspecialchars($order['items_summary']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="order-actions">
                                    <button class="btn-process" onclick="openPaymentModal(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['customer_name']); ?>', <?php echo $order['total']; ?>)">
                                        <i class="fas fa-money-bill-wave"></i> Process Payment
                                    </button>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn-view">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-orders">
                            <i class="fas fa-check-circle" style="font-size: 4rem; color: #27ae60; margin-bottom: 20px;"></i>
                            <p>No pending payments!</p>
                            <p style="font-size: 1rem; color: #999;">All orders have been paid.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="payment-modal">
        <div class="payment-modal-content">
            <h3><i class="fas fa-cash-register"></i> Process Payment</h3>
            
            <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 0px; margin-top: 0px;">
                <p style="margin-top: 0px; margin-bottom: 0px;"><strong>Order #<span id="modalOrderId"></span></strong></p>
                <p style="margin-top: 0px; margin-bottom: 0px;"><strong>Customer:</strong> <span id="modalCustomerName"></span></p>
                <p style="font-size: 1.2rem; color: #666; margin-top: 0px; margin-bottom: 0px;"><strong>Original Total: ₱<span id="modalOriginalTotal"></span></strong></p>
                <div style="margin-top: 15px; padding: 15px; background: #fff; border-radius: 8px; border: 2px solid #ECB212;">
                    <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #3d2d00;">Discount (%):</label>
                    <input type="number" id="discountPercent" placeholder="Enter discount %" min="0" max="100" step="0.01" value="0" style="width: 95%; padding: 10px; border: 2px solid #E8E0D5; border-radius: 8px; font-size: 1rem;">
                    <p style="font-size: 0.9rem; color: #666; margin-top: 3px; margin-bottom: 0px;">Discount Amount: ₱<span id="discountAmount">0.00</span></p>
                </div>
                <p style="font-size: 1.5rem; color: #3d2d00; margin-top: 3px; margin-bottom: 0px; font-weight: bold;"><strong>Final Total: ₱<span id="modalTotal"></span></strong></p>
            </div>
            
            <h4 style="margin-bottom: 0px; margin-top: 0px; color: #3d2d00;">Select Payment Method:</h4>
            
            <div class="payment-method-btns">
                <div     class="payment-method-btn" onclick="selectPaymentMethod('cash')">
                    <i class="fas fa-money-bill-wave"></i>
                    Cash
                </div>
                <div class="payment-method-btn" onclick="selectPaymentMethod('online')">
                    <i class="fas fa-credit-card"></i>
                    Online/Card
                </div>
            </div>
            
            <div id="cashPaymentSection" class="cash-payment-section">
                <div class="form-group">
                    <label>Amount Received:</label>
                    <input type="number" id="cashReceived" placeholder="Enter amount" step="0.01" min="0">
                </div>
                
                <div class="change-display" id="changeDisplay">
                    Change: ₱0.00
                </div>
            </div>
            
            <div id="onlinePaymentSection" class="online-payment-section">
                <h4 style="color: #3d2d00; margin-top: 0;">Scan GCash QR Code</h4>
                <div class="qr-code-container">
                    <img src="ggg.jpg" alt="GCash QR Code">
                    <p>Scan to Pay via GCash</p>
                </div>
                <p style="color: #666; font-size: 0.9rem;">After payment, click Confirm Payment below</p>
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

    <div id="placeOrderModal" class="order-modal">
        <div class="order-modal-content">
            <h3>Place Order</h3>
            <form id="placeOrderForm">
                <label>Customer Name <input type="text" id="customerName" placeholder="Name" required></label>
                <label>Table Number <input type="text" id="tableNum" placeholder="e.g. 1" value="1"></label>
                <div class="modal-btns">
                    <button type="submit" class="checkout-btn">Confirm Order</button>
                    <button type="button" class="btn-cancel-modal" onclick="closePlaceOrderModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <style>
    .order-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center; }
    .order-modal.open { display:flex; }
    .order-modal-content { background:white; padding:24px; border-radius:12px; min-width:320px; }
    .order-modal-content h3 { margin:0 0 16px; }
    .order-modal-content label { display:block; margin-bottom:12px; }
    .order-modal-content input { width:100%; padding:10px; border:2px solid #E8E0D5; border-radius:8px; box-sizing:border-box; }
    .modal-btns { display:flex; gap:10px; margin-top:16px; }
    .btn-cancel-modal { padding:10px 20px; background:#95a5a6; color:white; border:none; border-radius:8px; cursor:pointer; }
    .checkout-btn { cursor:pointer; }
    </style>

    <script>
    // Tab Switching
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        
        document.getElementById(tabName + '-tab').classList.add('active');
        event.target.classList.add('active');
    }

    const receiptContainer = document.getElementById('receipt-items');
    const totalDisplay = document.getElementById('grand-total');
    const timestampDisplay = document.getElementById('timestamp');
    let cart = {};
    let currentCategory = 'hot';

    document.querySelectorAll('.prod .nav-item').forEach(el => {
        el.addEventListener('click', function() {
            document.querySelectorAll('.prod .nav-item').forEach(n => n.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.dataset.cat;
            document.querySelectorAll('.product-card').forEach(card => {
                card.style.display = card.dataset.category === currentCategory ? '' : 'none';
            });
        });
    });

    document.querySelectorAll('.product-card').forEach(card => {
        const cat = card.dataset.category;
        if (cat !== 'hot') card.style.display = 'none';
    });

    function updateReceipt() {
        receiptContainer.innerHTML = '';
        let total = 0, count = 0;
        Object.keys(cart).forEach(name => {
            if (cart[name].qty > 0) {
                count++;
                const itemTotal = cart[name].qty * cart[name].price;
                total += itemTotal;
                const row = document.createElement('div');
                row.className = 'receipt-row';
                row.innerHTML = `<span>${name} x${cart[name].qty}</span><span>₱${itemTotal.toFixed(2)}</span>`;
                receiptContainer.appendChild(row);
            }
        });
        if (count === 0) {
            receiptContainer.innerHTML = '<p class="empty-msg">Select items to begin...</p>';
            timestampDisplay.innerText = "No order active";
        } else {
            timestampDisplay.innerText = "Date: " + new Date().toLocaleString();
        }
        totalDisplay.innerText = `TOTAL: ₱${total.toFixed(2)}`;
    }

    document.querySelectorAll('.product-card').forEach(card => {
        const name = card.getAttribute('data-name');
        const price = parseFloat(card.getAttribute('data-price'));
        const qtyDisplay = card.querySelector('.qty-number');
        card.querySelector('.plus').addEventListener('click', () => {
            if (!cart[name]) cart[name] = { price, qty: 0 };
            cart[name].qty++;
            qtyDisplay.innerText = cart[name].qty;
            updateReceipt();
        });
        card.querySelector('.minus').addEventListener('click', () => {
            if (cart[name] && cart[name].qty > 0) {
                cart[name].qty--;
                qtyDisplay.innerText = cart[name].qty;
                updateReceipt();
            }
        });
    });

    document.getElementById("placeOrderBtn").addEventListener("click", function () {
        let count = 0;
        Object.keys(cart).forEach(name => { if (cart[name].qty > 0) count++; });
        if (count === 0) { alert("No items in order."); return; }
        document.getElementById("placeOrderModal").classList.add("open");
    });

    document.getElementById("placeOrderForm").addEventListener("submit", function (e) {
        e.preventDefault();
        const items = [];
        Object.keys(cart).forEach(name => {
            if (cart[name].qty > 0) items.push({ name: name, price: cart[name].price, qty: cart[name].qty });
        });
        fetch("../api/save_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                customer_name: document.getElementById("customerName").value || "Walk-in",
                table_num: document.getElementById("tableNum").value || "1",
                items: items
            })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                closePlaceOrderModal();
                cart = {};
                document.querySelectorAll(".qty-number").forEach(el => el.innerText = "0");
                updateReceipt();
                alert("Order #" + d.order_id + " placed successfully!");
                location.reload();
            } else {
                alert(d.error || "Failed to place order");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Failed to place order. Please try again.");
        });
    });

    function closePlaceOrderModal() { document.getElementById("placeOrderModal").classList.remove("open"); }
    document.getElementById("placeOrderModal").addEventListener("click", function(e) { if (e.target === this) closePlaceOrderModal(); });

    document.getElementById("printBtn").addEventListener("click", function () {
        let count = 0;
        Object.keys(cart).forEach(name => { if (cart[name].qty > 0) count++; });
        if (count === 0) { alert("No items in order."); return; }
        let receiptContent = `<h2>The Debug Café</h2><p>${new Date().toLocaleString()}</p><hr>`;
        let total = 0;
        Object.keys(cart).forEach(name => {
            if (cart[name].qty > 0) {
                let itemTotal = cart[name].qty * cart[name].price;
                total += itemTotal;
                receiptContent += `<p>${name} x${cart[name].qty} — ₱${itemTotal.toFixed(2)}</p>`;
            }
        });
        receiptContent += `<hr><h3>Total: ₱${total.toFixed(2)}</h3><p>Thank you for visiting!</p>`;
        let printWindow = window.open('', '', 'width=400,height=600');
        printWindow.document.write(`<html><head><title>Receipt</title><style>body{font-family:monospace;padding:20px;text-align:center;}hr{border:1px dashed black;}</style></head><body>${receiptContent}</body></html>`);
        printWindow.document.close();
        printWindow.print();
    });

    // PAYMENT PROCESSING LOGIC
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
        
        // Reset form
        document.getElementById('discountPercent').value = '0';
        document.getElementById('discountAmount').textContent = '0.00';
        document.getElementById('cashReceived').value = '';
        document.getElementById('changeDisplay').textContent = 'Change: ₱0.00';
        document.getElementById('cashPaymentSection').classList.remove('active');
        document.getElementById('onlinePaymentSection').classList.remove('active');
        document.getElementById('confirmPaymentBtn').disabled = true;
        
        // Remove selected class from all payment methods
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('selected');
        });
        
        document.getElementById('paymentModal').classList.add('open');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.remove('open');
        currentOrderId = null;
        currentOrderTotal = 0;
        originalOrderTotal = 0;
        selectedPaymentMethod = null;
    }

    // Calculate discount
    document.getElementById('discountPercent').addEventListener('input', function() {
        const discountPercent = parseFloat(this.value) || 0;
        const discountAmount = (originalOrderTotal * discountPercent) / 100;
        const finalTotal = originalOrderTotal - discountAmount;
        
        currentOrderTotal = finalTotal;
        document.getElementById('discountAmount').textContent = discountAmount.toFixed(2);
        document.getElementById('modalTotal').textContent = finalTotal.toFixed(2);
        
        // Recalculate change if cash payment is active
        if (selectedPaymentMethod === 'cash') {
            const received = parseFloat(document.getElementById('cashReceived').value) || 0;
            const change = received - currentOrderTotal;
            
            if (change >= 0) {
                document.getElementById('changeDisplay').textContent = `Change: ₱${change.toFixed(2)}`;
                document.getElementById('changeDisplay').style.background = '#3d2d00';
                document.getElementById('changeDisplay').style.color = '#ECB212';
                document.getElementById('confirmPaymentBtn').disabled = false;
            } else {
                document.getElementById('changeDisplay').textContent = `Insufficient: ₱${Math.abs(change).toFixed(2)} more needed`;
                document.getElementById('changeDisplay').style.background = '#e74c3c';
                document.getElementById('changeDisplay').style.color = 'white';
                document.getElementById('confirmPaymentBtn').disabled = true;
            }
        }
    });

    function selectPaymentMethod(method) {
        selectedPaymentMethod = method;
        
        // Update UI
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('selected');
        });
        event.target.closest('.payment-method-btn').classList.add('selected');
        
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

    // Calculate change for cash payment
    document.getElementById('cashReceived').addEventListener('input', function() {
        const received = parseFloat(this.value) || 0;
        const change = received - currentOrderTotal;
        
        if (change >= 0) {
            document.getElementById('changeDisplay').textContent = `Change: ₱${change.toFixed(2)}`;
            document.getElementById('changeDisplay').style.background = '#3d2d00';
            document.getElementById('changeDisplay').style.color = '#ECB212';
            document.getElementById('confirmPaymentBtn').disabled = false;
        } else {
            document.getElementById('changeDisplay').textContent = `Insufficient: ₱${Math.abs(change).toFixed(2)} more needed`;
            document.getElementById('changeDisplay').style.background = '#e74c3c';
            document.getElementById('changeDisplay').style.color = 'white';
            document.getElementById('confirmPaymentBtn').disabled = true;
        }
    });

    function confirmPayment() {
        if (!currentOrderId || !selectedPaymentMethod) {
            alert('Please select a payment method');
            return;
        }
        
        const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
        const discountAmount = (originalOrderTotal * discountPercent) / 100;
        
        if (selectedPaymentMethod === 'cash') {
            const received = parseFloat(document.getElementById('cashReceived').value) || 0;
            const change = received - currentOrderTotal;
            
            if (change < 0) {
                alert('Insufficient payment amount');
                return;
            }
            
            let confirmMsg = `Confirm Cash Payment?\n\nOriginal Total: ₱${originalOrderTotal.toFixed(2)}`;
            if (discountPercent > 0) {
                confirmMsg += `\nDiscount (${discountPercent}%): -₱${discountAmount.toFixed(2)}`;
            }
            confirmMsg += `\nFinal Total: ₱${currentOrderTotal.toFixed(2)}\nReceived: ₱${received.toFixed(2)}\nChange: ₱${change.toFixed(2)}`;
            
            if (confirm(confirmMsg)) {
                processPaymentAPI(currentOrderId, selectedPaymentMethod, received, change, discountPercent, discountAmount, currentOrderTotal);
            }
        } else {
            let confirmMsg = `Confirm Online/Card Payment?\n\nOriginal Total: ₱${originalOrderTotal.toFixed(2)}`;
            if (discountPercent > 0) {
                confirmMsg += `\nDiscount (${discountPercent}%): -₱${discountAmount.toFixed(2)}`;
            }
            confirmMsg += `\nFinal Total: ₱${currentOrderTotal.toFixed(2)}`;
            
            if (confirm(confirmMsg)) {
                processPaymentAPI(currentOrderId, selectedPaymentMethod, currentOrderTotal, 0, discountPercent, discountAmount, currentOrderTotal);
            }
        }
    }

    function processPaymentAPI(orderId, method, received, change, discountPercent, discountAmount, finalAmount) {
        fetch('../api/process_payment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                order_id: orderId,
                payment_method: method,
                amount_received: received,
                change: change,
                discount_percent: discountPercent,
                discount_amount: discountAmount,
                final_amount: finalAmount
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closePaymentModal();
                let successMsg = `Payment processed successfully!\n\nPayment Method: ${method.toUpperCase()}`;
                if (discountPercent > 0) {
                    successMsg += `\nDiscount Applied: ${discountPercent}% (-₱${discountAmount.toFixed(2)})`;
                }
                successMsg += `\nFinal Amount: ₱${finalAmount.toFixed(2)}`;
                if (method === 'cash') {
                    successMsg += `\nChange: ₱${change.toFixed(2)}`;
                }
                if (data.paymongo_payment_id) {
                    successMsg += `\n\nPayMongo Payment ID: ${data.paymongo_payment_id}`;
                }
                alert(successMsg);
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to process payment'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to process payment');
        });
    }

    // Close modal when clicking outside
    document.getElementById('paymentModal').addEventListener('click', function(e) {
        if (e.target === this) closePaymentModal();
    });
    </script>
</body>
</html>
