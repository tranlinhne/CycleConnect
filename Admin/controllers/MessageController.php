<?php
require_once "models/Message.php";

class MessageController {
    private $model;

    public function __construct() {
        session_start();
        // gia lap login, thay the sau
        if (isset($_GET['switch_user'])) {
        $_SESSION['user_id'] = (int)$_GET['switch_user'];
    }
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = 1;
        }
        $this->model = new Message();
    }

    
    public function index() {
        $conversations = $this->model->getConversations($_SESSION['user_id']);
        require __DIR__ . "/../views/conversations.php";
    }

    public function chat() {
        $hoi_thoai_id = $_GET['id'] ?? 0;
        $messages     = $this->model->getMessages($hoi_thoai_id);
        $conversation = $this->model->getConversationDetail($hoi_thoai_id);
        $conversations = $this->model->getConversations($_SESSION['user_id']); 
        require __DIR__ . "/../views/chat.php";
    }

    
    public function send() {
        $hoi_thoai_id = $_POST['hoi_thoai_id'] ?? 0;
        $noi_dung     = $_POST['noi_dung']     ?? '';

        if (empty($noi_dung)) {
            die("Nội dung tin nhắn không được trống!");
        }

        $this->model->sendMessage(
            $hoi_thoai_id,
            $_SESSION['user_id'],
            $noi_dung
        );

        
        header("Location: index.php?action=chat&id=" . $hoi_thoai_id);
    }

    
    public function createConversation() {
        $seller_id = $_GET['seller_id'] ?? 0;
        $xe_id     = $_GET['xe_id']     ?? 0;

        $hoi_thoai_id = $this->model->createConversation(
            $_SESSION['user_id'],
            $seller_id,
            $xe_id
        );

        header("Location: index.php?action=chat&id=" . $hoi_thoai_id);
    }
}