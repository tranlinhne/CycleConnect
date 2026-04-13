<?php
require_once '../inc/auth.php';
require_once '../inc/header.php';

$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$stmt = $pdo->prepare("SELECT b.*, c.name as cat_name, br.name as brand_name FROM bikes b 
                       LEFT JOIN categories c ON b.category_id = c.id 
                       LEFT JOIN brands br ON b.brand_id = br.id
                       ORDER BY b.id DESC LIMIT ? OFFSET ?");
$stmt->bindParam(1, $limit, PDO::PARAM_INT);
$stmt->bindParam(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$bikes = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM bikes")->fetchColumn();
$pages = ceil($total / $limit);
?>
<div class="d-flex justify-content-between mb-3">
    <h2>Quản lý sản phẩm</h2>
    <a href="add.php" class="btn btn-success">+ Thêm xe mới</a>
</div>
<table class="table table-bordered">
    <thead><tr><th>ID</th><th>Tiêu đề</th><th>Danh mục</th><th>Thương hiệu</th><th>Giá</th><th>Trạng thái</th><th>Hành động</th></tr></thead>
    <tbody>
    <?php foreach ($bikes as $b): ?>
    <tr>
        <td><?= $b['id'] ?></td>
        <td><?= htmlspecialchars($b['title']) ?></td>
        <td><?= htmlspecialchars($b['cat_name']) ?></td>
        <td><?= htmlspecialchars($b['brand_name']) ?></td>
        <td><?= number_format($b['price'],0,',','.') ?>đ</td>
        <td><?= $b['status'] ?></td>
        <td>
            <a href="edit.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">Sửa</a>
            <a href="delete.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa xe này?')">Xóa</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<nav><ul class="pagination"><?php for($i=1;$i<=$pages;$i++): ?><li class="page-item"><a class="page-link" href="?page=<?=$i?>"><?=$i?></a></li><?php endfor; ?></ul></nav>
<?php require_once '../inc/footer.php'; ?>