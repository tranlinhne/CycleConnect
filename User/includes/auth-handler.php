<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config.php';

/* ===== ĐĂNG NHẬP BẰNG EMAIL ===== */
function loginUser($email, $password) {
    global $conn;

    $email = trim($email);
    $password = trim($password);

    if ($email === '' || $password === '') {
        return ['success' => false, 'message' => 'Email và mật khẩu không được để trống'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email không hợp lệ'];
    }

    $stmt = $conn->prepare("
        SELECT id, email, password, full_name, avatar
        FROM users
        WHERE email = ? AND status = 'active'
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác'];
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác'];
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'] ?? '';
    $_SESSION['avatar'] = $user['avatar'] ?? '';
    $_SESSION['logged_in'] = true;

    $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $update_stmt->bind_param("i", $user['id']);
    $update_stmt->execute();
    $update_stmt->close();

    return ['success' => true, 'message' => 'Đăng nhập thành công'];
}

/* ===== ĐĂNG KÝ BẰNG EMAIL ===== */
function registerUser($email, $password, $confirm_password, $phone = '', $full_name = '') {
    global $conn;

    $email = trim($email);
    $password = trim($password);
    $confirm_password = trim($confirm_password);
    $phone = trim($phone);
    $full_name = trim($full_name);

    if ($email === '' || $password === '' || $confirm_password === '') {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email không hợp lệ'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Mật khẩu phải ít nhất 6 ký tự'];
    }

    if ($password !== $confirm_password) {
        return ['success' => false, 'message' => 'Mật khẩu không khớp'];
    }

    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $check_stmt->close();
        return ['success' => false, 'message' => 'Email này đã được đăng ký'];
    }
    $check_stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (email, phone, password, full_name, role, status)
        VALUES (?, ?, ?, ?, 'user', 'active')
    ");
    $stmt->bind_param("ssss", $email, $phone, $hashed_password, $full_name);

    if (!$stmt->execute()) {
        $message = 'Lỗi đăng ký: ' . $conn->error;
        $stmt->close();
        return ['success' => false, 'message' => $message];
    }

    $stmt->close();
    return ['success' => true, 'message' => 'Đăng ký thành công. Vui lòng đăng nhập'];
}

/* ===== QUÊN MẬT KHẨU ===== */
function sendPasswordResetEmail($email) {
    global $conn;

    $email = trim($email);

    if ($email === '') {
        return ['success' => false, 'message' => 'Vui lòng nhập email'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email không hợp lệ'];
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND status = 'active' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Email không tồn tại'];
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    $reset_token = bin2hex(random_bytes(32));
    $reset_token_expire = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $update_stmt = $conn->prepare("
        UPDATE users
        SET reset_token = ?, reset_token_expire = ?
        WHERE id = ?
    ");
    $update_stmt->bind_param("ssi", $reset_token, $reset_token_expire, $user['id']);
    $update_stmt->execute();
    $update_stmt->close();

    $base_path = isset($_SERVER['PHP_SELF']) ? dirname($_SERVER['PHP_SELF']) : '/CycleMarket/User';
    $base_path = str_replace('\\', '/', $base_path);
    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . rtrim($base_path, '/') . "/reset-password.php?token=" . $reset_token;

    $subject = "Yêu cầu đặt lại mật khẩu - GreenRide";
    $message = "Bạn đã yêu cầu đặt lại mật khẩu.<br>";
    $message .= "Nhấp vào liên kết bên dưới để đặt lại mật khẩu của bạn:<br>";
    $message .= "<a href='" . $reset_link . "'>Đặt lại mật khẩu</a><br>";
    $message .= "Liên kết này sẽ hết hạn trong 1 giờ.<br>";
    $message .= "Nếu bạn không yêu cầu này, hãy bỏ qua email này.";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";

    if (mail($email, $subject, $message, $headers)) {
        return ['success' => true, 'message' => 'Email hướng dẫn đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra email'];
    }

    return ['success' => false, 'message' => 'Lỗi gửi email. Vui lòng thử lại sau'];
}

/* ===== RESET MẬT KHẨU ===== */
function resetPassword($token, $password, $confirm_password) {
    global $conn;

    $token = trim($token);
    $password = trim($password);
    $confirm_password = trim($confirm_password);

    if ($token === '' || $password === '' || $confirm_password === '') {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Mật khẩu phải ít nhất 6 ký tự'];
    }

    if ($password !== $confirm_password) {
        return ['success' => false, 'message' => 'Mật khẩu không khớp'];
    }

    $stmt = $conn->prepare("
        SELECT id
        FROM users
        WHERE reset_token = ? AND reset_token_expire > NOW()
        LIMIT 1
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn'];
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $update_stmt = $conn->prepare("
        UPDATE users
        SET password = ?, reset_token = NULL, reset_token_expire = NULL
        WHERE id = ?
    ");
    $update_stmt->bind_param("si", $hashed_password, $user['id']);

    if (!$update_stmt->execute()) {
        $update_stmt->close();
        return ['success' => false, 'message' => 'Lỗi cập nhật mật khẩu'];
    }

    $update_stmt->close();
    return ['success' => true, 'message' => 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập'];
}

/* ===== CHECK LOGIN ===== */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/* ===== LOGOUT ===== */
function logoutUser() {
    session_destroy();
    return ['success' => true, 'message' => 'Đã đăng xuất'];
}

/* ===== LẤY THÔNG TIN USER ===== */
function getUserInfo($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, email, phone, full_name, avatar, address, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user;
}

/* ===== CẬP NHẬT PROFILE ===== */
function updateUserProfile($user_id, $full_name, $phone, $address) {
    global $conn;
    
    $full_name = trim($full_name);
    $phone = trim($phone);
    $address = trim($address);
    
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssi", $full_name, $phone, $address, $user_id);
    
    if (!$stmt->execute()) {
        $stmt->close();
        return ['success' => false, 'message' => 'Lỗi cập nhật thông tin'];
    }
    
    $stmt->close();
    $_SESSION['full_name'] = $full_name;
    
    return ['success' => true, 'message' => 'Cập nhật thông tin thành công'];
}

/* ===== CẬP NHẬT AVATAR ===== */
function updateUserAvatar($user_id, $avatar_path) {
    global $conn;

    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    $stmt->bind_param("si", $avatar_path, $user_id);

    if (!$stmt->execute()) {
        $stmt->close();
        return ['success' => false, 'message' => 'Lỗi cập nhật ảnh đại diện'];
    }

    $stmt->close();
    $_SESSION['avatar'] = $avatar_path;

    return ['success' => true, 'message' => 'Cập nhật ảnh đại diện thành công'];
}

/* ===== LIÊN HỆ ===== */
function saveContactMessage($name, $email, $phone, $subject, $message, $user_id = null) {
    global $conn;

    $name = trim($name);
    $email = trim($email);
    $phone = trim($phone);
    $subject = trim($subject);
    $message = trim($message);

    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email không hợp lệ'];
    }

    $stmt = $conn->prepare("
        INSERT INTO contacts (user_id, name, email, phone, subject, message)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssss", $user_id, $name, $email, $phone, $subject, $message);

    if (!$stmt->execute()) {
        $stmt->close();
        return ['success' => false, 'message' => 'Lỗi gửi tin nhắn'];
    }

    $stmt->close();
    return ['success' => true, 'message' => 'Tin nhắn của bạn đã được gửi. Chúng tôi sẽ liên hệ sớm'];
}

/* ===== ĐỔI MẬT KHẨU ===== */
function changePassword($user_id, $old_password, $new_password, $confirm_password) {
    global $conn;

    $old_password = trim($old_password);
    $new_password = trim($new_password);
    $confirm_password = trim($confirm_password);

    if ($old_password === '' || $new_password === '' || $confirm_password === '') {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
    }

    if (strlen($new_password) < 6) {
        return ['success' => false, 'message' => 'Mật khẩu mới phải ít nhất 6 ký tự'];
    }

    if ($new_password !== $confirm_password) {
        return ['success' => false, 'message' => 'Mật khẩu không khớp'];
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Người dùng không tồn tại'];
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($old_password, $user['password'])) {
        return ['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác'];
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update_stmt->bind_param("si", $hashed_password, $user_id);

    if (!$update_stmt->execute()) {
        $update_stmt->close();
        return ['success' => false, 'message' => 'Lỗi cập nhật mật khẩu'];
    }

    $update_stmt->close();
    return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
}
?>