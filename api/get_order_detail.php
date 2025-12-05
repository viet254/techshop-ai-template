<?php
// API: Lấy chi tiết đơn hàng của người dùng
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../database/connect.php';
// Nếu kết nối tới cơ sở dữ liệu gặp lỗi, trả về JSON thông báo lỗi thay vì để script dừng đột ngột
if (isset($conn) && $conn->connect_error) {
    echo json_encode([
        'error' => 'Database connection failed',
        'message' => $conn->connect_error
    ]);
    exit;
}
// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    echo json_encode(null);
    exit;
}
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($orderId <= 0) {
    echo json_encode(null);
    exit;
}
$userId = (int)$_SESSION['user']['id'];
// Kiểm tra xem đơn hàng có thuộc về user hay user là admin
$stmt = $conn->prepare("SELECT id, user_id, address_id, total, discount, final_total, voucher_code, status, cancel_reason, created_at FROM orders WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
if (!$order) {
    echo json_encode(null);
    exit;
}
$isAdmin = ($_SESSION['user']['role'] ?? 'user') === 'admin';
if (!$isAdmin && (int)$order['user_id'] !== $userId) {
    echo json_encode(null);
    exit;
}
// Lấy danh sách sản phẩm trong đơn
$stmtItems = $conn->prepare("SELECT oi.product_id, oi.quantity, p.name, p.price FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmtItems->bind_param('i', $orderId);
$stmtItems->execute();
$resItems = $stmtItems->get_result();
$items = [];
while ($row = $resItems->fetch_assoc()) {
    $items[] = [
        'product_id' => (int)$row['product_id'],
        'name' => $row['name'],
        'price' => (float)$row['price'],
        'quantity' => (int)$row['quantity']
    ];
}
// Lấy thông tin địa chỉ
$addressInfo = null;
if ($order['address_id']) {
    $stmtAddr = $conn->prepare("SELECT recipient_name, phone, address FROM addresses WHERE id = ? LIMIT 1");
    $stmtAddr->bind_param('i', $order['address_id']);
    $stmtAddr->execute();
    $resAddr2 = $stmtAddr->get_result();
    $addressInfo = $resAddr2->fetch_assoc();
}
// Thông tin đơn hàng
$info = [
    'id' => $order['id'],
    'status' => $order['status'],
    'created_at' => $order['created_at'],
    'user_id' => $order['user_id'],
    'total' => (float)$order['total'],
    'discount' => (float)$order['discount'],
    'final_total' => (float)$order['final_total'],
    'voucher_code' => $order['voucher_code'],
    'cancel_reason' => $order['cancel_reason'],
    'address' => $addressInfo,
    'username' => null,
    'email' => null
];
// Nếu admin, lấy thêm thông tin người dùng
if ($isAdmin) {
    $stmtUser = $conn->prepare("SELECT username, email FROM users WHERE id = ? LIMIT 1");
    $stmtUser->bind_param('i', $order['user_id']);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();
    if ($u = $resUser->fetch_assoc()) {
        $info['username'] = $u['username'];
        $info['email'] = $u['email'];
    }
}
echo json_encode([
    'info' => $info,
    'items' => $items
]);
?>