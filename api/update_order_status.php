<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'barista')) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

include "../php/db.php";

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

$order_id = (int)$data['order_id'];
$status = mysqli_real_escape_string($conn, $data['status']);

$allowed_statuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit();
}

// Check if updated_at column exists
$check = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'updated_at'");
$has_updated_at = mysqli_num_rows($check) > 0;

if ($has_updated_at) {
    $query = "UPDATE orders SET status = '$status', updated_at = NOW() WHERE id = $order_id";
} else {
    $query = "UPDATE orders SET status = '$status' WHERE id = $order_id";
}

$result = mysqli_query($conn, $query);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Order status updated']);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
}
?>
