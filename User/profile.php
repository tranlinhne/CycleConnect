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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #5f78dc 0%, #6d4fb4 100%);
        }

        .wrap {
            max-width: 980px;
            margin: 70px auto;
            padding: 0 16px 30px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 22px 56px rgba(32, 25, 73, 0.35);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(90deg, #5d81df 0%, #7648ab 100%);
            color: #fff;
            text-align: center;
            font-size: 40px;
            font-weight: 700;
            padding: 18px 20px;
        }

        .card-body {
            padding: 34px 34px 24px;
        }

        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .alert-danger { background: #fde2e5; color: #8b1c2a; border-left: 4px solid #f5a9b2; }
        .alert-success { background: #dcf6e5; color: #115c30; border-left: 4px solid #8cdeb0; }

        .profile-grid {
            display: grid;
            grid-template-columns: 230px 1fr;
            gap: 28px;
            align-items: start;
        }

        .avatar-box {
            text-align: center;
        }

        .avatar-box img {
            width: 170px;
            height: 170px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #5f78dc;
            box-shadow: 0 10px 24px rgba(95, 120, 220, 0.2);
            margin-bottom: 14px;
            background: #f0f0f0;
        }

        .avatar-label {
            font-size: 13px;
            font-weight: 700;
            color: #5a5a5a;
            margin: 8px 0 10px;
            text-transform: uppercase;
        }

        .avatar-box form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .avatar-box input[type=file] {
            font-size: 12px;
            border: 1px dashed #9bb1ef;
            border-radius: 6px;
            padding: 6px;
            background: #fafcff;
        }

        .section-title {
            color: #2c5a69;
            font-size: 33px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .info-section {
            background: #f7f7f7;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .info-section h3 {
            color: #2f5d62;
            font-size: 28px;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .info-box {
            padding: 10px;
            background: #efefef;
            border-radius: 6px;
            color: #33585f;
            min-height: 40px;
            font-size: 15px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 10px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #245466;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .form-group input,
        .form-group textarea {
            border: 1px solid #dadada;
            border-radius: 6px;
            padding: 10px 11px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 86px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #5f78dc;
            box-shadow: 0 0 0 3px rgba(95, 120, 220, 0.15);
        }

        .full { grid-column: 1 / -1; }

        .button-group {
            margin-top: 14px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .btn {
            border: 0;
            border-radius: 6px;
            padding: 11px 12px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            background: #2f2f33;
            transition: 0.2s;
        }

        .btn:hover {
            background: #1f2024;
        }

        .btn-upload {
            background: linear-gradient(90deg, #6179de 0%, #7549aa 100%);
        }

        .btn-upload:hover {
            background: linear-gradient(90deg, #5169cd 0%, #653999 100%);
        }

        @media (max-width: 860px) {
            .wrap { margin: 30px auto; }
            .card-body { padding: 20px; }
            .profile-grid { grid-template-columns: 1fr; }
            .avatar-box { max-width: 280px; margin: 0 auto; }
            .form-grid { grid-template-columns: 1fr; }
            .button-group { grid-template-columns: 1fr; }
            .section-title { font-size: 30px; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-circle"></i> Hồ sơ
        </div>

        <div class="card-body">

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
                    <button type="submit" name="update_avatar" class="btn btn-upload" style="width: 100%;">
                        <i class="fas fa-upload"></i> Tải ảnh lên
                    </button>
                </form>
            </div>

            <!-- PROFILE INFO SECTION -->
            <div>
                <h2 class="section-title"><i class="fas fa-user" style="font-size: 30px;"></i> Thông tin</h2>
                <!-- Information Display -->
                <div class="info-section">
                    <div class="form-grid">
                        <div>
                            <label>TÊN ĐĂNG NHẬP</label>
                            <div class="info-box">
                                <?= htmlspecialchars($user['username']) ?>
                            </div>
                        </div>
                        <div>
                            <label>EMAIL</label>
                            <div class="info-box">
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
                        <button type="submit" name="update_profile" class="btn">
                            <i class="fas fa-save"></i> Lưu
                        </button>
                        <a href="change-password.php" class="btn">
                            <i class="fas fa-lock"></i> Đổi mật khẩu
                        </a>
                        <a href="index.php" class="btn">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
</div>
</body>
</html>
