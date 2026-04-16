<?php
include 'config.php';
include 'includes/header.php';

$bikes = array(
    array(
        'id' => 1,
        'name' => 'Kukirin X1',
        'description' => 'Chiếc xe đạp điện leo núi cao cấp được thiết kế dành cho những người yêu thích khám phá và chinh phục địa hình khó. Xe được trang bị động cơ mạnh mẽ, pin dung lượng lớn giúp di chuyển quãng đường dài mà không lo hết pin. Phù hợp cho đi phượt, leo núi, hoặc di chuyển hằng ngày trong thành phố.',
        'price' => 15000000,
        'image' => 'xe-dap-dien-leo-nui-pro-x1.png'
    ),
    array(
        'id' => 2,
        'name' => 'City Lite',
        'description' => 'Xe đạp thành phố thời trang, nhẹ và linh hoạt, rất phù hợp cho việc đi học, đi làm và dạo phố. Thiết kế tối giản hiện đại giúp việc điều khiển trở nên dễ dàng ngay cả với người mới sử dụng. Đây là lựa chọn hoàn hảo cho sinh viên và dân văn phòng.',
        'price' => 6500000,
        'image' => 'xe-dap-thanh-pho-city-lite.png'
    ),
    array(
        'id' => 3,
        'name' => 'Racing Speed R9',
        'description' => 'Chiếc xe đạp đua hiệu suất cao dành cho những người đam mê tốc độ và thể thao. Khung carbon siêu nhẹ kết hợp thiết kế khí động học giúp giảm lực cản gió tối đa. Phù hợp cho luyện tập thể thao, thi đấu hoặc chinh phục những cung đường dài.',
        'price' => 22000000,
        'image' => 'xe-dap-dua-racing-speed-r9.png'
    )
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenRide - Trang chủ</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #222; }
        .hero { width: 100%; overflow: hidden; }
        .hero-inner { width: 100%; max-width: 1400px; margin: 0 auto; background: #2f5d62; color: #fff; position: relative; padding: 80px 60px 120px; }
        .hero-inner::after { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 160px; background: #f3f3f3; clip-path: polygon(0 100%, 100% 25%, 100% 100%); }
        .hero-grid { display: grid; grid-template-columns: 1.1fr 1fr; align-items: center; gap: 40px; position: relative; z-index: 2; }
        .hero-bike { position: relative; }
        .hero-bike img { width: 100%; max-width: 640px; filter: drop-shadow(0 26px 30px rgba(0,0,0,.35)); }
        .best-badge { position: absolute; top: 0; left: 16%; width: 90px; height: 90px; border-radius: 50%; background: #f0be6f; color: #234e5a; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 18px; z-index: 10; text-transform: uppercase; }
        .hero-text h1 { font-size: 68px; line-height: 1.05; margin: 0 0 20px; }
        .hero-text p { color: #d8e1e4; max-width: 500px; margin: 0 0 24px; font-size: 18px; line-height: 1.6; }
        .btn-hero { display: inline-block; background: #f0be6f; color: #234e5a; font-weight: 700; text-decoration: none; padding: 12px 26px; border-radius: 4px; }

        .cycle-section { background: #f3f3f3; padding: 80px 0 40px; }
        .container { width: 100%; max-width: 1240px; margin: 0 auto; padding: 0 18px; }
        .section-title { text-align: center; font-size: 58px; margin: 0; font-weight: 800; }
        .section-sub { text-align: center; color: #555; margin: 12px 0 70px; font-size: 20px; }

        .cycle-item { display: grid; grid-template-columns: 1fr 1fr; align-items: center; gap: 70px; margin-bottom: 90px; }
        .cycle-item.reverse { direction: rtl; }
        .cycle-item.reverse > * { direction: ltr; }

        .cycle-image-wrap { position: relative; min-height: 400px; display: flex; align-items: center; justify-content: center; }
        .shape { position: absolute; inset: 0 16% 0 0; background: #f0be6f; clip-path: polygon(0 7%, 100% 18%, 100% 85%, 0 100%); }
        .cycle-item.reverse .shape { inset: 0 0 0 16%; }
        .cycle-image-wrap img { position: relative; z-index: 2; max-width: 360px; width: 100%; }
        .number-dot { position: absolute; top: 20px; right: 18%; z-index: 3; width: 52px; height: 52px; border-radius: 50%; background: #2f5d62; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; }
        .cycle-item.reverse .number-dot { right: auto; left: 18%; }

        .line { width: 140px; height: 2px; background: #4d6670; margin-bottom: 22px; }
        .cycle-info h3 { font-size: 56px; margin: 0 0 20px; }
        .cycle-info p { color: #444; font-size: 19px; line-height: 1.7; margin: 0 0 26px; }
        .row-bottom { display: flex; justify-content: space-between; align-items: center; }
        .btn-buy { background: #2f5d62; color: #fff; text-decoration: none; font-weight: 700; padding: 12px 24px; border-radius: 2px; }
        .price { font-size: 36px; font-weight: 800; }
        .price small { font-size: 20px; color: #f0be6f; margin-left: 6px; }

        @media (max-width: 980px) {
            .hero-grid, .cycle-item { grid-template-columns: 1fr; }
            .hero-text h1 { font-size: 46px; }
            .section-title { font-size: 42px; }
            .cycle-info h3 { font-size: 40px; }
            .cycle-item.reverse { direction: ltr; }
        }
    </style>
</head>
<body>
<section class="hero">
    <div class="hero-inner">
        <div class="hero-grid">
            <div class="hero-bike">
                <span class="best-badge">BEST</span>
                <img src="assets/images/hero-bike.png" alt="Bike">
            </div>
            <div class="hero-text">
                <h1>Mẫu<br>Xe<br>2026</h1>
                <p>Mang đến trải nghiệm đạp xe hoàn hảo với thiết kế hiện đại và hiệu suất vượt trội.</p>
                <a href="#" class="btn-hero">Mua Ngay</a>
            </div>
        </div>
    </div>
</section>

<section class="cycle-section">
    <div class="container">
        <h2 class="section-title">Our Cycle</h2>
        <p class="section-sub">It is a long established fact that a reader will be distracted by the</p>

        <?php $i = 1; foreach ($bikes as $bike): ?>
            <article class="cycle-item <?= ($i % 2 === 0) ? 'reverse' : '' ?>">
                <div class="cycle-image-wrap">
                    <div class="shape"></div>
                    <span class="number-dot"><?= str_pad((string)$i, 2, '0', STR_PAD_LEFT) ?></span>
                    <img src="assets/images/<?= htmlspecialchars($bike['image']) ?>" alt="<?= htmlspecialchars($bike['name']) ?>">
                </div>

                <div class="cycle-info">
                    <div class="line"></div>
                    <h3><?= htmlspecialchars($bike['name']) ?></h3>
                    <p><?= htmlspecialchars($bike['description']) ?></p>
                    <div class="row-bottom">
                        <a href="#" class="btn-buy">Mua ngay</a>
                        <div class="price"><span style="color: #fca311; font-size: 20px; font-weight: 700; margin-right: 5px;">$</span><?= number_format((float)$bike['price'], 0, '.', ',') ?></div>
                    </div>
                </div>
            </article>
        <?php $i++; endforeach; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
