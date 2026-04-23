<?php
session_start();

include_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/auth-handler.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;

    $result = saveContactMessage(
        $_POST['name'] ?? '',
        $_POST['email'] ?? '',
        $_POST['phone'] ?? '',
        $_POST['subject'] ?? '',
        $_POST['message'] ?? '',
        $userId
    );

    if ($result['success']) {
        $success = $result['message'];
        $_POST = array();
    } else {
        $error = $result['message'];
    }
}

$prefillName = $_POST['name'] ?? ($_SESSION['full_name'] ?? '');
$prefillEmail = $_POST['email'] ?? ($_SESSION['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - GreenRide</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/footer.css">
</head>
<body class="contact-page">

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="contact-page-wrap">
    <div class="contact-layout">
        <div class="contact-left">
            <h1>Liên hệ với chúng tôi</h1>
            <p class="contact-desc">
                Bạn hãy điền nội dung tin nhắn vào form dưới đây và gửi cho chúng tôi.
                GreenRide sẽ phản hồi sớm nhất có thể.
            </p>

            <form method="POST" action="" class="contact-form">
                <?php if ($error): ?>
                    <div class="alert alert-danger contact-alert"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success contact-alert"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Họ và tên *</label>
                    <input id="name" type="text" name="name" required value="<?= htmlspecialchars($prefillName) ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input id="email" type="email" name="email" required value="<?= htmlspecialchars($prefillEmail) ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input id="phone" type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="subject">Tiêu đề *</label>
                    <input id="subject" type="text" name="subject" required value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="message">Nội dung *</label>
                    <textarea id="message" name="message" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="contact-submit">GỬI TIN NHẮN</button>
            </form>
        </div>

        <div class="contact-info-box">
            <h2>Thông tin liên hệ</h2>

            <div class="contact-detail">
                2 Võ Oanh, Phường 25, Bình Thạnh, Thành phố Hồ Chí Minh
            </div>

            <div class="contact-detail">
                1900 xxxx
            </div>

            <div class="contact-detail">
                greenride@gamil.com
            </div>

            <div class="contact-map">
                <iframe
                    src="https://www.google.com/maps?q=2%20Vo%20Oanh%2C%20Phuong%2025%2C%20Binh%20Thanh%2C%20Ho%20Chi%20Minh&output=embed"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

</body>
</html>