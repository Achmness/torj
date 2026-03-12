-- Run this SQL in phpMyAdmin to add user_id column to orders table
-- This allows tracking logged-in users while supporting walk-in orders

ALTER TABLE orders ADD COLUMN user_id INT NULL AFTER id;

-- Optional: Add foreign key constraint to link with users table
-- ALTER TABLE orders ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
