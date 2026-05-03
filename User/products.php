<?php 
include 'config.php';
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
    width: 100%;
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

/* ===== FILTER ===== */
.filter-box {
    max-width: 1200px;
    margin: 0 auto 40px;
    text-align: left;
}

.filter-group {
    margin-bottom: 15px;
}

.filter-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
}

.filter-group button {
    margin: 5px;
    padding: 8px 14px;
    border: none;
    background: #eee;
    border-radius: 20px;
    cursor: pointer;
    transition: 0.3s;
}

.filter-group button:hover {
    background: red;
    color: white;
}

.clear-filter {
    display: inline-block;
    margin-top: 10px;
    color: red;
    text-decoration: none;
}

/* ===== FILTER DROPDOWN ===== */
.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    margin-bottom: 40px;
}

.dropdown {
    position: relative;
}

.dropdown button {
    padding: 10px 18px;
    border-radius: 25px;
    border: none;
    background: #eee;
    cursor: pointer;
    font-weight: 500;
    transition: 0.3s;
}

.dropdown button:hover {
    background: red;
    color: white;
}

/* dropdown content */
.dropdown-content {
    display: none;
    position: absolute;
    top: 110%;
    left: 0;
    min-width: 220px;
    background: white;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    border-radius: 12px;
    padding: 10px;
    z-index: 100;
    max-height: 250px;
    overflow-y: auto;
}

.dropdown-content label {
    display: block;
    padding: 6px 10px;
    cursor: pointer;
    border-radius: 6px;
    transition: 0.2s;
}

.dropdown-content label:hover {
    background: #f5f5f5;
}

.dropdown-content input {
    margin-right: 8px;
}

.apply-btn {
    padding: 10px 25px;
    border: none;
    background: red;
    color: white;
    border-radius: 25px;
    cursor: pointer;
    font-weight: bold;
}

.apply-btn:hover {
    background: darkred;
}

/* ===== GRID 4 CỘT ===== */
.bike-list {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
    max-width: 1400px;
    margin: auto;
}

</style>

<!-- ===== BANNER ===== -->
<div class="banner">
    <div class="banner-content">
        
        
    </div>
</div>

<!-- ===== SHOWCASE ===== -->
<div class="showcase">
    <div class="showcase-wrapper">

        <!-- TRÁI -->
        <div class="side">
            <div class="pair">
                <div class="item left"
                    style="background-image:url('assets/images/bike1.png')">
                    <span>ROAD</span>
                </div>

                <div class="item right"
                    style="background-image:url('assets/images/bike1.png')">
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
                <div class="item left"
                    style="background-image:url('assets/images/bike2.jpg')">
                    <span>ROAD</span>
                </div>

                <div class="item right"
                    style="background-image:url('assets/images/bike2.jpg')">
                    <span>BIKE</span>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- ===== FILTER + LIST ===== -->
<div class="featured">
    <h2>DANH SÁCH XE ĐẠP</h2>

<?php
// ===== LẤY FILTER =====
$brand = $_GET['brand'] ?? [];
$category = $_GET['category'] ?? [];
$condition = $_GET['condition'] ?? [];
$size = $_GET['size'] ?? [];
$location = $_GET['location'] ?? [];

$where = [];

// helper
function buildIn($field, $arr, $conn) {
    if (empty($arr)) return null;

    $safe = array_map(function($v) use ($conn) {
        return "'".mysqli_real_escape_string($conn,$v)."'";
    }, $arr);

    return "$field IN (".implode(",", $safe).")";
}

// build query
if ($brand) $where[] = "b.brand_id IN (".implode(",", array_map('intval',$brand)).")";
if ($category) $where[] = "b.category_id IN (".implode(",", array_map('intval',$category)).")";
if ($condition) $where[] = buildIn("b.condition_status",$condition,$conn);
if ($size) $where[] = buildIn("b.frame_size",$size,$conn);
if ($location) $where[] = buildIn("b.location",$location,$conn);

$where_sql = $where ? "WHERE ".implode(" AND ", $where) : "";

// ===== LẤY DATA =====
$sql = "
SELECT b.*, c.name as category_name, br.name as brand_name
FROM bicycles b
LEFT JOIN categories c ON b.category_id = c.id
LEFT JOIN brands br ON b.brand_id = br.id
$where_sql
ORDER BY b.bicycle_id DESC
";

$result = mysqli_query($conn, $sql);

// ===== LẤY DATA FILTER =====
$brands = mysqli_query($conn,"SELECT * FROM brands");
$categories = mysqli_query($conn,"SELECT * FROM categories");
$conditions = mysqli_query($conn,"SELECT DISTINCT condition_status FROM bicycles");
$sizes = mysqli_query($conn,"SELECT DISTINCT frame_size FROM bicycles");
$locations = mysqli_query($conn,"SELECT DISTINCT location FROM bicycles");
?>

<!-- ===== FILTER UI ===== -->
<form method="GET" class="filter-box">

<div class="filter-row">

    <!-- BRAND -->
    <div class="dropdown">
        <button type="button" onclick="toggleDropdown(this)">Brand ▼</button>
        <div class="dropdown-content">
            <?php while($b = mysqli_fetch_assoc($brands)) { ?>
                <label>
                    <input type="checkbox" name="brand[]" value="<?php echo $b['id']; ?>">
                    <?php echo $b['name']; ?>
                </label>
            <?php } ?>
        </div>
    </div>

    <!-- CATEGORY -->
    <div class="dropdown">
        <button type="button" onclick="toggleDropdown(this)">Category ▼</button>
        <div class="dropdown-content">
            <?php while($c = mysqli_fetch_assoc($categories)) { ?>
                <label>
                    <input type="checkbox" name="category[]" value="<?php echo $c['id']; ?>">
                    <?php echo $c['name']; ?>
                </label>
            <?php } ?>
        </div>
    </div>

    <!-- CONDITION -->
    <div class="dropdown">
        <button type="button" onclick="toggleDropdown(this)">Condition ▼</button>
        <div class="dropdown-content">
            <?php while($c = mysqli_fetch_assoc($conditions)) { ?>
                <label>
                    <input type="checkbox" name="condition[]" value="<?php echo $c['condition_status']; ?>">
                    <?php echo $c['condition_status']; ?>
                </label>
            <?php } ?>
        </div>
    </div>

    <!-- SIZE -->
    <div class="dropdown">
        <button type="button" onclick="toggleDropdown(this)">Size ▼</button>
        <div class="dropdown-content">
            <?php while($s = mysqli_fetch_assoc($sizes)) { ?>
                <label>
                    <input type="checkbox" name="size[]" value="<?php echo $s['frame_size']; ?>">
                    <?php echo $s['frame_size']; ?>
                </label>
            <?php } ?>
        </div>
    </div>

    <!-- LOCATION -->
    <div class="dropdown">
        <button type="button" onclick="toggleDropdown(this)">Location ▼</button>
        <div class="dropdown-content">
            <?php while($l = mysqli_fetch_assoc($locations)) { ?>
                <label>
                    <input type="checkbox" name="location[]" value="<?php echo $l['location']; ?>">
                    <?php echo $l['location']; ?>
                </label>
            <?php } ?>
        </div>
    </div>

</div>

<div style="text-align:center; margin-top:20px;">
    <button type="submit" class="apply-btn">Áp dụng lọc</button>
    <a href="bikes.php" class="clear-filter">Xóa lọc</a>
</div>

</form>

<!-- ===== LIST ===== -->
<div class="bike-list">
<?php while($row = mysqli_fetch_assoc($result)) { ?>
    <div class="bike-card">
        <img src="<?php echo $row['main_image']; ?>">

        <div class="price">
            <?php echo number_format($row['price']); ?> VNĐ
        </div>

        <h3><?php echo $row['name']; ?></h3>

        <p class="desc"><?php echo $row['description']; ?></p>

        <div class="meta">
            <span><?php echo $row['frame_size']; ?></span>
            <span><?php echo $row['location']; ?></span>
            <span><?php echo $row['condition_status']; ?></span>
        </div>

        <a href="detail.php?id=<?php echo $row['bicycle_id']; ?>" class="rent-btn">
            XEM CHI TIẾT
        </a>
    </div>
<?php } ?>
</div>
<script>
function toggleDropdown(btn) {
    const dropdown = btn.nextElementSibling;

    document.querySelectorAll(".dropdown-content").forEach(d => {
        if (d !== dropdown) d.style.display = "none";
    });

    dropdown.style.display =
        dropdown.style.display === "block" ? "none" : "block";
}

document.addEventListener("click", function(e) {
    if (!e.target.closest(".dropdown")) {
        document.querySelectorAll(".dropdown-content").forEach(d => {
            d.style.display = "none";
        });
    }
});
</script>
</div>

<?php include "includes/footer.php"; ?>