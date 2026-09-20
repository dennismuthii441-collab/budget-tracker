<?php
session_start();
require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . getBaseUrl() . 'login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . getBaseUrl() . 'index.php');
        exit();
    }
}

function hasBookAccess($user_id, $book_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND book_id = ? AND status = 'completed'");
    $stmt->execute([$user_id, $book_id]);
    return $stmt->fetchColumn() > 0;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . $host . $path . '/';
}

function uploadFile($file, $uploadDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }
    
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, $allowedTypes)) {
        return false;
    }
    
    if ($fileError !== 0) {
        return false;
    }
    
    if ($fileSize > 5000000) { // 5MB limit
        return false;
    }
    
    $newFileName = uniqid('', true) . '.' . $fileExt;
    $fileDestination = $uploadDir . $newFileName;
    
    if (move_uploaded_file($fileTmpName, $fileDestination)) {
        return $newFileName;
    }
    
    return false;
}
?>