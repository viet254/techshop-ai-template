<?php
// Lấy thông tin cá nhân người dùng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
if (!isset($_SESSION['user'])) {
    echo json_encode(new stdClass());
    exit;
}
$userId = (int)$_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT username AS name, email, phone, avatar FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
echo json_encode($user ?: new stdClass());
?>