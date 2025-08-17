<?php
// Database connection details
$host = "localhost";     // Server ya MySQL
$username = "root";      // Username ya MySQL
$password = "";          // Password (default ni tupu kwa XAMPP)
$dbname = "harusi_db";   // Jina la database

// Unda connection
$conn = new mysqli($host, $username, $password, $dbname);

// Kagua kama kuna error kwenye connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
