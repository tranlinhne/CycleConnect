<?php
require_once '../inc/auth.php';
require_once '../inc/header.php';

// Phân trang
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Đếm tổng số người dùng
$total = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pages = ceil($total / $limit);

// Lấy danh sách người dùng có phân trang
$stmt = $pdo->prepare("SELECT id, first_name, last_name, email, username, phone, role, active, created_at 
                       FROM users 
                       ORDER BY id DESC 
                       LIMIT ? OFFSET ?");
$stmt->bindParam(1, $limit, PDO::PARAM_INT);
$stmt->bindParam(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();
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

    /* Bảng người dùng */
    .user-table {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .user-table thead th {
        background-color: #fafafa;
        color: var(--text-dark);
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--primary-orange);
        padding: 1rem;
    }
    .user-table tbody tr {
        transition: all 0.2s;
        border-bottom: 1px solid var(--border-light);
    }
    .user-table tbody tr:hover {
        background-color: rgba(245, 124, 0, 0.05);
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .user-table td {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        color: var(--text-dark);
    }

    /* Badge vai trò & trạng thái */
    .role-badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .role-admin {
        background-color: #ffecb3;
        color: #ff8f00;
    }
    .role-user {
        background-color: #e3f2fd;
        color: #1976d2;
    }
    .status-badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-active {
        background-color: #e8f5e9;
        color: #2e7d32;
    }
    .status-inactive {
        background-color: #ffebee;
        color: #c62828;
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
        .user-table, .user-table tbody, .user-table tr, .user-table td {
            display: block;
        }
        .user-table thead {
            display: none;
        }
        .user-table tr {
            margin-bottom: 1rem;
            border: 1px solid var(--border-light);
            border-radius: 1rem;
            padding: 0.5rem;
        }
        .user-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 1rem;
            border-bottom: 1px dashed #eee;
        }
        .user-table td:before {
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
        <h2><i class="fas fa-users me-2" style="color: #F57C00;"></i> Quản lý người dùng</h2>
        <a href="add.php" class="btn btn-orange"><i class="fas fa-user-plus me-1"></i> Thêm người dùng</a>
    </div>

    <div class="table-responsive">
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th><th>Họ tên</th><th>Email</th><th>Username</th><th>Vai trò</th><th>Trạng thái</th><th>Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td data-label="ID"><?= $u['id'] ?></td>
                    <td data-label="Họ tên"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></td>
                    <td data-label="Email"><?= htmlspecialchars($u['email']) ?></td>
                    <td data-label="Username"><?= htmlspecialchars($u['username']) ?></td>
                    <td data-label="Vai trò">
                        <span class="role-badge <?= ($u['role'] == 'admin') ? 'role-admin' : 'role-user' ?>">
                            <?= ($u['role'] == 'admin') ? 'Quản trị viên' : 'Khách hàng' ?>
                        </span>
                    </td>
                    <td data-label="Trạng thái">
                        <span class="status-badge <?= $u['active'] ? 'status-active' : 'status-inactive' ?>">
                            <?= $u['active'] ? 'Hoạt động' : 'Khóa' ?>
                        </span>
                    </td>
                    <td data-label="Hành động">
                        <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-outline-orange btn-sm me-1">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="delete.php?id=<?= $u['id'] ?>" class="btn btn-danger-custom btn-sm" onclick="return confirm('Xóa người dùng này?')">
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