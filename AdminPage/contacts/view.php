<?php
require_once '../inc/auth.php';
require_once '../inc/header.php';

// Lấy ID tin nhắn
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('<div class="container mt-5"><div class="alert alert-danger">⚠️ ID tin nhắn không hợp lệ.</div></div>');
}

// Lấy thông tin chi tiết
$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->execute([$id]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    die('<div class="container mt-5"><div class="alert alert-warning">🔍 Không tìm thấy tin nhắn với ID này.</div></div>');
}

// Đánh dấu đã đọc nếu chưa
if (!$message['is_read']) {
    $updateStmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    $updateStmt->execute([$id]);
    $message['is_read'] = 1; // cập nhật trạng thái trên giao diện
}

// Xử lý gửi phản hồi
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_text'])) {
    $replyText = trim($_POST['reply_text']);
    
    if (empty($replyText)) {
        $error = 'Vui lòng nhập nội dung phản hồi.';
    } else {
        // Lưu phản hồi vào DB, đồng thời đảm bảo đã đọc = 1
        $replyStmt = $pdo->prepare("UPDATE contact_messages SET reply = ?, is_read = 1 WHERE id = ?");
        if ($replyStmt->execute([$replyText, $id])) {
            $success = true;
            // Cập nhật lại mảng message để hiển thị reply mới
            $message['reply'] = $replyText;
            // Xóa thông báo lỗi nếu có
            $error = '';
        } else {
            $error = 'Có lỗi xảy ra khi lưu phản hồi. Vui lòng thử lại.';
        }
    }
}

// Định dạng ngày giờ
$createdDate = date('H:i, d/m/Y', strtotime($message['created_at']));
?>

<style>
    /* ---------- MÀU SẮC CHỦ ĐẠO ---------- */
    :root {
        --primary-orange: #F57C00;
        --bg-gray: #F5F5F5;
        --text-dark: #263238;
        --border-light: #e0e0e0;
        --card-shadow: 0 12px 24px rgba(0,0,0,0.08);
        --hover-shadow: 0 20px 30px rgba(0,0,0,0.12);
        --transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    }

    body {
        background-color: var(--bg-gray);
        color: var(--text-dark);
        font-family: 'Segoe UI', Roboto, system-ui, -apple-system, sans-serif;
    }

    /* Container chính */
    .reply-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem 0.5rem;
    }

    /* Breadcrumb / Navigation */
    .nav-back {
        margin-bottom: 1.8rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .btn-back {
        background: white;
        border: 1px solid var(--border-light);
        color: var(--text-dark);
        padding: 0.5rem 1.2rem;
        border-radius: 2rem;
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .btn-back:hover {
        background-color: var(--primary-orange);
        border-color: var(--primary-orange);
        color: white;
        transform: translateX(-4px);
        box-shadow: 0 4px 12px rgba(245,124,0,0.2);
    }
    .badge-status {
        background: white;
        padding: 0.4rem 1.2rem;
        border-radius: 2rem;
        font-weight: 600;
        font-size: 0.8rem;
        box-shadow: var(--card-shadow);
    }
    .badge-read {
        background: #e8f5e9;
        color: #2e7d32;
    }

    /* Card thông tin khách hàng */
    .info-card, .reply-card {
        background: white;
        border-radius: 1.5rem;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-bottom: 2rem;
        transition: var(--transition);
        border: 1px solid rgba(0,0,0,0.02);
    }
    .info-card:hover, .reply-card:hover {
        box-shadow: var(--hover-shadow);
        transform: translateY(-3px);
    }
    .card-header {
        background: white;
        padding: 1.2rem 2rem;
        border-bottom: 2px solid var(--primary-orange);
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }
    .card-header i {
        font-size: 1.6rem;
        color: var(--primary-orange);
        background: rgba(245,124,0,0.1);
        padding: 0.6rem;
        border-radius: 1rem;
    }
    .card-header h3 {
        margin: 0;
        font-weight: 700;
        font-size: 1.4rem;
        color: var(--text-dark);
    }
    .card-body {
        padding: 1.8rem 2rem;
    }

    /* Grid thông tin */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.5rem;
    }
    .info-item {
        background: #fafafc;
        padding: 1rem 1.2rem;
        border-radius: 1.2rem;
        border-left: 4px solid var(--primary-orange);
        transition: var(--transition);
    }
    .info-item:hover {
        background: #fff6ed;
        transform: translateX(5px);
    }
    .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        color: #6c7a89;
        margin-bottom: 0.4rem;
        display: block;
    }
    .info-value {
        font-size: 1rem;
        font-weight: 500;
        color: var(--text-dark);
        word-break: break-word;
    }
    .message-box {
        background: #fef9f0;
        border-radius: 1.2rem;
        padding: 1.6rem;
        margin-top: 1.5rem;
        border: 1px solid #ffe0b5;
        position: relative;
    }
    .message-box:before {
        content: "📝 Nội dung";
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--primary-orange);
        position: absolute;
        top: -0.8rem;
        left: 1.5rem;
        background: white;
        padding: 0 0.6rem;
    }
    .message-content {
        font-size: 1rem;
        line-height: 1.55;
        white-space: pre-wrap;
        color: #2c3e44;
    }

    /* Reply area */
    .reply-textarea {
        width: 100%;
        border: 2px solid var(--border-light);
        border-radius: 1rem;
        padding: 1rem;
        font-size: 1rem;
        font-family: inherit;
        transition: var(--transition);
        resize: vertical;
        min-height: 160px;
    }
    .reply-textarea:focus {
        border-color: var(--primary-orange);
        outline: none;
        box-shadow: 0 0 0 4px rgba(245,124,0,0.15);
    }
    .current-reply {
        background: #eef2f5;
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        margin-bottom: 1.8rem;
        border-left: 6px solid var(--primary-orange);
    }
    .current-reply p {
        margin-bottom: 0.5rem;
    }
    .reply-label {
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--primary-orange);
        margin-bottom: 0.5rem;
    }
    .btn-send {
        background: var(--primary-orange);
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 2.5rem;
        color: white;
        font-weight: 700;
        font-size: 1rem;
        transition: var(--transition);
        box-shadow: 0 4px 12px rgba(245,124,0,0.3);
        display: inline-flex;
        align-items: center;
        gap: 0.8rem;
    }
    .btn-send:hover {
        background: #e66a00;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(245,124,0,0.4);
    }
    .alert-custom {
        border-radius: 1rem;
        border-left: 5px solid;
        background: white;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }
    .alert-success-custom {
        border-left-color: #2e7d32;
        background: #e8f5e9;
        color: #1b5e20;
    }
    .alert-error-custom {
        border-left-color: #c62828;
        background: #ffebee;
        color: #b71c1c;
    }

    /* responsive */
    @media (max-width: 680px) {
        .card-header {
            padding: 1rem 1.2rem;
        }
        .card-body {
            padding: 1.2rem;
        }
        .info-grid {
            gap: 1rem;
        }
        .btn-back span {
            display: none;
        }
        .btn-back i {
            margin-right: 0;
        }
    }
</style>

<div class="container-fluid reply-container">
    <div class="nav-back">
        <a href="index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> <span>Quay lại danh sách</span>
        </a>
        <div class="badge-status">
            <i class="far fa-clock"></i> Gửi lúc: <?= htmlspecialchars($createdDate) ?>
            <span class="badge-read ms-2"> Đã đọc</span>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert-custom alert-success-custom">
            <i class="fas fa-check-circle me-2"></i> Phản hồi đã được lưu thành công! Khách hàng sẽ nhận được câu trả lời qua email.
        </div>
    <?php elseif ($error): ?>
        <div class="alert-custom alert-error-custom">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Thông tin khách hàng & tin nhắn gốc -->
    <div class="info-card">
        <div class="card-header">
            <i class="fas fa-user-headset"></i>
            <h3>Thông tin liên hệ</h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-user"></i> Họ & tên</span>
                    <div class="info-value"><?= htmlspecialchars($message['fullname']) ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                    <div class="info-value"><?= htmlspecialchars($message['email']) ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-phone-alt"></i> Số điện thoại</span>
                    <div class="info-value"><?= $message['phone'] ? htmlspecialchars($message['phone']) : '<span class="text-muted">Không cung cấp</span>' ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-tag"></i> Chủ đề</span>
                    <div class="info-value"><?= htmlspecialchars($message['subject']) ?></div>
                </div>
            </div>

            <div class="message-box">
                <div class="message-content">
                    <?= nl2br(htmlspecialchars($message['message'])) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Phần phản hồi -->
    <div class="reply-card">
        <div class="card-header">
            <i class="fas fa-reply-all"></i>
            <h3>Phản hồi khách hàng</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($message['reply'])): ?>
                <div class="current-reply">
                    <div class="reply-label">
                        <i class="fas fa-check-double"></i> Phản hồi trước đó:
                    </div>
                    <p><?= nl2br(htmlspecialchars($message['reply'])) ?></p>
                    <small class="text-muted"><i class="far fa-edit"></i> Bạn có thể chỉnh sửa và gửi lại phản hồi mới.</small>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label fw-bold mb-2"><i class="fas fa-comment-dots"></i> Nội dung trả lời:</label>
                    <textarea name="reply_text" class="reply-textarea" placeholder="Nhập phản hồi của bạn... Khách hàng sẽ nhận được câu trả lời này qua email nếu hệ thống được cấu hình gửi mail."><?= htmlspecialchars($message['reply'] ?? '') ?></textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn-send">
                        <i class="fas fa-paper-plane"></i> Gửi phản hồi & lưu
                    </button>
                </div>
            </form>
            <div class="mt-3 small text-muted">
                <i class="fas fa-info-circle"></i> Lưu ý: Sau khi gửi, tin nhắn sẽ được đánh dấu đã đọc và lưu phản hồi vào hệ thống.
            </div>
        </div>
    </div>
</div>

<?php require_once '../inc/footer.php'; ?>