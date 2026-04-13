<?php
require_once '../inc/auth.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Lấy thông tin sản phẩm hiện tại
$stmt = $pdo->prepare("SELECT * FROM bikes WHERE id = ?");
$stmt->execute([$id]);
$bike = $stmt->fetch();
if (!$bike) {
    header('Location: index.php');
    exit;
}

// Lấy danh sách danh mục và thương hiệu
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT id, username, email FROM users WHERE role = 'user' OR role = 'admin' ORDER BY username")->fetchAll();

// Lấy ảnh của sản phẩm
$images = $pdo->prepare("SELECT * FROM bike_images WHERE bike_id = ? ORDER BY is_primary DESC, id ASC");
$images->execute([$id]);
$bikeImages = $images->fetchAll();

// Xử lý xóa ảnh (ngay lập tức)
if (isset($_GET['delete_img']) && is_numeric($_GET['delete_img'])) {
    $imgId = (int)$_GET['delete_img'];
    $stmtImg = $pdo->prepare("SELECT image_url FROM bike_images WHERE id = ? AND bike_id = ?");
    $stmtImg->execute([$imgId, $id]);
    $img = $stmtImg->fetch();
    if ($img) {
        $filePath = '../' . $img['image_url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $pdo->prepare("DELETE FROM bike_images WHERE id = ?")->execute([$imgId]);
        // Nếu ảnh xóa là ảnh chính, chuyển ảnh khác làm chính
        $checkPrimary = $pdo->prepare("SELECT COUNT(*) FROM bike_images WHERE bike_id = ? AND is_primary = 1");
        $checkPrimary->execute([$id]);
        if ($checkPrimary->fetchColumn() == 0) {
            $pdo->prepare("UPDATE bike_images SET is_primary = 1 WHERE bike_id = ? LIMIT 1")->execute([$id]);
        }
    }
    header("Location: edit.php?id=$id");
    exit;
}

// Xử lý đặt ảnh chính
if (isset($_GET['set_primary']) && is_numeric($_GET['set_primary'])) {
    $imgId = (int)$_GET['set_primary'];
    $pdo->prepare("UPDATE bike_images SET is_primary = 0 WHERE bike_id = ?")->execute([$id]);
    $pdo->prepare("UPDATE bike_images SET is_primary = 1 WHERE id = ? AND bike_id = ?")->execute([$imgId, $id]);
    header("Location: edit.php?id=$id");
    exit;
}

// Xử lý cập nhật sản phẩm (bao gồm upload ảnh mới)
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $price = str_replace(',', '', $_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $condition_bike = trim($_POST['condition_bike'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $warranty = trim($_POST['warranty'] ?? '');
    $material = trim($_POST['material'] ?? '');
    $status = $_POST['status'] ?? 'available';
    $category_id = (int)($_POST['category_id'] ?? 0);
    $brand_id = (int)($_POST['brand_id'] ?? 0);
    $user_id = (int)($_POST['user_id'] ?? 0);

    if ($title && $price > 0 && $category_id && $brand_id && $user_id) {
        $update = $pdo->prepare("
            UPDATE bikes SET 
                title = ?, price = ?, description = ?, location = ?, 
                condition_bike = ?, color = ?, warranty = ?, material = ?, 
                status = ?, category_id = ?, brand_id = ?, user_id = ? 
            WHERE id = ?
        ");
        $update->execute([$title, $price, $description, $location, $condition_bike, $color, $warranty, $material, $status, $category_id, $brand_id, $user_id, $id]);
        
        // Xử lý upload ảnh mới (từ file input)
        if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $files = $_FILES['new_images'];
            $totalFiles = count($files['name']);
            $uploadedCount = 0;
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                    $newName = time() . '_' . uniqid() . '.' . $ext;
                    $destination = $uploadDir . $newName;
                    if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
                        $checkPrimary = $pdo->prepare("SELECT COUNT(*) FROM bike_images WHERE bike_id = ?");
                        $checkPrimary->execute([$id]);
                        $hasImages = $checkPrimary->fetchColumn();

                        $isPrimary = ($hasImages == 0 && $uploadedCount == 0) ? 1 : 0;
                        $insImg = $pdo->prepare("INSERT INTO bike_images (bike_id, image_url, is_primary) VALUES (?, ?, ?)");
                        $insImg->execute([$id, 'uploads/' . $newName, $isPrimary]);
                        $uploadedCount++;
                    }
                }
            }
            // Nếu chưa có ảnh chính, set ảnh đầu tiên làm chính
            $checkPrimary = $pdo->prepare("SELECT COUNT(*) FROM bike_images WHERE bike_id = ? AND is_primary = 1");
            $checkPrimary->execute([$id]);
            if ($checkPrimary->fetchColumn() == 0) {
                $pdo->prepare("UPDATE bike_images SET is_primary = 1 WHERE bike_id = ? ORDER BY id LIMIT 1")->execute([$id]);
            }
        }
        
        $success = "Cập nhật sản phẩm thành công!";
        // Refresh dữ liệu
        $stmt = $pdo->prepare("SELECT * FROM bikes WHERE id = ?");
        $stmt->execute([$id]);
        $bike = $stmt->fetch();
        $images = $pdo->prepare("SELECT * FROM bike_images WHERE bike_id = ? ORDER BY is_primary DESC, id ASC");
        $images->execute([$id]);
        $bikeImages = $images->fetchAll();
    } else {
        $error = "Vui lòng điền đầy đủ thông tin bắt buộc (Tiêu đề, Giá >0, Danh mục, Thương hiệu, Người bán).";
    }
}
require_once '../inc/header.php';
?>

<style>
    :root {
        --primary-orange: #F57C00;
        --bg-gray: #F5F5F5;
        --text-dark: #263238;
        --border-light: #e0e0e0;
        --card-shadow: 0 6px 12px rgba(0,0,0,0.05);
        --hover-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    body {
        background-color: var(--bg-gray);
        color: var(--text-dark);
    }
    .form-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    .card-custom {
        background: white;
        border-radius: 1.2rem;
        border: none;
        box-shadow: var(--card-shadow);
        transition: all 0.2s;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .card-custom:hover {
        box-shadow: var(--hover-shadow);
    }
    .card-header-custom {
        background: white;
        border-bottom: 2px solid var(--primary-orange);
        padding: 1rem 1.5rem;
        font-weight: 700;
        font-size: 1.2rem;
    }
    .card-header-custom i {
        color: var(--primary-orange);
        margin-right: 8px;
    }
    .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.3rem;
    }
    .form-control, .form-select {
        border-radius: 0.75rem;
        border: 1px solid var(--border-light);
        padding: 0.6rem 1rem;
        transition: all 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 3px rgba(245, 124, 0, 0.1);
        outline: none;
    }
    .btn-orange {
        background-color: var(--primary-orange);
        border: none;
        color: white;
        font-weight: 600;
        padding: 0.6rem 1.5rem;
        border-radius: 2rem;
        transition: all 0.2s;
    }
    .btn-orange:hover {
        background-color: #e66a00;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 124, 0, 0.3);
    }
    .btn-outline-orange {
        border: 1px solid var(--primary-orange);
        background: transparent;
        color: var(--primary-orange);
        border-radius: 2rem;
        padding: 0.3rem 1rem;
        transition: all 0.2s;
    }
    .btn-outline-orange:hover {
        background-color: var(--primary-orange);
        color: white;
    }
    .image-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1rem;
    }
    .image-item {
        position: relative;
        width: 150px;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        background: white;
        transition: transform 0.2s;
    }
    .image-item:hover {
        transform: translateY(-4px);
    }
    .image-item img {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }
    .image-actions {
        padding: 0.5rem;
        display: flex;
        justify-content: space-between;
        gap: 0.3rem;
        background: #f9f9f9;
    }
    .primary-badge {
        position: absolute;
        top: 5px;
        left: 5px;
        background: var(--primary-orange);
        color: white;
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 1rem;
        font-weight: bold;
    }
    .upload-area {
        border: 2px dashed var(--border-light);
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.2s;
        cursor: pointer;
    }
    .upload-area:hover {
        border-color: var(--primary-orange);
        background: rgba(245, 124, 0, 0.05);
    }
    .preview-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.8rem;
        margin-top: 1rem;
    }
    .preview-item {
        position: relative;
        width: 100px;
        border-radius: 0.8rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        background: #f8f9fa;
    }
    .preview-item img {
        width: 100%;
        height: 80px;
        object-fit: cover;
    }
    .remove-preview {
        position: absolute;
        top: 2px;
        right: 2px;
        background: rgba(220,53,69,0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        font-size: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .remove-preview:hover {
        background: #c82333;
        transform: scale(1.1);
    }
    @media (max-width: 768px) {
        .image-item {
            width: calc(33% - 0.7rem);
        }
    }
</style>

<div class="container-fluid px-4 form-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0" style="border-left: 5px solid #F57C00; padding-left: 1rem;">
            <i class="fas fa-edit me-2" style="color: #F57C00;"></i> Chỉnh sửa sản phẩm
        </h2>
        <a href="index.php" class="btn btn-outline-orange"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="editForm">
        <div class="row">
            <!-- Thông tin cơ bản -->
            <div class="col-lg-8">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fas fa-info-circle"></i> Thông tin xe đạp
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tiêu đề *</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($bike['title']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Giá (VNĐ) *</label>
                                <input type="text" name="price" class="form-control" value="<?= number_format($bike['price'], 0, '', '') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Mô tả</label>
                                <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($bike['description']) ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Danh mục *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $bike['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Thương hiệu *</label>
                                <select name="brand_id" class="form-select" required>
                                    <option value="">-- Chọn thương hiệu --</option>
                                    <?php foreach ($brands as $br): ?>
                                        <option value="<?= $br['id'] ?>" <?= $bike['brand_id'] == $br['id'] ? 'selected' : '' ?>><?= htmlspecialchars($br['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Người bán *</label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">-- Chọn người bán --</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= $bike['user_id'] == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['username'] . ' (' . $u['email'] . ')') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="available" <?= $bike['status'] == 'available' ? 'selected' : '' ?>>Còn bán</option>
                                    <option value="sold" <?= $bike['status'] == 'sold' ? 'selected' : '' ?>>Đã bán</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chi tiết kỹ thuật -->
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fas fa-cogs"></i> Thông số kỹ thuật
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Vị trí / Địa chỉ</label>
                                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($bike['location']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tình trạng xe</label>
                                <input type="text" name="condition_bike" class="form-control" value="<?= htmlspecialchars($bike['condition_bike']) ?>" placeholder="Như mới, đã qua sử dụng...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Màu sắc</label>
                                <input type="text" name="color" class="form-control" value="<?= htmlspecialchars($bike['color']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bảo hành</label>
                                <input type="text" name="warranty" class="form-control" value="<?= htmlspecialchars($bike['warranty']) ?>" placeholder="12 tháng...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Chất liệu khung</label>
                                <input type="text" name="material" class="form-control" value="<?= htmlspecialchars($bike['material']) ?>" placeholder="Nhôm, thép, carbon...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quản lý ảnh bên phải -->
            <div class="col-lg-4">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fas fa-images"></i> Hình ảnh sản phẩm
                    </div>
                    <div class="p-3">
                        <!-- Ảnh cũ -->
                        <?php if (count($bikeImages) > 0): ?>
                            <label class="form-label">Ảnh hiện tại</label>
                            <div class="image-grid">
                                <?php foreach ($bikeImages as $img): ?>
                                    <div class="image-item">
                                        <?php if ($img['is_primary']): ?>
                                            <div class="primary-badge"><i class="fas fa-star"></i> Chính</div>
                                        <?php endif; ?>
                                        <img src="../<?= $img['image_url'] ?>" alt="Ảnh xe">
                                        <div class="image-actions">
                                            <?php if (!$img['is_primary']): ?>
                                                <a href="?id=<?= $id ?>&set_primary=<?= $img['id'] ?>" class="btn btn-sm btn-outline-orange" title="Đặt làm ảnh chính"><i class="fas fa-crown"></i></a>
                                            <?php else: ?>
                                                <span class="btn btn-sm disabled" style="opacity:0.5"><i class="fas fa-check"></i></span>
                                            <?php endif; ?>
                                            <a href="?id=<?= $id ?>&delete_img=<?= $img['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa ảnh này?')"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-2">Chưa có ảnh nào.</div>
                        <?php endif; ?>
                        
                        <!-- Khu vực thêm ảnh mới -->
                        <label class="form-label mt-3">Thêm ảnh mới</label>
                        <div class="upload-area" onclick="document.getElementById('imageInput').click()">
                            <i class="fas fa-cloud-upload-alt fa-2x" style="color: #F57C00;"></i>
                            <p class="mb-0 mt-2">Nhấp để chọn ảnh (có thể chọn nhiều)</p>
                            <small class="text-muted">Hỗ trợ JPG, PNG, GIF</small>
                            <input type="file" name="new_images[]" id="imageInput" multiple accept="image/*" style="display:none">
                        </div>
                        
                        <!-- Khu vực hiển thị preview ảnh mới -->
                        <div id="previewContainer" class="preview-grid"></div>
                        
                        <div class="mt-3 text-end">
                            <button type="submit" name="submit" class="btn btn-orange w-100"><i class="fas fa-save"></i> Lưu thay đổi</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Quản lý danh sách file đã chọn và preview
const fileInput = document.getElementById('imageInput');
const previewContainer = document.getElementById('previewContainer');
let selectedFiles = []; // Lưu trữ File objects

// Hàm cập nhật preview và file input
function updatePreview() {
    previewContainer.innerHTML = '';
    if (selectedFiles.length === 0) {
        previewContainer.style.display = 'none';
        return;
    }
    previewContainer.style.display = 'flex';
    
    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        const previewDiv = document.createElement('div');
        previewDiv.className = 'preview-item';
        previewDiv.setAttribute('data-index', index);
        
        const img = document.createElement('img');
        reader.onload = function(e) {
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
        
        const removeBtn = document.createElement('button');
        removeBtn.innerHTML = '✕';
        removeBtn.className = 'remove-preview';
        removeBtn.onclick = (e) => {
            e.stopPropagation();
            selectedFiles.splice(index, 1);
            updatePreview();
            updateFileInput();
        };
        
        previewDiv.appendChild(img);
        previewDiv.appendChild(removeBtn);
        previewContainer.appendChild(previewDiv);
    });
}

// Cập nhật lại file input dựa trên selectedFiles (dùng DataTransfer)
function updateFileInput() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => {
        dataTransfer.items.add(file);
    });
    fileInput.files = dataTransfer.files;
}

// Lắng nghe sự kiện change của file input
fileInput.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    selectedFiles = selectedFiles.concat(files);
    updatePreview();
    updateFileInput();
});

// Khởi tạo (không có file mặc định)
updatePreview();
</script>

<?php require_once '../inc/footer.php'; ?>