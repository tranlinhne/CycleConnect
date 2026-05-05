<?php 
session_start();
require_once "config.php";
include "includes/header.php";

// 1. Lấy danh sách danh mục và thương hiệu
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");
$brands = $conn->query("SELECT id, name FROM brands ORDER BY name");

// 2. Nhận các tham số lọc từ URL
$cat_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brand_filter = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;
$sort_filter = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// --- THIẾT LẬP PHÂN TRANG ---
$limit = 10; // Số lượng xe hiển thị trên 1 trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit; // Điểm bắt đầu cắt dữ liệu

// 3. Xây dựng điều kiện lọc chung (Dùng cho cả việc đếm tổng và lấy dữ liệu)
$where_clause = "WHERE b.status = 'available' AND b.user_id IN (SELECT id FROM users WHERE role = 'user')";

if ($cat_filter > 0) {
    $where_clause .= " AND b.category_id = $cat_filter";
}
if ($brand_filter > 0) {
    $where_clause .= " AND b.brand_id = $brand_filter";
}

// 4. Đếm tổng số xe để tính tổng số trang
$count_sql = "SELECT COUNT(b.id) as total_items FROM bikes b $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_result)['total_items'];
$total_pages = ceil($total_rows / $limit); // Hàm ceil giúp làm tròn lên (VD: 11 xe / 10 = 1.1 -> 2 trang)

// 5. Xây dựng câu lệnh sắp xếp
$order_clause = "";
if ($sort_filter === 'price_asc') {
    $order_clause = "ORDER BY b.price ASC";
} elseif ($sort_filter === 'price_desc') {
    $order_clause = "ORDER BY b.price DESC";
} elseif ($sort_filter === 'oldest') {
    $order_clause = "ORDER BY b.created_at ASC"; 
} else {
    $order_clause = "ORDER BY b.created_at DESC"; 
}

// 6. Truy vấn dữ liệu chính (Có LIMIT và OFFSET để phân trang)
$sql = "SELECT b.*, 
        (SELECT image_url FROM bike_images WHERE bike_id = b.id ORDER BY is_primary DESC LIMIT 1) as main_image 
        FROM bikes b 
        $where_clause 
        $order_clause 
        LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);

// Hàm nhỏ để tạo URL giữ nguyên bộ lọc khi chuyển trang
function getPageUrl($pageNum, $cat, $brand, $sort) {
    return "?category=$cat&brand=$brand&sort=$sort&page=$pageNum";
}
?>

<style>
.filter-section { background: #f8fafc; padding: 20px 40px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
.filter-form { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
.filter-select { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; color: #333; min-width: 150px; }
.btn-filter { padding: 10px 20px; background: #2f5d62; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.3s; }
.btn-filter:hover { background: #1e3f42; }
.btn-post-ad-large { display: inline-block; padding: 10px 25px; background: #F57C00; color: white; font-weight: bold; font-size: 15px; border-radius: 8px; text-decoration: none; transition: 0.3s; white-space: nowrap; }
.btn-post-ad-large:hover { background: #e65100; color: white;}
.bike-list { display: flex; justify-content: flex-start; gap: 30px; flex-wrap: wrap; padding: 40px; max-width: 1200px; margin: 0 auto;}
.bike-card { width: 260px; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: 0.3s; position: relative; margin-bottom: 20px;}
.bike-card:hover { transform: translateY(-10px); }
.bike-card img { width: 100%; height: 180px; object-fit: cover; }
.bike-card .price { position: absolute; top: 150px; left: 50%; transform: translateX(-50%); background: #2f5d62; color: white; padding: 6px 14px; font-weight: bold; border-radius: 5px; white-space: nowrap;}
.bike-card h3 { margin: 25px 15px 10px; font-size: 16px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.meta { display: flex; justify-content: space-between; font-size: 13px; margin: 15px; color: #777; border-top: 1px solid #eee; padding-top: 15px;}
.view-btn { display: block; text-align: center; margin: 0 15px 20px; padding: 10px; background: #f1f5f9; color: #2f5d62; text-decoration: none; border-radius: 5px; font-weight: bold; transition: 0.3s;}
.view-btn:hover { background: #2f5d62; color: white; }

/* --- CSS MỚI CHO THANH PHÂN TRANG --- */
.pagination-wrapper { text-align: center; margin-bottom: 50px; width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px;}
.page-link { display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #222; color: #fff; text-decoration: none; font-weight: bold; transition: 0.3s; border: 1px solid #333;}
.page-link:hover { background: #F57C00; border-color: #F57C00; }
.page-link.active { background: #F57C00; border-color: #F57C00; pointer-events: none;}
.page-link.disabled { background: #444; color: #777; pointer-events: none; border-color: #444;}
</style>

<div class="filter-section">
    <form method="GET" action="classifieds.php" class="filter-form">
        <select name="category" class="filter-select">
            <option value="0">-- Tất cả loại xe --</option>
            <?php while($c = $categories->fetch_assoc()) {
                $selected = ($c['id'] == $cat_filter) ? 'selected' : '';
                echo "<option value='{$c['id']}' $selected>{$c['name']}</option>";
            } ?>
        </select>

        <select name="brand" class="filter-select">
            <option value="0">-- Tất cả thương hiệu --</option>
            <?php while($b = $brands->fetch_assoc()) {
                $selected = ($b['id'] == $brand_filter) ? 'selected' : '';
                echo "<option value='{$b['id']}' $selected>{$b['name']}</option>";
            } ?>
        </select>

        <select name="sort" class="filter-select">
            <option value="newest" <?= $sort_filter == 'newest' ? 'selected' : '' ?>>Tin mới nhất</option>
            <option value="oldest" <?= $sort_filter == 'oldest' ? 'selected' : '' ?>>Tin cũ nhất</option>
            <option value="price_asc" <?= $sort_filter == 'price_asc' ? 'selected' : '' ?>>Giá: Thấp đến Cao</option>
            <option value="price_desc" <?= $sort_filter == 'price_desc' ? 'selected' : '' ?>>Giá: Cao xuống Thấp</option>
        </select>

        <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Áp dụng</button>
        <a href="classifieds.php" style="color: #64748b; text-decoration: none; font-size: 14px; margin-left: 10px;">Xóa bộ lọc</a>
    </form>
    <a href="sell.php" class="btn-post-ad-large"><i class="fas fa-edit"></i> Đăng tin bán xe</a>
</div>

<div class="bike-list">
    <?php
    if ($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
    ?>
        <div class="bike-card">
            <img src="<?php echo !empty($row['main_image']) ? $row['main_image'] : 'assets/images/no-image.png'; ?>" alt="">
            <div class="price"><?php echo number_format($row['price']); ?> VNĐ</div>
            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
            <div class="meta">
                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['location'] ?? 'Cập nhật'); ?></span>
                <span><i class="fas fa-tools"></i> <?php echo htmlspecialchars($row['condition_bike']); ?></span>
            </div>
            <a href="ad-detail.php?id=<?php echo $row['id']; ?>" class="view-btn">Xem chi tiết & Liên hệ</a>
        </div>
    <?php 
        }
    } else {
        echo "<div style='text-align: center; width: 100%; color: #64748b; margin-top: 60px;'>
                <i class='fas fa-search' style='font-size: 40px; margin-bottom: 20px; color: #cbd5e1;'></i>
                <h3>Không tìm thấy chiếc xe nào phù hợp.</h3>
                <p>Hãy thử thay đổi tiêu chí hoặc bấm 'Xóa bộ lọc' để xem tất cả các xe nhé!</p>
              </div>";
    }
    ?>

    <!-- --- GIAO DIỆN THANH PHÂN TRANG --- -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-wrapper">
        <!-- Nút Trở về trang trước -->
        <?php $prev_class = ($page <= 1) ? 'disabled' : ''; ?>
        <a href="<?= getPageUrl($page - 1, $cat_filter, $brand_filter, $sort_filter) ?>" class="page-link <?= $prev_class ?>"><i class="fas fa-chevron-left"></i></a>

        <!-- In ra các số trang 1, 2, 3... -->
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php $active_class = ($page == $i) ? 'active' : ''; ?>
            <a href="<?= getPageUrl($i, $cat_filter, $brand_filter, $sort_filter) ?>" class="page-link <?= $active_class ?>"><?= $i ?></a>
        <?php endfor; ?>

        <!-- Nút Tới trang sau -->
        <?php $next_class = ($page >= $total_pages) ? 'disabled' : ''; ?>
        <a href="<?= getPageUrl($page + 1, $cat_filter, $brand_filter, $sort_filter) ?>" class="page-link <?= $next_class ?>"><i class="fas fa-chevron-right"></i></a>
    </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>