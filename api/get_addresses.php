<?php
// Lấy danh sách địa chỉ giao hàng của người dùng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';

if (!isset($_SESSION['user'])) {
    echo json_encode([]);
    exit;
}

$userId = (int)$_SESSION['user']['id'];
$stmt = $conn->prepare("
    SELECT id, recipient_name, email, phone, city, district, address, is_default
    FROM addresses
    WHERE user_id = ?
    ORDER BY is_default DESC, created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$addresses = [];
while ($row = $res->fetch_assoc()) {
    $addresses[] = $row;
}
echo json_encode($addresses);
?>
