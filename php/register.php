<?php
require_once __DIR__ . '/bd/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);

    if (empty($email) || empty($password) || empty($confirm)) {
        header("Location: ../login/register.html?error=empty_fields");
        exit();
    }

    if ($password !== $confirm) {
        header("Location: ../login/register.html?error=password_mismatch");
        exit();
    }

    if (strlen($password) < 6) {
        header("Location: ../login/register.html?error=password_too_short");
        exit();
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            header("Location: ../login/register.html?error=email_exists");
            exit();
        }

        // Create user with secure password hash
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, created_at) VALUES (?, ?, NOW())");
        
        if ($stmt->execute([$email, $hashed_password])) {
            header("Location: ../login/index.html?success=registration_complete");
            exit();
        } else {
            header("Location: ../login/register.html?error=registration_failed");
            exit();
        }
    } catch (Exception $e) {
        header("Location: ../login/register.html?error=database_error");
        exit();
    }
}
?>
