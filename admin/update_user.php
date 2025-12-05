<?php
// Xử lý cập nhật thông tin người dùng từ trang edit_user.php
session_start();
// Chỉ cho phép admin cập nhật
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../database/connect.php';

// Thu thập dữ liệu POST
$userId   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$password = trim($_POST['password'] ?? '');

// Không cho phép cập nhật chính bản thân qua script này
if ($userId <= 0 || $userId == ($_SESSION['user']['id'] ?? 0)) {
    header('Location: manage_users.php');
    exit;
}

// Chuẩn bị câu lệnh cập nhật động
$fields = [];
$params = [];
$types  = '';
if ($username !== '') {
    $fields[] = 'username = ?';
    $params[] = $username;
    $types  .= 's';
}
if ($email !== '') {
    $fields[] = 'email = ?';
    $params[] = $email;
    $types  .= 's';
}
// Điện thoại có thể để trống
if ($phone !== '') {
    $fields[] = 'phone = ?';
    $params[] = $phone;
    $types  .= 's';
} else {
    // Nếu admin xóa nội dung điện thoại, đặt về NULL
    $fields[] = 'phone = NULL';
}
if ($password !== '') {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $fields[] = 'password = ?';
    $params[] = $passwordHash;
    $types  .= 's';
}

if ($fields) {
    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $params[] = $userId;
    $types    .= 'i';
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Sử dụng "splat" operator chỉ khả dụng trong PHP 5.6+
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }
}
// Điều hướng lại trang quản lý với thông báo
header('Location: manage_users.php?msg=updated');
exit;