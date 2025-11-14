<?php
// Xử lý thêm sản phẩm (admin)
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
    header('Location: /techshop-ai-template/login.php');
    exit;
}
require_once __DIR__ . '/../database/connect.php';
// Lấy dữ liệu POST
$name = trim($_POST['name'] ?? '');
$price = trim($_POST['price'] ?? '');
$category = trim($_POST['category'] ?? '');
$stock = (int)($_POST['stock'] ?? 0);
$image = trim($_POST['image'] ?? '');
$description = trim($_POST['description'] ?? '');
if ($name && $price) {
    $stmt = $conn->prepare("INSERT INTO products (name, price, image, description, category, stock) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sdsssi', $name, $price, $image, $description, $category, $stock);
    $stmt->execute();
}
header('Location: manage_products.php');
exit;
?>