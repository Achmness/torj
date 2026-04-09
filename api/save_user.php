<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

include "../php/db.php";

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['fullname']) || !isset($data['email']) || !isset($data['role'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

$user_id = $data['user_id'] ?? null;
$fullname = mysqli_real_escape_string($conn, trim($data['fullname']));
$email = mysqli_real_escape_string($conn, trim($data['email']));
$role = mysqli_real_escape_string($conn, $data['role']);
$password = trim($data['password'] ?? '');

// Validate role
$allowed_roles = ['customer', 'cashier', 'barista', 'admin'];
if (!in_array($role, $allowed_roles)) {
    echo json_encode(['success' => false, 'error' => 'Invalid role']);
    exit();
}

if ($user_id) {
    // Update existing user
    if (!empty($password)) {
        // Update with new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET fullname = '$fullname', email = '$email', password = '$hashed_password', role = '$role' WHERE id = " . (int)$user_id;
    } else {
        // Update without changing password
        $query = "UPDATE users SET fullname = '$fullname', email = '$email', role = '$role' WHERE id = " . (int)$user_id;
    }
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
    }
} else {
    // Add new user
    if (empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Password is required for new users']);
        exit();
    }
    
    // Check if email already exists
    $check_query = "SELECT id FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['success' => false, 'error' => 'Email already exists']);
        exit();
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (fullname, email, password, role) VALUES ('$fullname', '$email', '$hashed_password', '$role')";
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'User added successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
    }
}
?>
