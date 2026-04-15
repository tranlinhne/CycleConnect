<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "greenride"; // đổi đúng tên database của bạn

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Lỗi kết nối DB: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>
