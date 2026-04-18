<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../config.php';

// Hàm đăng nhập
function loginUser($login, $password) {
    global $conn;
    
    // Validate input
    $login = trim($login);
    $password = trim($password);
    
    if (empty($login) || empty($password)) {
        return ['success' => false, 'message' => 'Tên đăng nhập/Email và mật khẩu không được để trống'];
    }
    
    // Kiểm tra tài khoản tồn tại (đăng nhập bằng username hoặc email)
    $stmt = $conn->prepare("SELECT id, username, email, password, full_name, avatar FROM users WHERE (email = ? OR username = ?) AND status = 'active'");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác'];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác'];
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['avatar'] = $user['avatar'];
    $_SESSION['logged_in'] = true;
    
    // Update last login
    $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $update_stmt->bind_param("i", $user['id']);
    $update_stmt->execute();
    $update_stmt->close();
    
    return ['success' => true, 'message' => 'Đăng nhập thành công'];
}

// Hàm đăng ký
function registerUser($username, $email, $password, $confirm_password, $phone = '', $full_name = '') {
    global $conn;
    
    // Validate input
    $username = trim($username);
    $email = trim($email);
    $password = trim($password);
    $confirm_password = trim($confirm_password);
    $phone = trim($phone);
    $full_name = trim($full_name);
    
    // Kiểm tra dữ liệu bắt buộc
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc'];
    }
    
    // Kiểm tra độ dài username
    if (strlen($username) < 3 || strlen($username) > 50) {
        return ['success' => false, 'message' => 'Tên đăng nhập phải từ 3-50 ký tự'];
    }
    
    // Kiểm tra email hợp lệ
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email không hợp lệ'];
    }
    
    // Kiểm tra độ dài password
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Mật khẩu phải ít nhất 6 ký tự'];
    }
    
    // Kiểm tra mật khẩu khớp
    if ($password !== $confirm_password) {
        return ['success' => false, 'message' => 'Mật khẩu không khớp'];
    }
    
    // Kiểm tra email đã tồn tại
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $check_stmt->close();
        return ['success' => false, 'message' => 'Email này đã được đăng ký'];
    }
    $check_stmt->close();
    
    // Kiểm tra username đã tồn tại
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $check_stmt->close();
        return ['success' => false, 'message' => 'Tên đăng nhập này đã tồn tại'];
    }
    $check_stmt->close();
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, full_name, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("sssss", $username, $email, $hashed_password, $phone, $full_name);
    
    if (!$stmt->execute()) {
        $stmt->close();
        return ['success' => false, 'message' => 'Lỗi đăng ký: ' . $conn->error];
    }
    
    $stmt->close();
    return ['success' => true, 'message' => 'Đăng ký thành công. Vui lòng đăng nhập'];
}

// Hàm gửi email reset password
function sendPasswordResetEmail($email) {
    global $conn;
    
    $email = trim($email);
    
    if (empty($email)) {
        return ['success' => false, 'message' => 'Vui lòng nhập email'];
    }
    
    // Kiểm tra email tồn tại
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Email không tồn tại'];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Generate reset token
    $reset_token = bin2hex(random_bytes(32));
    $reset_token_expire = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Save token to database
    $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expire = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $reset_token, $reset_token_expire, $user['id']);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Send email
    $base_path = isset($_SERVER['PHP_SELF']) ? dirname($_SERVER['PHP_SELF']) : '/CycleMarket/User';
    $base_path = str_replace('\\', '/', $base_path);
    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . rtrim($base_path, '/') . "/reset-password.php?token=" . $reset_token;
    $subject = "Yêu cầu đặt lại mật khẩu - GreenRide";
    $message = "Bạn đã yêu cầu đặt lại mật khẩu.<br>";
    $message .= "Nhấp vào liên kết bên dưới để đặt lại mật khẩu của bạn:<br>";
    $message .= "<a href='" . $reset_link . "'>Đặt lại mật khẩu</a><br>";
    $message .= "Liên kết này sẽ hết hạn trong 1 giờ.<br>";
    $message .= "Nếu bạn không yêu cầu này, hãy bỏ qua email này.";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    
    if (mail($email, $subject, $message, $headers)) {
        return ['success' => true, 'message' => 'Email hướng dẫn đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra email'];
    } else {
        return ['success' => false, 'message' => 'Lỗi gửi email. Vui lòng thử lại sau'];
    }
}

// Hàm reset password
function resetPassword($token, $password, $confirm_password) {
    global $conn;
    
    $token = trim($token);
    $password = trim($password);
    $confirm_password = trim($confirm_password);
    
    if (empty($token) || empty($password) || empty($confirm_password)) {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Mật khẩu phải ít nhất 6 ký tự'];
    }
    
    if ($password !== $confirm_password) {
        return ['success' => false, 'message' => 'Mật khẩu không khớp'];
    }
    
    // Verify token
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expire > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn'];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Hash password and update
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expire = NULL WHERE id = ?");
    $update_stmt->bind_param("si", $hashed_password, $user['id']);
    
    if (!$update_stmt->execute()) {
        $update_stmt->close();
        return ['success' => false, 'message' => 'Lỗi cập nhật mật khẩu'];
    }
    
    $update_stmt->close();
    return ['success' => true, 'message' => 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập'];
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Logout user
function logoutUser() {
    session_destroy();
    return ['success' => true, 'message' => 'Đã đăng xuất'];
}

// Get user info
function getUserInfo($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, username, email, phone, full_name, avatar, address, bio, created_at FROM users WHERE id = ?");
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

// Update user profile
function updateUserProfile($user_id, $full_name, $phone, $address, $bio) {
    global $conn;
    
    $full_name = trim($full_name);
    $phone = trim($phone);
    $address = trim($address);
    $bio = trim($bio);
    
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $full_name, $phone, $address, $bio, $user_id);
    
    if (!$stmt->execute()) {
        $stmt->close();
        return ['success' => false, 'message' => 'Lỗi cập nhật thông tin'];
    }
    
    $stmt->close();
    
    // Update session
    $_SESSION['full_name'] = $full_name;
    
    return ['success' => true, 'message' => 'Cập nhật thông tin thành công'];
}

// Update user avatar
function updateUserAvatar($user_id, $avatar_path) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    $stmt->bind_param("si", $avatar_path, $user_id);
    
    if (!$stmt->execute()) {
        $stmt->close();
        return ['success' => false, 'message' => 'Lỗi cập nhật ảnh đại diện'];
    }
    
    $stmt->close();
    
    // Update session
    $_SESSION['avatar'] = $avatar_path;
    
    return ['success' => true, 'message' => 'Cập nhật ảnh đại diện thành công'];
}

// Save contact message
function saveContactMessage($name, $email, $phone, $subject, $message, $user_id = null) {
    global $conn;
    
    $name = trim($name);
    $email = trim($email);
    $phone = trim($phone);
    $subject = trim($subject);
    $message = trim($message);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email không hợp lệ'];
    }
    
    $stmt = $conn->prepare("INSERT INTO contacts (user_id, name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $name, $email, $phone, $subject, $message);
    
    if (!$stmt->execute()) {
        $stmt->close();
        return ['success' => false, 'message' => 'Lỗi gửi tin nhắn'];
    }
    
    $stmt->close();
    return ['success' => true, 'message' => 'Tin nhắn của bạn đã được gửi. Chúng tôi sẽ liên hệ sớm'];
}

// Change password
function changePassword($user_id, $old_password, $new_password, $confirm_password) {
    global $conn;
    
    $old_password = trim($old_password);
    $new_password = trim($new_password);
    $confirm_password = trim($confirm_password);
    
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
    }
    
    if (strlen($new_password) < 6) {
        return ['success' => false, 'message' => 'Mật khẩu mới phải ít nhất 6 ký tự'];
    }
    
    if ($new_password !== $confirm_password) {
        return ['success' => false, 'message' => 'Mật khẩu không khớp'];
    }
    
    // Get current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Người dùng không tồn tại'];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify old password
    if (!password_verify($old_password, $user['password'])) {
        return ['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác'];
    }
    
    // Hash new password and update
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
