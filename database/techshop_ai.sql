-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2025 at 01:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `techshop_ai`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `recipient_name`, `phone`, `address`, `created_at`) VALUES
(1, 2, 'tranviet', '12345678', 'bac giang', '2025-11-01 20:42:47');

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
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `address_id`, `total`, `discount`, `final_total`, `voucher_code`, `status`, `created_at`) VALUES
(1, 2, 1, 35000000.00, 0.00, 35000000.00, '', 'Pending', '2025-11-01 21:32:36');

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
(1, 1, 4, 1, 35000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `specs` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`, `description`, `specs`, `category`, `stock`) VALUES
(1, 'Chuột không dây Logitech M331', 299000.00, 'mouse.jpg', 'Chuột không dây yên tĩnh, thiết kế gọn nhẹ.', 'Kết nối: 2.4GHz; Pin: 1 x AA; Độ nhạy: 1000 DPI;', 'Phụ kiện', 100),
(2, 'Bàn phím cơ Razer BlackWidow', 2499000.00, 'keyboard.jpg', 'Bàn phím cơ chuyên game với switch Razer.', 'Switch: Razer Green; Layout: 104 phím; Đèn nền: RGB;', 'Phụ kiện', 50),
(3, 'Tai nghe Gaming HyperX Cloud II', 1999000.00, 'headset.jpg', 'Tai nghe chơi game âm thanh vòm 7.1.', 'Loa: 53mm; Kết nối: USB và 3.5mm; Trọng lượng: 320g;', 'Phụ kiện', 30),
(4, 'Laptop Dell XPS 13', 35000000.00, 'laptop_xps13.jpg', 'Laptop cao cấp với màn hình 13 inch, hiệu năng mạnh mẽ.', 'CPU: Intel Core i7; RAM: 16GB; SSD: 512GB; Màn hình: 13 inch FHD; Hệ điều hành: Windows 11;', 'Laptop', 19),
(5, 'Ổ cứng SSD Samsung 1TB', 2500000.00, 'ssd_samsung_1tb.jpg', 'Ổ cứng SSD tốc độ cao dung lượng 1TB.', 'Dung lượng: 1TB; Chuẩn: NVMe PCIe; Tốc độ đọc: 3500MB/s; Tốc độ ghi: 3200MB/s;', 'Linh kiện', 40),
(6, 'RAM Kingston 16GB DDR4', 1500000.00, 'ram_kingston_16gb.jpg', 'RAM DDR4 dung lượng 16GB với tốc độ cao.', 'Dung lượng: 16GB; Loại: DDR4; Bus: 3200MHz;', 'RAM', 60),
(7, 'SSD WD Blue 500GB', 1800000.00, 'ssd_wd_blue_500gb.jpg', 'SSD WD Blue dung lượng 500GB cho tốc độ ổn định.', 'Dung lượng: 500GB; Chuẩn: SATA III; Tốc độ đọc: 560MB/s; Tốc độ ghi: 530MB/s;', 'SSD', 50),
(8, 'USB Sandisk 32GB', 250000.00, 'usb_sandisk_32gb.jpg', 'USB dung lượng 32GB nhỏ gọn tiện dụng.', 'Dung lượng: 32GB; Chuẩn USB: 3.0; Bảo hành: 5 năm;', 'USB', 100),
(9, 'HDD Seagate 1TB', 1200000.00, 'hdd_seagate_1tb.jpg', 'Ổ cứng HDD Seagate dung lượng 1TB.', 'Dung lượng: 1TB; Tốc độ quay: 7200RPM; Bộ nhớ đệm: 64MB;', 'HDD', 70),
(10, 'Màn hình Dell 24 inch', 3500000.00, 'monitor_dell_24.jpg', 'Màn hình Dell 24 inch độ phân giải cao.', 'Kích thước: 24 inch; Độ phân giải: 1920x1080; Tần số quét: 75Hz;', 'Màn hình - Loa', 25),
(11, 'Chuột Gaming Razer Viper Mini', 890000.00, 'razer-viper-mini.jpg', 'Chuột gaming nhẹ, DPI cao, thiết kế cho game thủ.', 'DPI: 8000; Kết nối: USB; Switch: Optical; Trọng lượng: 61g', 'Phụ kiện', 25),
(12, 'Bàn phím cơ Akko 3084', 1290000.00, 'akko-3084.jpg', 'Bàn phím cơ compact 75%, switch bền, led RGB.', 'Layout: 75%; Switch: Gateron; Keycap: PBT; Kết nối: USB-C', 'Phụ kiện', 18),
(13, 'Tai nghe Logitech G432', 650000.00, 'logitech-g432.jpg', 'Tai nghe gaming 7.1, microphone có lọc tạp âm.', 'Loại: Over-ear; Trở kháng: 39Ω; Micro: Có; Kết nối: 3.5mm', 'Phụ kiện', 30),
(14, 'Túi Laptop 15.6\" Timbuk2', 420000.00, 'timbuk2-15.jpg', 'Túi chống sốc, vải chống nước, nhiều ngăn.', 'Kích thước: 15.6 inch; Chất liệu: Polyester; Ngăn: 3', 'Phụ kiện', 40),
(15, 'Sạc nhanh USB-C 65W Baseus', 390000.00, 'baseus-65w.jpg', 'Sạc PD 65W, nhỏ gọn, tương thích laptop và điện thoại.', 'Công suất: 65W; Cổng: USB-C; PD: Có; Bảo vệ: OVP/OTP', 'Phụ kiện', 60),
(16, 'Chuột Logitech G102 Lightsync', 390000.00, 'logitech-g102.jpg', 'Chuột gaming phổ biến với cảm biến 8000 DPI và hiệu ứng LED RGB.', 'DPI: 8000; Switch: Logitech; LED: RGB; Kết nối: USB', 'Phụ kiện', 45),
(17, 'Bàn phím DareU EK87 RGB', 680000.00, 'dareu-ek87.jpg', 'Bàn phím cơ TKL phù hợp cho game thủ và dân văn phòng.', 'Switch: D; Layout: TKL; LED: RGB; Keycap: ABS', 'Phụ kiện', 30),
(18, 'Tai nghe Rapoo VH510', 550000.00, 'rapoo-vh510.jpg', 'Tai nghe gaming có mic lọc tiếng tốt và âm thanh sống động.', 'Driver: 50mm; LED: RGB; Mic: Chống ồn; Kết nối: USB', 'Phụ kiện', 36),
(19, 'Card VGA NVIDIA GTX 1650 4GB', 4290000.00, 'gtx-1650.jpg', 'Card đồ họa tầm trung cho game và đồ họa nhẹ.', 'GPU: GTX 1650; VRAM: 4GB GDDR5; Bus: 128-bit', 'Linh kiện', 12),
(20, 'Mainboard MSI B560M PRO', 1790000.00, 'msi-b560m.jpg', 'Mainboard socket LGA1200, phù hợp CPU Intel thế hệ 10/11.', 'Socket: LGA1200; RAM: DDR4 4 khe; Form factor: mATX', 'Linh kiện', 8),
(21, 'CPU Intel Core i5-11400', 3890000.00, 'i5-11400.jpg', 'CPU 6 nhân 12 luồng, hiệu năng ổn cho đa nhiệm và game.', 'Cores/Threads: 6/12; Base: 2.6GHz; Turbo: 4.4GHz; TDP: 65W', 'Linh kiện', 14),
(22, 'Nguồn Cooler Master 650W', 1250000.00, 'cm-650w.jpg', 'Nguồn 80+ Bronze, hiệu suất ổn định cho gaming PC.', 'Công suất: 650W; Hiệu suất: 80+ Bronze; Fan: 120mm', 'Linh kiện', 20),
(23, 'Quạt tản nhiệt CPU Noctua NH-U12S', 1390000.00, 'noctua-nh-u12s.jpg', 'Quạt tản hiệu năng cao, êm, phù hợp build mạnh.', 'Loại: Air cooler; Kích thước: 120mm; Tương thích: AMD/Intel', 'Linh kiện', 15),
(24, 'Card màn hình AMD RX 6600 8GB', 5790000.00, 'amd-rx6600.jpg', 'Card đồ họa mạnh mẽ cho gaming 1080p, hiệu năng cao.', 'VRAM: 8GB GDDR6; TDP: 132W; Xung: 2491MHz; Cổng: DP/HDMI', 'Linh kiện', 20),
(25, 'Mainboard ASUS PRIME B450M-K II', 1690000.00, 'asus-b450m-k2.jpg', 'Bo mạch chủ phổ biến hỗ trợ Ryzen 1000–5000.', 'Socket: AM4; RAM: 128GB; M.2: 1; PCIe: 3.0', 'Linh kiện', 28),
(26, 'CPU AMD Ryzen 5 5600G', 2990000.00, 'ryzen-5600g.jpg', 'CPU tích hợp VGA mạnh, phù hợp cho PC văn phòng và gaming nhẹ.', '6C/12T; Base: 3.9GHz; Boost: 4.4GHz; GPU: Vega 7', 'Linh kiện', 22),
(27, 'Nguồn Corsair CV550 550W', 1090000.00, 'corsair-cv550.jpg', 'Nguồn công suất thực, độ bền cao phù hợp cho PC phổ thông.', 'Công suất: 550W; Chuẩn: 80 Plus Bronze; Quạt: 120mm', 'Linh kiện', 40),
(28, 'Tản nhiệt khí DeepCool Gammaxx 400 V2', 420000.00, 'gammaxx-400-v2.jpg', 'Tản nhiệt hiệu năng tốt với 4 ống đồng.', 'RPM: 1650; LED: Blue; Chiều cao: 155mm; Socket: Intel/AMD', 'Linh kiện', 38),
(29, 'Laptop Dell Inspiron 15 5510', 18990000.00, 'dell-inspiron-5510.jpg', 'Laptop đa dụng cho công việc và học tập, cấu hình cân bằng.', 'CPU: i5-11320H; RAM: 8GB; SSD: 512GB; Màn: 15.6\" FHD', 'Laptop', 7),
(30, 'Laptop ASUS ROG Strix G15', 35990000.00, 'asus-rog-g15.jpg', 'Laptop gaming hiệu năng cao, tản nhiệt tốt.', 'CPU: Ryzen 7 6800H; GPU: RTX 3060; RAM: 16GB; SSD: 1TB', 'Laptop', 5),
(31, 'MacBook Air M1 13-inch', 28990000.00, 'macbook-air-m1.jpg', 'Mỏng nhẹ, pin lâu, hiệu năng Apple M1 vượt trội.', 'Chip: Apple M1; RAM: 8GB; SSD: 256GB; Màn: 13.3\" Retina', 'Laptop', 6),
(32, 'HP Pavilion 14', 14990000.00, 'hp-pavilion-14.jpg', 'Laptop văn phòng nhẹ, giá hợp lý.', 'CPU: i3-1115G4; RAM: 8GB; SSD: 256GB; Màn: 14\" FHD', 'Laptop', 10),
(33, 'Lenovo ThinkPad E14', 17990000.00, 'thinkpad-e14.jpg', 'Bền bỉ, bàn phím tốt, phù hợp doanh nghiệp.', 'CPU: i5-1135G7; RAM: 8GB; SSD: 512GB; Bảo mật: Fingerprint', 'Laptop', 4),
(34, 'Laptop Acer Aspire 7 A715', 14990000.00, 'acer-aspire7.jpg', 'Laptop phổ thông mạnh mẽ, phù hợp đồ họa và giải trí.', 'CPU: Ryzen 5 5500U; RAM: 8GB; SSD: 512GB; GPU: GTX1650', 'Laptop', 12),
(35, 'Laptop MSI GF63 Thin', 17490000.00, 'msi-gf63-thin.jpg', 'Laptop gaming mỏng nhẹ với GPU GTX1650 Max-Q.', 'CPU: i5-11400H; RAM: 8GB; SSD: 512GB; Màn: 15.6 FHD 144Hz', 'Laptop', 10),
(36, 'RAM Corsair Vengeance 8GB DDR4 3200MHz', 620000.00, 'corsair-8gb-3200.jpg', 'Module RAM tốc độ cao cho desktop.', 'Dung lượng: 8GB; Loại: DDR4; Tốc độ: 3200MHz; Latency: CL16', 'RAM', 50),
(37, 'RAM Kingston HyperX 16GB DDR4 2666MHz', 1190000.00, 'kingston-16gb-2666.jpg', 'Bộ nhớ ổn định cho đa nhiệm nặng.', 'Dung lượng: 16GB; Loại: DDR4; Tốc độ: 2666MHz', 'RAM', 40),
(38, 'RAM G.Skill Trident Z 32GB (2x16) DDR4 3600MHz', 2790000.00, 'gskill-32gb-3600.jpg', 'Kit RAM hiệu năng cao cho gaming và workstation.', 'Kit: 2x16GB; Tốc độ: 3600MHz; CL: 16', 'RAM', 12),
(39, 'RAM TeamGroup Elite 4GB DDR4 2400MHz', 290000.00, 'team-4gb-2400.jpg', 'RAM giá rẻ cho máy văn phòng hoặc nâng cấp cơ bản.', 'Dung lượng: 4GB; Tốc độ: 2400MHz; Loại: DDR4', 'RAM', 80),
(40, 'RAM Crucial 16GB DDR4 SO-DIMM 3200MHz', 1290000.00, 'crucial-16gb-sodimm.jpg', 'Module SO-DIMM cho laptop, hiệu năng ổn.', 'Dung lượng: 16GB; Loại: SO-DIMM; Tốc độ: 3200MHz', 'RAM', 25),
(41, 'RAM Adata XPG Spectrix 8GB RGB 3200MHz', 690000.00, 'adata-spectrix-8gb.jpg', 'Thanh RAM DDR4 có LED RGB nổi bật.', 'Dung lượng: 8GB; Bus: 3200MHz; LED: RGB; Loại: DDR4', 'RAM', 55),
(42, 'RAM Samsung 32GB ECC Registered DDR4', 2400000.00, 'samsung-32gb-ecc.jpg', 'RAM dành cho máy chủ với độ ổn định cao.', '32GB; Bus: 2666MHz; ECC Registered; DDR4', 'RAM', 15),
(43, 'SSD Samsung 970 EVO Plus 500GB NVMe', 1690000.00, 'samsung-970-evo-500.jpg', 'SSD NVMe tốc độ cao, phù hợp hệ điều hành & game.', 'Loại: NVMe M.2; Dung lượng: 500GB; Tốc độ đọc: 3500MB/s; Ghi: 3300MB/s', 'SSD', 22),
(44, 'SSD Kingston A2000 1TB NVMe', 2490000.00, 'kingston-a2000-1tb.jpg', 'NVMe giá tốt, dung lượng lớn.', 'Loại: NVMe M.2; Dung lượng: 1TB; Tốc độ đọc: 2200MB/s', 'SSD', 18),
(45, 'SSD Crucial MX500 1TB SATA', 1790000.00, 'crucial-mx500-1tb.jpg', 'SSD SATA bền, giá hợp lý.', 'Loại: SATA 2.5\"; Dung lượng: 1TB; TBW: 700TB', 'SSD', 30),
(46, 'SSD WD Blue SN550 250GB', 420000.00, 'wd-sn550-250.jpg', 'SSD NVMe cơ bản cho nâng cấp hiệu năng.', 'Loại: NVMe M.2; Dung lượng: 250GB; Read: 2400MB/s', 'SSD', 45),
(47, 'SSD Silicon Power 512GB NVMe', 850000.00, 'sp-512gb.jpg', 'SSD NVMe tầm trung, phù hợp nhiều nhu cầu.', 'Dung lượng: 512GB; Loại: NVMe; Read: 2000MB/s', 'SSD', 28),
(48, 'SSD Lexar NM620 1TB NVMe', 1390000.00, 'lexar-nm620-1tb.jpg', 'SSD tốc độ cao phù hợp cho gaming và đồ họa.', 'Dung lượng: 1TB; Đọc: 3500MB/s; Ghi: 3000MB/s; Chuẩn: M.2 NVMe', 'SSD', 26),
(49, 'SSD Kingston KC3000 2TB NVMe', 3990000.00, 'kc3000-2tb.jpg', 'SSD cao cấp với tốc độ cực nhanh.', 'Dung lượng: 2TB; Đọc: 7000MB/s; Ghi: 6000MB/s', 'SSD', 18),
(50, 'HDD Seagate Barracuda 2TB 7200RPM', 2300000.00, 'seagate-2tb.jpg', 'HDD 1TB tốc độ 7200RPM, lưu trữ đa dụng.', 'Dung lượng: 2TB; RPM: 7200; Cache: 256MB; Interface: SATA', 'HDD', 55),
(51, 'HDD Western Digital Blue 2TB', 1190000.00, 'wd-blue-2tb.jpg', 'Ổ cứng dung lượng lớn cho backup và lưu trữ.', 'Dung lượng: 2TB; RPM: 5400; Interface: SATA', 'HDD', 30),
(52, 'HDD Toshiba P300 4TB', 2090000.00, 'toshiba-4tb.jpg', 'HDD 4TB cho NAS/PC lưu trữ dữ liệu lớn.', 'Dung lượng: 4TB; RPM: 7200; Cache: 128MB', 'HDD', 12),
(53, 'HDD Seagate IronWolf 6TB', 3990000.00, 'ironwolf-6tb.jpg', 'Ổ cứng chuyên cho NAS, độ bền cao.', 'Dung lượng: 6TB; RPM: 7200; TBW cao; Optimized NAS', 'HDD', 6),
(54, 'HDD WD Elements 5TB', 2890000.00, 'wd-elements-5tb.jpg', 'Ổ cứng di động/PC dung lượng lớn.', 'Dung lượng: 5TB; RPM: 5400; Interface: SATA', 'HDD', 10),
(55, 'HDD WD Purple 4TB', 1890000.00, 'wd-purple-4tb.jpg', 'Ổ cứng giám sát độ bền cao cho hệ thống camera.', 'Dung lượng: 4TB; Chuẩn: SATA III; 5400RPM; Cache: 64MB', 'HDD', 22),
(56, 'HDD Seagate SkyHawk 8TB', 4290000.00, 'skyhawk-8tb.jpg', 'Ổ cứng cho hệ thống DVR/NVR hoạt động 24/7.', 'Dung lượng: 8TB; 7200RPM; Cache: 256MB', 'HDD', 14),
(57, 'Màn hình LG 24MP59G 24\" IPS', 3290000.00, 'lg-24mp59g.jpg', 'Màn hình 24\" IPS, tần số 75Hz, độ phản hồi nhanh.', 'Kích thước: 24\"; Độ phân giải: 1920x1080; Tần số: 75Hz; Panel: IPS', 'Màn hình - Loa', 20),
(58, 'Màn hình Dell UltraSharp 27\" U2720Q', 12990000.00, 'dell-u2720q.jpg', 'Màn hình 4K chuyên đồ họa, màu chuẩn.', 'Kích thước: 27\"; Độ phân giải: 3840x2160; Panel: IPS; USB-C', 'Màn hình - Loa', 5),
(59, 'Loa Logitech Z333', 990000.00, 'logitech-z333.jpg', 'Loa 2.1 công suất tốt, bass mạnh mẽ cho phòng nhỏ.', 'Công suất RMS: 80W; Loa: 2.1; Kết nối: 3.5mm/RCA', 'Màn hình - Loa', 25),
(60, 'Loa Creative Pebble Plus', 490000.00, 'creative-pebble-plus.jpg', 'Loa desktop nhỏ gọn, âm thanh cân bằng.', 'Loại: 2.1; Công suất: 8W RMS; Kết nối: 3.5mm/USB', 'Màn hình - Loa', 40),
(61, 'Màn hình Samsung Odyssey 27\" 144Hz', 7490000.00, 'samsung-odyssey-27.jpg', 'Màn hình gaming 144Hz, cong nhẹ, tần số cao.', 'Kích thước: 27\"; Độ phân giải: 2560x1440; Tần số: 144Hz; Panel: VA', 'Màn hình - Loa', 7),
(62, 'Màn hình ViewSonic VA2432-h 24', 2190000.00, 'viewsonic-va2432h.jpg', 'Màn hình IPS góc nhìn rộng phù hợp văn phòng.', 'Tấm nền: IPS; Tần số: 75Hz; Cổng: HDMI/VGA', 'Màn hình - Loa', 4);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `phone`, `role`, `created_at`) VALUES
(1, 'tranviet', 'viet@gmail.com', '$2y$10$9lUQaa5wQoASyT4dvwrCO.bUQ4q2/VESS8.fH3olWqdkVyL.TDT.K', NULL, 'admin', '2025-11-01 18:17:55'),
(2, 'viettran', 'tran@gmail.com', '$2y$10$xRa7HK7DHRYpsC1ZngQKLetYCcgjSNu0SN6b21MLVLWBZmRIvr8i.', NULL, 'user', '2025-11-01 20:41:32');

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
(1, 'SALE10', 'percent', 10.00, 1, '2030-12-31'),
(2, 'SAVE50K', 'fixed', 50000.00, 1, '2030-12-31');

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
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_items`
--
ALTER TABLE `saved_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

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
