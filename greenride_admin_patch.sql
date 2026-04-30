-- GreenRide admin patch
-- Apply this after greenride_clean.sql to make AdminPage work

USE greenride;

-- 1) Patch users table (greenride_clean.sql doesn't have admin columns)
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS first_name VARCHAR(150) NULL AFTER id,
  ADD COLUMN IF NOT EXISTS last_name VARCHAR(150) NULL AFTER first_name,
  ADD COLUMN IF NOT EXISTS role ENUM('user','admin') NOT NULL DEFAULT 'user' AFTER avatar,
  ADD COLUMN IF NOT EXISTS warning_count INT NOT NULL DEFAULT 0 AFTER role,
  ADD COLUMN IF NOT EXISTS active TINYINT(1) NOT NULL DEFAULT 1 AFTER warning_count;

-- Backfill first_name from full_name if present
UPDATE users
SET first_name = COALESCE(NULLIF(first_name, ''), COALESCE(full_name, username))
WHERE first_name IS NULL OR first_name = '';

UPDATE users
SET last_name = COALESCE(NULLIF(last_name, ''), '')
WHERE last_name IS NULL;

-- Admin account will be auto-created by AdminPage/login.php if not found

-- 2) Master data tables used by AdminPage
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS brands (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bikes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category_id INT NOT NULL,
  brand_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  price DECIMAL(15,2) NOT NULL,
  description TEXT NULL,
  location VARCHAR(100) NULL,
  condition_bike VARCHAR(100) NULL,
  color VARCHAR(50) NULL,
  warranty VARCHAR(100) NULL,
  material VARCHAR(100) NULL,
  status ENUM('available','sold','hidden') DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bikes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_bikes_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
  CONSTRAINT fk_bikes_brand FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bike_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bike_id INT NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  is_primary TINYINT(1) DEFAULT 1,
  CONSTRAINT fk_bike_images_bike FOREIGN KEY (bike_id) REFERENCES bikes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Orders / revenue
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bike_id INT NOT NULL,
  buyer_id INT NOT NULL,
  seller_id INT NOT NULL,
  total_price DECIMAL(15,2) NOT NULL,
  status ENUM('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  payment_method VARCHAR(50) NULL,
  payment_status ENUM('unpaid','paid') DEFAULT 'unpaid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_bike FOREIGN KEY (bike_id) REFERENCES bikes(id) ON DELETE CASCADE,
  CONSTRAINT fk_orders_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_orders_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Contacts (AdminPage expects contact_messages)
CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(20) NULL,
  subject VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  reply TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migrate old contacts data if exists
INSERT INTO contact_messages (fullname, email, phone, subject, message, is_read, created_at)
SELECT c.name, c.email, c.phone, c.subject, c.message,
       CASE WHEN c.status = 'new' THEN 0 ELSE 1 END,
       c.created_at
FROM contacts c
WHERE NOT EXISTS (SELECT 1 FROM contact_messages cm);

-- 5) Moderation / social tables for dashboard and reports
CREATE TABLE IF NOT EXISTS favorites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  bike_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_favorites (user_id, bike_id),
  CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_favorites_bike FOREIGN KEY (bike_id) REFERENCES bikes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reviewer_id INT NOT NULL,
  reviewed_user_id INT NOT NULL,
  bike_id INT NOT NULL,
  rating INT NOT NULL,
  comment TEXT NULL,
  status ENUM('visible','hidden','flagged') DEFAULT 'visible',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_reviews (reviewer_id, bike_id),
  CONSTRAINT fk_reviews_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_reviewed FOREIGN KEY (reviewed_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_bike FOREIGN KEY (bike_id) REFERENCES bikes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  bike_id INT NOT NULL,
  reason VARCHAR(255) NOT NULL,
  description TEXT NULL,
  status ENUM('pending','reviewed','rejected') DEFAULT 'pending',
  handled_by INT NULL,
  handled_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_reports (reporter_id, bike_id),
  CONSTRAINT fk_reports_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_reports_bike FOREIGN KEY (bike_id) REFERENCES bikes(id) ON DELETE CASCADE,
  CONSTRAINT fk_reports_admin FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS featured_bikes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  price DECIMAL(10,2) NULL,
  image VARCHAR(255) NULL,
  display_order INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed base categories/brands if empty
INSERT INTO categories (name)
SELECT 'Xe đạp địa hình' WHERE NOT EXISTS (SELECT 1 FROM categories);
INSERT INTO categories (name)
SELECT 'Xe đạp đua' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Xe đạp đua');
INSERT INTO categories (name)
SELECT 'Xe đạp phổ thông' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Xe đạp phổ thông');

INSERT INTO brands (name)
SELECT 'Giant' WHERE NOT EXISTS (SELECT 1 FROM brands);
INSERT INTO brands (name)
SELECT 'Trek' WHERE NOT EXISTS (SELECT 1 FROM brands WHERE name = 'Trek');
INSERT INTO brands (name)
SELECT 'Asama' WHERE NOT EXISTS (SELECT 1 FROM brands WHERE name = 'Asama');

-- 6) Multi-level admin reporting for Revenue
ALTER TABLE users MODIFY COLUMN role ENUM('user', 'manager', 'admin') NOT NULL DEFAULT 'user';

CREATE TABLE IF NOT EXISTS revenue_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  manager_id INT NOT NULL,
  report_period VARCHAR(7) NOT NULL, -- e.g., '2026-04'
  total_revenue DECIMAL(15,2) NOT NULL,
  platform_fee DECIMAL(15,2) NOT NULL,
  notes TEXT,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  reviewed_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_revenue_manager FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_revenue_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
