<?php include("../config.php"); ?>

<?php
$keyword = trim($_GET['keyword'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$bulkAction = $_POST['bulk_action'] ?? '';
$selectedIds = $_POST['selected_ids'] ?? [];

$message = "";
$messageType = "";

/* =========================
   DELETE SINGLE USER
========================= */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];

    $deleteStmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($deleteStmt, "i", $deleteId);

    if (mysqli_stmt_execute($deleteStmt)) {
        $message = "Xóa user thành công!";
        $messageType = "success";
    } else {
        $message = "Xóa user thất bại!";
        $messageType = "error";
    }
}

/* =========================
   BULK ACTION
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($bulkAction) && !empty($selectedIds) && is_array($selectedIds)) {
    $cleanIds = [];

    foreach ($selectedIds as $id) {
        if (is_numeric($id)) {
            $cleanIds[] = (int)$id;
        }
    }

    if (!empty($cleanIds)) {
        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $types = str_repeat('i', count($cleanIds));

        if ($bulkAction === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id IN ($placeholders)");
        } elseif ($bulkAction === 'activate') {
            $stmt = mysqli_prepare($conn, "UPDATE users SET active = 1 WHERE id IN ($placeholders)");
        } elseif ($bulkAction === 'deactivate') {
            $stmt = mysqli_prepare($conn, "UPDATE users SET active = 0 WHERE id IN ($placeholders)");
        } else {
            $stmt = null;
        }

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$cleanIds);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Thực hiện bulk action thành công!";
                $messageType = "success";
            } else {
                $message = "Bulk action thất bại!";
                $messageType = "error";
            }
        }
    }
}

/* =========================
   COUNT FILTER LINKS
========================= */
$countAll = 0;
$countAdmin = 0;
$countUser = 0;
$countActive = 0;
$countInactive = 0;

$countResult = mysqli_query($conn, "
    SELECT
        COUNT(*) AS total_all,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS total_admin,
        SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) AS total_user,
        SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) AS total_active,
        SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) AS total_inactive
    FROM users
");

if ($countRow = mysqli_fetch_assoc($countResult)) {
    $countAll = (int)($countRow['total_all'] ?? 0);
    $countAdmin = (int)($countRow['total_admin'] ?? 0);
    $countUser = (int)($countRow['total_user'] ?? 0);
    $countActive = (int)($countRow['total_active'] ?? 0);
    $countInactive = (int)($countRow['total_inactive'] ?? 0);
}

/* =========================
   BUILD FILTER QUERY
========================= */
$where = [];
$params = [];
$types = "";

if ($keyword !== '') {
    $where[] = "(name LIKE ? OR email LIKE ?)";
    $searchValue = "%" . $keyword . "%";
    $params[] = $searchValue;
    $params[] = $searchValue;
    $types .= "ss";
}

if ($roleFilter !== '' && in_array($roleFilter, ['admin', 'user'])) {
    $where[] = "role = ?";
    $params[] = $roleFilter;
    $types .= "s";
}

if ($statusFilter !== '' && in_array($statusFilter, ['1', '0'], true)) {
    $where[] = "active = ?";
    $params[] = (int)$statusFilter;
    $types .= "i";
}

$sql = "SELECT * FROM users";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

function buildUserUrl($extra = []) {
    $params = ['page' => 'users'];

    if (isset($_GET['keyword']) && $_GET['keyword'] !== '') {
        $params['keyword'] = $_GET['keyword'];
    }

    if (isset($_GET['role']) && $_GET['role'] !== '') {
        $params['role'] = $_GET['role'];
    }

    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $params['status'] = $_GET['status'];
    }

    $params = array_merge($params, $extra);

    foreach ($params as $key => $value) {
        if ($value === '') {
            unset($params[$key]);
        }
    }

    return 'index.php?' . http_build_query($params);
}
?>

<style>
        .users-wrap {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
    }

    .users-header {
        margin-bottom: 16px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 6px;
    }

    .users-header h2 {
        margin: 0;
        font-size: 30px;
        font-weight: 700;
        color: #1f2937;
    }

    .users-header p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .filter-links {
        margin: 14px 0 18px;
        font-size: 14px;
        color: #6b7280;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .filter-links a {
        color: #2563eb;
        text-decoration: none;
        transition: 0.2s;
    }

    .filter-links a:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    .filter-links a.active {
        color: #111827;
        font-weight: 700;
    }

    .filter-links .sep {
        color: #9ca3af;
    }

    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 18px;
        padding: 14px;
        background: #f9fafb;
        border: 1px solid #eceff3;
        border-radius: 10px;
    }

    .toolbar-left,
    .toolbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .toolbar select,
    .toolbar input[type="text"] {
        min-height: 38px;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        font-size: 14px;
        color: #111827;
        transition: 0.2s;
    }

    .toolbar select:focus,
    .toolbar input[type="text"]:focus {
        outline: none;
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.18);
    }

    .toolbar input[type="text"] {
        width: 240px;
    }

    .btn {
        min-height: 38px;
        padding: 8px 14px;
        border: 1px solid #2563eb;
        background: #2563eb;
        color: #fff;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }

    .btn:hover {
        opacity: 0.95;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #ffffff;
        border-color: #d1d5db;
        color: #374151;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
    }

    .btn-add {
        background: #10b981;
        border-color: #10b981;
        color: #fff;
    }

    .btn-add:hover {
        background: #059669;
        border-color: #059669;
    }

    .btn-small {
        min-height: 30px;
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 7px;
    }

    .message {
        padding: 13px 14px;
        border-radius: 10px;
        margin-bottom: 16px;
        font-size: 14px;
        border: 1px solid transparent;
    }

    .message.success {
        background: #ecfdf3;
        color: #166534;
        border-color: #bbf7d0;
    }

    .message.error {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    .table-wrap {
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }

    table.users-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
    }

    .users-table th,
    .users-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #eef0f3;
        text-align: left;
        font-size: 14px;
        vertical-align: top;
    }

    .users-table th {
        background: #f9fafb;
        color: #4b5563;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .users-table tr:hover td {
        background: #fafcff;
    }

    .username-cell strong {
        color: #1d4ed8;
        font-size: 15px;
        font-weight: 700;
    }

    .row-actions {
        margin-top: 7px;
        font-size: 12px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .row-actions a {
        text-decoration: none;
        font-weight: 500;
    }

    .row-actions a:nth-child(1) {
        color: #6b7280;
    }

    .row-actions a:nth-child(2) {
        color: #2563eb;
    }

    .row-actions a:nth-child(3) {
        color: #dc2626;
    }

    .row-actions a:hover {
        text-decoration: underline;
    }

    .role-badge,
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.5;
    }

    .role-admin {
        background: #fee2e2;
        color: #b91c1c;
    }

    .role-user {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .status-active {
        background: #dcfce7;
        color: #15803d;
    }

    .status-inactive {
        background: #f3f4f6;
        color: #6b7280;
    }

    .empty-row {
        text-align: center;
        color: #6b7280;
        padding: 28px 10px;
    }

    .bottom-actions {
        margin-top: 14px;
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .users-footer {
        margin-top: 14px;
        font-size: 13px;
        color: #6b7280;
    }

    @media (max-width: 768px) {
        .users-wrap {
            padding: 16px;
        }

        .header-left {
            flex-wrap: wrap;
            align-items: flex-start;
        }

        .users-header h2 {
            font-size: 24px;
        }

        .toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .toolbar-left,
        .toolbar-right {
            width: 100%;
        }

        .toolbar input[type="text"] {
            width: 100%;
        }

        .bottom-actions {
            flex-wrap: wrap;
        }
    }
</style>

<div class="users-wrap">
    <div class="users-header">
    <div class="header-left">
        <h2>Users</h2>
        <a href="index.php?page=add_user" class="btn btn-add btn-small">Add User</a>
    </div>
    <p>Quản lý danh sách tài khoản trong hệ thống CycleMarket</p>
</div>

    <div class="filter-links">
        <a href="index.php?page=users" class="<?= ($roleFilter === '' && $statusFilter === '') ? 'active' : '' ?>">All (<?= $countAll ?>)</a>
        <span class="sep">|</span>
        <a href="index.php?page=users&role=admin<?= $keyword !== '' ? '&keyword=' . urlencode($keyword) : '' ?>" class="<?= $roleFilter === 'admin' ? 'active' : '' ?>">Administrator (<?= $countAdmin ?>)</a>
        <span class="sep">|</span>
        <a href="index.php?page=users&role=user<?= $keyword !== '' ? '&keyword=' . urlencode($keyword) : '' ?>" class="<?= $roleFilter === 'user' ? 'active' : '' ?>">User (<?= $countUser ?>)</a>
        <span class="sep">|</span>
        <a href="index.php?page=users&status=1<?= $keyword !== '' ? '&keyword=' . urlencode($keyword) : '' ?>" class="<?= $statusFilter === '1' ? 'active' : '' ?>">Active (<?= $countActive ?>)</a>
        <span class="sep">|</span>
        <a href="index.php?page=users&status=0<?= $keyword !== '' ? '&keyword=' . urlencode($keyword) : '' ?>" class="<?= $statusFilter === '0' ? 'active' : '' ?>">Inactive (<?= $countInactive ?>)</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="message <?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="toolbar">
        <form method="POST" action="<?= htmlspecialchars(buildUserUrl()) ?>" class="toolbar-left">
            <select name="bulk_action">
                <option value="">Bulk actions</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete</option>
            </select>

            <button type="submit" class="btn btn-secondary">Apply</button>

            <select onchange="window.location.href=this.value">
                <option value="<?= htmlspecialchars(buildUserUrl(['role' => '', 'status' => $statusFilter])) ?>">All roles</option>
                <option value="<?= htmlspecialchars(buildUserUrl(['role' => 'admin', 'status' => $statusFilter])) ?>" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Administrator</option>
                <option value="<?= htmlspecialchars(buildUserUrl(['role' => 'user', 'status' => $statusFilter])) ?>" <?= $roleFilter === 'user' ? 'selected' : '' ?>>User</option>
            </select>

            <select onchange="window.location.href=this.value">
                <option value="<?= htmlspecialchars(buildUserUrl(['status' => '', 'role' => $roleFilter])) ?>">All status</option>
                <option value="<?= htmlspecialchars(buildUserUrl(['status' => '1', 'role' => $roleFilter])) ?>" <?= $statusFilter === '1' ? 'selected' : '' ?>>Active</option>
                <option value="<?= htmlspecialchars(buildUserUrl(['status' => '0', 'role' => $roleFilter])) ?>" <?= $statusFilter === '0' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </form>

        <form method="GET" action="index.php" class="toolbar-right">
            <input type="hidden" name="page" value="users">
            <?php if ($roleFilter !== ''): ?>
                <input type="hidden" name="role" value="<?= htmlspecialchars($roleFilter) ?>">
            <?php endif; ?>
            <?php if ($statusFilter !== ''): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <?php endif; ?>
            <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Search users...">
            <button type="submit" class="btn btn-secondary">Search</button>
        </form>
    </div>

    <form method="POST" action="<?= htmlspecialchars(buildUserUrl()) ?>">
        <div class="table-wrap">
            <table class="users-table">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" id="checkAll"></th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($u = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_ids[]" value="<?= (int)$u['id'] ?>">
                                </td>
                                <td class="username-cell">
                                    <strong><?= htmlspecialchars($u['name']) ?></strong>
                                    <div class="row-actions">
                                        <a href="index.php?page=profile&id=<?= (int)$u['id'] ?>">View</a> |
                                        <a href="index.php?page=edit_user&id=<?= (int)$u['id'] ?>">Edit</a> |
                                        <a href="<?= htmlspecialchars(buildUserUrl(['delete' => (int)$u['id']])) ?>" onclick="return confirm('Bạn có chắc muốn xóa user này không?')">Delete</a>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="role-badge role-admin">Administrator</span>
                                    <?php else: ?>
                                        <span class="role-badge role-user">User</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($u['active']) && $u['active'] == 1): ?>
                                        <span class="status-badge status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-row">Không có dữ liệu người dùng</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:12px;">
            <select name="bulk_action">
                <option value="">Bulk actions</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="btn btn-secondary">Apply</button>
        </div>
    </form>
</div>


<script>
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = checkAll.checked;
            });
        });
    }
</script>