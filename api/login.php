<?php
header("Content-Type: application/json");
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$password = trim($data["password"] ?? "");

if ($email === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Vui lòng nhập email và mật khẩu"
    ]);
    exit;
}

$file = __DIR__ . "/../data/users.json";

if (!file_exists($file)) {
    echo json_encode([
        "success" => false,
        "message" => "Chưa có tài khoản nào"
    ]);
    exit;
}

$users = json_decode(file_get_contents($file), true);

if (!is_array($users)) {
    $users = [];
}

foreach ($users as $user) {
    if ($user["email"] === $email && password_verify($password, $user["password"])) {
        $_SESSION["user"] = [
            "id" => $user["id"],
            "full_name" => $user["full_name"],
            "email" => $user["email"],
            "phone" => $user["phone"],
            "address" => $user["address"]
        ];

        echo json_encode([
            "success" => true,
            "message" => "Đăng nhập thành công"
        ]);
        exit;
    }
}

echo json_encode([
    "success" => false,
    "message" => "Sai email hoặc mật khẩu"
]);