CREATE DATABASE IF NOT EXISTS techshop_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techshop_ai;

-- Bảng sản phẩm
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(100) NOT NULL,
  description TEXT,
  specs TEXT,
  category VARCHAR(50),
  stock INT DEFAULT 0
);

-- Bảng người dùng
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  role ENUM('admin','user') DEFAULT 'user',
  -- Đường dẫn hoặc tên file ảnh đại diện của người dùng
  avatar VARCHAR(255) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bảng địa chỉ giao hàng của người dùng
-- Đặt bảng này trước bảng đơn hàng vì orders cần tham chiếu tới addresses
CREATE TABLE IF NOT EXISTS addresses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  recipient_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20),
  address TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Bảng đơn hàng
-- Lưu ý: bảng orders tham chiếu tới bảng addresses nên cần tạo addresses trước
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  address_id INT,
  total DECIMAL(10,2) NOT NULL,
  discount DECIMAL(10,2) DEFAULT 0,
  final_total DECIMAL(10,2) DEFAULT 0,
  voucher_code VARCHAR(50),
  status VARCHAR(50) DEFAULT 'Pending',
  -- Lý do hủy đơn hàng (nếu người dùng hủy)
  cancel_reason TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (address_id) REFERENCES addresses(id)
);

-- Bảng chi tiết đơn hàng
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT,
  product_id INT,
  quantity INT DEFAULT 1,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Bảng đánh giá sản phẩm
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  user_id INT,
  rating INT CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Bảng sản phẩm lưu mua sau
CREATE TABLE IF NOT EXISTS saved_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  product_id INT,
  quantity INT DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Bảng giỏ hàng tạm để lưu sản phẩm của người dùng đăng nhập
CREATE TABLE IF NOT EXISTS cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  product_id INT,
  quantity INT DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);


-- Bảng voucher giảm giá
CREATE TABLE IF NOT EXISTS vouchers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  discount_type ENUM('percent','fixed') NOT NULL,
  discount_value DECIMAL(10,2) NOT NULL,
  active BOOLEAN DEFAULT TRUE,
  expiration_date DATE
);

-- Dữ liệu mẫu voucher
INSERT INTO vouchers (code, discount_type, discount_value, active, expiration_date) VALUES
('SALE10', 'percent', 10, true, '2030-12-31'),
('SAVE50K', 'fixed', 50000, true, '2030-12-31');

-- Dữ liệu mẫu sản phẩm
INSERT INTO products (name, price, image, description, specs, category, stock) VALUES
('Chuột không dây Logitech M331', 299000, 'mouse.jpg', 'Chuột không dây yên tĩnh, thiết kế gọn nhẹ.',
 'Kết nối: 2.4GHz; Pin: 1 x AA; Độ nhạy: 1000 DPI;', 'Phụ kiện', 100),
('Bàn phím cơ Razer BlackWidow', 2499000, 'keyboard.jpg', 'Bàn phím cơ chuyên game với switch Razer.',
 'Switch: Razer Green; Layout: 104 phím; Đèn nền: RGB;', 'Phụ kiện', 50),
('Tai nghe Gaming HyperX Cloud II', 1999000, 'headset.jpg', 'Tai nghe chơi game âm thanh vòm 7.1.',
 'Loa: 53mm; Kết nối: USB và 3.5mm; Trọng lượng: 320g;', 'Phụ kiện', 30);

-- Thêm một vài sản phẩm mẫu khác
INSERT INTO products (name, price, image, description, specs, category, stock) VALUES
('Laptop Dell XPS 13', 35000000, 'laptop_xps13.jpg', 'Laptop cao cấp với màn hình 13 inch, hiệu năng mạnh mẽ.',
 'CPU: Intel Core i7; RAM: 16GB; SSD: 512GB; Màn hình: 13 inch FHD; Hệ điều hành: Windows 11;', 'Laptop', 20),
('Ổ cứng SSD Samsung 1TB', 2500000, 'ssd_samsung_1tb.jpg', 'Ổ cứng SSD tốc độ cao dung lượng 1TB.',
 'Dung lượng: 1TB; Chuẩn: NVMe PCIe; Tốc độ đọc: 3500MB/s; Tốc độ ghi: 3200MB/s;', 'Linh kiện', 40);

-- Thêm các sản phẩm mẫu khác để hiển thị ở trang chủ và danh mục
INSERT INTO products (name, price, image, description, specs, category, stock) VALUES
('RAM Kingston 16GB DDR4', 1500000, 'ram_kingston_16gb.jpg', 'RAM DDR4 dung lượng 16GB với tốc độ cao.',
 'Dung lượng: 16GB; Loại: DDR4; Bus: 3200MHz;', 'RAM', 60),
('SSD WD Blue 500GB', 1800000, 'ssd_wd_blue_500gb.jpg', 'SSD WD Blue dung lượng 500GB cho tốc độ ổn định.',
 'Dung lượng: 500GB; Chuẩn: SATA III; Tốc độ đọc: 560MB/s; Tốc độ ghi: 530MB/s;', 'SSD', 50),
('USB Sandisk 32GB', 250000, 'usb_sandisk_32gb.jpg', 'USB dung lượng 32GB nhỏ gọn tiện dụng.',
 'Dung lượng: 32GB; Chuẩn USB: 3.0; Bảo hành: 5 năm;', 'USB', 100),
('HDD Seagate 1TB', 1200000, 'hdd_seagate_1tb.jpg', 'Ổ cứng HDD Seagate dung lượng 1TB.',
 'Dung lượng: 1TB; Tốc độ quay: 7200RPM; Bộ nhớ đệm: 64MB;', 'HDD', 70),
('Màn hình Dell 24 inch', 3500000, 'monitor_dell_24.jpg', 'Màn hình Dell 24 inch độ phân giải cao.',
 'Kích thước: 24 inch; Độ phân giải: 1920x1080; Tần số quét: 75Hz;', 'Màn hình - Loa', 25);