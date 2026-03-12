<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

include __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['items']) || !is_array($input['items'])) {
    echo json_encode(['success' => false, 'error' => 'No items in order']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? NULL;
$customer_name = trim($input['customer_name'] ?? 'Walk-in');
$table_num = trim($input['table_num'] ?? '1');
$items = $input['items'];

$total = 0;
foreach ($items as $it) {
    $qty = (int)($it['qty'] ?? 0);
    $price = (float)($it['price'] ?? 0);
    if ($qty > 0 && $price >= 0) $total += $qty * $price;
}

$stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, table_num, total, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("issd", $user_id, $customer_name, $table_num, $total);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Failed to create order']);
    exit;
}
$order_id = (int)$conn->insert_id;
$stmt->close();

// Create order_items table if it doesn't exist (stores product_name, price, quantity per order)
$r = mysqli_query($conn, "SHOW TABLES LIKE 'order_items'");
if (!$r || mysqli_num_rows($r) == 0) {
    mysqli_query($conn, "CREATE TABLE order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL
    )");
}

// Save line items (product_name, price, quantity)
$ins = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
foreach ($items as $it) {
    $qty = (int)($it['qty'] ?? 0);
    $price = (float)($it['price'] ?? 0);
    $name = trim($it['name'] ?? '');
    if ($qty > 0 && $name !== '') {
        $ins->bind_param("isdi", $order_id, $name, $price, $qty);
        $ins->execute();
    }
}
$ins->close();

echo json_encode(['success' => true, 'order_id' => $order_id]);
