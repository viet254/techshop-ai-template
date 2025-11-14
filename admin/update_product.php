<?php
// Cập nhật sản phẩm (admin)
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
    header('Location: /techshop-ai-template/login.php');
    exit;
}
require_once __DIR__ . '/../database/connect.php';
$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$price = trim($_POST['price'] ?? '');
$category = trim($_POST['category'] ?? '');
$stock = (int)($_POST['stock'] ?? 0);
$image = trim($_POST['image'] ?? '');
$description = trim($_POST['description'] ?? '');
if ($id > 0 && $name && $price) {
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category = ?, stock = ?, image = ?, description = ? WHERE id = ?");
    $stmt->bind_param('sdssssi', $name, $price, $category, $stock, $image, $description, $id);
    $stmt->execute();
}
header('Location: manage_products.php');
exit;
?>