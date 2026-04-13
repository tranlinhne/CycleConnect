<?php
require_once 'inc/auth.php';
require_once 'inc/header.php';
?>
<h2>Tổng quan</h2>
<div class="row mt-4">
    <div class="col-md-3"><div class="card text-white bg-primary mb-3"><div class="card-body"><h5 class="card-title">Sản phẩm</h5><p class="card-text fs-3"><?= $pdo->query("SELECT COUNT(*) FROM bikes")->fetchColumn() ?></p></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-success mb-3"><div class="card-body"><h5 class="card-title">Người dùng</h5><p class="card-text fs-3"><?= $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?></p></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-warning mb-3"><div class="card-body"><h5 class="card-title">Đơn hàng</h5><p class="card-text fs-3"><?= $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?></p></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-info mb-3"><div class="card-body"><h5 class="card-title">Doanh thu</h5><p class="card-text fs-3"><?= number_format($pdo->query("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE payment_status='paid'")->fetchColumn(), 0, ',', '.') ?>đ</p></div></div></div>
</div>
<?php require_once 'inc/footer.php'; ?>