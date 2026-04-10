<?php
header("Content-Type: application/json");
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$full_name = trim($data["full_name"] ?? "");
$email = trim($data["email"] ?? "");
$phone = trim($data["phone"] ?? "");
$password = trim($data["password"] ?? "");

if ($full_name === "" || $email === "" || $phone === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Vui lòng nhập đầy đủ thông tin"
    ]);
    exit;
}

$file = __DIR__ . "/../data/users.json";

if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
}

$users = json_decode(file_get_contents($file), true);

if (!is_array($users)) {
    $users = [];
}

foreach ($users as $user) {
    if ($user["email"] === $email) {
        echo json_encode([
            "success" => false,
            "message" => "Email đã tồn tại"
        ]);
        exit;
    }
}

$newUser = [
    "id" => time(),
    "full_name" => $full_name,
    "email" => $email,
    "phone" => $phone,
    "password" => password_hash($password, PASSWORD_DEFAULT),
    "address" => ""
];

$users[] = $newUser;

file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    "success" => true,
    "message" => "Đăng ký thành công"
]);