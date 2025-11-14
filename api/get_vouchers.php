<?php
// Lấy danh sách voucher đang hoạt động
header('Content-Type: application/json');
require_once __DIR__ . '/../database/connect.php';
// Chỉ lấy voucher active và chưa hết hạn
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT code, discount_type, discount_value, expiration_date FROM vouchers WHERE active = 1 AND (expiration_date IS NULL OR expiration_date >= ?) ORDER BY expiration_date ASC");
$stmt->bind_param('s', $today);
$stmt->execute();
$res = $stmt->get_result();
$vouchers = [];
while ($row = $res->fetch_assoc()) {
    $vouchers[] = [
        'code' => $row['code'],
        'discount_type' => $row['discount_type'],
        'discount_value' => (float)$row['discount_value'],
        'expiration_date' => $row['expiration_date']
    ];
}
echo json_encode($vouchers);
?>