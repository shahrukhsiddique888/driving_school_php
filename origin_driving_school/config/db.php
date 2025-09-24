<?php
/**
 * Database connection for Origin Driving School project
 * Using PDO with error handling and secure defaults
 */

$host = "localhost";                 // Database host (XAMPP default: localhost)
$db   = "origin_driving_school";     // Database name
$user = "root";                      // Default XAMPP user
$pass = "";                          // Default XAMPP password is empty

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // return associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // use native prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Uncomment the line below for debugging connection:
    // echo "✅ Database connected successfully!";
} catch (PDOException $e) {
    die("❌ DB connection failed: " . $e->getMessage());
}
