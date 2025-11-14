<?php
// Kiểm tra mã voucher
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$code = trim($data['code'] ?? '');
if ($code === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Mã voucher không hợp lệ']);
    exit;
}

$stmt = $conn->prepare("SELECT code, discount_type, discount_value, active, expiration_date FROM vouchers WHERE code = ? LIMIT 1");
$stmt->bind_param('s', $code);
$stmt->execute();
$res = $stmt->get_result();
$voucher = $res->fetch_assoc();
if (!$voucher) {
    http_response_code(404);
    echo json_encode(['error' => 'Voucher không tồn tại']);
    exit;
}
// Kiểm tra còn hiệu lực
if (!$voucher['active'] || ($voucher['expiration_date'] && $voucher['expiration_date'] < date('Y-m-d'))) {
    http_response_code(410);
    echo json_encode(['error' => 'Voucher đã hết hạn']);
    exit;
}
// Trả về thông tin voucher
echo json_encode([
    'code' => $voucher['code'],
    'type' => $voucher['discount_type'],
    'value' => (float)$voucher['discount_value']
]);
?>