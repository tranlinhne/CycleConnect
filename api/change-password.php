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

$old_password = trim($data["old_password"] ?? "");
$new_password = trim($data["new_password"] ?? "");

if ($old_password === "" || $new_password === "") {
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
        "message" => "Không có dữ liệu người dùng"
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
        if (!password_verify($old_password, $user["password"])) {
            echo json_encode([
                "success" => false,
                "message" => "Mật khẩu cũ không đúng"
            ]);
            exit;
        }

        $user["password"] = password_hash($new_password, PASSWORD_DEFAULT);
        $updated = true;
        break;
    }
}

if ($updated) {
    file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode([
        "success" => true,
        "message" => "Đổi mật khẩu thành công"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Không tìm thấy user"
    ]);
}