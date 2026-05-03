<?php
session_start();
include_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/auth-handler.php';

if (!isLoggedIn()) {
    header('Location: login.php?error=Vui lòng đăng nhập để xem báo cáo');
    exit();
}

$user_id = $_SESSION['user_id'];

// --- TRUY VẤN 1: Danh sách xe người dùng đã đăng bán (Có kèm ID) ---
$sql_my_bikes = "SELECT b.id, b.title, b.price, b.status, b.created_at,
                        (SELECT image_url FROM bike_images WHERE bike_id = b.id ORDER BY is_primary DESC LIMIT 1) as main_image
                 FROM bikes b 
                 WHERE b.user_id = ? 
                 ORDER BY b.created_at DESC";
$stmt_bikes = $conn->prepare($sql_my_bikes);
$stmt_bikes->bind_param("i", $user_id);
$stmt_bikes->execute();
$res_my_bikes = $stmt_bikes->get_result();

// --- TRUY VẤN 2: Thống kê Dòng xe mua nhiều nhất (Sở thích cá nhân) ---
$sql_fav = "SELECT c.name, COUNT(o.id) as cnt 
            FROM orders o JOIN bikes b ON o.bike_id = b.id JOIN categories c ON b.category_id = c.id 
            WHERE o.buyer_id = ? AND o.payment_status = 'paid' 
            GROUP BY c.id ORDER BY cnt DESC LIMIT 1";
$stmt_fav = $conn->prepare($sql_fav);
$stmt_fav->bind_param("i", $user_id);
$stmt_fav->execute();
$fav = $stmt_fav->get_result()->fetch_assoc();

// --- TRUY VẤN 3: Top 5 xe bán chạy nhất CỦA TÔI ---
$sql_top = "SELECT b.title, COUNT(o.id) as qty, SUM(o.total_price) as rev 
            FROM orders o JOIN bikes b ON o.bike_id = b.id 
            WHERE o.payment_status = 'paid' AND b.user_id = ? 
            GROUP BY b.id ORDER BY qty DESC LIMIT 5";
$stmt_top = $conn->prepare($sql_top);
$stmt_top->bind_param("i", $user_id);
$stmt_top->execute();
$res_top = $stmt_top->get_result();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>My Statistics - GreenRide</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .s-container { max-width: 900px; margin: 50px auto; padding: 0 20px; }
        .s-grid { display: grid; grid-template-columns: 1fr; gap: 30px; }
        .s-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #edf2f7; }
        .s-title { color: #2f5d62; font-size: 1.5rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;}
        .s-table { width: 100%; border-collapse: collapse; }
        .s-table th { text-align: left; padding: 12px; background: #f8fafc; color: #64748b; font-size: 0.85rem; text-transform: uppercase; border-radius: 4px;}
        .s-table td { padding: 15px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .fav-box { background: linear-gradient(135deg, #2f5d62 0%, #4a8e95 100%); color: white; padding: 25px; border-radius: 12px; text-align: center; }
        .fav-name { font-size: 2rem; font-weight: 800; display: block; margin: 10px 0; color: #f4a261; }
        
        /* Nhãn trạng thái cho xe của tôi */
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; display: inline-block; }
        .status-available { background-color: #e6f4ea; color: #1e8e3e; }
        .status-sold { background-color: #fce8e6; color: #d93025; }
        .status-pending { background-color: #fef7e0; color: #f29900; }

        /* Nút quay lại (Giống bên Profile) */
        .back-btn { 
            display: inline-flex; align-items: center; gap: 8px; 
            padding: 10px 20px; background-color: #f8fafc; color: #475569; 
            text-decoration: none; border-radius: 8px; font-weight: 600; 
            font-size: 0.95rem; border: 1px solid #e2e8f0; 
            transition: all 0.3s ease; margin-bottom: 20px; 
        }
        .back-btn:hover { background-color: #e2e8f0; color: #0f172a; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="s-container">

    <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại</a>

    <h1 style="color: #2f5d62; margin-bottom: 30px; text-align: center;">Bảng Điều Khiển Của Tôi</h1>

    <div class="s-grid">
        
        <div class="s-card">
            <div class="s-title"><i class="fas fa-bicycle text-primary"></i> Xe tôi đã đăng bán</div>
            <?php if ($res_my_bikes->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="s-table">
                        <tr>
                            <th>Tên xe</th>
                            <th>Giá bán</th>
                            <th>Ngày đăng</th>
                            <th>Trạng thái</th>
                        </tr>
                        <?php while($bike = $res_my_bikes->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <a href="ad-detail.php?id=<?= $bike['id'] ?>" style="color: #2f5d62; text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='#F57C00'" onmouseout="this.style.color='#2f5d62'">
                                    <strong><?= htmlspecialchars($bike['title']) ?></strong>
                                </a>
                            </td>
                            <td style="color: #F57C00; font-weight: bold;"><?= number_format($bike['price']) ?>đ</td>
                            <td><?= date('d/m/Y', strtotime($bike['created_at'])) ?></td>
                            <td>
                                <?php 
                                    if ($bike['status'] === 'available') {
                                        echo '<span class="status-badge status-available"><i class="fas fa-check-circle"></i> Đang bán</span>';
                                    } elseif ($bike['status'] === 'sold') {
                                        echo '<span class="status-badge status-sold"><i class="fas fa-times-circle"></i> Đã bán</span>';
                                    } else {
                                        echo '<span class="status-badge status-pending">Chờ duyệt</span>';
                                    }
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #94a3b8; padding: 20px 0;">Bạn chưa đăng bán chiếc xe nào.</p>
                <div style="text-align: center;">
                    <a href="post-ad.php" style="background: #F57C00; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Đăng tin ngay</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="s-card">
            <div class="s-title"><i class="fas fa-fingerprint text-success"></i> Phân tích sở thích mua hàng</div>
            <?php if ($fav): ?>
                <div class="fav-box">
                    <span>Bạn là một tín đồ của dòng xe</span>
                    <span class="fav-name"><?= htmlspecialchars($fav['name']) ?></span>
                    <p>Bạn đã thực hiện thành công <strong><?= $fav['cnt'] ?></strong> đơn hàng cho loại xe này.</p>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #94a3b8;">Bạn chưa có lịch sử mua hàng để thực hiện phân tích.</p>
            <?php endif; ?>
        </div>

        <div class="s-card">
            <div class="s-title"><i class="fas fa-trophy text-warning"></i> Top 5 Sản phẩm Bán chạy nhất của tôi</div>
            <?php if ($res_top->num_rows > 0): ?>
                <table class="s-table">
                    <tr><th>Tên xe</th><th>Lượt bán</th><th>Tổng doanh thu</th></tr>
                    <?php while($r = $res_top->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($r['title']) ?></strong></td>
                        <td><?= $r['qty'] ?> giao dịch</td>
                        <td style="color: #2f5d62; font-weight: bold;"><?= number_format($r['rev']) ?>đ</td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #94a3b8; padding: 20px 0;">Bạn chưa có giao dịch bán hàng nào thành công.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>