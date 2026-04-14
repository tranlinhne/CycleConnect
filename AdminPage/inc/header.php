<?php if (!isset($hideHeader)): ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - GreenRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ---------- TOÀN BỘ GIAO DIỆN ADMIN ---------- */
        body {
            background-color: #F5F5F5; /* Xám trung tính */
            color: #263238;           /* Chữ tối */
            font-family: 'Segoe UI', system-ui, -apple-system, 'Inter', sans-serif;
        }

        /* Header admin mới – gradient nhẹ, bóng đổ */
        .admin-header {
            background: linear-gradient(135deg, #ffffff 0%, #F5F5F5 100%);
            border-bottom: 2px solid rgba(245, 124, 0, 0.2);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            padding: 0.5rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        /* Brand / Logo */
        .navbar-brand-custom {
            font-weight: 700;
            font-size: 1.6rem;
            color: #263238 !important;
            letter-spacing: -0.3px;
            transition: all 0.2s;
        }
        .navbar-brand-custom i {
            color: #F57C00;
            font-size: 1.8rem;
            margin-right: 8px;
            vertical-align: middle;
        }
        .navbar-brand-custom:hover {
            color: #F57C00 !important;
            transform: scale(1.02);
        }

        /* Menu điều hướng */
        .nav-link-custom {
            font-weight: 500;
            color: #263238 !important;
            margin: 0 4px;
            padding: 8px 16px;
            border-radius: 40px;
            transition: all 0.25s ease;
        }
        .nav-link-custom i {
            margin-right: 8px;
            font-size: 1rem;
            color: #5a626e;
            transition: color 0.2s;
        }
        .nav-link-custom:hover {
            background-color: rgba(245, 124, 0, 0.12);
            color: #F57C00 !important;
            transform: translateY(-2px);
        }
        .nav-link-custom:hover i {
            color: #F57C00;
        }
        .nav-link-custom.active {
            background-color: #F57C00;
            color: white !important;
            box-shadow: 0 4px 8px rgba(245, 124, 0, 0.3);
        }
        .nav-link-custom.active i {
            color: white;
        }

        /* Thông tin admin & nút đăng xuất */
        .admin-welcome {
            font-weight: 500;
            background: rgba(38, 50, 56, 0.05);
            padding: 6px 14px;
            border-radius: 40px;
            color: #263238;
        }
        .admin-welcome i {
            color: #F57C00;
            margin-right: 6px;
        }
        .btn-logout {
            background-color: transparent;
            border: 1.5px solid #F57C00;
            color: #F57C00;
            border-radius: 40px;
            padding: 5px 16px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-logout:hover {
            background-color: #F57C00;
            color: white;
            border-color: #F57C00;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(245, 124, 0, 0.3);
        }

        /* Nút toggle mobile */
        .navbar-toggler-custom {
            border: none;
            background: transparent;
            font-size: 1.6rem;
            color: #263238;
            display: none;
        }
        .navbar-toggler-custom:focus {
            outline: none;
            box-shadow: none;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .navbar-brand-custom {
                font-size: 1.3rem;
            }
            .nav-link-custom {
                margin: 4px 0;
            }
            .admin-welcome {
                margin: 8px 0;
                display: inline-block;
            }
            .navbar-toggler-custom {
                display: flex !important;
            }
        }
    </style>
</head>
<body>
<header class="admin-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <!-- Logo + Brand -->
            <a class="navbar-brand navbar-brand-custom" href="<?= BASE_URL ?>dashboard.php">
                <i class="fas fa-leaf"></i> GreenRide Admin
            </a>

            <!-- Toggle button cho mobile -->
            <button class="navbar-toggler-custom" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Menu điều hướng -->
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?= strpos($_SERVER['PHP_SELF'], 'products/index.php') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>products/index.php">
                            <i class="fas fa-bicycle"></i> Quản lý sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?= strpos($_SERVER['PHP_SELF'], 'users/index.php') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>users/index.php">
                            <i class="fas fa-users"></i> Quản lý người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?= strpos($_SERVER['PHP_SELF'], 'contacts/index.php') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>contacts/index.php">
                            <i class="fas fa-envelope"></i> Liên hệ khách hàng
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?= strpos($_SERVER['PHP_SELF'], 'reports/index.php') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>reports/index.php">
                            <i class="fas fa-envelope"></i> Quản lý báo cáo
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?= strpos($_SERVER['PHP_SELF'], 'revenue/index.php') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>revenue/index.php">
                            <i class="fas fa-chart-line"></i> Doanh thu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom <?= strpos($_SERVER['PHP_SELF'], 'orders/index.php') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>orders/index.php">
                            <i class="fas fa-shopping-cart"></i> Đơn hàng
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <span class="admin-welcome">
                        <i class="fas fa-user-circle"></i> Xin chào, <?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?>
                    </span>
                    <a href="<?= BASE_URL ?>logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>
<div class="container mt-4">
<?php endif; ?>