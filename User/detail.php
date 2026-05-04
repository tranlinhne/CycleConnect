<?php
session_start();
require_once "config.php";
include "includes/header.php";
?>

<link rel="stylesheet" href="assets/css/detail.css">

<?php
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

/* ===== CART COUNT ===== */
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'] ?? 0;
    }
}

/* ===== ID ===== */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ===== PRODUCT ===== */
$sql = "
SELECT b.*, 
       c.name AS category_name,
       br.name AS brand_name,
       COUNT(o.id) AS total_orders
FROM bicycles b
LEFT JOIN categories c ON b.category_id = c.id
LEFT JOIN brands br ON b.brand_id = br.id
LEFT JOIN orders o ON b.bicycle_id = o.bike_id 
    AND o.status IN ('accepted','deposit_paid','completed')
WHERE b.bicycle_id = $id
GROUP BY b.bicycle_id
LIMIT 1
";
$result = mysqli_query($conn, $sql);

/* ===== SECTION ===== */
$section_sql = "SELECT * FROM product_sections WHERE bicycle_id = $id LIMIT 1";
$product_section = mysqli_fetch_assoc(mysqli_query($conn, $section_sql));

/* ===== REVIEW ===== */
$review_sql = "
SELECT r.*, u.name 
FROM reviews r
JOIN users u ON r.user_id = u.id
WHERE r.bicycle_id = $id
ORDER BY r.created_at DESC
";
$review_result = mysqli_query($conn, $review_sql);

/* ===== AVG ===== */
$avg_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE bicycle_id = $id";
$avg_result = mysqli_fetch_assoc(mysqli_query($conn, $avg_sql));

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    echo "<h2 style='padding:50px'>Không tìm thấy xe</h2>";
    exit;
}
?>

<!-- ===== MAIN ===== -->
<div class="detail-banner">
    <div class="red-shape"></div>

    <div class="detail-left">
        <h1><?= htmlspecialchars($row['name']); ?></h1>
        <h2><?= number_format($row['price'], 0, ',', '.'); ?> VNĐ</h2>

        <div class="info-table">
            <div class="info-row"><span class="label">Nơi sản xuất</span><span class="value"><?= $row['location']; ?></span></div>
            <div class="info-row"><span class="label">Tình trạng</span><span class="value"><?= $row['condition_status']; ?></span></div>
            <div class="info-row"><span class="label">Kích thước</span><span class="value"><?= $row['frame_size']; ?></span></div>
            <div class="info-row"><span class="label">Mô tả</span><span class="value"><?= $row['description']; ?></span></div>
            <div class="info-row"><span class="label">Thể loại</span><span class="value"><?= $row['category_name']; ?></span></div>
            <div class="info-row"><span class="label">Thương hiệu</span><span class="value"><?= $row['brand_name']; ?></span></div>
            <div class="info-row"><span class="label">Lượt mua</span><span class="value"><?= $row['total_orders']; ?> đã bán</span></div>

            <div class="action-buttons">
                <a href="cart.php?action=add&id=<?= $row['bicycle_id']; ?>" class="cart-btn">🛒 Thêm Giỏ Hàng</a>
                <a href="checkout.php?id=<?= $row['bicycle_id']; ?>" class="order-btn">Đặt Hàng</a>
            </div>
        </div>
    </div>

    <div class="detail-right">
        <img id="mainImage" src="<?= $row['main_image']; ?>" 
             onerror="this.src='assets/images/default-bike.png'">
    </div>
</div>

<!-- ===== THUMB ===== -->
<div class="thumbs-wrapper">
    <div class="thumbs-outside">
        <?php foreach (['main_image','sub_image1','sub_image2','sub_image3'] as $img): ?>
            <?php if (!empty($row[$img])): ?>
                <div class="thumb-item">
                    <img src="<?= $row[$img]; ?>" onclick="changeImage(this)">
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- ===== TAB ===== -->
<div class="product-tabs">

<div class="tab-buttons">
    <div class="tab-btn active" onclick="openTab(0)">Chi tiết sản phẩm</div>
    <div class="tab-btn" onclick="openTab(1)">Đánh giá</div>
</div>

<!-- TAB 1 -->
<div class="tab-content active">
<?php if($product_section): ?>
<div class="big-content">
<?= $product_section['thong_so'] ?>
<?= $product_section['qua_tang'] ?>
<?= $product_section['huong_dan'] ?>
<?= $product_section['dong_kiem'] ?>
<?= $product_section['doi_tra'] ?>
</div>
<?php else: ?>
<p>Chưa có thông tin chi tiết.</p>
<?php endif; ?>
</div>

<!-- TAB 2: REVIEW -->
<div class="tab-content">

<h3>Đánh giá sản phẩm</h3>

<!-- SUMMARY -->
<div class="review-summary">
    <h2><?= round($avg_result['avg_rating'],1) ?: 0 ?> ⭐</h2>
    <p><?= $avg_result['total'] ?: 0 ?> đánh giá</p>
</div>

<!-- FORM -->
<?php if(isset($_SESSION['khach_hang'])): ?>
<form method="POST" action="submit_review.php" class="review-form" onsubmit="return validateReview()">
    <input type="hidden" name="bicycle_id" value="<?= $id ?>">

    <div class="stars">
        <?php for($i=1;$i<=5;$i++): ?>
            <span onclick="setRating(<?= $i ?>)">★</span>
        <?php endfor; ?>
    </div>

    <input type="hidden" name="rating" id="rating" required>

    <textarea name="comment" placeholder="Nhập đánh giá..." required></textarea>
    <button type="submit">Gửi đánh giá</button>
</form>
<?php else: ?>
<p>👉 Vui lòng đăng nhập để đánh giá</p>
<?php endif; ?>

<hr>

<!-- LIST -->
<?php if(mysqli_num_rows($review_result) > 0): ?>
    <?php while($rv = mysqli_fetch_assoc($review_result)): ?>
        <div class="review-item">

    <div class="review-user">
        <img src="assets/images/avatar.png" class="avatar">
        <div>
            <div class="name"><?= htmlspecialchars($rv['name']) ?></div>
            <div class="stars-display">
                <?= str_repeat("★", (int)$rv['rating']); ?>
            </div>
        </div>
    </div>

    <p class="review-content"><?= htmlspecialchars($rv['comment']); ?></p>

    <small><?= $rv['created_at']; ?></small>

</div>
    <?php endwhile; ?>
<?php else: ?>
    <p>Chưa có đánh giá nào</p>
<?php endif; ?>

</div>

</div>

<script>
function setRating(val) {
    document.getElementById("rating").value = val;

    const stars = document.querySelectorAll(".stars span");

    stars.forEach((s, i) => {
        s.style.color = (i < val) ? "#ffc107" : "#ccc";
    });
}

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

function openTab(index) {
    document.querySelectorAll(".tab-btn").forEach(btn => btn.classList.remove("active"));
    document.querySelectorAll(".tab-content").forEach(tab => tab.classList.remove("active"));
    document.querySelectorAll(".tab-btn")[index].classList.add("active");
    document.querySelectorAll(".tab-content")[index].classList.add("active");
}

function validateReview() {
    const rating = document.getElementById("rating").value;

    if (!rating || rating == 0) {
        alert("Vui lòng chọn số sao!");
        return false;
    }
    return true;
}
</script>

<?php include "includes/footer.php"; ?>