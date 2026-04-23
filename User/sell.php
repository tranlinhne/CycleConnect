<?php
session_start();
include_once __DIR__ . '/config.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bán xe của bạn - GreenRide</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sell-landing-container { max-width: 1000px; margin: 40px auto; padding: 20px; text-align: center; }
        .sell-hero { background: #f9f9f9; padding: 60px 20px; border-radius: 12px; margin-bottom: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .sell-hero h1 { color: #2f5d62; font-size: 2.5em; margin-bottom: 15px; }
        .sell-hero p { font-size: 1.2em; color: #555; margin-bottom: 30px; }
        .btn-sell-now { display: inline-block; background: #F57C00; color: white; padding: 15px 40px; font-size: 1.2em; text-decoration: none; border-radius: 8px; font-weight: bold; transition: background 0.3s; }
        .btn-sell-now:hover { background: #e66a00; color: white; }
        
        .sell-features { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-top: 50px; text-align: left; }
        .feature-box { padding: 20px; background: white; border-radius: 8px; border: 1px solid #eee; text-align: center; }
        .feature-box i { font-size: 3em; color: #2f5d62; margin-bottom: 15px; }
        .feature-box h3 { color: #333; margin-bottom: 10px; }
        .feature-box p { color: #666; font-size: 0.95em; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="sell-landing-container">
    <div class="sell-hero">
        <h1>Biến chiếc xe đạp cũ của bạn thành tiền mặt</h1>
        <p>Quy trình đăng bán đơn giản, nhanh chóng và tiếp cận hàng ngàn người mua tiềm năng trên GreenRide.</p>
        
        <a href="post-ad.php" class="btn-sell-now"><i class="fas fa-bicycle"></i> BÁN NGAY</a>
    </div>

    <div class="sell-features">
        <div class="feature-box">
            <i class="fas fa-camera"></i>
            <h3>1. Chụp ảnh & Mô tả</h3>
            <p>Cung cấp vài bức ảnh rõ nét và thông tin cơ bản về chiếc xe của bạn. Chỉ mất chưa đầy 5 phút.</p>
        </div>
        <div class="feature-box">
            <i class="fas fa-globe"></i>
            <h3>2. Tiếp cận người mua</h3>
            <p>Tin đăng của bạn sẽ hiển thị ngay lập tức với cộng đồng đam mê xe đạp trên toàn quốc.</p>
        </div>
        <div class="feature-box">
            <i class="fas fa-handshake"></i>
            <h3>3. Giao dịch an toàn</h3>
            <p>Trao đổi trực tiếp với người mua thông qua hệ thống tin nhắn bảo mật của chúng tôi.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>