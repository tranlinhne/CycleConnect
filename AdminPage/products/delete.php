<?php
require_once '../inc/auth.php';


// Chỉ admin mới được xóa (bạn có thể điều chỉnh nếu cấp quyền khác)
if ($_SESSION['role'] !== 'admin') {
    die('<div class="container mt-5"><div class="alert alert-danger">Bạn không có quyền thực hiện thao tác này.</div></div>');
}

// Lấy ID sản phẩm từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('<div class="container mt-5"><div class="alert alert-warning">ID sản phẩm không hợp lệ.</div></div>');
}

// Xử lý xóa khi submit form (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
    try {
        // Bắt đầu transaction
        $pdo->beginTransaction();
        
        // Lấy thông tin xe để kiểm tra tồn tại
        $checkStmt = $pdo->prepare("SELECT id FROM bikes WHERE id = ?");
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            throw new Exception('Sản phẩm không tồn tại.');
        }
        
        // Xóa xe (các bảng liên quan sẽ tự động xóa nhờ ON DELETE CASCADE)
        $deleteStmt = $pdo->prepare("DELETE FROM bikes WHERE id = ?");
        $deleteStmt->execute([$id]);
        
        $pdo->commit();
        
        $_SESSION['flash_msg'] = "Đã xóa sản phẩm thành công!";
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Lỗi khi xóa: " . $e->getMessage();
    }
}

// Lấy thông tin chi tiết của xe để hiển thị
$stmt = $pdo->prepare("
    SELECT b.*, 
           c.name as category_name, 
           br.name as brand_name,
           (SELECT COUNT(*) FROM bike_images WHERE bike_id = b.id) as image_count
    FROM bikes b
    LEFT JOIN categories c ON b.category_id = c.id
    LEFT JOIN brands br ON b.brand_id = br.id
    WHERE b.id = ?
");
$stmt->execute([$id]);
$bike = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bike) {
    die('<div class="container mt-5"><div class="alert alert-danger">Không tìm thấy sản phẩm với ID này.</div></div>');
}

// Định dạng giá
$formatted_price = number_format($bike['price'], 0, ',', '.') . 'đ';
require_once '../inc/header.php';
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
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto;
    }

    .delete-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    /* Card chính */
    .delete-card {
        background: white;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        border: 1px solid rgba(0,0,0,0.03);
    }
    .delete-card:hover {
        box-shadow: var(--hover-shadow);
        transform: translateY(-3px);
    }

    /* Header */
    .card-header {
        background: linear-gradient(135deg, #fff5eb 0%, #ffffff 100%);
        padding: 1.5rem 2rem;
        border-bottom: 2px solid var(--primary-orange);
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }
    .card-header i {
        font-size: 2rem;
        color: var(--primary-orange);
        background: rgba(245,124,0,0.15);
        padding: 0.7rem;
        border-radius: 1rem;
    }
    .card-header h2 {
        margin: 0;
        font-weight: 700;
        font-size: 1.6rem;
        color: var(--text-dark);
    }

    /* Body */
    .card-body {
        padding: 2rem;
    }

    /* Thông báo lỗi */
    .alert-custom {
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }
    .alert-error {
        background: #ffebee;
        border-left: 5px solid #c62828;
        color: #b71c1c;
    }

    /* Thông tin xe */
    .bike-info {
        background: #fefaf5;
        border-radius: 1.2rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #ffe0b5;
    }
    .bike-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-bottom: 2px dashed var(--primary-orange);
        padding-bottom: 0.5rem;
    }
    .bike-title i {
        color: var(--primary-orange);
    }
    .detail-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 0.8rem;
        font-size: 0.95rem;
    }
    .detail-label {
        width: 130px;
        font-weight: 600;
        color: var(--primary-orange);
    }
    .detail-value {
        flex: 1;
        color: var(--text-dark);
    }
    .warning-box {
        background: #fff3e0;
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        margin: 1.5rem 0;
        display: flex;
        align-items: center;
        gap: 1rem;
        border-left: 6px solid #f57c00;
    }
    .warning-box i {
        font-size: 2rem;
        color: #f57c00;
    }
    .warning-text {
        font-weight: 600;
        color: #e65100;
    }

    /* Nút bấm */
    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1.5rem;
    }
    .btn-cancel {
        background: white;
        border: 1px solid var(--border-light);
        color: var(--text-dark);
        padding: 0.7rem 1.8rem;
        border-radius: 2.5rem;
        font-weight: 600;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-cancel:hover {
        background: #f5f5f5;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    .btn-danger {
        background: #dc3545;
        border: none;
        color: white;
        padding: 0.7rem 1.8rem;
        border-radius: 2.5rem;
        font-weight: 700;
        transition: var(--transition);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(220,53,69,0.3);
    }
    .btn-danger:hover {
        background: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(220,53,69,0.4);
    }

    /* Responsive */
    @media (max-width: 640px) {
        .card-header {
            padding: 1rem 1.2rem;
        }
        .card-body {
            padding: 1.2rem;
        }
        .detail-label {
            width: 100%;
            margin-bottom: 0.2rem;
        }
        .detail-row {
            flex-direction: column;
            margin-bottom: 1rem;
        }
        .action-buttons {
            flex-direction: column;
        }
        .btn-cancel, .btn-danger {
            justify-content: center;
        }
    }
</style>

<div class="delete-container">
    <div class="delete-card">
        <div class="card-header">
            <i class="fas fa-trash-alt"></i>
            <h2>Xóa sản phẩm</h2>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert-custom alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="bike-info">
                <div class="bike-title">
                    <i class="fas fa-bicycle"></i>
                    <?= htmlspecialchars($bike['title']) ?>
                </div>
                <div class="detail-row">
                    <div class="detail-label">ID xe:</div>
                    <div class="detail-value">#<?= $bike['id'] ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Danh mục:</div>
                    <div class="detail-value"><?= htmlspecialchars($bike['category_name'] ?? 'Chưa phân loại') ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Thương hiệu:</div>
                    <div class="detail-value"><?= htmlspecialchars($bike['brand_name'] ?? 'Chưa có') ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Giá:</div>
                    <div class="detail-value"><?= $formatted_price ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Trạng thái:</div>
                    <div class="detail-value">
                        <span class="badge <?= $bike['status'] == 'available' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= ucfirst($bike['status'] ?? 'Chưa cập nhật') ?>
                        </span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Hình ảnh:</div>
                    <div class="detail-value"><?= (int)($bike['image_count'] ?? 0) ?> ảnh</div>
                </div>
                <?php if (!empty($bike['description'])): ?>
                <div class="detail-row">
                    <div class="detail-label">Mô tả:</div>
                    <div class="detail-value"><?= nl2br(htmlspecialchars(substr($bike['description'], 0, 150))) ?>...</div>
                </div>
                <?php endif; ?>
            </div>

            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong class="warning-text">Cảnh báo!</strong><br>
                    Hành động này sẽ <strong>xoá vĩnh viễn</strong> sản phẩm khỏi hệ thống.<br>
                    Tất cả dữ liệu liên quan (hình ảnh, đánh giá, báo cáo, tin nhắn, đơn hàng) cũng sẽ bị xóa.<br>
                    <span style="font-size: 0.85rem;">⚠️ Không thể khôi phục sau khi xóa.</span>
                </div>
            </div>

            <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Hành động không thể hoàn tác.');">
                <input type="hidden" name="confirm_delete" value="yes">
                <div class="action-buttons">
                    <a href="index.php" class="btn-cancel">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                    <button type="submit" class="btn-danger">
                        <i class="fas fa-trash-alt"></i> Xác nhận xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../inc/footer.php'; ?>