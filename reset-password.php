<?php
session_start();
include_once __DIR__ . '/config.php';

$error = '';
$success = '';

$email = $_SESSION['reset_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($email === '' || $code === '' || $new_password === '' || $confirm_password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Xác nhận mật khẩu không khớp.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    } else {
        $stmt = $conn->prepare("
            SELECT id, expires_at, is_used 
            FROM password_resets 
            WHERE email = ? AND reset_code = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows <= 0) {
            $error = 'Mã xác nhận không đúng.';
        } else {
            $reset = $result->fetch_assoc();

            if ((int)$reset['is_used'] === 1) {
                $error = 'Mã này đã được sử dụng.';
            } elseif (strtotime($reset['expires_at']) < time()) {
                $error = 'Mã xác nhận đã hết hạn.';
            } else {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

                $updateUser = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $updateUser->bind_param("ss", $hashedPassword, $email);

                if ($updateUser->execute()) {
                    $markUsed = $conn->prepare("UPDATE password_resets SET is_used = 1 WHERE id = ?");
                    $markUsed->bind_param("i", $reset['id']);
                    $markUsed->execute();

                    unset($_SESSION['reset_email']);
                    header("Location: login.php?reset=1");
                    exit();
                } else {
                    $error = 'Không cập nhật được mật khẩu.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - GreenRide</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">
    <div class="auth-box auth-register">
        <h1 class="auth-title">Đặt lại mật khẩu</h1>
        <p class="auth-subtitle">Nhập email, mã xác nhận và mật khẩu mới.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    required
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="Nhập email"
                >
            </div>

            <div class="form-group">
                <label for="code">Mã xác nhận:</label>
                <input
                    id="code"
                    type="text"
                    name="code"
                    required
                    placeholder="Nhập mã 6 số"
                >
            </div>

            <div class="form-group">
                <label for="new_password">Mật khẩu mới:</label>
                <input
                    id="new_password"
                    type="password"
                    name="new_password"
                    required
                    placeholder="Nhập mật khẩu mới"
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                <input
                    id="confirm_password"
                    type="password"
                    name="confirm_password"
                    required
                    placeholder="Nhập lại mật khẩu mới"
                >
            </div>

            <button type="submit" class="auth-btn">Đổi mật khẩu</button>

            <div class="auth-text">
                <a href="login.php">Quay lại đăng nhập</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>