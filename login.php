<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <base href="/Cycle-main/">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body{background:#f5f7fa;}
        .auth-box{max-width:450px;margin:80px auto;padding:30px;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.08);}
        .auth-box h2{text-align:center;margin-bottom:20px;}
        .form-group{margin-bottom:15px;}
        .form-group label{display:block;margin-bottom:6px;font-weight:600;}
        .form-group input{width:100%;height:45px;padding:0 12px;border:1px solid #ccc;border-radius:8px;}
        .btn-auth{width:100%;height:45px;border:none;border-radius:8px;background:#f0be6f;color:#fff;font-weight:700;}
        .error{color:red;font-size:13px;}
        .top-nav{background:#1f4f63;padding:15px;text-align:center;}
        .top-nav a{color:#fff;margin:0 10px;text-decoration:none;}
    </style>
</head>
<body>
<div class="top-nav">
    <a href="index.php">Home</a>
    <a href="about.php">About</a>
    <a href="cycle.php">Our Cycle</a>
    <a href="news.php">News</a>
    <a href="contact.php">Contact</a>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
</div>

<div class="auth-box">
    <h2>Đăng nhập</h2>
    <form id="loginForm">
        <div class="form-group">
            <label>Email</label>
            <input type="email" id="loginEmail" name="email">
            <small class="error" id="loginEmailError"></small>
        </div>

        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" id="loginPassword" name="password">
            <small class="error" id="loginPasswordError"></small>
        </div>

        <button type="submit" class="btn-auth">Đăng nhập</button>
    </form>

    <p style="text-align:center;margin-top:15px;">
        Chưa có tài khoản? <a href="register.php">Đăng ký</a>
    </p>
</div>

<script src="js/auth.js"></script>
</body>
</html>