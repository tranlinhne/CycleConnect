-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 12, 2026 lúc 06:03 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `greenride`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bikes`
--

CREATE TABLE `bikes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `condition_bike` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `warranty` varchar(100) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bikes`
--

INSERT INTO `bikes` (`id`, `user_id`, `category_id`, `brand_id`, `title`, `price`, `description`, `location`, `condition_bike`, `color`, `warranty`, `material`, `status`, `created_at`) VALUES
(1, 1, 1, 1, 'Xe Giant XTC 800', 8500000.00, 'Xe mua mới chạy được 2 tháng', 'Quận 1, TP.HCM', 'Như mới', 'Đen Lam', '12 tháng', 'Hợp kim nhôm ALUXX SL', 'available', '2026-04-09 15:14:29'),
(3, 1, 1, 1, 'Xe đạp Trek siêu lướt', 5000000.00, 'Mới đi được 3 vòng công viên', 'Quận 3, TP.HCM', NULL, NULL, NULL, NULL, 'available', '2026-04-09 15:51:07'),
(5, 1, 1, 1, 'Đang test lại chức năng', 1500000.00, 'Đang test chức năng', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-09 15:55:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bike_images`
--

CREATE TABLE `bike_images` (
  `id` int(11) NOT NULL,
  `bike_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bike_images`
--

INSERT INTO `bike_images` (`id`, `bike_id`, `image_url`, `is_primary`) VALUES
(24, 1, 'uploads/1775912093_0_Slide7.jpg', 1),
(25, 1, 'uploads/1775912093_1_Slide1.jpg', 0),
(26, 1, 'uploads/1775912093_2_Slide2.jpg', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `brands`
--

INSERT INTO `brands` (`id`, `name`) VALUES
(1, 'Giant'),
(2, 'Trek'),
(3, 'Asama');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Xe đạp địa hình'),
(2, 'Xe đạp đua'),
(3, 'Xe đạp phổ thông');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `featured_bikes`
--

CREATE TABLE `featured_bikes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `featured_bikes`
--

INSERT INTO `featured_bikes` (`id`, `name`, `description`, `price`, `image`, `display_order`, `created_at`) VALUES
(1, 'Xe đạp điện leo núi Pro X1', 'Chiếc xe đạp điện leo núi cao cấp được thiết kế dành cho những người yêu thích khám phá và chinh phục địa hình khó.\r\nXe được trang bị động cơ mạnh mẽ, pin dung lượng lớn giúp di chuyển quãng đường dài mà không lo hết pin.\r\n\r\nKhung xe hợp kim nhôm siêu nhẹ nhưng cực kỳ chắc chắn, hệ thống giảm xóc cao cấp giúp hấp thụ rung động tốt khi đi đường gồ ghề.\r\nPhanh đĩa thủy lực đảm bảo an toàn tuyệt đối trong mọi điều kiện thời tiết.\r\n\r\nPhù hợp cho đi phượt, leo núi, hoặc di chuyển hằng ngày trong thành phố.', 15000000.00, 'xe-dap-dien-leo-nui-pro-x1.png', 1, '2026-04-10 14:24:41'),
(2, 'Xe đạp thành phố City Lite', 'Xe đạp thành phố thời trang, nhẹ và linh hoạt, rất phù hợp cho việc đi học, đi làm và dạo phố.\r\nThiết kế tối giản hiện đại giúp việc điều khiển trở nên dễ dàng ngay cả với người mới sử dụng.\r\n\r\nYên xe êm ái, tay lái thoải mái giúp bạn có tư thế ngồi chuẩn, không mỏi lưng khi đi đường dài.\r\nLốp xe trơn giúp di chuyển mượt mà trên đường phố đông đúc.\r\n\r\nĐây là lựa chọn hoàn hảo cho sinh viên và dân văn phòng.', 6500000.00, 'xe-dap-thanh-pho-city-lite.png', 2, '2026-04-10 14:24:41'),
(3, 'Xe đạp đua Racing Speed R9', 'Chiếc xe đạp đua hiệu suất cao dành cho những người đam mê tốc độ và thể thao.\r\nKhung carbon siêu nhẹ kết hợp thiết kế khí động học giúp giảm lực cản gió tối đa.\r\n\r\nHệ thống truyền động 22 tốc độ giúp chuyển số mượt mà, tăng tốc nhanh và tiết kiệm sức lực.\r\nPhanh đĩa cao cấp mang lại sự an toàn khi di chuyển ở tốc độ cao.\r\n\r\nPhù hợp cho luyện tập thể thao, thi đấu hoặc chinh phục những cung đường dài.', 22000000.00, 'xe-dap-dua-racing-speed-r9.png', 3, '2026-04-10 14:24:41');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bikes`
--
ALTER TABLE `bikes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bike_brand` (`brand_id`),
  ADD KEY `fk_bike_category` (`category_id`);

--
-- Chỉ mục cho bảng `bike_images`
--
ALTER TABLE `bike_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_image_bike` (`bike_id`);

--
-- Chỉ mục cho bảng `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `featured_bikes`
--
ALTER TABLE `featured_bikes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bikes`
--
ALTER TABLE `bikes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `bike_images`
--
ALTER TABLE `bike_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT cho bảng `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `featured_bikes`
--
ALTER TABLE `featured_bikes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bikes`
--
ALTER TABLE `bikes`
  ADD CONSTRAINT `fk_bike_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bike_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `bike_images`
--
ALTER TABLE `bike_images`
  ADD CONSTRAINT `fk_image_bike` FOREIGN KEY (`bike_id`) REFERENCES `bikes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


CREATE TABLE users (
    id int(11) AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(150) NOT NULL,
    last_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    avatar VARCHAR(255),
    role ENUM('user', 'admin') DEFAULT 'user',
    warning_count INT DEFAULT 0,
   	active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE favorites (
    id int(11) AUTO_INCREMENT PRIMARY KEY,
    user_id int(11) NOT NULL,
    bike_id int(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    FOREIGN KEY (bike_id) REFERENCES bikes(id)
        ON DELETE CASCADE,

    UNIQUE (user_id, bike_id)
) ENGINE=InnoDB;

CREATE TABLE conversations (
    id int(11) AUTO_INCREMENT PRIMARY KEY,
    buyer_id int(11) NOT NULL,
    seller_id int(11) NOT NULL,
    bike_id int(11) NOT NULL,

    status ENUM('active','blocked','closed') DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (buyer_id) REFERENCES users(id)
        ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id)
        ON DELETE CASCADE,
    FOREIGN KEY (bike_id) REFERENCES bikes(id)
        ON DELETE CASCADE,

    UNIQUE (buyer_id, seller_id, bike_id)
) ENGINE=InnoDB;

CREATE TABLE messages (
    id int(11) AUTO_INCREMENT PRIMARY KEY,
    conversation_id int(11) NOT NULL,
    sender_id int(11) NOT NULL,

    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    is_deleted BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (conversation_id) REFERENCES conversations(id)
        ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE reviews (
    id int(11) AUTO_INCREMENT PRIMARY KEY,
    reviewer_id int(11) NOT NULL,
    reviewed_user_id int(11) NOT NULL,
    bike_id int(11) NOT NULL,

    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,

    status ENUM('visible','hidden','flagged') DEFAULT 'visible',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (reviewer_id) REFERENCES users(id)
        ON DELETE CASCADE,
    FOREIGN KEY (reviewed_user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    FOREIGN KEY (bike_id) REFERENCES bikes(id)
        ON DELETE CASCADE,

    UNIQUE (reviewer_id, bike_id)
) ENGINE=InnoDB;


CREATE TABLE reports (
    id int(11) AUTO_INCREMENT PRIMARY KEY,

    reporter_id int(11) NOT NULL,   -- Người báo cáo
    bike_id int(11) NOT NULL,       -- Tin đăng bị báo cáo

    reason VARCHAR(255) NOT NULL,
    description TEXT,

    status ENUM('pending','reviewed','rejected') DEFAULT 'pending',

    handled_by int(11) NULL,        -- Admin xử lý
    handled_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (reporter_id) REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (bike_id) REFERENCES bikes(id)
        ON DELETE CASCADE,

    FOREIGN KEY (handled_by) REFERENCES users(id)
        ON DELETE SET NULL,

    UNIQUE (reporter_id, bike_id)
) ENGINE=InnoDB;


-- Bảng đơn hàng (Orders)
CREATE TABLE orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    bike_id INT(11) NOT NULL,
    buyer_id INT(11) NOT NULL,
    seller_id INT(11) NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('unpaid', 'paid') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bike_id) REFERENCES bikes(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng liên hệ khách hàng (Contact messages)
CREATE TABLE contact_messages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    reply TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Chèn một admin mẫu (mật khẩu: admin123, đã hash)
INSERT INTO users (first_name, last_name, email, username, password, phone, role, active)
VALUES ('Admin', 'GreenRide', 'admin@greenride.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456789', 'admin', 1);
-- Lưu ý: password 'admin123' được hash bằng password_hash()


CREATE INDEX idx_messages_conversation ON messages(conversation_id);
CREATE INDEX idx_reviews_user ON reviews(reviewed_user_id);
CREATE INDEX idx_reports_status ON reports(status);
CREATE INDEX idx_reports_bike ON reports(bike_id);