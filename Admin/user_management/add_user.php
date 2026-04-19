<?php require_once __DIR__ . "/../config.php"; ?>

<?php
$message = "";
$messageType = "";

$name = "";
$email = "";
$role = "user";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    // ===== VALIDATE =====
    if (empty($name) || empty($email) || empty($password_input) || empty($confirm_password)) {
        $message = "Vui lòng nhập đầy đủ thông tin!";
        $messageType = "error";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email không hợp lệ!";
        $messageType = "error";
    }
    elseif ($password_input !== $confirm_password) {
        $message = "Mật khẩu xác nhận không khớp!";
        $messageType = "error";
    }
    elseif (!in_array($role, ['user', 'admin'])) {
        $message = "Role không hợp lệ!";
        $messageType = "error";
    }
    else {
        // ===== CHECK EMAIL EXIST =====
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if (mysqli_num_rows($checkResult) > 0) {
            $message = "Email đã tồn tại!";
            $messageType = "error";
        } else {
            $password = password_hash($password_input, PASSWORD_DEFAULT);

            $insertStmt = mysqli_prepare($conn, "
                INSERT INTO users (name, email, password, role, active)
                VALUES (?, ?, ?, ?, 1)
            ");

            mysqli_stmt_bind_param($insertStmt, "ssss", $name, $email, $password, $role);

            if (mysqli_stmt_execute($insertStmt)) {
                $message = "Thêm user thành công!";
                $messageType = "success";

                // reset form
                $name = "";
                $email = "";
                $role = "user";
            } else {
                $message = "Có lỗi xảy ra khi thêm user!";
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add User</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f6fa;
    margin: 0;
    padding: 20px;
}

/* ===== TITLE ===== */
.page-title {
    font-size: 26px;
    font-weight: bold;
    margin-bottom: 10px;
    text-align: center;
}

/* ===== BACK BUTTON ===== */
.back-link {
    display: block;
    margin-bottom: 20px;
    text-decoration: none;
    color: #6c5ce7;
    font-weight: 600;
    text-align: center;
}

/* ===== CARD ===== */ 
.card-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 10px;
}

.card {
    width: 100%;
    max-width: 500px;
    background: white;
    padding: 24px;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,.06);
}

.btn-center {
    text-align: center;
    margin-top: 10px;
}
.card {
    max-width: 500px;
    background: white;
    padding: 24px;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,.06);
}

/* ===== FORM ===== */
.form-group {
    margin-bottom: 16px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
}

input, select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #ddd;
    font-size: 14px;
    box-sizing: border-box;
}

input:focus, select:focus {
    outline: none;
    border-color: #f39c12;
}

/* ===== BUTTON ===== */
.btn {
    background: #f39c12;
    color: white;
    border: none;
    padding: 12px 18px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
}

.btn:hover {
    background: #e67e22;
}

/* ===== MESSAGE ===== */
.message {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 14px;
}

.message.error {
    background: #fdecea;
    color: #c0392b;
}

.message.success {
    background: #eafaf1;
    color: #27ae60;
}

.header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mini-back {
    font-size: 13px;
    color: #6b7280;
    text-decoration: none;
}

.mini-back:hover {
    color: #2563eb;
}


</style>

</head>
<body>

<div class="page-title">Add New User</div>

<div class="header-top">
    <a href="index.php?page=all_users" class="mini-back"></a>
</div>

<div class="card-wrapper">
    <div class="card">

        <?php if (!empty($message)): ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name"
                    value="<?= htmlspecialchars($name) ?>"
                    placeholder="Enter full name" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="Enter email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password"
                    placeholder="Enter password" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password"
                    placeholder="Confirm password" required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="user" <?= ($role == 'user') ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= ($role == 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <div class="btn-center">
                <button type="submit" class="btn">Add User</button>
            </div>

        </form>
    </div>
</div>

</body>
</html>