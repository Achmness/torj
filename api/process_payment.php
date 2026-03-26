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

include __DIR__ . '/../php/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$order_id = (int)($input['order_id'] ?? 0);
$payment_method = $input['payment_method'] ?? 'cash';
$online_payment_method = $input['online_payment_method'] ?? null;
$amount_received = (float)($input['amount_received'] ?? 0);
$change = (float)($input['change'] ?? 0);
$discount_percent = (float)($input['discount_percent'] ?? 0);
$discount_amount = (float)($input['discount_amount'] ?? 0);
$final_amount = (float)($input['final_amount'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

// Get order details
$order_query = mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id");
if (!$order_query || mysqli_num_rows($order_query) === 0) {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}
$order = mysqli_fetch_assoc($order_query);

// Check if new columns exist
$check = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'payment_status'");
$has_payment_status = mysqli_num_rows($check) > 0;

$check2 = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'processed_by'");
$has_processed_by = mysqli_num_rows($check2) > 0;

$check3 = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'updated_at'");
$has_updated_at = mysqli_num_rows($check3) > 0;

$check4 = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'payment_method'");
$has_payment_method = mysqli_num_rows($check4) > 0;

$check5 = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'discount_percent'");
$has_discount = mysqli_num_rows($check5) > 0;

// PayMongo Integration for online payments
if ($payment_method === 'online' && $online_payment_method) {
    // PayMongo API Configuration
    $paymongo_secret_key = 'sk_test_your_secret_key_here'; // Replace with your actual secret key
    
    // Create PayMongo Payment Intent
    $amount_in_centavos = (int)($final_amount * 100); // Convert to centavos
    
    // Set payment method based on selection
    $payment_methods_allowed = [];
    if ($online_payment_method === 'card') {
        $payment_methods_allowed = ['card'];
    } elseif ($online_payment_method === 'gcash') {
        $payment_methods_allowed = ['gcash'];
    } elseif ($online_payment_method === 'paymaya') {
        $payment_methods_allowed = ['paymaya'];
    } else {
        $payment_methods_allowed = ['card', 'gcash', 'paymaya'];
    }
    
    $payment_intent_data = [
        'data' => [
            'attributes' => [
                'amount' => $amount_in_centavos,
                'payment_method_allowed' => $payment_methods_allowed,
                'payment_method_options' => [
                    'card' => ['request_three_d_secure' => 'any']
                ],
                'currency' => 'PHP',
                'description' => 'Order #' . $order_id . ' - ' . $order['customer_name'] . ' (' . strtoupper($online_payment_method) . ')',
                'statement_descriptor' => 'Debug Cafe Order'
            ]
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.paymongo.com/v1/payment_intents');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_intent_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($paymongo_secret_key . ':')
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200 && $http_code !== 201) {
        // If PayMongo fails, log it but continue with manual processing
        error_log('PayMongo API Error: ' . $response);
        // Continue with manual processing below
    } else {
        $payment_response = json_decode($response, true);
        // Store payment intent ID for reference
        $payment_intent_id = $payment_response['data']['id'] ?? null;
    }
}

// Update order based on available columns.
// Key goal: if `payment_status` exists, always set it to `paid` so the order disappears from "pending payments".
$cashier_id = $_SESSION['user_id'];

$setParts = [];
$setParts[] = "status = 'completed'";

if ($has_payment_status) $setParts[] = "payment_status = 'paid'";
if ($has_updated_at) $setParts[] = "updated_at = NOW()";

$types = '';
$params = [];

if ($has_processed_by) {
    $setParts[] = "processed_by = ?";
    $types .= 'i';
    $params[] = $cashier_id;
}

if ($has_payment_method) {
    $setParts[] = "payment_method = ?";
    $types .= 's';
    $params[] = $payment_method;
}

if ($has_discount) {
    $setParts[] = "discount_percent = ?";
    $types .= 'd';
    $params[] = $discount_percent;

    $setParts[] = "discount_amount = ?";
    $types .= 'd';
    $params[] = $discount_amount;
}

// `total` is required because `orders.total` is used everywhere in your UI.
$setParts[] = "total = ?";
$types .= 'd';
$params[] = $final_amount;

$sql = "UPDATE orders SET " . implode(', ', $setParts) . " WHERE id = ?";
$types .= 'i';
$params[] = $order_id;

$stmt = $conn->prepare($sql);

// bind_param requires references when using call_user_func_array
$bindRefs = [];
foreach ($params as $i => $val) {
    $bindRefs[$i] = &$params[$i];
}
call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $bindRefs));

if ($stmt->execute()) {
    $response_data = [
        'success' => true, 
        'message' => 'Payment processed successfully',
        'payment_method' => $payment_method,
        'original_amount' => $order['total'],
        'discount_percent' => $discount_percent,
        'discount_amount' => $discount_amount,
        'final_amount' => $final_amount
    ];
    
    if ($payment_method === 'cash') {
        $response_data['amount_received'] = $amount_received;
        $response_data['change'] = $change;
    }
    
    if (isset($payment_intent_id)) {
        $response_data['paymongo_payment_id'] = $payment_intent_id;
    }
    
    if (!$has_payment_status || !$has_processed_by) {
        $response_data['warning'] = 'Some payment columns are missing (you may need migration).';
    }
    
    echo json_encode($response_data);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to process payment: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
