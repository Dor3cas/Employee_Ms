<?php
// Database configuration - update these values for your server
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'user_system');

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Create tables if they don't exist
function setupDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
    $conn->set_charset("utf8mb4");

    $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->select_db(DB_NAME);

    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL
    )");

    // Insert default admin if none exists
    $result = $conn->query("SELECT COUNT(*) as cnt FROM admins");
    $row = $result->fetch_assoc();
    if ($row['cnt'] == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO admins (name, password) VALUES ('Admin', '$hash')");
    }

    $conn->close();
}

setupDatabase();
?>
