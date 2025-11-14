<?php
// Cập nhật trạng thái đơn hàng cho admin
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Không có quyền']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : 0;
$status = trim($data['status'] ?? '');
if ($id <= 0 || $status === '') {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}
$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái']);
}
?>