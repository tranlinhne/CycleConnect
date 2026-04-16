<?php
session_start();
include_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/auth-handler.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$tokenValid = false;

if ($token !== '') {
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expire > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $tokenValid = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

if (!$tokenValid) {
    $error = 'Token không hợp lệ hoặc đã hết hạn';
}

if ($tokenValid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $result = resetPassword($token, $password, $confirm);
    if ($result['success']) {
        $success = $result['message'];
        $tokenValid = false;
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
    <title>Đặt lại mật khẩu - GreenRide</title>
    <style>
        body { margin: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .auth-wrap { max-width: 480px; margin: 60px auto 0; padding: 0 16px 30px; }
        .auth-box { background: #fff; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,.16); padding: 28px; }
        .auth-box h1 { margin: 0 0 8px; color: #2f5d62; font-size: 30px; }
        .auth-box p { margin: 0 0 18px; color: #666; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 700; color: #1f3540; }
        .form-group input { width: 100%; height: 44px; border: 1px solid #d7d7d7; border-radius: 8px; padding: 0 12px; font-size: 14px; }
        .btn-submit { width: 100%; height: 44px; border: 0; border-radius: 8px; background: #2f5d62; color: #fff; font-weight: 700; cursor: pointer; }
        .alert { border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; font-size: 14px; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d4edda; color: #155724; }
        .auth-links { margin-top: 14px; }
        .auth-links a { color: #2f5d62; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="auth-wrap">
    <div class="auth-box">
        <h1>Đặt lại mật khẩu</h1>
        <p>Nhập mật khẩu mới cho tài khoản của bạn.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($tokenValid): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">Mật khẩu mới</label>
                    <input id="password" type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu</label>
                    <input id="confirm_password" type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-submit">Đặt lại mật khẩu</button>
            </form>
        <?php endif; ?>

        <div class="auth-links">
            <a href="login.php">Quay lại đăng nhập</a>
        </div>
    </div>
</div>
</body>
</html>
