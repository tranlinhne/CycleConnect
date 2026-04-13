<?php
require_once '../inc/auth.php';
require_once '../inc/header.php';

$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$stmt = $pdo->prepare("SELECT b.*, c.name as cat_name, br.name as brand_name FROM bikes b 
                       LEFT JOIN categories c ON b.category_id = c.id 
                       LEFT JOIN brands br ON b.brand_id = br.id
                       ORDER BY b.id DESC LIMIT ? OFFSET ?");
$stmt->bindParam(1, $limit, PDO::PARAM_INT);
$stmt->bindParam(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$bikes = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM bikes")->fetchColumn();
$pages = ceil($total / $limit);
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
    .btn-orange {
        background-color: var(--primary-orange);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.5rem 1.2rem;
        border-radius: 2rem;
        transition: all 0.2s ease;
        box-shadow: 0 2px 5px rgba(245, 124, 0, 0.3);
    }
    .btn-orange:hover {
        background-color: #e66a00;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 124, 0, 0.4);
        color: white;
    }
    .btn-outline-orange {
        border: 1px solid var(--primary-orange);
        background: transparent;
        color: var(--primary-orange);
        border-radius: 2rem;
        padding: 0.25rem 1rem;
        transition: all 0.2s;
    }
    .btn-outline-orange:hover {
        background-color: var(--primary-orange);
        color: white;
        transform: translateY(-1px);
    }
    .btn-danger-custom {
        background-color: #dc3545;
        border: none;
        border-radius: 2rem;
        padding: 0.25rem 1rem;
        color: white;
        transition: all 0.2s;
    }
    .btn-danger-custom:hover {
        background-color: #c82333;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(220,53,69,0.3);
    }

    /* Bảng sản phẩm */
    .product-table {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .product-table thead th {
        background-color: #fafafa;
        color: var(--text-dark);
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--primary-orange);
        padding: 1rem;
    }
    .product-table tbody tr {
        transition: all 0.2s;
        border-bottom: 1px solid var(--border-light);
    }
    .product-table tbody tr:hover {
        background-color: rgba(245, 124, 0, 0.05);
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .product-table td {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        color: var(--text-dark);
    }
    /* Badge trạng thái */
    .status-badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        background-color: #e8f5e9;
        color: #2e7d32;
    }
    .status-badge.available {
        background-color: #e8f5e9;
        color: #2e7d32;
    }
    .status-badge.sold {
        background-color: #ffebee;
        color: #c62828;
    }
    .status-badge.default {
        background-color: #f5f5f5;
        color: #616161;
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
    @media (max-width: 768px) {
        .product-table, .product-table tbody, .product-table tr, .product-table td {
            display: block;
        }
        .product-table thead {
            display: none;
        }
        .product-table tr {
            margin-bottom: 1rem;
            border: 1px solid var(--border-light);
            border-radius: 1rem;
            padding: 0.5rem;
        }
        .product-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 1rem;
            border-bottom: 1px dashed #eee;
        }
        .product-table td:before {
            content: attr(data-label);
            font-weight: bold;
            width: 40%;
            color: var(--primary-orange);
        }
        .page-header {
            flex-direction: column;
            align-items: stretch;
            text-align: center;
        }
    }
</style>

<div class="container-fluid px-4">
    <div class="page-header">
        <h2><i class="fas fa-bicycle me-2" style="color: #F57C00;"></i> Quản lý sản phẩm</h2>
        <a href="add.php" class="btn btn-orange"><i class="fas fa-plus-circle me-1"></i> Thêm xe mới</a>
    </div>

    <div class="table-responsive">
        <table class="product-table">
            <thead>
                <tr>
                    <th>ID</th><th>Tiêu đề</th><th>Danh mục</th><th>Thương hiệu</th><th>Giá</th><th>Trạng thái</th><th>Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bikes as $b): ?>
                <tr>
                    <td data-label="ID"><?= $b['id'] ?></td>
                    <td data-label="Tiêu đề"><?= htmlspecialchars($b['title']) ?></td>
                    <td data-label="Danh mục"><?= htmlspecialchars($b['cat_name']) ?></td>
                    <td data-label="Thương hiệu"><?= htmlspecialchars($b['brand_name']) ?></td>
                    <td data-label="Giá"><?= number_format($b['price'],0,',','.') ?>đ</td>
                    <td data-label="Trạng thái">
                        <span class="status-badge <?= ($b['status'] == 'available') ? 'available' : (($b['status'] == 'sold') ? 'sold' : 'default') ?>">
                            <?= ucfirst($b['status'] ?? 'Chưa cập nhật') ?>
                        </span>
                    </td>
                    <td data-label="Hành động">
                        <a href="edit.php?id=<?= $b['id'] ?>" class="btn btn-outline-orange btn-sm me-1">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="delete.php?id=<?= $b['id'] ?>" class="btn btn-danger-custom btn-sm" onclick="return confirm('Xóa xe này?')">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
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