<?php
header("Content-Type: application/json");
session_start();

if (!isset($_SESSION["user"])) {
    echo json_encode([
        "success" => false,
        "message" => "Chưa đăng nhập"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$full_name = trim($data["full_name"] ?? "");
$email = trim($data["email"] ?? "");
$phone = trim($data["phone"] ?? "");
$address = trim($data["address"] ?? "");

if ($full_name === "" || $email === "" || $phone === "" || $address === "") {
    echo json_encode([
        "success" => false,
        "message" => "Vui lòng nhập đầy đủ thông tin"
    ]);
    exit;
}

$file = __DIR__ . "/../data/users.json";

if (!file_exists($file)) {
    echo json_encode([
        "success" => false,
        "message" => "Không tìm thấy dữ liệu người dùng"
    ]);
    exit;
}

$users = json_decode(file_get_contents($file), true);

if (!is_array($users)) {
    $users = [];
}

$userId = $_SESSION["user"]["id"];
$updated = false;

foreach ($users as &$user) {
    if ($user["id"] == $userId) {
        $user["full_name"] = $full_name;
        $user["email"] = $email;
        $user["phone"] = $phone;
        $user["address"] = $address;
        $updated = true;
        break;
    }
}

if ($updated) {
    file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $_SESSION["user"]["full_name"] = $full_name;
    $_SESSION["user"]["email"] = $email;
    $_SESSION["user"]["phone"] = $phone;
    $_SESSION["user"]["address"] = $address;

    echo json_encode([
        "success" => true,
        "message" => "Cập nhật hồ sơ thành công"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Không tìm thấy user"
    ]);
}