<?php
require_once "config/database.php";

class Message {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    
    public function getConversations($user_id) {
    $query = "SELECT c.*,
                b.name AS ten_xe,
                CASE 
                    WHEN c.buyer_id = :user_id THEN u_seller.name
                    ELSE u_buyer.name
                END AS ten_doi_phuong,
                (SELECT noi_dung 
                 FROM tin_nhan t 
                 WHERE t.hoi_thoai_id = c.id 
                 ORDER BY t.created_at DESC 
                 LIMIT 1) AS tin_nhan_cuoi
              FROM cuoc_hoi_thoai c
              JOIN bicycles b     ON c.xe_id     = b.id
              JOIN users u_buyer  ON c.buyer_id  = u_buyer.id
              JOIN users u_seller ON c.seller_id = u_seller.id
              WHERE c.buyer_id = :user_id OR c.seller_id = :user_id
              ORDER BY c.created_at DESC";
    $stmt = $this->conn->prepare($query);
    $stmt->execute(['user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    
    public function getMessages($hoi_thoai_id) {
    $query = "SELECT t.*, u.name AS ten_nguoi_gui
              FROM tin_nhan t
              JOIN users u ON t.nguoi_gui_id = u.id
              WHERE t.hoi_thoai_id = :hoi_thoai_id
              ORDER BY t.created_at ASC";
    $stmt = $this->conn->prepare($query);
    $stmt->execute(['hoi_thoai_id' => $hoi_thoai_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getConversationDetail($hoi_thoai_id) {
    $query = "SELECT c.*, b.name AS ten_xe,
                u_buyer.name  AS ten_buyer,
                u_seller.name AS ten_seller
              FROM cuoc_hoi_thoai c
              JOIN bicycles b     ON c.xe_id     = b.id
              JOIN users u_buyer  ON c.buyer_id  = u_buyer.id
              JOIN users u_seller ON c.seller_id = u_seller.id
              WHERE c.id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->execute(['id' => $hoi_thoai_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
    
    public function sendMessage($hoi_thoai_id, $nguoi_gui_id, $noi_dung) {
        $query = "INSERT INTO tin_nhan (hoi_thoai_id, nguoi_gui_id, noi_dung)
                  VALUES (:hoi_thoai_id, :nguoi_gui_id, :noi_dung)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            'hoi_thoai_id' => $hoi_thoai_id,
            'nguoi_gui_id' => $nguoi_gui_id,
            'noi_dung'     => $noi_dung
        ]);
    }

    
    public function createConversation($buyer_id, $seller_id, $xe_id) {

        $query = "SELECT id FROM cuoc_hoi_thoai
                  WHERE buyer_id = :buyer_id
                  AND seller_id = :seller_id
                  AND xe_id = :xe_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            'buyer_id'  => $buyer_id,
            'seller_id' => $seller_id,
            'xe_id'     => $xe_id
        ]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        
        if ($existing) {
            return $existing['id'];
        }

        
        $query = "INSERT INTO cuoc_hoi_thoai (buyer_id, seller_id, xe_id)
                  VALUES (:buyer_id, :seller_id, :xe_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            'buyer_id'  => $buyer_id,
            'seller_id' => $seller_id,
            'xe_id'     => $xe_id
        ]);
        return $this->conn->lastInsertId();
    }
}