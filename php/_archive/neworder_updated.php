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
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>New Order - Debug Café</title>
    <style>
        body { margin: 0; padding: 0; background: rgb(136, 136, 131); overflow-x: hidden; }
        
        .container {
            display: flex;
            min-height: 100vh;
            padding: 20px;
            padding-left: 240px;
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
            background: rgba(255,255,255,0.1);
        }
        
        .tab-btn.active {
            background: #AA7B39;
        }
        
        .back-to-dashboard {
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
        
        .back-to-dashboard:hover {
            background: #c0392b;
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
            width: 100%;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .neworder-main { 
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        .neworder-main .outer { margin: 0; }
        
        /* Payment Processing Section */
        .payment-section {
            width: 100%;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-sizing: border-box;
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
            align-items: flex-start;
            justify-content: center;
            overflow-y: auto;
            padding: 20px 0;
        }
        
        .payment-modal.open {
            display: flex;
        }
        
        .payment-modal-content {
            background: white;
            padding: 25px;
            border-radius: 12px;
            max-width: 550px;
            width: 90%;
            margin: auto;
        }
        
        .payment-modal-content h3 {
            color: #3d2d00;
            margin: 0 0 15px 0;
            font-size: 1.4rem;
        }
        
        .payment-method-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin: 15px 0;
        }
        
        .payment-method-btn {
            padding: 18px;
            background: #f9f9f9;
            border: 3px solid #ddd;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
            font-weight: bold;
            font-size: 1rem;
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
            font-size: 1.8rem;
            margin-bottom: 8px;
            color: #3d2d00;
        }
        
        .online-method-btn {
            padding: 12px;
            background: #f9f9f9;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .online-method-btn:hover {
            border-color: #ECB212;
            background: #fff;
        }
        
        .online-method-btn.selected {
            border-color: #ECB212;
            background: #fffbf0;
        }
        
        .online-method-btn i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 5px;
            color: #3d2d00;
        }
        
        .cash-payment-section {
            display: none;
            margin: 15px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 10px;
        }
        
        .cash-payment-section.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 12px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #3d2d00;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ECB212;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        
        .change-display {
            background: #3d2d00;
            color: #ECB212;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-top: 12px;
            font-size: 1.3rem;
            font-weight: bold;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-confirm {
            flex: 1;
            padding: 12px;
            background: #3d2d00;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1rem;
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
            padding: 12px;
            background: #95a5a6;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1rem;
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
        <a href="admin.php" class="back-to-dashboard">
            <i class="fas fa-arrow-left"></i> Back
        </a>
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
            
            <div style="background: #f9f9f9; padding: 12px; border-radius: 8px; margin-bottom: 12px;">
                <p style="margin: 3px 0;"><strong>Order #<span id="modalOrderId"></span></strong></p>
                <p style="margin: 3px 0;"><strong>Customer:</strong> <span id="modalCustomerName"></span></p>
                <p style="font-size: 1.1rem; color: #666; margin: 5px 0;"><strong>Original Total: ₱<span id="modalOriginalTotal"></span></strong></p>
                <div style="margin-top: 10px; padding: 12px; background: #fff; border-radius: 8px; border: 2px solid #ECB212;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #3d2d00; font-size: 0.9rem;">Discount (%):</label>
                    <input type="number" id="discountPercent" placeholder="Enter discount %" min="0" max="100" step="0.01" value="0" style="width: 100%; padding: 8px; border: 2px solid #E8E0D5; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
                    <p style="font-size: 0.85rem; color: #666; margin: 5px 0 0 0;">Discount Amount: ₱<span id="discountAmount">0.00</span></p>
                </div>
                <p style="font-size: 1.3rem; color: #3d2d00; margin: 8px 0 0 0; font-weight: bold;"><strong>Final Total: ₱<span id="modalTotal"></span></strong></p>
            </div>
            
            <h4 style="margin: 12px 0 8px 0; color: #3d2d00; font-size: 1.1rem;">Select Payment Method:</h4>
            
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
            
            <div id="onlinePaymentSection" class="cash-payment-section">
                <h4 style="margin: 0 0 10px 0; color: #3d2d00; font-size: 1rem;">Choose Online Payment:</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                    <div class="online-method-btn" onclick="selectOnlineMethod('card')">
                        <i class="fas fa-credit-card"></i>
                        <span>Card</span>
                    </div>
                    <div class="online-method-btn" onclick="selectOnlineMethod('gcash')">
                        <i class="fas fa-mobile-alt"></i>
                        <span>GCash</span>
                    </div>
                    <div class="online-method-btn" onclick="selectOnlineMethod('paymaya')">
                        <i class="fas fa-wallet"></i>
                        <span>PayMaya</span>
                    </div>
                </div>
                <input type="hidden" id="selectedOnlineMethod" value="">
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

    <div id="placeOrderModal" class="order-modal">
        <div class="order-modal-content">
            <h3>Place Order</h3>
            <form id="placeOrderForm">
                <label>Customer Name <input type="text" id="customerName" placeholder="e.g. Walk-in" required></label>
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

    <script src="neworder_script.js"></script>
</body>
</html>
