<?php if (!isset($hideHeader)): ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - GreenRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand"  href="<?= BASE_URL ?>dashboard.php">GreenRide Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link"  href="<?= BASE_URL ?>products/index.php"><i class="fas fa-bicycle"></i> Quản lý sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link"  href="<?= BASE_URL ?>users/index.php"><i class="fas fa-users"></i> Quản lý người dùng</a></li>
                <li class="nav-item"><a class="nav-link"  href="<?= BASE_URL ?>contacts/index.php"><i class="fas fa-envelope"></i> Liên hệ khách hàng</a></li>
                <li class="nav-item"><a class="nav-link"  href="<?= BASE_URL ?>revenue/index.php"><i class="fas fa-chart-line"></i> Doanh thu</a></li>
                <li class="nav-item"><a class="nav-link"  href="<?= BASE_URL ?>orders/index.php"><i class="fas fa-shopping-cart"></i> Đơn hàng</a></li>
            </ul>
            <span class="navbar-text me-3">Xin chào, <?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?></span>
            <a  href="<?= BASE_URL ?>logout.php" class="btn btn-outline-light btn-sm">Đăng xuất</a>
        </div>
    </div>
</nav>
<div class="container mt-4">
<?php endif; ?>