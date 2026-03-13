<?php
header('Content-Type: application/json');
include __DIR__ . '/../db.php';

$sql = "SELECT id, name, price, image, category FROM products WHERE 1=1";
$result = mysqli_query($conn, $sql);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode($products);
