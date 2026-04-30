<?php
require_once '../inc/auth.php';
redirectIfNotSuperAdmin(); // Chỉ admin cấp cao mới được duyệt báo cáo

// Xử lý duyệt/từ chối
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $report_id = (int)$_POST['report_id'];
    $status = $_POST['status']; // 'approved' or 'rejected'
    
    if (in_array($status, ['approved', 'rejected'])) {
        $stmt = $pdo->prepare("UPDATE revenue_reports SET status = ?, reviewed_by = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $_SESSION['user_id'], $report_id]);
        $_SESSION['flash_msg'] = "Cập nhật trạng thái báo cáo thành công!";
    }
    header("Location: index.php");
    exit;
}

// Lọc theo trạng thái
$filter = $_GET['filter'] ?? 'pending';
$whereClaus = "WHERE r.status = ?";
$params = [$filter];

if ($filter === 'all') {
    $whereClaus = "";
    $params = [];
}

// Phân trang
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalSql = "SELECT COUNT(*) FROM revenue_reports r $whereClaus";
$stmt = $pdo->prepare($totalSql);
$stmt->execute($params);
$total = $stmt->fetchColumn();
$pages = ceil($total / $limit);

$sql = "SELECT r.*, m.full_name as manager_name, m.email as manager_email, a.full_name as admin_name 
        FROM revenue_reports r
        LEFT JOIN users m ON r.manager_id = m.id
        LEFT JOIN users a ON r.reviewed_by = a.id
        $whereClaus
        ORDER BY r.created_at DESC
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

require_once '../inc/header.php';
?>

<style>
    :root {
        --primary-orange: #F57C00;
        --bg-gray: #F5F5F5;
        --text-dark: #263238;
        --border-light: #e0e0e0;
        --card-shadow: 0 8px 16px rgba(0,0,0,0.05);
    }
    body { background-color: var(--bg-gray); color: var(--text-dark); }
    .page-header {
        background: white; border-radius: 1rem; padding: 1.2rem 1.5rem; margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow); display: flex; justify-content: space-between; align-items: center;
    }
    .page-header h2 {
        font-weight: 700; font-size: 1.6rem; margin: 0; color: var(--text-dark);
        border-left: 5px solid var(--primary-orange); padding-left: 1rem;
    }
    .filter-group select {
        border-radius: 2rem; border: 1px solid var(--border-light); padding: 0.5rem 1rem; outline: none;
    }
    .report-card {
        background: white; border-radius: 1.2rem; margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow); overflow: hidden;
    }
    .card-head {
        background: #fefaf5; padding: 1rem 1.5rem; border-bottom: 1px solid #ffe0b5;
        display: flex; justify-content: space-between; align-items: center;
    }
    .card-body-custom { padding: 1.5rem; }
    .badge-status { padding: 0.4rem 1rem; border-radius: 2rem; font-weight: 600; font-size: 0.85rem; }
    .badge-pending { background: #fff8e1; color: #f57c00; }
    .badge-approved { background: #e8f5e9; color: #2e7d32; }
    .badge-rejected { background: #ffebee; color: #c62828; }
    
    .data-row { display: flex; align-items: center; gap: 2rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .data-item { flex: 1; min-width: 200px; background: #f8f9fc; padding: 1rem; border-radius: 0.8rem; border-left: 3px solid var(--primary-orange); }
    .data-item.fee { border-left-color: #673AB7; }
    .data-label { color: #6c757d; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.3rem; }
    .data-value { font-size: 1.2rem; font-weight: 700; color: var(--text-dark); }
    
    .note-box { background: #f1f3f4; padding: 1rem; border-radius: 0.6rem; font-style: italic; color: #444; }
    
    .actions { display: flex; gap: 0.8rem; margin-top: 1.5rem; border-top: 1px dashed var(--border-light); padding-top: 1.5rem; }
    .btn-action { padding: 0.5rem 1.5rem; border-radius: 2rem; font-weight: 600; border: none; cursor: pointer; transition: 0.2s; }
    .btn-approve { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9;}
    .btn-approve:hover { background: #2e7d32; color: white; }
    .btn-reject { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2;}
    .btn-reject:hover { background: #c62828; color: white; }
</style>

<div class="container-fluid px-4">
    <div class="page-header">
        <h2><i class="fas fa-file-invoice-dollar me-2" style="color: #F57C00;"></i> Duyệt Báo Cáo Doanh Thu</h2>
        <div class="filter-group">
            <select onchange="location.href='?filter='+this.value">
                <option value="all" <?= $filter=='all'?'selected':'' ?>>Tất cả trạng thái</option>
                <option value="pending" <?= $filter=='pending'?'selected':'' ?>>Đang chờ duyệt</option>
                <option value="approved" <?= $filter=='approved'?'selected':'' ?>>Đã duyệt</option>
                <option value="rejected" <?= $filter=='rejected'?'selected':'' ?>>Đã từ chối</option>
            </select>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 1rem;">
            <?= $_SESSION['flash_msg']; unset($_SESSION['flash_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($reports)): ?>
        <div class="alert alert-light text-center p-5 rounded-4 shadow-sm" style="background:white;">
            <i class="fas fa-inbox fa-3x mb-3" style="color:#ccc;"></i>
            <p>Không có báo cáo nào ở trạng thái này.</p>
        </div>
    <?php else: ?>
        <?php foreach ($reports as $r): ?>
        <div class="report-card">
            <div class="card-head">
                <div>
                    <strong>Mã báo cáo: #<?= $r['id'] ?></strong>
                    <span class="ms-3 text-muted"><i class="fas fa-calendar-alt"></i> Kỳ: Tháng <?= htmlspecialchars($r['report_period']) ?></span>
                </div>
                <div>
                    <?php if ($r['status'] == 'pending'): ?>
                        <span class="badge-status badge-pending"><i class="fas fa-hourglass-half"></i> Chờ duyệt</span>
                    <?php elseif ($r['status'] == 'approved'): ?>
                        <span class="badge-status badge-approved"><i class="fas fa-check-circle"></i> Đã duyệt</span>
                    <?php else: ?>
                        <span class="badge-status badge-rejected"><i class="fas fa-times-circle"></i> Từ chối</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-body-custom">
                <div class="mb-3 text-muted" style="font-size: 0.9rem;">
                    <i class="fas fa-user-tie text-orange"></i> Người gửi: <strong><?= htmlspecialchars($r['manager_name'] ?: 'Nhân viên') ?></strong> (<?= htmlspecialchars($r['manager_email']) ?>) 
                    - Lúc: <?= date('H:i d/m/Y', strtotime($r['created_at'])) ?>
                </div>
                
                <div class="data-row">
                    <div class="data-item">
                        <div class="data-label">Tổng doanh thu chốt</div>
                        <div class="data-value"><?= number_format($r['total_revenue'], 0, ',', '.') ?> đ</div>
                    </div>
                    <div class="data-item fee">
                        <div class="data-label">Chiết khấu / Phí thu</div>
                        <div class="data-value" style="color:#673AB7;"><?= number_format($r['platform_fee'], 0, ',', '.') ?> đ</div>
                    </div>
                </div>

                <?php if (!empty($r['notes'])): ?>
                <div class="note-box">
                    <strong><i class="fas fa-comment"></i> Ghi chú từ Manager:</strong><br>
                    <?= nl2br(htmlspecialchars($r['notes'])) ?>
                </div>
                <?php endif; ?>

                <div class="actions">
                    <?php if ($r['status'] != 'approved'): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Bạn xác nhận DUYỆT báo cáo này hợp lệ?');">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="btn-action btn-approve"><i class="fas fa-check"></i> Duyệt báo cáo</button>
                    </form>
                    <?php endif; ?>

                    <?php if ($r['status'] != 'rejected'): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Bạn muốn TỪ CHỐI báo cáo này?');">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn-action btn-reject"><i class="fas fa-times"></i> Từ chối</button>
                    </form>
                    <?php endif; ?>
                </div>

                <?php if ($r['status'] != 'pending'): ?>
                <div class="mt-3 text-muted" style="font-size: 0.85rem; border-top: 1px dashed #ddd; padding-top: 1rem;">
                    Người cập nhật lần cuối: <strong><?= htmlspecialchars($r['admin_name'] ?: 'Admin') ?></strong> vào lúc <?= date('H:i d/m/Y', strtotime($r['updated_at'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if ($pages > 1): ?>
        <ul class="pagination justify-content-center">
            <?php for($i=1; $i<=$pages; $i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="?filter=<?= $filter ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once '../inc/footer.php'; ?>

