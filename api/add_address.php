<?php
// Thêm địa chỉ giao hàng mới cho người dùng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';

// Chỉ cho phép user đã đăng nhập
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Bạn phải đăng nhập để thêm địa chỉ']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Lấy dữ liệu từ form
$recipientName = trim($data['recipient_name'] ?? '');
$email        = trim($data['email'] ?? '');
$phone        = trim($data['phone'] ?? '');
$city         = trim($data['city'] ?? '');
$district     = trim($data['district'] ?? '');
$street       = trim($data['address'] ?? '');   // textarea "Địa chỉ giao hàng"

// Kiểm tra bắt buộc
if ($recipientName === '' || $street === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Thông tin bắt buộc: tên người nhận và địa chỉ']);
    exit;
}

$userId = (int)$_SESSION['user']['id'];

// Ghép địa chỉ đầy đủ để lưu vào cột address
$fullAddress = $street;
if ($district !== '') {
    $fullAddress .= ', ' . $district;
}
if ($city !== '') {
    $fullAddress .= ', ' . $city;
}

$stmt = $conn->prepare("
    INSERT INTO addresses (user_id, recipient_name, email, phone, city, district, address)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    'issssss',
    $userId,
    $recipientName,
    $email,
    $phone,
    $city,
    $district,
    $fullAddress
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Không thể thêm địa chỉ']);
}
?>
