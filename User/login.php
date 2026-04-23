<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/auth-handler.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ tài khoản và mật khẩu.';
    } else {
        $result = loginUser($login, $password);

        if (is_array($result) && !empty($result['success'])) {
            $_SESSION['login_success'] = 'Đăng nhập thành công!';
            header('Location: index.php');
            exit();
        } else {
            $error = $result['message'] ?? 'Tài khoản hoặc mật khẩu không đúng.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - GreenRide</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">
    <div class="auth-box">
        <h1 class="auth-title">Đăng nhập</h1>
        <p class="auth-subtitle">Nhập thông tin để tiếp tục.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="login">Tài khoản:</label>
                <input
                    id="login"
                    type="text"
                    name="login"
                    required
                    value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                    placeholder="Nhập tên tài khoản hoặc email"
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

            <div class="auth-text">
                Chưa có tài khoản?
                <a href="register.php">Đăng ký ngay.</a>
            </div>

            <a class="auth-link" href="forgot-password.php">Quên mật khẩu?</a>

            <button type="submit" class="auth-btn">Đăng nhập</button>
        </form>
    </div>
</div>

</body>
</html>