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

    .search-box {
        display: none;
        align-items: center;
        background: #fff;
        border-radius: 5px;
        padding: 4px;
    }

    .search-box input,
    .search-box select,
    .search-box button {
        border: none;
        outline: none;
        padding: 6px 8px;
    }

    .search-box button {
        background: #f4a261;
        color: #fff;
    }

    .search-box.active {
        display: flex;
    }

    /* DROPDOWN MENU */
    .user-dropdown {
        position: relative;
    }

    .user-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        background: transparent;
        border: none;
        color: #fff;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        padding: 0;
        transition: 0.3s;
    }

    .user-btn:hover {
        color: #f4a261;
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 180px;
        z-index: 1000;
        overflow: hidden;
        display: none;
        margin-top: 8px;
    }

    .dropdown-menu.active {
        display: block;
    }

    .dropdown-menu a,
    .dropdown-menu button {
        display: block;
        width: 100%;
        padding: 12px 16px;
        text-align: left;
        text-decoration: none;
        color: #333;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 14px;
        transition: 0.2s;
    }

    .dropdown-menu a:hover,
    .dropdown-menu button:hover {
        background: #f5f5f5;
        color: #2f5d62;
    }

    .dropdown-divider {
        height: 1px;
        background: #eee;
        margin: 4px 0;
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
            <!-- USER DROPDOWN (Khi đăng nhập) -->
            <div class="user-dropdown">
                <button class="user-btn" id="userBtn">
                    <i class="fas fa-user"></i>
                    <span><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                </button>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="profile.php"><i class="fas fa-user-circle" style="margin-right: 8px;"></i>Tài khoản</a>
                    <a href="news.php"><i class="fas fa-newspaper" style="margin-right: 8px;"></i>Lịnh</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php"><i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i>Đăng xuất</a>
                </div>
            </div>
        <?php else: ?>
            <!-- LOGIN -->
            <a href="login.php" class="login">Đăng nhập</a>
        <?php endif; ?>

        <!-- SEARCH BOX -->
        <div class="search-box" id="searchBox">
            <input type="text" placeholder="Tìm xe đạp...">

            <!-- FILTER -->
            <select>
                <option value="">Tất cả</option>
                <option value="dien">Xe điện</option>
                <option value="duongpho">Xe đường phố</option>
                <option value="thethao">Xe thể thao</option>
            </select>

            <button><i class="fa fa-search"></i></button>
        </div>

        <!-- ICON -->
        <i class="fa fa-search search-toggle" onclick="toggleSearch()"></i>
        <a href="cart.php"><i class="fa fa-shopping-cart"></i></a>
        <i class="fa fa-bars menu-toggle"></i>

    </div>

</header>

<!-- SCRIPT -->
<script>
function toggleSearch() {
    const box = document.getElementById("searchBox");
    box.classList.toggle("active");
}

// Dropdown menu
const userBtn = document.getElementById("userBtn");
const dropdownMenu = document.getElementById("dropdownMenu");

if (userBtn && dropdownMenu) {
    userBtn.addEventListener("click", function(e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle("active");
    });

    // Đóng dropdown khi click ra ngoài
    document.addEventListener("click", function() {
        dropdownMenu.classList.remove("active");
    });

    dropdownMenu.addEventListener("click", function(e) {
        e.stopPropagation();
    });
}
</script>
