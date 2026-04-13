<?php
require_once '../inc/auth.php';
require_once '../inc/header.php';

$stmt = $pdo->query("SELECT id, first_name, last_name, email, username, phone, role, active, created_at FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
?>
<h2>Quản lý người dùng</h2>
<table class="table table-striped">
    <thead><tr><th>ID</th><th>Họ tên</th><th>Email</th><th>Username</th><th>Vai trò</th><th>Trạng thái</th><th>Hành động</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= $u['role'] ?></td>
        <td><?= $u['active'] ? 'Hoạt động' : 'Khóa' ?></td>
        <td>
            <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-primary">Sửa</a>
            <a href="delete.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa người dùng này?')">Xóa</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once '../inc/footer.php'; ?>