<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'cashier')) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

include "../php/db.php";

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing order ID']);
    exit();
}

$order_id = (int)$_GET['id'];

// Get order details
$order_query = "SELECT * FROM orders WHERE id = $order_id";
$order_result = mysqli_query($conn, $order_query);

if (!$order_result || mysqli_num_rows($order_result) === 0) {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Get order items
$items = [];
$items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);

if ($items_result) {
    while ($row = mysqli_fetch_assoc($items_result)) {
        $items[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items
]);
?>
