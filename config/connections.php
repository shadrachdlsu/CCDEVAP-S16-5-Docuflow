<?php

$host = 'localhost';
$dbname = 'docuflow_db';
$username = 'root';
$password = '';     // leave empty unless configured

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    
    // PDO configuration options
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch rows as associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
    ];

    // Instantiate the PDO object
    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch (PDOException $e) {
    // Makes sure nothing runs if db isn't running
    die("Database connection failed: " . $e->getMessage());
}
?>