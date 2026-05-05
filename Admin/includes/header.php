<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bike Market</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ICON -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* ===== TOP BAR ===== */
        .topbar {
            background: #1a1a1a;
            color: #aaa;
            font-size: 13px;
            padding: 8px 60px;
            display: flex;
            justify-content: space-between;
        }

        .topbar a {
            color: #aaa;
            text-decoration: none;
            margin-right: 15px;
        }

        .topbar a:hover {
            color: #fff;
        }

        /* ===== HEADER ===== */
        .header {
            background: rgba(0,0,0,0.85);
            height: 80px; /* 🔥 CHIỀU CAO CHUẨN */
            padding: 0 60px;
            display: flex;
            align-items: center;
        }

        /* LOGO */
        .logo {
            color: white;
            font-size: 26px;
            font-weight: bold;
        }

        .logo span {
            color: red;
        }

        /* MENU */
        .menu {
            display: flex;
            height: 100%;
            margin-left: auto;
            margin-right: 80px;
            width: 850px;
            justify-content: space-between;
        }

        .menu a {
            color: #ddd;
            text-decoration: none;
            padding: 0 20px;
            display: flex;
            align-items: center;
            height: 100%;
            font-size: 14px;
            transition: 0.3s;
            transition: 0.3s;
        }

        /* 🔥 ACTIVE (HOME) */
        .menu a.active {
            background: #e60000;
            color: white;
        }

        /* 🔥 HOVER GIỐNG TEMPLATE */
        .menu a:hover {
            background: #e60000;
            color: white;
        }

        /* ICON */
        .icons {
            color: white;
        }

        .icons i {
            margin-left: 15px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
    <div>
        <a href="#"><i class="fa fa-user"></i> Đăng nhập</a>
        <a href="#"><i class="fa fa-user-plus"></i> Đăng ký</a>
        <a href="#"><i class="fa fa-heart"></i> Yêu thích</a>
    </div>

    <div>
        <i class="fa fa-phone"></i> 0123 456 789
    </div>
</div>

<!-- HEADER -->
<div class="header">

    <!-- LOGO -->
    <div class="logo">
        Bike<span>Market</span>
    </div>

    <!-- MENU -->
    <div class="menu">
        <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">HOME</a>

        <a href="bikes.php" class="<?= ($current_page == 'bikes.php') ? 'active' : '' ?>">XE ĐẠP</a>

        <a href="sell.php" class="<?= ($current_page == 'sell.php') ? 'active' : '' ?>">ĐĂNG BÁN</a>

        <a href="services.php" class="<?= ($current_page == 'services.php') ? 'active' : '' ?>">DỊCH VỤ</a>

        <a href="blog.php" class="<?= ($current_page == 'blog.php') ? 'active' : '' ?>">BLOG</a>

        <a href="contact.php" class="<?= ($current_page == 'contact.php') ? 'active' : '' ?>">LIÊN HỆ</a>

    </div>

    <!-- ICON -->
    <div class="icons">
        <i class="fa fa-search"></i>
        <i class="fa fa-shopping-cart"></i>
    </div>

</div>  