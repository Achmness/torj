# Optimized Ordering System - Implementation Guide

## Overview
Your café now has a modern ordering system similar to Jollibee/McDonald's where customers can order themselves and cashiers only handle payments.

---

## Database Changes

### New Orders Table Structure:
- `customer_id` (INT NULL) - The customer who placed the order (NULL for walk-ins)
- `processed_by` (INT NULL) - The cashier/staff who processed the payment
- `customer_name` (VARCHAR) - Name for display
- `table_num` (VARCHAR) - Table number
- `total` (DECIMAL) - Order total
- `status` (ENUM) - pending, preparing, ready, completed, cancelled
- `payment_status` (ENUM) - unpaid, paid
- `created_at` (TIMESTAMP) - When order was placed
- `updated_at` (TIMESTAMP) - Last update time

### Migration Steps:
1. Open phpMyAdmin
2. Select your database
3. Run the SQL from `migrate_optimized_orders.sql`

---

## System Flow

### 1. Customer Orders (Self-Service)
**URL:** `customer_order.php`

- Customer browses menu
- Adds items to cart
- Enters name and table number
- Places order
- Order status: `pending`, Payment: `unpaid`

### 2. Kitchen/Barista Prepares
**URL:** `barista.php`

- Sees new orders
- Updates status: `pending` → `preparing` → `ready`
- Payment still: `unpaid`

### 3. Cashier Processes Payment
**URL:** `orders.php`

- Sees all orders with payment status
- Customer comes to pay
- Clicks "Process Payment" button
- Order marked as `paid` and `completed`
- System records which cashier processed it

---

## New Files Created

1. **customer_order.php** - Customer self-ordering interface (like kiosk)
2. **api/get_products.php** - Fetches products for menu display
3. **api/process_payment.php** - Cashier payment processing endpoint
4. **migrate_optimized_orders.sql** - Database migration script

## Updated Files

1. **api/save_order.php** - Now handles both customer and cashier orders
2. **orders.php** - Shows payment status and payment processing button

---

## User Roles & Access

### Customer (Regular User)
- Can place orders via `customer_order.php`
- Orders linked to their account (`customer_id`)

### Walk-in Customer (No Account)
- Can still order via `customer_order.php`
- `customer_id` = NULL, just uses name

### Cashier
- Can take orders for walk-ins (existing `neworder.php`)
- Can process payments for all orders
- Their ID recorded in `processed_by`

### Barista
- Updates order preparation status
- No payment access

### Admin
- Full access to everything

---

## How to Use

### For Customers:
1. Go to `customer_order.php`
2. Browse menu and add items
3. Enter name and table number
4. Click "Place Order"
5. Go to cashier when ready to pay

### For Cashiers:
1. Go to `orders.php`
2. See all orders with payment status
3. When customer comes to pay:
   - Click "Process Payment" button
   - Order marked as paid and completed

### For Baristas:
1. Go to `barista.php`
2. See pending orders
3. Update status as you prepare items

---

## Benefits of This System

✅ Faster service - customers order themselves
✅ Less cashier workload - only handle payments
✅ Better tracking - know who ordered and who processed payment
✅ Scalable - can add kiosks, mobile app, etc.
✅ Modern experience - like major fast-food chains
✅ Still supports walk-ins - cashiers can take orders manually

---

## Next Steps (Optional Enhancements)

1. Add QR code ordering (scan table QR → order)
2. Add mobile app for ordering
3. Add order notifications (SMS/email when ready)
4. Add payment methods (cash, card, e-wallet)
5. Add customer order history
6. Add loyalty points system

---

## Testing Checklist

- [ ] Run migration SQL in phpMyAdmin
- [ ] Test customer ordering via `customer_order.php`
- [ ] Test barista updating order status
- [ ] Test cashier processing payment
- [ ] Test walk-in orders via `neworder.php`
- [ ] Verify payment status displays correctly
- [ ] Verify cashier ID is recorded when processing payment

---

## Support

If you encounter any issues:
1. Check database migration completed successfully
2. Verify all new files are uploaded
3. Check browser console for JavaScript errors
4. Verify user sessions are working correctly
