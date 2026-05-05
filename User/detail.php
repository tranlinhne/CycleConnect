<?php
require_once "config.php";
include "includes/header.php";

/* ===== LẤY ID AN TOÀN ===== */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ===== LẤY FULL THÔNG TIN XE ===== */
$sql = "
SELECT b.*, 
       c.name AS category_name,
       br.name AS brand_name,
       COUNT(o.id) AS total_orders
FROM bicycles b
LEFT JOIN categories c ON b.category_id = c.id
LEFT JOIN brands br ON b.brand_id = br.id
LEFT JOIN orders o ON b.bicycle_id = o.bike_id AND o.status IN ('accepted','deposit_paid','completed')
WHERE b.bicycle_id = $id
GROUP BY b.bicycle_id
LIMIT 1
";

$result = mysqli_query($conn, $sql);

/* ===== LẤY NỘI DUNG TAB CHI TIẾT ===== */
$section_sql = "SELECT * FROM product_sections WHERE bicycle_id = $id LIMIT 1";
$section_result = mysqli_query($conn, $section_sql);
$product_section = mysqli_fetch_assoc($section_result);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    echo "<h2 style='padding:50px'>Không tìm thấy xe</h2>";
    exit;
}
?>

<style>

/* ===== HERO ===== */
.detail-banner {
    display: flex;
    position: relative;
    overflow: hidden;
    min-height: 500px;
    background: transparent;
    margin-top: 30px;
}



/* nền  */
.detail-banner::after {
    content: "";
    position: absolute;
    right: 0;
    top: 0;
    bottom: -188px;
    width: 50%;
    
    background: url('assets/images/under_background.png') no-repeat;
    
    background-size: 80%;          /* ↓ nhỏ lại (tăng/giảm tùy ý) */
    background-position: bottom center; /* ↓ đẩy xuống dưới */

    z-index: 0;
}

/* ===== LEFT ===== */
.detail-left {
    width: 45%;
    padding: 80px 80px 80px 120px;
    z-index: 2;
}

.detail-left h1 { font-size: 38px; }
.detail-left h2 { color: red; }

/* ===== INFO ===== */
.info-table { margin-top: 25px; }

.info-row {
    display: flex;
    gap: 20px;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.label { width: 130px; color: #888; }
.value { flex: 1; font-weight: normal; }

/* ===== RIGHT ===== */
.detail-right {
    width: 30%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 2;
}

/* ===== MAIN IMAGE ===== */
#mainImage {
    max-width: 100%;
    margin-bottom: 30px;
    margin-right: -30px;
    filter: drop-shadow(0 20px 30px rgba(0,0,0,0.5));
    transition: 0.3s;
}

/* ===== THUMB ===== */
.thumbs-outside {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
    padding: 12px;
    background: #fff;
    border-radius: 14px;
    width: fit-content;
    margin-left: auto;
    margin-right: 40px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.thumbs-outside img {
    width: 90px;
    height: 90px;
    object-fit: cover;
    cursor: pointer;
    border-radius: 8px;
    border: 1px solid #eee;
    opacity: 0.85;
    transition: 0.25s;
}

.thumbs-outside img:hover {
    opacity: 1;
    transform: scale(1.08);
}

.thumbs-outside img.active {
    border-color: #ee4d2d;
    opacity: 1;
    transform: scale(1.1);
}

/* ===== BUTTON GROUP ===== */
.action-buttons {
    display: flex;
    gap: 15px;
    margin-top: 25px;
}

.action-buttons button {
    padding: 15px 26px;
    font-size: 16px;
    border-radius: 10px;
    cursor: pointer;
    transition: 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 144px;
}

/* nút giỏ */
.cart-btn {
    background: #fff;
    color: #e53935;
    border: 2px solid #e53935;
}

.cart-btn:hover {
    background: #ffe5e5;
}

/* nút đặt hàng */
.order-btn {
    background: linear-gradient(90deg, #ff3b30, #c62828);
    color: #fff;
    border: none;
}

.order-btn:hover {
    transform: scale(1.05);
}

/* icon */
.cart-icon {
    font-size: 22px;
    margin-right: 8px;
}

/* ===== TAB AREA FULL WIDTH ===== */
.product-tabs {
    width: 100%;
    margin-top: 80px;
}

/* thanh tab full ngang */
.tab-buttons {
    width: 100%;
    padding-left: 120px;   /* canh thẳng với khối trái phía trên */
    border-bottom: 3px solid #eee;
    display: flex;
    gap: 40px;
    background: #fff;
}

/* nút tab */
.tab-btn {
    padding: 18px 0;
    font-size: 20px;
    cursor: pointer;
    color: #888;
    border-bottom: 3px solid transparent;
    transition: 0.3s;
}

.tab-btn.active {
    color: #e53935;
    border-color: #e53935;
    font-weight: bold;
}

/* nội dung tab full ngang */
.tab-content {
    display: none;
    width: 100%;
    padding: 50px 120px;  /* canh lề giống tab */
    line-height: 1.8;
    font-size: 16px;
    color: #444;
}

.tab-content.active {
    display: block;
}

/* khối nội dung lớn */
.big-content {
    background: #fafafa;
    border-radius: 16px;
    padding: 45px 60px;   /* bỏ padding trái dư */
    white-space: pre-line;
    max-width: 1500px;    /* giúp đọc dễ hơn */
}
.tab-buttons {
    display: flex;
    border-bottom: 3px solid #eee;
    gap: 30px;
}

.tab-btn {
    padding: 15px 5px;
    font-size: 18px;
    cursor: pointer;
    color: #888;
    border-bottom: 3px solid transparent;
    transition: 0.3s;
}

.tab-btn.active {
    color: #e53935;
    border-color: #e53935;
    font-weight: bold;
}

.tab-content {
    display: none;
    width: 100%;
    padding: 50px 120px 80px 120px; /* container giữ padding */
    background: #f5f5f5;            /* nền xám full ngang */
}

.tab-content.active {
    display: block;
}

/* box nội dung */
.spec-box {
    background: #fafafa;
    padding: 30px;
    border-radius: 14px;
    margin-bottom: 25px;
    white-space: pre-line;
}
</style>

<!-- ===== MAIN ===== -->
<div class="detail-banner">

    <!-- KHỐI ĐỎ -->
    <div class="red-shape"></div>

    <!-- LEFT -->
    <div class="detail-left">
        <h1><?php echo htmlspecialchars($row['name']); ?></h1>
        <h2><?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ</h2>

        <div class="info-table">
            <div class="info-row">
                <span class="label">Nơi sản xuất</span>
                <span class="value"><?php echo htmlspecialchars($row['location']); ?></span>
            </div>

            <div class="info-row">
                <span class="label">Tình trạng</span>
                <span class="value"><?php echo htmlspecialchars($row['condition_status']); ?></span>
            </div>

            <div class="info-row">
                <span class="label">Kích thước</span>
                <span class="value"><?php echo htmlspecialchars($row['frame_size']); ?></span>
            </div>

            <div class="info-row">
                <span class="label">Mô tả</span>
                <span class="value" style="font-weight: normal; color:#555;">
                    <?php echo htmlspecialchars($row['description']); ?>
                </span>
            </div>

            <div class="info-row">
    <span class="label">Thể loại</span>
    <span class="value"><?php echo $row['category_name']; ?></span>
</div>

<div class="info-row">
    <span class="label">Thương hiệu</span>
    <span class="value"><?php echo $row['brand_name']; ?></span>
</div>

<div class="info-row">
    <span class="label">Lượt mua</span>
    <span class="value"><?php echo $row['total_orders']; ?> đã bán</span>
</div>

            <div class="action-buttons">
                <button class="cart-btn">
                    <span class="cart-icon">🛒</span>
                    Thêm Giỏ Hàng
                </button>
                <button class="order-btn">Đặt Hàng</button>
            </div>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="detail-right">
        <img id="mainImage"
             src="<?php echo $row['main_image']; ?>"
             onerror="this.src='assets/images/default-bike.png'">
    </div>

</div>

<!-- ===== THUMBNAILS ===== -->
<div class="thumbs-outside">
    <?php foreach (['main_image','sub_image1','sub_image2','sub_image3'] as $img): ?>
        <?php if (!empty($row[$img])): ?>
            <img src="<?php echo $row[$img]; ?>" onclick="changeImage(this)">
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!-- ================= TAB ================= -->
<div class="product-tabs">

    <div class="tab-buttons">
        <div class="tab-btn active" onclick="openTab(0)">Chi tiết sản phẩm</div>
        <div class="tab-btn" onclick="openTab(1)">Đánh giá</div>
    </div>

<!-- TAB 1 -->
<div class="tab-content active">

    <?php if($product_section): ?>

        <div class="big-content">
            <?php
                echo $product_section['thong_so'] . "\n\n";
                echo $product_section['qua_tang'] . "\n\n";
                echo $product_section['huong_dan'] . "\n\n";
                echo $product_section['dong_kiem'] . "\n\n";
                echo $product_section['doi_tra'];
            ?>
        </div>

    <?php else: ?>
        <p style="padding-left:120px">Chưa có thông tin chi tiết.</p>
    <?php endif; ?>

</div>

    <!-- TAB 2 -->
    <div class="tab-content">
        <h3>Đánh giá sản phẩm</h3>
        <p>⭐ Chức năng đánh giá sẽ được cập nhật sau.</p>
        <p>Hiện chưa có đánh giá nào.</p>
    </div>

</div>

<script>
function changeImage(img) {
    const main = document.getElementById("mainImage");

    main.style.opacity = 0;

    setTimeout(() => {
        main.src = img.src;
        main.style.opacity = 1;
    }, 150);

    document.querySelectorAll(".thumbs-outside img").forEach(el => {
        el.classList.remove("active");
    });

    img.classList.add("active");
}

document.addEventListener("DOMContentLoaded", function () {
    const first = document.querySelector(".thumbs-outside img");
    if (first) first.classList.add("active");
});

function openTab(index) {
    document.querySelectorAll(".tab-btn").forEach(btn => btn.classList.remove("active"));
    document.querySelectorAll(".tab-content").forEach(tab => tab.classList.remove("active"));

    document.querySelectorAll(".tab-btn")[index].classList.add("active");
    document.querySelectorAll(".tab-content")[index].classList.add("active");
}
</script>

<?php include "includes/footer.php"; ?>