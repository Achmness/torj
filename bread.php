<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Debug Cafe | Bread and Pastries</title>
</head>
<body>
<div class="outer">
    <div class="inner-1">
        <div class="innerimage">
            <img src="logo.png" class="imagecafe" alt="Logo">
            <p class="nav-header">PRODUCTS</p>
            <div class="prod">
                <p class="nav-item" onclick="window.location.href='home.php'">Hot Drinks</p>
                <p class="nav-item" onclick="window.location.href='colddrinks.php'">Cold Drinks</p>
                <p class="nav-item active">Bread and Pastries</p>
            </div>
        </div>
    </div>

    <div class="inner-2">
        <div class="product-grid">
            <script>
const products = [
    { name: "Sourdough Bread", price: 65, img: "bread-1.jpg" },
    { name: "Pain Au Levain", price: 45, img: "bread-2.jpg" },
    { name: "Croissant", price: 70, img: "bread-3.jpg" },
    { name: "Simit", price: 55, img: "bread-4.jpg" },
    { name: "Sliced Bread", price: 50, img: "bread-5.jpg" },
    { name: "Babka", price: 40, img: "bread-6.jpg" },
    { name: "Banana Bread", price: 25, img: "bread-7.jpg" },
    { name: "Melonpan", price: 60, img: "bread-8.jpg" },
    { name: "Hot Cross Bun", price: 75, img: "bread-9.jpg" }
];

const grid = document.querySelector('.product-grid');

products.forEach((prod, i) => {
    grid.innerHTML += `
    <div class="product-card" 
         data-name="${prod.name}" 
         data-price="${prod.price}" 
         style="--order: ${i+1}">
         
        <img src="${prod.img}">
        <p class="product-name">${prod.name}</p>
        <p class="product-price">₱${prod.price.toFixed(2)}</p>

        <div class="quantity-controls">
            <button class="qty-btn minus">-</button>
            <span class="qty-number">0</span>
            <button class="qty-btn plus">+</button>
        </div>

    </div>
    `;
});
</script>
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
            <button class="checkout-btn" id="printBtn">PRINT RECEIPT</button>
        </div>
    </div>
</div>

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
document.getElementById("printBtn").addEventListener("click", function () {

    if (Object.keys(cart).length === 0) {
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