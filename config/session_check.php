<?php
/**
 * Session Management and Authentication
 * Enforces login requirements across the website
 */

session_start();

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require user to be logged in
 * Redirects to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // Store intended URL for redirect after login
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login page
        header('Location: ../login/index.html');
        exit();
    }
}

/**
 * Require user to be admin
 * Redirects to login if not admin
 */
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        // Redirect to home page if not admin
        header('Location: ../public/index.php');
        exit();
    }
}

/**
 * Get current user data
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'first_name' => $_SESSION['first_name'] ?? '',
        'last_name' => $_SESSION['last_name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
        'is_active' => $_SESSION['is_active'] ?? false
    ];
}

/**
 * Log user activity
 * @param string $action
 * @param string $description
 */
function logActivity($action, $description = '') {
    if (!isLoggedIn()) {
        return;
    }
    
    try {
        require_once 'database.php';
        $pdo = getDB();
        
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_id, action, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        // Log error but don't break the application
        error_log("Activity logging failed: " . $e->getMessage());
    }
}

/**
 * Set user session data
 * @param array $user
 */
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['is_active'] = $user['is_active'];
    $_SESSION['login_time'] = time();
    
    // Log successful login
    logActivity('login', 'User logged in successfully');
}

/**
 * Clear user session
 */
function clearUserSession() {
    if (isLoggedIn()) {
        logActivity('logout', 'User logged out');
    }
    
    // Clear all session data
    session_unset();
    session_destroy();
    
    // Start new session for flash messages
    session_start();
}

/**
 * Check if session has expired
 * @param int $timeout_minutes
 * @return bool
 */
function isSessionExpired($timeout_minutes = 60) {
    if (!isLoggedIn()) {
        return true;
    }
    
    $login_time = $_SESSION['login_time'] ?? 0;
    $current_time = time();
    $timeout_seconds = $timeout_minutes * 60;
    
    return ($current_time - $login_time) > $timeout_seconds;
}

/**
 * Refresh session timeout
 */
function refreshSession() {
    if (isLoggedIn()) {
        $_SESSION['login_time'] = time();
    }
}

/**
 * Get user's full name
 * @return string
 */
function getUserFullName() {
    if (!isLoggedIn()) {
        return '';
    }
    
    $first_name = $_SESSION['first_name'] ?? '';
    $last_name = $_SESSION['last_name'] ?? '';
    
    return trim($first_name . ' ' . $last_name);
}

/**
 * Check if user can access specific resource
 * @param string $resource_type
 * @param int $resource_id
 * @return bool
 */
function canAccessResource($resource_type, $resource_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Admin can access everything
    if (isAdmin()) {
        return true;
    }
    
    // Users can only access their own resources
    switch ($resource_type) {
        case 'profile':
            return $_SESSION['user_id'] == $resource_id;
        case 'payment':
        case 'guest':
        case 'qr_code':
            // Check if resource belongs to current user
            try {
                require_once 'database.php';
                $pdo = getDB();
                
                $stmt = $pdo->prepare("
                    SELECT user_id FROM {$resource_type}s WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$resource_id, $_SESSION['user_id']]);
                
                return $stmt->fetch() !== false;
            } catch (Exception $e) {
                error_log("Resource access check failed: " . $e->getMessage());
                return false;
            }
        default:
            return false;
    }
}
?>
