<?php
// Xử lý thêm sản phẩm (admin)
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
    header('Location: /login.php');
    exit;
}
require_once __DIR__ . '/../database/connect.php';
// Lấy dữ liệu POST
// Lấy dữ liệu từ form
$name = trim($_POST['name'] ?? '');
$price = trim($_POST['price'] ?? '');
$salePrice = trim($_POST['sale_price'] ?? '');
$category = trim($_POST['category'] ?? '');
$stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
$description = trim($_POST['description'] ?? '');
// Lấy thông số kỹ thuật nếu có
$specs = trim($_POST['specs'] ?? '');

// Xử lý tải lên ảnh sản phẩm nếu có
$imageFileName = '';
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['image_file'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (in_array($file['type'], $allowedTypes)) {
        $uploadDir = __DIR__ . '/../assets/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
        $timestamp = time();
        // Loại bỏ ký tự đặc biệt khỏi tên
        $safeBaseName = preg_replace('/[^A-Za-z0-9_-]/', '', $baseName);
        $imageFileName = $safeBaseName . '_' . $timestamp . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $uploadDir . $imageFileName);
    }
}

// Nếu không upload file mà có input 'image' thì lấy tên file từ input (giữ tương thích cũ)
$imageField = trim($_POST['image'] ?? '');
if ($imageFileName) {
    $image = $imageFileName;
} else {
    $image = $imageField;
}

// Chèn vào DB nếu có tên và giá
if ($name && $price) {
    // Nếu người dùng nhập giá khuyến mãi, chèn giá gốc và giá khuyến mãi; ngược lại gán sale_price là NULL
    if ($salePrice !== '') {
        // Khi có sale_price, bao gồm cột specs trong câu lệnh INSERT
        $stmt = $conn->prepare("INSERT INTO products (name, price, sale_price, image, description, specs, category, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sddssssi', $name, $price, $salePrice, $image, $description, $specs, $category, $stock);
    } else {
        // Khi không có sale_price, gán sale_price = NULL và vẫn lưu specs
        $stmt = $conn->prepare("INSERT INTO products (name, price, sale_price, image, description, specs, category, stock) VALUES (?, ?, NULL, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sdssssi', $name, $price, $image, $description, $specs, $category, $stock);
    }
    $stmt->execute();
}

header('Location: manage_products.php');
exit;
?>