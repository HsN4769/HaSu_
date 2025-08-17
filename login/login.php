<?php
/**
 * Login Processing
 * Handles user authentication and session management
 */

session_start();
header('Content-Type: application/json');

// Include database configuration
require_once '../config/database.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    // Get form data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Tafadhali jaza barua pepe na nenosiri'
        ]);
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Barua pepe si sahihi'
        ]);
        exit();
    }
    
    // Get database connection
    $pdo = getDB();
    
    // Check if user exists
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, password, role, is_active 
        FROM users 
        WHERE email = ? AND is_active = 1 
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Barua pepe au nenosiri si sahihi'
        ]);
        exit();
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Barua pepe au nenosiri si sahihi'
        ]);
        exit();
    }
    
    // Check if account is active
    if (!$user['is_active']) {
        echo json_encode([
            'success' => false,
            'message' => 'Account yako imefungwa. Tafadhali wasiliana na admin'
        ]);
        exit();
    }
    
    // Set user session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['is_active'] = $user['is_active'];
    $_SESSION['login_time'] = time();
    
    // Log successful login
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, action, description, ip_address, user_agent) 
        VALUES (?, 'login', 'User logged in successfully', ?, ?)
    ");
    $stmt->execute([
        $user['id'],
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Umefanikiwa kuingia! Inaelekeza kwenye ukurasa wa nyumbani...',
        'user' => [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Login error: " . $e->getMessage());
    
    // Return generic error message
    echo json_encode([
        'success' => false,
        'message' => 'Kuna tatizo la mfumo. Tafadhali jaribu tena baadaye.'
    ]);
}
?>
