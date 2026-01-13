<?php
// Thiết lập địa chỉ mặc định cho user
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Bạn phải đăng nhập để thiết lập địa chỉ mặc định']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$addressId = (int)($data['id'] ?? 0);

if ($addressId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Địa chỉ không hợp lệ']);
    exit;
}

$userId = (int)$_SESSION['user']['id'];

// Kiểm tra địa chỉ thuộc về user
$check = $conn->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ? LIMIT 1");
$check->bind_param('ii', $addressId, $userId);
$check->execute();
$res = $check->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Không tìm thấy địa chỉ']);
    exit;
}

// Bỏ mặc định tất cả địa chỉ của user
$conn->query("UPDATE addresses SET is_default = 0 WHERE user_id = " . (int)$userId);

// Đặt mặc định cho địa chỉ được chọn
$stmt = $conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $addressId, $userId);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Đã thiết lập địa chỉ mặc định.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Không thể thiết lập địa chỉ mặc định']);
}
?>


