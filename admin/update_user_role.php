<?php
// Cập nhật quyền người dùng (admin)
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
    header('Location: /login.php');
    exit;
}
require_once __DIR__ . '/../database/connect.php';
$userId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$newRole = isset($_POST['role']) ? trim($_POST['role']) : 'user';
// Không cho phép cập nhật quyền bản thân thông qua form này
if ($userId > 0 && $userId != ($_SESSION['user']['id'] ?? 0) && ($newRole === 'admin' || $newRole === 'user')) {
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param('si', $newRole, $userId);
    $stmt->execute();
}
header('Location: manage_users.php');
exit;
?>