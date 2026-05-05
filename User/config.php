<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "greenride"; 

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Lỗi kết nối DB: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>
