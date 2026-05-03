<?php
session_start();
require_once "config.php";
include "includes/auth-handler.php";

// 1. Kiểm tra bảo mật: Bắt buộc đăng nhập
if (!isLoggedIn()) {
    header("Location: login.php?error=Vui lòng đăng nhập để chỉnh sửa tin.");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$bike_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Truy vấn dữ liệu cũ VÀ kiểm tra quyền sở hữu
$sql_check = "SELECT * FROM bikes WHERE id = ? AND user_id = ?";
$stmt_check = $conn->prepare($sql_check);
// Truyền 2 số nguyên (integer - "ii") vào câu truy vấn
$stmt_check->bind_param("ii", $bike_id, $current_user_id);
$stmt_check->execute();
$bike = $stmt_check->get_result()->fetch_assoc();

// Nếu không tìm thấy xe, hoặc xe này không phải của user đang đăng nhập
if (!$bike) {
    die("<div style='text-align:center; padding: 50px; font-size: 18px; color: red;'>
            <i class='fas fa-exclamation-triangle'></i> Bạn không có quyền chỉnh sửa tin đăng này!
         </div>");
}

// 3. Xử lý khi người dùng gửi biểu mẫu (Bấm Lưu thay đổi)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu mới từ Form
    $title = trim($_POST['title']);
    $price = floatval($_POST['price']);
    $condition_bike = trim($_POST['condition_bike']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);

    // Câu lệnh SQL UPDATE để cập nhật thông tin mới
    $update_sql = "UPDATE bikes SET title=?, price=?, condition_bike=?, location=?, description=? WHERE id=? AND user_id=?";
    $update_stmt = $conn->prepare($update_sql);
    
    // Ràng buộc dữ liệu an toàn: s (string/chuỗi), d (double/số thập phân), i (integer/số nguyên)
    $update_stmt->bind_param("sdsssii", $title, $price, $condition_bike, $location, $description, $bike_id, $current_user_id);

    if ($update_stmt->execute()) {
        // Cập nhật thành công, chuyển hướng về trang chi tiết xe
        header("Location: ad-detail.php?id=" . $bike_id);
        exit();
    } else {
        $error_msg = "Có lỗi xảy ra khi lưu vào cơ sở dữ liệu. Vui lòng thử lại.";
    }
}

include "includes/header.php";
?>

<style>
.edit-container { max-width: 800px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
.edit-title { color: #2f5d62; margin-bottom: 25px; font-size: 24px; text-align: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;}
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #1e293b; }
.form-control { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px; box-sizing: border-box; }
.form-control:focus { border-color: #2f5d62; outline: none; box-shadow: 0 0 0 2px rgba(47, 93, 98, 0.2);}
.btn-save { background: #F57C00; color: white; border: none; padding: 14px 30px; font-size: 16px; font-weight: bold; border-radius: 6px; cursor: pointer; width: 100%; transition: 0.3s; margin-top: 10px;}
.btn-save:hover { background: #e65100; transform: translateY(-2px);}
.alert-error { padding: 15px; background: #fee2e2; color: #b91c1c; border-radius: 6px; margin-bottom: 20px; }
</style>

<div class="edit-container">
    <h2 class="edit-title"><i class="fas fa-edit"></i> Chỉnh sửa thông tin xe</h2>

    <?php if (isset($error_msg)): ?>
        <div class="alert-error"><?= $error_msg ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Tiêu đề tin đăng *</label>
            <!-- Sử dụng htmlspecialchars để in dữ liệu cũ an toàn, không làm hỏng HTML -->
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($bike['title']) ?>" required>
        </div>

        <div class="form-group">
            <label>Giá bán (VNĐ) *</label>
            <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($bike['price']) ?>" required>
        </div>

        <div class="form-group">
            <label>Tình trạng xe *</label>
            <input type="text" name="condition_bike" class="form-control" value="<?= htmlspecialchars($bike['condition_bike']) ?>" required>
        </div>

        <div class="form-group">
            <label>Khu vực giao dịch *</label>
            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($bike['location']) ?>" required>
        </div>

        <div class="form-group">
            <label>Mô tả chi tiết *</label>
            <!-- Thẻ textarea không có thuộc tính value, dữ liệu được kẹp giữa thẻ đóng và mở -->
            <textarea name="description" class="form-control" rows="8" required><?= htmlspecialchars($bike['description']) ?></textarea>
        </div>

        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Lưu thay đổi</button>
    </form>
</div>

<?php include "includes/footer.php"; ?>