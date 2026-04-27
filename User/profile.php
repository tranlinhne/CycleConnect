<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/auth-handler.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$user = getUserInfo($userId);

if (!$user) {
    header('Location: logout.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $result = updateUserProfile(
            $userId,
            trim($_POST['full_name'] ?? ''),
            trim($_POST['phone'] ?? ''),
            trim($_POST['address'] ?? '')
        );

        if (!empty($result['success'])) {
            $success = $result['message'] ?? 'Cập nhật hồ sơ thành công.';
            $user = getUserInfo($userId);
        } else {
            $error = $result['message'] ?? 'Không thể cập nhật hồ sơ.';
        }
    }

    if (
        isset($_POST['update_avatar']) &&
        isset($_FILES['avatar']) &&
        $_FILES['avatar']['error'] === UPLOAD_ERR_OK
    ) {
        $tmp = $_FILES['avatar']['tmp_name'];
        $name = $_FILES['avatar']['name'];
        $size = (int) $_FILES['avatar']['size'];
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allow = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allow, true)) {
            $error = 'Ảnh đại diện chỉ hỗ trợ JPG, JPEG, PNG, GIF.';
        } elseif ($size > 5 * 1024 * 1024) {
            $error = 'Kích thước ảnh tối đa là 5MB.';
        } else {
            $uploadDir = __DIR__ . '/uploads/avatars';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            $destPath = $uploadDir . '/' . $fileName;
            $dbPath   = 'uploads/avatars/' . $fileName;

            if (move_uploaded_file($tmp, $destPath)) {
                $result = updateUserAvatar($userId, $dbPath);

                if (!empty($result['success'])) {
                    $success = $result['message'] ?? 'Cập nhật ảnh đại diện thành công.';
                    $user = getUserInfo($userId);
                } else {
                    $error = $result['message'] ?? 'Không thể cập nhật ảnh đại diện.';
                }
            } else {
                $error = 'Không thể tải ảnh lên, vui lòng thử lại.';
            }
        }
    }
}

$displayName = !empty($user['full_name']) ? $user['full_name'] : ($user['email'] ?? 'Người dùng');
$joinedAt    = !empty($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'Chưa cập nhật';

$avatarSrc = !empty($user['avatar'])
    ? htmlspecialchars($user['avatar'])
    : 'https://via.placeholder.com/260x260?text=Avatar';

$openEdit = isset($_POST['update_profile']) || isset($_POST['update_avatar']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - GreenRide</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/footer.css">
</head>
<body class="ghp-page">

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="ghp-shell">
    <div class="ghp-page-top">
        <a href="index.php" class="ghp-btn ghp-btn-light">Quay lại</a>
    </div>

    <?php if ($error): ?>
        <div class="ghp-alert ghp-alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="ghp-alert ghp-alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="ghp-panel">
        <aside class="ghp-left">
            <img src="<?= $avatarSrc ?>" alt="Ảnh đại diện" class="ghp-avatar">

            <h2 class="ghp-name"><?= htmlspecialchars($displayName) ?></h2>

            <div class="ghp-badge">Người dùng GreenRide</div>

            <div class="ghp-left-actions">
                <button type="button" class="ghp-btn ghp-btn-light" onclick="toggleProfileEdit()">Chỉnh sửa</button>
                <a href="change-password.php" class="ghp-btn ghp-btn-dark">Đổi mật khẩu</a>
            </div>

            <div id="ghpEditBox" class="ghp-edit-box <?= $openEdit ? 'show' : '' ?>">
                <form method="POST" class="ghp-edit-form">
                    <div class="ghp-form-group">
                        <label for="full_name">Tên</label>
                        <input
                            id="full_name"
                            type="text"
                            name="full_name"
                            value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                            placeholder="Nhập họ và tên"
                        >
                    </div>

                    <div class="ghp-form-group">
                        <label for="phone">Số điện thoại</label>
                        <input
                            id="phone"
                            type="text"
                            name="phone"
                            value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                            placeholder="Nhập số điện thoại"
                        >
                    </div>

                    <div class="ghp-form-group">
                        <label for="address">Khu vực</label>
                        <input
                            id="address"
                            type="text"
                            name="address"
                            value="<?= htmlspecialchars($user['address'] ?? '') ?>"
                            placeholder="Nhập khu vực giao dịch"
                        >
                    </div>

                    <div class="ghp-edit-actions">
                        <button type="submit" name="update_profile" class="ghp-btn ghp-btn-green">Lưu</button>
                        <button type="button" class="ghp-btn ghp-btn-light" onclick="toggleProfileEdit(false)">Hủy</button>
                    </div>
                </form>

                <form method="POST" enctype="multipart/form-data" class="ghp-upload-box">
                    <label for="avatar">Cập nhật ảnh đại diện</label>
                    <input id="avatar" type="file" name="avatar" accept=".jpg,.jpeg,.png,.gif" required>
                    <button type="submit" name="update_avatar" class="ghp-btn ghp-btn-blue">Tải lên ảnh đại diện</button>
                </form>
            </div>
        </aside>

        <section class="ghp-right">
            <div class="ghp-header">
                <h1>Hồ sơ cá nhân</h1>
                <p>Thông tin tài khoản và hồ sơ giao dịch xe đạp của bạn.</p>
            </div>

            <div class="ghp-section">
                <h3>Thông tin cá nhân</h3>
                <div class="ghp-table">
                    <div class="ghp-row">
                        <div class="ghp-label">Email</div>
                        <div class="ghp-value"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                    </div>

                    <div class="ghp-row">
                        <div class="ghp-label">Số điện thoại</div>
                        <div class="ghp-value"><?= htmlspecialchars($user['phone'] ?? 'Chưa cập nhật') ?></div>
                    </div>

                    <div class="ghp-row">
                        <div class="ghp-label">Khu vực</div>
                        <div class="ghp-value"><?= htmlspecialchars($user['address'] ?? 'Chưa cập nhật') ?></div>
                    </div>

                    <div class="ghp-row">
                        <div class="ghp-label">Ngày tham gia</div>
                        <div class="ghp-value"><?= htmlspecialchars($joinedAt) ?></div>
                    </div>
                </div>
            </div>

            <div class="ghp-section">
                <div class="ghp-right-actions">
                    <a href="logout.php" class="ghp-btn ghp-btn-red">Đăng xuất</a>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
function toggleProfileEdit(forceState) {
    const box = document.getElementById('ghpEditBox');
    if (!box) return;

    if (typeof forceState === 'boolean') {
        box.classList.toggle('show', forceState);
        return;
    }

    box.classList.toggle('show');
}
</script>

</body>
</html>