<?php
session_start();
require_once "config.php";
include "includes/auth-handler.php";

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header("Location: login.php?error=Vui lòng đăng nhập để xem tin nhắn.");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$bike_id = isset($_GET['bike_id']) ? (int)$_GET['bike_id'] : 0;
// partner_id là người mình muốn chat (có thể có hoặc không)
$partner_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0; 

// 1. Lấy thông tin chiếc xe và kiểm tra ai là chủ xe
$sql_bike = "SELECT user_id as owner_id, title FROM bikes WHERE id = ?";
$stmt_b = $conn->prepare($sql_bike);
$stmt_b->bind_param("i", $bike_id);
$stmt_b->execute();
$bike = $stmt_b->get_result()->fetch_assoc();

if (!$bike) {
    die("<div style='padding: 50px; text-align: center;'>Không tìm thấy thông tin xe.</div>");
}

$is_owner = ($current_user_id === $bike['owner_id']);

// 2. PHÂN LUỒNG GIAO DIỆN (MODE)
$mode = 'chat'; // Mặc định là khung chat

if ($is_owner) {
    // Nếu là chủ xe và chưa chọn khách hàng cụ thể -> Hiện hộp thư
    if ($partner_id == 0 || $partner_id == $current_user_id) {
        $mode = 'inbox';
    }
} else {
    // Nếu là khách, người đối diện mặc định luôn là chủ xe
    $partner_id = $bike['owner_id'];
}

// 3. XỬ LÝ KHI GỬI TIN NHẮN (Chỉ chạy ở chế độ Chat)
if ($mode === 'chat' && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $content = trim($_POST['message']);
    $sql_insert = "INSERT INTO messages (sender_id, receiver_id, bike_id, content) VALUES (?, ?, ?, ?)";
    $stmt_ins = $conn->prepare($sql_insert);
    $stmt_ins->bind_param("iiis", $current_user_id, $partner_id, $bike_id, $content);
    $stmt_ins->execute();
    
    // Refresh lại trang để hiện tin nhắn mới
    header("Location: chat.php?bike_id=$bike_id&receiver_id=$partner_id");
    exit();
}

include "includes/header.php";
?>

<style>
.chat-container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; height: 600px; }
.chat-header { background: #2f5d62; color: white; padding: 20px; font-weight: bold; font-size: 16px; display: flex; align-items: center; gap: 15px; }
.chat-header a { color: white; font-size: 20px; text-decoration: none; }
.chat-body { flex: 1; padding: 20px; overflow-y: auto; background: #f8fafc; display: flex; flex-direction: column; gap: 15px; }
.msg-bubble { max-width: 75%; padding: 12px 18px; border-radius: 20px; font-size: 15px; line-height: 1.4; }
.msg-me { background: #F57C00; color: white; align-self: flex-end; border-bottom-right-radius: 5px; }
.msg-them { background: #e2e8f0; color: #1e293b; align-self: flex-start; border-bottom-left-radius: 5px; }
.chat-footer { padding: 15px; background: #fff; border-top: 1px solid #e2e8f0; display: flex; gap: 10px; }
.chat-input { flex: 1; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 25px; outline: none; font-size: 15px; }
.btn-send { background: #2f5d62; color: white; border: none; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; }

/* CSS riêng cho hộp thư */
.inbox-list { flex: 1; padding: 20px; overflow-y: auto; background: #fff; }
.inbox-item { display: flex; align-items: center; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 10px; text-decoration: none; color: #1e293b; transition: 0.3s; }
.inbox-item:hover { border-color: #F57C00; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
.inbox-icon { font-size: 35px; color: #cbd5e1; margin-right: 15px; }
</style>

<div class="chat-container">

    <?php if ($mode === 'inbox'): ?>
        <!-- ================= MÀN HÌNH 1: HỘP THƯ CỦA CHỦ XE ================= -->
        <?php
        // SQL: Tìm tất cả những user đã gửi hoặc nhận tin nhắn từ chủ xe về chiếc xe này
        $sql_inbox = "SELECT u.id, u.full_name 
                      FROM users u 
                      WHERE u.id IN (
                          SELECT sender_id FROM messages WHERE bike_id = ? AND receiver_id = ?
                          UNION
                          SELECT receiver_id FROM messages WHERE bike_id = ? AND sender_id = ?
                      )";
        $stmt_inbox = $conn->prepare($sql_inbox);
        $stmt_inbox->bind_param("iiii", $bike_id, $current_user_id, $bike_id, $current_user_id);
        $stmt_inbox->execute();
        $inbox_users = $stmt_inbox->get_result();
        ?>
        
        <div class="chat-header">
            <a href="ad-detail.php?id=<?= $bike_id ?>"><i class="fas fa-arrow-left"></i></a>
            <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                Khách hàng quan tâm: <?= htmlspecialchars($bike['title']) ?>
            </span>
        </div>
        
        <div class="inbox-list">
            <?php if ($inbox_users->num_rows > 0): ?>
                <?php while($u = $inbox_users->fetch_assoc()): ?>
                    <a href="chat.php?bike_id=<?= $bike_id ?>&receiver_id=<?= $u['id'] ?>" class="inbox-item">
                        <i class="fas fa-user-circle inbox-icon"></i>
                        <div>
                            <strong style="display: block; font-size: 16px; margin-bottom: 5px;"><?= htmlspecialchars($u['full_name']) ?></strong>
                            <span style="color: #64748b; font-size: 13px;">Bấm để xem cuộc trò chuyện</span>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; color: #64748b; margin-top: 50px;">
                    <i class="fas fa-box-open" style="font-size: 40px; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                    Chưa có khách hàng nào nhắn tin cho chiếc xe này.
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- ================= MÀN HÌNH 2: KHUNG CHAT ================= -->
        <?php
        // Lấy tên người đối diện để hiển thị trên thanh tiêu đề
        $sql_partner = "SELECT full_name FROM users WHERE id = ?";
        $stmt_p = $conn->prepare($sql_partner);
        $stmt_p->bind_param("i", $partner_id);
        $stmt_p->execute();
        $partner_data = $stmt_p->get_result()->fetch_assoc();
        
        // Truy vấn toàn bộ lịch sử trò chuyện giữa 2 người về chiếc xe này
        $sql_chat = "SELECT * FROM messages 
                     WHERE bike_id = ? 
                       AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) 
                     ORDER BY created_at ASC";
        $stmt_chat = $conn->prepare($sql_chat);
        $stmt_chat->bind_param("iiiii", $bike_id, $current_user_id, $partner_id, $partner_id, $current_user_id);
        $stmt_chat->execute();
        $chat_history = $stmt_chat->get_result();
        ?>
        
        <div class="chat-header">
            <!-- Nút back: Nếu là chủ xe thì back ra Hộp thư, nếu là khách thì back ra Trang sản phẩm -->
            <?php $back_link = $is_owner ? "chat.php?bike_id=$bike_id" : "ad-detail.php?id=$bike_id"; ?>
            <a href="<?= $back_link ?>"><i class="fas fa-arrow-left"></i></a>
            <span>Trò chuyện với: <?= htmlspecialchars($partner_data['full_name'] ?? 'Người dùng') ?></span>
        </div>
        
        <div class="chat-body" id="chatBox">
            <?php if ($chat_history->num_rows > 0): ?>
                <?php while($msg = $chat_history->fetch_assoc()): ?>
                    <?php $is_me = ($msg['sender_id'] == $current_user_id); ?>
                    <div class="msg-bubble <?= $is_me ? 'msg-me' : 'msg-them' ?>">
                        <?= nl2br(htmlspecialchars($msg['content'])) ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; color: #94a3b8; margin: auto;">Hãy gửi lời chào để bắt đầu cuộc trò chuyện!</div>
            <?php endif; ?>
        </div>
        
        <form method="POST" class="chat-footer">
            <input type="text" name="message" class="chat-input" placeholder="Nhập tin nhắn..." required autocomplete="off" autofocus>
            <button type="submit" class="btn-send"><i class="fas fa-paper-plane"></i></button>
        </form>
    <?php endif; ?>

</div>

<script>
// Tự động cuộn xuống cuối màn hình (chỉ áp dụng nếu có phần tử chatBox)
const chatBox = document.getElementById("chatBox");
if (chatBox) {
    chatBox.scrollTop = chatBox.scrollHeight;
}
</script>

<?php include "includes/footer.php"; ?>