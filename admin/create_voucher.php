<?php
// Xử lý thêm voucher (admin)
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
    header('Location: /techshop-ai-template/login.php');
    exit;
}
require_once __DIR__ . '/../database/connect.php';

// Lấy dữ liệu POST
$code = trim($_POST['code'] ?? '');
$discountType = trim($_POST['discount_type'] ?? 'percent');
$discountValue = floatval($_POST['discount_value'] ?? 0);
$expirationDate = trim($_POST['expiration_date'] ?? null);

if ($code && $discountValue > 0) {
    $stmt = $conn->prepare("INSERT INTO vouchers (code, discount_type, discount_value, expiration_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssds', $code, $discountType, $discountValue, $expirationDate);
    $stmt->execute();
}
header('Location: manage_vouchers.php');
exit;
?>