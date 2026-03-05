<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Debug Cafe | Hot Drinks</title>
</head>
<body>
<div class="outer">
    <div class="inner-1">
        <div class="innerimage">
            <img src="logo.png" class="imagecafe" alt="Logo">
            <p class="nav-header">PRODUCTS</p>
            <div class="prod">
                <p class="nav-item active">Hot Drinks</p>
                <p class="nav-item" onclick="window.location.href='colddrinks.php'">Cold Drinks</p>
                <p class="nav-item" onclick="window.location.href='bread.php'">Bread and Pastries</p>
            </div>
        </div>
    </div>

    <div class="inner-2">
        <div class="product-grid">
            <div class="product-card" data-name="Cappuccino" data-price="145" style="--order: 1">
                <img src="h1.jpg"><p class="product-name">Cappuccino</p><p class="product-price">₱145.00</p>
                <div class="quantity-controls"><button class="qty-btn minus">-</button><span class="qty-number">0</span><button class="qty-btn plus">+</button></div>
            </div>
            <div class="product-card" data-name="Matcha" data-price="160" style="--order: 2">
                <img src="h2.jpg"><p class="product-name">Matcha</p><p class="product-price">₱160.00</p>
                <div class="quantity-controls"><button class="qty-btn minus">-</button><span class="qty-number">0</span><button class="qty-btn plus">+</button></div>
            </div>
            <div class="product-card" data-name="Cinnamon" data-price="135" style="--order: 3">
                <img src="h3.jpg"><p class="product-name">Cinnamon</p><p class="product-price">₱135.00</p>
                <div class="quantity-controls"><button class="qty-btn minus">-</button><span class="qty-number">0</span><button class="qty-btn plus">+</button></div>
            </div>
            <div class="product-card" data-name="Chocolate" data-price="120" style="--order: 4">
                <img src="h4.jpg"><p class="product-name">Chocolate</p><p class="product-price">₱120.00</p>
                <div class="quantity-controls"><button class="qty-btn minus">-</button><span class="qty-number">0</span><button class="qty-btn plus">+</button></div>
            </div>
            <div class="product-card" data-name="Melted Mallows" data-price="150" style="--order: 5">
                <img src="h5.jpg"><p class="product-name">Melted Mallows</p><p class="product-price">₱150.00</p>
                <div class="quantity-controls"><button class="qty-btn minus">-</button><span class="qty-number">0</span><button class="qty-btn plus">+</button></div>
            </div>
            <div class="product-card" data-name="Gingerbread" data-price="155" style="--order: 6">
                <img src="h6.jpg"><p class="product-name">Gingerbread</p><p class="product-price">₱155.00</p>
                <div class="quantity-controls"><button class="qty-btn minus">-</button><span class="qty-number">0</span><button class="qty-btn plus">+</button></div>
            </div>
            <div class="product-card" data-name="Ginger tea" data-price="95" style="--order: 7">
                <img src="h7.jpg"><p class="product-name">Ginger tea</p><p class="product-price">₱95.00</p>
                <div class="quantity-controls"><button class="qty-btn minus">-</button><span class="qty-number">0</span><button class="qty-btn plus">+</button></div>
            </div>
            <div class="product-card" data-name="Choc-Milk Latte" data-price="140" style="--order: 8">
                <img src="h8.jpg"><p class="product-name">Choc-Milk Latte</p><p class="product-price">₱140.00</p>
                <div class="quantity-controls"><button class="qty-btn minus">-</button><span class="qty-number">0</span><button class="qty-btn plus">+</button></div>
            </div>
            <div class="product-card" data-name="Dreamy Night" data-price="130" style="--order: 9">
                <img src="h9.jpg"><p class="product-name">Dreamy Night</p><p class="product-price">₱130.00</p>
                <div class="quantity-controls"><button class="qty-btn minus">-</button><span class="qty-number">0</span><button class="qty-btn plus">+</button></div>
            </div>
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
</style>

<script>
    const receiptContainer = document.getElementById('receipt-items');
    const totalDisplay = document.getElementById('grand-total');
    const timestampDisplay = document.getElementById('timestamp');
    let cart = {};

    function updateReceipt() {
        receiptContainer.innerHTML = '';
        let total = 0;
        let count = 0;
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
</script>

<script>
document.getElementById("placeOrderBtn").addEventListener("click", function () {
    let count = 0;
    Object.keys(cart).forEach(name => { if (cart[name].qty > 0) count++; });
    if (count === 0) {
        alert("No items in order.");
        return;
    }
    document.getElementById("placeOrderModal").classList.add("open");
});
document.getElementById("placeOrderForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const items = [];
    Object.keys(cart).forEach(name => {
        if (cart[name].qty > 0) {
            items.push({ name: name, price: cart[name].price, qty: cart[name].qty });
        }
    });
    fetch("api/save_order.php", {
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
    });
});
function closePlaceOrderModal() {
    document.getElementById("placeOrderModal").classList.remove("open");
}
document.getElementById("placeOrderModal").addEventListener("click", function(e) {
    if (e.target === this) closePlaceOrderModal();
});

document.getElementById("printBtn").addEventListener("click", function () {

    let count = 0;
    Object.keys(cart).forEach(name => { if (cart[name].qty > 0) count++; });
    if (count === 0) {
        alert("No items in order.");
        return;
    }

    let receiptContent = `
        <h2>The Debug Café</h2>
        <p>${new Date().toLocaleString()}</p>
        <hr>
    `;

    let total = 0;

    Object.keys(cart).forEach(name => {
        if (cart[name].qty > 0) {
            let itemTotal = cart[name].qty * cart[name].price;
            total += itemTotal;

            receiptContent += `
                <p>${name} x${cart[name].qty} — ₱${itemTotal.toFixed(2)}</p>
            `;
        }
    });

    receiptContent += `
        <hr>
        <h3>Total: ₱${total.toFixed(2)}</h3>
        <p>Thank you for visiting!</p>
    `;

    let printWindow = window.open('', '', 'width=400,height=600');
    printWindow.document.write(`
        <html>
        <head>
            <title>Receipt</title>
            <style>
                body {
                    font-family: monospace;
                    padding: 20px;
                    text-align: center;
                }
                hr {
                    border: 1px dashed black;
                }
            </style>
        </head>
        <body>
            ${receiptContent}
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.print();
});
</script>
</body>
</html>