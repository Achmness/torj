
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
let selectedOnlinePaymentMethod = null;

function openPaymentModal(orderId, customerName, total) {
    currentOrderId = orderId;
    currentOrderTotal = total;
    originalOrderTotal = total;
    selectedPaymentMethod = null;
    selectedOnlinePaymentMethod = null;
    
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
    document.getElementById('selectedOnlineMethod').value = '';
    document.getElementById('confirmPaymentBtn').disabled = true;
    
    // Remove selected class from all payment methods
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    document.querySelectorAll('.online-method-btn').forEach(btn => {
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
    selectedOnlinePaymentMethod = null;
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
        document.getElementById('confirmPaymentBtn').disabled = true;
    }
}

function selectOnlineMethod(method) {
    selectedOnlinePaymentMethod = method;
    document.getElementById('selectedOnlineMethod').value = method;
    
    // Update UI
    document.querySelectorAll('.online-method-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    event.target.closest('.online-method-btn').classList.add('selected');
    
    document.getElementById('confirmPaymentBtn').disabled = false;
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
            processPaymentAPI(currentOrderId, 'cash', received, change, discountPercent, discountAmount, currentOrderTotal, null);
        }
    } else {
        if (!selectedOnlinePaymentMethod) {
            alert('Please select an online payment method (Card, GCash, or PayMaya)');
            return;
        }
        
        let confirmMsg = `Confirm ${selectedOnlinePaymentMethod.toUpperCase()} Payment?\n\nOriginal Total: ₱${originalOrderTotal.toFixed(2)}`;
        if (discountPercent > 0) {
            confirmMsg += `\nDiscount (${discountPercent}%): -₱${discountAmount.toFixed(2)}`;
        }
        confirmMsg += `\nFinal Total: ₱${currentOrderTotal.toFixed(2)}`;
        
        if (confirm(confirmMsg)) {
            processPaymentAPI(currentOrderId, 'online', currentOrderTotal, 0, discountPercent, discountAmount, currentOrderTotal, selectedOnlinePaymentMethod);
        }
    }
}

function processPaymentAPI(orderId, method, received, change, discountPercent, discountAmount, finalAmount, onlineMethod) {
    fetch('api/process_payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            order_id: orderId,
            payment_method: method,
            online_payment_method: onlineMethod,
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
            if (onlineMethod) {
                successMsg += ` (${onlineMethod.toUpperCase()})`;
            }
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
