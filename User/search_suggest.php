<?php
require_once "config.php";

header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode([]);
    exit;
}

// tránh lỗi ký tự đặc biệt
$q = "%$q%";

$sql = "SELECT bicycle_id, name, price, main_image 
        FROM bicycles 
        WHERE name LIKE ? 
        LIMIT 8";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "SQL prepare failed"]);
    exit;
}

$stmt->bind_param("s", $q);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "bicycle_id" => $row["bicycle_id"],
        "name" => $row["name"],
        "price" => (int)$row["price"],
        "main_image" => $row["main_image"] ?: "assets/images/default-bike.png"
    ];
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);