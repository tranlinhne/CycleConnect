<?php
require_once '../inc/auth.php';
require_once '../inc/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Lấy thông tin người dùng hiện tại
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $active = isset($_POST['active']) ? 1 : 0;
    $new_password = $_POST['password'] ?? '';
    $remove_avatar = isset($_POST['remove_avatar']) ? true : false;
    
    // Validate
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username)) {
        $error = "Vui lòng điền đầy đủ Họ, Tên, Email, Username.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ.";
    } else {
        // Kiểm tra email/username đã tồn tại chưa (trừ chính user đang sửa)
        $check = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $check->execute([$email, $username, $id]);
        if ($check->fetch()) {
            $error = "Email hoặc Username đã được sử dụng bởi người dùng khác.";
        } else {
            // Xử lý ảnh đại diện
            $avatarPath = $user['avatar']; // giữ ảnh cũ
            if ($remove_avatar) {
                // Xoá file avatar cũ nếu tồn tại
                if ($avatarPath && file_exists('../' . $avatarPath)) {
                    unlink('../' . $avatarPath);
                }
                $avatarPath = null;
            }
            
            // Upload ảnh mới (nếu có)
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/avatars/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($ext, $allowed)) {
                    $newName = time() . '_' . uniqid() . '.' . $ext;
                    $destination = $uploadDir . $newName;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                        // Xoá ảnh cũ nếu có và không phải đang giữ
                        if ($avatarPath && file_exists('../' . $avatarPath)) {
                            unlink('../' . $avatarPath);
                        }
                        $avatarPath = 'uploads/avatars/' . $newName;
                    } else {
                        $error = "Không thể upload ảnh đại diện mới.";
                    }
                } else {
                    $error = "Định dạng ảnh không hợp lệ (cho phép JPG, PNG, GIF, WEBP).";
                }
            }
            
            if (empty($error)) {
                // Chuẩn bị câu lệnh UPDATE
                if (!empty($new_password)) {
                    if (strlen($new_password) < 6) {
                        $error = "Mật khẩu mới phải có ít nhất 6 ký tự.";
                    } else {
                        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                        $update = $pdo->prepare("
                            UPDATE users SET 
                                first_name = ?, last_name = ?, email = ?, username = ?, 
                                phone = ?, role = ?, active = ?, password = ?, avatar = ?
                            WHERE id = ?
                        ");
                        $update->execute([$first_name, $last_name, $email, $username, $phone, $role, $active, $hashedPassword, $avatarPath, $id]);
                    }
                } else {
                    // Giữ nguyên mật khẩu cũ
                    $update = $pdo->prepare("
                        UPDATE users SET 
                            first_name = ?, last_name = ?, email = ?, username = ?, 
                            phone = ?, role = ?, active = ?, avatar = ?
                        WHERE id = ?
                    ");
                    $update->execute([$first_name, $last_name, $email, $username, $phone, $role, $active, $avatarPath, $id]);
                }
                
                if (empty($error)) {
                    $success = "Cập nhật thông tin người dùng thành công!";
                    // Refresh dữ liệu hiển thị
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$id]);
                    $user = $stmt->fetch();
                }
            }
        }
    }
}
?>

<style>
    :root {
        --primary-orange: #F57C00;
        --bg-gray: #F5F5F5;
        --text-dark: #263238;
        --border-light: #e0e0e0;
        --card-shadow: 0 6px 12px rgba(0,0,0,0.05);
        --hover-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    body {
        background-color: var(--bg-gray);
        color: var(--text-dark);
    }
    .form-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .card-custom {
        background: white;
        border-radius: 1.2rem;
        border: none;
        box-shadow: var(--card-shadow);
        transition: all 0.2s;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .card-custom:hover {
        box-shadow: var(--hover-shadow);
    }
    .card-header-custom {
        background: white;
        border-bottom: 2px solid var(--primary-orange);
        padding: 1rem 1.5rem;
        font-weight: 700;
        font-size: 1.2rem;
    }
    .card-header-custom i {
        color: var(--primary-orange);
        margin-right: 8px;
    }
    .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.3rem;
    }
    .form-control, .form-select {
        border-radius: 0.75rem;
        border: 1px solid var(--border-light);
        padding: 0.6rem 1rem;
        transition: all 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 3px rgba(245, 124, 0, 0.1);
        outline: none;
    }
    .btn-orange {
        background-color: var(--primary-orange);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.6rem 1.5rem;
        border-radius: 2rem;
        transition: all 0.2s;
    }
    .btn-orange:hover {
        background-color: #e66a00;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 124, 0, 0.3);
    }
    .btn-outline-orange {
        border: 1px solid var(--primary-orange);
        background: transparent;
        color: var(--primary-orange);
        border-radius: 2rem;
        padding: 0.3rem 1rem;
        transition: all 0.2s;
    }
    .btn-outline-orange:hover {
        background-color: var(--primary-orange);
        color: white;
    }
    .avatar-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary-orange);
        margin-top: 0.5rem;
    }
    .current-avatar {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 0.5rem;
    }
    .form-check-input:checked {
        background-color: var(--primary-orange);
        border-color: var(--primary-orange);
    }
    @media (max-width: 768px) {
        .form-container {
            padding: 0 1rem;
        }
    }
</style>

<div class="container-fluid px-4 form-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0" style="border-left: 5px solid #F57C00; padding-left: 1rem;">
            <i class="fas fa-user-edit me-2" style="color: #F57C00;"></i> Chỉnh sửa người dùng
        </h2>
        <a href="index.php" class="btn btn-outline-orange"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card-custom">
        <div class="card-header-custom">
            <i class="fas fa-info-circle"></i> Thông tin tài khoản
        </div>
        <div class="p-4">
            <form method="post" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Họ *</label>
                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tên *</label>
                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tên đăng nhập (Username) *</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" name="password" class="form-control" placeholder="Để trống nếu không đổi">
                        <small class="text-muted">Tối thiểu 6 ký tự, chỉ nhập khi muốn thay đổi</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vai trò</label>
                        <select name="role" class="form-select">
                            <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>Khách hàng</option>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Quản trị viên</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trạng thái</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="active" id="activeCheck" <?= $user['active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="activeCheck">
                                Hoạt động
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Ảnh đại diện</label>
                        <div class="current-avatar">
                            <?php if ($user['avatar'] && file_exists('../' . $user['avatar'])): ?>
                                <img src="../<?= $user['avatar'] ?>" class="avatar-preview" alt="Avatar hiện tại">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_avatar" id="removeAvatar">
                                    <label class="form-check-label" for="removeAvatar">
                                        Xóa ảnh đại diện hiện tại
                                    </label>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">Chưa có ảnh đại diện</span>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="avatar" class="form-control mt-2" accept="image/*" id="avatarInput">
                        <img id="avatarPreview" class="avatar-preview" style="display: none;" alt="Xem trước avatar mới">
                        <small class="text-muted">Hỗ trợ JPG, PNG, GIF, WEBP. Chọn ảnh mới để thay thế.</small>
                    </div>
                    <div class="col-12 text-end mt-3">
                        <button type="submit" class="btn btn-orange"><i class="fas fa-save"></i> Lưu thay đổi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Xem trước avatar mới khi chọn file
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const removeAvatarCheckbox = document.getElementById('removeAvatar');
    
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                avatarPreview.src = event.target.result;
                avatarPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
            // Nếu chọn ảnh mới, tự động bỏ chọn xóa ảnh cũ
            if (removeAvatarCheckbox) removeAvatarCheckbox.checked = false;
        } else {
            avatarPreview.style.display = 'none';
        }
    });
</script>

<?php require_once '../inc/footer.php'; ?>