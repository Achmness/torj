<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'barista') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

include "../db.php";

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing order_id']);
    exit();
}

$order_id = (int)$data['order_id'];

// Update order status to completed
$query = "UPDATE orders SET status = 'completed' WHERE id = $order_id";
$result = mysqli_query($conn, $query);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Order completed successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
}
?>
