<?php
session_start();
include 'config.php';

/* ===== AJAX HANDLE ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {

    header('Content-Type: application/json');

    if ($_POST['action'] === 'remove') {
        $id = (int)$_POST['id'];
        unset($_SESSION['cart'][$id]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    if ($_POST['action'] === 'update') {
        $id = (int)$_POST['id'];
        $qty = max(1, (int)$_POST['qty']);

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty'] = $qty;
        }

        echo json_encode(['status' => 'success']);
        exit;
    }
}

include 'includes/header.php';

$cart = $_SESSION['cart'] ?? [];

// normalize dữ liệu
foreach ($cart as $id => $item) {
    if (!isset($item['qty'])) {
        $cart[$id]['qty'] = $item['quantity'] ?? 1;
        $_SESSION['cart'][$id]['qty'] = $cart[$id]['qty'];
    }
}
?>

<style>

.cart-page h2{
    text-align:center;
    margin-bottom:30px;
    font-weight:600;
}

.cart-page{
    width:1200px;
    margin:40px auto;
}

/* EMPTY */
.empty-cart{
    text-align:center;
    padding:80px 0;
}
.empty-cart img{
    width:220px;
    opacity:0.9;
}
.empty-cart p{
    font-size:22px;
    color:#777;
}

/* HEADER */
.cart-header{
    display:grid;
    grid-template-columns: 120px 2fr 1fr 1fr 1fr 80px;
    background:#fafafa;
    padding:15px;
    font-weight:bold;
    border-radius:10px;
    background:#f5f5f5;
    font-size:15px;
    color:#666;
}

/* ITEM */
.cart-item{
    display:grid;
    grid-template-columns: 120px 2fr 1fr 1fr 1fr 80px;
    align-items:center;
    gap:20px;
    padding:18px;
    border-bottom:1px solid #f0f0f0;
    font-size:15px;
    transition:all 0.2s ease;
}
.cart-item:hover{
    background:#fafafa;
    transform:scale(1.01);
}

.cart-item img{
    width:100px;
    height:100px;
    object-fit:contain;   
    background:#fff;
    padding:5px;
    border-radius:10px;
    border:1px solid #eee;
}

.cart-header div,
.cart-item div{
    text-align:center;
}

.cart-item div:nth-child(2){
    text-align:left;
    font-weight:500;
}

/* QTY */
.qty-box{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:5px;
}
.qty-box button{
    width:32px;
    height:32px;
    border:none;
    background:#eee;
    cursor:pointer;
    border-radius:6px;
}
.qty-box button:hover{
    background:#ddd;
}
.qty-box input{
    width:55px;
    height:32px;
    font-size:14px;
    text-align:center;
    border:1px solid #ddd;
    border-radius:6px;
}

/* PRICE */
.price{
    color:#ee4d2d;
    font-weight:bold;
}

/* REMOVE */
.remove{
    color:#999;
    font-size:18px;
    cursor:pointer;
}
.remove:hover{
    color:red;
}

/* SUMMARY */
.cart-summary{
    margin-top:30px;
    display:flex;
    justify-content:flex-end;
}
.summary-box{
    width:380px;
    border:1px solid #eee;
    padding:25px;
    border-radius:12px;
    background:#fff;
}
.summary-row{
    display:flex;
    justify-content:space-between;
    margin-bottom:12px;
}
.total{
    font-size:22px;
    color:#ee4d2d;
    font-weight:bold;
}

/* BUTTON */
.btn-update{
    width:100%;
    padding:12px;
    margin-top:10px;
    border:none;
    background:#ddd;
    cursor:pointer;
    border-radius:8px;
}
.checkout-btn{
    width:100%;
    padding:14px;
    margin-top:10px;
    border:none;
    background:#ee4d2d;
    color:#fff;
    border-radius:8px;
    font-size:16px;
}
.checkout-btn:hover{
    background:#d73211;
}

.remove-btn{
    width:36px;
    height:36px;
    border:none;
    background:#fff;
    border-radius:8px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#aaa;
    border:1px solid #eee;
    transition:0.25s;
}

.remove-btn:hover{
    background:#fff0f0;
    color:#ee4d2d;
    transform:scale(1.1);
}
</style>

<div class="cart-page">

<h2>🛒 Giỏ hàng của bạn</h2>

<?php if(empty($cart)): ?>

<div class="empty-cart">
    <img src="assets/images/empty-cart.png">
    <p>Giỏ hàng đang trống</p>
    <a href="index.php" class="checkout-btn" style="display:inline-block;width:auto;padding:12px 25px;margin-top:20px;">
        Mua ngay
    </a>
</div>

<?php else: ?>

<form method="POST">

<div class="cart-header">
    <div>Ảnh</div>
    <div>Sản phẩm</div>
    <div>Đơn giá</div>
    <div>Số lượng</div>
    <div>Thành tiền</div>
    <div></div>
</div>

<?php 
$total = 0;
foreach($cart as $item): 
$qty = $item['qty'] ?? ($item['quantity'] ?? 1);
$sub = $item['price'] * $qty;
$total += $sub;
?>

<div class="cart-item">

    <img src="<?= $item['image'] ?>">

    <div><?= $item['name'] ?></div>

    <div><?= number_format($item['price']) ?>₫</div>

    <div class="qty-box">
        <button type="button" onclick="changeQty(this,-1)">-</button>
        <input type="number"
       data-id="<?= $item['id'] ?>"
       value="<?= $item['qty'] ?? ($item['quantity'] ?? 1) ?>"
       min="1">
        <button type="button" onclick="changeQty(this,1)">+</button>
    </div>

    <div class="price"><?= number_format($sub) ?>₫</div>

    <div>
        <button type="button" class="remove-btn" onclick="removeItem(<?= $item['id'] ?>)">
            <i class="fas fa-trash-alt"></i>
        </button>
    </div>

</div>

<?php endforeach; ?>

<div class="cart-summary">
    <div class="summary-box">

        <div class="summary-row">
            <span>Tạm tính</span>
            <span><?= number_format($total) ?>₫</span>
        </div>

        <div class="summary-row">
            <span>Vận chuyển</span>
            <span>0₫</span>
        </div>

        <div class="summary-row total">
            <span>Tổng</span>
            <span><?= number_format($total) ?>₫</span>
        </div>

        <button type="submit" name="update_qty" class="btn-update">
            Cập nhật giỏ
        </button>

        <a href="checkout.php" class="checkout-btn" style="display:block;text-align:center;text-decoration:none;">
    Thanh toán
</a>

    </div>
</div>

</form>

<?php endif; ?>

</div>

<script>
function changeQty(btn, change){
    let input = btn.parentElement.querySelector("input");
    let val = parseInt(input.value) || 1;
    val += change;
    if(val < 1) val = 1;

    input.value = val;

    // gọi AJAX luôn
    let id = input.getAttribute("data-id");
    updateQty(id, val);
}

function removeItem(id){

    if(!confirm("Xóa sản phẩm này?")) return;

    fetch("cart.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "ajax=1&action=remove&id=" + id
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === "success"){
            location.reload(); // (có thể nâng cấp bỏ reload sau)
        }
    });
}

function updateQty(id, qty){
    fetch("cart.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "ajax=1&action=update&id=" + id + "&qty=" + qty
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === "success"){
            location.reload();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>