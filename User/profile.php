<?php
session_start();
include_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/auth-handler.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getUserInfo((int)$_SESSION['user_id']);
if (!$user) {
    header('Location: logout.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $result = updateUserProfile(
            (int)$_SESSION['user_id'],
            $_POST['full_name'] ?? '',
            $_POST['phone'] ?? '',
            $_POST['address'] ?? '',
            $_POST['bio'] ?? ''
        );
        if ($result['success']) {
            $success = $result['message'];
            $user = getUserInfo((int)$_SESSION['user_id']);
        } else {
            $error = $result['message'];
        }
    }

    if (isset($_POST['update_avatar']) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['avatar']['tmp_name'];
        $name = $_FILES['avatar']['name'];
        $size = (int)$_FILES['avatar']['size'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allow = array('jpg', 'jpeg', 'png', 'gif');

        if (!in_array($ext, $allow, true)) {
            $error = 'Ảnh đại diện chỉ hỗ trợ JPG, JPEG, PNG, GIF';
        } elseif ($size > 5 * 1024 * 1024) {
            $error = 'Kích thước ảnh tối đa là 5MB';
        } else {
            $uploadDir = __DIR__ . '/uploads/avatars';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = 'avatar_' . (int)$_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $destPath = $uploadDir . '/' . $fileName;
            $dbPath = 'uploads/avatars/' . $fileName;

            if (move_uploaded_file($tmp, $destPath)) {
                $result = updateUserAvatar((int)$_SESSION['user_id'], $dbPath);
                if ($result['success']) {
                    $success = $result['message'];
                    $user = getUserInfo((int)$_SESSION['user_id']);
                } else {
                    $error = $result['message'];
                }
            } else {
                $error = 'Không thể tải ảnh lên, vui lòng thử lại';
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
    <title>Hồ sơ cá nhân - GreenRide</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .wrap { max-width: 1100px; margin: 40px auto; padding: 0 16px 40px; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 12px 48px rgba(0,0,0,0.15); padding: 40px; }
        .card-header { text-align: center; margin-bottom: 30px; }
        .card-header h1 { margin: 0; color: #2f5d62; font-size: 32px; }
        
        .alert { border-radius: 8px; padding: 12px 16px; margin-bottom: 18px; font-size: 14px; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #c3e6cb; }
        
        .profile-grid { display: grid; grid-template-columns: 280px 1fr; gap: 32px; }
        
        .avatar-box { 
            text-align: center; 
            border: 3px solid #f0be6f; 
            border-radius: 20px; 
            padding: 20px; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .avatar-box img { 
            width: 200px; 
            height: 200px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 5px solid #fff;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            margin-bottom: 14px;
        }
        .avatar-label { 
            font-weight: 700; 
            color: #2f5d62; 
            margin: 16px 0 12px; 
            font-size: 15px;
        }
        .avatar-box form { margin-top: 12px; display: flex; flex-direction: column; gap: 8px; }
        .avatar-box input[type=file] { 
            font-size: 12px; 
            padding: 8px;
            border: 2px dashed #2f5d62;
            border-radius: 8px;
        }
        
        .form-section label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 700; 
            color: #1f3540;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group input, 
        .form-group textarea { 
            border: 1px solid #d7d7d7; 
            border-radius: 8px; 
            padding: 12px 14px; 
            font-size: 14px;
            transition: 0.3s;
        }
        .form-group input:focus, 
        .form-group textarea:focus { 
            outline: none;
            border-color: #2f5d62;
            box-shadow: 0 0 0 3px rgba(47, 93, 98, 0.1);
        }
        .form-group textarea { 
            min-height: 120px; 
            resize: vertical; 
        }
        .full { grid-column: 1 / -1; }
        
        .button-group { 
            display: flex; 
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #eee;
        }
        
        .btn { 
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
        
        .btn-danger {
            background: #e74c3c;
        }
        .btn-danger:hover { 
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);
        }
        
        .info-section { 
            background: #f9fafb; 
            border-radius: 8px; 
            padding: 16px; 
            margin-bottom: 20px;
        }
        .info-section h3 {
            color: #2f5d62;
            font-size: 16px;
            margin: 0 0 12px;
        }
        
        @media (max-width: 800px) {
            .profile-grid { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
            .card { padding: 20px; }
            .button-group { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="wrap">
    <div class="card">
        <div class="card-header">
            <h1>👤 Hồ sơ cá nhân</h1>
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

        <div class="profile-grid">
            <!-- AVATAR SECTION -->
            <div class="avatar-box">
                <?php
                $avatarSrc = !empty($user['avatar']) ? $user['avatar'] : 'https://via.placeholder.com/200';
                ?>
                <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="Avatar">
                <p class="avatar-label">Cập nhật ảnh đại diện</p>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.gif" required>
                    <button type="submit" name="update_avatar" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-upload"></i> Tải ảnh lên
                    </button>
                </form>
            </div>

            <!-- PROFILE INFO SECTION -->
            <div>
                <!-- Information Display -->
                <div class="info-section">
                    <h3>📋 Thông tin</h3>
                    <div class="form-grid">
                        <div>
                            <label>TÊN ĐĂNG NHẬP</label>
                            <div style="padding: 12px; background: #f5f5f5; border-radius: 8px; color: #666; font-size: 14px;">
                                <?= htmlspecialchars($user['username']) ?>
                            </div>
                        </div>
                        <div>
                            <label>EMAIL</label>
                            <div style="padding: 12px; background: #f5f5f5; border-radius: 8px; color: #666; font-size: 14px;">
                                <?= htmlspecialchars($user['email']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Editable Form -->
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>HỌ VÀ TÊN</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" placeholder="Nhập họ và tên">
                        </div>
                        <div class="form-group">
                            <label>ĐIỆN THOẠI</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Nhập số điện thoại">
                        </div>

                        <div class="form-group full">
                            <label>ĐỊA CHỈ</label>
                            <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Nhập địa chỉ">
                        </div>

                        <div class="form-group full">
                            <label>GIỚI THIỆU</label>
                            <textarea name="bio" placeholder="Nhập thông tin giới thiệu về bạn..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="button-group">
                        <a href="change-password.php" class="btn btn-secondary">
                            <i class="fas fa-lock"></i> Đổi mật khẩu
                        </a>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
