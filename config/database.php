<?php
/**
 * Database Configuration
 * Handles database connections with PDO and mysqli fallback
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'hamisi_wedding');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection
 * @return PDO
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    return $pdo;
}

/**
 * Get mysqli database connection (fallback)
 * @return mysqli
 */
function getDBi() {
    static $mysqli = null;
    
    if ($mysqli === null) {
        try {
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($mysqli->connect_error) {
                throw new Exception("Connection failed: " . $mysqli->connect_error);
            }
            
            $mysqli->set_charset(DB_CHARSET);
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    return $mysqli;
}

/**
 * Test database connection
 * @return bool
 */
function testDBConnection() {
    try {
        $pdo = getDB();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
