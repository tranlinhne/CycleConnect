<?php
session_start();
$host = 'localhost';
$dbname = 'greenride';
$username = 'root';
$password = '';

define('BASE_URL', '/CycleMarket/admin/');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && $_SESSION['role'] === 'admin';
}

function redirectIfNotAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
?>