<?php
require_once '../inc/auth.php';
require_once '../inc/header.php';

// Phân trang
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Đếm tổng số đơn hàng
$total = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pages = ceil($total / $limit);

// Lấy danh sách đơn hàng có phân trang
$stmt = $pdo->prepare("
    SELECT o.*, b.title as bike_title, 
           buyer.username as buyer_name, seller.username as seller_name 
    FROM orders o 
    JOIN bikes b ON o.bike_id = b.id 
    JOIN users buyer ON o.buyer_id = buyer.id 
    JOIN users seller ON o.seller_id = seller.id 
    ORDER BY o.id DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindParam(1, $limit, PDO::PARAM_INT);
$stmt->bindParam(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

// Xử lý đánh dấu đã thanh toán
if (isset($_GET['mark_paid'])) {
    $id = (int)$_GET['mark_paid'];
    $pdo->prepare("UPDATE orders SET payment_status='paid' WHERE id=?")->execute([$id]);
    header('Location: index.php?page=' . $page);
    exit;
}
?>

<style>
    /* ---------- MÀU SẮC CHỦ ĐẠO ---------- */
    :root {
        --primary-orange: #F57C00;
        --bg-gray: #F5F5F5;
        --text-dark: #263238;
        --border-light: #e0e0e0;
        --card-shadow: 0 6px 12px rgba(0,0,0,0.05);
        --hover-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }

    body {
        background-color: var(--bg-gray);
        color: var(--text-dark);
    }

    /* Tiêu đề & thanh công cụ */
    .page-header {
        background: white;
        border-radius: 1rem;
        padding: 1.2rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .page-header h2 {
        font-weight: 700;
        font-size: 1.6rem;
        margin: 0;
        color: var(--text-dark);
        border-left: 5px solid var(--primary-orange);
        padding-left: 1rem;
    }

    /* Bảng đơn hàng */
    .order-table {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .order-table thead th {
        background-color: #fafafa;
        color: var(--text-dark);
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--primary-orange);
        padding: 1rem 0.8rem;
    }
    .order-table tbody tr {
        transition: all 0.2s;
        border-bottom: 1px solid var(--border-light);
    }
    .order-table tbody tr:hover {
        background-color: rgba(245, 124, 0, 0.05);
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .order-table td {
        padding: 0.9rem 0.8rem;
        vertical-align: middle;
        color: var(--text-dark);
    }

    /* Badge trạng thái đơn hàng */
    .status-badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        min-width: 90px;
    }
    .status-pending {
        background-color: #fff3e0;
        color: #F57C00;
    }
    .status-confirmed {
        background-color: #e3f2fd;
        color: #1976d2;
    }
    .status-shipped {
        background-color: #e8f5e9;
        color: #2e7d32;
    }
    .status-delivered {
        background-color: #2e7d32;
        color: white;
    }
    .status-cancelled {
        background-color: #ffebee;
        color: #c62828;
    }

    /* Badge thanh toán */
    .payment-badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .payment-paid {
        background-color: #e8f5e9;
        color: #2e7d32;
    }
    .payment-unpaid {
        background-color: #ffebee;
        color: #c62828;
    }

    /* Form cập nhật trạng thái */
    .status-form {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .status-select {
        padding: 0.35rem 0.8rem;
        border-radius: 2rem;
        border: 1px solid var(--border-light);
        font-size: 0.8rem;
        background-color: white;
        color: var(--text-dark);
        transition: all 0.2s;
    }
    .status-select:focus {
        border-color: var(--primary-orange);
        outline: none;
        box-shadow: 0 0 0 2px rgba(245,124,0,0.2);
    }
    .btn-update {
        background-color: #6c757d;
        border: none;
        border-radius: 2rem;
        padding: 0.3rem 1rem;
        font-size: 0.75rem;
        color: white;
        transition: all 0.2s;
    }
    .btn-update:hover {
        background-color: #5a6268;
        transform: translateY(-1px);
    }
    .btn-paid {
        background-color: var(--primary-orange);
        border: none;
        border-radius: 2rem;
        padding: 0.3rem 1rem;
        font-size: 0.75rem;
        color: white;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
    }
    .btn-paid:hover {
        background-color: #e66a00;
        transform: translateY(-1px);
        color: white;
    }

    /* Phân trang */
    .pagination-custom {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
        gap: 0.3rem;
        flex-wrap: wrap;
    }
    .pagination-custom .page-link-custom {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: white;
        color: var(--text-dark);
        border-radius: 2rem;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin: 0 2px;
    }
    .pagination-custom .page-link-custom:hover {
        background-color: var(--primary-orange);
        color: white;
        transform: translateY(-2px);
    }
    .pagination-custom .active .page-link-custom {
        background-color: var(--primary-orange);
        color: white;
        box-shadow: 0 2px 6px rgba(245,124,0,0.3);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .order-table, .order-table tbody, .order-table tr, .order-table td {
            display: block;
        }
        .order-table thead {
            display: none;
        }
        .order-table tr {
            margin-bottom: 1rem;
            border: 1px solid var(--border-light);
            border-radius: 1rem;
            padding: 0.5rem;
        }
        .order-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 1rem;
            border-bottom: 1px dashed #eee;
        }
        .order-table td:before {
            content: attr(data-label);
            font-weight: bold;
            width: 40%;
            color: var(--primary-orange);
        }
        .status-form {
            justify-content: flex-end;
        }
    }
</style>

<div class="container-fluid px-4">
    <div class="page-header">
        <h2><i class="fas fa-shopping-cart me-2" style="color: #F57C00;"></i> Quản lý đơn hàng</h2>
        <div>
            <span class="badge bg-secondary">Tổng số: <?= $total ?> đơn</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="order-table">
            <thead>
                <tr>
                    <th>ID</th><th>Xe</th><th>Người mua</th><th>Người bán</th><th>Tổng tiền</th><th>Trạng thái</th><th>Thanh toán</th><th>Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td data-label="ID">#<?= $o['id'] ?></td>
                        <td data-label="Xe"><?= htmlspecialchars($o['bike_title']) ?></td>
                        <td data-label="Người mua"><?= htmlspecialchars($o['buyer_name']) ?></td>
                        <td data-label="Người bán"><?= htmlspecialchars($o['seller_name']) ?></td>
                        <td data-label="Tổng tiền" class="fw-bold"><?= number_format($o['total_price'],0,',','.') ?>đ</td>
                        <td data-label="Trạng thái">
                            <?php
                            $statusClass = '';
                            switch ($o['status']) {
                                case 'pending': $statusClass = 'status-pending'; $statusText = 'Chờ xác nhận'; break;
                                case 'confirmed': $statusClass = 'status-confirmed'; $statusText = 'Đã xác nhận'; break;
                                case 'shipped': $statusClass = 'status-shipped'; $statusText = 'Đang giao'; break;
                                case 'delivered': $statusClass = 'status-delivered'; $statusText = 'Đã giao'; break;
                                case 'cancelled': $statusClass = 'status-cancelled'; $statusText = 'Đã hủy'; break;
                                default: $statusClass = 'status-pending'; $statusText = $o['status'];
                            }
                            ?>
                            <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                        </td>
                        <td data-label="Thanh toán">
                            <span class="payment-badge <?= $o['payment_status'] == 'paid' ? 'payment-paid' : 'payment-unpaid' ?>">
                                <?= $o['payment_status'] == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
                            </span>
                        </td>
                        <td data-label="Hành động">
                            <div class="status-form">
                                <form method="post" action="update_status.php" style="display:inline-block">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <select name="status" class="status-select">
                                        <option value="pending" <?= $o['status']=='pending' ? 'selected' : '' ?>>Chờ xác nhận</option>
                                        <option value="confirmed" <?= $o['status']=='confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                        <option value="shipped" <?= $o['status']=='shipped' ? 'selected' : '' ?>>Đang giao</option>
                                        <option value="delivered" <?= $o['status']=='delivered' ? 'selected' : '' ?>>Đã giao</option>
                                        <option value="cancelled" <?= $o['status']=='cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                    </select>
                                    <button type="submit" class="btn-update"><i class="fas fa-sync-alt"></i> Cập nhật</button>
                                </form>
                                <?php if ($o['payment_status'] != 'paid'): ?>
                                    <a href="?mark_paid=<?= $o['id'] ?>&page=<?= $page ?>" class="btn-paid" onclick="return confirm('Xác nhận đã thanh toán cho đơn hàng #<?= $o['id'] ?>?')">
                                        <i class="fas fa-check-circle"></i> Đã thanh toán
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small"><i class="fas fa-check"></i> Đã TT</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center py-4">Chưa có đơn hàng nào</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="pagination-custom">
        <?php for($i = 1; $i <= $pages; $i++): ?>
            <div class="<?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link-custom" href="?page=<?= $i ?>"><?= $i ?></a>
            </div>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../inc/footer.php'; ?>