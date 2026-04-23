<?php
session_start();
include_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/auth-handler.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $full_name = $_POST['full_name'] ?? '';

    $result = registerUser($username, $email, $password, $confirm_password, $phone, $full_name);

    if ($result['success']) {
        header('Location: login.php?registered=1');
        exit();
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
    <title>Đăng ký - GreenRide</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">
    <div class="auth-box auth-register">
        <h1 class="auth-title">Đăng ký tài khoản</h1>
        <p class="auth-subtitle">Điền thông tin để tạo tài khoản mới.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="auth-grid">
                <div class="form-group">
                    <label for="username">Tên đăng nhập:</label>
                    <input
                        id="username"
                        type="text"
                        name="username"
                        required
                        placeholder="Nhập tên đăng nhập"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        required
                        placeholder="Nhập email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="full_name">Họ và tên:</label>
                    <input
                        id="full_name"
                        type="text"
                        name="full_name"
                        placeholder="Nhập họ và tên"
                        value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="phone">Số điện thoại:</label>
                    <input
                        id="phone"
                        type="text"
                        name="phone"
                        placeholder="Nhập số điện thoại"
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu:</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        placeholder="Nhập mật khẩu"
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu:</label>
                    <input
                        id="confirm_password"
                        type="password"
                        name="confirm_password"
                        required
                        placeholder="Nhập lại mật khẩu"
                    >
                </div>
            </div>

            <div class="auth-text">
                Đã có tài khoản?
                <a href="login.php">Đăng nhập ngay.</a>
            </div>

            <button type="submit" class="auth-btn">Đăng ký</button>
        </form>
    </div>
</div>

</body>
</html>