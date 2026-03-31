<?php
session_start();
include "db.php";

$is_logged_in = isset($_SESSION['user_id']);
$customer_name = $is_logged_in ? $_SESSION['fullname'] : '';

$products = [];
$r = mysqli_query($conn, "SELECT * FROM products ORDER BY category, name");
if ($r) while ($row = mysqli_fetch_assoc($r)) $products[] = $row;

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
    <link rel="stylesheet" href="../style/customer_order.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Place Order - Debug Café</title>
    <style>
        /* Customer Info Section Styling */
        .customer-info-section {
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 0.8rem;
        }
        
        .form-group label {
            display: block;
            font-weight: 700;
            color: #3d2d00;
            margin-bottom: 0.3rem;
            font-size: 0.95rem;
        }
        
        .customer-input {
            width: 100%;
            padding: 0.7rem;
            border: 2px solid #ECB212;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            background: rgba(232, 224, 213, 0.3);
            transition: all 0.3s;
        }
        
        .customer-input:focus {
            outline: none;
            border-color: #8B6F47;
            background: white;
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.1);
        }
        
        .divider {
            height: 2px;
            background: linear-gradient(to right, transparent, #3d2d00, transparent);
            margin: 1rem 0;
        }
    </style>

</head>
<body style="padding-top: 32px;">
    <div class="backto">
    <a href="index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i>
        Back to Home
    </a>
    </div>
    
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
                    <div class="product-card" data-id="<?php echo $pid; ?>" data-name="<?php echo $name; ?>" data-price="<?php echo $price; ?>" data-category="<?php echo htmlspecialchars($cat); ?>" style="--order: <?php echo $idx; ?>">
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
                <p class="cart-text">YOUR ORDER</p>
                <p id="timestamp" class="timestamp-text">Select items to begin...</p>
                
                <!-- Customer Info Fields -->
                <div class="customer-info-section">
                    <div class="form-group">
                        <label for="customerNameField">Name:</label>
                        <input type="text" id="customerNameField" value="<?php echo htmlspecialchars($customer_name); ?>" placeholder="Enter your name" class="customer-input">
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
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div id="receiptModal" class="receipt-modal">
        <div class="receipt-content">
            <h2>🎉 Order Placed Successfully!</h2>
            
            <div class="order-number" id="orderNumberDisplay">#0</div>
            
            <p class="instruction-text">
                <strong>Show this number to the cashier for payment</strong>
            </p>
            
            <div class="receipt-details" id="receiptDetails"></div>
            
            <div>
                <button class="btn-print" onclick="printReceipt()">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <button class="btn-close" onclick="closeReceipt()">Close</button>
            </div>
        </div>
    </div>

    <script>
    const receiptContainer = document.getElementById('receipt-items');
    const totalDisplay = document.getElementById('grand-total');
    const timestampDisplay = document.getElementById('timestamp');
    let cart = {};
    let currentCategory = 'hot';
    let lastOrderId = null;
    let lastOrderData = null;

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
        Object.keys(cart).forEach(id => {
            if (cart[id].qty > 0) {
                count++;
                const itemTotal = cart[id].qty * cart[id].price;
                total += itemTotal;
                const row = document.createElement('div');
                row.className = 'receipt-row';
                row.innerHTML = `<span>${cart[id].name} x${cart[id].qty}</span><span>₱${itemTotal.toFixed(2)}</span>`;
                receiptContainer.appendChild(row);
            }
        });
        if (count === 0) {
            receiptContainer.innerHTML = '<p class="empty-msg">Select items to begin...</p>';
            timestampDisplay.innerText = "Select items to begin...";
        } else {
            timestampDisplay.innerText = "Date: " + new Date().toLocaleString();
        }
        totalDisplay.innerText = `TOTAL: ₱${total.toFixed(2)}`;
    }

    document.querySelectorAll('.product-card').forEach(card => {
        const id = card.getAttribute('data-id');
        const name = card.getAttribute('data-name');
        const price = parseFloat(card.getAttribute('data-price'));
        const qtyDisplay = card.querySelector('.qty-number');
        
        card.querySelector('.plus').addEventListener('click', () => {
            if (!cart[id]) cart[id] = { name, price, qty: 0 };
            cart[id].qty++;
            qtyDisplay.innerText = cart[id].qty;
            updateReceipt();
        });
        
        card.querySelector('.minus').addEventListener('click', () => {
            if (cart[id] && cart[id].qty > 0) {
                cart[id].qty--;
                qtyDisplay.innerText = cart[id].qty;
                if (cart[id].qty === 0) delete cart[id];
                updateReceipt();
            }
        });
    });

    document.getElementById("placeOrderBtn").addEventListener("click", function () {
        const customerName = document.getElementById("customerNameField").value.trim();
        const tableNum = document.getElementById("tableNumField").value.trim();
        
        if (!customerName) {
            alert("Please enter your name");
            document.getElementById("customerNameField").focus();
            return;
        }
        
        if (!tableNum) {
            alert("Please enter table number");
            document.getElementById("tableNumField").focus();
            return;
        }
        
        let count = 0;
        Object.keys(cart).forEach(id => { if (cart[id].qty > 0) count++; });
        if (count === 0) { alert("No items in order."); return; }
        
        const items = [];
        Object.keys(cart).forEach(id => {
            if (cart[id].qty > 0) items.push({ name: cart[id].name, price: cart[id].price, qty: cart[id].qty });
        });
        
        this.disabled = true;
        this.textContent = "PLACING ORDER...";
        
        fetch("../api/save_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                customer_name: customerName,
                table_num: tableNum,
                order_type: "customer",
                items: items
            })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                // Store order data for printing
                lastOrderId = d.order_id;
                lastOrderData = {
                    customerName: customerName,
                    tableNum: tableNum,
                    items: items,
                    timestamp: new Date().toLocaleString()
                };
                
                // Show receipt modal
                showReceipt(d.order_id, customerName, tableNum, items);
                
                // Reset cart
                cart = {};
                document.querySelectorAll(".qty-number").forEach(el => el.innerText = "0");
                updateReceipt();
                document.getElementById("customerNameField").value = "<?php echo $customer_name; ?>";
                document.getElementById("tableNumField").value = "1";
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

    function showReceipt(orderId, customerName, tableNum, items) {
        document.getElementById('orderNumberDisplay').textContent = '#' + orderId;
        
        let total = 0;
        let detailsHTML = `
            <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 2px dashed #ddd;">
                <p><strong>Customer:</strong> ${customerName}</p>
                <p><strong>Table:</strong> ${tableNum}</p>
                <p><strong>Date:</strong> ${new Date().toLocaleString()}</p>
            </div>
        `;
        
        items.forEach(item => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;
            detailsHTML += `
                <div class="receipt-item">
                    <span>${item.name} x${item.qty}</span>
                    <span>₱${itemTotal.toFixed(2)}</span>
                </div>
            `;
        });
        
        detailsHTML += `
            <div class="receipt-item">
                <span>TOTAL</span>
                <span>₱${total.toFixed(2)}</span>
            </div>
        `;
        
        document.getElementById('receiptDetails').innerHTML = detailsHTML;
        document.getElementById('receiptModal').classList.add('open');
    }

    function closeReceipt() {
        document.getElementById('receiptModal').classList.remove('open');
    }

    function printReceipt() {
        if (!lastOrderId || !lastOrderData) {
            alert("No order to print");
            return;
        }
        
        // Build receipt content exactly like neworder.php
        let receiptContent = `<h2>The Debug Café</h2>`;
        receiptContent += `<p><strong>Order #${lastOrderId}</strong></p>`;
        receiptContent += `<p>${lastOrderData.timestamp}</p>`;
        receiptContent += `<p>Customer: ${lastOrderData.customerName}</p>`;
        receiptContent += `<p>Table: ${lastOrderData.tableNum}</p>`;
        receiptContent += `<hr>`;
        
        let total = 0;
        lastOrderData.items.forEach(item => {
            let itemTotal = item.qty * item.price;
            total += itemTotal;
            receiptContent += `<p>${item.name} x${item.qty} — ₱${itemTotal.toFixed(2)}</p>`;
        });
        
        receiptContent += `<hr><h3>Total: ₱${total.toFixed(2)}</h3>`;
        receiptContent += `<p style="margin-top:20px;">Please show this receipt to the cashier for payment.</p>`;
        receiptContent += `<p>Thank you for visiting!</p>`;
        
        // Open new window and print (same as neworder.php)
        let printWindow = window.open('', '', 'width=400,height=600');
        printWindow.document.write(`<html><head><title>Receipt - Order #${lastOrderId}</title><style>body{font-family:monospace;padding:20px;text-align:center;}hr{border:1px dashed black;}h2{margin-bottom:10px;}p{margin:5px 0;}</style></head><body>${receiptContent}</body></html>`);
        printWindow.document.close();
        printWindow.print();
    }

    document.getElementById('receiptModal').addEventListener('click', function(e) {
        if (e.target === this) closeReceipt();
    });
</script>

</body>
</html>
