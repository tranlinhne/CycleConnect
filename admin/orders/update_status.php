<?php
require_once '../inc/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status, $order_id]);
}
header('Location: index.php');
exit;