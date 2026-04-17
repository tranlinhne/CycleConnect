<?php include("../config.php"); ?>

<?php
$user = null;
$message = "";
$messageType = "error";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        $message = "User không tồn tại!";
    }
} else {
    $message = "Hãy chọn user từ All Users.";
}
?>

<style>
    .profile-box {
        background: white;
        padding: 24px;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0,0,0,.05);
    }

    .profile-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .profile-header h3 {
        margin: 0;
        font-size: 24px;
        color: #2d3436;
    }

    .profile-header p {
        margin: 5px 0 0;
        color: #888;
        font-size: 14px;
    }

    .actions a {
        text-decoration: none;
        padding: 9px 14px;
        border-radius: 8px;
        font-size: 14px;
        margin-left: 8px;
        display: inline-block;
    }

    .btn-back {
        background: #dfe6e9;
        color: #2d3436;
    }

    .btn-edit {
        background: #00b894;
        color: white;
    }

    .message {
        padding: 12px 14px;
        border-radius: 10px;
        font-size: 14px;
        background: #fdecea;
        color: #c0392b;
    }

    .profile-card {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 14px 20px;
        align-items: center;
        margin-top: 10px;
    }

    .label {
        font-weight: 600;
        color: #636e72;
    }

    .value {
        color: #2d3436;
    }

    .badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        display: inline-block;
    }

    .badge-admin {
        background: #ffe0e0;
        color: #e74c3c;
    }

    .badge-user {
        background: #e3f2fd;
        color: #2196f3;
    }

    .badge-active {
        background: #eafaf1;
        color: #27ae60;
    }

    .badge-inactive {
        background: #fbeaea;
        color: #c0392b;
    }

    .avatar-box {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #6c5ce7;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: bold;
        margin-bottom: 20px;
    }
</style>

<div class="profile-box">
    <div class="profile-header">
        <div>
            <h3>User Profile</h3>
            <p>Xem chi tiết thông tin tài khoản người dùng</p>
        </div>

        <div class="actions">
            <a href="index.php?page=users" class="btn-back">← Back</a>

            <?php if ($user): ?>
                <a href="index.php?page=edit_user&id=<?= (int)$user['id'] ?>" class="btn-edit">Edit User</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php elseif ($user): ?>
        <div class="avatar-box">
            <?= strtoupper(substr($user['name'], 0, 1)) ?>
        </div>

        <div class="profile-card">
            <div class="label">User ID</div>
            <div class="value"><?= (int)$user['id'] ?></div>

            <div class="label">Full Name</div>
            <div class="value"><?= htmlspecialchars($user['name']) ?></div>

            <div class="label">Email</div>
            <div class="value"><?= htmlspecialchars($user['email']) ?></div>

            <div class="label">Role</div>
            <div class="value">
                <?php if ($user['role'] === 'admin'): ?>
                    <span class="badge badge-admin">Admin</span>
                <?php else: ?>
                    <span class="badge badge-user">User</span>
                <?php endif; ?>
            </div>

            <div class="label">Status</div>
            <div class="value">
                <?php if (!empty($user['active']) && $user['active'] == 1): ?>
                    <span class="badge badge-active">Active</span>
                <?php else: ?>
                    <span class="badge badge-inactive">Inactive</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>