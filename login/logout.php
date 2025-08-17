<?php
/**
 * Logout Processing
 * Destroys user session and redirects to login
 */

session_start();

// Log logout activity if user was logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    try {
        require_once '../config/database.php';
        $pdo = getDB();
        
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_id, action, description, ip_address, user_agent) 
            VALUES (?, 'logout', 'User logged out', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        // Log error but don't break logout
        error_log("Logout logging failed: " . $e->getMessage());
    }
}

// Clear all session data
session_unset();
session_destroy();

// Start new session for flash message
session_start();
$_SESSION['logout_message'] = 'Umefanikiwa kutoka kwenye system. Tafadhali ingia tena.';

// Redirect to login page
header('Location: index.html');
exit();
?>
