<?php
// Xóa voucher (admin)
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
    header('Location: /login.php');
    exit;
}
require_once __DIR__ . '/../database/connect.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM vouchers WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}
header('Location: manage_vouchers.php');
exit;
?>