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
        body { margin: 0; background: #f3f6fb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .wrap { max-width: 900px; margin: 30px auto; padding: 0 16px 30px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 6px 24px rgba(0,0,0,.08); padding: 24px; }
        .title { margin: 0 0 18px; color: #2f5d62; font-size: 28px; }
        .alert { border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; font-size: 14px; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d4edda; color: #155724; }
        .profile-grid { display: grid; grid-template-columns: 260px 1fr; gap: 24px; }
        .avatar-box { text-align: center; border: 1px solid #eee; border-radius: 10px; padding: 14px; }
        .avatar-box img { width: 180px; height: 180px; border-radius: 50%; object-fit: cover; border: 4px solid #f0be6f; }
        .avatar-box form { margin-top: 12px; }
        .avatar-box input[type=file] { width: 100%; font-size: 12px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 700; color: #1f3540; }
        .form-group input, .form-group textarea { width: 100%; border: 1px solid #d7d7d7; border-radius: 8px; padding: 10px 12px; font-size: 14px; }
        .form-group textarea { min-height: 110px; resize: vertical; }
        .full { grid-column: 1 / -1; }
        .btn { border: 0; border-radius: 8px; background: #2f5d62; color: #fff; font-weight: 700; padding: 11px 14px; cursor: pointer; }
        .btn:hover { background: #23444a; }
        .muted { color: #666; font-size: 13px; }
        @media (max-width: 800px) {
            .profile-grid { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="wrap">
    <div class="card">
        <h1 class="title">Hồ sơ cá nhân</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="profile-grid">
            <div class="avatar-box">
                <?php
                $avatarSrc = !empty($user['avatar']) ? $user['avatar'] : 'assets/images/about1.jpg';
                ?>
                <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="Avatar">
                <p class="muted" style="margin: 10px 0 8px;">Cập nhật ảnh đại diện</p>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.gif" required>
                    <button type="submit" name="update_avatar" class="btn" style="margin-top: 8px; width: 100%;">Tải ảnh lên</button>
                </form>
            </div>

            <div>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Tên đăng nhập</label>
                            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Họ và tên</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Số điện thoại</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>

                        <div class="form-group full">
                            <label>Địa chỉ</label>
                            <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                        </div>

                        <div class="form-group full">
                            <label>Giới thiệu</label>
                            <textarea name="bio"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="btn">Lưu thay đổi</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
