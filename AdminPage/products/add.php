<?php
require_once '../inc/auth.php';
require_once '../inc/header.php';

// Lấy danh sách danh mục và thương hiệu hiện có
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT id, username, email FROM users WHERE role = 'user' OR role = 'admin' ORDER BY username")->fetchAll();

// Xử lý khi submit form
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $title = trim($_POST['title'] ?? '');
    $price = str_replace(',', '', $_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $condition_bike = trim($_POST['condition_bike'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $warranty = trim($_POST['warranty'] ?? '');
    $material = trim($_POST['material'] ?? '');
    $status = $_POST['status'] ?? 'available';
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    // Xử lý danh mục (có thể là id cũ hoặc tên mới)
    $category_id = 0;
    if (!empty($_POST['category_id']) && is_numeric($_POST['category_id'])) {
        $category_id = (int)$_POST['category_id'];
    } elseif (!empty($_POST['new_category'])) {
        $newCat = trim($_POST['new_category']);
        // Kiểm tra xem danh mục đã tồn tại chưa
        $check = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $check->execute([$newCat]);
        $exist = $check->fetch();
        if ($exist) {
            $category_id = $exist['id'];
        } else {
            $insert = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $insert->execute([$newCat]);
            $category_id = $pdo->lastInsertId();
        }
    }
    // Xử lý thương hiệu
    $brand_id = 0;
    if (!empty($_POST['brand_id']) && is_numeric($_POST['brand_id'])) {
        $brand_id = (int)$_POST['brand_id'];
    } elseif (!empty($_POST['new_brand'])) {
        $newBrand = trim($_POST['new_brand']);
        $check = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
        $check->execute([$newBrand]);
        $exist = $check->fetch();
        if ($exist) {
            $brand_id = $exist['id'];
        } else {
            $insert = $pdo->prepare("INSERT INTO brands (name) VALUES (?)");
            $insert->execute([$newBrand]);
            $brand_id = $pdo->lastInsertId();
        }
    }
    // Validate
    if ($title && $price > 0 && $category_id && $brand_id && $user_id) {
        // Insert vào bảng bikes
        $insertBike = $pdo->prepare("
            INSERT INTO bikes (user_id, category_id, brand_id, title, price, description, location, condition_bike, color, warranty, material, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $insertBike->execute([$user_id, $category_id, $brand_id, $title, $price, $description, $location, $condition_bike, $color, $warranty, $material, $status]);
        $bike_id = $pdo->lastInsertId();
        
        // Xử lý upload ảnh
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $uploadedFiles = [];
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $files = $_FILES['images'];
            $totalFiles = count($files['name']);
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (in_array($ext, $allowed)) {
                        $newName = time() . '_' . uniqid() . '.' . $ext;
                        $destination = $uploadDir . $newName;
                        if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
                            $isPrimary = ($i == 0) ? 1 : 0; // Ảnh đầu tiên là ảnh chính
                            $insertImg = $pdo->prepare("INSERT INTO bike_images (bike_id, image_url, is_primary) VALUES (?, ?, ?)");
                            $insertImg->execute([$bike_id, 'uploads/' . $newName, $isPrimary]);
                            $uploadedFiles[] = $newName;
                        }
                    }
                }
            }
        }
        
        $success = "Thêm sản phẩm thành công! Bạn có thể <a href='edit.php?id=$bike_id'>chỉnh sửa</a> hoặc <a href='add.php'>thêm tiếp</a>.";
        // Reset form sau thành công (không giữ file upload, nhưng giữ lại dữ liệu text có thể xóa)
        // Ở đây ta không reset form tự động để tránh mất thông báo, nhưng có thể dùng JS xóa file preview.
    } else {
        $error = "Vui lòng điền đầy đủ thông tin bắt buộc: Tiêu đề, Giá > 0, Danh mục, Thương hiệu, Người bán.";
    }
}
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
        max-width: 1100px;
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
    .upload-area {
        border: 2px dashed var(--border-light);
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.2s;
        cursor: pointer;
        background: #fafafa;
    }
    .upload-area:hover {
        border-color: var(--primary-orange);
        background: rgba(245, 124, 0, 0.05);
    }
    .row-flex {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .flex-grow {
        flex: 1;
    }
    @media (max-width: 768px) {
        .preview-item {
            width: calc(33% - 0.6rem);
        }
    }
</style>

<div class="container-fluid px-4 form-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0" style="border-left: 5px solid #F57C00; padding-left: 1rem;">
            <i class="fas fa-plus-circle me-2" style="color: #F57C00;"></i> Thêm sản phẩm mới
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

    <form method="post" enctype="multipart/form-data" id="addForm">
        <div class="row">
            <!-- Cột chính: thông tin xe -->
            <div class="col-lg-8">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fas fa-info-circle"></i> Thông tin cơ bản
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tiêu đề *</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Giá (VNĐ) *</label>
                                <input type="text" name="price" class="form-control" required placeholder="VD: 15000000">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Mô tả</label>
                                <textarea name="description" rows="4" class="form-control" placeholder="Mô tả chi tiết về xe..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Danh mục *</label>
                                <select name="category_id" class="form-select" id="categorySelect">
                                    <option value="">-- Chọn danh mục có sẵn --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="mt-2">
                                    <input type="text" name="new_category" class="form-control" placeholder="Hoặc nhập tên danh mục mới">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Thương hiệu *</label>
                                <select name="brand_id" class="form-select" id="brandSelect">
                                    <option value="">-- Chọn thương hiệu có sẵn --</option>
                                    <?php foreach ($brands as $br): ?>
                                        <option value="<?= $br['id'] ?>"><?= htmlspecialchars($br['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="mt-2">
                                    <input type="text" name="new_brand" class="form-control" placeholder="Hoặc nhập tên thương hiệu mới">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Người bán *</label>
                                <select name="user_id" class="form-select" required>
                                    <option value="">-- Chọn người bán --</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username'] . ' (' . $u['email'] . ')') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="available">Còn bán</option>
                                    <option value="sold">Đã bán</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fas fa-cogs"></i> Thông số kỹ thuật
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Vị trí / Địa chỉ</label>
                                <input type="text" name="location" class="form-control" placeholder="Quận/Huyện, TP">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tình trạng xe</label>
                                <input type="text" name="condition_bike" class="form-control" placeholder="Như mới, đã qua sử dụng...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Màu sắc</label>
                                <input type="text" name="color" class="form-control" placeholder="Đen, trắng, xanh...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bảo hành</label>
                                <input type="text" name="warranty" class="form-control" placeholder="12 tháng, 24 tháng...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Chất liệu khung</label>
                                <input type="text" name="material" class="form-control" placeholder="Nhôm, thép, carbon...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: upload ảnh -->
            <div class="col-lg-4">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fas fa-images"></i> Hình ảnh sản phẩm
                    </div>
                    <div class="p-3">
                        <div class="upload-area" onclick="document.getElementById('imageInput').click()">
                            <i class="fas fa-cloud-upload-alt fa-2x" style="color: #F57C00;"></i>
                            <p class="mb-0 mt-2">Nhấp để chọn ảnh (có thể chọn nhiều)</p>
                            <small class="text-muted">Hỗ trợ JPG, PNG, GIF, WebP. Ảnh đầu tiên sẽ là ảnh chính.</small>
                            <input type="file" name="images[]" id="imageInput" multiple accept="image/*" style="display:none">
                        </div>
                        <div id="previewContainer" class="preview-grid"></div>
                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-orange w-100"><i class="fas fa-save"></i> Thêm sản phẩm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Xử lý preview ảnh và danh sách file
const fileInput = document.getElementById('imageInput');
const previewContainer = document.getElementById('previewContainer');
let selectedFiles = [];

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

function updateFileInput() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => {
        dataTransfer.items.add(file);
    });
    fileInput.files = dataTransfer.files;
}

fileInput.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    selectedFiles = selectedFiles.concat(files);
    updatePreview();
    updateFileInput();
});

// Khởi tạo
updatePreview();

// Xử lý để nếu nhập new_category thì bỏ chọn select, tương tự new_brand
const catSelect = document.getElementById('categorySelect');
const newCatInput = document.querySelector('input[name="new_category"]');
newCatInput.addEventListener('input', function() {
    if (this.value.trim() !== '') catSelect.value = '';
});
catSelect.addEventListener('change', function() {
    if (this.value !== '') newCatInput.value = '';
});

const brandSelect = document.getElementById('brandSelect');
const newBrandInput = document.querySelector('input[name="new_brand"]');
newBrandInput.addEventListener('input', function() {
    if (this.value.trim() !== '') brandSelect.value = '';
});
brandSelect.addEventListener('change', function() {
    if (this.value !== '') newBrandInput.value = '';
});
</script>

<?php require_once '../inc/footer.php'; ?>
