<?php
session_start();

// Only allow admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die('Access denied. Admin only.');
}

include "db.php";

echo "<h1>Database Status Check</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #4b3200; color: white; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .btn { display: inline-block; padding: 10px 20px; background: #4b3200; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
</style>";

// Check orders table structure
echo "<h2>Orders Table Structure</h2>";
$result = mysqli_query($conn, "DESCRIBE orders");

if ($result) {
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    
    $columns = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $columns[] = $row['Field'];
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for required columns
    echo "<h2>Migration Status</h2>";
    echo "<table>";
    echo "<tr><th>Column</th><th>Status</th></tr>";
    
    $required_columns = [
        'id' => 'Basic',
        'customer_id' => 'New (Optimized System)',
        'processed_by' => 'New (Optimized System)',
        'customer_name' => 'Basic',
        'table_num' => 'Basic',
        'total' => 'Basic',
        'status' => 'Basic',
        'payment_status' => 'New (Optimized System)',
        'created_at' => 'Basic',
        'updated_at' => 'New (Optimized System)'
    ];
    
    $missing_columns = [];
    foreach ($required_columns as $col => $type) {
        $exists = in_array($col, $columns);
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($col) . "</strong> <em>(" . $type . ")</em></td>";
        if ($exists) {
            echo "<td class='success'>✓ EXISTS</td>";
        } else {
            echo "<td class='error'>✗ MISSING</td>";
            $missing_columns[] = $col;
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Show migration status
    if (empty($missing_columns)) {
        echo "<div class='success'>";
        echo "<h3>✓ All columns exist! Your database is ready for the optimized system.</h3>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h3>✗ Missing columns detected!</h3>";
        echo "<p>You need to run the migration SQL to add these columns:</p>";
        echo "<ul>";
        foreach ($missing_columns as $col) {
            echo "<li>" . htmlspecialchars($col) . "</li>";
        }
        echo "</ul>";
        echo "<p><strong>Action Required:</strong></p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin</li>";
        echo "<li>Select your database</li>";
        echo "<li>Go to SQL tab</li>";
        echo "<li>Copy and paste the contents of <code>migrate_optimized_orders.sql</code></li>";
        echo "<li>Click 'Go'</li>";
        echo "<li>Refresh this page to verify</li>";
        echo "</ol>";
        echo "</div>";
    }
    
} else {
    echo "<p class='error'>Error: Could not retrieve table structure - " . mysqli_error($conn) . "</p>";
}

// Show sample orders
echo "<h2>Recent Orders (Last 5)</h2>";
$orders_result = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");

if ($orders_result && mysqli_num_rows($orders_result) > 0) {
    echo "<table>";
    echo "<tr>";
    foreach ($columns as $col) {
        echo "<th>" . htmlspecialchars($col) . "</th>";
    }
    echo "</tr>";
    
    while ($order = mysqli_fetch_assoc($orders_result)) {
        echo "<tr>";
        foreach ($columns as $col) {
            $value = $order[$col] ?? 'NULL';
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>No orders found in database.</p>";
}

echo "<br><a href='orders.php' class='btn'>← Back to Orders</a>";
echo "<a href='admin.php' class='btn'>← Back to Dashboard</a>";
?>
