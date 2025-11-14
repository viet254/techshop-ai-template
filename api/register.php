<?php
// API đăng ký tài khoản
header('Content-Type: application/json');
require_once __DIR__ . '/../database/connect.php';
session_start();
$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Thông tin không hợp lệ']);
    exit;
}
// Kiểm tra tồn tại
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc email đã tồn tại']);
    exit;
}
// Hash password
$hash = password_hash($password, PASSWORD_BCRYPT);
$insert = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
$insert->bind_param('sss', $username, $email, $hash);
if ($insert->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể tạo tài khoản']);
}
?>