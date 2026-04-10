<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu</title>
    <base href="/Cycle-main/">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body{background:#f5f7fa;}
        .auth-box{max-width:500px;margin:80px auto;padding:30px;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.08);}
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
    <a href="profile.php">Profile</a>
    <a href="change-password.php">Change Password</a>
    <a href="logout.php">Logout</a>
</div>

<div class="auth-box">
    <h2>Đổi mật khẩu</h2>

    <form id="changePasswordForm">
        <div class="form-group">
            <label>Mật khẩu cũ</label>
            <input type="password" id="oldPassword" name="old_password">
            <small class="error" id="oldPasswordError"></small>
        </div>

        <div class="form-group">
            <label>Mật khẩu mới</label>
            <input type="password" id="newPassword" name="new_password">
            <small class="error" id="newPasswordError"></small>
        </div>

        <div class="form-group">
            <label>Nhập lại mật khẩu mới</label>
            <input type="password" id="confirmNewPassword" name="confirm_new_password">
            <small class="error" id="confirmNewPasswordError"></small>
        </div>

        <button type="submit" class="btn-auth">Đổi mật khẩu</button>
    </form>
</div>

<script src="js/auth.js"></script>
</body>
</html>