<?php
session_start();

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'cashier' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

include "db.php";

$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Cashier';

// Get products for ordering
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

$upload_dir = __DIR__ . '/uploads/products/';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
$basePath = ($basePath === '' || $basePath === '.') ? '' : $basePath;
$imgBase = $basePath ? $basePath . '/' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Cashier - Debug Café</title>
    <style>
        body { margin: 0; padding: 0; background: rgb(136, 136, 131); }
        
        .cashier-container {
            display: flex;
            min-height: 100vh;
            padding: 20px;
            gap: 20px;
        }
        
        /* Tab Navigation */
        .tab-navigation {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 100;
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 200px;
        }
        
        .tab-btn {
            padding: 15px 20px;
            background: #3d2d00;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .tab-btn:hover {
            background: #ECB212;
            color: #3d2d00;
        }
        
        .tab-btn.active {
            background: #ECB212;
            color: #3d2d00;
        }
        
        .logout-btn {
            padding: 15px 20px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
            width: 100%;
            margin-top: 20px;
            margin-left: 220px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* New Order Section (Same as neworder.php) */
        .neworder-main { 
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        
        /* Payment Processing Section */
        .payment-section {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
        }
        
        .payment-section h2 {
            color: #3d2d00;
            margin-bottom: 20px;
            font-size: 2rem;
        }
        
        .orders-grid {
            display: grid;
            gap: 15px;
        }
        
        .order-card {
            background: #f9f9f9;
            border: 2px solid #ECB212;
            border-radius: 10px;
            padding: 20px;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 20px;
            align-items: center;
            transition: all 0.3s;
        }
        
        .order-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transform: translateY(-2px);
        }
        
        .order-number {
            font-size: 2rem;
            font-weight: bold;
            color: #ECB212;
            background: #3d2d00;
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
        }
        
        .order-details {
            flex: 1;
        }
        
        .order-details h3 {
            margin: 0 0 10px 0;
            color: #3d2d00;
        }
        
        .order-details p {
            margin: 5px 0;
            color: #666;
        }
        
        .order-items {
            background: white;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 0.9rem;
        }
        
        .order-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .btn-process {
            padding: 12px 30px;
            background: #3d2d00;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .btn-process:hover {
            background: #ECB212;
            color: #3d2d00;
            transform: translateY(-2px);
        }
        
        .btn-view {
            padding: 12px 30px;
            background: #916c07;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn-view:hover {
            background: #6b5005;
        }
        
        .no-orders {
            text-align: center;
            padding: 60px;
            color: #999;
            font-size: 1.2rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .status-pending {
            background: #f39c12;
            color: white;
        }
        
        .status-preparing {
            background: #3498db;
            color: white;
        }
        
        .status-ready {
            background: #27ae60;
            color: white;
        }
        
        .payment-badge {
            background: #ECB212;
            color: #3d2d00;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        
        /* Payment Modal */
        .payment-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .payment-modal.open {
            display: flex;
        }
        
        .payment-modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
        }
        
        .payment-modal-content h3 {
            color: #3d2d00;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .payment-method-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        
        .payment-method-btn {
            padding: 20px;
            background: #f9f9f9;
            border: 3px solid #ddd;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .payment-method-btn:hover {
            border-color: #ECB212;
            background: #fff;
        }
        
        .payment-method-btn.selected {
            border-color: #ECB212;
            background: #fffbf0;
        }
        
        .payment-method-btn i {
            display: block;
            font-size: 2rem;
            margin-bottom: 10px;
            color: #3d2d00;
        }
        
        .cash-payment-section {
            display: none;
            margin: 20px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
        }
        
        .cash-payment-section.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #3d2d00;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ECB212;
            border-radius: 8px;
            font-size: 1.1rem;
            box-sizing: border-box;
        }
        
        .change-display {
            background: #3d2d00;
            color: #ECB212;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-top: 15px;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-confirm {
            flex: 1;
            padding: 15px;
            background: #3d2d00;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-confirm:hover {
            background: #ECB212;
            color: #3d2d00;
        }
        
        .btn-confirm:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .btn-cancel {
            flex: 1;
            padding: 15px;
            background: #95a5a6;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            background: #7f8c8d;
        }
    </style>
</head>
<body>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <button class="tab-btn active" onclick="switchTab('neworder')">
            <i class="fas fa-shopping-cart"></i> New Order
        </button>
        <button class="tab-btn" onclick="switchTab('payment')">
            <i class="fas fa-cash-register"></i> Process Payment
        </button>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="cashier-container">
        
        <!-- NEW ORDER TAB -->
        <div id="neworder-tab" class="tab-content active">
            <div class="neworder-main">
                <div class="outer">
                    <div class="inner-1">
                        <div class="innerimage">
                            <img src="logo.png" class="imagecafe" alt="Logo">
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
                                    ? $imgBase . 'uploads/products/' . htmlspecialchars($p['image'])
                                    : 'logo.png';
                                $idx++;
                            ?>
                            <div class="product-card" data-name="<?php echo $name; ?>" data-price="<?php echo $price; ?>" data-category="<?php echo htmlspecialchars($cat); ?>" style="--order: <?php echo $idx; ?>">
                                <img src="<?php echo $img; ?>" alt="">
                                <p class="product-name"><?php echo $name; ?></p>
                                <p class="product-price">₱<?php echo number_format($price, 2); ?></p>
                                <div class="quantity-controls">
                                    <button class="qty-btn minus">-</button>
                                    <span class="qty-number">0</span>
                                    <button class="qty-btn plus">+</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($products) === 0): ?>
                                <p style="grid-column:1/-1; text-align:center; padding:40px;">No products yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="inner-3">
                        <p class="cart-text">CURRENT ORDER</p>
                        <p id="timestamp" class="timestamp-text">No order active</p>
                        
                        <div style="margin: 15px 0;">
                            <label style="display:block; margin-bottom:5px; font-weight:bold; color:#3d2d00;">Customer Name:</label>
                            <input type="text" id="customerName" placeholder="e.g. Walk-in" style="width:100%; padding:8px; border:2px solid #ECB212; border-radius:6px;">
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <label style="display:block; margin-bottom:5px; font-weight:bold; color:#3d2d00;">Table #:</label>
                            <input type="text" id="tableNum" placeholder="Table number" value="1" style="width:100%; padding:8px; border:2px solid #ECB212; border-radius:6px;">
                        </div>
                        
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

        <!-- PAYMENT PROCESSING TAB -->
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
            
            <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <p><strong>Order #<span id="modalOrderId"></span></strong></p>
                <p><strong>Customer:</strong> <span id="modalCustomerName"></span></p>
                <p style="font-size: 1.5rem; color: #3d2d00; margin-top: 10px;"><strong>Total: ₱<span id="modalTotal"></span></strong></p>
            </div>
            
            <h4 style="margin-bottom: 15px; color: #3d2d00;">Select Payment Method:</h4>
            
            <div class="payment-method-btns">
                <div class="payment-method-btn" onclick="selectPaymentMethod('cash')">
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
    // Tab Switching
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        
        document.getElementById(tabName + '-tab').classList.add('active');
        event.target.classList.add('active');
    }

    // NEW ORDER LOGIC (Same as neworder.php)
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
        const customerName = document.getElementById("customerName").value.trim();
        const tableNum = document.getElementById("tableNum").value.trim();
        
        if (!customerName) {
            alert("Please enter customer name");
            return;
        }
        
        let count = 0;
        Object.keys(cart).forEach(name => { if (cart[name].qty > 0) count++; });
        if (count === 0) { alert("No items in order."); return; }
        
        const items = [];
        Object.keys(cart).forEach(name => {
            if (cart[name].qty > 0) items.push({ name: name, price: cart[name].price, qty: cart[name].qty });
        });
        
        this.disabled = true;
        this.textContent = "PLACING ORDER...";
        
        fetch("api/save_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                customer_name: customerName,
                table_num: tableNum || "1",
                items: items
            })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                alert("Order #" + d.order_id + " placed successfully!");
                cart = {};
                document.querySelectorAll(".qty-number").forEach(el => el.innerText = "0");
                updateReceipt();
                document.getElementById("customerName").value = "";
                document.getElementById("tableNum").value = "1";
                
                // Refresh payment tab
                location.reload();
            } else {
                alert(d.error || "Failed to place order");
            }
            this.disabled = false;
            this.textContent = "PLACE ORDER";
        })
        .catch(err => {
            console.error(err);
            alert("Failed to place order. Please try again.");
            this.disabled = false;
            this.textContent = "PLACE ORDER";
        });
    });

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
    let selectedPaymentMethod = null;

    function openPaymentModal(orderId, customerName, total) {
        currentOrderId = orderId;
        currentOrderTotal = total;
        selectedPaymentMethod = null;
        
        document.getElementById('modalOrderId').textContent = orderId;
        document.getElementById('modalCustomerName').textContent = customerName;
        document.getElementById('modalTotal').textContent = total.toFixed(2);
        
        // Reset form
        document.getElementById('cashReceived').value = '';
        document.getElementById('changeDisplay').textContent = 'Change: ₱0.00';
        document.getElementById('cashPaymentSection').classList.remove('active');
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
        selectedPaymentMethod = null;
    }

    function selectPaymentMethod(method) {
        selectedPaymentMethod = method;
        
        // Update UI
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('selected');
        });
        event.target.closest('.payment-method-btn').classList.add('selected');
        
        if (method === 'cash') {
            document.getElementById('cashPaymentSection').classList.add('active');
            document.getElementById('confirmPaymentBtn').disabled = true;
        } else {
            document.getElementById('cashPaymentSection').classList.remove('active');
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
        
        if (selectedPaymentMethod === 'cash') {
            const received = parseFloat(document.getElementById('cashReceived').value) || 0;
            const change = received - currentOrderTotal;
            
            if (change < 0) {
                alert('Insufficient payment amount');
                return;
            }
            
            if (confirm(`Confirm Cash Payment?\n\nReceived: ₱${received.toFixed(2)}\nChange: ₱${change.toFixed(2)}`)) {
                processPaymentAPI(currentOrderId, selectedPaymentMethod, received, change);
            }
        } else {
            if (confirm(`Confirm Online/Card Payment?\n\nTotal: ₱${currentOrderTotal.toFixed(2)}`)) {
                processPaymentAPI(currentOrderId, selectedPaymentMethod, currentOrderTotal, 0);
            }
        }
    }

    function processPaymentAPI(orderId, method, received, change) {
        fetch('api/process_payment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                order_id: orderId,
                payment_method: method,
                amount_received: received,
                change: change
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closePaymentModal();
                alert(`Payment processed successfully!\n\nPayment Method: ${method.toUpperCase()}${method === 'cash' ? '\nChange: ₱' + change.toFixed(2) : ''}`);
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
