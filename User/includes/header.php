<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/auth-handler.php';

$current = basename($_SERVER['PHP_SELF']);

$displayName = 'User';
$avatarPath = '';

if (!empty($_SESSION['logged_in'])) {
    $displayName = trim($_SESSION['full_name'] ?? '') !== ''
        ? $_SESSION['full_name']
        : ($_SESSION['email'] ?? 'User');

    if (!empty($_SESSION['avatar'])) {
        $avatarPath = $_SESSION['avatar'];
    }

    if (!empty($_SESSION['user_id']) && function_exists('getUserInfo')) {
        $headerUser = getUserInfo((int) $_SESSION['user_id']);

        if (!empty($headerUser['full_name'])) {
            $displayName = $headerUser['full_name'];
        } elseif (!empty($headerUser['email'])) {
            $displayName = $headerUser['email'];
        }

        if (!empty($headerUser['avatar'])) {
            $avatarPath = $headerUser['avatar'];
        }
    }
}

$initialChar = function_exists('mb_substr')
    ? mb_substr($displayName, 0, 1, 'UTF-8')
    : substr($displayName, 0, 1);

$initialChar = strtoupper($initialChar);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    .header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(to right, #ffffff 21.65%, #2f5d62 21.65%);
        padding: 12px 24px;
    }

    .logo a {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        text-decoration: none;
    }

    .logo-icon {
        font-size: 20px;
    }

    .logo-text {
        font-weight: 700;
        font-size: 20px;
        color: #2f5d62;
    }

    .nav {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 2px;
    }

    .nav a {
        color: #fff;
        text-decoration: none;
        margin: 0 14px;
        font-size: 20px;
        font-weight: 600;
    }

    .nav a:hover,
    .nav a.active {
        color: #f4a261;
    }

    .header-right {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .header-right a,
    .header-right i {
        color: #fff;
        text-decoration: none;
        cursor: pointer;
        font-size: 20px;
    }

    .header-right a:hover,
    .header-right i:hover {
        color: #f4a261;
    }

    .user-dropdown {
        position: relative;
    }

    .user-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.35);
        border-radius: 6px;
        color: #fff;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        padding: 4px 10px;
        transition: 0.2s;
    }

    .user-btn:hover {
        background: rgba(255, 255, 255, 0.12);
    }

    .header-avatar,
    .dropdown-avatar {
        object-fit: cover;
        border-radius: 50%;
        display: block;
        flex-shrink: 0;
    }

    .header-avatar {
        width: 28px;
        height: 28px;
        border: 1px solid rgba(255, 255, 255, 0.35);
    }

    .dropdown-avatar {
        width: 34px;
        height: 34px;
        border: 1px solid #d7dce1;
    }

    .header-avatar-text,
    .dropdown-avatar-text {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: 700;
        flex-shrink: 0;
    }

    .header-avatar-text {
        width: 28px;
        height: 28px;
        background: #ffffff;
        color: #2f5d62;
        font-size: 13px;
    }

    .dropdown-avatar-text {
        width: 34px;
        height: 34px;
        background: #2f5d62;
        color: #ffffff;
        font-size: 14px;
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        min-width: 210px;
        z-index: 1000;
        overflow: hidden;
        display: none;
        margin-top: 10px;
        border: 1px solid #ececec;
    }

    .dropdown-menu.active {
        display: block;
    }

    .dropdown-head-item {
        padding: 12px 14px;
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
        border-bottom: 1px solid #f0f0f0;
    }

    .dropdown-menu a.dropdown-head-item {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        text-decoration: none;
        color: #1f2937;
        background: #fff;
        text-align: left;
    }

    .dropdown-menu a.dropdown-head-item:hover {
        background: #f6f6f6;
        color: #1f2937;
    }

    .dropdown-menu a {
        display: block;
        width: 100%;
        text-decoration: none;
        color: #3b3b3b;
        padding: 11px 14px;
        font-size: 14px;
        transition: 0.2s;
    }

    .dropdown-menu a:hover {
        background: #f6f6f6;
        color: #2f5d62;
    }

    .dropdown-divider {
        height: 1px;
        background: #efefef;
    }

    .dropdown-menu a.logout-item,
    .dropdown-menu a.logout-item i {
        color: #d93025;
    }

    .dropdown-menu a.logout-item:hover,
    .dropdown-menu a.logout-item:hover i {
        color: #b42318;
    }

    .dropdown-menu a.account-item,
    .dropdown-menu a.account-item i {
        color: #000;
    }

    .dropdown-menu a.account-item:hover,
    .dropdown-menu a.account-item:hover i {
        color: #000;
    }

    @media (max-width: 900px) {
        .header {
            flex-wrap: wrap;
            row-gap: 10px;
        }

        .nav {
            position: static;
            transform: none;
            order: 3;
            width: 100%;
            justify-content: flex-start;
            overflow-x: auto;
            white-space: nowrap;
        }
    }
</style>

<header class="header">
    <div class="logo">
        <a href="index.php">
            <span class="logo-icon">🍃</span>
            <span class="logo-text">GREENRIDE</span>
        </a>
    </div>

    <nav class="nav">
        <a href="index.php" class="<?= ($current == 'index.php') ? 'active' : '' ?>">Trang chủ</a>
        <a href="about.php" class="<?= ($current == 'about.php') ? 'active' : '' ?>">Giới thiệu</a>
        <a href="products.php" class="<?= ($current == 'products.php') ? 'active' : '' ?>">Sản phẩm</a>
        <a href="sell.php" class="<?= ($current == 'sell.php') ? 'active' : '' ?>">Đăng tin</a>
        <a href="contact.php" class="<?= ($current == 'contact.php') ? 'active' : '' ?>">Liên hệ</a>
    </nav>

    <div class="header-right">
        <?php if (!empty($_SESSION['logged_in'])): ?>
            <div class="user-dropdown">
                <button class="user-btn" id="userBtn" type="button">
                    <?php if ($avatarPath !== ''): ?>
                        <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar" class="header-avatar">
                    <?php else: ?>
                        <span class="header-avatar-text"><?= htmlspecialchars($initialChar) ?></span>
                    <?php endif; ?>

                    <span><?= htmlspecialchars($displayName) ?></span>
                </button>

                <div class="dropdown-menu" id="dropdownMenu">
                    <a class="dropdown-head-item" href="profile.php">
                        <?php if ($avatarPath !== ''): ?>
                            <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar" class="dropdown-avatar">
                        <?php else: ?>
                            <span class="dropdown-avatar-text"><?= htmlspecialchars($initialChar) ?></span>
                        <?php endif; ?>

                        <span><?= htmlspecialchars($displayName) ?></span>
                    </a>

                    <a class="account-item" href="profile.php">
                        <i class="fas fa-id-card" style="margin-right: 8px;"></i> Tài khoản
                    </a>

                    <a class="account-item" href="statistics.php">
                        <i class="fas fa-chart-line" style="margin-right: 8px;"></i> Thống kê
                    </a>

                    <div class="dropdown-divider"></div>

                    <a class="logout-item" href="logout.php">
                        <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Đăng xuất
                    </a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php">Đăng nhập</a>
        <?php endif; ?>
    </div>
</header>

<script>
    const userBtn = document.getElementById("userBtn");
    const dropdownMenu = document.getElementById("dropdownMenu");

    if (userBtn && dropdownMenu) {
        userBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle("active");
        });

        document.addEventListener("click", function () {
            dropdownMenu.classList.remove("active");
        });

        dropdownMenu.addEventListener("click", function (e) {
            e.stopPropagation();
        });
    }
</script>