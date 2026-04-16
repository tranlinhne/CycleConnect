<?php include __DIR__ . '/../includes/header.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.conversation-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #eee;
    text-decoration: none;
    color: #333;
    transition: background 0.2s;
}
.conversation-item:hover {
    background: #f8f8f8;
    color: #333;
}
.conv-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e60000;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 20px;
    flex-shrink: 0;
}
.conv-info {
    margin-left: 15px;
    flex: 1;
    overflow: hidden;
}
.conv-name {
    font-weight: bold;
    font-size: 15px;
}
.conv-bike {
    font-size: 13px;
    color: #e60000;
}
.conv-last {
    font-size: 13px;
    color: #888;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.conv-arrow {
    color: #ccc;
    font-size: 20px;
    margin-left: 10px;
}
</style>

<div class="container" style="max-width: 700px; margin-top: 30px; margin-bottom: 40px;">
    <h4 class="mb-3"><i class="fa fa-comments"></i> Tin nhắn của tôi</h4>

    <div class="card shadow-sm">
        <?php if (empty($conversations)): ?>
            <div class="text-center p-5 text-muted">
                <i class="fa fa-comment-slash fa-2x mb-3"></i>
                <p>Chưa có tin nhắn nào.</p>
            </div>
        <?php else: ?>
            <?php foreach ($conversations as $c): ?>
                <a href="index.php?action=chat&id=<?= $c['id'] ?>"
                   class="conversation-item">

                    <div class="conv-avatar">
                        <?= mb_substr($c['ten_doi_phuong'], 0, 1) ?>
                    </div>

                    <div class="conv-info">
                        <div class="conv-name"><?= htmlspecialchars($c['ten_doi_phuong']) ?></div>
                        <div class="conv-bike">
                            <i class="fa fa-bicycle"></i>
                            <?= htmlspecialchars($c['ten_xe']) ?>
                        </div>
                        <div class="conv-last">
                            <?= htmlspecialchars($c['tin_nhan_cuoi'] ?? 'Chưa có tin nhắn') ?>
                        </div>
                    </div>

                    <div class="conv-arrow">›</div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>