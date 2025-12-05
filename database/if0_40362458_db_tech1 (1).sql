-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql103.infinityfree.com
-- Generation Time: Dec 04, 2025 at 11:30 PM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40362458_db_tech1`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `recipient_name`, `email`, `phone`, `city`, `district`, `address`, `created_at`) VALUES
(1, 2, 'tranviet', NULL, '12345678', NULL, NULL, 'bac giang', '2025-11-01 20:42:47'),
(5, 2, 'Tranviet', 'tran123@gmail.com', '12345678', 'Bac Giang', 'Hiệp Hòa', 'Ngõ 248, Hiệp Hòa, Bac Giang', '2025-12-02 02:42:00'),
(6, 4, '1', '11@gmail.com', '1', 'Ha Noi', 'Hai Bà Trưng', 'nhà a, Hai Bà Trưng, Ha Noi', '2025-12-02 06:04:12'),
(7, 2, 'Bong', '1@gmail.com', '000000000', 'Bac Giang', 'Hiệp Hòa', 'Ngõ 123456, Hiệp Hòa, Bac Giang', '2025-12-02 06:08:37');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(4, 3, 1, 10, '2025-11-27 16:26:46');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `final_total` decimal(10,2) DEFAULT 0.00,
  `voucher_code` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `cancel_reason` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `address_id`, `total`, `discount`, `final_total`, `voucher_code`, `status`, `cancel_reason`, `created_at`) VALUES
(1, 2, 1, '35000000.00', '0.00', '35000000.00', '', 'Completed', NULL, '2025-11-01 21:32:36'),
(2, 2, 1, '99999999.99', '0.00', '99999999.99', '', 'Cancelled', NULL, '2025-11-27 22:12:50'),
(3, 2, 1, '35990000.00', '3599000.00', '32391000.00', 'SALE10', 'Pending', NULL, '2025-12-01 06:19:10'),
(4, 2, 1, '17490000.00', '1749000.00', '15741000.00', 'SALE10', 'Cancelled', NULL, '2025-12-02 00:17:12'),
(5, 2, 5, '17490000.00', '0.00', '17490000.00', '', 'Cancelled', 'Đổi', '2025-12-02 05:49:16'),
(6, 2, 5, '2499000.00', '0.00', '2499000.00', '', 'Cancelled', 'Muốn đổi sản phẩm khác', '2025-12-02 05:51:11'),
(7, 2, 5, '17490000.00', '0.00', '17490000.00', '', 'Cancelled', 'Đặt nhầm', '2025-12-02 05:53:41'),
(8, 4, 6, '34980000.00', '3498000.00', '31482000.00', 'SALE10', 'Cancelled', 'Muốn đổi sản phẩm khác', '2025-12-02 06:04:41'),
(9, 2, 5, '17490000.00', '0.00', '17490000.00', '', 'Pending', NULL, '2025-12-02 06:07:24'),
(10, 2, 5, '14990000.00', '0.00', '14990000.00', '', 'Cancelled', 'Đặt nhầm', '2025-12-02 06:07:48'),
(11, 4, 6, '299000.00', '50000.00', '249000.00', 'SAVE50K', 'Pending', NULL, '2025-12-04 20:12:47');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 4, 1, '35000000.00'),
(2, 2, 34, 12, '14990000.00'),
(3, 3, 30, 1, '35990000.00'),
(4, 4, 35, 1, '17490000.00'),
(5, 5, 35, 1, '17490000.00'),
(6, 6, 2, 1, '2499000.00'),
(7, 7, 35, 1, '17490000.00'),
(8, 8, 35, 2, '17490000.00'),
(9, 9, 35, 1, '17490000.00'),
(10, 10, 34, 1, '14990000.00'),
(11, 11, 1, 1, '299000.00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `specs` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `sale_price`, `image`, `description`, `specs`, `category`, `stock`) VALUES
(1, 'Chuột không dây Logitech M331', '299000.00', NULL, 'mouse.jpg', 'Chuột không dây yên tĩnh, thiết kế gọn nhẹ.', 'Kết nối: 2.4GHz; Pin: 1 x AA; Độ nhạy: 1000 DPI;', 'Phụ kiện', 99),
(2, 'Bàn phím cơ Razer BlackWidow', '2499000.00', NULL, 'keyboard.jpg', 'Bàn phím cơ chuyên game với switch Razer.', 'Switch: Razer Green; Layout: 104 phím; Đèn nền: RGB;', 'Phụ kiện', 50),
(3, 'Tai nghe Gaming HyperX Cloud II', '1999000.00', NULL, 'headset.jpg', 'Tai nghe chơi game âm thanh vòm 7.1.', 'Loa: 53mm; Kết nối: USB và 3.5mm; Trọng lượng: 320g;', 'Phụ kiện', 30),
(4, 'Laptop Dell XPS 13', '35000000.00', '33000000.00', 'laptop_xps13.jpg', 'Laptop cao cấp với màn hình 13 inch, hiệu năng mạnh mẽ.', 'CPU: Intel Core i7; RAM: 16GB; SSD: 512GB; Màn hình: 13 inch FHD; Hệ điều hành: Windows 11;', 'Laptop', 19),
(5, 'Ổ cứng SSD Samsung 1TB', '2500000.00', NULL, 'ssd_samsung_1tb.jpg', 'Ổ cứng SSD tốc độ cao dung lượng 1TB.', 'Dung lượng: 1TB; Chuẩn: NVMe PCIe; Tốc độ đọc: 3500MB/s; Tốc độ ghi: 3200MB/s;', 'Linh kiện', 40),
(6, 'RAM Kingston 16GB DDR4', '1500000.00', NULL, 'ram_kingston_16gb.jpg', 'RAM DDR4 dung lượng 16GB với tốc độ cao.', 'Dung lượng: 16GB; Loại: DDR4; Bus: 3200MHz;', 'RAM', 60),
(7, 'SSD WD Blue 500GB', '1800000.00', NULL, 'ssd_wd_blue_500gb.jpg', 'SSD WD Blue dung lượng 500GB cho tốc độ ổn định.', 'Dung lượng: 500GB; Chuẩn: SATA III; Tốc độ đọc: 560MB/s; Tốc độ ghi: 530MB/s;', 'SSD', 50),
(8, 'USB Sandisk 32GB', '250000.00', NULL, 'usb_sandisk_32gb.jpg', 'USB dung lượng 32GB nhỏ gọn tiện dụng.', 'Dung lượng: 32GB; Chuẩn USB: 3.0; Bảo hành: 5 năm;', 'USB', 100),
(9, 'HDD Seagate 1TB', '1200000.00', NULL, 'hdd_seagate_1tb.jpg', 'Ổ cứng HDD Seagate dung lượng 1TB.', 'Dung lượng: 1TB; Tốc độ quay: 7200RPM; Bộ nhớ đệm: 64MB;', 'HDD', 70),
(10, 'Màn hình Dell 24 inch', '3500000.00', NULL, 'monitor_dell_24.jpg', 'Màn hình Dell 24 inch độ phân giải cao.', 'Kích thước: 24 inch; Độ phân giải: 1920x1080; Tần số quét: 75Hz;', 'Màn hình - Loa', 25),
(11, 'Chuột Gaming Razer Viper Mini', '890000.00', NULL, 'razer-viper-mini.jpg', 'Chuột gaming nhẹ, DPI cao, thiết kế cho game thủ.', 'DPI: 8000; Kết nối: USB; Switch: Optical; Trọng lượng: 61g', 'Phụ kiện', 25),
(12, 'Bàn phím cơ Akko 3084', '1290000.00', NULL, 'akko-3084.jpg', 'Bàn phím cơ compact 75%, switch bền, led RGB.', 'Layout: 75%; Switch: Gateron; Keycap: PBT; Kết nối: USB-C', 'Phụ kiện', 18),
(13, 'Tai nghe Logitech G432', '650000.00', NULL, 'logitech-g432.jpg', 'Tai nghe gaming 7.1, microphone có lọc tạp âm.', 'Loại: Over-ear; Trở kháng: 39Ω; Micro: Có; Kết nối: 3.5mm', 'Phụ kiện', 30),
(14, 'Túi Laptop 15.6\" Timbuk2', '420000.00', NULL, 'timbuk2-15.jpg', 'Túi chống sốc, vải chống nước, nhiều ngăn.', 'Kích thước: 15.6 inch; Chất liệu: Polyester; Ngăn: 3', 'Phụ kiện', 40),
(15, 'Sạc nhanh USB-C 65W Baseus', '390000.00', NULL, 'baseus-65w.jpg', 'Sạc PD 65W, nhỏ gọn, tương thích laptop và điện thoại.', 'Công suất: 65W; Cổng: USB-C; PD: Có; Bảo vệ: OVP/OTP', 'Phụ kiện', 60),
(16, 'Chuột Logitech G102 Lightsync', '390000.00', NULL, 'logitech-g102.jpg', 'Chuột gaming phổ biến với cảm biến 8000 DPI và hiệu ứng LED RGB.', 'DPI: 8000; Switch: Logitech; LED: RGB; Kết nối: USB', 'Phụ kiện', 45),
(17, 'Bàn phím DareU EK87 RGB', '680000.00', NULL, 'dareu-ek87.jpg', 'Bàn phím cơ TKL phù hợp cho game thủ và dân văn phòng.', 'Switch: D; Layout: TKL; LED: RGB; Keycap: ABS', 'Phụ kiện', 30),
(18, 'Tai nghe Rapoo VH510', '550000.00', NULL, 'rapoo-vh510.jpg', 'Tai nghe gaming có mic lọc tiếng tốt và âm thanh sống động.', 'Driver: 50mm; LED: RGB; Mic: Chống ồn; Kết nối: USB', 'Phụ kiện', 36),
(19, 'Card VGA NVIDIA GTX 1650 4GB', '4290000.00', NULL, 'gtx-1650.jpg', 'Card đồ họa tầm trung cho game và đồ họa nhẹ.', 'GPU: GTX 1650; VRAM: 4GB GDDR5; Bus: 128-bit', 'Linh kiện', 12),
(20, 'Mainboard MSI B560M PRO', '1790000.00', NULL, 'msi-b560m.jpg', 'Mainboard socket LGA1200, phù hợp CPU Intel thế hệ 10/11.', 'Socket: LGA1200; RAM: DDR4 4 khe; Form factor: mATX', 'Linh kiện', 8),
(21, 'CPU Intel Core i5-11400', '3890000.00', NULL, 'i5-11400.jpg', 'CPU 6 nhân 12 luồng, hiệu năng ổn cho đa nhiệm và game.', 'Cores/Threads: 6/12; Base: 2.6GHz; Turbo: 4.4GHz; TDP: 65W', 'Linh kiện', 14),
(22, 'Nguồn Cooler Master 650W', '1250000.00', NULL, 'cm-650w.jpg', 'Nguồn 80+ Bronze, hiệu suất ổn định cho gaming PC.', 'Công suất: 650W; Hiệu suất: 80+ Bronze; Fan: 120mm', 'Linh kiện', 20),
(23, 'Quạt tản nhiệt CPU Noctua NH-U12S', '1390000.00', NULL, 'noctua-nh-u12s.jpg', 'Quạt tản hiệu năng cao, êm, phù hợp build mạnh.', 'Loại: Air cooler; Kích thước: 120mm; Tương thích: AMD/Intel', 'Linh kiện', 15),
(24, 'Card màn hình AMD RX 6600 8GB', '5790000.00', '5090000.00', 'amd-rx6600.jpg', 'Card đồ họa mạnh mẽ cho gaming 1080p, hiệu năng cao.', 'VRAM: 8GB GDDR6; TDP: 132W; Xung: 2491MHz; Cổng: DP/HDMI', 'Linh kiện', 20),
(25, 'Mainboard ASUS PRIME B450M-K II', '1690000.00', NULL, 'asus-b450m-k2.jpg', 'Bo mạch chủ phổ biến hỗ trợ Ryzen 1000–5000.', 'Socket: AM4; RAM: 128GB; M.2: 1; PCIe: 3.0', 'Linh kiện', 28),
(26, 'CPU AMD Ryzen 5 5600G', '2990000.00', NULL, 'ryzen-5600g.jpg', 'CPU tích hợp VGA mạnh, phù hợp cho PC văn phòng và gaming nhẹ.', '6C/12T; Base: 3.9GHz; Boost: 4.4GHz; GPU: Vega 7', 'Linh kiện', 22),
(27, 'Nguồn Corsair CV550 550W', '1090000.00', NULL, 'corsair-cv550.jpg', 'Nguồn công suất thực, độ bền cao phù hợp cho PC phổ thông.', 'Công suất: 550W; Chuẩn: 80 Plus Bronze; Quạt: 120mm', 'Linh kiện', 40),
(28, 'Tản nhiệt khí DeepCool Gammaxx 400 V2', '420000.00', NULL, 'gammaxx-400-v2.jpg', 'Tản nhiệt hiệu năng tốt với 4 ống đồng.', 'RPM: 1650; LED: Blue; Chiều cao: 155mm; Socket: Intel/AMD', 'Linh kiện', 38),
(29, 'Laptop Dell Inspiron 15 5510', '18990000.00', NULL, 'dell-inspiron-5510.jpg', 'Laptop đa dụng cho công việc và học tập, cấu hình cân bằng.', 'CPU: i5-11320H; RAM: 8GB; SSD: 512GB; Màn: 15.6\" FHD', 'Laptop', 7),
(30, 'Laptop ASUS ROG Strix G15', '35990000.00', '34990000.00', 'asus-rog-g15.jpg', 'Laptop gaming hiệu năng cao, tản nhiệt tốt.', 'CPU: Ryzen 7 6800H; GPU: RTX 3060; RAM: 16GB; SSD: 1TB', 'Laptop', 4),
(31, 'MacBook Air M1 13-inch', '28990000.00', '28000000.00', 'macbook-air-m1.jpg', 'Mỏng nhẹ, pin lâu, hiệu năng Apple M1 vượt trội.', 'Chip: Apple M1; RAM: 8GB; SSD: 256GB; Màn: 13.3\" Retina', 'Laptop', 6),
(32, 'HP Pavilion 14', '14990000.00', NULL, 'hp-pavilion-14.jpg', 'Laptop văn phòng nhẹ, giá hợp lý.', 'CPU: i3-1115G4; RAM: 8GB; SSD: 256GB; Màn: 14\" FHD', 'Laptop', 10),
(33, 'Lenovo ThinkPad E14', '17990000.00', '16990000.00', 'thinkpad-e14.jpg', 'Bền bỉ, bàn phím tốt, phù hợp doanh nghiệp.', 'CPU: i5-1135G7; RAM: 8GB; SSD: 512GB; Bảo mật: Fingerprint', 'Laptop', 4),
(34, 'Laptop Acer Aspire 7 A715', '14990000.00', '14000000.00', 'acer-aspire7.jpg', 'Laptop phổ thông mạnh mẽ, phù hợp đồ họa và giải trí.', 'CPU: Ryzen 5 5500U; RAM: 8GB; SSD: 512GB; GPU: GTX1650', 'Laptop', 12),
(35, 'Laptop MSI GF63 Thin', '17490000.00', '16490000.00', 'msi-gf63-thin.jpg', 'Laptop gaming mỏng nhẹ với GPU GTX1650 Max-Q.', 'CPU: i5-11400H; RAM: 8GB; SSD: 512GB; Màn: 15.6 FHD 144Hz', 'Laptop', 9),
(36, 'RAM Corsair Vengeance 8GB DDR4 3200MHz', '620000.00', NULL, 'corsair-8gb-3200.jpg', 'Module RAM tốc độ cao cho desktop.', 'Dung lượng: 8GB; Loại: DDR4; Tốc độ: 3200MHz; Latency: CL16', 'RAM', 50),
(37, 'RAM Kingston HyperX 16GB DDR4 2666MHz', '1190000.00', NULL, 'kingston-16gb-2666.jpg', 'Bộ nhớ ổn định cho đa nhiệm nặng.', 'Dung lượng: 16GB; Loại: DDR4; Tốc độ: 2666MHz', 'RAM', 40),
(38, 'RAM G.Skill Trident Z 32GB (2x16) DDR4 3600MHz', '2790000.00', NULL, 'gskill-32gb-3600.jpg', 'Kit RAM hiệu năng cao cho gaming và workstation.', 'Kit: 2x16GB; Tốc độ: 3600MHz; CL: 16', 'RAM', 12),
(39, 'RAM TeamGroup Elite 4GB DDR4 2400MHz', '290000.00', NULL, 'team-4gb-2400.jpg', 'RAM giá rẻ cho máy văn phòng hoặc nâng cấp cơ bản.', 'Dung lượng: 4GB; Tốc độ: 2400MHz; Loại: DDR4', 'RAM', 80),
(40, 'RAM Crucial 16GB DDR4 SO-DIMM 3200MHz', '1290000.00', NULL, 'crucial-16gb-sodimm.jpg', 'Module SO-DIMM cho laptop, hiệu năng ổn.', 'Dung lượng: 16GB; Loại: SO-DIMM; Tốc độ: 3200MHz', 'RAM', 25),
(41, 'RAM Adata XPG Spectrix 8GB RGB 3200MHz', '690000.00', NULL, 'adata-spectrix-8gb.jpg', 'Thanh RAM DDR4 có LED RGB nổi bật.', 'Dung lượng: 8GB; Bus: 3200MHz; LED: RGB; Loại: DDR4', 'RAM', 55),
(42, 'RAM Samsung 32GB ECC Registered DDR4', '2400000.00', NULL, 'samsung-32gb-ecc.jpg', 'RAM dành cho máy chủ với độ ổn định cao.', '32GB; Bus: 2666MHz; ECC Registered; DDR4', 'RAM', 15),
(43, 'SSD Samsung 970 EVO Plus 500GB NVMe', '1690000.00', NULL, 'samsung-970-evo-500.jpg', 'SSD NVMe tốc độ cao, phù hợp hệ điều hành & game.', 'Loại: NVMe M.2; Dung lượng: 500GB; Tốc độ đọc: 3500MB/s; Ghi: 3300MB/s', 'SSD', 22),
(44, 'SSD Kingston A2000 1TB NVMe', '2490000.00', NULL, 'kingston-a2000-1tb.jpg', 'NVMe giá tốt, dung lượng lớn.', 'Loại: NVMe M.2; Dung lượng: 1TB; Tốc độ đọc: 2200MB/s', 'SSD', 18),
(45, 'SSD Crucial MX500 1TB SATA', '1790000.00', NULL, 'crucial-mx500-1tb.jpg', 'SSD SATA bền, giá hợp lý.', 'Loại: SATA 2.5\"; Dung lượng: 1TB; TBW: 700TB', 'SSD', 30),
(46, 'SSD WD Blue SN550 250GB', '420000.00', NULL, 'wd-sn550-250.jpg', 'SSD NVMe cơ bản cho nâng cấp hiệu năng.', 'Loại: NVMe M.2; Dung lượng: 250GB; Read: 2400MB/s', 'SSD', 45),
(47, 'SSD Silicon Power 512GB NVMe', '850000.00', NULL, 'sp-512gb.jpg', 'SSD NVMe tầm trung, phù hợp nhiều nhu cầu.', 'Dung lượng: 512GB; Loại: NVMe; Read: 2000MB/s', 'SSD', 28),
(48, 'SSD Lexar NM620 1TB NVMe', '1390000.00', NULL, 'lexar-nm620-1tb.jpg', 'SSD tốc độ cao phù hợp cho gaming và đồ họa.', 'Dung lượng: 1TB; Đọc: 3500MB/s; Ghi: 3000MB/s; Chuẩn: M.2 NVMe', 'SSD', 26),
(49, 'SSD Kingston KC3000 2TB NVMe', '3990000.00', NULL, 'kc3000-2tb.jpg', 'SSD cao cấp với tốc độ cực nhanh.', 'Dung lượng: 2TB; Đọc: 7000MB/s; Ghi: 6000MB/s', 'SSD', 18),
(50, 'HDD Seagate Barracuda 2TB 7200RPM', '2300000.00', NULL, 'seagate-2tb.jpg', 'HDD 1TB tốc độ 7200RPM, lưu trữ đa dụng.', 'Dung lượng: 2TB; RPM: 7200; Cache: 256MB; Interface: SATA', 'HDD', 55),
(51, 'HDD Western Digital Blue 2TB', '1190000.00', NULL, 'wd-blue-2tb.jpg', 'Ổ cứng dung lượng lớn cho backup và lưu trữ.', 'Dung lượng: 2TB; RPM: 5400; Interface: SATA', 'HDD', 30),
(52, 'HDD Toshiba P300 4TB', '2090000.00', NULL, 'toshiba-4tb.jpg', 'HDD 4TB cho NAS/PC lưu trữ dữ liệu lớn.', 'Dung lượng: 4TB; RPM: 7200; Cache: 128MB', 'HDD', 12),
(53, 'HDD Seagate IronWolf 6TB', '3990000.00', NULL, 'ironwolf-6tb.jpg', 'Ổ cứng chuyên cho NAS, độ bền cao.', 'Dung lượng: 6TB; RPM: 7200; TBW cao; Optimized NAS', 'HDD', 6),
(54, 'HDD WD Elements 5TB', '2890000.00', NULL, 'wd-elements-5tb.jpg', 'Ổ cứng di động/PC dung lượng lớn.', 'Dung lượng: 5TB; RPM: 5400; Interface: SATA', 'HDD', 10),
(55, 'HDD WD Purple 4TB', '1890000.00', NULL, 'wd-purple-4tb.jpg', 'Ổ cứng giám sát độ bền cao cho hệ thống camera.', 'Dung lượng: 4TB; Chuẩn: SATA III; 5400RPM; Cache: 64MB', 'HDD', 22),
(56, 'HDD Seagate SkyHawk 8TB', '4290000.00', '4090000.00', 'skyhawk-8tb.jpg', 'Ổ cứng cho hệ thống DVR/NVR hoạt động 24/7.', 'Dung lượng: 8TB; 7200RPM; Cache: 256MB', 'HDD', 14),
(57, 'Màn hình LG 24MP59G 24\" IPS', '3290000.00', NULL, 'lg-24mp59g.jpg', 'Màn hình 24\" IPS, tần số 75Hz, độ phản hồi nhanh.', 'Kích thước: 24\"; Độ phân giải: 1920x1080; Tần số: 75Hz; Panel: IPS', 'Màn hình - Loa', 20),
(58, 'Màn hình Dell UltraSharp 27\" U2720Q', '12990000.00', '12000000.00', 'dell-u2720q.jpg', 'Màn hình 4K chuyên đồ họa, màu chuẩn.', 'Kích thước: 27\"; Độ phân giải: 3840x2160; Panel: IPS; USB-C', 'Màn hình - Loa', 5),
(59, 'Loa Logitech Z333', '990000.00', NULL, 'logitech-z333.jpg', 'Loa 2.1 công suất tốt, bass mạnh mẽ cho phòng nhỏ.', 'Công suất RMS: 80W; Loa: 2.1; Kết nối: 3.5mm/RCA', 'Màn hình - Loa', 25),
(60, 'Loa Creative Pebble Plus', '490000.00', NULL, 'creative-pebble-plus.jpg', 'Loa desktop nhỏ gọn, âm thanh cân bằng.', 'Loại: 2.1; Công suất: 8W RMS; Kết nối: 3.5mm/USB', 'Màn hình - Loa', 40),
(61, 'Màn hình Samsung Odyssey 27\" 144Hz', '7490000.00', '6490000.00', 'samsung-odyssey-27.jpg', 'Màn hình gaming 144Hz, cong nhẹ, tần số cao.', 'Kích thước: 27\"; Độ phân giải: 2560x1440; Tần số: 144Hz; Panel: VA', 'Màn hình - Loa', 7),
(62, 'Màn hình ViewSonic VA2432-h 24', '2190000.00', NULL, 'viewsonic-va2432h.jpg', 'Màn hình IPS góc nhìn rộng phù hợp văn phòng.', 'Tấm nền: IPS; Tần số: 75Hz; Cổng: HDMI/VGA', 'Màn hình - Loa', 4),
(63, 'Laptop Dell G15 5525 RTX 3050Ti', '25990000.00', '23990000.00', 'laptop_dell_g15_5525.png', 'Laptop gaming Dell G15 5525 với CPU Ryzen 7 và RTX 3050Ti, phù hợp chơi game và làm đồ họa cơ bản.', 'CPU: AMD Ryzen 7 6800H; RAM: 16GB DDR5; SSD: 512GB NVMe; GPU: NVIDIA GeForce RTX 3050Ti 4GB; Màn hình: 15.6\" FHD 120Hz; Pin: ~86Wh.', 'Laptop', 15),
(64, 'Laptop MSI GF63 Thin 11SC', '19990000.00', '16990000.00', 'laptop_msi_gf63_11SC.png', 'Laptop gaming mỏng nhẹ MSI GF63 Thin với CPU Intel Core i5 và GTX 1650, phù hợp sinh viên yêu thích game.', 'CPU: Intel Core i5-11400H; RAM: 8GB DDR4 (nâng cấp tối đa 64GB); SSD: 512GB NVMe; GPU: NVIDIA GeForce GTX 1650 4GB; Màn hình: 15.6\" FHD 144Hz.', 'Laptop', 20),
(65, 'Laptop Acer Aspire 7 A715', '18990000.00', '15490000.00', 'laptop_acer_aspire7_a715.png', 'Laptop Acer Aspire 7 A715 hiệu năng tốt cho cả làm việc và giải trí, thiết kế đơn giản, bền bỉ.', 'CPU: AMD Ryzen 5 5500U; RAM: 8GB DDR4; SSD: 512GB; GPU: NVIDIA GTX 1650 4GB; Màn hình: 15.6\" FHD; Bàn phím LED trắng.', 'Laptop', 18),
(66, 'Laptop ASUS TUF Gaming F15', '24990000.00', '21990000.00', 'laptop_asus_tuf_f15.png', 'Laptop ASUS TUF Gaming F15 với thiết kế hầm hố, đạt độ bền tiêu chuẩn quân đội, phù hợp game thủ.', 'CPU: Intel Core i7-12700H; RAM: 16GB DDR4; SSD: 512GB NVMe; GPU: RTX 3060 6GB; Màn hình: 15.6\" FHD 144Hz; Bàn phím RGB.', 'Laptop', 12),
(67, 'Laptop Lenovo IdeaPad 3 15ADA', '11990000.00', '9990000.00', 'laptop_lenovo_ideapad3_15ada.png', 'Laptop Lenovo IdeaPad 3 15 inch dành cho văn phòng, học tập cơ bản, thiết kế gọn nhẹ.', 'CPU: AMD Ryzen 3 3250U; RAM: 8GB DDR4; SSD: 256GB; Màn hình: 15.6\" FHD; Trọng lượng ~1.7kg; Pin ~6 giờ.', 'Laptop', 25),
(68, 'Laptop HP 15s Intel Core i5', '14990000.00', '12990000.00', 'laptop_hp_15s_i5.png', 'Laptop HP 15s với CPU Core i5, phù hợp công việc văn phòng và học online ổn định.', 'CPU: Intel Core i5-1235U; RAM: 8GB DDR4; SSD: 512GB; Màn hình: 15.6\" FHD; Webcam HD; Bàn phím full-size.', 'Laptop', 22),
(69, 'Laptop Apple MacBook Air M1 13', '26990000.00', '23990000.00', 'laptop_macbook_air_m1.png', 'MacBook Air M1 13 inch mỏng nhẹ, pin trâu, phù hợp làm việc văn phòng, học tập và giải trí.', 'CPU: Apple M1 8‑core; RAM: 8GB; SSD: 256GB; Màn hình: 13.3\" Retina; Thời lượng pin lên đến 15 giờ; Trọng lượng 1.29kg.', 'Laptop', 10),
(70, 'Laptop ASUS VivoBook 14 OLED', '19990000.00', '17990000.00', 'laptop_asus_vivobook14_oled.png', 'Laptop ASUS VivoBook 14 với màn hình OLED rực rỡ, phù hợp làm nội dung, học tập và giải trí.', 'CPU: Intel Core i5-1240P; RAM: 16GB; SSD: 512GB; Màn hình: 14\" OLED FHD; Bảo mật vân tay; Wi-Fi 6.', 'Laptop', 14),
(71, 'Laptop LG Gram 16 siêu nhẹ', '33990000.00', '30990000.00', 'laptop_lg_gram16.png', 'Laptop LG Gram 16 inch siêu nhẹ, thích hợp cho người hay di chuyển, pin rất lâu.', 'CPU: Intel Core i7; RAM: 16GB; SSD: 512GB; Màn hình: 16\" WQXGA; Trọng lượng ~1.2kg; Pin lên đến 19 giờ.', 'Laptop', 8),
(72, 'Laptop Gigabyte G5 Gaming', '22990000.00', '19990000.00', 'laptop_gigabyte_g5.png', 'Laptop Gigabyte G5 dành cho game thủ, hiệu năng cao với RTX 3060 và màn hình 144Hz.', 'CPU: Intel Core i5 H-series; RAM: 16GB; SSD: 512GB; GPU: RTX 3060 6GB; Màn hình: 15.6\" FHD 144Hz.', 'Laptop', 9),
(73, 'RAM Kingston Fury Beast 8GB DDR4 3200MHz', '790000.00', '690000.00', 'ram_kingston_fury_8gb_3200.png', 'RAM Kingston Fury Beast 8GB DDR4 3200MHz, phù hợp nâng cấp máy tính chơi game và làm việc.', 'Dung lượng: 8GB; Chuẩn: DDR4; Bus: 3200MHz; Điện áp: 1.35V; Tản nhiệt nhôm.', 'RAM', 40),
(74, 'RAM Kingston Fury Beast 16GB DDR4 3200MHz', '1490000.00', '1290000.00', 'ram_kingston_fury_16gb_3200.png', 'RAM Kingston Fury Beast 16GB cho đa nhiệm mượt mà, phù hợp làm việc và chơi game.', 'Dung lượng: 16GB; Chuẩn: DDR4; Bus: 3200MHz; Điện áp: 1.35V; Hỗ trợ XMP.', 'RAM', 35),
(75, 'RAM G.Skill Ripjaws V 16GB DDR4 3600MHz', '1990000.00', '1790000.00', 'ram_gskill_ripjaws_16gb_3600.png', 'RAM G.Skill Ripjaws V 16GB DDR4 bus 3600MHz, hiệu năng cao cho PC gaming.', 'Dung lượng: 16GB (2x8GB); Chuẩn: DDR4; Bus: 3600MHz; Điện áp: 1.35V; Tản nhiệt nhôm.', 'RAM', 20),
(76, 'RAM Corsair Vengeance LPX 8GB DDR4 2666MHz', '690000.00', '590000.00', 'ram_corsair_lpx_8gb_2666.png', 'RAM Corsair Vengeance LPX 8GB DDR4 2666MHz, phù hợp nâng cấp cơ bản cho PC.', 'Dung lượng: 8GB; Chuẩn: DDR4; Bus: 2666MHz; Điện áp: 1.2V; Low-profile heatsink.', 'RAM', 30),
(77, 'RAM Laptop Kingston 8GB DDR4 3200MHz', '790000.00', '690000.00', 'ram_laptop_kingston_8gb_3200.png', 'RAM laptop Kingston 8GB DDR4 3200MHz, giúp laptop chạy đa nhiệm mượt hơn.', 'Dung lượng: 8GB; Chuẩn: DDR4 SO-DIMM; Bus: 3200MHz; Điện áp: 1.2V.', 'RAM', 28),
(78, 'RAM Laptop Samsung 16GB DDR4 3200MHz', '1590000.00', '1390000.00', 'ram_laptop_samsung_16gb_3200.png', 'RAM laptop Samsung 16GB DDR4 3200MHz cho nhu cầu làm việc nặng.', 'Dung lượng: 16GB; Chuẩn: DDR4 SO-DIMM; Bus: 3200MHz; Điện áp: 1.2V.', 'RAM', 18),
(79, 'RAM DDR5 Corsair Vengeance 16GB 5200MHz', '2690000.00', '2390000.00', 'ram_ddr5_corsair_16gb_5200.png', 'RAM DDR5 Corsair Vengeance 16GB bus 5200MHz thế hệ mới cho PC hiệu năng cao.', 'Dung lượng: 16GB; Chuẩn: DDR5; Bus: 5200MHz; Điện áp thấp; Hỗ trợ XMP 3.0.', 'RAM', 15),
(80, 'RAM DDR5 G.Skill Trident Z5 RGB 32GB 6000MHz', '4990000.00', '4590000.00', 'ram_ddr5_gskill_z5_32gb_6000.png', 'RAM DDR5 G.Skill Trident Z5 RGB 32GB 6000MHz cho cấu hình cao cấp.', 'Dung lượng: 32GB (2x16GB); Chuẩn: DDR5; Bus: 6000MHz; LED RGB; Hỗ trợ XMP/EXPO.', 'RAM', 10),
(81, 'SSD Kingston NV2 500GB M.2 NVMe', '1090000.00', '890000.00', 'ssd_kingston_nv2_500.png', 'SSD Kingston NV2 500GB M.2 NVMe, tốc độ cao, giá tốt.', 'Dung lượng: 500GB; Chuẩn: M.2 NVMe; Tốc độ đọc ~3500MB/s; Tốc độ ghi ~2100MB/s.', 'SSD', 30),
(82, 'SSD Kingston NV2 1TB M.2 NVMe', '1890000.00', '1590000.00', 'ssd_kingston_nv2_1tb_v2.png', 'SSD Kingston NV2 1TB M.2 NVMe cho nhu cầu lưu trữ lớn.', 'Dung lượng: 1TB; Chuẩn: M.2 PCIe 4.0 NVMe; Tốc độ đọc tối đa ~3500MB/s.', 'SSD', 25),
(83, 'SSD Samsung 980 500GB M.2 NVMe', '1690000.00', '1490000.00', 'ssd_samsung_980_500.png', 'SSD Samsung 980 500GB NVMe, độ ổn định cao, phù hợp PC/laptop.', 'Dung lượng: 500GB; Chuẩn: M.2 NVMe PCIe 3.0; Tốc độ đọc ~3500MB/s; Tốc độ ghi ~3000MB/s.', 'SSD', 20),
(84, 'SSD WD Blue SN570 1TB M.2 NVMe', '2190000.00', '1890000.00', 'ssd_wd_blue_sn570_1tb.png', 'SSD WD Blue SN570 1TB NVMe cho nhu cầu làm việc, sáng tạo nội dung.', 'Dung lượng: 1TB; Chuẩn: M.2 NVMe; Tốc độ đọc ~3500MB/s; Bảo hành 5 năm.', 'SSD', 18),
(85, 'SSD SATA Kingston A400 240GB', '790000.00', '590000.00', 'ssd_kingston_a400_240.png', 'SSD SATA Kingston A400 240GB nâng cấp tốc độ cho PC/laptop cũ.', 'Dung lượng: 240GB; Chuẩn: SATA 2.5\"; Tốc độ đọc ~500MB/s; Tốc độ ghi ~350MB/s.', 'SSD', 35),
(86, 'SSD SATA Samsung 870 EVO 1TB', '2890000.00', '2590000.00', 'ssd_samsung_870evo_1tb.png', 'SSD SATA Samsung 870 EVO 1TB độ bền cao, phù hợp lưu trữ dữ liệu.', 'Dung lượng: 1TB; Chuẩn: SATA 2.5\"; Tốc độ đọc ~560MB/s; Tốc độ ghi ~530MB/s.', 'SSD', 15),
(87, 'Màn hình LG 24MP60G 24 inch 75Hz IPS', '3290000.00', '2890000.00', 'monitor_lg_24mp60g.png', 'Màn hình LG 24MP60G 24\" IPS 75Hz, phù hợp làm việc và giải trí.', 'Kích thước: 24\"; Độ phân giải: FHD 1920x1080; Tấm nền: IPS; Tần số quét: 75Hz; Thời gian đáp ứng: 1ms MBR; Cổng: HDMI, DisplayPort.', 'Màn hình - Loa', 12),
(88, 'Màn hình Dell P2422H 24 inch IPS', '4590000.00', '4290000.00', 'monitor_dell_p2422h.png', 'Màn hình Dell P2422H cho dân văn phòng, thiết kế công thái học, xoay dọc được.', 'Kích thước: 24\"; Độ phân giải: FHD; Tấm nền: IPS; Cổng: HDMI, DisplayPort, VGA; Chân đế xoay, nâng hạ.', 'Màn hình - Loa', 8),
(89, 'Màn hình ASUS VG249Q 23.8 inch 144Hz', '5290000.00', '4790000.00', 'monitor_asus_vg249q.png', 'Màn hình ASUS VG249Q 144Hz dành cho game thủ, FreeSync.', 'Kích thước: 23.8\"; Độ phân giải: FHD; Tấm nền: IPS; Tần số quét: 144Hz; FreeSync; Cổng: HDMI, DisplayPort.', 'Màn hình - Loa', 10),
(90, 'Màn hình Xiaomi Mi Desktop 27 inch IPS', '3990000.00', '3490000.00', 'monitor_xiaomi_27.png', 'Màn hình Xiaomi 27\" thiết kế viền mỏng, phù hợp văn phòng.', 'Kích thước: 27\"; Độ phân giải: FHD; Tấm nền: IPS; Góc nhìn rộng 178°; Cổng: HDMI, VGA.', 'Màn hình - Loa', 9),
(91, 'Chuột Logitech G102 Lightsync', '490000.00', '390000.00', 'mouse_logitech_g102.png', 'Chuột gaming Logitech G102 Lightsync với LED RGB 16.8 triệu màu.', 'Cảm biến: lên tới 8000 DPI; Kết nối: USB; 6 nút lập trình; LED RGB; Phù hợp tay nhỏ-vừa.', 'Chuột', 40),
(92, 'Chuột Logitech M331 Silent không dây', '390000.00', '320000.00', 'mouse_logitech_m331.png', 'Chuột không dây Logitech M331 Silent click êm, phù hợp văn phòng.', 'Độ phân giải: 1000 DPI; Kết nối: Wireless 2.4GHz; Tầm hoạt động: 10m; Pin: 1xAA tới 24 tháng.', 'Chuột', 35),
(93, 'Chuột Razer DeathAdder Essential', '690000.00', '590000.00', 'mouse_razer_deathadder_ess.png', 'Chuột gaming Razer DeathAdder Essential, form công thái học cho game thủ.', 'Cảm biến: 6400 DPI; Nút: 5 nút có thể lập trình; Kết nối: USB; Tuổi thọ switch: 10 triệu lần click.', 'Chuột', 25),
(94, 'Chuột không dây Rapoo M100 Silent', '250000.00', '199000.00', 'mouse_rapoo_m100.png', 'Chuột Rapoo M100 silent nhiều màu, kết nối đa thiết bị.', 'Độ phân giải: 1300 DPI; Kết nối: 2.4GHz + Bluetooth; Kết nối cùng lúc 3 thiết bị; Click êm.', 'Chuột', 30),
(95, 'Bàn phím cơ DareU EK87 RGB Blue Switch', '890000.00', '790000.00', 'keyboard_dareu_ek87.png', 'Bàn phím cơ DareU EK87 TKL, LED RGB nhiều hiệu ứng.', 'Layout: Tenkeyless 87 phím; Switch: Blue switch; Kết nối: USB; LED RGB; Keycap ABS.', 'Bàn phím', 20),
(96, 'Bàn phím cơ AKKO 3068B Plus Multi-mode', '1990000.00', '1790000.00', 'keyboard_akko_3068b.png', 'Bàn phím cơ không dây AKKO 3068B Plus, hỗ trợ Bluetooth và 2.4GHz.', 'Layout: 68 phím; Kết nối: USB-C, Bluetooth, 2.4GHz; Pin: 1800mAh; Switch AKKO; LED RGB.', 'Bàn phím', 12),
(97, 'Bàn phím Logitech K120 USB', '190000.00', '150000.00', 'keyboard_logitech_k120.png', 'Bàn phím Logitech K120 có dây, đơn giản, bền bỉ, phù hợp văn phòng.', 'Layout: full-size; Kết nối: USB; Chống tràn; Phím gõ êm, độ bền cao.', 'Bàn phím', 50),
(98, 'Combo phím chuột không dây Logitech MK270', '590000.00', '490000.00', 'keyboard_logitech_mk270_combo.png', 'Combo phím chuột không dây Logitech MK270 cho văn phòng, học tập.', 'Kết nối: Wireless 2.4GHz; Bàn phím full-size; Chuột quang 1000 DPI; Pin lâu.', 'Bàn phím', 25),
(99, 'Tai nghe gaming Logitech G331', '890000.00', '690000.00', 'headset_logitech_g331.png', 'Tai nghe gaming Logitech G331 âm thanh sống động, micro rõ.', 'Kiểu: Over-ear; Kết nối: jack 3.5mm; Driver: 50mm; Tương thích PC, Console.', 'Tai nghe', 18),
(100, 'Tai nghe Bluetooth Xiaomi Redmi Buds 4', '1290000.00', '990000.00', 'earbuds_xiaomi_redmi_buds4.png', 'Tai nghe true wireless Redmi Buds 4 với chống ồn chủ động ANC.', 'Kiểu: In-ear TWS; Kết nối: Bluetooth 5.2; Thời lượng pin: ~6h + hộp sạc; Chống ồn ANC.', 'Tai nghe', 30),
(101, 'Tai nghe Sony WH-CH520 Bluetooth', '1490000.00', '1290000.00', 'headphone_sony_whch520.png', 'Tai nghe on-ear Sony WH-CH520 pin trâu, đeo thoải mái.', 'Kiểu: On-ear; Kết nối: Bluetooth; Pin: tới 50 giờ; Hỗ trợ ứng dụng Sony Headphones Connect.', 'Tai nghe', 16),
(102, 'Tai nghe in-ear Samsung AKG Type-C', '390000.00', '290000.00', 'earphone_samsung_akg_typec.png', 'Tai nghe in-ear AKG đi kèm flagship Samsung, âm thanh cân bằng.', 'Kiểu: In-ear; Kết nối: USB Type-C; Driver động; Micro thoại; Dây chống rối.', 'Tai nghe', 40),
(103, 'Router Wi-Fi TP-Link Archer AX23 Wi-Fi 6', '1690000.00', '1490000.00', 'router_tplink_ax23.png', 'Router TP-Link Archer AX23 hỗ trợ Wi-Fi 6 tốc độ cao cho gia đình.', 'Chuẩn: Wi-Fi 6 AX1800; Băng tần: 2.4GHz & 5GHz; 4 anten ngoài; 4 cổng LAN; Hỗ trợ app Tether.', 'Thiết bị mạng', 15),
(104, 'Router Wi-Fi Xiaomi 4A Gigabit', '690000.00', '590000.00', 'router_xiaomi_4a_gigabit.png', 'Router Xiaomi 4A Gigabit giá rẻ, phủ sóng ổn định, quản lý qua app.', 'Chuẩn: Wi-Fi 5 AC1200; 4 anten ngoài; 1 WAN + 2 LAN Gigabit; Quản lý qua Mi Wi-Fi app.', 'Thiết bị mạng', 25),
(105, 'Bộ phát Wi-Fi Mesh Tenda Nova MW3 (2 pack)', '1590000.00', '1390000.00', 'mesh_tenda_nova_mw3.png', 'Bộ Wi-Fi Mesh Tenda Nova MW3 gồm 2 node phủ sóng cả căn hộ.', 'Chuẩn: AC1200; 2 node; Phủ sóng ~200m2; Hỗ trợ roaming liền mạch; Quản lý qua app.', 'Thiết bị mạng', 10),
(106, 'Switch TP-Link LS1008G 8 cổng Gigabit', '590000.00', '490000.00', 'switch_tplink_ls1008g.png', 'Switch TP-Link LS1008G 8 cổng Gigabit, phù hợp mở rộng mạng có dây.', 'Tốc độ: 8x Gigabit 10/100/1000Mbps; Plug and Play; Vỏ nhựa; Tiêu thụ điện thấp.', 'Thiết bị mạng', 20),
(107, 'Giá đỡ laptop nhôm gập gọn', '350000.00', '290000.00', 'accessory_laptop_stand_fold.png', 'Giá đỡ laptop bằng nhôm, có thể gập gọn, giúp nâng cao màn hình và tản nhiệt tốt.', 'Chất liệu: Nhôm; Gập gọn; Nhiều mức chiều cao; Tương thích laptop 11-17\".', 'Phụ kiện', 40),
(108, 'Đế tản nhiệt laptop 2 quạt LED xanh', '290000.00', '230000.00', 'accessory_coolerpad_2fan.png', 'Đế tản nhiệt laptop với 2 quạt lớn LED xanh, giảm nhiệt độ khi dùng lâu.', '2 quạt 140mm; Cổng USB cấp nguồn; Độ ồn thấp; Kích thước phù hợp laptop 15.6\".', 'Phụ kiện', 25),
(109, 'Hub USB-C 5 trong 1 HDMI + USB 3.0', '590000.00', '490000.00', 'accessory_usbc_hub_5in1.png', 'Hub USB-C 5 trong 1 chuyển ra HDMI và 3 cổng USB 3.0, thêm cổng Type-C PD.', 'Cổng: 1x HDMI 4K, 3x USB 3.0, 1x USB-C PD; Vỏ nhôm; Hỗ trợ Windows/MacOS.', 'Phụ kiện', 30),
(110, 'Balo laptop 15.6 inch chống sốc', '590000.00', '490000.00', 'accessory_backpack_156.png', 'Balo laptop 15.6\" có ngăn chống sốc, nhiều ngăn tiện dụng.', 'Chất liệu: Vải dù; Ngăn chống sốc cho laptop 15.6\"; Dây đeo êm; Chống nước nhẹ.', 'Phụ kiện', 20),
(111, 'Ổ cứng di động WD Elements 1TB USB 3.0', '1590000.00', '1390000.00', 'accessory_hdd_wd_elements_1tb.png', 'Ổ cứng di động WD Elements 1TB, nhỏ gọn, tiện mang theo.', 'Dung lượng: 1TB; Chuẩn: HDD 2.5\"; Kết nối: USB 3.0; Tương thích Windows/Mac.', 'Phụ kiện', 18),
(112, 'USB SanDisk Ultra Flair 64GB 3.0', '290000.00', '230000.00', 'accessory_usb_sandisk_64gb.png', 'USB SanDisk Ultra Flair 64GB tốc độ cao, vỏ kim loại chắc chắn.', 'Dung lượng: 64GB; Chuẩn: USB 3.0; Tốc độ đọc tối đa ~150MB/s; Vỏ kim loại; Lỗ móc khóa.', 'Phụ kiện', 35);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(0, 35, 2, 5, '', '2025-12-02 07:19:23'),
(0, 35, 2, 5, 'Tuy?t v?i', '2025-12-02 07:19:38'),
(0, 35, 2, 4, 'Tuyệt', '2025-12-02 07:24:02');

-- --------------------------------------------------------

--
-- Table structure for table `saved_items`
--

CREATE TABLE `saved_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` datetime DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `phone`, `role`, `created_at`, `avatar`) VALUES
(1, 'tranviet', 'viet@gmail.com', '$2y$10$9lUQaa5wQoASyT4dvwrCO.bUQ4q2/VESS8.fH3olWqdkVyL.TDT.K', NULL, 'admin', '2025-11-01 18:17:55', NULL),
(2, 'viettran', 'tran@gmail.com', '$2y$10$xRa7HK7DHRYpsC1ZngQKLetYCcgjSNu0SN6b21MLVLWBZmRIvr8i.', '123456789', 'user', '2025-11-01 20:41:32', 'user_2_1764594165.png'),
(3, 'vinh', 'a@gmail.com', '$2y$10$fQtudzYtBj6fR9gmnRtvz.euYgChiO7BtKbSj6UZR.I3PtYXBYqtK', NULL, 'user', '2025-11-27 16:25:53', NULL),
(4, 'anh', 'an@gmail.com', '$2y$10$AXCrzOFglFvlT.qKufpzP.gdWbHW2KZf9CA5dlIv/Q5an3V66GCQi', NULL, 'user', '2025-12-02 05:45:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percent','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `expiration_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `discount_type`, `discount_value`, `active`, `expiration_date`) VALUES
(2, 'SAVE50K', 'fixed', '50000.00', 1, '2030-12-31'),
(3, 'SALE20', 'percent', '20.00', 1, '2025-12-22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saved_items`
--
ALTER TABLE `saved_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `saved_items`
--
ALTER TABLE `saved_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `saved_items`
--
ALTER TABLE `saved_items`
  ADD CONSTRAINT `saved_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `saved_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
