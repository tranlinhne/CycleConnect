<?php
session_start();
include_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/auth-handler.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $result = sendPasswordResetEmail($email);
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
    <title>Quên mật khẩu - GreenRide</title>
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
        <h1>Quên mật khẩu</h1>
        <p>Nhập email để nhận liên kết đặt lại mật khẩu.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-submit">Gửi liên kết đặt lại</button>
        </form>

        <div class="auth-links">
            <a href="login.php">Quay lại đăng nhập</a>
        </div>
    </div>
</div>
</body>
</html>
