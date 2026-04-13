<?php
require_once 'inc/config.php';
// Kiểm tra xem đã có admin chưa
$stmtCheck = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
$adminCount = $stmtCheck->fetchColumn();

if ($adminCount == 0) {
    // Tạo tài khoản admin mặc định
    $defaultUsername = 'admin';
    $defaultEmail = 'admin@example.com';
    $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);

    $stmtInsert = $pdo->prepare("
        INSERT INTO users (username, email, password, role, first_name, last_name, active)
        VALUES (?, ?, ?, 'admin', 'Super', 'Admin', 1)
    ");
    $stmtInsert->execute([
        $defaultUsername,
        $defaultEmail,
        $defaultPassword
    ]);
}


if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, username, password, role, first_name, last_name FROM users WHERE (username = ? OR email = ?) AND role = 'admin' AND active = 1");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();
        echo "pass admin: " . $admin['password'];
        echo "pass: " . $password;
        if ($admin && password_verify($password, $admin['password'])) {
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['role'] = 'admin';
            $_SESSION['fullname'] = $admin['first_name'] . ' ' . $admin['last_name'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng, hoặc tài khoản không phải admin.';
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background: #f8f9fa; } .login-form { max-width: 400px; margin: 100px auto; }</style>
</head>
<body>
<div class="container">
    <div class="login-form card shadow">
        <div class="card-header bg-primary text-white">Đăng nhập Admin</div>
        <div class="card-body">
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label>Tên đăng nhập hoặc Email</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>