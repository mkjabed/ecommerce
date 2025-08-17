<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Check if email already exists
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $checkStmt->execute([$email]);
    $emailExists = $checkStmt->fetchColumn();
    
    if ($emailExists > 0) {
        // Redirect back to login page with error
        header("Location: login_register.html?error=exists");
        exit();
    } else {
        // Proceed with registration
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['name'] = $name;
            $_SESSION['role'] = 'customer';
            header("Location: login_register.html"); // Redirect to homepage or success page
            exit();
        } catch (PDOException $e) {
            // Redirect back with generic error
            header("Location: login_register.html?error=fail");
            exit();
        }
    }
}
?>