<?php
session_start();
include_once __DIR__ . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = 'Vui lòng nhập email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows <= 0) {
            $error = 'Email không tồn tại trong hệ thống.';
        } else {
            $code = strval(random_int(100000, 999999));

            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_code_demo'] = $code;

            header("Location: reset-password.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - GreenRide</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">
    <div class="auth-box">
        <h1 class="auth-title">Quên mật khẩu</h1>
        <p class="auth-subtitle">Nhập email để nhận mã lấy lại mật khẩu.</p>

        <?php if ($error): ?>
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
                    placeholder="Nhập email của bạn"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </div>

            <div class="auth-text">
                Nhớ mật khẩu rồi?
                <a href="login.php">Quay lại đăng nhập.</a>
            </div>

            <button type="submit" class="auth-btn">Gửi mã xác nhận</button>
        </form>
    </div>
</div>

</body>
</html>