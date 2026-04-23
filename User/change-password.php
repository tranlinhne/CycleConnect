<?php
session_start();
include_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/auth-handler.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $result = changePassword(
        (int)$_SESSION['user_id'],
        $old_password,
        $new_password,
        $confirm_password
    );

    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - GreenRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="change-password-page">

<div class="change-password-wrapper">
    <div class="change-password-box">
        <h1 class="change-password-title">Đổi mật khẩu</h1>
        <p class="change-password-subtitle">Cập nhật mật khẩu để bảo vệ tài khoản của bạn.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="old_password">Mật khẩu hiện tại</label>
                <input
                    id="old_password"
                    type="password"
                    name="old_password"
                    required
                    placeholder="Nhập mật khẩu hiện tại"
                >
            </div>

            <div class="form-group">
                <label for="new_password">Mật khẩu mới</label>
                <input
                    id="new_password"
                    type="password"
                    name="new_password"
                    required
                    placeholder="Nhập mật khẩu mới"
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới</label>
                <input
                    id="confirm_password"
                    type="password"
                    name="confirm_password"
                    required
                    placeholder="Nhập lại mật khẩu mới"
                >
            </div>

            <div class="change-password-actions">
                <a href="profile.php" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu mật khẩu
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>