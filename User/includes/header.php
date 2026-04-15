<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .site-header {
        position: sticky;
        top: 0;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #2f5d62;
        padding: 12px 40px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .site-logo {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        font-weight: 800;
        font-size: 30px;
        color: #f0be6f;
    }

    .site-nav {
        display: flex;
        align-items: center;
        gap: 26px;
        margin-left: 40px;
    }

    .site-nav a {
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        opacity: 0.92;
    }

    .site-nav a.active,
    .site-nav a:hover {
        color: #f0be6f;
    }

    .site-right {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .site-right .icon-link {
        color: #fff;
        font-size: 18px;
        text-decoration: none;
    }

    .site-right .icon-link:hover {
        color: #f0be6f;
    }

    .btn-auth-link {
        background: #f0be6f;
        color: #234e5a;
        text-decoration: none;
        font-weight: 700;
        padding: 9px 16px;
        border-radius: 6px;
        font-size: 14px;
    }

    .btn-auth-link:hover {
        background: #e7a64d;
    }

    @media (max-width: 900px) {
        .site-header {
            padding: 12px 16px;
            flex-wrap: wrap;
            row-gap: 10px;
        }

        .site-nav {
            order: 3;
            width: 100%;
            margin-left: 0;
            gap: 16px;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 4px;
        }
    }
</style>

<header class="site-header">
    <a href="index.php" class="site-logo"><i class="fas fa-bicycle"></i><span>CYCLE</span></a>

    <nav class="site-nav">
        <a href="index.php" class="<?= $current === 'index.php' ? 'active' : '' ?>">Trang chủ</a>
        <a href="about.php" class="<?= $current === 'about.php' ? 'active' : '' ?>">Về chúng tôi</a>
        <a href="cycle.php" class="<?= $current === 'cycle.php' ? 'active' : '' ?>">Sản phẩm</a>
        <a href="contact.php" class="<?= $current === 'contact.php' ? 'active' : '' ?>">Liên hệ</a>
    </nav>

    <div class="site-right">
        <a class="icon-link" href="#" aria-label="Giỏ hàng"><i class="fas fa-shopping-cart"></i></a>
        <?php if (!empty($_SESSION['logged_in'])): ?>
            <a class="btn-auth-link" href="profile.php">Tài khoản</a>
            <a class="btn-auth-link" href="logout.php">Đăng xuất</a>
        <?php else: ?>
            <a class="btn-auth-link" href="login.php">Đăng nhập</a>
        <?php endif; ?>
    </div>
</header>