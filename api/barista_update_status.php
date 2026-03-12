<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'barista') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

include "../db.php";

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

$query = "UPDATE orders SET status = '$status' WHERE id = $order_id";
$result = mysqli_query($conn, $query);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Order status updated']);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
}
?>
