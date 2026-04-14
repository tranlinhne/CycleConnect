<?php
require_once 'inc/config.php';

// Ensure session is started if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Admin Login | Hệ thống quản trị</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Inter & Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            /* Mountain bike background image (high-res, free to use) */
            background-image: url('../AdminPage/uploads/1776181193_69de5fc95dbfe.png');
            background-size: cover;
            background-position: center 30%;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Dark overlay to improve text contrast */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.4) 100%);
            pointer-events: none;
            z-index: 0;
        }

        .login-wrapper {
            width: 100%;
            padding: 1rem;
            position: relative;
            z-index: 2;
            animation: fadeInUp 0.8s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Card with slight transparency but still solid enough */
        .login-card {
            border: none;
            border-radius: 32px;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(0px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.4);
        }

        .card-header-custom {
            background: linear-gradient(120deg, #F57C00, #7C3AED);
            padding: 1.8rem 2rem;
            text-align: center;
            border-bottom: none;
        }

        .card-header-custom h3 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1.8rem;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .card-header-custom p {
            font-size: 0.9rem;
            opacity: 0.85;
            margin-top: 8px;
            margin-bottom: 0;
        }

        .avatar-icon {
            background: rgba(255,255,255,0.2);
            width: 70px;
            height: 70px;
            line-height: 70px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 2.5rem;
            backdrop-filter: blur(4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .card-body-custom {
            padding: 2rem 2rem 2.2rem;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group-custom .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.1rem;
            z-index: 10;
            transition: color 0.2s;
        }

        .form-control-custom {
            width: 100%;
            padding: 14px 16px 14px 48px;
            font-size: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 60px;
            background-color: #f9fafb;
            transition: all 0.25s ease;
            font-weight: 500;
        }

        .form-control-custom:focus {
            border-color: #F57C00;
            box-shadow: 0 0 0 4px rgba(245,124,0,0.15);
            background-color: #ffffff;
            outline: none;
        }

        .form-control-custom:focus + .input-icon {
            color: #F57C00;
        }

        .btn-gradient {
            background: linear-gradient(95deg, #F57C00, #7C3AED);
            border: none;
            padding: 12px 20px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 60px;
            color: white;
            width: 100%;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
            letter-spacing: 0.5px;
        }

        .btn-gradient:hover {
            transform: scale(1.02);
            background: linear-gradient(95deg, #F57C00, #6d28d9);
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.5);
        }

        .btn-gradient:active {
            transform: scale(0.98);
        }

        .alert-custom {
            border-radius: 60px;
            background-color: #fee2e2;
            border-left: 5px solid #dc2626;
            font-size: 0.9rem;
            padding: 0.8rem 1.2rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .footer-note {
            text-align: center;
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 1.5rem;
            border-top: 1px solid #edf2f7;
            padding-top: 1.2rem;
        }

        @media (max-width: 576px) {
            .card-header-custom {
                padding: 1.2rem 1rem;
            }
            .card-body-custom {
                padding: 1.5rem;
            }
            .avatar-icon {
                width: 55px;
                height: 55px;
                font-size: 2rem;
            }
            .card-header-custom h3 {
                font-size: 1.5rem;
            }
        }

        .btn-gradient i {
            transition: transform 0.2s;
        }
        .btn-gradient:hover i {
            transform: translateX(4px);
        }
    </style>
</head>
<body>
<div class="container login-wrapper">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="login-card">
                <div class="card-header-custom text-white">
                    <div class="avatar-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3><i class="fas fa-shield-alt me-2"></i> Admin Panel</h3>
                    <p>Đăng nhập vào hệ thống quản trị</p>
                </div>
                <div class="card-body-custom">
                    <?php if ($error): ?>
                        <div class="alert alert-custom d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="input-group-custom">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="username" class="form-control form-control-custom" placeholder="Tên đăng nhập hoặc Email" required autofocus>
                        </div>
                        
                        <div class="input-group-custom">
                            <i class="fas fa-key input-icon"></i>
                            <input type="password" name="password" class="form-control form-control-custom" placeholder="Mật khẩu" required>
                        </div>
                        
                        <button type="submit" class="btn btn-gradient">
                            <i class="fas fa-arrow-right-to-bracket me-2"></i> Đăng nhập
                        </button>
                        
                        <div class="footer-note">
                            <i class="fas fa-shield-heart me-1"></i> Khu vực dành cho Quản trị viên
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>