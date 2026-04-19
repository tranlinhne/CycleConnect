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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .wrap { max-width: 500px; margin: 40px auto; padding: 0 16px 40px; }
        .card { 
            background: #fff; 
            border-radius: 16px; 
            box-shadow: 0 12px 48px rgba(0,0,0,0.2); 
            padding: 40px;
        }
        .card-header { 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .card-header h1 { 
            margin: 0 0 8px; 
            color: #2f5d62; 
            font-size: 28px; 
            font-weight: 700;
        }
        .card-header p {
            color: #999;
            font-size: 14px;
        }
        .alert { 
            border-radius: 8px; 
            padding: 12px 16px; 
            margin-bottom: 18px; 
            font-size: 14px; 
            border-left: 4px solid;
        }
        .alert-danger { 
            background: #f8d7da; 
            color: #721c24; 
            border-left-color: #f5c6cb;
        }
        .alert-success { 
            background: #d4edda; 
            color: #155724; 
            border-left-color: #c3e6cb;
        }
        .form-group { 
            margin-bottom: 18px;
        }
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 700; 
            color: #1f3540; 
            font-size: 13px;
            text-transform: uppercase;
        }
        .form-group input { 
            width: 100%; 
            height: 48px; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            padding: 0 14px; 
            font-size: 14px;
            transition: 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2f5d62;
            box-shadow: 0 0 0 3px rgba(47, 93, 98, 0.1);
        }
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .btn { 
            flex: 1;
            border: 0; 
            border-radius: 8px; 
            color: #fff; 
            font-weight: 700; 
            padding: 12px 24px; 
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-primary {
            background: #2f5d62;
        }
        .btn-primary:hover { 
            background: #23444a;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(47, 93, 98, 0.3);
        }
        .btn-secondary {
            background: #666;
        }
        .btn-secondary:hover { 
            background: #555;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 102, 102, 0.3);
        }
        @media (max-width: 600px) {
            .card { padding: 20px; }
            .button-group { flex-direction: column; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="wrap">
    <div class="card">
        <div class="card-header">
            <h1>🔐 Đổi mật khẩu</h1>
            <p>Cập nhật mật khẩu của bạn để bảo vệ tài khoản</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <strong>⚠️ Lỗi:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <strong>✓ Thành công:</strong> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="old_password">
                    <i class="fas fa-lock" style="margin-right: 6px;"></i> Mật khẩu hiện tại
                </label>
                <input id="old_password" type="password" name="old_password" required placeholder="Nhập mật khẩu hiện tại">
            </div>

            <div class="form-group">
                <label for="new_password">
                    <i class="fas fa-key" style="margin-right: 6px;"></i> Mật khẩu mới
                </label>
                <input id="new_password" type="password" name="new_password" required placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)">
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-check-circle" style="margin-right: 6px;"></i> Xác nhận mật khẩu mới
                </label>
                <input id="confirm_password" type="password" name="confirm_password" required placeholder="Nhập lại mật khẩu mới">
            </div>

            <div class="button-group">
                <a href="profile.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>