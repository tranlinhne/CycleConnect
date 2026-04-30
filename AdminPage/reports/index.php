    <?php


require_once '../inc/auth.php';
redirectIfNotSuperAdmin();

ini_set('display_errors', 1);

// Xử lý các action từ form
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$report_id = isset($_POST['report_id']) ? (int)$_POST['report_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($report_id && $action && $_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'update_status':
            $new_status = $_POST['status'] ?? '';
            if (in_array($new_status, ['reviewed', 'rejected'])) {
                $stmt = $pdo->prepare("UPDATE reports SET status = ?, handled_by = ?, handled_at = NOW() WHERE id = ?");
                $stmt->execute([$new_status, $_SESSION['user_id'], $report_id]);
                $_SESSION['flash_msg'] = "Đã cập nhật trạng thái báo cáo.";
            }
            break;
        
        case 'hide_bike':
            $stmt = $pdo->prepare("SELECT bike_id FROM reports WHERE id = ?");
            $stmt->execute([$report_id]);
            $bike_id = $stmt->fetchColumn();
            if ($bike_id) {
                $update = $pdo->prepare("UPDATE bikes SET status = 'hidden' WHERE id = ?");
                $update->execute([$bike_id]);
                $_SESSION['flash_msg'] = "Đã ẩn tin đăng (ID xe: $bike_id).";
            }
            break;
        
        case 'warn_user':
            $stmt = $pdo->prepare("SELECT b.user_id FROM reports r JOIN bikes b ON r.bike_id = b.id WHERE r.id = ?");
            $stmt->execute([$report_id]);
            $seller_id = $stmt->fetchColumn();
            if ($seller_id) {
                $update = $pdo->prepare("UPDATE users SET warning_count = warning_count + 1 WHERE id = ?");
                $update->execute([$seller_id]);
                $_SESSION['flash_msg'] = "Đã gửi cảnh cáo đến người dùng ID $seller_id.";
            }
            break;
        
        default:
            break;
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?status_filter=" . ($_GET['status_filter'] ?? ''));
    exit;
}

// Lọc theo trạng thái
$status_filter = $_GET['status_filter'] ?? 'all';
$where_clause = "";
$params = []; 
$use_named = false; 

if ($status_filter !== 'all') {
    $where_clause = "WHERE r.status = ?";
    $params[] = $status_filter;
}

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Đếm tổng số báo cáo (dùng placeholder ?)
$count_sql = "SELECT COUNT(*) FROM reports r $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetchColumn();
$pages = ceil($total / $limit);


$sql = "SELECT r.*, 
               u.email as reporter_email, u.username as reporter_name,
               b.title as bike_title, b.user_id as seller_id,
               adm.username as handler_name
        FROM reports r
        LEFT JOIN users u ON r.reporter_id = u.id
        LEFT JOIN bikes b ON r.bike_id = b.id
        LEFT JOIN users adm ON r.handled_by = adm.id
        $where_clause
        ORDER BY 
            CASE r.status 
                WHEN 'pending' THEN 1 
                WHEN 'reviewed' THEN 2 
                WHEN 'rejected' THEN 3 
            END,
            r.created_at DESC
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

function statusBadge($status) {
    switch($status) {
        case 'pending': return '<span class="badge-status pending"><i class="fas fa-hourglass-half"></i> Chờ xử lý</span>';
        case 'reviewed': return '<span class="badge-status reviewed"><i class="fas fa-check-circle"></i> Đã xử lý</span>';
        case 'rejected': return '<span class="badge-status rejected"><i class="fas fa-times-circle"></i> Từ chối</span>';
        default: return '<span class="badge-status">'.$status.'</span>';
    }
}

require_once '../inc/header.php';
?>

<style>
   
    :root {
        --primary-orange: #F57C00;
        --bg-gray: #F5F5F5;
        --text-dark: #263238;
        --border-light: #e0e0e0;
        --card-shadow: 0 8px 20px rgba(0,0,0,0.05);
        --hover-shadow: 0 12px 28px rgba(0,0,0,0.1);
    }
    body {
        background-color: var(--bg-gray);
        color: var(--text-dark);
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto;
    }
    .reports-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 1rem;
    }
    .page-header {
        background: white;
        border-radius: 1.2rem;
        padding: 1.2rem 1.8rem;
        margin-bottom: 2rem;
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
        border-left: 5px solid var(--primary-orange);
        padding-left: 1rem;
    }
    .filter-group {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        background: #f8f9fc;
        padding: 0.3rem 0.8rem;
        border-radius: 2rem;
    }
    .filter-group label {
        font-weight: 600;
        margin-right: 0.5rem;
    }
    .filter-select {
        border: 1px solid var(--border-light);
        border-radius: 2rem;
        padding: 0.4rem 1rem;
        background: white;
        font-weight: 500;
        transition: 0.2s;
    }
    .filter-select:focus {
        border-color: var(--primary-orange);
        outline: none;
    }
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 1.5rem;
    }
    .report-card {
        background: white;
        border-radius: 1.2rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(0,0,0,0.03);
    }
    .report-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--hover-shadow);
    }
    .card-head {
        padding: 1rem 1.2rem;
        background: #fefaf5;
        border-bottom: 1px solid #ffe0b5;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .report-id {
        font-weight: 700;
        color: var(--primary-orange);
        background: rgba(245,124,0,0.1);
        padding: 0.2rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.75rem;
    }
    .card-body {
        padding: 1.2rem;
        flex: 1;
    }
    .bike-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-dark);
    }
    .bike-title i {
        color: var(--primary-orange);
    }
    .reason {
        background: #fff3e0;
        padding: 0.5rem 0.8rem;
        border-radius: 0.8rem;
        margin: 0.8rem 0;
        font-size: 0.85rem;
        font-weight: 500;
        border-left: 3px solid var(--primary-orange);
    }
    .meta-info {
        font-size: 0.8rem;
        color: #5a6e7c;
        margin-top: 0.8rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.8rem;
    }
    .meta-info i {
        width: 1.2rem;
        color: var(--primary-orange);
    }
    .card-actions {
        padding: 1rem 1.2rem;
        background: #fafafa;
        border-top: 1px solid var(--border-light);
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
    }
    .btn-sm-custom {
        border: none;
        background: transparent;
        padding: 0.4rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        transition: 0.2s;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .btn-danger-light {
        background: #ffe6e5;
        color: #c62828;
    }
    .btn-danger-light:hover {
        background: #c62828;
        color: white;
    }
    .btn-warning-light {
        background: #fff4e0;
        color: #e67e22;
    }
    .btn-warning-light:hover {
        background: #e67e22;
        color: white;
    }
    .status-select {
        border-radius: 2rem;
        padding: 0.3rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 500;
        border: 1px solid var(--border-light);
    }
    .badge-status {
        display: inline-block;
        padding: 0.3rem 0.9rem;
        border-radius: 2rem;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .pending { background: #fff8e1; color: #f57c00; }
    .reviewed { background: #e0f2e9; color: #2e7d32; }
    .rejected { background: #ffebee; color: #c62828; }
    .pagination-wrap {
        margin-top: 2.5rem;
        display: flex;
        justify-content: center;
    }
    .pagination-custom {
        display: flex;
        gap: 0.4rem;
        flex-wrap: wrap;
        background: white;
        padding: 0.5rem 1rem;
        border-radius: 3rem;
        box-shadow: var(--card-shadow);
    }
    .page-link {
        display: inline-block;
        padding: 0.4rem 0.9rem;
        border-radius: 2rem;
        background: transparent;
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 500;
        transition: 0.2s;
    }
    .page-link:hover, .page-link.active {
        background: var(--primary-orange);
        color: white;
    }
    .modal-custom {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        z-index: 1050;
    }
    .modal-content-custom {
        background: white;
        max-width: 550px;
        width: 90%;
        border-radius: 1.5rem;
        overflow: hidden;
        animation: fadeInUp 0.2s ease;
    }
    .modal-header {
        padding: 1rem 1.5rem;
        background: var(--primary-orange);
        color: white;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
    }
    .modal-body {
        padding: 1.5rem;
        max-height: 60vh;
        overflow-y: auto;
    }
    .close-modal {
        background: none;
        border: none;
        font-size: 1.8rem;
        cursor: pointer;
        color: white;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px);}
        to { opacity: 1; transform: translateY(0);}
    }
    @media (max-width: 640px) {
        .reports-grid { grid-template-columns: 1fr; }
        .page-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="reports-container">
    <div class="page-header">
        <h2><i class="fas fa-flag-checkered" style="color: #F57C00;"></i> Quản lý báo cáo</h2>
        <div class="filter-group">
            <label><i class="fas fa-filter"></i> Lọc:</label>
            <select class="filter-select" id="statusFilter" onchange="location.href='?status_filter='+this.value">
                <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Tất cả</option>
                <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                <option value="reviewed" <?= $status_filter == 'reviewed' ? 'selected' : '' ?>>Đã xử lý</option>
                <option value="rejected" <?= $status_filter == 'rejected' ? 'selected' : '' ?>>Từ chối</option>
            </select>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="border-radius: 1rem;">
            <?= $_SESSION['flash_msg']; unset($_SESSION['flash_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($reports)): ?>
        <div class="alert alert-light text-center p-5 rounded-4" style="background:white;">
            <i class="fas fa-inbox fa-3x mb-3" style="color:#ccc;"></i>
            <p>Không có báo cáo.</p>
        </div>
    <?php else: ?>
        <div class="reports-grid">
            <?php foreach ($reports as $report): ?>
                <div class="report-card">
                    <div class="card-head">
                        <span class="report-id">#<?= $report['id'] ?></span>
                        <?= statusBadge($report['status']) ?>
                    </div>
                    <div class="card-body">
                        <div class="bike-title">
                            <i class="fas fa-bicycle"></i> 
                            <?= htmlspecialchars($report['bike_title'] ?? 'Xe không tồn tại') ?>
                        </div>
                        <div class="reason">
                            <i class="fas fa-comment-dots"></i> Lý do: <?= htmlspecialchars($report['reason']) ?>
                        </div>
                        <div class="meta-info">
                            <span><i class="fas fa-user"></i> Người tố cáo: <?= htmlspecialchars($report['reporter_name'] ?? 'N/A') ?></span>
                            <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($report['created_at'])) ?></span>
                        </div>
                        <?php if ($report['description']): ?>
                            <div class="meta-info mt-1">
                                <span><i class="fas fa-info-circle"></i> Chi tiết: 
                                    <a href="#" class="text-orange" onclick="showDetail(<?= $report['id'] ?>, '<?= addslashes(htmlspecialchars($report['description'])) ?>'); return false;">Xem thêm</a>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-actions">
                        <form method="POST" style="display:inline-block;" onsubmit="return confirm('Cập nhật trạng thái?')">
                            <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                            <input type="hidden" name="action" value="update_status">
                            <select name="status" class="status-select" onchange="this.form.submit()">
                                <option value="pending" <?= $report['status']=='pending'?'selected':'' ?>>Chờ</option>
                                <option value="reviewed" <?= $report['status']=='reviewed'?'selected':'' ?>>Đã xử lý</option>
                                <option value="rejected" <?= $report['status']=='rejected'?'selected':'' ?>>Từ chối</option>
                            </select>
                        </form>
                        <div style="display: flex; gap: 6px;">
                            <?php if ($report['status'] == 'pending'): ?>
                                <form method="POST" onsubmit="return confirm('Ẩn tin đăng này? Hành động này sẽ khiến bài viết không hiển thị công khai.')">
                                    <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                    <input type="hidden" name="action" value="hide_bike">
                                    <button class="btn-sm-custom btn-danger-light"><i class="fas fa-eye-slash"></i> Ẩn tin</button>
                                </form>
                                <form method="POST" onsubmit="return confirm('Cảnh cáo người đăng tin? (+1 điểm vi phạm)')">
                                    <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                    <input type="hidden" name="action" value="warn_user">
                                    <button class="btn-sm-custom btn-warning-light"><i class="fas fa-exclamation-triangle"></i> Cảnh cáo</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($pages > 1): ?>
        <div class="pagination-wrap">
            <div class="pagination-custom">
                <?php for($i=1; $i<=$pages; $i++): ?>
                    <a href="?page=<?= $i ?>&status_filter=<?= $status_filter ?>" class="page-link <?= $i==$page?'active':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div id="detailModal" class="modal-custom">
    <div class="modal-content-custom">
        <div class="modal-header">
            <span><i class="fas fa-file-alt"></i> Chi tiết báo cáo</span>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalDescription"></div>
    </div>
</div>

<script>
    function showDetail(id, description) {
        document.getElementById('modalDescription').innerHTML = '<p><strong>Báo cáo #' + id + '</strong></p><p>' + description.replace(/\n/g, '<br>') + '</p>';
        document.getElementById('detailModal').style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('detailModal').style.display = 'none';
    }
    window.onclick = function(event) {
        let modal = document.getElementById('detailModal');
        if (event.target === modal) modal.style.display = 'none';
    }
</script>

<?php require_once '../inc/footer.php'; ?>
