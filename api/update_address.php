<?php
// Cập nhật địa chỉ giao hàng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Bạn phải đăng nhập để cập nhật địa chỉ']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$addressId     = (int)($data['id'] ?? 0);
$recipientName = trim($data['recipient_name'] ?? '');
$email         = trim($data['email'] ?? '');
$phone         = trim($data['phone'] ?? '');
$city          = trim($data['city'] ?? '');
$district      = trim($data['district'] ?? '');
$street        = trim($data['address'] ?? '');

if ($addressId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Địa chỉ không hợp lệ']);
    exit;
}

if ($recipientName === '' || $street === '') {
    echo json_encode(['success' => false, 'error' => 'Thông tin bắt buộc: tên người nhận và địa chỉ']);
    exit;
}

$userId = (int)$_SESSION['user']['id'];

// Đảm bảo địa chỉ thuộc về user hiện tại
$check = $conn->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ? LIMIT 1");
$check->bind_param('ii', $addressId, $userId);
$check->execute();
$res = $check->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Không tìm thấy địa chỉ']);
    exit;
}

// Ghép lại chuỗi địa chỉ đầy đủ giống add_address.php
$fullAddress = $street;
if ($district !== '') {
    $fullAddress .= ', ' . $district;
}
if ($city !== '') {
    $fullAddress .= ', ' . $city;
}

$stmt = $conn->prepare("
    UPDATE addresses
    SET recipient_name = ?, email = ?, phone = ?, city = ?, district = ?, address = ?
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param(
    'ssssssii',
    $recipientName,
    $email,
    $phone,
    $city,
    $district,
    $fullAddress,
    $addressId,
    $userId
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Đã cập nhật địa chỉ.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Không thể cập nhật địa chỉ']);
}
?>


