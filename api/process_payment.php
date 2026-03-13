<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Only cashiers and admins can process payments
$user_role = $_SESSION['role'] ?? '';
if ($user_role !== 'cashier' && $user_role !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

include __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);
$order_id = (int)($input['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

// Check if new columns exist
$check = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'payment_status'");
$has_payment_status = mysqli_num_rows($check) > 0;

$check2 = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'processed_by'");
$has_processed_by = mysqli_num_rows($check2) > 0;

$check3 = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'updated_at'");
$has_updated_at = mysqli_num_rows($check3) > 0;

// Update order based on available columns
$cashier_id = $_SESSION['user_id'];

if ($has_payment_status && $has_processed_by && $has_updated_at) {
    // New system with all columns
    $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid', processed_by = ?, status = 'completed', updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ii", $cashier_id, $order_id);
} else {
    // Old system - just update status
    $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
}

if ($stmt->execute()) {
    if (!$has_payment_status || !$has_processed_by) {
        echo json_encode([
            'success' => true, 
            'message' => 'Payment processed successfully',
            'warning' => 'Please run migrate_optimized_orders.sql to enable full features'
        ]);
    } else {
        echo json_encode(['success' => true, 'message' => 'Payment processed successfully']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to process payment: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
