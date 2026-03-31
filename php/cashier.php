<?php
session_start();

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'cashier' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

include "db.php";

$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Cashier';

$products = [];
$r = mysqli_query($conn, "SELECT * FROM products ORDER BY category, name");
if ($r) while ($row = mysqli_fetch_assoc($r)) $products[] = $row;

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
    <link rel="stylesheet" href="../style/cashier.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Cashier - Debug Café</title>

</head>
<body style="margin-top: 23px;">

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <a href="cashier.php" class="tab-btn active"><i class="fas fa-shopping-cart"></i> New Order</a>
        <a href="process_payment.php" class="tab-btn"><i class="fas fa-cash-register"></i> Process Payment</a>
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
                            <img src="../images/logo.png" class="imagecafe" alt="Logo">
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
                        
                        <!-- Customer Info Fields -->
                        <div class="customer-info-section">
                            <div class="form-group">
                                <label for="customerNameField">Customer Name:</label>
                                <input type="text" id="customerNameField" placeholder="Name" class="customer-input">
                            </div>
                            <div class="form-group">
                                <label for="tableNumField">Table #:</label>
                                <input type="text" id="tableNumField" placeholder="1" value="1" class="customer-input">
                            </div>
                        </div>
                        
                        <div class="divider"></div>
                        
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
            
            <div id="onlinePaymentSection" class="online-payment-section">
                <h4 style="color: #3d2d00; margin-top: 0;">Scan GCash QR Code</h4>
                <div class="qr-code-container">
                    <img src="gcash_qr.png" alt="GCash QR Code">
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

    <script src="../js/cashier.js"></script>
</body>
</html>
