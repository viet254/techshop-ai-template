<?php
// Admin header include
// Start the session and ensure user is admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    // Redirect to login if not an admin
    header('Location: /techshop-ai-template/login.php');
    exit;
}
$username = htmlspecialchars($_SESSION['user']['username'] ?? '');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>TechShop AI Admin</title>
    <link rel="stylesheet" href="/techshop-ai-template/assets/css/style.css">
</head>
<body>
<header class="admin-header">
    <div class="admin-logo">
        <a href="/techshop-ai-template/admin/dashboard.php">TechShop <span>AI</span> Admin</a>
    </div>
    <div class="admin-user">
        Xin chào, <?= $username ?>
        | <a href="/techshop-ai-template/api/logout.php">Đăng xuất</a>
    </div>
</header>
<nav class="admin-main-nav">
    <a href="/techshop-ai-template/admin/dashboard.php">Bảng điều khiển</a>
    <a href="/techshop-ai-template/admin/manage_products.php">Sản phẩm</a>
    <a href="/techshop-ai-template/admin/manage_orders.php">Đơn hàng</a>
    <a href="/techshop-ai-template/admin/manage_vouchers.php">Voucher</a>
    <a href="/techshop-ai-template/admin/manage_users.php">Người dùng</a>
</nav>
<main class="admin-content">