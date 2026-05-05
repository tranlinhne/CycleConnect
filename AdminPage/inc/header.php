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

body {
    background-color: #f5f6fa;
    color: #263238;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    overflow-x: hidden;
}


.wrapper {
    display: flex;
    width: 100%;
}


.sidebar {
    width: 260px;
    height: 100vh;
    background: #2f5d62;
    border-right: 1px solid #23474b;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar.collapsed {
    left: -260px;
}

.sidebar-header {
    height: 70px;
    display: flex;
    align-items: center;
    padding: 0 20px;
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.sidebar-header i {
    color: #f4a261;
    margin-right: 10px;
    font-size: 1.8rem;
}

.sidebar-menu {
    flex: 1;
    overflow-y: auto;
    padding: 20px 0;
    list-style: none;
    margin: 0;
}

.sidebar-menu li {
    padding: 0 15px;
    margin-bottom: 8px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 12px 18px;
    color: #ffffff;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.2s;
    letter-spacing: 0.3px;
}

.sidebar-menu a:hover {
    background: rgba(255,255,255,0.1);
    color: #ffffff;
}
.sidebar-menu a:hover i {
    color: #f4a261;
}

.sidebar-menu a.active {
    background: #f4a261;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(244, 162, 97, 0.4);
}

.sidebar-menu i {
    width: 24px;
    font-size: 1.1rem;
    text-align: center;
    margin-right: 10px;
    transition: color 0.2s;
    color: #ffffff;
}
.sidebar-menu a.active i {
    color: #ffffff;
}

/* Main Content */
.main-content {
    flex: 1;
    margin-left: 260px;
    min-height: 100vh;
    transition: all 0.3s ease;
    width: 100%;
}

.main-content.expanded {
    margin-left: 0;
}

/* Top Navbar */
.top-navbar {
    height: 70px;
    background: #ffffff;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    position: sticky;
    top: 0;
    z-index: 999;
}

.menu-toggle {
    background: none;
    border: none;
    font-size: 1.4rem;
    color: #263238;
    cursor: pointer;
    transition: color 0.2s;
}
.menu-toggle:hover {
    color: #2f5d62;
}

.top-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.admin-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    color: #263238;
    padding: 6px 12px;
    background: #f5f6fa;
    border-radius: 30px;
    text-decoration: none;
    transition: all 0.2s;
}
.admin-profile:hover {
    background: #e0e0e0;
    color: #2f5d62;
}
.admin-profile i {
    font-size: 1.5rem;
    color: #2f5d62;
}

.btn-logout {
    border: 1px solid #2f5d62;
    color: #2f5d62;
    background: transparent;
    border-radius: 30px;
    padding: 6px 18px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-logout:hover {
    background: #2f5d62;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(47, 93, 98, 0.2);
}

.content-area-inner {
    padding: 25px;
}

/* Mobile Responsive */
@media (max-width: 992px) {
    .sidebar {
        left: -260px; 
    }
    .main-content {
        margin-left: 0; /* Full width */
    }
    .sidebar.mobile-open {
        left: 0;
    }
    .main-content.mobile-open {
        margin-left: 0; 
    }
    
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 998;
    }
    .sidebar-overlay.active {
        display: block;
    }
}
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Overlay for mobile toggle -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?= BASE_URL ?>dashboard.php" style="text-decoration:none; color:inherit;">
                <i class="fas fa-leaf"></i> GreenRide Admin
            </a>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="<?= BASE_URL ?>dashboard.php" class="<?= strpos($_SERVER['PHP_SELF'], 'dashboard.php') !== false ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i> Tổng quan
                </a>
            </li>
            <?php if (isSuperAdmin()): ?>
            <li>
                <a href="<?= BASE_URL ?>products/index.php" class="<?= strpos($_SERVER['PHP_SELF'], 'products/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-bicycle"></i> Quản lý sản phẩm
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>user_management/all_users.php" class="<?= strpos($_SERVER['PHP_SELF'], 'users/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Quản lý người dùng
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="<?= BASE_URL ?>contacts/index.php" class="<?= strpos($_SERVER['PHP_SELF'], 'contacts/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i> Liên hệ khách hàng
                </a>
            </li>
            <?php if (isSuperAdmin()): ?>
            <li>
                <a href="<?= BASE_URL ?>reports/index.php" class="<?= strpos($_SERVER['PHP_SELF'], 'reports/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-flag"></i> Quản lý báo cáo
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>revenue_reports/index.php" class="<?= strpos($_SERVER['PHP_SELF'], 'revenue_reports/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-file-invoice-dollar"></i> Duyệt báo cáo doanh thu
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a href="<?= BASE_URL ?>revenue/index.php" class="<?= strpos($_SERVER['PHP_SELF'], 'revenue/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> Doanh thu
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>orders/index.php" class="<?= strpos($_SERVER['PHP_SELF'], 'orders/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i> Đơn hàng
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="top-right">
                <a href="<?= BASE_URL ?>users/edit.php?id=<?= $_SESSION['user_id'] ?? 0 ?>" class="admin-profile">
                    <?php if (!empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../' . $_SESSION['avatar'])): ?>
                        <img src="<?= BASE_URL . $_SESSION['avatar'] ?>" alt="Avatar" style="width:32px; height:32px; border-radius:50%; object-fit:cover; border: 1px solid #2f5d62;">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                    <span class="d-none d-sm-inline"><?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?></span>
                </a>
                <a href="<?= BASE_URL ?>logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> <span class="d-none d-sm-inline">Đăng xuất</span>
                </a>
            </div>
        </header>
        
        
        <div class="content-area-inner container-fluid mt-4">
<?php endif; ?>
