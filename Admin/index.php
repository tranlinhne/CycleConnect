<?php 
require_once "config.php";

$current_page = basename($_SERVER['PHP_SELF']);

$isUserPage = strpos($_SERVER['REQUEST_URI'], 'user_management') !== false;

// ✅ PHẢI ĐỂ TRONG PHP
$total_users = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM users")
)['total'] ?? 0;

$total_bikes = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM featured_bikes")
)['total'] ?? 0;

$total_transactions = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM transactions")
)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Segoe UI;
}

body {
    background: #f4f6fb;
    display: flex;
}

.sidebar {
    width: 250px;
    background: white;
    height: 100vh;
    padding: 20px;
    box-shadow: 0 0 20px rgba(0,0,0,.05);
}

.logo {
    font-size: 22px;
    font-weight: 700;
    color: #5b5ce2;
    margin-bottom: 30px;
}

.menu a {
    display: block;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 8px;
    text-decoration: none;
    color: #555;
    font-size: 14px;
}

.menu a.active,
.menu a:hover {
    background: linear-gradient(90deg,#6c5ce7,#5b5ce2);
    color: white;
}

.main {
    flex: 1;
    padding: 20px 30px;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.search {
    background: white;
    padding: 10px 15px;
    border-radius: 10px;
    width: 300px;
    box-shadow: 0 5px 15px rgba(0,0,0,.05);
}

.search input {
    border: none;
    outline: none;
    width: 100%;
}

.profile {
    display: flex;
    align-items: center;
    gap: 15px;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: url('https://i.pravatar.cc/100');
    background-size: cover;
}

.cards {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 10px 20px rgba(0,0,0,.05);
}

.card h4 {
    color: #888;
    font-size: 13px;
}

.card h2 {
    margin-top: 8px;
}

.grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.box {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 10px 20px rgba(0,0,0,.05);
}

.chart {
    height: 250px;
    background: linear-gradient(180deg,#f5f6ff,#ffffff);
    border-radius: 10px;
    margin-top: 15px;
}

.submenu {
    display: none;
    margin-left: 15px;
}

.submenu a {
    display: block;
    padding: 10px;
    font-size: 13px;
    color: #777;
    border-radius: 8px;
    text-decoration: none;
}

.submenu a:hover {
    background: #f1f2ff;
    color: #5b5ce2;
}

</style>
</head>

<body>

<div class="sidebar">

    <div class="logo">
        VENUS <br>
        <small style="font-size:12px;color:#999">
            DASHBOARD
        </small>
    </div>

    <div class="menu">

    <a href="dashboard.php"
    class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
        <i class="fa fa-chart-line"></i> Dashboard
    </a>

    <div class="menu-item">

    <a href="#" onclick="toggleMenu()">
        <i class="fa fa-users"></i> Quản lý người dùng
        <i class="fa fa-angle-down" style="float:right;"></i>
    </a>

    <div id="user-submenu" class="submenu"
         style="<?= $isUserPage ? 'display:block;' : '' ?>">

        <a href="user_management/all_users.php">All Users</a>
        <a href="user_management/add_user.php">Add New</a>
        <a href="user_management/profile.php">Profile</a>

    </div>

</div>

    <a href="bicycle_management.php"
    class="<?= ($current_page == 'bicycle_management.php') ? 'active' : '' ?>">
        <i class="fa fa-bicycle"></i> Quản lý xe đạp
    </a>

    <a href="inspection_management.php"
    class="<?= ($current_page == 'inspection_management.php') ? 'active' : '' ?>">
        <i class="fa fa-check-circle"></i> Kiểm định xe
    </a>

    <a href="transaction_management.php"
    class="<?= ($current_page == 'transaction_management.php') ? 'active' : '' ?>">
        <i class="fa fa-credit-card"></i> Quản lý giao dịch
    </a>

    <a href="message_management.php"
    class="<?= ($current_page == 'message_management.php') ? 'active' : '' ?>">
        <i class="fa fa-envelope"></i> Phản hồi tin nhắn
    </a>

    <a href="system_statistics.php"
    class="<?= ($current_page == 'system_statistics.php') ? 'active' : '' ?>">
        <i class="fa fa-chart-bar"></i> Thống kê hệ thống
    </a>

</div>

</div>

<div class="main">

    <div class="topbar">

        <div class="search">
            <input type="text" placeholder="Search">
        </div>

        <div class="profile">
            <i class="fa fa-bell"></i>
            <div class="avatar"></div>
        </div>

    </div>

    <div class="cards">

        <div class="card">
            <h4>Tổng người dùng</h4>
            <h2><?= $total_users ?></h2>
        </div>

        <div class="card">
            <h4>Xe đăng bán</h4>
            <h2><?= $total_bikes ?></h2>
        </div>

        <div class="card">
            <h4>Giao dịch</h4>
            <h2><?= $total_transactions ?></h2>
        </div>

        <div class="card">
            <h4>Doanh thu</h4>
            <h2>$540.50</h2>
        </div>

    </div>

    <div class="grid">

        <div class="box">
            <h3>Thống kê hệ thống</h3>
            <div class="chart"></div>
        </div>

        <div class="box">
            <h3>Hoạt động</h3>
            <div class="chart"></div>
        </div>

    </div>

</div>

<script>
function toggleMenu() {
    let menu = document.getElementById("user-submenu");

    if (menu.style.display === "block") {
        menu.style.display = "none";
    } else {
        menu.style.display = "block";
    }
}
</script>

</body>
</html>