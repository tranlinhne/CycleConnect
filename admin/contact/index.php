<?php
require_once '../inc/auth.php';
require_once '../inc/header.php';

$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC");
$msgs = $stmt->fetchAll();
?>
<h2>Liên hệ khách hàng</h2>
<table class="table">
    <thead><tr><th>ID</th><th>Họ tên</th><th>Email</th><th>Chủ đề</th><th>Đã đọc</th><th>Ngày gửi</th><th>Thao tác</th></tr></thead>
    <tbody>
    <?php foreach ($msgs as $m): ?>
    <tr class="<?= $m['is_read'] ? '' : 'table-warning' ?>">
        <td><?= $m['id'] ?></td>
        <td><?= htmlspecialchars($m['fullname']) ?></td>
        <td><?= htmlspecialchars($m['email']) ?></td>
        <td><?= htmlspecialchars($m['subject']) ?></td>
        <td><?= $m['is_read'] ? 'Đã đọc' : 'Chưa đọc' ?></td>
        <td><?= $m['created_at'] ?></td>
        <td><a href="view.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-info">Xem / Trả lời</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once '../inc/footer.php'; ?>