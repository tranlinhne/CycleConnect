    <?php
    include 'config.php';
    include 'includes/header.php';

    // Lấy dữ liệu
    $sql = "SELECT * FROM featured_bikes ORDER BY display_order ASC";
    $result = $conn->query($sql);

    // Lấy sản phẩm đầu tiên cho banner
    $first = null;
    if ($result && $result->num_rows > 0) {
        $first = $result->fetch_assoc();
    }
    ?>
    <style>


    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #ffffff;
        color: #333;
        margin: 0;
    }

    /* KHỐI NỀN XANH */
    .hero-inner{
        width:70%;          /* tạo khoảng trắng bên trái */
        margin-left:auto;       /* đẩy khối sang phải */
        background:#2f5d62;
        position:relative;
        color:white;
        padding:100px 80px 0px;
        overflow:visible;
        margin-top: 0;   
    }

    /* CẮT HÌNH THANG */
    .hero-inner::after{
        content:"";
        position:absolute;
        bottom:0;
        left:0;
        width:100%;
        height:220px;
        background:#ffffff;
        clip-path: polygon(0 100%, 100% -1px, 101% 101%, -1% 101%);
    }


    .hero-container {
        display: flex;
        align-items:flex-start; 
        justify-content: space-between;
        gap: 40px;
    }

    /* ===== LEFT (BIKE) ===== */
    .hero-left {
        flex: 1.1;
        position: relative;
        text-align: center;
        margin-left: -340px;
        z-index: 5;     
    }

    .hero-left img {
        width: 650px;          /* XE TO GIỐNG MOCKUP */
        max-width: 100%;
        filter: drop-shadow(0 40px 40px rgba(0,0,0,0.45));
    }

    /* BADGE BEST (MÀU CAM) */
    .badge {
        position: absolute;
        top: -50px;
        left: 470px;
        background: #f4a261;
        width: 95px;
        height: 95px;
        border-radius: 50%;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2f5d62;
        font-size: 18px;
    }

    /* ===== RIGHT TEXT ===== */
    .hero-right{
    flex:1;
    max-width:520px;
    margin-top:-70px;
    transform: translateX(-240px);    /* chỉnh số này để canh */
}

    .hero-right h1 {
        font-size: 64px;
        font-weight: 800;
        margin-bottom: 25px;
        line-height: 1.15;
    }

    .hero-right p {
        font-size: 17px;
        margin-bottom: 30px;
        line-height: 1.7;
        color: #d6d6d6;
    }

    /* BUTTON */
    .btn {
        display: inline-block;
        background: #f4a261;
        padding: 15px 34px;
        color: white;
        text-decoration: none;
        font-weight: 600;
        letter-spacing: .5px;
        transition: 0.3s;
    }

    .btn:hover {
        background: #e76f51;
    }

    /* ===== ARROW NAV ===== */
    .hero-nav {
        position: absolute;
        right: 90px;
        bottom: 60px;
        display: flex;
        gap: 18px;
    }

    .hero-nav button {
        width: 58px;
        height: 58px;
        border: none;
        cursor: pointer;
        font-size: 18px;
    }

    .hero-nav .prev {
        background: #f4a261;
        color: white;
    }

    .hero-nav .next {
        background: #264653;
        color: white;
    }
    /* BUTTON */
    .btn {
        display: inline-block;
        background: #f4a261;
        padding: 14px 30px;
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: 0.3s;
    }

    .btn:hover {
        background: #e76f51;
    }

    .btn.dark {
        background: #264653;
    }


    /* ===== SECTION ===== */
.cycle{
    padding:120px 0px;
    background:#ffffff;
}

.cycle-container{
    width:1200px;
    margin:auto;
}

    .cycle h2 {
        font-size: 62px;
        margin-bottom: -20px;
        text-align: center;
    }

    .cycle .sub {
        color: #777;
        font-size: 22px;
        margin-bottom: 100px;
        text-align: center;
    }

    /* ===== ITEM ===== */
.cycle-item{
    display:grid;
    grid-template-columns: 520px 1fr;
    align-items:center;
    gap:120px;
    margin-bottom:240px;
}

/* ĐẢO NGƯỢC */
.cycle-item.reverse .cycle-img{
    order:2;
}

.cycle-item.reverse .cycle-info{
    order:1;
}

    /* đảo layout */
    .cycle-item.reverse {
        flex-direction: row-reverse;
    }

    /* IMAGE */
    .cycle-img {
        position: relative;
        width: 40%;
    }

.cycle-img img{
    width:420px;
    position:relative;
    z-index:3;
    filter: drop-shadow(0 30px 30px rgba(0,0,0,0.25));
}

.cycle-img{
    position:relative;
    width:100%;
}

/* NỀN CAM DỰNG ĐỨNG */
.cycle-img::before{
    content:"";
    position:absolute;
    left:-40px;
    top:-60px;
    width:260px;
    height:520px;
    background:#f4a261;
    clip-path: polygon(0 0, 100% 8%, 100% 75%, 0 100%);
    z-index:1;
}

.number{
    position:absolute;
    right:300px;
    top:-40px; 
    width:55px;
    height:55px;
    font-size:14px;
    background:#2f5d62;
    color:white;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
    z-index:5;
}
    /* INFO */
.cycle-info{
    display:grid;
    display:block; 
    align-items:start;
    gap:40px;
}

.title-row{
    display:flex;
    align-items:center;
    gap:60px;
    margin-bottom:20px;
}

.line{
    width:80px;
    height:2px;
    background:#2f5d62;
}
/* cột trái */
.cycle-text h3{
    font-size:42px;
    margin:0;
    margin-left:180px; 
}

.cycle-text p{
    color:#555;
    line-height:1.8;
    margin-bottom:30px;
    max-width:780px;
}

.cycle-item .cycle-text{
    transform: translateX(-20px);
}

.cycle-item.reverse .cycle-text{
    transform: translateX(20px);
}
/* ===== DỊCH XE + NỀN CAM ===== */
.cycle-item .cycle-img{
    transform: translateX(100px);   /* item 1,3 → qua phải */
}

.cycle-item.reverse .cycle-img{
    transform: translateX(100px);  /* item 2 → qua trái */
}
.cycle-price{
    display:flex;
    align-items:flex-start;
    gap:6px;
    font-weight:bold;
    font-size:22px;
}

/* dấu $ */
.cycle-price .currency{
    font-size:22px;
    color:#f4a261;
}


.bottom-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-top:20px;
    max-width:700px;
}

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {

        .hero-container {
            flex-direction: column;
            text-align: center;
        }

        .hero-left img {
            width: 300px;
        }

        .cycle-item {
            flex-direction: column;
            text-align: center;
        }

        .cycle-item.reverse {
            flex-direction: column;
        }

        .cycle-img,
        .cycle-info {
            width: 100%;
        }

        .cycle-info {
            text-align: center;
        }

        
    }

    
    html, body{
        max-width:100%;
        overflow-x:hidden;
    }
    /* ẨN SCROLLBAR DỌC HOÀN TOÀN */

    /* Chrome, Edge, Safari */
    html::-webkit-scrollbar,
    body::-webkit-scrollbar{
        width:0;
        height:0;
    }

    /* Firefox */
    html, body{
        scrollbar-width:none;
    }

    /* IE cũ */
    html, body{
        -ms-overflow-style:none;
    }
    </style>

    <body>

<section class="hero">
<div class="hero-inner">
    <div class="hero-container">

        <div class="hero-left">
            <span class="badge">BEST</span>
            <img src="assets/images/hero-bike.png" alt="Bike">
        </div>

        <div class="hero-right">
            <h1>Mẫu<br>Xe<br>2026</h1>
            <p>
                Mang đến trải nghiệm đạp xe hoàn hảo với thiết kế hiện đại và hiệu suất vượt trội.
            </p>
            <a href="#" class="btn">Mua Ngay</a>
        </div>

        <div class="hero-nav">
            <button class="prev"><i class="fas fa-chevron-left"></i></button>
            <button class="next"><i class="fas fa-chevron-right"></i></button>
        </div>

    </div>
</div>
</section>

    <!-- OUR CYCLE -->
    <section class="cycle">
        <div class="cycle-container">
        <h2>Our Cycle</h2>
        <p class="sub">Sản phẩm nổi bật của chúng tôi</p>

        <?php
        if ($result && $result->num_rows > 0) {

            // quay lại từ đầu
            $result->data_seek(0);
            $i = 1;

            while ($row = $result->fetch_assoc()) {
        ?>

        <div class="cycle-item <?php echo ($i % 2 == 0) ? 'reverse' : ''; ?>">

            <div class="cycle-img">
                <div class="bg-box"></div>
                <img src="assets/images/<?php echo $row['image']; ?>" alt="">
                <span class="number"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></span>
            </div>

            <div class="cycle-info">

    <div class="cycle-text">
    <div class="title-row">
        <span class="line"></span>
        <h3><?php echo $row['name']; ?></h3>
    </div>

    <p><?php echo nl2br($row['description']); ?></p>

    <div class="bottom-row">
        <a href="#" class="btn dark">Mua ngay</a>
        <div class="cycle-price">
    <span class="currency">$</span>
    <span class="amount"><?php echo number_format($row['price']); ?></span>
</div>
    </div>
</div>

</div>

        </div>

        <?php 
            $i++;
            }
        }
        ?>

        </div>

    </section>
<?php include "includes/footer.php"; ?>
    </body>
