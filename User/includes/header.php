<link rel="stylesheet" href="assets/css/header.css">
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Xác định trang hiện tại
$current = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #2f5d62;
        padding: 12px 24px;
    }

    .logo a {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #f1f1f1;
        padding: 6px 14px;
        text-decoration: none;
    }

    .logo-icon {
        font-size: 16px;
    }

    .logo-text {
        font-weight: 700;
        font-size: 16px;
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
        font-size: 13px;
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
    }

    .header-right a:hover,
    .header-right i:hover {
        color: #f4a261;
    }



    /* DROPDOWN MENU */
    .user-dropdown {
        position: relative;
    }

    .user-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.35);
        border-radius: 4px;
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

    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 170px;
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
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 14px;
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
        border-bottom: 1px solid #f0f0f0;
    }

    .dropdown-menu a.dropdown-head-item {
        text-decoration: none;
        color: #1f2937;
        background: #fff;
    }

    .dropdown-menu a.dropdown-head-item:hover {
        background: #f6f6f6;
        color: #1f2937;
    }

    .dropdown-menu a.dropdown-head-item i {
        color: #f4a261;
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
    <!-- LOGO -->
    <div class="logo">
        <a href="index.php">
            <span class="logo-icon">🚲</span>
            <span class="logo-text">CYCLE</span>
        </a>
    </div>

    <!-- MENU -->
    <nav class="nav">
        <a href="index.php" class="<?= ($current == 'index.php') ? 'active' : '' ?>">Trang chủ</a>
        <a href="about.php" class="<?= ($current == 'about.php') ? 'active' : '' ?>">Giới thiệu</a>
        <a href="cycle.php" class="<?= ($current == 'cycle.php') ? 'active' : '' ?>">Sản phẩm</a>
        <a href="news.php" class="<?= ($current == 'news.php') ? 'active' : '' ?>">Đăng tin</a>
        <a href="contact.php" class="<?= ($current == 'contact.php') ? 'active' : '' ?>">Liên hệ</a>
    </nav>

    <!-- RIGHT -->
    <div class="header-right">
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
            <?php $displayName = trim($_SESSION['full_name'] ?? '') !== '' ? $_SESSION['full_name'] : ($_SESSION['username'] ?? 'User'); ?>
            <!-- USER DROPDOWN -->
            <div class="user-dropdown">
                <button class="user-btn" id="userBtn">
                    <i class="fas fa-user"></i>
                    <span><?= htmlspecialchars($displayName) ?></span>
                </button>
                                <div class="dropdown-menu" id="dropdownMenu">
                    <a class="dropdown-head-item" href="profile.php">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($displayName) ?>
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
        <?php else: ?>
            <!-- LOGIN -->
            <a href="login.php">Đăng nhập</a>
        <?php endif; ?>
    </div>
</header>

<!-- SCRIPT -->
<script>
    const userBtn = document.getElementById("userBtn");
    const dropdownMenu = document.getElementById("dropdownMenu");

    if (userBtn && dropdownMenu) {
        userBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle("active");
        });

        document.addEventListener("click", function() {
            dropdownMenu.classList.remove("active");
        });

        dropdownMenu.addEventListener("click", function(e) {
            e.stopPropagation();
        });
    }
</script>
