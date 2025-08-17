<?php
session_start();
require_once __DIR__ . '/bd/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        header("Location: ../login/index.html?error=empty_fields");
        exit();
    }

    try {
        // Query user using PDO
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check both MD5 (old) and password_hash (new) methods for compatibility
            if (password_verify($password, $user['password']) || 
                (strlen($user['password']) === 32 && md5($password) === $user['password'])) {
                
                // If using old MD5, upgrade to password_hash
                if (strlen($user['password']) === 32) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hash, $user['id']]);
                }
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                header("Location: ../index.html");
                exit();
            } else {
                header("Location: ../login/index.html?error=wrong_password");
                exit();
            }
        } else {
            header("Location: ../login/index.html?error=user_not_found");
            exit();
        }
    } catch (Exception $e) {
        header("Location: ../login/index.html?error=database_error");
        exit();
    }
}
?>