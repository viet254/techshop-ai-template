<?php
// Cập nhật sản phẩm (admin)
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
    header('Location: /login.php');
    exit;
}
require_once __DIR__ . '/../database/connect.php';
$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$price = trim($_POST['price'] ?? '');
$salePrice = trim($_POST['sale_price'] ?? '');
$category = trim($_POST['category'] ?? '');
$stock = (int)($_POST['stock'] ?? 0);
// Mô tả sản phẩm
$description = trim($_POST['description'] ?? '');
// Thông số kỹ thuật
$specs = trim($_POST['specs'] ?? '');

// Xử lý upload ảnh sản phẩm nếu có
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
        $safeBaseName = preg_replace('/[^A-Za-z0-9_-]/', '', $baseName);
        $imageFileName = $safeBaseName . '_' . $timestamp . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $uploadDir . $imageFileName);
    }
}
// Ảnh hiện tại (nếu không upload file mới)
$currentImage = trim($_POST['current_image'] ?? '');
// Xác định ảnh sẽ lưu: ưu tiên ảnh upload mới, nếu không thì lấy ảnh hiện tại
if ($imageFileName) {
    $image = $imageFileName;
} else {
    $image = $currentImage;
}
if ($id > 0 && $name && $price) {
    // Cập nhật sản phẩm với giá khuyến mãi nếu được nhập, nếu không thì gán sale_price NULL
    if ($salePrice !== '') {
        // Có giá khuyến mãi: sử dụng đúng kiểu tham số (string/double/int)
        $stmt = $conn->prepare(
            "UPDATE products SET name = ?, price = ?, sale_price = ?, category = ?, stock = ?, image = ?, description = ?, specs = ? WHERE id = ?"
        );
        // Kiểu tham số: s (string) cho name; d (double) cho price; d (double) cho sale_price;
        // s (string) cho category; i (int) cho stock; s (string) cho image;
        // s (string) cho description; s (string) cho specs; i (int) cho id
        $stmt->bind_param(
            'sddsisssi',
            $name,
            $price,
            $salePrice,
            $category,
            $stock,
            $image,
            $description,
            $specs,
            $id
        );
    } else {
        // Không có giá khuyến mãi: gán sale_price NULL
        $stmt = $conn->prepare(
            "UPDATE products SET name = ?, price = ?, sale_price = NULL, category = ?, stock = ?, image = ?, description = ?, specs = ? WHERE id = ?"
        );
        // Kiểu tham số: s (string) cho name; d (double) cho price;
        // s (string) cho category; i (int) cho stock; s (string) cho image;
        // s (string) cho description; s (string) cho specs; i (int) cho id
        $stmt->bind_param(
            'sdsisssi',
            $name,
            $price,
            $category,
            $stock,
            $image,
            $description,
            $specs,
            $id
        );
    }
    $stmt->execute();
}
header('Location: manage_products.php');
exit;
?>