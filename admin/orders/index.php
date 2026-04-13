<?php
require_once '../inc/auth.php';
require_once '../inc/header.php';

$stmt = $pdo->prepare("SELECT o.*, b.title as bike_title, buyer.username as buyer_name, seller.username as seller_name 
                       FROM orders o 
                       JOIN bikes b ON o.bike_id = b.id 
                       JOIN users buyer ON o.buyer_id = buyer.id 
                       JOIN users seller ON o.seller_id = seller.id 
                       ORDER BY o.id DESC");
$stmt->execute();
$orders = $stmt->fetchAll();
?>
<h2>Quản lý đơn hàng</h2>
<table class="table table-bordered">
    <thead><tr><th>ID</th><th>Xe</th><th>Người mua</th><th>Người bán</th><th>Tổng tiền</th><th>Trạng thái</th><th>Thanh toán</th><th>Hành động</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
    <tr>
        <td><?= $o['id'] ?></td>
        <td><?= htmlspecialchars($o['bike_title']) ?></td>
        <td><?= htmlspecialchars($o['buyer_name']) ?></td>
        <td><?= htmlspecialchars($o['seller_name']) ?></td>
        <td><?= number_format($o['total_price'],0,',','.') ?>đ</td>
        <td><?= $o['status'] ?></td>
        <td><?= $o['payment_status'] ?></td>
        <td>
            <form method="post" action="update_status.php" style="display:inline-block">
                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                <select name="status" class="form-select form-select-sm d-inline w-auto">
                    <option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>pending</option>
                    <option value="confirmed" <?= $o['status']=='confirmed'?'selected':'' ?>>confirmed</option>
                    <option value="shipped" <?= $o['status']=='shipped'?'selected':'' ?>>shipped</option>
                    <option value="delivered" <?= $o['status']=='delivered'?'selected':'' ?>>delivered</option>
                    <option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?>>cancelled</option>
                </select>
                <button type="submit" class="btn btn-sm btn-secondary">Cập nhật</button>
            </form>
            <a href="?mark_paid=<?= $o['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Đánh dấu đã thanh toán?')">Đã thanh toán</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php
if (isset($_GET['mark_paid'])) {
    $id = (int)$_GET['mark_paid'];
    $pdo->prepare("UPDATE orders SET payment_status='paid' WHERE id=?")->execute([$id]);
    header('Location: index.php');
    exit;
}
require_once '../inc/footer.php';
?>