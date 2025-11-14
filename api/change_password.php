<?php
// Đổi mật khẩu
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$old = $data['old_password'] ?? '';
$new = $data['new_password'] ?? '';
if (!$old || !$new) {
    echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
    exit;
}
$userId = (int)$_SESSION['user']['id'];
// Lấy mật khẩu cũ
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row || !password_verify($old, $row['password'])) {
    echo json_encode(['success' => false, 'message' => 'Mật khẩu cũ không đúng']);
    exit;
}
// Cập nhật mật khẩu mới
$hash = password_hash($new, PASSWORD_BCRYPT);
$update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update->bind_param('si', $hash, $userId);
if ($update->execute()) {
    echo json_encode(['success' => true, 'message' => 'Đã đổi mật khẩu']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật']);
}
?>