<?php
// File: db.php — main logic for db page.
// Database configuration
$host = 'localhost';
$user = 'root';
$pass = ''; // Default WAMP/XAMPP password is empty
$dbname = 'simple_ecommerce';

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
