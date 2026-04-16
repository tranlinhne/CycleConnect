<?php
// Xác định trang hiện tại
$current = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
        <a href="products.php" class="<?= ($current == 'products.php') ? 'active' : '' ?>">Sản phẩm</a>
        <a href="post.php" class="<?= ($current == 'post.php') ? 'active' : '' ?>">Đăng tin</a>
        <a href="contact.php" class="<?= ($current == 'contact.php') ? 'active' : '' ?>">Liên hệ</a>
    </nav>

    <!-- RIGHT -->
    <div class="header-right">

        <!-- LOGIN -->
        <a href="login.php" class="login">Login</a>

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
</script>