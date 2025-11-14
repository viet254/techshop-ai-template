<?php
// Thêm địa chỉ giao hàng mới cho người dùng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';

// Chỉ cho phép user đã đăng nhập
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Bạn phải đăng nhập để thêm địa chỉ']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$recipientName = trim($data['recipient_name'] ?? '');
$phone = trim($data['phone'] ?? '');
$address = trim($data['address'] ?? '');

if ($recipientName === '' || $address === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Thông tin bắt buộc: tên người nhận và địa chỉ']);
    exit;
}

$userId = (int)$_SESSION['user']['id'];
$stmt = $conn->prepare("INSERT INTO addresses (user_id, recipient_name, phone, address) VALUES (?, ?, ?, ?)");
$stmt->bind_param('isss', $userId, $recipientName, $phone, $address);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Không thể thêm địa chỉ']);
}
?>