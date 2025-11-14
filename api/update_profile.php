<?php
// Cập nhật thông tin cá nhân
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$userId = (int)$_SESSION['user']['id'];
if (!$name || !$email) {
    echo json_encode(['success' => false, 'message' => 'Tên và email bắt buộc']);
    exit;
}
// Kiểm tra email đã tồn tại cho người khác
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
$stmt->bind_param('si', $email, $userId);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng']);
    exit;
}
// Update
$update = $conn->prepare("UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?");
$update->bind_param('sssi', $name, $email, $phone, $userId);
if ($update->execute()) {
    // Cập nhật session
    $_SESSION['user']['username'] = $name;
    $_SESSION['user']['email'] = $email;
    echo json_encode(['success' => true, 'message' => 'Đã cập nhật thông tin']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật']);
}
?>