<?php
$host     = '127.0.0.1';
$port     = 3306; // Changed from default 3306 to 3307 for custom XAMPP port configuration
$dbname   = 'edusphere_db';
$username = 'root';
$password = ''; // Default in XAMPP is empty string ''

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}