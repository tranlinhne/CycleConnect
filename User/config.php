<?php
$servername = "localhost";
$username = "root";  
$password = "";
$dbname = "greenride";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Chỉ chạy session_start nếu session chưa được kích hoạt
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
