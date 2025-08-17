<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate required fields
    if (empty($data['name']) || empty($data['email']) || empty($data['subject']) || empty($data['message'])) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        exit;
    }
    
    // Sanitize input data
    $name = trim($data['name']);
    $email = trim($data['email']);
    $subject = trim($data['subject']);
    $message = trim($data['message']);
    
    // Insert contact message into database
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, submitted_at, status) VALUES (?, ?, ?, ?, NOW(), 'unread')");
    
    if ($stmt->execute([$name, $email, $subject, $message])) {
        echo json_encode([
            'success' => true, 
            'message' => 'Contact message submitted successfully',
            'id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save message']);
    }
    
} catch (PDOException $e) {
    error_log("Database error in submit_contact.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error in submit_contact.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred while processing your request']);
}
?>