<?php
include 'config.php';

// Lấy dữ liệu
$sql = "SELECT * FROM featured_bikes ORDER BY display_order ASC";
$result = $conn->query($sql);

// Lấy sản phẩm đầu tiên cho banner
$first = null;
if ($result && $result->num_rows > 0) {
    $first = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bike Market</title>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- ICON -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- HEADER -->
<?php include 'includes/header.php'; ?>

<!-- HERO -->
<section class="hero">
    <div class="hero-container">

        <div class="hero-left">
            <span class="badge">BEST</span>
            <?php if ($first) { ?>
                <img src="assets/images/<?php echo $first['image']; ?>" alt="">
            <?php } ?>
        </div>

        <div class="hero-right">
            <h1>New Model Cycle</h1>
            <p>Xe đạp mới nhất, thiết kế hiện đại và mạnh mẽ</p>
            <a href="#" class="btn">Shop Now</a>
        </div>

    </div>
</section>

<!-- OUR CYCLE -->
<section class="cycle">
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
            <h3><?php echo $row['name']; ?></h3>
            <p><?php echo nl2br($row['description']); ?></p>
            <div class="price">Price $<?php echo number_format($row['price']); ?></div>
            <a href="#" class="btn dark">Buy Now</a>
        </div>

    </div>

    <?php 
        $i++;
        }
    }
    ?>

</section>

</body>
</html>