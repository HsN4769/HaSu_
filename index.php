<?php
/**
 * Root Index File
 * Redirects all requests to login page
 */

// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // User is logged in, redirect to public home
    header('Location: public/index.php');
    exit();
}

// User is not logged in, redirect to login
header('Location: login/index.html');
exit();
?>
