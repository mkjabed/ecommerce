<?php
// submit_order.php - Handle order submission
// submit_order.php - Handle order submission
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login to place an order']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON data from frontend
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO orders (customer_name, email, phone, address, city, notes, total, payment_method) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
    $_SESSION['name'],
    $input['customer']['email'],
    $input['customer']['phone'],
        $input['customer']['address'],
        $input['customer']['city'],
        $input['customer']['notes'],
        $input['total'],
        $input['paymentMethod']
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Insert order items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, quantity, price) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($input['items'] as $item) {
        $stmt->execute([
            $order_id,
            $item['id'],
            $item['name'],
            $item['quantity'],
            $item['price']
        ]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Order placed successfully!'
    ]);
    
} catch(PDOException $e) {
    // Rollback transaction on error
    $pdo->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>