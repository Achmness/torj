# Cashier Page - User Guide

## Overview
The new `cashier.php` is a unified interface for cashiers to handle both taking orders and processing payments.

---

## 🎯 Features

### 1. **Two-Tab Interface**
- **New Order Tab** - Take orders from walk-in customers
- **Process Payment Tab** - Process payments for pending orders

### 2. **New Order Tab**
Same interface as `neworder.php`:
- Browse products by category (Hot, Cold, Bread)
- Add items with +/- buttons
- Enter customer name and table number
- Place order
- Print receipt

### 3. **Process Payment Tab**
Shows all unpaid orders with:
- Large order number (#123)
- Customer name
- Table number
- Order status (pending/preparing/ready)
- Total amount
- List of items
- "Process Payment" button
- "View Details" link

---

## 📋 Cashier Workflow

### Scenario 1: Walk-in Customer Orders at Counter

1. Customer comes to cashier
2. Cashier clicks **"New Order"** tab
3. Selects items customer wants
4. Enters customer name and table number
5. Clicks **"Place Order"**
6. Order is created as UNPAID
7. Cashier immediately processes payment:
   - Clicks **"Process Payment"** tab
   - Finds the order (should be at top)
   - Clicks **"Process Payment"** button
   - Order marked as PAID

### Scenario 2: Customer Ordered via customer_order.php

1. Customer already placed order online
2. Customer comes to cashier with order number
3. Cashier clicks **"Process Payment"** tab
4. Finds order by number (e.g., #123)
5. Verifies items and total with customer
6. Clicks **"Process Payment"** button
7. Order marked as PAID

---

## 🔄 Order Status Flow

```
Customer Orders → pending (unpaid)
                    ↓
Barista Prepares → preparing (unpaid)
                    ↓
Order Ready     → ready (unpaid)
                    ↓
Cashier Payment → completed (PAID) ✓
```

---

## 💡 Key Points

1. **Orders can be created two ways:**
   - Cashier takes order (New Order tab)
   - Customer self-orders (customer_order.php)

2. **All unpaid orders appear in Process Payment tab**
   - Regardless of who created them
   - Sorted by newest first
   - Shows up to 20 recent unpaid orders

3. **Payment processing is one-click:**
   - Click "Process Payment" button
   - Confirm the action
   - Order automatically marked as paid and completed

4. **Order details available:**
   - Click "View Details" to see full order information
   - Links to order_detail.php

---

## 🖥️ Access

**URL:** `http://localhost/torj/cashier.php`

**Login Required:** Yes (Cashier or Admin role)

**Redirects:**
- Cashiers automatically redirected here after login
- Non-cashiers redirected to login page

---

## 🎨 UI Features

- **Tab navigation** at top left
- **Logout button** at top right
- **Color-coded badges:**
  - Orange = Pending
  - Blue = Preparing
  - Green = Ready
  - Red = Unpaid

- **Large order numbers** for easy identification
- **Hover effects** on order cards
- **Responsive design** works on tablets

---

## 🔧 Technical Details

**Database Queries:**
- Fetches products for ordering
- Fetches unpaid orders with item summaries
- Joins with order_items table

**API Endpoints Used:**
- `api/save_order.php` - Create new orders
- `api/process_payment.php` - Process payments

**Session Requirements:**
- Must be logged in
- Role must be 'cashier' or 'admin'

---

## ✅ Testing Checklist

- [ ] Login as cashier
- [ ] Create new order in New Order tab
- [ ] Verify order appears in Process Payment tab
- [ ] Process payment for the order
- [ ] Verify order disappears from unpaid list
- [ ] Test with customer-created order
- [ ] Print receipt from New Order tab
- [ ] View order details link works

---

## 🆚 Comparison with Old System

### Before:
- `home.php` - Basic cashier page
- `neworder.php` - Only for taking orders
- `orders.php` - Admin only, for viewing orders

### After:
- `cashier.php` - Unified interface
  - Take orders (New Order tab)
  - Process payments (Process Payment tab)
  - All in one place!

---

## 🎉 Benefits

1. **Faster workflow** - Everything in one page
2. **Less confusion** - Clear separation of tasks
3. **Better UX** - Tab interface is intuitive
4. **Real-time updates** - See unpaid orders immediately
5. **Consistent design** - Matches neworder.php style

---

**The cashier page is now ready to use!** 🚀
