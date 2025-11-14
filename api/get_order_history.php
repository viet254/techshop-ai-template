<?php
// Lịch sử đơn hàng của người dùng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
if (!isset($_SESSION['user'])) {
    echo json_encode([]);
    exit;
}
$userId = (int)$_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT id, total, discount, final_total, voucher_code, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$orders = [];
while ($row = $res->fetch_assoc()) {
    $orders[] = [
        'id' => $row['id'],
        'total' => (float)$row['total'],
        'discount' => (float)$row['discount'],
        'final_total' => (float)$row['final_total'],
        'voucher_code' => $row['voucher_code'],
        'status' => $row['status'],
        'created_at' => $row['created_at']
    ];
}
echo json_encode($orders);
?>