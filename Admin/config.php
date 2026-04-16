<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "greenride";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Kết nối database thất bại: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>