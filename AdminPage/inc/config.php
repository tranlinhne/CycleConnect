<?php
session_start();
$host = 'localhost';
$dbname = 'greenride';
$username = 'root';
$password = '';

define('BASE_URL', '/CycleMarket/AdminPage/');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && in_array($_SESSION['role'], ['admin', 'manager']);
}

function isSuperAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && $_SESSION['role'] === 'admin';
}

function redirectIfNotAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function redirectIfNotSuperAdmin() {
    if (!isSuperAdmin()) {
        echo "<div style='padding: 50px; text-align: center; font-family: sans-serif; color: #333;'>
                <h1 style='color: #dc3545;'>Truy Cập Bị Từ Chối</h1>
                <p>Bạn không có quyền truy cập trang này. Chỉ Super Admin mới được phép thực hiện các thao tác này.</p>
                <a href='" . BASE_URL . "dashboard.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #F57C00; color: white; text-decoration: none; border-radius: 5px;'>Quay lại Tổng Quan</a>
              </div>";
        exit();
    }
}
?>
