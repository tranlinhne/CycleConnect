<?php
session_start();
require_once "config.php";
include "includes/header.php";

// Lấy ID của xe từ thanh địa chỉ URL
$bike_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Truy vấn thông tin xe VÀ thông tin người bán (JOIN với bảng users)
// Lưu ý: Mình giả định bảng users của bạn có cột 'fullname', 'phone', và 'created_at'
$sql_bike = "SELECT b.*, u.full_name AS fullname, u.phone, u.created_at as user_joined 
             FROM bikes b 
             JOIN users u ON b.user_id = u.id 
             WHERE b.id = ? AND b.status = 'available'";
$stmt = $conn->prepare($sql_bike);
$stmt->bind_param("i", $bike_id);
$stmt->execute();
$bike = $stmt->get_result()->fetch_assoc();

// Nếu không tìm thấy xe, báo lỗi
if (!$bike) {
    echo "<div style='text-align:center; padding: 100px; font-size: 20px;'>Tin đăng này không tồn tại hoặc đã bị ẩn.</div>";
    include "includes/footer.php";
    exit;
}

// Truy vấn lấy danh sách tất cả hình ảnh của xe này
$sql_images = "SELECT image_url FROM bike_images WHERE bike_id = ? ORDER BY is_primary DESC";
$stmt_img = $conn->prepare($sql_images);
$stmt_img->bind_param("i", $bike_id);
$stmt_img->execute();
$images = $stmt_img->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<style>
/* Reset một số margin cơ bản */
.ad-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }

/* CỘT TRÁI: THÔNG TIN XE */
.ad-left { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }

/* Thư viện ảnh */
.gallery-main { width: 100%; height: 450px; border-radius: 8px; overflow: hidden; margin-bottom: 15px; background: #f1f5f9; display: flex; align-items: center; justify-content: center;}
.gallery-main img { max-width: 100%; max-height: 100%; object-fit: contain; }
.gallery-thumbs { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 10px; }
.gallery-thumbs img { width: 80px; height: 80px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 2px solid transparent; transition: 0.2s; }
.gallery-thumbs img:hover, .gallery-thumbs img.active { border-color: #F57C00; }

/* Chi tiết xe */
.ad-title { font-size: 24px; color: #1e293b; margin: 20px 0 10px; }
.ad-price { font-size: 28px; color: #d93025; font-weight: bold; margin-bottom: 20px; }
.ad-meta { display: flex; flex-wrap: wrap; gap: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; color: #475569;}
.ad-desc-box { line-height: 1.8; color: #334155; padding-top: 20px; border-top: 1px solid #e2e8f0; }

/* CỘT PHẢI: THÔNG TIN NGƯỜI BÁN */
.ad-right { display: flex; flex-direction: column; gap: 20px; }
.seller-card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; }
.seller-avatar { width: 80px; height: 80px; background: #2f5d62; color: #fff; font-size: 30px; font-weight: bold; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto 15px; }
.seller-name { font-size: 18px; font-weight: bold; color: #1e293b; margin-bottom: 5px; }
.seller-joined { font-size: 13px; color: #64748b; margin-bottom: 20px; }

/* Nút liên hệ */
.btn-contact { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px; border-radius: 8px; font-weight: bold; text-decoration: none; font-size: 16px; margin-bottom: 10px; transition: 0.3s; cursor: pointer; border: none;}
.btn-phone { background: #4caf50; color: white; }
.btn-phone:hover { background: #388e3c; }
.btn-chat { background: #f1f5f9; color: #1e293b; }
.btn-chat:hover { background: #e2e8f0; }

/* Đáp ứng giao diện điện thoại */
@media (max-width: 900px) {
    .ad-container { grid-template-columns: 1fr; }
}
</style>

<div class="ad-container">
    <!-- Cột Trái: Hình ảnh và Mô tả -->
    <div class="ad-left">
        <!-- Khối hiển thị ảnh -->
        <div class="gallery-main">
            <img id="mainImage" src="<?= !empty($images) ? $images[0]['image_url'] : 'assets/images/no-image.png' ?>" alt="Hình ảnh chính">
        </div>
        
        <?php if(count($images) > 1): ?>
        <div class="gallery-thumbs">
            <?php foreach($images as $index => $img): ?>
                <img src="<?= $img['image_url'] ?>" class="<?= $index === 0 ? 'active' : '' ?>" onclick="changeMainImage(this.src, this)">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Khối Thông tin -->
        <h1 class="ad-title"><?= htmlspecialchars($bike['title']) ?></h1>
        <div class="ad-price"><?= number_format($bike['price']) ?> VNĐ</div>
        
        <div class="ad-meta">
            <div><i class="fas fa-map-marker-alt text-primary"></i> <strong>Khu vực:</strong> <?= htmlspecialchars($bike['location']) ?></div>
            <div><i class="fas fa-tools text-warning"></i> <strong>Tình trạng:</strong> <?= htmlspecialchars($bike['condition_bike']) ?></div>
            <div><i class="fas fa-calendar-alt text-success"></i> <strong>Ngày đăng:</strong> <?= date('d/m/Y', strtotime($bike['created_at'])) ?></div>
        </div>

        <div class="ad-desc-box">
            <h3 style="margin-bottom: 15px; color: #1e293b;">Mô tả chi tiết</h3>
            <?= nl2br(htmlspecialchars($bike['description'])) ?>
        </div>
    </div>

    <!-- Cột Phải: Thẻ thông tin người bán -->
    <div class="ad-right">
        <div class="seller-card">
            <!-- Lấy chữ cái đầu của tên làm Avatar -->
            <div class="seller-avatar"><?= mb_substr($bike['fullname'] ?? 'U', 0, 1, "utf-8") ?></div>
            <div class="seller-name"><?= htmlspecialchars($bike['fullname'] ?? 'Người dùng GreenRide') ?></div>
            <div class="seller-joined">Tham gia từ: <?= date('m/Y', strtotime($bike['user_joined'])) ?></div>
            
            <?php
            // KIỂM TRA QUYỀN SỞ HỮU
            // Lấy ID người dùng đang đăng nhập (nếu chưa đăng nhập thì mặc định là 0)
            $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            
            // So sánh ID: Nếu khớp thì đây là chủ xe
            $is_owner = ($current_user_id === $bike['user_id']);
            ?>

            <?php if ($is_owner): ?>
                <!-- GIAO DIỆN DÀNH RIÊNG CHO CHỦ BÀI ĐĂNG -->
                <div style="background: #e0f2f1; color: #2f5d62; padding: 15px; border-radius: 8px; margin-bottom: 15px; font-weight: bold; border: 1px dashed #2f5d62;">
                    <i class="fas fa-user-check"></i> Đây là tin đăng của bạn
                </div>
                
                <a href="edit-ad.php?id=<?= $bike['id'] ?>" class="btn-contact" style="background: #F57C00; color: white;">
                    <i class="fas fa-edit"></i> Chỉnh sửa tin đăng
                </a>
                
                <!-- Nút dẫn vào Hộp thư của chiếc xe này -->
                <a href="chat.php?bike_id=<?= $bike['id'] ?>" class="btn-contact" style="background: #2f5d62; color: white;">
                    <i class="fas fa-inbox"></i> Tin nhắn khách hàng
                </a>

            <?php else: ?>
                <!-- GIAO DIỆN DÀNH CHO KHÁCH TÌM MUA XE -->
                <button class="btn-contact btn-phone" onclick="revealPhone(this, '<?= htmlspecialchars($bike['phone']) ?>')">
                    <i class="fas fa-phone-alt"></i> Bấm để hiện số
                </button>
                
                <a href="chat.php?receiver_id=<?= $bike['user_id'] ?>&bike_id=<?= $bike['id'] ?>" class="btn-contact btn-chat">
                    <i class="fas fa-comment-dots"></i> Chat với người bán
                </a>
            <?php endif; ?>

        </div> 
    </div>
</div>

<script>
// Chức năng đổi ảnh khi bấm vào ảnh nhỏ
function changeMainImage(src, element) {
    document.getElementById('mainImage').src = src;
    
    // Gỡ viền cam ở ảnh cũ, gắn viền cam vào ảnh mới
    let thumbs = document.querySelectorAll('.gallery-thumbs img');
    thumbs.forEach(thumb => thumb.classList.remove('active'));
    element.classList.add('active');
}

// Chức năng ẩn/hiện số điện thoại cho ngầu giống Chợ Tốt
function revealPhone(btnElement, phoneNumber) {
    if(!phoneNumber) phoneNumber = 'Chưa cập nhật số';
    btnElement.innerHTML = '<i class="fas fa-phone-alt"></i> ' + phoneNumber;
    btnElement.style.background = '#2e7d32'; // Đổi màu xậm hơn chút khi đã bấm
}
</script>

<?php include "includes/footer.php"; ?>