

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
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(to right, #ffffff 21.65%, #2f5d62 21.65%);
        padding: 12px 24px;
    }
    .logo {
        flex: 0 0 auto;
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
        font-size: 17px;
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
        gap: 12px;
        z-index: 10; 
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

    /* ===== SEARCH ===== */
.search-box {
    position: relative;
    margin-right: 15px;
    z-index: 9999;
    width: 200px;
}

.search-box input {
    width: 100%;
    padding: 6px 35px 6px 12px;
    border-radius: 20px;
    border: none;
    outline: none;
}

.search-box input:focus {
    width: 200px;
}
.search-box input:focus {
    width: 220px;
}

.search-box button {
    position: absolute;
    right: 8px;
    top: 4px;
    border: none;
    background: none;
    cursor: pointer;
}

/* ===== SEARCH DROPDOWN ===== */
#searchDropdown {
    position: absolute;
    top: 100%;        /* luôn nằm dưới input */
    left: 0;

    width: 100%;      /* ⭐ QUAN TRỌNG: bằng input */
    
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);

    display: none;
    z-index: 9999;

    max-height: 260px;
    overflow-y: auto;

    margin-top: 5px;  /* cách input 1 chút */
}

/* bật bằng JS */
#searchDropdown.show {
    display: block;
}

/* item */
.search-suggestion {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    cursor: pointer;
    transition: 0.2s;

    overflow: hidden; /* ⭐ chặn tràn */
}

/* FIX ẢNH */
.search-suggestion img {
    width: 40px;
    height: 40px;

    min-width: 40px;     /* ⭐ quan trọng */
    max-width: 40px;

    object-fit: cover;   /* ⭐ đổi contain → cover */
    border-radius: 6px;

    flex-shrink: 0;      /* ⭐ CHỐT LỖI */
}

/* FIX TEXT KHÔNG TRÀN */
.search-suggestion-info {
    flex: 1;
    overflow: hidden;
}

.search-suggestion-info h6 {
    font-size: 13px;
    margin: 0;
    color: #000; 

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis; 
}

.search-suggestion-info p {
    font-size: 12px;
    margin: 2px 0 0;
    color: #e60000;   
}

/* no result */
.search-no-results {
    padding: 12px;
    text-align: center;
    font-size: 13px;
    color: #777;
}

.cart-icon {
    position: relative;
    font-size: 20px;
}

.cart-count {
    position: absolute;
    top: -6px;
    right: -8px;
    background: red;
    color: #fff;
    font-size: 11px;
    padding: 2px 5px;
    border-radius: 50%;
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
        <a href="classifieds.php" class="<?= ($current == 'classifieds.php') ? 'active' : '' ?>">Đăng tin</a>
        <a href="contact.php" class="<?= ($current == 'contact.php') ? 'active' : '' ?>">Liên hệ</a>
    </nav>

    <div class="header-right">

    <!-- SEARCH -->
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Tìm kiếm">
        <button><i class="fa fa-search"></i></button>
        <div id="searchDropdown"></div>
    </div>

    <?php if (!empty($_SESSION['logged_in'])): ?>

        <!-- USER -->
        <div class="user-dropdown">
            <button class="user-btn" id="userBtn" type="button">
                <?php if ($avatarPath !== ''): ?>
                    <img src="<?= htmlspecialchars($avatarPath) ?>" class="header-avatar">
                <?php else: ?>
                    <span class="header-avatar-text"><?= htmlspecialchars($initialChar) ?></span>
                <?php endif; ?>

                <span><?= htmlspecialchars($displayName) ?></span>
            </button>

            <div class="dropdown-menu" id="dropdownMenu">
                <a class="dropdown-head-item" href="profile.php">
                    <?php if ($avatarPath !== ''): ?>
                        <img src="<?= htmlspecialchars($avatarPath) ?>" class="dropdown-avatar">
                    <?php else: ?>
                        <span class="dropdown-avatar-text"><?= htmlspecialchars($initialChar) ?></span>
                    <?php endif; ?>

                    <span><?= htmlspecialchars($displayName) ?></span>
                </a>

                <a class="account-item" href="profile.php">
                    <i class="fas fa-id-card"></i> Tài khoản
                </a>

                <a class="account-item" href="statistics.php">
                    <i class="fas fa-chart-line"></i> Thống kê
                </a>

                <div class="dropdown-divider"></div>

                <a class="logout-item" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </div>
        </div>

        <!-- CART -->
        <a href="cart.php" class="cart-icon">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count">2</span>
        </a>

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

    document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('searchInput');
    const searchDropdown = document.getElementById('searchDropdown');

    if (!searchInput || !searchDropdown) return;

    let timeout;

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();
        clearTimeout(timeout);

        if (query.length < 1) {
            searchDropdown.classList.remove('show');
            searchDropdown.innerHTML = '';
            return;
        }

        timeout = setTimeout(() => {

            fetch(`search_suggest.php?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {

                    if (!data || data.length === 0) {
                        searchDropdown.innerHTML =
                            '<div class="search-no-results">Không tìm thấy</div>';
                        searchDropdown.classList.add('show');
                        return;
                    }

                    searchDropdown.innerHTML = data.map(item => `
                        <div class="search-suggestion" onclick="selectSuggestion(${item.bicycle_id})">
                            <img src="${item.main_image}" 
                                 onerror="this.src='assets/images/default-bike.png'">
                            <div class="search-suggestion-info">
                                <h6>${item.name}</h6>
                                <p>${formatPrice(item.price)}₫</p>
                            </div>
                        </div>
                    `).join('');

                    searchDropdown.classList.add('show');

                })
                .catch(err => {
                    console.error(err);
                    searchDropdown.innerHTML =
                        '<div class="search-no-results">Lỗi tải</div>';
                    searchDropdown.classList.add('show');
                });

        }, 300);
    });

    // click ngoài
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            searchDropdown.classList.remove('show');
        }
    });

    // chuyển trang
    window.selectSuggestion = function (id) {
        window.location.href = `detail.php?id=${id}`;
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price);
    }

    // Enter search
    searchInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            window.location.href = `bikes.php?search=${searchInput.value}`;
        }
    });

});
</script>