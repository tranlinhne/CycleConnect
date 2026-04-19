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
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = loginUser($login, $password);
    if ($result['success']) {
        $success = $result['message'];
        header('Refresh: 1; url=index.php');
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
    <title>Đăng nhập - GreenRide</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-wrap { 
            max-width: 480px; 
            width: 100%;
            padding: 0 16px;
        }
        .auth-box { 
            background: #fff; 
            border-radius: 16px; 
            box-shadow: 0 12px 48px rgba(0,0,0,0.2); 
            padding: 40px;
            text-align: center;
        }
        .auth-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        .auth-box h1 { 
            margin: 0 0 8px; 
            color: #2f5d62; 
            font-size: 28px; 
            font-weight: 700;
        }
        .auth-box p { 
            margin: 0 0 24px; 
            color: #999; 
            font-size: 14px;
        }
        .form-group { 
            margin-bottom: 16px; 
            text-align: left;
        }
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 700; 
            color: #1f3540; 
            font-size: 13px;
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
        .btn-submit { 
            width: 100%; 
            height: 48px; 
            border: 0; 
            border-radius: 8px; 
            background: #2f5d62; 
            color: #fff; 
            font-weight: 700; 
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }
        .btn-submit:hover { 
            background: #23444a;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(47, 93, 98, 0.3);
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
        .auth-links { 
            margin-top: 20px; 
            display: flex; 
            justify-content: space-between; 
            gap: 12px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .auth-links a { 
            flex: 1;
            color: #2f5d62; 
            font-weight: 600; 
            text-decoration: none; 
            font-size: 13px;
            padding: 10px;
            border-radius: 6px;
            transition: 0.3s;
        }
        .auth-links a:hover {
            background: #f5f5f5;
        }
    </style>
</head>
<body>

<div class="auth-wrap">
    <div class="auth-box">
        <div class="auth-icon">🚲</div>
        <h1>Đăng nhập</h1>
        <p>Quay lại với GreenRide</p>

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

        <form method="POST" action="">
            <div class="form-group">
                <label for="login">
                    <i class="fas fa-user" style="margin-right: 6px;"></i> Tên đăng nhập hoặc Email
                </label>
                <input id="login" type="text" name="login" required placeholder="Nhập tên đăng nhập hoặc email" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock" style="margin-right: 6px;"></i> Mật khẩu
                </label>
                <input id="password" type="password" name="password" required placeholder="Nhập mật khẩu">
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> Đăng nhập
            </button>
        </form>

        <div class="auth-links">
            <a href="forgot-password.php">
                <i class="fas fa-key" style="margin-right: 4px;"></i> Quên mật khẩu?
            </a>
            <a href="register.php">
                <i class="fas fa-user-plus" style="margin-right: 4px;"></i> Đăng ký
            </a>
        </div>
    </div>
</div>

</body>
</html>
