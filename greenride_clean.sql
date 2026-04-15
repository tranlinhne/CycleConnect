-- GreenRide Database - Authentication & Profile System
-- Database cho trang Login, Register, Profile, Contact

-- Tạo database nếu chưa tồn tại
CREATE DATABASE IF NOT EXISTS greenride;
USE greenride;

-- Bảng Users - Quản lý người dùng
CREATE TABLE IF NOT EXISTS users (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(20),
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(255),
  avatar VARCHAR(255),
  address TEXT,
  bio TEXT,
  status ENUM('active','inactive','banned') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL,
  reset_token VARCHAR(255),
  reset_token_expire DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng Contacts - Tin nhắn liên hệ
CREATE TABLE IF NOT EXISTS contacts (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11),
  name VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL,
  phone VARCHAR(20),
  subject VARCHAR(255) NOT NULL,
  message LONGTEXT NOT NULL,
  status ENUM('new','replied','closed') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dữ liệu mẫu - Người dùng test
INSERT INTO users (username, email, phone, password, full_name, status) 
VALUES ('testuser', 'test@example.com', '0901234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Người Dùng Test', 'active');

-- Dữ liệu mẫu - Tin nhắn liên hệ
INSERT INTO contacts (user_id, name, email, phone, subject, message, status) 
VALUES 
(1, 'Nguyễn Văn A', 'test@example.com', '0901234567', 'Hỏi về tính năng', 'Tôi muốn hỏi thêm về tính năng này', 'new'),
(NULL, 'Khách Hàng', 'customer@example.com', '0987654321', 'Báo cáo lỗi', 'Gặp lỗi khi upload ảnh', 'new');
