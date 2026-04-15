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
    <style>
        body { margin: 0; background: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .wrap { max-width: 980px; margin: 30px auto; padding: 0 16px 30px; }
        .grid { display: grid; grid-template-columns: 300px 1fr; gap: 20px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 6px 24px rgba(0,0,0,.08); padding: 22px; }
        h1 { margin: 0 0 10px; color: #2f5d62; }
        .muted { color: #666; margin-bottom: 12px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 700; color: #1f3540; }
        .form-group input, .form-group textarea { width: 100%; border: 1px solid #d7d7d7; border-radius: 8px; padding: 10px 12px; font-size: 14px; }
        .form-group textarea { min-height: 140px; resize: vertical; }
        .full { grid-column: 1 / -1; }
        .btn { border: 0; border-radius: 8px; background: #2f5d62; color: #fff; font-weight: 700; padding: 11px 14px; cursor: pointer; }
        .btn:hover { background: #23444a; }
        .alert { border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; font-size: 14px; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d4edda; color: #155724; }
        .info-row { margin-bottom: 10px; color: #334; }
        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="wrap">
    <div class="grid">
        <div class="card">
            <h1>Liên hệ</h1>
            <p class="muted">Nếu bạn cần hỗ trợ, hãy gửi thông tin cho GreenRide.</p>
            <div class="info-row"><strong>Email:</strong> support@greenride.local</div>
            <div class="info-row"><strong>Hotline:</strong> 0901 234 567</div>
            <div class="info-row"><strong>Địa chỉ:</strong> Bình Thạnh, TP.HCM</div>
            <div class="info-row"><strong>Thời gian:</strong> 8:00 - 21:00</div>
        </div>

        <div class="card">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Họ tên</label>
                        <input id="name" type="text" name="name" required value="<?= htmlspecialchars($prefillName) ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" required value="<?= htmlspecialchars($prefillEmail) ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input id="phone" type="text" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="subject">Tiêu đề</label>
                        <input id="subject" type="text" name="subject" required value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                    </div>

                    <div class="form-group full">
                        <label for="message">Nội dung</label>
                        <textarea id="message" name="message" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn">Gửi liên hệ</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
