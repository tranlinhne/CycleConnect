<?php
session_start();
require_once "config.php";

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode(['status' => 'error']);
    exit;
}

// lấy thông tin xe
$sql = "SELECT bicycle_id, name, price, main_image FROM bicycles WHERE bicycle_id = $id LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($row = mysqli_fetch_assoc($result)) {

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // nếu đã có thì tăng số lượng
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty'] += 1;
    } else {
        $_SESSION['cart'][$id] = [
            'id' => $row['bicycle_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'image' => $row['main_image'],
            'qty' => $qty,
        ];
    }

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}