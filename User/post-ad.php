<?php
session_start();
include_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/auth-handler.php';

// Bước đệm: Nếu chưa đăng nhập, lưu lại trang này và chuyển hướng
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'post-ad.php'; 
    header('Location: login.php?error=Vui lòng đăng nhập để bắt đầu đăng bán xe của bạn.');
    exit();
}

$error = '';

// Lấy danh sách danh mục và thương hiệu từ Database
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");
$brands = $conn->query("SELECT id, name FROM brands ORDER BY name");

// XỬ LÝ KHI NGƯỜI DÙNG BẤM "ĐĂNG BÁN NGAY"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $price = str_replace(',', '', $_POST['price'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $brand_id = (int)($_POST['brand_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $condition_bike = trim($_POST['condition_bike'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $material = trim($_POST['material'] ?? '');

    if (empty($title) || empty($price) || $category_id == 0 || $brand_id == 0) {
        $error = 'Vui lòng điền đầy đủ các thông tin bắt buộc (*).';
    } else {
        // 1. Chèn thông tin xe vào bảng bikes
        $stmt = $conn->prepare("INSERT INTO bikes (user_id, category_id, brand_id, title, price, description, location, condition_bike, color, material, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available')");
        $stmt->bind_param("iiisdsssss", $user_id, $category_id, $brand_id, $title, $price, $description, $location, $condition_bike, $color, $material);
        
        if ($stmt->execute()) {
            $bike_id = $stmt->insert_id;
            $stmt->close();

            // 2. Xử lý upload ảnh
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $uploadDir = __DIR__ . '/uploads/bikes/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                foreach ($_FILES['images']['name'] as $key => $name) {
                    if ($_FILES['images']['error'][$key] === 0) {
                        $ext = pathinfo($name, PATHINFO_EXTENSION);
                        $fileName = time() . '_' . rand(100, 999) . '.' . $ext;
                        $destPath = $uploadDir . $fileName;
                        $dbPath = 'uploads/bikes/' . $fileName;

                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $destPath)) {
                            $is_primary = ($key === 0) ? 1 : 0;
                            $img_stmt = $conn->prepare("INSERT INTO bike_images (bike_id, image_url, is_primary) VALUES (?, ?, ?)");
                            $img_stmt->bind_param("isi", $bike_id, $dbPath, $is_primary);
                            $img_stmt->execute();
                            $img_stmt->close();
                        }
                    }
                }
            }
            // 3. Thông báo và chuyển hướng sang trang Chợ xe cũ (classifieds.php)
            echo "<script>
                alert('Chúc mừng! Xe của bạn đã được đăng bán thành công.');
                window.location.href = 'classifieds.php';
            </script>";
            exit();
        } else {
            $error = 'Lỗi hệ thống: ' . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin đăng bán - GreenRide</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .post-ad-wrapper { max-width: 800px; margin: 40px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .step-panel { display: none; }
        .step-panel.active { display: block; animation: slideIn 0.4s ease-out; }
        .form-label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; }
        .form-input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 20px; font-size: 16px; transition: border-color 0.3s; }
        .form-input:focus { border-color: #2f5d62; outline: none; }
        .btn-nav-group { display: flex; justify-content: space-between; margin-top: 30px; }
        .btn-step { padding: 12px 30px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-next { background: #F57C00; color: #fff; }
        .btn-prev { background: #e0e0e0; color: #555; }
        .stepper { display: flex; justify-content: center; align-items: center; margin-bottom: 40px; }
        .step-circle { width: 40px; height: 40px; border-radius: 50%; background: #eee; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #999; z-index: 2; transition: all 0.3s ease; }
        .step-circle.active { background: #2f5d62; color: #fff; }
        .step-line { width: 80px; height: 4px; background: #eee; z-index: 1; transition: all 0.3s ease; margin: 0 -2px; }
        .step-line.active { background: #2f5d62; }
        .in-page-error {
            color: #d93025;
            background-color: #fce8e6;
            border: 1px solid #fad2cf;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
            display: none; /* Ẩn theo mặc định */
        }
        @keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="post-ad-wrapper">
    <div class="stepper">
        <div class="step-circle active" id="c1">1</div>
        <div class="step-line" id="l1"></div> <div class="step-circle" id="c2">2</div>
        <div class="step-line" id="l2"></div> <div class="step-circle" id="c3">3</div>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <!-- Hộp chứa thông báo lỗi thay cho alert -->
    <div id="jsErrorBox" class="in-page-error"></div>

    <form method="POST" enctype="multipart/form-data" id="adForm">
        
        <div class="step-panel active" id="step1">
            <h2 style="margin-bottom: 20px; color: #2f5d62;">Bước 1: Thông tin xe cơ bản</h2>
            
            <label class="form-label">Tên chiếc xe đạp *</label>
            <input type="text" name="title" class="form-input" required placeholder="VD: Trek Domane SL 6">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label class="form-label">Loại xe *</label>
                    <select name="category_id" class="form-input" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php while($c = $categories->fetch_assoc()) echo "<option value='{$c['id']}'>{$c['name']}</option>"; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Thương hiệu *</label>
                    <select name="brand_id" class="form-input" required>
                        <option value="">-- Chọn thương hiệu --</option>
                        <?php while($b = $brands->fetch_assoc()) echo "<option value='{$b['id']}'>{$b['name']}</option>"; ?>
                    </select>
                </div>
            </div>

            <div class="btn-nav-group">
                <span></span>
                <!-- Truyền thêm tham số 1 để báo cho hàm biết đang ở bước 1 -->
                <button type="button" class="btn-step btn-next" onclick="goTo(2, 1)">Tiếp theo <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <div class="step-panel" id="step2">
            <h2 style="margin-bottom: 20px; color: #2f5d62;">Bước 2: Tình trạng & Giá</h2>
            
            <label class="form-label">Giá bán mong muốn (VNĐ) *</label>
            <input type="number" name="price" class="form-input" required placeholder="VD: 15000000">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label class="form-label">Tình trạng</label>
                    <input type="text" name="condition_bike" class="form-input" placeholder="VD: Mới 95%, Đã đi 500km">
                </div>
                <div>
                    <label class="form-label">Màu sắc</label>
                    <input type="text" name="color" class="form-input" placeholder="VD: Xanh ngọc">
                </div>
            </div>

            <label class="form-label">Chất liệu khung</label>
            <input type="text" name="material" class="form-input" placeholder="VD: Carbon Fiber">

            <div class="btn-nav-group">
                <!-- Khi lùi lại không cần validate, chỉ cần truyền bước đích -->
                <button type="button" class="btn-step btn-prev" onclick="goTo(1)"><i class="fas fa-arrow-left"></i> Quay lại</button>
                <button type="button" class="btn-step btn-next" onclick="goTo(3, 2)">Tiếp theo <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <div class="step-panel" id="step3">
            <h2 style="margin-bottom: 20px; color: #2f5d62;">Bước 3: Hình ảnh & Mô tả</h2>
            
            <label class="form-label">Tải ảnh xe lên (Chọn tối thiểu 1 ảnh) *</label>
            <input type="file" name="images[]" multiple accept="image/*" class="form-input" required>

            <label class="form-label">Vị trí xem xe (Thành phố/Quận)</label>
            <input type="text" name="location" class="form-input" placeholder="VD: Quận Bình Thạnh, TP.HCM">

            <label class="form-label">Mô tả thêm</label>
            <textarea name="description" class="form-input" rows="4" placeholder="Hãy viết thêm về lịch sử xe, phụ kiện tặng kèm..."></textarea>

            <div class="btn-nav-group">
                <button type="button" class="btn-step btn-prev" onclick="goTo(2)"><i class="fas fa-arrow-left"></i> Quay lại</button>
                <button type="submit" class="btn-step btn-next" style="background: #2f5d62;">HOÀN TẤT & ĐĂNG BÁN</button>
            </div>
        </div>

    </form>
</div>

<script>
function goTo(nextStep, currentStep = null) {
    const errorBox = document.getElementById('jsErrorBox');
    
    // Luôn ẩn hộp thông báo lỗi mỗi khi bắt đầu chuyển bước
    errorBox.style.display = 'none';
    errorBox.innerHTML = '';

    // 1. Kiểm tra dữ liệu bị bỏ trống nếu người dùng đang tiến lên bước tiếp theo
    if (currentStep !== null) {
        const currentPanel = document.getElementById('step' + currentStep);
        const requiredInputs = currentPanel.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.style.borderColor = '#d93025'; // Bôi đỏ ô bị thiếu
            } else {
                input.style.borderColor = '#ccc'; // Trả lại màu bình thường
            }
        });

        if (!isValid) {
            // Hiển thị lỗi ngay trên trang thay vì dùng alert()
            errorBox.innerHTML = '<i class="fas fa-exclamation-circle"></i> Vui lòng điền đầy đủ các thông tin bắt buộc (*) được viền đỏ trước khi tiếp tục.';
            errorBox.style.display = 'block';
            
            // Tự động cuộn trang lên chỗ thông báo lỗi để người dùng dễ thấy
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return; // Dừng hàm, không cho chuyển giao diện
        }
    }

    // 2. Nếu dữ liệu hợp lệ, tiến hành ẩn hiện các bảng panel
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.step-circle').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.step-line').forEach(l => l.classList.remove('active'));
    
    // Hiện panel đích
    document.getElementById('step' + nextStep).classList.add('active');
    
    // Tô màu thanh tiến trình cho đến bước hiện tại
    for(let i = 1; i <= nextStep; i++) {
        document.getElementById('c' + i).classList.add('active');
        if(i < nextStep) {
            document.getElementById('l' + i).classList.add('active');
        }
    }
}
</script>