<?php
require_once 'inc/auth.php';
require_once 'inc/header.php';

// ---------- LẤY CÁC CHỈ SỐ THỐNG KÊ ----------
// Tổng số xe đạp
$totalBikes = $pdo->query("SELECT COUNT(*) FROM bikes")->fetchColumn();

// Tổng số người dùng
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Tổng số đơn hàng
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Doanh thu từ đơn hàng đã thanh toán
$revenue = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();

// Đơn hàng chờ xác nhận (pending)
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// Tin nhắn liên hệ chưa đọc
$unreadContacts = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();

// Báo cáo chờ xử lý (pending)
$pendingReports = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();

// Tổng số lượt yêu thích (favorites)
$totalFavorites = $pdo->query("SELECT COUNT(*) FROM favorites")->fetchColumn();

// Số lượng đánh giá (reviews)
$totalReviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

// Lấy 5 đơn hàng mới nhất kèm thông tin xe và người mua
$recentOrders = $pdo->query("
    SELECT o.id, o.total_price, o.status, o.created_at,
           b.title as bike_title,
           u.first_name, u.last_name
    FROM orders o
    JOIN bikes b ON o.bike_id = b.id
    JOIN users u ON o.buyer_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll();

// Lấy 5 sản phẩm mới nhất (xe đạp)
$recentBikes = $pdo->query("
    SELECT id, title, price, created_at, status
    FROM bikes
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();
?>

<style>
    /* ---------- MÀU SẮC CHỦ ĐẠO ---------- */
    :root {
        --primary-orange: #F57C00;
        --bg-gray: #F5F5F5;
        --text-dark: #263238;
        --card-shadow: 0 10px 20px rgba(0,0,0,0.05);
        --hover-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    body {
        background-color: var(--bg-gray);
        color: var(--text-dark);
    }

    /* Tiêu đề trang */
    .dashboard-title {
        font-weight: 700;
        font-size: 1.8rem;
        color: var(--text-dark);
        border-left: 5px solid var(--primary-orange);
        padding-left: 1rem;
        margin-bottom: 1.5rem;
    }

    /* Thẻ thống kê (card) */
    .stat-card {
        background: white;
        border: none;
        border-radius: 1.25rem;
        transition: all 0.25s ease;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        position: relative;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }
    .stat-card .card-body {
        padding: 1.5rem;
    }
    .stat-icon {
        position: absolute;
        right: 1.2rem;
        top: 1.2rem;
        font-size: 2.8rem;
        opacity: 0.2;
        color: var(--primary-orange);
    }
    .stat-title {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }
    .stat-number {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 0;
        line-height: 1.2;
    }
    .stat-unit {
        font-size: 0.9rem;
        font-weight: 500;
        color: #6c757d;
    }
    .stat-footer {
        margin-top: 0.75rem;
        font-size: 0.75rem;
        color: #6c757d;
        border-top: 1px solid #eee;
        padding-top: 0.5rem;
    }
    .stat-footer i {
        color: var(--primary-orange);
        margin-right: 4px;
    }

    /* Bảng & danh sách */
    .section-card {
        background: white;
        border-radius: 1.25rem;
        border: none;
        box-shadow: var(--card-shadow);
        margin-bottom: 1.5rem;
        transition: box-shadow 0.2s;
    }
    .section-card:hover {
        box-shadow: var(--hover-shadow);
    }
    .section-card .card-header {
        background: white;
        border-bottom: 2px solid rgba(245, 124, 0, 0.2);
        padding: 1rem 1.5rem;
        font-weight: 700;
        font-size: 1.1rem;
        border-top-left-radius: 1.25rem;
        border-top-right-radius: 1.25rem;
    }
    .section-card .card-header i {
        color: var(--primary-orange);
        margin-right: 8px;
    }
    .table-custom {
        margin-bottom: 0;
    }
    .table-custom th {
        border-top: none;
        font-weight: 600;
        color: var(--text-dark);
        background-color: #fafafa;
    }
    .table-custom td {
        vertical-align: middle;
        color: var(--text-dark);
    }
    .badge-status {
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .badge-pending {
        background-color: #fff3e0;
        color: #F57C00;
    }
    .badge-paid {
        background-color: #e0f2e9;
        color: #2e7d32;
    }
    .badge-unpaid {
        background-color: #ffebee;
        color: #c62828;
    }
    .btn-outline-orange {
        color: var(--primary-orange);
        border-color: var(--primary-orange);
        border-radius: 40px;
        padding: 0.2rem 1rem;
        font-size: 0.8rem;
    }
    .btn-outline-orange:hover {
        background-color: var(--primary-orange);
        color: white;
    }
    /* Responsive */
    @media (max-width: 768px) {
        .stat-number {
            font-size: 1.5rem;
        }
        .stat-icon {
            font-size: 2rem;
        }
    }
</style>

<div class="container-fluid px-4">
    <h2 class="dashboard-title">
        <i class="fas fa-chalkboard-user me-2" style="color: #F57C00;"></i> Tổng quan hệ thống
    </h2>

    <!-- Hàng thẻ thống kê chính -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon"><i class="fas fa-bicycle"></i></div>
                    <div class="stat-title">XE ĐẠP</div>
                    <div class="stat-number"><?= number_format($totalBikes) ?></div>
                    <div class="stat-footer"><i class="fas fa-chart-line"></i> Tổng sản phẩm đang bán</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-title">NGƯỜI DÙNG</div>
                    <div class="stat-number"><?= number_format($totalUsers) ?></div>
                    <div class="stat-footer"><i class="fas fa-user-plus"></i> Khách hàng đã đăng ký</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-title">ĐƠN HÀNG</div>
                    <div class="stat-number"><?= number_format($totalOrders) ?></div>
                    <div class="stat-footer">
                        <i class="fas fa-clock"></i> Chờ xử lý: <?= $pendingOrders ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-title">DOANH THU</div>
                    <div class="stat-number"><?= number_format($revenue, 0, ',', '.') ?>đ</div>
                    <div class="stat-footer"><i class="fas fa-check-circle"></i> Từ đơn hàng đã thanh toán</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hàng thẻ phụ (cảnh báo và tương tác) -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon"><i class="fas fa-envelope-open-text"></i></div>
                    <div class="stat-title">LIÊN HỆ CHƯA ĐỌC</div>
                    <div class="stat-number"><?= $unreadContacts ?></div>
                    <div class="stat-footer">
                        <a href="<?= BASE_URL ?>contacts/index.php" class="btn btn-outline-orange btn-sm">Xem ngay <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon"><i class="fas fa-flag"></i></div>
                    <div class="stat-title">BÁO CÁO CHỜ DUYỆT</div>
                    <div class="stat-number"><?= $pendingReports ?></div>
                    <div class="stat-footer">
                        <a href="<?= BASE_URL ?>reports/index.php" class="btn btn-outline-orange btn-sm">Xử lý <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon"><i class="fas fa-heart"></i></div>
                    <div class="stat-title">YÊU THÍCH & ĐÁNH GIÁ</div>
                    <div class="stat-number"><?= number_format($totalFavorites) ?> / <?= number_format($totalReviews) ?></div>
                    <div class="stat-footer"><i class="fas fa-star" style="color: #F57C00;"></i> Lượt thích / Đánh giá</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hàng chi tiết: đơn hàng gần đây & sản phẩm mới -->
    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card section-card">
                <div class="card-header">
                    <i class="fas fa-clock"></i> Đơn hàng gần đây
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Sản phẩm</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recentOrders) > 0): ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?= $order['id'] ?></td>
                                            <td><?= htmlspecialchars($order['bike_title']) ?></td>
                                            <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                            <td><?= number_format($order['total_price'], 0, ',', '.') ?>đ</td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                $statusText = '';
                                                switch ($order['status']) {
                                                    case 'pending': $statusClass = 'badge-pending'; $statusText = 'Chờ xác nhận'; break;
                                                    case 'confirmed': $statusClass = 'badge-paid'; $statusText = 'Đã xác nhận'; break;
                                                    case 'shipped': $statusClass = 'badge-paid'; $statusText = 'Đang giao'; break;
                                                    case 'delivered': $statusClass = 'badge-paid'; $statusText = 'Đã giao'; break;
                                                    case 'cancelled': $statusClass = 'badge-unpaid'; $statusText = 'Đã hủy'; break;
                                                    default: $statusClass = 'badge-pending'; $statusText = $order['status'];
                                                }
                                                ?>
                                                <span class="badge-status <?= $statusClass ?>"><?= $statusText ?></span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center">Chưa có đơn hàng nào</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent text-end border-0">
                    <a href="<?= BASE_URL ?>orders/index.php" class="btn btn-link" style="color: #F57C00;">Xem tất cả đơn hàng <i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card section-card">
                <div class="card-header">
                    <i class="fas fa-bicycle"></i> Xe đạp mới đăng
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (count($recentBikes) > 0): ?>
                            <?php foreach ($recentBikes as $bike): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($bike['title']) ?></strong><br>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($bike['created_at'])) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold" style="color: #F57C00;"><?= number_format($bike['price'], 0, ',', '.') ?>đ</span><br>
                                        <span class="badge bg-light text-dark"><?= $bike['status'] ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center">Chưa có xe đạp nào</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="card-footer bg-transparent text-end border-0">
                    <a href="<?= BASE_URL ?>products/index.php" class="btn btn-link" style="color: #F57C00;">Quản lý sản phẩm <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <!-- Thêm một card tiện ích nhỏ: thống kê nhanh -->
            <div class="card section-card mt-3">
                <div class="card-header">
                    <i class="fas fa-chart-pie"></i> Thông tin bổ sung
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Danh mục xe:</span>
                        <strong><?= $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Thương hiệu:</span>
                        <strong><?= $pdo->query("SELECT COUNT(*) FROM brands")->fetchColumn() ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Xe nổi bật:</span>
                        <strong><?= $pdo->query("SELECT COUNT(*) FROM featured_bikes")->fetchColumn() ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'inc/footer.php'; ?>