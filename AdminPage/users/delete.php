<?php
require_once '../inc/auth.php';


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Lấy thông tin người dùng
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    header('Location: index.php');
    exit;
}

// Không cho xóa chính mình (tài khoản đang đăng nhập)
if ($id == $_SESSION['user_id']) {
    $error = "Bạn không thể xóa tài khoản của chính mình đang đăng nhập.";
} else {
    // Xử lý xóa khi người dùng xác nhận
    if (isset($_POST['confirm_delete'])) {
        // Xóa file avatar nếu có
        if ($user['avatar'] && file_exists('../' . $user['avatar'])) {
            unlink('../' . $user['avatar']);
        }
        // Xóa người dùng (các bảng liên quan có ON DELETE CASCADE sẽ tự xóa)
        $delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $delete->execute([$id]);
        header('Location: index.php');
        exit;
    }
}
require_once '../inc/header.php';
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
    .delete-container {
        max-width: 600px;
        margin: 2rem auto;
    }
    .card-custom {
        background: white;
        border-radius: 1.2rem;
        border: none;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .card-header-custom {
        background: white;
        border-bottom: 2px solid #dc3545;
        padding: 1rem 1.5rem;
        font-weight: 700;
        font-size: 1.2rem;
        color: #dc3545;
    }
    .card-header-custom i {
        margin-right: 8px;
    }
    .user-info {
        background: #f8f9fa;
        border-radius: 0.75rem;
        padding: 1rem;
        margin: 1rem 0;
    }
    .user-info p {
        margin: 0.5rem 0;
    }
    .user-info strong {
        color: var(--primary-orange);
        width: 120px;
        display: inline-block;
    }
    .btn-danger-custom {
        background-color: #dc3545;
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.6rem 1.5rem;
        border-radius: 2rem;
        transition: all 0.2s;
    }
    .btn-danger-custom:hover {
        background-color: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220,53,69,0.3);
    }
    .btn-outline-orange {
        border: 1px solid var(--primary-orange);
        background: transparent;
        color: var(--primary-orange);
        border-radius: 2rem;
        padding: 0.6rem 1.5rem;
        transition: all 0.2s;
    }
    .btn-outline-orange:hover {
        background-color: var(--primary-orange);
        color: white;
    }
    .avatar-thumb {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary-orange);
        vertical-align: middle;
        margin-right: 10px;
    }
</style>

<div class="container delete-container">
    <div class="card-custom">
        <div class="card-header-custom">
            <i class="fas fa-exclamation-triangle"></i> Xác nhận xóa người dùng
        </div>
        <div class="p-4">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-ban"></i> <?= $error ?>
                </div>
                <div class="text-end">
                    <a href="index.php" class="btn btn-outline-orange">Quay lại danh sách</a>
                </div>
            <?php else: ?>
                <p class="mb-3">Bạn có chắc chắn muốn xóa người dùng sau đây? Hành động này không thể hoàn tác.</p>
                
                <div class="user-info">
                    <?php if ($user['avatar'] && file_exists('../' . $user['avatar'])): ?>
                        <img src="../<?= $user['avatar'] ?>" class="avatar-thumb" alt="Avatar">
                    <?php else: ?>
                        <i class="fas fa-user-circle fa-3x" style="color: #F57C00; vertical-align: middle; margin-right: 10px;"></i>
                    <?php endif; ?>
                    <p><strong>ID:</strong> <?= $user['id'] ?></p>
                    <p><strong>Họ tên:</strong> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                    <p><strong>Vai trò:</strong> <?= $user['role'] == 'admin' ? 'Quản trị viên' : 'Khách hàng' ?></p>
                    <p><strong>Trạng thái:</strong> <?= $user['active'] ? 'Hoạt động' : 'Khóa' ?></p>
                    <p><strong>Ngày tạo:</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></p>
                </div>
                
                <form method="post">
                    <div class="d-flex justify-content-between gap-2">
                        <a href="index.php" class="btn btn-outline-orange"><i class="fas fa-arrow-left"></i> Hủy bỏ</a>
                        <button type="submit" name="confirm_delete" class="btn btn-danger-custom" onclick="return confirm('Lần cuối: Bạn có chắc muốn xóa người dùng này?');">
                            <i class="fas fa-trash-alt"></i> Xóa vĩnh viễn
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../inc/footer.php'; ?>