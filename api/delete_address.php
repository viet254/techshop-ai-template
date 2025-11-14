<?php
// Xóa địa chỉ giao hàng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Bạn phải đăng nhập để xóa địa chỉ']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$addressId = (int)($data['id'] ?? 0);
if ($addressId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Địa chỉ không hợp lệ']);
    exit;
}

$userId = (int)$_SESSION['user']['id'];
// Kiểm tra quyền sở hữu
$stmt = $conn->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param('ii', $addressId, $userId);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Không tìm thấy địa chỉ']);
    exit;
}
$stmt = $conn->prepare("DELETE FROM addresses WHERE id = ?");
$stmt->bind_param('i', $addressId);
$success = $stmt->execute();
echo json_encode(['success' => $success]);
?>