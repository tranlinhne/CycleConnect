<?php
require_once '../inc/auth.php';

$user = null;
$message = "";
$messageType = "error";

$id = null;

// Nếu bấm từ All Users -> View
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];
}

// Nếu không có id thì lấy admin đầu tiên làm profile mặc định
if ($id === null) {
    $defaultStmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1");
    $defaultStmt->execute();
    $user = $defaultStmt->fetch();
    if (!$user) {
        $message = "Không có tài khoản admin để hiển thị profile.";
    }
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        $message = "User không tồn tại!";
    }
}

function getRoleLabel($role) {
    switch ($role) {
        case 'admin': return 'Admin';
        case 'buyer': return 'Buyer';
        case 'seller': return 'Seller';
        default: return ucfirst($role);
    }
}
?>

<style>
    .profile-page {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(0,0,0,.05);
        overflow: hidden;
        max-width: 1100px;
    }

    .profile-page-header {
        padding: 26px 28px 10px;
        border-bottom: 1px solid #f1f5f9;
    }

    .profile-page-header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #334155;
    }

    .profile-page-body {
        padding: 26px 28px 30px;
        background: #fbfcfe;
    }

    .message {
        padding: 14px 16px;
        border-radius: 12px;
        font-size: 14px;
        border: 1px solid transparent;
    }

    .message.error {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    .profile-card {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 18px;
        overflow: hidden;
    }

    .profile-top {
        display: grid;
        grid-template-columns: 220px 1fr 260px;
        gap: 24px;
        padding: 28px;
        align-items: center;
        background: linear-gradient(180deg, #fbfdff 0%, #ffffff 100%);
    }

    .avatar-area {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .avatar-circle {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background: linear-gradient(135deg, #60a5fa, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 52px;
        font-weight: 700;
        box-shadow: 0 12px 24px rgba(99,102,241,.18);
    }

    .main-info h3 {
        margin: 0 0 8px;
        font-size: 34px;
        color: #1f2937;
        font-weight: 700;
    }

    .main-sub {
        font-size: 15px;
        color: #0ea5e9;
        font-weight: 600;
        margin-bottom: 14px;
    }

    .info-list {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 10px 16px;
        font-size: 15px;
    }

    .info-list .label {
        color: #475569;
    }

    .info-list .value {
        color: #111827;
        font-weight: 500;
        word-break: break-word;
    }

    .summary-box {
        text-align: center;
        padding: 12px 10px;
    }

    .summary-title {
        font-size: 30px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .summary-title.active {
        color: #16a34a;
    }

    .summary-title.inactive {
        color: #6b7280;
    }

    .summary-line {
        color: #374151;
        font-size: 15px;
        margin-bottom: 10px;
    }

    .summary-line strong {
        color: #111827;
    }

    .summary-date {
        color: #dc2626;
        font-weight: 700;
        margin-top: 10px;
        font-size: 16px;
    }

    .badge-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 18px;
    }

    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.5;
    }

    .role-admin {
        background: #fee2e2;
        color: #b91c1c;
    }

    .role-buyer {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .role-seller {
        background: #fef3c7;
        color: #b45309;
    }

    .status-active {
        background: #dcfce7;
        color: #15803d;
    }

    .status-inactive {
        background: #e5e7eb;
        color: #475569;
    }

    .profile-tabs {
        display: flex;
        border-top: 1px solid #eef2f7;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        overflow-x: auto;
    }

    .profile-tab {
        padding: 16px 28px;
        font-size: 15px;
        color: #475569;
        white-space: nowrap;
        border-bottom: 3px solid transparent;
    }

    .profile-tab.active {
        color: #0ea5e9;
        font-weight: 700;
        border-bottom-color: #38bdf8;
        background: #f8fbff;
    }

    .profile-content {
        padding: 0;
        background: #fff;
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
    }

    .detail-table th,
    .detail-table td {
        padding: 16px 18px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 15px;
        text-align: left;
        vertical-align: middle;
    }

    .detail-table th {
        width: 220px;
        color: #64748b;
        font-weight: 600;
        background: #fcfdff;
    }

    .detail-table td {
        color: #111827;
        font-weight: 500;
    }

    .profile-actions {
        padding: 22px 28px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        background: #fff;
        border-top: 1px solid #f1f5f9;
    }

    .btn {
        min-height: 40px;
        padding: 10px 16px;
        border: 1px solid #2563eb;
        background: #2563eb;
        color: #fff;
        border-radius: 10px;
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

    @media (max-width: 980px) {
        .profile-top {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .main-info h3 {
            font-size: 28px;
        }

        .info-list {
            grid-template-columns: 1fr;
            text-align: left;
        }

        .detail-table th,
        .detail-table td {
            display: block;
            width: 100%;
        }

        .detail-table th {
            padding-bottom: 8px;
            border-bottom: none;
        }

        .detail-table td {
            padding-top: 0;
        }
    }
</style>

<div class="profile-page">
    <div class="profile-page-header">
        <h2>User Profile</h2>
    </div>

    <div class="profile-page-body">
        <?php if (!empty($message)): ?>
            <div class="message <?= htmlspecialchars($messageType) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php elseif ($user): ?>
            <div class="profile-card">
                <div class="profile-top">
                    <div class="avatar-area">
                        <div class="avatar-circle">
                            <?= strtoupper(mb_substr($user['full_name'], 0, 1, 'UTF-8')) ?>
                        </div>
                    </div>

                    <div class="main-info">
                        <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                        <div class="main-sub">CycleMarket User Profile</div>

                        <div class="info-list">
                            <div class="label">Email</div>
                            <div class="value"><?= htmlspecialchars($user['email']) ?></div>

                            <div class="label">Role</div>
                            <div class="value"><?= htmlspecialchars(getRoleLabel($user['role'])) ?></div>

                            <div class="label">User ID</div>
                            <div class="value"><?= (int)$user['id'] ?></div>

                            <div class="label">Account Status</div>
                            <div class="value">
                                <?= (!empty($user['active']) && $user['active'] == 1) ? 'Active' : 'Inactive' ?>
                            </div>
                        </div>

                        <div class="badge-row">
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge role-admin">Admin</span>
                            <?php elseif ($user['role'] === 'buyer'): ?>
                                <span class="badge role-buyer">Buyer</span>
                            <?php elseif ($user['role'] === 'seller'): ?>
                                <span class="badge role-seller">Seller</span>
                            <?php endif; ?>

                            <?php if (!empty($user['active']) && $user['active'] == 1): ?>
                                <span class="badge status-active">Active</span>
                            <?php else: ?>
                                <span class="badge status-inactive">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="summary-box">
                        <div class="summary-title <?= (!empty($user['active']) && $user['active'] == 1) ? 'active' : 'inactive' ?>">
                            <?= (!empty($user['active']) && $user['active'] == 1) ? 'Đang hoạt động' : 'Không hoạt động' ?>
                        </div>

                        <div class="summary-line"><strong>Role:</strong> <?= htmlspecialchars(getRoleLabel($user['role'])) ?></div>
                        <div class="summary-line"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
                        <div class="summary-line"><strong>ID:</strong> #<?= (int)$user['id'] ?></div>

                        <div class="summary-date"><?= date('d/m/Y') ?></div>
                    </div>
                </div>

                <div class="profile-tabs">
                    <div class="profile-tab active">Overview</div>
                    <div class="profile-tab">Account Info</div>
                    <div class="profile-tab">Activity</div>
                    <div class="profile-tab">Attachments</div>
                </div>

                <div class="profile-content">
                    <table class="detail-table">
                        <tr>
                            <th>Full Name</th>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Email Address</th>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td><?= htmlspecialchars(getRoleLabel($user['role'])) ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><?= (!empty($user['active']) && $user['active'] == 1) ? 'Active' : 'Inactive' ?></td>
                        </tr>
                        <tr>
                            <th>User ID</th>
                            <td>#<?= (int)$user['id'] ?></td>
                        </tr>
                        <tr>
                            <th>Account Type</th>
                            <td>CycleMarket Member</td>
                        </tr>
                    </table>
                </div>

                <div class="profile-actions">
                    <a href="index.php?page=edit_user&id=<?= (int)$user['id'] ?>" class="btn">Edit User</a>
                    <a href="index.php?page=all_users" class="btn btn-secondary">All Users</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>