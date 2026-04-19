<?php require_once __DIR__ . "/../config.php"; ?>

<?php
$message = "";
$messageType = "";
$user = null;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $message = "ID người dùng không hợp lệ!";
    $messageType = "error";
} else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        $message = "Không tìm thấy người dùng!";
        $messageType = "error";
    }
}

$name = $user['name'] ?? '';
$email = $user['email'] ?? '';
$role = $user['role'] ?? 'buyer';
$active = isset($user['active']) ? (int)$user['active'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $active = isset($_POST['active']) ? (int)$_POST['active'] : 1;
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $role === '') {
        $message = "Vui lòng nhập đầy đủ thông tin!";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email không hợp lệ!";
        $messageType = "error";
    } elseif (!in_array($role, ['admin', 'buyer', 'seller'])) {
        $message = "Role không hợp lệ!";
        $messageType = "error";
    } elseif (!in_array($active, [0, 1], true)) {
        $message = "Trạng thái không hợp lệ!";
        $messageType = "error";
    } elseif ($newPassword !== '' && strlen($newPassword) < 6) {
        $message = "Mật khẩu mới phải có ít nhất 6 ký tự!";
        $messageType = "error";
    } elseif ($newPassword !== '' && $newPassword !== $confirmPassword) {
        $message = "Xác nhận mật khẩu không khớp!";
        $messageType = "error";
    } else {
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($checkStmt, "si", $email, $id);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if (mysqli_num_rows($checkResult) > 0) {
            $message = "Email này đã được sử dụng bởi tài khoản khác!";
            $messageType = "error";
        } else {
            if ($newPassword !== '') {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $updateStmt = mysqli_prepare($conn, "
                    UPDATE users
                    SET name = ?, email = ?, role = ?, active = ?, password = ?
                    WHERE id = ?
                ");
                mysqli_stmt_bind_param($updateStmt, "sssisi", $name, $email, $role, $active, $hashedPassword, $id);
            } else {
                $updateStmt = mysqli_prepare($conn, "
                    UPDATE users
                    SET name = ?, email = ?, role = ?, active = ?
                    WHERE id = ?
                ");
                mysqli_stmt_bind_param($updateStmt, "sssii", $name, $email, $role, $active, $id);
            }

            if (mysqli_stmt_execute($updateStmt)) {
                $message = "Cập nhật người dùng thành công!";
                $messageType = "success";

                $reloadStmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
                mysqli_stmt_bind_param($reloadStmt, "i", $id);
                mysqli_stmt_execute($reloadStmt);
                $reloadResult = mysqli_stmt_get_result($reloadStmt);
                $user = mysqli_fetch_assoc($reloadResult);

                $name = $user['name'] ?? $name;
                $email = $user['email'] ?? $email;
                $role = $user['role'] ?? $role;
                $active = isset($user['active']) ? (int)$user['active'] : $active;
            } else {
                $message = "Cập nhật thất bại!";
                $messageType = "error";
            }
        }
    }
}
?>

<style>
    .edit-wrap {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
        max-width: 760px;
    }

    .edit-header {
        margin-bottom: 20px;
    }

    .edit-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }

    .edit-header h2 {
        margin: 0;
        font-size: 30px;
        font-weight: 700;
        color: #1f2937;
    }

    .edit-header p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .back-link {
        text-decoration: none;
        color: #2563eb;
        font-weight: 600;
        font-size: 14px;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .message {
        padding: 13px 14px;
        border-radius: 10px;
        margin-bottom: 18px;
        font-size: 14px;
        border: 1px solid transparent;
    }

    .message.success {
        background: #ecfdf3;
        color: #166534;
        border-color: #bbf7d0;
    }

    .message.error {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    .form-card {
        background: #f9fafb;
        border: 1px solid #eceff3;
        border-radius: 12px;
        padding: 22px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label {
        margin-bottom: 7px;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .form-group input,
    .form-group select {
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        font-size: 14px;
        color: #111827;
        transition: 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.18);
    }

    .helper-text {
        margin-top: 6px;
        font-size: 12px;
        color: #6b7280;
    }

    .form-actions {
        margin-top: 22px;
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn {
        min-height: 40px;
        padding: 9px 16px;
        border: 1px solid #2563eb;
        background: #2563eb;
        color: #fff;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }

    .btn:hover {
        opacity: 0.95;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #ffffff;
        border-color: #d1d5db;
        color: #374151;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
    }

    @media (max-width: 768px) {
        .edit-wrap {
            padding: 16px;
        }

        .edit-header h2 {
            font-size: 24px;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="edit-wrap">
    <div class="edit-header">
        <div class="edit-header-top">
            <h2>Edit User</h2>
            <a href="index.php?page=all_users" class="back-link">← Back to Users</a>
        </div>
        <p>Chỉnh sửa thông tin tài khoản người dùng trong hệ thống CycleMarket</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="message <?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($user): ?>
        <div class="form-card">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?= htmlspecialchars($name) ?>"
                            placeholder="Enter full name"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($email) ?>"
                            placeholder="Enter email"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="buyer" <?= $role === 'buyer' ? 'selected' : '' ?>>Buyer</option>
                            <option value="seller" <?= $role === 'seller' ? 'selected' : '' ?>>Seller</option>
                            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="active">Status</label>
                        <select id="active" name="active" required>
                            <option value="1" <?= $active === 1 ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= $active === 0 ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Leave blank if you do not want to change"
                        >
                        <div class="helper-text">Để trống nếu bạn không muốn đổi mật khẩu.</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Confirm new password"
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Update User</button>
                    <a href="index.php?page=profile&id=<?= (int)$id ?>" class="btn btn-secondary">View Profile</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>