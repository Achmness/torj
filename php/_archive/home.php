<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Cashier';

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
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>New Order - Debug Café</title>
    <style>
        body { margin: 0; padding: 0; background: rgb(136, 136, 131); }
        .neworder-main { 
            min-height: 100vh; 
            padding: 20px; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
        }
        .neworder-main .outer { margin: 0; }
    </style>
</head>
<body>
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
                    <a href="logout.php" class="nav-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
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
                        <p style="grid-column:1/-1; text-align:center; padding:40px;">No products yet.</p>
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
    
    /* Logout button styling */
    .nav-logout {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 1rem 0;
        margin-top: 380px;
        margin-left: 25px;
        color: #E8E0D5;
        text-decoration: none;
        transition: all 0.3s;
        border-top: 1px solid rgba(232, 224, 213, 0.2);
        
    }
    
    .nav-logout:hover {
        color: #ff5252;
    }
    
    .nav-logout i {
        font-size: 1rem;
    }
    </style>

    <script>
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
    </script>
</body>
</html>
