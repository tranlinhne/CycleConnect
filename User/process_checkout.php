<?php
session_start();
include 'config.php';



/* =====================================================
   BƯỚC 1: KIỂM TRA ĐĂNG NHẬP + GIỎ HÀNG
===================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' 
    || !isset($_SESSION['user'])
    || !isset($_SESSION['cart']) 
    || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$kh_id = (int)$_SESSION['user']['id'];  
$ho_ten      = trim($_POST['ho_ten']);
$dien_thoai  = trim($_POST['dien_thoai']);
$email       = trim($_POST['email'] ?? '');
$dia_chi     = trim($_POST['dia_chi']);
$ghi_chu     = trim($_POST['ghi_chu'] ?? '');
$phuong_thuc = $_POST['phuong_thuc']; // cod | bank


/* =====================================================
   BƯỚC 2: TÍNH LẠI TỔNG TIỀN TỪ DATABASE (CHỐNG SỬA GIÁ)
===================================================== */
$tong_tien = 0;

foreach ($_SESSION['cart'] as $item) {

    $id_sp    = (int)$item['id'];
    $so_luong = (int)($item['qty'] ?? $item['quantity'] ?? 1);

    $sql = "SELECT price FROM bicycles WHERE bicycle_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) continue;

    $stmt->bind_param("i", $id_sp);
    $stmt->execute();
    $result = $stmt->get_result();

    $gia = ($result->num_rows > 0) 
            ? $result->fetch_assoc()['price'] 
            : 0;

    $stmt->close();

    $tong_tien += $gia * $so_luong;
}


/* =====================================================
   BƯỚC 3: TẠO ĐƠN HÀNG
===================================================== */
/* =====================================================
   BƯỚC 3: TẠO ĐƠN HÀNG (ĐÃ SỬA THEO CẤU TRÚC BẢNG)
===================================================== */

$sql = "INSERT INTO don_hang 
        (id_users, ho_ten, dien_thoai, email, dia_chi, ghi_chu, phuong_thuc_thanh_toan, tong_tien, trang_thai, ngay_dat) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'cho_xac_nhan', NOW())";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Lỗi chuẩn bị SQL (don_hang): " . $conn->error);
}

$stmt->bind_param(
    "isssssid", 
    $kh_id,        // id_users
    $ho_ten,
    $dien_thoai,
    $email,
    $dia_chi,
    $ghi_chu,
    $phuong_thuc,
    $tong_tien
);

if (!$stmt->execute()) {
    die("Lỗi lưu đơn hàng: " . $stmt->error);
}

$don_hang_id = $conn->insert_id;
$stmt->close();


/* =====================================================
   BƯỚC 4: THÊM CHI TIẾT ĐƠN HÀNG (ĐÃ FIX LỖI GIÁ = 0)
===================================================== */
foreach ($_SESSION['cart'] as $item) {

    $id_sp    = (int)$item['id'];
    $so_luong = (int)($item['qty'] ?? $item['quantity'] ?? 1);

    // Lấy giá CHUẨN từ DB
    $gia_result = $conn->query("SELECT price FROM bicycles WHERE bicycle_id = $id_sp LIMIT 1");
    $gia = ($gia_result && $gia_result->num_rows > 0) 
            ? $gia_result->fetch_assoc()['price'] 
            : 0;

    $sql_detail = "INSERT INTO chi_tiet_don_hang 
                   (id_don_hang, id_san_pham, so_luong, don_gia) 
                   VALUES (?, ?, ?, ?)";

    $stmt_detail = $conn->prepare($sql_detail);
    if ($stmt_detail) {
        $stmt_detail->bind_param("iiid", $don_hang_id, $id_sp, $so_luong, $gia);
        $stmt_detail->execute();
        $stmt_detail->close();
    }
}


/* =====================================================
   BƯỚC 5: XÓA GIỎ HÀNG
===================================================== */
unset($_SESSION['cart']);


/* =====================================================
   BƯỚC 6: CHUYỂN TRANG CẢM ƠN
===================================================== */
header("Location: thank_you.php?order=" . $don_hang_id);
exit;
?>