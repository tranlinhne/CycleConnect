<?php 
require_once "config.php";
include "includes/header.php";
?>

<style>

/* ===== BANNER ===== */
.banner {
    position: relative;
    height: 700px;
    background: url('assets/images/banner.jpg') no-repeat center 55%;
    background-size: cover;
    display: flex;
    align-items: center;
    color: white;
    overflow: hidden;
}

/* overlay */
.banner::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.4);
}

/* 🔥 phần cong nhọn */
.banner::after {
    content: "";
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 120px;
    background: #f3f3f3;

    clip-path: polygon(0 40%, 50% 100%, 100% 40%, 100% 100%, 0% 100%);
}

/* nội dung */
.banner-content {
    position: relative;
    z-index: 2;
    padding-left: 100px;
}

.banner-content h1 {
    font-size: 50px;
    font-weight: bold;
}

.banner-content p {
    font-size: 18px;
    margin: 10px 0;
}

.banner-content .price {
    font-size: 28px;
    color: #ff3c3c;
}

/* ===== SHOWCASE ===== */
.showcase {
    background: #f3f3f3;
    padding: 120px 0;
}

.showcase-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 120px;
}

.side {
    display: flex;
}

.pair {
    display: flex;
    gap: 10px;
}

.item {
    width: 150px;
    height: 320px;
    background-size: cover;
    background-repeat: no-repeat;
    position: relative;
    transition: all 0.4s ease;

    clip-path: polygon(0 12%, 100% 0, 100% 100%, 0% 100%);
}

/* ảnh trái */
.left {
    background-position: left center;
}

/* ảnh phải */
.right {
    background-position: right center;
}

/* ===== BÊN PHẢI (mirror chuẩn) ===== */
.right-side .item {
    transform: scaleX(-1);
    clip-path: polygon(0 0, 100% 12%, 100% 100%, 0% 100%);
}

/* hover */
.item:hover {
    transform: translateY(-15px) scale(1.06);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
}

/* hover bên phải */
.right-side .item:hover {
    transform: scaleX(-1) translateY(-15px) scale(1.06);
}

/* chữ dọc */
.item span {
    position: absolute;
    bottom: 15px;
    left: 12px;
    color: white;
    font-size: 13px;
    letter-spacing: 2px;
    writing-mode: vertical-rl;
    transform: rotate(180deg);
}

/* fix chữ bên phải */
.right-side .item span {
    transform: rotate(180deg) scaleX(-1);
}

/* ===== TEXT GIỮA ===== */
.center-text {
    text-align: center;
    animation: fadeUp 1s ease;
}

.center-text p {
    font-size: 13px;
    color: #777;
    letter-spacing: 2px;
}

.center-text h2 {
    color: red;
    font-size: 32px;
    margin: 8px 0;
    font-weight: bold;
}

.center-text span {
    font-size: 13px;
    color: #555;
}

/* ===== FEATURED ===== */
.featured {
    padding: 80px 0;
    background: #fff;
    text-align: center;
}

.featured h2 {
    font-size: 32px;
    margin-bottom: 40px;
}

/* list */
.bike-list {
    display: flex;
    justify-content: center;
    gap: 30px;
}

/* card */
.bike-card {
    width: 300px;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: 0.3s;
    position: relative;
}

.bike-card:hover {
    transform: translateY(-10px);
}

/* image */
.bike-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

/* price */
.bike-card .price {
    position: absolute;
    top: 170px;
    left: 50%;
    transform: translateX(-50%);
    
    background: orange;
    color: white;
    padding: 6px 14px;
    font-weight: bold;
    border-radius: 5px;
}

/* title */
.bike-card h3 {
    margin: 20px 0 10px;
}

/* desc */
.bike-card .desc {
    font-size: 14px;
    color: #666;
    padding: 0 15px;
    height: 50px;
}

/* meta */
.meta {
    display: flex;
    justify-content: space-around;
    font-size: 13px;
    margin: 10px 0;
    color: #777;
}

/* button */
.rent-btn {
    display: inline-block;
    margin: 15px 0 20px;
    padding: 10px 20px;
    background: red;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.rent-btn:hover {
    background: darkred;
}

/* animation */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.item {
    animation: fadeUp 1s ease;
    animation-fill-mode: both;
}

</style>

<!-- ===== BANNER ===== -->
<div class="banner">
    <div class="banner-content">
        <h1>FIND YOUR PERFECT BIKE</h1>
        <p>Xe đạp thể thao chất lượng – an toàn – minh bạch</p>
        <div class="price">Từ 2.000.000 VNĐ</div>
    </div>
</div>

<!-- ===== SHOWCASE ===== -->
<div class="showcase">
    <div class="showcase-wrapper">

        <!-- TRÁI -->
        <div class="side">
            <div class="pair">
                <div class="item left"
                    style="background-image:url('assets/images/bike1.jpg')">
                    <span>ROAD</span>
                </div>

                <div class="item right"
                    style="background-image:url('assets/images/bike1.jpg')">
                    <span>BIKE</span>
                </div>
            </div>
        </div>

        <!-- GIỮA -->
        <div class="center-text">
            <p>WELCOME TO</p>
            <h2>BIKE MARKET</h2>
            <span>NỀN TẢNG MUA BÁN XE ĐẠP</span>
        </div>

        <!-- PHẢI -->
        <div class="side right-side">
            <div class="pair">

                <!-- đúng thứ tự -->
                <div class="item right"
                    style="background-image:url('assets/images/bike1.jpg')">
                    <span>ROAD</span>
                </div>

                <div class="item left"
                    style="background-image:url('assets/images/bike1.jpg')">
                    <span>BIKE</span>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- ===== FEATURED BIKES ===== -->
<div class="featured">
    <h2>FEATURED BIKES</h2>

    <div class="bike-list">
        <?php
        $sql = "SELECT * FROM bicycles WHERE bicycle_id LIMIT 3";
        $result = mysqli_query($conn, $sql);

        while($row = mysqli_fetch_assoc($result)) {
        ?>
            <div class="bike-card">
                <img src="<?php echo $row['main_image']; ?>" alt="">

                <div class="price">
                    <?php echo number_format($row['price']); ?> VNĐ
                </div>

                <h3><?php echo $row['name']; ?></h3>

                <p class="desc">
                    <?php echo $row['description']; ?>
                </p>

                <div class="meta">
                    <span><?php echo $row['location']; ?></span>
                    <span><?php echo $row['condition_status']; ?></span>
                </div>

                <a href="detail.php?id=<?php echo $row['bicycle_id']; ?>" class="rent-btn">
    XEM CHI TIẾT
</a>
                
            </div>
        <?php } ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>