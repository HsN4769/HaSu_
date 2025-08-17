<?php
/**
 * System Installation
 * Sets up database and initial configuration
 */

// Check if already installed
if (file_exists('config/installed.lock')) {
    die('System is already installed. Remove config/installed.lock to reinstall.');
}

// Include database configuration
require_once 'config/database.php';

// Function to create database and tables
function installSystem() {
    try {
        // Test database connection
        if (!testDBConnection()) {
            throw new Exception("Database connection failed. Please check your database configuration.");
        }
        
        $pdo = getDB();
        
        // Read and execute schema
        $schema_file = 'database/schema.sql';
        if (!file_exists($schema_file)) {
            throw new Exception("Database schema file not found: $schema_file");
        }
        
        $schema = file_get_contents($schema_file);
        
        // Split SQL statements
        $statements = array_filter(
            array_map('trim', explode(';', $schema)),
            function($stmt) { return !empty($stmt) && !preg_match('/^--/', $stmt); }
        );
        
        // Execute each statement
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        // Create necessary directories
        $directories = [
            'temp/qr_codes',
            'logs',
            'assets/uploads',
            'assets/backups'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        // Create .htaccess files for protection
        $htaccess_content = "Order deny,allow\nDeny from all";
        file_put_contents('config/.htaccess', $htaccess_content);
        file_put_contents('database/.htaccess', $htaccess_content);
        file_put_contents('logs/.htaccess', $htaccess_content);
        
        // Create installed lock file
        file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
        
        return true;
        
    } catch (Exception $e) {
        error_log("Installation error: " . $e->getMessage());
        throw $e;
    }
}

// Function to check system requirements
function checkRequirements() {
    $requirements = [];
    
    // Check PHP version
    $requirements['php_version'] = [
        'required' => '7.4.0',
        'current' => PHP_VERSION,
        'status' => version_compare(PHP_VERSION, '7.4.0', '>=')
    ];
    
    // Check PHP extensions
    $required_extensions = ['pdo', 'pdo_mysql', 'gd', 'json', 'session'];
    foreach ($required_extensions as $ext) {
        $requirements['extensions'][$ext] = [
            'required' => true,
            'current' => extension_loaded($ext),
            'status' => extension_loaded($ext)
        ];
    }
    
    // Check directory permissions
    $writable_dirs = ['temp', 'logs', 'assets/uploads', 'assets/backups'];
    foreach ($writable_dirs as $dir) {
        $requirements['permissions'][$dir] = [
            'required' => 'Writable',
            'current' => is_writable($dir) ? 'Writable' : 'Not Writable',
            'status' => is_writable($dir)
        ];
    }
    
    // Check if all requirements are met
    $requirements['all_met'] = true;
    foreach ($requirements as $key => $requirement) {
        if ($key === 'all_met') continue;
        
        if (is_array($requirement)) {
            foreach ($requirement as $sub_key => $sub_req) {
                if ($sub_key === 'status' && !$sub_req) {
                    $requirements['all_met'] = false;
                }
            }
        }
    }
    
    return $requirements;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (installSystem()) {
            $success_message = "System installed successfully! You can now log in with admin@harusi.com / admin123";
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Check requirements
$requirements = checkRequirements();
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hamisi Wedding System - Installation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .content {
            padding: 2rem;
        }

        .requirements {
            margin-bottom: 2rem;
        }

        .requirement-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            margin-bottom: 0.5rem;
        }

        .requirement-item.passed {
            background: #d4edda;
            border-color: #c3e6cb;
        }

        .requirement-item.failed {
            background: #f8d7da;
            border-color: #f5c6cb;
        }

        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status.passed {
            background: #28a745;
            color: white;
        }

        .status.failed {
            background: #dc3545;
            color: white;
        }

        .install-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        .install-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .install-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .section {
            margin-bottom: 2rem;
        }

        .section h2 {
            color: #333;
            margin-bottom: 1rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-heart"></i> Hamisi Wedding System</h1>
            <p>System Installation & Setup</p>
        </div>
        
        <div class="content">
            <?php if (isset($success_message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
                <div class="text-center">
                    <a href="login/index.html" class="install-btn" style="text-decoration: none; display: inline-block;">
                        <i class="fas fa-sign-in-alt"></i> Enda kwenye Login
                    </a>
                </div>
            <?php elseif (isset($error_message)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="section">
                <h2><i class="fas fa-list-check"></i> System Requirements</h2>
                <div class="requirements">
                    <div class="requirement-item <?php echo $requirements['php_version']['status'] ? 'passed' : 'failed'; ?>">
                        <span>PHP Version (>= 7.4.0)</span>
                        <span class="status <?php echo $requirements['php_version']['status'] ? 'passed' : 'failed'; ?>">
                            <?php echo $requirements['php_version']['current']; ?>
                        </span>
                    </div>
                    
                    <?php foreach ($requirements['extensions'] as $ext => $info): ?>
                        <div class="requirement-item <?php echo $info['status'] ? 'passed' : 'failed'; ?>">
                            <span>PHP Extension: <?php echo strtoupper($ext); ?></span>
                            <span class="status <?php echo $info['status'] ? 'passed' : 'failed'; ?>">
                                <?php echo $info['status'] ? 'Installed' : 'Missing'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php foreach ($requirements['permissions'] as $dir => $info): ?>
                        <div class="requirement-item <?php echo $info['status'] ? 'passed' : 'failed'; ?>">
                            <span>Directory: <?php echo $dir; ?></span>
                            <span class="status <?php echo $info['status'] ? 'passed' : 'failed'; ?>">
                                <?php echo $info['current']; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if ($requirements['all_met'] && !isset($success_message)): ?>
                <div class="section">
                    <h2><i class="fas fa-download"></i> Install System</h2>
                    <p>All requirements are met. Click the button below to install the system.</p>
                    
                    <form method="POST">
                        <button type="submit" class="install-btn">
                            <i class="fas fa-magic"></i> Install Hamisi Wedding System
                        </button>
                    </form>
                </div>
            <?php elseif (!$requirements['all_met']): ?>
                <div class="section">
                    <h2><i class="fas fa-exclamation-triangle"></i> Requirements Not Met</h2>
                    <p>Please fix the above requirements before installing the system.</p>
                </div>
            <?php endif; ?>
            
            <div class="section">
                <h2><i class="fas fa-info-circle"></i> Installation Notes</h2>
                <ul style="padding-left: 1.5rem; line-height: 1.6;">
                    <li>Make sure your database server (MySQL/MariaDB) is running</li>
                    <li>Check that the database credentials in config/database.php are correct</li>
                    <li>Ensure the web server has write permissions to temp/, logs/, and assets/ directories</li>
                    <li>After installation, you can log in with: admin@harusi.com / admin123</li>
                    <li>Remove install.php after successful installation for security</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
