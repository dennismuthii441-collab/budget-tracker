<?php
$host = 'localhost';
$db = 'unilife'; // Your database name
$user = 'root';
$pass = ''; // Default password for XAMPP is empty

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
