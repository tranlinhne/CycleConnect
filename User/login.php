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
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    } else {
        $result = loginUser($email, $password);

        if (is_array($result) && !empty($result['success'])) {
            $_SESSION['login_success'] = 'Đăng nhập thành công!';
            header('Location: index.php');
            exit();
        } else {
            $error = $result['message'] ?? 'Email hoặc mật khẩu không đúng.';
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

        <?php if (!empty($_GET['registered'])): ?>
            <div class="alert alert-success">Đăng ký thành công. Vui lòng đăng nhập.</div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    placeholder="Nhập email"
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