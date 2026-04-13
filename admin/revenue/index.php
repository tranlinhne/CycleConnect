<?php
require_once '../inc/auth.php';
require_once '../inc/header.php';

$year = date('Y');
$monthly = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as revenue FROM orders WHERE payment_status='paid' GROUP BY month ORDER BY month DESC LIMIT 12");
$monthly->execute();
$data = $monthly->fetchAll();

$total = $pdo->query("SELECT SUM(total_price) FROM orders WHERE payment_status='paid'")->fetchColumn();
?>
<h2>Báo cáo doanh thu</h2>
<div class="row">
    <div class="col-md-4"><div class="card bg-success text-white p-3"><h4>Tổng doanh thu</h4><h2><?= number_format($total,0,',','.') ?>đ</h2></div></div>
</div>
<h4 class="mt-4">Doanh thu theo tháng (12 tháng gần nhất)</h4>
<table class="table">
    <thead><tr><th>Tháng</th><th>Doanh thu (đ)</th></tr></thead>
    <tbody>
    <?php foreach ($data as $row): ?>
    <tr><td><?= $row['month'] ?></td><td><?= number_format($row['revenue'],0,',','.') ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once '../inc/footer.php'; ?>