<?php include __DIR__ . '/../includes/header.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.messenger-wrapper {
    display: flex;
    height: calc(100vh - 220px);
    max-width: 1000px;
    margin: 20px auto;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

/* ===== CỘT TRÁI — danh sách ===== */
.conv-list {
    width: 300px;
    min-width: 300px;
    border-right: 1px solid #eee;
    overflow-y: auto;
    background: #fff;
}
.conv-list-header {
    padding: 15px;
    font-weight: bold;
    font-size: 15px;
    border-bottom: 1px solid #eee;
    background: #fafafa;
}
.conv-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    border-bottom: 1px solid #f5f5f5;
    text-decoration: none;
    color: #333;
    transition: background 0.15s;
    gap: 10px;
}
.conv-item:hover {
    background: #fff5f5;
    color: #333;
}
.conv-item.active {
    background: #fff0f0;
    border-left: 3px solid #e60000;
}
.conv-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #e60000;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 17px;
    flex-shrink: 0;
}
.conv-info { overflow: hidden; }
.conv-name {
    font-weight: bold;
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.conv-bike {
    font-size: 11px;
    color: #e60000;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.conv-last {
    font-size: 11px;
    color: #999;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ===== CỘT PHẢI — chat ===== */
.chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #fff;
    min-width: 0;
}
.chat-header {
    background: #1a1a1a;
    color: white;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}
.chat-header .avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #e60000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    flex-shrink: 0;
}
.chat-header .hd-name {
    font-weight: bold;
    font-size: 14px;
}
.chat-header .hd-bike {
    font-size: 11px;
    color: #aaa;
}
.chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f5f5f5;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.bubble-wrap { display: flex; flex-direction: column; }
.bubble-wrap.me { align-items: flex-end; }
.bubble-wrap.other { align-items: flex-start; }
.bubble {
    max-width: 60%;
    padding: 9px 14px;
    border-radius: 18px;
    font-size: 13px;
    line-height: 1.5;
    word-break: break-word;
}
.bubble-wrap.me .bubble {
    background: #e60000;
    color: white;
    border-bottom-right-radius: 4px;
}
.bubble-wrap.other .bubble {
    background: white;
    color: #333;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.bubble-meta {
    font-size: 10px;
    color: #aaa;
    margin-top: 3px;
    padding: 0 4px;
}
.chat-footer {
    padding: 10px 15px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
    background: white;
    flex-shrink: 0;
}
.chat-footer input {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 20px;
    padding: 8px 16px;
    font-size: 13px;
    outline: none;
}
.chat-footer input:focus { border-color: #e60000; }
.chat-footer button {
    background: #e60000;
    color: white;
    border: none;
    border-radius: 20px;
    padding: 8px 20px;
    font-size: 13px;
    cursor: pointer;
}
.chat-footer button:hover { background: #c00; }
</style>

<div class="messenger-wrapper">

    <!-- CỘT TRÁI -->
    <div class="conv-list">
        <div class="conv-list-header">
            💬 Tin nhắn
        </div>

        <?php if (empty($conversations)): ?>
            <div class="p-3 text-muted text-center" style="font-size:13px;">
                Chưa có hội thoại nào
            </div>
        <?php else: ?>
            <?php foreach ($conversations as $c): ?>
                <?php $active = ($c['id'] == ($_GET['id'] ?? 0)) ? 'active' : ''; ?>
                <a href="index.php?action=chat&id=<?= $c['id'] ?>"
                   class="conv-item <?= $active ?>">
                    <div class="conv-avatar">
                        <?= mb_substr($c['ten_doi_phuong'], 0, 1) ?>
                    </div>
                    <div class="conv-info">
                        <div class="conv-name"><?= htmlspecialchars($c['ten_doi_phuong']) ?></div>
                        <div class="conv-bike">🚲 <?= htmlspecialchars($c['ten_xe']) ?></div>
                        <div class="conv-last">
                            <?= htmlspecialchars($c['tin_nhan_cuoi'] ?? 'Chưa có tin nhắn') ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- CỘT PHẢI -->
    <div class="chat-area">

        <!-- Header -->
        <div class="chat-header">
            <div class="avatar">
                <?= mb_substr($conversation['ten_seller'] ?? '?', 0, 1) ?>
            </div>
            <div>
                <div class="hd-name">
                    <?= htmlspecialchars($conversation['ten_buyer']) ?>
                    →
                    <?= htmlspecialchars($conversation['ten_seller']) ?>
                </div>
                <div class="hd-bike">🚲 <?= htmlspecialchars($conversation['ten_xe']) ?></div>
            </div>
        </div>

        <!-- Tin nhắn -->
        <div class="chat-body" id="chatBody">
            <?php if (empty($messages)): ?>
                <div class="text-center text-muted mt-4" style="font-size:13px;">
                    Chưa có tin nhắn. Hãy bắt đầu cuộc trò chuyện!
                </div>
            <?php else: ?>
                <?php foreach ($messages as $m): ?>
                    <?php $is_me = ($m['nguoi_gui_id'] == $_SESSION['user_id']); ?>
                    <div class="bubble-wrap <?= $is_me ? 'me' : 'other' ?>">
                        <div class="bubble">
                            <?= htmlspecialchars($m['noi_dung']) ?>
                        </div>
                        <div class="bubble-meta">
                            <?= htmlspecialchars($m['ten_nguoi_gui']) ?>
                            · <?= date('H:i d/m', strtotime($m['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Form gửi -->
        <form method="POST" action="index.php?action=send" class="chat-footer">
            <input type="hidden" name="hoi_thoai_id" value="<?= $_GET['id'] ?? 0 ?>">
            <input type="text" name="noi_dung"
                   placeholder="Nhập tin nhắn..." required autocomplete="off">
            <button type="submit">Gửi</button>
        </form>

    </div>
</div>

<script>
    const chatBody = document.getElementById('chatBody');
    chatBody.scrollTop = chatBody.scrollHeight;
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>